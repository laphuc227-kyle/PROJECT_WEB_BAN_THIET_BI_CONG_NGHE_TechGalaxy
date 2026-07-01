<?php
/**
 * Front Controller — TechGalaxy (BẢN HỢP NHẤT GIỮA PHÚC & QUÂN)
 * Đã giải quyết Conflict — Đưa toàn bộ các route của Quân về cấu trúc Controller Object chuẩn
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/includes/helpers.php';

// Bật hiển thị lỗi phục vụ giai đoạn fix bug
ini_set('display_errors', 1);
error_reporting(E_ALL);

// =========================================================================
// 1. AUTOLOADER - TỰ ĐỘNG NẠP CLASS THEO NAMESPACE
// =========================================================================
spl_autoload_register(function ($className) {
    $className = ltrim($className, '\\');
    $parts = explode('\\', $className);
    if (isset($parts[0])) {
        $parts[0] = strtolower($parts[0]); 
    }
    $file = __DIR__ . '/' . implode('/', $parts) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// =========================================================================
// 2. XỬ LÝ ĐƯỜNG DẪN URL
// =========================================================================
$basePath = parse_url(BASE_URL, PHP_URL_PATH) ?: '';
$uri      = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path     = trim((string) preg_replace('#^' . preg_quote($basePath, '#') . '#', '', $uri), '/');

if ($path === '' || $path === 'index.php') {
    $path = 'index';
}

// =========================================================================
// 3. BẢN ĐỒ ĐỊNH TUYẾN TÍCH HỢP (PHÚC + QUÂN + KIM + NGUYÊN)
// =========================================================================
$routes = [
    // --- KHU VỰC TRANG CHỦ & CỬA HÀNG (PHÚC & NGUYÊN & THUẬN) ---
    'index'                              => 'ProductController@home', 
    'shop'                               => 'ProductController@shop',
    'product/(?P<id>\d+)'                => 'ProductController@productDetail',
    'product/(?P<slug>[a-zA-Z0-9\-]+)'   => 'ProductController@productDetail', 
    'wishlist'                           => 'ProductController@wishlistPage',
    
    // --- KHU VỰC GIỎ HÀNG & ĐƠN HÀNG (SƠN) ---
    'cart'                               => 'CartController@index',
    'checkout'                           => 'OrderController@checkout',
    'order-complete'                     => 'OrderController@complete',
    'contact'                            => 'ContactController@index',

    // --- KHU VỰC BLOG & BÀI VIẾT (QUÂN - ĐÃ CHUYỂN ĐỔI SANG MVC OBJECT) ---
    'blog'                               => 'PostController@index',
    'blog/(?P<id>\d+)'                   => 'PostController@detail',
    'blog/(?P<slug>[a-zA-Z0-9\-]+)'     => 'PostController@detail', // Hỗ trợ luôn cả link chữ chuẩn SEO cho Quân

    // --- KHU VỰC TÀI KHOẢN & XÁC THỰC (KIM) ---
    'login'                              => 'AuthController@login',
    'register'                           => 'AuthController@register',
    'forgot-password'                    => 'AuthController@forgotPassword',
    'reset-password'                     => 'AuthController@resetPassword',
    'logout'                             => 'AuthController@logout',
    'my-account'                         => 'AuthController@myAccount',
    'my-address'                         => 'AuthController@myAddress',
    'my-orders'                          => 'OrderController@myOrders',

    // --- KHU VỰC QUẢN TRỊ ADMIN PANEL (QUÂN & ANH EM) ---
    'admin'                              => 'DashboardController@index',
    'admin/login'                        => 'AuthController@adminLogin',
    
    // Admin CRUD Sản phẩm (Nguyên)
    'admin/products'                     => 'ProductController@adminIndex',
    'admin/products/create'              => 'ProductController@adminCreate',
    'admin/products/edit/(?P<id>\d+)'    => 'ProductController@adminEdit',
    'admin/products/delete/(?P<id>\d+)'  => 'ProductController@adminDelete',
    
    // Admin Danh mục & Kho hàng
    'admin/categories'                   => 'ProductController@adminCategoryIndex',
    'admin/categories/create'            => 'ProductController@adminCategoryCreate',
    'admin/categories/edit/(?P<id>\d+)'  => 'ProductController@adminCategoryEdit',
    'admin/inventory'                    => 'ProductController@adminInventory',
    
    // Admin Đơn hàng & Khách hàng
    'admin/orders'                       => 'OrderController@adminIndex',
    'admin/orders/(?P<id>\d+)'           => 'OrderController@adminDetail',
    'admin/customers'                    => 'AuthController@adminCustomerIndex',
    'admin/customers/(?P<id>\d+)'        => 'AuthController@adminCustomerDetail',
    
    // Admin Coupons & Cài đặt hệ thống
    'admin/coupons'                      => 'CouponController@adminIndex',
    'admin/coupons/create'               => 'CouponController@adminCreate',
    'admin/reports'                      => 'ReportController@adminIndex',
    'admin/settings'                     => 'SettingController@adminIndex',

    // Admin Quản lý Bài viết Blog (Quân)
    'admin/posts'                        => 'PostController@adminIndex',
    'admin/posts/create'                 => 'PostController@adminCreate',
    'admin/posts/edit/(?P<id>\d+)'       => 'PostController@adminEdit',
    'admin/posts/delete/(?P<id>\d+)'     => 'PostController@adminDelete',

    // --- KHU VỰC AJAX ENDPOINTS ---
    'ajax/wishlist/toggle'               => 'ProductController@ajaxToggleWishlist',
];

// =========================================================================
// 4. CORE ROUTER & DISPATCHER ENGINE
// =========================================================================
$routeMatched = false;

// Đánh chặn sửa lỗi gọi AJAX thiếu subfolder
if (!isset($routes[$path]) && strpos($_SERVER['REQUEST_URI'], 'ajax/') !== false) {
    $ajaxParts = explode('ajax/', $_SERVER['REQUEST_URI']);
    if (isset($ajaxParts[1])) {
        $path = 'ajax/' . trim($ajaxParts[1], '/');
    }
}

foreach ($routes as $pattern => $target) {
    $regex = '#^' . $pattern . '$#';

    if (preg_match($regex, $path, $matches)) {
        $routeMatched = true;
        $params = [];
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
                $_GET[$key] = $value; 
            }
        }

        list($controllerName, $methodName) = explode('@', $target);
        $fullClassName = '\\Controllers\\' . $controllerName;
        
        if (class_exists($fullClassName)) {
            $controller = new $fullClassName();
            
            if (method_exists($controller, $methodName)) {
                // Thực thi gọi hàm xử lý tương ứng
                call_user_func_array([$controller, $methodName], $params);
                exit;
            } else {
                die("<div style='padding:20px; background:#f8d7da; color:#721c24; font-family:sans-serif;'><h3>Lỗi 500</h3>Class <b>{$controllerName}</b> thiếu hàm <b>{$methodName}()</b>.</div>");
            }
        } else {
            die("<div style='padding:20px; background:#f8d7da; color:#721c24; font-family:sans-serif;'><h3>Lỗi 500</h3>Không tìm thấy class Controller <b>{$fullClassName}</b>. Đảm bảo Quân đã đặt đúng Namespace trong Controller mới.</div>");
        }
    }
}

// =========================================================================
// 5. HIỂN THỊ TRANG LỖI 404 CUSTOM
// =========================================================================
if (!$routeMatched) {
    http_response_code(404);
    if (file_exists(__DIR__ . '/includes/header.php')) {
        require_once __DIR__ . '/includes/header.php';
        if (file_exists(__DIR__ . '/includes/navbar.php')) require_once __DIR__ . '/includes/navbar.php';
        echo "<main class='container py-5 text-center' style='min-height:50vh;'><h1>404 Not Found</h1><p>Đường dẫn <code>/{$path}</code> không tồn tại trên hệ thống TechGalaxy.</p></main>";
        require_once __DIR__ . '/includes/footer.php';
    } else {
        echo "<h1>404 Not Found</h1>";
    }
}