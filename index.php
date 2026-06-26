<?php
/**
 * Front controller — TechGalaxy
 * Trỏ DocumentRoot hoặc truy cập: http://localhost:8012/techgalaxy/
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

$basePath = parse_url(BASE_URL, PHP_URL_PATH) ?: '';
$uri      = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path     = '/' . trim((string) preg_replace('#^' . preg_quote($basePath, '#') . '#', '', $uri), '/');

$routes = [
    ''       => __DIR__ . '/views/user/index.php',
    'index'  => __DIR__ . '/views/user/index.php',
];

// Bắt buộc khởi động Session ở đây để lưu phiên đăng nhập Admin
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';


$basePath = parse_url(BASE_URL, PHP_URL_PATH) ?: '';
$uri      = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Đã sửa logic: Xóa bỏ dấu '/' thừa thãi để khớp với mảng Routes bên dưới
$path = trim((string) preg_replace('#^' . preg_quote($basePath, '#') . '#', '', $uri), '/');

// Danh sách các đường dẫn CHUẨN xác nhất
$routes = [
    // --- Trang chủ ---
    ''                      => __DIR__ . '/views/user/index.php',
    'index'                 => __DIR__ . '/views/user/index.php',
    'index.php'             => __DIR__ . '/views/user/index.php',
    
    // --- Xác thực (Login) ---
    'login'                 => __DIR__ . '/views/auth/login.php',

    // --- Phần Blog phía Người dùng ---
    'blog'                  => __DIR__ . '/views/user/blog.php',
    'blog/detail'           => __DIR__ . '/views/user/blog_detail.php',

    // --- Khu vực Quản trị Admin Panel (TASK-06) ---
    'admin'                 => __DIR__ . '/controllers/DashboardController.php',
    'admin/coupons'         => __DIR__ . '/controllers/CouponController.php',
    'admin/coupons/create'  => __DIR__ . '/views/admin/coupons/create.php',
    'admin/posts'           => __DIR__ . '/controllers/PostController.php',
    'admin/posts/create'    => __DIR__ . '/views/admin/posts/create.php',
    'admin/reports'         => __DIR__ . '/controllers/ReportController.php',
    'admin/reports/export'  => __DIR__ . '/controllers/ReportController.php',
    'admin/settings'        => __DIR__ . '/controllers/SettingController.php',
    'admin/orders'               => __DIR__ . '/controllers/OrderController.php',
    'admin/orders/(?P<id>\d+)'   => __DIR__ . '/controllers/OrderController.php',
    'admin/orders/(?P<id>\d+)/status' => __DIR__ . '/controllers/OrderController.php',
    'admin/products' => __DIR__ . '/controllers/ProductController.php',
];

// Nếu tìm thấy đường dẫn trong mảng, gọi file giao diện tương ứng
>>>>>>> develop
if (isset($routes[$path])) {
    require $routes[$path];
    exit;
}

<<<<<<< HEAD
=======
// Nếu không tìm thấy, hiển thị trang lỗi 404
>>>>>>> develop
http_response_code(404);
$pageTitle = 'Không tìm thấy trang';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<main class="container-xl py-5 text-center">
  <h1>404</h1>
  <p class="text-muted">Trang bạn tìm không tồn tại.</p>
  <a href="<?= BASE_URL ?>/" class="btn-primary-tg">Về trang chủ</a>
</main>
<<<<<<< HEAD
<?php require_once __DIR__ . '/includes/footer.php'; ?>
=======
<?php require_once __DIR__ . '/includes/footer.php'; ?>
>>>>>>> develop
