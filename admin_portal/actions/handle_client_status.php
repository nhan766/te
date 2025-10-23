<?php
session_start();
require_once('../../includes/db.php');

// Chỉ admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php'); exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['client_id'], $_POST['action'])) {
    $clientId = (int)$_POST['client_id'];
    $action = $_POST['action']; // 'ban' or 'unban'
    $newStatus = null;

    if ($action === 'ban') $newStatus = 0; // is_active = 0
    elseif ($action === 'unban') $newStatus = 1; // is_active = 1

    if ($newStatus !== null && $clientId > 0) {
        try {
            // Cập nhật trạng thái trong bảng clients
            $stmt = $pdo->prepare("UPDATE clients SET is_active = ? WHERE client_id = ?");
            if ($stmt->execute([$newStatus, $clientId]) && $stmt->rowCount() > 0) {
                $_SESSION['client_manage_message'] = "Client status updated successfully.";
                // Có thể thêm logic ở đây: ví dụ, khi ban client, tự động reject các survey 'pending_approval' của họ?
                /*
                if ($newStatus == 0) {
                    $rejectSurveys = $pdo->prepare("UPDATE surveys SET status = 'rejected' WHERE client_id = ? AND status = 'pending_approval'");
                    $rejectSurveys->execute([$clientId]);
                }
                */
            } else {
                $_SESSION['client_manage_message'] = "Failed to update client status or client not found.";
                $_SESSION['client_manage_error'] = true;
            }
        } catch (PDOException $e) {
            error_log("Client Status Update Error: " . $e->getMessage());
            $_SESSION['client_manage_message'] = "Database error during status update.";
            $_SESSION['client_manage_error'] = true;
        }
    } else {
        $_SESSION['client_manage_message'] = "Invalid request.";
        $_SESSION['client_manage_error'] = true;
    }
} else {
    $_SESSION['client_manage_message'] = "Invalid request method.";
    $_SESSION['client_manage_error'] = true;
}

header('Location: ../manage_clients.php'); // Quay lại trang quản lý clients
exit;
?>