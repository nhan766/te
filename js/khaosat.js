document.addEventListener('DOMContentLoaded', function() {
    // Survey Data
    const surveys = [
        {
            id: 1,
            title: "Khảo sát thói quen mua sắm trực tuyến",
            description: "Chúng tôi muốn hiểu rõ hơn về thói quen mua sắm trực tuyến của bạn để cải thiện dịch vụ.",
            category: "shopping",
            points: 100,
            time: "5 phút",
            questions: [
                {
                    question: "Bạn mua sắm trực tuyến bao nhiêu lần mỗi tháng?",
                    type: "single",
                    options: ["Dưới 3 lần", "3-5 lần", "6-10 lần", "Trên 10 lần"]
                },
                {
                    question: "Bạn thường mua những loại sản phẩm nào trực tuyến?",
                    type: "multiple",
                    options: ["Thời trang", "Điện tử", "Thực phẩm", "Sách", "Đồ gia dụng"]
                },
                {
                    question: "Điều gì quan trọng nhất khi bạn chọn mua sắm trực tuyến?",
                    type: "single",
                    options: ["Giá cả", "Chất lượng sản phẩm", "Dịch vụ giao hàng", "Chính sách đổi trả", "Thương hiệu"]
                }
            ]
        },
        {
            id: 2,
            title: "Khảo sát về dịch vụ streaming",
            description: "Giúp chúng tôi cải thiện trải nghiệm dịch vụ streaming phim và nhạc của bạn.",
            category: "entertainment",
            points: 150,
            time: "7 phút",
            questions: [
                {
                    question: "Bạn sử dụng dịch vụ streaming nào?",
                    type: "multiple",
                    options: ["Netflix", "Disney+", "YouTube Premium", "Spotify", "Apple Music", "Khác"]
                },
                {
                    question: "Bạn chi khoảng bao nhiêu mỗi tháng cho dịch vụ streaming?",
                    type: "single",
                    options: ["Dưới 100.000đ", "100.000đ - 200.000đ", "200.000đ - 500.000đ", "Trên 500.000đ"]
                }
            ]
        },
        {
            id: 3,
            title: "Khảo sát về sức khỏe và thể dục",
            description: "Chia sẻ thói quen chăm sóc sức khỏe và luyện tập thể chất hàng ngày của bạn.",
            category: "health",
            points: 120,
            time: "6 phút",
            questions: [
                {
                    question: "Bạn tập thể dục bao nhiêu lần mỗi tuần?",
                    type: "single",
                    options: ["Không tập", "1-2 lần", "3-4 lần", "5 lần trở lên"]
                },
                {
                    question: "Bạn quan tâm đến những loại hình tập luyện nào?",
                    type: "multiple",
                    options: ["Gym", "Yoga", "Chạy bộ", "Bơi lội", "Đạp xe", "Khác"]
                }
            ]
        }
    ];

    // Current survey state
    let currentSurvey = null;
    let currentQuestionIndex = 0;
    let answers = {};

    // DOM Elements
    const surveyList = document.querySelector('.survey-list');
    const surveyModal = document.getElementById('survey-modal');
    const completeModal = document.getElementById('survey-complete-modal');
    const questionsContainer = document.getElementById('survey-questions');

    // Display surveys
    function displaySurveys() {
        if (!surveyList) return;
        surveyList.innerHTML = '';
        
        const category = document.getElementById('survey-category').value;
        const sort = document.getElementById('survey-sort').value;
        
        let filteredSurveys = surveys;
        if (category !== 'all') {
            filteredSurveys = surveys.filter(s => s.category === category);
        }
        
        filteredSurveys.sort((a, b) => {
            if (sort === 'newest') return b.id - a.id;
            if (sort === 'highest-point') return b.points - a.points;
            if (sort === 'shortest') return a.time.localeCompare(b.time);
            return 0;
        });
        
        filteredSurveys.forEach(survey => {
            const surveyCard = document.createElement('div');
            surveyCard.className = 'survey-card fade-in';
            surveyCard.innerHTML = `
                <div class="survey-card-image" style="background-color: ${getCategoryColor(survey.category)}">
                    <i class="${getCategoryIcon(survey.category)}"></i>
                </div>
                <div class="survey-card-info">
                    <h3 class="survey-card-title">${survey.title}</h3>
                    <p class="survey-card-description">${survey.description}</p>
                    <div class="survey-card-meta">
                        <span class="survey-card-points">+${survey.points} điểm</span>
                        <span class="survey-card-time">⏱️ ${survey.time}</span>
                    </div>
                    <button class="survey-card-btn" data-id="${survey.id}">Bắt đầu khảo sát</button>
                </div>
            `;
            surveyList.appendChild(surveyCard);
        });
        
        addEventListenersToSurveyButtons();
        observeFadeInElements();
    }

    function getCategoryColor(category) {
        const colors = { shopping: '#3498db', entertainment: '#9b59b6', technology: '#e74c3c', health: '#2ecc71' };
        return colors[category] || '#2980b9';
    }
    
    function getCategoryIcon(category) {
        const icons = { shopping: 'bx bxs-shopping-bag', entertainment: 'bx bxs-movie-play', technology: 'bx bxs-chip', health: 'bx bxs-heart' };
        return icons[category] || 'bx bxs-help-circle';
    }
    
    function addEventListenersToSurveyButtons() {
        document.querySelectorAll('.survey-card-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const surveyId = parseInt(this.dataset.id);
                startSurvey(surveyId);
            });
        });
    }

    // Start survey
    function startSurvey(surveyId) {
        currentSurvey = surveys.find(s => s.id === surveyId);
        currentQuestionIndex = 0;
        answers = {};
        
        document.getElementById('survey-modal-title').textContent = currentSurvey.title;
        document.querySelector('#survey-modal .survey-points').textContent = `+${currentSurvey.points} điểm`;
        surveyModal.classList.add('active');
        
        loadQuestion(true);
    }
    
    // Load question with animation
    function loadQuestion(isFirstQuestion = false) {
        const question = currentSurvey.questions[currentQuestionIndex];
        
        const updateContent = () => {
            const progress = ((currentQuestionIndex + 1) / currentSurvey.questions.length) * 100;
            document.getElementById('survey-progress').style.width = `${progress}%`;
            document.getElementById('progress-text').textContent = `${currentQuestionIndex + 1}/${currentSurvey.questions.length}`;
            
            questionsContainer.innerHTML = ''; // Clear previous
            const questionElement = document.createElement('div');
            questionElement.className = 'survey-question';
            
            let optionsHTML = question.options.map((option, i) => {
                const optionId = `option-${currentQuestionIndex}-${i}`;
                const inputType = question.type === 'multiple' ? 'checkbox' : 'radio';
                const inputName = `question-${currentQuestionIndex}`;
                const isChecked = answers[currentQuestionIndex] && (question.type === 'multiple' ? answers[currentQuestionIndex].includes(option) : answers[currentQuestionIndex] === option);
                return `
                    <label class="option-item" for="${optionId}">
                        <input type="${inputType}" id="${optionId}" name="${inputName}" value="${option}" ${isChecked ? 'checked' : ''}>
                        <span class="option-text">${option}</span>
                    </label>
                `;
            }).join('');
            
            questionElement.innerHTML = `
                <h3 class="survey-question-title">${question.question}</h3>
                <div class="survey-options">${optionsHTML}</div>
            `;
            questionsContainer.appendChild(questionElement);
            
            questionsContainer.classList.remove('fade-out'); // Fade in new question
            updateNavButtons();
        };

        if (isFirstQuestion) {
            updateContent();
        } else {
            questionsContainer.classList.add('fade-out'); // Fade out current question
            setTimeout(updateContent, 300); // Wait for fade out to complete
        }
    }

    function updateNavButtons() {
        document.getElementById('prev-question').disabled = currentQuestionIndex === 0;
        const isLastQuestion = currentQuestionIndex === currentSurvey.questions.length - 1;
        document.getElementById('next-question').style.display = isLastQuestion ? 'none' : 'block';
        document.getElementById('submit-survey').style.display = isLastQuestion ? 'block' : 'none';
    }

    // Save answer
    function saveAnswer() {
        const question = currentSurvey.questions[currentQuestionIndex];
        const inputs = document.querySelectorAll(`input[name="question-${currentQuestionIndex}"]:checked`);
        if (inputs.length === 0) return; // No answer selected
        
        if (question.type === 'multiple') {
            answers[currentQuestionIndex] = Array.from(inputs).map(input => input.value);
        } else {
            answers[currentQuestionIndex] = inputs[0].value;
        }
    }
    
    // --- Event Listeners ---
    if(document.getElementById('survey-category')) {
      document.getElementById('survey-category').addEventListener('change', displaySurveys);
      document.getElementById('survey-sort').addEventListener('change', displaySurveys);
    }
    
    document.getElementById('prev-question').addEventListener('click', function() {
        saveAnswer();
        if (currentQuestionIndex > 0) {
            currentQuestionIndex--;
            loadQuestion();
        }
    });

    document.getElementById('next-question').addEventListener('click', function() {
        saveAnswer();
        if (currentQuestionIndex < currentSurvey.questions.length - 1) {
            currentQuestionIndex++;
            loadQuestion();
        }
    });

    document.getElementById('submit-survey').addEventListener('click', function() {
        saveAnswer();
        console.log('Survey answers:', answers);
        
        surveyModal.classList.remove('active');
        
        setTimeout(() => {
            document.getElementById('earned-points').textContent = currentSurvey.points;
            completeModal.classList.add('active');
        }, 400); // Wait for survey modal to close
        
        addPoints(currentSurvey.points);
        saveSurveyHistory(currentSurvey.id);
    });
    
    function closeModal(modal) {
        modal.classList.remove('active');
    }

    document.getElementById('close-complete-modal').addEventListener('click', () => closeModal(completeModal));
    document.querySelector('#survey-modal .close-modal').addEventListener('click', () => closeModal(surveyModal));
    
    // Close modal on outside click
    [surveyModal, completeModal].forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(modal);
            }
        });
    });

    function addPoints(points) {
        // This function would update user points on the server/main page
        console.log(`Added ${points} points.`);
    }

    function saveSurveyHistory(surveyId) {
        const history = JSON.parse(localStorage.getItem('survey_history')) || [];
        const survey = surveys.find(s => s.id === surveyId);
        history.unshift({ date: new Date().toISOString(), title: survey.title, points: survey.points });
        localStorage.setItem('survey_history', JSON.stringify(history));
        displaySurveyHistory(); // Refresh history if on profile page
    }
    
    function displaySurveyHistory() {
        const historyList = document.querySelector('.survey-history-list');
        if (!historyList) return;
        
        const history = JSON.parse(localStorage.getItem('survey_history')) || [];
        historyList.innerHTML = '';
        
        if (history.length === 0) {
            historyList.innerHTML = `<div class="no-survey-history"><p>Bạn chưa tham gia khảo sát nào</p></div>`;
            return;
        }
        
        history.forEach(item => {
            const dateStr = new Date(item.date).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
            const historyItem = document.createElement('div');
            historyItem.className = 'survey-history-item';
            historyItem.innerHTML = `
                <div class="survey-history-info">
                    <div class="survey-history-title">${item.title}</div>
                    <div class="survey-history-date">${dateStr}</div>
                </div>
                <div class="survey-history-points">+${item.points} điểm</div>
            `;
            historyList.appendChild(historyItem);
        });
    }
    
    // --- Intersection Observer for fade-in effect ---
    function observeFadeInElements() {
        const faders = document.querySelectorAll('.fade-in');
        if (faders.length === 0) return;
        
        const appearOptions = { threshold: 0.15, rootMargin: "0px 0px -50px 0px" };
        const appearOnScroll = new IntersectionObserver(function(entries, observer) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, appearOptions);
        
        faders.forEach(fader => appearOnScroll.observe(fader));
    }

    // Initialize
    displaySurveys();
    displaySurveyHistory();
});