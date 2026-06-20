<?php
// File: controllers/OrderController.php
declare(strict_types=1);

class OrderController
{
    private Order $orderModel;
    private OrderDetail $orderDetailModel;
    private Cart $cartModel;
    private CartItem $cartItemModel;

    public function __construct()
    {
        $this->orderModel       = new Order();
        $this->orderDetailModel = new OrderDetail();
        $this->cartModel        = new Cart();
        $this->cartItemModel    = new CartItem();
    }

    // Lấy user_id từ session — redirect login nếu chưa đăng nhập
    private function getCurrentUserId(): int
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        return (int) $_SESSION['user']['id'];
    }

    // GET /checkout
    public function showCheckout(): void
    {
        $userId = $this->getCurrentUserId();
        $cart   = $this->cartModel->getOrCreateCart($userId, session_id());
        $items  = $this->cartItemModel->getByCartId((int) $cart['id']);

        if (empty($items)) {
            header('Location: /cart');
            exit;
        }

        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['price'] * $item['quantity']; // sửa typo
        }

        $shippingFee = $subtotal >= 500000 ? 0 : 30000;
        $discount    = (int) ($_SESSION['coupon_discount'] ?? 0);
        $total       = $subtotal + $shippingFee - $discount;

        require __DIR__ . '/../views/user/checkout.php';
    }

    // POST /checkout
    public function placeOrder(): void
    {
        $userId = $this->getCurrentUserId();
        $cart   = $this->cartModel->getOrCreateCart($userId, session_id());
        $items  = $this->cartItemModel->getByCartId((int) $cart['id']);

        if (empty($items)) {
            header('Location: /cart');
            exit;
        }

        $addressId     = (int)  ($_POST['address_id']        ?? 0);
        $paymentMethod = trim(   $_POST['payment_method']     ?? 'cod');
        $note          = trim(   $_POST['note']               ?? '');
        $couponId      = (int)  ($_SESSION['coupon_id']       ?? 0);
        $discount      = (int)  ($_SESSION['coupon_discount'] ?? 0);

        if (!in_array($paymentMethod, ['cod', 'banking'], true)) {
            $_SESSION['error'] = 'Phương thức thanh toán không hợp lệ.';
            header('Location: /checkout');
            exit;
        }

        if ($addressId <= 0) {
            $_SESSION['error'] = 'Vui lòng chọn địa chỉ giao hàng.';
            header('Location: /checkout');
            exit;
        }

        $subtotal    = 0;
        foreach ($items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $shippingFee = $subtotal >= 500000 ? 0 : 30000;
        $total       = $subtotal + $shippingFee - $discount;

        $pdo = $this->orderModel->getPdo();

        try {
            $pdo->beginTransaction();

            // Kiểm tra stock + lock từng sản phẩm
            foreach ($items as $item) {
                $stmt = $pdo->prepare(
                    "SELECT stock FROM products WHERE id = :id FOR UPDATE"
                );
                $stmt->execute([':id' => $item['product_id']]);
                $stock = (int) $stmt->fetchColumn();

                if ($stock < $item['quantity']) {
                    throw new \RuntimeException(
                        "Sản phẩm \"{$item['name']}\" không đủ số lượng trong kho."
                    );
                }
            }

            // Tạo đơn hàng
            $orderId = $this->orderModel->createOrder([
                'user_id'        => $userId,
                'address_id'     => $addressId,
                'status'         => 'pending',
                'total'          => $total,
                'payment_method' => $paymentMethod,
                'coupon_id'      => $couponId ?: null,
                'discount'       => $discount,
                'note'           => $note,
            ]);

            // Tạo order_details + giảm stock
            foreach ($items as $item) {
                $this->orderDetailModel->createDetail(
                    $orderId,
                    (int) $item['product_id'],
                    (int) $item['quantity'],
                    (int) $item['price']
                );

                $stmt = $pdo->prepare(
                    "UPDATE products
                     SET stock = stock - :qty
                     WHERE id = :id AND stock >= :qty"
                );
                $stmt->execute([
                    ':qty' => $item['quantity'],
                    ':id'  => $item['product_id'],
                ]);

                if ($stmt->rowCount() === 0) {
                    throw new \RuntimeException(
                        "Sản phẩm \"{$item['name']}\" vừa hết hàng."
                    );
                }
            }

            // Đánh dấu coupon đã dùng
            if ($couponId > 0) {
                $stmt = $pdo->prepare(
                    "UPDATE coupons
                     SET used_count = used_count + 1
                     WHERE id = :id
                       AND (max_uses = 0 OR used_count < max_uses)"
                );
                $stmt->execute([':id' => $couponId]);
            }

            // Xoá giỏ hàng
            $this->cartModel->clearCart((int) $cart['id']);

            $pdo->commit();

            unset(
                $_SESSION['coupon_code'],
                $_SESSION['coupon_id'],
                $_SESSION['coupon_discount']
            );

            header("Location: /order/complete/{$orderId}");
            exit;

        } catch (\RuntimeException $e) {
            $pdo->rollBack();
            $_SESSION['error'] = $e->getMessage();
            header('Location: /checkout');
            exit;

        } catch (\Exception $e) {
            $pdo->rollBack();
            error_log('[placeOrder Error] ' . $e->getMessage());
            $_SESSION['error'] = 'Đặt hàng thất bại. Vui lòng thử lại.';
            header('Location: /checkout');
            exit;
        }
    }

    // GET /order/complete/{id}
    public function orderComplete(int $orderId): void
    {
        $userId = $this->getCurrentUserId();
        $order  = $this->orderModel->getByIdAndUserId($orderId, $userId);

        if (!$order) {
            header('Location: /');
            exit;
        }

        $items = $this->orderDetailModel->getByOrderId($orderId);
        require __DIR__ . '/../views/user/order_complete.php';
    }

    // GET /my-orders
    public function myOrders(): void
    {
        $userId = $this->getCurrentUserId();
        $orders = $this->orderModel->getByUserId($userId);
        require __DIR__ . '/../views/user/my_orders.php';
    }

    // GET /my-orders/{id}
    public function orderDetail(int $orderId): void
    {
        $userId = $this->getCurrentUserId();
        $order  = $this->orderModel->getByIdAndUserId($orderId, $userId);

        if (!$order) {
            header('Location: /my-orders');
            exit;
        }

        $items = $this->orderDetailModel->getByOrderId($orderId);
        require __DIR__ . '/../views/user/order_detail.php';
    }

    // POST /my-orders/{id}/cancel
    public function cancelOrder(int $orderId): void
    {
    $userId = $this->getCurrentUserId();

    $pdo = $this->orderModel->getPdo();

    try {

        $pdo->beginTransaction();

        $items = $this->orderDetailModel->getByOrderId($orderId);

        $success = $this->orderModel->cancelOrder(
            $orderId,
            $userId
        );

        if (!$success) {
            throw new RuntimeException(
                'Không thể huỷ đơn hàng.'
            );
        }

        foreach ($items as $item) {

            $stmt = $pdo->prepare(
                "UPDATE products
                 SET stock = stock + :qty
                 WHERE id = :id"
            );

            $stmt->execute([
                ':qty' => $item['quantity'],
                ':id'  => $item['product_id']
            ]);
        }

        $pdo->commit();

        $_SESSION['success'] =
            'Đã huỷ đơn hàng thành công.';

    } catch (Exception $e) {

        $pdo->rollBack();

        $_SESSION['error'] =
            'Không thể huỷ đơn hàng.';
    }

    header('Location: /my-orders');
    exit;
    }



    public function getOrderStats(): array
{
    return [

        'total' =>
            $this->orderModel->countOrders(),

        'pending' =>
            $this->orderModel->countOrders(
                'pending'
            ),

        'shipping' =>
            $this->orderModel->countOrders(
                'shipping'
            ),

        'completed' =>
            $this->orderModel->countOrders(
                'completed'
            )
    ];
}

    // GET /admin/orders
    public function adminIndex(): void
    {
        $status = trim(
            $_GET['status'] ?? ''
        );

        $orders = $this->orderModel
            ->getAllOrders($status);

        $stats = $this->getOrderStats();

        require __DIR__
            . '/../views/admin/orders/index.php';
    }

    // GET /admin/orders/{id}
    public function adminDetail(int $orderId): void
    {
        $order = $this->orderModel->getOrderDetail($orderId);

        if (!$order) {
            header('Location: /admin/orders');
            exit;
        }

        $items = $this->orderDetailModel->getByOrderId($orderId);

        require __DIR__ . '/../views/admin/orders/detail.php';
    }

    // POST /admin/orders/{id}/status
    public function adminUpdateStatus(int $orderId): void
    {
        $status = trim($_POST['status'] ?? '');

        $allowedStatuses = [
        'pending',
        'confirmed',
        'shipping',
        'delivered',
        'completed',
        'cancelled'
        ];

        if (!in_array($status, $allowedStatuses, true)) {

            $_SESSION['error'] = 'Trạng thái không hợp lệ.';

            header("Location: /admin/orders/{$orderId}");
            exit;
        }

        $success = $this->orderModel->updateStatus(
            $orderId,
            $status
        );

        if ($success) {
            $_SESSION['success'] = 'Cập nhật trạng thái thành công.';
        } else {
            $_SESSION['error'] = 'Không thể cập nhật trạng thái.';
        }

        header("Location: /admin/orders/{$orderId}");
        exit;
    }

}