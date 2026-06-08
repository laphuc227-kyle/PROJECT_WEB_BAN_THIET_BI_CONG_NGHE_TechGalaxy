<?php
// File: models/CartItem.php
declare(strict_types=1);

class CartItem extends BaseModel
{
    protected string $table = 'cart_items';

    // ----------------------------------------------------------
    // Lấy toàn bộ sản phẩm trong giỏ, kèm thông tin sản phẩm và ảnh
    // JOIN products  : lấy tên, slug, sku, stock
    // LEFT JOIN product_images : lấy ảnh đại diện (is_primary = 1)
    //   → LEFT JOIN vì sản phẩm có thể chưa có ảnh
    // ----------------------------------------------------------
    public function getByCartId(int $cartId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                ci.id,
                ci.cart_id,
                ci.product_id,
                ci.quantity,
                ci.price,

                p.name,
                p.slug,
                p.sku,
                p.stock,
                p.price AS product_price,
                p.sale_price,

                pi.image_path
             FROM cart_items ci
             JOIN products p
                 ON ci.product_id = p.id
             LEFT JOIN product_images pi
                 ON pi.product_id = p.id
                 AND pi.is_primary = 1
             WHERE ci.cart_id = :cart_id"
        );
        $stmt->execute([':cart_id' => $cartId]);
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------
    // Tìm 1 item cụ thể trong giỏ
    // Dùng để kiểm tra sản phẩm đã có trong giỏ chưa,
    // hoặc lấy quantity hiện tại trước khi cộng thêm
    // ----------------------------------------------------------
    public function findItem(int $cartId, int $productId): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM cart_items
             WHERE cart_id = :cart_id AND product_id = :product_id
             LIMIT 1"
        );
        $stmt->execute([
            ':cart_id'    => $cartId,
            ':product_id' => $productId,
        ]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findByCartAndProduct(
        int $cartId,
        int $productId
    ): array|false {

        $stmt = $this->pdo->prepare(
            "SELECT *
             FROM cart_items
             WHERE cart_id = :cart_id
             AND product_id = :product_id
             LIMIT 1"
        );

        $stmt->execute([
            ':cart_id'    => $cartId,
            ':product_id' => $productId
        ]);

        return $stmt->fetch();
    }

    // ----------------------------------------------------------
    // Thêm sản phẩm vào giỏ
    // ----------------------------------------------------------
    public function addItem(
        int $cartId,
        int $productId,
        int $quantity,
        int $price      // đổi float → int cho đúng schema
    ): bool {
    $item = $this->findByCartAndProduct($cartId, $productId);

    if ($item) {
        // Đã có → cộng dồn quantity
        return $this->update(
            (int)$item['id'],
            ['quantity' => (int)$item['quantity'] + $quantity]
        );
    }

    // Chưa có → insert mới
    $newId = $this->insert([
        'cart_id'    => $cartId,
        'product_id' => $productId,
        'quantity'   => $quantity,
        'price'      => $price,
    ]);
    return $newId > 0;
}


    // ----------------------------------------------------------
    // Cập nhật số lượng sản phẩm trong giỏ
    // Nếu quantity <= 0 → xoá luôn dòng đó thay vì để quantity = 0
    // ----------------------------------------------------------
    public function updateQuantity(
        int $cartId,
        int $productId,
        int $quantity
    ): bool {

        $item = $this->findItem(
            $cartId,
            $productId
        );

        if (!$item) {
            return false;
        }

        if ($quantity <= 0) {
            return $this->delete((int)$item['id']);
        }

        return $this->update(
            (int)$item['id'],
            [
                'quantity' => $quantity
            ]
        );
    }

    // ----------------------------------------------------------
    // Xoá 1 sản phẩm khỏi giỏ theo product_id
    // ----------------------------------------------------------
    public function removeItem(
        int $cartId,
        int $productId
    ): bool {

        $item = $this->findItem(
            $cartId,
            $productId
        );

        if (!$item) {
            return false;
        }

        return $this->delete((int)$item['id']);
    }


}