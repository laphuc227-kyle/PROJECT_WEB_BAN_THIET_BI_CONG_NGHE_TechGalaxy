<?php
// File: views/user/cart.php
// Biến nhận từ CartController::showCart():
//   $items       array   — danh sách sản phẩm trong giỏ
//   $subtotal    int     — tổng trước giảm giá
//   $shippingFee int     — phí ship (0 nếu miễn phí)
//   $discount    int     — số tiền giảm từ coupon
//   $total       int     — tổng cuối
//   $couponCode  string  — mã coupon đã áp dụng ('' nếu chưa có)

$pageTitle   = 'Giỏ hàng — ' . APP_NAME;
$currentPage = 'cart';

// Fallback phòng trường hợp gọi view trực tiếp
$items       = $items       ?? [];
$subtotal    = $subtotal    ?? 0;
$shippingFee = $shippingFee ?? 0;
$discount    = $discount    ?? 0;
$total       = $total       ?? 0;
$couponCode  = $couponCode  ?? '';

require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/navbar.php';
?>

<style>

body{
    background:#f5f7fa;
}

.cart-card{
    border:none;
    border-radius:18px;
    overflow:hidden;
}

.cart-summary{
    position:sticky;
    top:90px;
    border:none;
    border-radius:18px;
    overflow:hidden;
}

.cart-summary .card-header{
    background:linear-gradient(
        135deg,
        #0d6efd,
        #0a58ca
    );
    color:#fff;
    border:none;
}

.cart-item{
    transition:.2s;
}

.cart-item:hover{
    background:#f8fbff;
}

.product-thumb{
    width:72px;
    height:72px;
    object-fit:cover;
    border-radius:12px;
    border:1px solid #eee;
}

.qty-input{
    font-weight:600;
}

.checkout-btn{
    height:54px;
    font-size:1.05rem;
    border-radius:12px;
}

.total-price{
    font-size:1.7rem;
    font-weight:700;
}

.price-highlight{
    color:#0d6efd;
}

.summary-box{
    background:#f8f9fa;
    border-radius:12px;
    padding:15px;
}

</style>

<main class="py-5">
<div class="container-xl">

  <!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">
        <a href="<?= BASE_URL ?>/">Trang chủ</a>
      </li>
      <li class="breadcrumb-item active">Giỏ hàng</li>
    </ol>
  </nav>

  <div class="alert alert-primary border-0 shadow-sm mb-4">
    <div class="d-flex align-items-center">
        <i class="fa-solid fa-bolt fa-2x me-3"></i>
        <div>
            <strong>Mua sắm an toàn tại TechGalaxy</strong>
            <div class="small">
                Miễn phí vận chuyển cho đơn từ 500.000₫
            </div>
        </div>
    </div>
  </div>

  <h2 class="mb-4">
    <i class="fa-solid fa-cart-shopping me-2 text-primary"></i>
    Giỏ hàng
    <?php if (!empty($items)): 
      $cartCount = array_sum(array_column($items, 'quantity'));
    ?>
      <span class="badge bg-primary fs-6 ms-1" id="cartCountBadge">
        <?= $cartCount ?>
      </span>
    <?php endif; ?>
  </h2>

  <?php if (empty($items)): ?>
  <div class="text-center py-5">
    <div class="mb-4">
    <i class="fa-solid fa-cart-shopping" style="font-size:100px; color:#d0d7de;"></i>
  </div>
    <h5 class="text-muted mb-2">Giỏ hàng của bạn đang trống</h5>
    <p class="text-muted mb-4">Hãy thêm sản phẩm để tiếp tục mua sắm.</p>
    <a href="<?= BASE_URL ?>/shop" class="btn btn-primary btn-lg px-5">
      <i class="fa-solid fa-store me-2"></i>Tiếp tục mua sắm
    </a>
  </div>

  <?php else: ?>
  <!-- ═══════════════ CÓ SẢN PHẨM ═══════════════ -->
  <div class="row g-4 align-items-start">

    <!-- ══ CỘT TRÁI: Bảng sản phẩm ══════════════ -->
    <div class="col-lg-8">
      <div class="card cart-card shadow">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th style="width:80px"></th>
                  <th>Sản phẩm</th>
                  <th class="text-center" style="width:120px">Đơn giá</th>
                  <th class="text-center" style="width:160px">Số lượng</th>
                  <th class="text-end"    style="width:120px">Thành tiền</th>
                  <th style="width:44px"></th>
                </tr>
              </thead>
              <tbody id="cartBody">
              <?php foreach ($items as $item):
                $lineTotal  = (int)$item['price'] * (int)$item['quantity'];
                  $imgSrc = getProductImageUrl($item['image_path'] ?? null);
              ?>
                <tr class="cart-item"
                    data-product-id="<?= (int)$item['product_id'] ?>"
                    data-price="<?= (int)$item['price'] ?>">

                  <!-- Ảnh -->
                  <td class="ps-3">
                    <a href="<?= BASE_URL ?>/product/<?= htmlspecialchars($item['slug']) ?>">
                      <img src="<?= $imgSrc ?>"
                        alt="<?= htmlspecialchars($item['name']) ?>"
                        class="product-thumb">
                    </a>
                  </td>

                  <!-- Tên + SKU + cảnh báo tồn kho -->
                  <td>
                    <a href="<?= BASE_URL ?>/product/<?= htmlspecialchars($item['slug']) ?>"
                       class="fw-semibold text-dark text-decoration-none d-block lh-sm item-name">
                      <?= htmlspecialchars($item['name']) ?>
                    </a>
                    <?php if (!empty($item['sku'])): ?>
                      <small class="text-muted">
                        SKU: <?= htmlspecialchars($item['sku']) ?>
                      </small>
                    <?php endif; ?>
                    <?php if ((int)$item['stock'] > 0 && (int)$item['stock'] <= 5): ?>
                      <small class="text-warning d-block mt-1">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        Chỉ còn <?= (int)$item['stock'] ?> sản phẩm
                      </small>
                    <?php endif; ?>
                  </td>

                  <!-- Đơn giá -->
                  <td class="text-center fw-bold price-highlight">
                    <?= formatPrice($item['price']) ?>
                  </td>

                  <!-- Số lượng +/- -->
                  <td class="text-center">
                    <div class="d-flex align-items-center justify-content-center gap-1">
                      <button class="btn btn-outline-secondary btn-sm px-2 qty-btn"
                              data-action="decrease"
                              data-product-id="<?= (int)$item['product_id'] ?>"
                              <?= (int)$item['quantity'] <= 1 ? 'disabled' : '' ?>>
                        <i class="fa-solid fa-minus fa-xs"></i>
                      </button>
                      <input type="number"
                             class="form-control form-control-sm text-center qty-input"
                             style="width:54px"
                             value="<?= (int)$item['quantity'] ?>"
                             min="1"
                             max="<?= (int)$item['stock'] ?>"
                             data-product-id="<?= (int)$item['product_id'] ?>"
                             data-last="<?= (int)$item['quantity'] ?>">
                      <button class="btn btn-outline-secondary btn-sm px-2 qty-btn"
                              data-action="increase"
                              data-product-id="<?= (int)$item['product_id'] ?>"
                              <?= (int)$item['quantity'] >= (int)$item['stock'] ? 'disabled' : '' ?>>
                        <i class="fa-solid fa-plus fa-xs"></i>
                      </button>
                    </div>
                  </td>

                  <!-- Thành tiền -->
                  <td class="text-end fw-bold pe-2 line-total">
                    <?= formatPrice($lineTotal) ?>
                  </td>

                  <!-- Xoá -->
                  <td class="text-center">
                    <button class="btn btn-link text-danger p-1 remove-btn"
                            data-product-id="<?= (int)$item['product_id'] ?>"
                            title="Xoá">
                      <i class="fa-solid fa-trash-can"></i>
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="card-footer bg-white d-flex justify-content-between flex-wrap gap-2">
          <a href="<?= BASE_URL ?>/shop"
             class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>Tiếp tục mua sắm
          </a>
          <button class="btn btn-outline-danger btn-sm" id="clearCartBtn">
            <i class="fa-solid fa-trash me-1"></i>Xoá toàn bộ giỏ
          </button>
        </div>
      </div>
    </div><!-- /col-lg-8 -->

    <!-- ══ CỘT PHẢI: Tổng đơn hàng ══════════════ -->
    <div class="col-lg-4">
      <div class="card cart-summary shadow">

        <div class="card-header bg-white border-bottom fw-bold py-3">
          <i class="fa-solid fa-receipt me-2 text-primary"></i>Tổng đơn hàng
        </div>

        <div class="card-body">

          <!-- Coupon -->
          <div class="mb-4">
            <label class="form-label fw-semibold small text-uppercase text-muted">
              Mã giảm giá
            </label>
            <div class="input-group">
              <input type="text"
                     id="couponInput"
                     class="form-control"
                     placeholder="Nhập mã..."
                     value="<?= htmlspecialchars($couponCode) ?>"
                     style="text-transform:uppercase">
              <button class="btn btn-outline-primary"
                      id="applyCouponBtn" type="button">
                Áp dụng
              </button>
            </div>
            <div id="couponMsg"
                 class="mt-2 small <?= $couponCode ? 'text-success' : 'd-none' ?>">
              <?php if ($couponCode): ?>
                <i class="fa-solid fa-circle-check"></i>
                Đã áp dụng mã <strong><?= htmlspecialchars($couponCode) ?></strong>
              <?php endif; ?>
            </div>
          </div>

          <!-- Bảng tổng tiền -->
          <ul class="list-unstyled mb-4">
            <li class="d-flex justify-content-between py-2 border-bottom">
              <span class="text-muted">Tạm tính</span>
              <span id="subtotalTxt"><?= formatPrice($subtotal) ?></span>
            </li>
            <li class="d-flex justify-content-between py-2 border-bottom">
              <span class="text-muted">
                Phí vận chuyển
                <?php if ($shippingFee === 0): ?>
                  <small class="text-success d-block">Miễn phí đơn ≥ 500K</small>
                <?php endif; ?>
              </span>
              <span id="shippingTxt">
                <?php if ($shippingFee === 0): ?>
                  <span class="text-success">Miễn phí</span>
                <?php else: ?>
                  <?= formatPrice($shippingFee) ?>
                <?php endif; ?>
              </span>
            </li>
            <li class="d-flex justify-content-between py-2 border-bottom text-success
                        <?= $discount === 0 ? 'd-none' : '' ?>"
                id="discountRow">
              <span>Giảm giá</span>
              <span id="discountTxt">
                - <?= formatPrice($discount) ?>
              </span>
            </li>
            <div class="summary-box mt-3">

                <div class="d-flex justify-content-between align-items-center">

                    <span class="fw-bold">
                        Tổng cộng
                    </span>

                    <span
                        class="text-primary total-price"
                        id="totalTxt">

                        <?= formatPrice($total) ?>

                    </span>

                </div>

            </div>
          </ul>

          <a href="<?= BASE_URL ?>/checkout" class="btn btn-primary w-100 fw-bold checkout-btn">
            <i class="fa-solid fa-lock me-2"></i>Đặt hàng ngay
          </a>

        </div>
      </div>
    </div><!-- /col-lg-4 -->

  </div><!-- /row -->
  <?php endif; ?>

</div><!-- /container -->
</main>

<?php
// ── JS: inject vào $extraJS → footer load sau Bootstrap & SweetAlert2
$csrf = $_SESSION['csrf_token'] ?? '';
ob_start();
?>
<script>
const CSRF     = '<?= $csrf ?>';
const BASE_URL = '<?= BASE_URL ?>';

/* ── Tiện ích ─────────────────────────────────────────── */
const fmtPrice = n => n.toLocaleString('vi-VN') + ' ₫';

function updateBadge(count) {
  ['cartBadge', 'cartBadgeMobile', 'cartCountBadge'].forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = count;
    el.classList.toggle('d-none', count === 0);
  });
}

function updateTotals(d) {
  document.getElementById('subtotalTxt').textContent = d.subtotalFmt;

  // Phí ship
  const shipEl = document.getElementById('shippingTxt');
  shipEl.innerHTML = d.shippingFee === 0
    ? '<span class="text-success">Miễn phí</span>'
    : d.shippingFeeFmt;

  // Discount row
  const discRow = document.getElementById('discountRow');
  const discTxt = document.getElementById('discountTxt');
  if (d.discount > 0) {
    discRow.classList.remove('d-none');
    discTxt.textContent = '- ' + d.discountFmt;
  } else {
    discRow.classList.add('d-none');
    clearCouponUI(); // giỏ thay đổi → coupon không còn hiệu lực
  }

  document.getElementById('totalTxt').textContent = d.totalFmt;
}

function clearCouponUI() {
  const inp = document.getElementById('couponInput');
  const msg = document.getElementById('couponMsg');
  if (inp) inp.value = '';
  if (msg) { msg.className = 'mt-2 small d-none'; msg.innerHTML = ''; }
}

function toast(icon, title) {
  Swal.fire({
    toast: true, position: 'top-end',
    icon, title,
    showConfirmButton: false,
    timer: 2200, timerProgressBar: true
  });
}

async function ajax(url, fd)
{
    try {

        fd.append('csrf_token', CSRF);

        const r = await fetch(BASE_URL + url, {
            method: 'POST',
            body: fd
        });

        return await r.json();

    } catch (e) {

        return {
            success: false,
            message: 'Lỗi kết nối máy chủ.'
        };
    }
}

function checkEmpty() {
  if (!document.querySelector('#cartBody .cart-item')) renderEmptyCart();
}

/* ── Debounce sync số lượng ───────────────────────────── */
let timer = null;

async function syncQty(productId, qty, row) {
  const fd = new FormData();
  fd.append('product_id', productId);
  fd.append('quantity',   qty);

  const d = await ajax('/cart/update', fd);

  if (!d.success) {
    toast('error', d.message);
    const inp = row.querySelector('.qty-input');
    inp.value = inp.dataset.last; // revert
    return;
  }

  if (qty <= 0) {
    row.remove();
    checkEmpty();
  } else {
    // Cập nhật thành tiền dòng
    const price  = parseInt(row.dataset.price ?? 0);
    const lineTd = row.querySelector('.line-total');
    if (lineTd) lineTd.textContent = fmtPrice(price * qty);
    row.querySelector('.qty-input').dataset.last = qty;
  }

  updateTotals(d);
  updateBadge(d.cartCount);
  clearCouponUI();
}

/* ── Nút +/- ──────────────────────────────────────────── */
document.querySelectorAll('.qty-btn').forEach(btn => {
  btn.addEventListener('click', function () {
    const row = this.closest('.cart-item');
    const inp = row.querySelector('.qty-input');
    let   qty = parseInt(inp.value);
    const max = parseInt(inp.max);
    const pid = this.dataset.productId;

    if (this.dataset.action === 'increase') {
      if (qty >= max) { toast('warning', 'Đã đạt số lượng tối đa.'); return; }
      qty++;
    } else {
      if (qty <= 1) return;
      qty--;
    }

    inp.value = qty;
    row.querySelector('[data-action="decrease"]').disabled = qty <= 1;
    row.querySelector('[data-action="increase"]').disabled = qty >= max;

    clearTimeout(timer);
    timer = setTimeout(() => syncQty(pid, qty, row), 500);
  });
});

/* ── Input gõ tay ─────────────────────────────────────── */
document.querySelectorAll('.qty-input').forEach(inp => {
  inp.addEventListener('change', function () {
    const row = this.closest('.cart-item');
    const pid = this.dataset.productId;
    let   qty = parseInt(this.value) || 0;
    const max = parseInt(this.max);

    if (qty > max) { qty = max; this.value = max; }
    if (qty < 0)   { qty = 0;  this.value = 0; }

    row.querySelector('[data-action="decrease"]').disabled = qty <= 1;
    row.querySelector('[data-action="increase"]').disabled = qty >= max;

    clearTimeout(timer);
    timer = setTimeout(() => syncQty(pid, qty, row), 500);
  });
});

/* ── Xoá 1 sản phẩm ───────────────────────────────────── */
document.querySelectorAll('.remove-btn').forEach(btn => {
  btn.addEventListener('click', async function () {
    const pid  = this.dataset.productId;
    const row  = this.closest('.cart-item');
    const name = row.querySelector('.item-name')?.textContent.trim() ?? '';

    const cf = await Swal.fire({
      title: 'Xoá sản phẩm?', text: name, icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc3545',
      confirmButtonText: 'Xoá', cancelButtonText: 'Huỷ',
    });
    if (!cf.isConfirmed) return;

    const fd = new FormData();
    fd.append('product_id', pid);
    const d = await ajax('/cart/remove', fd);

    if (!d.success) { toast('error', d.message); return; }

    row.remove();
    updateTotals(d);
    updateBadge(d.cartCount);
    clearCouponUI();
    checkEmpty();
    toast('success', 'Đã xoá sản phẩm.');
  });
});

/* ── Xoá toàn bộ giỏ ─────────────────────────────────── */
document.getElementById('clearCartBtn')?.addEventListener('click', async () => {
  const cf = await Swal.fire({
    title: 'Xoá toàn bộ giỏ hàng?',
    text:  'Hành động này không thể hoàn tác.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    confirmButtonText: 'Xoá hết', cancelButtonText: 'Huỷ',
  });
  if (!cf.isConfirmed) return;

  // clearCart() redirect → phải dùng form submit
  const form   = document.createElement('form');
  form.method  = 'POST';
  form.action  = BASE_URL + '/cart/clear';
  const hidden = document.createElement('input');
  hidden.type  = 'hidden';
  hidden.name  = 'csrf_token';
  hidden.value = CSRF;
  form.appendChild(hidden);
  document.body.appendChild(form);
  form.submit();
});

/* ── Áp mã giảm giá ──────────────────────────────────── */
document.getElementById('applyCouponBtn')?.addEventListener('click', async () => {
  const inp  = document.getElementById('couponInput');
  const msg  = document.getElementById('couponMsg');
  const code = inp.value.trim().toUpperCase();

  if (!code) {
    msg.className = 'mt-2 small text-danger';
    msg.textContent = 'Vui lòng nhập mã giảm giá.';
    return;
  }

  const fd = new FormData();
  fd.append('coupon_code', code);
  const d = await ajax('/cart/coupon', fd);

  if (!d.success) {
    msg.className = 'mt-2 small text-danger';
    msg.innerHTML = '<i class="fa-solid fa-circle-xmark me-1"></i>' + d.message;
    return;
  }

  msg.className = 'mt-2 small text-success';
  msg.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i>' + d.message;
  updateTotals(d);
});
</script>
<?php
$extraJS = ob_get_clean();
require __DIR__ . '/../../includes/footer.php';