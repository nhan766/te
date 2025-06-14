document.addEventListener('DOMContentLoaded', () => {
    // DOM Elements
    const redeemButtons = document.querySelectorAll('.redeem-btn');
    const confirmModal = document.getElementById('confirm-modal');
    const successModal = document.getElementById('success-modal');
    const confirmText = document.getElementById('confirm-text');
    const voucherCodeDisplay = document.getElementById('voucher-code-display');
    const confirmBtn = document.getElementById('confirm-redeem');
    const cancelBtn = document.getElementById('cancel-redeem');
    const closeSuccessBtn = document.getElementById('close-success-modal');

    let currentReward = null;

    // Lắng nghe sự kiện click trên tất cả các nút "Đổi ngay"
    redeemButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            const card = e.target.closest('.reward-card');
            currentReward = {
                id: card.dataset.id,
                title: card.dataset.title,
                points: parseInt(card.dataset.points)
            };
            
            // Lấy điểm của người dùng từ localStorage
            const userData = JSON.parse(localStorage.getItem('surveyon_user')) || { points: 0 };
            
            // Kiểm tra đủ điểm
            if (userData.points < currentReward.points) {
                alert('Bạn không đủ điểm để đổi phần thưởng này!');
                return;
            }

            // Hiển thị pop-up xác nhận
            confirmText.textContent = `Bạn có chắc chắn muốn dùng ${currentReward.points.toLocaleString()} điểm để đổi "${currentReward.title}" không?`;
            confirmModal.classList.add('active');
        });
    });

    // Xử lý khi nhấn nút "Xác nhận"
    confirmBtn.addEventListener('click', () => {
        // 1. Trừ điểm người dùng
        const userData = JSON.parse(localStorage.getItem('surveyon_user')) || { points: 0 };
        userData.points -= currentReward.points;
        localStorage.setItem('surveyon_user', JSON.stringify(userData));

        // 2. Tạo mã voucher ngẫu nhiên
        const voucherCode = 'SVN-' + Math.random().toString(36).substr(2, 8).toUpperCase();

        // 3. Lưu vào lịch sử đổi thưởng
        const rewardHistory = JSON.parse(localStorage.getItem('reward_history')) || [];
        const newHistoryItem = {
            date: new Date().toISOString(),
            title: currentReward.title,
            points: currentReward.points,
            code: voucherCode,
            status: 'Đã nhận'
        };
        rewardHistory.unshift(newHistoryItem); // Thêm vào đầu mảng
        localStorage.setItem('reward_history', JSON.stringify(rewardHistory));

        // 4. Hiển thị pop-up thành công
        confirmModal.classList.remove('active');
        voucherCodeDisplay.textContent = voucherCode;
        successModal.classList.add('active');
    });

    // Xử lý các nút đóng/hủy
    cancelBtn.addEventListener('click', () => confirmModal.classList.remove('active'));
    closeSuccessBtn.addEventListener('click', () => successModal.classList.remove('active'));
});