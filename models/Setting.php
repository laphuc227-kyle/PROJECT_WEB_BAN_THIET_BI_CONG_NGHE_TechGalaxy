<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class Setting extends BaseModel
{
    protected string $table = 'settings';
    private static array $cache = [];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Lấy giá trị cài đặt theo key (có cache trong session)
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        // Thử lấy từ session cache
        if (isset($_SESSION['settings'][$key])) {
            self::$cache[$key] = $_SESSION['settings'][$key];
            return self::$cache[$key];
        }

        $stmt = $this->pdo->prepare("SELECT value FROM settings WHERE `key` = ? LIMIT 1");
        $stmt->execute([$key]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        $value = $row ? $row['value'] : $default;
        self::$cache[$key] = $value;

        return $value;
    }

    /**
     * Lưu hoặc cập nhật một cài đặt
     */
    public function set(string $key, mixed $value): bool
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO settings (`key`, `value`) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)"
        );
        $ok = $stmt->execute([$key, $value]);

        // Xoá cache
        unset(self::$cache[$key]);
        if (isset($_SESSION['settings'][$key])) {
            unset($_SESSION['settings'][$key]);
        }

        return $ok;
    }

    /**
     * Lưu nhiều cài đặt cùng lúc (dùng trong form settings)
     */
    public function setMany(array $data): bool
    {
        $this->pdo->beginTransaction();
        try {
            foreach ($data as $key => $value) {
                $this->set($key, $value);
            }
            $this->pdo->commit();
            // Làm mới cache session
            $_SESSION['settings'] = [];
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    /**
     * Lấy tất cả cài đặt dạng key => value
     */
    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT `key`, `value` FROM settings");
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $row) {
            $result[$row['key']] = $row['value'];
        }
        return $result;
    }
}