<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Post.php';

global $pdo; 
$postModel = new Post();

// Bóc tách thư mục gốc để đường dẫn luôn chuẩn xác 100%
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = parse_url(defined('BASE_URL') ? BASE_URL : '', PHP_URL_PATH) ?: '';
$path = $uri;
if ($basePath !== '' && strpos($uri, $basePath) === 0) {
    $path = substr($uri, strlen($basePath));
    if ($path === '') $path = '/';
}

// ==========================================================
// 1. CÁC HÀNH ĐỘNG GỬI DỮ LIỆU (POST)
// ==========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1.1 XÓA BÀI VIẾT
    if (preg_match('@^/admin/posts/(\d+)/delete$@', $path, $matches)) {
        $id = (int)$matches[1];
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: ' . BASE_URL . '/admin/posts');
        exit;
    }

    // 1.2 LƯU BÀI VIẾT MỚI
    if ($path === '/admin/posts' || $path === '/admin/posts/') {
        $title   = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $status  = $_POST['status'] ?? 'draft';

        $slug = mb_strtolower($title, 'UTF-8');
        $slug = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $slug);
        $slug = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $slug);
        $slug = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $slug);
        $slug = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $slug);
        $slug = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $slug);
        $slug = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $slug);
        $slug = preg_replace("/(đ)/", 'd', $slug);
        $slug = preg_replace("/([^a-z0-9\-]+)/", '-', $slug);
        $slug = trim(preg_replace("/(-+)/", '-', $slug), '-');

        $imageName = '';
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../public/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
            $imageName = 'blog_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadDir . $imageName);
        }

        $postModel->insert([
            'title'      => $title,
            'slug'       => $slug,
            'content'    => $content,
            'image'      => $imageName,
            'status'     => $status,
            'author_id'  => $_SESSION['user_id'] ?? 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        header('Location: ' . BASE_URL . '/admin/posts');
        exit;
    }

    // 1.3 CẬP NHẬT (SỬA) BÀI VIẾT VÀ TRẠNG THÁI
if (preg_match('@^/admin/posts/(\d+)/?$@', $path, $matches)) {
    $id = (int)$matches[1];
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    
    // Ép nhận giá trị status từ form, nếu không có mới fallback về nháp
    $status  = isset($_POST['status']) ? $_POST['status'] : 'draft';

    $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ?, status = ? WHERE id = ?");
    $stmt->execute([$title, $content, $status, $id]);

    header('Location: ' . BASE_URL . '/admin/posts');
    exit;
}
}

// ==========================================================
// 2. CÁC HÀNH ĐỘNG HIỂN THỊ GIAO DIỆN (GET)
// ==========================================================

if (strpos($path, '/admin/posts/create') !== false) {
    require_once __DIR__ . '/../views/admin/posts/create.php';
    exit;
}

if (preg_match('@^/admin/posts/(\d+)/edit$@', $path, $matches)) {
    $id = (int)$matches[1];
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        header('Location: ' . BASE_URL . '/admin/posts');
        exit;
    }
    require_once __DIR__ . '/../views/admin/posts/edit.php';
    exit;
}

if (preg_match('@^/admin/posts/(\d+)$@', $path, $matches)) {
    $id = (int)$matches[1];
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $comments = []; 
    require_once __DIR__ . '/../views/admin/posts/detail.php';
    exit;
}

$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$paginated = $postModel->adminGetAll($page, 10, $search);

require_once __DIR__ . '/../views/admin/posts/index_post.php';