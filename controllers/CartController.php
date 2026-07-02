<?php
// File: controllers/CartController.php
declare(strict_types=1);

namespace Controllers;

// 1. NẠP TRỰC TIẾP CÁC FILE MODEL (Khắc phục triệt để lỗi Class not found)
require_once __DIR__ . '/../models/BaseModel.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/CartItem.php';
require_once __DIR__ . '/../models/Coupon.php';

// 2. KHAI BÁO SỬ DỤNG
use Models\Product; // Chỉ giữ Product vì các class dưới đã được Quân chuyển ra Global

class CartController
{
    private \Cart $cartModel;
    private \CartItem $cartItemModel; 
    private Product $productModel;
    private \Coupon $couponModel;

    public function __construct()
    {
        $this->cartModel     = new \Cart();
        $this->cartItemModel = new \CartItem(); 
        $this->productModel  = new Product();
        $this->couponModel   = new \Coupon();
    }

    // ----------------------------------------------------------
    // Helper: lấy user_id từ session (null nếu guest)
    // ----------------------------------------------------------
    private function getUserId(): ?int
    {
        // GIỮ CODE CỦA QUÂN: Sửa lỗi lấy session_id
        return !empty($_SESSION['user_id'])
            ? (int) $_SESSION['user_id']
            : null;
    }

    // ----------------------------------------------------------
    // Helper: trả JSON và kết thúc request
    // ----------------------------------------------------------
    private function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ----------------------------------------------------------
    // Helper: tính lại toàn bộ tổng tiền giỏ hàng
    // ----------------------------------------------------------
    private function calcTotals(int $cartId): array
    {
        $subtotal    = (int) $this->cartModel->getCartSubtotal($cartId);
        $shippingFee = $subtotal >= 500000 ? 0 : 30000;
        $discount    = (int) ($_SESSION['coupon_discount'] ?? 0);
        $total       = $subtotal + $shippingFee - $discount;

        return [
            'subtotal'    => $subtotal,
            'shippingFee' => $shippingFee,
            'discount'    => $discount,
            'total'       => max(0, $total), // không âm
        ];
    }

    // ----------------------------------------------------------
    // GET /cart — hiển thị trang giỏ hàng
    // ----------------------------------------------------------
    public function showCart(): void
    {
        $userId    = $this->getUserId();
        $sessionId = session_id();

        $cart  = $this->cartModel->getOrCreateCart($userId, $sessionId);
        $items = $this->cartItemModel->getByCartId((int) $cart['id']);

        $totals      = $this->calcTotals((int) $cart['id']);
        $subtotal    = $totals['subtotal'];
        $shippingFee = $totals['shippingFee'];
        $discount    = $totals['discount'];
        $total       = $totals['total'];
        $couponCode  = $_SESSION['coupon_code'] ?? '';

        $pageTitle = 'Giỏ hàng';
        require_once __DIR__ . '/../views/user/cart.php';
    }

    // ----------------------------------------------------------
    // POST /cart/add — thêm sản phẩm (AJAX → JSON)
    // ----------------------------------------------------------
    public function addToCart(): void
    {
        $raw = json_decode(file_get_contents('php://input'), true) ?: [];
        $productId = (int) ($_POST['product_id'] ?? $raw['product_id'] ?? 0);
        $quantity  = (int) ($_POST['quantity']   ?? $raw['quantity']   ?? 1);

        if ($productId <= 0 || $quantity <= 0) {
            $this->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ.'], 400);
        }

        $product = $this->productModel->findById($productId);
        if (!$product) {
            $this->json(['success' => false, 'message' => 'Sản phẩm không tồn tại.'], 404);
        }

        if ((int) $product['stock'] <= 0) {
            $this->json(['success' => false, 'message' => 'Sản phẩm đã hết hàng.'], 422);
        }

        $userId    = $this->getUserId();
        $sessionId = session_id();
        $cart      = $this->cartModel->getOrCreateCart($userId, $sessionId);
        $cartId    = (int) $cart['id'];

        $existing = $this->cartItemModel->findItem($cartId, $productId);
        $newQty   = $quantity + ($existing ? (int) $existing['quantity'] : 0);

        if ($newQty > (int) $product['stock']) {
            $this->json(['success' => false, 'message' => 'Số lượng vượt quá tồn kho. Còn lại: ' . $product['stock'] . ' sản phẩm.'], 422);
        }

        $price = !empty($product['sale_price']) ? (int)$product['sale_price'] : (int)$product['price'];

        $this->cartItemModel->addItem($cartId, $productId, $quantity, $price);

        $cartCount = $this->cartModel->countItems($cartId);

        $this->json([
            'success'   => true,
            'message'   => 'Đã thêm vào giỏ hàng.',
            'cartCount' => $cartCount,
        ]);
    }

    public function updateCart(): void
    {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity  = (int) ($_POST['quantity']   ?? 0);

        if ($productId <= 0) {
            $this->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ.'], 400);
        }

        if ($quantity > 0) {
            $product = $this->productModel->findById($productId);
            if ($product && $quantity > (int) $product['stock']) {
                $this->json(['success' => false, 'message' => 'Số lượng vượt quá tồn kho. Còn lại: ' . $product['stock']], 422);
            }
        }

        $userId    = $this->getUserId();
        $sessionId = session_id();
        $cart      = $this->cartModel->getOrCreateCart($userId, $sessionId);
        $cartId    = (int) $cart['id'];

        $success = $this->cartItemModel->updateQuantity($cartId, $productId, $quantity);
        if (!$success) {
            $this->json(['success' => false, 'message' => 'Không tìm thấy sản phẩm trong giỏ.'], 404);
        }
        $totals    = $this->calcTotals($cartId);
        $cartCount = $this->cartModel->countItems($cartId);

        $this->json([
            'success'     => true,
            'cartCount'   => $cartCount,
            'subtotal'    => $totals['subtotal'],
            'shippingFee' => $totals['shippingFee'],
            'discount'    => $totals['discount'],
            'total'       => $totals['total'],
            'subtotalFmt'    => number_format($totals['subtotal'],    0, ',', '.') . ' ₫',
            'shippingFeeFmt' => number_format($totals['shippingFee'], 0, ',', '.') . ' ₫',
            'discountFmt'    => number_format($totals['discount'],    0, ',', '.') . ' ₫',
            'totalFmt'       => number_format($totals['total'],       0, ',', '.') . ' ₫',
        ]);
    }

    public function removeFromCart(): void
    {
        $productId = (int) ($_POST['product_id'] ?? 0);

        if ($productId <= 0) {
            $this->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ.'], 400);
        }

        $userId    = $this->getUserId();
        $sessionId = session_id();
        $cart      = $this->cartModel->getOrCreateCart($userId, $sessionId);
        $cartId    = (int) $cart['id'];

        $success = $this->cartItemModel->removeItem($cartId, $productId);

        if (!$success) {
            $this->json(['success' => false, 'message' => 'Không tìm thấy sản phẩm trong giỏ.'], 404);
        }

        $totals    = $this->calcTotals($cartId);
        $cartCount = $this->cartModel->countItems($cartId);

        $this->json([
            'success'        => true,
            'message'        => 'Đã xoá sản phẩm khỏi giỏ hàng.',
            'cartCount'      => $cartCount,
            'subtotal'       => $totals['subtotal'],
            'shippingFee'    => $totals['shippingFee'],
            'discount'       => $totals['discount'],
            'total'          => $totals['total'],
            'subtotalFmt'    => number_format($totals['subtotal'],    0, ',', '.') . ' ₫',
            'shippingFeeFmt' => number_format($totals['shippingFee'], 0, ',', '.') . ' ₫',
            'discountFmt'    => number_format($totals['discount'],    0, ',', '.') . ' ₫',
            'totalFmt'       => number_format($totals['total'],       0, ',', '.') . ' ₫',
        ]);
    }

    public function clearCart(): void
    {
        $userId    = $this->getUserId();
        $sessionId = session_id();
        $cart      = $this->cartModel->getOrCreateCart($userId, $sessionId);

        $this->cartModel->clearCart((int) $cart['id']);

        unset($_SESSION['coupon_code'], $_SESSION['coupon_id'], $_SESSION['coupon_discount']);

        setFlash('success', 'Đã xoá toàn bộ giỏ hàng.');
        redirect('/cart');
    }

    public function applyCoupon(): void
    {
        $code = strtoupper(trim($_POST['coupon_code'] ?? ''));

        if ($code === '') {
            $this->json(['success' => false, 'message' => 'Vui lòng nhập mã giảm giá.'], 400);
        }

        $userId    = $this->getUserId();
        $sessionId = session_id();
        $cart      = $this->cartModel->getOrCreateCart($userId, $sessionId);
        $cartId    = (int) $cart['id'];

        $subtotal = (float) $this->cartModel->getCartSubtotal($cartId);

        if ($subtotal <= 0) {
            $this->json(['success' => false, 'message' => 'Giỏ hàng trống, không thể áp mã giảm giá.'], 422);
        }

        $result = $this->couponModel->validateCoupon($code, $subtotal, $userId ?? 0);

        if (!$result['valid']) {
            $this->json(['success' => false, 'message' => $result['message']]);
        }

        $_SESSION['coupon_code']     = $result['coupon']['code'];
        $_SESSION['coupon_id']       = (int) $result['coupon']['id'];
        $_SESSION['coupon_discount'] = (int) $result['discount'];

        $shippingFee = (int) $subtotal >= 500000 ? 0 : 30000;
        $discount    = (int) $result['discount'];
        $total       = max(0, (int) $subtotal + $shippingFee - $discount);

        $this->json([
            'success'        => true,
            'message'        => $result['message'],
            'discount'       => $discount,
            'shippingFee'    => $shippingFee,
            'total'          => $total,
            'discountFmt'    => number_format($discount,    0, ',', '.') . ' ₫',
            'shippingFeeFmt' => number_format($shippingFee, 0, ',', '.') . ' ₫',
            'totalFmt'       => number_format($total,       0, ',', '.') . ' ₫',
        ]);
    }

    // ----------------------------------------------------------
    // GET /checkout — hiển thị trang thanh toán
    // ----------------------------------------------------------
    public function showCheckout(): void
    {
        $userId    = $this->getUserId();
        $sessionId = session_id();

        $cart   = $this->cartModel->getOrCreateCart($userId, $sessionId);
        $cartId = (int) $cart['id'];
        
        $cartItems  = $this->cartItemModel->getByCartId($cartId);

        if (empty($cartItems)) {
            setFlash('error', 'Giỏ hàng của bạn đang trống, không thể thanh toán.');
            redirect('/cart');
            exit;
        }   

        $totals      = $this->calcTotals($cartId);
        $subtotal    = $totals['subtotal'];
        $shippingFee = $totals['shippingFee'];
        $discount    = $totals['discount'];
        $grandTotal  = $totals['total'];
        
        $couponCode  = $_SESSION['coupon_code'] ?? '';
        $pageTitle   = 'Thanh toán đơn hàng';

        require_once __DIR__ . '/../views/user/checkout.php';
    }

    // ----------------------------------------------------------
    // POST /checkout — xử lý khi bấm nút Xác nhận đặt hàng
    // ----------------------------------------------------------
    public function processCheckout(): void
    {
        $addressId = $_POST['address_id'] ?? null;
        $newName   = trim($_POST['new_name'] ?? '');
        $newPhone  = trim($_POST['new_phone'] ?? '');
        $newAddr   = trim($_POST['new_address'] ?? '');
        $note      = trim($_POST['note'] ?? '');
        $payment   = $_POST['payment_method'] ?? 'COD';

        $userId    = $this->getUserId();
        $sessionId = session_id();
        $cart      = $this->cartModel->getOrCreateCart($userId, $sessionId);
        $cartId    = (int) $cart['id'];

        // Lưu Order...

        $this->cartModel->clearCart($cartId);

        unset($_SESSION['coupon_code'], $_SESSION['coupon_id'], $_SESSION['coupon_discount']);

        redirect('/order-complete');
        exit;
    }

    public function getCartCount(): void
    {
        $userId    = $this->getUserId();
        $sessionId = session_id();
        $cart      = $this->cartModel->getOrCreateCart($userId, $sessionId);
        $count     = $this->cartModel->countItems((int) $cart['id']);

        $this->json(['cartCount' => $count]);
    }
}

// =========================================================
// KHỞI TẠO VÀ ĐIỀU HƯỚNG (ROUTER) DÀNH CHO GIỎ HÀNG
// =========================================================
$cartController = new CartController();

$basePath = parse_url(BASE_URL ?? '', PHP_URL_PATH) ?: '';
$uri      = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path     = trim((string) preg_replace('#^' . preg_quote($basePath, '#') . '#', '', $uri), '/');

if ($path === 'cart') {
    $cartController->showCart();
} elseif ($path === 'cart/add') {
    $cartController->addToCart();
} elseif ($path === 'cart/update') {
    $cartController->updateCart();
} elseif ($path === 'cart/remove') {
    $cartController->removeFromCart();
} elseif ($path === 'cart/clear') {
    $cartController->clearCart();
} elseif ($path === 'cart/coupon') {
    $cartController->applyCoupon();
} elseif ($path === 'cart/count') {
    $cartController->getCartCount();

// GIỮ LẠI ĐIỀU HƯỚNG CHECKOUT CỦA PHÚC ĐỂ GỌI ĐÚNG HÀM CỦA QUÂN
} elseif ($path === 'checkout') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $cartController->processCheckout();
    } else {
        $cartController->showCheckout();
    }
}