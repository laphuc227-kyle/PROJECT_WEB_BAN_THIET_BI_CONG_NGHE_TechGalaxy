<?php
// ===== LẤY DỮ LIỆU SẢN PHẨM & DANH MỤC =====
require_once __DIR__ . '/../../models/Post.php'; // đã có BaseModel
require_once __DIR__ . '/../../config/database.php';

global $pdo;

// Lấy tham số từ URL
$currentPage       = max(1, (int) ($_GET['page'] ?? 1));
$searchKeyword     = trim($_GET['q'] ?? '');
$selectedCategory  = $_GET['category'] ?? ''; // có thể là slug hoặc id
$perPage           = 12;
$offset            = ($currentPage - 1) * $perPage;

// Xây dựng điều kiện WHERE động
$where  = "p.deleted_at IS NULL AND p.status = 1";
$params = [];

if ($searchKeyword !== '') {
    // Tìm kiếm theo: Tên sản phẩm HOẶC Mô tả sản phẩm HOẶC Tên danh mục
    $where   .= " AND (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)";
    $params[] = "%{$searchKeyword}%"; 
    $params[] = "%{$searchKeyword}%"; 
    $params[] = "%{$searchKeyword}%"; 
}

if ($selectedCategory !== '') {
    // Hỗ trợ cả slug (vd: "laptop") lẫn id (vd: "2")
    if (is_numeric($selectedCategory)) {
        $where   .= " AND p.category_id = ?";
        $params[] = (int) $selectedCategory;
    } else {
        $where   .= " AND c.slug = ?";
        $params[] = $selectedCategory;
    }
}

// Đếm tổng sản phẩm
$countStmt = $pdo->prepare(
    "SELECT COUNT(*) FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE {$where}"
);
$countStmt->execute($params);
$totalProducts = (int) $countStmt->fetchColumn();
$totalPages    = (int) ceil($totalProducts / $perPage);

// Lấy danh sách sản phẩm theo trang
$productParams   = array_merge($params, [$perPage, $offset]);
$productStmt     = $pdo->prepare(
    "SELECT p.*, c.name AS category_name, c.slug AS category_slug,
            (SELECT pi.image_path FROM product_images pi
             WHERE pi.product_id = p.id AND pi.is_primary = 1
             LIMIT 1) AS primary_image
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE {$where}
     ORDER BY p.created_at DESC
     LIMIT ? OFFSET ?"
);
// Bind LIMIT và OFFSET riêng vì cần PARAM_INT
foreach ($params as $i => $val) {
    $productStmt->bindValue($i + 1, $val);
}
$productStmt->bindValue(count($params) + 1, $perPage, PDO::PARAM_INT);
$productStmt->bindValue(count($params) + 2, $offset,  PDO::PARAM_INT);
$productStmt->execute();
$products = $productStmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh mục cho sidebar
$catStmt = $pdo->prepare(
    "SELECT c.*, COUNT(p.id) AS product_count
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.id
         AND p.deleted_at IS NULL AND p.status = 1
     GROUP BY c.id
     ORDER BY c.name ASC"
);
$catStmt->execute();
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
/**
 * views/user/shop.php
 *
 * Biến được truyền từ ProductController::shop():
 *  - $products        array   Danh sách sản phẩm
 *  - $categories      array   Danh sách danh mục (sidebar)
 *  - $totalProducts   int     Tổng số sản phẩm
 *  - $totalPages      int     Tổng số trang
 *  - $currentPage     int     Trang hiện tại
 *  - $searchKeyword   string  Từ khóa tìm kiếm
 *  - $selectedCategory int|null  ID danh mục đang lọc
 */


// Helper: tạo URL query string giữ các tham số hiện tại, chỉ thay thế 1 tham số
function buildQuery(array $override = []): string
{
    $params = array_merge([
        'q'        => $_GET['q']        ?? '',
        'category' => $_GET['category'] ?? '',
        'page'     => $_GET['page']     ?? 1,
    ], $override);
    // Loại bỏ tham số rỗng
    $params = array_filter($params, fn($v) => $v !== '' && $v !== null);
    return '?' . http_build_query($params);
}

// Kiểm tra user đã đăng nhập
$isLoggedIn = !empty($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop — TechGalaxy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --accent: #f59e0b;
            --bg-soft: #f8fafc;
            --border: #e2e8f0;
            --text-muted: #64748b;
            --card-radius: 14px;
            --shadow-sm: 0 2px 8px rgba(0,0,0,.06);
            --shadow-hover: 0 8px 24px rgba(37,99,235,.15);
        }
        body { font-family: 'Inter', sans-serif; background: var(--bg-soft); }
        h1,h2,h3,h4,h5,h6,.navbar-brand { font-family: 'Space Grotesk', sans-serif; }

        /* ---- Navbar ---- */
        .navbar { background: #fff; border-bottom: 1px solid var(--border); }
        .navbar-brand { font-weight: 700; color: var(--primary) !important; font-size: 1.4rem; }

        /* ---- Sidebar ---- */
        .sidebar-card {
            background: #fff;
            border-radius: var(--card-radius);
            box-shadow: var(--shadow-sm);
            padding: 1.5rem;
            margin-bottom: 1.25rem;
        }
        .sidebar-card h6 {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            font-size: .8rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 1rem;
        }
        .category-link {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: .45rem .6rem;
            border-radius: 8px;
            text-decoration: none;
            color: #374151;
            font-size: .9rem;
            transition: background .15s, color .15s;
        }
        .category-link:hover, .category-link.active {
            background: #eff6ff;
            color: var(--primary);
            font-weight: 500;
        }
        .category-link .badge {
            background: var(--border);
            color: var(--text-muted);
            font-size: .7rem;
            border-radius: 20px;
        }
        .category-link.active .badge {
            background: var(--primary);
            color: #fff;
        }

        /* ---- Product Card ---- */
        .product-card {
            background: #fff;
            border-radius: var(--card-radius);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            transition: box-shadow .25s, transform .25s;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .product-card:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-4px);
        }
        .product-card__img-wrap {
            position: relative;
            overflow: hidden;
            aspect-ratio: 4/3;
            background: var(--bg-soft);
        }
        .product-card__img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .4s ease;
        }
        .product-card:hover .product-card__img-wrap img {
            transform: scale(1.06);
        }
        .product-card__badge {
            position: absolute;
            top: .6rem;
            left: .6rem;
            background: #ef4444;
            color: #fff;
            font-size: .7rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 20px;
        }
        .btn-wishlist {
            position: absolute;
            top: .6rem;
            right: .6rem;
            width: 36px; height: 36px;
            border-radius: 50%;
            background: rgba(255,255,255,.92);
            border: none;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,.12);
            transition: background .2s, transform .2s;
            font-size: 1.1rem;
            color: #94a3b8;
        }
        .btn-wishlist:hover { background: #fff; transform: scale(1.12); color: #ef4444; }
        .btn-wishlist.active { color: #ef4444; }
        .product-card__body {
            padding: 1rem 1rem .8rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .product-card__category {
            font-size: .72rem;
            color: var(--primary);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: .3rem;
        }
        .product-card__name {
            font-family: 'Space Grotesk', sans-serif;
            font-size: .95rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: .5rem;
            line-height: 1.35;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .product-card__price {
            margin-top: auto;
            display: flex;
            align-items: baseline;
            gap: .5rem;
        }
        .price-current {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--primary);
        }
        .price-original {
            font-size: .82rem;
            color: var(--text-muted);
            text-decoration: line-through;
        }
        .product-card__footer {
            padding: .6rem 1rem;
            border-top: 1px solid var(--border);
            display: flex;
            gap: .4rem;
        }
        .btn-detail {
            flex: 1;
            padding: .4rem;
            font-size: .82rem;
            border-radius: 8px;
        }

        /* ---- Stock badge ---- */
        .stock-badge {
            font-size: .7rem;
            padding: 2px 8px;
            border-radius: 20px;
        }
        .stock-in  { background: #dcfce7; color: #16a34a; }
        .stock-out { background: #fee2e2; color: #dc2626; }

        /* ---- Search bar ---- */
        .search-bar .form-control:focus { box-shadow: 0 0 0 3px rgba(37,99,235,.18); border-color: var(--primary); }

        /* ---- Pagination ---- */
        .page-link { border-radius: 8px !important; margin: 0 2px; color: var(--primary); }
        .page-item.active .page-link { background: var(--primary); border-color: var(--primary); }

        /* ---- Results info ---- */
        .results-info { color: var(--text-muted); font-size: .88rem; }

        /* ---- Empty state ---- */
        .empty-state { text-align: center; padding: 4rem 2rem; color: var(--text-muted); }
        .empty-state i { font-size: 3.5rem; opacity: .35; margin-bottom: 1rem; }

        /* Toast notification */
        .toast-container { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999; }
    </style>
</head>
<body>

<!-- ===== NAVBAR ===== -->
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
    <div class="row g-4">

        <!-- ===== SIDEBAR ===== -->
        <div class="col-lg-3">
            <!-- Search -->
            <div class="sidebar-card">
                <h6><i class="bi bi-search me-1"></i>Tìm kiếm</h6>
                <form method="GET" class="search-bar">
                    <?php if ($selectedCategory): ?>
                        <input type="hidden" name="category" value="<?= htmlspecialchars($selectedCategory) ?>">
                    <?php endif; ?>
                    <div class="input-group">
                        <input type="text" name="q" class="form-control form-control-sm"
                               placeholder="Tên sản phẩm..."
                               value="<?= htmlspecialchars($searchKeyword) ?>">
                        <button class="btn btn-primary btn-sm" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Danh mục -->
            <div class="sidebar-card">
                <h6><i class="bi bi-grid me-1"></i>Danh mục</h6>
                <nav class="d-flex flex-column gap-1">
                    <!-- Tất cả -->
                    <a href="<?= buildQuery(['category' => '', 'page' => 1]) ?>"
                       class="category-link <?= $selectedCategory === null ? 'active' : '' ?>">
                        <span><i class="bi bi-collection me-1"></i>Tất cả sản phẩm</span>
                        <span class="badge"><?= $totalProducts ?></span>
                    </a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="<?= buildQuery(['category' => $cat['id'], 'page' => 1]) ?>"
                           class="category-link <?= (int)$selectedCategory === (int)$cat['id'] ? 'active' : '' ?>">
                            <span><?= htmlspecialchars($cat['name']) ?></span>
                            <span class="badge"><?= $cat['product_count'] ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>
        </div>

        <!-- ===== MAIN CONTENT ===== -->
        <div class="col-lg-9">

            <!-- Header + Results info -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0 fw-bold">
                        <?php if (!empty($searchKeyword)): ?>
                            Kết quả cho "<span class="text-primary"><?= htmlspecialchars($searchKeyword) ?></span>"
                        <?php elseif ($selectedCategory): ?>
                            <?php
                            $catName = '';
                            foreach ($categories as $c) {
                                if ((int)$c['id'] === (int)$selectedCategory) { $catName = $c['name']; break; }
                            }
                            echo htmlspecialchars($catName);
                            ?>
                        <?php else: ?>
                            Tất cả sản phẩm
                        <?php endif; ?>
                    </h5>
                    <p class="results-info mb-0">
                        <?= number_format($totalProducts) ?> sản phẩm
                        — Trang <?= $currentPage ?> / <?= max(1, $totalPages) ?>
                    </p>
                </div>

                <!-- Clear filter -->
                <?php if (!empty($searchKeyword) || $selectedCategory): ?>
                    <a href="<?= BASE_URL ?>/shop" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-x-circle me-1"></i>Xóa bộ lọc
</a>
                <?php endif; ?>
            </div>

            <!-- ===== PRODUCT GRID ===== -->
            <?php if (empty($products)): ?>
                <div class="empty-state">
    <div><i class="bi bi-search"></i></div>
    <h5 class="fw-semibold">Không tìm thấy sản phẩm</h5>
    <p>Thử tìm kiếm với từ khóa khác hoặc xóa bộ lọc.</p>
    <a href="<?= BASE_URL ?>/shop" class="btn btn-primary">Xem tất cả sản phẩm</a>
</div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 g-3 mb-4">
                    <?php foreach ($products as $p): ?>
                        <?php
                        $hasSale    = !empty($p['sale_price']) && $p['sale_price'] < $p['price'];
                        $displayPrice = $hasSale ? $p['sale_price'] : $p['price'];
                        $inStock    = (int)($p['stock'] ?? 0) > 0;
                        $imgSrc = !empty($p['primary_image']) ? BASE_URL . '/' . ltrim($p['primary_image'], '/') : BASE_URL . '/public/assets/img/no-image.png';                        ?>
                        <div class="col">
                            <div class="product-card">
                                <!-- Ảnh -->
                                <div class="product-card__img-wrap">
                                    <img src="<?= htmlspecialchars($imgSrc) ?>"
                                         alt="<?= htmlspecialchars($p['name']) ?>"
                                         loading="lazy">

                                    <?php if ($hasSale): ?>
                                        <?php
                                        $pct = round((($p['price'] - $p['sale_price']) / $p['price']) * 100);
                                        ?>
                                        <span class="product-card__badge">-<?= $pct ?>%</span>
                                    <?php endif; ?>

                                    <!-- Nút Wishlist -->
                                    <button class="btn-wishlist <?= !empty($p['is_wishlisted']) ? 'active' : '' ?>"
                                            data-product-id="<?= $p['id'] ?>"
                                            title="Thêm vào yêu thích"
                                            onclick="toggleWishlist(this, <?= $p['id'] ?>)">
                                        <i class="bi bi-heart<?= !empty($p['is_wishlisted']) ? '-fill' : '' ?>"></i>
                                    </button>
                                </div>

                                <!-- Thông tin -->
                                <div class="product-card__body">
                                    <div class="product-card__category">
                                        <?= htmlspecialchars($p['category_name'] ?? '') ?>
                                    </div>
                                    <div class="product-card__name">
                                        <?= htmlspecialchars($p['name']) ?>
                                    </div>
                                    <div class="product-card__price">
                                        <span class="price-current"><?= formatPrice((float)$displayPrice) ?></span>
                                        <?php if ($hasSale): ?>
                                            <span class="price-original"><?= formatPrice((float)$p['price']) ?></span>
                                        <?php endif; ?>
                                        <span class="stock-badge ms-auto <?= $inStock ? 'stock-in' : 'stock-out' ?>">
                                            <?= $inStock ? 'Còn hàng' : 'Hết hàng' ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Footer actions -->
                                <div class="product-card__footer">
    <a href="<?= BASE_URL ?>/product/<?= $p['id'] ?>"
       class="btn btn-outline-primary btn-detail">
        <i class="bi bi-eye me-1"></i>Xem chi tiết
    </a>
</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- ===== PAGINATION ===== -->
                <?php if ($totalPages > 1): ?>
                    <nav>
                        <ul class="pagination justify-content-center flex-wrap gap-1">
                            <!-- Prev -->
                            <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= buildQuery(['page' => $currentPage - 1]) ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>

                            <?php
                            // Hiển thị trang đầu, dấu ..., các trang lân cận, dấu ..., trang cuối
                            $window = 2; // số trang hiển thị mỗi bên trang hiện tại
                            $start  = max(1, $currentPage - $window);
                            $end    = min($totalPages, $currentPage + $window);

                            if ($start > 1): ?>
                                <li class="page-item"><a class="page-link" href="<?= buildQuery(['page' => 1]) ?>">1</a></li>
                                <?php if ($start > 2): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                            <?php endif; ?>

                            <?php for ($p = $start; $p <= $end; $p++): ?>
                                <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= buildQuery(['page' => $p]) ?>"><?= $p ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($end < $totalPages): ?>
                                <?php if ($end < $totalPages - 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                                <li class="page-item"><a class="page-link" href="<?= buildQuery(['page' => $totalPages]) ?>"><?= $totalPages ?></a></li>
                            <?php endif; ?>

                            <!-- Next -->
                            <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= buildQuery(['page' => $currentPage + 1]) ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div><!-- /col main -->
    </div><!-- /row -->
</div><!-- /container -->

<!-- ===== TOAST ===== -->
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
const IS_LOGGED_IN = <?= $isLoggedIn ? 'true' : 'false' ?>;

/**
 * Toggle Wishlist qua AJAX
 * Nếu chưa đăng nhập → chuyển sang trang login
 */
async function toggleWishlist(btn, productId) {
    if (!IS_LOGGED_IN) {
        window.location.href = '/login?redirect=' + encodeURIComponent(window.location.href);
        return;
    }

    btn.disabled = true; // chống double-click

    try {
        const formData = new FormData();
        formData.append('product_id', productId);

       const res = await fetch('<?= BASE_URL ?>/ajax/wishlist/toggle', {
    method: 'POST',
    body: formData,
});
        const data = await res.json();

        if (data.success) {
            const icon = btn.querySelector('i');
            const isNowWishlisted = data.wishlisted;

            if (isNowWishlisted) {
                btn.classList.add('active');
                icon.className = 'bi bi-heart-fill';
            } else {
                btn.classList.remove('active');
                icon.className = 'bi bi-heart';
            }
            showToast(data.message, isNowWishlisted ? 'success' : 'info');
        } else {
            showToast(data.message || 'Có lỗi xảy ra.', 'danger');
            if (data.redirect) window.location.href = data.redirect;
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

    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();
}
</script>
</body>
</html>