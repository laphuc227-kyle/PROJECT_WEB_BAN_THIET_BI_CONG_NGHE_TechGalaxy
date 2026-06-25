<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../models/Dashboard.php';

// 1. Khởi tạo Model
$dashboardModel = new Dashboard();

// 2. Kéo dữ liệu thật 100% từ Database
$stats        = $dashboardModel->getStats();
$latestOrders = $dashboardModel->getLatestOrders(5);
$revenueChart = $dashboardModel->getRevenueChart();

// 3. Truyền toàn bộ biến sang View để hiển thị
require_once __DIR__ . '/../views/admin/dashboard.php';