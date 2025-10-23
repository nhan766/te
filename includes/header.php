<?php
// Luôn bắt đầu session ở đầu các trang cần thông tin đăng nhập
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('db.php'); // Kết nối CSDL

$current_user = null;
$user_points = 0; // Điểm mặc định
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, points FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $current_user = $stmt->fetch();
        if ($current_user) {
            $user_points = $current_user['points']; // Lấy điểm từ CSDL
        } else {
            // User ID trong session không hợp lệ, hủy session
            session_unset();
            session_destroy();
        }
    } catch (PDOException $e) {
        error_log("Error fetching user data: " . $e->getMessage());
        // Có thể hiển thị lỗi hoặc xử lý khác
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'SurveyForGood'; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
</head>
<body>
    <div class="loader"></div>
    <header>
        <div class="container nav-container">
            <div class="logo">Survey<span>ForGood</span></div>
            <nav class="nav-menu">
                <ul>
                    <?php if ($current_user): ?>
                        <li><a href="dashboard.php">Trang chủ</a></li>
                        <li><a href="diendan.php">Diễn đàn</a></li>
                        <li><a href="khaosat.php">Khảo sát</a></li>
                        <li><a href="diemdanh.php">Điểm danh</a></li>
                        <li><a href="doithuong.php">Đổi điểm</a></li>
                        <li><a href="lienhe.php">Liên hệ</a></li>
                    <?php else: ?>
                        <li><a href="index.php">Trang chủ</a></li>
                        <li><a href="index.php#faq">FAQ</a></li>
                        <li><a href="lienhe.php">Liên hệ</a></li>
                    <?php endif; ?>
                </ul>
                <?php if ($current_user): // Chỉ hiển thị phần này khi đã đăng nhập ?>
                <div class="mobile-user-actions">
                    <hr>
                    <ul>
                        <li><a href="trangcanhan.php" class="profile-link"><i class="fa-solid fa-user"></i> Trang cá nhân</a></li>
                        <li><a href="actions/handle_logout.php" class="logout-link"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a></li>
                    </ul>
                </div>
                <?php endif; ?>
            </nav>
            <div class="auth-buttons">
                <?php if ($current_user): ?>
                    <div class="user-avatar" style="display:flex;align-items:center;gap:10px;">
                        <img src="https://i.pravatar.cc/40?u=<?php echo $current_user['id']; ?>" alt="Avatar" style="border-radius:50%;width:40px;height:40px;">
                        <span style="font-weight:500;">Xin chào, <?php echo htmlspecialchars($current_user['username']); ?>!</span>
                    </div>
                    <a href="trangcanhan.php" class="signin" style="margin-left:20px;">Trang cá nhân</a>
                    <a href="actions/handle_logout.php" class="signup" style="margin-left:20px;">Đăng xuất</a>
                <?php else: ?>
                    <a href="login.php" class="sg">Tham gia khảo sát</a>
                <?php endif; ?>
            </div>
            <button class="mobile-menu-toggler">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
        </div>
    </header>
    <main>
   <script src="js/script.js"></script>
   </body>
</html>