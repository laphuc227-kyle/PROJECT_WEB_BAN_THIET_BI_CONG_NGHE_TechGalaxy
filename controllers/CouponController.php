<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Coupon.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

class CouponController
{
    private Coupon $couponModel;

    public function __construct()
    {
        $this->couponModel = new Coupon();
    }

    // =========================================================
    // ADMIN ROUTES
    // =========================================================

    /**
     * GET /admin/coupons — Danh sách coupon
     */
    public function adminIndex(): void
    {
        requireAdmin();
        $page      = max(1, (int) ($_GET['page'] ?? 1));
        $paginated = $this->couponModel->adminGetAll($page);

        $pageTitle = 'Quản lý mã giảm giá';
        require_once __DIR__ . '/../views/admin/coupons/index.php';
    }

    /**
     * GET /admin/coupons/create — Form thêm coupon
     */
    public function adminCreate(): void
    {
        requireAdmin();
        $pageTitle = 'Thêm mã giảm giá';
        require_once __DIR__ . '/../views/admin/coupons/create.php';
    }

    /**
     * POST /admin/coupons — Lưu coupon mới
     */
    public function adminStore(): void
    {
        requireAdmin();

        if (!validateCSRF($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Yêu cầu không hợp lệ.');
            redirect(BASE_URL . '/admin/coupons/create');
            return;
        }

        $code      = strtoupper(sanitize($_POST['code'] ?? ''));
        $type      = in_array($_POST['type'] ?? '', ['percent', 'fixed']) ? $_POST['type'] : 'fixed';
        $value     = (float) ($_POST['value'] ?? 0);
        $minOrder  = (float) ($_POST['min_order'] ?? 0);
        $maxUses   = max(1, (int) ($_POST['max_uses'] ?? 1));
        $startDate = sanitize($_POST['start_date'] ?? '');
        $endDate   = sanitize($_POST['end_date'] ?? '');

        // Validate
        $errors = [];
        if (empty($code))  $errors[] = 'Mã giảm giá không được để trống.';
        if ($value <= 0)   $errors[] = 'Giá trị giảm phải lớn hơn 0.';
        if ($type === 'percent' && $value > 100) $errors[] = 'Phần trăm giảm không thể > 100%.';
        if (empty($startDate) || empty($endDate)) $errors[] = 'Vui lòng nhập ngày bắt đầu và kết thúc.';
        if ($startDate >= $endDate) $errors[] = 'Ngày kết thúc phải sau ngày bắt đầu.';

        // Kiểm tra trùng code
        if ($this->couponModel->findByCode($code)) {
            $errors[] = "Mã '{$code}' đã tồn tại.";
        }

        if (!empty($errors)) {
            setFlash('error', implode('<br>', $errors));
            redirect(BASE_URL . '/admin/coupons/create');
            return;
        }

        $id = $this->couponModel->insert([
            'code'       => $code,
            'type'       => $type,
            'value'      => $value,
            'min_order'  => $minOrder,
            'max_uses'   => $maxUses,
            'used_count' => 0,
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'status'     => 'active',
        ]);

        if ($id) {
            setFlash('success', "Mã giảm giá '{$code}' đã được tạo thành công.");
        } else {
            setFlash('error', 'Có lỗi khi tạo mã giảm giá.');
        }
        redirect(BASE_URL . '/admin/coupons');
    }

    /**
     * POST /admin/coupons/{id}/delete — Xoá coupon
     */
    public function adminDelete(int $id): void
    {
        requireAdmin();

        if (!validateCSRF($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Yêu cầu không hợp lệ.');
            redirect(BASE_URL . '/admin/coupons');
            return;
        }

        $coupon = $this->couponModel->findById($id);
        if (!$coupon) {
            setFlash('error', 'Mã giảm giá không tồn tại.');
            redirect(BASE_URL . '/admin/coupons');
            return;
        }

        $this->couponModel->delete($id);
        setFlash('success', "Đã xoá mã '{$coupon['code']}'.");
        redirect(BASE_URL . '/admin/coupons');
    }

    // =========================================================
    // USER AJAX ROUTE (dùng bởi Sơn — CartController)
    // =========================================================

    /**
     * POST /cart/coupon — Validate coupon qua AJAX
     * Trả về JSON: { valid, discount, message }
     */
    public function validate(): void
    {
        header('Content-Type: application/json');

        // Kiểm tra CSRF qua header hoặc body
        $csrfToken = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!validateCSRF($csrfToken)) {
            echo json_encode(['valid' => false, 'discount' => 0, 'message' => 'Yêu cầu không hợp lệ.']);
            return;
        }

        if (!isLoggedIn()) {
            echo json_encode(['valid' => false, 'discount' => 0, 'message' => 'Bạn cần đăng nhập để dùng mã giảm giá.']);
            return;
        }

        $code       = trim($_POST['code'] ?? '');
        $orderTotal = (float) ($_POST['order_total'] ?? 0);
        $userId     = (int) $_SESSION['user_id'];

        if (empty($code)) {
            echo json_encode(['valid' => false, 'discount' => 0, 'message' => 'Vui lòng nhập mã giảm giá.']);
            return;
        }

        $result = $this->couponModel->validateCoupon($code, $orderTotal, $userId);

        // Lưu coupon vào session nếu hợp lệ (CartController sẽ dùng)
        if ($result['valid']) {
            $_SESSION['applied_coupon'] = [
                'id'       => $result['coupon']['id'],
                'code'     => $result['coupon']['code'],
                'discount' => $result['discount'],
            ];
        } else {
            unset($_SESSION['applied_coupon']);
        }

        echo json_encode([
            'valid'    => $result['valid'],
            'discount' => $result['discount'],
            'message'  => $result['message'],
        ]);
    }
}