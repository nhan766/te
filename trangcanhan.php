<?php
$page_title = "Trang cá nhân";
require_once('includes/header.php');
if (!$current_user) {
    header('Location: login.php?redirect=trangcanhan.php');
    exit;
}

// Lấy thông tin chi tiết hơn từ CSDL (nếu cần, ví dụ: ngày sinh, sđt...)
$stmt = $pdo->prepare("SELECT *, DATE_FORMAT(join_date, '%m/%Y') as member_since_formatted FROM users WHERE id = ?");
$stmt->execute([$current_user['id']]);
$user_details = $stmt->fetch();

// Lấy lịch sử hoạt động (ví dụ: 5 hoạt động gần nhất)
// Cần tạo bảng 'user_activities' để lưu log khi user làm khảo sát, điểm danh...
/*
CREATE TABLE user_activities (
    activity_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    activity_description VARCHAR(255),
    points_change INT DEFAULT 0,
    activity_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
*/
$activityStmt = $pdo->prepare("SELECT *, DATE_FORMAT(activity_time, '%H:%i %d/%m/%Y') as formatted_time FROM user_activities WHERE user_id = ? ORDER BY activity_time DESC LIMIT 5");
$activityStmt->execute([$current_user['id']]);
$activities = $activityStmt->fetchAll();


// Lấy lịch sử đổi thưởng (ví dụ: 3 lần gần nhất)
$rewardStmt = $pdo->prepare("SELECT *, DATE_FORMAT(redeem_date, '%d/%m/%Y') as formatted_date FROM reward_history WHERE user_id = ? ORDER BY redeem_date DESC LIMIT 3");
$rewardStmt->execute([$current_user['id']]);
$rewards = $rewardStmt->fetchAll();

// Lấy tổng số khảo sát đã làm (cần cách lưu trữ khi hoàn thành khảo sát)
// Ví dụ: Tạo bảng user_completed_surveys (user_id, survey_id, completed_at)
$completedStmt = $pdo->prepare("SELECT COUNT(*) FROM user_completed_surveys WHERE user_id = ?");
$completedStmt->execute([$current_user['id']]);
$totalSurveysCompleted = $completedStmt->fetchColumn();


?>
<link rel="stylesheet" href="css/trangcanhan.css">

<section class="user-profile">
    <div class="container">
        <div class="profile-header">
             <div class="profile-avatar">
                <img src="https://i.pravatar.cc/150?u=<?php echo $user_details['id']; ?>" alt="Avatar" id="user-avatar">
                <button class="edit-avatar">Đổi ảnh</button>
            </div>
            <div class="profile-info">
                <h2 id="user-name"><?php echo htmlspecialchars($user_details['username']); ?></h2>
                <p class="user-level">Thành viên <span class="level-badge <?php echo strtolower($user_details['level'] ?? 'bronze'); ?>"><?php echo ucfirst($user_details['level'] ?? 'Bronze'); ?></span></p>
                <div class="profile-stats">
                    <div class="stat-item">
                        <span class="stat-value" id="total-points"><?php echo number_format($user_details['points']); ?></span>
                        <span class="stat-label">Điểm tích lũy</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value" id="total-surveys"><?php echo $totalSurveysCompleted; ?></span>
                        <span class="stat-label">Khảo sát đã làm</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value" id="member-since"><?php echo $user_details['member_since_formatted']; ?></span>
                        <span class="stat-label">Tham gia từ</span>
                    </div>
                </div>
            </div>
        </div>

          <?php if (isset($_SESSION['profile_message'])): ?>
                <p class="profile-update-message <?php echo isset($_SESSION['profile_error']) ? 'error' : 'success'; ?>" style="text-align: center; padding: 10px; margin-bottom: 15px; border-radius: 5px; background-color: <?php echo isset($_SESSION['profile_error']) ? '#f8d7da' : '#d4edda'; ?>; color: <?php echo isset($_SESSION['profile_error']) ? '#721c24' : '#155724'; ?>;">
                    <?php echo $_SESSION['profile_message']; ?>
                </p>
                <?php unset($_SESSION['profile_message'], $_SESSION['profile_error']); ?>
            <?php endif; ?>

        </div>

        <div class="profile-sections">
            <div class="profile-section">
                <h3>Thông tin cá nhân</h3>
                <form class="profile-form" action="actions/update_profile.php" method="POST">
                    <div class="form-group"><label>Họ và tên</label><input type="text" name="full_name" value="<?php echo htmlspecialchars($user_details['full_name'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Email</label><input type="email" id="input-email" value="<?php echo htmlspecialchars($user_details['email']); ?>" disabled></div> <div class="form-group"><label>Số điện thoại</label><input type="tel" name="phone" value="<?php echo htmlspecialchars($user_details['phone'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Ngày sinh</label><input type="date" name="birthday" value="<?php echo htmlspecialchars($user_details['birthday'] ?? ''); ?>"></div>
                    <div class="form-group">
                        <label>Giới tính</label>
                        <select name="gender">
                            <option value="male" <?php if(($user_details['gender'] ?? '') == 'male') echo 'selected'; ?>>Nam</option>
                            <option value="female" <?php if(($user_details['gender'] ?? '') == 'female') echo 'selected'; ?>>Nữ</option>
                            <option value="other" <?php if(($user_details['gender'] ?? '') == 'other') echo 'selected'; ?>>Khác</option>
                        </select>
                    </div>
                    <button type="submit" class="save-button">Lưu thay đổi</button>
                </form>
            </div>

             <div class="profile-section">
                <h3>Lịch sử hoạt động</h3>
                <div class="survey-history-list">
                    <?php if (empty($activities)): ?>
                        <div class="no-survey-history"><p>Chưa có hoạt động nào.</p></div>
                    <?php else: ?>
                        <?php foreach($activities as $act): ?>
                            <div class="survey-history-item">
                                <div>
                                    <div class="survey-history-title"><?php echo htmlspecialchars($act['activity_description']); ?></div>
                                    <div class="survey-history-date"><?php echo $act['formatted_time']; ?></div>
                                </div>
                                <div class="survey-history-points"><?php echo ($act['points_change'] > 0 ? '+' : '') . $act['points_change'] . ' điểm'; ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                   </div>
            </div>

            <div class="profile-section">
                <h3>Lịch sử đổi thưởng</h3>
                <div id="reward-history-summary">
                     <?php if (empty($rewards)): ?>
                        <div class="no-reward-history"><p>Bạn chưa đổi thưởng lần nào.</p></div>
                    <?php else: ?>
                        <?php foreach($rewards as $rew): ?>
                            <div class="reward-history-item">
                                <div class="reward-history-info">
                                    <div class="reward-history-title"><?php echo htmlspecialchars($rew['reward_title']); ?></div>
                                    <div class="reward-history-date"><?php echo $rew['formatted_date']; ?></div>
                                </div>
                                <div class="reward-history-points">-<?php echo number_format($rew['points_cost']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </div>
                <a href="lsdt.php" class="view-all-btn">Xem tất cả lịch sử</a> </div>
        </div>
    </div>
</section>

<?php require_once('includes/footer.php'); ?>