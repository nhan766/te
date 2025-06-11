// User Profile Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Load user data from localStorage or use default
    const userData = JSON.parse(localStorage.getItem('surveyon_user')) || {
        name: "Nguyễn Văn A",
        email: "nguyenvana@example.com",
        phone: "0987654321",
        birthday: "1990-01-01",
        gender: "male",
        points: 2450,
        surveys: 24,
        joinDate: "05/2023",
        level: "silver",
        activities: [
            {
                date: "2024-05-15",
                content: "Hoàn thành khảo sát về thói quen mua sắm (+50 điểm)"
            },
            {
                date: "2024-05-14",
                content: "Đổi 1000 điểm lấy thẻ quà tặng Shopee"
            },
            {
                date: "2024-05-10",
                content: "Hoàn thành khảo sát hàng tuần (+100 điểm)"
            },
            {
                date: "2024-05-08",
                content: "Giới thiệu bạn bè tham gia (+1000 điểm)"
            }
        ]
    };
    
    // Populate profile data
    document.getElementById('user-name').textContent = userData.name;
    document.getElementById('input-name').value = userData.name;
    document.getElementById('input-email').value = userData.email;
    document.getElementById('input-phone').value = userData.phone;
    document.getElementById('input-birthday').value = userData.birthday;
    document.getElementById('input-gender').value = userData.gender;
    document.getElementById('total-points').textContent = userData.points.toLocaleString();
    document.getElementById('total-surveys').textContent = userData.surveys;
    document.getElementById('member-since').textContent = userData.joinDate;
    
    // Set level badge
    const levelBadge = document.querySelector('.level-badge');
    levelBadge.className = 'level-badge ' + userData.level;
    levelBadge.textContent = userData.level === 'bronze' ? 'Đồng' : 
                            userData.level === 'silver' ? 'Bạc' : 'Vàng';
    
    // Populate activity timeline
    const timeline = document.querySelector('.activity-timeline');
    timeline.innerHTML = '';
    
    userData.activities.forEach(activity => {
        const activityDate = new Date(activity.date);
        const dateStr = activityDate.toLocaleDateString('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
        
        const activityItem = document.createElement('div');
        activityItem.className = 'activity-item';
        activityItem.innerHTML = `
            <div class="activity-date">${dateStr}</div>
            <div class="activity-content">${activity.content}</div>
        `;
        
        timeline.appendChild(activityItem);
    });
    
    // Handle form submission
    const profileForm = document.querySelector('.profile-form');
    profileForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Update user data
        userData.name = document.getElementById('input-name').value;
        userData.email = document.getElementById('input-email').value;
        userData.phone = document.getElementById('input-phone').value;
        userData.birthday = document.getElementById('input-birthday').value;
        userData.gender = document.getElementById('input-gender').value;
        
        // Save to localStorage
        localStorage.setItem('surveyon_user', JSON.stringify(userData));
        
        // Update displayed name
        document.getElementById('user-name').textContent = userData.name;
        
        // Show success message
        alert('Thông tin cá nhân đã được cập nhật!');
    });
    
    // Handle avatar change
    const editAvatarBtn = document.querySelector('.edit-avatar');
    editAvatarBtn.addEventListener('click', function() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        
        input.onchange = function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('user-avatar').src = event.target.result;
                    // In a real app, you would upload this to your server
                };
                reader.readAsDataURL(file);
            }
        };
        
        input.click();
    });
});

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