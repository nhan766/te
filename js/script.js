// Sự kiện này đảm bảo tất cả các mã JavaScript sẽ chỉ chạy sau khi toàn bộ cây HTML đã được tải xong.
document.addEventListener('DOMContentLoaded', () => {

    // --- Logic cho Menu Mobile ---
    const menuToggler = document.querySelector('.mobile-menu-toggler');
    const navMenu = document.querySelector('.nav-menu');
    if (menuToggler && navMenu) {
        menuToggler.addEventListener('click', () => {
            menuToggler.classList.toggle('active');
            navMenu.classList.toggle('active');
        });
        document.querySelectorAll('.nav-menu a').forEach(link => {
            link.addEventListener('click', () => {
                menuToggler.classList.remove('active');
                navMenu.classList.remove('active');
            });
        });
    }

    // --- Logic cho FAQ Accordion ---
    document.querySelectorAll('.faq-question').forEach(question => {
        question.addEventListener('click', () => {
            const item = question.parentNode;
            const isActive = item.classList.contains('active');
            document.querySelectorAll('.faq-item').forEach(faqItem => {
                faqItem.classList.remove('active');
            });
            if (!isActive) {
                item.classList.add('active');
            }
        });
    });
    
    // --- Logic cho Rewards Tab System (trên trang doithuong.html) ---
    document.querySelectorAll('.tab-btn').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            
            const selectedCategory = tab.getAttribute('data-tab');
            document.querySelectorAll('.reward-card').forEach(card => {
                if (selectedCategory === 'all-rewards' || card.classList.contains(selectedCategory)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
    
    // --- Logic cho Pagination (trên trang doithuong.html và khaosat.html) ---
    document.querySelectorAll('.pagination-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.pagination-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });

    // --- Logic cho Hiệu ứng Fade-in khi cuộn ---
    const faders = document.querySelectorAll('.fade-in');
    if (faders.length > 0) {
        const appearOptions = { threshold: 0.2 };
        const appearOnScroll = new IntersectionObserver(function(entries, observer) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, appearOptions);
        faders.forEach(fader => appearOnScroll.observe(fader));
    }

    // =============================================
    // --- LOGIC MỚI CHO HIỆU ỨNG ĐẾM SỐ ---
    // =============================================
    const counters = document.querySelectorAll('.metric-value, .impact-number');
    if (counters.length > 0) {
        const animateCounter = (element) => {
            // Lấy giá trị gốc và giá trị mục tiêu
            const originalText = element.textContent;
            let target;
            if(element.dataset.target) {
                target = +element.dataset.target; // Ưu tiên lấy từ data-target
            } else {
                const value = parseFloat(originalText.replace(/,/g, ''));
                if (originalText.toLowerCase().includes('m')) { target = value * 1000000; }
                else if (originalText.toLowerCase().includes('k')) { target = value * 1000; }
                else { target = value; }
            }

            if (isNaN(target)) return; // Bỏ qua nếu không phải là số

            let current = 0;
            const duration = 2000; // 2 giây
            const frameDuration = 1000 / 60; // 60fps
            const totalFrames = Math.round(duration / frameDuration);
            const increment = target / totalFrames;

            const counterInterval = setInterval(() => {
                current += increment;
                if (current >= target) {
                    clearInterval(counterInterval);
                    element.textContent = originalText; // Hiển thị lại văn bản gốc khi xong
                } else {
                    element.textContent = Math.round(current).toLocaleString('en-US');
                }
            }, frameDuration);
        };

        const counterObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        counters.forEach(counter => {
            counterObserver.observe(counter);
        });
    }

});


// --- Logic cho Loader (sự kiện 'load' chạy sau DOMContentLoaded) ---
window.addEventListener('load', function() {
    const loader = document.querySelector('.loader');
    if (loader) {
        setTimeout(function() {
            loader.classList.add('hidden');
        }, 500); // Thêm một chút trễ để người dùng kịp thấy loader
    }
});