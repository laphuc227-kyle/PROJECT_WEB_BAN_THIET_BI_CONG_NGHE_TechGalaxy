<?php
// File: config/constants.php
// Nơi định nghĩa các hằng số quy ước dùng chung cho toàn hệ thống

/**
 * 1. Hằng số Phân trang (Dành cho Task 04 và 06)
 */
define('PAGINATION_LIMIT_SHOP', 12); // Trang shop Nguyên cần 12 sp/trang
define('PAGINATION_LIMIT_BLOG', 6);  // Trang blog Quân cần 6 bài/trang

/**
 * 2. Hằng số Trạng thái Đơn hàng (Dành cho Task 05 của Sơn)
 */
define('ORDER_STATUS_PENDING', 'pending');       // Chờ xử lý
define('ORDER_STATUS_CONFIRMED', 'confirmed');   // Đã xác nhận
define('ORDER_STATUS_SHIPPING', 'shipping');     // Đang giao hàng
define('ORDER_STATUS_DELIVERED', 'delivered');   // Đã giao
define('ORDER_STATUS_COMPLETED', 'completed');   // Hoàn tất
define('ORDER_STATUS_CANCELLED', 'cancelled');   // Đã huỷ

/**
 * 3. Hằng số Trạng thái User (Dành cho Task 03 của Kim)
 */
define('USER_STATUS_ACTIVE', 1);
define('USER_STATUS_BLOCKED', 0);

/**
 * 4. Hằng số Vai trò (Role)
 */
define('ROLE_ADMIN', 'admin');
define('ROLE_USER', 'user');