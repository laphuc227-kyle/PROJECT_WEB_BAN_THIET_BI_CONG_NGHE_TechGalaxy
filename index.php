<?php
/**
 * Front controller — TechGalaxy
 * Trỏ DocumentRoot hoặc truy cập: http://localhost:8012/techgalaxy/
 */

// Bắt buộc khởi động Session ở đây để lưu phiên đăng nhập Admin
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

$basePath = parse_url(BASE_URL, PHP_URL_PATH) ?: '';
$uri      = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Xóa bỏ dấu '/' thừa thãi để khớp với mảng Routes bên dưới
$path = trim((string) preg_replace('#^' . preg_quote($basePath, '#') . '#', '', $uri), '/');

// Danh sách các đường dẫn CHUẨN xác nhất
$routes = [
    // --- Trang chủ ---
    ''                      => __DIR__ . '/views/user/index.php',
    'index'                 => __DIR__ . '/views/user/index.php',
    'index.php'             => __DIR__ . '/views/user/index.php',

    // --- Xác thực ---
    'login'                 => __DIR__ . '/controllers/AuthController.php',
    'register'              => __DIR__ . '/controllers/AuthController.php',
    'logout'                => __DIR__ . '/controllers/AuthController.php',
    'forgot-password'       => __DIR__ . '/controllers/AuthController.php',
    'reset-password'        => __DIR__ . '/controllers/AuthController.php',

    // --- Shop & Sản phẩm ---
    'shop'                  => __DIR__ . '/views/user/shop.php',

    // --- Giỏ hàng & Thanh toán ---
    'cart'                  => __DIR__ . '/controllers/CartController.php',
    'checkout'              => __DIR__ . '/controllers/CartController.php',
    'order-complete'        => __DIR__ . '/views/user/order_complete.php',

    // --- Tài khoản người dùng ---
    'my-account'            => __DIR__ . '/views/user/my_account.php',
    'my-orders'             => __DIR__ . '/views/user/my_orders.php',
    'my-address'            => __DIR__ . '/views/user/my_address.php',
    'wishlist'              => __DIR__ . '/controllers/WishlistController.php',

    // --- Liên hệ ---
    'contact'               => __DIR__ . '/controllers/ContactController.php',

    // --- Blog ---
    'blog'                  => __DIR__ . '/views/user/blog.php',
    'blog/detail'           => __DIR__ . '/views/user/blog_detail.php',

    // --- Admin ---
    'admin'                 => __DIR__ . '/controllers/DashboardController.php',
    'admin/coupons'         => __DIR__ . '/controllers/CouponController.php',
    'admin/coupons/create'  => __DIR__ . '/views/admin/coupons/create.php',
    'admin/posts'           => __DIR__ . '/controllers/PostController.php',
    'admin/posts/create'    => __DIR__ . '/views/admin/posts/create.php',
    'admin/reports'         => __DIR__ . '/controllers/ReportController.php',
    'admin/reports/export'  => __DIR__ . '/controllers/ReportController.php',
    'admin/settings'        => __DIR__ . '/controllers/SettingController.php',
    'admin/orders'          => __DIR__ . '/controllers/OrderController.php',
    'admin/products'        => __DIR__ . '/controllers/ProductController.php',
    'admin/customers'       => __DIR__ . '/controllers/CustomerController.php',
    // --- Tài khoản người dùng ---
'account'                    => __DIR__ . '/controllers/AuthController.php',
'account/addresses'          => __DIR__ . '/controllers/AuthController.php',
'account/addresses/add'      => __DIR__ . '/controllers/AuthController.php',
'account/addresses/edit'     => __DIR__ . '/controllers/AuthController.php',
'account/addresses/delete'   => __DIR__ . '/controllers/AuthController.php',
'account/addresses/default'  => __DIR__ . '/controllers/AuthController.php',
'account/change-password'    => __DIR__ . '/controllers/AuthController.php',
];

// Nếu tìm thấy đường dẫn trong mảng, gọi file giao diện tương ứng
if (isset($routes[$path])) {
    require $routes[$path];
    exit;
}

// 2. Xử lý các đường dẫn động (chứa ID phía sau) bằng strpos
// (strpos kiểm tra xem đường dẫn có BẮT ĐẦU bằng cụm từ đó không)
// --- Các route động còn thiếu ---
if (strpos($path, 'admin/orders') === 0) {
    require __DIR__ . '/controllers/OrderController.php'; exit;
}
if (strpos($path, 'admin/products') === 0) {
    require __DIR__ . '/controllers/ProductController.php'; exit;
}
if (strpos($path, 'admin/customers') === 0) {
    require __DIR__ . '/controllers/CustomerController.php'; exit;
}
if (strpos($path, 'admin/posts') === 0) {
    require __DIR__ . '/controllers/PostController.php'; exit;
}
// Xử lý các request AJAX cho wishlist
if (strpos($path, 'ajax/wishlist') === 0) {
    require __DIR__ . '/controllers/WishlistController.php';
    exit;
}

// --- Route động: Chi tiết sản phẩm (/shop/{slug} hoặc /product/{id}) ---
if (preg_match('@^shop/([a-z0-9\-]+)$@', $path) ||
    preg_match('@^product/(\d+)$@', $path)) {
    require __DIR__ . '/views/user/product_detail.php'; exit;
}

// --- Route động: Auth (reset password với token) ---
if (strpos($path, 'reset-password') === 0) {
    require __DIR__ . '/controllers/AuthController.php'; exit;
}

// --- Route động: Cart actions ---
if (strpos($path, 'cart') === 0) {
    require __DIR__ . '/controllers/CartController.php'; exit;
}

// --- Route động: Wishlist actions ---
if (strpos($path, 'wishlist') === 0) {
    require __DIR__ . '/controllers/WishlistController.php'; exit;
}
// ===== Xử lý Bình luận (Admin: Duyệt / Ẩn / Xóa) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    preg_match('@^admin/comments/(\d+)/(approve|hide|delete)$@', $path, $cm)) {

    global $pdo;

    if (!isAdmin()) {
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    $commentId = (int) $cm[1];
    $action    = $cm[2];
    $redirect  = $_POST['redirect'] ?? (BASE_URL . '/admin/posts');

    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE comments SET status = 'approved' WHERE id = ?");
        $stmt->execute([$commentId]);
        setFlash('success', 'Đã duyệt bình luận.');

    } elseif ($action === 'hide') {
        $stmt = $pdo->prepare("UPDATE comments SET status = 'pending' WHERE id = ?");
        $stmt->execute([$commentId]);
        setFlash('info', 'Đã ẩn bình luận.');

    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$commentId]);
        setFlash('success', 'Đã xóa bình luận.');
    }

    header('Location: ' . $redirect);
    exit;
}
if (strpos($path, 'account') === 0) {
    require __DIR__ . '/controllers/AuthController.php';
    exit;
}
// ===== Route động: Chi tiết bài viết Blog (/blog/{slug}) =====
if (preg_match('@^blog/([a-z0-9\-]+)$@', $path, $m) && $path !== 'blog/detail') {
    require_once __DIR__ . '/models/Post.php';
    $postModel = new Post();

    $slug = $m[1];
    $post = $postModel->findBySlug($slug);

    if (!$post) {
        http_response_code(404);
        $pageTitle = 'Không tìm thấy bài viết';
        require_once __DIR__ . '/includes/header.php';
        require_once __DIR__ . '/includes/navbar.php';
        echo '<main class="container py-5 text-center"><h1>404</h1><p class="text-muted">Bài viết không tồn tại hoặc đã bị gỡ.</p><a href="' . BASE_URL . '/blog" class="btn-primary-tg">Quay lại Blog</a></main>';
        require_once __DIR__ . '/includes/footer.php';
        exit;
    }

    global $pdo;

    // ===== XỬ LÝ GỬI BÌNH LUẬN MỚI (POST) =====
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_content'])) {
        if (!isLoggedIn()) {
            header('Location: ' . BASE_URL . '/login?redirect=' . urlencode(BASE_URL . '/blog/' . $slug));
            exit;
        }

        $content = trim($_POST['comment_content']);

        if ($content === '') {
            $_SESSION['flash_message'] = 'Nội dung bình luận không được để trống.';
            $_SESSION['flash_type']    = 'danger';
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO comments (post_id, user_id, content, status, created_at)
                 VALUES (?, ?, ?, 'pending', NOW())"
            );
            $stmt->execute([$post['id'], $_SESSION['user_id'], $content]);

            $_SESSION['flash_message'] = 'Bình luận của bạn đã được gửi, đang chờ duyệt.';
            $_SESSION['flash_type']    = 'success';
        }

        header('Location: ' . BASE_URL . '/blog/' . $slug . '#comments');
        exit;
    }

    // ===== LẤY BÌNH LUẬN ĐÃ DUYỆT ĐỂ HIỂN THỊ =====
    $stmt = $pdo->prepare(
        "SELECT c.*, u.name AS user_name, u.avatar AS user_avatar
         FROM comments c
         LEFT JOIN users u ON c.user_id = u.id
         WHERE c.post_id = ? AND c.status = 'approved'
         ORDER BY c.created_at DESC"
    );
    $stmt->execute([$post['id']]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $related     = $postModel->getRelated((int) $post['id'], 3);
    $readingTime = $postModel->readingTime($post['content']);
    $pageTitle   = $post['title'] . ' — TechGalaxy';
    $metaDesc    = $postModel->makeExcerpt($post['content'], 160);

    require __DIR__ . '/views/user/blog_detail.php';
    exit;
}

// Nếu không tìm thấy, hiển thị trang lỗi 404
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
<?php require_once __DIR__ . '/includes/footer.php'; ?>
