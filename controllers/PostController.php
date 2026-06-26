<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Post.php';

$postModel = new Post();

/* ==========================================================
 * 1. XỬ LÝ LƯU BÀI VIẾT (Khi form bấm "Đăng bài" - POST)
 * ========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nhận dữ liệu từ form gửi lên
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $status  = $_POST['status'] ?? 'draft';

    // Tạo Slug (đường dẫn không dấu) từ Tiêu đề
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

    // Xử lý upload ảnh bìa (lưu vào thư mục public/uploads/)
    $imageName = '';
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../public/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Tự động tạo thư mục nếu chưa có
        }
        
        $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $imageName = 'blog_' . time() . '.' . $ext; // Đổi tên ảnh để không bị trùng
        move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadDir . $imageName);
    }

    // Gọi hàm insert() có sẵn trong BaseModel để lưu vào CSDL
    $postModel->insert([
        'title'      => $title,
        'slug'       => $slug,
        'content'    => $content,
        'image'      => $imageName,
        'status'     => $status,
        'author_id'  => $_SESSION['user_id'] ?? 1, // Tạm fix ID tác giả là 1 nếu admin chưa đăng nhập
        'created_at' => date('Y-m-d H:i:s')
    ]);

    // Lưu xong thì quay đầu xe về lại trang danh sách bài viết
    header('Location: ' . BASE_URL . '/admin/posts');
    exit;
}

/* ==========================================================
 * 2. XỬ LÝ HIỂN THỊ DANH SÁCH (Truy cập bình thường - GET)
 * ========================================================== */
$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$paginated = $postModel->adminGetAll($page, 10, $search);

// Gọi View để hiển thị
require_once __DIR__ . '/../views/admin/posts/index_post.php';