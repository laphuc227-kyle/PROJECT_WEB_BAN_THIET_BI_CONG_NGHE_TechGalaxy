<?php
/**
 * views/admin/dashboard.php — Trang tổng quan Admin
 * Variables: $stats, $revenueChart, $latestOrders
 */
require_once __DIR__ . '/../../includes/admin_header.php';
require_once __DIR__ . '/../../includes/admin_sidebar.php';
?>

<div class="admin-content">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 fw-bold mb-0" style="color: var(--text-main);">
      <i class="fa-solid fa-gauge-high me-2 text-primary"></i>Dashboard
    </h1>
    <span class="text-muted small">
      <i class="fa-regular fa-clock me-1"></i>
      <?= date('d/m/Y H:i') ?>
    </span>
  </div>

  <!-- ===== ROW 1: 4 STAT CARDS ===== -->
  <div class="row g-4 mb-4">

    <!-- Card: Đơn hàng hôm nay -->
    <div class="col-xl-3 col-md-6">
      <div class="stat-card rounded-3 p-4 h-100 d-flex justify-content-between align-items-center"
           style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
        <div>
          <p class="text-muted small mb-1">Đơn hàng hôm nay</p>
          <h2 class="h3 fw-bold mb-0" style="color: var(--text-main);">
            <?= number_format($stats['orders_today']) ?>
          </h2>
        </div>
        <div class="rounded-3 d-flex align-items-center justify-content-center"
             style="width: 56px; height: 56px; background: rgba(37,99,235,0.1);">
          <i class="fa-solid fa-cart-shopping fa-xl" style="color: var(--primary);"></i>
        </div>
      </div>
    </div>

    <!-- Card: Doanh thu hôm nay -->
    <div class="col-xl-3 col-md-6">
      <div class="stat-card rounded-3 p-4 h-100 d-flex justify-content-between align-items-center"
           style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
        <div>
          <p class="text-muted small mb-1">Doanh thu hôm nay</p>
          <h2 class="h3 fw-bold mb-0" style="color: var(--text-main);">
            <?= formatPrice($stats['revenue_today']) ?>
          </h2>
        </div>
        <div class="rounded-3 d-flex align-items-center justify-content-center"
             style="width: 56px; height: 56px; background: rgba(16,185,129,0.1);">
          <i class="fa-solid fa-sack-dollar fa-xl" style="color: var(--success);"></i>
        </div>
      </div>
    </div>

    <!-- Card: Khách hàng mới -->
    <div class="col-xl-3 col-md-6">
      <div class="stat-card rounded-3 p-4 h-100 d-flex justify-content-between align-items-center"
           style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
        <div>
          <p class="text-muted small mb-1">Khách hàng mới (tuần)</p>
          <h2 class="h3 fw-bold mb-0" style="color: var(--text-main);">
            <?= number_format($stats['new_customers']) ?>
          </h2>
        </div>
        <div class="rounded-3 d-flex align-items-center justify-content-center"
             style="width: 56px; height: 56px; background: rgba(245,158,11,0.1);">
          <i class="fa-solid fa-users fa-xl" style="color: var(--accent);"></i>
        </div>
      </div>
    </div>

    <!-- Card: Sản phẩm sắp hết -->
    <div class="col-xl-3 col-md-6">
      <div class="stat-card rounded-3 p-4 h-100 d-flex justify-content-between align-items-center"
           style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
        <div>
          <p class="text-muted small mb-1">Sản phẩm sắp hết kho</p>
          <h2 class="h3 fw-bold mb-0" style="color: <?= $stats['low_stock'] > 0 ? 'var(--danger)' : 'var(--success)' ?>;">
            <?= number_format($stats['low_stock']) ?>
          </h2>
        </div>
        <div class="rounded-3 d-flex align-items-center justify-content-center"
             style="width: 56px; height: 56px; background: rgba(239,68,68,0.1);">
          <i class="fa-solid fa-triangle-exclamation fa-xl" style="color: var(--danger);"></i>
        </div>
      </div>
    </div>

  </div>

  <!-- ===== ROW 2: CHART + LATEST ORDERS ===== -->
  <div class="row g-4">

    <!-- Line chart doanh thu 7 ngày -->
    <div class="col-lg-7">
      <div class="rounded-3 p-4 h-100"
           style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
        <h2 class="h6 fw-bold mb-4" style="color: var(--text-main);">
          <i class="fa-solid fa-chart-line me-2 text-primary"></i>Doanh thu 7 ngày gần nhất
        </h2>
        <canvas id="revenueChart" height="100"></canvas>
      </div>
    </div>

    <!-- Bảng 5 đơn hàng mới nhất -->
    <div class="col-lg-5">
      <div class="rounded-3 p-4 h-100"
           style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h2 class="h6 fw-bold mb-0" style="color: var(--text-main);">
            <i class="fa-solid fa-receipt me-2 text-primary"></i>Đơn hàng mới nhất
          </h2>
          <a href="<?= BASE_URL ?>/admin/orders" class="text-primary small text-decoration-none">
            Xem tất cả <i class="fa-solid fa-arrow-right ms-1"></i>
          </a>
        </div>

        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0" style="font-size: 0.875rem;">
            <thead style="background: var(--bg-light);">
              <tr>
                <th class="border-0 fw-semibold py-2">Mã đơn</th>
                <th class="border-0 fw-semibold py-2">Khách hàng</th>
                <th class="border-0 fw-semibold py-2">Tổng</th>
                <th class="border-0 fw-semibold py-2">Trạng thái</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($latestOrders)): ?>
              <tr>
                <td colspan="4" class="text-center text-muted py-3">Chưa có đơn hàng</td>
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
                foreach ($latestOrders as $order):
                  $st = $statusMap[$order['status']] ?? ['label' => $order['status'], 'class' => 'secondary'];
                ?>
                <tr>
                  <td>
                    <a href="<?= BASE_URL ?>/admin/orders/<?= $order['id'] ?>"
                       class="text-decoration-none fw-medium text-primary">
                      #<?= $order['id'] ?>
                    </a>
                  </td>
                  <td class="text-truncate" style="max-width: 120px;">
                    <?= htmlspecialchars($order['customer_name'] ?? '—') ?>
                  </td>
                  <td class="fw-medium"><?= formatPrice((float) $order['total']) ?></td>
                  <td>
                    <span class="badge text-bg-<?= $st['class'] ?> rounded-pill"
                          style="font-size: 0.7rem;">
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

<!-- ===== Chart.js ===== -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
  const labels = <?= json_encode($revenueChart['labels']) ?>;
  const data   = <?= json_encode($revenueChart['data']) ?>;

  const ctx = document.getElementById('revenueChart').getContext('2d');
  new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Doanh thu (₫)',
        data,
        borderColor: '#2563EB',
        backgroundColor: 'rgba(37,99,235,0.08)',
        borderWidth: 2.5,
        pointBackgroundColor: '#2563EB',
        pointRadius: 4,
        tension: 0.35,
        fill: true,
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (ctx) => ' ' + new Intl.NumberFormat('vi-VN').format(ctx.parsed.y) + ' ₫'
          }
        }
      },
      scales: {
        x: { grid: { display: false } },
        y: {
          beginAtZero: true,
          ticks: {
            callback: (val) => new Intl.NumberFormat('vi-VN', { notation: 'compact' }).format(val) + ' ₫'
          }
        }
      }
    }
  });
})();
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>