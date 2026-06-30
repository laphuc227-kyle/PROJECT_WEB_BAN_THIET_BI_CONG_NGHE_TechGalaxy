<?php
/**
 * views/admin/posts/edit.php — Sửa bài viết
 * Variables: $post
 */
require_once __DIR__ . '/../../../includes/admin_header.php';
require_once __DIR__ . '/../../../includes/admin_sidebar.php';
?>

<div class="admin-content">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 fw-bold mb-0" style="color: var(--text-main);">
      <i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Sửa bài viết
    </h1>
    <a href="<?= BASE_URL ?>/admin/posts" class="btn btn-outline-secondary">
      <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
    </a>
  </div>

  <?= getFlash() ?>

  <form method="POST" action="<?= BASE_URL ?>/admin/posts/<?= $post['id'] ?>" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= generateToken() ?>">

    <div class="row g-4">
      <div class="col-lg-8">

        <div class="rounded-3 p-4 mb-4"
             style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
          <div class="mb-3">
            <label for="title" class="form-label fw-semibold">
              Tiêu đề <span class="text-danger">*</span>
            </label>
            <input type="text" id="title" name="title"
                   class="form-control form-control-lg"
                   value="<?= htmlspecialchars($post['title']) ?>"
                   maxlength="255" required>
          </div>
          <div class="text-muted small">
            <i class="fa-solid fa-link me-1"></i>
            Slug hiện tại: <span class="text-primary">/blog/<?= htmlspecialchars($post['slug']) ?></span>
          </div>
        </div>

        <div class="rounded-3 p-4"
             style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
          <label class="form-label fw-semibold mb-3">
            Nội dung <span class="text-danger">*</span>
          </label>
          <textarea id="postContent" name="content" rows="20"
                    class="form-control"><?= htmlspecialchars($post['content']) ?></textarea>
        </div>

      </div>

      <div class="col-lg-4">

        <div class="rounded-3 p-4 mb-4"
             style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
          <h2 class="h6 fw-bold mb-3">Xuất bản</h2>
          <div class="mb-3">
            <label class="form-label fw-semibold small">Trạng thái</label>
            <select name="status" class="form-select">
              <option value="draft" <?= $post['status'] === 'draft' ? 'selected' : '' ?>>
                📝 Lưu nháp
              </option>
              <option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>
                🚀 Đã đăng
              </option>
            </select>
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-primary">
              <i class="fa-solid fa-floppy-disk me-1"></i> Lưu thay đổi
            </button>
          </div>
        </div>

        <div class="rounded-3 p-4"
             style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
          <h2 class="h6 fw-bold mb-3">Ảnh bìa</h2>

          <?php if (!empty($post['image'])): ?>
          <div class="mb-3">
            <p class="text-muted small mb-1">Ảnh hiện tại:</p>
            <img src="<?= BASE_URL . '/public/uploads/' . htmlspecialchars($post['image']) ?>"
                 alt="Ảnh bìa hiện tại"
                 class="img-fluid rounded-2 w-100"
                 style="max-height: 180px; object-fit: cover;">
          </div>
          <?php endif; ?>

          <label class="form-label fw-semibold small">Thay ảnh mới (tuỳ chọn)</label>
          <input type="file" name="cover_image" class="form-control"
                 accept="image/jpeg,image/png,image/webp">
          <div class="form-text">JPG, PNG, WEBP · Tối đa 5MB</div>
        </div>

      </div>
    </div>
  </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
  selector: '#postContent',
  height: 500,
  language: 'vi',
  plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
  toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | code fullscreen',
  content_style: 'body { font-family: Inter, sans-serif; font-size: 15px; line-height: 1.8; }',
  branding: false,
  promotion: false,
});
</script>

<?php require_once __DIR__ . '/../../../includes/admin_footer.php'; ?>