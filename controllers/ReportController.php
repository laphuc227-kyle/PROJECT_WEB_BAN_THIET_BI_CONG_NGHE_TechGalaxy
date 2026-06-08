<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

class ReportController
{
    private \PDO    $pdo;
    private Setting $settingModel;

    public function __construct()
    {
        // Lấy $pdo từ singleton/global (tuỳ cách Phúc setup)
        global $pdo;
        $this->pdo          = $pdo;
        $this->settingModel = new Setting();
    }

    // =========================================================
    // ADMIN DASHBOARD
    // =========================================================

    /**
     * GET /admin — Dashboard tổng quan
     */
    public function dashboard(): void
    {
        requireAdmin();

        $today     = date('Y-m-d');
        $weekStart = date('Y-m-d', strtotime('monday this week'));

        // 4 card thống kê
        $stats = [
            'orders_today'    => $this->countOrdersToday($today),
            'revenue_today'   => $this->revenueToday($today),
            'new_customers'   => $this->newCustomersThisWeek($weekStart),
            'low_stock'       => $this->countLowStock(5),
        ];

        // Doanh thu 7 ngày gần nhất (line chart)
        $revenueChart = $this->revenue7Days();

        // 5 đơn hàng mới nhất
        $latestOrders = $this->getLatestOrders(5);

        $pageTitle = 'Dashboard';
        require_once __DIR__ . '/../views/admin/dashboard.php';
    }

    // =========================================================
    // ADMIN REPORTS
    // =========================================================

    /**
     * GET /admin/reports — Báo cáo doanh thu
     */
    public function index(): void
    {
        requireAdmin();

        $fromDate = sanitize($_GET['from'] ?? date('Y-m-01'));
        $toDate   = sanitize($_GET['to']   ?? date('Y-m-d'));

        // Validate ngày
        if (!$this->isValidDate($fromDate) || !$this->isValidDate($toDate)) {
            $fromDate = date('Y-m-01');
            $toDate   = date('Y-m-d');
        }

        $summary      = $this->getSummary($fromDate, $toDate);
        $ordersByStatus = $this->ordersByStatus($fromDate, $toDate);  // Pie chart
        $topProducts  = $this->topProducts($fromDate, $toDate, 5);    // Bar chart
        $topCustomers = $this->topCustomers($fromDate, $toDate, 5);

        $pageTitle = 'Báo cáo doanh thu';
        require_once __DIR__ . '/../views/admin/reports/index.php';
    }

    /**
     * GET /admin/reports/export — Xuất CSV
     */
    public function exportCSV(): void
    {
        requireAdmin();

        $fromDate = sanitize($_GET['from'] ?? date('Y-m-01'));
        $toDate   = sanitize($_GET['to']   ?? date('Y-m-d'));

        $stmt = $this->pdo->prepare(
            "SELECT o.id, o.created_at, u.name AS customer_name, u.email,
                    o.total, o.status, o.payment_method
             FROM orders o
             LEFT JOIN users u ON o.user_id = u.id
             WHERE o.status = 'completed'
               AND DATE(o.created_at) BETWEEN ? AND ?
             ORDER BY o.created_at DESC"
        );
        $stmt->execute([$fromDate, $toDate]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // UTF-8 BOM để Excel hiển thị đúng tiếng Việt
        $bom = "\xEF\xBB\xBF";

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="doanh_thu_' . $fromDate . '_' . $toDate . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fwrite($out, $bom);

        // Header
        fputcsv($out, ['Mã đơn', 'Ngày đặt', 'Khách hàng', 'Email', 'Tổng tiền', 'Trạng thái', 'Thanh toán']);

        foreach ($rows as $row) {
            fputcsv($out, [
                '#' . $row['id'],
                date('d/m/Y H:i', strtotime($row['created_at'])),
                $row['customer_name'],
                $row['email'],
                number_format((float) $row['total'], 0, ',', '.') . ' ₫',
                $this->translateStatus($row['status']),
                $row['payment_method'] === 'cod' ? 'Tiền mặt (COD)' : 'Chuyển khoản',
            ]);
        }

        fclose($out);
        exit;
    }

    // =========================================================
    // ADMIN CUSTOMERS
    // =========================================================

    /**
     * GET /admin/customers — Danh sách khách hàng
     */
    public function customers(): void
    {
        requireAdmin();

        $page   = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 15;
        $offset = ($page - 1) * $perPage;
        $filter = sanitize($_GET['filter'] ?? 'all');   // all | active | blocked
        $search = sanitize($_GET['search'] ?? '');

        $where  = "WHERE u.role = 'user'";
        $params = [];

        if ($filter === 'active')  { $where .= " AND u.status = 1"; }
        if ($filter === 'blocked') { $where .= " AND u.status = 0"; }

        if ($search !== '') {
            $where   .= " AND (u.name LIKE ? OR u.email LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $sql = "SELECT u.*,
                       COUNT(DISTINCT o.id) AS order_count
                FROM users u
                LEFT JOIN orders o ON o.user_id = u.id
                {$where}
                GROUP BY u.id
                ORDER BY u.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $customers = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $countSql  = "SELECT COUNT(*) FROM users u {$where}";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total      = (int) $countStmt->fetchColumn();
        $totalPages = (int) ceil($total / $perPage);

        $pageTitle = 'Quản lý khách hàng';
        require_once __DIR__ . '/../views/admin/customers/index.php';
    }

    /**
     * GET /admin/customers/{id} — Chi tiết khách hàng
     */
    public function customerDetail(int $id): void
    {
        requireAdmin();

        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'user' LIMIT 1");
        $stmt->execute([$id]);
        $customer = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$customer) {
            setFlash('error', 'Khách hàng không tồn tại.');
            redirect(BASE_URL . '/admin/customers');
            return;
        }

        // Lịch sử mua hàng
        $orderStmt = $this->pdo->prepare(
            "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 10"
        );
        $orderStmt->execute([$id]);
        $orders = $orderStmt->fetchAll(\PDO::FETCH_ASSOC);

        $pageTitle = 'Chi tiết khách hàng: ' . sanitize($customer['name']);
        require_once __DIR__ . '/../views/admin/customers/detail.php';
    }

    /**
     * POST /admin/customers/{id}/toggle — Block / Unblock user
     */
    public function toggleBlock(int $id): void
    {
        requireAdmin();

        if (!validateCSRF($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Yêu cầu không hợp lệ.');
            redirect(BASE_URL . '/admin/customers');
            return;
        }

        $stmt = $this->pdo->prepare("SELECT status FROM users WHERE id = ? AND role = 'user' LIMIT 1");
        $stmt->execute([$id]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            setFlash('error', 'Người dùng không tồn tại.');
            redirect(BASE_URL . '/admin/customers');
            return;
        }

        $newStatus = $user['status'] == 1 ? 0 : 1;
        $upStmt    = $this->pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        $upStmt->execute([$newStatus, $id]);

        // Nếu block → huỷ session của user đó (đơn giản: đăng xuất họ bằng cách destroy session theo user_id không có built-in)
        // Cách thực tế: lưu "blocked" vào DB, middleware kiểm tra mỗi request (đã được handle trong auth.php của Phúc)

        $msg = $newStatus === 0 ? 'Đã chặn người dùng thành công.' : 'Đã mở chặn người dùng.';
        setFlash('success', $msg);
        redirect(BASE_URL . '/admin/customers');
    }

    // =========================================================
    // ADMIN SETTINGS
    // =========================================================

    /**
     * GET /admin/settings — Form cài đặt hệ thống
     */
    public function settings(): void
    {
        requireAdmin();
        $settings  = $this->settingModel->getAll();
        $pageTitle = 'Cài đặt hệ thống';
        require_once __DIR__ . '/../views/admin/settings/index.php';
    }

    /**
     * POST /admin/settings — Lưu cài đặt
     */
    public function saveSettings(): void
    {
        requireAdmin();

        if (!validateCSRF($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Yêu cầu không hợp lệ.');
            redirect(BASE_URL . '/admin/settings');
            return;
        }

        $allowed = [
            'store_name', 'store_logo', 'store_description',
            'contact_email', 'contact_phone', 'contact_address',
            'shipping_fee', 'free_shipping_from',
            'send_order_email',
        ];

        $data = [];
        foreach ($allowed as $key) {
            if (isset($_POST[$key])) {
                $data[$key] = sanitize($_POST[$key]);
            }
        }

        // Xử lý upload logo
        if (!empty($_FILES['logo_file']['name'])) {
            $logoPath = $this->uploadLogo();
            if ($logoPath) {
                $data['store_logo'] = $logoPath;
            }
        }

        $ok = $this->settingModel->setMany($data);

        if ($ok) {
            setFlash('success', 'Cài đặt đã được lưu.');
        } else {
            setFlash('error', 'Có lỗi khi lưu cài đặt.');
        }
        redirect(BASE_URL . '/admin/settings');
    }

    // =========================================================
    // PRIVATE HELPERS — Truy vấn thống kê
    // =========================================================

    private function countOrdersToday(string $today): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = ?");
        $stmt->execute([$today]);
        return (int) $stmt->fetchColumn();
    }

    private function revenueToday(string $today): float
    {
        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(SUM(total), 0) FROM orders WHERE status = 'completed' AND DATE(created_at) = ?"
        );
        $stmt->execute([$today]);
        return (float) $stmt->fetchColumn();
    }

    private function newCustomersThisWeek(string $weekStart): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM users WHERE role = 'user' AND created_at >= ?"
        );
        $stmt->execute([$weekStart . ' 00:00:00']);
        return (int) $stmt->fetchColumn();
    }

    private function countLowStock(int $threshold): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM products WHERE stock < ? AND status = 'active'");
        $stmt->execute([$threshold]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Doanh thu 7 ngày gần nhất, trả về array phù hợp Chart.js
     * ['labels' => [...], 'data' => [...]]
     */
    private function revenue7Days(): array
    {
        $labels = [];
        $data   = [];

        for ($i = 6; $i >= 0; $i--) {
            $date     = date('Y-m-d', strtotime("-{$i} days"));
            $labels[] = date('d/m', strtotime($date));

            $stmt = $this->pdo->prepare(
                "SELECT COALESCE(SUM(total), 0) FROM orders WHERE status = 'completed' AND DATE(created_at) = ?"
            );
            $stmt->execute([$date]);
            $data[] = (float) $stmt->fetchColumn();
        }

        return ['labels' => $labels, 'data' => $data];
    }

    private function getLatestOrders(int $limit): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT o.id, o.total, o.status, o.created_at, u.name AS customer_name
             FROM orders o
             LEFT JOIN users u ON o.user_id = u.id
             ORDER BY o.created_at DESC
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getSummary(string $from, string $to): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) AS order_count,
                    COALESCE(SUM(total), 0) AS revenue
             FROM orders
             WHERE status = 'completed'
               AND DATE(created_at) BETWEEN ? AND ?"
        );
        $stmt->execute([$from, $to]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function ordersByStatus(string $from, string $to): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT status, COUNT(*) AS cnt
             FROM orders
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY status"
        );
        $stmt->execute([$from, $to]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $labels = [];
        $data   = [];
        foreach ($rows as $row) {
            $labels[] = $this->translateStatus($row['status']);
            $data[]   = (int) $row['cnt'];
        }
        return ['labels' => $labels, 'data' => $data];
    }

    private function topProducts(string $from, string $to, int $limit): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT p.name, SUM(od.quantity) AS total_sold, SUM(od.subtotal) AS total_revenue
             FROM order_details od
             JOIN products p ON od.product_id = p.id
             JOIN orders o ON od.order_id = o.id
             WHERE o.status = 'completed'
               AND DATE(o.created_at) BETWEEN ? AND ?
             GROUP BY p.id
             ORDER BY total_sold DESC
             LIMIT ?"
        );
        $stmt->execute([$from, $to, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function topCustomers(string $from, string $to, int $limit): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT u.name, u.email, COUNT(o.id) AS order_count, SUM(o.total) AS total_spent
             FROM orders o
             JOIN users u ON o.user_id = u.id
             WHERE o.status = 'completed'
               AND DATE(o.created_at) BETWEEN ? AND ?
             GROUP BY u.id
             ORDER BY total_spent DESC
             LIMIT ?"
        );
        $stmt->execute([$from, $to, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function translateStatus(string $status): string
    {
        return match ($status) {
            'pending'   => 'Chờ xử lý',
            'confirmed' => 'Đã xác nhận',
            'shipping'  => 'Đang giao',
            'delivered' => 'Đã giao',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã huỷ',
            default     => $status,
        };
    }

    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    private function uploadLogo(): ?string
    {
        $file    = $_FILES['logo_file'];
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'];
        if (!in_array($file['type'], $allowed)) return null;
        if ($file['size'] > 2 * 1024 * 1024) return null;

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'logo_' . time() . '.' . strtolower($ext);
        $dest     = UPLOAD_PATH . 'settings/' . $filename;

        if (!is_dir(UPLOAD_PATH . 'settings/')) {
            mkdir(UPLOAD_PATH . 'settings/', 0755, true);
        }

        return move_uploaded_file($file['tmp_name'], $dest) ? 'settings/' . $filename : null;
    }
}
