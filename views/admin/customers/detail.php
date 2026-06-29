<?php
/**
 * views/admin/customers/detail.php — Chi tiết khách hàng
 * Variables: $customer, $orders
 */
require_once __DIR__ . '/../../../includes/admin_header.php';
require_once __DIR__ . '/../../../includes/admin_sidebar.php';
?>

<div class="admin-content">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 fw-bold mb-0" style="color: var(--text-main);">
      <i class="fa-solid fa-user me-2 text-primary"></i>Chi tiết khách hàng
    </h1>
    <a href="/techgalaxy/admin/customers" class="btn btn-outline-secondary">
      <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
    </a>
  </div>

  <?php if (isset($_SESSION['flash_message'])): ?>
      <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show">
          <?= $_SESSION['flash_message'] ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
  <?php endif; ?>

  <div class="row g-4">
    <div class="col-lg-4">
      <div class="rounded-3 p-4 text-center"
           style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
        <?php if (!empty($customer['avatar'])): ?>
          <img src="/public/uploads/<?= htmlspecialchars($customer['avatar']) ?>"
               alt="<?= htmlspecialchars($customer['name']) ?>"
               class="rounded-circle mb-3"
               style="width: 90px; height: 90px; object-fit: cover; border: 3px solid var(--border);">
        <?php else: ?>
          <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold mx-auto mb-3"
               style="width: 90px; height: 90px; background: var(--primary); font-size: 2rem;">
            <?= mb_strtoupper(mb_substr($customer['name'], 0, 1)) ?>
          </div>
        <?php endif; ?>

        <h2 class="h5 fw-bold mb-1" style="color: var(--text-main);">
          <?= htmlspecialchars($customer['name']) ?>
        </h2>
        <p class="text-muted small mb-3"><?= htmlspecialchars($customer['email']) ?></p>

        <span class="badge text-bg-<?= $customer['status'] ? 'success' : 'danger' ?> rounded-pill px-3 py-2">
          <?= $customer['status'] ? 'Đang hoạt động' : 'Đã bị chặn' ?>
        </span>

        <hr style="border-color: var(--border);">

        <div class="text-start">
          <div class="mb-2 d-flex gap-2">
            <i class="fa-solid fa-phone text-muted mt-1" style="width: 16px;"></i>
            <span class="small"><?= htmlspecialchars($customer['phone'] ?? 'Chưa cập nhật') ?></span>
          </div>
          <div class="mb-2 d-flex gap-2">
            <i class="fa-solid fa-calendar-plus text-muted mt-1" style="width: 16px;"></i>
            <span class="small">Đăng ký: <?= date('d/m/Y', strtotime($customer['created_at'])) ?></span>
          </div>
          <div class="d-flex gap-2">
            <i class="fa-solid fa-cart-shopping text-muted mt-1" style="width: 16px;"></i>
            <span class="small"><?= count($orders) ?> đơn hàng</span>
          </div>
        </div>

        <div class="mt-4">
          <form method="POST"
                action="/techgalaxy/admin/customers/<?= $customer['id'] ?>/toggle"
                onsubmit="return confirm('<?= $customer['status'] ? 'Chặn' : 'Mở chặn' ?> người dùng này?')">
            
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            
            <button type="submit"
                    class="btn btn-sm w-100 <?= $customer['status'] ? 'btn-outline-danger' : 'btn-outline-success' ?>">
              <i class="fa-solid <?= $customer['status'] ? 'fa-ban' : 'fa-circle-check' ?> me-1"></i>
              <?= $customer['status'] ? 'Chặn tài khoản' : 'Mở chặn tài khoản' ?>
            </button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="rounded-3 overflow-hidden"
           style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
        <div class="p-4" style="border-bottom: 1px solid var(--border);">
          <h2 class="h6 fw-bold mb-0" style="color: var(--text-main);">
            <i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Lịch sử mua hàng (10 đơn gần nhất)
          </h2>
        </div>

        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead style="background: var(--bg-light);">
              <tr>
                <th class="py-3 ps-4">Mã đơn</th>
                <th class="py-3">Ngày đặt</th>
                <th class="py-3">Tổng tiền</th>
                <th class="py-3">Thanh toán</th>
                <th class="py-3 pe-4">Trạng thái</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($orders)): ?>
              <tr>
                <td colspan="5" class="text-center text-muted py-4">Chưa có đơn hàng nào.</td>
              </tr>
              <?php else: ?>
                <?php
                $statusMap = [
                  'pending'   => ['label' => 'Chờ xử lý',  'class' => 'warning'],
                  'confirmed' => ['label' => 'Xác nhận',   'class' => 'info'],
                  'shipping'  => ['label' => 'Đang giao',  'class' => 'primary'],
                  'delivered' => ['label' => 'Đã giao',    'class' => 'success'],
                  'completed' => ['label' => 'Hoàn thành', 'class' => 'success'],
                  'cancelled' => ['label' => 'Đã huỷ',     'class' => 'danger'],
                ];
                foreach ($orders as $order):
                  $st = $statusMap[$order['status']] ?? ['label' => $order['status'], 'class' => 'secondary'];
                ?>
                <tr>
                  <td class="ps-4">
                    <a href="/admin/orders/<?= $order['id'] ?>"
                       class="fw-semibold text-primary text-decoration-none">
                      #<?= $order['id'] ?>
                    </a>
                  </td>
                  <td class="text-muted small">
                    <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                  </td>
                  <td class="fw-semibold"><?= formatPrice((float) $order['total']) ?></td>
                  <td class="text-muted small">
                    <?= $order['payment_method'] === 'cod' ? 'COD' : 'Chuyển khoản' ?>
                  </td>
                  <td class="pe-4">
                    <span class="badge text-bg-<?= $st['class'] ?> rounded-pill">
                      <?= $st['label'] ?>
                    </span>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../../includes/admin_footer.php'; ?>