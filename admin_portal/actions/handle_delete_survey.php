<?php
session_start();
require_once('../../includes/db.php');

// Chỉ admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php'); exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['survey_id'])) {
    $surveyId = (int)$_POST['survey_id'];
    // Admin có thể xóa bất kỳ survey nào

    $pdo->beginTransaction();
    try {
        // Lấy thông tin survey để hiển thị thông báo
        $stmtInfo = $pdo->prepare("SELECT title FROM surveys WHERE survey_id = ?");
        $stmtInfo->execute([$surveyId]);
        $surveyTitle = $stmtInfo->fetchColumn();

        if ($surveyTitle === false) {
             throw new Exception("Survey not found.");
        }

        // Xóa survey (khóa ngoại `ON DELETE CASCADE` sẽ tự động xóa questions, options, user_responses, user_completed_surveys liên quan)
        $stmtDelete = $pdo->prepare("DELETE FROM surveys WHERE survey_id = ?");
        $deleted = $stmtDelete->execute([$surveyId]);

        if ($deleted && $stmtDelete->rowCount() > 0) {
            $pdo->commit();
            $_SESSION['admin_dashboard_message'] = "Survey '".htmlspecialchars($surveyTitle)."' and all related data have been deleted.";
        } else {
             throw new Exception("Failed to delete survey or survey not found.");
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Delete Survey Error: " . $e->getMessage());
        $_SESSION['admin_dashboard_message'] = "Error deleting survey: " . $e->getMessage();
        $_SESSION['admin_dashboard_error'] = true;
    }

} else {
    $_SESSION['admin_dashboard_message'] = "Invalid request.";
    $_SESSION['admin_dashboard_error'] = true;
}

header('Location: ../dashboard.php'); // Quay về dashboard admin
exit;
?>