<?php
// File: includes/account_sidebar.php
$user = $_SESSION['user'] ?? null;
$activeTab = $currentPage ?? '';
?>
<div class="card border-0 shadow-sm mb-4" style="border-radius: var(--radius); overflow: hidden;">
  <div class="card-body p-4 text-center bg-white">
    <div class="position-relative d-inline-block mb-3">
      <img src="<?= BASE_URL ?>/public/uploads/avatars/<?= htmlspecialchars($user['avatar'] ?? 'default.png') ?>" 
           alt="<?= htmlspecialchars($user['name'] ?? '') ?>" 
           class="rounded-circle border border-3 border-light shadow-sm"
           style="width: 100px; height: 100px; object-fit: cover;">
    </div>
    <h5 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($user['name'] ?? '') ?></h5>
    <p class="text-muted small mb-0"><?= htmlspecialchars($user['email'] ?? '') ?></p>
  </div>
  <div class="list-group list-group-flush border-top border-light">
    <a href="<?= BASE_URL ?>/account" 
       class="list-group-item list-group-item-action px-4 py-3 border-0 d-flex align-items-center gap-3 <?= $activeTab === 'account' ? 'active fw-semibold text-primary' : 'text-secondary' ?>" 
       style="<?= $activeTab === 'account' ? 'background-color: #f0f7ff; color: var(--primary) !important;' : 'background-color: transparent;' ?>">
      <i class="fa-regular fa-user fs-5 <?= $activeTab === 'account' ? 'text-primary' : 'text-muted' ?>"></i> 
      <span>Thông tin cá nhân</span>
    </a>
    <a href="<?= BASE_URL ?>/account/addresses" 
       class="list-group-item list-group-item-action px-4 py-3 border-0 d-flex align-items-center gap-3 <?= $activeTab === 'addresses' ? 'active fw-semibold text-primary' : 'text-secondary' ?>" 
       style="<?= $activeTab === 'addresses' ? 'background-color: #f0f7ff; color: var(--primary) !important;' : 'background-color: transparent;' ?>">
      <i class="fa-regular fa-address-book fs-5 <?= $activeTab === 'addresses' ? 'text-primary' : 'text-muted' ?>"></i> 
      <span>Địa chỉ nhận hàng</span>
    </a>
    <a href="<?= BASE_URL ?>/my-orders" 
       class="list-group-item list-group-item-action px-4 py-3 border-0 d-flex align-items-center gap-3 <?= $activeTab === 'orders' ? 'active fw-semibold text-primary' : 'text-secondary' ?>" 
       style="<?= $activeTab === 'orders' ? 'background-color: #f0f7ff; color: var(--primary) !important;' : 'background-color: transparent;' ?>">
      <i class="fa-regular fa-rectangle-list fs-5 <?= $activeTab === 'orders' ? 'text-primary' : 'text-muted' ?>"></i> 
      <span>Đơn hàng của tôi</span>
    </a>
    <a href="<?= BASE_URL ?>/wishlist" 
       class="list-group-item list-group-item-action px-4 py-3 border-0 d-flex align-items-center gap-3 <?= $activeTab === 'wishlist' ? 'active fw-semibold text-primary' : 'text-secondary' ?>" 
       style="<?= $activeTab === 'wishlist' ? 'background-color: #f0f7ff; color: var(--primary) !important;' : 'background-color: transparent;' ?>">
      <i class="fa-regular fa-heart fs-5 <?= $activeTab === 'wishlist' ? 'text-primary' : 'text-muted' ?>"></i> 
      <span>Sản phẩm yêu thích</span>
    </a>
    <a href="<?= BASE_URL ?>/logout" 
       class="list-group-item list-group-item-action px-4 py-3 border-0 d-flex align-items-center gap-3 text-danger" 
       style="background-color: transparent;">
      <i class="fa-solid fa-right-from-bracket fs-5"></i> 
      <span>Đăng xuất</span>
    </a>
  </div>
</div>