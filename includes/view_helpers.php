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

        // Xử lý ảnh: Ưu tiên primary_image từ DB, nếu không có lấy ảnh mặc định
        $imgUrl = !empty($p['primary_image']) 
            ? $base . '/' . ltrim($p['primary_image'], '/') 
            : (!empty($p['image']) ? $p['image'] : $base . '/public/assets/img/no-image.png');

        return "
    <div class='product-card'>
      <div class='product-card__image-wrap'>
        <a href='{$base}/product/{$p['id']}'>
            <img src='{$imgUrl}' alt='" . htmlspecialchars($p['name']) . "'
                 class='product-card__image' loading='lazy' style='width:100%; aspect-ratio:1; object-fit:cover;'>
        </a>
        <div class='product-card__badges'>{$newBadge}{$salePercent}</div>
        <button class='product-card__wishlist {$heartClass}'
                data-product-id='{$p['id']}' aria-label='Thêm yêu thích'>
          <i class='{$heartIcon}'></i>
        </button>
      </div>
      <div class='product-card__body' style='padding: 1rem;'>
        <span class='product-card__category' style='color:#2563eb; font-weight:600; font-size:0.75rem;'>{$p['category_name']}</span>
        <a href='{$base}/product/{$p['id']}' class='product-card__name' style='color:#1e293b; font-weight:600; text-decoration:none; display:block; margin:0.5rem 0;'>" . htmlspecialchars($p['name']) . "</a>
        <div class='product-card__price-wrap'>
          <span class='product-card__price' style='color:#2563eb; font-weight:700;'>{$displayPrice}</span>
          {$originalPrice}
        </div>
      </div>
      <div class='product-card__footer' style='padding:0.5rem 1rem;'>
        <button class='btn-add-cart'
                data-product-id='{$p['id']}' data-quantity='1' style='width:100%; border:1px solid #2563eb; background:none; color:#2563eb; padding:5px; border-radius:6px;'>
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