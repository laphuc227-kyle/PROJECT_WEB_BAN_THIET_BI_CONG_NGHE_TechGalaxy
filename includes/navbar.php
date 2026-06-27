<?php
$cartCount     = (int) ($_SESSION['cart_count'] ?? 0);
$wishlistCount = (int) ($_SESSION['wishlist_count'] ?? 0);
$currentPage   = $currentPage ?? '';
$user          = $_SESSION['user'] ?? null;
?>

<!-- ── ANNOUNCEMENT BAR ─────────────────────────────────────────────── -->
<div class="announcement-bar">
  <div class="container-xl">
    <div class="announcement-bar__inner">
      <span><i class="fa-solid fa-truck-fast"></i> Miễn phí giao hàng cho đơn từ 500K</span>
      <span class="d-none d-md-inline">·</span>
      <span class="d-none d-md-inline"><i class="fa-solid fa-shield-halved"></i> Bảo hành chính hãng 12–24 tháng</span>
      <span class="d-none d-md-inline">·</span>
      <span class="d-none d-md-inline"><i class="fa-solid fa-rotate-left"></i> Đổi trả trong 7 ngày</span>
    </div>
  </div>
</div>

<!-- ── MAIN NAVBAR ──────────────────────────────────────────────────── -->
<nav class="navbar navbar-expand-lg navbar-tg" id="mainNavbar">
  <div class="container-xl">

    <!-- Logo -->
    <a class="navbar-brand" href="<?= BASE_URL ?>/">
      <span class="brand-icon"><i class="fa-solid fa-microchip"></i></span>
      <span class="brand-text">Tech<strong>Galaxy</strong></span>
    </a>

    <div class="navbar-mobile-actions d-flex d-lg-none align-items-center gap-2 ms-auto me-2">
      <button type="button" class="nav-icon-btn" id="searchOpenBtn" aria-label="Tìm kiếm">
        <i class="fa-solid fa-magnifying-glass"></i>
      </button>
      <a href="<?= BASE_URL ?>/wishlist" class="nav-icon-btn" aria-label="Yêu thích">
        <i class="fa-regular fa-heart"></i>
        <span class="nav-badge <?= $wishlistCount === 0 ? 'd-none' : '' ?>"
              id="wishlistBadgeMobile"><?= $wishlistCount ?></span>
      </a>
      <a href="<?= BASE_URL ?>/cart" class="nav-icon-btn" aria-label="Giỏ hàng">
        <i class="fa-solid fa-cart-shopping"></i>
        <span class="nav-badge <?= $cartCount === 0 ? 'd-none' : '' ?>"
              id="cartBadgeMobile"><?= $cartCount ?></span>
      </a>
    </div>

    <!-- Hamburger -->
    <button class="navbar-toggler" type="button"
            data-bs-toggle="collapse" data-bs-target="#navbarMain"
            aria-controls="navbarMain" aria-expanded="false" aria-label="Mở menu">
      <span class="hamburger-line"></span>
      <span class="hamburger-line"></span>
      <span class="hamburger-line"></span>
    </button>

    <!-- Collapse menu -->
    <div class="collapse navbar-collapse" id="navbarMain">

      <!-- ── Menu chính ── -->
      <ul class="navbar-nav mx-auto gap-1">
        <li class="nav-item">
          <a class="nav-link <?= navActive('home', $currentPage) ?>"
             href="<?= BASE_URL ?>/">Trang chủ</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= navActive('shop', $currentPage) ?>"
             href="<?= BASE_URL ?>/shop" data-bs-toggle="dropdown">
            Shop
          </a>
          <ul class="dropdown-menu mega-menu">
            <li>
              <div class="mega-menu__grid">
                <!-- TODO (Nguyên): Load danh mục thật từ DB -->
                <?php
                $demoCategories = [
                    ['icon' => 'fa-mobile-screen',   'name' => 'Điện thoại',    'slug' => 'dien-thoai'],
                    ['icon' => 'fa-laptop',           'name' => 'Laptop',        'slug' => 'laptop'],
                    ['icon' => 'fa-headphones',       'name' => 'Tai nghe',      'slug' => 'tai-nghe'],
                    ['icon' => 'fa-watch',            'name' => 'Đồng hồ thông minh', 'slug' => 'dong-ho'],
                    ['icon' => 'fa-tablet-screen-button', 'name' => 'Máy tính bảng', 'slug' => 'may-tinh-bang'],
                    ['icon' => 'fa-keyboard',         'name' => 'Phụ kiện',      'slug' => 'phu-kien'],
                ];
                foreach ($demoCategories as $cat): ?>
                  <a href="<?= BASE_URL ?>/shop?category=<?= $cat['slug'] ?>" class="mega-menu__item">
                    <span class="mega-menu__icon"><i class="fa-solid <?= $cat['icon'] ?>"></i></span>
                    <span><?= $cat['name'] ?></span>
                  </a>
                <?php endforeach; ?>
              </div>
              <div class="mega-menu__footer">
                <a href="<?= BASE_URL ?>/shop">Xem tất cả sản phẩm <i class="fa-solid fa-arrow-right"></i></a>
              </div>
            </li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= navActive('blog', $currentPage) ?>"
             href="<?= BASE_URL ?>/blog">Blog</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= navActive('contact', $currentPage) ?>"
             href="<?= BASE_URL ?>/contact">Liên hệ</a>
        </li>
      </ul>

      <!-- ── Thanh tìm kiếm ── -->
      <form class="navbar-search" action="<?= BASE_URL ?>/shop" method="GET" role="search">
        <div class="search-wrapper">
          <input type="search" name="q" class="search-input"
                 placeholder="Tìm sản phẩm..." aria-label="Tìm kiếm"
                 value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" autocomplete="off">
          <button type="submit" class="search-btn" aria-label="Tìm kiếm">
            <i class="fa-solid fa-magnifying-glass"></i>
          </button>
          <!-- Gợi ý tìm kiếm (AJAX) -->
          <div class="search-suggestions" id="searchSuggestions" hidden></div>
        </div>
      </form>

      <!-- ── Icon actions ── -->
      <div class="navbar-actions d-none d-lg-flex align-items-center gap-2">

        <!-- Wishlist -->
        <a href="<?= BASE_URL ?>/wishlist" class="nav-icon-btn" aria-label="Danh sách yêu thích">
          <i class="fa-regular fa-heart"></i>
          <span class="nav-badge <?= $wishlistCount === 0 ? 'd-none' : '' ?>"
                id="wishlistBadge"><?= $wishlistCount ?></span>
        </a>

        <!-- Giỏ hàng -->
        <a href="<?= BASE_URL ?>/cart" class="nav-icon-btn" aria-label="Giỏ hàng">
          <i class="fa-solid fa-cart-shopping"></i>
          <span class="nav-badge <?= $cartCount === 0 ? 'd-none' : '' ?>"
                id="cartBadge"><?= $cartCount ?></span>
        </a>

        <!-- User Menu -->
        <?php if ($user): ?>
          <div class="dropdown">
            <button class="nav-user-btn dropdown-toggle" data-bs-toggle="dropdown"
                    aria-expanded="false" aria-label="Tài khoản">
              <img src="<?= BASE_URL ?>/public/uploads/avatars/<?= htmlspecialchars($user['avatar'] ?? 'default.png') ?>"
                   alt="<?= htmlspecialchars($user['name']) ?>" class="nav-avatar">
              <span class="d-none d-xl-inline"><?= htmlspecialchars(explode(' ', $user['name'])[0]) ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end user-dropdown">
              <li class="user-dropdown__header">
                <strong><?= htmlspecialchars($user['name']) ?></strong>
                <small><?= htmlspecialchars($user['email']) ?></small>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item" href="<?= BASE_URL ?>/account">
                  <i class="fa-regular fa-user"></i> Tài khoản của tôi
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="<?= BASE_URL ?>/my-orders">
                  <i class="fa-regular fa-rectangle-list"></i> Đơn hàng
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="<?= BASE_URL ?>/wishlist">
                  <i class="fa-regular fa-heart"></i> Danh sách yêu thích
                </a>
              </li>
              <?php if (isset($user['role']) && $user['role'] === 'admin'): ?>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item text-primary" href="<?= BASE_URL ?>/admin">
                    <i class="fa-solid fa-gauge"></i> Quản trị viên
                  </a>
                </li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout">
                  <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                </a>
              </li>
            </ul>
          </div>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/login" class="btn btn-login">
            <i class="fa-regular fa-user"></i>
            <span class="d-none d-xl-inline ms-1">Đăng nhập</span>
          </a>
        <?php endif; ?>
      </div><!-- /navbar-actions -->

    </div><!-- /collapse -->
  </div><!-- /container -->
</nav>

<!-- ── SEARCH OVERLAY (mobile full-screen) ─────────────────────────── -->
<div class="search-overlay" id="searchOverlay">
  <div class="search-overlay__inner">
    <form action="<?= BASE_URL ?>/shop" method="GET">
      <input type="search" name="q" class="search-overlay__input"
             placeholder="Tìm kiếm sản phẩm...">
      <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
    </form>
    <button type="button" class="search-overlay__close" id="searchCloseBtn" aria-label="Đóng tìm kiếm">
      <i class="fa-solid fa-xmark"></i>
    </button>
  </div>
</div>
