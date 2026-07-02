<?php
declare(strict_types=1);

$pageTitle   = 'Chi tiết đơn hàng';
$currentPage = 'my-orders';

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
    'pending'   => ['class' => 'warning',   'icon' => 'clock',        'text' => 'Chờ xác nhận'],
    'confirmed' => ['class' => 'info',      'icon' => 'check',        'text' => 'Đã xác nhận'],
    'shipping'  => ['class' => 'primary',   'icon' => 'truck',        'text' => 'Đang giao'],
    'completed' => ['class' => 'success',   'icon' => 'circle-check', 'text' => 'Hoàn thành'],
    'cancelled' => ['class' => 'danger',    'icon' => 'xmark',        'text' => 'Đã huỷ'],
    default     => ['class' => 'secondary', 'icon' => 'circle',       'text' => ucfirst($order['status'])],
};

$canCancel = in_array($order['status'], ['pending', 'confirmed'], true);
$discount  = (float) ($order['discount'] ?? 0);
$total     = (float) ($order['total']    ?? 0);
$csrf      = $_SESSION['csrf_token'] ?? '';
?>

<style>
.order-detail-page{ background:#f5f7fa; min-height:100vh; }
.order-card{ border:none; border-radius:16px; }
.order-card .card-header{ background:#0d6efd; color:#fff; border:none; }
.product-row:hover{ background:#f8f9fa; transition:.2s; }
.product-image{ width:70px; height:70px; object-fit:cover; }
.summary-card{ position:sticky; top:20px; }
.total-box{ background:#0d6efd; color:#fff; border-radius:12px; padding:15px; }
</style>

<main class="order-detail-page py-5">
<div class="container">

    <!-- Breadcrumb + tiêu đề -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/my-orders">Đơn hàng của tôi</a></li>
                    <li class="breadcrumb-item active">#<?= (int) $order['id'] ?></li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-0">
                Đơn hàng #<?= str_pad((string) $order['id'], 6, '0', STR_PAD_LEFT) ?>
            </h2>
        </div>

        <span class="badge bg-<?= $statusInfo['class'] ?> fs-6 px-3 py-2">
            <i class="fa-solid fa-<?= $statusInfo['icon'] ?> me-1"></i>
            <?= $statusInfo['text'] ?>
        </span>
    </div>

    <div class="row g-4">

        <!-- LEFT -->
        <div class="col-lg-8">

            <div class="card order-card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa-solid fa-box me-2"></i>Thông tin đơn hàng</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Ngày đặt</strong>
                            <div>
                                <?= !empty($order['created_at'])
                                    ? date('d/m/Y H:i', strtotime($order['created_at']))
                                    : '-' ?>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Thanh toán</strong>
                            <div>
                                <?= strtolower((string)($order['payment_method'] ?? '')) === 'banking'
                                    ? 'Chuyển khoản ngân hàng'
                                    : 'Thanh toán khi nhận hàng (COD)' ?>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($order['note'])): ?>
                        <hr>
                        <strong>Ghi chú</strong>
                        <p class="mt-2 mb-0"><?= nl2br(htmlspecialchars($order['note'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card order-card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa-solid fa-location-dot me-2"></i>Địa chỉ giao hàng</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($order['receiver_name'])): ?>
                        <div><strong><?= htmlspecialchars($order['receiver_name']) ?></strong></div>
                        <div><?= htmlspecialchars($order['receiver_phone'] ?? '') ?></div>
                        <div class="text-muted">
                            <?= htmlspecialchars(trim(implode(', ', array_filter([
                                $order['detail']   ?? '',
                                $order['ward']     ?? '',
                                $order['district'] ?? '',
                                $order['province'] ?? '',
                            ])), ', ')) ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">Không có thông tin địa chỉ.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card order-card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa-solid fa-cart-shopping me-2"></i>Sản phẩm đã đặt</h5>
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
                                                    <div class="fw-semibold"><?= htmlspecialchars($item['name']) ?></div>
                                                    <small class="text-muted">SKU: <?= htmlspecialchars($item['sku'] ?? '') ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center"><?= (int) $item['quantity'] ?></td>
                                        <td class="text-end"><?= formatPrice((float) $item['price']) ?></td>
                                        <td class="text-end fw-bold"><?= formatPrice((float) $item['subtotal']) ?></td>
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
                    <h5 class="mb-0"><i class="fa-solid fa-credit-card me-2"></i>Thanh toán</h5>
                </div>
                <div class="card-body">
                    <?php if ($discount > 0): ?>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Giảm giá</span>
                            <span class="text-success fw-bold">-<?= formatPrice($discount) ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="total-box">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Tổng thanh toán</span>
                            <span class="fw-bold fs-4"><?= formatPrice($total) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($canCancel): ?>
                <form action="<?= BASE_URL ?>/my-orders/<?= (int) $order['id'] ?>/cancel"
                      method="POST"
                      class="mt-4"
                      onsubmit="return confirm('Bạn có chắc muốn huỷ đơn hàng này?');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i class="fa-solid fa-xmark me-2"></i>Huỷ đơn hàng
                    </button>
                </form>
            <?php endif; ?>

            <a href="<?= BASE_URL ?>/my-orders" class="btn btn-outline-secondary w-100 mt-3">
                <i class="fa-solid fa-arrow-left me-2"></i>Quay lại danh sách
            </a>
        </div>

    </div>

</div>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>