<?php
/**
 * views/admin/categories/edit.php
 *
 * Biến được truyền từ CategoryController::adminEdit():
 *  - $category  array   Dữ liệu danh mục hiện tại
 *  - $errors    array   Lỗi validate ['field' => 'message']
 *  - $old       array   Dữ liệu POST cũ (khi form submit bị lỗi)
 */
// Nếu form chưa submit lần nào, $old lấy từ dữ liệu hiện tại của danh mục
$old = !empty($old) ? $old : $category;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Danh mục — TechGalaxy Admin</title>
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
                <li class="breadcrumb-item active">Sửa danh mục</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h4 class="admin-page-title mb-1">
                    <i class="bi bi-pencil-square me-2 text-warning"></i>Sửa danh mục
                </h4>
                <p class="text-muted small mb-0">
                    ID: <code><?= $category['id'] ?></code> &mdash;
                    Tạo lúc: <?= date('d/m/Y', strtotime($category['created_at'] ?? 'now')) ?>
                </p>
            </div>
            <a href="/admin/categories" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Quay lại
            </a>
        </div>

        <!-- Error summary -->
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
            <form method="POST" action="/admin/categories/edit/<?= $category['id'] ?>" novalidate>

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
                </div>

                <!-- Slug -->
                <div class="mb-3">
                    <label for="slug" class="form-label">
                        Slug (URL)
                        <span class="text-muted fw-normal small">(có thể chỉnh sửa thủ công)</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text text-muted" style="font-size:.8rem;">/shop?category=</span>
                        <input type="text"
                               id="slug"
                               name="slug"
                               class="form-control <?= isset($errors['slug']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($old['slug'] ?? '') ?>">
                    </div>
                    <?php if (isset($errors['slug'])): ?>
                        <div class="invalid-feedback d-block"><?= htmlspecialchars($errors['slug']) ?></div>
                    <?php endif; ?>
                    <div class="form-text">
                        <i class="bi bi-info-circle me-1"></i>
                        Thay đổi slug có thể ảnh hưởng đến URL đang được index.
                    </div>
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

                <hr class="my-4">

                <!-- Buttons -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-warning px-4 text-white fw-semibold">
                        <i class="bi bi-check-lg me-1"></i>Cập nhật danh mục
                    </button>
                    <a href="/admin/categories" class="btn btn-outline-secondary">Hủy</a>
                </div>
            </form>
        </div>

        <!-- Danger zone: Xóa danh mục -->
        <div class="form-card mt-4 border border-danger" style="border-radius: var(--card-radius);">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-danger fw-bold mb-1"><i class="bi bi-exclamation-triangle me-1"></i>Vùng nguy hiểm</h6>
                    <p class="text-muted small mb-0">
                        Xóa danh mục này sẽ không thể hoàn tác.
                        <?php if (!empty($category['product_count']) && $category['product_count'] > 0): ?>
                            <strong class="text-danger">
                                Danh mục đang có <?= $category['product_count'] ?> sản phẩm liên kết!
                            </strong>
                        <?php endif; ?>
                    </p>
                </div>
                <button class="btn btn-outline-danger btn-sm"
                        onclick="confirmDelete(<?= $category['id'] ?>, '<?= htmlspecialchars(addslashes($category['name'])) ?>')">
                    <i class="bi bi-trash3 me-1"></i>Xóa
                </button>
            </div>
        </div>

    </div><!-- /admin-main -->
</div><!-- /admin-content -->

<!-- Delete confirmation modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Xác nhận xóa
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <p id="deleteMsg"></p>
                <?php if (!empty($category['product_count']) && $category['product_count'] > 0): ?>
                    <div class="alert alert-warning small">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Danh mục này đang liên kết với <strong><?= $category['product_count'] ?> sản phẩm</strong>.
                        Hãy chuyển các sản phẩm sang danh mục khác trước khi xóa.
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                <a id="deleteBtn" href="#" class="btn btn-danger">
                    <i class="bi bi-trash3 me-1"></i>Xóa vĩnh viễn
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-generate slug khi người dùng gõ tên
function generateSlug(name) {
    const viet = 'àáảãạăắặằẳẵâấậầẩẫèéẻẽẹêếệềểễìíỉĩịòóỏõọôốộồổỗơớợờởỡùúủũụưứựừửữỳýỷỹỵđ';
    const lat  = 'aaaaaaaaaaaaaaaaaeeeeeeeeeeeiiiiiooooooooooooooooouuuuuuuuuuuyyyyyd';
    let slug = name.toLowerCase();
    for (let i = 0; i < viet.length; i++) slug = slug.replaceAll(viet[i], lat[i]);
    slug = slug.replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-').trim();
    document.getElementById('slug').value = slug;
}

function confirmDelete(id, name) {
    document.getElementById('deleteMsg').textContent = `Bạn có chắc muốn xóa danh mục "${name}"?`;
    document.getElementById('deleteBtn').href = `/admin/categories/delete/${id}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
</body>
</html>