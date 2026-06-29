<?php
/**
 * views/user/wishlist.php
 *
 * Biến được truyền từ ProductController::wishlistPage():
 *  - $items         array   Danh sách sản phẩm yêu thích (kèm thông tin)
 *  - $flashMessage  string|null  Thông báo flash
 *  - $flashType     string       Loại alert: success | danger | warning | info
 */

function formatPrice(float $price): string
{
    return number_format($price, 0, ',', '.') . 'đ';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách yêu thích — TechGalaxy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --danger: #ef4444;
            --bg-soft: #f8fafc;
            --border: #e2e8f0;
            --text-muted: #64748b;
            --card-radius: 14px;
            --shadow: 0 2px 12px rgba(0,0,0,.07);
        }
        body { font-family: 'Inter', sans-serif; background: var(--bg-soft); }
        h1,h2,h3,h4,h5,h6,.navbar-brand { font-family: 'Space Grotesk', sans-serif; }
        .navbar { background: #fff; border-bottom: 1px solid var(--border); }
        .navbar-brand { font-weight: 700; color: var(--primary) !important; font-size: 1.4rem; }

        .page-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.75rem;
        }
        .page-header-icon {
            width: 52px; height: 52px;
            background: #fee2e2;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            color: var(--danger);
        }
        .page-header h2 { font-weight: 700; margin: 0; font-size: 1.5rem; color: #1e293b; }
        .page-header p { margin: 0; color: var(--text-muted); font-size: .9rem; }

        /* ---- Wishlist Item Card ---- */
        .wishlist-item {
            background: #fff;
            border-radius: var(--card-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            display: flex;
            gap: 0;
            transition: box-shadow .2s, transform .2s;
            animation: slideIn .3s ease both;
        }
        .wishlist-item:hover { box-shadow: 0 8px 28px rgba(0,0,0,.1); transform: translateY(-2px); }
        .wishlist-item__img {
            flex: 0 0 140px;
            height: 140px;
            overflow: hidden;
            background: var(--bg-soft);
        }
        .wishlist-item__img img { width: 100%; height: 100%; object-fit: cover; transition: transform .35s; }
        .wishlist-item:hover .wishlist-item__img img { transform: scale(1.06); }
        .wishlist-item__body {
            flex: 1;
            padding: 1rem 1.25rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .wishlist-item__category {
            font-size: .72rem;
            color: var(--primary);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: .2rem;
        }
        .wishlist-item__name {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.05rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: .35rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .wishlist-item__price {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary);
        }
        .wishlist-item__price--original {
            font-size: .85rem;
            font-weight: 400;
            color: var(--text-muted);
            text-decoration: line-through;
            margin-left: .4rem;
        }
        .wishlist-item__meta { font-size: .78rem; color: var(--text-muted); margin-top: .25rem; }
        .wishlist-item__actions {
            flex: 0 0 auto;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: center;
            gap: .5rem;
            padding: 1rem 1.25rem 1rem 0;
        }
        .stock-badge {
            font-size: .72rem;
            padding: 3px 10px;
            border-radius: 20px;
        }
        .stock-in  { background: #dcfce7; color: #16a34a; }
        .stock-out { background: #fee2e2; color: #dc2626; }

        /* ---- Buttons ---- */
        .btn-view { border-radius: 10px; font-size: .85rem; padding: .45rem 1rem; }
        .btn-remove {
            border: none;
            background: none;
            color: var(--text-muted);
            font-size: .82rem;
            padding: .3rem .5rem;
            border-radius: 8px;
            cursor: pointer;
            transition: color .15s, background .15s;
            display: flex; align-items: center; gap: .3rem;
        }
        .btn-remove:hover { color: var(--danger); background: #fee2e2; }

        /* ---- Empty state ---- */
        .empty-state {
            text-align: center;
            padding: 5rem 2rem;
            background: #fff;
            border-radius: var(--card-radius);
            box-shadow: var(--shadow);
        }
        .empty-state__icon { font-size: 4rem; color: #fca5a5; margin-bottom: 1rem; }
        .empty-state h4 { font-weight: 700; color: #1e293b; }
        .empty-state p { color: var(--text-muted); }

        /* Animation */
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .removing { opacity: 0; transform: translateX(40px); transition: all .3s ease; pointer-events: none; }

        /* Toast */
        .toast-container { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999; }

        @media (max-width: 576px) {
            .wishlist-item__img { flex: 0 0 100px; height: 100px; }
            .wishlist-item__actions { padding: .75rem .75rem .75rem 0; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="/"><i class="bi bi-stars me-1"></i>TechGalaxy</a>
        <div class="ms-auto d-flex align-items-center gap-2">
            <a href="/shop" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-shop me-1"></i>Tiếp tục mua sắm
            </a>
            <a href="/logout" class="btn btn-outline-secondary btn-sm">Đăng xuất</a>
        </div>
    </div>
</nav>

<div class="container py-4" style="max-width: 860px;">

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-icon"><i class="bi bi-heart-fill"></i></div>
        <div>
            <h2>Danh sách yêu thích</h2>
            <p><?= count($items) ?> sản phẩm</p>
        </div>
    </div>

    <!-- Flash Message (từ server sau redirect) -->
    <?php if ($flashMessage): ?>
        <div id="flashAlert"
             class="alert alert-<?= htmlspecialchars($flashType) ?> alert-dismissible d-flex align-items-center gap-2 mb-4"
             role="alert">
            <?php
            $icons = ['success' => 'check-circle-fill', 'danger' => 'x-circle-fill', 'warning' => 'exclamation-triangle-fill', 'info' => 'info-circle-fill'];
            $icon  = $icons[$flashType] ?? 'info-circle-fill';
            ?>
            <i class="bi bi-<?= $icon ?>"></i>
            <div><?= htmlspecialchars($flashMessage) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Alert vùng chứa thông báo AJAX -->
    <div id="ajaxAlert" class="mb-3" style="display:none;"></div>

    <!-- ===== DANH SÁCH SẢN PHẨM YÊU THÍCH ===== -->
    <?php if (empty($items)): ?>
        <div class="empty-state">
            <div class="empty-state__icon"><i class="bi bi-heart"></i></div>
            <h4>Chưa có sản phẩm yêu thích</h4>
            <p class="mb-4">Hãy khám phá shop và nhấn nút ❤ để thêm sản phẩm vào đây.</p>
            <a href="/shop" class="btn btn-primary px-4">
                <i class="bi bi-shop me-1"></i>Khám phá ngay
            </a>
        </div>
    <?php else: ?>
        <div class="d-flex flex-column gap-3" id="wishlistContainer">
            <?php foreach ($items as $i => $item): ?>
                <?php
                $hasSale = !empty($item['sale_price']) && (float)$item['sale_price'] < (float)$item['price'];
                $displayPrice = $hasSale ? $item['sale_price'] : $item['price'];
                $inStock = (int)($item['stock'] ?? 0) > 0;
                $imgSrc  = !empty($item['primary_image']) ? '/' . $item['primary_image'] : '/public/assets/img/no-image.png';
                ?>
                <div class="wishlist-item"
                     id="item-<?= $item['wishlist_id'] ?>"
                     style="animation-delay: <?= $i * 0.06 ?>s">

                    <!-- Ảnh sản phẩm -->
                    <a href="/product/<?= $item['product_id'] ?>" class="wishlist-item__img">
                        <img src="<?= htmlspecialchars($imgSrc) ?>"
                             alt="<?= htmlspecialchars($item['name']) ?>"
                             loading="lazy">
                    </a>

                    <!-- Thông tin -->
                    <div class="wishlist-item__body">
                        <div class="wishlist-item__category">
                            <?= htmlspecialchars($item['category_name'] ?? '') ?>
                        </div>
                        <div class="wishlist-item__name">
                            <?= htmlspecialchars($item['name']) ?>
                        </div>
                        <div>
                            <span class="wishlist-item__price"><?= formatPrice((float)$displayPrice) ?></span>
                            <?php if ($hasSale): ?>
                                <span class="wishlist-item__price--original">
                                    <?= formatPrice((float)$item['price']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="mt-2">
                            <span class="stock-badge <?= $inStock ? 'stock-in' : 'stock-out' ?>">
                                <?= $inStock ? 'Còn hàng' : 'Hết hàng' ?>
                            </span>
                        </div>
                        <div class="wishlist-item__meta">
                            <i class="bi bi-clock me-1"></i>
                            Thêm lúc: <?= date('d/m/Y H:i', strtotime($item['added_at'])) ?>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="wishlist-item__actions">
                        <a href="/product/<?= $item['product_id'] ?>"
                           class="btn btn-outline-primary btn-view">
                            <i class="bi bi-eye me-1"></i>Xem
                        </a>
                        <button class="btn-remove"
                                onclick="removeFromWishlist(<?= $item['wishlist_id'] ?>, <?= $item['product_id'] ?>)">
                            <i class="bi bi-trash3"></i> Xóa
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div><!-- /container -->

<!-- TOAST -->
<div class="toast-container">
    <div id="wishlistToast" class="toast align-items-center border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body fw-medium" id="toastMsg"></div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
/**
 * Xóa sản phẩm khỏi Wishlist qua AJAX
 * Sau khi xóa thành công: xóa element khỏi DOM, hiển thị thông báo
 */
async function removeFromWishlist(wishlistId, productId) {
    if (!confirm('Bạn có chắc muốn xóa sản phẩm này khỏi danh sách yêu thích?')) return;

    const itemEl = document.getElementById('item-' + wishlistId);

    try {
        const fd = new FormData();
        fd.append('product_id', productId);

        const res  = await fetch('/ajax/wishlist/remove', { method: 'POST', body: fd });
        const data = await res.json();

        if (data.success) {
            // Animation: slide ra + xóa DOM
            itemEl.classList.add('removing');
            setTimeout(() => {
                itemEl.remove();
                updateCount();
                showAlert('Đã xóa sản phẩm khỏi danh sách yêu thích.', 'success');
                checkEmpty();
            }, 320);
        } else {
            showAlert(data.message || 'Có lỗi xảy ra.', 'danger');
        }
    } catch (err) {
        showAlert('Không thể kết nối máy chủ. Vui lòng thử lại.', 'danger');
    }
}

/** Cập nhật số đếm trên header */
function updateCount() {
    const items    = document.querySelectorAll('.wishlist-item').length;
    const countEl  = document.querySelector('.page-header p');
    if (countEl) countEl.textContent = items + ' sản phẩm';
}

/** Kiểm tra nếu hết hàng → hiện empty state */
function checkEmpty() {
    const items = document.querySelectorAll('.wishlist-item').length;
    if (items === 0) {
        document.getElementById('wishlistContainer').innerHTML = `
            <div class="empty-state">
                <div class="empty-state__icon"><i class="bi bi-heart"></i></div>
                <h4>Danh sách yêu thích trống</h4>
                <p class="mb-4">Hãy khám phá shop và thêm sản phẩm yêu thích.</p>
                <a href="/shop" class="btn btn-primary px-4">
                    <i class="bi bi-shop me-1"></i>Khám phá ngay
                </a>
            </div>
        `;
    }
}

/** Hiển thị Alert inline (trên trang) */
function showAlert(message, type = 'success') {
    const alertEl = document.getElementById('ajaxAlert');
    const iconMap  = { success: 'check-circle-fill', danger: 'x-circle-fill', warning: 'exclamation-triangle-fill', info: 'info-circle-fill' };
    alertEl.innerHTML = `
        <div class="alert alert-${type} alert-dismissible d-flex align-items-center gap-2 fade show" role="alert">
            <i class="bi bi-${iconMap[type] || 'info-circle-fill'}"></i>
            <div>${message}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    alertEl.style.display = 'block';

    // Tự động ẩn sau 4 giây
    setTimeout(() => {
        const alertInner = alertEl.querySelector('.alert');
        if (alertInner) alertInner.classList.remove('show');
        setTimeout(() => { alertEl.style.display = 'none'; }, 200);
    }, 4000);
}
</script>
</body>
</html>