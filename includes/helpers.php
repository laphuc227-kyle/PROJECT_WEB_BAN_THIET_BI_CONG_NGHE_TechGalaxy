<?php
// File: includes/helpers.php

/**
 * 1. Chuyển hướng trang
 */
function redirect($url) {
    // Đảm bảo BASE_URL đã được define trong config/app.php
    header("Location: " . BASE_URL . $url);
    exit();
}

/**
 * 2. Cài đặt Flash Message (thông báo dùng 1 lần)
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // VD: 'success', 'error', 'warning'
        'message' => $message
    ];
}

/**
 * 3. Lấy Flash Message và xóa ngay sau khi lấy
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * 4. Làm sạch dữ liệu đầu vào (Chống XSS)
 */
function sanitize($input) {
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = sanitize($value);
        }
        return $input;
    }
    return htmlspecialchars(trim($input ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * 5. Kiểm tra trạng thái đăng nhập
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * 6. Kiểm tra quyền Admin
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * 7. Định dạng tiền tệ VNĐ
 */
function formatPrice($price) {
    return number_format((float)$price, 0, ',', '.') . ' ₫';
}

/**
 * 8. Tạo CSRF Token (Chống giả mạo request)
 */
function generateToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * 9. Hiển thị thời gian trôi qua (VD: "3 phút trước")
 */
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;

    if ($diff < 60) return "Vừa xong";
    if ($diff < 3600) return floor($diff / 60) . " phút trước";
    if ($diff < 86400) return floor($diff / 3600) . " giờ trước";
    if ($diff < 2592000) return floor($diff / 86400) . " ngày trước";
    if ($diff < 31536000) return floor($diff / 2592000) . " tháng trước";
    return floor($diff / 31536000) . " năm trước";
}

/**
 * 10. Tạo chuỗi URL thân thiện (Slugify)
 */
function slugify($string) {
    $search = array(
        '#(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)#',
        '#(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)#',
        '#(ì|í|ị|ỉ|ĩ)#',
        '#(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)#',
        '#(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)#',
        '#(ỳ|ý|ỵ|ỷ|ỹ)#',
        '#(đ)#',
        '#(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)#',
        '#(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)#',
        '#(Ì|Í|Ị|Ỉ|Ĩ)#',
        '#(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)#',
        '#(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)#',
        '#(Ỳ|Ý|Ỵ|Ỷ|Ỹ)#',
        '#(Đ)#',
        '/[^a-zA-Z0-9\-\_]/',
    );
    $replace = array(
        'a', 'e', 'i', 'o', 'u', 'y', 'd',
        'A', 'E', 'I', 'O', 'U', 'Y', 'D',
        '-',
    );
    $string = preg_replace($search, $replace, $string);
    $string = preg_replace('/(-)+/', '-', $string);
    $string = strtolower($string);
    return trim($string, '-');
}
if (!function_exists('formatHeroTitle')) {
    /**
     * Định dạng tiêu đề Hero Banner, tự động bọc thẻ span cho từ khóa highlight
     */
    function formatHeroTitle(string $title, string $highlight): string 
    {
        $escapedTitle = htmlspecialchars($title);
        $escapedHighlight = htmlspecialchars($highlight);
        
        if (!empty($escapedHighlight)) {
            $replacement = '<span class="text-highlight-tg">' . $escapedHighlight . '</span>';
            return nl2br(str_replace($escapedHighlight, $replacement, $escapedTitle));
        }
        
        return nl2br($escapedTitle);
    }
}