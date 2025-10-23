<?php
session_start();
// Bảo vệ trang: chỉ client mới vào được
if (!isset($_SESSION['client_logged_in']) || $_SESSION['client_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
// Giả định file db.php nằm ở ../../includes/db.php
require_once('../../includes/db.php');

$clientId = $_SESSION['client_id'];
$companyName = $_SESSION['client_company_name']; // Giả định session này được set khi login

$message = $_SESSION['survey_create_message'] ?? '';
$is_error = $_SESSION['survey_create_error'] ?? false;
unset($_SESSION['survey_create_message'], $_SESSION['survey_create_error']);

$survey_id_to_edit = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : null;
$survey_data = null;
$questions_data = []; // Mảng chứa dữ liệu câu hỏi và options để JS dùng

if ($survey_id_to_edit) {
    // --- Bắt đầu phần FIX: Lấy dữ liệu để Edit ---
    try {
        // Lấy thông tin survey cơ bản
        $stmtSurvey = $pdo->prepare("SELECT * FROM surveys WHERE survey_id = ? AND client_id = ? AND (status = 'draft' OR status = 'rejected')");
        $stmtSurvey->execute([$survey_id_to_edit, $clientId]);
        $survey_data = $stmtSurvey->fetch();

        if (!$survey_data) {
            // Không tìm thấy survey hoặc không có quyền sửa, hoặc status không cho phép sửa
            $_SESSION['client_message'] = "Survey not found, cannot be edited, or you don't have permission.";
            $_SESSION['client_error'] = true;
            header('Location: dashboard.php');
            exit;
        }

        // Lấy questions và options liên quan
        $stmtQuestions = $pdo->prepare("SELECT * FROM questions WHERE survey_id = ? ORDER BY question_id ASC");
        $stmtQuestions->execute([$survey_id_to_edit]);
        $questions = $stmtQuestions->fetchAll();

        $stmtOptions = $pdo->prepare("SELECT * FROM options WHERE question_id = ? ORDER BY option_id ASC");

        foreach ($questions as $q) {
            $q_item = $q; // Sao chép dữ liệu câu hỏi
            $q_item['options'] = []; // Khởi tạo mảng options cho câu hỏi này
            if ($q['question_type'] == 'single_choice' || $q['question_type'] == 'multiple_choice') {
                $stmtOptions->execute([$q['question_id']]);
                $q_item['options'] = $stmtOptions->fetchAll(); // Lấy tất cả options
            }
            $questions_data[] = $q_item; // Thêm câu hỏi (cùng options) vào mảng
        }

    } catch (PDOException $e) {
        error_log("Edit Survey Load Error: " . $e->getMessage());
        $_SESSION['client_message'] = "Error loading survey data for editing.";
        $_SESSION['client_error'] = true;
        header('Location: dashboard.php');
        exit;
    }
    // --- Kết thúc phần FIX ---
}

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $survey_id_to_edit ? 'Edit' : 'Create New'; ?> Survey</title>
    <style>
        /* CSS giữ nguyên như bạn đã cung cấp */
        body { font-family: sans-serif; margin: 0; }
        .admin-header { background-color: #2c3e50; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .admin-header h1 { margin: 0; font-size: 1.5em; }
        .admin-header a { color: #ecf0f1; text-decoration: none; margin-left: 15px;}
        .admin-container { padding: 20px; max-width: 900px; margin: auto; }
        .form-section { background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #eee; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold;}
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea,
        .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;}
        textarea { resize: vertical; min-height: 80px;}
        button { padding: 10px 15px; cursor: pointer; border-radius: 4px; border: none; font-size: 1em;}
        .btn-primary { background-color: #27ae60; color: white; }
        .btn-secondary { background-color: #3498db; color: white; margin-right: 5px;}
        .btn-danger { background-color: #e74c3c; color: white; font-size: 0.8em; padding: 3px 6px;}
        #questions-container .question-block { border: 1px dashed #ccc; padding: 15px; margin-bottom: 15px; border-radius: 5px; background: #fff;} /* Sửa ID */
        .question-block h4 { margin-top: 0; display: flex; justify-content: space-between; align-items: center; }
        .options-list { margin-top: 10px; padding-left: 20px; }
        .option-item { display: flex; align-items: center; margin-bottom: 5px; }
        .option-item input { flex-grow: 1; margin-right: 5px;}
        .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1><?php echo $survey_id_to_edit ? 'Edit' : 'Create New'; ?> Survey</h1>
        <div>
            <a href="dashboard.php">Back to Dashboard</a>
            <a href="actions/handle_client_logout.php">Logout</a> </div>
    </header>
    <div class="admin-container">
        <?php if ($message): ?>
            <p class="message <?php echo $is_error ? 'error' : 'success'; ?>"><?php echo $message; ?></p>
        <?php endif; ?>

        <form action="actions/handle_save_survey.php" method="POST" id="survey-form">
            <?php if ($survey_id_to_edit): ?>
                <input type="hidden" name="survey_id" value="<?php echo $survey_id_to_edit; ?>">
            <?php endif; ?>

            <div class="form-section">
                <h3>Survey Details</h3>
                <div class="form-group">
                    <label for="title">Survey Title:</label>
                    <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($survey_data['title'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="description">Description (Optional):</label>
                    <textarea id="description" name="description"><?php echo htmlspecialchars($survey_data['description'] ?? ''); ?></textarea>
                </div>
                 <div class="form-group">
                    <label for="points_reward">Points Reward per Completion:</label>
                    <input type="number" id="points_reward" name="points_reward" required min="1" value="<?php echo htmlspecialchars($survey_data['points_reward'] ?? 10); ?>">
                </div>
                 <div class="form-group">
                    <label for="category">Category (Optional):</label>
                    <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($survey_data['category'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-section">
                <h3>Questions</h3>
                <div id="questions-container">
                    </div>
                <div style="margin-top: 15px;">
                    <button type="button" class="btn-secondary" onclick="addQuestion('single_choice')">Add Single Choice</button>
                    <button type="button" class="btn-secondary" onclick="addQuestion('multiple_choice')">Add Multiple Choice</button>
                    <button type="button" class="btn-secondary" onclick="addQuestion('text_input')">Add Text Input</button>
                </div>
            </div>

            <button type="submit" class="btn-primary" name="save_action" value="save_draft">Save as Draft</button>
             <p style="margin-top: 15px; color: #555;">Saving as draft allows you to edit later. Once submitted for approval, you cannot edit unless rejected by the admin.</p>
        </form>
    </div>

<script>
    let questionCounter = 0; // Luôn bắt đầu từ 0, JS sẽ tăng khi thêm mới
    const questionsContainer = document.getElementById('questions-container'); // Định nghĩa ở ngoài để dễ truy cập

    function addQuestion(type, qData = null, isExisting = false) {
        // qData là dữ liệu câu hỏi khi load (chế độ edit)
        // isExisting đánh dấu đây là câu hỏi đã có ID
        const currentCounter = isExisting && qData && qData.question_id ? `existing_${qData.question_id}` : ++questionCounter;
        const questionBlock = document.createElement('div');
        questionBlock.className = 'question-block';
        questionBlock.id = `q_${currentCounter}`;
        questionBlock.dataset.type = type;

        let optionsHTML = '';
        if (type === 'single_choice' || type === 'multiple_choice') {
            optionsHTML = `<div class="options-list" id="options_${currentCounter}">`;
            // Render options đã có (nếu edit)
            if (qData && qData.options && qData.options.length > 0) {
                 qData.options.forEach((optData, optIndex) => {
                     optionsHTML += createOptionHTML(currentCounter, optData, true); // true = existing option
                 });
            } else if (!isExisting) { // Chỉ thêm option mặc định cho câu hỏi mới
                 optionsHTML += createOptionHTML(currentCounter, null, false); // Option 1
                 optionsHTML += createOptionHTML(currentCounter, null, false); // Option 2
            }
            optionsHTML += `</div><button type="button" style="font-size: 0.9em; margin-top: 5px;" onclick="addOption('${currentCounter}')">Add Option</button>`;
        }

        questionBlock.innerHTML = `
            <h4>
                <span>Question (${type.replace('_', ' ')})</span>
                <button type="button" class="btn-danger" onclick="removeQuestion('q_${currentCounter}')">Remove Question</button>
            </h4>
            <input type="hidden" name="questions[${currentCounter}][type]" value="${type}">
            ${isExisting ? `<input type="hidden" name="questions[${currentCounter}][question_id]" value="${qData.question_id}">` : ''}
            <div class="form-group">
                <label>Question Text:</label>
                <textarea name="questions[${currentCounter}][text]" required>${isExisting ? escapeHtml(qData.question_text) : ''}</textarea>
            </div>
            ${optionsHTML}
        `;
        questionsContainer.appendChild(questionBlock);
    }

    // Hàm tạo HTML cho một option
    function createOptionHTML(questionKey, optData = null, isExisting = false) {
        const optionKey = isExisting && optData && optData.option_id ? `existing_${optData.option_id}` : `new_${Date.now()}_${Math.random()}`; // Đảm bảo key duy nhất cho option mới
        return `
            <div class="option-item">
                ${isExisting ? `<input type="hidden" name="questions[${questionKey}][options][${optionKey}][option_id]" value="${optData.option_id}">` : ''}
                <input type="text" name="questions[${questionKey}][options][${optionKey}][text]" placeholder="${isExisting ? '' : 'New Option'}" value="${isExisting ? escapeHtml(optData.option_text) : ''}" required>
                <button type="button" class="btn-danger" onclick="removeOption(this)">X</button>
            </div>
        `;
    }


    function addOption(questionKey) {
        const optionsList = document.getElementById(`options_${questionKey}`);
        if(optionsList){
             const newOptionHTML = createOptionHTML(questionKey, null, false); // false = new option
             optionsList.insertAdjacentHTML('beforeend', newOptionHTML);
        }
    }

    function removeQuestion(questionBlockId) {
        if (confirm('Are you sure you want to remove this question and its options?')) {
            const questionBlock = document.getElementById(questionBlockId);
            if (questionBlock) {
                // Nếu muốn xử lý xóa mềm (đánh dấu để PHP xóa sau), thêm input hidden đánh dấu xóa
                // Ví dụ: questionBlock.innerHTML += '<input type="hidden" name="questions[...][delete]" value="1">';
                // Hoặc xóa trực tiếp khỏi DOM:
                questionBlock.remove();
            }
        }
    }

     function removeOption(button) {
        const optionItem = button.closest('.option-item');
        const optionsList = optionItem.closest('.options-list');
        if (optionsList.querySelectorAll('.option-item').length > 1) {
            // Tương tự removeQuestion, có thể đánh dấu xóa mềm hoặc xóa khỏi DOM
            optionItem.remove();
        } else {
            alert('A choice question must have at least two options.'); // Nên yêu cầu ít nhất 2 options
        }
    }

    // --- FIX: Logic render câu hỏi khi Edit ---
    document.addEventListener('DOMContentLoaded', () => {
        <?php if ($survey_id_to_edit && !empty($questions_data)): ?>
            const existingQuestions = <?php echo json_encode($questions_data); ?>;
            existingQuestions.forEach((qData) => {
                // Gọi hàm addQuestion với dữ liệu và cờ isExisting = true
                addQuestion(qData.question_type, qData, true);
            });
        <?php endif; ?>
    });

    // Hàm helper escapeHtml
    function escapeHtml(unsafe) {
        if (typeof unsafe !== 'string') return '';
        return unsafe
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

</script>

</body>
</html>