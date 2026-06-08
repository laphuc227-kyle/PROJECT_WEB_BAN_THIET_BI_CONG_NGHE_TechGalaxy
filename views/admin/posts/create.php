<?php
/**
 * views/admin/posts/create.php — Thêm bài viết mới
 */
require_once __DIR__ . '/../../../includes/admin_header.php';
require_once __DIR__ . '/../../../includes/admin_sidebar.php';
?>

<div class="admin-content">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 fw-bold mb-0" style="color: var(--text-main);">
      <i class="fa-solid fa-plus-circle me-2 text-primary"></i>Thêm bài viết mới
    </h1>
    <a href="<?= BASE_URL ?>/admin/posts" class="btn btn-outline-secondary">
      <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
    </a>
  </div>

  <?= getFlash() ?>

  <form method="POST" action="<?= BASE_URL ?>/admin/posts" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= generateToken() ?>">

    <div class="row g-4">
      <!-- LEFT: Nội dung chính -->
      <div class="col-lg-8">

        <!-- Tiêu đề -->
        <div class="rounded-3 p-4 mb-4"
             style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
          <div class="mb-3">
            <label for="title" class="form-label fw-semibold">
              Tiêu đề bài viết <span class="text-danger">*</span>
            </label>
            <input type="text" id="title" name="title"
                   class="form-control form-control-lg"
                   placeholder="Nhập tiêu đề hấp dẫn..."
                   maxlength="255" required
                   value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
          </div>

          <!-- Preview slug -->
          <div class="text-muted small">
            <i class="fa-solid fa-link me-1"></i>
            Slug: <span id="slugPreview" class="text-primary">/blog/tieu-de-bai-viet</span>
          </div>
        </div>

        <!-- Editor nội dung -->
        <div class="rounded-3 p-4"
             style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
          <label class="form-label fw-semibold mb-3">
            Nội dung <span class="text-danger">*</span>
          </label>
          <textarea id="postContent" name="content" rows="20"
                    class="form-control"
                    required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
          <div class="form-text mt-2">
            <i class="fa-solid fa-circle-info me-1"></i>
            Sử dụng TinyMCE editor bên dưới để định dạng nội dung.
          </div>
        </div>

      </div>

      <!-- RIGHT: Sidebar settings -->
      <div class="col-lg-4">

        <!-- Đăng bài -->
        <div class="rounded-3 p-4 mb-4"
             style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
          <h2 class="h6 fw-bold mb-3" style="color: var(--text-main);">Xuất bản</h2>

          <div class="mb-3">
            <label for="status" class="form-label fw-semibold small">Trạng thái</label>
            <select id="status" name="status" class="form-select">
              <option value="draft" <?= ($_POST['status'] ?? '') === 'draft' ? 'selected' : '' ?>>
                📝 Lưu nháp
              </option>
              <option value="published" <?= ($_POST['status'] ?? '') === 'published' ? 'selected' : '' ?>>
                🚀 Đăng ngay
              </option>
            </select>
          </div>

          <div class="d-grid gap-2 mt-4">
            <button type="submit" name="status" value="published" class="btn btn-primary">
              <i class="fa-solid fa-paper-plane me-1"></i> Đăng bài
            </button>
            <button type="submit" name="status" value="draft" class="btn btn-outline-secondary">
              <i class="fa-solid fa-floppy-disk me-1"></i> Lưu nháp
            </button>
          </div>
        </div>

        <!-- Ảnh bìa -->
        <div class="rounded-3 p-4"
             style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
          <h2 class="h6 fw-bold mb-3" style="color: var(--text-main);">Ảnh bìa</h2>

          <!-- Preview -->
          <div id="imagePreviewContainer" class="mb-3 text-center d-none">
            <img id="imagePreview"
                 src="#"
                 alt="Preview ảnh bìa"
                 class="img-fluid rounded-2"
                 style="max-height: 200px; object-fit: cover;">
          </div>

          <label for="cover_image"
                 class="d-flex flex-column align-items-center justify-content-center border-2 border-dashed rounded-3 p-4 text-center text-muted"
                 style="cursor: pointer; border-style: dashed !important; border-color: var(--border) !important;"
                 id="uploadLabel">
            <i class="fa-solid fa-cloud-arrow-up fa-2x mb-2" style="color: var(--primary);"></i>
            <span class="small">Kéo thả hoặc click để chọn ảnh</span>
            <span class="text-muted" style="font-size: 0.75rem;">JPG, PNG, WEBP · Tối đa 5MB</span>
          </label>
          <input type="file" id="cover_image" name="cover_image"
                 accept="image/jpeg,image/png,image/webp"
                 class="d-none">
        </div>

      </div>
    </div>
  </form>
</div>

<!-- TinyMCE CDN (miễn phí, self-hosted hoặc cloud) -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
// Khởi tạo TinyMCE
tinymce.init({
  selector: '#postContent',
  height: 500,
  language: 'vi',
  plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
  toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | removeformat | code fullscreen',
  content_style: 'body { font-family: Inter, sans-serif; font-size: 15px; line-height: 1.8; }',
  branding: false,
  promotion: false,
});

// Auto-generate slug từ tiêu đề
document.getElementById('title').addEventListener('input', function () {
  const slug = this.value
    .toLowerCase()
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    .replace(/đ/g, 'd').replace(/Đ/g, 'd')
    .replace(/[^a-z0-9\s-]/g, '')
    .trim().replace(/\s+/g, '-');
  document.getElementById('slugPreview').textContent = '/blog/' + (slug || 'tieu-de-bai-viet');
});

// Preview ảnh khi chọn
document.getElementById('cover_image').addEventListener('change', function () {
  const file = this.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    document.getElementById('imagePreview').src = e.target.result;
    document.getElementById('imagePreviewContainer').classList.remove('d-none');
    document.getElementById('uploadLabel').style.display = 'none';
  };
  reader.readAsDataURL(file);
});
</script>

<?php require_once __DIR__ . '/../../../includes/admin_footer.php'; ?>