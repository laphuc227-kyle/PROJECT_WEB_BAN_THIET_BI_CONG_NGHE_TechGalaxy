<?php
// File: models/Product.php
declare(strict_types=1);

class Product extends BaseModel
{
    protected string $table = 'products';

    public const PER_PAGE = 12;

    // ----------------------------------------------------------
    // Lấy tất cả sản phẩm — có phân trang
    // ----------------------------------------------------------
    public function getAllProducts(
        int $page    = 1,
        int $perPage = self::PER_PAGE
    ): array {
        $offset = ($page - 1) * $perPage;

        // Đếm tổng
        $countStmt = $this->pdo->prepare(
            "SELECT COUNT(*)
             FROM products
             WHERE status = 1"
        );
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        // Lấy dữ liệu
        $stmt = $this->pdo->prepare(
            "SELECT
                p.*,
                c.name AS category_name,
                pi.image_path AS primary_image
             FROM products p
             LEFT JOIN categories c
                 ON c.id = p.category_id
             LEFT JOIN product_images pi
                 ON pi.product_id = p.id
                 AND pi.is_primary = 1
             WHERE p.status = 1
             ORDER BY p.created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit',  $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data'  => $stmt->fetchAll(),
            'total' => $total,
            'pages' => (int) ceil($total / $perPage),
        ];
    }

    // ----------------------------------------------------------
    // Lấy 1 sản phẩm theo ID — kèm thông tin danh mục
    // ----------------------------------------------------------
    public function getProductById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                p.*,
                c.name AS category_name,
                c.slug AS category_slug
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.id = :id AND p.status = 1
             LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    // ----------------------------------------------------------
    // Lấy sản phẩm theo danh mục — có phân trang
    // ----------------------------------------------------------
    public function getProductsByCategory(
        int $categoryId,
        int $page    = 1,
        int $perPage = self::PER_PAGE
    ): array {
        $offset = ($page - 1) * $perPage;

        $countStmt = $this->pdo->prepare(
            "SELECT COUNT(*)
             FROM products
             WHERE category_id = :cat_id AND status = 1"
        );
        $countStmt->execute([':cat_id' => $categoryId]);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $this->pdo->prepare(
            "SELECT
                p.*,
                c.name AS category_name,
                pi.image_path AS primary_image
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN product_images pi
                 ON pi.product_id = p.id AND pi.is_primary = 1
             WHERE p.category_id = :cat_id AND p.status = 1
             ORDER BY p.created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':cat_id', $categoryId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit',  $perPage,    \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,     \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data'  => $stmt->fetchAll(),
            'total' => $total,
            'pages' => (int) ceil($total / $perPage),
        ];
    }

    // ----------------------------------------------------------
    // Tìm kiếm sản phẩm — có lọc danh mục và phân trang
    // ----------------------------------------------------------
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
        $countStmt = $this->pdo->prepare($countSql);
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
        $stmt = $this->pdo->prepare($sql);
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
        $stmt = $this->pdo->prepare("
            INSERT INTO products
                (name, slug, description, price, sale_price, stock, category_id, status, created_at)
            VALUES
                (:name, :slug, :description, :price, :sale_price, :stock, :category_id, :status, NOW())
        ");

        $slug = $this->generateSlug($data['name']);
        $description = $data['description'] ?? '';
        $salePrice = $data['sale_price'] ?? null;
        $stock = $data['stock'] ?? 0;
        $status = $data['status'] ?? 'active';

        $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':price', $data['price'], PDO::PARAM_STR);
        $stmt->bindParam(':sale_price',  $salePrice);
        $stmt->bindParam(':stock', $stock, PDO::PARAM_INT);
        $stmt->bindParam(':category_id', $data['category_id'], PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);

        $stmt->execute();
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Cập nhật sản phẩm theo ID
     */
    public function updateProduct(int $id, array $data): bool
    {
        $slug = $this->generateSlug($data['name']);

        $stmt = $this->pdo->prepare("
            UPDATE products
            SET name = :name, slug = :slug, description = :description,
                price = :price, sale_price = :sale_price, stock = :stock,
                category_id = :category_id, status = :status, updated_at = NOW()
            WHERE id = :id
        ");

        $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':price', $data['price'], PDO::PARAM_STR);
        $stmt->bindParam(':sale_price',  $salePrice);
        $stmt->bindParam(':stock', $stock, PDO::PARAM_INT);
        $stmt->bindParam(':category_id', $data['category_id'], PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Xóa mềm (soft delete) sản phẩm theo ID
     */
    public function deleteProduct(int $id): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE products SET deleted_at = NOW() WHERE id = :id
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // ----------------------------------------------------------
    // Lấy sản phẩm nổi bật
    // ----------------------------------------------------------
    public function getFeaturedProducts(int $limit = 8): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                p.*,
                pi.image_path AS primary_image
             FROM products p
             LEFT JOIN product_images pi
                 ON pi.product_id = p.id AND pi.is_primary = 1
             WHERE p.status = 1 AND p.is_featured = 1
             ORDER BY p.created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------
    // Lấy sản phẩm liên quan — cùng danh mục, loại trừ SP hiện tại
    // ----------------------------------------------------------
    public function getRelatedProducts(
        int $productId,
        int $categoryId,
        int $limit = 6
    ): array {
        $stmt = $this->pdo->prepare(
            "SELECT
                p.*,
                pi.image_path AS primary_image
             FROM products p
             LEFT JOIN product_images pi
                 ON pi.product_id = p.id AND pi.is_primary = 1
             WHERE p.category_id = :cat_id
               AND p.id != :product_id
               AND p.status = 1
             ORDER BY RAND()
             LIMIT :limit"
        );
        $stmt->bindValue(':cat_id',     $categoryId, \PDO::PARAM_INT);
        $stmt->bindValue(':product_id', $productId,  \PDO::PARAM_INT);
        $stmt->bindValue(':limit',      $limit,      \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------
    // Helper: tạo slug từ tên — hỗ trợ tiếng Việt
    // ----------------------------------------------------------
    public function generateSlug(string $name): string
    {
        $slug = mb_strtolower($name, 'UTF-8');

        $vietnamese = [
            'à','á','ả','ã','ạ','ă','ắ','ặ','ằ','ẳ','ẵ',
            'â','ấ','ậ','ầ','ẩ','ẫ','è','é','ẻ','ẽ','ẹ',
            'ê','ế','ệ','ề','ể','ễ','ì','í','ỉ','ĩ','ị',
            'ò','ó','ỏ','õ','ọ','ô','ố','ộ','ồ','ổ','ỗ',
            'ơ','ớ','ợ','ờ','ở','ỡ','ù','ú','ủ','ũ','ụ',
            'ư','ứ','ự','ừ','ử','ữ','ỳ','ý','ỷ','ỹ','ỵ','đ',
        ];
        $latin = [
            'a','a','a','a','a','a','a','a','a','a','a',
            'a','a','a','a','a','a','e','e','e','e','e',
            'e','e','e','e','e','e','i','i','i','i','i',
            'o','o','o','o','o','o','o','o','o','o','o',
            'o','o','o','o','o','o','u','u','u','u','u',
            'u','u','u','u','u','u','y','y','y','y','y','d',
        ];

        $slug = str_replace($vietnamese, $latin, $slug);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s]+/', '-', trim($slug));
        return $slug;
    }
}