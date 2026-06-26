<?php
// File: views/user/login.php
$pageTitle = 'Đăng nhập - TechGalaxy';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<div class="container-xl py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5 col-xl-4">
      <div class="card border-0 shadow-sm p-4 mt-4" style="border-radius: var(--radius)">
        <div class="text-center mb-4">
          <h3 class="fw-bold text-dark mb-1">Chào mừng quay lại</h3>
          <p class="text-muted small">Đăng nhập tài khoản của bạn để tiếp tục</p>
        </div>
        <form action="<?= BASE_URL ?>/login" method="POST" novalidate>
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
          <div class="mb-3">
            <label for="loginEmail" class="form-label fw-medium text-secondary small">Email</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-0 text-muted"><i class="fa-regular fa-envelope"></i></span>
              <input type="email" class="form-control bg-light border-0 py-2" id="loginEmail" name="email" 
                     placeholder="name@example.com" required style="border-radius: 0 var(--radius) var(--radius) 0">
            </div>
          </div>
          <div class="mb-4">
            <div class="d-flex justify-content-between mb-1">
              <label for="loginPassword" class="form-label fw-medium text-secondary small mb-0">Mật khẩu</label>
              <a href="<?= BASE_URL ?>/forgot_password" class="text-primary small fw-semibold text-decoration-none">Quên mật khẩu?</a>
            </div>
            <div class="input-group">
              <span class="input-group-text bg-light border-0 text-muted"><i class="fa-solid fa-lock"></i></span>
              <input type="password" class="form-control bg-light border-0 py-2" id="loginPassword" name="password" 
                     placeholder="Nhập mật khẩu" required style="border-radius: 0 var(--radius) var(--radius) 0">
            </div>
          </div>
          <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mb-3" 
                  style="border-radius: var(--radius)">
            Đăng nhập
          </button>
          <div class="text-center">
            <p class="text-muted small mb-0">Chưa có tài khoản? 
              <a href="<?= BASE_URL ?>/register" class="text-primary fw-semibold text-decoration-none">Đăng ký ngay</a>
            </p>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>