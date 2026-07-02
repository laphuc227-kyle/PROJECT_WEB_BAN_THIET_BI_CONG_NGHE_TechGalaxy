<?php
// File: controllers/OrderController.php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

// Nhúng các Model cần thiết mà Controller đang gọi
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/OrderDetail.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/CartItem.php';

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
    // ĐÃ SỬA LẠI CHO ĐÚNG: AuthController lưu $_SESSION['user_id'] là số nguyên
    private function getCurrentUserId(): int
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        return (int) $_SESSION['user_id'];
    }

    // GET /checkout
    public function showCheckout(): void
    {
        $userId = $this->getCurrentUserId();
        $cart      = $this->cartModel->getOrCreateCart($userId, session_id());
        $cartItems = $this->cartItemModel->getByCartId((int) $cart['id']);

        if (empty($cartItems)) {
            // ĐÃ SỬA: Thêm BASE_URL để không bị mất thư mục
            header('Location: ' . BASE_URL . '/cart');
            exit;
        }

        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $shippingFee = $subtotal >= 500000 ? 0 : 30000;
        $discount    = (int) ($_SESSION['coupon_discount'] ?? 0);
        $grandTotal  = $subtotal + $shippingFee - $discount;

        // ĐÃ SỬA: Lấy danh sách địa chỉ đã lưu của user để hiển thị radio chọn
        $pdo = $this->orderModel->getPdo();
        $stmt = $pdo->prepare(
            "SELECT * FROM addresses
             WHERE user_id = :user_id
             ORDER BY is_default DESC, id DESC"
        );
        $stmt->execute([':user_id' => $userId]);
        $addresses = $stmt->fetchAll();

        require __DIR__ . '/../views/user/checkout.php';
    }

    // POST /checkout
    public function placeOrder(): void
    {
        $userId = $this->getCurrentUserId();
        $cart   = $this->cartModel->getOrCreateCart($userId, session_id());
        $items  = $this->cartItemModel->getByCartId((int) $cart['id']);

        if (empty($items)) {
            header('Location: ' . BASE_URL . '/cart');
            exit;
        }

        $addressId     = (int)  ($_POST['address_id']        ?? 0);
        $newName       = trim(   $_POST['new_name']           ?? '');
        $newPhone      = trim(   $_POST['new_phone']           ?? '');
        $newAddress    = trim(   $_POST['new_address']         ?? '');
        // ĐÃ SỬA: form gửi "COD"/"BANKING" viết hoa -> chuẩn hoá về chữ thường trước khi so sánh
        $paymentMethod = strtolower(trim($_POST['payment_method'] ?? 'cod'));
        $note          = trim(   $_POST['note']               ?? '');
        $couponId      = (int)  ($_SESSION['coupon_id']       ?? 0);
        $discount      = (int)  ($_SESSION['coupon_discount'] ?? 0);

        if (!in_array($paymentMethod, ['cod', 'banking'], true)) {
            setFlash('error', 'Phương thức thanh toán không hợp lệ.');
            header('Location: ' . BASE_URL . '/checkout');
            exit;
        }

        $pdo = $this->orderModel->getPdo();

        // ĐÃ SỬA: Nếu chưa chọn địa chỉ có sẵn -> thử tạo địa chỉ mới từ form
        if ($addressId <= 0) {
            if ($newName === '' || $newPhone === '' || $newAddress === '') {
                setFlash('error', 'Vui lòng chọn địa chỉ có sẵn hoặc nhập đầy đủ địa chỉ giao hàng mới.');
                header('Location: ' . BASE_URL . '/checkout');
                exit;
            }

            $stmt = $pdo->prepare(
                "INSERT INTO addresses (user_id, name, phone, province, district, ward, detail, is_default)
                 VALUES (:user_id, :name, :phone, :province, :district, :ward, :detail, 0)"
            );
            $stmt->execute([
                ':user_id'  => $userId,
                ':name'     => $newName,
                ':phone'    => $newPhone,
                ':province' => '',
                ':district' => '',
                ':ward'     => '',
                ':detail'   => $newAddress,
            ]);
            $addressId = (int) $pdo->lastInsertId();
        }

        $subtotal    = 0;
        foreach ($items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $shippingFee = $subtotal >= 500000 ? 0 : 30000;
        $total       = $subtotal + $shippingFee - $discount;

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
                     SET stock = stock - :qty1
                     WHERE id = :id AND stock >= :qty2"
                );
                $stmt->execute([
                    ':qty1' => $item['quantity'],
                    ':qty2' => $item['quantity'],
                    ':id'   => $item['product_id'],
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

            // ĐÃ SỬA: Chuyển hướng chuẩn xác sang trang order-complete
            header("Location: " . BASE_URL . "/order/complete/{$orderId}");
            exit;

        } catch (\RuntimeException $e) {
            $pdo->rollBack();
            setFlash('error', $e->getMessage());
            header('Location: ' . BASE_URL . '/checkout');
            exit;

        } catch (\Exception $e) {
            $pdo->rollBack();
            error_log('[placeOrder Error] ' . $e->getMessage());
            setFlash('error', 'Đặt hàng thất bại. Vui lòng thử lại.');
            header('Location: ' . BASE_URL . '/checkout');
            exit;
        }
    }
    
    // GET /order/complete/{id}
    public function orderComplete(int $orderId): void
    {
        // ĐÃ TẠM THỜI TẮT CHECK USER ĐỂ AI CŨNG XEM ĐƯỢC TRANG NÀY
        $order  = $this->orderModel->getById($orderId);

        if (!$order) {
            header('Location: ' . BASE_URL . '/');
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
        // ĐÃ SỬA: dùng getOrderDetail() vì có JOIN sẵn thông tin địa chỉ giao hàng
        $order  = $this->orderModel->getOrderDetail($orderId);

        // Không tồn tại HOẶC không phải đơn của user này -> chặn lại
        if (!$order || (int) $order['user_id'] !== $userId) {
            header('Location: ' . BASE_URL . '/my-orders');
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
                throw new RuntimeException('Không thể huỷ đơn hàng.');
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
            $_SESSION['success'] = 'Đã huỷ đơn hàng thành công.';

        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = 'Không thể huỷ đơn hàng.';
        }

        header('Location: ' . BASE_URL . '/my-orders');
        exit;
    }

    public function getOrderStats(): array
    {
        return [
            'total' => $this->orderModel->countOrders(),
            'pending' => $this->orderModel->countOrders('pending'),
            'shipping' => $this->orderModel->countOrders('shipping'),
            'completed' => $this->orderModel->countOrders('completed')
        ];
    }

    // GET /admin/orders
    public function adminIndex(): void
    {
        $status = trim($_GET['status'] ?? '');
        $orders = $this->orderModel->getAllOrders($status);
        $stats = $this->getOrderStats();

        require __DIR__ . '/../views/admin/orders/index.php';
    }

    // GET /admin/orders/{id}
    public function adminDetail(int $orderId): void
    {
        $order = $this->orderModel->getOrderDetail($orderId);

        if (!$order) {
            header('Location: ' . BASE_URL . '/admin/orders');
            exit;
        }

        $items = $this->orderDetailModel->getByOrderId($orderId);
        require __DIR__ . '/../views/admin/orders/detail.php';
    }

    // POST /admin/orders/{id}/status
    public function adminUpdateStatus(int $orderId): void
    {
        try {
            $status = trim($_POST['status'] ?? '');
            $allowedStatuses = [
                'pending', 'confirmed', 'shipping', 'delivered', 'completed', 'cancelled'
            ];

            if (!in_array($status, $allowedStatuses, true)) {
                $_SESSION['flash_message'] = 'Trạng thái không hợp lệ.';
                $_SESSION['flash_type'] = 'danger';
                return;
            }

            $success = $this->orderModel->updateStatus($orderId, $status);

            if ($success) {
                $_SESSION['flash_message'] = 'Cập nhật trạng thái thành công.';
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_message'] = 'Trạng thái không đổi hoặc đã được cập nhật trước đó.';
                $_SESSION['flash_type'] = 'warning';
            }

        } catch (\Throwable $e) {
            error_log('[adminUpdateStatus] ' . $e->getMessage());
            $_SESSION['flash_message'] = 'Không thể cập nhật trạng thái. Vui lòng thử lại.';
            $_SESSION['flash_type'] = 'danger';
        }

        header("Location: " . BASE_URL . "/admin/orders/{$orderId}");
        exit;
    }
}