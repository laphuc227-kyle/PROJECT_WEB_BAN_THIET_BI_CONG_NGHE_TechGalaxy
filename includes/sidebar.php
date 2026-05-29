<?php
// Xác định trang hiện tại để highlight menu
$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
function isActive(string $prefix): string {
    global $currentUri;
    return str_starts_with($currentUri, $prefix) ? 'active' : '';
}
?>
<aside class="admin-sidebar">
    <a href="/admin" class="sidebar-logo">
        <i class="bi bi-stars me-1"></i>Tech<span>Galaxy</span>
    </a>

    <nav class="sidebar-nav">
        <div class="sidebar-label">Tổng quan</div>
        <a href="/admin" class="sidebar-link <?= $currentUri === '/admin' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <div class="sidebar-label">Sản phẩm</div>
        <a href="/admin/categories" class="sidebar-link <?= isActive('/admin/categories') ?>">
            <i class="bi bi-grid"></i> Danh mục
        </a>
        <a href="/admin/products" class="sidebar-link <?= isActive('/admin/products') ?>">
            <i class="bi bi-box-seam"></i> Sản phẩm
        </a>

        <div class="sidebar-label">Người dùng</div>
        <a href="/admin/users" class="sidebar-link <?= isActive('/admin/users') ?>">
            <i class="bi bi-people"></i> Tài khoản
        </a>

        <div class="sidebar-label">Hệ thống</div>
        <a href="/" class="sidebar-link" target="_blank">
            <i class="bi bi-box-arrow-up-right"></i> Xem website
        </a>
        <a href="/logout" class="sidebar-link">
            <i class="bi bi-box-arrow-right"></i> Đăng xuất
        </a>
    </nav>
</aside>