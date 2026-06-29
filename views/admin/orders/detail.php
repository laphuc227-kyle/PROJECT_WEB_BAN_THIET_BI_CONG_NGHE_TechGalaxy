<?php
// File: views/admin/orders/detail.php
$pageTitle = 'Chi tiết đơn hàng #' . ($order['id'] ?? '');
$adminPage = 'orders';
$extraJS   = '';

/** @var array $order */
/** @var array $items */

$order = $order ?? [];
$items = $items ?? [];

$statusMap = [
    'pending'   => ['label' => 'Chờ xác nhận', 'badge' => 'bg-warning text-dark'],
    'confirmed' => ['label' => 'Đã xác nhận',  'badge' => 'bg-info text-dark'],
    'shipping'  => ['label' => 'Đang giao',     'badge' => 'bg-primary'],
    'delivered' => ['label' => 'Đã giao',       'badge' => 'bg-success'],
    'completed' => ['label' => 'Hoàn thành',    'badge' => 'bg-success'],
    'cancelled' => ['label' => 'Đã huỷ',        'badge' => 'bg-danger'],
];

require_once $_SERVER['DOCUMENT_ROOT'] . '/techgalaxy/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/techgalaxy/includes/sidebar.php';
?>

<div class="admin-content">

  <!-- Topbar -->
  <div class="admin-topbar">
    <div class="d-flex align-items-center gap-3">
      <button class="btn btn-sm btn-outline-secondary d-lg-none border-0" id="sidebarToggle">
        <i class="fa-solid fa-bars"></i>
      </button>
      <span class="admin-topbar-title">
        <i class="fa-solid fa-receipt me-2 text-primary"></i>
        Chi tiết đơn hàng #<?= str_pad((string)$order['id'], 6, '0', STR_PAD_LEFT) ?>
      </span>
    </div>
    <a href="/techgalaxy/admin/orders" class="btn btn-outline-secondary">
  <i class="fa-solid fa-arrow-left me-1"></i>Quay lại
</a>

  </div>

  <div class="admin-main p-4">
  <div class="row g-4">

    <!-- ══ CỘT TRÁI ════════════════════════════════════ -->
    <div class="col-lg-8">

      <!-- Thông tin đơn hàng -->
      <div class="admin-card mb-4">
        <div class="admin-card-header">
          <i class="fa-solid fa-circle-info me-2 text-primary"></i>Thông tin đơn hàng
        </div>
        <div class="card-body p-3">
          <div class="row g-3">
            <div class="col-sm-6">
              <div class="text-muted small mb-1">Mã đơn hàng</div>
              <strong>#<?= str_pad((string)$order['id'], 6, '0', STR_PAD_LEFT) ?></strong>
            </div>
            <div class="col-sm-6">
              <div class="text-muted small mb-1">Ngày đặt</div>
              <strong><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></strong>
            </div>
            <div class="col-sm-6">
              <div class="text-muted small mb-1">Trạng thái</div>
              <span class="badge <?= $statusMap[$order['status']]['badge'] ?? 'bg-secondary' ?>">
                <?= $statusMap[$order['status']]['label'] ?? $order['status'] ?>
              </span>
            </div>
            <div class="col-sm-6">
              <div class="text-muted small mb-1">Thanh toán</div>
              <strong>
                <?= $order['payment_method'] === 'banking' ? 'Chuyển khoản' : 'COD' ?>
              </strong>
            </div>
            <?php if (!empty($order['note'])): ?>
            <div class="col-12">
              <div class="text-muted small mb-1">Ghi chú</div>
              <span><?= nl2br(htmlspecialchars($order['note'])) ?></span>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Thông tin khách hàng -->
      <div class="admin-card mb-4">
        <div class="admin-card-header">
          <i class="fa-solid fa-user me-2 text-primary"></i>Thông tin khách hàng
        </div>
        <div class="card-body p-3">
          <div class="row g-3">
            <div class="col-sm-6">
              <div class="text-muted small mb-1">Họ tên</div>
              <!-- SỬA: user_name thay vì customer_name -->
              <strong><?= htmlspecialchars($order['user_name'] ?? '') ?></strong>
            </div>
            <div class="col-sm-6">
              <div class="text-muted small mb-1">Email</div>
              <!-- SỬA: user_email thay vì customer_email -->
              <strong><?= htmlspecialchars($order['user_email'] ?? '') ?></strong>
            </div>
            <div class="col-sm-6">
              <div class="text-muted small mb-1">Số điện thoại</div>
              <!-- SỬA: user_phone thay vì customer_phone -->
              <strong><?= htmlspecialchars($order['user_phone'] ?? '—') ?></strong>
            </div>
          </div>
        </div>
      </div>

      <!-- Địa chỉ giao hàng -->
      <div class="admin-card mb-4">
        <div class="admin-card-header">
          <i class="fa-solid fa-location-dot me-2 text-primary"></i>Địa chỉ giao hàng
        </div>
        <div class="card-body p-3">
          <?php
          // Schema nhóm không có receiver_name/phone
          // → dùng addr_name/addr_phone từ JOIN addresses
          $addrName  = $order['addr_name']  ?? null;
          $addrPhone = $order['addr_phone'] ?? null;
          $addrFull  = implode(', ', array_filter([
              $order['detail']   ?? '',
              $order['ward']     ?? '',
              $order['district'] ?? '',
              $order['province'] ?? '',
          ]));
          ?>
          <?php if ($addrName || $addrFull): ?>
            <div class="row g-3">
              <div class="col-sm-6">
                <div class="text-muted small mb-1">Người nhận</div>
                <strong><?= htmlspecialchars($addrName ?? '—') ?></strong>
              </div>
              <div class="col-sm-6">
                <div class="text-muted small mb-1">SĐT</div>
                <strong><?= htmlspecialchars($addrPhone ?? '—') ?></strong>
              </div>
              <div class="col-12">
                <div class="text-muted small mb-1">Địa chỉ</div>
                <strong><?= htmlspecialchars($addrFull ?: '—') ?></strong>
              </div>
            </div>
          <?php else: ?>
            <p class="text-muted mb-0">Không có thông tin địa chỉ.</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Sản phẩm trong đơn -->
      <div class="admin-card">
        <div class="admin-card-header">
          <i class="fa-solid fa-box me-2 text-primary"></i>Sản phẩm trong đơn
        </div>
        <div class="table-responsive">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Sản phẩm</th>
                <th class="text-center" style="width:60px">SL</th>
                <th class="text-end"    style="width:120px">Đơn giá</th>
                <th class="text-end"    style="width:120px">Thành tiền</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
              <tr>
                <td>
                  <!-- SỬA: product_name thay vì name -->
                  <div class="fw-semibold">
                    <?= htmlspecialchars($item['product_name']) ?>
                  </div>
                  <?php if (!empty($item['product_sku'])): ?>
                    <small class="text-muted">
                      SKU: <?= htmlspecialchars($item['product_sku']) ?>
                    </small>
                  <?php endif; ?>
                </td>
                <td class="text-center"><?= (int)$item['quantity'] ?></td>
                <td class="text-end"><?= formatPrice($item['price']) ?></td>
                <td class="text-end fw-semibold"><?= formatPrice($item['subtotal']) ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Tổng tiền footer -->
        <div class="p-3" style="border-top:1px solid var(--border);background:#f8fafc">
          <div class="d-flex justify-content-end gap-4 text-sm">
            <?php if ((int)$order['discount'] > 0): ?>
            <div class="text-end">
              <div class="text-muted small">Giảm giá</div>
              <div class="text-success fw-semibold">
                -<?= formatPrice($order['discount']) ?>
              </div>
            </div>
            <?php endif; ?>
            <div class="text-end">
              <div class="text-muted small">Tổng thanh toán</div>
              <div class="text-primary fw-bold fs-5">
                <?= formatPrice($order['total']) ?>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div><!-- /col-lg-8 -->

    <!-- ══ CỘT PHẢI ═════════════════════════════════════ -->
    <div class="col-lg-4">

      <!-- Cập nhật trạng thái -->
      <div class="admin-card mb-4">
        <div class="admin-card-header">
          <i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Cập nhật trạng thái
        </div>
        <div class="card-body p-3">
          <form method="POST"
                action="<?= '/techgalaxy' ?>/admin/orders/<?= (int)$order['id'] ?>/status">
            <input type="hidden" name="csrf_token"
                   value="<?= $_SESSION['csrf_token'] ?? '' ?>">

            <div class="mb-3">
              <label class="form-label fw-semibold small">Trạng thái hiện tại</label>
              <div>
                <span class="badge <?= $statusMap[$order['status']]['badge'] ?? 'bg-secondary' ?> fs-6">
                  <?= $statusMap[$order['status']]['label'] ?? $order['status'] ?>
                </span>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold small">Chuyển sang</label>
              <select name="status" class="form-select form-select-sm">
                <?php foreach ($statusMap as $val => $info): ?>
                  <option value="<?= $val ?>"
                    <?= $order['status'] === $val ? 'selected' : '' ?>>
                    <?= $info['label'] ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <?php if (!in_array($order['status'], ['completed', 'cancelled'])): ?>
              <button type="submit" class="btn btn-primary w-100">
                <i class="fa-solid fa-floppy-disk me-1"></i>Lưu trạng thái
              </button>
            <?php else: ?>
              <div class="alert alert-secondary small py-2 mb-0">
                Đơn hàng đã ở trạng thái cuối, không thể thay đổi.
              </div>
            <?php endif; ?>
          </form>
        </div>
      </div>

      <!-- Tổng quan tài chính -->
      <div class="admin-card">
        <div class="admin-card-header">
          <i class="fa-solid fa-sack-dollar me-2 text-primary"></i>Tài chính
        </div>
        <div class="card-body p-3">
          <ul class="list-unstyled mb-0">
            <li class="d-flex justify-content-between py-2 border-bottom">
              <span class="text-muted small">Tạm tính</span>
              <span><?= formatPrice($order['total'] + (int)$order['discount']) ?></span>
            </li>
            <?php if ((int)$order['discount'] > 0): ?>
            <li class="d-flex justify-content-between py-2 border-bottom text-success">
              <span class="small">Giảm giá</span>
              <span>-<?= formatPrice($order['discount']) ?></span>
            </li>
            <?php endif; ?>
            <li class="d-flex justify-content-between py-2 fw-bold">
              <span>Tổng thanh toán</span>
              <span class="text-primary"><?= formatPrice($order['total']) ?></span>
            </li>
          </ul>
        </div>
      </div>

    </div><!-- /col-lg-4 -->

  </div><!-- /row -->
  </div><!-- /admin-main -->
</div><!-- /admin-content -->

<?php require __DIR__ . '/../../../includes/footer.php'; ?>