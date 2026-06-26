<?php
/**
 * includes/footer.php
 * Footer chung toàn site + tất cả script CDN.
 * Luôn include cuối mỗi view (trước </body>).
 */
?>

<!-- ══════════════════════════════════════════════════════════════════ -->
<!-- FOOTER                                                             -->
<!-- ══════════════════════════════════════════════════════════════════ -->
<footer class="site-footer">

  <!-- Sóng trang trí phía trên footer -->
  <div class="footer-wave">
    <svg viewBox="0 0 1440 60" preserveAspectRatio="none">
      <path d="M0,30 C360,60 1080,0 1440,30 L1440,60 L0,60 Z" fill="currentColor"/>
    </svg>
  </div>

  <div class="footer-main">
    <div class="container-xl">
      <div class="row g-4">

        <!-- Cột 1: Thương hiệu -->
        <div class="col-12 col-md-6 col-lg-3">
          <div class="footer-brand">
            <a href="<?= BASE_URL ?>/" class="footer-logo">
              <i class="fa-solid fa-microchip"></i>
              Tech<strong>Galaxy</strong>
            </a>
            <p class="footer-tagline">
              Thiết bị công nghệ chính hãng, giá tốt nhất thị trường.
              Giao hàng toàn quốc, bảo hành tận nơi.
            </p>
            <div class="footer-social">
              <a href="#" aria-label="Facebook"  class="social-link"><i class="fa-brands fa-facebook-f"></i></a>
              <a href="#" aria-label="TikTok"    class="social-link"><i class="fa-brands fa-tiktok"></i></a>
              <a href="#" aria-label="YouTube"   class="social-link"><i class="fa-brands fa-youtube"></i></a>
              <a href="#" aria-label="Instagram" class="social-link"><i class="fa-brands fa-instagram"></i></a>
            </div>
          </div>
        </div>

        <!-- Cột 2: Danh mục -->
        <div class="col-6 col-md-3 col-lg-2">
          <h6 class="footer-heading">Danh mục</h6>
          <ul class="footer-links">
            <li><a href="<?= BASE_URL ?>/shop?category=dien-thoai">Điện thoại</a></li>
            <li><a href="<?= BASE_URL ?>/shop?category=laptop">Laptop</a></li>
            <li><a href="<?= BASE_URL ?>/shop?category=tai-nghe">Tai nghe</a></li>
            <li><a href="<?= BASE_URL ?>/shop?category=dong-ho">Đồng hồ thông minh</a></li>
            <li><a href="<?= BASE_URL ?>/shop?category=may-tinh-bang">Máy tính bảng</a></li>
            <li><a href="<?= BASE_URL ?>/shop?category=phu-kien">Phụ kiện</a></li>
          </ul>
        </div>

        <!-- Cột 3: Hỗ trợ -->
        <div class="col-6 col-md-3 col-lg-2">
          <h6 class="footer-heading">Hỗ trợ</h6>
          <ul class="footer-links">
            <li><a href="<?= BASE_URL ?>/contact">Liên hệ</a></li>
            <li><a href="#">Chính sách bảo hành</a></li>
            <li><a href="#">Chính sách đổi trả</a></li>
            <li><a href="#">Hướng dẫn mua hàng</a></li>
            <li><a href="#">Câu hỏi thường gặp</a></li>
          </ul>
        </div>

        <!-- Cột 4: Thông tin liên hệ -->
        <div class="col-12 col-md-6 col-lg-3">
          <h6 class="footer-heading">Liên hệ</h6>
          <ul class="footer-contact">
            <li>
              <i class="fa-solid fa-location-dot"></i>
              <span>227 Nguyễn Văn Cừ, Q.5, TP.HCM</span>
            </li>
            <li>
              <i class="fa-solid fa-phone"></i>
              <a href="tel:19001234">1900 1234</a>
            </li>
            <li>
              <i class="fa-solid fa-envelope"></i>
              <a href="mailto:support@techgalaxy.vn">support@techgalaxy.vn</a>
            </li>
            <li>
              <i class="fa-regular fa-clock"></i>
              <span>8:00 – 22:00, Thứ 2 – Chủ nhật</span>
            </li>
          </ul>
        </div>

        <!-- Cột 5: Newsletter -->
        <div class="col-12 col-lg-2">
          <h6 class="footer-heading">Đăng ký nhận ưu đãi</h6>
          <form class="footer-newsletter" onsubmit="handleNewsletter(event)">
            <input type="email" placeholder="Email của bạn" required>
            <button type="submit">
              <i class="fa-solid fa-paper-plane"></i>
            </button>
          </form>
          <p class="footer-newsletter-note">Nhận thông báo sản phẩm mới và khuyến mãi độc quyền.</p>

          <!-- Phương thức thanh toán -->
          <div class="footer-payments">
            <img src="<?= BASE_URL ?>/assets/images/payments/visa.svg"       alt="Visa"       width="36">
            <img src="<?= BASE_URL ?>/assets/images/payments/mastercard.svg" alt="Mastercard" width="36">
            <img src="<?= BASE_URL ?>/assets/images/payments/momo.svg"       alt="MoMo"       width="36">
            <img src="<?= BASE_URL ?>/assets/images/payments/vnpay.svg"      alt="VNPay"      width="36">
          </div>
        </div>

      </div><!-- /row -->
    </div><!-- /container -->
  </div><!-- /footer-main -->

  <!-- Footer bottom -->
  <div class="footer-bottom">
    <div class="container-xl">
      <div class="row align-items-center">
        <div class="col-12 col-md-6 text-center text-md-start">
          <small>&copy; <?= date('Y') ?> <?= APP_NAME ?>. Tất cả quyền được bảo lưu.</small>
        </div>
        <div class="col-12 col-md-6 text-center text-md-end mt-2 mt-md-0">
          <small>
            <a href="#">Điều khoản sử dụng</a> &nbsp;·&nbsp;
            <a href="#">Chính sách bảo mật</a>
          </small>
        </div>
      </div>
    </div>
  </div>

</footer>

<!-- ── Nút back-to-top ──────────────────────────────────────────────── -->
<button class="back-to-top" id="backToTop" aria-label="Lên đầu trang">
  <i class="fa-solid fa-chevron-up"></i>
</button>

<!-- ══════════════════════════════════════════════════════════════════ -->
<!-- SCRIPTS CDN                                                        -->
<!-- ══════════════════════════════════════════════════════════════════ -->

<!-- Bootstrap 5.3 JS (bundle = Popper included) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<!-- JS chính dự án -->
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>

<!-- JS riêng từng trang (view inject vào trước footer) -->
<?= $extraJS ?? '' ?>

</body>
<<<<<<< HEAD
</html>
=======
</html>
>>>>>>> develop
