<?php
session_start();
require_once('../includes/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $_SESSION['error_message'] = "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.";
        header("Location: ../login.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]); 
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Đăng nhập thành công
            session_regenerate_id(true); // Bảo mật session fixation
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: ../dashboard.php");
            exit;
        } else {
            $_SESSION['error_message'] = "Tên đăng nhập hoặc mật khẩu không đúng.";
            header("Location: ../login.php");
            exit;
        }
    } catch (PDOException $e) {
        error_log("Login Error: " . $e->getMessage());
        $_SESSION['error_message'] = "Đã xảy ra lỗi hệ thống. Vui lòng thử lại sau.";
        header("Location: ../login.php");
        exit;
    }
} else {
    header("Location: ../login.php"); 
}
?>