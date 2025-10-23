<?php
$page_title = "Đổi điểm thưởng";
require_once('includes/header.php');
if (!$current_user) {
    header('Location: login.php?redirect=doithuong.php');
    exit;
}
$userId = $current_user['id'];

// Lấy thông tin user đầy đủ (level, điểm...)
$stmtUser = $pdo->prepare("SELECT points, level FROM users WHERE id = ?");
$stmtUser->execute([$userId]);
$user_info = $stmtUser->fetch();

// Lấy hoạt động gần đây
$activityStmt = $pdo->prepare("SELECT activity_description, points_change FROM user_activities WHERE user_id = ? ORDER BY activity_time DESC LIMIT 3");
$activityStmt->execute([$userId]);
$recent_activities = $activityStmt->fetchAll();

// Lấy danh sách phần thưởng đang active
$rewardsStmt = $pdo->prepare("SELECT * FROM rewards WHERE is_active = TRUE ORDER BY points_cost ASC");
$rewardsStmt->execute();
$rewards = $rewardsStmt->fetchAll();

?>
<link rel="stylesheet" href="css/doithuong.css"> <style> /* CSS tạm cho thông báo */
    .redeem-message { padding: 10px; margin-bottom: 15px; border-radius: 5px; text-align: center; }
    .redeem-message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .redeem-message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
</style>

<main class="rewards-page-section">
    <div class="container">
        <div class="section-title">
            <h2>Đổi điểm thưởng</h2>
            <p>Sử dụng điểm của bạn để nhận quà hấp dẫn.</p>
             <?php if (isset($_SESSION['redeem_message'])): ?>
                <p class="redeem-message <?php echo isset($_SESSION['redeem_error']) ? 'error' : 'success'; ?>">
                    <?php echo $_SESSION['redeem_message']; ?>
                    <?php if (isset($_SESSION['voucher_code'])): ?>
                        <br><strong>Mã của bạn: <?php echo htmlspecialchars($_SESSION['voucher_code']); ?></strong>
                    <?php endif; ?>
                </p>
                <?php unset($_SESSION['redeem_message'], $_SESSION['redeem_error'], $_SESSION['voucher_code']); ?>
            <?php endif; ?>
        </div>

        <div class="rewards-status">
             <div class="status-card">
                 <h3>Điểm khả dụng</h3>
                 <div class="points-display" id="user-points"><?php echo number_format($user_info['points']); ?></div>
                 </div>
             <div class="status-card">
                 <h3>Cấp bậc thành viên</h3>
                 <div class="level-display" id="user-level"><?php echo ucfirst($user_info['level'] ?? 'Bronze'); ?></div>
                 </div>
             <div class="status-card">
                 <h3>Hoạt động gần đây</h3>
                 <ul class="activity-list" id="recent-activity-list">
                     <?php if (empty($recent_activities)): ?>
                         <li>Chưa có hoạt động nào.</li>
                     <?php else: ?>
                         <?php foreach($recent_activities as $act): ?>
                             <li>
                                 <span style="color: <?php echo $act['points_change'] >= 0 ? 'green' : 'red'; ?>">
                                     <?php echo ($act['points_change'] >= 0 ? '+' : '') . $act['points_change']; ?>
                                 </span>
                                 <?php echo htmlspecialchars($act['activity_description']); ?>
                             </li>
                         <?php endforeach; ?>
                     <?php endif; ?>
                 </ul>
             </div>
        </div>

        <div class="rewards-tabs">
            <div class="rewards-grid">
                 <?php if (empty($rewards)): ?>
                     <p>Hiện chưa có phần thưởng nào.</p>
                 <?php else: ?>
                    <?php foreach ($rewards as $reward): ?>
                        <div class="reward-card">
                            <div class="reward-image">
                                <img src="<?php echo htmlspecialchars($reward['image_url'] ?? 'image/placeholder_voucher.png'); ?>" alt="<?php echo htmlspecialchars($reward['title']); ?>"/>
                            </div>
                            <div class="reward-info">
                                <h3><?php echo htmlspecialchars($reward['title']); ?></h3>
                                <div class="reward-points"><?php echo number_format($reward['points_cost']); ?> điểm</div>
                                <form action="actions/redeem_reward.php" method="POST" onsubmit="return confirm('Xác nhận đổi <?php echo number_format($reward['points_cost']); ?> điểm lấy phần thưởng này?');">
                                    <input type="hidden" name="reward_id" value="<?php echo $reward['reward_id']; ?>">
                                    <button type="submit" class="redeem-btn" <?php if ($user_info['points'] < $reward['points_cost']) echo 'disabled'; ?>>
                                        <?php echo ($user_info['points'] < $reward['points_cost']) ? 'Không đủ điểm' : 'Đổi ngay'; ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                 <?php endif; ?>
            </div>
            </div>
    </div>
</main>

<?php require_once('includes/footer.php'); ?>