<?php
/**
 * views/admin/posts/index.php — Danh sách bài viết (Admin)
 * Variables: $paginated (items, total, currentPage, totalPages, perPage), $search
 */
require_once __DIR__ . '/../../../includes/admin_header.php';
require_once __DIR__ . '/../../../includes/admin_sidebar.php';
$posts = $paginated['items'];
?>

<div class="admin-content">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 fw-bold mb-0" style="color: var(--text-main);">
      <i class="fa-solid fa-newspaper me-2 text-primary"></i>Quản lý bài viết
    </h1>
    <a href="<?= BASE_URL ?>/admin/posts/create" class="btn btn-primary">
      <i class="fa-solid fa-plus me-1"></i> Thêm bài viết
    </a>
  </div>

  <?= getFlash() ?>

  <!-- Search -->
  <form method="GET" action="<?= BASE_URL ?>/admin/posts" class="mb-4">
    <div class="input-group" style="max-width: 400px;">
      <input type="text" name="search" class="form-control"
             placeholder="Tìm kiếm theo tiêu đề..."
             value="<?= htmlspecialchars($search ?? '') ?>">
      <button class="btn btn-outline-primary" type="submit">
        <i class="fa-solid fa-magnifying-glass"></i>
      </button>
      <?php if (!empty($search)): ?>
        <a href="<?= BASE_URL ?>/admin/posts" class="btn btn-outline-secondary">Xoá lọc</a>
      <?php endif; ?>
    </div>
  </form>

  <!-- Table -->
  <div class="rounded-3 overflow-hidden"
       style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead style="background: var(--bg-light);">
          <tr>
            <th style="width: 50px;" class="py-3 ps-4">#</th>
            <th class="py-3">Ảnh bìa</th>
            <th class="py-3">Tiêu đề</th>
            <th class="py-3">Tác giả</th>
            <th class="py-3">Trạng thái</th>
            <th class="py-3">Ngày tạo</th>
            <th class="py-3 pe-4 text-end">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($posts)): ?>
          <tr>
            <td colspan="7" class="text-center text-muted py-5">
              <i class="fa-regular fa-newspaper fa-2x mb-2 d-block"></i>
              Chưa có bài viết nào<?= !empty($search) ? " khớp với \"<strong>{$search}</strong>\"" : '' ?>.
            </td>
          </tr>
          <?php else: ?>
            <?php foreach ($posts as $i => $post): ?>
            <tr>
              <td class="ps-4 text-muted small">
                <?= ($paginated['currentPage'] - 1) * $paginated['perPage'] + $i + 1 ?>
              </td>
              <td>
                <img
                  src="<?= !empty($post['image']) ? BASE_URL . '/public/uploads/' . htmlspecialchars($post['image']) : BASE_URL . '/assets/img/blog-placeholder.jpg' ?>"
                  alt="<?= htmlspecialchars($post['title']) ?>"
                  style="width: 70px; height: 50px; object-fit: cover; border-radius: 8px;"
                >
              </td>
              <td>
                <div class="fw-semibold mb-1" style="color: var(--text-main); max-width: 320px;">
                  <?= htmlspecialchars($post['title']) ?>
                </div>
                <a href="<?= BASE_URL ?>/blog/<?= htmlspecialchars($post['slug']) ?>"
                   target="_blank"
                   class="text-muted small text-decoration-none">
                  /blog/<?= htmlspecialchars($post['slug']) ?>
                  <i class="fa-solid fa-arrow-up-right-from-square ms-1" style="font-size: 0.7rem;"></i>
                </a>
              </td>
              <td class="text-muted small"><?= htmlspecialchars($post['author_name'] ?? '—') ?></td>
              <td>
                <span class="badge text-bg-<?= $post['status'] === 'published' ? 'success' : 'secondary' ?> rounded-pill">
                  <?= $post['status'] === 'published' ? 'Đã đăng' : 'Nháp' ?>
                </span>
              </td>
              <td class="text-muted small">
                <?= date('d/m/Y', strtotime($post['created_at'])) ?>
              </td>
              <td class="pe-4 text-end">
                <a href="<?= BASE_URL ?>/admin/posts/<?= $post['id'] ?>/edit"
                   class="btn btn-sm btn-outline-primary me-1">
                  <i class="fa-solid fa-pen"></i>
                </a>
                <form method="POST"
                      action="<?= BASE_URL ?>/admin/posts/<?= $post['id'] ?>/delete"
                      class="d-inline"
                      onsubmit="return confirm('Xoá bài viết này? Hành động không thể hoàn tác.')">
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

    <!-- Pagination footer -->
    <?php if ($paginated['totalPages'] > 1): ?>
    <div class="d-flex justify-content-between align-items-center px-4 py-3"
         style="border-top: 1px solid var(--border); background: var(--bg-light);">
      <span class="text-muted small">
        Hiển thị <?= count($posts) ?> / <?= $paginated['total'] ?> bài viết
      </span>
      <nav>
        <ul class="pagination pagination-sm mb-0 gap-1">
          <li class="page-item <?= $paginated['currentPage'] <= 1 ? 'disabled' : '' ?>">
            <a class="page-link rounded-2"
               href="?page=<?= $paginated['currentPage'] - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
              <i class="fa-solid fa-chevron-left"></i>
            </a>
          </li>
          <?php for ($p = 1; $p <= $paginated['totalPages']; $p++): ?>
          <li class="page-item <?= $p === $paginated['currentPage'] ? 'active' : '' ?>">
            <a class="page-link rounded-2"
               href="?page=<?= $p ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
              <?= $p ?>
            </a>
          </li>
          <?php endfor; ?>
          <li class="page-item <?= $paginated['currentPage'] >= $paginated['totalPages'] ? 'disabled' : '' ?>">
            <a class="page-link rounded-2"
               href="?page=<?= $paginated['currentPage'] + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
              <i class="fa-solid fa-chevron-right"></i>
            </a>
          </li>
        </ul>
      </nav>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../../../includes/admin_footer.php'; ?>