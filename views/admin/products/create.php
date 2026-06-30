<?php
/**
 * views/admin/products/create.php
 * Form thêm mới sản phẩm
 */

// Đọc flash message nếu có lỗi khi submit
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
                    <i class="bi bi-plus-circle me-2 text-primary"></i>Thêm sản phẩm mới
                </h4>
                <p class="text-muted small mb-0">Nhập thông tin chi tiết cho sản phẩm</p>
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

        <div class="admin-card rounded-3 p-4" style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
            <form action="/techgalaxy/admin/products/create" method="POST" enctype="multipart/form-data">
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tên sản phẩm <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="Nhập tên sản phẩm..." required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Giá bán (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" name="price" class="form-control" placeholder="VD: 15000000" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Giá khuyến mãi (VNĐ)</label>
                        <input type="number" name="sale_price" class="form-control" placeholder="Để trống nếu không giảm giá">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Tồn kho <span class="text-danger">*</span></label>
                        <input type="number" name="stock" class="form-control" value="0" min="0" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Danh mục</label>
                        <select name="category_id" class="form-select">
                            <option value="1">Điện thoại</option>
                            <option value="2">Laptop</option>
                            <option value="3">Phụ kiện</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="active">Đang bán (Active)</option>
                            <option value="draft">Bản nháp (Draft)</option>
                            <option value="inactive">Ẩn (Inactive)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Ảnh đại diện (Thumbnail)</label>
                    <input type="file" name="primary_image" class="form-control" accept="image/*">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Mô tả chi tiết</label>
                    <textarea name="description" class="form-control" rows="5" placeholder="Viết mô tả sản phẩm..."></textarea>
                </div>

                <hr style="border-color: var(--border);">
                
                <div class="text-end mt-3">
                    <a href="/techgalaxy/admin/products" class="btn btn-outline-secondary me-2">Hủy bỏ</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Lưu sản phẩm