<?php

$pageTitle = 'Quản lý tồn kho';

$products = $products ?? [];

if (!defined('BASE_URL')) {
    define('BASE_URL', '');
}

require_once __DIR__ . '/../../../includes/header.php';
?>

<div class="container-fluid py-4">

```
<div class="card border-0 shadow-sm">

    <div class="card-header bg-white">

        <div class="row align-items-center">

            <div class="col-md-6">
                <h4 class="mb-0">
                    Quản lý tồn kho
                </h4>
            </div>

            <div class="col-md-6">

                <form method="GET">

                    <div class="row g-2">

                        <div class="col-md-8">
                            <input
                                type="text"
                                class="form-control"
                                name="keyword"
                                placeholder="Tìm sản phẩm..."
                            >
                        </div>

                        <div class="col-md-4">
                            <select
                                name="stock_filter"
                                class="form-select"
                            >
                                <option value="">
                                    Tất cả
                                </option>

                                <option value="low">
                                    Sắp hết hàng
                                </option>

                                <option value="out">
                                    Hết hàng
                                </option>
                            </select>
                        </div>

                    </div>

                </form>

            </div>

        </div>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>Sản phẩm</th>
                        <th>SKU</th>
                        <th>Tồn kho</th>
                        <th>Đã bán</th>
                        <th>Trạng thái</th>
                        <th width="180">Điều chỉnh</th>
                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($products as $product): ?>

                        <tr>

                            <td>
                                <?= $product['id'] ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($product['name']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($product['sku']) ?>
                            </td>

                            <td>

                                <?php if ($product['stock'] <= 0): ?>
                                    <span class="badge bg-danger">
                                        <?= $product['stock'] ?>
                                    </span>

                                <?php elseif ($product['stock'] < 10): ?>
                                    <span class="badge bg-warning text-dark">
                                        <?= $product['stock'] ?>
                                    </span>

                                <?php else: ?>
                                    <span class="badge bg-success">
                                        <?= $product['stock'] ?>
                                    </span>
                                <?php endif; ?>

                            </td>

                            <td>
                                <?= $product['sold'] ?? 0 ?>
                            </td>

                            <td>

                                <?php if ($product['stock'] <= 0): ?>
                                    <span class="badge bg-danger">
                                        Hết hàng
                                    </span>

                                <?php elseif ($product['stock'] < 10): ?>
                                    <span class="badge bg-warning text-dark">
                                        Sắp hết
                                    </span>

                                <?php else: ?>
                                    <span class="badge bg-success">
                                        Còn hàng
                                    </span>
                                <?php endif; ?>

                            </td>

                            <td>

                                <button
                                    class="btn btn-sm btn-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#stockModal<?= $product['id'] ?>"
                                >
                                    Điều chỉnh
                                </button>

                            </td>

                        </tr>

                        <div
                            class="modal fade"
                            id="stockModal<?= $product['id'] ?>"
                        >
                            <div class="modal-dialog">
                                <div class="modal-content">

                                    <form
                                        method="POST"
                                        action="<?= BASE_URL ?>/admin/inventory/update"
                                    >

                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                Điều chỉnh tồn kho
                                            </h5>
                                        </div>

                                        <div class="modal-body">

                                            <input
                                                type="hidden"
                                                name="product_id"
                                                value="<?= $product['id'] ?>"
                                            >

                                            <div class="mb-3">
                                                <label>
                                                    Số lượng mới
                                                </label>

                                                <input
                                                    type="number"
                                                    class="form-control"
                                                    name="stock"
                                                    value="<?= $product['stock'] ?>"
                                                    required
                                                >
                                            </div>

                                            <div>
                                                <label>
                                                    Lý do
                                                </label>

                                                <textarea
                                                    class="form-control"
                                                    name="reason"
                                                    rows="3"
                                                ></textarea>
                                            </div>

                                        </div>

                                        <div class="modal-footer">

                                            <button
                                                class="btn btn-secondary"
                                                data-bs-dismiss="modal"
                                            >
                                                Huỷ
                                            </button>

                                            <button
                                                type="submit"
                                                class="btn btn-primary"
                                            >
                                                Lưu thay đổi
                                            </button>

                                        </div>

                                    </form>

                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>
```

</div>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
