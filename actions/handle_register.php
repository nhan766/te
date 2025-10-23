<?php
session_start();
require_once('../includes/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // --- Validations ---
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error_message'] = "Vui lòng điền đầy đủ thông tin.";
        $_SESSION['register_error'] = true; // Để active tab đăng ký
        header("Location: ../login.php");
        exit;
    }
    if ($password !== $confirm_password) {
        $_SESSION['error_message'] = "Mật khẩu xác nhận không khớp.";
        $_SESSION['register_error'] = true;
        header("Location: ../login.php");
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Địa chỉ email không hợp lệ.";
        $_SESSION['register_error'] = true;
        header("Location: ../login.php");
        exit;
    }
    // (Thêm các validation khác: độ dài username/password, ký tự đặc biệt...)

    try {
        // Kiểm tra username hoặc email đã tồn tại chưa
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $_SESSION['error_message'] = "Tên đăng nhập hoặc Email đã được sử dụng.";
            $_SESSION['register_error'] = true;
            header("Location: ../login.php");
            exit;
        }

        // Băm mật khẩu
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Chèn vào CSDL
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        if ($stmt->execute([$username, $email, $password_hash])) {
            $_SESSION['success_message'] = "Đăng ký thành công! Vui lòng đăng nhập.";
            header("Location: ../login.php"); // Chuyển về trang đăng nhập với thông báo thành công
            exit;
        } else {
            $_SESSION['error_message'] = "Đăng ký thất bại. Vui lòng thử lại.";
            $_SESSION['register_error'] = true;
            header("Location: ../login.php");
            exit;
        }

    } catch (PDOException $e) {
        error_log("Register Error: " . $e->getMessage());
        $_SESSION['error_message'] = "Đã xảy ra lỗi hệ thống. Vui lòng thử lại sau.";
        $_SESSION['register_error'] = true;
        header("Location: ../login.php");
        exit;
    }

} else {
    header("Location: ../login.php");
    exit;
}
?>