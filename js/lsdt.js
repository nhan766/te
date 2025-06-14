document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('reward-history-table-body');
    const rewardHistory = JSON.parse(localStorage.getItem('reward_history')) || [];

    if (!tableBody) return;

    if (rewardHistory.length === 0) {
        const row = `<tr><td colspan="5" class="no-history">Chưa có lịch sử đổi thưởng nào.</td></tr>`;
        tableBody.innerHTML = row;
        return;
    }

    rewardHistory.forEach(item => {
        const date = new Date(item.date).toLocaleDateString('vi-VN');
        const row = `
            <tr>
                <td>${date}</td>
                <td class="reward-title">${item.title}</td>
                <td class="reward-points">-${item.points.toLocaleString()}</td>
                <td><span class="reward-code">${item.code}</span></td>
                <td><span class="status-badge completed">${item.status}</span></td>
            </tr>
        `;
        tableBody.innerHTML += row;
    });
});