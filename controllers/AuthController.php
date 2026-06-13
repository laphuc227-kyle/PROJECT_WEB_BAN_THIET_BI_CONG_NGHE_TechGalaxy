<?php
// File: controllers/AuthController.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Nạp thư viện PHPMailer và file cấu hình mail
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/mail.php';

class AuthController {

    /**
     * Hàm gửi email OTP Quên mật khẩu
     */
    public function sendOtp($email) {
        $mail = new PHPMailer(true);

        try {
            // 1. Cấu hình Server gửi mail (Lấy từ config/mail.php)
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = MAIL_PORT;
            $mail->CharSet    = 'UTF-8';

            // 2. Cấu hình người gửi và người nhận
            $mail->setFrom(MAIL_USERNAME, MAIL_FROM_NAME);
            $mail->addAddress($email); // Gửi đến email mà người dùng nhập vào

            // 3. Tạo mã OTP ngẫu nhiên 6 chữ số
            $otp = rand(100000, 999999);

            // 4. Soạn nội dung Email
            $mail->isHTML(true);
            $mail->Subject = 'Mã xác nhận quên mật khẩu - TechGalaxy';
            $mail->Body    = "Chào bạn,<br><br>
                              Bạn vừa yêu cầu đặt lại mật khẩu tại TechGalaxy.<br>
                              Mã OTP xác nhận của bạn là: <b style='color:red; font-size:20px;'>{$otp}</b>.<br>
                              Vui lòng không chia sẻ mã này cho bất kỳ ai. Mã có hiệu lực trong vòng 5 phút.<br><br>
                              Trân trọng,<br>
                              TechGalaxy Team";

            // 5. Bấm nút gửi
            $mail->send();

            return $otp; // Trả về mã OTP để hệ thống đối chiếu sau này

        } catch (Exception $e) {
            // Nếu có lỗi (sai pass, mất mạng...), trả về false
            error_log("Lỗi gửi mail: {$mail->ErrorInfo}");
            return false;
        }
    }
}