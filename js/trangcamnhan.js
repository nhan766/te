// js/trangcamnhan.js

// Hàm chính, chạy khi trang được tải
document.addEventListener('DOMContentLoaded', function() {
    loadUserDataAndHistories();
    setupEventListeners();
});


/**
 * Tải và hiển thị tất cả thông tin người dùng và các lịch sử liên quan.
 */
function loadUserDataAndHistories() {
    // 1. Tải và hiển thị thông tin cá nhân
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
    const userData = { ...defaultUser, ...savedUserData }; // Trộn dữ liệu để chống lỗi 'undefined'

    // Điền thông tin lên giao diện
    document.getElementById('user-name').textContent = userData.name;
    document.getElementById('input-name').value = userData.name;
    document.getElementById('input-email').value = userData.email;
    document.getElementById('input-phone').value = userData.phone;
    document.getElementById('input-birthday').value = userData.birthday;
    document.getElementById('input-gender').value = userData.gender;
    document.getElementById('total-points').textContent = userData.points.toLocaleString();
    document.getElementById('total-surveys').textContent = userData.surveys;
    document.getElementById('member-since').textContent = userData.joinDate;
    
    // Cập nhật huy hiệu
    const levelBadge = document.querySelector('.level-badge');
    if(levelBadge) {
        levelBadge.className = 'level-badge ' + userData.level;
        levelBadge.textContent = userData.level.charAt(0).toUpperCase() + userData.level.slice(1);
    }
    
    // 2. Tải và hiển thị lịch sử hoạt động (khảo sát)
    const activities = JSON.parse(localStorage.getItem('survey_activities')) || [];
    displayActivityHistory(activities);

    // 3. Tải và hiển thị lịch sử đổi thưởng
    const rewards = JSON.parse(localStorage.getItem('reward_history')) || [];
    displayRewardHistorySummary(rewards);
}


/**
 * Hiển thị lịch sử hoạt động (khảo sát)
 */
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

        const historyItem = `
            <div class="survey-history-item">
                <div>
                    <div class="survey-history-title">${contentText}</div>
                    <div class="survey-history-date">${formattedDate}</div>
                </div>
                <div class="survey-history-points">${points}</div>
            </div>
        `;
        historyList.innerHTML += historyItem;
    });
}

/**
 * Hiển thị tóm tắt lịch sử đổi thưởng
 */
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


/**
 * Gắn các sự kiện cho các nút bấm
 */
function setupEventListeners() {
    // Xử lý khi lưu thông tin cá nhân
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

    // Xử lý khi đổi ảnh đại diện
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