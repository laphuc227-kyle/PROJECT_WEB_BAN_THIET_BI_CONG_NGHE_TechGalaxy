<?php
// File: config/database.example.php
// Hướng dẫn cho team: Copy file này, đổi tên thành database.php và điền thông tin máy local của bạn.

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'techgalaxy'); // Điền tên database local của bạn
define('DB_USER', 'root');       // Username MySQL local
define('DB_PASS', '');           // Password MySQL local

// Khởi tạo kết nối PDO toàn cục — bắt buộc cho BaseModel (global $pdo)
$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // Không lộ chi tiết kết nối (host/user/pass) ra ngoài production
    error_log('[DB Connection Error] ' . $e->getMessage());
    http_response_code(500);
    die('Không thể kết nối cơ sở dữ liệu. Vui lòng thử lại sau.');
}