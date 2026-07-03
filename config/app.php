<?php
// File: config/app.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BASE_URL', 'http://localhost/techgalaxy'); // Đảm bảo URL này khớp với folder XAMPP
define('APP_NAME', 'TechGalaxy');
define('UPLOAD_PATH', __DIR__ . '/../public/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('TIMEZONE', 'Asia/Ho_Chi_Minh');

date_default_timezone_set(TIMEZONE);
// --- CẤU HÌNH PHÂN TRANG ---
// Trang blog phía User cần hiển thị 6 bài / 1 trang
define('PAGINATION_LIMIT_BLOG', 6);