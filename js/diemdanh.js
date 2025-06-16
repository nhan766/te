// js/diemdanh.js
document.addEventListener('DOMContentLoaded', () => {
    const calendarGrid = document.getElementById('calendar-grid');
    const currentMonthYearEl = document.getElementById('current-month-year');
    const checkinBtn = document.getElementById('checkin-btn');
    const prevMonthBtn = document.getElementById('prev-month-btn');
    const nextMonthBtn = document.getElementById('next-month-btn');

    let currentDate = new Date();

    function renderCalendar(year, month) {
        if (!calendarGrid || !currentMonthYearEl) return;
        calendarGrid.innerHTML = '';
        currentMonthYearEl.textContent = `Tháng ${month + 1}, ${year}`;
        
        const firstDayOfMonth = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        const checkinHistory = JSON.parse(localStorage.getItem('checkin_history')) || [];

        for (let i = 0; i < firstDayOfMonth; i++) {
            calendarGrid.innerHTML += '<div class="day-cell"></div>';
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const dayCell = document.createElement('div');
            dayCell.classList.add('day-cell', 'valid-day');
            
            const dayNumber = document.createElement('span');
            dayNumber.className = 'day-number';
            dayNumber.textContent = day;
            dayCell.appendChild(dayNumber);

            const today = new Date();
            if (day === today.getDate() && month === today.getMonth() && year === today.getFullYear()) {
                dayCell.classList.add('today');
            }

            const dateString = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            if (checkinHistory.includes(dateString)) {
                dayCell.classList.add('checked-in');
            }

            calendarGrid.appendChild(dayCell);
        }
    }

    function handleCheckin() {
        const today = new Date();
        const todayString = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

        let checkinHistory = JSON.parse(localStorage.getItem('checkin_history')) || [];
        
        if (checkinHistory.includes(todayString)) {
            alert('Bạn đã điểm danh hôm nay rồi!');
            return;
        }

        // 1. Thêm vào lịch sử điểm danh
        checkinHistory.push(todayString);
        localStorage.setItem('checkin_history', JSON.stringify(checkinHistory));

        // 2. Cộng điểm
        const pointsToAdd = 10;
        const userData = JSON.parse(localStorage.getItem('surveyon_user')) || { points: 0, surveys: 0 };
        userData.points = (userData.points || 0) + pointsToAdd;
        localStorage.setItem('surveyon_user', JSON.stringify(userData));

        // 3. GHI VÀO LỊCH SỬ HOẠT ĐỘNG
        const newActivity = {
            content: `Điểm danh hàng ngày (+${pointsToAdd} điểm)`,
            date: new Date().toISOString()
        };
        const activities = JSON.parse(localStorage.getItem('survey_activities')) || [];
        activities.unshift(newActivity);
        localStorage.setItem('survey_activities', JSON.stringify(activities));

        alert(`Điểm danh thành công! Bạn nhận được +${pointsToAdd} điểm.`);
        
        // Cập nhật lại giao diện trang điểm danh
        renderCalendar(currentDate.getFullYear(), currentDate.getMonth());
        updateCheckinButtonState();
    }

    function updateCheckinButtonState() {
        const today = new Date();
        const todayString = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
        const checkinHistory = JSON.parse(localStorage.getItem('checkin_history')) || [];

        if(checkinBtn) {
            if (checkinHistory.includes(todayString)) {
                checkinBtn.disabled = true;
                checkinBtn.textContent = 'Đã điểm danh';
            } else {
                checkinBtn.disabled = false;
                checkinBtn.textContent = 'Điểm danh ngay';
            }
        }
    }

    prevMonthBtn?.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar(currentDate.getFullYear(), currentDate.getMonth());
    });

    nextMonthBtn?.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar(currentDate.getFullYear(), currentDate.getMonth());
    });

    checkinBtn?.addEventListener('click', handleCheckin);

    // Khởi tạo
    renderCalendar(currentDate.getFullYear(), currentDate.getMonth());
    updateCheckinButtonState();
});