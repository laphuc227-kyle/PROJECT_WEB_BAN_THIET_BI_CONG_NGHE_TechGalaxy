'use strict';

/* ── Helpers ───────────────────────────────────────────────────────── */
function getBaseUrl() {
  const base = (window.TG_BASE_URL || '').replace(/\/$/, '');
  return base || window.location.origin;
}

/**
 * Lấy CSRF token từ meta tag (do PHP inject).
 * Dùng trong mọi request POST qua fetch.
 */
function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
}

/**
 * Format số thành tiền VND: 1290000 → "1.290.000 ₫"
 */
function formatPrice(amount) {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
    minimumFractionDigits: 0
  }).format(amount);
}

/**
 * Debounce: trì hoãn hàm gọi liên tục (dùng cho search, update cart)
 */
function debounce(fn, delay = 400) {
  let timer;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => fn.apply(this, args), delay);
  };
}

/**
 * Gọi AJAX POST với JSON body.
 * Tự động đính kèm CSRF token.
 * @returns {Promise<Object>} JSON response
 */
async function postJSON(url, data = {}) {
  const response = await fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': getCsrfToken(),
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify(data)
  });

  if (!response.ok) {
    const err = await response.json().catch(() => ({ message: 'Lỗi không xác định' }));
    throw new Error(err.message || `HTTP ${response.status}`);
  }

  return response.json();
}

/* ══════════════════════════════════════════════════════════════════════
   2. NAVBAR — SCROLL EFFECT
   ══════════════════════════════════════════════════════════════════════ */
(function initNavbarScroll() {
  const navbar = document.getElementById('mainNavbar');
  if (!navbar) return;

  const onScroll = () => {
    navbar.classList.toggle('scrolled', window.scrollY > 30);
  };

  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll(); // Run once on load
})();

/* ══════════════════════════════════════════════════════════════════════
   3. BACK TO TOP
   ══════════════════════════════════════════════════════════════════════ */
(function initBackToTop() {
  const btn = document.getElementById('backToTop');
  if (!btn) return;

  window.addEventListener('scroll', () => {
    btn.classList.toggle('visible', window.scrollY > 400);
  }, { passive: true });

  btn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
})();

/* ══════════════════════════════════════════════════════════════════════
   4. FLASH TOAST — AUTO DISMISS
   ══════════════════════════════════════════════════════════════════════ */
(function initFlashToast() {
  const toast = document.getElementById('flashToast');
  if (!toast) return;

  // Tự xoá sau 3.5 giây (animation CSS chạy 3s → 3.4s)
  setTimeout(() => toast.remove(), 3500);

  // Click để đóng sớm
  toast.addEventListener('click', () => toast.remove());
})();

/**
 * Hiển thị toast thông báo từ JS (không cần reload trang).
 * @param {string} message
 * @param {'success'|'error'|'warning'} type
 */
function showToast(message, type = 'success') {
  // Xoá toast cũ nếu có
  document.querySelectorAll('.flash-toast').forEach(el => el.remove());

  const icon = {
    success: 'fa-circle-check',
    error:   'fa-circle-exclamation',
    warning: 'fa-triangle-exclamation'
  }[type] || 'fa-circle-check';

  const toast = document.createElement('div');
  toast.className = `flash-toast flash-${type}`;
  toast.id = 'flashToast';
  toast.innerHTML = `<i class="fa-solid ${icon}"></i> ${message}`;

  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3500);
  toast.addEventListener('click', () => toast.remove());
}

/* ══════════════════════════════════════════════════════════════════════
   5. CART — AJAX ADD TO CART
   ══════════════════════════════════════════════════════════════════════ */

/**
 * Cập nhật số hiển thị trên badge giỏ hàng (navbar).
 */
function updateCartBadge(count) {
  document.querySelectorAll('#cartBadge, #cartBadgeMobile').forEach((badge) => {
    badge.textContent = count > 99 ? '99+' : String(count);
    badge.classList.toggle('d-none', count <= 0);
  });
}

function updateWishlistBadge(count) {
  document.querySelectorAll('#wishlistBadge, #wishlistBadgeMobile').forEach((badge) => {
    badge.textContent = count > 99 ? '99+' : String(count);
    badge.classList.toggle('d-none', count <= 0);
  });
}

/**
 * Thêm sản phẩm vào giỏ qua AJAX.
 * Gắn vào nút với data-product-id và data-quantity.
 *
 * Cách dùng trong HTML:
 *   <button class="btn-add-cart" data-product-id="5" data-quantity="1">
 *     Thêm vào giỏ
 *   </button>
 */
async function addToCart(productId, quantity = 1, btn = null) {
  if (!productId) return;

  // Loading state trên nút
  const originalHTML = btn?.innerHTML;
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang thêm...';
  }

  try {
    const data = await postJSON(`${getBaseUrl()}/cart/add`, { product_id: productId, quantity });

    if (data.success) {
      updateCartBadge(data.cart_count);
      showToast('Đã thêm vào giỏ hàng!', 'success');

      // Ripple animation trên nút
      btn?.classList.add('added');
      setTimeout(() => btn?.classList.remove('added'), 1000);
    } else {
      showToast(data.message || 'Không thể thêm vào giỏ hàng', 'error');
    }
  } catch (err) {
    console.error('addToCart error:', err);
    showToast('Đã xảy ra lỗi. Vui lòng thử lại.', 'error');
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = originalHTML;
    }
  }
}

// Lắng nghe click trên tất cả nút add-to-cart (event delegation)
document.addEventListener('click', (e) => {
  const btn = e.target.closest('.btn-add-cart');
  if (!btn) return;

  e.preventDefault();
  const productId = btn.dataset.productId;
  const quantity  = parseInt(btn.dataset.quantity || '1', 10);

  // Nếu chưa đăng nhập → redirect login
  if (btn.dataset.requireAuth === 'true' && !window.TG_IS_LOGGED_IN) {
    window.location.href = getBaseUrl() + '/login';
    return;
  }

  addToCart(productId, quantity, btn);
});

/* ══════════════════════════════════════════════════════════════════════
   6. WISHLIST TOGGLE
   ══════════════════════════════════════════════════════════════════════ */
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('.product-card__wishlist');
  if (!btn) return;

  e.preventDefault();

  const productId = btn.dataset.productId;
  if (!productId) return;

  // Chưa đăng nhập
  if (!window.TG_IS_LOGGED_IN) {
    window.location.href = getBaseUrl() + '/login';
    return;
  }

  try {
    const data = await postJSON(`${getBaseUrl()}/wishlist/toggle`, { product_id: productId });

    if (data.success) {
      btn.classList.toggle('active', data.in_wishlist);
      const icon = btn.querySelector('i');
      if (icon) {
        icon.className = data.in_wishlist ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
      }

      // Cập nhật badge wishlist
      if (typeof data.wishlist_count === 'number') {
        updateWishlistBadge(data.wishlist_count);
      }

      showToast(data.in_wishlist ? 'Đã thêm vào yêu thích' : 'Đã xoá khỏi yêu thích',
                data.in_wishlist ? 'success' : 'warning');
    }
  } catch {
    showToast('Lỗi kết nối, thử lại sau.', 'error');
  }
});

/* ══════════════════════════════════════════════════════════════════════
   7. SEARCH — GỢI Ý AJAX
   ══════════════════════════════════════════════════════════════════════ */
(function initSearch() {
  const searchInput       = document.querySelector('.search-input');
  const searchSuggestions = document.getElementById('searchSuggestions');
  if (!searchInput || !searchSuggestions) return;

  const fetchSuggestions = debounce(async (query) => {
    if (query.length < 2) {
      searchSuggestions.hidden = true;
      return;
    }

    try {
      const response = await fetch(
        `${getBaseUrl()}/api/search-suggestions?q=${encodeURIComponent(query)}`,
        { headers: { 'X-Requested-With': 'XMLHttpRequest' } }
      );
      const data = await response.json();

      if (data.items?.length) {
        searchSuggestions.innerHTML = data.items.map(item => `
          <a href="${getBaseUrl()}/product/${item.slug}" class="search-suggestion-item">
            <img src="${item.image}" alt="${item.name}" width="36" height="36">
            <div>
              <div class="suggestion-name">${item.name}</div>
              <div class="suggestion-price">${formatPrice(item.price)}</div>
            </div>
          </a>
        `).join('');
        searchSuggestions.hidden = false;
      } else {
        searchSuggestions.hidden = true;
      }
    } catch {
      searchSuggestions.hidden = true;
    }
  }, 300);

  searchInput.addEventListener('input', (e) => fetchSuggestions(e.target.value.trim()));

  // Đóng gợi ý khi click ra ngoài
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.search-wrapper')) {
      searchSuggestions.hidden = true;
    }
  });
})();

/* ══════════════════════════════════════════════════════════════════════
   8. NEWSLETTER FORM
   ══════════════════════════════════════════════════════════════════════ */
window.handleNewsletter = async function(e) {
  e.preventDefault();
  const form  = e.target;
  const email = form.querySelector('input[type="email"]').value;
  const btn   = form.querySelector('button[type="submit"]');

  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

  await new Promise((r) => setTimeout(r, 800));
  showToast('Đăng ký nhận tin thành công!', 'success');
  form.reset();
  btn.disabled = false;
  btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i>';
};

/* ══════════════════════════════════════════════════════════════════════
   9. CONFIRM DIALOG (dùng SweetAlert2)
   ══════════════════════════════════════════════════════════════════════ */

/**
 * Confirm dialog trước khi xoá.
 * @param {string} title
 * @param {string} text
 * @returns {Promise<boolean>}
 *
 * Cách dùng:
 *   if (await confirmDelete('Xoá sản phẩm?', 'Hành động này không thể hoàn tác')) {
 *     // tiến hành xoá
 *   }
 */
async function confirmDelete(title = 'Xác nhận xoá?', text = 'Hành động này không thể hoàn tác!') {
  if (typeof Swal === 'undefined') return window.confirm(title + '\n' + text);

  const result = await Swal.fire({
    title,
    text,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: getComputedStyle(document.documentElement).getPropertyValue('--danger').trim() || '#EF4444',
    cancelButtonColor: getComputedStyle(document.documentElement).getPropertyValue('--secondary').trim() || '#64748B',
    confirmButtonText: 'Xoá',
    cancelButtonText: 'Huỷ',
    focusCancel: true,
    customClass: { popup: 'swal-tg' }
  });

  return result.isConfirmed;
}

/**
 * Alert thành công.
 */
function alertSuccess(title, text = '') {
  if (typeof Swal === 'undefined') return alert(title);
  const primary = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim();
  return Swal.fire({ icon: 'success', title, text, confirmButtonColor: primary || '#2563EB' });
}

/* ── Search overlay (mobile) ───────────────────────────────────────── */
function openSearch() {
  const overlay = document.getElementById('searchOverlay');
  if (!overlay) return;
  overlay.classList.add('is-open');
  overlay.querySelector('.search-overlay__input')?.focus();
  document.body.style.overflow = 'hidden';
}

function closeSearch() {
  const overlay = document.getElementById('searchOverlay');
  if (!overlay) return;
  overlay.classList.remove('is-open');
  document.body.style.overflow = '';
}

(function initSearchOverlay() {
  document.getElementById('searchOpenBtn')?.addEventListener('click', openSearch);
  document.getElementById('searchCloseBtn')?.addEventListener('click', closeSearch);
  document.getElementById('searchOverlay')?.addEventListener('click', (e) => {
    if (e.target.id === 'searchOverlay') closeSearch();
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeSearch();
  });
})();

window.openSearch = openSearch;
window.closeSearch = closeSearch;

/* ── Swiper init ───────────────────────────────────────────────────── */

/**
 * Khởi tạo Hero Swiper (trang chủ).
 * Gọi từ views/user/index.php sau khi DOM load.
 */
window.initHeroSwiper = function() {
  return new Swiper('.hero-swiper', {
    loop: true,
    autoplay: { delay: 5000, disableOnInteraction: false },
    effect: 'fade',
    fadeEffect: { crossFade: true },
    pagination: {
      el: '.hero-section .swiper-pagination',
      clickable: true
    },
    speed: 800
  });
};

/**
 * Khởi tạo Featured Products Swiper.
 */
window.initFeaturedSwiper = function() {
  return new Swiper('.featured-swiper', {
    slidesPerView: 1.2,
    spaceBetween: 16,
    pagination: { el: '.featured-swiper .swiper-pagination', clickable: true },
    breakpoints: {
      576:  { slidesPerView: 2.2, spaceBetween: 16 },
      768:  { slidesPerView: 3,   spaceBetween: 20 },
      1024: { slidesPerView: 4,   spaceBetween: 24 },
      1280: { slidesPerView: 4,   spaceBetween: 24 }
    }
  });
};

/**
 * Khởi tạo Brands Swiper.
 */
window.initBrandsSwiper = function() {
  return new Swiper('.brands-swiper', {
    slidesPerView: 2,
    spaceBetween: 20,
    loop: true,
    autoplay: { delay: 2500, disableOnInteraction: false },
    breakpoints: {
      480:  { slidesPerView: 3 },
      768:  { slidesPerView: 4 },
      1024: { slidesPerView: 6 }
    }
  });
};

/* ══════════════════════════════════════════════════════════════════════
   12. DOM READY — Khởi tạo khi trang load xong
   ══════════════════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
    new bootstrap.Tooltip(el, { trigger: 'hover' });
  });

  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener('click', (e) => {
      const href = anchor.getAttribute('href');
      if (!href || href === '#') return;
      const target = document.querySelector(href);
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });
});
