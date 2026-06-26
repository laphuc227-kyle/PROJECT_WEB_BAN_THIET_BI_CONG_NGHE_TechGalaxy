<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Setting.php';

$settingModel = new Setting();

/* ==========================================================
 * 1. XỬ LÝ LƯU CÀI ĐẶT (Khi bấm nút "Lưu cài đặt" - POST)
 * ========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Thu thập dữ liệu text từ form
    $data = [
        'site_name'           => trim($_POST['site_name'] ?? ''),
        'contact_email'       => trim($_POST['contact_email'] ?? ''),
        'contact_phone'       => trim($_POST['contact_phone'] ?? ''),
        'address'             => trim($_POST['address'] ?? ''),
        'store_description'   => trim($_POST['store_description'] ?? ''),
        // Sửa lỗi chính tả từ thresold thành threshold
        'free_ship_threshold' => trim($_POST['free_ship_thresold'] ?? '500000'), 
        'free_shipping_from'  => trim($_POST['free_shipping_from'] ?? '0'),
        // Checkbox: Nếu được tích thì bằng 1, không thì bằng 0
        'send_order_email'    => isset($_POST['send_order_email']) ? '1' : '0'
    ];

    // Xử lý upload Logo nếu người dùng có chọn file mới
    if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../public/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $ext = pathinfo($_FILES['logo_file']['name'], PATHINFO_EXTENSION);
        $logoName = 'logo_' . time() . '.' . $ext;
        
        if (move_uploaded_file($_FILES['logo_file']['tmp_name'], $uploadDir . $logoName)) {
            $data['store_logo'] = $logoName; // Chỉ cập nhật logo nếu upload thành công
        }
    }

    // Lưu toàn bộ mảng dữ liệu vào Database bằng hàm setMany()
    $settingModel->setMany($data);

    // Quay lại trang cài đặt
    header('Location: ' . BASE_URL . '/admin/settings');
    exit;
}

/* ==========================================================
 * 2. LẤY DỮ LIỆU HIỂN THỊ LÊN FORM (Truy cập bình thường - GET)
 * ========================================================== */
// Kéo toàn bộ setting từ CSDL ra dạng mảng [key => value]
$settings = $settingModel->getAll();

// Gọi View để hiển thị
require_once __DIR__ . '/../views/admin/settings/index_setting.php';