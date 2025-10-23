<?php
session_start();
// Bảo vệ trang: chỉ client mới vào được
if (!isset($_SESSION['client_logged_in']) || $_SESSION['client_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
require_once('../../includes/db.php'); // Điều chỉnh đường dẫn nếu cần

$clientId = $_SESSION['client_id'];
$companyName = $_SESSION['client_company_name'];

$message = $_SESSION['survey_create_message'] ?? '';
$is_error = $_SESSION['survey_create_error'] ?? false;
unset($_SESSION['survey_create_message'], $_SESSION['survey_create_error']);

// Có thể thêm logic để sửa khảo sát (lấy survey_id từ GET và load dữ liệu)
$survey_id_to_edit = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : null;
$survey_data = null;
$questions_data = [];
if ($survey_id_to_edit) {
    // TODO: Lấy thông tin survey và questions/options từ CSDL để điền vào form
    // Đảm bảo chỉ client sở hữu mới được sửa
}

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $survey_id_to_edit ? 'Edit' : 'Create New'; ?> Survey</title>
    <style>
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
        .questions-container .question-block { border: 1px dashed #ccc; padding: 15px; margin-bottom: 15px; border-radius: 5px; background: #fff;}
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
            <a href="actions/handle_client_logout.php">Logout</a>
        </div>
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
                    <?php
               
                    ?>
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
    let questionCounter = <?php echo count($questions_data); ?>; // Bắt đầu từ số câu hỏi đã có (nếu edit)

    function addQuestion(type) {
        questionCounter++;
        const container = document.getElementById('questions-container');
        const questionBlock = document.createElement('div');
        questionBlock.className = 'question-block';
        questionBlock.id = `q_${questionCounter}`;
        questionBlock.dataset.type = type;

        let optionsHTML = '';
        if (type === 'single_choice' || type === 'multiple_choice') {
            optionsHTML = `
                <div class="options-list" id="options_${questionCounter}">
                    <div class="option-item">
                        <input type="text" name="questions[${questionCounter}][options][]" placeholder="Option 1" required>
                        <button type="button" class="btn-danger" onclick="removeOption(this)">X</button>
                    </div>
                     <div class="option-item">
                        <input type="text" name="questions[${questionCounter}][options][]" placeholder="Option 2" required>
                        <button type="button" class="btn-danger" onclick="removeOption(this)">X</button>
                    </div>
                </div>
                <button type="button" style="font-size: 0.9em; margin-top: 5px;" onclick="addOption(${questionCounter})">Add Option</button>
            `;
        }

        questionBlock.innerHTML = `
            <h4>
                <span>Question ${questionCounter} (${type.replace('_', ' ')})</span>
                <button type="button" class="btn-danger" onclick="removeQuestion(${questionCounter})">Remove Question</button>
            </h4>
            <input type="hidden" name="questions[${questionCounter}][type]" value="${type}">
            <div class="form-group">
                <label>Question Text:</label>
                <textarea name="questions[${questionCounter}][text]" required></textarea>
            </div>
            ${optionsHTML}
        `;
        container.appendChild(questionBlock);
    }

    function addOption(questionId) {
        const optionsList = document.getElementById(`options_${questionId}`);
        const optionItem = document.createElement('div');
        optionItem.className = 'option-item';
        optionItem.innerHTML = `
            <input type="text" name="questions[${questionId}][options][]" placeholder="New Option" required>
            <button type="button" class="btn-danger" onclick="removeOption(this)">X</button>
        `;
        optionsList.appendChild(optionItem);
    }

    function removeQuestion(questionId) {
        if (confirm('Are you sure you want to remove this question?')) {
            const questionBlock = document.getElementById(`q_${questionId}`);
            if (questionBlock) {
                questionBlock.remove();
                // Nên cập nhật lại số thứ tự câu hỏi nếu cần, nhưng phức tạp hơn
            }
        }
    }

     function removeOption(button) {
        const optionItem = button.closest('.option-item');
        // Đảm bảo còn ít nhất 1-2 options tùy loại câu hỏi
        const optionsList = optionItem.closest('.options-list');
        if (optionsList.querySelectorAll('.option-item').length > 1) { // Giữ lại ít nhất 1
             optionItem.remove();
        } else {
            alert('A choice question must have at least one option.');
        }
    }

    // TODO: Nếu là edit, dùng JS để điền dữ liệu câu hỏi và options vào các field đã tạo ở PHP
    document.addEventListener('DOMContentLoaded', () => {
        <?php if ($survey_id_to_edit && !empty($questions_data)): ?>
            // Giả sử $questions_data là mảng PHP chứa câu hỏi và options
            const existingQuestions = <?php echo json_encode($questions_data); ?>;
            existingQuestions.forEach((qData, index) => {
                const qId = `q_existing_${index}`; // ID tạm thời
                const container = document.getElementById('questions-container');
                const questionBlock = document.createElement('div');
                questionBlock.className = 'question-block';
                questionBlock.id = qId;
                questionBlock.dataset.type = qData.question_type;

                let optionsHTML = '';
                 if (qData.question_type === 'single_choice' || qData.question_type === 'multiple_choice') {
                    optionsHTML += `<div class="options-list" id="options_${qId}">`;
                    qData.options.forEach((optData, optIndex) => {
                         optionsHTML += `
                         <div class="option-item">
                            <input type="hidden" name="questions[${qId}][options][${optIndex}][option_id]" value="${optData.option_id}">
                            <input type="text" name="questions[${qId}][options][${optIndex}][text]" value="${escapeHtml(optData.option_text)}" required>
                            <button type="button" class="btn-danger" onclick="removeOption(this)">X</button>
                         </div>`;
                    });
                     optionsHTML += `</div><button type="button" style="font-size: 0.9em; margin-top: 5px;" onclick="addOption('${qId}')">Add Option</button>`;
                }

                 questionBlock.innerHTML = `
                    <h4>
                        <span>Question ${index + 1} (${qData.question_type.replace('_', ' ')})</span>
                        <button type="button" class="btn-danger" onclick="removeQuestion('${qId}')">Remove Question</button>
                    </h4>
                    <input type="hidden" name="questions[${qId}][type]" value="${qData.question_type}">
                    <input type="hidden" name="questions[${qId}][question_id]" value="${qData.question_id}">
                    <div class="form-group">
                        <label>Question Text:</label>
                        <textarea name="questions[${qId}][text]" required>${escapeHtml(qData.question_text)}</textarea>
                    </div>
                    ${optionsHTML}
                `;
                container.appendChild(questionBlock);
            });
            // Cập nhật questionCounter để câu hỏi mới thêm vào có ID đúng
             questionCounter = existingQuestions.length;
        <?php endif; ?>
    });

    // Hàm helper để tránh XSS khi điền dữ liệu cũ vào value/textarea
    function escapeHtml(unsafe) {
        if (!unsafe) return '';
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