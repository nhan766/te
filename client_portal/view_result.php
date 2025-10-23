<?php
session_start();
// Bảo vệ trang
if (!isset($_SESSION['client_logged_in']) || $_SESSION['client_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
require_once('../../includes/db.php');

if (!isset($_GET['id'])) {
    header('Location: dashboard.php'); // Thiếu ID khảo sát
    exit;
}

$clientId = $_SESSION['client_id'];
$surveyId = (int)$_GET['id'];

// --- Lấy thông tin survey và kiểm tra quyền sở hữu ---
$surveyStmt = $pdo->prepare("SELECT survey_id, title, description FROM surveys WHERE survey_id = ? AND client_id = ?");
$surveyStmt->execute([$surveyId, $clientId]);
$survey = $surveyStmt->fetch();

if (!$survey) {
    // Không tìm thấy survey hoặc không phải của client này
    $_SESSION['client_message'] = "Survey not found or access denied.";
    $_SESSION['client_error'] = true;
    header('Location: dashboard.php');
    exit;
}

// --- Lấy tất cả câu hỏi của survey này ---
$qStmt = $pdo->prepare("SELECT question_id, question_text, question_type FROM questions WHERE survey_id = ? ORDER BY question_id ASC");
$qStmt->execute([$surveyId]);
$questions = $qStmt->fetchAll();

// --- Lấy và tổng hợp kết quả ---
$results = [];
$totalRespondents = 0; // Đếm tổng số người đã trả lời (ước lượng)

// Lấy tổng số user duy nhất đã trả lời ít nhất 1 câu hỏi trong survey này
$respCountStmt = $pdo->prepare("SELECT COUNT(DISTINCT ur.user_id)
                                FROM user_responses ur
                                JOIN questions q ON ur.question_id = q.question_id
                                WHERE q.survey_id = ?");
$respCountStmt->execute([$surveyId]);
$totalRespondents = $respCountStmt->fetchColumn();


foreach ($questions as $q) {
    $questionId = $q['question_id'];
    $results[$questionId] = [
        'text' => $q['question_text'],
        'type' => $q['question_type'],
        'data' => [] // Nơi lưu trữ kết quả tổng hợp
    ];

    if ($q['question_type'] == 'single_choice' || $q['question_type'] == 'multiple_choice') {
        // Lấy các options và đếm số lượt chọn
        $optStmt = $pdo->prepare(
            "SELECT o.option_id, o.option_text, COUNT(ur.response_id) as selection_count
             FROM options o
             LEFT JOIN user_responses ur ON o.option_id = ur.selected_option_id AND ur.question_id = :qid
             WHERE o.question_id = :qid2
             GROUP BY o.option_id, o.option_text
             ORDER BY o.option_id ASC"
        );
         $optStmt->execute([':qid' => $questionId, ':qid2' => $questionId]);
        $optionsData = $optStmt->fetchAll();
        $results[$questionId]['data'] = $optionsData;

    } elseif ($q['question_type'] == 'text_input') {
        // Lấy danh sách các câu trả lời dạng text (có thể giới hạn số lượng)
        $textStmt = $pdo->prepare(
            "SELECT answer_text
             FROM user_responses
             WHERE question_id = ? AND answer_text IS NOT NULL AND answer_text != ''
             ORDER BY responded_at DESC
             LIMIT 50" // Giới hạn 50 câu trả lời gần nhất
        );
        $textStmt->execute([$questionId]);
        $textAnswers = $textStmt->fetchAll(PDO::FETCH_COLUMN, 0);
        $results[$questionId]['data'] = $textAnswers;
    }
}

// Chuẩn bị dữ liệu cho Chart.js (ví dụ)
$chartData = [];
foreach ($results as $qid => $res) {
    if ($res['type'] == 'single_choice' || $res['type'] == 'multiple_choice') {
        $labels = [];
        $counts = [];
        foreach ($res['data'] as $option) {
            $labels[] = $option['option_text'];
            $counts[] = $option['selection_count'];
        }
        $chartData[$qid] = [
            'type' => 'pie', // Hoặc 'bar'
            'labels' => $labels,
            'counts' => $counts,
            'questionText' => $res['text']
        ];
    }
}


?>
<!DOCTYPE html>
<html>
<head>
    <title>Survey Results: <?php echo htmlspecialchars($survey['title']); ?></title>
     <style>
        /* CSS tương tự admin dashboard */
         body { font-family: sans-serif; margin: 0; }
         .admin-header { background-color: #2c3e50; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
         .admin-header h1 { margin: 0; font-size: 1.5em; }
         .admin-header a { color: #ecf0f1; text-decoration: none; margin-left: 15px;}
         .admin-container { padding: 20px; max-width: 1000px; margin: auto; }
        .result-block { background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #eee; }
        .result-block h3 { margin-top: 0; border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-bottom: 15px;}
        .text-answers ul { list-style: disc; margin-left: 20px; max-height: 200px; overflow-y: auto; background: #fff; padding: 10px; border: 1px solid #ddd;}
        .chart-container { max-width: 400px; margin: 15px auto; } /* Giới hạn chiều rộng biểu đồ */
    </style>
     <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header class="admin-header">
        <h1>Survey Results</h1>
        <div>
            <a href="dashboard.php">Back to Dashboard</a>
            <a href="actions/handle_client_logout.php">Logout</a>
        </div>
    </header>
    <div class="admin-container">
        <h2><?php echo htmlspecialchars($survey['title']); ?></h2>
        <p>Total Respondents (approx.): <?php echo $totalRespondents; ?></p>
        <hr>

        <?php foreach ($results as $qid => $res): ?>
            <div class="result-block">
                <h3><?php echo htmlspecialchars($res['text']); ?> <span style="font-weight: normal; font-size: 0.8em;">(<?php echo str_replace('_', ' ', $res['type']); ?>)</span></h3>

                <?php if (($res['type'] == 'single_choice' || $res['type'] == 'multiple_choice') && !empty($res['data'])): ?>
                    <div class="chart-container">
                        <canvas id="chart-<?php echo $qid; ?>"></canvas>
                    </div>
                    <ul>
                        <?php foreach ($res['data'] as $option): ?>
                            <li><?php echo htmlspecialchars($option['option_text']); ?>: <?php echo $option['selection_count']; ?> votes</li>
                        <?php endforeach; ?>
                    </ul>
                <?php elseif ($res['type'] == 'text_input'): ?>
                    <div class="text-answers">
                        <?php if (empty($res['data'])): ?>
                            <p>No text answers submitted.</p>
                        <?php else: ?>
                             <p>Showing latest <?php echo count($res['data']); ?> answers:</p>
                            <ul>
                                <?php foreach ($res['data'] as $answer): ?>
                                    <li><?php echo htmlspecialchars($answer); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                 <?php else: ?>
                      <p>No responses recorded for this question yet.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

    </div>

<script>
    // JS để vẽ biểu đồ
    document.addEventListener('DOMContentLoaded', () => {
        const chartData = <?php echo json_encode($chartData); ?>;
        for (const qid in chartData) {
            const ctx = document.getElementById(`chart-${qid}`);
            if (ctx) {
                const data = chartData[qid];
                new Chart(ctx, {
                    type: data.type, // 'pie' or 'bar'
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: '# of Votes',
                            data: data.counts,
                            backgroundColor: [ // Thêm nhiều màu hơn nếu cần
                                'rgba(255, 99, 132, 0.7)',
                                'rgba(54, 162, 235, 0.7)',
                                'rgba(255, 206, 86, 0.7)',
                                'rgba(75, 192, 192, 0.7)',
                                'rgba(153, 102, 255, 0.7)',
                                'rgba(255, 159, 64, 0.7)'
                            ],
                            borderColor: [
                                'rgba(255, 99, 132, 1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(153, 102, 255, 1)',
                                'rgba(255, 159, 64, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: false, // Tiêu đề đã có ở trên
                                text: data.questionText
                            }
                        }
                    }
                });
            }
        }
    });
</script>

</body>
</html>