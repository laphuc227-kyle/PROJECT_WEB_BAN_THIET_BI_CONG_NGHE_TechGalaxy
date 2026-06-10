<?php
<<<<<<< HEAD
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../../config/app.php';
}

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
        'cta'      => ['text' => 'Xem ngay', 'href' => BASE_URL . '/shop?category=laptop'],
        'cta2'     => ['text' => 'So sánh', 'href' => BASE_URL . '/shop?category=laptop&sort=featured'],
        'image'    => $img . '/hero/laptop-hero.png',
        'stats'    => [
            ['number' => '500+', 'label' => 'Model laptop'],
            ['number' => '12T+', 'label' => 'Doanh thu'],
            ['number' => '2h',   'label' => 'Giao hàng nội thành'],
        ]
    ],
];

$categories = [
    ['icon' => 'fa-mobile-screen',        'name' => 'Điện thoại',       'slug' => 'dien-thoai',      'count' => 248],
    ['icon' => 'fa-laptop',               'name' => 'Laptop',            'slug' => 'laptop',           'count' => 156],
    ['icon' => 'fa-headphones',           'name' => 'Tai nghe',          'slug' => 'tai-nghe',         'count' => 312],
    ['icon' => 'fa-watch',                'name' => 'Smartwatch',        'slug' => 'smartwatch',       'count' => 89],
    ['icon' => 'fa-tablet-screen-button', 'name' => 'Máy tính bảng',    'slug' => 'may-tinh-bang',    'count' => 67],
    ['icon' => 'fa-keyboard',             'name' => 'Phụ kiện',          'slug' => 'phu-kien',         'count' => 520],
];

$featuredProducts = [
    ['id' => 1, 'name' => 'iPhone 15 Pro Max 256GB', 'slug' => 'iphone-15-pro-max', 'price' => 34990000, 'sale_price' => 29990000, 'image' => $img . '/products/product-1.jpg', 'category_name' => 'Điện thoại', 'is_new' => true, 'in_wishlist' => false],
    ['id' => 2, 'name' => 'MacBook Air M3 15"', 'slug' => 'macbook-air-m3', 'price' => 28990000, 'sale_price' => null, 'image' => $img . '/products/product-2.jpg', 'category_name' => 'Laptop', 'is_new' => true, 'in_wishlist' => false],
    ['id' => 3, 'name' => 'Sony WH-1000XM5', 'slug' => 'sony-wh-1000xm5', 'price' => 7490000, 'sale_price' => 6490000, 'image' => $img . '/products/product-3.jpg', 'category_name' => 'Tai nghe', 'is_new' => false, 'in_wishlist' => false],
    ['id' => 4, 'name' => 'Samsung Galaxy Watch 6', 'slug' => 'galaxy-watch-6', 'price' => 6990000, 'sale_price' => null, 'image' => $img . '/products/product-4.jpg', 'category_name' => 'Smartwatch', 'is_new' => true, 'in_wishlist' => false],
    ['id' => 5, 'name' => 'iPad Pro M4 11"', 'slug' => 'ipad-pro-m4', 'price' => 24990000, 'sale_price' => 22990000, 'image' => $img . '/products/product-5.jpg', 'category_name' => 'Máy tính bảng', 'is_new' => true, 'in_wishlist' => false],
    ['id' => 6, 'name' => 'Logitech MX Master 3S', 'slug' => 'mx-master-3s', 'price' => 2490000, 'sale_price' => null, 'image' => $img . '/products/product-6.jpg', 'category_name' => 'Phụ kiện', 'is_new' => false, 'in_wishlist' => false],
    ['id' => 7, 'name' => 'ASUS ROG Zephyrus G16', 'slug' => 'rog-zephyrus-g16', 'price' => 45990000, 'sale_price' => 41990000, 'image' => $img . '/products/product-7.jpg', 'category_name' => 'Laptop', 'is_new' => true, 'in_wishlist' => false],
    ['id' => 8, 'name' => 'Galaxy Buds3 Pro', 'slug' => 'galaxy-buds3-pro', 'price' => 4990000, 'sale_price' => null, 'image' => $img . '/products/product-8.jpg', 'category_name' => 'Tai nghe', 'is_new' => true, 'in_wishlist' => false],
];

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

$latestPosts = [
    [
        'title'      => 'iPhone 15 Pro Max vs Samsung S24 Ultra: Đâu là lựa chọn tốt nhất 2024?',
        'slug'       => 'iphone-15-pro-max-vs-samsung-s24-ultra',
        'excerpt'    => 'Chúng tôi so sánh hai flagship đỉnh cao của năm 2024 trên mọi phương diện: hiệu năng, camera, pin và giá trị sử dụng thực tế.',
        'image'      => $img . '/blog/blog-1.jpg',
        'tag'        => 'So sánh',
        'created_at' => '15/01/2025',
        'read_time'  => '8 phút đọc',
    ],
    [
        'title'      => 'Top 5 tai nghe chống ồn tốt nhất dưới 5 triệu đồng',
        'slug'       => 'top-5-tai-nghe-chong-on-tot-nhat',
        'excerpt'    => 'Tổng hợp 5 mẫu tai nghe chống ồn active đáng mua nhất hiện nay với giá dưới 5 triệu, phù hợp với nhiều nhu cầu sử dụng.',
        'image'      => $img . '/blog/blog-2.jpg',
        'tag'        => 'Top list',
        'created_at' => '10/01/2025',
        'read_time'  => '5 phút đọc',
    ],
    [
        'title'      => 'Laptop gaming tầm trung 2025: Hiệu năng tốt, không cần tốn nhiều tiền',
        'slug'       => 'laptop-gaming-tam-trung-2025',
        'excerpt'    => 'Bạn muốn chơi game mượt mà mà không cần chi quá 25 triệu? Đây là những lựa chọn laptop gaming tầm trung đáng mua nhất.',
        'image'      => $img . '/blog/blog-3.jpg',
        'tag'        => 'Tư vấn',
        'created_at' => '05/01/2025',
        'read_time'  => '6 phút đọc',
    ],
];

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
        <?php foreach ($featuredProducts as $product): ?>
        <div class="swiper-slide h-auto">
          <?= renderProductCard($product) ?>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="swiper-pagination"></div>
    </div>
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
            <a href="<?= BASE_URL ?>/shop?category=dien-thoai&sale=1"
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
            <h3 class="promo-banner__title promo-banner__title--sm">Tai nghe Sony WH-1000XM5</h3>
            <p class="promo-banner__subtitle promo-banner__subtitle--sm">Giảm 20% trong hôm nay</p>
            <a href="<?= BASE_URL ?>/shop?category=tai-nghe" class="btn-hero-outline promo-cta--sm">Xem ngay</a>
          </div>
        </div>

        <div class="promo-banner promo-banner--teal promo-banner--compact">
          <div class="promo-banner__content">
            <span class="promo-tag">💻 Deal</span>
            <h3 class="promo-banner__title promo-banner__title--sm">Laptop Gaming RTX 4060</h3>
            <p class="promo-banner__subtitle promo-banner__subtitle--sm">Trả góp 0% lãi suất</p>
            <a href="<?= BASE_URL ?>/shop?category=laptop" class="btn-hero-outline promo-cta--sm">Xem ngay</a>
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
      <div class="col">
        <?= renderProductCard($product) ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════════════════════════════════ -->
<!-- SECTION 7: BÀI VIẾT MỚI NHẤT                                   -->
<!-- ════════════════════════════════════════════════════════════════ -->
<section class="blog-section" aria-label="Bài viết mới">
  <div class="container-xl">
    <div class="section-header d-flex align-items-end justify-content-between flex-wrap gap-3">
      <div>
        <h2 class="section-title">Blog <span>công nghệ</span></h2>
        <p class="section-subtitle mt-1">Tin tức, đánh giá và tư vấn sản phẩm mới nhất</p>
      </div>
      <a href="<?= BASE_URL ?>/blog" class="btn-outline-tg btn-sm">
        Xem tất cả <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>

    <div class="row g-4 mt-2">
      <?php foreach ($latestPosts as $post): ?>
      <div class="col-12 col-md-4">
        <article class="blog-card">
          <div class="blog-card__image-wrap">
            <img src="<?= htmlspecialchars($post['image']) ?>"
                 alt="<?= htmlspecialchars($post['title']) ?>"
                 class="blog-card__image" loading="lazy">
          </div>
          <div class="blog-card__body">
            <span class="blog-card__tag"><?= htmlspecialchars($post['tag']) ?></span>
            <h3 class="blog-card__title">
              <a href="<?= BASE_URL ?>/blog/<?= htmlspecialchars($post['slug']) ?>">
                <?= htmlspecialchars($post['title']) ?>
              </a>
            </h3>
            <p class="blog-card__excerpt"><?= htmlspecialchars($post['excerpt']) ?></p>
            <div class="blog-card__footer">
              <span><i class="fa-regular fa-calendar me-1"></i><?= $post['created_at'] ?></span>
              <a href="<?= BASE_URL ?>/blog/<?= htmlspecialchars($post['slug']) ?>"
                 class="blog-card__read-more">
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
</script>
<?php
$extraJS = '<script>document.addEventListener("DOMContentLoaded",function(){window.initHeroSwiper?.();window.initFeaturedSwiper?.();window.initBrandsSwiper?.();});</script>';
require_once __DIR__ . '/../../includes/footer.php';
=======
/**
 * views/admin/categories/index.php
 * Danh sách danh mục — Admin Panel
 *
 * Biến:
 *  - $categories    array   Danh sách danh mục
 *  - $flashMessage  string|null
 *  - $flashType     string
 */
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
                <span><i class="bi bi-list-ul me-2"></i>Danh sách (<?= count($categories) ?>)</span>
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
>>>>>>> origin/feature/shop-product-wishlist
