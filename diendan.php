<?php
// --- BẬT HIỂN THỊ LỖI ĐỂ DEBUG (Xóa hoặc comment // đi sau khi sửa xong) ---
ini_set('display_errors', 1);
error_reporting(E_ALL);
// --- /BẬT HIỂN THỊ LỖI ---

$page_title = "Diễn đàn cộng đồng";
require_once('includes/header.php');
if (!$current_user) {
    header('Location: login.php?redirect=diendan.php');
    exit;
}

$category = $_GET['category'] ?? 'all';
$userId = $current_user['id']; // Lấy user ID để kiểm tra quyền xóa

// --- Truy vấn CSDL ---
// Khởi tạo $posts là mảng rỗng để tránh lỗi nếu query thất bại
$posts = [];
try {
    $sql = "SELECT p.id, p.user_id, p.title, p.content, p.category, p.created_at, u.username as author_name
            FROM forum_posts p
            JOIN users u ON p.user_id = u.id";
    $params = [];

    if ($category != 'all') {
        $sql .= " WHERE p.category = ?"; // Sử dụng placeholder
        $params[] = $category;
    }
    $sql .= " ORDER BY p.created_at DESC";
    // Thêm LIMIT OFFSET nếu bạn muốn phân trang sau này

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Forum Posts Fetch Error: " . $e->getMessage());
    // Gán thông báo lỗi vào session để hiển thị
    $_SESSION['forum_message'] = "Lỗi khi tải bài viết từ cơ sở dữ liệu.";
    $_SESSION['forum_error'] = true;
    // Không exit, vẫn hiển thị trang nhưng với danh sách bài viết rỗng
}
?>

<link rel="stylesheet" href="css/diendan.css">
<style>
    .forum-action-message { text-align: center; padding: 10px; margin-bottom: 15px; border-radius: 5px; max-width: 1160px; margin-left: auto; margin-right: auto; }
    .forum-action-message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;}
    .forum-action-message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;}
    /* Style cho nút tìm kiếm nếu cần */
    .post-search { display: flex; margin-bottom: 20px; }
    .post-search input { flex-grow: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px 0 0 4px; }
    .post-search button { padding: 10px 15px; border: none; background-color: #2980b9; color: white; border-radius: 0 4px 4px 0; cursor: pointer; }
</style>

<section class="community-forum">
    <div class="container"> <?php if (isset($_SESSION['forum_message'])): ?>
            <p class="forum-action-message <?php echo isset($_SESSION['forum_error']) ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($_SESSION['forum_message']); // Dùng htmlspecialchars ?>
            </p>
            <?php unset($_SESSION['forum_message'], $_SESSION['forum_error']); ?>
        <?php endif; ?>

        <div class="section-title fade-in">
             <h2>Cộng đồng SurveyForGood</h2>
             <p>Chia sẻ kinh nghiệm và thảo luận với các thành viên khác</p>
        </div>

        <div class="forum-container"> <div class="forum-sidebar fade-in">
                <div class="sidebar-block">
                    <button class="new-post-button"><i class="fa-solid fa-pen-to-square"></i> Tạo bài viết mới</button>
                </div>
                <div class="sidebar-block">
                    <h3><i class="fa-solid fa-layer-group"></i> Danh mục</h3>
                    <ul class="forum-categories">
                         <li class="<?php if($category == 'all') echo 'active'; ?>"><a href="diendan.php?category=all" data-category="all"><i class="fa-solid fa-globe"></i> Tất cả</a></li>
                         <li class="<?php if($category == 'tips') echo 'active'; ?>"><a href="diendan.php?category=tips" data-category="tips"><i class="fa-solid fa-lightbulb"></i> Mẹo kiếm điểm</a></li>
                         <li class="<?php if($category == 'rewards') echo 'active'; ?>"><a href="diendan.php?category=rewards" data-category="rewards"><i class="fa-solid fa-gift"></i> Đổi thưởng</a></li>
                         <li class="<?php if($category == 'questions') echo 'active'; ?>"><a href="diendan.php?category=questions" data-category="questions"><i class="fa-solid fa-circle-question"></i> Câu hỏi</a></li>
                         <li class="<?php if($category == 'feedback') echo 'active'; ?>"><a href="diendan.php?category=feedback" data-category="feedback"><i class="fa-solid fa-comments"></i> Góp ý</a></li>
                    </ul>
                </div>
            </div>

            <div class="forum-main-content fade-in">
                 <form action="diendan.php" method="GET" class="post-search">
                     <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>"> <input type="text" name="search" placeholder="Tìm kiếm bài viết...">
                     <button type="submit"><i class="fa-solid fa-search"></i></button>
                 </form>

                <div class="posts-list">
                    <?php if (empty($posts)): ?>
                         <div class="no-posts" style="text-align: center; padding: 20px; color: #777;">Chưa có bài viết nào trong danh mục này.</div>
                    <?php else: ?>
                        <?php foreach($posts as $post):
                            // --- FIX: Sử dụng hàm substr và strlen thay vì mb_ ---
                            // Lưu ý: Có thể cắt sai ký tự tiếng Việt có dấu nếu không có mbstring
                            $contentPreview = substr($post['content'], 0, 150) . (strlen($post['content']) > 150 ? '...' : '');
                            // --- /FIX ---
                            // Định dạng ngày tháng
                             $postDate = new DateTime($post['created_at']);
                             $dateStr = $postDate->format('d/m/Y H:i'); // Thêm giờ phút
                        ?>
                        <div class="post-item">
                             <?php if ($post['user_id'] == $userId): // Chỉ chủ bài viết mới thấy nút xóa ?>
                             <form action="actions/delete_post.php" method="POST" style="display: inline; position: absolute; top: 15px; right: 15px;" onsubmit="return confirm('Bạn chắc chắn muốn xóa bài viết này?');">
                                 <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                 <button type="submit" class="delete-post-btn" title="Xóa bài viết">&times;</button>
                             </form>
                             <?php endif; ?>
                             <div class="post-header">
                                 <img src="https://i.pravatar.cc/45?u=<?php echo $post['user_id']; ?>" alt="Avatar" class="post-avatar">
                                 <div class="post-author-info">
                                     <div class="post-author"><?php echo htmlspecialchars($post['author_name']); ?></div>
                                     <div class="post-date"><?php echo $dateStr; ?></div>
                                 </div>
                                 <span class="post-category"><?php echo htmlspecialchars(ucfirst($post['category'] ?? 'Khác')); // Thêm ?? 'Khác' ?></span>
                             </div>
                             <h3 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                             <div class="post-content-preview"><?php echo nl2br(htmlspecialchars($contentPreview)); // nl2br giữ lại xuống dòng ?></div>
                             <div class="post-footer">
                                 <span><i class="fa-regular fa-comment-dots"></i> 0</span>
                                 <span><i class="fa-regular fa-thumbs-up"></i> 0</span>
                             </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                </div>
        </div> </div> </section>

<div class="modal" id="new-post-modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Tạo bài viết mới</h2>
        <form id="new-post-form" action="actions/create_post.php" method="POST">
            <div class="form-group">
                <label for="post-title">Tiêu đề</label>
                <input type="text" id="post-title" name="title" required placeholder="Nhập tiêu đề hấp dẫn...">
            </div>
            <div class="form-group">
                <label for="post-category">Danh mục</label>
                <select id="post-category" name="category" required>
                    <option value="tips">Mẹo kiếm điểm</option>
                    <option value="rewards">Đổi thưởng</option>
                    <option value="questions">Câu hỏi</option>
                    <option value="feedback">Góp ý</option>
                    <option value="other">Khác</option> </select>
            </div>
            <div class="form-group">
                <label for="post-content">Nội dung</label>
                <textarea id="post-content" name="content" rows="6" required placeholder="Chia sẻ suy nghĩ của bạn..."></textarea>
            </div>
            <button type="submit" class="submit-button">Đăng bài</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('new-post-modal');
    const newPostBtn = document.querySelector('.new-post-button');

    // Kiểm tra modal và nút có tồn tại không
    if (modal && newPostBtn) {
         const closeModalBtn = modal.querySelector('.close-modal');

         newPostBtn.addEventListener('click', () => modal.classList.add('active'));

         if(closeModalBtn) {
             closeModalBtn.addEventListener('click', () => modal.classList.remove('active'));
         }

         modal.addEventListener('click', function(e) {
             // Chỉ đóng khi click vào nền mờ (chính là modal), không phải content bên trong
             if (e.target === this) {
                 modal.classList.remove('active');
             }
         });
    } else {
        console.warn("Modal or New Post Button not found."); // Thông báo nếu thiếu element
    }

    // --- Xử lý Fade In (Giữ lại từ script.js nếu cần) ---
    const faders = document.querySelectorAll('.fade-in');
    if (faders.length > 0 && typeof IntersectionObserver !== 'undefined') {
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
    }
    // --- /Xử lý Fade In ---

});
</script>

<?php require_once('includes/footer.php'); ?>