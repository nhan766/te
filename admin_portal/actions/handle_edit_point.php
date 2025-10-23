<?php
session_start();
require_once('../../includes/db.php');

// Chỉ admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php'); exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'], $_POST['new_points'])) {
    $userId = (int)$_POST['user_id'];
    $newPoints = (int)$_POST['new_points'];
    $reason = trim($_POST['reason']) ?: "Manual adjustment by admin";
    $adminUserId = $_SESSION['admin_user_id']; // ID của admin thực hiện

    if ($userId <= 0 || $newPoints < 0) {
        $_SESSION['user_manage_message'] = "Invalid user ID or points value.";
        $_SESSION['user_manage_error'] = true;
        header('Location: ../manage_users.php');
        exit;
    }

    $pdo->beginTransaction();
    try {
        // Lấy điểm hiện tại để tính toán thay đổi
        $stmtOld = $pdo->prepare("SELECT points FROM users WHERE id = ? FOR UPDATE");
        $stmtOld->execute([$userId]);
        $oldPoints = $stmtOld->fetchColumn();

        if ($oldPoints === false) {
             throw new Exception("User not found.");
        }
        $pointsChange = $newPoints - $oldPoints;

        // Cập nhật điểm mới
        $stmtUpdate = $pdo->prepare("UPDATE users SET points = ? WHERE id = ?");
        $stmtUpdate->execute([$newPoints, $userId]);

        // Ghi log hoạt động (quan trọng để theo dõi)
        $activityDesc = $reason . " (by Admin ID: $adminUserId)";
        $logActivity = $pdo->prepare("INSERT INTO user_activities (user_id, activity_description, points_change) VALUES (?, ?, ?)");
        $logActivity->execute([$userId, $activityDesc, $pointsChange]);

        $pdo->commit();
        $_SESSION['user_manage_message'] = "User points updated successfully.";

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Edit Points Error: " . $e->getMessage());
        $_SESSION['user_manage_message'] = "Error updating points: " . $e->getMessage();
        $_SESSION['user_manage_error'] = true;
    }

} else {
    $_SESSION['user_manage_message'] = "Invalid request.";
    $_SESSION['user_manage_error'] = true;
}

header('Location: ../manage_users.php');
exit;
?>