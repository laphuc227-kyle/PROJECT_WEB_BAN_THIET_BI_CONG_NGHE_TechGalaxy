<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

// ĐÃ THÊM LỚP BẢO VỆ CHỐNG TRÙNG LẶP CLASS TỪ NHÁNH QUÂN
if (!class_exists('Coupon')) {

    class Coupon extends \BaseModel
    {
        protected string $table = 'coupons';

        public function __construct()
        {
            parent::__construct();
        }

        public function findByCode(string $code): array|false
        {
            $stmt = $this->pdo->prepare("SELECT * FROM coupons WHERE code = ? LIMIT 1");
            $stmt->execute([strtoupper(trim($code))]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        public function validateCoupon(string $code, float $orderTotal, int $userId): array
        {
            $coupon = $this->findByCode($code);
            if (!$coupon) {
                return ['valid' => false, 'discount' => 0, 'message' => 'Mã giảm giá không tồn tại.', 'coupon' => null];
            }

            if ((int) $coupon['status'] !== 1) {
                return ['valid' => false, 'discount' => 0, 'message' => 'Mã giảm giá đã bị vô hiệu hoá.', 'coupon' => null];
            }

            $now   = new \DateTime();
            $start = new \DateTime($coupon['start_date']);
            $end   = new \DateTime($coupon['end_date']);

            if ($now < $start) return ['valid' => false, 'discount' => 0, 'message' => 'Mã giảm giá chưa có hiệu lực.', 'coupon' => null];
            if ($now > $end)   return ['valid' => false, 'discount' => 0, 'message' => 'Mã giảm giá đã hết hạn.', 'coupon' => null];

            if ((int) $coupon['used_count'] >= (int) $coupon['max_uses']) {
                return ['valid' => false, 'discount' => 0, 'message' => 'Mã giảm giá đã hết lượt sử dụng.', 'coupon' => null];
            }

            if ($orderTotal < (float) $coupon['min_order']) {
                $minFormatted = formatPrice((float) $coupon['min_order']);
                return [
                    'valid'   => false, 'discount' => 0,
                    'message' => "Đơn hàng tối thiểu {$minFormatted} để dùng mã này.",
                    'coupon'  => null,
                ];
            }

            $discount = 0.0;
            if ($coupon['type'] === 'percent') {
                $discount = round($orderTotal * ((float) $coupon['value'] / 100), 0);
            } elseif ($coupon['type'] === 'fixed') {
                $discount = min((float) $coupon['value'], $orderTotal);
            }

            return [
                'valid'    => true,
                'discount' => $discount,
                'message'  => 'Áp dụng mã giảm giá thành công! Bạn được giảm ' . formatPrice($discount),
                'coupon'   => $coupon,
            ];
        }

        public function incrementUsed(int $couponId): bool
        {
            $stmt = $this->pdo->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?");
            return $stmt->execute([$couponId]);
        }

        public function adminGetAll(int $page = 1, int $perPage = 15): array
        {
            return $this->paginate($page, $perPage);
        }
    }

} // KẾT THÚC LỚP BẢO VỆ