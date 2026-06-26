<?php
/**
 * views/admin/reports/index.php — Báo cáo doanh thu
 * Variables: $fromDate, $toDate, $summary, $ordersByStatus, $topProducts, $topCustomers
 */
require_once __DIR__ . '/../../../includes/admin_header.php';
require_once __DIR__ . '/../../../includes/admin_sidebar.php';
?>

<div class="admin-content">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <h1 class="h4 fw-bold mb-0" style="color: var(--text-main);">
      <i class="fa-solid fa-chart-bar me-2 text-primary"></i>Báo cáo doanh thu
    </h1>

    <!-- Export CSV -->
    <a href="<?= BASE_URL ?>/admin/reports/export?from=<?= urlencode($fromDate) ?>&to=<?= urlencode($toDate) ?>"
       class="btn btn-outline-success">
      <i class="fa-solid fa-file-csv me-1"></i> Xuất CSV
    </a>
  </div>

  <!-- ===== BỘ LỌC ===== -->
  <form method="GET" action="<?= BASE_URL ?>/admin/reports" class="mb-4">
    <div class="rounded-3 p-3"
         style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
      <div class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label fw-semibold small">Từ ngày</label>
          <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($fromDate) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold small">Đến ngày</label>
          <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($toDate) ?>">
        </div>
        <div class="col-md-4">
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">
              <i class="fa-solid fa-filter me-1"></i> Lọc
            </button>
            <!-- Shortcut buttons -->
            <a href="?from=<?= date('Y-m-01') ?>&to=<?= date('Y-m-d') ?>"
               class="btn btn-outline-secondary btn-sm d-flex align-items-center">Tháng này</a>
            <a href="?from=<?= date('Y-01-01') ?>&to=<?= date('Y-12-31') ?>"
               class="btn btn-outline-secondary btn-sm d-flex align-items-center">Năm này</a>
          </div>
        </div>
      </div>
    </div>
  </form>

  <!-- ===== SUMMARY CARDS ===== -->
  <div class="row g-4 mb-4">
    <div class="col-md-6">
      <div class="rounded-3 p-4 text-center"
           style="background: linear-gradient(135deg, #2563EB, #1D4ED8); color: #fff; box-shadow: var(--shadow);">
        <p class="mb-1 opacity-75 small">Tổng doanh thu kỳ này</p>
        <h2 class="display-6 fw-bold mb-0"><?= formatPrice((float) ($summary['revenue'] ?? 0)) ?></h2>
        <p class="mt-1 mb-0 opacity-75 small">Chỉ tính đơn "Hoàn thành"</p>
      </div>
    </div>
    <div class="col-md-6">
      <div class="rounded-3 p-4 text-center"
           style="background: linear-gradient(135deg, #10B981, #059669); color: #fff; box-shadow: var(--shadow);">
        <p class="mb-1 opacity-75 small">Số đơn hàng hoàn thành</p>
        <h2 class="display-6 fw-bold mb-0"><?= number_format((int) ($summary['order_count'] ?? 0)) ?></h2>
        <p class="mt-1 mb-0 opacity-75 small">
          Kỳ: <?= date('d/m/Y', strtotime($fromDate)) ?> → <?= date('d/m/Y', strtotime($toDate)) ?>
        </p>
      </div>
    </div>
  </div>

  <!-- ===== CHARTS ROW ===== -->
  <div class="row g-4 mb-4">

    <!-- Pie chart: Đơn hàng theo trạng thái -->
    <div class="col-lg-5">
      <div class="rounded-3 p-4 h-100"
           style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
        <h2 class="h6 fw-bold mb-4" style="color: var(--text-main);">
          <i class="fa-solid fa-chart-pie me-2 text-primary"></i>Đơn hàng theo trạng thái
        </h2>
        <?php if (empty($ordersByStatus['data'])): ?>
          <p class="text-muted text-center py-4">Không có dữ liệu trong kỳ này.</p>
        <?php else: ?>
          <canvas id="pieChart"></canvas>
        <?php endif; ?>
      </div>
    </div>

    <!-- Bar chart: Top sản phẩm -->
    <div class="col-lg-7">
      <div class="rounded-3 p-4 h-100"
           style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
        <h2 class="h6 fw-bold mb-4" style="color: var(--text-main);">
          <i class="fa-solid fa-trophy me-2 text-primary"></i>Top 5 sản phẩm bán chạy
        </h2>
        <?php if (empty($topProducts)): ?>
          <p class="text-muted text-center py-4">Không có dữ liệu trong kỳ này.</p>
        <?php else: ?>
          <canvas id="barChart"></canvas>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <!-- ===== TOP CUSTOMERS ===== -->
  <div class="rounded-3 overflow-hidden"
       style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
    <div class="p-4 d-flex justify-content-between align-items-center"
         style="border-bottom: 1px solid var(--border);">
      <h2 class="h6 fw-bold mb-0" style="color: var(--text-main);">
        <i class="fa-solid fa-crown me-2 text-primary"></i>Top 5 khách hàng mua nhiều
      </h2>
    </div>

    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead style="background: var(--bg-light);">
          <tr>
            <th class="py-3 ps-4" style="width: 50px;">Hạng</th>
            <th class="py-3">Khách hàng</th>
            <th class="py-3">Email</th>
            <th class="py-3">Số đơn</th>
            <th class="py-3 pe-4">Tổng chi tiêu</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($topCustomers)): ?>
          <tr>
            <td colspan="5" class="text-center text-muted py-4">Không có dữ liệu.</td>
          </tr>
          <?php else: ?>
            <?php
            $medals = ['🥇', '🥈', '🥉', '4.', '5.'];
            foreach ($topCustomers as $i => $cust):
            ?>
            <tr>
              <td class="ps-4 text-center fw-bold"><?= $medals[$i] ?? ($i + 1 . '.') ?></td>
              <td class="fw-semibold"><?= htmlspecialchars($cust['name']) ?></td>
              <td class="text-muted small"><?= htmlspecialchars($cust['email']) ?></td>
              <td><?= number_format((int) $cust['order_count']) ?> đơn</td>
              <td class="pe-4 fw-bold" style="color: var(--primary);">
                <?= formatPrice((float) $cust['total_spent']) ?>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
<?php if (!empty($ordersByStatus['data'])): ?>
// Pie chart
new Chart(document.getElementById('pieChart'), {
  type: 'doughnut',
  data: {
    labels: <?= json_encode($ordersByStatus['labels']) ?>,
    datasets: [{
      data: <?= json_encode($ordersByStatus['data']) ?>,
      backgroundColor: ['#F59E0B','#3B82F6','#8B5CF6','#10B981','#2563EB','#EF4444'],
      borderWidth: 2,
      borderColor: '#fff',
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'bottom' }
    }
  }
});
<?php endif; ?>

<?php if (!empty($topProducts)): ?>
// Bar chart
new Chart(document.getElementById('barChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_column($topProducts, 'name')) ?>,
    datasets: [{
      label: 'Đã bán (sản phẩm)',
      data: <?= json_encode(array_column($topProducts, 'total_sold')) ?>,
      backgroundColor: 'rgba(37,99,235,0.75)',
      borderRadius: 6,
      borderSkipped: false,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { display: false } },
      y: { beginAtZero: true, ticks: { stepSize: 1 } }
    }
  }
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../../../includes/admin_footer.php'; ?>