<?php
// File: views/admin/orders/index.php
// Biến nhận từ OrderController::adminIndex():
//   $orders  array   — danh sách đơn hàng (kèm user_name, user_email từ JOIN)
//   $status  string  — filter hiện tại ('' = tất cả)

$pageTitle = 'Quản lý đơn hàng — ' . (defined('APP_NAME') ? APP_NAME : 'TechGalaxy');
$adminPage = 'orders';
$extraJS   = '';

// Map trạng thái
$statusMap = [
    ''          => ['label' => 'Tất cả',        'btn' => 'btn-secondary'],
    'pending'   => ['label' => 'Chờ xác nhận',  'btn' => 'btn-warning'],
    'confirmed' => ['label' => 'Đã xác nhận',   'btn' => 'btn-info'],
    'shipping'  => ['label' => 'Đang giao',      'btn' => 'btn-primary'],
    'delivered' => ['label' => 'Đã giao',        'btn' => 'btn-success'],
    'completed' => ['label' => 'Hoàn thành',     'btn' => 'btn-success'],
    'cancelled' => ['label' => 'Đã huỷ',         'btn' => 'btn-danger'],
];

$statusBadge = [
    'pending'   => 'bg-warning text-dark',
    'confirmed' => 'bg-info text-dark',
    'shipping'  => 'bg-primary',
    'delivered' => 'bg-success',
    'completed' => 'bg-success',
    'cancelled' => 'bg-danger',
];

// Helper: format price for display (avoid undefined function error)
if (!function_exists('formatPrice')) {
  function formatPrice($amount)
  {
    // If amount is null/empty, show 0
    $amount = $amount ?? 0;
    return number_format((float)$amount, 0, ',', '.') . ' ₫';
  }
}

require __DIR__ . '/../../../includes/admin_header.php';
require __DIR__ . '/../../../includes/admin_sidebar.php';
?>

<?php

$orders = $orders ?? [];

$status = $status ?? '';

$stats = $stats ?? [
    'total' => 0,
    'pending' => 0,
    'shipping' => 0,
    'completed' => 0
];

?>

<!-- ══ NỘI DUNG ADMIN ════════════════════════════════════ -->
<div class="admin-content">

  <!-- Topbar -->
  <div class="admin-topbar">
    <div class="d-flex align-items-center gap-3">
      <!-- Toggle sidebar mobile -->
      <button class="btn btn-sm btn-outline-secondary d-lg-none border-0"
              id="sidebarToggle">
        <i class="fa-solid fa-bars"></i>
      </button>
      <span class="admin-topbar-title">
        <i class="fa-solid fa-receipt me-2 text-primary"></i>Quản lý đơn hàng
      </span>
    </div>
    <div class="d-flex align-items-center gap-2">
      <span class="text-muted small">
        <i class="fa-regular fa-clock me-1"></i><?= date('d/m/Y H:i') ?>
      </span>
      <a href="<?= '/techgalaxy' ?>/" target="_blank"
         class="btn btn-sm btn-outline-secondary">
        <i class="fa-solid fa-arrow-up-right-from-square"></i>
      </a>
    </div>
  </div>

  <!-- Main -->
  <div class="admin-main p-4">

    <!-- Page header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="admin-page-title mb-1">
          Đơn hàng
          <span class="badge text-bg-secondary ms-2 align-middle"
                style="font-size:.75rem">
            <?= count($orders) ?>
          </span>
        </h4>
        <p class="text-muted mb-0 small">Quản lý và cập nhật trạng thái đơn hàng</p>
      </div>
    </div>

    <!-- ── Filter tabs theo trạng thái ────────────────── -->
    <div class="d-flex flex-wrap gap-2 mb-4">
      <?php foreach ($statusMap as $key => $info): ?>
        <a href="<?= '/techgalaxy' ?>/admin/orders<?= $key ? '?status=' . $key : '' ?>"
           class="btn btn-sm <?= $status === $key
             ? str_replace('btn-', 'btn-', $info['btn'])
             : 'btn-outline-secondary' ?>">
          <?= $info['label'] ?>
          <?php if ($key === $status): ?>
            <span class="badge bg-white text-dark ms-1"><?= count($orders) ?></span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- ── Bảng đơn hàng ──────────────────────────────── -->
    <div class="admin-card">
      <div class="admin-card-header d-flex justify-content-between align-items-center">
        <span>
          <i class="fa-solid fa-list-ul me-2"></i>
          <?= $statusMap[$status]['label'] ?? 'Tất cả' ?>
        </span>
        <!-- Tìm nhanh client-side -->
        <input type="text" id="orderSearch"
               class="form-control form-control-sm"
               placeholder="Tìm mã đơn, khách hàng..."
               style="max-width:240px">
      </div>

      <div class="table-responsive">
        <table class="admin-table" id="orderTable">
          <thead>
            <tr>
              <th style="width:70px">Mã đơn</th>
              <th>Khách hàng</th>
              <th class="text-end" style="width:130px">Tổng tiền</th>
              <th class="text-center" style="width:120px">Thanh toán</th>
              <th class="text-center" style="width:130px">Trạng thái</th>
              <th class="text-center" style="width:120px">Ngày đặt</th>
              <th class="text-center" style="width:120px">Thao tác</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($orders)): ?>
            <tr>
              <td colspan="7" class="text-center text-muted py-5">
                <i class="fa-solid fa-inbox fa-2x d-block mb-2 opacity-50"></i>
                Không có đơn hàng nào.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($orders as $order): ?>
            <tr>
              <!-- Mã đơn -->
              <td>
                <a href="<?= '/techgalaxy' ?>/admin/orders/<?= (int)$order['id'] ?>"
                   class="fw-bold text-primary text-decoration-none">
                  #<?= (int)$order['id'] ?>
                </a>
              </td>

              <!-- Khách hàng -->
              <td>
                <div class="fw-semibold" style="color:var(--text-main)">
                  <?= htmlspecialchars($order['user_name']) ?>
                </div>
                <div class="text-muted small">
                  <?= htmlspecialchars($order['user_email']) ?>
                </div>
              </td>

              <!-- Tổng tiền -->
              <td class="text-end fw-semibold">
                <?= formatPrice($order['total']) ?>
                <?php if ((int)$order['discount'] > 0): ?>
                  <div class="text-success small">
                    -<?= formatPrice($order['discount']) ?>
                  </div>
                <?php endif; ?>
              </td>

              <!-- Phương thức thanh toán -->
              <td class="text-center">
                <?php if ($order['payment_method'] === 'banking'): ?>
                  <span class="badge bg-light text-dark border">
                    <i class="fa-solid fa-building-columns me-1"></i>Chuyển khoản
                  </span>
                <?php else: ?>
                  <span class="badge bg-light text-dark border">
                    <i class="fa-solid fa-money-bill-wave me-1"></i>COD
                  </span>
                <?php endif; ?>
              </td>

              <!-- Trạng thái -->
              <td class="text-center">
                <span class="badge <?= $statusBadge[$order['status']] ?? 'bg-secondary' ?> rounded-pill">
                  <?= $statusMap[$order['status']]['label'] ?? $order['status'] ?>
                </span>
              </td>

              <!-- Ngày đặt -->
              <td class="text-center text-muted small">
                <?= date('d/m/Y', strtotime($order['created_at'])) ?>
                <div><?= date('H:i', strtotime($order['created_at'])) ?></div>
              </td>

              <!-- Thao tác -->
              <td class="text-center">
    <a href="/techgalaxy/admin/orders/<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary" title="Xem">
        <i class="fa-solid fa-eye"></i>
    </a>

    <?php if (!in_array($order['status'], ['completed', 'cancelled'])): ?>
        <select class="form-select form-select-sm d-inline-block w-auto ms-1" 
                onchange="updateOrderStatus(<?= $order['id'] ?>, this.value)">
            <option value="" selected disabled>Đổi trạng thái...</option> <option value="pending">Chờ xác nhận</option>
            <option value="confirmed">Đã xác nhận</option>
            <option value="shipping">Đang giao</option>
            <option value="delivered">Đã giao</option>
        </select>
    <?php endif; ?>
</td>

                <!-- Đổi trạng thái (dropdown) -->
                <?php if (!in_array($order['status'], ['completed', 'cancelled'])): ?>
                <div class="dropdown d-inline">
                  <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                          data-bs-toggle="dropdown"
                          title="Đổi trạng thái">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <?php
                    // Chỉ hiện các trạng thái tiếp theo hợp lệ
                    $nextStatus = [
                        'pending'   => ['confirmed' => 'Xác nhận đơn'],
                        'confirmed' => ['shipping'  => 'Bắt đầu giao'],
                        'shipping'  => ['delivered' => 'Đã giao hàng'],
                        'delivered' => ['completed' => 'Hoàn thành'],
                    ];
                    $canCancel = in_array($order['status'], ['pending', 'confirmed']);

                    foreach ($nextStatus[$order['status']] ?? [] as $val => $label):
                    ?>
                      <li>
                        <button class="dropdown-item status-btn"
                                data-order-id="<?= (int)$order['id'] ?>"
                                data-status="<?= $val ?>">
                          <i class="fa-solid fa-circle-check text-success me-2"></i>
                          <?= $label ?>
                        </button>
                      </li>
                    <?php endforeach; ?>

                    <?php if ($canCancel): ?>
                      <?php if (!empty($nextStatus[$order['status']])): ?>
                        <li><hr class="dropdown-divider"></li>
                      <?php endif; ?>
                      <li>
                        <button class="dropdown-item text-danger status-btn"
                                data-order-id="<?= (int)$order['id'] ?>"
                                data-status="cancelled">
                          <i class="fa-solid fa-ban me-2"></i>Huỷ đơn hàng
                        </button>
                      </li>
                    <?php endif; ?>
                  </ul>
                </div>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div><!-- /table-responsive -->
    </div><!-- /admin-card -->

  </div><!-- /admin-main -->
</div><!-- /admin-content -->

<?php
$csrf = $_SESSION['csrf_token'] ?? '';
ob_start();
?>
<script>
const CSRF     = '<?= $csrf ?>';
const BASE_URL = '/techgalaxy';
// ── Tìm nhanh client-side ─────────────────────────────
document.getElementById('orderSearch')?.addEventListener('input', function () {
  const q = this.value.toLowerCase();
  document.querySelectorAll('#orderTable tbody tr').forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});

// ── Đổi trạng thái (AJAX POST) ───────────────────────
document.querySelectorAll('.status-btn').forEach(btn => {
  btn.addEventListener('click', async function () {
    const orderId = this.dataset.orderId;
    const status  = this.dataset.status;

    const statusLabel = {
      confirmed: 'Xác nhận đơn hàng',
      shipping:  'Chuyển sang đang giao',
      delivered: 'Đánh dấu đã giao',
      completed: 'Hoàn thành đơn hàng',
      cancelled: 'Huỷ đơn hàng',
    };

    const cf = await Swal.fire({
      title:   statusLabel[status] + '?',
      text:    'Đơn hàng #' + orderId,
      icon:    status === 'cancelled' ? 'warning' : 'question',
      showCancelButton:   true,
      confirmButtonColor: status === 'cancelled' ? '#dc3545' : '#2563eb',
      confirmButtonText:  'Xác nhận',
      cancelButtonText:   'Huỷ',
    });
    if (!cf.isConfirmed) return;

    try {
      const fd = new FormData();
      fd.append('status',     status);
      fd.append('csrf_token', CSRF);

      const r = await fetch(
        BASE_URL + '/admin/orders/' + orderId + '/status',
        { method: 'POST', body: fd }
      );

      if (r.redirected || r.ok) {
        // Reload để cập nhật badge và dòng trong bảng
        Swal.fire({
          toast: true, position: 'top-end', icon: 'success',
          title: 'Cập nhật thành công!',
          showConfirmButton: false, timer: 1500,
        }).then(() => location.reload());
      } else {
        throw new Error('Server error');
      }
    } catch (e) {
      Swal.fire('Lỗi', 'Không thể cập nhật trạng thái. Vui lòng thử lại.', 'error');
    }
  });
});
function updateOrderStatus(orderId, newStatus) {
    if (!confirm('Bạn có chắc muốn đổi trạng thái đơn #' + orderId + ' không?')) {
        location.reload(); // Huỷ thì load lại để chọn đúng status cũ
        return;
    }

    fetch('/techgalaxy/admin/orders/' + orderId + '/status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'status=' + newStatus + '&csrf_token=<?= $_SESSION['csrf_token'] ?? '' ?>'
    })
    .then(response => {
        if (response.ok) {
            alert('Cập nhật thành công!');
            location.reload(); // Load lại để cập nhật màu Badge
        } else {
            alert('Lỗi: Không thể cập nhật trạng thái.');
        }
    });
}
</script>
<?php
$extraJS = ob_get_clean();
