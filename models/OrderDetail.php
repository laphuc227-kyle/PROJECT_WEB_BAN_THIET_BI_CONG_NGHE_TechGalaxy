<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class OrderDetail extends BaseModel
{
    protected string $table = 'order_details';

    // --------------------------------------------------
    // Lấy toàn bộ sản phẩm thuộc một đơn hàng
    // Dùng cho:
    // - My Orders
    // - Admin Order Detail
    // --------------------------------------------------
    public function getByOrderId(int $orderId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                od.*,
                p.name,
                p.slug,
                p.sku,
                pi.image_path
            FROM order_details od
            JOIN products p
                ON od.product_id = p.id
            LEFT JOIN (
                    SELECT
                        product_id,
                        MIN(image_path) AS image_path
                    FROM product_images
                    GROUP BY product_id
                ) pi
                ON p.id = pi.product_id
            WHERE od.order_id = :order_id
            ORDER BY od.id ASC"
        );

        $stmt->execute([
            ':order_id' => $orderId
        ]);

        return $stmt->fetchAll();
    }

    // --------------------------------------------------
    // Tạo 1 dòng order detail
    // --------------------------------------------------
    public function createDetail(
        int $orderId,
        int $productId,
        int $quantity,
        int $price
    ): int {

        return $this->insert([
            'order_id'   => $orderId,
            'product_id' => $productId,
            'quantity'   => $quantity,
            'price'      => $price,
            'subtotal'   => $quantity * $price
        ]);
    }

    // --------------------------------------------------
    // Tạo nhiều dòng order detail từ cart
    // --------------------------------------------------
    public function createMany(
        int $orderId,
        array $cartItems
    ): void {

        foreach ($cartItems as $item) {

            $this->createDetail(
                $orderId,
                (int)$item['product_id'],
                (int)$item['quantity'],
                (int)$item['price']
            );
        }
    }
    public function getSoldQuantity(int $productId): int
    {
    $stmt = $this->pdo->prepare(
        "SELECT COALESCE(
            SUM(quantity),
            0
        )
        FROM order_details
        WHERE product_id = :id"
    );

    $stmt->execute([
        ':id' => $productId
    ]);

    return (int)$stmt->fetchColumn();
    }
}