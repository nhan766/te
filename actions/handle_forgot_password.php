<?php
session_start();
require_once('../includes/db.php');
// Include thư viện gửi mail nếu dùng (ví dụ: PHPMailer)
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;
// require '../vendor/autoload.php'; // Nếu dùng Composer

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['reset_message'] = "Địa chỉ email không hợp lệ.";
        $_SESSION['reset_error'] = true;
        header('Location: ../forgot_password.php');
        exit;
    }

    $pdo->beginTransaction();
    try {
        // 1. Kiểm tra email có tồn tại trong bảng users không
        $stmtUser = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmtUser->execute([$email]);
        if (!$stmtUser->fetch()) {
            // Không thông báo rõ là email không tồn tại để bảo mật
            // Chỉ cần báo đã gửi (dù thực tế không gửi)
            $_SESSION['reset_message'] = "Nếu email tồn tại trong hệ thống, bạn sẽ nhận được liên kết đặt lại mật khẩu.";
            $pdo->commit(); // Commit để không giữ transaction
            header('Location: ../forgot_password.php');
            exit;
        }

        // 2. Xóa token cũ (nếu có) cho email này
        $stmtDelete = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmtDelete->execute([$email]);

        // 3. Tạo token mới
        $token = bin2hex(random_bytes(32)); // Token ngẫu nhiên an toàn
        $expires = time() + (60 * 30); // Hết hạn sau 30 phút
        $expires_at = date('Y-m-d H:i:s', $expires);

        // 4. Lưu token vào CSDL
        $stmtInsert = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmtInsert->execute([$email, $token, $expires_at]);

        // 5. Gửi Email
        $resetLink = "http://yourwebsite.com/reset_password.php?token=" . urlencode($token); // Thay yourwebsite.com
        $subject = "Đặt lại mật khẩu cho SurveyForGood";
        $body = "Chào bạn,\n\n";
        $body .= "Bạn nhận được email này vì đã yêu cầu đặt lại mật khẩu cho tài khoản của bạn.\n";
        $body .= "Vui lòng nhấp vào liên kết sau để đặt lại mật khẩu (liên kết có hiệu lực trong 30 phút):\n";
        $body .= $resetLink . "\n\n";
        $body .= "Nếu bạn không yêu cầu điều này, vui lòng bỏ qua email này.\n\n";
        $body .= "Trân trọng,\nĐội ngũ SurveyForGood";
        $headers = "From: no-reply@yourwebsite.com\r\n"; // Thay email gửi
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        // Dùng mail() hoặc PHPMailer
        if (mail($email, $subject, $body, $headers)) {
            $pdo->commit();
            $_SESSION['reset_message'] = "Một liên kết đặt lại mật khẩu đã được gửi đến email của bạn (nếu email tồn tại).";
        } else {
             throw new Exception("Không thể gửi email đặt lại mật khẩu.");
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Forgot Password Error: " . $e->getMessage());
        $_SESSION['reset_message'] = "Đã xảy ra lỗi. Vui lòng thử lại sau.";
        $_SESSION['reset_error'] = true;
    }

    header('Location: ../forgot_password.php');
    exit;
} else {
    header('Location: ../forgot_password.php');
    exit;
}
?>