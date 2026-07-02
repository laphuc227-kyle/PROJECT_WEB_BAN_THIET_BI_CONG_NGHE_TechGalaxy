<?php
// File: models/Cart.php
declare(strict_types=1);

// ĐÃ THÊM LỚP BẢO VỆ CHỐNG TRÙNG LẶP CLASS TỪ NHÁNH CỦA QUÂN
if (!class_exists('Cart')) {

    class Cart extends \BaseModel
    {
        protected string $table = 'carts';

        public function getOrCreateCart(?int $userId, string $sessionId): array
        {
            if ($userId !== null) {
                $stmt = $this->pdo->prepare(
                    "SELECT * FROM carts WHERE user_id = :user_id LIMIT 1"
                );
                $stmt->execute([':user_id' => $userId]);
            } else {
                $stmt = $this->pdo->prepare(
                    "SELECT * FROM carts WHERE session_id = :session_id AND user_id IS NULL LIMIT 1"
                );
                $stmt->execute([':session_id' => $sessionId]);
            }

            $cart = $stmt->fetch();
            if ($cart) return $cart;

            $newId = $this->insert([
                'user_id'    => $userId,
                'session_id' => $sessionId,
            ]);

            return $this->findById($newId);
        }

        public function countItems(int $cartId): int
        {
            $stmt = $this->pdo->prepare(
                "SELECT COALESCE(SUM(quantity), 0) FROM cart_items WHERE cart_id = :cart_id"
            );
            $stmt->execute([':cart_id' => $cartId]);
            return (int) $stmt->fetchColumn();
        }

        public function getCartSubtotal(int $cartId): float 
        {
            $stmt = $this->pdo->prepare(
                "SELECT COALESCE(SUM(quantity * price), 0) FROM cart_items WHERE cart_id = :cart_id"
            );
            $stmt->execute([':cart_id' => $cartId]);
            return (float)$stmt->fetchColumn();
        }

        public function clearCart(int $cartId): bool
        {
            $stmt = $this->pdo->prepare("DELETE FROM cart_items WHERE cart_id = :cart_id");
            return $stmt->execute([':cart_id' => $cartId]);
        }

        public function mergeSessionToDb(int $userId, string $sessionId): void
        {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM carts WHERE session_id = :session_id AND user_id IS NULL LIMIT 1"
            );
            $stmt->execute([':session_id' => $sessionId]);
            $guestCart = $stmt->fetch();

            if (!$guestCart) return;

            $userCart = $this->findBy('user_id', $userId);

            if (!$userCart) {
                $this->update($guestCart['id'], ['user_id' => $userId]);
                return;
            }

            $stmt = $this->pdo->prepare(
                "INSERT INTO cart_items (cart_id, product_id, quantity, price)
                 SELECT :user_cart_id, ci.product_id, ci.quantity, ci.price
                 FROM cart_items ci
                 WHERE ci.cart_id = :guest_cart_id
                 ON DUPLICATE KEY UPDATE
                     cart_items.quantity = cart_items.quantity + VALUES(quantity)"
            );
            $stmt->execute([
                ':user_cart_id'  => $userCart['id'],
                ':guest_cart_id' => $guestCart['id'],
            ]);

            $this->delete($guestCart['id']);
        }
    }
} // KẾT THÚC LỚP BẢO VỆ