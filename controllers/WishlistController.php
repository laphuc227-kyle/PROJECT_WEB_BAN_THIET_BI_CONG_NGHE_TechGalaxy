<?php

namespace Controllers;

use Models\Wishlist;

/**
 * WishlistController
 *
 * Xử lý toàn bộ logic liên quan đến Wishlist:
 *  - Hiển thị trang danh sách yêu thích (user)
 *  - Các endpoint AJAX: toggle, add, remove
 *  - Xóa trực tiếp qua URL (non-AJAX, dùng cho trang wishlist)
 *
 * Tất cả action đều yêu cầu user đã đăng nhập.
 */
class WishlistController
{
    private Wishlist $wishlistModel;

    public function __construct()
    {
        $this->wishlistModel = new Wishlist();
    }

    // =========================================================================
    //  PAGE ACTION
    // =========================================================================

    /**
     * Hiển thị trang Wishlist của người dùng đang đăng nhập
     * URL: GET /wishlist
     *
     * Luồng:
     * 1. Kiểm tra đăng nhập → redirect /login nếu chưa
     * 2. Lấy danh sách wishlist kèm thông tin sản phẩm
     * 3. Đọc + xóa flash message (nếu có từ redirect trước)
     * 4. Nạp view
     */
    public function index(): void
    {
        $this->requireLogin();

        $userId = (int) $_SESSION['user_id'];
        $items  = $this->wishlistModel->getWishlistByUser($userId);

        // Lấy flash message (nếu có) rồi xóa khỏi session
        $flashMessage = $_SESSION['flash_message'] ?? null;
        $flashType    = $_SESSION['flash_type']    ?? 'success';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);

        require_once __DIR__ . '/../views/user/wishlist.php';
    }

    // =========================================================================
    //  AJAX ACTIONS
    // =========================================================================

    /**
     * AJAX: Toggle Wishlist (thêm nếu chưa có, xóa nếu đã có)
     * URL:    POST /ajax/wishlist/toggle
     * Body:   product_id=int
     * Return: JSON { success, message, wishlisted, wishlist_id? }
     *
     * Đây là endpoint chính được gọi từ các nút ❤ trên shop.php và product_detail.php
     */
    public function ajaxToggle(): void
    {
        $this->requireLoginJson();
        $this->requireMethod('POST');

        $userId    = (int) $_SESSION['user_id'];
        $productId = (int) ($_POST['product_id'] ?? 0);

        if ($productId <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'ID sản phẩm không hợp lệ.']);
            return;
        }

        // Kiểm tra xem đã có trong wishlist chưa
        $existingId = $this->wishlistModel->checkWishlistExists($userId, $productId);

        if ($existingId !== false) {
            // Đã có → XÓA
            $result = $this->wishlistModel->removeFromWishlist($existingId);
            $this->jsonResponse([
                'success'    => $result['success'],
                'message'    => $result['message'],
                'wishlisted' => false,
            ]);
        } else {
            // Chưa có → THÊM
            $result = $this->wishlistModel->addToWishlist($userId, $productId);
            $this->jsonResponse([
                'success'     => $result['success'],
                'message'     => $result['message'],
                'wishlisted'  => $result['success'],
                'wishlist_id' => $result['id'] ?? null,
            ]);
        }
    }

    /**
     * AJAX: Thêm sản phẩm vào Wishlist
     * URL:    POST /ajax/wishlist/add
     * Body:   product_id=int
     * Return: JSON { success, message, wishlisted, wishlist_id? }
     */
    public function ajaxAdd(): void
    {
        $this->requireLoginJson();
        $this->requireMethod('POST');

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
     * AJAX: Xóa sản phẩm khỏi Wishlist bằng product_id
     * URL:    POST /ajax/wishlist/remove
     * Body:   product_id=int
     * Return: JSON { success, message, wishlisted }
     *
     * Được gọi từ trang wishlist.php khi nhấn nút "Xóa"
     */
    public function ajaxRemove(): void
    {
        $this->requireLoginJson();
        $this->requireMethod('POST');

        $userId    = (int) $_SESSION['user_id'];
        $productId = (int) ($_POST['product_id'] ?? 0);

        if ($productId <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'ID sản phẩm không hợp lệ.']);
            return;
        }

        // Xóa theo user_id + product_id (an toàn, chống xóa nhầm của user khác)
        $result = $this->wishlistModel->removeByUserAndProduct($userId, $productId);

        $this->jsonResponse([
            'success'    => $result['success'],
            'message'    => $result['message'],
            'wishlisted' => false,
        ]);
    }

    /**
     * AJAX: Xóa theo wishlist_id (dùng khi đã biết chính xác ID bản ghi)
     * URL:    POST /ajax/wishlist/remove-by-id
     * Body:   wishlist_id=int
     * Return: JSON { success, message }
     *
     * Lưu ý bảo mật: kiểm tra wishlist_id thuộc về user hiện tại
     * trước khi xóa để tránh Insecure Direct Object Reference (IDOR)
     */
    public function ajaxRemoveById(): void
    {
        $this->requireLoginJson();
        $this->requireMethod('POST');

        $userId     = (int) $_SESSION['user_id'];
        $wishlistId = (int) ($_POST['wishlist_id'] ?? 0);

        if ($wishlistId <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'ID không hợp lệ.']);
            return;
        }

        // Bảo vệ IDOR: xác minh wishlist_id thuộc về user hiện tại
        $items = $this->wishlistModel->getWishlistByUser($userId);
        $owned = false;
        foreach ($items as $item) {
            if ((int) $item['wishlist_id'] === $wishlistId) {
                $owned = true;
                break;
            }
        }

        if (!$owned) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Bạn không có quyền xóa mục này.',
            ], 403);
            return;
        }

        $result = $this->wishlistModel->removeFromWishlist($wishlistId);
        $this->jsonResponse($result);
    }

    /**
     * Xóa và redirect (non-AJAX) — dùng cho trường hợp JS bị tắt
     * URL: GET /wishlist/remove/{wishlist_id}
     */
    public function removeAndRedirect(int $wishlistId): void
    {
        $this->requireLogin();

        $userId = (int) $_SESSION['user_id'];

        // Kiểm tra quyền sở hữu
        $items = $this->wishlistModel->getWishlistByUser($userId);
        $owned = false;
        foreach ($items as $item) {
            if ((int) $item['wishlist_id'] === $wishlistId) {
                $owned = true;
                break;
            }
        }

        if ($owned) {
            $this->wishlistModel->removeFromWishlist($wishlistId);
            $_SESSION['flash_message'] = 'Đã xóa sản phẩm khỏi danh sách yêu thích.';
            $_SESSION['flash_type']    = 'success';
        } else {
            $_SESSION['flash_message'] = 'Không tìm thấy mục cần xóa.';
            $_SESSION['flash_type']    = 'danger';
        }

        header('Location: /wishlist');
        exit;
    }

    /**
     * API: Lấy tổng số wishlist item của user (dùng để cập nhật badge trên navbar)
     * URL:    GET /ajax/wishlist/count
     * Return: JSON { count: int }
     */
    public function ajaxCount(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->jsonResponse(['count' => 0]);
            return;
        }

        $count = $this->wishlistModel->countByUser((int) $_SESSION['user_id']);
        $this->jsonResponse(['count' => $count]);
    }

    // =========================================================================
    //  PRIVATE HELPERS
    // =========================================================================

    /**
     * Kiểm tra đăng nhập — redirect sang /login nếu chưa
     */
    private function requireLogin(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
    }

    /**
     * Kiểm tra đăng nhập cho AJAX — trả JSON 401 thay vì redirect
     */
    private function requireLoginJson(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->jsonResponse([
                'success'  => false,
                'message'  => 'Vui lòng đăng nhập để thực hiện.',
                'redirect' => '/login',
            ], 401);
            exit;
        }
    }

    /**
     * Kiểm tra HTTP method — trả JSON 405 nếu sai method
     */
    private function requireMethod(string $method): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
            $this->jsonResponse(['success' => false, 'message' => 'Method không hợp lệ.'], 405);
            exit;
        }
    }

    /**
     * Gửi JSON response và kết thúc script
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}