<?php
/**
 * views/admin/settings/index.php — Cài đặt hệ thống
 * Variables: $settings (key => value array)
 */
require_once __DIR__ . '/../../../includes/admin_header.php';
require_once __DIR__ . '/../../../includes/admin_sidebar.php';

// Helper: lấy setting an toàn
$s = function (string $key, string $default = '') use ($settings): string {
    return htmlspecialchars($settings[$key] ?? $default);
};
?>

<div class="admin-content">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 fw-bold mb-0" style="color: var(--text-main);">
      <i class="fa-solid fa-gears me-2 text-primary"></i>Cài đặt hệ thống
    </h1>
  </div>

  <?= getFlash() ?>

  <form method="POST" action="<?= BASE_URL ?>/admin/settings" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= generateToken() ?>">

    <div class="row g-4">

      <!-- ===== CỘT TRÁI ===== -->
      <div class="col-lg-8">

        <!-- Thông tin cửa hàng -->
        <div class="rounded-3 p-4 mb-4"
             style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
          <h2 class="h6 fw-bold mb-4" style="color: var(--text-main); border-bottom: 1px solid var(--border); padding-bottom: 12px;">
            <i class="fa-solid fa-store me-2 text-primary"></i>Thông tin cửa hàng
          </h2>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Tên cửa hàng</label>
              <input type="text" name="site_name" class="form-control"
                     value="<?= $s('site_name', 'TechGalaxy') ?>"
                     maxlength="100">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Email liên hệ</label>
              <input type="email" name="contact_email" class="form-control"
                     value="<?= $s('contact_email') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Số điện thoại</label>
              <input type="text" name="contact_phone" class="form-control"
                     value="<?= $s('contact_phone') ?>" maxlength="20">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Địa chỉ</label>
              <input type="text" name="address" class="form-control"
                     value="<?= $s('address') ?>" maxlength="255">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold small">Mô tả ngắn</label>
              <textarea name="store_description" class="form-control" rows="3"
                        maxlength="500"><?= $s('store_description') ?></textarea>
            </div>
          </div>
        </div>

        <!-- Cài đặt đơn hàng -->
        <div class="rounded-3 p-4 mb-4"
             style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
          <h2 class="h6 fw-bold mb-4" style="color: var(--text-main); border-bottom: 1px solid var(--border); padding-bottom: 12px;">
            <i class="fa-solid fa-truck me-2 text-primary"></i>Cài đặt đơn hàng & vận chuyển
          </h2>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Phí ship mặc định (₫)</label>
              <div class="input-group">
                <input type="number" name="free_ship_thresold" class="form-control"
                       value="<?= $s('free_ship_threshold', '500000') ?>"
                       min="0" step="1000">
                <span class="input-group-text">₫</span>
              </div>
              <div class="form-text">Phí giao hàng cố định mặc định</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Miễn phí ship từ (₫)</label>
              <div class="input-group">
                <input type="number" name="free_shipping_from" class="form-control"
                       value="<?= $s('free_shipping_from', '500000') ?>"
                       min="0" step="10000">
                <span class="input-group-text">₫</span>
              </div>
              <div class="form-text">Đặt 0 để không áp dụng miễn phí ship</div>
            </div>
          </div>
        </div>

        <!-- Cài đặt email -->
        <div class="rounded-3 p-4"
             style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
          <h2 class="h6 fw-bold mb-4" style="color: var(--text-main); border-bottom: 1px solid var(--border); padding-bottom: 12px;">
            <i class="fa-solid fa-envelope me-2 text-primary"></i>Cài đặt Email
          </h2>

          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox"
                   id="sendOrderEmail" name="send_order_email" value="1"
                   <?= ($settings['send_order_email'] ?? '1') === '1' ? 'checked' : '' ?>>
            <label class="form-check-label fw-semibold" for="sendOrderEmail">
              Gửi email xác nhận khi có đơn hàng mới
            </label>
          </div>
          <div class="form-text mt-1">
            Khi bật, hệ thống tự gửi email xác nhận đến khách hàng sau khi đặt hàng thành công.
          </div>
        </div>

      </div>

      <!-- ===== CỘT PHẢI ===== -->
      <div class="col-lg-4">

        <!-- Logo -->
        <div class="rounded-3 p-4 mb-4"
             style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
          <h2 class="h6 fw-bold mb-4" style="color: var(--text-main);">
            <i class="fa-solid fa-image me-2 text-primary"></i>Logo cửa hàng
          </h2>

          <!-- Preview logo hiện tại -->
          <?php if (!empty($settings['store_logo'])): ?>
          <div class="mb-3 text-center p-3 rounded-2" style="background: var(--bg-light);">
            <img src="<?= BASE_URL . '/public/uploads/' . htmlspecialchars($settings['store_logo']) ?>"
                 alt="Logo hiện tại"
                 style="max-height: 80px; max-width: 100%; object-fit: contain;">
            <p class="text-muted small mt-2 mb-0">Logo hiện tại</p>
          </div>
          <?php else: ?>
          <div class="mb-3 text-center p-3 rounded-2 text-muted" style="background: var(--bg-light);">
            <i class="fa-solid fa-image fa-2x mb-1 d-block"></i>
            <span class="small">Chưa có logo</span>
          </div>
          <?php endif; ?>

          <input type="file" name="logo_file" class="form-control form-control-sm"
                 accept="image/jpeg,image/png,image/webp,image/svg+xml">
          <div class="form-text">JPG, PNG, WEBP, SVG · Tối đa 2MB</div>
        </div>

        <!-- Lưu -->
        <div class="rounded-3 p-4"
             style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
          <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg">
              <i class="fa-solid fa-floppy-disk me-2"></i>Lưu cài đặt
            </button>
          </div>
          <p class="text-muted small text-center mt-2 mb-0">
            Thay đổi sẽ có hiệu lực ngay lập tức.
          </p>
        </div>

      </div>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../../../includes/admin_footer.php'; ?>