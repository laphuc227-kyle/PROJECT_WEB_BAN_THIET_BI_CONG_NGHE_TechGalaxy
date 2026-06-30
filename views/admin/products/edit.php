<?php
/**
 * views/admin/products/edit.php
 * Form chỉnh sửa sản phẩm
 * Biến truyền vào từ ProductController::adminEdit():
 * - $product    array   Thông tin sản phẩm hiện tại
 * - $categories array   Danh sách danh mục
 * - $images     array   Ảnh hiện có của sản phẩm
 * - $errors     array   Lỗi validate (nếu có)
 */

$flashMessage = $_SESSION['flash_message'] ?? null;
$flashType    = $_SESSION['flash_type']    ?? 'success';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

require_once __DIR__ . '/../../../includes/admin_header.php';
require_once __DIR__ . '/../../../includes/admin_sidebar.php';
?>

<div class="admin-content">
    <div class="admin-main p-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="admin-page-title mb-1" style="color: var(--text-main);">
                    <i class="bi bi-pencil-square me-2 text-primary"></i>Chỉnh sửa sản phẩm
                </h4>
                <p class="text-muted small mb-0">Cập nhật thông tin cho sản phẩm #<?= (int) $product['id'] ?></p>
            </div>
            <a href="/techgalaxy/admin/products" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Quay lại
            </a>
        </div>

        <?php if ($flashMessage): ?>
            <div class="alert alert-<?= $flashType ?> alert-dismissible fade show mb-4">
                <?= htmlspecialchars($flashMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger mb-4">
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="admin-card rounded-3 p-4" style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
            <form action="/techgalaxy/admin/products/<?= (int) $product['id'] ?>/edit" method="POST" enctype="multipart/form-data">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tên sản phẩm <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control"
                           value="<?= htmlspecialchars($product['name']) ?>" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Giá bán (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" name="price" class="form-control"
                               value="<?= htmlspecialchars((string) $product['price']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Giá khuyến mãi (VNĐ)</label>
                        <input type="number" name="sale_price" class="form-control"
                               value="<?= htmlspecialchars((string) ($product['sale_price'] ?? '')) ?>"
                               placeholder="Để trống nếu không giảm giá">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Tồn kho <span class="text-danger">*</span></label>
                        <input type="number" name="stock" class="form-control"
                               value="<?= (int) $product['stock'] ?>" min="0" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Danh mục</label>
                        <select name="category_id" class="form-select">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int) $cat['id'] ?>"
                                    <?= (int) $cat['id'] === (int) $product['category_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="active"   <?= $product['status'] === 'active'   ? 'selected' : '' ?>>Đang bán (Active)</option>
                            <option value="draft"    <?= $product['status'] === 'draft'    ? 'selected' : '' ?>>Bản nháp (Draft)</option>
                            <option value="inactive" <?= $product['status'] === 'inactive' ? 'selected' : '' ?>>Ẩn (Inactive)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold mb-2">Ảnh hiện có</label>
                    <?php if (empty($images)): ?>
                        <p class="text-muted small">Sản phẩm chưa có ảnh nào.</p>
                    <?php else: ?>
                        <div class="d-flex flex-wrap gap-3">
                            <?php foreach ($images as $img): ?>
                                <div class="text-center" style="width: 110px;">
                                    <img src="/techgalaxy/<?= htmlspecialchars(ltrim($img['image_path'], '/')) ?>"
                                         class="rounded-2 mb-1"
                                         style="width: 110px; height: 90px; object-fit: cover; border: 1px solid var(--border);">
                                    <div class="form-check d-flex align-items-center justify-content-center gap-1">
                                        <input type="checkbox" class="form-check-input" name="delete_images[]"
                                               value="<?= (int) $img['id'] ?>" id="del_<?= (int) $img['id'] ?>">
                                        <label class="form-check-label small text-danger" for="del_<?= (int) $img['id'] ?>">Xóa</label>
                                    </div>
                                    <?php if (!empty($img['is_primary'])): ?>
                                        <span class="badge bg-primary" style="font-size: .65rem;">Ảnh chính</span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Thêm ảnh mới (có thể chọn nhiều)</label>
                    <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Mô tả chi tiết</label>
                    <textarea name="description" class="form-control" rows="5"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                </div>

                <hr style="border-color: var(--border);">

                <div class="text-end mt-3">
                    <a href="/techgalaxy/admin/products" class="btn btn-outline-secondary me-2">Hủy bỏ</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Cập nhật sản phẩm
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../../../includes/admin_footer.php'; ?>