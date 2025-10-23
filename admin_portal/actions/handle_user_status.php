<?php
session_start();
require_once('../../includes/db.php');

// Chỉ admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php'); exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'], $_POST['action'])) {
    $userId = (int)$_POST['user_id'];
    $action = $_POST['action']; // 'ban' or 'unban'
    $newStatus = null;

    if ($action === 'ban') $newStatus = 0; // is_active = 0
    elseif ($action === 'unban') $newStatus = 1; // is_active = 1

    if ($newStatus !== null && $userId > 0) {
         // Không cho ban admin khác (nếu cần)
        $checkAdmin = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
        $checkAdmin->execute([$userId]);
        if($checkAdmin->fetchColumn()){
            $_SESSION['user_manage_message'] = "Cannot change status of an admin account.";
            $_SESSION['user_manage_error'] = true;
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ? AND is_admin = FALSE");
                if ($stmt->execute([$newStatus, $userId]) && $stmt->rowCount() > 0) {
                    $_SESSION['user_manage_message'] = "User status updated successfully.";
                } else {
                    $_SESSION['user_manage_message'] = "Failed to update user status or user not found/is admin.";
                    $_SESSION['user_manage_error'] = true;
                }
            } catch (PDOException $e) {
                error_log("User Status Update Error: " . $e->getMessage());
                $_SESSION['user_manage_message'] = "Database error during status update.";
                $_SESSION['user_manage_error'] = true;
            }
        }
    } else {
        $_SESSION['user_manage_message'] = "Invalid request.";
        $_SESSION['user_manage_error'] = true;
    }
} else {
    $_SESSION['user_manage_message'] = "Invalid request method.";
    $_SESSION['user_manage_error'] = true;
}

header('Location: ../manage_users.php'); // Quay lại trang quản lý users
exit;
?>