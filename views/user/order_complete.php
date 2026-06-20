<?php
declare(strict_types=1);

$pageTitle = 'Đặt hàng thành công';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

$order = $order ?? [];
$items = $items ?? [];

if (empty($order)) {
?>

<div class="container py-5">
    <div class="alert alert-danger">
        Không tìm thấy đơn hàng.
    </div>
</div>
<?php
    require_once __DIR__ . '/../../includes/footer.php';
    return;
}

$statusInfo = match ($order['status']) {

'pending' => [
    'class' => 'warning',
    'icon'  => 'clock',
    'text'  => 'Chờ xác nhận'
],

'confirmed' => [
    'class' => 'info',
    'icon'  => 'check',
    'text'  => 'Đã xác nhận'
],

'shipping' => [
    'class' => 'primary',
    'icon'  => 'truck',
    'text'  => 'Đang giao'
],

'completed' => [
    'class' => 'success',
    'icon'  => 'circle-check',
    'text'  => 'Hoàn thành'
],

'cancelled' => [
    'class' => 'danger',
    'icon'  => 'xmark',
    'text'  => 'Đã huỷ'
],

default => [
    'class' => 'secondary',
    'icon'  => 'circle',
    'text'  => ucfirst($order['status'])
]


};

$discount = (float)($order['discount'] ?? 0);
$total    = (float)($order['total'] ?? 0);
?>

<style>

.order-complete-page{
    background:
        linear-gradient(
            135deg,
            #f5f7fa 0%,
            #eef2f7 100%
        );
    min-height:100vh;
}

.success-card{
    border:none;
    border-radius:20px;
}

.success-icon{
    width:120px;
    height:120px;
    border-radius:50%;
    background:rgba(25,135,84,.12);
    display:flex;
    align-items:center;
    justify-content:center;
    margin:auto;
}

.success-icon i{
    font-size:70px;
}

.order-card{
    border:none;
    border-radius:16px;
}

.order-card .card-header{
    background:#0d6efd;
    color:#fff;
    border:none;
}

.product-row:hover{
    background:#f8f9fa;
    transition:.2s;
}

.product-image{
    width:70px;
    height:70px;
    object-fit:cover;
}

.summary-card{
    position:sticky;
    top:20px;
}

.total-box{
    background:#0d6efd;
    color:#fff;
    border-radius:12px;
    padding:15px;
}

</style>

    <main class="order-complete-page py-5">

    ```
    <div class="container">

        <!-- SUCCESS -->

        <div class="card success-card shadow-lg mb-5">

            <div class="card-body text-center py-5">

                <div class="success-icon mb-4">
                    <i class="fa-solid fa-circle-check text-success"></i>
                </div>

                <h1 class="fw-bold text-success">
                    Đặt hàng thành công!
                </h1>

                <p class="text-muted fs-5">
                    Cảm ơn bạn đã mua sắm tại TechGalaxy.
                </p>

                <div class="mt-4">

                    <span class="badge bg-primary fs-6 px-4 py-3">
                        Mã đơn hàng #<?= (int)$order['id'] ?>
                    </span>

                </div>

            </div>

        </div>

        <div class="row g-4">

            <!-- LEFT -->

            <div class="col-lg-8">

                <div class="card order-card shadow-sm mb-4">

                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fa-solid fa-box me-2"></i>
                            Thông tin đơn hàng
                        </h5>
                    </div>

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <strong>Mã đơn hàng</strong>
                                <div>#<?= (int)$order['id'] ?></div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <strong>Ngày đặt</strong>
                                <div>
                                    <?= !empty($order['created_at'])
                                        ? date('d/m/Y H:i', strtotime($order['created_at']))
                                        : '-' ?>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <strong>Trạng thái</strong>
                                <div>
                                    <span class="badge bg-<?= $statusInfo['class'] ?>">
                                        <i class="fa-solid fa-<?= $statusInfo['icon'] ?>"></i>
                                        <?= $statusInfo['text'] ?>
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <strong>Thanh toán</strong>
                                <div>
                                    <?= $order['payment_method'] === 'banking'
                                        ? 'Chuyển khoản ngân hàng'
                                        : 'Thanh toán khi nhận hàng (COD)' ?>
                                </div>
                            </div>

                        </div>

                        <?php if (!empty($order['note'])): ?>

                            <hr>

                            <strong>Ghi chú</strong>

                            <p class="mt-2 mb-0">
                                <?= nl2br(htmlspecialchars($order['note'])) ?>
                            </p>

                        <?php endif; ?>

                    </div>

                </div>

                <div class="card order-card shadow-sm">

                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fa-solid fa-cart-shopping me-2"></i>
                            Sản phẩm đã đặt
                        </h5>
                    </div>

                    <div class="card-body p-0">

                        <div class="table-responsive">

                            <table class="table align-middle mb-0">

                                <thead>

                                <tr>
                                    <th>Sản phẩm</th>
                                    <th class="text-center">SL</th>
                                    <th class="text-end">Đơn giá</th>
                                    <th class="text-end">Thành tiền</th>
                                </tr>

                                </thead>

                                <tbody>

                                <?php foreach ($items as $item): ?>

                                    <tr class="product-row">

                                        <td>

                                            <div class="d-flex align-items-center">

                                                <img
                                                    src="<?= BASE_URL ?>/public/uploads/products/<?= htmlspecialchars($item['image_path'] ?? 'default-product.jpg') ?>"
                                                    alt="<?= htmlspecialchars($item['name']) ?>"
                                                    class="product-image border rounded me-3"
                                                >

                                                <div>

                                                    <div class="fw-semibold">
                                                        <?= htmlspecialchars($item['name']) ?>
                                                    </div>

                                                    <small class="text-muted">
                                                        SKU:
                                                        <?= htmlspecialchars($item['sku']) ?>
                                                    </small>

                                                </div>

                                            </div>

                                        </td>

                                        <td class="text-center">
                                            <?= (int)$item['quantity'] ?>
                                        </td>

                                        <td class="text-end">
                                            <?= formatPrice((float)$item['price']) ?>
                                        </td>

                                        <td class="text-end fw-bold">
                                            <?= formatPrice((float)$item['subtotal']) ?>
                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

            <!-- RIGHT -->

            <div class="col-lg-4">

                <div class="card order-card shadow summary-card">

                    <div class="card-header bg-dark">
                        <h5 class="mb-0">
                            <i class="fa-solid fa-credit-card me-2"></i>
                            Thanh toán
                        </h5>
                    </div>

                    <div class="card-body">

                        <?php if ($discount > 0): ?>

                            <div class="d-flex justify-content-between mb-3">

                                <span>Giảm giá</span>

                                <span class="text-success fw-bold">
                                    -<?= formatPrice($discount) ?>
                                </span>

                            </div>

                        <?php endif; ?>

                        <div class="total-box">

                            <div class="d-flex justify-content-between align-items-center">

                                <span class="fw-bold">
                                    Tổng thanh toán
                                </span>

                                <span class="fw-bold fs-4">
                                    <?= formatPrice($total) ?>
                                </span>

                            </div>

                        </div>

                    </div>

                </div>

                <?php if (($order['payment_method'] ?? '') === 'banking'): ?>

                    <div class="card order-card shadow-sm mt-4">

                        <div class="card-header bg-success">
                            <h5 class="mb-0">
                                <i class="fa-solid fa-building-columns me-2"></i>
                                Thông tin chuyển khoản
                            </h5>
                        </div>

                        <div class="card-body">

                            <p>
                                <strong>Ngân hàng:</strong><br>
                                <?= defined('BANK_NAME') ? BANK_NAME : 'Chưa cấu hình' ?>
                            </p>

                            <p>
                                <strong>Số tài khoản:</strong><br>
                                <?= defined('BANK_ACCOUNT') ? BANK_ACCOUNT : 'Chưa cấu hình' ?>
                            </p>

                            <p>
                                <strong>Chủ tài khoản:</strong><br>
                                <?= defined('BANK_OWNER') ? BANK_OWNER : 'Chưa cấu hình' ?>
                            </p>

                            <div class="alert alert-warning mb-0">

                                <strong>Nội dung chuyển khoản:</strong><br>

                                TG<?= (int)$order['id'] ?>

                            </div>

                        </div>

                    </div>

                <?php endif; ?>

            </div>

        </div>

        <div class="text-center mt-5">

            <a href="<?= BASE_URL ?>/my-orders"
            class="btn btn-primary btn-lg px-4">

                <i class="fa-solid fa-box me-2"></i>

                Đơn hàng của tôi

            </a>

            <a href="<?= BASE_URL ?>/shop"
            class="btn btn-outline-dark btn-lg px-4 ms-2">

                <i class="fa-solid fa-store me-2"></i>

                Tiếp tục mua sắm

            </a>

        </div>

    </div>


</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

