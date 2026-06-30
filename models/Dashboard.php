<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class Dashboard extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Lấy 4 chỉ số thống kê trên cùng
     */
    public function getStats(): array
    {
        // 1. Số đơn hàng hôm nay
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()");
        $ordersToday = (int) $stmt->fetchColumn();

        // 2. Doanh thu hôm nay (Chỉ tính đơn đã hoàn thành)
        $stmt = $this->pdo->query("SELECT SUM(total) FROM orders WHERE DATE(created_at) = CURDATE() AND status IN ('completed', 'delivered')");
        $revenueToday = (float) $stmt->fetchColumn();

        // 3. Khách hàng mới trong 7 ngày qua
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
        $newCustomers = (int) $stmt->fetchColumn();

        // 4. Sản phẩm sắp hết kho (tồn kho dưới 10)
        // Lưu ý: Nếu DB của bạn cột tồn kho tên khác (ví dụ: quantity), hãy sửa lại chữ 'stock'
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM products WHERE stock < 10");
        $lowStock = (int) $stmt->fetchColumn();

        return [
            'orders_today'  => $ordersToday,
            'revenue_today' => $revenueToday,
            'new_customers' => $newCustomers,
            'low_stock'     => $lowStock
        ];
    }

    /**
     * Lấy danh sách đơn hàng mới nhất
     */
   public function getLatestOrders(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT o.id, COALESCE(u.name, 'Khách vãng lai') AS customer_name, o.total, o.status
             FROM orders o
             LEFT JOIN users u ON o.user_id = u.id
             ORDER BY o.created_at DESC
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Lấy dữ liệu vẽ biểu đồ Chart.js (7 ngày gần nhất)
     */
    public function getRevenueChart(): array
    {
        $stmt = $this->pdo->query(
            "SELECT DATE(created_at) as date, SUM(total) as daily_revenue
             FROM orders
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
               AND status IN ('completed', 'delivered')
             GROUP BY DATE(created_at)
             ORDER BY DATE(created_at) ASC"
        );
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $labels = [];
        $data   = [];
        
        // Tự động lấp đầy mảng 7 ngày (kể cả những ngày không có đơn hàng nào)
        for ($i = 6; $i >= 0; $i--) {
            $dateStr = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('d/m', strtotime($dateStr)); // Format: 25/06
            
            $revenue = 0;
            foreach ($rows as $row) {
                if ($row['date'] === $dateStr) {
                    $revenue = (float) $row['daily_revenue'];
                    break;
                }
            }
            $data[] = $revenue;
        }

        return [
            'labels' => $labels,
            'data'   => $data
        ];
    }
}