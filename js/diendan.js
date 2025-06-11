// Forum Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Sample forum data
    const forumData = {
        posts: [
            {
                id: 1,
                title: "Cách kiếm điểm nhanh nhất?",
                category: "tips",
                content: "Mình mới tham gia SurveyOn được 1 tuần, có bạn nào có mẹo gì để kiếm điểm nhanh không ạ? Mình cảm ơn!",
                author: "Trần Thị B",
                avatar: "/api/placeholder/40/40",
                date: "2024-05-15",
                comments: 5,
                likes: 12
            },
            {
                id: 2,
                title: "Review thẻ quà tặng Shopee",
                category: "rewards",
                content: "Mình vừa đổi thẻ quà tặng Shopee 100k, nhận code ngay sau khi đổi điểm và sử dụng được luôn. Rất hài lòng!",
                author: "Nguyễn Văn C",
                avatar: "/api/placeholder/40/40",
                date: "2024-05-14",
                comments: 3,
                likes: 8
            },
            {
                id: 3,
                title: "Khảo sát không nhận điểm",
                category: "questions",
                content: "Mình hoàn thành khảo sát nhưng không nhận được điểm, phải làm sao ạ?",
                author: "Lê Thị D",
                avatar: "/api/placeholder/40/40",
                date: "2024-05-12",
                comments: 7,
                likes: 2
            }
        ]
    };
    
    // Load posts from localStorage or use sample data
    let posts = JSON.parse(localStorage.getItem('surveyon_posts')) || forumData.posts;
    
    // Modal elements
    const modal = document.getElementById('new-post-modal');
    const newPostBtn = document.querySelector('.new-post-button');
    const closeModal = document.querySelector('.close-modal');
    const postForm = document.getElementById('new-post-form');
    
    // Display posts
    function displayPosts(filterCategory = 'all') {
        const postsList = document.querySelector('.posts-list');
        postsList.innerHTML = '';
        
        const filteredPosts = filterCategory === 'all' 
            ? posts 
            : posts.filter(post => post.category === filterCategory);
        
        if (filteredPosts.length === 0) {
            postsList.innerHTML = '<div class="no-posts">Chưa có bài viết nào trong danh mục này</div>';
            return;
        }
        
        filteredPosts.forEach(post => {
            const postDate = new Date(post.date);
            const dateStr = postDate.toLocaleDateString('vi-VN', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            
            const categoryName = {
                'tips': 'Mẹo kiếm điểm',
                'rewards': 'Đổi thưởng',
                'questions': 'Câu hỏi',
                'feedback': 'Góp ý'
            }[post.category];
            
            const postItem = document.createElement('div');
            postItem.className = 'post-item';
            postItem.innerHTML = `
                <div class="post-header">
                    <img src="${post.avatar}" alt="Avatar" class="post-avatar">
                    <span class="post-author">${post.author}</span>
                    <span class="post-category">${categoryName}</span>
                    <span class="post-date">${dateStr}</span>
                </div>
                <h3 class="post-title">${post.title}</h3>
                <div class="post-content">${post.content}</div>
                <div class="post-footer">
                    <span class="post-comments"><i>💬</i> ${post.comments} bình luận</span>
                    <span class="post-likes"><i>👍</i> ${post.likes} thích</span>
                </div>
            `;
            
            postsList.appendChild(postItem);
        });
    }
    
    // Filter posts by category
    document.querySelectorAll('.forum-categories a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const category = this.getAttribute('data-category');
            
            // Update active category
            document.querySelectorAll('.forum-categories li').forEach(li => {
                li.classList.remove('active');
            });
            this.parentElement.classList.add('active');
            
            // Filter posts
            displayPosts(category);
        });
    });
    
    // Modal handling
    newPostBtn.addEventListener('click', function() {
        modal.style.display = 'flex';
    });
    
    closeModal.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Handle new post submission
    postForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const userData = JSON.parse(localStorage.getItem('surveyon_user')) || {
            name: "Nguyễn Văn A",
            avatar: "/api/placeholder/40/40"
        };
        
        const newPost = {
            id: posts.length + 1,
            title: document.getElementById('post-title').value,
            category: document.getElementById('post-category').value,
            content: document.getElementById('post-content').value,
            author: userData.name,
            avatar: userData.avatar,
            date: new Date().toISOString().split('T')[0],
            comments: 0,
            likes: 0
        };
        
        posts.unshift(newPost);
        localStorage.setItem('surveyon_posts', JSON.stringify(posts));
        
        // Reset form
        postForm.reset();
        modal.style.display = 'none';
        
        // Refresh posts
        displayPosts();
        
        alert('Bài viết của bạn đã được đăng thành công!');
    });
    
    // Initialize
    displayPosts();
});