document.addEventListener('DOMContentLoaded', () => {
    // Thay đổi selector để trỏ đến container mới
    const listContainer = document.getElementById('reward-history-list');
    const rewardHistory = JSON.parse(localStorage.getItem('reward_history')) || [];

    if (!listContainer) return;

    if (rewardHistory.length === 0) {
        listContainer.innerHTML = `<div class="no-history">Chưa có lịch sử đổi thưởng nào.</div>`;
        return;
    }

    // Tạo HTML cho mỗi thẻ lịch sử
    rewardHistory.forEach(item => {
        const date = new Date(item.date).toLocaleDateString('vi-VN');
        const cardHTML = `
            <div class="history-card">
                <div class="history-icon">
                    <i class="fas fa-gift"></i>
                </div>
                <div class="history-info">
                    <h4 class="reward-title">${item.title}</h4>
                    <p class="reward-date">${date}</p>
                </div>
                <div class="history-details">
                    <div class="detail-item">
                        <span class="label">Điểm</span>
                        <span class="reward-points">-${item.points.toLocaleString()}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Mã Voucher</span>
                        <span class="reward-code">${item.code}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Trạng thái</span>
                        <span class="status-badge">${item.status}</span>
                    </div>
                </div>
            </div>
        `;
        listContainer.innerHTML += cardHTML;
    });
});