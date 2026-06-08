<?php
/**
 * views/admin/coupons/index.php — Danh sách mã giảm giá
 * Variables: $paginated
 */
require_once __DIR__ . '/../../../includes/admin_header.php';
require_once __DIR__ . '/../../../includes/admin_sidebar.php';
$coupons = $paginated['items'];
?>

<div class="admin-content">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 fw-bold mb-0" style="color: var(--text-main);">
      <i class="fa-solid fa-tag me-2 text-primary"></i>Mã giảm giá
    </h1>
    <a href="<?= BASE_URL ?>/admin/coupons/create" class="btn btn-primary">
      <i class="fa-solid fa-plus me-1"></i> Thêm mã mới
    </a>
  </div>

  <?= getFlash() ?>

  <div class="rounded-3 overflow-hidden"
       style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead style="background: var(--bg-light);">
          <tr>
            <th class="py-3 ps-4">Mã</th>
            <th class="py-3">Loại giảm</th>
            <th class="py-3">Giá trị</th>
            <th class="py-3">Đơn tối thiểu</th>
            <th class="py-3">Lượt dùng</th>
            <th class="py-3">Hiệu lực</th>
            <th class="py-3">Trạng thái</th>
            <th class="py-3 pe-4 text-end">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($coupons)): ?>
          <tr>
            <td colspan="8" class="text-center text-muted py-5">
              <i class="fa-solid fa-tag fa-2x mb-2 d-block"></i>
              Chưa có mã giảm giá nào.
            </td>
          </tr>
          <?php else: ?>
            <?php
            $now = new DateTime();
            foreach ($coupons as $coupon):
              $start = new DateTime($coupon['start_date']);
              $end   = new DateTime($coupon['end_date']);
              $isExpired = $now > $end;
              $isNotStarted = $now < $start;
            ?>
            <tr>
              <td class="ps-4">
                <span class="fw-bold font-monospace"
                      style="background: var(--bg-light); padding: 4px 10px; border-radius: 6px; font-size: 0.9rem;">
                  <?= htmlspecialchars($coupon['code']) ?>
                </span>
              </td>
              <td class="text-muted small">
                <?= $coupon['type'] === 'percent' ? '% Phần trăm' : '₫ Cố định' ?>
              </td>
              <td class="fw-semibold" style="color: var(--danger);">
                <?php if ($coupon['type'] === 'percent'): ?>
                  -<?= number_format((float) $coupon['value']) ?>%
                <?php else: ?>
                  -<?= formatPrice((float) $coupon['value']) ?>
                <?php endif; ?>
              </td>
              <td class="text-muted small">
                <?= $coupon['min_order'] > 0 ? formatPrice((float) $coupon['min_order']) : 'Không giới hạn' ?>
              </td>
              <td>
                <span class="<?= (int)$coupon['used_count'] >= (int)$coupon['max_uses'] ? 'text-danger fw-bold' : 'text-muted' ?>">
                  <?= number_format((int) $coupon['used_count']) ?> / <?= number_format((int) $coupon['max_uses']) ?>
                </span>
              </td>
              <td class="text-muted small">
                <?= date('d/m/Y', strtotime($coupon['start_date'])) ?>
                <span class="mx-1">→</span>
                <?= date('d/m/Y', strtotime($coupon['end_date'])) ?>
              </td>
              <td>
                <?php if ($coupon['status'] !== 'active'): ?>
                  <span class="badge text-bg-secondary rounded-pill">Vô hiệu</span>
                <?php elseif ($isExpired): ?>
                  <span class="badge text-bg-danger rounded-pill">Hết hạn</span>
                <?php elseif ($isNotStarted): ?>
                  <span class="badge text-bg-warning rounded-pill">Chưa bắt đầu</span>
                <?php elseif ((int)$coupon['used_count'] >= (int)$coupon['max_uses']): ?>
                  <span class="badge text-bg-secondary rounded-pill">Hết lượt</span>
                <?php else: ?>
                  <span class="badge text-bg-success rounded-pill">Đang hoạt động</span>
                <?php endif; ?>
              </td>
              <td class="pe-4 text-end">
                <form method="POST"
                      action="<?= BASE_URL ?>/admin/coupons/<?= $coupon['id'] ?>/delete"
                      class="d-inline"
                      onsubmit="return confirm('Xoá mã <?= htmlspecialchars($coupon['code']) ?>?')">
                  <input type="hidden" name="csrf_token" value="<?= generateToken() ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($paginated['totalPages'] > 1): ?>
    <div class="d-flex justify-content-between align-items-center px-4 py-3"
         style="border-top: 1px solid var(--border); background: var(--bg-light);">
      <span class="text-muted small">
        Tổng: <?= $paginated['total'] ?> mã giảm giá
      </span>
      <nav>
        <ul class="pagination pagination-sm mb-0 gap-1">
          <?php for ($p = 1; $p <= $paginated['totalPages']; $p++): ?>
          <li class="page-item <?= $p === $paginated['currentPage'] ? 'active' : '' ?>">
            <a class="page-link rounded-2" href="?page=<?= $p ?>"><?= $p ?></a>
          </li>
          <?php endfor; ?>
        </ul>
      </nav>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../../../includes/admin_footer.php'; ?>