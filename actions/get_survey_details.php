<?php
session_start();
require_once('../includes/db.php');
header('Content-Type: application/json'); // Quan trọng: báo cho JS biết đây là JSON

$response = ['error' => 'Invalid request'];

if (!isset($_SESSION['user_id'])) {
    $response['error'] = 'Bạn cần đăng nhập để làm khảo sát.';
    echo json_encode($response);
    exit;
}

if (isset($_GET['id'])) {
    $surveyId = (int)$_GET['id'];

    try {
        // Lấy thông tin cơ bản của khảo sát
        $stmt = $pdo->prepare("SELECT survey_id, title, points_reward FROM surveys WHERE survey_id = ? AND status = 'published'");
        $stmt->execute([$surveyId]);
        $survey = $stmt->fetch();

        if ($survey) {
            $surveyData = [
                'id' => $survey['survey_id'],
                'title' => $survey['title'],
                'points' => $survey['points_reward'],
                'questions' => []
            ];

            // Lấy các câu hỏi
            $qStmt = $pdo->prepare("SELECT question_id, question_text, question_type FROM questions WHERE survey_id = ? ORDER BY question_id ASC"); // Sắp xếp theo thứ tự
            $qStmt->execute([$surveyId]);
            $questions = $qStmt->fetchAll();

            // Lấy các options cho từng câu hỏi (nếu có)
            $oStmt = $pdo->prepare("SELECT option_id, option_text FROM options WHERE question_id = ? ORDER BY option_id ASC");

            foreach ($questions as $q) {
                $questionItem = [
                    'id' => $q['question_id'],
                    'text' => $q['question_text'],
                    'type' => $q['question_type'], // single_choice, multiple_choice, text_input
                    'options' => []
                ];

                if ($q['question_type'] == 'single_choice' || $q['question_type'] == 'multiple_choice') {
                    $oStmt->execute([$q['question_id']]);
                    $options = $oStmt->fetchAll();
                    foreach ($options as $opt) {
                        $questionItem['options'][] = [
                            'id' => $opt['option_id'],
                            'text' => $opt['option_text']
                        ];
                    }
                }
                $surveyData['questions'][] = $questionItem;
            }

            echo json_encode($surveyData); // Trả về dữ liệu thành công
            exit;

        } else {
            $response['error'] = 'Không tìm thấy khảo sát hoặc khảo sát chưa được công bố.';
        }

    } catch (PDOException $e) {
        error_log("Error getting survey details: " . $e->getMessage());
        $response['error'] = 'Lỗi máy chủ khi tải chi tiết khảo sát.';
    }
}

echo json_encode($response); // Trả về lỗi nếu có
?>