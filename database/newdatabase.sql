-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th7 02, 2026 lúc 06:24 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `techgalaxy`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `addresses`
--

CREATE TABLE `addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `province` varchar(100) NOT NULL,
  `district` varchar(100) NOT NULL,
  `ward` varchar(100) NOT NULL,
  `detail` varchar(255) NOT NULL,
  `is_default` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `name`, `phone`, `province`, `district`, `ward`, `detail`, `is_default`) VALUES
(1, 2, 'Nhà riêng', '0911111111', 'Hồ Chí Minh', 'Quận 1', 'Phường Bến Nghé', '123 Lê Lợi', 1),
(2, 2, 'Công ty', '0911111111', 'Hồ Chí Minh', 'Quận 3', 'Phường Võ Thị Sáu', '456 Điện Biên Phủ', 0),
(3, 3, 'Nhà Kim', '0922222222', 'Hà Nội', 'Quận Cầu Giấy', 'Phường Dịch Vọng', '789 Xuân Thủy', 1),
(4, 9, 'hoang phuc', '13142536447', '', '', '', 'xo viet nghe tinh', 0),
(5, 9, 'hoang phuc', '13142536447', '', '', '', 'da lat', 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `carts`
--

CREATE TABLE `carts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'NULL nếu là guest',
  `session_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `session_id`, `created_at`) VALUES
(1, 4, 'sess_abc123', '2026-06-24 13:59:21'),
(2, 8, 'hq9122j8oo9kghavrqdin7ngm3', '2026-07-01 17:47:38'),
(3, 9, 'hq9122j8oo9kghavrqdin7ngm3', '2026-07-02 14:40:25');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `price` int(11) NOT NULL COMMENT 'Giá tại thời điểm thêm'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `cart_items`
--

INSERT INTO `cart_items` (`id`, `cart_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 8, 2, 350000),
(6, 2, 11, 2, 31990000),
(7, 2, 12, 3, 39990000),
(8, 2, 13, 2, 5990000),
(9, 2, 15, 2, 32990000);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `parent_id`, `status`, `sort_order`) VALUES
(1, 'Điện thoại', 'dien-thoai', 'Smartphone các dòng', NULL, NULL, 1, 1),
(2, 'Laptop', 'laptop', 'Máy tính xách tay', NULL, NULL, 1, 2),
(3, 'Phụ kiện', 'phu-kien', 'Tai nghe, cáp, sạc, ốp lưng', NULL, NULL, 1, 3),
(4, 'Đồng hồ thông minh', 'smartwatch', 'Apple Watch, Galaxy Watch', NULL, NULL, 1, 4),
(5, 'Apple', 'apple', 'Sản phẩm iOS', NULL, 1, 1, 1),
(6, 'Samsung', 'samsung', 'Sản phẩm Android', NULL, 1, 1, 2),
(7, 'Xiaomi', 'xiaomi', 'Ngon bổ rẻ', NULL, 1, 1, 3),
(8, 'MacBook', 'macbook', 'Apple Laptop', NULL, 2, 1, 1),
(9, 'Asus', 'asus', 'Laptop Gaming & Văn phòng', NULL, 2, 1, 2),
(10, 'Tai nghe', 'tai-nghe', 'Tai nghe Bluetooth & Có dây', NULL, 3, 1, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `user_id`, `content`, `status`, `created_at`) VALUES
(1, 1, 2, 'Bài viết rất chi tiết, cảm ơn admin!', 1, '2026-06-24 13:59:22'),
(2, 1, 3, 'Mình đang xài bản màu Titan tự nhiên, bao đẹp.', 1, '2026-06-24 13:59:22'),
(3, 2, 4, 'Admin tư vấn giúp em tầm giá 15 triệu nên mua máy gì ạ?', 1, '2026-06-24 13:59:22'),
(4, 13, 1, 'akdsad', 0, '2026-06-30 06:55:52'),
(5, 3, 1, 'ngu vãi', 0, '2026-06-30 07:05:10');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `type` enum('percent','fixed') NOT NULL,
  `value` int(11) NOT NULL,
  `min_order` int(11) DEFAULT 0,
  `max_uses` int(11) DEFAULT 0 COMMENT '0 là không giới hạn',
  `used_count` int(11) DEFAULT 0,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `type`, `value`, `min_order`, `max_uses`, `used_count`, `start_date`, `end_date`, `status`) VALUES
(1, 'WELCOME100', 'fixed', 100000, 1000000, 100, 5, '2024-01-01 00:00:00', '2026-12-31 00:00:00', 1),
(2, 'SALE20', 'percent', 20, 5000000, 50, 1, '2024-05-01 00:00:00', '2026-12-31 00:00:00', 1),
(3, 'EXPIRED50', 'fixed', 50000, 0, 10, 10, '2023-01-01 00:00:00', '2023-12-31 00:00:00', 1),
(4, 'SSL', 'percent', 10, 0, 100, 0, '2026-06-25 00:00:00', '2026-07-03 00:00:00', 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address_id` int(11) DEFAULT NULL,
  `status` enum('pending','confirmed','shipping','delivered','completed','cancelled') DEFAULT 'pending',
  `total` int(11) NOT NULL,
  `payment_method` enum('cod','banking') DEFAULT 'cod',
  `coupon_id` int(11) DEFAULT NULL,
  `discount` int(11) DEFAULT 0,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `address_id`, `status`, `total`, `payment_method`, `coupon_id`, `discount`, `note`, `created_at`) VALUES
(1, 2, 1, 'completed', 32890000, 'banking', 1, 100000, 'Giao giờ hành chính', '2024-05-15 03:00:00'),
(2, 3, 3, 'pending', 27990000, 'cod', NULL, 0, 'Gọi trước khi giao', '2024-05-31 02:00:00'),
(3, 2, 2, 'cancelled', 5890000, 'cod', NULL, 0, 'Hủy do đổi ý', '2024-05-20 08:30:00'),
(4, 9, 4, 'pending', 71980000, 'cod', NULL, 0, 'giao nhanh len', '2026-07-02 14:41:18'),
(5, 9, 5, 'pending', 64980000, 'banking', NULL, 0, 'giao nhanh con me m len', '2026-07-02 15:42:45');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `order_details`
--

INSERT INTO `order_details` (`id`, `order_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES
(1, 1, 1, 1, 32990000, 32990000),
(2, 2, 3, 1, 27990000, 27990000),
(3, 3, 5, 1, 5890000, 5890000),
(4, 4, 11, 1, 31990000, 31990000),
(5, 4, 12, 1, 39990000, 39990000),
(6, 5, 1, 1, 32990000, 32990000),
(7, 5, 2, 1, 31990000, 31990000);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `posts`
--

INSERT INTO `posts` (`id`, `title`, `slug`, `content`, `image`, `status`, `author_id`, `created_at`) VALUES
(1, 'Đánh giá chi tiết iPhone 15 Pro Max', 'danh-gia-chi-tiet-iphone-15-pro-max', '<p>Sau 1 tháng sử dụng, đây là những cảm nhận chân thực nhất...</p>', 'blog-ip15.jpg', 'published', 1, '2024-05-10 01:00:00'),
(2, 'Top 5 Laptop đáng mua nhất 2024 dành cho sinh viên', 'top-5-laptop-dang-mua-2024', '<p>Nếu bạn là sinh viên và đang phân vân chọn mua máy tính...</p>', 'blog-laptop.jpg', 'published', 1, '2024-05-25 02:30:00'),
(3, 'Sự kiện Apple WWDC sắp tới có gì hot?', 'su-kien-apple-wwdc-co-gi-hot', '<p>Bản tin tổng hợp tin đồn về iOS 18 v&agrave; macOS mới...</p>', 'blog-wwdc.jpg', 'published', 1, '2024-05-30 07:15:00'),
(13, 'iphone 17 promax sale sập sàn', 'iphone-17-promax-sale-sap-san', '<p>Chỉ với 5 triệu đồng th&igrave; bạn đ&atilde; sở hữu ngay cho m&igrave;nh chiếc điện thoại n&agrave;y</p>', '', 'published', 1, '2026-06-30 04:18:18');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `price` int(11) NOT NULL,
  `sale_price` int(11) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `sku` varchar(50) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `price`, `sale_price`, `stock`, `sku`, `status`, `is_featured`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 5, 'iPhone 15 Pro Max 256GB', 'iphone-15-pro-max-256gb', 'Siêu phẩm công nghệ 2023 với khung Titanium.', 34990000, 32990000, 49, 'IP15PM-256', 0, 1, '2026-06-24 13:59:21', NULL, NULL),
(2, 6, 'Samsung Galaxy S24 Ultra', 'samsung-galaxy-s24-ultra', '<p>Quyền năng AI trong tầm tay.</p>', 33990000, 31990000, 29, 'SGS24U-256', 1, 1, '2026-06-24 13:59:21', NULL, NULL),
(3, 8, 'MacBook Air M3 2024', 'macbook-air-m3-2024', '<p>Chiếc laptop quốc dân thế hệ mới.</p>', 27990000, NULL, 15, 'MBA-M3', 1, 1, '2026-06-24 13:59:21', NULL, NULL),
(4, 7, 'Xiaomi 14 5G', 'xiaomi-14-5g', '<p>Camera Leica đỉnh cao.</p>', 22990000, 19990000, 30, 'MI14-5G', 0, 0, '2026-06-24 13:59:21', NULL, NULL),
(5, 10, 'AirPods Pro Gen 2', 'airpods-pro-gen-2', '<p>Chống ồn chủ động xuất sắc.</p>', 6990000, 5890000, 200, 'APP-G2', 1, 1, '2026-06-24 13:59:21', NULL, NULL),
(6, 9, 'Asus ROG Strix G15', 'asus-rog-strix-g15', '<p>Laptop gaming hiệu năng khủng.</p>', 29990000, 28500000, 5, 'ROG-G15', 1, 1, '2026-06-24 13:59:21', NULL, NULL),
(7, 4, 'Apple Watch Series 9', 'apple-watch-series-9', '<p>Double tap thông minh.</p>', 10490000, 9990000, 0, 'AWS9', 1, 0, '2026-06-24 13:59:21', NULL, NULL),
(8, 3, 'Cáp sạc Anker 20W', 'cap-sac-anker-20w', '<p>Bền bỉ, sạc nhanh.</p>', 450000, 350000, 500, 'ANK-20W', 1, 0, '2026-06-24 13:59:21', NULL, NULL),
(9, 1, 'Iphone 17 promax', 'iphone-17-promax', '', 35000000, 0, 18, NULL, 0, 0, '2026-06-30 06:25:12', NULL, NULL),
(11, 6, 'Samsung Galaxy S24 Ultra', 'samsung-galaxy-s24-ultra-1', 'Sản phẩm siêu hot mới về.', 31990000, 0, 49, 'SS-S24U', 0, 0, '2026-07-01 15:59:53', NULL, NULL),
(12, 9, 'Dell XPS 15 9530', 'dell-xps-15', '<p>Sản phẩm siêu hot mới về.</p>', 42990000, 39990000, 19, 'DELL-XPS15', 1, 0, '2026-07-01 15:59:53', NULL, NULL),
(13, 10, 'AirPods Pro 2 USB-C', 'airpods-pro-2', '<p>Sản phẩm siêu hot mới về.</p>', 5990000, NULL, 100, 'APP2-USBC', 1, 0, '2026-07-01 15:59:53', NULL, NULL),
(14, 4, 'Xiaomi 14 Ultra', 'xiaomi-14-ultra', '<p>Sản phẩm siêu hot mới về.</p>', 26990000, 24990000, 30, 'MI-14U', 1, 0, '2026-07-01 15:59:53', NULL, NULL),
(15, 9, 'LG Gram 16 2024', 'lg-gram-16', '<p>Sản phẩm siêu hot mới về.</p>', 32990000, NULL, 15, 'LG-GRAM16', 1, 0, '2026-07-01 15:59:53', NULL, NULL),
(16, 5, 'Apple Watch Series 9', 'apple-watch-9', '<p>Sản phẩm siêu hot mới về.</p>', 10990000, 9990000, 40, 'AW-S9', 1, 0, '2026-07-01 15:59:53', NULL, NULL),
(17, 3, 'Keychron Q1 Pro', 'keychron-q1-pro', '<p>Sản phẩm siêu hot mới về.</p>', 4590000, NULL, 60, 'KC-Q1P', 1, 0, '2026-07-01 15:59:53', NULL, NULL),
(18, 6, 'OnePlus 12', 'oneplus-12', '<p>Sản phẩm siêu hot mới về.</p>', 18990000, 16990000, 25, 'OP-12', 1, 0, '2026-07-01 15:59:53', NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `is_primary`) VALUES
(1, 1, 'iphone-15-pm-titan.webp', 1),
(2, 1, 'iphone-15-pm-side.webp', 0),
(3, 2, 's24-ultra-grey.webp', 1),
(4, 2, 's24-ultra-pen.webp', 0),
(5, 3, 'macbook-air-m3-silver.webp', 1),
(6, 4, 'xiaomi-14-black.webp', 1),
(7, 5, 'airpods-pro-2.webp', 1),
(8, 6, 'asus-rog-g15.webp', 1),
(9, 7, 'apple-watch-9.webp', 1),
(10, 8, 'anker-cable.webp', 1),
(11, 11, 'assets/images/products/product-1.jpg', 1),
(12, 12, 'assets/images/products/product-2.jpg', 1),
(13, 13, 'assets/images/products/product-3.jpg', 1),
(14, 14, 'assets/images/products/product-4.jpg', 1),
(15, 15, 'assets/images/products/product-5.jpg', 1),
(16, 16, 'assets/images/products/product-6.jpg', 1),
(17, 17, 'assets/images/products/product-7.jpg', 1),
(18, 18, 'assets/images/products/product-8.jpg', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `description`) VALUES
(1, 'site_name', 'TechGalaxy', 'Tên website'),
(2, 'contact_email', 'support@techgalaxy.com', 'Email liên hệ'),
(3, 'contact_phone', '09090123233', 'Hotline hỗ trợ'),
(4, 'address', '123 Đường Công Nghệ, Quận 1, TP.HCM', 'Địa chỉ cửa hàng'),
(5, 'shipping_fee', '30000', 'Phí vận chuyển mặc định'),
(6, 'free_ship_threshold', '32000', 'Mức giá đơn hàng để được freeship'),
(11, 'store_description', '', NULL),
(13, 'free_shipping_from', '500000', NULL),
(14, 'send_order_email', '1', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `status` tinyint(1) DEFAULT 1 COMMENT '1: Active, 0: Blocked',
  `otp_code` varchar(6) DEFAULT NULL,
  `otp_expire` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `avatar`, `role`, `status`, `otp_code`, `otp_expire`, `created_at`) VALUES
(1, 'Admin Phúc', 'admin@techgalaxy.com', '$2y$12$uOBu9FIOROeBTpQKQ1.FT.oNck3WVlKmzMPxp9JxKACg4CWWsLkkC', '0900000001', 'avatar-admin.jpg', 'admin', 1, NULL, NULL, '2026-06-24 13:59:21'),
(2, 'Trần Minh Thuận', 'thuan.tran@gmail.com', '$2y$12$ZQq89K9M9.6Z7ZpZt01V0eK.3xV98Iu/a6v4q514Jc7V.48A73OZm', '0911111111', 'avatar-thuan.jpg', 'user', 1, NULL, NULL, '2026-06-24 13:59:21'),
(3, 'Lê Hoàng Kim', 'kim.le@gmail.com', '$2y$12$ZQq89K9M9.6Z7ZpZt01V0eK.3xV98Iu/a6v4q514Jc7V.48A73OZm', '0922222222', NULL, 'user', 1, NULL, NULL, '2026-06-24 13:59:21'),
(4, 'Phạm Nguyên', 'nguyen.pham@gmail.com', '$2y$12$ZQq89K9M9.6Z7ZpZt01V0eK.3xV98Iu/a6v4q514Jc7V.48A73OZm', '0933333333', NULL, 'user', 1, NULL, NULL, '2026-06-24 13:59:21'),
(5, 'User Bị Khóa', 'blocked@gmail.com', '$2y$12$ZQq89K9M9.6Z7ZpZt01V0eK.3xV98Iu/a6v4q514Jc7V.48A73OZm', '0944444444', NULL, 'user', 0, NULL, NULL, '2026-06-24 13:59:21'),
(6, 'Bùi Minh Quân', 'quanbui30032006@gmail.com', '$2y$12$5pRuGovRQmr6xM8unxNf9.ZvIC71cmQSQ/uSH3l6d1zInh59zRgAS', '0783797834', 'default.png', 'user', 1, NULL, NULL, '2026-07-01 03:31:04'),
(7, 'Minh Quan', 'test@gmail.com', '$2y$12$uOBu9FIOROeBTpQKQ1.FT.oNck3WVlKmzMPxp9JxKACg4CWWsLkkC', '0783797834', 'default.png', 'user', 1, NULL, NULL, '2026-07-01 12:53:59'),
(8, 'Hoàng Phúc', 'laphuc227@gmail.com', '$2y$12$Vdgbg31Hq1cZWn7BBhsWWulMyUWwk8rbJfhX0Qn5qpOPAFgJYaEM6', '0846915916', 'default.png', 'user', 1, NULL, NULL, '2026-07-01 17:48:42'),
(9, 'ielts 9.0', 'phuclnh7767@ut.edu.vn', '$2y$12$UZx7sGPeliBI5wQguJ8ANOqQYHhu28S2Vat006UnjIP2RjiG8uGqu', '0349583465', 'default.png', 'user', 1, NULL, NULL, '2026-07-02 06:43:59');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `wishlists`
--

CREATE TABLE `wishlists` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `wishlists`
--

INSERT INTO `wishlists` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(1, 2, 1, '2026-06-24 13:59:21'),
(2, 2, 5, '2026-06-24 13:59:21'),
(3, 3, 3, '2026-06-24 13:59:21'),
(8, 6, 4, '2026-07-01 06:52:14'),
(11, 6, 9, '2026-07-01 12:38:38'),
(18, 1, 9, '2026-07-01 16:37:38'),
(19, 1, 4, '2026-07-01 16:37:45'),
(20, 8, 11, '2026-07-01 18:59:40'),
(21, 9, 11, '2026-07-02 14:40:28'),
(22, 9, 12, '2026-07-02 14:40:29'),
(23, 9, 13, '2026-07-02 14:40:31'),
(24, 1, 12, '2026-07-02 15:48:34'),
(25, 1, 11, '2026-07-02 15:48:36'),
(26, 1, 13, '2026-07-02 15:48:37'),
(27, 1, 14, '2026-07-02 15:48:39'),
(28, 1, 15, '2026-07-02 15:48:39'),
(29, 1, 16, '2026-07-02 15:48:40');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_cart_session` (`session_id`);

--
-- Chỉ mục cho bảng `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Chỉ mục cho bảng `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `address_id` (`address_id`),
  ADD KEY `coupon_id` (`coupon_id`);

--
-- Chỉ mục cho bảng `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `author_id` (`author_id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_product_status` (`status`),
  ADD KEY `idx_product_featured` (`is_featured`);

--
-- Chỉ mục cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Chỉ mục cho bảng `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT cho bảng `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT cho bảng `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Các ràng buộc cho bảng `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlists_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;