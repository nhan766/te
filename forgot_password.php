<?php
$page_title = "Quên Mật khẩu";
require_once('includes/header.php'); // Dùng header chung
?>
<style> /* CSS đơn giản */
    .form-container { max-width: 400px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .form-group { margin-bottom: 15px; }
    /* ... thêm CSS cho input, button, message ... */
    .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; text-align: center; }
    .success { background-color: #d4edda; color: #155724; }
    .error { background-color: #f8d7da; color: #721c24; }
</style>

<div class="form-container">
    <h2>Quên Mật khẩu</h2>
    <p>Nhập địa chỉ email của bạn. Chúng tôi sẽ gửi một liên kết để đặt lại mật khẩu.</p>

    <?php if (isset($_SESSION['reset_message'])): ?>
        <p class="message <?php echo isset($_SESSION['reset_error']) ? 'error' : 'success'; ?>">
            <?php echo $_SESSION['reset_message']; ?>
        </p>
        <?php unset($_SESSION['reset_message'], $_SESSION['reset_error']); ?>
    <?php endif; ?>

    <form action="actions/handle_forgot_password.php" method="POST">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <button type="submit">Gửi liên kết Reset</button>
    </form>
    <p style="margin-top: 15px; text-align: center;"><a href="login.php">Quay lại Đăng nhập</a></p>
</div>

<?php require_once('includes/footer.php'); ?>