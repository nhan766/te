<?php
session_start();
require_once('../../includes/db.php'); // Đường dẫn relativo

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        // Tìm user là admin
        $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND is_admin = TRUE");
        $stmt->execute([$username, $username]);
        $admin_user = $stmt->fetch();

        if ($admin_user && password_verify($password, $admin_user['password_hash'])) {
            // Đăng nhập admin thành công
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user_id'] = $admin_user['id'];
            $_SESSION['admin_username'] = $admin_user['username'];
            header("Location: ../dashboard.php"); // Vào trang dashboard admin
            exit;
        } else {
            $_SESSION['admin_error'] = "Invalid credentials or not an admin.";
            header("Location: ../login.php");
            exit;
        }
    } catch (PDOException $e) {
        error_log("Admin Login Error: " . $e->getMessage());
        $_SESSION['admin_error'] = "System error during login.";
        header("Location: ../login.php");
        exit;
    }
} else {
    header("Location: ../login.php");
    exit;
}
?>