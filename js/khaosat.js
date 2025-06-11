    // Survey Data
    const surveys = [
        {
            id: 1,
            title: "Khảo sát thói quen mua sắm trực tuyến",
            description: "Chúng tôi muốn hiểu rõ hơn về thói quen mua sắm trực tuyến của bạn",
            category: "shopping",
            points: 100,
            time: "5 phút",
            questions: [
                {
                    question: "Bạn mua sắm trực tuyến bao nhiêu lần mỗi tháng?",
                    type: "single",
                    options: [
                        "Dưới 3 lần",
                        "3-5 lần",
                        "6-10 lần",
                        "Trên 10 lần"
                    ]
                },
                {
                    question: "Bạn thường mua những loại sản phẩm nào trực tuyến?",
                    type: "multiple",
                    options: [
                        "Thời trang",
                        "Điện tử",
                        "Thực phẩm",
                        "Sách",
                        "Đồ gia dụng"
                    ]
                },
                {
                    question: "Điều gì quan trọng nhất khi bạn chọn mua sắm trực tuyến?",
                    type: "single",
                    options: [
                        "Giá cả",
                        "Chất lượng sản phẩm",
                        "Dịch vụ giao hàng",
                        "Chính sách đổi trả",
                        "Thương hiệu"
                    ]
                }
            ]
        },
        {
            id: 2,
            title: "Khảo sát về dịch vụ streaming",
            description: "Giúp chúng tôi cải thiện trải nghiệm dịch vụ streaming",
            category: "entertainment",
            points: 150,
            time: "7 phút",
            questions: [
                {
                    question: "Bạn sử dụng dịch vụ streaming nào?",
                    type: "multiple",
                    options: [
                        "Netflix",
                        "Disney+",
                        "YouTube Premium",
                        "Spotify",
                        "Apple Music",
                        "Khác"
                    ]
                },
                {
                    question: "Bạn chi khoảng bao nhiêu mỗi tháng cho dịch vụ streaming?",
                    type: "single",
                    options: [
                        "Dưới 100.000đ",
                        "100.000đ - 200.000đ",
                        "200.000đ - 500.000đ",
                        "Trên 500.000đ"
                    ]
                }
            ]
        },
        {
            id: 3,
            title: "Khảo sát về sức khỏe và thể dục",
            description: "Chia sẻ thói quen chăm sóc sức khỏe của bạn",
            category: "health",
            points: 120,
            time: "6 phút",
            questions: [
                {
                    question: "Bạn tập thể dục bao nhiêu lần mỗi tuần?",
                    type: "single",
                    options: [
                        "Không tập",
                        "1-2 lần",
                        "3-4 lần",
                        "5 lần trở lên"
                    ]
                },
                {
                    question: "Bạn quan tâm đến những loại hình tập luyện nào?",
                    type: "multiple",
                    options: [
                        "Gym",
                        "Yoga",
                        "Chạy bộ",
                        "Bơi lội",
                        "Đạp xe",
                        "Khác"
                    ]
                },
                {
                    question: "Bạn có theo dõi chế độ ăn uống đặc biệt nào không?",
                    type: "single",
                    options: [
                        "Không",
                        "Ăn chay",
                        "Keto",
                        "Low-carb",
                        "Khác"
                    ]
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

    // Display surveys
    function displaySurveys() {
        surveyList.innerHTML = '';
        
        const category = document.getElementById('survey-category').value;
        const sort = document.getElementById('survey-sort').value;
        
        // Filter surveys
        let filteredSurveys = surveys;
        if (category !== 'all') {
            filteredSurveys = surveys.filter(s => s.category === category);
        }
        
        // Sort surveys
        filteredSurveys.sort((a, b) => {
            if (sort === 'newest') return b.id - a.id;
            if (sort === 'highest-point') return b.points - a.points;
            if (sort === 'shortest') return a.time.localeCompare(b.time);
            return 0;
        });
        
        // Display surveys
        filteredSurveys.forEach(survey => {
            const surveyCard = document.createElement('div');
            surveyCard.className = 'survey-card';
            surveyCard.innerHTML = `
                <div class="survey-card-image" style="background-color: ${getCategoryColor(survey.category)}">
                    ${survey.title.charAt(0)}
                </div>
                <div class="survey-card-info">
                    <h3 class="survey-card-title">${survey.title}</h3>
                    <p class="survey-card-description">${survey.description}</p>
                    <div class="survey-card-meta">
                        <span class="survey-card-points">+${survey.points} điểm</span>
                        <span class="survey-card-time">⏱ ${survey.time}</span>
                    </div>
                    <button class="survey-card-btn" data-id="${survey.id}">Tham gia ngay</button>
                </div>
            `;
            surveyList.appendChild(surveyCard);
        });
        
        // Add event listeners to survey buttons
        document.querySelectorAll('.survey-card-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const surveyId = parseInt(this.dataset.id);
                startSurvey(surveyId);
            });
        });
    }

    // Get category color
    function getCategoryColor(category) {
        const colors = {
            shopping: '#3498db',
            entertainment: '#9b59b6',
            technology: '#e74c3c',
            health: '#2ecc71'
        };
        return colors[category] || '#2980b9';
    }

    // Start survey
    function startSurvey(surveyId) {
        currentSurvey = surveys.find(s => s.id === surveyId);
        currentQuestionIndex = 0;
        answers = {};
        
        // Show modal
        document.getElementById('survey-modal-title').textContent = currentSurvey.title;
        document.querySelector('.survey-points').textContent = `+${currentSurvey.points} điểm`;
        surveyModal.style.display = 'block';
        
        // Load first question
        loadQuestion();
    }

    // Load question
    function loadQuestion() {
        const question = currentSurvey.questions[currentQuestionIndex];
        const questionsContainer = document.getElementById('survey-questions');
        
        // Update progress
        const progress = ((currentQuestionIndex + 1) / currentSurvey.questions.length) * 100;
        document.getElementById('survey-progress').style.width = `${progress}%`;
        document.getElementById('progress-text').textContent = `${currentQuestionIndex + 1}/${currentSurvey.questions.length}`;
        
        // Create question HTML
        questionsContainer.innerHTML = '';
        const questionElement = document.createElement('div');
        questionElement.className = 'survey-question';
        
        let optionsHTML = '';
        question.options.forEach((option, i) => {
            const optionId = `option-${currentQuestionIndex}-${i}`;
            const inputType = question.type === 'multiple' ? 'checkbox' : 'radio';
            const inputName = question.type === 'multiple' ? `question-${currentQuestionIndex}[]` : `question-${currentQuestionIndex}`;
            
            // Check if this option was previously selected
            const isChecked = answers[currentQuestionIndex] && 
                            (question.type === 'multiple' 
                            ? answers[currentQuestionIndex].includes(option)
                            : answers[currentQuestionIndex] === option);
            
            optionsHTML += `
                <label class="option-item" for="${optionId}">
                    <input type="${inputType}" id="${optionId}" name="${inputName}" value="${option}" 
                        ${isChecked ? 'checked' : ''}>
                    <span class="option-text">${option}</span>
                </label>
            `;
        });
        
        questionElement.innerHTML = `
            <h3 class="survey-question-title">${question.question}</h3>
            <div class="survey-options">
                ${optionsHTML}
            </div>
        `;
        
        questionsContainer.appendChild(questionElement);
        
        // Update navigation buttons
        document.getElementById('prev-question').disabled = currentQuestionIndex === 0;
        document.getElementById('next-question').style.display = 
            currentQuestionIndex < currentSurvey.questions.length - 1 ? 'block' : 'none';
        document.getElementById('submit-survey').style.display = 
            currentQuestionIndex === currentSurvey.questions.length - 1 ? 'block' : 'none';
    }

    // Save answer
    function saveAnswer() {
        const question = currentSurvey.questions[currentQuestionIndex];
        const inputs = document.querySelectorAll(`input[name^="question-${currentQuestionIndex}"]:checked`);
        
        if (question.type === 'multiple') {
            answers[currentQuestionIndex] = Array.from(inputs).map(input => input.value);
        } else {
            answers[currentQuestionIndex] = inputs.length > 0 ? inputs[0].value : null;
        }
    }

    // Event listeners
    document.getElementById('survey-category').addEventListener('change', displaySurveys);
    document.getElementById('survey-sort').addEventListener('change', displaySurveys);

    document.getElementById('prev-question').addEventListener('click', function() {
        saveAnswer();
        currentQuestionIndex--;
        loadQuestion();
    });

    document.getElementById('next-question').addEventListener('click', function() {
        saveAnswer();
        currentQuestionIndex++;
        loadQuestion();
    });

    document.getElementById('submit-survey').addEventListener('click', function() {
        saveAnswer();
        
        // In a real app, you would send answers to the server here
        console.log('Survey answers:', answers);
        
        // Show completion modal
        surveyModal.style.display = 'none';
        document.getElementById('earned-points').textContent = currentSurvey.points;
        completeModal.style.display = 'flex';
        
        // Add points to user
        addPoints(currentSurvey.points);
        
        // Save to survey history
        saveSurveyHistory(currentSurvey.id);
    });

    document.getElementById('close-complete-modal').addEventListener('click', function() {
        completeModal.style.display = 'none';
    });

    document.querySelector('.close-modal').addEventListener('click', function() {
        surveyModal.style.display = 'none';
    });

    // Add points to user
    function addPoints(points) {
        const pointsDisplay = document.querySelector('.points-display');
        const currentPoints = parseInt(pointsDisplay.textContent.replace(/,/g, ''));
        pointsDisplay.textContent = (currentPoints + points).toLocaleString();
        
        // Save to localStorage
        const userData = JSON.parse(localStorage.getItem('surveyon_user')) || {};
        userData.points = (userData.points || 0) + points;
        localStorage.setItem('surveyon_user', JSON.stringify(userData));
    }

    // Save survey history
    function saveSurveyHistory(surveyId) {
        const history = JSON.parse(localStorage.getItem('survey_history')) || [];
        const survey = surveys.find(s => s.id === surveyId);
        
        history.unshift({
            date: new Date().toISOString(),
            surveyId: survey.id,
            title: survey.title,
            points: survey.points
        });
        
        localStorage.setItem('survey_history', JSON.stringify(history));
    }

    // Initialize
    displaySurveys();

    // Display survey history in profile
    function displaySurveyHistory() {
        const history = JSON.parse(localStorage.getItem('survey_history')) || [];
        const historyList = document.querySelector('.survey-history-list');
        
        if (!historyList) return;
        
        historyList.innerHTML = '';
        
        if (history.length === 0) {
            historyList.innerHTML = `
                <div class="no-survey-history">
                    <p>Bạn chưa tham gia khảo sát nào</p>
                </div>
            `;
            return;
        }
        
        history.forEach(item => {
            const date = new Date(item.date);
            const dateStr = date.toLocaleDateString('vi-VN', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            
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

    // Call this function when profile page loads
    displaySurveyHistory();