<?php
// 1. Bật hiển thị lỗi để dễ dàng lập trình (Debug)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Nhúng file Autoload quyền lực của Composer vừa tạo thành công lúc nãy
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

echo "<div style='text-align: center; margin-top: 60px; font-family: Arial, sans-serif;'>";
echo "<h1 style='color: #2ecc71; font-size: 36px;'>🚀 Chuẩn chỉnh rồi em ơi!</h1>";
echo "<h2 style='color: #2c3e50;'>Dự án TechGalaxy đã chính thức thức tỉnh!</h2>";
echo "<p style='color: #7f8c8d; font-size: 18px; margin-top: 20px;'>Cấu trúc MVC thuần và Composer Autoload đã thông suốt.</p>";
echo "<p style='color: #34495e; font-weight: bold;'>Bây giờ em có thể tự tin bắt tay vào code giao diện và logic rồi đó! 💪</p>";
echo "</div>";