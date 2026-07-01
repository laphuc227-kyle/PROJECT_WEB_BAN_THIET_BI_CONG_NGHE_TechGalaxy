<?php
declare(strict_types=1);
class ContactController {
    
    private PDO $pdo;
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    /**
     * Hiển thị trang liên hệ
     */
    public function showContact(): void {
        $pageTitle = 'Liên hệ - TechGalaxy';
        $currentPage = 'contact';
        require_once __DIR__ . '/../views/user/contact.php';
    }
    /**
     * Xử lý gửi contact form
     */
    public function submitContact(): void {
        // Chống spam: Rate limit session-based (tối đa 3 lần/giờ)
        if (!isset($_SESSION['contact_times'])) {
            $_SESSION['contact_times'] = [];
        }
        $now = time();
        // Lọc các lần gửi trong vòng 1 giờ qua
        $_SESSION['contact_times'] = array_filter($_SESSION['contact_times'], function($time) use ($now) {
            return ($now - $time) < 3600;
        });
        if (count($_SESSION['contact_times']) >= 3) {
            setFlash('error', 'Bạn đã gửi liên hệ quá nhiều lần (tối đa 3 lần/giờ). Vui lòng đợi và thử lại sau.');
            redirect('/contact');
            return;
        }
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $message = sanitize($_POST['message'] ?? '');
        // Validation
        $errors = [];
        if (empty($name) || strlen($name) < 2) {
            $errors[] = 'Họ tên phải từ 2 ký tự trở lên.';
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email liên hệ không hợp lệ.';
        }
        if (empty($message) || strlen($message) < 10) {
            $errors[] = 'Nội dung liên hệ phải dài từ 10 ký tự trở lên.';
        }
        if (!empty($errors)) {
            setFlash('error', implode('<br>', $errors));
            $_SESSION['old_contact'] = [
                'name' => $name,
                'email' => $email,
                'message' => $_POST['message'] ?? ''
            ];
            redirect('/contact');
            return;
        }
        try {
            // Tạo bảng contacts nếu chưa tồn tại
            $sqlCreateTable = "CREATE TABLE IF NOT EXISTS contacts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(150) NOT NULL,
                message TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            $this->pdo->exec($sqlCreateTable);
            // Ghi dữ liệu liên hệ
            $stmt = $this->pdo->prepare("INSERT INTO contacts (name, email, message) VALUES (:name, :email, :message)");
            $success = $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':message' => $message
            ]);
            if ($success) {
                // Thêm thời gian gửi vào rate limit
                $_SESSION['contact_times'][] = $now;
                unset($_SESSION['old_contact']);
                setFlash('success', 'Gửi lời nhắn liên hệ thành công! Chúng tôi sẽ phản hồi lại sớm nhất.');
            } else {
                setFlash('error', 'Gửi liên hệ thất bại. Vui lòng thử lại sau.');
            }
        } catch (PDOException $e) {
            error_log("Lỗi gửi liên hệ: " . $e->getMessage());
            setFlash('error', 'Đã xảy ra lỗi trên máy chủ. Vui lòng thử lại sau.');
        }
        redirect('/contact');
    }
}
// ===== ROUTING =====
$contactCtrl = new ContactController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contactCtrl->submitContact();
} else {
    $contactCtrl->showContact();
}