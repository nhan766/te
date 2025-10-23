<?php
session_start();
require_once('../includes/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['token'], $_POST['password'], $_POST['confirm_password'])) {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // --- Validation ---
    if (empty($password) || empty($confirm_password)) {
        $_SESSION['reset_status_message'] = "Vui lòng nhập đầy đủ mật khẩu mới.";
        $_SESSION['reset_status_error'] = true;
        header('Location: ../reset_password.php?token=' . urlencode($token));
        exit;
    }
    if ($password !== $confirm_password) {
        $_SESSION['reset_status_message'] = "Mật khẩu xác nhận không khớp.";
        $_SESSION['reset_status_error'] = true;
        header('Location: ../reset_password.php?token=' . urlencode($token));
        exit;
    }
    // (Thêm validation độ mạnh mật khẩu nếu cần)

    $pdo->beginTransaction();
    try {
        // 1. Tìm token và kiểm tra hết hạn (FOR UPDATE để khóa)
        $stmt = $pdo->prepare("SELECT email, expires_at FROM password_resets WHERE token = ? FOR UPDATE");
        $stmt->execute([$token]);
        $resetRequest = $stmt->fetch();

        if ($resetRequest) {
            $now = time();
            $expires = strtotime($resetRequest['expires_at']);

            if ($now < $expires) {
                // Token hợp lệ!
                $email = $resetRequest['email'];

                // 2. Băm mật khẩu mới
                $new_password_hash = password_hash($password, PASSWORD_DEFAULT);

                // 3. Cập nhật mật khẩu trong bảng users
                $updateStmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
                $updateStmt->execute([$new_password_hash, $email]);

                // 4. Xóa token đã sử dụng
                $deleteStmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
                $deleteStmt->execute([$token]);

                $pdo->commit();
                $_SESSION['reset_status_message'] = "Mật khẩu của bạn đã được đặt lại thành công!";
                 // Không set reset_status_error
                 header('Location: ../reset_password.php?token=' . urlencode($token)); // Quay lại trang reset để hiện thông báo thành công
                 exit;

            } else {
                // Token hết hạn
                $deleteStmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
                $deleteStmt->execute([$token]);
                $pdo->commit(); // Commit việc xóa token hết hạn
                $_SESSION['reset_status_message'] = "Liên kết đặt lại mật khẩu đã hết hạn.";
                $_SESSION['reset_status_error'] = true;
            }
        } else {
            // Token không tồn tại hoặc đã bị xóa
            $pdo->rollBack(); // Không cần thiết nhưng để cho chắc
            $_SESSION['reset_status_message'] = "Liên kết đặt lại mật khẩu không hợp lệ hoặc đã được sử dụng.";
            $_SESSION['reset_status_error'] = true;
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Reset Password Error: " . $e->getMessage());
        $_SESSION['reset_status_message'] = "Đã xảy ra lỗi khi đặt lại mật khẩu.";
        $_SESSION['reset_status_error'] = true;
    }

    // Redirect về trang reset với token ban đầu để hiển thị lỗi
    header('Location: ../reset_password.php?token=' . urlencode($token));
    exit;

} else {
    header('Location: ../login.php'); // Truy cập không hợp lệ
    exit;
}
?>