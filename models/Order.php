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
            "SELECT
                o.*,
                COALESCE(SUM(od.quantity), 0) AS product_count
            FROM orders o

            LEFT JOIN order_details od
                ON o.id = od.order_id
        
            WHERE o.user_id = :user_id

            GROUP BY o.id

            ORDER BY o.created_at DESC"
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
                    u.name as user_name,
                    u.email as user_email,
                    COALESCE(SUM(od.quantity), 0) AS product_count
                FROM orders o
                JOIN users u
                    ON o.user_id = u.id
                LEFT JOIN order_details od
                    ON o.id = od.order_id
                WHERE o.status  = :status
                GROUP BY o.id
                ORDER BY o.created_at DESC"
            );
            $stmt->execute([':status' => $status]);
        } else {
            $stmt = $this->pdo->prepare(
                "SELECT 
                    o.*,
                    u.name as user_name,
                    u.email as user_email,
                    COALESCE(SUM(od.quantity), 0) AS product_count
                FROM orders o
                JOIN users u
                    ON o.user_id = u.id
                LEFT JOIN order_details od
                    ON o.id = od.order_id
                GROUP BY o.id
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

            u.name  AS user_name,
            u.email AS user_email,
            u.phone AS user_phone,

            a.name AS receiver_name,
            a.phone AS receiver_phone,
            a.province,
            a.district,
            a.ward,
            a.detail

        FROM orders o
        JOIN users u
            ON o.user_id = u.id
        LEFT JOIN addresses a
            ON o.address_id = a.id
        WHERE o.id = :id
        LIMIT 1"
        );

        $stmt->execute([':id' => $orderId]);

        $result = $stmt->fetch();

        return $result ?: null;

    }


    public function getById(int $orderId): ?array
    {
    $stmt = $this->pdo->prepare(
        "SELECT *
         FROM orders
         WHERE id = :id
         LIMIT 1"
    );

    $stmt->execute([
        ':id' => $orderId
    ]);

    $result = $stmt->fetch();

    return $result ?: null;
    }

    
    public function countTodayOrders(): int
    {
    $stmt = $this->pdo->query(
        "SELECT COUNT(*)
         FROM orders
         WHERE DATE(created_at)=CURDATE()"
    );

    return (int)$stmt->fetchColumn();
    }


    public function getTodayRevenue(): float
    {
    $stmt = $this->pdo->query(
        "SELECT COALESCE(SUM(total),0)
         FROM orders
         WHERE status='completed'
         AND DATE(created_at)=CURDATE()"
    );

    return (float)$stmt->fetchColumn();
    }



    public function countItems(int $orderId): int
    {
    $stmt = $this->pdo->prepare(
        "SELECT COALESCE(
            SUM(quantity),
            0
        )
        FROM order_details
        WHERE order_id = :id"
    );

    $stmt->execute([
        ':id' => $orderId
    ]);

    return (int)$stmt->fetchColumn();
    }



    public function countOrders(string $status = ''): int
    {
        if ($status !== '') {

            $stmt = $this->pdo->prepare(
                "
                SELECT COUNT(*)
                FROM orders
                WHERE status = :status
                "
            );

            $stmt->execute([
                ':status' => $status
            ]);

        } else {

            $stmt = $this->pdo->prepare(
                "
                SELECT COUNT(*)
                FROM orders
                "
            );

            $stmt->execute();
        }

        return (int)$stmt->fetchColumn();
    }


}