<?php
/**
 * views/admin/products/index.php
 *
 * Biến được truyền từ ProductController::adminIndex():
 *  - $products     array   Danh sách sản phẩm trang hiện tại
 *  - $totalPages   int     Tổng số trang
 *  - $currentPage  int     Trang hiện tại
 */

// Đọc + xóa flash message
$flashMessage = $_SESSION['flash_message'] ?? null;
$flashType    = $_SESSION['flash_type']    ?? 'success';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// Helper format giá
function formatPrice(float $price): string {
    return number_format($price, 0, ',', '.') . 'đ';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Sản phẩm — TechGalaxy Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <?php include __DIR__ . '/../_admin_styles.php'; ?>
    <style>
        .product-thumb {
            width: 52px; height: 52px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
        }
        .product-thumb-placeholder {
            width: 52px; height: 52px;
            border-radius: 8px;
            background: #f1f5f9;
            display: flex; align-items: center; justify-content: center;
            color: #94a3b8; font-size: 1.2rem;
            border: 1px solid #e2e8f0;
        }
        .status-badge { font-size: .72rem; padding: 3px 10px; border-radius: 20px; font-weight: 600; }
        .status-active  { background: #dcfce7; color: #16a34a; }
        .status-inactive{ background: #fee2e2; color: #dc2626; }
        .status-draft   { background: #fef9c3; color: #ca8a04; }
        .price-col      { font-family: 'Space Grotesk', sans-serif; font-weight: 600; color: #2563eb; }
        .price-original { font-size: .78rem; color: #94a3b8; text-decoration: line-through; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../_sidebar.php'; ?>

<div class="admin-content">
    <?php include __DIR__ . '/../_topbar.php'; ?>

    <div class="admin-main p-4">

        <!-- Page header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="admin-page-title mb-1">
                    <i class="bi bi-box-seam me-2 text-primary"></i>Quản lý sản phẩm
                </h4>
                <p class="text-muted small mb-0">Danh sách tất cả sản phẩm trong hệ thống</p>
            </div>
            <a href="/admin/products/create" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Thêm sản phẩm
            </a>
        </div>

        <!-- Flash message -->
        <?php if ($flashMessage): ?>
            <div class="alert alert-<?= $flashType ?> alert-dismissible d-flex align-items-center gap-2 mb-4" role="alert">
                <?php
                $icons = ['success' => 'check-circle-fill','danger' => 'x-circle-fill','warning' => 'exclamation-triangle-fill','info' => 'info-circle-fill'];
                ?>
                <i class="bi bi-<?= $icons[$flashType] ?? 'info-circle-fill' ?>"></i>
                <div><?= htmlspecialchars($flashMessage) ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Table card -->
        <div class="admin-card">
            <div class="admin-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="bi bi-list-ul me-2"></i>Danh sách sản phẩm</span>
                <!-- Tìm nhanh -->
                <input type="text" id="tableSearch" class="form-control form-control-sm"
                       placeholder="Tìm nhanh theo tên..." style="max-width:240px;">
            </div>

            <div class="table-responsive">
                <table class="admin-table" id="productTable">
                    <thead>
                        <tr>
                            <th style="width:60px">Ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Danh mục</th>
                            <th class="text-end">Giá</th>
                            <th class="text-center">Tồn kho</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center" style="width:130px">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-6 d-block mb-2 opacity-40"></i>
                                    Chưa có sản phẩm nào. <a href="/admin/products/create">Thêm ngay</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $p): ?>
                                <?php
                                $hasSale    = !empty($p['sale_price']) && (float)$p['sale_price'] < (float)$p['price'];
                                $displayPrice = $hasSale ? $p['sale_price'] : $p['price'];
                                $statusMap  = ['active' => ['active','Đang bán'], 'inactive' => ['inactive','Ẩn'], 'draft' => ['draft','Nháp']];
                                [$statusClass, $statusLabel] = $statusMap[$p['status']] ?? ['draft','Nháp'];
                                $imgSrc = !empty($p['primary_image']) ? '/' . $p['primary_image'] : null;
                                ?>
                                <tr>
                                    <!-- Ảnh thumbnail -->
                                    <td>
                                        <?php if ($imgSrc): ?>
                                            <img src="<?= htmlspecialchars($imgSrc) ?>"
                                                 class="product-thumb"
                                                 alt="<?= htmlspecialchars($p['name']) ?>"
                                                 loading="lazy">
                                        <?php else: ?>
                                            <div class="product-thumb-placeholder">
                                                <i class="bi bi-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Tên -->
                                    <td>
                                        <div class="fw-semibold" style="max-width:260px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                            <?= htmlspecialchars($p['name']) ?>
                                        </div>
                                        <div class="text-muted" style="font-size:.75rem;">
                                            ID: <?= $p['id'] ?>
                                        </div>
                                    </td>

                                    <!-- Danh mục -->
                                    <td>
                                        <span class="badge bg-light text-primary border border-primary" style="font-size:.75rem;">
                                            <?= htmlspecialchars($p['category_name'] ?? '—') ?>
                                        </span>
                                    </td>

                                    <!-- Giá -->
                                    <td class="text-end">
                                        <div class="price-col"><?= formatPrice((float)$displayPrice) ?></div>
                                        <?php if ($hasSale): ?>
                                            <div class="price-original"><?= formatPrice((float)$p['price']) ?></div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Tồn kho -->
                                    <td class="text-center">
                                        <?php if ((int)$p['stock'] === 0): ?>
                                            <span class="badge bg-danger">Hết hàng</span>
                                        <?php elseif ((int)$p['stock'] <= 5): ?>
                                            <span class="badge bg-warning text-dark"><?= $p['stock'] ?> còn</span>
                                        <?php else: ?>
                                            <span class="text-muted"><?= number_format($p['stock']) ?></span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Trạng thái -->
                                    <td class="text-center">
                                        <span class="status-badge status-<?= $statusClass ?>">
                                            <?= $statusLabel ?>
                                        </span>
                                    </td>

                                    <!-- Thao tác -->
                                    <td class="text-center">
                                        <a href="/admin/products/edit/<?= $p['id'] ?>"
                                           class="btn btn-sm btn-outline-primary me-1" title="Sửa">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger"
                                                onclick="confirmDelete(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['name'])) ?>')"
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

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="d-flex justify-content-center p-3 border-top">
                    <nav>
                        <ul class="pagination pagination-sm mb-0 gap-1">
                            <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $currentPage - 1 ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                            <?php for ($i = max(1,$currentPage-2); $i <= min($totalPages,$currentPage+2); $i++): ?>
                                <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $currentPage + 1 ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div><!-- /admin-card -->

    </div>
</div>

<!-- Delete modal -->
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
                <p id="deleteMsg" class="mb-0"></p>
                <p class="text-muted small mt-2 mb-0">Sản phẩm sẽ bị ẩn khỏi cửa hàng (soft delete). Bạn có thể khôi phục sau.</p>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                <a id="deleteConfirmBtn" href="#" class="btn btn-danger">
                    <i class="bi bi-trash3 me-1"></i>Xóa sản phẩm
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Tìm nhanh trong bảng (client-side)
document.getElementById('tableSearch').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#productTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});

function confirmDelete(id, name) {
    document.getElementById('deleteMsg').textContent = `Bạn có chắc muốn xóa sản phẩm "${name}"?`;
    document.getElementById('deleteConfirmBtn').href = `/admin/products/delete/${id}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
</body>
</html>