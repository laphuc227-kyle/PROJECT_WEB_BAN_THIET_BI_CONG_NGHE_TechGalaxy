<?php
/**
 * includes/admin_header.php
 * Chứa thẻ <head> và Navbar trên cùng
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

// Bảo vệ toàn bộ khu vực Admin: Phải đăng nhập & Phải là admin
requireAdmin();

$pageTitle = $pageTitle ?? 'TechGalaxy Admin';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <meta name="csrf-token" content="<?= generateToken() ?>">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    /* Biến CSS đồng bộ với file main.css của Thuận */
    :root {
      --primary: #2563EB;
      --primary-dark: #1D4ED8;
      --secondary: #64748B;
      --accent: #F59E0B;
      --success: #10B981;
      --danger: #EF4444;
      --bg-light: #F8FAFC;
      --text-main: #1E293B;
      --text-muted: #94A3B8;
      --border: #E2E8F0;
      --shadow: 0 4px 6px rgba(0,0,0,0.07);
      --font: 'Inter', sans-serif;
    }

    body {
      font-family: var(--font);
      background-color: var(--bg-light);
      color: var(--text-main);
      overflow-x: hidden; /* Tránh scroll ngang khi có sidebar */
    }

    /* Layout cơ bản: Navbar fixed top, Sidebar fixed left, Content thụt vào */
    .admin-navbar {
      height: 60px;
      background: #fff;
      border-bottom: 1px solid var(--border);
      position: fixed;
      top: 0;
      right: 0;
      left: 250px; /* Bằng độ rộng sidebar */
      z-index: 1030;
      transition: left 0.3s;
    }

    .admin-content {
      margin-top: 60px;
      margin-left: 250px;
      padding: 24px;
      min-height: calc(100vh - 60px);
      transition: margin-left 0.3s;
    }

    /* Media query cho Mobile/Tablet */
    @media (max-width: 991.98px) {
      .admin-navbar { left: 0; }
      .admin-content { margin-left: 0; padding: 16px; }
    }
  </style>
</head>
<body>

<nav class="admin-navbar d-flex align-items-center justify-content-between px-4 shadow-sm">
  <div class="d-flex align-items-center">
    <button class="btn btn-sm d-lg-none me-2" id="sidebarToggle" style="color: var(--text-main);">
      <i class="fa-solid fa-bars fa-lg"></i>
    </button>
    <a href="<?= BASE_URL ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Xem trang chủ">
      <i class="fa-solid fa-earth-asia me-1"></i> Xem Website
    </a>
  </div>

  <div class="d-flex align-items-center gap-3">
    <div class="dropdown">
      <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" style="color: var(--text-main);">
        <?php if (!empty($_SESSION['user_avatar'])): ?>
          <img src="<?= BASE_URL . '/public/uploads/' . htmlspecialchars($_SESSION['user_avatar']) ?>" alt="Admin" class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">
        <?php else: ?>
          <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
            <i class="fa-solid fa-user-shield"></i>
          </div>
        <?php endif; ?>
        <span class="fw-medium small"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></span>
      </a>
      <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
        <li><a class="dropdown-item" href="<?= BASE_URL ?>/account"><i class="fa-regular fa-id-badge me-2 text-muted"></i>Tài khoản</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout"><i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất</a></li>
      </ul>
    </div>
  </div>
</nav>