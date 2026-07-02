<?php
// File: controllers/CartController.php
declare(strict_types=1);

namespace Controllers;

// 1. NẠP TRỰC TIẾP CÁC FILE MODEL
require_once __DIR__ . '/../models/BaseModel.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/CartItem.php';
require_once __DIR__ . '/../models/Coupon.php';

// 2. KHAI BÁO SỬ DỤNG
use Models\Product;

class CartController
{
    private \Cart $cartModel;
    
   
    private \CartItem $cartItemModel; 
    
    private Product $productModel;
    private \Coupon $couponModel;

    public function __construct()
    {
        $this->cartModel     = new \Cart();
        
        // ĐÃ SỬA: Thêm dấu \ khi khởi tạo CartItem
        $this->cartItemModel = new \CartItem(); 
        
        $this->productModel  = new Product();
        $this->couponModel   = new \Coupon();
    }

    // ----------------------------------------------------------
    // Helper: lấy user_id từ session (null nếu guest)
    // AuthController lưu $_SESSION['user']['id']
    // ----------------------------------------------------------
    private function getUserId(): ?int
    {
        // ĐÃ SỬA LẠI CHO ĐÚNG: AuthController lưu $_SESSION['user_id'] là số nguyên,
        // không phải mảng — trước đây check ['id'] trên số nguyên nên luôn ra null
        return !empty($_SESSION['user_id'])
            ? (int) $_SESSION['user_id']
            : null;
    }

    // ----------------------------------------------------------
    // Helper: trả JSON và kết thúc request
    // ----------------------------------------------------------
    private function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ----------------------------------------------------------
    // Helper: tính lại toàn bộ tổng tiền giỏ hàng
    // Dùng lại nhiều lần trong update/remove/coupon
    // ----------------------------------------------------------
    private function calcTotals(int $cartId): array
    {
        $subtotal    = (int) $this->cartModel->getCartSubtotal($cartId);
        $shippingFee = $subtotal >= 500000 ? 0 : 30000;
        $discount    = (int) ($_SESSION['coupon_discount'] ?? 0);
        $total       = $subtotal + $shippingFee - $discount;

        return [
            'subtotal'    => $subtotal,
            'shippingFee' => $shippingFee,
            'discount'    => $discount,
            'total'       => max(0, $total), // không âm
        ];
    }

    // ----------------------------------------------------------
    // GET /cart — hiển thị trang giỏ hàng
    // ----------------------------------------------------------
    public function showCart(): void
    {
        $userId    = $this->getUserId();
        $sessionId = session_id();

        $cart  = $this->cartModel->getOrCreateCart($userId, $sessionId);
        $items = $this->cartItemModel->getByCartId((int) $cart['id']);

        $totals      = $this->calcTotals((int) $cart['id']);
        $subtotal    = $totals['subtotal'];
        $shippingFee = $totals['shippingFee'];
        $discount    = $totals['discount'];
        $total       = $totals['total'];
        $couponCode  = $_SESSION['coupon_code'] ?? '';

        $pageTitle = 'Giỏ hàng';
        require_once __DIR__ . '/../views/user/cart.php';
    }

    // ----------------------------------------------------------
    // POST /cart/add — thêm sản phẩm (AJAX → JSON)
    // ----------------------------------------------------------
    public function addToCart(): void
    {
        $raw = json_decode(file_get_contents('php://input'), true) ?: [];
        $productId = (int) ($_POST['product_id'] ?? $raw['product_id'] ?? 0);
        $quantity  = (int) ($_POST['quantity']   ?? $raw['quantity']   ?? 1);

        // Validate input
        if ($productId <= 0 || $quantity <= 0) {
            $this->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
            ], 400);
        }

        // Kiểm tra sản phẩm tồn tại
        $product = $this->productModel->findById($productId);
        if (!$product) {
            $this->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại.',
            ], 404);
        }

        // Kiểm tra stock
        if ((int) $product['stock'] <= 0) {
            $this->json([
                'success' => false,
                'message' => 'Sản phẩm đã hết hàng.',
            ], 422);
        }

        $userId    = $this->getUserId();
        $sessionId = session_id();
        $cart      = $this->cartModel->getOrCreateCart($userId, $sessionId);
        $cartId    = (int) $cart['id'];

        // Kiểm tra tổng quantity không vượt stock
        $existing = $this->cartItemModel->findItem($cartId, $productId);
        $newQty   = $quantity + ($existing ? (int) $existing['quantity'] : 0);

        if ($newQty > (int) $product['stock']) {
            $this->json([
                'success' => false,
                'message' => 'Số lượng vượt quá tồn kho. Còn lại: ' . $product['stock'] . ' sản phẩm.',
            ], 422);
        }

        // Lấy giá: ưu tiên sale_price nếu có   
        $price = !empty($product['sale_price']) ? (int)$product['sale_price'] : (int)$product['price'];

        $this->cartItemModel->addItem($cartId, $productId, $quantity, $price);

        $cartCount = $this->cartModel->countItems($cartId);

        $this->json([
            'success'   => true,
            'message'   => 'Đã thêm vào giỏ hàng.',
            'cartCount' => $cartCount,
        ]);
    }

    // ----------------------------------------------------------
    // POST /cart/update — cập nhật số lượng (AJAX → JSON)
    // Dùng debounce 500ms ở frontend trước khi gọi
    // ----------------------------------------------------------
    public function updateCart(): void
    {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity  = (int) ($_POST['quantity']   ?? 0);

        if ($productId <= 0) {
            $this->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
            ], 400);
        }

        // Nếu quantity > 0 thì kiểm tra stock
        if ($quantity > 0) {
            $product = $this->productModel->findById($productId);
            if ($product && $quantity > (int) $product['stock']) {
                $this->json([
                    'success' => false,
                    'message' => 'Số lượng vượt quá tồn kho. Còn lại: ' . $product['stock'],
                ], 422);
            }
        }

        $userId    = $this->getUserId();
        $sessionId = session_id();
        $cart      = $this->cartModel->getOrCreateCart($userId, $sessionId);
        $cartId    = (int) $cart['id'];

        // quantity = 0 → updateQuantity tự xoá item
        $success = $this->cartItemModel->updateQuantity($cartId, $productId, $quantity);
        if (!$success) {
            $this->json([
                'success' => false,
                'message' => 'Không tìm thấy sản phẩm trong giỏ.'
            ], 404);
        }
        // Tính lại tổng tiền để trả về frontend cập nhật UI
        $totals    = $this->calcTotals($cartId);
        $cartCount = $this->cartModel->countItems($cartId);

        $this->json([
            'success'     => true,
            'cartCount'   => $cartCount,
            'subtotal'    => $totals['subtotal'],
            'shippingFee' => $totals['shippingFee'],
            'discount'    => $totals['discount'],
            'total'       => $totals['total'],
            // Format sẵn để JS hiển thị không cần xử lý
            'subtotalFmt'    => number_format($totals['subtotal'],    0, ',', '.') . ' ₫',
            'shippingFeeFmt' => number_format($totals['shippingFee'], 0, ',', '.') . ' ₫',
            'discountFmt'    => number_format($totals['discount'],    0, ',', '.') . ' ₫',
            'totalFmt'       => number_format($totals['total'],       0, ',', '.') . ' ₫',
        ]);
    }

    // ----------------------------------------------------------
    // POST /cart/remove — xoá 1 sản phẩm (AJAX → JSON)
    // ----------------------------------------------------------
    public function removeFromCart(): void
    {
        $productId = (int) ($_POST['product_id'] ?? 0);

        if ($productId <= 0) {
            $this->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
            ], 400);
        }

        $userId    = $this->getUserId();
        $sessionId = session_id();
        $cart      = $this->cartModel->getOrCreateCart($userId, $sessionId);
        $cartId    = (int) $cart['id'];

        $success = $this->cartItemModel->removeItem($cartId, $productId);

        if (!$success) {
            $this->json([
                'success' => false,
                'message' => 'Không tìm thấy sản phẩm trong giỏ.'
            ], 404);
        }

        $totals    = $this->calcTotals($cartId);
        $cartCount = $this->cartModel->countItems($cartId);

        $this->json([
            'success'        => true,
            'message'        => 'Đã xoá sản phẩm khỏi giỏ hàng.',
            'cartCount'      => $cartCount,
            'subtotal'       => $totals['subtotal'],
            'shippingFee'    => $totals['shippingFee'],
            'discount'       => $totals['discount'],
            'total'          => $totals['total'],
            'subtotalFmt'    => number_format($totals['subtotal'],    0, ',', '.') . ' ₫',
            'shippingFeeFmt' => number_format($totals['shippingFee'], 0, ',', '.') . ' ₫',
            'discountFmt'    => number_format($totals['discount'],    0, ',', '.') . ' ₫',
            'totalFmt'       => number_format($totals['total'],       0, ',', '.') . ' ₫',
        ]);
    }

    // ----------------------------------------------------------
    // POST /cart/clear — xoá toàn bộ giỏ (redirect)
    // ----------------------------------------------------------
    public function clearCart(): void
    {
        $userId    = $this->getUserId();
        $sessionId = session_id();
        $cart      = $this->cartModel->getOrCreateCart($userId, $sessionId);

        $this->cartModel->clearCart((int) $cart['id']);

        // Xoá coupon trong session
        unset(
            $_SESSION['coupon_code'],
            $_SESSION['coupon_id'],
            $_SESSION['coupon_discount']
        );

        setFlash('success', 'Đã xoá toàn bộ giỏ hàng.');
        redirect('/cart');
    }

    // ----------------------------------------------------------
    // POST /cart/coupon — áp mã giảm giá (AJAX → JSON)
    // ----------------------------------------------------------
    public function applyCoupon(): void
    {
        $code = strtoupper(trim($_POST['coupon_code'] ?? ''));

        if ($code === '') {
            $this->json([
                'success' => false,
                'message' => 'Vui lòng nhập mã giảm giá.',
            ], 400);
        }

        $userId    = $this->getUserId();
        $sessionId = session_id();
        $cart      = $this->cartModel->getOrCreateCart($userId, $sessionId);
        $cartId    = (int) $cart['id'];

        $subtotal = (float) $this->cartModel->getCartSubtotal($cartId);

        if ($subtotal <= 0) {
            $this->json([
                'success' => false,
                'message' => 'Giỏ hàng trống, không thể áp mã giảm giá.',
            ], 422);
        }

        $result = $this->couponModel->validateCoupon(
            $code,
            $subtotal,
            $userId ?? 0
        );

        if (!$result['valid']) {
            $this->json([
                'success' => false,
                'message' => $result['message'],
            ]);
        }

        // Lưu coupon vào session để dùng lại ở checkout
        $_SESSION['coupon_code']     = $result['coupon']['code'];
        $_SESSION['coupon_id']       = (int) $result['coupon']['id'];
        $_SESSION['coupon_discount'] = (int) $result['discount'];

        // Tính lại tổng tiền với discount mới
        $shippingFee = (int) $subtotal >= 500000 ? 0 : 30000;
        $discount    = (int) $result['discount'];
        $total       = max(0, (int) $subtotal + $shippingFee - $discount);

        $this->json([
            'success'        => true,
            'message'        => $result['message'],
            'discount'       => $discount,
            'shippingFee'    => $shippingFee,
            'total'          => $total,
            'discountFmt'    => number_format($discount,    0, ',', '.') . ' ₫',
            'shippingFeeFmt' => number_format($shippingFee, 0, ',', '.') . ' ₫',
            'totalFmt'       => number_format($total,       0, ',', '.') . ' ₫',
        ]);
    }
// ----------------------------------------------------------
    // GET /checkout — hiển thị trang thanh toán
    // ----------------------------------------------------------
   // ----------------------------------------------------------
    // GET /checkout — hiển thị trang thanh toán
    // ----------------------------------------------------------
    public function showCheckout(): void
    {
        $userId    = $this->getUserId();
        $sessionId = session_id();

        // 1. Lấy thông tin giỏ hàng hiện tại
        $cart   = $this->cartModel->getOrCreateCart($userId, $sessionId);
        $cartId = (int) $cart['id'];
        
        // ĐÃ SỬA: Đổi $items thành $cartItems cho khớp với giao diện
        $cartItems  = $this->cartItemModel->getByCartId($cartId);

        // 2. Chặn người dùng nếu giỏ hàng trống mà cứ cố tình bấm vào thanh toán
        if (empty($cartItems)) {
            setFlash('error', 'Giỏ hàng của bạn đang trống, không thể thanh toán.');
            redirect('/cart');
            exit;
        }   

        // 3. Tính toán lại toàn bộ tiền bạc, thuế phí, mã giảm giá
        $totals      = $this->calcTotals($cartId);
        $subtotal    = $totals['subtotal'];
        $shippingFee = $totals['shippingFee'];
        $discount    = $totals['discount'];
        
        // ĐÃ SỬA: Đổi $total thành $grandTotal cho khớp với giao diện
        $grandTotal  = $totals['total'];
        
        $couponCode  = $_SESSION['coupon_code'] ?? '';
        $pageTitle   = 'Thanh toán đơn hàng';

        // 4. Gọi file giao diện (HTML) của trang thanh toán ra hiển thị
        require_once __DIR__ . '/../views/user/checkout.php';
    }
    // ----------------------------------------------------------
    // POST /checkout — xử lý khi bấm nút Xác nhận đặt hàng
    // ----------------------------------------------------------
    public function processCheckout(): void
    {
        // 1. Nhận dữ liệu từ form gửi lên
        $addressId = $_POST['address_id'] ?? null;
        $newName   = trim($_POST['new_name'] ?? '');
        $newPhone  = trim($_POST['new_phone'] ?? '');
        $newAddr   = trim($_POST['new_address'] ?? '');
        $note      = trim($_POST['note'] ?? '');
        $payment   = $_POST['payment_method'] ?? 'COD';

        $userId    = $this->getUserId();
        $sessionId = session_id();
        $cart      = $this->cartModel->getOrCreateCart($userId, $sessionId);
        $cartId    = (int) $cart['id'];

        // --- GÓC LƯU Ý CHO NHÓM ---
        // Tại đây, bạn sẽ gọi OrderModel để insert dữ liệu vào bảng `orders` và `order_items`
        // Ví dụ: $this->orderModel->createOrder($userId, $cartItems, $totals, $address...);
        // ---------------------------

        // 2. Sau khi lưu DB thành công -> Xóa giỏ hàng
        $this->cartModel->clearCart($cartId);

        // 3. Xóa mã giảm giá đang lưu trong phiên làm việc
        unset(
            $_SESSION['coupon_code'],
            $_SESSION['coupon_id'],
            $_SESSION['coupon_discount']
        );

        // 4. Chuyển hướng người dùng sang trang Đặt hàng thành công
        // (Trong index.php của bạn đã có route 'order-complete')
        redirect('/order-complete');
        exit;
    }
    // ----------------------------------------------------------
    // GET /cart/count — đếm badge navbar (AJAX → JSON)
    // ----------------------------------------------------------
    public function getCartCount(): void
    {
        $userId    = $this->getUserId();
        $sessionId = session_id();
        $cart      = $this->cartModel->getOrCreateCart($userId, $sessionId);
        $count     = $this->cartModel->countItems((int) $cart['id']);

        $this->json(['cartCount' => $count]);
    }
}

// =========================================================
// KHỞI TẠO VÀ ĐIỀU HƯỚNG (ROUTER) DÀNH CHO GIỎ HÀNG
// =========================================================
$cartController = new CartController();

$basePath = parse_url(BASE_URL ?? '', PHP_URL_PATH) ?: '';
$uri      = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path     = trim((string) preg_replace('#^' . preg_quote($basePath, '#') . '#', '', $uri), '/');

// Điều hướng dựa trên URL hiện tại
if ($path === 'cart') {
    $cartController->showCart();
} elseif ($path === 'cart/add') {
    $cartController->addToCart();
} elseif ($path === 'cart/update') {
    $cartController->updateCart();
} elseif ($path === 'cart/remove') {
    $cartController->removeFromCart();
} elseif ($path === 'cart/clear') {
    $cartController->clearCart();
} elseif ($path === 'cart/coupon') {
    $cartController->applyCoupon();
} elseif ($path === 'cart/count') {
    $cartController->getCartCount();
}
// HẾT! Xóa bỏ hoàn toàn phần checkout ở đây