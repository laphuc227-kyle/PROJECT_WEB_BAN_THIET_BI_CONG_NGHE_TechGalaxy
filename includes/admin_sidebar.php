<?php
/**
 * includes/admin_sidebar.php
 */
// Lấy URL hiện tại để active menu
$currentURI = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
<style>
  .admin-sidebar {
    width: 250px;
    height: 100vh;
    background: #1E293B; /* Xanh đen */
    color: #F8FAFC;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1040;
    overflow-y: auto;
    transition: transform 0.3s ease;
  }

  .sidebar-brand {
    height: 60px;
    display: flex;
    align-items: center;
    padding: 0 20px;
    font-size: 1.25rem;
    font-weight: bold;
    color: #fff;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    text-decoration: none;
  }

  .sidebar-menu {
    list-style: none;
    padding: 16px 0;
    margin: 0;
  }

  .sidebar-menu .menu-title {
    padding: 10px 20px 5px;
    font-size: 0.75rem;
    text-transform: uppercase;
    color: #94A3B8;
    font-weight: 600;
  }

  .sidebar-link {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: #CBD5E1;
    text-decoration: none;
    transition: all 0.2s;
  }

  .sidebar-link:hover {
    color: #fff;
    background: rgba(255,255,255,0.05);
  }

  .sidebar-link.active {
    color: #fff;
    background: var(--primary);
    border-left: 4px solid #fff;
  }

  .sidebar-link i {
    width: 24px;
    font-size: 1.1rem;
    text-align: center;
    margin-right: 10px;
  }

  /* Ẩn sidebar trên mobile bằng translate */
  @media (max-width: 991.98px) {
    .admin-sidebar {
      transform: translateX(-100%);
    }
    .admin-sidebar.show {
      transform: translateX(0);
    }
    /* Lớp phủ màn hình khi hiện sidebar trên mobile */
    .sidebar-overlay {
      display: none;
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,0.5);
      z-index: 1035;
    }
    .sidebar-overlay.show { display: block; }
  }
</style>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="admin-sidebar" id="adminSidebar">
  <a href="<?= BASE_URL ?>/admin" class="sidebar-brand">
    <i class="fa-solid fa-rocket text-primary me-2"></i> TechGalaxy
  </a>

  <ul class="sidebar-menu">
    <li>
      <a href="<?= BASE_URL ?>/admin" class="sidebar-link <?= $currentURI === '/admin' ? 'active' : '' ?>">
        <i class="fa-solid fa-gauge-high"></i> Dashboard
      </a>
    </li>

    <li class="menu-title">Bán hàng</li>
    <li>
      <a href="<?= BASE_URL ?>/admin/products" class="sidebar-link <?= strpos($currentURI, '/admin/products') === 0 ? 'active' : '' ?>">
        <i class="fa-solid fa-box"></i> Sản phẩm
      </a>
    </li>
    <li>
      <a href="<?= BASE_URL ?>/admin/orders" class="sidebar-link <?= strpos($currentURI, '/admin/orders') === 0 ? 'active' : '' ?>">
        <i class="fa-solid fa-cart-flatbed"></i> Đơn hàng
      </a>
    </li>

    <li class="menu-title">Tiếp thị & Nội dung</li>
    <li>
      <a href="<?= BASE_URL ?>/admin/coupons" class="sidebar-link <?= strpos($currentURI, '/admin/coupons') === 0 ? 'active' : '' ?>">
        <i class="fa-solid fa-tags"></i> Mã giảm giá
      </a>
    </li>
    <li>
      <a href="<?= BASE_URL ?>/admin/posts" class="sidebar-link <?= strpos($currentURI, '/admin/posts') === 0 ? 'active' : '' ?>">
        <i class="fa-solid fa-newspaper"></i> Blog
      </a>
    </li>

    <li class="menu-title">Quản lý chung</li>
    <li>
      <a href="<?= BASE_URL ?>/admin/customers" class="sidebar-link <?= strpos($currentURI, '/admin/customers') === 0 ? 'active' : '' ?>">
        <i class="fa-solid fa-users"></i> Khách hàng
      </a>
    </li>
    <li>
      <a href="<?= BASE_URL ?>/admin/reports" class="sidebar-link <?= strpos($currentURI, '/admin/reports') === 0 ? 'active' : '' ?>">
        <i class="fa-solid fa-chart-pie"></i> Báo cáo
      </a>
    </li>
    <li>
      <a href="<?= BASE_URL ?>/admin/settings" class="sidebar-link <?= strpos($currentURI, '/admin/settings') === 0 ? 'active' : '' ?>">
        <i class="fa-solid fa-gears"></i> Cài đặt hệ thống
      </a>
    </li>
  </ul>
</aside>