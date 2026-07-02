<?php
global $pdo;

// Lấy 4 sản phẩm nổi bật (is_featured = 1, status = 1, chưa bị xóa)
$featStmt = $pdo->prepare(
    "SELECT p.*, c.name AS category_name,
            (SELECT pi.image_path FROM product_images pi 
             WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) AS primary_image
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.deleted_at IS NULL AND p.status = 1 AND p.is_featured = 1
     ORDER BY p.created_at DESC
     LIMIT 4"
);
$featStmt->execute();
$featuredProducts = $featStmt->fetchAll(PDO::FETCH_ASSOC);
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../../config/app.php';
}
// Lấy 8 sản phẩm MỚI NHẤT (dựa vào created_at)
$latestStmt = $pdo->prepare(
    "SELECT p.*, c.name AS category_name,
            (SELECT pi.image_path FROM product_images pi 
             WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) AS primary_image
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.deleted_at IS NULL AND p.status = 1
     ORDER BY p.created_at DESC
     LIMIT 8"
);
$latestStmt->execute();
$latestProducts = $latestStmt->fetchAll(PDO::FETCH_ASSOC);


$pageTitle = 'Trang chủ';
$pageDesc  = 'TechGalaxy - Thiết bị công nghệ & phụ kiện điện tử chính hãng, giá tốt nhất';
$extraCSS  = '<link rel="stylesheet" href="' . BASE_URL . '/assets/css/home.css">';
$currentPage = 'home';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

$img = BASE_URL . '/assets/images';
$heroSlides = [
    [
        'badge'    => '🔥 Hot Deal',
        'title'    => "Công nghệ đỉnh cao\ngiá không tưởng",
        'highlight'=> 'không tưởng',
        'subtitle' => 'Khám phá hàng nghìn sản phẩm chính hãng với giá cạnh tranh. Giao hàng toàn quốc, bảo hành chính hãng.',
        'cta'      => ['text' => 'Mua ngay', 'href' => BASE_URL . '/shop'],
        'cta2'     => ['text' => 'Khám phá', 'href' => BASE_URL . '/shop?sort=newest'],
        'image'    => $img . '/hero/iphone-hero.png',
        'stats'    => [
            ['number' => '10K+', 'label' => 'Sản phẩm'],
            ['number' => '50K+', 'label' => 'Khách hàng'],
            ['number' => '4.9★', 'label' => 'Đánh giá'],
        ]
    ],
    [
        'badge'    => '💻 Mới ra mắt',
        'title'    => "Laptop gaming\nhiệu suất vượt trội",
        'highlight'=> 'vượt trội',
        'subtitle' => 'Dòng laptop gaming mới nhất với GPU thế hệ mới, màn hình 165Hz, pin trâu 72Wh.',
        'cta'      => ['text' => 'Xem ngay', 'href' => BASE_URL . '/shop?category=9'],
        'cta2'     => ['text' => 'So sánh', 'href' => BASE_URL . '/shop?category=9&sort=featured'],
        'image'    => $img . '/hero/laptop-hero.png',
        'stats'    => [
            ['number' => '500+', 'label' => 'Model laptop'],
            ['number' => '12T+', 'label' => 'Doanh thu'],
            ['number' => '2h',   'label' => 'Giao hàng nội thành'],
        ]
    ],
];

// Danh sách các danh mục muốn hiển thị ra trang chủ (slug chính là ID thật)
$categories = [
    ['icon' => 'fa-mobile-screen',       'name' => 'Apple',        'slug' => '5'],
    ['icon' => 'fa-laptop',              'name' => 'MacBook',      'slug' => '8'],
    ['icon' => 'fa-headphones',          'name' => 'Tai nghe',     'slug' => '10'],
    ['icon' => 'fa-watch',               'name' => 'Smartwatch',   'slug' => '4'],
    ['icon' => 'fa-mobile-screen',       'name' => 'Samsung',      'slug' => '6'],
    ['icon' => 'fa-keyboard',            'name' => 'Asus',         'slug' => '9'],
];

// Chạy vòng lặp để đếm số lượng sản phẩm THẬT từ Database cho từng danh mục
foreach ($categories as &$cat) {
    $countStmt = $pdo->prepare(
        "SELECT COUNT(id) FROM products 
         WHERE category_id = ? AND status = 1 AND deleted_at IS NULL"
    );
    $countStmt->execute([$cat['slug']]); // Truyền ID danh mục vào để đếm
    $cat['count'] = $countStmt->fetchColumn(); // Gắn kết quả đếm được vào biến count
}
unset($cat); // Xóa tham chiếu sau khi vòng lặp kết thúc cho an toàn

$latestProducts = [
    ['id' => 11, 'name' => 'Samsung Galaxy S24 Ultra', 'slug' => 'galaxy-s24-ultra', 'price' => 31990000, 'sale_price' => null, 'image' => $img . '/products/product-1.jpg', 'category_name' => 'Điện thoại', 'is_new' => true, 'in_wishlist' => false],
    ['id' => 12, 'name' => 'Dell XPS 15 9530', 'slug' => 'dell-xps-15', 'price' => 42990000, 'sale_price' => 39990000, 'image' => $img . '/products/product-2.jpg', 'category_name' => 'Laptop', 'is_new' => true, 'in_wishlist' => false],
    ['id' => 13, 'name' => 'AirPods Pro 2 USB-C', 'slug' => 'airpods-pro-2', 'price' => 5990000, 'sale_price' => null, 'image' => $img . '/products/product-3.jpg', 'category_name' => 'Tai nghe', 'is_new' => true, 'in_wishlist' => false],
    ['id' => 14, 'name' => 'Xiaomi 14 Ultra', 'slug' => 'xiaomi-14-ultra', 'price' => 26990000, 'sale_price' => 24990000, 'image' => $img . '/products/product-4.jpg', 'category_name' => 'Điện thoại', 'is_new' => true, 'in_wishlist' => false],
    ['id' => 15, 'name' => 'LG Gram 16 2024', 'slug' => 'lg-gram-16', 'price' => 32990000, 'sale_price' => null, 'image' => $img . '/products/product-5.jpg', 'category_name' => 'Laptop', 'is_new' => true, 'in_wishlist' => false],
    ['id' => 16, 'name' => 'Apple Watch Series 9', 'slug' => 'apple-watch-9', 'price' => 10990000, 'sale_price' => 9990000, 'image' => $img . '/products/product-6.jpg', 'category_name' => 'Smartwatch', 'is_new' => false, 'in_wishlist' => false],
    ['id' => 17, 'name' => 'Keychron Q1 Pro', 'slug' => 'keychron-q1-pro', 'price' => 4590000, 'sale_price' => null, 'image' => $img . '/products/product-7.jpg', 'category_name' => 'Phụ kiện', 'is_new' => true, 'in_wishlist' => false],
    ['id' => 18, 'name' => 'OnePlus 12', 'slug' => 'oneplus-12', 'price' => 18990000, 'sale_price' => 16990000, 'image' => $img . '/products/product-8.jpg', 'category_name' => 'Điện thoại', 'is_new' => true, 'in_wishlist' => false],
];

require_once __DIR__ . '/../../models/Post.php';
$postModel = new Post();
$latestPosts = $postModel->getLatest(3);

$brands = [
    ['name' => 'Apple',    'logo' => $img . '/brands/apple.svg'],
    ['name' => 'Samsung',  'logo' => $img . '/brands/samsung.svg'],
    ['name' => 'Sony',     'logo' => $img . '/brands/sony.svg'],
    ['name' => 'LG',       'logo' => $img . '/brands/lg.svg'],
    ['name' => 'ASUS',     'logo' => $img . '/brands/asus.svg'],
    ['name' => 'Dell',     'logo' => $img . '/brands/dell.svg'],
    ['name' => 'Xiaomi',   'logo' => $img . '/brands/xiaomi.svg'],
    ['name' => 'Logitech', 'logo' => $img . '/brands/logitech.svg'],
];
?>

<!-- ════════════════════════════════════════════════════════════════ -->
<!-- SECTION 1: HERO BANNER                                          -->
<!-- ════════════════════════════════════════════════════════════════ -->
<section class="hero-section" aria-label="Slide quảng cáo">
  <div class="swiper hero-swiper">
    <div class="swiper-wrapper">
      <?php foreach ($heroSlides as $slide): ?>
      <div class="swiper-slide">
        <div class="hero-slide container-xl">
          <div class="row align-items-center g-0" style="width:100%">

            <!-- Nội dung -->
            <div class="col-lg-6">
              <div class="hero-content">
                <span class="hero-badge">
                  <i class="fa-solid fa-bolt"></i>
                  <?= htmlspecialchars($slide['badge']) ?>
                </span>

                <h1 class="hero-title"><?= formatHeroTitle($slide['title'], $slide['highlight']) ?></h1>

                <p class="hero-subtitle">
                  <?= htmlspecialchars($slide['subtitle']) ?>
                </p>

                <div class="hero-actions">
                  <a href="<?= htmlspecialchars($slide['cta']['href']) ?>"
                     class="btn-hero-primary">
                    <?= htmlspecialchars($slide['cta']['text']) ?>
                    <i class="fa-solid fa-arrow-right"></i>
                  </a>
                  <a href="<?= htmlspecialchars($slide['cta2']['href']) ?>"
                     class="btn-hero-outline">
                    <?= htmlspecialchars($slide['cta2']['text']) ?>
                  </a>
                </div>

                <div class="hero-stats">
                  <?php foreach ($slide['stats'] as $stat): ?>
                  <div class="hero-stat">
                    <div class="hero-stat__number"><?= htmlspecialchars($stat['number']) ?></div>
                    <div class="hero-stat__label"><?= htmlspecialchars($stat['label']) ?></div>
                  </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>

            <!-- Ảnh sản phẩm -->
            <div class="col-lg-6 d-none d-lg-flex">
              <div class="hero-image-wrap" style="width:100%">
                <div class="hero-image-glow"></div>
                <img src="<?= htmlspecialchars($slide['image']) ?>"
                     alt="<?= htmlspecialchars($slide['badge']) ?>"
                     class="hero-image mx-auto">
              </div>
            </div>

          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="swiper-pagination"></div>
  </div>
</section>

<!-- ════════════════════════════════════════════════════════════════ -->
<!-- SECTION 2: TRUST BADGES                                         -->
<!-- ════════════════════════════════════════════════════════════════ -->
<section class="trust-section" aria-label="Cam kết dịch vụ">
  <div class="container-xl">
    <div class="row g-4 justify-content-center">
      <?php
      $trustItems = [
          ['icon' => 'fa-truck-fast',     'title' => 'Giao hàng nhanh',       'desc' => 'Giao trong 2h nội thành, toàn quốc 1–3 ngày'],
          ['icon' => 'fa-shield-halved',  'title' => 'Bảo hành chính hãng',   'desc' => 'Bảo hành 12–24 tháng, đổi mới trong 30 ngày'],
          ['icon' => 'fa-rotate-left',    'title' => 'Đổi trả dễ dàng',       'desc' => 'Đổi trả miễn phí trong 7 ngày nếu lỗi'],
          ['icon' => 'fa-headset',        'title' => 'Hỗ trợ 24/7',           'desc' => 'Tư vấn viên sẵn sàng hỗ trợ bất kỳ lúc nào'],
      ];
      foreach ($trustItems as $item): ?>
      <div class="col-6 col-md-3">
        <div class="trust-item">
          <div class="trust-item__icon">
            <i class="fa-solid <?= $item['icon'] ?>"></i>
          </div>
          <div>
            <div class="trust-item__title"><?= htmlspecialchars($item['title']) ?></div>
            <div class="trust-item__desc"><?= htmlspecialchars($item['desc']) ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════════════════════════════════ -->
<!-- SECTION 3: DANH MỤC SẢN PHẨM                                   -->
<!-- ════════════════════════════════════════════════════════════════ -->
<section class="categories-section section-gap" aria-label="Danh mục sản phẩm">
  <div class="container-xl">
    <div class="section-header text-center">
      <h2 class="section-title">Danh mục <span>nổi bật</span></h2>
      <p class="section-subtitle mt-2">Khám phá hàng nghìn sản phẩm theo từng danh mục</p>
    </div>

    <div class="row row-cols-3 row-cols-md-6 g-3 mt-2">
      <?php foreach ($categories as $cat): ?>
      <div class="col">
        <a href="<?= BASE_URL ?>/shop?category=<?= htmlspecialchars($cat['slug']) ?>"
           class="category-card">
          <div class="category-card__icon">
            <i class="fa-solid <?= $cat['icon'] ?>"></i>
          </div>
          <span class="category-card__name"><?= htmlspecialchars($cat['name']) ?></span>
          <span class="category-card__count"><?= $cat['count'] ?> sản phẩm</span>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════════════════════════════════ -->
<!-- SECTION 4: SẢN PHẨM NỔI BẬT                                    -->
<!-- ════════════════════════════════════════════════════════════════ -->
<section class="featured-section" aria-label="Sản phẩm nổi bật">
  <div class="container-xl">
    <div class="section-header d-flex align-items-end justify-content-between flex-wrap gap-3">
      <div>
        <h2 class="section-title">Sản phẩm <span>nổi bật</span></h2>
        <p class="section-subtitle mt-1">Được yêu thích nhất tháng này</p>
      </div>
      <a href="<?= BASE_URL ?>/shop?sort=featured" class="btn-outline-tg btn-sm">
        Xem tất cả <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>

    <!-- Swiper carousel trên mobile, grid trên desktop -->
    <div class="swiper featured-swiper mt-4">
  <div class="swiper-wrapper">
    <?php foreach ($featuredProducts as $p): ?>
        <?php
        $hasSale = !empty($p['sale_price']) && $p['sale_price'] < $p['price'];
        $displayPrice = $hasSale ? $p['sale_price'] : $p['price'];
        $imgSrc = !empty($p['primary_image']) ? BASE_URL . '/' . ltrim($p['primary_image'], '/') : BASE_URL . '/public/assets/img/no-image.png';
        ?>
        <div class="swiper-slide h-auto">
            <div class="product-card">
                <div class="product-card__img-wrap">
                    <a href="<?= BASE_URL ?>/product/<?= $p['id'] ?>">
                        <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="img-fluid" style="width:100%; height:100%; object-fit:cover;">
                    </a>
                    <?php if ($hasSale): ?>
                        <span class="product-card__badge" style="position:absolute; top:10px; left:10px; background:#ef4444; color:#fff; padding:2px 8px; border-radius:12px; font-size:0.75rem;">-<?= round((($p['price'] - $p['sale_price']) / $p['price']) * 100) ?>%</span>
                    <?php endif; ?>
                </div>
                <div class="product-card__body" style="padding: 1rem;">
                    <div class="product-card__category" style="font-size: 0.75rem; color: #2563eb; text-transform: uppercase; font-weight: 600;"><?= htmlspecialchars($p['category_name'] ?? 'Công nghệ') ?></div>
                    <a href="<?= BASE_URL ?>/product/<?= $p['id'] ?>" class="text-decoration-none">
                        <div class="product-card__name" style="color: #1e293b; font-weight: 600; font-size: 1rem; margin: 0.5rem 0;"><?= htmlspecialchars($p['name']) ?></div>
                    </a>
                    <div class="product-card__price">
                        <span class="price-current" style="color: #2563eb; font-weight: 700; font-size: 1.1rem;"><?= formatPrice((float)$displayPrice) ?></span>
                        <?php if ($hasSale): ?>
                            <span class="price-original text-muted text-decoration-line-through ms-2" style="font-size: 0.85rem;"><?= formatPrice((float)$p['price']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="product-card__price">
                         </div>
                    <button class="btn btn-primary w-100 mt-3 btn-sm fw-medium" style="border-radius: 8px;" onclick="addToCart(<?= $p['id'] ?>)" <?= !(($p['stock'] ?? 1) > 0) ? 'disabled' : '' ?>>
                      <i class="bi bi-cart-plus me-1"></i> Thêm vào giỏ
                    </button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
  </div>
  <div class="swiper-pagination"></div>
</div>
</section>

<!-- ════════════════════════════════════════════════════════════════ -->
<!-- SECTION 5: PROMO BANNERS                                         -->
<!-- ════════════════════════════════════════════════════════════════ -->
<section class="promo-section" aria-label="Khuyến mãi">
  <div class="container-xl">
    <div class="row g-4">

      <!-- Banner lớn bên trái -->
      <div class="col-12 col-lg-7">
        <div class="promo-banner promo-banner--blue">
          <div class="promo-banner__content">
            <span class="promo-tag">⚡ Flash Sale</span>
            <h3 class="promo-banner__title">
              iPhone 15 Series<br>Giảm đến <strong>5 triệu</strong>
            </h3>
            <p class="promo-banner__subtitle">Chỉ còn 12 giờ · Số lượng có hạn</p>
            <a href="<?= BASE_URL ?>/shop?category=5&sale=1" 
              class="btn-hero-primary promo-cta--md">
              Mua ngay <i class="fa-solid fa-arrow-right"></i>
            </a>
          </div>
          <img src="<?= $img ?>/promo/iphone-promo.png"
               alt="iPhone 15 Sale" class="promo-banner__image" loading="lazy">
        </div>
      </div>

      <!-- Banner nhỏ bên phải -->
      <div class="col-12 col-lg-5 d-flex flex-column gap-4">
        <div class="promo-banner promo-banner--dark promo-banner--compact">
          <div class="promo-banner__content">
            <span class="promo-tag">🎧 Mới</span>
            <h3 class="promo-banner__title promo-banner__title--sm">AirPods Pro Gen 2</h3>
            <p class="promo-banner__subtitle promo-banner__subtitle--sm">Chống ồn xuất sắc</p>
            <a href="<?= BASE_URL ?>/shop?category=10" class="btn-hero-outline promo-cta--sm">Xem ngay</a>
          </div>
        </div>

        <div class="promo-banner promo-banner--teal promo-banner--compact">
          <div class="promo-banner__content">
            <span class="promo-tag">💻 Deal</span>
            <h3 class="promo-banner__title promo-banner__title--sm">Asus ROG Strix G15</h3>
            <p class="promo-banner__subtitle promo-banner__subtitle--sm">Hiệu năng khủng</p>
            <a href="<?= BASE_URL ?>/shop?category=9" class="btn-hero-outline promo-cta--sm">Xem ngay</a>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ════════════════════════════════════════════════════════════════ -->
<!-- SECTION 6: SẢN PHẨM MỚI NHẤT                                   -->
<!-- ════════════════════════════════════════════════════════════════ -->
<section class="latest-section" aria-label="Sản phẩm mới nhất">
  <div class="container-xl">
    <div class="section-header d-flex align-items-end justify-content-between flex-wrap gap-3">
      <div>
        <h2 class="section-title">Hàng <span>mới về</span></h2>
        <p class="section-subtitle mt-1">Cập nhật liên tục mỗi ngày</p>
      </div>
      <a href="<?= BASE_URL ?>/shop?sort=newest" class="btn-outline-tg btn-sm">
        Xem tất cả <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>

    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4 mt-2">
  <?php foreach ($latestProducts as $product): ?>
    <?php
    $hasSale = !empty($product['sale_price']) && $product['sale_price'] < $product['price'];
    $displayPrice = $hasSale ? $product['sale_price'] : $product['price'];
    $imgSrc = !empty($product['primary_image']) ? BASE_URL . '/' . ltrim($product['primary_image'], '/') : BASE_URL . '/public/assets/img/no-image.png';
    ?>
    <div class="col">
      <div class="product-card">
          <div class="product-card__img-wrap" style="position: relative;">
              <a href="<?= BASE_URL ?>/product/<?= $product['id'] ?>">
                  <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="img-fluid" style="width: 100%; aspect-ratio: 1; object-fit: cover;">
              </a>
              <?php if ($hasSale): ?>
                  <span class="product-card__badge" style="position: absolute; top: 10px; left: 10px; background: #ef4444; color: #fff; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem;">-<?= round((($product['price'] - $product['sale_price']) / $product['price']) * 100) ?>%</span>
              <?php endif; ?>
          </div>
          <div class="product-card__body" style="padding: 1rem;">
              <div class="product-card__category" style="font-size: 0.75rem; color: #2563eb; text-transform: uppercase; font-weight: 600;"><?= htmlspecialchars($product['category_name'] ?? 'Công nghệ') ?></div>
              <a href="<?= BASE_URL ?>/product/<?= $product['id'] ?>" class="text-decoration-none">
                  <div class="product-card__name" style="color: #1e293b; font-weight: 600; font-size: 1rem; margin: 0.5rem 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                      <?= htmlspecialchars($product['name']) ?>
                  </div>
              </a>
              <div class="product-card__price">
                  <span class="price-current" style="color: #2563eb; font-weight: 700; font-size: 1.1rem;"><?= formatPrice((float)$displayPrice) ?></span>
                  <?php if ($hasSale): ?>
                      <span class="price-original text-muted text-decoration-line-through ms-2" style="font-size: 0.85rem;"><?= formatPrice((float)$product['price']) ?></span>
                  <?php endif; ?>
              </div>
              <div class="product-card__price">
                         </div>
                    <button class="btn btn-primary w-100 mt-3 btn-sm fw-medium" style="border-radius: 8px;" onclick="addToCart(<?= $product['id'] ?>)" <?= !(($product['stock'] ?? 1) > 0) ? 'disabled' : '' ?>>
                      <i class="bi bi-cart-plus me-1"></i> Thêm vào giỏ
                    </button>
          </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
  </div>
</section>

<!-- ════════════════════════════════════════════════════════════════ -->
<!-- SECTION 7: BÀI VIẾT MỚI NHẤT                                   -->
<!-- ════════════════════════════════════════════════════════════════ -->
<div class="row g-4 mt-2">
  <?php foreach ($latestPosts as $post): ?>
  <div class="col-12 col-md-4">
    <article class="blog-card">
      <div class="blog-card__image-wrap">
        <img src="<?= !empty($post['image']) ? BASE_URL . '/public/uploads/' . htmlspecialchars($post['image']) : BASE_URL . '/assets/img/blog-placeholder.jpg' ?>"
             alt="<?= htmlspecialchars($post['title']) ?>"
             class="blog-card__image" loading="lazy">
      </div>
      <div class="blog-card__body">
        <span class="blog-card__tag">Mới nhất</span>
        <h3 class="blog-card__title">
          <a href="<?= BASE_URL ?>/blog/<?= htmlspecialchars($post['slug']) ?>">
            <?= htmlspecialchars($post['title']) ?>
          </a>
        </h3>
        <p class="blog-card__excerpt"><?= htmlspecialchars($postModel->makeExcerpt($post['content'] ?? '', 100)) ?></p>
        <div class="blog-card__footer">
          <span><i class="fa-regular fa-calendar me-1"></i><?= date('d/m/Y', strtotime($post['created_at'])) ?></span>
          <a href="<?= BASE_URL ?>/blog/<?= htmlspecialchars($post['slug']) ?>" class="blog-card__read-more">
            Đọc thêm <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </article>
  </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════════════════════════════════ -->
<!-- SECTION 8: THƯƠNG HIỆU ĐỐI TÁC                                 -->
<!-- ════════════════════════════════════════════════════════════════ -->
<section class="brands-section" aria-label="Thương hiệu đối tác">
  <div class="container-xl">
    <div class="section-header text-center mb-4">
      <p class="brands-section__label">Thương hiệu chính hãng</p>
    </div>

    <div class="swiper brands-swiper">
      <div class="swiper-wrapper">
        <?php foreach ($brands as $brand): ?>
        <div class="swiper-slide">
          <div class="brand-logo-wrap">
            <img src="<?= htmlspecialchars($brand['logo']) ?>"
                 alt="<?= htmlspecialchars($brand['name']) ?>"
                 loading="lazy">
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<script>
  window.TG_BASE_URL = '<?= BASE_URL ?>';
  window.TG_IS_LOGGED_IN = <?= isLoggedIn() ? 'true' : 'false' ?>;

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
<?php
$extraJS = '<script>document.addEventListener("DOMContentLoaded",function(){window.initHeroSwiper?.();window.initFeaturedSwiper?.();window.initBrandsSwiper?.();});</script>';
require_once __DIR__ . '/../../includes/footer.php';