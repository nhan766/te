<?php
$page_title = "Danh sách khảo sát";
require_once('includes/header.php');
// Trang này yêu cầu đăng nhập
if (!$current_user) {
    header('Location: login.php?redirect=khaosat.php'); // Lưu lại trang muốn vào để redirect sau khi login
    exit;
}

// Lấy tham số lọc và sắp xếp từ URL
$category = $_GET['category'] ?? 'all';
$sort = $_GET['sort'] ?? 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Số khảo sát mỗi trang
$offset = ($page - 1) * $limit;

// Xây dựng câu truy vấn
$sql = "SELECT survey_id, client_id, title, description, points_reward, status, created_at FROM surveys WHERE status = 'published'"; // Chỉ lấy khảo sát đã duyệt
$countSql = "SELECT COUNT(*) FROM surveys WHERE status = 'published'";
$params = [];

if ($category != 'all') {
    $sql .= " AND category = ?"; // Giả sử có cột category trong bảng surveys
    $countSql .= " AND category = ?";
    $params[] = $category;
}

// Thêm ORDER BY
$orderBy = " ORDER BY created_at DESC"; // Mặc định mới nhất
if ($sort == 'highest-point') $orderBy = " ORDER BY points_reward DESC";
// if ($sort == 'shortest') $orderBy = " ORDER BY time_estimate ASC"; // Nếu có cột time_estimate

$sql .= $orderBy . " LIMIT ? OFFSET ?";

// Lấy tổng số khảo sát để phân trang
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalSurveys = $countStmt->fetchColumn();
$totalPages = ceil($totalSurveys / $limit);

// Thêm limit và offset vào params cho câu query chính
$params[] = $limit;
$params[] = $offset;

// Lấy danh sách khảo sát cho trang hiện tại
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$surveys = $stmt->fetchAll();

?>
<link rel="stylesheet" href="css/khaosat.css">

<section class="surveys-section">
    <div class="container">
        <div class="survey-header">
             <h2>Khảo sát hiện có</h2>
             <p>Tham gia khảo sát và nhận <?php echo $user_points; ?> điểm thưởng ngay</p>
        </div>
        <div class="survey-controls">
            <form method="GET" action="khaosat.php" id="filter-form">
                <div class="filter-group">
                    <label for="survey-category">Lọc theo:</label>
                    <select id="survey-category" name="category" onchange="document.getElementById('filter-form').submit()">
                        <option value="all" <?php if($category == 'all') echo 'selected'; ?>>Tất cả</option>
                        <option value="shopping" <?php if($category == 'shopping') echo 'selected'; ?>>Mua sắm</option>
                         <option value="entertainment" <?php if($category == 'entertainment') echo 'selected'; ?>>Giải trí</option>
                         </select>
                </div>
                <div class="filter-group">
                    <label for="survey-sort">Sắp xếp:</label>
                    <select id="survey-sort" name="sort" onchange="document.getElementById('filter-form').submit()">
                        <option value="newest" <?php if($sort == 'newest') echo 'selected'; ?>>Mới nhất</option>
                        <option value="highest-point" <?php if($sort == 'highest-point') echo 'selected'; ?>>Điểm cao nhất</option>
                        </select>
                </div>
                 <input type="hidden" name="page" value="1">
            </form>
        </div>
        <div class="survey-list">
            <?php if (empty($surveys)): ?>
                <p style="text-align: center; padding: 20px;">Không có khảo sát nào.</p>
            <?php else: ?>
                <?php foreach ($surveys as $survey): ?>
                    <div class="survey-item">
                        <div class="survey-item-info">
                            <h3 class="survey-item-title"><?php echo htmlspecialchars($survey['title']); ?></h3>
                            <p class="survey-item-description"><?php echo htmlspecialchars($survey['description'] ?? 'Không có mô tả'); ?></p>
                        </div>
                        <div class="survey-item-meta">
                            <div class="meta-points">
                                <span class="value"><?php echo $survey['points_reward']; ?></span>
                                <span class="label">Điểm</span>
                            </div>
                            <a href="take_survey.php?id=<?php echo $survey['survey_id']; ?>" class="start-survey-btn">Bắt đầu</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
         <div class="survey-pagination">
             <?php if ($page > 1): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="pagination-btn">←</a>
             <?php else: ?>
                 <button class="pagination-btn" disabled>←</button>
             <?php endif; ?>

             <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                 <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" class="pagination-btn <?php if ($i == $page) echo 'active'; ?>">
                     <?php echo $i; ?>
                 </a>
             <?php endfor; ?>

             <?php if ($page < $totalPages): ?>
                 <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="pagination-btn">→</a>
             <?php else: ?>
                 <button class="pagination-btn" disabled>→</button>
             <?php endif; ?>
        </div>
    </div>
</section>

<script>
    // Bỏ phần dữ liệu cứng allSurveys
    // Bỏ hàm filterAndSortSurveys()

    document.addEventListener('DOMContentLoaded', function() {
        const surveyModal = document.getElementById('survey-modal');
        const completeModal = document.getElementById('survey-complete-modal');
        const questionsContainer = document.getElementById('survey-questions');
        let currentSurveyData = null; // Dữ liệu khảo sát hiện tại (câu hỏi, options)
        let currentQuestionIndex = 0;
        let answers = {}; // Lưu câu trả lời của user

        // --- Hàm để fetch câu hỏi từ server ---
        async function fetchSurveyQuestions(surveyId) {
            try {
                // TODO: Thay URL này bằng endpoint PHP của bạn
                const response = await fetch(`actions/get_survey_details.php?id=${surveyId}`);
                if (!response.ok) throw new Error('Network response was not ok.');
                const data = await response.json();
                if (data.error) throw new Error(data.error);
                return data; // Mong đợi trả về { id, title, points, questions: [{id, text, type, options: [{id, text}] }] }
            } catch (error) {
                console.error('Error fetching survey questions:', error);
                alert('Không thể tải câu hỏi khảo sát. Vui lòng thử lại.');
                return null;
            }
        }

        // --- Hàm hiển thị câu hỏi (tương tự như cũ nhưng dùng dữ liệu từ server) ---
        function displayQuestion(isFirstQuestion = false) {
             if (!questionsContainer || !currentSurveyData) return;
             const question = currentSurveyData.questions[currentQuestionIndex];
             if (!question) return; // Không có câu hỏi?

             const updateContent = () => {
                 const progress = ((currentQuestionIndex + 1) / currentSurveyData.questions.length) * 100;
                 surveyModal.querySelector('#survey-progress').style.width = `${progress}%`;
                 surveyModal.querySelector('#progress-text').textContent = `${currentQuestionIndex + 1}/${currentSurveyData.questions.length}`;
                 questionsContainer.innerHTML = '';
                 const questionElement = document.createElement('div');
                 questionElement.className = 'survey-question';
                 questionElement.dataset.questionId = question.id; // Lưu ID câu hỏi

                 let optionsHTML = '';
                 if (question.type === 'single_choice' || question.type === 'multiple_choice') {
                     optionsHTML = question.options.map((option, i) => {
                         const optionId = `option-${question.id}-${option.id}`; // Dùng ID từ CSDL
                         const inputType = question.type === 'multiple_choice' ? 'checkbox' : 'radio';
                         const inputName = `question_${question.id}`; // Name theo question ID
                         // value lưu option_id
                         return `<label class="option-item" for="${optionId}">
                                     <input type="${inputType}" id="${optionId}" name="${inputName}${question.type === 'multiple_choice' ? '[]' : ''}" value="${option.id}">
                                     <span class="option-text">${option.text}</span>
                                 </label>`;
                     }).join('');
                 } else if (question.type === 'text_input') {
                      optionsHTML = `<textarea name="question_${question.id}" rows="4" placeholder="Nhập câu trả lời của bạn..." class="text-answer-input"></textarea>`;
                 }


                 questionElement.innerHTML = `<h3 class="survey-question-title">${question.text}</h3><div class="survey-options">${optionsHTML}</div>`;
                 questionsContainer.appendChild(questionElement);
                 questionsContainer.classList.remove('fade-out');
                 updateNavButtons();
             };

             if (isFirstQuestion) {
                 updateContent();
             } else {
                 questionsContainer.classList.add('fade-out');
                 setTimeout(updateContent, 300);
             }
        }

        function updateNavButtons() { /* Giữ nguyên như cũ */ }

        // --- Hàm lưu câu trả lời (lưu theo question_id và option_id/text) ---
        function saveCurrentAnswer() {
            if (!currentSurveyData) return;
            const questionDiv = questionsContainer.querySelector('.survey-question');
            if (!questionDiv) return;

            const questionId = questionDiv.dataset.questionId;
            const questionData = currentSurveyData.questions.find(q => q.id == questionId);
            if (!questionData) return;

            if (questionData.type === 'single_choice') {
                const checkedInput = questionsContainer.querySelector(`input[name="question_${questionId}"]:checked`);
                if (checkedInput) {
                    answers[questionId] = { option_id: checkedInput.value };
                }
            } else if (questionData.type === 'multiple_choice') {
                const checkedInputs = questionsContainer.querySelectorAll(`input[name="question_${questionId}[]"]:checked`);
                if (checkedInputs.length > 0) {
                     answers[questionId] = { option_ids: Array.from(checkedInputs).map(input => input.value) };
                } else {
                    delete answers[questionId]; // Xóa nếu bỏ chọn hết
                }
            } else if (questionData.type === 'text_input') {
                const textarea = questionsContainer.querySelector(`textarea[name="question_${questionId}"]`);
                if (textarea && textarea.value.trim() !== '') {
                    answers[questionId] = { text: textarea.value.trim() };
                } else {
                     delete answers[questionId]; // Xóa nếu trống
                }
            }
        }


        // --- Xử lý khi nhấn nút "Bắt đầu" ---
        document.querySelectorAll('.start-survey-btn').forEach(button => {
             button.addEventListener('click', async function(e) {
                e.preventDefault(); // Ngăn chuyển trang nếu là thẻ <a>
                const surveyId = this.dataset.id || this.closest('.survey-item').querySelector('a').href.split('id=')[1];
                if (!surveyId) return;

                // Hiện loading indicator nếu có
                currentSurveyData = await fetchSurveyQuestions(surveyId);

                if (currentSurveyData) {
                    currentQuestionIndex = 0;
                    answers = {};
                    surveyModal.querySelector('#survey-modal-title').textContent = currentSurveyData.title;
                    surveyModal.querySelector('.survey-points').textContent = `+${currentSurveyData.points} điểm`;
                    surveyModal.classList.add('active');
                    displayQuestion(true);
                }
             });
        });


        // --- Xử lý nút Next/Prev/Submit ---
        surveyModal?.querySelector('#prev-question').addEventListener('click', () => { saveCurrentAnswer(); if(currentSurveyData && currentQuestionIndex > 0) { currentQuestionIndex--; displayQuestion(); }});
        surveyModal?.querySelector('#next-question').addEventListener('click', () => { saveCurrentAnswer(); if(currentSurveyData && currentQuestionIndex < currentSurveyData.questions.length - 1) { currentQuestionIndex++; displayQuestion(); }});
        surveyModal?.querySelector('.close-modal').addEventListener('click', () => surveyModal.classList.remove('active'));
        completeModal?.querySelector('.close-modal').addEventListener('click', () => completeModal.classList.remove('active'));

        surveyModal?.querySelector('#submit-survey').addEventListener('click', async () => {
             saveCurrentAnswer();

             // Chuẩn bị dữ liệu gửi đi
             const submissionData = {
                 survey_id: currentSurveyData.id,
                 responses: answers // answers có dạng { question_id: {option_id: X} } hoặc { question_id: {option_ids: [X,Y]} } hoặc { question_id: {text: "..."} }
             };

             // Gửi dữ liệu lên server bằng fetch POST
             try {
                // TODO: Thay URL này bằng endpoint PHP xử lý submit
                 const response = await fetch('actions/submit_survey.php', {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         // Có thể cần thêm header CSRF token nếu dùng
                     },
                     body: JSON.stringify(submissionData)
                 });
                 const result = await response.json();

                 if (result.success) {
                     surveyModal.classList.remove('active');
                     if (completeModal) {
                         completeModal.querySelector('#earned-points').textContent = currentSurveyData.points;
                         completeModal.classList.add('active');
                         // TODO: Cập nhật lại số điểm trên header mà không cần tải lại trang
                         // Ví dụ: document.querySelector('.user-points-display').textContent = result.new_total_points;
                     }
                 } else {
                     alert(result.message || 'Gửi khảo sát thất bại. Vui lòng thử lại.');
                 }
             } catch (error) {
                 console.error('Error submitting survey:', error);
                 alert('Đã xảy ra lỗi khi gửi khảo sát.');
             }
        });

    });
</script>

<?php require_once('includes/footer.php'); ?>