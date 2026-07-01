<?php
// File: models/User.php
declare(strict_types=1);

namespace Models;

require_once __DIR__ . '/BaseModel.php';
class User extends BaseModel {
    protected string $table = 'users';
    /**
     * Tìm người dùng bằng Email
     */
    public function getByEmail(string $email): array|false {
        return $this->findBy('email', $email);
    }
    /**
     * Kiểm tra xem Email đã tồn tại trong hệ thống chưa
     */
    public function emailExists(string $email): bool {
        return $this->getByEmail($email) !== false;
    }
}
