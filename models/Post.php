<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class Post extends BaseModel
{
    protected string $table = 'posts';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Lấy tất cả bài viết có status = published (dùng cho user)
     */
    public function getPublished(int $page = 1, int $perPage = 6): array
    {
        return $this->paginate($page, $perPage, ['status' => 'published']);
    }

    /**
     * Lấy bài viết theo slug (dùng cho trang detail)
     */
    public function findBySlug(string $slug): array|false
    {
        $stmt = $this->pdo->prepare(
            "SELECT p.*, u.name AS author_name, u.avatar AS author_avatar
             FROM posts p
             LEFT JOIN users u ON p.author_id = u.id
             WHERE p.slug = ? AND p.status = 'published'
             LIMIT 1"
        );
        $stmt->execute([$slug]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Lấy bài viết liên quan (cùng tag hoặc 3 bài mới nhất)
     */
    public function getRelated(int $postId, int $limit = 3): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, title, slug, image, created_at
             FROM posts
             WHERE status = 'published' AND id != ?
             ORDER BY created_at DESC
             LIMIT ?"
        );
        $stmt->execute([$postId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Lấy N bài mới nhất (dùng cho trang chủ)
     */
    public function getLatest(int $limit = 3): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT p.id, p.title, p.slug, p.image, p.created_at, u.name AS author_name
             FROM posts p
             LEFT JOIN users u ON p.author_id = u.id
             WHERE p.status = 'published'
             ORDER BY p.created_at DESC
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Đọc thời gian ước tính (reading time)
     */
    public function readingTime(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));
        return (int) max(1, ceil($wordCount / 200)); // 200 từ/phút
    }

    /**
     * Tạo excerpt từ nội dung (loại bỏ HTML)
     */
    public function makeExcerpt(string $content, int $length = 150): string
    {
        $plain = strip_tags($content);
        return mb_strlen($plain) > $length
            ? mb_substr($plain, 0, $length) . '...'
            : $plain;
    }

    /**
     * Admin: lấy tất cả bài viết có phân trang + tìm kiếm
     */
    public function adminGetAll(int $page = 1, int $perPage = 10, string $search = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $where  = '';

        if ($search !== '') {
            $where    = 'WHERE p.title LIKE ?';
            $params[] = "%{$search}%";
        }

        $sql = "SELECT p.*, u.name AS author_name
                FROM posts p
                LEFT JOIN users u ON p.author_id = u.id
                {$where}
                ORDER BY p.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Đếm tổng
        $countSql  = "SELECT COUNT(*) FROM posts p {$where}";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        return [
            'items'       => $items,
            'total'       => $total,
            'currentPage' => $page,
            'perPage'     => $perPage,
            'totalPages'  => (int) ceil($total / $perPage),
        ];
    }
}