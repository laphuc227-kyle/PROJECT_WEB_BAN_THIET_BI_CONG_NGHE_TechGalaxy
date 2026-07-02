<?php
// File: models/Address.php
declare(strict_types=1);
require_once __DIR__ . '/BaseModel.php';
class Address extends BaseModel {
    protected string $table = 'addresses';
    /**
     * Lấy danh sách địa chỉ của user
     */
    public function getByUser(int $userId): array {
        return $this->findAll(['user_id' => $userId]);
    }
    /**
     * Lấy địa chỉ mặc định của user
     */
    public function getDefault(int $userId): array|false {
        $results = $this->findAll(['user_id' => $userId, 'is_default' => 1]);
        return !empty($results) ? $results[0] : false;
    }
    /**
     * Thiết lập địa chỉ mặc định cho user (hủy mặc định của các địa chỉ khác)
     */
    public function setDefault(int $addressId, int $userId): bool {
        try {
            $this->pdo->beginTransaction();
            // 1. Tắt mặc định cho tất cả địa chỉ của user này
            $stmt = $this->pdo->prepare("UPDATE {$this->table} SET is_default = 0 WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $userId]);
            // 2. Kích hoạt mặc định cho địa chỉ được chọn
            $stmt2 = $this->pdo->prepare("UPDATE {$this->table} SET is_default = 1 WHERE id = :id AND user_id = :user_id");
            $stmt2->execute([':id' => $addressId, ':user_id' => $userId]);
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Lỗi đặt địa chỉ mặc định: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Tạo địa chỉ mới
     */
    public function create(array $data): int {
        return $this->insert($data);
    }
    /**
     * Cập nhật địa chỉ theo ID (chỉ cập nhật nếu thuộc về user đó)
     */
    public function updateAddress(int $id, int $userId, array $data): bool {
        $clauses = [];
        $params = [':id' => $id, ':user_id' => $userId];
        foreach ($data as $key => $value) {
            $clauses[] = "$key = :$key";
            $params[":$key"] = $value;
        }
        $setString = implode(', ', $clauses);
        $sql = "UPDATE {$this->table} SET $setString WHERE id = :id AND user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    /**
     * Xóa địa chỉ nếu đúng là của user đó
     */
    public function deleteAddress(int $id, int $userId): bool {
        $sql = "DELETE FROM {$this->table} WHERE id = :id AND user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }
}