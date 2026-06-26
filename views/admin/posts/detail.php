<?php
/**
 * views/admin/posts/detail.php — Chi tiết bài viết (Admin)
 * Variables: $post, $comments
 */
require_once __DIR__ . '/../../../includes/admin_header.php';
require_once __DIR__ . '/../../../includes/admin_sidebar.php';
?>

<div class="admin-content">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 fw-bold mb-0" style="color: var(--text-main);">
      <i class="fa-solid fa-newspaper me-2 text-primary"></i>Chi tiết bài viết
    </h1>
    <div class="d-flex gap-2">
      <a href="<?= BASE_URL ?>/blog/<?= htmlspecialchars($post['slug']) ?>"
         target="_blank"
         class="btn btn-outline-secondary">
        <i class="fa-solid fa-eye me-1"></i> Xem ngoài web
      </a>
      <a href="<?= BASE_URL ?>/admin/posts/<?= $post['id'] ?>/edit"
         class="btn btn-primary">
        <i class="fa-solid fa-pen me-1"></i> Chỉnh sửa
      </a>
      <a href="<?= BASE_URL ?>/admin/posts" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
      </a>
    </div>
  </div>

  <?= getFlash() ?>

  <div class="row g-4">

    <!-- ===== NỘI DUNG CHÍNH ===== -->
    <div class="col-lg-8">

      <!-- Ảnh bìa -->
      <?php if (!empty($post['image'])): ?>
      <div class="rounded-3 overflow-hidden mb-4"
           style="border: 1px solid var(--border); box-shadow: var(--shadow);">
        <img src="<?= BASE_URL . '/public/uploads/' . htmlspecialchars($post['image']) ?>"
             alt="<?= htmlspecialchars($post['title']) ?>"
             class="w-100"
             style="max-height: 360px; object-fit: cover;">
      </div>
      <?php endif; ?>

      <!-- Nội dung bài viết -->
      <div class="rounded-3 p-4"
           style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
        <h2 class="h4 fw-bold mb-3" style="color: var(--text-main); line-height: 1.4;">
          <?= htmlspecialchars($post['title']) ?>
        </h2>

        <div class="d-flex flex-wrap gap-3 text-muted small mb-4 pb-3"
             style="border-bottom: 1px solid var(--border);">
          <span>
            <i class="fa-regular fa-calendar me-1"></i>
            <?= date('d/m/Y H:i', strtotime($post['created_at'])) ?>
          </span>
          <?php if (!empty($post['author_name'])): ?>
          <span>
            <i class="fa-solid fa-user me-1"></i>
            <?= htmlspecialchars($post['author_name']) ?>
          </span>
          <?php endif; ?>
          <span>
            <i class="fa-solid fa-link me-1"></i>
            <code class="small">/blog/<?= htmlspecialchars($post['slug']) ?></code>
          </span>
        </div>

        <!-- Nội dung HTML từ editor -->
        <div class="post-content" style="line-height: 1.85; color: var(--text-main);">
          <?= $post['content'] ?>
        </div>
      </div>

      <!-- ===== BÌNH LUẬN ===== -->
      <div class="rounded-3 overflow-hidden mt-4"
           style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
        <div class="p-4 d-flex justify-content-between align-items-center"
             style="border-bottom: 1px solid var(--border);">
          <h3 class="h6 fw-bold mb-0" style="color: var(--text-main);">
            <i class="fa-regular fa-comments me-2 text-primary"></i>
            Bình luận (<?= count($comments) ?>)
          </h3>
        </div>

        <?php if (empty($comments)): ?>
        <div class="text-center text-muted py-5">
          <i class="fa-regular fa-comment fa-2x mb-2 d-block"></i>
          Chưa có bình luận nào.
        </div>
        <?php else: ?>
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead style="background: var(--bg-light);">
              <tr>
                <th class="py-3 ps-4">Người dùng</th>
                <th class="py-3">Nội dung</th>
                <th class="py-3">Thời gian</th>
                <th class="py-3 text-center">Trạng thái</th>
                <th class="py-3 pe-4 text-end">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($comments as $comment): ?>
              <tr>
                <td class="ps-4">
                  <div class="d-flex align-items-center gap-2">
                    <?php if (!empty($comment['user_avatar'])): ?>
                      <img src="<?= BASE_URL . '/public/uploads/' . htmlspecialchars($comment['user_avatar']) ?>"
                           class="rounded-circle"
                           style="width: 32px; height: 32px; object-fit: cover;">
                    <?php else: ?>
                      <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                           style="width: 32px; height: 32px; background: var(--primary); font-size: 0.8rem; flex-shrink: 0;">
                        <?= mb_strtoupper(mb_substr($comment['user_name'] ?? 'A', 0, 1)) ?>
                      </div>
                    <?php endif; ?>
                    <span class="fw-semibold small">
                      <?= htmlspecialchars($comment['user_name'] ?? 'Ẩn danh') ?>
                    </span>
                  </div>
                </td>
                <td style="max-width: 300px;">
                  <p class="mb-0 small text-truncate" title="<?= htmlspecialchars($comment['content']) ?>">
                    <?= htmlspecialchars($comment['content']) ?>
                  </p>
                </td>
                <td class="text-muted small">
                  <?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?>
                </td>
                <td class="text-center">
                  <span class="badge text-bg-<?= $comment['status'] === 'approved' ? 'success' : 'warning' ?> rounded-pill">
                    <?= $comment['status'] === 'approved' ? 'Đã duyệt' : 'Chờ duyệt' ?>
                  </span>
                </td>
                <td class="pe-4 text-end">
                  <!-- Duyệt / Ẩn -->
                  <?php if ($comment['status'] === 'approved'): ?>
                  <form method="POST"
                        action="<?= BASE_URL ?>/admin/comments/<?= $comment['id'] ?>/hide"
                        class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?= generateToken() ?>">
                    <input type="hidden" name="redirect" value="<?= BASE_URL ?>/admin/posts/<?= $post['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-warning" title="Ẩn bình luận">
                      <i class="fa-solid fa-eye-slash"></i>
                    </button>
                  </form>
                  <?php else: ?>
                  <form method="POST"
                        action="<?= BASE_URL ?>/admin/comments/<?= $comment['id'] ?>/approve"
                        class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?= generateToken() ?>">
                    <input type="hidden" name="redirect" value="<?= BASE_URL ?>/admin/posts/<?= $post['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-success" title="Duyệt bình luận">
                      <i class="fa-solid fa-check"></i>
                    </button>
                  </form>
                  <?php endif; ?>

                  <!-- Xoá -->
                  <form method="POST"
                        action="<?= BASE_URL ?>/admin/comments/<?= $comment['id'] ?>/delete"
                        class="d-inline"
                        onsubmit="return confirm('Xoá bình luận này?')">
                    <input type="hidden" name="csrf_token" value="<?= generateToken() ?>">
                    <input type="hidden" name="redirect" value="<?= BASE_URL ?>/admin/posts/<?= $post['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger ms-1" title="Xoá">
                      <i class="fa-solid fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>

    </div>

    <!-- ===== SIDEBAR INFO ===== -->
    <div class="col-lg-4">

      <!-- Thông tin bài viết -->
      <div class="rounded-3 p-4 mb-4"
           style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
        <h3 class="h6 fw-bold mb-3" style="color: var(--text-main);">
          <i class="fa-solid fa-circle-info me-2 text-primary"></i>Thông tin
        </h3>

        <dl class="mb-0">
          <dt class="small text-muted mb-1">Trạng thái</dt>
          <dd class="mb-3">
            <span class="badge text-bg-<?= $post['status'] === 'published' ? 'success' : 'secondary' ?> rounded-pill px-3">
              <?= $post['status'] === 'published' ? '🚀 Đã đăng' : '📝 Nháp' ?>
            </span>
          </dd>

          <dt class="small text-muted mb-1">Ngày tạo</dt>
          <dd class="mb-3 small"><?= date('d/m/Y H:i', strtotime($post['created_at'])) ?></dd>

          <?php if (!empty($post['updated_at']) && $post['updated_at'] !== $post['created_at']): ?>
          <dt class="small text-muted mb-1">Cập nhật lần cuối</dt>
          <dd class="mb-3 small"><?= date('d/m/Y H:i', strtotime($post['updated_at'])) ?></dd>
          <?php endif; ?>

          <dt class="small text-muted mb-1">Tác giả</dt>
          <dd class="mb-3 small fw-semibold">
            <?= htmlspecialchars($post['author_name'] ?? 'Không rõ') ?>
          </dd>

          <dt class="small text-muted mb-1">Slug</dt>
          <dd class="mb-0">
            <code class="small" style="word-break: break-all;">
              /blog/<?= htmlspecialchars($post['slug']) ?>
            </code>
          </dd>
        </dl>
      </div>

      <!-- Thống kê nhanh -->
      <div class="rounded-3 p-4 mb-4"
           style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
        <h3 class="h6 fw-bold mb-3" style="color: var(--text-main);">
          <i class="fa-solid fa-chart-simple me-2 text-primary"></i>Thống kê
        </h3>
        <div class="d-flex justify-content-between align-items-center py-2"
             style="border-bottom: 1px solid var(--border);">
          <span class="small text-muted">Tổng bình luận</span>
          <span class="fw-bold"><?= count($comments) ?></span>
        </div>
        <div class="d-flex justify-content-between align-items-center py-2"
             style="border-bottom: 1px solid var(--border);">
          <span class="small text-muted">Đã duyệt</span>
          <span class="fw-bold text-success">
            <?= count(array_filter($comments, fn($c) => $c['status'] === 'approved')) ?>
          </span>
        </div>
        <div class="d-flex justify-content-between align-items-center pt-2">
          <span class="small text-muted">Chờ duyệt</span>
          <span class="fw-bold text-warning">
            <?= count(array_filter($comments, fn($c) => $c['status'] !== 'approved')) ?>
          </span>
        </div>
      </div>

      <!-- Xoá bài viết -->
      <div class="rounded-3 p-4"
           style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
        <h3 class="h6 fw-bold mb-3 text-danger">
          <i class="fa-solid fa-triangle-exclamation me-2"></i>Vùng nguy hiểm
        </h3>
        <p class="text-muted small mb-3">Xoá bài viết sẽ xoá toàn bộ bình luận liên quan và không thể hoàn tác.</p>
        <form method="POST"
              action="<?= BASE_URL ?>/admin/posts/<?= $post['id'] ?>/delete"
              onsubmit="return confirm('Bạn có chắc muốn xoá bài viết này? Hành động không thể hoàn tác.')">
          <input type="hidden" name="csrf_token" value="<?= generateToken() ?>">
          <button type="submit" class="btn btn-danger w-100">
            <i class="fa-solid fa-trash me-1"></i> Xoá bài viết
          </button>
        </form>
      </div>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../../includes/admin_footer.php'; ?>