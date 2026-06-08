<?php
declare(strict_types = 1);

require_once __DIR__ . "/BaseModel.php";

class Order extends BaseModel
{
    protected string $table = 'orders';

    //----------------------------------------
    // Tạo đơn hàng mới
    //----------------------------------------


    public function createOrder(array $data): int
    {
        return $this->insert([
            'user_id' => $data['user_id'],
            'address_id' => $data['address_id'],
            'status' => $data['status'],
            'total' => $data['total'],
            'payment_method' => $data['payment_method'],
            'coupon_id' => $data['coupon_id'],
            'discount' => $data['discount'],
            'note' => $data['note'],
        ]);
    }


    //---------------------------------------------------
    // Lấy tất cả đơn hàng của 1 user
    // Sắp xếp mới nhất trước (ORDER BY created_at DESC)
    //---------------------------------------------------

    public function getByUserId(int $userId) : array
    {
        $stmt = $this->pdo->prepare(
            "SELECT *
            FROM orders
            WHERE user_id = :user_id
            ORDER BY created_at DESC"
        );
        $stmt->execute([':user_id' => $userId]);

        return $stmt->fetchAll();
    }


    //---------------------------------------------------
    // Lấy 1 đơn, kiểm tra thuộc đúng user đó
    // Mục đích: chống User A xem đơn của User B
    //---------------------------------------------------

    public function getByIdAndUserId(int $orderId, int $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT *
            FROM orders
            WHERE id = :id AND user_id = :user_id
            LIMIT 1"
        );
        $stmt->execute([
            ':id' => $orderId,
            ':user_id' => $userId
        ]);

        $result = $stmt->fetch();

        return $result ?: null;
    }

    
    //---------------------------------------------------
    // Chỉ được huỷ khi status = 'pending' HOẶC 'confirmed'
    // Nếu status khác → return false, không làm gì
    //---------------------------------------------------

    public function cancelOrder(int $orderId, int $userId) : bool 
    {
        $stmt = $this->pdo->prepare(
            "UPDATE orders
            SET status = 'cancelled'
            WHERE id = :id AND user_id = :user_id AND status IN ('pending', 'confirmed')"
        );

        $stmt->execute([
            ':id' => $orderId,
            ':user_id' => $userId
        ]);
        return $stmt->rowCount() > 0;
    }


    //---------------------------------------------------
    // cập nhật đơn hàng
    //---------------------------------------------------

    public function updateStatus(int $orderId, string $status) : bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE orders
            SET status = :status
            WHERE id = :id"
        );

        return $stmt->execute([
        ':status' => $status,
        ':id'     => $orderId
        ]);
    }

    //---------------------------------------------------
    // lấy tất cả đơn hàng
    //---------------------------------------------------

    public function getAllOrders(string $status): array
    {
        if ($status !== '') {
            $stmt = $this->pdo->prepare(
                "SELECT 
                    o.*,
                    u.name,
                    u.email
                FROM orders o
                JOIN users u
                    ON o.user_id = u.id
                WHERE o.status  = :status
                ORDER BY o.created_at DESC"
            );
            $stmt->execute([':status' => $status]);
        } else {
            $stmt = $this->pdo->prepare(
                "SELECT 
                    o.*,
                    u.name,
                    u.email
                FROM orders o
                JOIN users u
                    ON o.user_id = u.id
                ORDER BY o.created_at DESC"
            );
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }


    //---------------------------------------------------
    // lấy chi tiết đơn hàng
    //---------------------------------------------------

    public function getOrderDetail(int $orderId) : ?array 
    {
        $stmt = $this->pdo->prepare(
        "SELECT
            o.*,
            u.name,
            u.email,
            u.phone
         FROM orders o
         JOIN users u
            ON o.user_id = u.id
         WHERE o.id = :id
         LIMIT 1"
        );

        $stmt->execute([':id' => $orderId]);

        $result = $stmt->fetch();

        return $result ?: null;

    }
}