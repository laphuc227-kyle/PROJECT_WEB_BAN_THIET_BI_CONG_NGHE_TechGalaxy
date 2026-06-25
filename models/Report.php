<?php
declare(strict_types=1);
require_once __DIR__ . '/BaseModel.php';

class Report extends BaseModel
{
    public function __construct() {
        parent::__construct();
    }

    // 1. Tính tổng doanh thu và số đơn
    public function getSummary(string $fromDate, string $toDate): array {
        $sql = "SELECT COUNT(*) as order_count, SUM(total) as revenue
                FROM orders
                WHERE status IN ('completed', 'delivered')
                AND DATE(created_at) >= ? AND DATE(created_at) <= ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$fromDate, $toDate]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: ['order_count' => 0, 'revenue' => 0];
    }

    // 2. Gom nhóm đơn hàng theo trạng thái (Vẽ Pie Chart)
    public function getOrdersByStatus(string $fromDate, string $toDate): array {
        $sql = "SELECT status, COUNT(*) as count
                FROM orders
                WHERE DATE(created_at) >= ? AND DATE(created_at) <= ?
                GROUP BY status";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$fromDate, $toDate]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $labels = []; $data = [];
        $statusMap = [
            'pending' => 'Chờ xử lý', 'confirmed' => 'Xác nhận',
            'shipping' => 'Đang giao', 'delivered' => 'Đã giao',
            'completed' => 'Hoàn thành', 'cancelled' => 'Đã huỷ'
        ];
        
        foreach ($rows as $row) {
            $labels[] = $statusMap[$row['status']] ?? $row['status'];
            $data[]   = (int) $row['count'];
        }
        return ['labels' => $labels, 'data' => $data];
    }

    // 3. Top Sản phẩm bán chạy (Vẽ Bar Chart)
    public function getTopProducts(string $fromDate, string $toDate, int $limit = 5): array {
        $sql = "SELECT p.name, SUM(oi.quantity) as total_sold
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                JOIN products p ON oi.product_id = p.id
                WHERE o.status IN ('completed', 'delivered')
                AND DATE(o.created_at) >= ? AND DATE(o.created_at) <= ?
                GROUP BY p.id
                ORDER BY total_sold DESC
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$fromDate, $toDate, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // 4. Top Khách hàng mua nhiều (Bảng xếp hạng)
    public function getTopCustomers(string $fromDate, string $toDate, int $limit = 5): array {
        $sql = "SELECT u.name, u.email, COUNT(o.id) as order_count, SUM(o.total) as total_spent
                FROM orders o
                JOIN users u ON o.user_id = u.id
                WHERE o.status IN ('completed', 'delivered')
                AND DATE(o.created_at) >= ? AND DATE(o.created_at) <= ?
                GROUP BY u.id
                ORDER BY total_spent DESC
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$fromDate, $toDate, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}