<?php

namespace Models;

use Config\Database;
use PDO;

class WishlistModel {
    private $db;

    public function __construct() {
        // Gọi đối tượng kết nối database từ class Config\Database
        $this->db = (new Database())->getConnection();
    }

    /**
     * Thêm sản phẩm vào Wishlist của user
     */
    public function addToWishlist($userId, $productId) {
        $query = "INSERT INTO wishlists (user_id, product_id, created_at) VALUES (:user_id, :product_id, NOW())";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':user_id' => $userId,
            ':product_id' => $productId
        ]);
    }

    /**
     * Xóa sản phẩm khỏi Wishlist
     */
    public function removeFromWishlist($wishlistId) {
        $query = "DELETE FROM wishlists WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $wishlistId]);
    }

    /**
     * Lấy toàn bộ danh sách sản phẩm yêu thích của một User
     * (Kết hợp JOIN với bảng products để lấy thông tin sản phẩm)
     */
    public function getWishlistByUser($userId) {
        $query = "SELECT w.id as wishlist_id, p.* FROM wishlists w
                  JOIN products p ON w.product_id = p.id
                  WHERE w.user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Kiểm tra xem sản phẩm đã có trong wishlist chưa
     */
    public function checkWishlistExists($userId, $productId) {
        $query = "SELECT id FROM wishlists WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':user_id' => $userId,
            ':product_id' => $productId
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }
}