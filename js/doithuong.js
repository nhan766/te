document.addEventListener('DOMContentLoaded', () => {

    // --- HÀM TẢI VÀ HIỂN THỊ TRẠNG THÁI NGƯỜI DÙNG ---
    function loadUserStatus() {
        // 1. Tải dữ liệu người dùng
        const defaultUser = { points: 0, level: 'Bronze', surveys: 0 };
        const savedUserData = JSON.parse(localStorage.getItem('surveyon_user')) || {};
        const userData = { ...defaultUser, ...savedUserData };

        // 2. Tải lịch sử hoạt động
        const activities = JSON.parse(localStorage.getItem('survey_activities')) || [];

        // 3. Cập nhật giao diện
        const pointsDisplay = document.getElementById('user-points');
        if (pointsDisplay) {
            pointsDisplay.textContent = userData.points.toLocaleString();
        }

        const levelDisplay = document.getElementById('user-level');
        if (levelDisplay) {
            levelDisplay.textContent = userData.level.charAt(0).toUpperCase() + userData.level.slice(1);
        }

        const progress = document.getElementById('user-level-progress');
        const progressNote = document.getElementById('user-level-note');
        if(progress && progressNote) {
            if(userData.level === 'Bạc') {
                progress.style.width = '65%';
                progressNote.textContent = '3,550 điểm nữa để lên Vàng';
            } else {
                progress.style.width = '10%';
                progressNote.textContent = 'Khởi đầu mới';
            }
        }

        const activityList = document.getElementById('recent-activity-list');
        if (activityList) {
            activityList.innerHTML = '';
            const recentActivities = activities.slice(0, 3);
            if(recentActivities.length === 0) {
                activityList.innerHTML = '<li>Chưa có hoạt động nào.</li>';
            } else {
                recentActivities.forEach(activity => {
                    const pointsMatch = activity.content.match(/([+-]\d+)/);
                    const points = pointsMatch ? `<span>${pointsMatch[0]}</span>` : '';
                    const contentText = activity.content.replace(/\(\s*[+-]\d+\s*điểm\s*\)/, '').trim();
                    activityList.innerHTML += `<li>${points} ${contentText}</li>`;
                });
            }
        }
    }

    // --- LOGIC XỬ LÝ SỰ KIỆN ĐỔI THƯỞNG ---
    const redeemButtons = document.querySelectorAll('.redeem-btn');
    const confirmModal = document.getElementById('confirm-modal');
    const successModal = document.getElementById('success-modal');
    const confirmText = document.getElementById('confirm-text');
    const voucherCodeDisplay = document.getElementById('voucher-code-display');
    const confirmBtn = document.getElementById('confirm-redeem');
    const cancelBtn = document.getElementById('cancel-redeem');
    const closeSuccessBtn = document.getElementById('close-success-modal');

    let currentReward = null;

    if (redeemButtons.length > 0 && confirmModal && successModal) {
        redeemButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const card = e.target.closest('.reward-card');
                currentReward = {
                    id: card.dataset.id,
                    title: card.dataset.title,
                    points: parseInt(card.dataset.points)
                };
                
                const userData = JSON.parse(localStorage.getItem('surveyon_user')) || { points: 0 };
                
                if (userData.points < currentReward.points) {
                    alert('Bạn không đủ điểm để đổi phần thưởng này!');
                    return;
                }

                confirmText.textContent = `Bạn có chắc chắn muốn dùng ${currentReward.points.toLocaleString()} điểm để đổi "${currentReward.title}" không?`;
                confirmModal.classList.add('active');
            });
        });

        confirmBtn.addEventListener('click', () => {
            const userData = JSON.parse(localStorage.getItem('surveyon_user')) || { points: 0 };
            
            if (userData.points < currentReward.points) {
                alert('Rất tiếc, bạn không đủ điểm!');
                confirmModal.classList.remove('active');
                return;
            }

            userData.points -= currentReward.points;
            localStorage.setItem('surveyon_user', JSON.stringify(userData));

            const voucherCode = 'SVN-' + Math.random().toString(36).substr(2, 8).toUpperCase();

            const rewardHistory = JSON.parse(localStorage.getItem('reward_history')) || [];
            const newHistoryItem = {
                date: new Date().toISOString(),
                title: currentReward.title,
                points: currentReward.points,
                code: voucherCode,
                status: 'Đã nhận'
            };
            rewardHistory.unshift(newHistoryItem);
            localStorage.setItem('reward_history', JSON.stringify(rewardHistory));

            confirmModal.classList.remove('active');
            voucherCodeDisplay.textContent = voucherCode;
            successModal.classList.add('active');

            // Cập nhật lại giao diện ngay sau khi đổi thưởng
            loadUserStatus();
        });

        cancelBtn.addEventListener('click', () => confirmModal.classList.remove('active'));
        closeSuccessBtn.addEventListener('click', () => successModal.classList.remove('active'));
    }
    
    // --- GỌI HÀM TẢI DỮ LIỆU KHI TRANG ĐƯỢC MỞ ---
    loadUserStatus();
});