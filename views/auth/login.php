<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/helpers.php';

$error = '';

// Xử lý khi người dùng ấn nút Đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Tài khoản Admin mẫu khớp với file seeds.sql của Phúc
    if ($email === 'admin@techgalaxy.com' && $password === '12345678') {
        $_SESSION['user_id'] = 1;
        $_SESSION['user_name'] = 'Quản trị viên';
        $_SESSION['user_role'] = 'admin';
        
        // Đăng nhập xong nhảy thẳng vào Admin Dashboard của Quân
        header('Location: ' . BASE_URL . '/admin');
        exit;
    } else {
        $error = 'Email hoặc mật khẩu không chính xác! (Thử: admin@techgalaxy.com / 12345678)';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - TechGalaxy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; font-family: sans-serif; }
        .login-card { max-width: 400px; width: 100%; border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .btn-tg { background: #2563eb; color: white; border-radius: 6px; }
        .btn-tg:hover { background: #1d4ed8; color: white; }
    </style>
</head>
<body>

<div class="card login-card p-4">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-primary">TechGalaxy</h3>
        <p class="text-muted small">Đăng nhập tài khoản kiểm thử hệ thống</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger small py-2"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label small fw-medium">Email</label>
            <input type="email" name="email" class="form-control" placeholder="admin@techgalaxy.com" required value="admin@techgalaxy.com">
        </div>
        <div class="mb-3">
            <label class="form-label small fw-medium">Mật khẩu</label>
            <input type="password" name="password" class="form-control" placeholder="12345678" required value="12345678">
        </div>
        <button type="submit" class="btn btn-tg w-100 py-2 mt-2">Đăng nhập</button>
    </form>
    
    <div class="text-center mt-3">
        <a href="<?= BASE_URL ?>/" class="text-decoration-none small">← Về trang chủ</a>
    </div>
</div>

</body>
</html>