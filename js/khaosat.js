// js/khaosat.js (Phiên bản cập nhật cho PHP Backend)

document.addEventListener('DOMContentLoaded', function() {

    // --- Biến và Đối tượng DOM ---
    let currentSurveyData = null; // Dữ liệu khảo sát hiện tại tải từ server
    let currentQuestionIndex = 0;
    let answers = {}; // Lưu câu trả lời { question_id: { option_id: X } / { option_ids: [X,Y] } / { text: "..." } }

    const surveyModal = document.getElementById('survey-modal');
    const completeModal = document.getElementById('survey-complete-modal');
    const questionsContainer = document.getElementById('survey-questions');
    const surveyListContainer = document.querySelector('.survey-list'); // Cần để gắn sự kiện

    // Kiểm tra sự tồn tại của các element cần thiết
    if (!surveyModal || !completeModal || !questionsContainer || !surveyListContainer) {
        console.error("Lỗi: Không tìm thấy một hoặc nhiều element cần thiết cho khảo sát (modal, questions container, survey list).");
        return; // Dừng thực thi nếu thiếu element quan trọng
    }

    // --- Hàm gọi API lấy chi tiết khảo sát ---
    async function fetchSurveyQuestions(surveyId) {
        try {
            const response = await fetch(`actions/get_survey_details.php?id=${surveyId}`);
            if (!response.ok) {
                // Thử phân tích lỗi JSON từ server nếu có
                try {
                    const errorData = await response.json();
                    throw new Error(errorData.error || `Lỗi mạng: ${response.status}`);
                } catch(e) { // Nếu response không phải JSON
                     throw new Error(`Lỗi mạng: ${response.status}`);
                }
            }
            const data = await response.json();
            if (data.error) {
                throw new Error(data.error);
            }
            // Kiểm tra xem có câu hỏi không
            if (!data.questions || data.questions.length === 0) {
                 throw new Error("Khảo sát này hiện không có câu hỏi nào.");
            }
            return data; // Dữ liệu hợp lệ: { id, title, points, questions: [...] }
        } catch (error) {
            console.error('Lỗi khi tải câu hỏi khảo sát:', error);
            alert(`Không thể tải câu hỏi khảo sát: ${error.message}`);
            return null;
        }
    }

    // --- Hàm bắt đầu khảo sát (sau khi fetch dữ liệu) ---
    function startSurvey(surveyData) {
        currentSurveyData = surveyData; // Lưu dữ liệu khảo sát
        currentQuestionIndex = 0;
        answers = {}; // Reset câu trả lời cũ

        // Cập nhật thông tin modal
        surveyModal.querySelector('#survey-modal-title').textContent = currentSurveyData.title;
        surveyModal.querySelector('.survey-points').textContent = `+${currentSurveyData.points} điểm`;

        // Hiển thị modal và tải câu hỏi đầu tiên
        surveyModal.classList.add('active');
        loadQuestion(true); // true = câu hỏi đầu tiên
    }

    // --- Hàm tải và hiển thị câu hỏi ---
    function loadQuestion(isFirstQuestion = false) {
        if (!questionsContainer || !currentSurveyData) return;
        const question = currentSurveyData.questions[currentQuestionIndex];
        if (!question) {
            console.error("Lỗi: Không tìm thấy dữ liệu câu hỏi ở index", currentQuestionIndex);
            return;
        }

        const updateContent = () => {
            // Cập nhật thanh progress
            const progress = ((currentQuestionIndex + 1) / currentSurveyData.questions.length) * 100;
            surveyModal.querySelector('#survey-progress').style.width = `${progress}%`;
            surveyModal.querySelector('#progress-text').textContent = `${currentQuestionIndex + 1}/${currentSurveyData.questions.length}`;

            // Xóa câu hỏi cũ và tạo câu hỏi mới
            questionsContainer.innerHTML = '';
            const questionElement = document.createElement('div');
            questionElement.className = 'survey-question';
            questionElement.dataset.questionId = question.id; // Lưu ID câu hỏi từ CSDL

            let optionsHTML = '';
            const inputNameBase = `question_${question.id}`; // Tên input dựa trên ID câu hỏi

            if (question.type === 'single_choice' || question.type === 'multiple_choice') {
                if (!question.options || question.options.length === 0) {
                    optionsHTML = '<p>Lỗi: Không có lựa chọn cho câu hỏi này.</p>';
                } else {
                    optionsHTML = question.options.map((option, i) => {
                        const optionDomId = `option-${question.id}-${option.id}`; // ID cho DOM (duy nhất)
                        const inputType = question.type === 'multiple_choice' ? 'checkbox' : 'radio';
                        const inputName = question.type === 'multiple_choice' ? `${inputNameBase}[]` : inputNameBase; // Thêm [] cho checkbox
                        const isChecked = checkAnswer(question.id, option.id); // Kiểm tra xem đã trả lời chưa

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
                 optionsHTML = '<p>Lỗi: Loại câu hỏi không được hỗ trợ.</p>';
            }

            questionElement.innerHTML = `<h3 class="survey-question-title">${escapeHtml(question.text)}</h3><div class="survey-options">${optionsHTML}</div>`;
            questionsContainer.appendChild(questionElement);

            // Xóa hiệu ứng fade-out và cập nhật nút nav
            questionsContainer.classList.remove('fade-out');
            updateNavButtons();
        };

        // Tạo hiệu ứng chuyển câu hỏi
        if (isFirstQuestion) {
            updateContent();
        } else {
            questionsContainer.classList.add('fade-out');
            setTimeout(updateContent, 300); // Đợi animation fade-out hoàn thành
        }
    }

    // --- Hàm kiểm tra câu trả lời đã lưu (để check lại radio/checkbox) ---
    function checkAnswer(questionId, optionId) {
        if (!answers[questionId]) return false;
        if (answers[questionId].option_id) { // Single choice
            return answers[questionId].option_id == optionId;
        }
        if (answers[questionId].option_ids) { // Multiple choice
            return answers[questionId].option_ids.includes(String(optionId)); // ID có thể là string/number
        }
        return false;
    }


    // --- Hàm cập nhật trạng thái nút Next/Prev/Submit ---
    function updateNavButtons() {
        if (!surveyModal || !currentSurveyData) return;
        const prevBtn = surveyModal.querySelector('#prev-question');
        const nextBtn = surveyModal.querySelector('#next-question');
        const submitBtn = surveyModal.querySelector('#submit-survey');

        prevBtn.disabled = currentQuestionIndex === 0;
        const isLastQuestion = currentQuestionIndex === currentSurveyData.questions.length - 1;
        nextBtn.style.display = isLastQuestion ? 'none' : 'block';
        submitBtn.style.display = isLastQuestion ? 'block' : 'none';
    }

    // --- Hàm lưu câu trả lời hiện tại vào biến 'answers' ---
    function saveCurrentAnswer() {
        if (!currentSurveyData) return;
        const questionDiv = questionsContainer.querySelector('.survey-question');
        if (!questionDiv) return;

        const questionId = questionDiv.dataset.questionId;
        const questionData = currentSurveyData.questions.find(q => q.id == questionId);
        if (!questionData) return;

        delete answers[questionId]; // Xóa câu trả lời cũ trước khi lưu mới

        if (questionData.type === 'single_choice') {
            const checkedInput = questionsContainer.querySelector(`input[name="question_${questionId}"]:checked`);
            if (checkedInput) {
                answers[questionId] = { option_id: checkedInput.value };
            }
        } else if (questionData.type === 'multiple_choice') {
            const checkedInputs = questionsContainer.querySelectorAll(`input[name="question_${questionId}[]"]:checked`);
            if (checkedInputs.length > 0) {
                 answers[questionId] = { option_ids: Array.from(checkedInputs).map(input => input.value) };
            }
        } else if (questionData.type === 'text_input') {
            const textarea = questionsContainer.querySelector(`textarea[name="question_${questionId}"]`);
            if (textarea && textarea.value.trim() !== '') {
                answers[questionId] = { text: textarea.value.trim() };
            }
        }
    }

    // --- Hàm đóng modal ---
    function closeModal(modalElement) {
        if (modalElement) {
            modalElement.classList.remove('active');
        }
    }

    // --- Hàm gửi câu trả lời lên server ---
    async function submitSurveyAnswers() {
        if (!currentSurveyData) return;
        saveCurrentAnswer(); // Lưu câu trả lời cuối cùng

        // Kiểm tra xem tất cả câu hỏi bắt buộc đã được trả lời chưa (nếu cần)
        // ... (thêm logic validation nếu is_required = true) ...

        const submissionData = {
            survey_id: currentSurveyData.id,
            responses: answers
        };

        // Vô hiệu hóa nút submit để tránh gửi nhiều lần
        const submitBtn = surveyModal.querySelector('#submit-survey');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Đang gửi...';

        try {
            const response = await fetch('actions/submit_survey.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    // 'X-CSRF-TOKEN': '...' // Thêm CSRF token nếu backend yêu cầu
                },
                body: JSON.stringify(submissionData)
            });

            // Xử lý response từ server
            const result = await response.json();

            if (result.success) {
                closeModal(surveyModal);
                // Cập nhật điểm trên header (nếu có element hiển thị điểm)
                const userPointsDisplay = document.querySelector('.user-points-display'); // Thay selector nếu cần
                if(userPointsDisplay && result.new_total_points !== undefined) {
                    userPointsDisplay.textContent = result.new_total_points.toLocaleString();
                }
                // Hiển thị modal hoàn thành
                if (completeModal) {
                    completeModal.querySelector('#earned-points').textContent = currentSurveyData.points;
                    completeModal.classList.add('active');
                }
                 // Tùy chọn: Xóa khảo sát đã làm khỏi danh sách trên trang hiện tại
                 const completedSurveyItem = surveyListContainer.querySelector(`a[href*="id=${currentSurveyData.id}"]`)?.closest('.survey-item');
                 if(completedSurveyItem) completedSurveyItem.remove();

            } else {
                throw new Error(result.message || 'Gửi khảo sát thất bại.');
            }
        } catch (error) {
            console.error('Lỗi khi gửi khảo sát:', error);
            alert(`Đã xảy ra lỗi khi gửi khảo sát: ${error.message}`);
        } finally {
             // Kích hoạt lại nút submit dù thành công hay thất bại
             submitBtn.disabled = false;
             submitBtn.textContent = 'Gửi khảo sát';
        }
    }

    // --- Gắn Sự kiện ---

    // 1. Sự kiện click vào nút "Bắt đầu" (dùng event delegation)
    surveyListContainer.addEventListener('click', async function(e) {
        const startButton = e.target.closest('.start-survey-btn'); // Tìm nút gần nhất được click
        if (startButton) {
            e.preventDefault(); // Ngăn hành động mặc định của thẻ <a>
            const surveyId = startButton.dataset.id || startButton.href.split('id=')[1]; // Lấy ID từ data-id hoặc href
            if (!surveyId) {
                alert("Lỗi: Không tìm thấy ID khảo sát.");
                return;
            }
            // Hiện loading (nếu có)
            startButton.textContent = 'Đang tải...';
            startButton.disabled = true;

            const surveyData = await fetchSurveyQuestions(surveyId);

            // Reset nút
            startButton.textContent = 'Bắt đầu';
            startButton.disabled = false;

            if (surveyData) {
                startSurvey(surveyData); // Bắt đầu khảo sát nếu tải thành công
            }
        }
    });


    // 2. Sự kiện cho các nút trong Modal khảo sát
    surveyModal.querySelector('#prev-question')?.addEventListener('click', () => {
        saveCurrentAnswer(); // Lưu câu trả lời hiện tại
        if (currentSurveyData && currentQuestionIndex > 0) {
            currentQuestionIndex--;
            loadQuestion(); // Tải câu trước đó
        }
    });

    surveyModal.querySelector('#next-question')?.addEventListener('click', () => {
        saveCurrentAnswer(); // Lưu câu trả lời hiện tại
        if (currentSurveyData && currentQuestionIndex < currentSurveyData.questions.length - 1) {
            currentQuestionIndex++;
            loadQuestion(); // Tải câu tiếp theo
        }
    });

    surveyModal.querySelector('#submit-survey')?.addEventListener('click', submitSurveyAnswers);

    // 3. Sự kiện đóng Modals
    surveyModal.querySelector('.close-modal')?.addEventListener('click', () => closeModal(surveyModal));
    completeModal.querySelector('.close-modal')?.addEventListener('click', () => closeModal(completeModal));

    // Đóng modal khi click ra ngoài (tùy chọn)
    // surveyModal.addEventListener('click', function(e) { if (e.target === this) closeModal(surveyModal); });
    // completeModal.addEventListener('click', function(e) { if (e.target === this) closeModal(completeModal); });


    // --- Hàm tiện ích ---
    function escapeHtml(unsafe) {
        if (typeof unsafe !== 'string') return '';
        return unsafe
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

    // --- Khởi tạo (Không cần gọi filterAndSortSurveys nữa) ---
    // console.log("Khảo sát JS initialized.");

});