<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class Comment extends BaseModel
{
    protected string $table = 'comments';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Lấy tất cả comment đã duyệt của một bài viết
     */
    public function getByPost(int $postId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT c.*, u.name AS user_name, u.avatar AS user_avatar
             FROM comments c
             LEFT JOIN users u ON c.user_id = u.id
             WHERE c.post_id = ? AND c.status = 'approved'
             ORDER BY c.created_at ASC"
        );
        $stmt->execute([$postId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Thêm comment mới (mặc định pending, chờ duyệt)
     */
    public function addComment(int $postId, int $userId, string $content): int
    {
        return $this->insert([
            'post_id'    => $postId,
            'user_id'    => $userId,
            'content'    => sanitize($content),
            'status'     => 'approved', // Auto-approve; đổi thành 'pending' nếu cần duyệt
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Admin: lấy tất cả comment kèm thông tin bài viết
     */
    public function adminGetAll(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $stmt   = $this->pdo->prepare(
            "SELECT c.*, u.name AS user_name, p.title AS post_title, p.slug AS post_slug
             FROM comments c
             LEFT JOIN users u ON c.user_id = u.id
             LEFT JOIN posts p ON c.post_id = p.id
             ORDER BY c.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Duyệt / ẩn comment
     */
    public function setStatus(int $id, string $status): bool
    {
        return $this->update($id, ['status' => $status]);
    }
}