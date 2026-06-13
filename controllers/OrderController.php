<?php
declare(strict_types=1);

require_once __DIR__ . "/../models/Order.php";
require_once __DIR__ . "/../models/OrderDetail.php";
require_once __DIR__ . "/../models/Cart.php";
require_once __DIR__ . "/../models/CartItem.php";

class OrderController
{
    private Order $orderModel;
    private OrderDetail $orderDetailModel;
    private Cart $cartModel;
    private CartItem $cartItemModel;

    public function __construct()
    {
        $this->orderModel = new Order();
        $this->orderDetailModel = new OrderDetail();
        $this->cartModel = new Cart();
        $this->cartItemModel = new CartItem();
    }

    private function getCurrentUserId() : int
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        return (int)$_SESSION['user']['id'];
    }

    public function showCheckout(): void
    {
        $userId = $this->getCurrentUserId();

        $cart = $this->cartModel->getOrCreateCart($userId, session_id());

        $items = $this->cartItemModel->getByCartId((int)$cart['id']);

        if (empty($items)) {
            header('Location: /cart');
            exit;
        }

        $subtotal = 0;

        foreach ($items as $item) {
            $subtotal += $item['price'] * $item['quanlity'];
        }

        require __DIR__ . '/../views/user/checkout.php';
    }


    public function orderComplete(int $orderId): void 
    {
    $userId =
        $this->getCurrentUserId();

    $order =
        $this->orderModel
        ->getByIdAndUserId(
            $orderId,
            $userId
        );

    if (!$order) {

        header('Location: /');
        exit;
    }

    require __DIR__ . '/../views/user/order_complete.php';
    }

    public function myOrders(): void
    {
    $userId =
        $this->getCurrentUserId();

    $orders =
        $this->orderModel
        ->getByUserId(
            $userId
        );

    require __DIR__ . '/../views/user/my_orders.php';
    }


    public function orderDetail(int $orderId): void
    {
    $userId =
        $this->getCurrentUserId();

    $order =
        $this->orderModel
        ->getByIdAndUserId(
            $orderId,
            $userId
        );

    if (!$order) {

        header('Location: /my-orders');
        exit;
    }

    $items =
        $this->orderDetailModel
        ->getByOrderId(
            $orderId
        );

    require __DIR__ . '/../views/user/order_detail.php';
    }


    public function cancelOrder(int $orderId): void
    {
    $userId =
        $this->getCurrentUserId();

    $success =
        $this->orderModel
        ->cancelOrder(
            $orderId,
            $userId
        );

    if ($success) {

        $_SESSION['success'] =
            'Đã hủy đơn hàng';
    }
    else {

        $_SESSION['error'] =
            'Không thể hủy đơn hàng';
    }

    header(
        'Location: /my-orders'
    );

    exit;
    }


    
}