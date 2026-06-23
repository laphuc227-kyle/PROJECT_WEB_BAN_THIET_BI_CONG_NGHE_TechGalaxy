<?php
// File: models/Cart.php
declare(strict_types=1);

class Cart extends BaseModel
{
    protected string $table = 'carts';

    // ----------------------------------------------------------
    // Tìm hoặc tạo giỏ hàng
    // - User đã login : tìm theo user_id
    // - Guest         : tìm theo session_id (user_id IS NULL)
    // - Chưa có       : tạo mới, trả về cart vừa tạo
    // ----------------------------------------------------------
    public function getOrCreateCart(?int $userId, string $sessionId): array
    {
        if ($userId !== null) {
            // Đã login → tìm theo user_id
            $stmt = $this->pdo->prepare(
                "SELECT * FROM carts
                 WHERE user_id = :user_id
                 LIMIT 1"
            );
            $stmt->execute([':user_id' => $userId]);
        } else {
            // Guest → tìm theo session_id, đảm bảo user_id vẫn là NULL
            $stmt = $this->pdo->prepare(
                "SELECT * FROM carts
                 WHERE session_id = :session_id AND user_id IS NULL
                 LIMIT 1"
            );
            $stmt->execute([':session_id' => $sessionId]);
        }

        $cart = $stmt->fetch();

        // Đã có giỏ → trả về luôn
        if ($cart) {
            return $cart;
        }

        // Chưa có → tạo mới bằng insert() kế thừa từ BaseModel
        $newId = $this->insert([
            'user_id'    => $userId,     // NULL nếu là guest
            'session_id' => $sessionId,
        ]);

        // findById() kế thừa từ BaseModel, trả về array của cart vừa tạo
        return $this->findById($newId);
    }

    // ----------------------------------------------------------
    // Đếm tổng số lượng sản phẩm trong giỏ (dùng cho navbar badge)
    // COALESCE trả về 0 thay vì NULL khi giỏ trống
    // ----------------------------------------------------------
    public function countItems(int $cartId): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(SUM(quantity), 0)
             FROM cart_items
             WHERE cart_id = :cart_id"
        );
        $stmt->execute([':cart_id' => $cartId]);
        return (int) $stmt->fetchColumn();
    }

    // ----------------------------------------------------------
    // Tổng tiền tạm tính
    // ----------------------------------------------------------
    public function getCartSubtotal(
        int $cartId
    ): float {

        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(SUM(quantity * price), 0)
             FROM cart_items
             WHERE cart_id = :cart_id"
        );

        $stmt->execute([':cart_id' => $cartId]);

        return (float)$stmt->fetchColumn();
    }


    // ----------------------------------------------------------
    // Xoá toàn bộ sản phẩm trong giỏ (gọi sau khi đặt hàng thành công)
    // ----------------------------------------------------------
    public function clearCart(int $cartId): bool
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM cart_items
             WHERE cart_id = :cart_id"
        );
        return $stmt->execute([':cart_id' => $cartId]);
    }

    // ----------------------------------------------------------
    // Merge giỏ hàng guest → user khi đăng nhập
    // Gọi từ AuthController ngay sau khi login thành công
    // ----------------------------------------------------------
    public function mergeSessionToDb(int $userId, string $sessionId): void
    {
        // Tìm giỏ hàng guest theo session_id
        $stmt = $this->pdo->prepare(
            "SELECT * FROM carts
             WHERE session_id = :session_id AND user_id IS NULL
             LIMIT 1"
        );
        $stmt->execute([':session_id' => $sessionId]);
        $guestCart = $stmt->fetch();

        // Không có giỏ guest → không cần làm gì
        if (!$guestCart) {
            return;
        }

        // Tìm giỏ của user đã login (nếu có)
        // findBy() kế thừa từ BaseModel
        $userCart = $this->findBy('user_id', $userId);

        if (!$userCart) {
            // User chưa có giỏ → gán luôn giỏ guest cho user này
            $this->update($guestCart['id'], ['user_id' => $userId]);
            return;
        }

        // Cả 2 đều có giỏ → gộp cart_items từ guest vào user
        // ON DUPLICATE KEY dựa vào UNIQUE KEY (cart_id, product_id) trong DB
        // Nếu sản phẩm đã tồn tại trong giỏ user → cộng dồn quantity
        $stmt = $this->pdo->prepare(
            "INSERT INTO cart_items (cart_id, product_id, quantity, price)
             SELECT :user_cart_id, product_id, quantity, price
             FROM cart_items
             WHERE cart_id = :guest_cart_id
             ON DUPLICATE KEY UPDATE
                 quantity = quantity + VALUES(quantity)"
        );
        $stmt->execute([
            ':user_cart_id'  => $userCart['id'],
            ':guest_cart_id' => $guestCart['id'],
        ]);

        // Xoá giỏ guest sau khi đã merge xong
        // delete() kế thừa từ BaseModel
        $this->delete($guestCart['id']);
    }
}