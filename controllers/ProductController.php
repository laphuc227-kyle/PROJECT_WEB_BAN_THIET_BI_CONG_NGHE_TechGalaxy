<?php

namespace Controllers;

use Models\Category;
use Models\Product;
use Models\ProductImage;
use Models\Wishlist;

/**
 * ProductController
 *
 * Điều hướng các request liên quan đến sản phẩm:
 *  - Trang Shop (danh sách, tìm kiếm, lọc, phân trang)
 *  - Trang Product Detail (chi tiết, gallery, wishlist, related)
 *  - AJAX Wishlist (thêm/xóa)
 *  - Admin CRUD sản phẩm
 */
class ProductController
{
    private Product      $productModel;
    private Category     $categoryModel;
    private ProductImage $imageModel;
    private Wishlist     $wishlistModel;

    public function __construct()
    {
        $this->productModel  = new Product();
        $this->categoryModel = new Category();
        $this->imageModel    = new ProductImage();
        $this->wishlistModel = new Wishlist();
    }

    // =========================================================================
    //  USER-FACING ACTIONS
    // =========================================================================

    /**
     * Trang Shop — Danh sách sản phẩm
     * URL: /shop  |  /shop?category=5  |  /shop?q=laptop&category=3&page=2
     *
     * Luồng xử lý:
     * 1. Đọc tham số GET (keyword, category_id, page)
     * 2. Gọi model tương ứng để lấy sản phẩm
     * 3. Lấy danh sách danh mục cho sidebar
     * 4. Truyền dữ liệu vào view
     */
    public function shop(): void
    {
        // --- 1. Đọc tham số ---
        $page       = max(1, (int) ($_GET['page']     ?? 1));
        $categoryId = isset($_GET['category']) ? (int) $_GET['category'] : null;
        $keyword    = trim($_GET['q'] ?? '');

        // --- 2. Lấy sản phẩm theo điều kiện ---
        if (!empty($keyword)) {
            // Trường hợp: có từ khóa tìm kiếm
            $result = $this->productModel->searchProducts($keyword, $categoryId, $page);
        } elseif ($categoryId !== null) {
            // Trường hợp: lọc theo danh mục
            $result = $this->productModel->getProductsByCategory($categoryId, $page);
        } else {
            // Trường hợp: hiển thị tất cả
            $result = $this->productModel->getAllProducts($page);
        }

        // --- 3. Lấy danh sách danh mục cho sidebar ---
        $categories       = $this->categoryModel->getAllCategories();
        $selectedCategory = $categoryId;

        // --- 4. Nạp view ---
        $products        = $result['data'];
        $totalProducts   = $result['total'];
        $totalPages      = $result['pages'];
        $currentPage     = $page;
        $searchKeyword   = $keyword;

        require_once __DIR__ . '/../views/user/shop.php';
    }

    // Thêm hàm này vào trong class ProductController (ngang hàng với hàm shop)
    public function home() 
    {
        // Giả sử trang chủ cần hiển thị sản phẩm mới hoặc sản phẩm nổi bật
        // Bạn có thể gọi model lấy data ở đây nếu Thuận cần dữ liệu
        // $featuredProducts = $this->productModel->getFeaturedProducts(); 

        // Nạp file giao diện trang chủ thực sự của Thuận
        require_once __DIR__ . '/../views/user/index.php';
    }


    /**
     * Trang Product Detail — Chi tiết sản phẩm
     * URL: /product/{id}
     *
     * Luồng xử lý:
     * 1. Lấy ID sản phẩm từ URL
     * 2. Truy vấn thông tin sản phẩm, nếu không tìm thấy → 404
     * 3. Lấy gallery ảnh
     * 4. Kiểm tra trạng thái wishlist của user hiện tại (nếu đã đăng nhập)
     * 5. Lấy sản phẩm liên quan (cùng category)
     * 6. Nạp view
     */
    public function productDetail(int $id): void
    {
        // --- 1 & 2. Lấy thông tin sản phẩm ---
        $product = $this->productModel->getProductById($id);
        if (!$product) {
            http_response_code(404);
            echo '<h1>Sản phẩm không tồn tại.</h1>';
            return;
        }

        // --- 3. Lấy gallery ảnh ---
        $images = $this->imageModel->getImagesByProduct($id);

        // --- 4. Kiểm tra wishlist (chỉ khi user đã đăng nhập) ---
        $isWishlisted = false;
        $wishlistId   = null;
        $userId       = $_SESSION['user_id'] ?? null;

        if ($userId) {
            $wid = $this->wishlistModel->checkWishlistExists((int) $userId, $id);
            if ($wid !== false) {
                $isWishlisted = true;
                $wishlistId   = $wid;
            }
        }

        // --- 5. Sản phẩm liên quan ---
        $relatedProducts = $this->productModel->getRelatedProducts($id, (int) $product['category_id']);

        // --- 6. Nạp view ---
        require_once __DIR__ . '/../views/user/product_detail.php';
    }

    /**
     * Trang Wishlist của người dùng
     * URL: /wishlist
     * Yêu cầu đăng nhập — nếu chưa đăng nhập, redirect sang trang login
     */
    public function wishlistPage(): void
    {
        $this->requireLogin();

        $userId   = (int) $_SESSION['user_id'];
        $items    = $this->wishlistModel->getWishlistByUser($userId);

        // Flash message (thêm/xóa thành công)
        $flashMessage = $_SESSION['flash_message'] ?? null;
        $flashType    = $_SESSION['flash_type']    ?? 'success';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);

        require_once __DIR__ . '/../views/user/wishlist.php';
    }

    // =========================================================================
    //  AJAX ACTIONS — Wishlist Toggle
    // =========================================================================

    /**
     * AJAX: Thêm sản phẩm vào Wishlist
     * Method: POST
     * Body: { product_id: int }
     * Response: JSON { success, message, wishlisted, wishlist_id }
     */
    public function ajaxAddWishlist(): void
    {
        $this->requireLoginJson();
        $this->requirePostJson();

        $userId    = (int) $_SESSION['user_id'];
        $productId = (int) ($_POST['product_id'] ?? 0);

        if ($productId <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'ID sản phẩm không hợp lệ.']);
            return;
        }

        $result = $this->wishlistModel->addToWishlist($userId, $productId);

        $this->jsonResponse([
            'success'     => $result['success'],
            'message'     => $result['message'],
            'wishlisted'  => $result['success'],
            'wishlist_id' => $result['id'] ?? null,
        ]);
    }

    /**
     * AJAX: Xóa sản phẩm khỏi Wishlist (toggle)
     * Method: POST
     * Body: { product_id: int }
     * Response: JSON { success, message, wishlisted }
     */
    public function ajaxRemoveWishlist(): void
    {
        $this->requireLoginJson();
        $this->requirePostJson();

        $userId    = (int) $_SESSION['user_id'];
        $productId = (int) ($_POST['product_id'] ?? 0);

        if ($productId <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'ID sản phẩm không hợp lệ.']);
            return;
        }

        $result = $this->wishlistModel->removeByUserAndProduct($userId, $productId);

        $this->jsonResponse([
            'success'    => $result['success'],
            'message'    => $result['message'],
            'wishlisted' => false,
        ]);
    }

    /**
     * AJAX: Toggle Wishlist (thêm nếu chưa có, xóa nếu đã có)
     * Method: POST
     * Body: { product_id: int }
     * Response: JSON { success, message, wishlisted, wishlist_id }
     */
    public function ajaxToggleWishlist(): void
    {
        $this->requireLoginJson();
        $this->requirePostJson();

        $userId    = (int) $_SESSION['user_id'];
        $productId = (int) ($_POST['product_id'] ?? 0);

        if ($productId <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'ID sản phẩm không hợp lệ.']);
            return;
        }

        $existingId = $this->wishlistModel->checkWishlistExists($userId, $productId);

        if ($existingId !== false) {
            // Đã có → xóa
            $this->wishlistModel->removeFromWishlist($existingId);
            $this->jsonResponse([
                'success'    => true,
                'message'    => 'Đã xóa khỏi danh sách yêu thích.',
                'wishlisted' => false,
            ]);
        } else {
            // Chưa có → thêm
            $result = $this->wishlistModel->addToWishlist($userId, $productId);
            $this->jsonResponse([
                'success'     => $result['success'],
                'message'     => $result['message'],
                'wishlisted'  => true,
                'wishlist_id' => $result['id'] ?? null,
            ]);
        }
    }

    // =========================================================================
    //  ADMIN ACTIONS
    // =========================================================================

    /**
     * Admin: Danh sách sản phẩm (có phân trang)
     */
    public function adminIndex(): void
    {
        $this->requireAdmin();
        $page   = max(1, (int) ($_GET['page'] ?? 1));
        $result = $this->productModel->getAllProducts($page, 15);

        $products    = $result['data'];
        $totalPages  = $result['pages'];
        $currentPage = $page;

        require_once __DIR__ . '/../views/admin/products/index.php';
    }

    /**
     * Admin: Form thêm sản phẩm mới
     */
    public function adminCreate(): void
    {
        $this->requireAdmin();

        $categories = $this->categoryModel->getAllCategories();
        $errors     = [];
        $old        = [];    // dữ liệu cũ để giữ lại khi form bị lỗi

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // --- Validate ---
            $old = $_POST;
            $errors = $this->validateProductForm($_POST);

            if (empty($errors)) {
                // --- Lưu sản phẩm ---
                $productId = $this->productModel->createProduct($_POST);

                // --- Upload ảnh nếu có ---
                if (!empty($_FILES['images']['name'][0])) {
                    $uploadResult = $this->imageModel->uploadImages($_FILES['images'], $productId);
                    if (!empty($uploadResult['errors'])) {
                        // Ghi nhận lỗi ảnh nhưng vẫn tiếp tục (sản phẩm đã được tạo)
                        $_SESSION['flash_message'] = 'Sản phẩm đã tạo nhưng có lỗi ảnh: ' . implode('; ', $uploadResult['errors']);
                        $_SESSION['flash_type']    = 'warning';
                    } else {
                        $_SESSION['flash_message'] = 'Thêm sản phẩm thành công!';
                        $_SESSION['flash_type']    = 'success';
                    }
                } else {
                    $_SESSION['flash_message'] = 'Thêm sản phẩm thành công!';
                    $_SESSION['flash_type']    = 'success';
                }

            header('Location: ' . BASE_URL . '/admin/products');                exit;
            }
        }

        require_once __DIR__ . '/../views/admin/products/create.php';
    }

    /**
     * Admin: Form chỉnh sửa sản phẩm
     */
    public function adminEdit(int $id): void
    {
        $this->requireAdmin();

        $product    = $this->productModel->getProductById($id);
        $categories = $this->categoryModel->getAllCategories();
        $images     = $this->imageModel->getImagesByProduct($id);
        $errors     = [];

        if (!$product) {
            header('Location: ' . BASE_URL . '/admin/products');       
                 exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->validateProductForm($_POST);

            if (empty($errors)) {
                $this->productModel->updateProduct($id, $_POST);

                // Upload ảnh mới nếu có
                if (!empty($_FILES['images']['name'][0])) {
                    $this->imageModel->uploadImages($_FILES['images'], $id);
                }

                // Xóa ảnh nếu được chỉ định
                if (!empty($_POST['delete_images'])) {
                    foreach ($_POST['delete_images'] as $imgId) {
                        $this->imageModel->deleteImage((int) $imgId);
                    }
                }

                $_SESSION['flash_message'] = 'Cập nhật sản phẩm thành công!';
                $_SESSION['flash_type']    = 'success';
                header('Location: ' . BASE_URL . '/admin/products');
                exit;
            }

            // Reload lại ảnh sau khi submit (có thể đã xóa một số)
            $images = $this->imageModel->getImagesByProduct($id);
        }

        require_once __DIR__ . '/../views/admin/products/edit.php';
    }

    /**
     * Admin: Xóa sản phẩm
     */
    public function adminDelete(int $id): void
    {
        $this->requireAdmin();
        $this->productModel->deleteProduct($id);

        $_SESSION['flash_message'] = 'Đã xóa sản phẩm.';
        $_SESSION['flash_type']    = 'info';
        header('Location: ' . BASE_URL . '/admin/products');       
 exit;
    }

    // =========================================================================
    //  PRIVATE HELPERS
    // =========================================================================

    /**
     * Validate form sản phẩm
     * @return array Mảng lỗi (rỗng nếu hợp lệ)
     */
    private function validateProductForm(array $data): array
    {
        $errors = [];

        if (empty(trim($data['name'] ?? ''))) {
            $errors['name'] = 'Tên sản phẩm không được để trống.';
        } elseif (mb_strlen($data['name']) > 255) {
            $errors['name'] = 'Tên sản phẩm tối đa 255 ký tự.';
        }

        if (!isset($data['price']) || !is_numeric($data['price']) || $data['price'] < 0) {
            $errors['price'] = 'Giá sản phẩm không hợp lệ.';
        }

        if (!empty($data['sale_price'])) {
            if (!is_numeric($data['sale_price']) || $data['sale_price'] < 0) {
                $errors['sale_price'] = 'Giá khuyến mãi không hợp lệ.';
            } elseif ($data['sale_price'] >= $data['price']) {
                $errors['sale_price'] = 'Giá khuyến mãi phải nhỏ hơn giá gốc.';
            }
        }

        if (empty($data['category_id']) || !is_numeric($data['category_id'])) {
            $errors['category_id'] = 'Vui lòng chọn danh mục.';
        }

        if (!isset($data['stock']) || !ctype_digit((string) $data['stock'])) {
            $errors['stock'] = 'Số lượng tồn kho không hợp lệ.';
        }

        return $errors;
    }

    /**
     * Kiểm tra đăng nhập — nếu chưa thì redirect login
     */

     private function requireLogin(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
    }

    /**
     * Kiểm tra đăng nhập cho AJAX — trả JSON 401 nếu chưa đăng nhập
     */

     private function requireLoginJson(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Vui lòng đăng nhập để thực hiện.', 'redirect' => BASE_URL . '/login'], 401);
            exit;
        }
    }

    /**
     * Kiểm tra quyền Admin
     */
    /**
     * ADMIN: Gọi giao diện form Thêm sản phẩm
     */
    

    /**
     * ADMIN: Nhận dữ liệu POST và lưu vào Database
     */
    public function adminStore(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            global $pdo; // Dùng biến toàn cục giống như các Controller khác
            
            // 1. Nhận và làm sạch dữ liệu từ Form
            $name        = trim($_POST['name'] ?? '');
            $price       = (float)($_POST['price'] ?? 0);
            $salePrice   = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
            $stock       = (int)($_POST['stock'] ?? 0);
            $categoryId  = (int)($_POST['category_id'] ?? 1);
            $status      = $_POST['status'] ?? 'draft';
            $description = trim($_POST['description'] ?? '');
            
            // 2. Xử lý Upload Ảnh (Nếu có)
            $primaryImage = null;
            if (isset($_FILES['primary_image']) && $_FILES['primary_image']['error'] === 0) {
                // Đảm bảo thư mục lưu ảnh tồn tại
                $targetDir = __DIR__ . '/../public/uploads/';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                
                $fileName = time() . '_' . basename($_FILES['primary_image']['name']);
                $targetFile = $targetDir . $fileName;
                
                // Di chuyển ảnh vừa upload vào thư mục
                if (move_uploaded_file($_FILES['primary_image']['tmp_name'], $targetFile)) {
                    $primaryImage = 'public/uploads/' . $fileName;
                }
            }

            // 3. Thêm dữ liệu vào Database bằng PDO
            try {
                $sql = "INSERT INTO products (name, price, sale_price, stock, category_id, status, description, primary_image) 
                        VALUES (:name, :price, :sale_price, :stock, :category_id, :status, :description, :primary_image)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':name'          => $name,
                    ':price'         => $price,
                    ':sale_price'    => $salePrice,
                    ':stock'         => $stock,
                    ':category_id'   => $categoryId,
                    ':status'        => $status,
                    ':description'   => $description,
                    ':primary_image' => $primaryImage
                ]);

                $_SESSION['flash_message'] = 'Đã thêm sản phẩm thành công!';
                $_SESSION['flash_type']    = 'success';
            } catch (\Exception $e) {
                $_SESSION['flash_message'] = 'Lỗi lưu dữ liệu: ' . $e->getMessage();
                $_SESSION['flash_type']    = 'danger';
            }
        }

        // Chuyển hướng về lại danh sách sản phẩm
            header('Location: ' . BASE_URL . '/admin/products');
        exit;
    }
    private function requireAdmin(): void
    {
        if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    /**
     * Kiểm tra method POST cho AJAX — trả JSON 405 nếu không đúng
     */
    private function requirePostJson(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method không hợp lệ.'], 405);
            exit;
        }
    }

    /**
     * Gửi response JSON và kết thúc script
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
