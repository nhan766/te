document.addEventListener('DOMContentLoaded', function() {
    // Sample forum data
    const forumData = {
        posts: [
            { id: 1, title: "Cách kiếm điểm nhanh nhất cho người mới?", category: "tips", content: "Mình mới tham gia SurveyOn được 1 tuần...", author: "Trần Thị B", avatar: "https://i.pravatar.cc/45?img=5", date: "2024-05-15", comments: 5, likes: 12 },
            { id: 2, title: "Review quá trình đổi thẻ quà tặng Shopee 100K", category: "rewards", content: "Mình vừa đổi thẻ quà tặng Shopee 100k...", author: "Nguyễn Văn C", avatar: "https://i.pravatar.cc/45?img=8", date: "2024-05-14", comments: 3, likes: 8 },
            { id: 3, title: "Hoàn thành khảo sát nhưng không nhận được điểm?", category: "questions", content: "Mình vừa hoàn thành một khảo sát dài...", author: "Lê Thị D", avatar: "https://i.pravatar.cc/45?img=7", date: "2024-05-12", comments: 7, likes: 2 }
        ]
    };
    
    // Load posts from localStorage or use sample data
    let posts = JSON.parse(localStorage.getItem('surveyon_posts')) || forumData.posts;
    
    // Modal elements
    const postsList = document.querySelector('.posts-list');
    const modal = document.getElementById('new-post-modal');
    const newPostBtn = document.querySelector('.new-post-button');
    const closeModalBtn = document.querySelector('.close-modal');
    const postForm = document.getElementById('new-post-form');
    
    // Display posts
    function displayPosts(filterCategory = 'all') {
        if (!postsList) {
            console.error("Error: '.posts-list' element not found.");
            return;
        }
        postsList.innerHTML = '';
        
        const filteredPosts = filterCategory === 'all' 
            ? posts 
            : posts.filter(post => post.category === filterCategory);
        
        if (filteredPosts.length === 0) {
            postsList.innerHTML = '<div class="no-posts">Chưa có bài viết nào trong danh mục này.</div>';
            return;
        }
        
        const categoryMapping = {
            'tips': { name: 'Mẹo kiếm điểm', icon: 'fa-solid fa-lightbulb' },
            'rewards': { name: 'Đổi thưởng', icon: 'fa-solid fa-gift' },
            'questions': { name: 'Câu hỏi', icon: 'fa-solid fa-circle-question' },
            'feedback': { name: 'Góp ý', icon: 'fa-solid fa-comments' }
        };

        filteredPosts.forEach(post => {
            const postDate = new Date(post.date);
            const dateStr = postDate.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
            const categoryInfo = categoryMapping[post.category] || { name: 'Khác', icon: 'fa-solid fa-tag' };
            const contentPreview = post.content.length > 150 ? post.content.substring(0, 150) + "..." : post.content;
            
            const postItem = document.createElement('div');
            postItem.className = 'post-item';

            // CẬP NHẬT: Thêm nút xóa vào mã HTML
            postItem.innerHTML = `
                <button class="delete-post-btn" data-id="${post.id}" title="Xóa bài viết">&times;</button>
                <div class="post-header">
                    <img src="https://i.pravatar.cc/45?u=${post.author}" alt="Avatar" class="post-avatar">
                    <div class="post-author-info">
                        <div class="post-author">${post.author}</div>
                        <div class="post-date">${dateStr}</div>
                    </div>
                    <span class="post-category">${categoryInfo.name}</span>
                </div>
                <h3 class="post-title">${post.title}</h3>
                <div class="post-content-preview">${contentPreview}</div>
                <div class="post-footer">
                    <span><i class="fa-regular fa-comment-dots"></i> ${post.comments}</span>
                    <span><i class="fa-regular fa-thumbs-up"></i> ${post.likes}</span>
                </div>
            `;
            postsList.appendChild(postItem);
        });
    }
    
    // --- THÊM HÀM VÀ SỰ KIỆN XỬ LÝ XÓA BÀI VIẾT ---
    function handleDeletePost(e) {
        if (e.target.classList.contains('delete-post-btn')) {
            const postId = parseInt(e.target.dataset.id);
            const isConfirmed = confirm('Bạn có chắc chắn muốn xóa bài viết này không?');
            
            if (isConfirmed) {
                posts = posts.filter(post => post.id !== postId);
                localStorage.setItem('surveyon_posts', JSON.stringify(posts));
                // Lấy category đang active để hiển thị lại đúng danh sách
                const activeCategory = document.querySelector('.forum-categories li.active a')?.dataset.category || 'all';
                displayPosts(activeCategory);
            }
        }
    }

    // Gắn sự kiện click vào cả danh sách để xử lý nút xóa
    if(postsList) {
        postsList.addEventListener('click', handleDeletePost);
    }
    
    // Filter posts by category (giữ nguyên)
    document.querySelectorAll('.forum-categories a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const category = this.getAttribute('data-category');
            document.querySelectorAll('.forum-categories li').forEach(li => li.classList.remove('active'));
            this.parentElement.classList.add('active');
            displayPosts(category);
        });
    });
    
    // Modal handling (giữ nguyên)
    if (newPostBtn && modal && closeModalBtn && postForm) {
        newPostBtn.addEventListener('click', () => modal.classList.add('active'));
        closeModalBtn.addEventListener('click', () => modal.classList.remove('active'));
        modal.addEventListener('click', function(e) { if (e.target === this) modal.classList.remove('active'); });
        
        postForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const userData = JSON.parse(localStorage.getItem('surveyon_user')) || { name: "Người dùng mới", avatar: `https://i.pravatar.cc/45?img=${Math.floor(Math.random() * 70)}` };
            const newPost = { id: Date.now(), title: document.getElementById('post-title').value, category: document.getElementById('post-category').value, content: document.getElementById('post-content').value, author: userData.name, avatar: userData.avatar, date: new Date().toISOString().split('T')[0], comments: 0, likes: 0 };
            
            posts.unshift(newPost);
            localStorage.setItem('surveyon_posts', JSON.stringify(posts));
            
            postForm.reset();
            modal.classList.remove('active');
            
            // Chuyển về tab "Tất cả" và hiển thị lại
            document.querySelector('.forum-categories li.active')?.classList.remove('active');
            document.querySelector('.forum-categories li:first-child').classList.add('active');
            displayPosts();
            
            alert('Bài viết của bạn đã được đăng thành công!');
        });
    }

    // Intersection Observer for fade-in effect (giữ nguyên)
    const faders = document.querySelectorAll('.fade-in');
    const appearOptions = { threshold: 0.1, rootMargin: "0px 0px -50px 0px" };
    const appearOnScroll = new IntersectionObserver(function(entries, observer) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, appearOptions);
    faders.forEach(fader => appearOnScroll.observe(fader));

    // Initial display
    displayPosts();
});