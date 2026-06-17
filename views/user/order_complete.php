<?php
// File: views/user/order_complete.php

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>

<div class="container py-5">

    <div class="text-center mb-5">

        <div class="mb-3">
            <i
                class="fa-solid fa-circle-check text-success"
                style="font-size:80px;">
            </i>
        </div>

        <h1 class="text-success">
            Đặt hàng thành công
        </h1>

        <p class="text-muted">
            Cảm ơn bạn đã mua sắm tại cửa hàng.
        </p>

    </div>

    <div class="row justify-content-center">

        <div class="col-lg-8">

            <div class="card shadow-sm">

                <div class="card-header">
                    <strong>Thông tin đơn hàng</strong>
                </div>

                <div class="card-body">

                    <div class="row mb-3">

                        <div class="col-md-6">
                            <strong>Mã đơn hàng:</strong>
                            #<?= $order['id'] ?>
                        </div>

                        <div class="col-md-6">
                            <strong>Ngày đặt:</strong>
                            <?= date(
                                'd/m/Y H:i',
                                strtotime($order['created_at'])
                            ) ?>
                        </div>

                    </div>

                    <div class="row mb-3">

                        <div class="col-md-6">

                            <strong>Trạng thái:</strong>

                            <span class="badge bg-warning text-dark">
                                <?= ucfirst($order['status']) ?>
                            </span>

                        </div>

                        <div class="col-md-6">

                            <strong>Thanh toán:</strong>

                            <?= $order['payment_method'] === 'banking'
                                ? 'Chuyển khoản'
                                : 'COD' ?>

                        </div>

                    </div>

                    <?php if (!empty($order['note'])): ?>

                        <div class="mb-3">

                            <strong>Ghi chú:</strong>

                            <div class="text-muted">
                                <?= nl2br(
                                    htmlspecialchars($order['note'])
                                ) ?>
                            </div>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

            <!-- Danh sách sản phẩm -->

            <div class="card shadow-sm mt-4">

                <div class="card-header">
                    <strong>Sản phẩm đã đặt</strong>
                </div>

                <div class="card-body p-0">

                    <table class="table mb-0">

                        <thead>

                        <tr>
                            <th>Sản phẩm</th>
                            <th class="text-center">
                                Số lượng
                            </th>
                            <th class="text-end">
                                Đơn giá
                            </th>
                            <th class="text-end">
                                Thành tiền
                            </th>
                        </tr>

                        </thead>

                        <tbody>

                        <?php foreach ($items as $item): ?>

                            <tr>

                                <td>
                                    <?= htmlspecialchars($item['name']) ?>
                                </td>

                                <td class="text-center">
                                    <?= $item['quantity'] ?>
                                </td>

                                <td class="text-end">
                                    <?= formatPrice($item['price']) ?>
                                </td>

                                <td class="text-end">

                                    <?= formatPrice(
                                        $item['subtotal']
                                    ) ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </div>

            <!-- Tổng tiền -->

            <div class="card shadow-sm mt-4">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <strong>Tổng thanh toán</strong>

                        <strong class="text-danger fs-4">

                            <?= formatPrice(
                                $order['total']
                            ) ?>

                        </strong>

                    </div>

                </div>

            </div>

            <!-- Nút -->

            <div class="text-center mt-4">

                <a
                    href="<?= BASE_URL ?>/my-orders"
                    class="btn btn-primary">

                    Đơn hàng của tôi

                </a>

                <a
                    href="<?= BASE_URL ?>/shop"
                    class="btn btn-outline-secondary">

                    Tiếp tục mua sắm

                </a>

            </div>

        </div>

    </div>

</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>