<?php
session_start();
require_once('../../includes/db.php');

// Chỉ admin mới được thực hiện
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['approval_message'] = "Unauthorized action.";
    $_SESSION['approval_error'] = true;
    header('Location: ../approve_surveys.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['survey_id']) && isset($_POST['action'])) {
    $surveyId = (int)$_POST['survey_id'];
    $action = $_POST['action']; // 'approve' or 'reject'
    $newStatus = '';

    if ($action === 'approve') {
        $newStatus = 'published';
    } elseif ($action === 'reject') {
        $newStatus = 'rejected';
    } else {
        $_SESSION['approval_message'] = "Invalid action.";
        $_SESSION['approval_error'] = true;
        header('Location: ../approve_surveys.php');
        exit;
    }

    try {
        // Chỉ cập nhật nếu status hiện tại là pending_approval
        $stmt = $pdo->prepare("UPDATE surveys SET status = ? WHERE survey_id = ? AND status = 'pending_approval'");
        $result = $stmt->execute([$newStatus, $surveyId]);

        if ($result && $stmt->rowCount() > 0) { // rowCount() > 0 đảm bảo có dòng bị ảnh hưởng
            $_SESSION['approval_message'] = "Survey successfully " . ($newStatus == 'published' ? 'approved.' : 'rejected.');
        } else if ($result && $stmt->rowCount() == 0) {
             $_SESSION['approval_message'] = "Survey status might have already been changed.";
             $_SESSION['approval_error'] = true; // Coi như lỗi vì không đúng trạng thái mong đợi
        }
         else {
            $_SESSION['approval_message'] = "Failed to update survey status.";
            $_SESSION['approval_error'] = true;
        }

    } catch (PDOException $e) {
        error_log("Approval Error: " . $e->getMessage());
        $_SESSION['approval_message'] = "Database error during approval.";
        $_SESSION['approval_error'] = true;
    }

} else {
    $_SESSION['approval_message'] = "Invalid request.";
    $_SESSION['approval_error'] = true;
}

header('Location: ../approve_surveys.php');
exit;
?>