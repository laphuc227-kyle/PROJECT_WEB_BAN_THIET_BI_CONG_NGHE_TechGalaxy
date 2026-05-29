<?php

namespace Models;

use Config\Database;
use PDO;

/**
 * Model Category
 * Xử lý toàn bộ thao tác CRUD với bảng `categories`
 */
class Category
{
    private PDO $db;

    public function __construct()
    {
        // Lấy kết nối PDO từ class Config\Database (Singleton)
        $this->db = Database::getConnection();
    }

    /**
     * Lấy tất cả danh mục, sắp xếp theo tên A→Z
     */
    public function getAllCategories(): array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, COUNT(p.id) AS product_count
            FROM categories c
            LEFT JOIN products p ON p.category_id = c.id AND p.deleted_at IS NULL
            GROUP BY c.id
            ORDER BY c.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy 1 danh mục theo ID
     */
    public function getCategoryById(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm danh mục mới
     * @return int ID của bản ghi vừa thêm
     */
    public function createCategory(string $name, string $description = '', string $slug = ''): int
    {
        // Tự động tạo slug nếu không truyền vào
        if (empty($slug)) {
            $slug = $this->generateSlug($name);
        }

        $stmt = $this->db->prepare("
            INSERT INTO categories (name, slug, description, created_at)
            VALUES (:name, :slug, :description, NOW())
        ");
        $stmt->bindParam(':name',        $name,        PDO::PARAM_STR);
        $stmt->bindParam(':slug',        $slug,        PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    /**
     * Cập nhật danh mục theo ID
     */
    public function updateCategory(int $id, string $name, string $description = '', string $slug = ''): bool
    {
        if (empty($slug)) {
            $slug = $this->generateSlug($name);
        }

        $stmt = $this->db->prepare("
            UPDATE categories
            SET name = :name, slug = :slug, description = :description, updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->bindParam(':name',        $name,        PDO::PARAM_STR);
        $stmt->bindParam(':slug',        $slug,        PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':id',          $id,          PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Xóa danh mục theo ID (hard delete)
     * Lưu ý: nên kiểm tra xem còn sản phẩm thuộc danh mục này không trước khi xóa
     */
    public function deleteCategory(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Kiểm tra danh mục có sản phẩm liên kết không
     */
    public function hasProducts(int $categoryId): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM products WHERE category_id = :id AND deleted_at IS NULL
        ");
        $stmt->bindParam(':id', $categoryId, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Tạo slug từ tên (hỗ trợ tiếng Việt cơ bản)
     */
    private function generateSlug(string $name): string
    {
        $slug = mb_strtolower($name, 'UTF-8');

        // Bảng chuyển đổi ký tự tiếng Việt → Latin
        $vietnamese = [
            'à','á','ả','ã','ạ','ă','ắ','ặ','ằ','ẳ','ẵ','â','ấ','ậ','ầ','ẩ','ẫ',
            'è','é','ẻ','ẽ','ẹ','ê','ế','ệ','ề','ể','ễ',
            'ì','í','ỉ','ĩ','ị',
            'ò','ó','ỏ','õ','ọ','ô','ố','ộ','ồ','ổ','ỗ','ơ','ớ','ợ','ờ','ở','ỡ',
            'ù','ú','ủ','ũ','ụ','ư','ứ','ự','ừ','ử','ữ',
            'ỳ','ý','ỷ','ỹ','ỵ','đ',
        ];
        $latin = [
            'a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a',
            'e','e','e','e','e','e','e','e','e','e','e',
            'i','i','i','i','i',
            'o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o',
            'u','u','u','u','u','u','u','u','u','u','u',
            'y','y','y','y','y','d',
        ];

        $slug = str_replace($vietnamese, $latin, $slug);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s]+/', '-', trim($slug));

        return $slug;
    }
}