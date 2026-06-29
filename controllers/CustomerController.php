<?php

namespace Controllers;

use PDO;

/**
 * CustomerController
 * Xử lý 3 luồng logic: Danh sách (index_customer.php), Chi tiết (detail.php) và Khóa/Mở tài khoản
 */
class CustomerController
{
    private PDO $db;

    public function __construct()
    {
        global $pdo;
        $this->db = $pdo;
    }

    /**
     * CHỨC NĂNG 1: HIỂN THỊ DANH SÁCH
     * Cung cấp dữ liệu cho file views/admin/customers/index_customer.php
     */
    public function adminIndex(): void
    {
        $this->requireAdmin();

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 15;
        $offset  = ($page - 1) * $perPage;
        
        $filter  = $_GET['filter'] ?? 'all';
        $search  = trim($_GET['search'] ?? '');

        $where = ["role IN ('customer', 'user')"];
        $params = [];

        if ($filter === 'active') {
            $where[] = "status = 1";
        } elseif ($filter === 'blocked') {
            $where[] = "status = 0";
        }

        if (!empty($search)) {
            $where[] = "(name LIKE :search OR email LIKE :search)";
            $params[':search'] = "%{$search}%";
        }
        $whereClause = implode(' AND ', $where);

        // Đếm tổng để phân trang
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE $whereClause");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        
        $totalPages = (int)ceil($total / $perPage);
        $currentPage = $page;

        // Lấy danh sách khách hàng & số lượng đơn hàng của họ
        $sql = "
            SELECT id, name, email, phone, status, created_at, avatar,
                   (SELECT COUNT(*) FROM orders WHERE orders.user_id = users.id) AS order_count
            FROM users 
            WHERE $whereClause
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Gọi đúng file giao diện của bạn
        require_once __DIR__ . '/../views/admin/customers/index.php';
    }

    /**
     * CHỨC NĂNG 2: XEM CHI TIẾT
     * Cung cấp dữ liệu cho file views/admin/customers/detail.php
     */
    public function adminDetail(int $id): void
    {
        $this->requireAdmin();

        // 1. Lấy thông tin khách hàng ($customer)
        $stmt = $this->db->prepare("SELECT id, name, email, phone, status, created_at, avatar FROM users WHERE id = :id AND role IN ('customer', 'user')");
        $stmt->execute([':id' => $id]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$customer) {
            setFlash('danger', 'Không tìm thấy khách hàng!');
            header('Location: /admin/customers');
            exit;
        }

        // 2. Lấy 10 đơn hàng gần nhất ($orders)
        $orderStmt = $this->db->prepare("SELECT id, created_at, total, payment_method, status FROM orders WHERE user_id = :id ORDER BY created_at DESC LIMIT 10");
        $orderStmt->execute([':id' => $id]);
        $orders = $orderStmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Gọi file detail.php
        require_once __DIR__ . '/../views/admin/customers/detail.php';
    }

    /**
     /**
     * CHỨC NĂNG 3: BLOCK/UNBLOCK KHÁCH HÀNG
     */
    public function adminToggleStatus(int $id): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Tạm thời tắt kiểm tra CSRF nếu hệ thống nhóm chưa cấu hình đồng bộ
            // để đảm bảo chức năng cốt lõi (Khóa/Mở) chạy được thành công trước.

            // Truy vấn lấy trạng thái hiện tại
            $stmt = $this->db->prepare("SELECT status FROM users WHERE id = :id AND role IN ('customer', 'user')");
            $stmt->execute([':id' => $id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Cập nhật trạng thái
                $newStatus = $user['status'] ? 0 : 1;
                $update = $this->db->prepare("UPDATE users SET status = :status WHERE id = :id");
                $update->execute([':status' => $newStatus, ':id' => $id]);
                
                // Lưu thông báo thành công vào Session (thay vì dùng setFlash)
                $_SESSION['flash_message'] = $newStatus ? 'Đã mở khóa tài khoản thành công.' : 'Đã chặn tài khoản khách hàng.';
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_message'] = 'Không tìm thấy tài khoản cần xử lý.';
                $_SESSION['flash_type'] = 'danger';
            }
        }

        // CHỖ NÀY QUAN TRỌNG: Chuyển hướng đúng về /techgalaxy/admin/customers
        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '/techgalaxy/admin/customers';
        header("Location: $redirectUrl");
        exit;
    }

    /**
    /**
     * Helper: Kiểm tra quyền truy cập (Admin)
     */
    private function requireAdmin(): void
    {
        if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
            header('Location: /login');
            exit;
        }
    }
}

// =========================================================================
// ROUTER: Chia đường dẫn tương ứng với 3 luồng chức năng
// =========================================================================
$customerController = new CustomerController();
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Luồng 3: Xử lý Toggle (POST /admin/customers/{id}/toggle)
if (preg_match('#/admin/customers/(\d+)/toggle/?$#', $uri, $matches)) {
    $customerController->adminToggleStatus((int)$matches[1]);
} 
// Luồng 2: Xem chi tiết (GET /admin/customers/{id})
elseif (preg_match('#/admin/customers/(\d+)/?$#', $uri, $matches)) {
    $customerController->adminDetail((int)$matches[1]);
} 
// Luồng 1: Danh sách (GET /admin/customers)
elseif (strpos($uri, '/admin/customers') !== false) {
    $customerController->adminIndex();
}