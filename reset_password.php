<?php
session_start();
require_once('includes/db.php');

$token = $_GET['token'] ?? null;
$validToken = false;
$tokenError = '';
$email = null; // Email liên kết với token hợp lệ

if ($token) {
    try {
        $stmt = $pdo->prepare("SELECT email, expires_at FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);
        $resetRequest = $stmt->fetch();

        if ($resetRequest) {
            $now = time();
            $expires = strtotime($resetRequest['expires_at']);
            if ($now < $expires) {
                $validToken = true; // Token hợp lệ và chưa hết hạn
                $email = $resetRequest['email'];
            } else {
                $tokenError = "Liên kết đặt lại mật khẩu đã hết hạn.";
                // Có thể xóa token hết hạn ở đây
                $delStmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
                $delStmt->execute([$token]);
            }
        } else {
            $tokenError = "Liên kết đặt lại mật khẩu không hợp lệ hoặc đã được sử dụng.";
        }
    } catch (PDOException $e) {
        error_log("Token Check Error: " . $e->getMessage());
        $tokenError = "Lỗi hệ thống khi kiểm tra liên kết.";
    }
} else {
    $tokenError = "Liên kết không hợp lệ.";
}
$page_title = "Đặt lại Mật khẩu";
require_once('includes/header.php');
?>
<style> /* CSS tương tự forgot_password.php */
    .form-container { max-width: 400px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    /* ... */
    .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; text-align: center; }
    .success { background-color: #d4edda; color: #155724; }
    .error { background-color: #f8d7da; color: #721c24; }
</style>

<div class="form-container">
    <h2>Đặt lại Mật khẩu</h2>

    <?php if ($tokenError): ?>
        <p class="message error"><?php echo $tokenError; ?></p>
        <p style="text-align: center;"><a href="forgot_password.php">Yêu cầu liên kết mới</a></p>
    <?php elseif ($validToken): ?>
        <?php if (isset($_SESSION['reset_status_message'])): ?>
            <p class="message <?php echo isset($_SESSION['reset_status_error']) ? 'error' : 'success'; ?>">
                <?php echo $_SESSION['reset_status_message']; ?>
            </p>
            <?php unset($_SESSION['reset_status_message'], $_SESSION['reset_status_error']); ?>
            <?php if (!isset($_SESSION['reset_status_error'])): // Nếu thành công thì chỉ hiện link login ?>
                 <p style="text-align: center;"><a href="login.php">Đăng nhập ngay</a></p>
                 <?php require_once('includes/footer.php'); exit(); // Dừng không hiển thị form nữa ?>
            <?php endif; ?>
        <?php endif; ?>

        <p>Nhập mật khẩu mới cho tài khoản: <?php echo htmlspecialchars($email); ?></p>
        <form action="actions/handle_reset_password.php" method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <div class="form-group">
                <label for="password">Mật khẩu mới:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Xác nhận mật khẩu mới:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit">Đặt lại Mật khẩu</button>
        </form>
    <?php else: ?>
         <p class="message error">Đã xảy ra lỗi không xác định.</p>
    <?php endif; ?>

</div>

<?php require_once('includes/footer.php'); ?>