<?php
// File: models/Coupon.php
declare(strict_types=1);

class Coupon extends BaseModel
{
    protected string $table = 'coupons';

    /**
     * Tìm coupon theo code
     */
    public function findByCode(string $code): array|false
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM coupons WHERE code = ? LIMIT 1"
        );
        $stmt->execute([strtoupper(trim($code))]);
        return $stmt->fetch();
    }

    /**
     * Validate coupon
     * status trong DB là TINYINT: 1 = active, 0 = inactive
     */
    public function validateCoupon(
        string $code,
        float $orderTotal,
        int $userId
    ): array {
        $coupon = $this->findByCode($code);

        if (!$coupon) {
            return [
                'valid'    => false,
                'discount' => 0,
                'message'  => 'Mã giảm giá không tồn tại.',
                'coupon'   => null,
            ];
        }

        // FIX: schema dùng TINYINT(1), so sánh với 1 thay vì 'active'
        if ((int) $coupon['status'] !== 1) {
            return [
                'valid'    => false,
                'discount' => 0,
                'message'  => 'Mã giảm giá đã bị vô hiệu hoá.',
                'coupon'   => null,
            ];
        }

        // Kiểm tra ngày hiệu lực
        $now   = new \DateTime();
        $start = new \DateTime($coupon['start_date']);
        $end   = new \DateTime($coupon['end_date']);

        if ($now < $start) {
            return [
                'valid'    => false,
                'discount' => 0,
                'message'  => 'Mã giảm giá chưa có hiệu lực.',
                'coupon'   => null,
            ];
        }
        if ($now > $end) {
            return [
                'valid'    => false,
                'discount' => 0,
                'message'  => 'Mã giảm giá đã hết hạn.',
                'coupon'   => null,
            ];
        }

        // Kiểm tra lượt dùng (max_uses = 0 → không giới hạn)
        if (
            (int) $coupon['max_uses'] > 0 &&
            (int) $coupon['used_count'] >= (int) $coupon['max_uses']
        ) {
            return [
                'valid'    => false,
                'discount' => 0,
                'message'  => 'Mã giảm giá đã hết lượt sử dụng.',
                'coupon'   => null,
            ];
        }

        // Kiểm tra min_order
        if ($orderTotal < (float) $coupon['min_order']) {
            return [
                'valid'    => false,
                'discount' => 0,
                'message'  => 'Đơn hàng tối thiểu ' . formatPrice((float) $coupon['min_order']) . ' để dùng mã này.',
                'coupon'   => null,
            ];
        }

        // Tính discount
        $discount = 0.0;
        if ($coupon['type'] === 'percent') {
            $discount = round($orderTotal * ((float) $coupon['value'] / 100));
        } elseif ($coupon['type'] === 'fixed') {
            $discount = min((float) $coupon['value'], $orderTotal);
        }

        return [
            'valid'    => true,
            'discount' => $discount,
            'message'  => 'Áp dụng thành công! Giảm ' . formatPrice($discount),
            'coupon'   => $coupon,
        ];
    }

    /**
     * Tăng used_count sau khi đặt hàng thành công
     */
    public function incrementUsed(int $couponId): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE coupons SET used_count = used_count + 1 WHERE id = ?"
        );
        return $stmt->execute([$couponId]);
    }

    /**
     * Admin: lấy tất cả coupon có phân trang
     */
    public function adminGetAll(int $page = 1, int $perPage = 15): array
    {
        return $this->paginate($page, $perPage);
    }
}