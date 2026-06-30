<?php
/**
 * Helpers dùng trong view (HTML partials).
 */

if (!function_exists('navActive')) {
    function navActive(string $page, string $current): string {
        return $page === $current ? 'active' : '';
    }
}

if (!function_exists('renderProductCard')) {
    function renderProductCard(array $p): string {
        $salePercent = '';
        if (!empty($p['sale_price']) && $p['price'] > 0) {
            $pct = round((1 - $p['sale_price'] / $p['price']) * 100);
            $salePercent = '<span class="product-card__badge badge-sale">-' . $pct . '%</span>';
        }
        $newBadge = !empty($p['is_new'])
            ? '<span class="product-card__badge badge-new">Mới</span>'
            : '';

        $displayPrice  = number_format($p['sale_price'] ?? $p['price'], 0, ',', '.') . ' ₫';
        $originalPrice = !empty($p['sale_price'])
            ? '<span class="product-card__price-original">' . number_format($p['price'], 0, ',', '.') . ' ₫</span>'
            : '';

        $heartClass = !empty($p['in_wishlist']) ? 'active' : '';
        $heartIcon  = !empty($p['in_wishlist']) ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
        $base       = defined('BASE_URL') ? BASE_URL : '';

        return "
    <div class='product-card'>
      <div class='product-card__image-wrap'>
        <img src='{$p['image']}' alt='" . htmlspecialchars($p['name']) . "'
             class='product-card__image' loading='lazy' width='400' height='400'>
        <div class='product-card__badges'>{$newBadge}{$salePercent}</div>
        <button class='product-card__wishlist {$heartClass}'
                data-product-id='{$p['id']}' aria-label='Thêm yêu thích'>
          <i class='{$heartIcon}'></i>
        </button>
      </div>
      <div class='product-card__body'>
        <span class='product-card__category'>{$p['category_name']}</span>
        <a href='{$base}/product/{$p['slug']}' class='product-card__name'>" . htmlspecialchars($p['name']) . "</a>
        <div class='product-card__price-wrap'>
          <span class='product-card__price'>{$displayPrice}</span>
          {$originalPrice}
        </div>
      </div>
      <div class='product-card__footer'>
        <button class='btn-add-cart'
                data-product-id='{$p['id']}' data-quantity='1'>
          <i class='fa-solid fa-cart-plus'></i> Thêm vào giỏ
        </button>
      </div>
    </div>";
    }
}

if (!function_exists('formatHeroTitle')) {
    function formatHeroTitle(string $title, string $highlight): string {
        $safeTitle     = htmlspecialchars($title);
        $safeHighlight = htmlspecialchars($highlight);
        $html          = str_replace($safeHighlight, '<span class="highlight">' . $safeHighlight . '</span>', $safeTitle);
        return nl2br($html);
    }
}
