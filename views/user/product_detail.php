<?php
// ===== LẤY DỮ LIỆU SẢN PHẨM CHI TIẾT =====
global $pdo;

// Lấy ID từ URL: /product/3
$productId = (int) (explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/product/'))[0] ?? 0);

// Cách lấy ID chuẩn hơn — parse từ path
$uriPath   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath  = parse_url(BASE_URL, PHP_URL_PATH) ?: '';
$cleanPath = trim(str_replace($basePath, '', $uriPath), '/');
// cleanPath lúc này là "product/3"
preg_match('@^product/(\d+)$@', $cleanPath, $idMatch);
$productId = (int) ($idMatch[1] ?? 0);

if ($productId <= 0) {
    http_response_code(404);
    exit('Sản phẩm không tồn tại.');
}

// Lấy thông tin sản phẩm
$stmt = $pdo->prepare(
    "SELECT p.*, c.name AS category_name, c.id AS category_id
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.id = ? AND p.deleted_at IS NULL AND p.status = 1
     LIMIT 1"
);
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    http_response_code(404);
    exit('Sản phẩm không tồn tại hoặc đã bị ẩn.');
}

// Lấy gallery ảnh (ảnh primary đứng đầu)
$imgStmt = $pdo->prepare(
    "SELECT * FROM product_images
     WHERE product_id = ?
     ORDER BY is_primary DESC, id ASC"
);
$imgStmt->execute([$productId]);
$images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);

// Kiểm tra wishlist (nếu đã đăng nhập)
$isWishlisted = false;
$wishlistId   = null;
if (!empty($_SESSION['user_id'])) {
    $wStmt = $pdo->prepare(
        "SELECT id FROM wishlists
         WHERE user_id = ? AND product_id = ? LIMIT 1"
    );
    $wStmt->execute([$_SESSION['user_id'], $productId]);
    $wRow = $wStmt->fetch(PDO::FETCH_ASSOC);
    if ($wRow) {
        $isWishlisted = true;
        $wishlistId   = (int) $wRow['id'];
    }
}

// Lấy sản phẩm liên quan (cùng danh mục, loại trừ sản phẩm hiện tại)
$relStmt = $pdo->prepare(
    "SELECT p.*,
            (SELECT pi.image_path FROM product_images pi
             WHERE pi.product_id = p.id AND pi.is_primary = 1
             LIMIT 1) AS primary_image
     FROM products p
     WHERE p.category_id = ? AND p.id != ?
       AND p.deleted_at IS NULL AND p.status = 1
     ORDER BY RAND()
     LIMIT 8"
);
$relStmt->execute([$product['category_id'], $productId]);
$relatedProducts = $relStmt->fetchAll(PDO::FETCH_ASSOC);


$hasSale      = !empty($product['sale_price']) && (float)$product['sale_price'] < (float)$product['price'];
$displayPrice = $hasSale ? $product['sale_price'] : $product['price'];
$inStock      = (int)($product['stock'] ?? 0) > 0;
$isLoggedIn   = !empty($_SESSION['user_id']);

// Ảnh chính (primary hoặc ảnh đầu tiên trong danh sách)
$mainImage = getProductImageUrl($images[0]['image_path'] ?? null);?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> — TechGalaxy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --accent: #f59e0b;
            --bg-soft: #f8fafc;
            --border: #e2e8f0;
            --text-muted: #64748b;
            --card-radius: 16px;
            --shadow: 0 4px 20px rgba(0,0,0,.08);
        }
        body { font-family: 'Inter', sans-serif; background: var(--bg-soft); }
        h1,h2,h3,h4,h5,h6,.navbar-brand { font-family: 'Space Grotesk', sans-serif; }
        .navbar { background: #fff; border-bottom: 1px solid var(--border); }
        .navbar-brand { font-weight: 700; color: var(--primary) !important; font-size: 1.4rem; }

        /* ---- Gallery ---- */
        .gallery-main {
            border-radius: var(--card-radius);
            overflow: hidden;
            background: #fff;
            box-shadow: var(--shadow);
            aspect-ratio: 1 / 1;
            cursor: zoom-in;
        }
        .gallery-main img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform .3s ease;
        }
        .gallery-main:hover img { transform: scale(1.04); }

        .gallery-thumbs {
            display: flex;
            gap: .5rem;
            overflow-x: auto;
            padding-bottom: .25rem;
            scroll-snap-type: x mandatory;
        }
        .gallery-thumbs::-webkit-scrollbar { height: 4px; }
        .gallery-thumbs::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

        .thumb-item {
            flex: 0 0 72px;
            height: 72px;
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid transparent;
            cursor: pointer;
            transition: border-color .2s;
            scroll-snap-align: start;
            background: #fff;
        }
        .thumb-item img { width: 100%; height: 100%; object-fit: cover; }
        .thumb-item.active { border-color: var(--primary); }
        .thumb-item:hover { border-color: #93c5fd; }

        /* ---- Product Info ---- */
        .info-card {
            background: #fff;
            border-radius: var(--card-radius);
            box-shadow: var(--shadow);
            padding: 2rem;
        }
        .product-category {
            font-size: .78rem;
            color: var(--primary);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .08em;
        }
        .product-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.3;
            margin: .5rem 0 1rem;
        }
        .price-box { display: flex; align-items: baseline; gap: 1rem; margin-bottom: 1.25rem; }
        .price-current {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
        }
        .price-original {
            font-size: 1.1rem;
            color: var(--text-muted);
            text-decoration: line-through;
        }
        .price-discount {
            background: #fef3c7;
            color: #d97706;
            font-size: .8rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
        }
        .stock-badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-size: .85rem;
            font-weight: 500;
            padding: 5px 14px;
            border-radius: 20px;
            margin-bottom: 1.25rem;
        }
        .stock-in  { background: #dcfce7; color: #16a34a; }
        .stock-out { background: #fee2e2; color: #dc2626; }

        .divider { border: none; border-top: 1px solid var(--border); margin: 1.25rem 0; }

        /* ---- Buttons ---- */
        .btn-wishlist-toggle {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .65rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: .95rem;
            border: 2px solid var(--border);
            background: #fff;
            color: #374151;
            transition: all .2s;
            cursor: pointer;
        }
        .btn-wishlist-toggle:hover,
        .btn-wishlist-toggle.active {
            border-color: #ef4444;
            color: #ef4444;
            background: #fff5f5;
        }
        .btn-wishlist-toggle.active { background: #fee2e2; }
        .btn-wishlist-toggle i { font-size: 1.15rem; }

        /* ---- Description ---- */
        .desc-card {
            background: #fff;
            border-radius: var(--card-radius);
            box-shadow: var(--shadow);
            padding: 2rem;
            margin-top: 1.5rem;
        }
        .desc-card h5 {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #1e293b;
        }

        /* ---- Related Products ---- */
        .related-section { margin-top: 2.5rem; }
        .section-title {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            font-size: 1.3rem;
            color: #1e293b;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .related-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            overflow: hidden;
            transition: box-shadow .2s, transform .2s;
        }
        .related-card:hover { box-shadow: 0 8px 24px rgba(37,99,235,.13); transform: translateY(-3px); }
        .related-card__img { aspect-ratio: 4/3; overflow: hidden; background: var(--bg-soft); }
        .related-card__img img { width: 100%; height: 100%; object-fit: cover; transition: transform .35s; }
        .related-card:hover .related-card__img img { transform: scale(1.05); }
        .related-card__body { padding: .8rem 1rem; }
        .related-card__name { font-weight: 600; font-size: .9rem; color: #1e293b; margin-bottom: .25rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .related-card__price { font-family: 'Space Grotesk', sans-serif; font-weight: 700; color: var(--primary); font-size: .95rem; }

        /* Toast */
        .toast-container { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999; }

        /* Breadcrumb */
        .breadcrumb-item + .breadcrumb-item::before { color: var(--text-muted); }
        .breadcrumb-item a { text-decoration: none; color: var(--primary); }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="<?= BASE_URL ?>/"><i class="bi bi-stars me-1"></i>TechGalaxy</a>
        <div class="ms-auto d-flex align-items-center gap-2">
            <?php if ($isLoggedIn): ?>
                <a href="<?= BASE_URL ?>/wishlist" class="btn btn-outline-danger btn-sm"><i class="bi bi-heart me-1"></i>Yêu thích</a>
                <a href="<?= BASE_URL ?>/logout" class="btn btn-outline-secondary btn-sm">Đăng xuất</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login" class="btn btn-primary btn-sm">Đăng nhập</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container py-4">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/shop">Shop</a></li>
            <?php if (!empty($product['category_name'])): ?>
                <li class="breadcrumb-item">
                    <a href="<?= BASE_URL ?>/shop?category=<?= $product['category_id'] ?>">
                        <?= htmlspecialchars($product['category_name']) ?>
                    </a>
                </li>
            <?php endif; ?>
            <li class="breadcrumb-item active"><?= htmlspecialchars($product['name']) ?></li>
        </ol>
    </nav>

    <!-- ===== PRODUCT DETAIL ===== -->
    <div class="row g-4 mb-2">

        <!-- ---- CỘT ẢNH ---- -->
        <div class="col-lg-5">
            <!-- Ảnh chính -->
            <div class="gallery-main mb-3">
                <img src="<?= htmlspecialchars($mainImage) ?>"
                     alt="<?= htmlspecialchars($product['name']) ?>"
                     id="mainImg">
            </div>

            <!-- Thumbnail gallery -->
            <?php if (count($images) > 1): ?>
                <div class="gallery-thumbs">
                    <?php foreach ($images as $i => $img): ?>
                        <div class="thumb-item <?= $i === 0 ? 'active' : '' ?>"
                             onclick="switchImage(this, '<?= htmlspecialchars(getProductImageUrl($img['image_path'] ?? null)) ?>')">
                            <img src="<?= getProductImageUrl($img['image_path'] ?? null) ?>"
                                 alt="Ảnh <?= $i + 1 ?>"
                                 loading="lazy">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ---- CỘT THÔNG TIN ---- -->
        <div class="col-lg-7">
            <div class="info-card">
                <!-- Danh mục -->
                <div class="product-category">
                    <i class="bi bi-tag me-1"></i>
                    <?= htmlspecialchars($product['category_name'] ?? 'Chưa phân loại') ?>
                </div>

                <!-- Tên sản phẩm -->
                <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>

                <!-- Giá -->
                <div class="price-box">
                    <span class="price-current"><?= formatPrice((float)$displayPrice) ?></span>
                    <?php if ($hasSale): ?>
                        <span class="price-original"><?= formatPrice((float)$product['price']) ?></span>
                        <?php $pct = round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>
                        <span class="price-discount">Giảm <?= $pct ?>%</span>
                    <?php endif; ?>
                </div>

                <!-- Trạng thái kho -->
                <div>
                    <span class="stock-badge <?= $inStock ? 'stock-in' : 'stock-out' ?>">
                        <i class="bi bi-<?= $inStock ? 'check-circle' : 'x-circle' ?>"></i>
                        <?php if ($inStock): ?>
                            Còn hàng
                            <?php if ((int)$product['stock'] <= 10): ?>
                                (chỉ còn <?= $product['stock'] ?> sản phẩm)
                            <?php endif; ?>
                        <?php else: ?>
                            Hết hàng
                        <?php endif; ?>
                    </span>
                </div>

                <hr class="divider">

                <!-- Mô tả ngắn -->
                <?php if (!empty($product['description'])): ?>
                    <div class="mb-3" style="color:#374151; font-size:.93rem; line-height:1.65;">
                        <?= mb_substr(strip_tags($product['description']), 0, 300) ?>
                        <?= mb_strlen($product['description']) > 300 ? '...' : '' ?>
                    </div>
                    <hr class="divider">
                <?php endif; ?>

                <!-- ACTION BUTTONS -->
                <div class="d-flex flex-wrap gap-2">
                    <!-- Nút Wishlist (Toggle) -->
                    <button class="btn-wishlist-toggle <?= $isWishlisted ? 'active' : '' ?>"
                            id="wishlistBtn"
                            onclick="toggleWishlist(<?= $product['id'] ?>)"
                            <?= !$isLoggedIn ? 'title="Đăng nhập để thêm vào yêu thích"' : '' ?>>
                        <i class="bi bi-heart<?= $isWishlisted ? '-fill' : '' ?>" id="wishlistIcon"></i>
                        <span id="wishlistText">
                            <?= $isWishlisted ? 'Đã yêu thích' : 'Thêm vào yêu thích' ?>
                        </span>
                    </button>
                </div>

                <hr class="divider">

                <div class="d-flex flex-column gap-3 mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <span class="fw-medium text-muted">Số lượng:</span>
                        <div class="input-group" style="width: 130px;">
                            <button class="btn btn-outline-secondary" type="button" onclick="let q=document.getElementById('qty'); if(q.value>1) q.value--;"><i class="bi bi-dash"></i></button>
                            <input type="number" id="qty" class="form-control text-center fw-bold" value="1" min="1" max="<?= $product['stock'] ?>">
                            <button class="btn btn-outline-secondary" type="button" onclick="let q=document.getElementById('qty'); if(q.value < <?= $product['stock'] ?>) q.value++;"><i class="bi bi-plus"></i></button>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-primary fw-bold px-4 d-flex align-items-center gap-2" 
                                style="border-radius: 12px; font-size: 1.05rem;" 
                                onclick="addToCart(<?= $product['id'] ?>, document.getElementById('qty').value)" 
                                <?= !$inStock ? 'disabled' : '' ?>>
                            <i class="bi bi-cart-plus fs-5"></i> Thêm vào giỏ hàng
                        </button>

                        <button class="btn-wishlist-toggle <?= $isWishlisted ? 'active' : '' ?>"
                                id="wishlistBtn"
                                onclick="toggleWishlist(<?= $product['id'] ?>)"
                                <?= !$isLoggedIn ? 'title="Đăng nhập để thêm vào yêu thích"' : '' ?>>
                            <i class="bi bi-heart<?= $isWishlisted ? '-fill' : '' ?>" id="wishlistIcon"></i>
                            <span id="wishlistText">
                                <?= $isWishlisted ? 'Đã yêu thích' : 'Thêm vào yêu thích' ?>
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Thông tin thêm -->
                <div class="row g-2" style="font-size:.85rem; color:var(--text-muted);">
                    <div class="col-6 d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-success"></i> Bảo hành chính hãng
                    </div>
                    <div class="col-6 d-flex align-items-center gap-2">
                        <i class="bi bi-truck text-primary"></i> Giao hàng toàn quốc
                    </div>
                    <div class="col-6 d-flex align-items-center gap-2">
                        <i class="bi bi-arrow-counterclockwise text-warning"></i> Đổi trả 30 ngày
                    </div>
                    <div class="col-6 d-flex align-items-center gap-2">
                        <i class="bi bi-headset text-info"></i> Hỗ trợ 24/7
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== MÔ TẢ ĐẦY ĐỦ ===== -->
    <?php if (!empty($product['description'])): ?>
        <div class="desc-card">
            <h5><i class="bi bi-file-text me-2 text-primary"></i>Mô tả sản phẩm</h5>
            <div style="color:#374151; line-height:1.7; font-size:.95rem;">
                <?= $product['description'] ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- ===== SẢN PHẨM LIÊN QUAN ===== -->
    <?php if (!empty($relatedProducts)): ?>
        <div class="related-section">
            <div class="section-title">
                <i class="bi bi-grid-3x3-gap text-primary"></i>
                Sản phẩm liên quan
            </div>
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
                <?php foreach (array_slice($relatedProducts, 0, 8) as $rp): ?>
                    <?php
                    $rpHasSale = !empty($rp['sale_price']) && $rp['sale_price'] < $rp['price'];
                    $rpPrice   = $rpHasSale ? $rp['sale_price'] : $rp['price'];
                    $rpImg     = getProductImageUrl($rp['primary_image'] ?? null);
                    ?>
                    <div class="col">
                        <a href="<?= BASE_URL ?>/product/<?= $rp['id'] ?>" class="text-decoration-none">
                            <div class="related-card">
                                <div class="related-card__img">
                                    <img src="<?= htmlspecialchars($rpImg) ?>"
                                         alt="<?= htmlspecialchars($rp['name']) ?>"
                                         loading="lazy">
                                </div>
                                <div class="related-card__body">
                                    <div class="related-card__name"><?= htmlspecialchars($rp['name']) ?></div>
                                    <div class="related-card__price"><?= formatPrice((float)$rpPrice) ?></div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
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
const IS_LOGGED_IN   = <?= $isLoggedIn ? 'true' : 'false' ?>;
let   isWishlisted   = <?= $isWishlisted ? 'true' : 'false' ?>;

/* ---------- Gallery ---------- */
function switchImage(thumb, src) {
    document.getElementById('mainImg').src = src;
    document.querySelectorAll('.thumb-item').forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
}

/* ---------- Wishlist Toggle ---------- */
async function toggleWishlist(productId) {
    if (!IS_LOGGED_IN) {
        window.location.href = '<?= BASE_URL ?>/login?redirect=' + encodeURIComponent(window.location.href);
        return;
    }

    const btn  = document.getElementById('wishlistBtn');
    const icon = document.getElementById('wishlistIcon');
    const text = document.getElementById('wishlistText');
    btn.disabled = true;

    try {
        const fd = new FormData();
        fd.append('product_id', productId);

        const res  = await fetch('<?= BASE_URL ?>/ajax/wishlist/toggle', { method: 'POST', body: fd });
        const data = await res.json();

        if (data.success) {
            isWishlisted = data.wishlisted;

            if (isWishlisted) {
                btn.classList.add('active');
                icon.className = 'bi bi-heart-fill';
                text.textContent = 'Đã yêu thích';
            } else {
                btn.classList.remove('active');
                icon.className = 'bi bi-heart';
                text.textContent = 'Thêm vào yêu thích';
            }
            showToast(data.message, isWishlisted ? 'success' : 'info');
        } else {
            showToast(data.message || 'Có lỗi xảy ra.', 'danger');
        }
    } catch (err) {
        showToast('Không thể kết nối máy chủ.', 'danger');
    } finally {
        btn.disabled = false;
    }
}

function showToast(message, type = 'success') {
    const toastEl = document.getElementById('wishlistToast');
    const msgEl   = document.getElementById('toastMsg');
    const colorMap = { success: 'bg-success text-white', info: 'bg-info text-white', danger: 'bg-danger text-white' };
    toastEl.className = `toast align-items-center border-0 ${colorMap[type] || ''}`;
    msgEl.textContent = message;
    new bootstrap.Toast(toastEl, { delay: 3000 }).show();
}

/* ---------- Add To Cart (AJAX) - Đã Fix Triệt Để ---------- */
async function addToCart(productId, quantity = 1) {
    try {
        const pId = parseInt(productId) || 0;
        const qty = parseInt(quantity) || 1;

        if (pId <= 0) {
            alert("Lỗi: Không lấy được ID sản phẩm.");
            return;
        }

        // LỚP BẢO VỆ 1: Loại bỏ dấu gạch chéo thừa ở BASE_URL để chống Redirect 301 làm rớt POST
        let baseUrl = '<?= BASE_URL ?>'.replace(/\/+$/, '');
        let url = baseUrl + '/cart/add';

        // LỚP BẢO VỆ 2: Đóng gói chuẩn JSON để không bị giới hạn bởi Server
        const res = await fetch(url, { 
            method: 'POST', 
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                product_id: pId,
                quantity: qty
            })
        });
        
        const data = await res.json();

        if (data.success) {
            // Thông báo thành công
            if (typeof showToast === 'function') showToast(data.message, 'success');
            else alert(data.message);
            
            // Cập nhật số lượng trên Navbar
            const badge = document.getElementById('cartCountBadge');
            if (badge) {
                badge.textContent = data.cartCount;
                badge.classList.remove('d-none');
            }
        } else {
            // Thông báo lỗi từ PHP (VD: Hết hàng, Vượt tồn kho)
            if (typeof showToast === 'function') showToast(data.message, 'danger');
            else alert(data.message);
        }
    } catch (err) {
        console.error("Lỗi quá trình Fetch:", err);
        if (typeof showToast === 'function') showToast('Có lỗi xảy ra. Hãy tải lại trang!', 'danger');
        else alert('Có lỗi xảy ra. Hãy tải lại trang!');
    }
}

</script>
</body>
</html>