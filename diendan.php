<?php
$page_title = "Diễn đàn cộng đồng";
require_once('includes/header.php');
if (!$current_user) {
    header('Location: login.php?redirect=diendan.php');
    exit;
}

$category = $_GET['category'] ?? 'all';

$sql = "SELECT p.*, u.username as author_name
        FROM forum_posts p
        JOIN users u ON p.user_id = u.id";
$params = [];

if ($category != 'all') {
    $sql .= " WHERE p.category = ?";
    $params[] = $category;
}
$sql .= " ORDER BY p.created_at DESC";


$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();

?>
<link rel="stylesheet" href="css/diendan.css"> <section class="community-forum">

<div class="container">
        <?php if (isset($_SESSION['forum_message'])): ?>
            <p class="forum-action-message <?php echo isset($_SESSION['forum_error']) ? 'error' : 'success'; ?>" style="text-align: center; padding: 10px; margin-bottom: 15px; border-radius: 5px; background-color: <?php echo isset($_SESSION['forum_error']) ? '#f8d7da' : '#d4edda'; ?>; color: <?php echo isset($_SESSION['forum_error']) ? '#721c24' : '#155724'; ?>;">
                <?php echo $_SESSION['forum_message']; ?>
            </p>
            <?php unset($_SESSION['forum_message'], $_SESSION['forum_error']); ?>
        <?php endif; ?>

        <div class="section-title fade-in"> </div>
        <div class="forum-container"> </div>
    </div>

    <div class="container">
        <div class="forum-container">
            <div class="forum-sidebar fade-in">
                <div class="sidebar-block">
                    <button class="new-post-button"><i class="fa-solid fa-pen-to-square"></i> Tạo bài viết mới</button>
                </div>
                 <div class="sidebar-block">
                    <h3><i class="fa-solid fa-layer-group"></i> Danh mục</h3>
                    <ul class="forum-categories">
                         <li class="<?php if($category == 'all') echo 'active'; ?>"><a href="diendan.php?category=all" data-category="all"><i class="fa-solid fa-globe"></i> Tất cả</a></li>
                         <li class="<?php if($category == 'tips') echo 'active'; ?>"><a href="diendan.php?category=tips" data-category="tips"><i class="fa-solid fa-lightbulb"></i> Mẹo kiếm điểm</a></li>
                         </ul>
                </div>
            </div>
            <div class="forum-main-content fade-in">
                 <div class="posts-list">
                    <?php if (empty($posts)): ?>
                         <div class="no-posts">Chưa có bài viết nào.</div>
                    <?php else: ?>
                        <?php foreach($posts as $post):
                            $postDate = new DateTime($post['created_at']);
                            $dateStr = $postDate->format('d/m/Y');
                            $contentPreview = mb_substr($post['content'], 0, 150) . (mb_strlen($post['content']) > 150 ? '...' : '');
                        ?>
                        <div class="post-item">
                             <?php if ($post['user_id'] == $current_user['id']): // Chỉ chủ bài viết mới thấy nút xóa ?>
                             <form action="actions/delete_post.php" method="POST" style="display: inline;" onsubmit="return confirm('Bạn chắc chắn muốn xóa bài viết này?');">
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
                                 <span class="post-category"><?php echo htmlspecialchars(ucfirst($post['category'])); ?></span>
                             </div>
                             <h3 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                             <div class="post-content-preview"><?php echo nl2br(htmlspecialchars($contentPreview)); ?></div>
                             <div class="post-footer">
                                 <span><i class="fa-regular fa-comment-dots"></i> 0</span>
                                 <span><i class="fa-regular fa-thumbs-up"></i> 0</span>
                             </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                </div>
        </div>
    </div>
</section>

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
                </select>
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
    const closeModalBtn = modal.querySelector('.close-modal');

    if (newPostBtn && modal && closeModalBtn) {
        newPostBtn.addEventListener('click', () => modal.classList.add('active'));
        closeModalBtn.addEventListener('click', () => modal.classList.remove('active'));
        modal.addEventListener('click', function(e) { if (e.target === this) modal.classList.remove('active'); });
    }
  
});
</script>

<?php require_once('includes/footer.php'); ?>