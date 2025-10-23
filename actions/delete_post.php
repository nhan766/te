<?php
session_start();
require_once('../includes/db.php');

// Yêu cầu đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_id'])) {
    $postId = (int)$_POST['post_id'];
    $userId = $_SESSION['user_id'];
    $isAdmin = false; // Biến kiểm tra quyền admin (nếu admin cũng được xóa)

    // Lấy thông tin user hiện tại (để kiểm tra admin) - Tùy chọn
    // $stmtAdmin = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
    // $stmtAdmin->execute([$userId]);
    // $isAdmin = $stmtAdmin->fetchColumn();

    $pdo->beginTransaction();
    try {
        // Lấy user_id của bài viết để kiểm tra quyền sở hữu
        $stmt = $pdo->prepare("SELECT user_id FROM forum_posts WHERE id = ?");
        $stmt->execute([$postId]);
        $postOwnerId = $stmt->fetchColumn();

        if ($postOwnerId === false) {
            throw new Exception("Bài viết không tồn tại.");
        }

        // Kiểm tra quyền xóa: Hoặc là chủ bài viết, hoặc là admin
        if ($postOwnerId == $userId || $isAdmin) {
            // TODO: Xóa các comment liên quan đến bài viết trước (nếu có bảng comments)
            // $deleteComments = $pdo->prepare("DELETE FROM forum_comments WHERE post_id = ?");
            // $deleteComments->execute([$postId]);

            // Xóa bài viết
            $deletePost = $pdo->prepare("DELETE FROM forum_posts WHERE id = ?");
            $deletePost->execute([$postId]);

            $pdo->commit();
            $_SESSION['forum_message'] = "Đã xóa bài viết thành công.";

        } else {
            // Không có quyền xóa
            $pdo->rollBack(); // Không cần thiết vì chưa thay đổi gì
            throw new Exception("Bạn không có quyền xóa bài viết này.");
        }

    } catch (Exception $e) {
        $pdo->rollBack(); // Rollback nếu có lỗi
        error_log("Delete Post Error: " . $e->getMessage());
        $_SESSION['forum_message'] = $e->getMessage(); // Hiển thị lỗi
        $_SESSION['forum_error'] = true;
    }

    header('Location: ../diendan.php'); // Quay lại diễn đàn
    exit;

} else {
    header('Location: ../diendan.php'); // Không cho truy cập trực tiếp
    exit;
}
?>