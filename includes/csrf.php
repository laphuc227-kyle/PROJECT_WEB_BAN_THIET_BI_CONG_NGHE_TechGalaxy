<?php
// File: includes/csrf.php

/**
 * Hàm xác thực CSRF Token
 */
function verifyCsrfToken($token) {
    if (empty($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        http_response_code(403);
        die('Lỗi bảo mật (CSRF Token không hợp lệ hoặc đã hết hạn). Vui lòng tải lại trang.');
    }
    return true;
}

/**
 * Middleware tự động kiểm tra CSRF cho mọi Request là POST.
 * (Chỉ cần gọi hàm này ở file index.php gốc là toàn hệ thống được bảo vệ)
 */
function csrfMiddleware() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Ưu tiên lấy từ form input hidden, nếu không có thì lấy từ HTTP Header (dành cho AJAX)
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        verifyCsrfToken($token);
    }
}