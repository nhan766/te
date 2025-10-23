<?php
session_start();
require_once('../includes/db.php');

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Vui lòng đăng nhập để đổi thưởng.";
    header('Location: ../login.php');
    exit;
}
if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_POST['reward_id'])) {
    header('Location: ../doithuong.php');
    exit;
}

$userId = $_SESSION['user_id'];
$rewardId = (int)$_POST['reward_id'];

// Bắt đầu transaction
$pdo->beginTransaction();

try {
    // 1. Lấy thông tin phần thưởng VÀ khóa dòng (FOR UPDATE) để kiểm tra tồn kho (nếu có)
    $stmtReward = $pdo->prepare("SELECT reward_id, title, points_cost, stock FROM rewards WHERE reward_id = ? AND is_active = TRUE FOR UPDATE");
    $stmtReward->execute([$rewardId]);
    $reward = $stmtReward->fetch();

    if (!$reward) {
        throw new Exception("Phần thưởng không hợp lệ hoặc không còn tồn tại.");
    }

    // 2. Lấy thông tin điểm của user VÀ khóa dòng (FOR UPDATE)
    $stmtUser = $pdo->prepare("SELECT points FROM users WHERE id = ? FOR UPDATE");
    $stmtUser->execute([$userId]);
    $userPoints = $stmtUser->fetchColumn();

    if ($userPoints === false) {
         throw new Exception("Người dùng không tồn tại.");
    }

    // 3. Kiểm tra điểm
    if ($userPoints < $reward['points_cost']) {
        throw new Exception("Bạn không đủ điểm để đổi phần thưởng này.");
    }

    // 4. Kiểm tra tồn kho (nếu có cột stock và stock > 0)
    if ($reward['stock'] !== null && $reward['stock'] <= 0) {
         throw new Exception("Phần thưởng này đã hết hàng.");
    }

    // --- Tất cả kiểm tra hợp lệ ---

    // 5. Trừ điểm người dùng
    $updatePoints = $pdo->prepare("UPDATE users SET points = points - ? WHERE id = ?");
    $updatePoints->execute([$reward['points_cost'], $userId]);

    // 6. Giảm tồn kho (nếu có)
    if ($reward['stock'] !== null) {
        $updateStock = $pdo->prepare("UPDATE rewards SET stock = stock - 1 WHERE reward_id = ?");
        $updateStock->execute([$rewardId]);
    }

    // 7. Tạo mã voucher (ví dụ đơn giản)
    $voucherCode = 'SVN-' . strtoupper(bin2hex(random_bytes(4))); // Tạo mã ngẫu nhiên

    // 8. Ghi lịch sử đổi thưởng
    $insertHistory = $pdo->prepare("INSERT INTO reward_history (user_id, reward_title, points_cost, voucher_code, status) VALUES (?, ?, ?, ?, ?)");
    $insertHistory->execute([$userId, $reward['title'], $reward['points_cost'], $voucherCode, 'Đã nhận']);

    // 9. Ghi log hoạt động
    $activityDesc = "Đổi thưởng: " . $reward['title'];
    $logActivity = $pdo->prepare("INSERT INTO user_activities (user_id, activity_description, points_change) VALUES (?, ?, ?)");
    $logActivity->execute([$userId, $activityDesc, -$reward['points_cost']]); // Ghi điểm bị trừ

    // Commit transaction
    $pdo->commit();

    $_SESSION['redeem_message'] = "Đổi thưởng thành công!";
    $_SESSION['voucher_code'] = $voucherCode; // Lưu mã voucher để hiển thị

} catch (Exception $e) {
    // Rollback nếu có lỗi
    $pdo->rollBack();
    error_log("Redeem Error: " . $e->getMessage());
    $_SESSION['redeem_message'] = $e->getMessage(); // Hiển thị lỗi cụ thể
    $_SESSION['redeem_error'] = true;
}

header('Location: ../doithuong.php'); // Quay lại trang đổi thưởng
exit;
?>