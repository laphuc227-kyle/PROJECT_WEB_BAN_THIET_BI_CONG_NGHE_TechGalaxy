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

    public function get(string $key, mixed $default = null): mixed
    {
        if (isset(self::$cache[$key])) return self::$cache[$key];
        if (isset($_SESSION['settings'][$key])) {
            self::$cache[$key] = $_SESSION['settings'][$key];
            return self::$cache[$key];
        }

        $stmt = $this->pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        $value = $row ? $row['setting_value'] : $default;
        self::$cache[$key] = $value;
        return $value;
    }

    public function set(string $key, mixed $value): bool
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
        );
        $ok = $stmt->execute([$key, $value]);

        unset(self::$cache[$key]);
        if (isset($_SESSION['settings'][$key])) unset($_SESSION['settings'][$key]);
        return $ok;
    }

    public function setMany(array $data): bool
    {
        $this->pdo->beginTransaction();
        try {
            foreach ($data as $key => $value) $this->set($key, $value);
            $this->pdo->commit();
            $_SESSION['settings'] = [];
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT setting_key, setting_value FROM settings");
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $row) {
            $result[$row['setting_key']] = $row['setting_value'];
        }
        return $result;
    }
}