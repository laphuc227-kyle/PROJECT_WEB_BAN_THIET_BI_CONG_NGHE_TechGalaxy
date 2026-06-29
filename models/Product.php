<?php

namespace Models;

use Config\Database;
use PDO;

/**
 * Model Product
 * Xử lý toàn bộ thao tác CRUD với bảng `products`
 * Hỗ trợ phân trang, tìm kiếm, lọc theo danh mục
 */
class Product
{
    private PDO $db;

    // Số sản phẩm mỗi trang (mặc định)
    public const PER_PAGE = 12;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Lấy tất cả sản phẩm có hỗ trợ phân trang
     *
     * @param int $page       Trang hiện tại (bắt đầu từ 1)
     * @param int $perPage    Số sản phẩm mỗi trang
     * @return array          ['data' => [...], 'total' => int, 'pages' => int]
     */
    public function getAllProducts(int $page = 1, int $perPage = self::PER_PAGE): array
    {
        $offset = ($page - 1) * $perPage;

        // Đếm tổng bản ghi để tính số trang
        $countStmt = $this->db->prepare("
            SELECT COUNT(*) FROM products WHERE deleted_at IS NULL
        ");
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        // Lấy dữ liệu theo trang
        $stmt = $this->db->prepare("
            SELECT p.*, c.name AS category_name,
                   (SELECT pi.image_path FROM product_images pi
                    WHERE pi.product_id = p.id AND pi.is_primary = 1
                    LIMIT 1) AS primary_image
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE p.deleted_at IS NULL
            ORDER BY p.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindParam(':limit',  $perPage, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset,  PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data'  => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'pages' => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Lấy 1 sản phẩm theo ID (kèm thông tin danh mục)
     */
    public function getProductById(int $id): array|false
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name AS category_name, c.slug AS category_slug
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE p.id = :id AND p.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách sản phẩm theo danh mục (có phân trang)
     */
    public function getProductsByCategory(int $categoryId, int $page = 1, int $perPage = self::PER_PAGE): array
    {
        $offset = ($page - 1) * $perPage;

        $countStmt = $this->db->prepare("
            SELECT COUNT(*) FROM products
            WHERE category_id = :cat_id AND deleted_at IS NULL
        ");
        $countStmt->bindParam(':cat_id', $categoryId, PDO::PARAM_INT);
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT p.*, c.name AS category_name,
                   (SELECT pi.image_path FROM product_images pi
                    WHERE pi.product_id = p.id AND pi.is_primary = 1
                    LIMIT 1) AS primary_image
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE p.category_id = :cat_id AND p.deleted_at IS NULL
            ORDER BY p.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindParam(':cat_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindParam(':limit',  $perPage,    PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset,     PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data'  => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'pages' => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Tìm kiếm sản phẩm theo từ khóa (tên hoặc mô tả)
     * Kết hợp lọc theo danh mục nếu có
     *
     * @param string   $keyword    Từ khóa tìm kiếm
     * @param int|null $categoryId Lọc thêm theo danh mục (tùy chọn)
     * @param int      $page       Trang hiện tại
     * @param int      $perPage    Số bản ghi mỗi trang
     */
    public function searchProducts(
        string $keyword,
        ?int $categoryId = null,
        int $page = 1,
        int $perPage = self::PER_PAGE
    ): array {
        $offset  = ($page - 1) * $perPage;
        $search  = '%' . $keyword . '%';

        // Xây dựng WHERE động
        $where   = "p.deleted_at IS NULL AND (p.name LIKE :keyword OR p.description LIKE :keyword2)";
        $params  = [':keyword' => $search, ':keyword2' => $search];

        if ($categoryId !== null) {
            $where   .= " AND p.category_id = :cat_id";
            $params[':cat_id'] = $categoryId;
        }

        // Đếm tổng kết quả
        $countSql  = "SELECT COUNT(*) FROM products p WHERE {$where}";
        $countStmt = $this->db->prepare($countSql);
        foreach ($params as $key => $val) {
            $countStmt->bindValue($key, $val);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        // Lấy dữ liệu theo trang
        $sql = "
            SELECT p.*, c.name AS category_name,
                   (SELECT pi.image_path FROM product_images pi
                    WHERE pi.product_id = p.id AND pi.is_primary = 1
                    LIMIT 1) AS primary_image
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE {$where}
            ORDER BY p.created_at DESC
            LIMIT :limit OFFSET :offset
        ";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data'    => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total'   => $total,
            'pages'   => (int) ceil($total / $perPage),
            'keyword' => $keyword,
        ];
    }

    /**
     * Thêm sản phẩm mới
     * @return int ID của sản phẩm vừa tạo
     */
    public function createProduct(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO products
                (name, slug, description, price, sale_price, stock, category_id, status, created_at)
            VALUES
                (:name, :slug, :description, :price, :sale_price, :stock, :category_id, :status, NOW())
        ");

        $slug = $this->generateSlug($data['name']);

        $stmt->bindParam(':name',        $data['name'],                       PDO::PARAM_STR);
        $stmt->bindParam(':slug',        $slug,                               PDO::PARAM_STR);
        $stmt->bindParam(':description', $data['description'] ?? '',          PDO::PARAM_STR);
        $stmt->bindParam(':price',       $data['price'],                      PDO::PARAM_STR);
        $stmt->bindParam(':sale_price',  $data['sale_price'] ?? null);
        $stmt->bindParam(':stock',       $data['stock'] ?? 0,                 PDO::PARAM_INT);
        $stmt->bindParam(':category_id', $data['category_id'],                PDO::PARAM_INT);
        $stmt->bindParam(':status',      $data['status'] ?? 'active',         PDO::PARAM_STR);

        $stmt->execute();
        return (int) $this->db->lastInsertId();
    }

    /**
     * Cập nhật sản phẩm theo ID
     */
    public function updateProduct(int $id, array $data): bool
    {
        $slug = $this->generateSlug($data['name']);

        $stmt = $this->db->prepare("
            UPDATE products
            SET name = :name, slug = :slug, description = :description,
                price = :price, sale_price = :sale_price, stock = :stock,
                category_id = :category_id, status = :status, updated_at = NOW()
            WHERE id = :id
        ");

        $stmt->bindParam(':name',        $data['name'],              PDO::PARAM_STR);
        $stmt->bindParam(':slug',        $slug,                      PDO::PARAM_STR);
        $stmt->bindParam(':description', $data['description'] ?? '', PDO::PARAM_STR);
        $stmt->bindParam(':price',       $data['price'],             PDO::PARAM_STR);
        $stmt->bindParam(':sale_price',  $data['sale_price'] ?? null);
        $stmt->bindParam(':stock',       $data['stock'] ?? 0,        PDO::PARAM_INT);
        $stmt->bindParam(':category_id', $data['category_id'],       PDO::PARAM_INT);
        $stmt->bindParam(':status',      $data['status'] ?? 'active',PDO::PARAM_STR);
        $stmt->bindParam(':id',          $id,                        PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Xóa mềm (soft delete) sản phẩm theo ID
     */
    public function deleteProduct(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE products SET deleted_at = NOW() WHERE id = :id
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Lấy sản phẩm liên quan (cùng danh mục, loại trừ sản phẩm hiện tại)
     */
    public function getRelatedProducts(int $productId, int $categoryId, int $limit = 6): array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name AS category_name,
                   (SELECT pi.image_path FROM product_images pi
                    WHERE pi.product_id = p.id AND pi.is_primary = 1
                    LIMIT 1) AS primary_image
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE p.category_id = :cat_id
              AND p.id != :product_id
              AND p.deleted_at IS NULL
              AND p.status = 'active'
            ORDER BY RAND()
            LIMIT :lim
        ");
        $stmt->bindParam(':cat_id',     $categoryId, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $productId,  PDO::PARAM_INT);
        $stmt->bindParam(':lim',        $limit,      PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Helper: Tạo slug từ tên (hỗ trợ tiếng Việt)
     */
    private function generateSlug(string $name): string
    {
        $slug = mb_strtolower($name, 'UTF-8');
        $vietnamese = ['à','á','ả','ã','ạ','ă','ắ','ặ','ằ','ẳ','ẵ','â','ấ','ậ','ầ','ẩ','ẫ','è','é','ẻ','ẽ','ẹ','ê','ế','ệ','ề','ể','ễ','ì','í','ỉ','ĩ','ị','ò','ó','ỏ','õ','ọ','ô','ố','ộ','ồ','ổ','ỗ','ơ','ớ','ợ','ờ','ở','ỡ','ù','ú','ủ','ũ','ụ','ư','ứ','ự','ừ','ử','ữ','ỳ','ý','ỷ','ỹ','ỵ','đ'];
        $latin       = ['a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','e','e','e','e','e','e','e','e','e','e','e','i','i','i','i','i','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','u','u','u','u','u','u','u','u','u','u','u','y','y','y','y','y','d'];
        $slug = str_replace($vietnamese, $latin, $slug);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s]+/', '-', trim($slug));
        return $slug;
    }
}