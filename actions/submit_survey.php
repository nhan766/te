<?php
session_start();
require_once('../includes/db.php');
// Đặt header Content-Type JSON ngay từ đầu
header('Content-Type: application/json; charset=utf-8');

// Khởi tạo $response mặc định
$response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ hoặc dữ liệu không đúng định dạng.'];

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Bạn cần đăng nhập để gửi khảo sát.';
    echo json_encode($response);
    exit;
}

// Chỉ xử lý phương thức POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // === FIX: Đọc và giải mã JSON từ request body ===
    $json_payload = file_get_contents('php://input');
    $input_data = json_decode($json_payload, true); // true để chuyển thành mảng PHP

    // Kiểm tra xem JSON có hợp lệ và chứa đủ key không
    if ($input_data !== null && isset($input_data['survey_id']) && isset($input_data['responses']) && is_array($input_data['responses'])) {
        $userId = $_SESSION['user_id'];
        $surveyId = (int)$input_data['survey_id'];
        $responsesInput = $input_data['responses']; // Dữ liệu responses từ JSON

        // --- Bắt đầu xử lý logic ---
        $pdo->beginTransaction();
        try {
            // 1. Kiểm tra xem đã làm khảo sát chưa
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
                throw new Exception("Khảo sát không hợp lệ, không tồn tại hoặc chưa được công bố.");
            }
            $pointsReward = (int)$surveyInfo['points_reward'];
            $surveyTitle = $surveyInfo['title'];

            // 3. Chuẩn bị câu lệnh INSERT response
            $insertResponseStmt = $pdo->prepare(
                "INSERT INTO user_responses (user_id, survey_id, question_id, selected_option_id, answer_text) VALUES (:user_id, :survey_id, :question_id, :option_id, :answer_text)"
            ); // Thêm survey_id vào insert

            // 4. Lặp qua các câu trả lời gửi lên và lưu vào CSDL
            foreach ($responsesInput as $questionId => $answerData) {
                $questionId = (int)$questionId;
                $optionId = null;
                $optionIds = [];
                $answerText = null;

                 if (isset($answerData['option_id'])) { // Single choice
                     $optionId = (int)$answerData['option_id'];
                     $insertResponseStmt->execute([
                         ':user_id' => $userId, ':survey_id' => $surveyId, ':question_id' => $questionId,
                         ':option_id' => $optionId, ':answer_text' => null
                     ]);
                 } elseif (isset($answerData['option_ids']) && is_array($answerData['option_ids'])) { // Multiple choice
                     $optionIds = $answerData['option_ids'];
                     foreach ($optionIds as $optId) {
                         if (!empty($optId)) {
                             $insertResponseStmt->execute([
                                 ':user_id' => $userId, ':survey_id' => $surveyId, ':question_id' => $questionId,
                                 ':option_id' => (int)$optId, ':answer_text' => null
                             ]);
                         }
                     }
                 } elseif (isset($answerData['text'])) { // Text input
                     $answerText = trim($answerData['text']);
                     if (!empty($answerText)) {
                         $insertResponseStmt->execute([
                              ':user_id' => $userId, ':survey_id' => $surveyId, ':question_id' => $questionId,
                              ':option_id' => null, ':answer_text' => $answerText
                         ]);
                       }
                 }
            }

            // 5. Cộng điểm cho người dùng
            $updatePointsStmt = $pdo->prepare("UPDATE users SET points = points + ? WHERE id = ?");
            $updatePointsStmt->execute([$pointsReward, $userId]);

            // 6. Ghi nhận đã hoàn thành khảo sát (Lưu cả điểm kiếm được)
            $markCompletedStmt = $pdo->prepare("INSERT INTO user_completed_surveys (user_id, survey_id, points_earned) VALUES (?, ?, ?)");
            $markCompletedStmt->execute([$userId, $surveyId, $pointsReward]);

            // 7. Ghi log hoạt động
            $activityDesc = "Hoàn thành khảo sát: \"" . $surveyTitle . "\"";
            $logActivity = $pdo->prepare("INSERT INTO user_activities (user_id, activity_description, points_change) VALUES (?, ?, ?)");
            $logActivity->execute([$userId, $activityDesc, $pointsReward]);

            // Commit transaction
            $pdo->commit();

            // Lấy lại tổng điểm mới để trả về cho JS
            $newPointsStmt = $pdo->prepare("SELECT points FROM users WHERE id = ?");
            $newPointsStmt->execute([$userId]);
            $newTotalPoints = $newPointsStmt->fetchColumn();

            // Gán lại response thành công
            $response = [
                'success' => true,
                'message' => "Nộp khảo sát thành công!",
                'new_total_points' => $newTotalPoints ?? $current_user['points'] + $pointsReward // Trả về điểm mới
            ];

        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Submit Survey Error: " . $e->getMessage());
            // Gán lại response lỗi
            $response['message'] = "Lỗi khi xử lý khảo sát: " . $e->getMessage();
        }
    }
    // else: Giữ nguyên $response lỗi mặc định nếu JSON không hợp lệ hoặc thiếu key
}
// else: Giữ nguyên $response lỗi mặc định nếu không phải POST

// Luôn echo $response ở cuối cùng
echo json_encode($response);
exit; // Kết thúc script sau khi echo JSON
?>