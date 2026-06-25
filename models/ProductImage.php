<?php

namespace Models;

//use Config\Database;
use PDO;

/**
 * Model ProductImage
 * Quản lý bộ sưu tập ảnh của sản phẩm
 * Hỗ trợ upload nhiều ảnh, validate file, đặt ảnh chính
 */
class ProductImage
{
    private PDO $db;

    // Thư mục lưu ảnh (tương đối so với root dự án)
    private const UPLOAD_DIR = 'public/uploads/products/';

    // Các loại MIME được chấp nhận
    private const ALLOWED_MIME = ['image/jpeg', 'image/jpg', 'image/png'];

    // Kích thước file tối đa: 5MB
    private const MAX_SIZE = 5 * 1024 * 1024;

    public function __construct()
    {
        global $pdo;
        $this->db = $pdo;
    
    }

    /**
     * Upload và lưu nhiều ảnh cho sản phẩm
     *
     * @param array $files     $_FILES['images'] — mảng file upload (multiple)
     * @param int   $productId ID sản phẩm cần gán ảnh
     * @return array           ['success' => bool, 'uploaded' => int, 'errors' => [...]]
     */
    public function uploadImages(array $files, int $productId): array
    {
        // Kiểm tra thư mục upload tồn tại, nếu chưa thì tạo
        $uploadPath = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/') . '/' . self::UPLOAD_DIR;
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $uploaded = 0;
        $errors   = [];

        // Lấy xem sản phẩm này đã có ảnh nào chưa (để set ảnh primary)
        $existingCount = $this->countImagesByProduct($productId);

        // Chuẩn hóa mảng $_FILES khi upload multiple
        $fileList = $this->normalizeFilesArray($files);

        foreach ($fileList as $index => $file) {
            // Bỏ qua nếu không có file nào được chọn
            if ($file['error'] === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            // --- Validate lỗi upload ---
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors[] = "File #" . ($index + 1) . ": Lỗi upload (code {$file['error']})";
                continue;
            }

            // --- Validate kích thước ---
            if ($file['size'] > self::MAX_SIZE) {
                $errors[] = "File '{$file['name']}': Vượt quá dung lượng cho phép (5MB).";
                continue;
            }

            // --- Validate MIME type thực sự (không tin vào extension) ---
            $finfo    = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, self::ALLOWED_MIME, true)) {
                $errors[] = "File '{$file['name']}': Chỉ chấp nhận JPG, JPEG, PNG.";
                continue;
            }

            // --- Đặt tên file ngẫu nhiên để tránh trùng lặp ---
            $extension = match ($mimeType) {
                'image/jpeg', 'image/jpg' => 'jpg',
                'image/png'               => 'png',
                default                   => 'jpg',
            };
            $newFileName = uniqid('prod_' . $productId . '_', true) . '.' . $extension;
            $destination = $uploadPath . $newFileName;

            // --- Di chuyển file từ tmp vào thư mục upload ---
            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                $errors[] = "File '{$file['name']}': Không thể lưu file lên server.";
                continue;
            }

            // --- Lưu đường dẫn vào database ---
            // Ảnh đầu tiên của sản phẩm sẽ là ảnh primary
            $isPrimary = ($existingCount === 0 && $uploaded === 0) ? 1 : 0;
            $imagePath = self::UPLOAD_DIR . $newFileName;

            $stmt = $this->db->prepare("
                INSERT INTO product_images (product_id, image_path, is_primary, created_at)
                VALUES (:product_id, :image_path, :is_primary, NOW())
            ");
            $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
            $stmt->bindParam(':image_path', $imagePath, PDO::PARAM_STR);
            $stmt->bindParam(':is_primary', $isPrimary, PDO::PARAM_INT);
            $stmt->execute();

            $uploaded++;
        }

        return [
            'success'  => $uploaded > 0,
            'uploaded' => $uploaded,
            'errors'   => $errors,
        ];
    }

    /**
     * Lấy tất cả ảnh của một sản phẩm
     * Ảnh primary luôn đứng đầu
     */
    public function getImagesByProduct(int $productId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM product_images
            WHERE product_id = :product_id
            ORDER BY is_primary DESC, id ASC
        ");
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Xóa một ảnh theo ID (xóa cả file vật lý lẫn bản ghi DB)
     */
    public function deleteImage(int $imageId): array
    {
        // Lấy thông tin ảnh trước khi xóa
        $stmt = $this->db->prepare("SELECT * FROM product_images WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $imageId, PDO::PARAM_INT);
        $stmt->execute();
        $image = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$image) {
            return ['success' => false, 'message' => 'Ảnh không tồn tại.'];
        }

        // Xóa file vật lý
        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/' . $image['image_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Xóa bản ghi trong DB
        $delStmt = $this->db->prepare("DELETE FROM product_images WHERE id = :id");
        $delStmt->bindParam(':id', $imageId, PDO::PARAM_INT);
        $delStmt->execute();

        // Nếu ảnh bị xóa là ảnh primary → tự động set ảnh đầu tiên còn lại làm primary
        if ($image['is_primary']) {
            $this->autoSetPrimary((int) $image['product_id']);
        }

        return ['success' => true, 'message' => 'Xóa ảnh thành công.'];
    }

    /**
     * Đặt một ảnh làm ảnh primary (ảnh đại diện chính)
     */
    public function setPrimaryImage(int $imageId, int $productId): bool
    {
        // Bỏ primary tất cả ảnh cũ của sản phẩm
        $stmt = $this->db->prepare("
            UPDATE product_images SET is_primary = 0 WHERE product_id = :product_id
        ");
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();

        // Set primary cho ảnh mới
        $stmt = $this->db->prepare("
            UPDATE product_images SET is_primary = 1 WHERE id = :id
        ");
        $stmt->bindParam(':id', $imageId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Tự động chọn ảnh đầu tiên còn lại làm primary
     */
    private function autoSetPrimary(int $productId): void
    {
        $stmt = $this->db->prepare("
            SELECT id FROM product_images
            WHERE product_id = :product_id
            ORDER BY id ASC
            LIMIT 1
        ");
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();
        $first = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($first) {
            $update = $this->db->prepare("
                UPDATE product_images SET is_primary = 1 WHERE id = :id
            ");
            $update->bindParam(':id', $first['id'], PDO::PARAM_INT);
            $update->execute();
        }
    }

    /**
     * Đếm số ảnh hiện có của sản phẩm
     */
    private function countImagesByProduct(int $productId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM product_images WHERE product_id = :product_id
        ");
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /**
     * Chuẩn hóa mảng $_FILES khi upload nhiều file với name="images[]"
     * PHP trả về cấu trúc lồng nhau, cần chuyển về dạng phẳng để duyệt
     *
     * Input:  ['name' => ['a.jpg','b.png'], 'tmp_name' => ['/tmp/x','/tmp/y'], ...]
     * Output: [['name'=>'a.jpg','tmp_name'=>'/tmp/x',...], ['name'=>'b.png',...]]
     */
    private function normalizeFilesArray(array $files): array
    {
        $result = [];

        // Trường hợp upload 1 file duy nhất (không phải array)
        if (!is_array($files['name'])) {
            return [$files];
        }

        $count = count($files['name']);
        for ($i = 0; $i < $count; $i++) {
            $result[] = [
                'name'     => $files['name'][$i],
                'type'     => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i],
            ];
        }

        return $result;
    }
}