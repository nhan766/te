document.addEventListener('DOMContentLoaded', function() {
    loadUserDataAndHistories();
    setupEventListeners();
});

function loadUserDataAndHistories() {
    const defaultUser = {
        name: "Nguyễn Văn A",
        email: "nguyenvana@example.com",
        phone: "0987654321",
        birthday: "1990-01-01",
        gender: "male",
        points: 0,
        surveys: 0,
        joinDate: "06/2025",
        level: "bronze"
    };
    const savedUserData = JSON.parse(localStorage.getItem('surveyon_user')) || {};
    const userData = { ...defaultUser, ...savedUserData };

    const activities = JSON.parse(localStorage.getItem('survey_activities')) || [];
    const rewards = JSON.parse(localStorage.getItem('reward_history')) || [];

    // Điền thông tin người dùng
    document.getElementById('user-name').textContent = userData.name;
    document.getElementById('input-name').value = userData.name;
    document.getElementById('input-email').value = userData.email;
    document.getElementById('input-phone').value = userData.phone;
    document.getElementById('input-birthday').value = userData.birthday;
    document.getElementById('input-gender').value = userData.gender;
    document.getElementById('total-points').textContent = userData.points.toLocaleString();
    document.getElementById('total-surveys').textContent = userData.surveys;
    document.getElementById('member-since').textContent = userData.joinDate;
    
    const levelBadge = document.querySelector('.level-badge');
    if(levelBadge) {
        levelBadge.className = 'level-badge ' + userData.level;
        levelBadge.textContent = userData.level.charAt(0).toUpperCase() + userData.level.slice(1);
    }
    
    displayActivityHistory(activities);
    displayRewardHistorySummary(rewards); 
}

function displayActivityHistory(activities) {
    const historyList = document.querySelector('.survey-history-list');
    if (!historyList) return;
    
    historyList.innerHTML = '';
    
    if (activities.length === 0) {
        historyList.innerHTML = `<div class="no-survey-history"><p>Chưa có hoạt động nào.</p></div>`;
        return;
    }
    
    activities.forEach(activity => {
        const activityDate = new Date(activity.date);
        const timeStr = activityDate.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
        const dateStr = activityDate.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
        const formattedDate = `${timeStr} ${dateStr}`;
        const pointsMatch = activity.content.match(/\(\+(\d+)\s*điểm\)/);
        const points = pointsMatch ? `+${pointsMatch[1]} điểm` : '';
        const contentText = activity.content.replace(/\(\+\d+\s*điểm\)/, '').trim();

        const historyItemHTML = `
            <div class="survey-history-item">
                <div>
                    <div class="survey-history-title">${contentText}</div>
                    <div class="survey-history-date">${formattedDate}</div>
                </div>
                <div class="survey-history-points">${points}</div>
            </div>
        `;
        historyList.innerHTML += historyItemHTML;
    });
}

function displayRewardHistorySummary(history) {
    const summaryContainer = document.getElementById('reward-history-summary');
    if (!summaryContainer) return;
    summaryContainer.innerHTML = '';
    if (history.length === 0) {
        summaryContainer.innerHTML = `<div class="no-reward-history"><p>Bạn chưa đổi thưởng lần nào.</p></div>`;
        return;
    }
    const recentHistory = history.slice(0, 3);
    recentHistory.forEach(item => {
        const date = new Date(item.date).toLocaleDateString('vi-VN');
        const historyItemHTML = `
            <div class="reward-history-item">
                <div class="reward-history-info">
                    <div class="reward-history-title">${item.title}</div>
                    <div class="reward-history-date">${date}</div>
                </div>
                <div class="reward-history-points">-${item.points.toLocaleString()}</div>
            </div>
        `;
        summaryContainer.innerHTML += historyItemHTML;
    });
}

function setupEventListeners() {
    const profileForm = document.querySelector('.profile-form');
    profileForm?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const savedUserData = JSON.parse(localStorage.getItem('surveyon_user')) || {};
        const userData = { ...savedUserData };
        userData.name = document.getElementById('input-name').value;
        userData.email = document.getElementById('input-email').value;
        userData.phone = document.getElementById('input-phone').value;
        userData.birthday = document.getElementById('input-birthday').value;
        userData.gender = document.getElementById('input-gender').value;

        localStorage.setItem('surveyon_user', JSON.stringify(userData));
        document.getElementById('user-name').textContent = userData.name;
        alert('Thông tin cá nhân đã được cập nhật!');
    });

    const editAvatarBtn = document.querySelector('.edit-avatar');
    editAvatarBtn?.addEventListener('click', function() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        input.onchange = function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('user-avatar').src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        };
        input.click();
    });
}