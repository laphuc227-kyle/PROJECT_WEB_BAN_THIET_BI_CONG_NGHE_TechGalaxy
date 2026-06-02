<?php
// File: includes/auth.php

/**
 * Bắt buộc người dùng phải đăng nhập mới được đi tiếp.
 * Thường dùng cho trang thanh toán, giỏ hàng, xem đơn hàng...
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlash('error', 'Bạn cần đăng nhập để thực hiện chức năng này.');
        redirect('/login'); // Yêu cầu route /login phải tồn tại bên controller của Kim
    }
}

/**
 * Bắt buộc người dùng phải có quyền Admin.
 * Thường dùng chặn ở đầu mọi file trong thư mục controllers/admin/
 */
function requireAdmin() {
    requireLogin(); // Tránh lỗi chưa đăng nhập mà check role
    if (!isAdmin()) {
        setFlash('error', 'Bạn không có quyền truy cập khu vực quản trị.');
        redirect('/'); // Đẩy về trang chủ nếu cố tình vào admin
    }
}

/**
 * Chặn người dùng đã đăng nhập không cho vào lại trang Login/Register.
 */
function redirectIfAuthenticated() {
    if (isLoggedIn()) {
        redirect('/');
    }
}