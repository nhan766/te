<?php
session_start();
require_once('../includes/db.php');

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Vui lòng đăng nhập để điểm danh.";
    header('Location: ../login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$todayStr = date('Y-m-d');
$pointsPerCheckin = 10; // Nên lấy từ config hoặc CSDL

// Bắt đầu transaction
$pdo->beginTransaction();

try {
    // 1. Kiểm tra xem hôm nay đã điểm danh chưa (trong transaction để đảm bảo an toàn)
    $stmt = $pdo->prepare("SELECT checkin_id FROM checkin_history WHERE user_id = ? AND checkin_date = ? FOR UPDATE"); // FOR UPDATE để khóa dòng, tránh điểm danh 2 lần cùng lúc
    $stmt->execute([$userId, $todayStr]);

    if ($stmt->fetch()) {
        // Đã điểm danh rồi
        $pdo->rollBack(); // Không cần commit vì không thay đổi gì
        $_SESSION['checkin_message'] = 'Bạn đã điểm danh hôm nay rồi!';
        $_SESSION['checkin_error'] = true;
        header('Location: ../diemdanh.php');
        exit;
    }

    // 2. Ghi nhận điểm danh
    $insertCheckin = $pdo->prepare("INSERT INTO checkin_history (user_id, checkin_date, points_earned) VALUES (?, ?, ?)");
    $insertCheckin->execute([$userId, $todayStr, $pointsPerCheckin]);

    // 3. Cộng điểm cho người dùng
    $updatePoints = $pdo->prepare("UPDATE users SET points = points + ? WHERE id = ?");
    $updatePoints->execute([$pointsPerCheckin, $userId]);

    // 4. Ghi log hoạt động
    $activityDesc = "Điểm danh hàng ngày";
    $logActivity = $pdo->prepare("INSERT INTO user_activities (user_id, activity_description, points_change) VALUES (?, ?, ?)");
    $logActivity->execute([$userId, $activityDesc, $pointsPerCheckin]);

    // Commit transaction nếu tất cả thành công
    $pdo->commit();

    $_SESSION['checkin_message'] = "Điểm danh thành công! Bạn nhận được +{$pointsPerCheckin} điểm.";

} catch (Exception $e) {
    // Rollback nếu có lỗi
    $pdo->rollBack();
    error_log("Check-in Error: " . $e->getMessage());
    $_SESSION['checkin_message'] = 'Đã xảy ra lỗi khi điểm danh. Vui lòng thử lại.';
    $_SESSION['checkin_error'] = true;
}

header('Location: ../diemdanh.php');
exit;
?>