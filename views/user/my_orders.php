<?php
declare(strict_types=1);

$pageTitle = 'Đơn hàng của tôi';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>

<style>

.orders-page{
    background:#f5f7fa;
    min-height:100vh;
}

.order-card{
    border:none;
    border-radius:18px;
    transition:.2s;
}

.order-card:hover{
    transform:translateY(-3px);
}

.order-price{
    color:#0d6efd;
    font-size:1.3rem;
    font-weight:700;
}

.empty-box{
    padding:80px 20px;
}

</style>

<main class="orders-page py-5">

<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="fw-bold mb-1">
                Đơn hàng của tôi
            </h2>

            <div class="text-muted">
                Theo dõi trạng thái đơn hàng của bạn
            </div>

        </div>

        <a href="<?= BASE_URL ?>/shop"
           class="btn btn-primary">

            <i class="fa-solid fa-store me-2"></i>
            Tiếp tục mua sắm

        </a>

    </div>

    <?php if (empty($orders)): ?>

        <div class="card shadow-sm border-0">

            <div class="card-body text-center empty-box">

                <i class="fa-solid fa-box-open"
                   style="font-size:100px;color:#d6d6d6;"></i>

                <h4 class="mt-4">
                    Bạn chưa có đơn hàng nào
                </h4>

                <p class="text-muted">
                    Hãy khám phá các sản phẩm công nghệ mới nhất.
                </p>

                <a href="<?= BASE_URL ?>/shop"
                   class="btn btn-primary btn-lg">

                    Mua sắm ngay

                </a>

            </div>

        </div>

    <?php else: ?>

        <?php foreach ($orders as $order): ?>

            <?php

            $statusInfo = match ($order['status']) {

                'pending' => [
                    'class' => 'warning',
                    'icon' => 'clock',
                    'text' => 'Chờ xác nhận'
                ],

                'confirmed' => [
                    'class' => 'info',
                    'icon' => 'check',
                    'text' => 'Đã xác nhận'
                ],

                'shipping' => [
                    'class' => 'primary',
                    'icon' => 'truck'
                    ,
                    'text' => 'Đang giao'
                ],

                'delivered' => [
                    'class' => 'success',
                    'icon' => 'box',
                    'text' => 'Đã giao'
                ],

                'completed' => [
                    'class' => 'success',
                    'icon' => 'circle-check',
                    'text' => 'Hoàn thành'
                ],

                'cancelled' => [
                    'class' => 'danger',
                    'icon' => 'xmark',
                    'text' => 'Đã huỷ'
                ],

                default => [
                    'class' => 'secondary',
                    'icon' => 'circle',
                    'text' => ucfirst($order['status'])
                ]
            };

            ?>

            <div class="card shadow-sm order-card mb-4">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <h5 class="mb-1">

                                #DH<?= str_pad(
                                    (string)$order['id'],
                                    6,
                                    '0',
                                    STR_PAD_LEFT
                                ) ?>

                            </h5>

                            <small class="text-muted">

                                <?= date(
                                    'd/m/Y H:i',
                                    strtotime($order['created_at'])
                                ) ?>

                            </small>

                        </div>

                        <span class="badge bg-<?= $statusInfo['class'] ?>">

                            <i class="fa-solid fa-<?= $statusInfo['icon'] ?>"></i>

                            <?= $statusInfo['text'] ?>

                        </span>

                    </div>

                    <hr>

                    <div class="row">

                        <div class="col-md-4 mb-3">

                            <div class="text-muted small">
                                Thanh toán
                            </div>

                            <div>

                                <?= $order['payment_method'] === 'banking'
                                    ? 'Chuyển khoản'
                                    : 'COD' ?>

                            </div>

                        </div>

                        <div class="col-md-4 mb-3">

                            <div class="text-muted small">
                                Sản phẩm
                            </div>

                            <div>

                                <?= (int)$order['product_count'] ?>

                                sản phẩm

                            </div>

                        </div>

                        <div class="col-md-4 mb-3">

                            <div class="text-muted small">
                                Tổng tiền
                            </div>

                            <div class="order-price">

                                <?= formatPrice(
                                    (float)$order['total']
                                ) ?>

                            </div>

                        </div>

                    </div>

                    <hr>

                    <div class="d-flex flex-wrap gap-2">

                        <a href="<?= BASE_URL ?>/my-orders/<?= $order['id'] ?>"
                           class="btn btn-primary">

                            <i class="fa-solid fa-eye me-2"></i>

                            Xem chi tiết

                        </a>

                        <?php if (
                            in_array(
                                $order['status'],
                                ['pending','confirmed'],
                                true
                            )
                        ): ?>

                            <form
                                action="<?= BASE_URL ?>/my-orders/<?= $order['id'] ?>/cancel"
                                method="POST"
                                onsubmit="return confirm('Bạn có chắc muốn huỷ đơn hàng này?')">

                                <button
                                    type="submit"
                                    class="btn btn-outline-danger">

                                    <i class="fa-solid fa-xmark me-2"></i>

                                    Huỷ đơn

                                </button>

                            </form>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</div>

</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>