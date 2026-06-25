<?php
/**
 * views/admin/coupons/create.php — Tạo mã giảm giá mới
 */
require_once __DIR__ . '/../../../includes/admin_header.php';
require_once __DIR__ . '/../../../includes/admin_sidebar.php';
?>

<div class="admin-content">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 fw-bold mb-0" style="color: var(--text-main);">
      <i class="fa-solid fa-plus-circle me-2 text-primary"></i>Thêm mã giảm giá
    </h1>
    <a href="<?= BASE_URL ?>/admin/coupons" class="btn btn-outline-secondary">
      <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
    </a>
  </div>

  <?= getFlash() ?>

  <div class="row justify-content-center">
    <div class="col-lg-7">
      <div class="rounded-3 p-4"
           style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">

        <form method="POST" action="<?= BASE_URL ?>/admin/coupons">
          <input type="hidden" name="csrf_token" value="<?= generateToken() ?>">

          <!-- Mã -->
          <div class="mb-4">
            <label for="code" class="form-label fw-semibold">
              Mã giảm giá <span class="text-danger">*</span>
            </label>
            <div class="input-group">
              <input type="text" id="code" name="code"
                     class="form-control text-uppercase fw-bold font-monospace"
                     placeholder="VD: SUMMER20"
                     maxlength="50" required
                     value="<?= htmlspecialchars($_POST['code'] ?? '') ?>"
                     style="letter-spacing: 2px; font-size: 1.1rem;">
              <button type="button" class="btn btn-outline-secondary" id="btnGenCode">
                <i class="fa-solid fa-rotate me-1"></i> Tự động
              </button>
            </div>
            <div class="form-text">Mã sẽ được tự động chuyển thành chữ HOA.</div>
          </div>

          <!-- Loại + Giá trị -->
          <div class="row g-3 mb-4">
            <div class="col-md-5">
              <label for="type" class="form-label fw-semibold">
                Loại giảm <span class="text-danger">*</span>
              </label>
              <select id="type" name="type" class="form-select">
                <option value="percent" <?= ($_POST['type'] ?? '') === 'percent' ? 'selected' : '' ?>>
                  % Phần trăm
                </option>
                <option value="fixed" <?= ($_POST['type'] ?? 'fixed') === 'fixed' ? 'selected' : '' ?>>
                  ₫ Số tiền cố định
                </option>
              </select>
            </div>
            <div class="col-md-7">
              <label for="value" class="form-label fw-semibold">
                Giá trị giảm <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <input type="number" id="value" name="value"
                       class="form-control"
                       placeholder="0"
                       min="1" step="1" required
                       value="<?= htmlspecialchars($_POST['value'] ?? '') ?>">
                <span class="input-group-text" id="valueUnit">₫</span>
              </div>
            </div>
          </div>

          <!-- Min order + Max uses -->
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label for="min_order" class="form-label fw-semibold">Đơn hàng tối thiểu (₫)</label>
              <input type="number" id="min_order" name="min_order"
                     class="form-control"
                     placeholder="0 = không giới hạn"
                     min="0" step="1000"
                     value="<?= htmlspecialchars($_POST['min_order'] ?? '0') ?>">
            </div>
            <div class="col-md-6">
              <label for="max_uses" class="form-label fw-semibold">Số lượt dùng tối đa</label>
              <input type="number" id="max_uses" name="max_uses"
                     class="form-control"
                     placeholder="100"
                     min="1" required
                     value="<?= htmlspecialchars($_POST['max_uses'] ?? '100') ?>">
            </div>
          </div>

          <!-- Ngày hiệu lực -->
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label for="start_date" class="form-label fw-semibold">
                Ngày bắt đầu <span class="text-danger">*</span>
              </label>
              <input type="date" id="start_date" name="start_date"
                     class="form-control"
                     min="<?= date('Y-m-d') ?>" required
                     value="<?= htmlspecialchars($_POST['start_date'] ?? date('Y-m-d')) ?>">
            </div>
            <div class="col-md-6">
              <label for="end_date" class="form-label fw-semibold">
                Ngày kết thúc <span class="text-danger">*</span>
              </label>
              <input type="date" id="end_date" name="end_date"
                     class="form-control"
                     min="<?= date('Y-m-d') ?>" required
                     value="<?= htmlspecialchars($_POST['end_date'] ?? '') ?>">
            </div>
          </div>

          <!-- Preview tóm tắt -->
          <div class="rounded-3 p-3 mb-4" style="background: var(--bg-light); border: 1px solid var(--border);">
            <h3 class="h6 fw-semibold mb-2" style="color: var(--text-main);">
              <i class="fa-solid fa-eye me-1 text-primary"></i>Xem trước
            </h3>
            <p id="previewText" class="mb-0 text-muted small">
              Điền thông tin để xem trước mã giảm giá.
            </p>
          </div>

          <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg">
              <i class="fa-solid fa-check me-1"></i> Tạo mã giảm giá
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
// Đổi đơn vị hiển thị theo loại giảm
document.getElementById('type').addEventListener('change', function () {
  document.getElementById('valueUnit').textContent = this.value === 'percent' ? '%' : '₫';
  updatePreview();
});

// Tự động tạo code
document.getElementById('btnGenCode').addEventListener('click', function () {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  let code = '';
  for (let i = 0; i < 8; i++) code += chars[Math.floor(Math.random() * chars.length)];
  document.getElementById('code').value = code;
});

// Auto uppercase code
document.getElementById('code').addEventListener('input', function () {
  this.value = this.value.toUpperCase();
  updatePreview();
});

function updatePreview() {
  const code    = document.getElementById('code').value || '???';
  const type    = document.getElementById('type').value;
  const value   = document.getElementById('value').value;
  const minOrder = document.getElementById('min_order').value;
  const end     = document.getElementById('end_date').value;

  if (!value) { document.getElementById('previewText').textContent = 'Điền thông tin để xem trước mã giảm giá.'; return; }

  let text = `Mã "${code}": giảm `;
  if (type === 'percent') text += `${value}%`;
  else text += `${parseInt(value).toLocaleString('vi-VN')} ₫`;

  if (minOrder > 0) text += ` cho đơn từ ${parseInt(minOrder).toLocaleString('vi-VN')} ₫`;
  if (end) text += `. Hết hạn ngày ${new Date(end).toLocaleDateString('vi-VN')}`;

  document.getElementById('previewText').textContent = text;
}

['type', 'value', 'min_order', 'end_date'].forEach(id =>
  document.getElementById(id).addEventListener('input', updatePreview)
);
</script>

<?php require_once __DIR__ . '/../../../includes/admin_footer.php'; ?>