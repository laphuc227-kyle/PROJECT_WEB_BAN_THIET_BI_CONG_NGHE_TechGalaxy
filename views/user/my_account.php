<?php
// File: views/user/my_account.php
if (empty($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}
$pageTitle = 'Tài khoản của tôi - TechGalaxy';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
$currentPage = 'account';
$activeTab = $_GET['tab'] ?? 'profile';
?>
<div class="container-xl py-5">
  <div class="row">
    <!-- Sidebar -->
    <div class="col-12 col-md-4 col-lg-3">
      <?php require_once __DIR__ . '/../../includes/account_sidebar.php'; ?>
    </div>
    <!-- Main Content -->
    <div class="col-12 col-md-8 col-lg-9">
      <div class="card border-0 shadow-sm p-4" style="border-radius: var(--radius)">
        
        <!-- Tab Navigation Header -->
        <ul class="nav nav-tabs border-bottom-0 mb-4 gap-2" id="accountTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link border-0 fw-semibold px-4 py-2 <?= $activeTab === 'profile' ? 'active text-primary bg-light' : 'text-secondary' ?>" 
                    id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-panel" 
                    type="button" role="tab" aria-controls="profile-panel" aria-selected="<?= $activeTab === 'profile' ? 'true' : 'false' ?>"
                    style="border-radius: var(--radius)">
              <i class="fa-regular fa-id-card me-2"></i>Thông tin cá nhân
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link border-0 fw-semibold px-4 py-2 <?= $activeTab === 'password' ? 'active text-primary bg-light' : 'text-secondary' ?>" 
                    id="password-tab" data-bs-toggle="tab" data-bs-target="#password-panel" 
                    type="button" role="tab" aria-controls="password-panel" aria-selected="<?= $activeTab === 'password' ? 'true' : 'false' ?>"
                    style="border-radius: var(--radius)">
              <i class="fa-solid fa-key me-2"></i>Đổi mật khẩu
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <a href="<?= BASE_URL ?>/my-orders" class="nav-link border-0 fw-semibold px-4 py-2 text-secondary" style="border-radius: var(--radius)">
              <i class="fa-regular fa-rectangle-list me-2"></i>Lịch sử mua hàng
            </a>
          </li>
        </ul>
        <!-- Tab Content -->
        <div class="tab-content bg-light p-4" style="border-radius: var(--radius)">
          
          <!-- Panel 1: Profile -->
          <div class="tab-pane fade <?= $activeTab === 'profile' ? 'show active' : '' ?>" 
               id="profile-panel" role="tabpanel" aria-labelledby="profile-tab">
            <div class="row g-4">
              <!-- Avatar Column -->
              <div class="col-12 col-lg-4 text-center border-end border-light">
                <div class="mb-3">
                  <img src="<?= BASE_URL ?>/public/uploads/avatars/<?= htmlspecialchars($user['avatar'] ?? 'default.png') ?>" 
                       alt="Avatar" class="rounded-circle border border-4 border-white shadow-sm"
                       style="width: 130px; height: 130px; object-fit: cover;">
                </div>
                
                <form action="<?= BASE_URL ?>/account/avatar" method="POST" enctype="multipart/form-data">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                  <div class="mb-3">
                    <label for="avatarInput" class="btn btn-outline-primary btn-sm px-3 fw-semibold" style="border-radius: var(--radius)">
                      <i class="fa-solid fa-camera me-1"></i>Chọn ảnh mới
                    </label>
                    <input type="file" id="avatarInput" name="avatar" class="d-none" accept="image/jpeg,image/png,image/webp" onchange="this.form.submit()">
                  </div>
                  <small class="text-muted d-block">Định dạng: JPG, PNG, WEBP. Tối đa 2MB.</small>
                </form>
              </div>
              <!-- Details Column -->
              <div class="col-12 col-lg-8">
                <h5 class="fw-bold mb-4 text-dark">Chi tiết tài khoản</h5>
                <form action="<?= BASE_URL ?>/account/update" method="POST">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                  
                  <div class="mb-3">
                    <label class="form-label text-muted small fw-medium">Địa chỉ Email (Không thể thay đổi)</label>
                    <input type="email" class="form-control border-0 py-2 bg-white" 
                           value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly style="border-radius: var(--radius)">
                  </div>
                  <div class="mb-3">
                    <label for="accName" class="form-label text-muted small fw-medium">Họ và tên <span class="text-danger">*</span></label>
                    <input type="text" class="form-control border-0 py-2 bg-white" id="accName" name="name" 
                           value="<?= htmlspecialchars($user['name'] ?? '') ?>" required style="border-radius: var(--radius)">
                  </div>
                  <div class="mb-4">
                    <label for="accPhone" class="form-label text-muted small fw-medium">Số điện thoại</label>
                    <input type="tel" class="form-control border-0 py-2 bg-white" id="accPhone" name="phone" 
                           value="<?= htmlspecialchars($user['phone'] ?? '') ?>" style="border-radius: var(--radius)">
                  </div>
                  <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold" style="border-radius: var(--radius)">
                    Lưu thay đổi
                  </button>
                </form>
              </div>
            </div>
          </div>
          <!-- Panel 2: Password -->
          <div class="tab-pane fade <?= $activeTab === 'password' ? 'show active' : '' ?>" 
               id="password-panel" role="tabpanel" aria-labelledby="password-tab">
            <h5 class="fw-bold mb-4 text-dark">Thay đổi mật khẩu bảo mật</h5>
            <form action="<?= BASE_URL ?>/account/password" method="POST" class="col-12 col-lg-8">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
              <div class="mb-3">
                <label for="oldPassword" class="form-label text-muted small fw-medium">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                <input type="password" class="form-control border-0 py-2 bg-white" id="oldPassword" name="old_password" required style="border-radius: var(--radius)">
              </div>
              <div class="mb-3">
                <label for="newPassword" class="form-label text-muted small fw-medium">Mật khẩu mới <span class="text-danger">*</span></label>
                <input type="password" class="form-control border-0 py-2 bg-white" id="newPassword" name="new_password" 
                       placeholder="Tối thiểu 8 ký tự (chữ + số)" required style="border-radius: var(--radius)">
              </div>
              <div class="mb-4">
                <label for="confirmPassword" class="form-label text-muted small fw-medium">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                <input type="password" class="form-control border-0 py-2 bg-white" id="confirmPassword" name="confirm_password" required style="border-radius: var(--radius)">
              </div>
              <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold" style="border-radius: var(--radius)">
                Cập nhật mật khẩu
              </button>
            </form>
          </div>
        </div><!-- /tab-content -->
      </div><!-- /card -->
    </div><!-- /col -->
  </div><!-- /row -->
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>