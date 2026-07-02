<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Coupon.php';

$couponModel = new Coupon();

/* ==========================================================
 * 1. XỬ LÝ LƯU MÃ GIẢM GIÁ MỚI (Khi bấm nút Thêm - POST)
 * ========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nhận dữ liệu từ form
    $code       = strtoupper(trim($_POST['code'] ?? ''));
    $type       = $_POST['type'] ?? 'fixed';
    $value      = (float) ($_POST['value'] ?? 0);
    $min_order  = (float) ($_POST['min_order'] ?? 0);
    $max_uses   = (int) ($_POST['max_uses'] ?? 1);
    $start_date = $_POST['start_date'] ?? date('Y-m-d');
    $end_date   = $_POST['end_date'] ?? date('Y-m-d');
    // ĐÃ SỬA: form create.php không có field 'status' -> mã mới luôn active (1),
    // cột status là tinyint(1) nên không được gán chuỗi 'active'
    $status     = 1;

    // Đẩy vào Database bằng hàm insert có sẵn của BaseModel
    $couponModel->insert([
        'code'       => $code,
        'type'       => $type,
        'value'      => $value,
        'min_order'  => $min_order,
        'max_uses'   => $max_uses,
        'used_count' => 0, // Mã mới thì số lượt đã dùng luôn bằng 0
        'start_date' => $start_date,
        'end_date'   => $end_date,
        'status'     => $status
    ]);

    // Quay về trang danh sách mã giảm giá
    header('Location: ' . BASE_URL . '/admin/coupons');
    exit;
}

/* ==========================================================
 * 2. XỬ LÝ HIỂN THỊ DANH SÁCH (Truy cập bình thường - GET)
 * ========================================================== */
$page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 10; 

// Lấy danh sách mã giảm giá từ Database
$items = $couponModel->adminGetAll($page, $perPage);

// Tính toán phân trang
$total      = $couponModel->count();
$totalPages = (int) ceil($total / $perPage);

$paginated = [
    'items'       => $items,
    'total'       => $total,
    'currentPage' => $page,
    'totalPages'  => max(1, $totalPages)
];

// Gọi View để hiển thị
require_once __DIR__ . '/../views/admin/coupons/index_coupon.php';