<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

class PostController
{
    private Post    $postModel;
    private Comment $commentModel;

    public function __construct()
    {
        $this->postModel    = new Post();
        $this->commentModel = new Comment();
    }

    // =========================================================
    // USER ROUTES
    // =========================================================

    /**
     * GET /blog — Danh sách bài viết (phân trang 6 bài/trang)
     */
    public function index(): void
    {
        $page      = max(1, (int) ($_GET['page'] ?? 1));
        $paginated = $this->postModel->getPublished($page, 6);
        $posts     = $paginated['items'];

        // Sinh excerpt cho mỗi bài
        foreach ($posts as &$post) {
            $post['excerpt'] = $this->postModel->makeExcerpt($post['content'] ?? '');
        }
        unset($post);

        $pageTitle   = 'Blog — TechGalaxy';
        $totalPages  = $paginated['totalPages'];
        $currentPage = $paginated['currentPage'];

        require_once __DIR__ . '/../views/user/blog.php';
    }

    /**
     * GET /blog/{slug} — Chi tiết bài viết
     */
    public function detail(string $slug): void
    {
        $post = $this->postModel->findBySlug($slug);

        if (!$post) {
            http_response_code(404);
            require_once __DIR__ . '/../views/errors/404.php';
            return;
        }

        $comments    = $this->commentModel->getByPost((int) $post['id']);
        $related     = $this->postModel->getRelated((int) $post['id']);
        $readingTime = $this->postModel->readingTime($post['content'] ?? '');
        $pageTitle   = sanitize($post['title']) . ' — TechGalaxy';
        $metaDesc    = $this->postModel->makeExcerpt($post['content'] ?? '', 160);

        // Xử lý POST comment
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_content'])) {
            $this->storeComment($post);
            return;
        }

        require_once __DIR__ . '/../views/user/blog_detail.php';
    }

    /**
     * POST /blog/{slug}/comment — Gửi comment
     */
    private function storeComment(array $post): void
    {
        // Kiểm tra đăng nhập
        if (!isLoggedIn()) {
            setFlash('error', 'Bạn cần đăng nhập để bình luận.');
            redirect(BASE_URL . '/blog/' . $post['slug']);
            return;
        }

        // CSRF
        if (!validateCSRF($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Yêu cầu không hợp lệ.');
            redirect(BASE_URL . '/blog/' . $post['slug']);
            return;
        }

        $content = trim($_POST['comment_content'] ?? '');
        if (mb_strlen($content) < 2 || mb_strlen($content) > 1000) {
            setFlash('error', 'Nội dung bình luận phải từ 2 đến 1000 ký tự.');
            redirect(BASE_URL . '/blog/' . $post['slug']);
            return;
        }

        $this->commentModel->addComment((int) $post['id'], (int) $_SESSION['user_id'], $content);
        setFlash('success', 'Bình luận của bạn đã được gửi.');
        redirect(BASE_URL . '/blog/' . $post['slug']);
    }

    // =========================================================
    // ADMIN ROUTES
    // =========================================================

    /**
     * GET /admin/posts — Danh sách bài viết (admin)
     */
    public function adminIndex(): void
    {
        requireAdmin();
        $page     = max(1, (int) ($_GET['page'] ?? 1));
        $search   = sanitize($_GET['search'] ?? '');
        $paginated = $this->postModel->adminGetAll($page, 10, $search);

        $pageTitle = 'Quản lý bài viết';
        require_once __DIR__ . '/../views/admin/posts/index.php';
    }

    /**
     * GET /admin/posts/create — Form thêm bài viết
     */
    public function adminCreate(): void
    {
        requireAdmin();
        $pageTitle = 'Thêm bài viết';
        require_once __DIR__ . '/../views/admin/posts/create.php';
    }

    /**
     * POST /admin/posts — Lưu bài viết mới
     */
    public function adminStore(): void
    {
        requireAdmin();

        if (!validateCSRF($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Yêu cầu không hợp lệ.');
            redirect(BASE_URL . '/admin/posts/create');
            return;
        }

        $title   = sanitize($_POST['title'] ?? '');
        $content = $_POST['content'] ?? ''; // HTML từ editor — không sanitize toàn bộ
        $status  = in_array($_POST['status'] ?? '', ['published', 'draft']) ? $_POST['status'] : 'draft';

        if (empty($title) || empty($content)) {
            setFlash('error', 'Tiêu đề và nội dung không được để trống.');
            redirect(BASE_URL . '/admin/posts/create');
            return;
        }

        $slug = slugify($title);

        // Xử lý upload ảnh cover
        $imagePath = $this->handleImageUpload('cover_image');

        $id = $this->postModel->insert([
            'title'      => $title,
            'slug'       => $slug,
            'content'    => $content,
            'image'      => $imagePath,
            'status'     => $status,
            'author_id'  => $_SESSION['user_id'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if ($id) {
            setFlash('success', 'Bài viết đã được tạo thành công.');
            redirect(BASE_URL . '/admin/posts');
        } else {
            setFlash('error', 'Có lỗi khi tạo bài viết.');
            redirect(BASE_URL . '/admin/posts/create');
        }
    }

    /**
     * GET /admin/posts/{id}/edit — Form sửa bài viết
     */
    public function adminEdit(int $id): void
    {
        requireAdmin();
        $post = $this->postModel->findById($id);

        if (!$post) {
            setFlash('error', 'Bài viết không tồn tại.');
            redirect(BASE_URL . '/admin/posts');
            return;
        }

        $pageTitle = 'Sửa bài viết: ' . sanitize($post['title']);
        require_once __DIR__ . '/../views/admin/posts/edit.php';
    }

    /**
     * POST /admin/posts/{id} — Cập nhật bài viết
     */
    public function adminUpdate(int $id): void
    {
        requireAdmin();

        if (!validateCSRF($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Yêu cầu không hợp lệ.');
            redirect(BASE_URL . '/admin/posts/' . $id . '/edit');
            return;
        }

        $post = $this->postModel->findById($id);
        if (!$post) {
            setFlash('error', 'Bài viết không tồn tại.');
            redirect(BASE_URL . '/admin/posts');
            return;
        }

        $title   = sanitize($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';
        $status  = in_array($_POST['status'] ?? '', ['published', 'draft']) ? $_POST['status'] : 'draft';

        if (empty($title) || empty($content)) {
            setFlash('error', 'Tiêu đề và nội dung không được để trống.');
            redirect(BASE_URL . '/admin/posts/' . $id . '/edit');
            return;
        }

        $data = [
            'title'   => $title,
            'slug'    => slugify($title),
            'content' => $content,
            'status'  => $status,
        ];

        // Xử lý ảnh cover mới nếu có upload
        $imagePath = $this->handleImageUpload('cover_image');
        if ($imagePath) {
            // Xoá ảnh cũ
            if (!empty($post['image'])) {
                $oldPath = UPLOAD_PATH . $post['image'];
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            $data['image'] = $imagePath;
        }

        $ok = $this->postModel->update($id, $data);

        if ($ok) {
            setFlash('success', 'Bài viết đã được cập nhật.');
        } else {
            setFlash('error', 'Có lỗi khi cập nhật bài viết.');
        }
        redirect(BASE_URL . '/admin/posts');
    }

    /**
     * POST /admin/posts/{id}/delete — Xoá bài viết
     */
    public function adminDelete(int $id): void
    {
        requireAdmin();

        if (!validateCSRF($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Yêu cầu không hợp lệ.');
            redirect(BASE_URL . '/admin/posts');
            return;
        }

        $post = $this->postModel->findById($id);
        if (!$post) {
            setFlash('error', 'Bài viết không tồn tại.');
            redirect(BASE_URL . '/admin/posts');
            return;
        }

        // Xoá ảnh cover
        if (!empty($post['image'])) {
            $imgPath = UPLOAD_PATH . $post['image'];
            if (file_exists($imgPath)) {
                unlink($imgPath);
            }
        }

        $this->postModel->delete($id);
        setFlash('success', 'Bài viết đã được xoá.');
        redirect(BASE_URL . '/admin/posts');
    }

    // =========================================================
    // HELPER
    // =========================================================

    /**
     * Xử lý upload ảnh bìa, trả về tên file hoặc null
     */
    private function handleImageUpload(string $fieldName): ?string
    {
        if (empty($_FILES[$fieldName]['name'])) {
            return null;
        }

        $file      = $_FILES[$fieldName];
        $allowed   = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize   = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowed)) {
            setFlash('error', 'Chỉ chấp nhận ảnh JPG, PNG, WEBP.');
            return null;
        }

        if ($file['size'] > $maxSize) {
            setFlash('error', 'Ảnh không được vượt quá 5MB.');
            return null;
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'post_' . uniqid() . '.' . strtolower($ext);
        $dest     = UPLOAD_PATH . 'posts/' . $filename;

        if (!is_dir(UPLOAD_PATH . 'posts/')) {
            mkdir(UPLOAD_PATH . 'posts/', 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return 'posts/' . $filename;
        }

        return null;
    }
}