<?php
/**
 * Front controller — TechGalaxy
 * Trỏ DocumentRoot hoặc truy cập: http://localhost/techgalaxy/
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/helpers.php';

$basePath = parse_url(BASE_URL, PHP_URL_PATH) ?: '';
$uri      = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path     = '/' . trim((string) preg_replace('#^' . preg_quote($basePath, '#') . '#', '', $uri), '/');

$routes = [
    ''       => __DIR__ . '/views/user/index.php',
    'index'  => __DIR__ . '/views/user/index.php',
];

if (isset($routes[$path])) {
    require $routes[$path];
    exit;
}

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
