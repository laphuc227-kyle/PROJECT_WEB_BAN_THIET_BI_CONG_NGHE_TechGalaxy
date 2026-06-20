<?php

$pageTitle = 'Thanh toán';

/*
|--------------------------------------------------------------------------
| Safe Defaults
|--------------------------------------------------------------------------
| Giúp View chạy độc lập trước khi merge code toàn nhóm
*/

$addresses = $addresses ?? [];
$cartItems = $cartItems ?? [];

$subtotal = (float)($subtotal ?? 0);
$shippingFee = (float)($shippingFee ?? 30000);
$discount = (float)($discount ?? 0);

$grandTotal = (float)(
    $grandTotal ??
    ($subtotal + $shippingFee - $discount)
);

if (!defined('BASE_URL')) {
    define('BASE_URL', '');
}

if (!function_exists('formatPrice')) {
    function formatPrice($price): float
    {
        return number_format((float)$price, 0, ',', '.') . ' ₫';
    }
}

require_once __DIR__ . '/../../includes/header.php';

if (file_exists(__DIR__ . '/../../includes/navbar.php')) {
    require_once __DIR__ . '/../../includes/navbar.php';
}
?>

<div class="container py-5">
    <form action="<?= BASE_URL ?>/checkout" method="POST">

```
    <div class="row g-4">

        <!-- LEFT -->
        <div class="col-lg-7">

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        Địa chỉ giao hàng
                    </h5>
                </div>

                <div class="card-body">

                    <?php if (!empty($addresses)): ?>
                        <?php foreach ($addresses as $address): ?>
                            <div class="form-check border rounded p-3 mb-3">
                                <input
                                    class="form-check-input"
                                    type="radio"
                                    name="address_id"
                                    value="<?= $address['id'] ?>"
                                    <?= $address['is_default'] ? 'checked' : '' ?>
                                >

                                <label class="form-check-label w-100">
                                    <strong>
                                        <?= htmlspecialchars($address['name']) ?>
                                    </strong>

                                    <div>
                                        <?= htmlspecialchars($address['phone']) ?>
                                    </div>

                                    <div class="text-muted">
                                        <?= htmlspecialchars($address['detail']) ?>
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <hr>

                    <h6 class="mb-3">
                        Hoặc nhập địa chỉ mới
                    </h6>

                    <div class="row g-3">

                        <div class="col-md-6">
                            <input
                                type="text"
                                class="form-control"
                                name="new_name"
                                placeholder="Họ tên"
                            >
                        </div>

                        <div class="col-md-6">
                            <input
                                type="text"
                                class="form-control"
                                name="new_phone"
                                placeholder="Số điện thoại"
                            >
                        </div>

                        <div class="col-12">
                            <textarea
                                class="form-control"
                                rows="3"
                                name="new_address"
                                placeholder="Địa chỉ chi tiết"
                            ></textarea>
                        </div>

                    </div>

                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        Ghi chú đơn hàng
                    </h5>
                </div>

                <div class="card-body">
                    <textarea
                        name="note"
                        class="form-control"
                        rows="4"
                        placeholder="Ghi chú cho đơn hàng..."
                    ></textarea>
                </div>
            </div>

        </div>

        <!-- RIGHT -->
        <div class="col-lg-5">

            <div class="card shadow-sm border-0 mb-4">

                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        Đơn hàng của bạn
                    </h5>
                </div>

                <div class="card-body">

                    <?php if (!empty($cartItems)): ?>

                        <?php foreach ($cartItems as $item): ?>

                            <div class="d-flex justify-content-between mb-3">

                                <div>
                                    <?= htmlspecialchars($item['name'] ?? 'Sản phẩm') ?>

                                    <small class="text-muted">
                                        x<?= (int)($item['quantity'] ?? 1) ?>
                                    </small>
                                </div>

                                <div>
                                    <?= formatPrice($item['subtotal'] ?? 0) ?>
                                </div>

                            </div>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <div class="text-center py-4">

                            <i class="fa-solid fa-cart-shopping fs-1 text-secondary"></i>

                            <h5 class="mt-3">
                                Chưa có sản phẩm nào trong giỏ hàng
                            </h5>

                        </div>

                    <?php endif; ?>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <span>Tạm tính</span>
                        <strong>
                            <?= formatPrice($subtotal) ?>
                        </strong>
                    </div>

                    <div class="d-flex justify-content-between mt-2">
                        <span>Phí vận chuyển</span>
                        <strong>
                            <?= formatPrice($shippingFee) ?>
                        </strong>
                    </div>

                    <div class="d-flex justify-content-between mt-2">
                        <span>Giảm giá</span>
                        <strong class="text-success">
                            -<?= formatPrice($discount) ?>
                        </strong>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <h5>Tổng cộng</h5>
                        <h5 class="text-primary">
                            <?= formatPrice($grandTotal) ?>
                        </h5>
                    </div>

                </div>

            </div>

            <div class="card shadow-sm border-0">

                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        Phương thức thanh toán
                    </h5>
                </div>

                <div class="card-body">

                    <div class="form-check mb-3">
                        <input
                            class="form-check-input"
                            type="radio"
                            name="payment_method"
                            value="COD"
                            checked
                        >

                        <label class="form-check-label">
                            Thanh toán khi nhận hàng (COD)
                        </label>
                    </div>

                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="radio"
                            name="payment_method"
                            value="BANKING"
                        >

                        <label class="form-check-label">
                            Chuyển khoản ngân hàng
                        </label>
                    </div>

                    <div class="alert alert-info mt-3">
                        Ngân hàng: Vietcombank <br>
                        STK: 123456789 <br>
                        Chủ TK: TECHGALAXY
                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary w-100 mt-3"
                    >
                        Xác nhận đặt hàng
                    </button>

                </div>

            </div>

        </div>

    </div>

</form>
```

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
