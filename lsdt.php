<?php
$page_title = "Lịch sử Đổi thưởng";
require_once('includes/header.php'); // Đã bao gồm kiểm tra đăng nhập
if (!$current_user) {
    header('Location: login.php?redirect=lsdt.php');
    exit;
}
$userId = $current_user['id'];

// Lấy tất cả lịch sử đổi thưởng của user, sắp xếp mới nhất trước
// Thêm phân trang nếu cần thiết (tương tự trang khaosat.php)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15; // Số lượng mỗi trang
$offset = ($page - 1) * $limit;

// Đếm tổng số
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM reward_history WHERE user_id = ?");
$countStmt->execute([$userId]);
$totalItems = $countStmt->fetchColumn();
$totalPages = ceil($totalItems / $limit);

// Lấy dữ liệu cho trang hiện tại
$historyStmt = $pdo->prepare(
    "SELECT *, DATE_FORMAT(redeem_date, '%d/%m/%Y %H:%i') as formatted_date
     FROM reward_history
     WHERE user_id = ?
     ORDER BY redeem_date DESC
     LIMIT ? OFFSET ?"
);
$historyStmt->bindParam(1, $userId, PDO::PARAM_INT);
$historyStmt->bindParam(2, $limit, PDO::PARAM_INT);
$historyStmt->bindParam(3, $offset, PDO::PARAM_INT);
$historyStmt->execute();
$rewardHistory = $historyStmt->fetchAll();

?>
<link rel="stylesheet" href="css/lsdt.css"> <main class="history-page">
    <div class="container">
        <div class="section-title">
            <h2>Lịch sử đổi thưởng</h2>
            <p>Toàn bộ các giao dịch quy đổi điểm của bạn được ghi lại tại đây.</p>
        </div>

        <div class="history-list-container" id="reward-history-list">
            <?php if (empty($rewardHistory)): ?>
                <div class="no-history">Chưa có lịch sử đổi thưởng nào.</div>
            <?php else: ?>
                <?php foreach ($rewardHistory as $item): ?>
                    <div class="history-card">
                        <div class="history-icon">
                            <i class="fas fa-gift"></i>
                        </div>
                        <div class="history-info">
                            <h4 class="reward-title"><?php echo htmlspecialchars($item['reward_title']); ?></h4>
                            <p class="reward-date"><?php echo $item['formatted_date']; ?></p>
                        </div>
                        <div class="history-details">
                            <div class="detail-item">
                                <span class="label">Điểm</span>
                                <span class="reward-points">-<?php echo number_format($item['points_cost']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Mã Voucher</span>
                                <span class="reward-code"><?php echo htmlspecialchars($item['voucher_code']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Trạng thái</span>
                                <span class="status-badge"><?php echo htmlspecialchars($item['status'] ?? 'Đã nhận'); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="survey-pagination" style="margin-top: 30px;">
             <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>" class="pagination-btn">←</a>
             <?php endif; ?>
             <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                 <a href="?page=<?php echo $i; ?>" class="pagination-btn <?php if ($i == $page) echo 'active'; ?>">
                     <?php echo $i; ?>
                 </a>
             <?php endfor; ?>
             <?php if ($page < $totalPages): ?>
                 <a href="?page=<?php echo $page + 1; ?>" class="pagination-btn">→</a>
             <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once('includes/footer.php'); ?>