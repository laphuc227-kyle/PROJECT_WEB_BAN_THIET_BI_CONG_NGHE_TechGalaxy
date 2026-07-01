<?php
// File: controllers/AuthController.php
declare(strict_types=1);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Nạp thư viện PHPMailer và file cấu hình mail
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/mail.php';
class AuthController {
    private User $userModel;
    private Address $addressModel;
    public function __construct() {
        $this->userModel = new User();
        $this->addressModel = new Address();
    }
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
    /**
     * Hiển thị trang đăng nhập
     */
    public function showLogin(): void {
        redirectIfAuthenticated();
        $pageTitle = 'Đăng nhập - TechGalaxy';
        require_once __DIR__ . '/../views/user/login.php';
    }
    /**
     * Xử lý đăng nhập
     */
    public function login(): void {
        redirectIfAuthenticated();
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (empty($email) || empty($password)) {
            setFlash('error', 'Vui lòng điền đầy đủ email và mật khẩu.');
            $this->showLogin();
            return;
        }
        $user = $this->userModel->getByEmail($email);
        if (!$user) {
            setFlash('error', 'Email hoặc mật khẩu không chính xác.');
            $this->showLogin();
            return;
        }
        // Kiểm tra xem tài khoản bị khóa hay không
        if ((int)$user['status'] === 0) {
            setFlash('error', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.');
            $this->showLogin();
            return;
        }
        if (password_verify($password, $user['password'])) {
            // Thiết lập session đăng nhập
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user'] = [
                'id' => (int)$user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'avatar' => $user['avatar'] ?: 'default.png',
                'role' => $user['role']
            ];
            setFlash('success', 'Đăng nhập thành công! Chào mừng bạn quay trở lại.');
            redirect('/');
        } else {
            setFlash('error', 'Email hoặc mật khẩu không chính xác.');
            $this->showLogin();
        }
    }
    /**
     * Hiển thị trang đăng ký
     */
    public function showRegister(): void {
        redirectIfAuthenticated();
        $pageTitle = 'Đăng ký tài khoản - TechGalaxy';
        require_once __DIR__ . '/../views/user/register.php';
    }
    /**
     * Xử lý đăng ký tài khoản mới
     */
    public function register(): void {
        redirectIfAuthenticated();
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $phone = sanitize($_POST['phone'] ?? '');
        // Validation
        $errors = [];
        if (empty($name) || strlen($name) < 2 || strlen($name) > 100) {
            $errors[] = 'Họ tên phải từ 2 đến 100 ký tự.';
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Định dạng email không hợp lệ.';
        } elseif ($this->userModel->emailExists($email)) {
            $errors[] = 'Email này đã được đăng ký trước đó.';
        }
        if (empty($password) || strlen($password) < 8) {
            $errors[] = 'Mật khẩu phải từ 8 ký tự trở lên.';
        } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Mật khẩu phải bao gồm cả chữ cái và chữ số.';
        }
        if ($password !== $confirmPassword) {
            $errors[] = 'Mật khẩu xác nhận không khớp.';
        }
        if (!empty($phone) && !preg_match('/^(03|05|07|08|09|01[2|6|8|9])+([0-9]{8})$/', $phone)) {
            $errors[] = 'Số điện thoại Việt Nam không đúng định dạng.';
        }
        if (!empty($errors)) {
            setFlash('error', implode('<br>', $errors));
            // Lưu lại thông tin cũ để điền vào form
            $_SESSION['old_register'] = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone
            ];
            $this->showRegister();
            return;
        }
        // Tạo tài khoản mới
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $userData = [
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
            'phone' => $phone ?: null,
            'avatar' => 'default.png',
            'role' => 'user',
            'status' => 1
        ];
        $userId = $this->userModel->insert($userData);
        if ($userId > 0) {
            unset($_SESSION['old_register']);
            setFlash('success', 'Đăng ký tài khoản thành công! Hãy đăng nhập ngay.');
            redirect('/login');
        } else {
            setFlash('error', 'Có lỗi xảy ra khi đăng ký tài khoản. Vui lòng thử lại.');
            $this->showRegister();
        }
    }
    /**
     * Xử lý đăng xuất
     */
    public function logout(): void {
        // Hủy session
        unset($_SESSION['user_id']);
        unset($_SESSION['user_role']);
        unset($_SESSION['user']);
        
        // Giữ lại flash message
        setFlash('success', 'Bạn đã đăng xuất tài khoản.');
        redirect('/');
    }
    /**
     * Hiển thị trang tài khoản của tôi
     */
    public function showMyAccount(): void {
        requireLogin();
        
        // Lấy lại dữ liệu người dùng mới nhất từ DB
        $userId = (int)$_SESSION['user_id'];
        $user = $this->userModel->findById($userId);
        
        if (!$user || (int)$user['status'] === 0) {
            $this->logout();
            return;
        }
        // Cập nhật lại session
        $_SESSION['user']['name'] = $user['name'];
        $_SESSION['user']['phone'] = $user['phone'];
        $_SESSION['user']['avatar'] = $user['avatar'] ?: 'default.png';
        $pageTitle = 'Tài khoản của tôi - TechGalaxy';
        $currentPage = 'account';
        require_once __DIR__ . '/../views/user/my_account.php';
    }
    /**
     * Cập nhật thông tin cá nhân
     */
    public function updateAccount(): void {
        requireLogin();
        $userId = (int)$_SESSION['user_id'];
        $name = sanitize($_POST['name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $errors = [];
        if (empty($name) || strlen($name) < 2 || strlen($name) > 100) {
            $errors[] = 'Họ tên phải từ 2 đến 100 ký tự.';
        }
        if (!empty($phone) && !preg_match('/^(03|05|07|08|09|01[2|6|8|9])+([0-9]{8})$/', $phone)) {
            $errors[] = 'Số điện thoại không đúng định dạng.';
        }
        if (!empty($errors)) {
            setFlash('error', implode('<br>', $errors));
            redirect('/account');
        }
        $updateData = [
            'name' => $name,
            'phone' => $phone ?: null
        ];
        if ($this->userModel->update($userId, $updateData)) {
            setFlash('success', 'Cập nhật thông tin tài khoản thành công!');
        } else {
            setFlash('error', 'Không có thay đổi nào được thực hiện.');
        }
        redirect('/account');
    }
    /**
     * Đổi mật khẩu
     */
    public function updatePassword(): void {
        requireLogin();
        $userId = (int)$_SESSION['user_id'];
        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $errors = [];
        if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
            $errors[] = 'Vui lòng nhập đầy đủ các trường mật khẩu.';
        }
        // Kiểm tra mật khẩu cũ
        $user = $this->userModel->findById($userId);
        if (!$user || !password_verify($oldPassword, $user['password'])) {
            $errors[] = 'Mật khẩu hiện tại không chính xác.';
        }
        // Validate mật khẩu mới
        if (empty($errors)) {
            if (strlen($newPassword) < 8) {
                $errors[] = 'Mật khẩu mới phải từ 8 ký tự trở lên.';
            } elseif (!preg_match('/[A-Za-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
                $errors[] = 'Mật khẩu mới phải bao gồm cả chữ cái và chữ số.';
            }
            if ($newPassword === $oldPassword) {
                $errors[] = 'Mật khẩu mới không được trùng với mật khẩu cũ.';
            }
            if ($newPassword !== $confirmPassword) {
                $errors[] = 'Mật khẩu xác nhận không trùng khớp.';
            }
        }
        if (!empty($errors)) {
            setFlash('error', implode('<br>', $errors));
            redirect('/account?tab=password');
        }
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        if ($this->userModel->update($userId, ['password' => $hashedPassword])) {
            setFlash('success', 'Đổi mật khẩu thành công!');
        } else {
            setFlash('error', 'Có lỗi xảy ra khi đổi mật khẩu.');
        }
        redirect('/account?tab=password');
    }
    /**
     * Upload ảnh đại diện
     */
    public function uploadAvatar(): void {
        requireLogin();
        $userId = (int)$_SESSION['user_id'];
        
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            setFlash('error', 'Vui lòng chọn ảnh đại diện hợp lệ.');
            redirect('/account');
        }
        $file = $_FILES['avatar'];
        $fileSize = $file['size'];
        $fileTmp = $file['tmp_name'];
        $fileType = $file['type'];
        // Validate size (2MB)
        if ($fileSize > 2 * 1024 * 1024) {
            setFlash('error', 'Ảnh đại diện không được vượt quá 2MB.');
            redirect('/account');
        }
        // Validate type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        // Sử dụng mime content type để chính xác hơn
        $detectedType = mime_content_type($fileTmp);
        if (!in_array($detectedType, $allowedTypes)) {
            setFlash('error', 'Chỉ chấp nhận định dạng ảnh JPG, PNG và WEBP.');
            redirect('/account');
        }
        // Tạo tên file ngẫu nhiên tránh trùng và bảo mật
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (!in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp'])) {
            setFlash('error', 'Đuôi mở rộng của tệp tin không hợp lệ.');
            redirect('/account');
        }
        $newFileName = uniqid('avatar_') . '_' . time() . '.' . strtolower($ext);
        $uploadDir = __DIR__ . '/../public/uploads/avatars/';
        
        // Tạo thư mục nếu chưa tồn tại
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $destPath = $uploadDir . $newFileName;
        if (move_uploaded_file($fileTmp, $destPath)) {
            // Xóa ảnh cũ nếu không phải default.png
            $user = $this->userModel->findById($userId);
            if ($user && !empty($user['avatar']) && $user['avatar'] !== 'default.png') {
                $oldAvatarPath = $uploadDir . $user['avatar'];
                if (file_exists($oldAvatarPath)) {
                    unlink($oldAvatarPath);
                }
            }
            // Lưu tên file mới vào DB
            $this->userModel->update($userId, ['avatar' => $newFileName]);
            $_SESSION['user']['avatar'] = $newFileName;
            setFlash('success', 'Cập nhật ảnh đại diện thành công!');
        } else {
            setFlash('error', 'Đã xảy ra lỗi trong quá trình lưu ảnh.');
        }
        redirect('/account');
    }
    /**
     * Hiển thị danh sách địa chỉ nhận hàng
     */
    public function showAddresses(): void {
        requireLogin();
        $userId = (int)$_SESSION['user_id'];
        $addresses = $this->addressModel->getByUser($userId);
        
        $pageTitle = 'Địa chỉ của tôi - TechGalaxy';
        $currentPage = 'addresses';
        require_once __DIR__ . '/../views/user/my_address.php';
    }
    /**
     * Thêm địa chỉ mới
     */
    public function createAddress(): void {
        requireLogin();
        $userId = (int)$_SESSION['user_id'];
        $name = sanitize($_POST['name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $province = sanitize($_POST['province'] ?? '');
        $district = sanitize($_POST['district'] ?? '');
        $ward = sanitize($_POST['ward'] ?? '');
        $detail = sanitize($_POST['detail'] ?? '');
        $isDefault = isset($_POST['is_default']) ? 1 : 0;
        $errors = [];
        if (empty($name)) $errors[] = 'Họ tên người nhận không được để trống.';
        if (empty($phone) || !preg_match('/^(03|05|07|08|09|01[2|6|8|9])+([0-9]{8})$/', $phone)) {
            $errors[] = 'Số điện thoại người nhận không hợp lệ.';
        }
        if (empty($province) || empty($district) || empty($ward) || empty($detail)) {
            $errors[] = 'Vui lòng nhập đầy đủ thông tin địa chỉ giao hàng.';
        }
        if (!empty($errors)) {
            setFlash('error', implode('<br>', $errors));
            redirect('/account/addresses');
        }
        // Kiểm tra xem đã có địa chỉ nào chưa, nếu chưa có thì bắt buộc làm mặc định
        $existingAddresses = $this->addressModel->getByUser($userId);
        if (empty($existingAddresses)) {
            $isDefault = 1;
        }
        $addressData = [
            'user_id' => $userId,
            'name' => $name,
            'phone' => $phone,
            'province' => $province,
            'district' => $district,
            'ward' => $ward,
            'detail' => $detail,
            'is_default' => $isDefault
        ];
        $addressId = $this->addressModel->create($addressData);
        if ($addressId > 0) {
            // Nếu đánh dấu mặc định, đặt các cái khác thành 0
            if ($isDefault === 1) {
                $this->addressModel->setDefault($addressId, $userId);
            }
            setFlash('success', 'Thêm địa chỉ giao hàng mới thành công!');
        } else {
            setFlash('error', 'Có lỗi xảy ra khi tạo địa chỉ.');
        }
        redirect('/account/addresses');
    }
    /**
     * Cập nhật địa chỉ nhận hàng
     */
    public function updateAddress(): void {
        requireLogin();
        $userId = (int)$_SESSION['user_id'];
        $addressId = (int)($_POST['address_id'] ?? 0);
        $name = sanitize($_POST['name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $province = sanitize($_POST['province'] ?? '');
        $district = sanitize($_POST['district'] ?? '');
        $ward = sanitize($_POST['ward'] ?? '');
        $detail = sanitize($_POST['detail'] ?? '');
        $isDefault = isset($_POST['is_default']) ? 1 : 0;
        $errors = [];
        if (empty($name)) $errors[] = 'Họ tên người nhận không được để trống.';
        if (empty($phone) || !preg_match('/^(03|05|07|08|09|01[2|6|8|9])+([0-9]{8})$/', $phone)) {
            $errors[] = 'Số điện thoại người nhận không hợp lệ.';
        }
        if (empty($province) || empty($district) || empty($ward) || empty($detail)) {
            $errors[] = 'Vui lòng nhập đầy đủ thông tin địa chỉ giao hàng.';
        }
        if (!empty($errors)) {
            setFlash('error', implode('<br>', $errors));
            redirect('/account/addresses');
        }
        $addressData = [
            'name' => $name,
            'phone' => $phone,
            'province' => $province,
            'district' => $district,
            'ward' => $ward,
            'detail' => $detail
        ];
        // Nếu chỉ có duy nhất địa chỉ này, bắt buộc là mặc định
        $existingAddresses = $this->addressModel->getByUser($userId);
        if (count($existingAddresses) === 1) {
            $isDefault = 1;
        }
        if ($this->addressModel->updateAddress($addressId, $userId, $addressData)) {
            if ($isDefault === 1) {
                $this->addressModel->setDefault($addressId, $userId);
            }
            setFlash('success', 'Cập nhật địa chỉ thành công!');
        } else {
            setFlash('error', 'Có lỗi xảy ra hoặc địa chỉ không thuộc về bạn.');
        }
        redirect('/account/addresses');
    }
    /**
     * Xóa địa chỉ nhận hàng
     */
    public function deleteAddress(): void {
        requireLogin();
        $userId = (int)$_SESSION['user_id'];
        $addressId = (int)($_POST['address_id'] ?? 0);
        // Không cho xóa địa chỉ mặc định nếu vẫn còn địa chỉ khác
        $address = $this->addressModel->findById($addressId);
        if ($address && (int)$address['is_default'] === 1) {
            $existingAddresses = $this->addressModel->getByUser($userId);
            if (count($existingAddresses) > 1) {
                setFlash('error', 'Bạn không thể xóa địa chỉ mặc định. Vui lòng đặt địa chỉ khác làm mặc định trước.');
                redirect('/account/addresses');
                return;
            }
        }
        if ($this->addressModel->deleteAddress($addressId, $userId)) {
            setFlash('success', 'Xóa địa chỉ giao hàng thành công!');
        } else {
            setFlash('error', 'Xóa địa chỉ thất bại.');
        }
        redirect('/account/addresses');
    }
    /**
     * Đặt địa chỉ mặc định
     */
    public function setDefaultAddress(): void {
        requireLogin();
        $userId = (int)$_SESSION['user_id'];
        $addressId = (int)($_POST['address_id'] ?? 0);
        if ($this->addressModel->setDefault($addressId, $userId)) {
            setFlash('success', 'Đã đặt địa chỉ làm mặc định!');
        } else {
            setFlash('error', 'Đặt địa chỉ mặc định thất bại.');
        }
        redirect('/account/addresses');
    }
    /**
     * Hiển thị trang Quên mật khẩu
     */
    public function showForgotPassword(): void {
        redirectIfAuthenticated();
        $pageTitle = 'Quên mật khẩu - TechGalaxy';
        require_once __DIR__ . '/../views/user/forgot_password.php';
    }
    /**
     * Xử lý gửi OTP Quên mật khẩu
     */
    public function forgotPassword(): void {
        redirectIfAuthenticated();
        $email = sanitize($_POST['email'] ?? '');
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlash('error', 'Vui lòng nhập email hợp lệ.');
            redirect('/forgot_password.php');
            return;
        }
        $user = $this->userModel->getByEmail($email);
        if (!$user) {
            setFlash('error', 'Email không tồn tại trong hệ thống.');
            redirect('/forgot_password.php');
            return;
        }
        // Gửi OTP qua email
        $otp = $this->sendOtp($email);
        if ($otp !== false) {
            // Lưu OTP vào DB
            $expire = date('Y-m-d H:i:s', time() + 600); // 10 phút hiệu lực
            $this->userModel->update((int)$user['id'], [
                'otp_code' => (string)$otp,
                'otp_expire' => $expire
            ]);
            $_SESSION['reset_email'] = $email;
            setFlash('success', 'Mã OTP xác thực đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư (bao gồm cả thư rác).');
            redirect('/reset_password.php');
        } else {
            setFlash('error', 'Không thể gửi email OTP lúc này. Vui lòng thử lại sau.');
            redirect('/forgot_password.php');
        }
    }
    /**
     * Hiển thị trang đặt lại mật khẩu
     */
    public function showResetPassword(): void {
        redirectIfAuthenticated();
        if (empty($_SESSION['reset_email'])) {
            setFlash('error', 'Vui lòng yêu cầu cấp lại mật khẩu trước.');
            redirect('/forgot_password.php');
            return;
        }
        $pageTitle = 'Đặt lại mật khẩu - TechGalaxy';
        require_once __DIR__ . '/../views/user/reset_password.php';
    }
    /**
     * Xử lý đặt lại mật khẩu mới bằng OTP
     */
    public function resetPassword(): void {
        redirectIfAuthenticated();
        
        if (empty($_SESSION['reset_email'])) {
            setFlash('error', 'Yêu cầu không hợp lệ.');
            redirect('/forgot_password.php');
            return;
        }
        $email = $_SESSION['reset_email'];
        $otp = sanitize($_POST['otp'] ?? '');
        $newPassword = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $errors = [];
        if (empty($otp)) {
            $errors[] = 'Vui lòng nhập mã OTP.';
        }
        if (empty($newPassword) || strlen($newPassword) < 8) {
            $errors[] = 'Mật khẩu mới phải từ 8 ký tự trở lên.';
        } elseif (!preg_match('/[A-Za-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
            $errors[] = 'Mật khẩu mới phải bao gồm cả chữ cái và chữ số.';
        }
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'Xác nhận mật khẩu không trùng khớp.';
        }
        if (!empty($errors)) {
            setFlash('error', implode('<br>', $errors));
            redirect('/reset_password.php');
            return;
        }
        // Lấy thông tin user
        $user = $this->userModel->getByEmail($email);
        if (!$user) {
            setFlash('error', 'Tài khoản không tồn tại.');
            redirect('/forgot_password.php');
            return;
        }
        // Kiểm tra OTP
        $dbOtp = $user['otp_code'];
        $dbExpire = $user['otp_expire'] ? strtotime($user['otp_expire']) : 0;
        if ($dbOtp !== $otp || time() > $dbExpire) {
            setFlash('error', 'Mã OTP không chính xác hoặc đã hết hạn.');
            redirect('/reset_password.php');
            return;
        }
        // Cập nhật mật khẩu mới và xoá OTP
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $success = $this->userModel->update((int)$user['id'], [
            'password' => $hashedPassword,
            'otp_code' => null,
            'otp_expire' => null
        ]);
        if ($success) {
            unset($_SESSION['reset_email']);
            setFlash('success', 'Đổi mật khẩu thành công! Bạn có thể đăng nhập bằng mật khẩu mới.');
            redirect('/login');
        } else {
            setFlash('error', 'Có lỗi xảy ra khi đổi mật khẩu.');
            redirect('/reset_password.php');
        }
    }
// ← đóng hàm resetPassword()
}
// ← đóng class AuthController

// ===== ROUTING =====
if (!function_exists('redirectIfAuthenticated')) {
    function redirectIfAuthenticated(): void {
        if (!empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/');
            exit;
        }
    }
}
if (!function_exists('requireLogin')) {
    function requireLogin(): void {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }
}

$authCtrl = new AuthController();

$uri      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = parse_url(BASE_URL, PHP_URL_PATH) ?: '';
$authPath = trim(str_replace($basePath, '', $uri), '/');
$method   = $_SERVER['REQUEST_METHOD'];

if ($authPath === 'login') {
    $method === 'POST' ? $authCtrl->login() : $authCtrl->showLogin();

} elseif ($authPath === 'register') {
    $method === 'POST' ? $authCtrl->register() : $authCtrl->showRegister();

} elseif ($authPath === 'logout') {
    $authCtrl->logout();

} elseif ($authPath === 'forgot-password') {
    $method === 'POST' ? $authCtrl->forgotPassword() : $authCtrl->showForgotPassword();

} elseif ($authPath === 'reset-password') {
    $method === 'POST' ? $authCtrl->resetPassword() : $authCtrl->showResetPassword();
} elseif ($authPath === 'account') {
    // Sửa updateProfile thành updateAccount
    $method === 'POST' ? $authCtrl->updateAccount() : $authCtrl->showMyAccount();

} elseif ($authPath === 'account/addresses') {
    // Sửa showMyAddress thành showAddresses
    $authCtrl->showAddresses();

} elseif ($authPath === 'account/addresses/add') {
    // Sửa addAddress thành createAddress
    $authCtrl->createAddress();

} elseif ($authPath === 'account/addresses/edit') {
    $authCtrl->updateAddress();

} elseif ($authPath === 'account/addresses/delete') {
    $authCtrl->deleteAddress();

} elseif ($authPath === 'account/addresses/default') {
    $authCtrl->setDefaultAddress();

} elseif ($authPath === 'account/change-password') {
    // Sửa changePassword thành updatePassword. 
    // Vì không có hàm showChangePassword (dùng chung giao diện account), ta redirect về tab password nếu người dùng vào bằng GET
    if ($method === 'POST') {
        $authCtrl->updatePassword();
    } else {
        header('Location: ' . BASE_URL . '/account?tab=password');
        exit;
    }
}