document.addEventListener('DOMContentLoaded', function() {
    
    // Dữ liệu khảo sát thủ công
    const allSurveys = [
        { id: 1, title: "Thói quen mua sắm trực tuyến của bạn", description: "Chia sẻ về cách bạn mua sắm online...", category: "shopping", points: 120, time: "7 phút", questions: [{ question: "Tần suất mua sắm online của bạn?", type: "single", options: ["Hàng ngày", "Vài lần/tuần", "Vài lần/tháng", "Rất hiếm khi"] }, { question: "Bạn thường mua gì online?", type: "multiple", options: ["Thời trang", "Điện tử", "Mỹ phẩm", "Sách", "Đồ ăn"] }] },
        { id: 2, title: "Đánh giá về các dịch vụ Streaming Phim", description: "Bạn nghĩ gì về Netflix, Disney+...", category: "entertainment", points: 150, time: "8 phút", questions: [{ question: "Dịch vụ nào bạn đang sử dụng?", type: "multiple", options: ["Netflix", "Disney+", "HBO Go", "FPT Play"] }, { question: "Bạn hài lòng nhất với yếu tố nào?", type: "single", options: ["Chất lượng phim", "Số lượng phim", "Giá cả", "Giao diện"] }] },
        { id: 3, title: "Lựa chọn Smartphone tiếp theo của bạn", description: "Giúp các hãng điện thoại hiểu rõ hơn...", category: "technology", points: 200, time: "10 phút", questions: [{ question: "Thương hiệu nào bạn tin dùng?", type: "single", options: ["Apple", "Samsung", "Xiaomi", "OPPO"] }, { question: "Bạn quan tâm nhất đến tính năng nào?", type: "multiple", options: ["Camera", "Hiệu năng (Chip)", "Thời lượng pin", "Thiết kế"] }] },
        { id: 4, title: "Thói quen ăn uống và ẩm thực", description: "Chia sẻ về sở thích ẩm thực...", category: "food", points: 100, time: "5 phút", questions: [{ question: "Bạn thường ăn ngoài hay tự nấu?", type: "single", options: ["Chủ yếu ăn ngoài", "Chủ yếu tự nấu", "50/50"] }] },
        { id: 5, "title": "Kế hoạch du lịch trong năm nay", description: "Bạn dự định đi đâu? Chia sẻ kế hoạch...", category: "travel", points: 180, time: "9 phút", questions: [{ question: "Bạn thích du lịch biển hay núi?", type: "single", options: ["Biển", "Núi"] }] },
        { id: 6, title: "Cân bằng giữa công việc và cuộc sống", description: "Hãy chia sẻ cách bạn quản lý thời gian...", category: "work", points: 5050, time: "12 phút", questions: [{ question: "Bạn có làm việc ngoài giờ không?", type: "single", options: ["Thường xuyên", "Thỉnh thoảng", "Không bao giờ"] }] }
    ];

    // Các biến và đối tượng DOM
    let currentSurvey = null;
    let currentQuestionIndex = 0;
    let answers = {};
    const surveyList = document.querySelector('.survey-list');
    const surveyModal = document.getElementById('survey-modal');
    const completeModal = document.getElementById('survey-complete-modal');
    const questionsContainer = document.getElementById('survey-questions');

    // Các hàm chức năng (startSurvey, loadQuestion, v.v...) giữ nguyên như bạn đã gửi
    function startSurvey(surveyId) {
        if (!surveyModal) { console.error("Lỗi: Không tìm thấy #survey-modal trong file HTML."); return; }
        currentSurvey = allSurveys.find(s => s.id === surveyId);
        if (!currentSurvey) { return; }
        currentQuestionIndex = 0;
        answers = {};
        surveyModal.querySelector('#survey-modal-title').textContent = currentSurvey.title;
        surveyModal.querySelector('.survey-points').textContent = `+${currentSurvey.points} điểm`;
        surveyModal.classList.add('active');
        loadQuestion(true);
    }
    function loadQuestion(isFirstQuestion = false) {
        if (!questionsContainer || !currentSurvey) return;
        const question = currentSurvey.questions[currentQuestionIndex];
        const updateContent = () => {
            const progress = ((currentQuestionIndex + 1) / currentSurvey.questions.length) * 100;
            surveyModal.querySelector('#survey-progress').style.width = `${progress}%`;
            surveyModal.querySelector('#progress-text').textContent = `${currentQuestionIndex + 1}/${currentSurvey.questions.length}`;
            questionsContainer.innerHTML = '';
            const questionElement = document.createElement('div');
            questionElement.className = 'survey-question';
            let optionsHTML = question.options.map((option, i) => {
                const optionId = `option-${currentQuestionIndex}-${i}`;
                const inputType = question.type === 'multiple' ? 'checkbox' : 'radio';
                const inputName = `question-${currentQuestionIndex}`;
                return `<label class="option-item" for="${optionId}"><input type="${inputType}" id="${optionId}" name="${inputName}" value="${option}"><span class="option-text">${option}</span></label>`;
            }).join('');
            questionElement.innerHTML = `<h3 class="survey-question-title">${question.question}</h3><div class="survey-options">${optionsHTML}</div>`;
            questionsContainer.appendChild(questionElement);
            questionsContainer.classList.remove('fade-out');
            updateNavButtons();
        };
        if (isFirstQuestion) { updateContent(); } 
        else { questionsContainer.classList.add('fade-out'); setTimeout(updateContent, 300); }
    }
    function updateNavButtons() {
        if (!surveyModal) return;
        surveyModal.querySelector('#prev-question').disabled = currentQuestionIndex === 0;
        const isLastQuestion = currentQuestionIndex === currentSurvey.questions.length - 1;
        surveyModal.querySelector('#next-question').style.display = isLastQuestion ? 'none' : 'block';
        surveyModal.querySelector('#submit-survey').style.display = isLastQuestion ? 'block' : 'none';
    }
    function saveAnswer() {
        if (!currentSurvey) return;
        const question = currentSurvey.questions[currentQuestionIndex];
        const inputs = surveyModal.querySelectorAll(`input[name="question-${currentQuestionIndex}"]:checked`);
        if (inputs.length === 0) return;
        if (question.type === 'multiple') { answers[currentQuestionIndex] = Array.from(inputs).map(input => input.value); } 
        else { answers[currentQuestionIndex] = inputs[0].value; }
    }
    function closeModal(modal) { if (modal) { modal.classList.remove('active'); } }
    function filterAndSortSurveys() {
        if (!surveyList) return;
        surveyList.innerHTML = '';
        const category = document.getElementById('survey-category').value;
        const sort = document.getElementById('survey-sort').value;
        let displaySurveys = [...allSurveys];
        if (category !== 'all') { displaySurveys = displaySurveys.filter(s => s.category === category); }
        displaySurveys.sort((a, b) => {
            if (sort === 'newest') return b.id - a.id;
            if (sort === 'highest-point') return b.points - a.points;
            if (sort === 'shortest') return (a.time || "").localeCompare(b.time || "");
            return 0;
        });
        displaySurveys.forEach(survey => {
            const surveyItem = document.createElement('div');
            surveyItem.className = 'survey-item';
            surveyItem.innerHTML = `<div class="survey-item-category" style="background-color: ${getCategoryColor(survey.category)}"><i class="bx ${getCategoryIcon(survey.category)}"></i></div><div class="survey-item-info"><h3 class="survey-item-title">${survey.title}</h3><p class="survey-item-description">${survey.description}</p></div><div class="survey-item-meta"><div class="meta-points"><span class="value">${survey.points}</span><span class="label">Điểm</span></div><div class="meta-time"><span class="value">${survey.time || 'N/A'}</span><span class="label">Thời gian</span></div><button class="start-survey-btn" data-id="${survey.id}">Bắt đầu</button></div>`;
            surveyList.appendChild(surveyItem);
        });
        addEventListenersToSurveyButtons();
    }
    function addEventListenersToSurveyButtons() {
        document.querySelectorAll('.start-survey-btn').forEach(btn => {
            btn.addEventListener('click', function() { startSurvey(parseInt(this.dataset.id)); });
        });
    }
    function getCategoryColor(category) { const c = { shopping: '#3498db', entertainment: '#9b59b6', technology: '#e74c3c', health: '#2ecc71', travel: '#1abc9c', food: '#f1c40f', work: '#34495e' }; return c[category] || '#2980b9'; }
    function getCategoryIcon(category) { const i = { shopping: 'bxs-shopping-bag', entertainment: 'bxs-movie-play', technology: 'bxs-chip', health: 'bxs-heart', travel: 'bxs-plane-alt', food: 'bxs-dish', work: 'bxs-briefcase' }; return i[category] || 'bxs-help-circle'; }

    
    // Gắn các sự kiện
    document.getElementById('survey-category')?.addEventListener('change', filterAndSortSurveys);
    document.getElementById('survey-sort')?.addEventListener('change', filterAndSortSurveys);
    
    surveyModal?.querySelector('#prev-question').addEventListener('click', () => { saveAnswer(); if(currentSurvey && currentQuestionIndex > 0) { currentQuestionIndex--; loadQuestion(); }});
    surveyModal?.querySelector('#next-question').addEventListener('click', () => { saveAnswer(); if(currentSurvey && currentQuestionIndex < currentSurvey.questions.length - 1) { currentQuestionIndex++; loadQuestion(); }});

    // ==================================================
    // --- CẬP NHẬT LOGIC CỘNG ĐIỂM VÀ LƯU LỊCH SỬ ---
    // ==================================================
    surveyModal?.querySelector('#submit-survey').addEventListener('click', () => {
        saveAnswer();
        closeModal(surveyModal);

        // 1. Cập nhật lịch sử hoạt động
        const newActivity = {
            content: `Hoàn thành khảo sát "${currentSurvey.title}" (+${currentSurvey.points} điểm)`,
            date: new Date().toISOString()
        };
        // Đọc lịch sử cũ, thêm mục mới vào đầu, rồi lưu lại
        const activities = JSON.parse(localStorage.getItem('survey_activities')) || [];
        activities.unshift(newActivity);
        localStorage.setItem('survey_activities', JSON.stringify(activities));

        // 2. Cập nhật tổng điểm và số khảo sát đã làm
        const defaultUser = { points: 0, surveys: 0 };
        const savedUserData = JSON.parse(localStorage.getItem('surveyon_user')) || {};
        const userData = { ...defaultUser, ...savedUserData }; // Trộn dữ liệu để không mất thông tin cũ

        userData.points += currentSurvey.points;
        userData.surveys += 1;
        // Lưu lại toàn bộ đối tượng người dùng đã được cập nhật
        localStorage.setItem('surveyon_user', JSON.stringify(userData));

        // Mở pop-up thông báo hoàn thành
        if (completeModal) {
            setTimeout(() => {
                completeModal.querySelector('#earned-points').textContent = currentSurvey.points;
                completeModal.classList.add('active');
            }, 400);
        }
    });

    surveyModal?.querySelector('.close-modal').addEventListener('click', () => closeModal(surveyModal));
    completeModal?.querySelector('.close-modal').addEventListener('click', () => closeModal(completeModal));

    // Khởi tạo trang
    filterAndSortSurveys();
});