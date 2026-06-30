<?php
/**
 * views/admin/categories/create.php
 *
 * Biến:
 *  - $errors   array   Lỗi validate ['field' => 'message']
 *  - $old      array   Dữ liệu cũ để giữ lại khi có lỗi
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Danh mục — TechGalaxy Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <?php include __DIR__ . '/../_admin_styles.php'; ?>
</head>
<body>
<?php include __DIR__ . '/../_sidebar.php'; ?>
<div class="admin-content">
    <?php include __DIR__ . '/../_topbar.php'; ?>
    <div class="admin-main p-4" style="max-width: 680px;">

        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/admin/categories" class="text-decoration-none">Danh mục</a></li>
                <li class="breadcrumb-item active">Thêm mới</li>
            </ol>
        </nav>

        <h4 class="admin-page-title mb-4">
            <i class="bi bi-plus-circle me-2 text-primary"></i>Thêm danh mục mới
        </h4>

        <!-- Form errors summary -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible d-flex gap-2 mb-4" role="alert">
                <i class="bi bi-exclamation-circle-fill mt-1 flex-shrink-0"></i>
                <div>
                    <strong>Vui lòng kiểm tra lại:</strong>
                    <ul class="mb-0 mt-1">
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <div class="form-card">
            <form method="POST" action="/admin/categories/create" novalidate>
                <!-- CSRF Token (nếu dự án có implement) -->
                <!-- <input type="hidden" name="_token" value="<?= /* csrf_token() */ '' ?>"> -->

                <!-- Tên danh mục -->
                <div class="mb-3">
                    <label for="name" class="form-label">
                        Tên danh mục <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                           value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                           placeholder="Ví dụ: Điện thoại thông minh"
                           maxlength="255"
                           oninput="generateSlug(this.value)"
                           required>
                    <?php if (isset($errors['name'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div>
                    <?php endif; ?>
                    <div class="form-text">Tối đa 255 ký tự.</div>
                </div>

                <!-- Slug (tự động tạo, có thể chỉnh) -->
                <div class="mb-3">
                    <label for="slug" class="form-label">
                        Slug (URL)
                        <span class="text-muted fw-normal small">(tự động tạo từ tên)</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text text-muted" style="font-size:.8rem;">/shop?category=</span>
                        <input type="text"
                               id="slug"
                               name="slug"
                               class="form-control <?= isset($errors['slug']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($old['slug'] ?? '') ?>"
                               placeholder="dien-thoai-thong-minh">
                    </div>
                    <?php if (isset($errors['slug'])): ?>
                        <div class="invalid-feedback d-block"><?= htmlspecialchars($errors['slug']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Mô tả -->
                <div class="mb-4">
                    <label for="description" class="form-label">Mô tả</label>
                    <textarea id="description"
                              name="description"
                              class="form-control"
                              rows="4"
                              placeholder="Mô tả ngắn về danh mục này..."><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                </div>

                <!-- Buttons -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-1"></i>Lưu danh mục
                    </button>
                    <a href="/admin/categories" class="btn btn-outline-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
/**
 * Tự động tạo slug từ tên (phía client)
 * Khớp logic với phía server trong Category::generateSlug()
 */
function generateSlug(name) {
    const viet = 'àáảãạăắặằẳẵâấậầẩẫèéẻẽẹêếệềểễìíỉĩịòóỏõọôốộồổỗơớợờởỡùúủũụưứựừửữỳýỷỹỵđ';
    const lat  = 'aaaaaaaaaaaaaaaaaeeeeeeeeeeeiiiiiooooooooooooooooouuuuuuuuuuuyyyyyd';

    let slug = name.toLowerCase();
    for (let i = 0; i < viet.length; i++) {
        slug = slug.replaceAll(viet[i], lat[i]);
    }
    slug = slug.replace(/[^a-z0-9\s-]/g, '')
               .replace(/\s+/g, '-')
               .replace(/-+/g, '-')
               .trim();

    document.getElementById('slug').value = slug;
}
</script>
</body>
</html>