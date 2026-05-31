-- Đặt charset mặc định
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Làm sạch dữ liệu cũ (nếu chạy lại file seed nhiều lần)
TRUNCATE TABLE settings;
TRUNCATE TABLE comments;
TRUNCATE TABLE posts;
TRUNCATE TABLE order_details;
TRUNCATE TABLE orders;
TRUNCATE TABLE coupons;
TRUNCATE TABLE cart_items;
TRUNCATE TABLE carts;
TRUNCATE TABLE wishlists;
TRUNCATE TABLE product_images;
TRUNCATE TABLE products;
TRUNCATE TABLE categories;
TRUNCATE TABLE addresses;
TRUNCATE TABLE users;

-- ==========================================================
-- 1. TÀI KHOẢN & NGƯỜI DÙNG
-- ==========================================================
INSERT INTO users (id, name, email, password, phone, avatar, role, status) VALUES
(1, 'Admin Phúc', 'admin@techgalaxy.com', '$2y$12$ZQq89K9M9.6Z7ZpZt01V0eK.3xV98Iu/a6v4q514Jc7V.48A73OZm', '0900000001', 'avatar-admin.jpg', 'admin', 1),
(2, 'Trần Minh Thuận', 'thuan.tran@gmail.com', '$2y$12$ZQq89K9M9.6Z7ZpZt01V0eK.3xV98Iu/a6v4q514Jc7V.48A73OZm', '0911111111', 'avatar-thuan.jpg', 'user', 1),
(3, 'Lê Hoàng Kim', 'kim.le@gmail.com', '$2y$12$ZQq89K9M9.6Z7ZpZt01V0eK.3xV98Iu/a6v4q514Jc7V.48A73OZm', '0922222222', NULL, 'user', 1),
(4, 'Phạm Nguyên', 'nguyen.pham@gmail.com', '$2y$12$ZQq89K9M9.6Z7ZpZt01V0eK.3xV98Iu/a6v4q514Jc7V.48A73OZm', '0933333333', NULL, 'user', 1),
(5, 'User Bị Khóa', 'blocked@gmail.com', '$2y$12$ZQq89K9M9.6Z7ZpZt01V0eK.3xV98Iu/a6v4q514Jc7V.48A73OZm', '0944444444', NULL, 'user', 0);

INSERT INTO addresses (id, user_id, name, phone, province, district, ward, detail, is_default) VALUES
(1, 2, 'Nhà riêng', '0911111111', 'Hồ Chí Minh', 'Quận 1', 'Phường Bến Nghé', '123 Lê Lợi', 1),
(2, 2, 'Công ty', '0911111111', 'Hồ Chí Minh', 'Quận 3', 'Phường Võ Thị Sáu', '456 Điện Biên Phủ', 0),
(3, 3, 'Nhà Kim', '0922222222', 'Hà Nội', 'Quận Cầu Giấy', 'Phường Dịch Vọng', '789 Xuân Thủy', 1);

-- ==========================================================
-- 2. DANH MỤC
-- ==========================================================
INSERT INTO categories (id, name, slug, description, parent_id, sort_order) VALUES
(1, 'Điện thoại', 'dien-thoai', 'Smartphone các dòng', NULL, 1),
(2, 'Laptop', 'laptop', 'Máy tính xách tay', NULL, 2),
(3, 'Phụ kiện', 'phu-kien', 'Tai nghe, cáp, sạc, ốp lưng', NULL, 3),
(4, 'Đồng hồ thông minh', 'smartwatch', 'Apple Watch, Galaxy Watch', NULL, 4),
(5, 'Apple', 'apple', 'Sản phẩm iOS', 1, 1),
(6, 'Samsung', 'samsung', 'Sản phẩm Android', 1, 2),
(7, 'Xiaomi', 'xiaomi', 'Ngon bổ rẻ', 1, 3),
(8, 'MacBook', 'macbook', 'Apple Laptop', 2, 1),
(9, 'Asus', 'asus', 'Laptop Gaming & Văn phòng', 2, 2),
(10, 'Tai nghe', 'tai-nghe', 'Tai nghe Bluetooth & Có dây', 3, 1);

-- ==========================================================
-- 3. SẢN PHẨM & ẢNH
-- ==========================================================
INSERT INTO products (id, category_id, name, slug, description, price, sale_price, stock, sku, is_featured, status) VALUES
(1, 5, 'iPhone 15 Pro Max 256GB', 'iphone-15-pro-max-256gb', '<p>Siêu phẩm công nghệ 2023 với khung Titanium.</p>', 34990000, 32990000, 50, 'IP15PM-256', 1, 1),
(2, 6, 'Samsung Galaxy S24 Ultra', 'samsung-galaxy-s24-ultra', '<p>Quyền năng AI trong tầm tay.</p>', 33990000, 31990000, 30, 'SGS24U-256', 1, 1),
(3, 8, 'MacBook Air M3 2024', 'macbook-air-m3-2024', '<p>Chiếc laptop quốc dân thế hệ mới.</p>', 27990000, NULL, 15, 'MBA-M3', 1, 1),
(4, 7, 'Xiaomi 14 5G', 'xiaomi-14-5g', '<p>Camera Leica đỉnh cao.</p>', 22990000, 19990000, 100, 'MI14-5G', 0, 1),
(5, 10, 'AirPods Pro Gen 2', 'airpods-pro-gen-2', '<p>Chống ồn chủ động xuất sắc.</p>', 6990000, 5890000, 200, 'APP-G2', 1, 1),
(6, 9, 'Asus ROG Strix G15', 'asus-rog-strix-g15', '<p>Laptop gaming hiệu năng khủng.</p>', 29990000, 28500000, 5, 'ROG-G15', 1, 1),
(7, 4, 'Apple Watch Series 9', 'apple-watch-series-9', '<p>Double tap thông minh.</p>', 10490000, 9990000, 0, 'AWS9', 0, 1), -- Hết hàng để test
(8, 3, 'Cáp sạc Anker 20W', 'cap-sac-anker-20w', '<p>Bền bỉ, sạc nhanh.</p>', 450000, 350000, 500, 'ANK-20W', 0, 1);

INSERT INTO product_images (product_id, image_path, is_primary) VALUES
(1, 'iphone-15-pm-titan.webp', 1), (1, 'iphone-15-pm-side.webp', 0),
(2, 's24-ultra-grey.webp', 1), (2, 's24-ultra-pen.webp', 0),
(3, 'macbook-air-m3-silver.webp', 1),
(4, 'xiaomi-14-black.webp', 1),
(5, 'airpods-pro-2.webp', 1),
(6, 'asus-rog-g15.webp', 1),
(7, 'apple-watch-9.webp', 1),
(8, 'anker-cable.webp', 1);

-- ==========================================================
-- 4. WISHLIST & GIỎ HÀNG
-- ==========================================================
INSERT INTO wishlists (user_id, product_id) VALUES
(2, 1), (2, 5), (3, 3);

INSERT INTO carts (id, user_id, session_id) VALUES
(1, 4, 'sess_abc123');

INSERT INTO cart_items (cart_id, product_id, quantity, price) VALUES
(1, 8, 2, 350000); -- Nguyên đang để 2 sợi cáp trong giỏ

-- ==========================================================
-- 5. COUPON & ĐƠN HÀNG
-- ==========================================================
INSERT INTO coupons (id, code, type, value, min_order, max_uses, used_count, start_date, end_date) VALUES
(1, 'WELCOME100', 'fixed', 100000, 1000000, 100, 5, '2024-01-01', '2026-12-31'),
(2, 'SALE20', 'percent', 20, 5000000, 50, 1, '2024-05-01', '2026-12-31'),
(3, 'EXPIRED50', 'fixed', 50000, 0, 10, 10, '2023-01-01', '2023-12-31'); -- Coupon hết hạn/hết lượt để test

INSERT INTO orders (id, user_id, address_id, status, total, payment_method, coupon_id, discount, note, created_at) VALUES
(1, 2, 1, 'completed', 32890000, 'banking', 1, 100000, 'Giao giờ hành chính', '2024-05-15 10:00:00'),
(2, 3, 3, 'pending', 27990000, 'cod', NULL, 0, 'Gọi trước khi giao', '2024-05-31 09:00:00'),
(3, 2, 2, 'cancelled', 5890000, 'cod', NULL, 0, 'Hủy do đổi ý', '2024-05-20 15:30:00');

INSERT INTO order_details (order_id, product_id, quantity, price, subtotal) VALUES
(1, 1, 1, 32990000, 32990000), -- Thuận mua iPhone 15 PM
(2, 3, 1, 27990000, 27990000), -- Kim mua MacBook
(3, 5, 1, 5890000, 5890000);   -- Thuận mua Airpod (đã hủy)

-- ==========================================================
-- 6. BLOG & COMMENT & SETTINGS
-- ==========================================================
INSERT INTO posts (id, title, slug, content, image, status, author_id, created_at) VALUES
(1, 'Đánh giá chi tiết iPhone 15 Pro Max', 'danh-gia-chi-tiet-iphone-15-pro-max', '<p>Sau 1 tháng sử dụng, đây là những cảm nhận chân thực nhất...</p>', 'blog-ip15.jpg', 1, 1, '2024-05-10 08:00:00'),
(2, 'Top 5 Laptop đáng mua nhất 2024 dành cho sinh viên', 'top-5-laptop-dang-mua-2024', '<p>Nếu bạn là sinh viên và đang phân vân chọn mua máy tính...</p>', 'blog-laptop.jpg', 1, 1, '2024-05-25 09:30:00'),
(3, 'Sự kiện Apple WWDC sắp tới có gì hot?', 'su-kien-apple-wwdc-co-gi-hot', '<p>Bản tin tổng hợp tin đồn về iOS 18 và macOS mới...</p>', 'blog-wwdc.jpg', 1, 1, '2024-05-30 14:15:00');

INSERT INTO comments (id, post_id, user_id, content, status) VALUES
(1, 1, 2, 'Bài viết rất chi tiết, cảm ơn admin!', 1),
(2, 1, 3, 'Mình đang xài bản màu Titan tự nhiên, bao đẹp.', 1),
(3, 2, 4, 'Admin tư vấn giúp em tầm giá 15 triệu nên mua máy gì ạ?', 1);

INSERT INTO settings (setting_key, setting_value, description) VALUES
('site_name', 'TechGalaxy', 'Tên website'),
('contact_email', 'support@techgalaxy.com', 'Email liên hệ'),
('contact_phone', '0900000001', 'Hotline hỗ trợ'),
('address', '123 Đường Công Nghệ, Quận 1, TP.HCM', 'Địa chỉ cửa hàng'),
('shipping_fee', '30000', 'Phí vận chuyển mặc định'),
('free_ship_threshold', '500000', 'Mức giá đơn hàng để được freeship');

SET FOREIGN_KEY_CHECKS = 1;