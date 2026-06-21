<?php
// File: views/user/contact.php
$pageTitle = 'Liên hệ với chúng tôi - TechGalaxy';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
$old = $_SESSION['old_contact'] ?? null;
unset($_SESSION['old_contact']);
?>
<div class="container-xl py-5">
  <!-- Header Section -->
  <div class="text-center mb-5">
    <h1 class="fw-bold text-dark mb-2">Liên hệ với TechGalaxy</h1>
    <p class="text-muted col-lg-6 mx-auto">Nếu bạn có bất kỳ thắc mắc, phản hồi hoặc cần hỗ trợ về đơn hàng, vui lòng gửi tin nhắn hoặc liên hệ với chúng tôi qua các kênh bên dưới.</p>
  </div>
  <div class="row g-4 mb-5">
    <!-- Contact Info Columns -->
    <div class="col-12 col-md-4">
      <div class="card border-0 shadow-sm p-4 text-center h-100" style="border-radius: var(--radius)">
        <div class="text-primary display-6 mb-3"><i class="fa-solid fa-phone"></i></div>
        <h5 class="fw-bold text-dark mb-2">Điện thoại hỗ trợ</h5>
        <p class="text-muted small mb-3">Chúng tôi sẵn sàng giải đáp thắc mắc của bạn qua điện thoại.</p>
        <a href="tel:19001234" class="text-primary fw-bold text-decoration-none fs-5">1900 1234</a>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="card border-0 shadow-sm p-4 text-center h-100" style="border-radius: var(--radius)">
        <div class="text-primary display-6 mb-3"><i class="fa-solid fa-envelope"></i></div>
        <h5 class="fw-bold text-dark mb-2">Email hỗ trợ</h5>
        <p class="text-muted small mb-3">Gửi email cho bộ phận chăm sóc khách hàng bất cứ lúc nào.</p>
        <a href="mailto:support@techgalaxy.vn" class="text-primary fw-bold text-decoration-none fs-5">support@techgalaxy.vn</a>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="card border-0 shadow-sm p-4 text-center h-100" style="border-radius: var(--radius)">
        <div class="text-primary display-6 mb-3"><i class="fa-solid fa-location-dot"></i></div>
        <h5 class="fw-bold text-dark mb-2">Địa chỉ cửa hàng</h5>
        <p class="text-muted small mb-3">Ghé thăm văn phòng hoặc trung tâm bảo hành của chúng tôi.</p>
        <span class="text-secondary fw-semibold">227 Nguyễn Văn Cừ, Q.5, TP.HCM</span>
      </div>
    </div>
  </div>
  <div class="row g-4">
    <!-- Contact Form Column -->
    <div class="col-12 col-lg-6">
      <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: var(--radius)">
        <h4 class="fw-bold text-dark mb-3">Gửi tin nhắn phản hồi</h4>
        <p class="text-muted small mb-4">Các trường có dấu <span class="text-danger">*</span> là bắt buộc nhập.</p>
        <form action="<?= BASE_URL ?>/contact" method="POST" novalidate>
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
          <div class="mb-3">
            <label for="contactName" class="form-label fw-medium text-secondary small">Họ và tên <span class="text-danger">*</span></label>
            <input type="text" class="form-control bg-light border-0 py-2" id="contactName" name="name" 
                   placeholder="Nhập họ và tên" required value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                   style="border-radius: var(--radius)">
          </div>
          <div class="mb-3">
            <label for="contactEmail" class="form-label fw-medium text-secondary small">Email liên hệ <span class="text-danger">*</span></label>
            <input type="email" class="form-control bg-light border-0 py-2" id="contactEmail" name="email" 
                   placeholder="name@example.com" required value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                   style="border-radius: var(--radius)">
          </div>
          <div class="mb-4">
            <label for="contactMessage" class="form-label fw-medium text-secondary small">Nội dung tin nhắn <span class="text-danger">*</span></label>
            <textarea class="form-control bg-light border-0 py-2" id="contactMessage" name="message" 
                      rows="5" placeholder="Nhập nội dung phản hồi, tối thiểu 10 ký tự" required 
                      style="border-radius: var(--radius)"><?= htmlspecialchars($old['message'] ?? '') ?></textarea>
          </div>
          <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold" style="border-radius: var(--radius)">
            <i class="fa-regular fa-paper-plane me-1"></i> Gửi đi
          </button>
        </form>
      </div>
    </div>
    <!-- Map Column -->
    <div class="col-12 col-lg-6">
      <div class="card border-0 shadow-sm p-2 h-100" style="border-radius: var(--radius); overflow: hidden;">
        <iframe 
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.669726978586!2d106.679683775838!3d10.76008085949575!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1m3!1d3919.669726978586!2d106.679683775838!3d10.76008085949575!2m2!1d106.6822587!2d10.7624128!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1svi!2s!5v1700000000000" 
          width="100%" 
          height="100%" 
          style="border:0; min-height: 350px; border-radius: calc(var(--radius) - 8px)" 
          allowfullscreen="" 
          loading="lazy" 
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>