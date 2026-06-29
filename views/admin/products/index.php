<?php
/**
 * views/admin/products/index.php
 *
 * Biến được truyền từ ProductController::adminIndex():
 * - $products     array   Danh sách sản phẩm trang hiện tại
 * - $totalPages   int     Tổng số trang
 * - $currentPage  int     Trang hiện tại
 */

// Đọc + xóa flash message
$flashMessage = $_SESSION['flash_message'] ?? null;
$flashType    = $_SESSION['flash_type']    ?? 'success';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// GỌI LAYOUT CHUẨN CỦA DỰ ÁN (Thay thế cho các file cũ)
require_once __DIR__ . '/../../../includes/admin_header.php';
require_once __DIR__ . '/../../../includes/admin_sidebar.php';
?>

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

<div class="admin-content">
    <div class="admin-main p-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="admin-page-title mb-1" style="color: var(--text-main);">
                    <i class="bi bi-box-seam me-2 text-primary"></i>Quản lý sản phẩm
                </h4>
                <p class="text-muted small mb-0">Danh sách tất cả sản phẩm trong hệ thống</p>
            </div>
            <a href="/techgalaxy/admin/products/create" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Thêm sản phẩm
            </a>
        </div>

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

        <div class="admin-card rounded-3 overflow-hidden" style="background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow);">
            <div class="admin-card-header d-flex justify-content-between align-items-center flex-wrap gap-2 p-3 border-bottom" style="background: var(--bg-light);">
                <span class="fw-bold"><i class="bi bi-list-ul me-2"></i>Danh sách sản phẩm</span>
                <input type="text" id="tableSearch" class="form-control form-control-sm"
                       placeholder="Tìm nhanh theo tên..." style="max-width:240px;">
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0" id="productTable">
                    <thead style="background: var(--bg-light);">
                        <tr>
                            <th class="py-3 ps-4" style="width:60px">Ảnh</th>
                            <th class="py-3">Tên sản phẩm</th>
                            <th class="py-3">Danh mục</th>
                            <th class="py-3 text-end">Giá</th>
                            <th class="py-3 text-center">Tồn kho</th>
                            <th class="py-3 text-center">Trạng thái</th>
                            <th class="py-3 pe-4 text-center" style="width:130px">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-6 d-block mb-2 opacity-40"></i>
                                    Chưa có sản phẩm nào. <a href="/techgalaxy/admin/products/create">Thêm ngay</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $p): ?>
                                <?php
                                $hasSale    = !empty($p['sale_price']) && (float)$p['sale_price'] < (float)$p['price'];
                                $displayPrice = $hasSale ? $p['sale_price'] : $p['price'];
                                $statusMap  = ['active' => ['active','Đang bán'], 'inactive' => ['inactive','Ẩn'], 'draft' => ['draft','Nháp']];
                                [$statusClass, $statusLabel] = $statusMap[$p['status']] ?? ['draft','Nháp'];
                                $imgSrc = !empty($p['primary_image']) ? '/techgalaxy/' . ltrim($p['primary_image'], '/') : null;
                                ?>
                                <tr>
                                    <td class="ps-4">
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

                                    <td>
                                        <div class="fw-semibold" style="max-width:260px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; color: var(--text-main);">
                                            <?= htmlspecialchars($p['name']) ?>
                                        </div>
                                        <div class="text-muted" style="font-size:.75rem;">
                                            ID: <?= $p['id'] ?>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="badge bg-light text-primary border border-primary" style="font-size:.75rem;">
                                            <?= htmlspecialchars($p['category_name'] ?? '—') ?>
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        <div class="price-col"><?= number_format((float)$displayPrice, 0, ',', '.') . 'đ' ?></div>
                                        <?php if ($hasSale): ?>
                                            <div class="price-original"><?= number_format((float)$p['price'], 0, ',', '.') . 'đ' ?></div>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center">
                                        <?php if ((int)$p['stock'] === 0): ?>
                                            <span class="badge bg-danger">Hết hàng</span>
                                        <?php elseif ((int)$p['stock'] <= 5): ?>
                                            <span class="badge bg-warning text-dark"><?= $p['stock'] ?> còn</span>
                                        <?php else: ?>
                                            <span class="text-muted"><?= number_format($p['stock']) ?></span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center">
                                        <span class="status-badge status-<?= $statusClass ?>">
                                            <?= $statusLabel ?>
                                        </span>
                                    </td>

                                    <td class="pe-4 text-center">
                                        <a href="/techgalaxy/admin/products/edit/<?= $p['id'] ?>"
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

            <?php if ($totalPages > 1): ?>
                <div class="d-flex justify-content-center p-3" style="border-top: 1px solid var(--border); background: var(--bg-light);">
                    <nav>
                        <ul class="pagination pagination-sm mb-0 gap-1">
                            <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link rounded-2" href="?page=<?= $currentPage - 1 ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                            <?php for ($i = max(1,$currentPage-2); $i <= min($totalPages,$currentPage+2); $i++): ?>
                                <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                    <a class="page-link rounded-2" href="?page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link rounded-2" href="?page=<?= $currentPage + 1 ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div></div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border