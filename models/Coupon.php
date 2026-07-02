<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

// ĐÃ THÊM LỚP BẢO VỆ CHỐNG TRÙNG LẶP CLASS
if (!class_exists('Coupon')) {

    class Coupon extends BaseModel
    {
        protected string $table = 'coupons';

        public function __construct()
        {
            parent::__construct();
        }

        /**
         * Tìm coupon theo code
         */
        public function findByCode(string $code): array|false
        {
            $stmt = $this->pdo->prepare("SELECT * FROM coupons WHERE code = ? LIMIT 1");
            $stmt->execute([strtoupper(trim($code))]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        /**
         * Validate coupon — trả về JSON-friendly array
         *
         * @param  string $code       Mã giảm giá
         * @param  float  $orderTotal Tổng đơn hàng (trước giảm giá)
         * @param  int    $userId     ID user (tương lai: kiểm tra đã dùng chưa)
         * @return array  ['valid' => bool, 'discount' => float, 'message' => string, 'coupon' => array|null]
         */
        public function validateCoupon(string $code, float $orderTotal, int $userId): array
        {
            // 1. Tìm coupon
            $coupon = $this->findByCode($code);
            if (!$coupon) {
                return ['valid' => false, 'discount' => 0, 'message' => 'Mã giảm giá không tồn tại.', 'coupon' => null];
            }

            // 2. Kiểm tra status = active (status là tinyint: 1 = active, 0 = vô hiệu)
            if ((int) $coupon['status'] !== 1) {
                return ['valid' => false, 'discount' => 0, 'message' => 'Mã giảm giá đã bị vô hiệu hoá.', 'coupon' => null];
            }

            // 3. Kiểm tra ngày còn hiệu lực
            $now   = new \DateTime();
            $start = new \DateTime($coupon['start_date']);
            $end   = new \DateTime($coupon['end_date']);

            if ($now < $start) {
                return ['valid' => false, 'discount' => 0, 'message' => 'Mã giảm giá chưa có hiệu lực.', 'coupon' => null];
            }
            if ($now > $end) {
                return ['valid' => false, 'discount' => 0, 'message' => 'Mã giảm giá đã hết hạn.', 'coupon' => null];
            }

            // 4. Kiểm tra còn lượt dùng
            if ((int) $coupon['used_count'] >= (int) $coupon['max_uses']) {
                return ['valid' => false, 'discount' => 0, 'message' => 'Mã giảm giá đã hết lượt sử dụng.', 'coupon' => null];
            }

            // 5. Kiểm tra min_order
            if ($orderTotal < (float) $coupon['min_order']) {
                $minFormatted = formatPrice((float) $coupon['min_order']);
                return [
                    'valid'   => false,
                    'discount' => 0,
                    'message' => "Đơn hàng tối thiểu {$minFormatted} để dùng mã này.",
                    'coupon'  => null,
                ];
            }

            // 6. Tính số tiền giảm
            $discount = 0.0;
            if ($coupon['type'] === 'percent') {
                $discount = round($orderTotal * ((float) $coupon['value'] / 100), 0);
            } elseif ($coupon['type'] === 'fixed') {
                $discount = min((float) $coupon['value'], $orderTotal); // không giảm hơn tổng đơn
            }

            return [
                'valid'    => true,
                'discount' => $discount,
                'message'  => 'Áp dụng mã giảm giá thành công! Bạn được giảm ' . formatPrice($discount),
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

} // KẾT THÚC LỚP BẢO VỆ