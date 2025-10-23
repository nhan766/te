<?php
session_start();
require_once('../includes/db.php');

if (!isset($_SESSION['user_id'])) {
    // Redirect hoặc báo lỗi chưa đăng nhập
    header('Location: ../login.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $category = $_POST['category'];
    $content = trim($_POST['content']);
    $userId = $_SESSION['user_id'];

    if (empty($title) || empty($content) || empty($category)) {
        // Xử lý lỗi thiếu thông tin
        $_SESSION['error_message'] = "Vui lòng nhập đủ tiêu đề, nội dung và chọn danh mục.";
        header('Location: ../diendan.php'); // Quay lại diễn đàn
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO forum_posts (user_id, title, content, category) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $title, $content, $category]);
        $_SESSION['success_message'] = "Đăng bài thành công!";
        header('Location: ../diendan.php'); // Quay lại diễn đàn
        exit;
    } catch (PDOException $e) {
        error_log("Create Post Error: " . $e->getMessage());
        $_SESSION['error_message'] = "Đã xảy ra lỗi khi đăng bài.";
        header('Location: ../diendan.php');
        exit;
    }
} else {
    header('Location: ../diendan.php');
    exit;
}
?>