<?php
declare(strict_types=1);

class BaseModel {
    protected PDO $pdo;
    protected string $table;

    public function __construct() {
        // Lấy biến $pdo từ file config/database.php đã được require ở index
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Lấy nhiều bản ghi có điều kiện
     */
    public function findAll(array $conditions = [], string $order = '', ?int $limit = null): array {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $clauses = [];
            foreach ($conditions as $key => $value) {
                $clauses[] = "$key = :$key";
                $params[":$key"] = $value;
            }
            $sql .= " WHERE " . implode(" AND ", $clauses);
        }

        if ($order !== '') {
            $sql .= " ORDER BY $order";
        }

        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Tìm 1 bản ghi theo ID
     */
    public function findById(int $id): array|false {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Tìm 1 bản ghi theo 1 cột bất kỳ
     */
    public function findBy(string $column, mixed $value): array|false {
        // Cột không bind được qua PDO nên nối chuỗi, giá trị thì bind
        $sql = "SELECT * FROM {$this->table} WHERE $column = :value LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':value' => $value]);
        return $stmt->fetch();
    }

    /**
     * Thêm mới 1 bản ghi
     */
    public function insert(array $data): int {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $stmt = $this->pdo->prepare($sql);

        $params = [];
        foreach ($data as $key => $value) {
            $params[":$key"] = $value;
        }

        $stmt->execute($params);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Cập nhật bản ghi theo ID
     */
    public function update(int $id, array $data): bool {
        $clauses = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            $clauses[] = "$key = :$key";
            $params[":$key"] = $value;
        }

        $setString = implode(', ', $clauses);
        $sql = "UPDATE {$this->table} SET $setString WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Xóa bản ghi theo ID
     */
    public function delete(int $id): bool {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Đếm số lượng bản ghi
     */
    public function count(array $conditions = []): int {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $clauses = [];
            foreach ($conditions as $key => $value) {
                $clauses[] = "$key = :$key";
                $params[":$key"] = $value;
            }
            $sql .= " WHERE " . implode(" AND ", $clauses);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Phân trang dữ liệu
     */
    public function paginate(int $page, int $perPage, array $conditions = []): array {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $clauses = [];
            foreach ($conditions as $key => $value) {
                $clauses[] = "$key = :$key";
                $params[":$key"] = $value;
            }
            $sql .= " WHERE " . implode(" AND ", $clauses);
        }

        // Ép kiểu (int) để nối trực tiếp LIMIT và OFFSET tránh lỗi PDO khi emulate prepares = false
        $sql .= " LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}