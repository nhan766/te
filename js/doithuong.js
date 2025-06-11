// Thêm vào phần JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Lưu trữ dữ liệu phần thưởng
    const rewardsData = [
        {
            id: 1,
            title: "Thẻ quà tặng Shopee 50K",
            points: 5000,
            image: "/api/placeholder/300/300",
            description: "Thẻ quà tặng Shopee 50.000đ, sử dụng để mua sắm trên ứng dụng Shopee hoặc website Shopee.vn",
            terms: [
                "Tài khoản phải có đủ điểm",
                "Mỗi tài khoản chỉ được đổi tối đa 3 thẻ/tháng",
                "Thẻ có hiệu lực trong 30 ngày kể từ ngày nhận"
            ]
        },
        {
            id: 2,
            title: "Thẻ quà tặng Lazada 100K",
            points: 10000,
            image: "/api/placeholder/300/300",
            description: "Thẻ quà tặng Lazada 100.000đ, áp dụng cho tất cả sản phẩm trên Lazada.vn",
            terms: [
                "Tài khoản phải có đủ điểm",
                "Mỗi tài khoản chỉ được đổi tối đa 2 thẻ/tháng",
                "Thẻ có hiệu lực trong 60 ngày"
            ]
        },
        // Thêm các phần thưởng khác
    ];

    // Xử lý click vào nút "Đổi ngay" trên card
    document.querySelectorAll('.redeem-btn').forEach(button => {
        button.addEventListener('click', function() {
            const card = this.closest('.reward-card');
            const rewardId = parseInt(card.dataset.id);
            const reward = rewardsData.find(r => r.id === rewardId);
            
            if (reward) {
                showRewardDetail(reward);
            }
        });
    });

    // Hiển thị trang chi tiết phần thưởng
    function showRewardDetail(reward) {
        document.getElementById('rewards-donation').style.display = 'none';
        document.getElementById('reward-detail').style.display = 'block';
        
        // Điền thông tin phần thưởng
        document.getElementById('detail-reward-image').src = reward.image;
        document.getElementById('detail-reward-title').textContent = reward.title;
        document.getElementById('detail-reward-points').textContent = reward.points.toLocaleString() + ' điểm';
        document.getElementById('detail-reward-description').textContent = reward.description;
        
        const termsList = document.getElementById('detail-reward-terms');
        termsList.innerHTML = '';
        reward.terms.forEach(term => {
            const li = document.createElement('li');
            li.textContent = term;
            termsList.appendChild(li);
        });
        
        // Kiểm tra đủ điểm không
        const userPoints = parseInt(document.querySelector('.points-display').textContent.replace(/,/g, ''));
        document.getElementById('redeem-now').disabled = userPoints < reward.points;
    }

    // Xử lý nút quay lại
    document.getElementById('back-to-rewards').addEventListener('click', function() {
        document.getElementById('rewards-donation').style.display = 'block';
        document.getElementById('reward-detail').style.display = 'none';
    });

    // Xử lý đổi thưởng
    document.getElementById('redeem-now').addEventListener('click', function() {
        const rewardTitle = document.getElementById('detail-reward-title').textContent;
        const rewardPoints = parseInt(document.getElementById('detail-reward-points').textContent.replace(/[^\d]/g, ''));
        
        // Tạo mã ngẫu nhiên
        const rewardCode = 'SNXK-' + Math.random().toString(36).substr(2, 8).toUpperCase();
        
        // Hiển thị popup thành công
        document.getElementById('success-message').textContent = `Bạn đã đổi thành công ${rewardTitle}`;
        document.getElementById('reward-code').textContent = rewardCode;
        document.getElementById('success-popup').style.display = 'flex';
        
        // Cập nhật điểm (trừ điểm)
        const pointsDisplay = document.querySelector('.points-display');
        const currentPoints = parseInt(pointsDisplay.textContent.replace(/,/g, ''));
        pointsDisplay.textContent = (currentPoints - rewardPoints).toLocaleString();
        
        // Lưu vào lịch sử đổi thưởng
        saveRewardHistory(rewardTitle, rewardPoints, rewardCode);
    });

    // Đóng popup
    document.getElementById('close-popup').addEventListener('click', function() {
        document.getElementById('success-popup').style.display = 'none';
    });

    // Về trang chủ
    document.getElementById('go-home').addEventListener('click', function() {
        document.getElementById('success-popup').style.display = 'none';
        document.getElementById('rewards-donation').style.display = 'block';
        document.getElementById('reward-detail').style.display = 'none';
        // Scroll lên đầu trang
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Lưu lịch sử đổi thưởng
    function saveRewardHistory(title, points, code) {
        const history = JSON.parse(localStorage.getItem('reward_history')) || [];
        const newItem = {
            date: new Date().toISOString(),
            title: title,
            points: points,
            code: code,
            status: 'Đã nhận'
        };
        history.unshift(newItem);
        localStorage.setItem('reward_history', JSON.stringify(history));
    }
});