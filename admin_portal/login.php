<?php
session_start();
// Nếu đã đăng nhập admin, vào dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}
$error_message = $_SESSION['admin_error'] ?? '';
unset($_SESSION['admin_error']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f0f0f0; }
        .login-form { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 300px; }
        .login-form h2 { text-align: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;}
        .error { color: red; margin-bottom: 15px; text-align: center;}
        button { width: 100%; padding: 10px; background-color: #2980b9; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #3498db; }
    </style>
</head>
<body>
    <div class="login-form">
        <h2>Admin Login</h2>
        <?php if ($error_message): ?><p class="error"><?php echo $error_message; ?></p><?php endif; ?>
        <form action="actions/handle_admin_login.php" method="POST">
            <div class="form-group">
                <label for="username">Username or Email:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>