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
// --- Sử dụng named placeholders cho rõ ràng và bindValue ---
$sql = "SELECT survey_id, client_id, title, description, points_reward, status, created_at FROM surveys WHERE status = 'published'"; // Chỉ lấy khảo sát đã duyệt
$countSql = "SELECT COUNT(*) FROM surveys WHERE status = 'published'";
$params = [];
$countParams = []; // Tách params cho count query

if ($category != 'all') {
    $sql .= " AND category = :category"; // Giả sử có cột category trong bảng surveys
    $countSql .= " AND category = :category";
    $params[':category'] = $category;
    $countParams[':category'] = $category;
}

// Thêm ORDER BY
$orderBy = " ORDER BY created_at DESC"; // Mặc định mới nhất
if ($sort == 'highest-point') $orderBy = " ORDER BY points_reward DESC";
// if ($sort == 'shortest') $orderBy = " ORDER BY time_estimate ASC"; // Nếu có cột time_estimate

$sql .= $orderBy . " LIMIT :limit OFFSET :offset"; // Dùng placeholder

// Lấy tổng số khảo sát để phân trang
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($countParams); // Execute với params của count
$totalSurveys = $countStmt->fetchColumn();
$totalPages = ceil($totalSurveys / $limit);

// Thêm limit và offset vào params cho câu query chính
$params[':limit'] = $limit;
$params[':offset'] = $offset;

// Lấy danh sách khảo sát cho trang hiện tại
$stmt = $pdo->prepare($sql);
// --- Bind các parameter ---
foreach ($params as $key => $val) {
    // Xác định kiểu dữ liệu
    $paramType = PDO::PARAM_STR; // Mặc định là string
    if ($key === ':limit' || $key === ':offset') {
        $paramType = PDO::PARAM_INT; // LIMIT/OFFSET phải là INT
    }
    $stmt->bindValue($key, $val, $paramType);
}
$stmt->execute();
$surveys = $stmt->fetchAll();

?>
<link rel="stylesheet" href="css/khaosat.css">

<section class="surveys-section">
    <div class="container">
        <div class="survey-header">
             <h2>Khảo sát hiện có</h2>
             <p>Tham gia khảo sát và nhận <?php echo number_format($user_points ?? 0); ?> điểm thưởng ngay</p>
        </div>
        <div class="survey-controls">
            <form method="GET" action="khaosat.php" id="filter-form">
                <div class="filter-group">
                    <label for="survey-category">Lọc theo:</label>
                    <select id="survey-category" name="category" onchange="document.getElementById('filter-form').submit()">
                        <option value="all" <?php if($category == 'all') echo 'selected'; ?>>Tất cả</option>
                        <option value="shopping" <?php if($category == 'shopping') echo 'selected'; ?>>Mua sắm</option>
                        <option value="entertainment" <?php if($category == 'entertainment') echo 'selected'; ?>>Giải trí</option>
                        <option value="technology" <?php if($category == 'technology') echo 'selected'; ?>>Công nghệ</option>
                         <option value="health" <?php if($category == 'health') echo 'selected'; ?>>Sức khỏe</option>
                         <option value="travel" <?php if($category == 'travel') echo 'selected'; ?>>Du lịch</option>
                         <option value="food" <?php if($category == 'food') echo 'selected'; ?>>Ẩm thực</option>
                         <option value="work" <?php if($category == 'work') echo 'selected'; ?>>Công việc</option>
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
                <p style="text-align: center; padding: 20px;">Không có khảo sát nào phù hợp.</p>
            <?php else: ?>
                <?php foreach ($surveys as $survey): ?>
                    <div class="survey-item">
                        <div class="survey-item-info">
                            <h3 class="survey-item-title"><?php echo htmlspecialchars($survey['title']); ?></h3>
                            <p class="survey-item-description"><?php echo htmlspecialchars($survey['description'] ?? 'Không có mô tả'); ?></p>
                        </div>
                        <div class="survey-item-meta">
                            <div class="meta-points">
                                <span class="value"><?php echo number_format($survey['points_reward']); ?></span>
                                <span class="label">Điểm</span>
                            </div>
                            <button class="start-survey-btn" data-id="<?php echo $survey['survey_id']; ?>">Bắt đầu</button>
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

             <?php // Cải thiện hiển thị phân trang
                $range = 2;
                for ($i = 1; $i <= $totalPages; $i++):
                    if ($i == 1 || $i == $totalPages || ($i >= $page - $range && $i <= $page + $range)):
             ?>
                 <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" class="pagination-btn <?php if ($i == $page) echo 'active'; ?>">
                     <?php echo $i; ?>
                 </a>
             <?php elseif ($i == $page - $range - 1 || $i == $page + $range + 1): ?>
                 <span class="pagination-dots" style="padding: 5px 0;">...</span>
             <?php
                    endif;
                endfor;
             ?>

             <?php if ($page < $totalPages): ?>
                 <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="pagination-btn">→</a>
             <?php else: ?>
                 <button class="pagination-btn" disabled>→</button>
             <?php endif; ?>
        </div>
    </div>
</section>

<div id="survey-modal" class="survey-modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <div class="survey-progress">
            <div class="progress-bar"><div id="survey-progress" style="width: 0%;"></div></div>
            <span id="progress-text">0/0</span>
        </div>
        <h2 id="survey-modal-title">Đang tải khảo sát...</h2>
        <p class="survey-points">+0 điểm</p>
        <div id="survey-questions"><p>Đang tải câu hỏi...</p></div>
        <div class="survey-nav">
            <button id="prev-question" class="survey-nav-btn" disabled>← Câu trước</button>
            <button id="next-question" class="survey-nav-btn">Câu tiếp →</button>
            <button id="submit-survey" class="submit-survey-btn" style="display: none;">Gửi khảo sát</button>
        </div>
    </div>
</div>

<div id="survey-complete-modal" class="survey-complete-modal">
    <div class="modal-content">
        <span id="close-complete-modal" class="close-modal">&times;</span>
        <div class="complete-icon">✓</div>
        <h2>Hoàn thành khảo sát!</h2>
        <p>Bạn đã nhận được <span id="earned-points">0</span> điểm.</p>
        <button onclick="window.location.href='khaosat.php';" style="margin-top: 15px; padding: 8px 15px; background-color: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer;">Quay lại danh sách</button>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // --- Biến và Đối tượng DOM ---
        let currentSurveyData = null;
        let currentQuestionIndex = 0;
        let answers = {};

        const surveyModal = document.getElementById('survey-modal');
        const completeModal = document.getElementById('survey-complete-modal');
        const questionsContainer = document.getElementById('survey-questions');
        const surveyListContainer = document.querySelector('.survey-list');

        // Kiểm tra element tồn tại
        if (!surveyModal || !completeModal || !questionsContainer || !surveyListContainer) {
            console.error("Missing essential HTML elements for survey functionality.");
            // Không dừng hẳn script, chỉ log lỗi
            // return;
        }

        // --- Hàm gọi API lấy chi tiết khảo sát ---
        async function fetchSurveyQuestions(surveyId) {
            try {
                const response = await fetch(`actions/get_survey_details.php?id=${surveyId}`);
                if (!response.ok) {
                    let errorMsg = `Network error: ${response.status}`;
                    try { const errorData = await response.json(); errorMsg = errorData.error || errorMsg; } catch (e) {}
                    throw new Error(errorMsg);
                }
                const data = await response.json();
                if (data.error) throw new Error(data.error);
                if (!data.questions || data.questions.length === 0) throw new Error("Khảo sát này không có câu hỏi.");
                return data;
            } catch (error) {
                console.error('Error fetching survey questions:', error);
                alert(`Không thể tải câu hỏi khảo sát: ${error.message}`);
                return null;
            }
        }

        // --- Hàm bắt đầu khảo sát ---
        function startSurvey(surveyData) {
            if (!surveyModal) return; // Kiểm tra lại modal tồn tại
            currentSurveyData = surveyData;
            currentQuestionIndex = 0;
            answers = {};
            surveyModal.querySelector('#survey-modal-title').textContent = currentSurveyData.title;
            surveyModal.querySelector('.survey-points').textContent = `+${currentSurveyData.points} điểm`;
            surveyModal.classList.add('active');
            loadQuestion(true);
        }

        // --- Hàm tải và hiển thị câu hỏi ---
        function loadQuestion(isFirstQuestion = false) {
             if (!questionsContainer || !currentSurveyData || !surveyModal) return; // Kiểm tra tồn tại
             const question = currentSurveyData.questions[currentQuestionIndex];
             if (!question) { console.error("Invalid question index:", currentQuestionIndex); return; }

             const updateContent = () => {
                 // Update progress bar
                 const progress = ((currentQuestionIndex + 1) / currentSurveyData.questions.length) * 100;
                 const progressBar = surveyModal.querySelector('#survey-progress');
                 const progressText = surveyModal.querySelector('#progress-text');
                 if(progressBar) progressBar.style.width = `${progress}%`;
                 if(progressText) progressText.textContent = `${currentQuestionIndex + 1}/${currentSurveyData.questions.length}`;

                 // Build question HTML
                 questionsContainer.innerHTML = ''; // Clear previous
                 const questionElement = document.createElement('div');
                 questionElement.className = 'survey-question';
                 questionElement.dataset.questionId = question.id;

                 let optionsHTML = '';
                 const inputNameBase = `question_${question.id}`;

                 if (question.type === 'single_choice' || question.type === 'multiple_choice') {
                      if (!question.options || question.options.length === 0) {
                          optionsHTML = '<p style="color:red;">Lỗi: Không có lựa chọn.</p>';
                      } else {
                          optionsHTML = question.options.map((option) => {
                              const optionDomId = `option-${question.id}-${option.id}`;
                              const inputType = question.type === 'multiple_choice' ? 'checkbox' : 'radio';
                              const inputName = question.type === 'multiple_choice' ? `${inputNameBase}[]` : inputNameBase;
                              const isChecked = checkAnswer(question.id, option.id);
                              return `<label class="option-item" for="${optionDomId}">
                                          <input type="${inputType}" id="${optionDomId}" name="${inputName}" value="${option.id}" ${isChecked ? 'checked' : ''}>
                                          <span class="option-text">${escapeHtml(option.text)}</span>
                                      </label>`;
                          }).join('');
                      }
                 } else if (question.type === 'text_input') {
                     const currentAnswerText = answers[question.id] ? answers[question.id].text : '';
                     optionsHTML = `<textarea name="${inputNameBase}" rows="4" placeholder="Nhập câu trả lời..." class="text-answer-input">${escapeHtml(currentAnswerText)}</textarea>`;
                 } else {
                      optionsHTML = '<p style="color:red;">Lỗi: Loại câu hỏi không hỗ trợ.</p>';
                 }

                 questionElement.innerHTML = `<h3 class="survey-question-title">${escapeHtml(question.text)}</h3><div class="survey-options">${optionsHTML}</div>`;
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

         // --- Hàm kiểm tra câu trả lời đã lưu ---
         function checkAnswer(questionId, optionId) {
             if (!answers[questionId]) return false;
             if (answers[questionId].option_id) { // Single choice
                 return answers[questionId].option_id == optionId;
             }
             if (answers[questionId].option_ids) { // Multiple choice
                 return answers[questionId].option_ids.includes(String(optionId));
             }
             return false;
         }

        // --- Hàm cập nhật nút điều hướng ---
        function updateNavButtons() {
             if (!surveyModal || !currentSurveyData) return;
             const prevBtn = surveyModal.querySelector('#prev-question');
             const nextBtn = surveyModal.querySelector('#next-question');
             const submitBtn = surveyModal.querySelector('#submit-survey');
             if(!prevBtn || !nextBtn || !submitBtn) return; // Thoát nếu không tìm thấy nút

             prevBtn.disabled = currentQuestionIndex === 0;
             const isLastQuestion = currentQuestionIndex === currentSurveyData.questions.length - 1;
             nextBtn.style.display = isLastQuestion ? 'none' : 'block';
             submitBtn.style.display = isLastQuestion ? 'block' : 'none';
        }

        // --- Hàm lưu câu trả lời hiện tại ---
        function saveCurrentAnswer() {
             if (!currentSurveyData || !questionsContainer) return; // Thêm kiểm tra
             const questionDiv = questionsContainer.querySelector('.survey-question');
             if (!questionDiv) return;

             const questionId = questionDiv.dataset.questionId;
             const questionData = currentSurveyData.questions.find(q => q.id == questionId);
             if (!questionData) return;

             delete answers[questionId]; // Clear old answer for this question

             if (questionData.type === 'single_choice') {
                 const checkedInput = questionsContainer.querySelector(`input[name="question_${questionId}"]:checked`);
                 if (checkedInput) answers[questionId] = { option_id: checkedInput.value };
             } else if (questionData.type === 'multiple_choice') {
                 const checkedInputs = questionsContainer.querySelectorAll(`input[name="question_${questionId}[]"]:checked`);
                 if (checkedInputs.length > 0) answers[questionId] = { option_ids: Array.from(checkedInputs).map(input => input.value) };
             } else if (questionData.type === 'text_input') {
                 const textarea = questionsContainer.querySelector(`textarea[name="question_${questionId}"]`);
                 if (textarea && textarea.value.trim() !== '') answers[questionId] = { text: textarea.value.trim() };
             }
        }

        // --- Hàm đóng modal ---
        function closeModal(modalElement) {
             if (modalElement) modalElement.classList.remove('active');
        }

        // --- Hàm gửi câu trả lời ---
        async function submitSurveyAnswers() {
             if (!currentSurveyData || !surveyModal || !completeModal) return; // Kiểm tra
             saveCurrentAnswer(); // Save last answer

             // Basic validation: Check if all questions have some answer stored in 'answers' object
             // More robust validation (checking 'is_required' flag from backend) should be added
             if (Object.keys(answers).length !== currentSurveyData.questions.length) {
                 // Tìm câu hỏi đầu tiên chưa trả lời và cuộn đến đó (nếu cần)
                 alert('Vui lòng trả lời tất cả các câu hỏi.');
                 return;
             }


             const submissionData = {
                 survey_id: currentSurveyData.id,
                 responses: answers
             };

             const submitBtn = surveyModal.querySelector('#submit-survey');
             if(submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Đang gửi...';
             }

             try {
                 const response = await fetch('actions/submit_survey.php', {
                     method: 'POST',
                     headers: { 'Content-Type': 'application/json' },
                     body: JSON.stringify(submissionData)
                 });
                 const result = await response.json();

                 if (result.success) {
                     closeModal(surveyModal);
                     // Update points display on header (if exists)
                     const userPointsDisplay = document.querySelector('.user-points-display'); // **Cần thêm class này vào header.php**
                     if(userPointsDisplay && result.new_total_points !== undefined) {
                         userPointsDisplay.textContent = result.new_total_points.toLocaleString();
                     }
                     // Show completion modal
                     const earnedPointsSpan = completeModal.querySelector('#earned-points');
                     if(earnedPointsSpan) earnedPointsSpan.textContent = currentSurveyData.points;
                     completeModal.classList.add('active');

                     // Remove completed survey from list (optional)
                     const completedSurveyItem = surveyListContainer.querySelector(`.start-survey-btn[data-id="${currentSurveyData.id}"]`)?.closest('.survey-item');
                     if(completedSurveyItem) completedSurveyItem.style.display = 'none'; // Ẩn đi thay vì xóa hẳn

                 } else {
                     throw new Error(result.message || 'Gửi khảo sát thất bại.');
                 }
             } catch (error) {
                 console.error('Lỗi khi gửi khảo sát:', error);
                 alert(`Đã xảy ra lỗi khi gửi khảo sát: ${error.message}`);
             } finally {
                  if(submitBtn) { // Kích hoạt lại nút
                      submitBtn.disabled = false;
                      submitBtn.textContent = 'Gửi khảo sát';
                  }
             }
        }

        // --- Gắn Sự kiện ---
        if (surveyListContainer) {
            surveyListContainer.addEventListener('click', async function(e) {
                const startButton = e.target.closest('.start-survey-btn');
                if (startButton) {
                    e.preventDefault();
                    // Lấy ID từ data-id trước, nếu không có mới lấy từ href
                    const surveyId = startButton.dataset.id || (startButton.href ? startButton.href.split('id=')[1] : null);
                    if (!surveyId) { alert("Lỗi: Không tìm thấy ID khảo sát."); return; }

                    startButton.textContent = 'Đang tải...';
                    startButton.disabled = true;

                    const surveyData = await fetchSurveyQuestions(surveyId);

                    startButton.textContent = 'Bắt đầu';
                    startButton.disabled = false;

                    if (surveyData) {
                        startSurvey(surveyData);
                    }
                }
            });
        }

        // Sự kiện nút trong Modal khảo sát
        surveyModal?.querySelector('#prev-question')?.addEventListener('click', () => { saveCurrentAnswer(); if (currentSurveyData && currentQuestionIndex > 0) { currentQuestionIndex--; loadQuestion(); }});
        surveyModal?.querySelector('#next-question')?.addEventListener('click', () => { saveCurrentAnswer(); if (currentSurveyData && currentQuestionIndex < currentSurveyData.questions.length - 1) { currentQuestionIndex++; loadQuestion(); }});
        surveyModal?.querySelector('#submit-survey')?.addEventListener('click', submitSurveyAnswers);
        surveyModal?.querySelector('.close-modal')?.addEventListener('click', () => closeModal(surveyModal));

        // Sự kiện đóng Modal hoàn thành
        completeModal?.querySelector('#close-complete-modal')?.addEventListener('click', () => closeModal(completeModal));


        // --- Hàm tiện ích ---
        function escapeHtml(unsafe) {
            if (typeof unsafe !== 'string') return '';
            return unsafe.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }

    });
</script>

<?php require_once('includes/footer.php'); ?>