<?php
/**
 * views/admin/customers/index.php — Danh sách khách hàng
 * Variables: $customers, $total, $totalPages, $currentPage, $filter, $search
 */
require_once __DIR__ . '/../../../includes/admin_header.php';
require_once __DIR__ . '/../../../includes/admin_sidebar.php';
?>

<div class="admin-content">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 fw-bold mb-0" style="color: var(--text-main);">
      <i class="fa-solid fa-users me-2 text-primary"></i>Quản lý khách hàng
      <span class="badge text-bg-secondary ms-2 align-middle" style="font-size: 0.75rem;">
        <?= number_format($total) ?>
      </span>
    </h1>
  </div>

  <?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show mb-4">
        <?= htmlspecialchars($_SESSION['flash_message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
<?php endif; ?>

  <!-- Filters + Search -->
  <form method="GET" action="<?= BASE_URL ?>/admin/customers" class="mb-4">
    <div class="d-flex flex-wrap gap-2 align-items-center">
      <!-- Tab filter -->
      <div class="btn-group">
        <?php
        $filters = ['all' => 'Tất cả', 'active' => 'Đang hoạt động', 'blocked' => 'Đã chặn'];
        foreach ($filters as $key => $label):
        ?>
        <a href="?filter=<?= $key ?>&search=<?= urlencode($search ?? '') ?>"
           class="btn btn-sm <?= ($filter ?? 'all') === $key ? 'btn-primary' : 'btn-outline-secondary' ?>">
          <?= $label ?>
        </a>
        <?php endforeach; ?>
      </div>

      <!-- Search -->
      <div class="input-group ms-auto" style="max-width: 280px;">
        <input type="hidden" name="filter" value="<?= htmlspecialchars($filter ?? 'all') ?>">
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="Tên hoặc email..."
               value="<?= htmlspecialchars($search ?? '') ?>">
        <button class="btn btn-sm btn-outline-primary" type="submit">
          <i class="fa-solid fa-magnifying-glass"></i>
        </button>
      </div>
    </div>
  </form>

  <!-- Table -->
  <div class="rounded-3 overflow-hidden"
       style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead style="background: var(--bg-light);">
          <tr>
            <th class="py-3 ps-4" style="width: 50px;">STT</th>
            <th class="py-3">Avatar</th>
            <th class="py-3">Tên</th>
            <th class="py-3">Email</th>
            <th class="py-3">SĐT</th>
            <th class="py-3">Ngày đăng ký</th>
            <th class="py-3 text-center">Số đơn</th>
            <th class="py-3 text-center">Trạng thái</th>
            <th class="py-3 pe-4 text-end">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($customers)): ?>
          <tr>
            <td colspan="9" class="text-center text-muted py-5">
              <i class="fa-solid fa-users fa-2x mb-2 d-block"></i>
              Không tìm thấy khách hàng nào.
            </td>
          </tr>
          <?php else: ?>
            <?php foreach ($customers as $i => $cust): ?>
            <tr>
              <td class="ps-4 text-muted small">
                <?= ($currentPage - 1) * 15 + $i + 1 ?>
              </td>
              <td>
                <?php if (!empty($cust['avatar'])): ?>
                  <img src="<?= BASE_URL . '/public/uploads/' . htmlspecialchars($cust['avatar']) ?>"
                       alt="<?= htmlspecialchars($cust['name']) ?>"
                       class="rounded-circle"
                       style="width: 38px; height: 38px; object-fit: cover;">
                <?php else: ?>
                  <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                       style="width: 38px; height: 38px; background: var(--primary); font-size: 0.9rem;">
                    <?= mb_strtoupper(mb_substr($cust['name'], 0, 1)) ?>
                  </div>
                <?php endif; ?>
              </td>
              <td>
                <a href="<?= BASE_URL ?>/admin/customers/<?= $cust['id'] ?>"
                   class="fw-semibold text-decoration-none" style="color: var(--text-main);">
                  <?= htmlspecialchars($cust['name']) ?>
                </a>
              </td>
              <td class="text-muted small"><?= htmlspecialchars($cust['email']) ?></td>
              <td class="text-muted small"><?= htmlspecialchars($cust['phone'] ?? '—') ?></td>
              <td class="text-muted small">
                <?= date('d/m/Y', strtotime($cust['created_at'])) ?>
              </td>
              <td class="text-center fw-semibold"><?= number_format((int) $cust['order_count']) ?></td>
              <td class="text-center">
                <span class="badge text-bg-<?= $cust['status'] ? 'success' : 'danger' ?> rounded-pill">
                  <?= $cust['status'] ? 'Hoạt động' : 'Đã chặn' ?>
                </span>
              </td>
              <td class="pe-4 text-end">
                <a href="/techgalaxy/admin/customers/<?= $cust['id'] ?>"
                   class="btn btn-sm btn-outline-primary me-1"
                   title="Xem chi tiết">
                  <i class="fa-solid fa-eye"></i>
                </a>
                <form method="POST"
                      action="/techgalaxy/admin/customers/<?= $cust['id'] ?>/toggle"
                      class="d-inline"
                      onsubmit="return confirm('<?= $cust['status'] ? 'Chặn' : 'Mở chặn' ?> người dùng này?')">
                  <input type="hidden" name="csrf_token" value="<?= generateToken() ?>">
                  <button type="submit"
                          class="btn btn-sm <?= $cust['status'] ? 'btn-outline-danger' : 'btn-outline-success' ?>"
                          title="<?= $cust['status'] ? 'Chặn tài khoản' : 'Mở chặn' ?>">
                    <i class="fa-solid <?= $cust['status'] ? 'fa-ban' : 'fa-circle-check' ?>"></i>
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
    <?php if ($totalPages > 1): ?>
    <div class="d-flex justify-content-between align-items-center px-4 py-3"
         style="border-top: 1px solid var(--border); background: var(--bg-light);">
      <span class="text-muted small">
        Tổng <?= number_format($total) ?> khách hàng
      </span>
      <nav>
        <ul class="pagination pagination-sm mb-0 gap-1">
          <?php for ($p = 1; $p <= $totalPages; $p++): ?>
          <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
            <a class="page-link rounded-2"
               href="?page=<?= $p ?>&filter=<?= urlencode($filter ?? 'all') ?>&search=<?= urlencode($search ?? '') ?>">
              <?= $p ?>
            </a>
          </li>
          <?php endfor; ?>
        </ul>
      </nav>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../../../includes/admin_footer.php'; ?>