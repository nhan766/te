<?php
$page_title = "Làm Khảo sát";
require_once('includes/header.php');
if (!$current_user) {
    header('Location: login.php?redirect=khaosat.php'); // Nếu chưa login, quay lại danh sách
    exit;
}
$userId = $current_user['id'];

if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "Không tìm thấy khảo sát.";
    header('Location: khaosat.php');
    exit;
}
$surveyId = (int)$_GET['id'];

try {
    // 1. Lấy thông tin cơ bản khảo sát (kiểm tra tồn tại, published, client...)
    $stmtSurvey = $pdo->prepare(
        "SELECT s.survey_id, s.title, s.description, s.points_reward, c.company_name
         FROM surveys s
         JOIN clients c ON s.client_id = c.client_id
         WHERE s.survey_id = ? AND s.status = 'published'"
    );
    $stmtSurvey->execute([$surveyId]);
    $survey = $stmtSurvey->fetch();

    if (!$survey) {
        $_SESSION['error_message'] = "Khảo sát không tồn tại hoặc chưa sẵn sàng.";
        header('Location: khaosat.php');
        exit;
    }

    // 2. Kiểm tra xem user đã làm khảo sát này chưa (Cần bảng user_completed_surveys)
    $stmtCompleted = $pdo->prepare("SELECT COUNT(*) FROM user_completed_surveys WHERE user_id = ? AND survey_id = ?");
    $stmtCompleted->execute([$userId, $surveyId]);
    if ($stmtCompleted->fetchColumn() > 0) {
         $_SESSION['error_message'] = "Bạn đã hoàn thành khảo sát này trước đây.";
        header('Location: khaosat.php');
        exit;
    }

    // 3. Lấy tất cả câu hỏi và lựa chọn của khảo sát
    $questions_data = []; // Mảng chứa dữ liệu đầy đủ
    $stmtQuestions = $pdo->prepare("SELECT * FROM questions WHERE survey_id = ? ORDER BY question_id ASC");
    $stmtQuestions->execute([$surveyId]);
    $questions = $stmtQuestions->fetchAll();

    $stmtOptions = $pdo->prepare("SELECT * FROM options WHERE question_id = ? ORDER BY option_id ASC");

    foreach ($questions as $q) {
        $q_item = $q;
        $q_item['options'] = [];
        if ($q['question_type'] == 'single_choice' || $q['question_type'] == 'multiple_choice') {
            $stmtOptions->execute([$q['question_id']]);
            $q_item['options'] = $stmtOptions->fetchAll();
        }
        $questions_data[] = $q_item;
    }

    if (empty($questions_data)) {
        $_SESSION['error_message'] = "Khảo sát này hiện không có câu hỏi nào.";
        header('Location: khaosat.php');
        exit;
    }

} catch (PDOException $e) {
    error_log("Take Survey Load Error: " . $e->getMessage());
    $_SESSION['error_message'] = "Lỗi khi tải khảo sát.";
    header('Location: khaosat.php');
    exit;
}
?>

<link rel="stylesheet" href="css/khaosat.css"> <style>
    .survey-taking-container { max-width: 800px; margin: 30px auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    .survey-info { margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee; }
    .survey-info h2 { margin-top: 0; }
    .survey-info p { color: #555; }
    .survey-question-block { margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px dotted #ccc; }
    .survey-question-block:last-child { border-bottom: none; margin-bottom: 0; }
    .survey-question-title { font-size: 1.1em; font-weight: bold; margin-bottom: 15px; }
    .survey-options label { display: block; margin-bottom: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; cursor: pointer; transition: background-color 0.2s; }
    .survey-options label:hover { background-color: #f5f5f5; }
    .survey-options input[type="radio"],
    .survey-options input[type="checkbox"] { margin-right: 10px; }
    .survey-options textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; min-height: 80px; box-sizing: border-box; }
    .submit-button-container { text-align: center; margin-top: 30px; }
    .submit-survey-btn { background-color: #27ae60; color: white; padding: 12px 30px; font-size: 1.1em; border: none; border-radius: 5px; cursor: pointer; }
    .submit-survey-btn:hover { background-color: #2ecc71; }
    .message.error { color: red; text-align: center; margin-bottom: 15px; } /* Cho thông báo lỗi validation */
</style>

<div class="survey-taking-container">
    <div class="survey-info">
        <h2><?php echo htmlspecialchars($survey['title']); ?></h2>
        <p>Thực hiện bởi: <?php echo htmlspecialchars($survey['company_name']); ?></p>
        <p>Phần thưởng: <?php echo $survey['points_reward']; ?> điểm</p>
        <?php if ($survey['description']): ?>
            <p>Mô tả: <?php echo nl2br(htmlspecialchars($survey['description'])); ?></p>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['submit_survey_message'])): ?>
        <p class="message <?php echo isset($_SESSION['submit_survey_error']) ? 'error' : 'success'; ?>">
            <?php echo $_SESSION['submit_survey_message']; ?>
        </p>
        <?php unset($_SESSION['submit_survey_message'], $_SESSION['submit_survey_error']); ?>
    <?php endif; ?>


    <form action="actions/submit_survey.php" method="POST" id="survey-form">
        <input type="hidden" name="survey_id" value="<?php echo $surveyId; ?>">

        <?php foreach ($questions_data as $index => $q):
            $questionId = $q['question_id'];
            $isRequired = true; // Mặc định là bắt buộc, có thể thêm cột is_required vào bảng questions
        ?>
            <div class="survey-question-block">
                <p class="survey-question-title">
                    Câu <?php echo $index + 1; ?>: <?php echo htmlspecialchars($q['question_text']); ?>
                    <?php if ($isRequired): ?><span style="color: red;">*</span><?php endif; ?>
                </p>
                <div class="survey-options">
                    <?php if ($q['question_type'] == 'single_choice'): ?>
                        <?php foreach ($q['options'] as $opt): ?>
                            <label>
                                <input type="radio" name="responses[<?php echo $questionId; ?>][option_id]" value="<?php echo $opt['option_id']; ?>" <?php if ($isRequired) echo 'required'; ?>>
                                <?php echo htmlspecialchars($opt['option_text']); ?>
                            </label>
                        <?php endforeach; ?>
                    <?php elseif ($q['question_type'] == 'multiple_choice'): ?>
                         <?php foreach ($q['options'] as $opt): ?>
                            <label>
                                <input type="checkbox" name="responses[<?php echo $questionId; ?>][option_ids][]" value="<?php echo $opt['option_id']; ?>">
                                <?php echo htmlspecialchars($opt['option_text']); ?>
                            </label>
                        <?php endforeach; ?>
                         <?php elseif ($q['question_type'] == 'text_input'): ?>
                        <textarea name="responses[<?php echo $questionId; ?>][text]" rows="4" placeholder="Nhập câu trả lời..." <?php if ($isRequired) echo 'required'; ?>></textarea>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="submit-button-container">
            <button type="submit" class="submit-survey-btn">Hoàn thành & Gửi khảo sát</button>
        </div>
    </form>
</div>

<script>
    document.getElementById('survey-form').addEventListener('submit', function(event) {
        let isValid = true;
        // Kiểm tra các câu hỏi multiple choice bắt buộc (nếu có)
        const requiredCheckboxes = document.querySelectorAll('input[type="checkbox"][data-required="true"]'); // Thêm data-required="true" nếu cần
        const groupedCheckboxes = {};
        requiredCheckboxes.forEach(cb => {
            if (!groupedCheckboxes[cb.name]) groupedCheckboxes[cb.name] = [];
            groupedCheckboxes[cb.name].push(cb);
        });

        for (const name in groupedCheckboxes) {
            let oneChecked = false;
            groupedCheckboxes[name].forEach(cb => {
                if (cb.checked) oneChecked = true;
            });
            if (!oneChecked) {
                 isValid = false;
                 // Tìm đến block câu hỏi và báo lỗi
                 const questionBlock = groupedCheckboxes[name][0].closest('.survey-question-block');
                 let errorMsg = questionBlock.querySelector('.validation-error');
                 if(!errorMsg) {
                     errorMsg = document.createElement('p');
                     errorMsg.className = 'validation-error';
                     errorMsg.style.color = 'red';
                     errorMsg.textContent = 'Vui lòng chọn ít nhất một phương án.';
                     questionBlock.querySelector('.survey-options').appendChild(errorMsg);
                 }
                 // Cuộn đến lỗi đầu tiên
                 if (isValid === false && errorMsg) {
                    errorMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
                 }
            } else {
                 // Xóa lỗi nếu đã chọn
                 const questionBlock = groupedCheckboxes[name][0].closest('.survey-question-block');
                 const errorMsg = questionBlock.querySelector('.validation-error');
                 if(errorMsg) errorMsg.remove();
            }
        }


        if (!isValid) {
            alert('Vui lòng trả lời tất cả các câu hỏi bắt buộc (*).');
            event.preventDefault(); // Ngăn submit form
        }
        // Thêm validation khác nếu cần
    });
</script>

<?php require_once('includes/footer.php'); ?>