<?php
// File: views/user/reset_password.php
$pageTitle = 'Đặt lại mật khẩu - TechGalaxy';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<div class="container-xl py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5 col-xl-4">
      <div class="card border-0 shadow-sm p-4 mt-4" style="border-radius: var(--radius)">
        <div class="text-center mb-4">
          <h3 class="fw-bold text-dark mb-1">Đặt lại mật khẩu</h3>
          <p class="text-muted small">Nhập mã OTP gửi tới email <strong><?= htmlspecialchars($_SESSION['reset_email'] ?? '') ?></strong> và thiết lập mật khẩu mới</p>
        </div>
        
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> small py-2">
                <?= $_SESSION['flash_message']; unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/reset-password" method="POST">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
          
          <div class="mb-3">
            <label for="otpCode" class="form-label fw-medium text-secondary small">Mã xác thực OTP (6 số)</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-0 text-muted"><i class="fa-solid fa-key"></i></span>
              <input type="text" class="form-control bg-light border-0 py-2 text-center fw-bold text-primary fs-5" id="otpCode" name="otp" 
                     placeholder="------" maxlength="6" required style="letter-spacing: 4px; border-radius: 0 var(--radius) var(--radius) 0">
            </div>
          </div>

          <div class="mb-3">
            <label for="newPassword" class="form-label fw-medium text-secondary small">Mật khẩu mới</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-0 text-muted"><i class="fa-solid fa-lock"></i></span>
              <input type="password" class="form-control bg-light border-0 py-2" id="newPassword" name="password" 
                     placeholder="Tối thiểu 8 ký tự (chữ + số)" required style="border-radius: 0 var(--radius) var(--radius) 0">
            </div>
          </div>

          <div class="mb-4">
            <label for="confirmPassword" class="form-label fw-medium text-secondary small">Xác nhận mật khẩu mới</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-0 text-muted"><i class="fa-solid fa-circle-check"></i></span>
              <input type="password" class="form-control bg-light border-0 py-2" id="confirmPassword" name="confirm_password" 
                     placeholder="Nhập lại mật khẩu mới" required style="border-radius: 0 var(--radius) var(--radius) 0">
            </div>
          </div>

          <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mb-3" style="border-radius: var(--radius)">
            Xác nhận đặt lại mật khẩu
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>