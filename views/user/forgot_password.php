<?php
// File: views/user/forgot_password.php
$pageTitle = 'Quên mật khẩu - TechGalaxy';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<div class="container-xl py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5 col-xl-4">
      <div class="card border-0 shadow-sm p-4 mt-4" style="border-radius: var(--radius)">
        <div class="text-center mb-4">
          <h3 class="fw-bold text-dark mb-1">Quên mật khẩu</h3>
          <p class="text-muted small">Nhập email của bạn để nhận mã OTP xác thực</p>
        </div>
        
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> small py-2">
                <?= $_SESSION['flash_message']; unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/forgot-password" method="POST">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
          <div class="mb-4">
            <label for="forgotEmail" class="form-label fw-medium text-secondary small">Địa chỉ Email</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-0 text-muted"><i class="fa-regular fa-envelope"></i></span>
              <input type="email" class="form-control bg-light border-0 py-2" id="forgotEmail" name="email" 
                     placeholder="name@example.com" required style="border-radius: 0 var(--radius) var(--radius) 0">
            </div>
          </div>
          <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mb-3" style="border-radius: var(--radius)">
            Gửi mã xác nhận OTP
          </button>
          <div class="text-center">
            <a href="<?= BASE_URL ?>/login" class="text-primary small fw-semibold text-decoration-none">
              <i class="fa-solid fa-arrow-left me-1"></i> Quay lại đăng nhập
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>