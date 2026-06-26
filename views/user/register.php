<?php
// File: views/user/register.php
$pageTitle = 'Đăng ký tài khoản - TechGalaxy';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
$old = $_SESSION['old_register'] ?? null;
unset($_SESSION['old_register']);
?>
<div class="container-xl py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5 col-xl-4">
      <div class="card border-0 shadow-sm p-4 mt-3" style="border-radius: var(--radius)">
        <div class="text-center mb-4">
          <h3 class="fw-bold text-dark mb-1">Tạo tài khoản mới</h3>
          <p class="text-muted small">Hãy bắt đầu hành trình mua sắm của bạn</p>
        </div>
        <form action="<?= BASE_URL ?>/register" method="POST" novalidate>
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
          <div class="mb-3">
            <label for="regName" class="form-label fw-medium text-secondary small">Họ và tên <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text bg-light border-0 text-muted"><i class="fa-regular fa-user"></i></span>
              <input type="text" class="form-control bg-light border-0 py-2" id="regName" name="name" 
                     placeholder="Nguyễn Văn A" required value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                     style="border-radius: 0 var(--radius) var(--radius) 0">
            </div>
          </div>
          <div class="mb-3">
            <label for="regEmail" class="form-label fw-medium text-secondary small">Email <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text bg-light border-0 text-muted"><i class="fa-regular fa-envelope"></i></span>
              <input type="email" class="form-control bg-light border-0 py-2" id="regEmail" name="email" 
                     placeholder="name@example.com" required value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                     style="border-radius: 0 var(--radius) var(--radius) 0">
            </div>
          </div>
          <div class="mb-3">
            <label for="regPhone" class="form-label fw-medium text-secondary small">Số điện thoại</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-0 text-muted"><i class="fa-solid fa-phone"></i></span>
              <input type="tel" class="form-control bg-light border-0 py-2" id="regPhone" name="phone" 
                     placeholder="0xxxxxxxxx" value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
                     style="border-radius: 0 var(--radius) var(--radius) 0">
            </div>
          </div>
          <div class="mb-3">
            <label for="regPassword" class="form-label fw-medium text-secondary small">Mật khẩu <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text bg-light border-0 text-muted"><i class="fa-solid fa-lock"></i></span>
              <input type="password" class="form-control bg-light border-0 py-2" id="regPassword" name="password" 
                     placeholder="Tối thiểu 8 ký tự (chữ + số)" required
                     style="border-radius: 0 var(--radius) var(--radius) 0">
            </div>
          </div>
          <div class="mb-4">
            <label for="regConfirmPassword" class="form-label fw-medium text-secondary small">Xác nhận mật khẩu <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text bg-light border-0 text-muted"><i class="fa-solid fa-shield-halved"></i></span>
              <input type="password" class="form-control bg-light border-0 py-2" id="regConfirmPassword" name="confirm_password" 
                     placeholder="Nhập lại mật khẩu" required
                     style="border-radius: 0 var(--radius) var(--radius) 0">
            </div>
          </div>
          <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mb-3" 
                  style="border-radius: var(--radius)">
            Đăng ký tài khoản
          </button>
          <div class="text-center">
            <p class="text-muted small mb-0">Đã có tài khoản? 
              <a href="<?= BASE_URL ?>/login" class="text-primary fw-semibold text-decoration-none">Đăng nhập</a>
            </p>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>