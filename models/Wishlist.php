<?php

namespace Models;

use Config\Database;
use PDO;

/**
 * Model Wishlist
 * Quản lý danh sách yêu thích của người dùng
 */
class Wishlist
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Thêm sản phẩm vào Wishlist của người dùng
     * Kiểm tra trùng lặp trước khi thêm
     *
     * @return array ['success' => bool, 'message' => string, 'id' => int|null]
     */
    public function addToWishlist(int $userId, int $productId): array
    {
        // Kiểm tra đã tồn tại chưa
        if ($this->checkWishlistExists($userId, $productId)) {
            return ['success' => false, 'message' => 'Sản phẩm đã có trong danh sách yêu thích.'];
        }

        $stmt = $this->db->prepare("
            INSERT INTO wishlists (user_id, product_id, created_at)
            VALUES (:user_id, :product_id, NOW())
        ");
        $stmt->bindParam(':user_id',    $userId,    PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'success' => true,
            'message' => 'Đã thêm sản phẩm vào danh sách yêu thích!',
            'id'      => (int) $this->db->lastInsertId(),
        ];
    }

    /**
     * Xóa một mục khỏi Wishlist theo wishlist ID
     */
    public function removeFromWishlist(int $wishlistId): array
    {
        $stmt = $this->db->prepare("DELETE FROM wishlists WHERE id = :id");
        $stmt->bindParam(':id', $wishlistId, PDO::PARAM_INT);
        $result = $stmt->execute();

        return [
            'success' => $result && $stmt->rowCount() > 0,
            'message' => $result ? 'Đã xóa khỏi danh sách yêu thích.' : 'Không tìm thấy mục cần xóa.',
        ];
    }

    /**
     * Xóa Wishlist theo userId + productId (dùng cho toggle từ trang chi tiết sản phẩm)
     */
    public function removeByUserAndProduct(int $userId, int $productId): array
    {
        $stmt = $this->db->prepare("
            DELETE FROM wishlists WHERE user_id = :user_id AND product_id = :product_id
        ");
        $stmt->bindParam(':user_id',    $userId,    PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'success' => $stmt->rowCount() > 0,
            'message' => 'Đã xóa khỏi danh sách yêu thích.',
        ];
    }

    /**
     * Lấy toàn bộ Wishlist của người dùng (kèm thông tin sản phẩm)
     */
    public function getWishlistByUser(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT w.id AS wishlist_id, w.created_at AS added_at,
                   p.id AS product_id, p.name, p.slug, p.price, p.sale_price, p.stock, p.status,
                   c.name AS category_name,
                   (SELECT pi.image_path FROM product_images pi
                    WHERE pi.product_id = p.id AND pi.is_primary = 1
                    LIMIT 1) AS primary_image
            FROM wishlists w
            INNER JOIN products p ON p.id = w.product_id AND p.deleted_at IS NULL
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE w.user_id = :user_id
            ORDER BY w.created_at DESC
        ");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Kiểm tra xem sản phẩm đã có trong Wishlist của người dùng chưa
     * @return int|false   Trả về wishlist_id nếu tồn tại, false nếu chưa có
     */
    public function checkWishlistExists(int $userId, int $productId): int|false
    {
        $stmt = $this->db->prepare("
            SELECT id FROM wishlists
            WHERE user_id = :user_id AND product_id = :product_id
            LIMIT 1
        ");
        $stmt->bindParam(':user_id',    $userId,    PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['id'] : false;
    }

    /**
     * Lấy tổng số sản phẩm yêu thích của người dùng
     */
    public function countByUser(int $userId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM wishlists
            WHERE user_id = :user_id
        ");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}