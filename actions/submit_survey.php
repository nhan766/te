<?php
session_start();
require_once('../includes/db.php');
header('Content-Type: application/json'); // Luôn trả về JSON

$response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Bạn cần đăng nhập để gửi khảo sát.';
    echo json_encode($response);
    exit;
}

// Lấy dữ liệu từ POST (không phải JSON nữa vì submit form trực tiếp)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['survey_id']) && isset($_POST['responses'])) {
    $userId = $_SESSION['user_id'];
    $surveyId = (int)$_POST['survey_id'];
    $responsesInput = $_POST['responses']; // Dạng mảng từ form name="responses[qid][key]"

    // --- Validation cơ bản ---
    if (!is_array($responsesInput)) {
         $_SESSION['submit_survey_message'] = "Dữ liệu trả lời không hợp lệ.";
         $_SESSION['submit_survey_error'] = true;
         header('Location: ../take_survey.php?id=' . $surveyId);
         exit;
    }
     // TODO: Kiểm tra kỹ hơn xem tất cả câu hỏi bắt buộc đã được trả lời chưa (dựa vào danh sách câu hỏi từ CSDL)


    // --- Bắt đầu Transaction ---
    $pdo->beginTransaction();
    try {
        // 1. Kiểm tra lại xem user đã làm khảo sát này chưa
        $stmtCompleted = $pdo->prepare("SELECT COUNT(*) FROM user_completed_surveys WHERE user_id = ? AND survey_id = ?");
        $stmtCompleted->execute([$userId, $surveyId]);
        if ($stmtCompleted->fetchColumn() > 0) {
             throw new Exception("Bạn đã hoàn thành khảo sát này rồi.");
        }

        // 2. Lấy thông tin khảo sát (điểm thưởng, title)
        $stmtSurvey = $pdo->prepare("SELECT title, points_reward FROM surveys WHERE survey_id = ? AND status = 'published'");
        $stmtSurvey->execute([$surveyId]);
        $surveyInfo = $stmtSurvey->fetch();
        if (!$surveyInfo) {
            throw new Exception("Khảo sát không hợp lệ hoặc không tồn tại.");
        }
        $pointsReward = (int)$surveyInfo['points_reward'];
        $surveyTitle = $surveyInfo['title'];


        // 3. Chuẩn bị câu lệnh INSERT response
        $insertResponseStmt = $pdo->prepare(
            "INSERT INTO user_responses (user_id, question_id, selected_option_id, answer_text) VALUES (:user_id, :question_id, :option_id, :answer_text)"
        );

        // 4. Lặp qua các câu trả lời gửi lên và lưu vào CSDL
        foreach ($responsesInput as $questionId => $answerData) {
            $questionId = (int)$questionId;
            $optionId = null;
            $optionIds = [];
            $answerText = null;

            if (isset($answerData['option_id'])) { // Single choice
                 $optionId = (int)$answerData['option_id'];
                 $insertResponseStmt->execute([
                     ':user_id' => $userId, ':question_id' => $questionId,
                     ':option_id' => $optionId, ':answer_text' => null
                 ]);
            } elseif (isset($answerData['option_ids']) && is_array($answerData['option_ids'])) { // Multiple choice
                $optionIds = $answerData['option_ids'];
                 foreach ($optionIds as $optId) {
                     if (!empty($optId)) { // Bỏ qua giá trị rỗng nếu có
                         $insertResponseStmt->execute([
                             ':user_id' => $userId, ':question_id' => $questionId,
                             ':option_id' => (int)$optId, ':answer_text' => null
                         ]);
                     }
                 }
            } elseif (isset($answerData['text'])) { // Text input
                $answerText = trim($answerData['text']);
                if (!empty($answerText)) {
                    $insertResponseStmt->execute([
                         ':user_id' => $userId, ':question_id' => $questionId,
                         ':option_id' => null, ':answer_text' => $answerText
                    ]);
                }
            }
             // Bỏ qua nếu không có key nào khớp (có thể là câu hỏi không bắt buộc bị bỏ qua)
        }

        // 5. Cộng điểm cho người dùng
        $updatePointsStmt = $pdo->prepare("UPDATE users SET points = points + ? WHERE id = ?");
        $updatePointsStmt->execute([$pointsReward, $userId]);

        // 6. Ghi nhận đã hoàn thành khảo sát
        $markCompletedStmt = $pdo->prepare("INSERT INTO user_completed_surveys (user_id, survey_id) VALUES (?, ?)");
        $markCompletedStmt->execute([$userId, $surveyId]);

        // 7. Ghi log hoạt động
        $activityDesc = "Hoàn thành khảo sát: \"" . $surveyTitle . "\"";
        $logActivity = $pdo->prepare("INSERT INTO user_activities (user_id, activity_description, points_change) VALUES (?, ?, ?)");
        $logActivity->execute([$userId, $activityDesc, $pointsReward]);

        // Commit transaction
        $pdo->commit();

        // Chuẩn bị thông báo thành công cho trang khaosat.php
        $_SESSION['success_message'] = "Bạn đã hoàn thành khảo sát \"".htmlspecialchars($surveyTitle)."\" và nhận được +".$pointsReward." điểm!";
        header('Location: ../khaosat.php'); // Chuyển hướng về danh sách khảo sát
        exit;


    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Submit Survey Error: " . $e->getMessage());
        // Lưu lỗi vào session để hiển thị trên trang take_survey
        $_SESSION['submit_survey_message'] = "Lỗi khi gửi khảo sát: " . $e->getMessage();
        $_SESSION['submit_survey_error'] = true;
        header('Location: ../take_survey.php?id=' . $surveyId); // Quay lại trang làm khảo sát
        exit;
    }

} else {
     // Nếu không phải POST hoặc thiếu dữ liệu -> quay về trang khảo sát
     $_SESSION['error_message'] = "Yêu cầu không hợp lệ.";
     header('Location: ../khaosat.php');
     exit;
}
?>