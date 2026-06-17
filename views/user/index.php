<?php
/**
 * views/admin/categories/index.php
 * Danh sách danh mục — Admin Panel
 *
 * Biến:
 *  - $categories    array   Danh sách danh mục
 *  - $flashMessage  string|null
 *  - $flashType     string
 **/
$flashMessage = $_SESSION['flash_message'] ?? null;
$flashType    = $_SESSION['flash_type']    ?? 'success';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Danh mục — TechGalaxy Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <?php include __DIR__ . '/../_admin_styles.php'; ?>
</head>
<body class="admin-body">

<?php include __DIR__ . '/../_sidebar.php'; ?>

<div class="admin-content">
    <?php include __DIR__ . '/../_topbar.php'; ?>

    <div class="admin-main p-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="admin-page-title mb-1">
                    <i class="bi bi-grid me-2 text-primary"></i>Danh mục sản phẩm
                </h4>
                <p class="text-muted mb-0 small">Quản lý tất cả danh mục trong hệ thống</p>
            </div>
            <a href="/admin/categories/create" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Thêm danh mục
            </a>
        </div>

        <!-- Flash Message -->
        <?php if ($flashMessage): ?>
            <div class="alert alert-<?= $flashType ?> alert-dismissible d-flex align-items-center gap-2 mb-4" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                <div><?= htmlspecialchars($flashMessage) ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Table Card -->
        <div class="admin-card">
            <div class="admin-card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list-ul me-2"></i>Danh sách (<?= isset($categories) ? count($categories) : 0 ?>)</span>
                <!-- Quick search -->
                <input type="text" id="tableSearch" class="form-control form-control-sm w-auto"
                       placeholder="Tìm nhanh..." style="min-width:200px;">
            </div>

            <div class="table-responsive">
                <table class="admin-table" id="catTable">
                    <thead>
                        <tr>
                            <th style="width:50px">#</th>
                            <th>Tên danh mục</th>
                            <th>Slug</th>
                            <th>Mô tả</th>
                            <th class="text-center">Sản phẩm</th>
                            <th class="text-center" style="width:140px">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-6 d-block mb-2 opacity-50"></i>
                                    Chưa có danh mục nào.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $i => $cat): ?>
                                <tr>
                                    <td class="text-muted small"><?= $i + 1 ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($cat['name']) ?></div>
                                    </td>
                                    <td>
                                        <code class="text-primary" style="font-size:.8rem;">
                                            <?= htmlspecialchars($cat['slug']) ?>
                                        </code>
                                    </td>
                                    <td class="text-muted small">
                                        <?= htmlspecialchars(mb_substr($cat['description'] ?? '', 0, 60)) ?>
                                        <?= strlen($cat['description'] ?? '') > 60 ? '...' : '' ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary rounded-pill">
                                            <?= $cat['product_count'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="/admin/categories/edit/<?= $cat['id'] ?>"
                                           class="btn btn-sm btn-outline-primary me-1" title="Sửa">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger"
                                                onclick="confirmDelete(<?= $cat['id'] ?>, '<?= htmlspecialchars(addslashes($cat['name'])) ?>', <?= $cat['product_count'] ?>)"
                                                title="Xóa">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal xác nhận xóa -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">
                <p id="deleteModalMsg" class="mb-0"></p>
                <div id="deleteWarning" class="alert alert-warning mt-3 small" style="display:none;">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Danh mục này đang có sản phẩm liên kết. Xóa sẽ ảnh hưởng đến các sản phẩm đó!
                </div>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                <a id="deleteConfirmBtn" href="#" class="btn btn-danger">
                    <i class="bi bi-trash3 me-1"></i>Xác nhận xóa
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Tìm kiếm nhanh trong bảng
document.getElementById('tableSearch').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#catTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});

// Modal xác nhận xóa
function confirmDelete(id, name, productCount) {
    document.getElementById('deleteModalMsg').textContent = `Bạn có chắc muốn xóa danh mục "${name}"?`;
    const warning = document.getElementById('deleteWarning');
    warning.style.display = productCount > 0 ? 'block' : 'none';
    document.getElementById('deleteConfirmBtn').href = `/admin/categories/delete/${id}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
</body>
</html>
