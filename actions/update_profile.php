<?php
session_start();
require_once('../includes/db.php');

// Yêu cầu đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Vui lòng đăng nhập để cập nhật thông tin.";
    header('Location: ../login.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $_SESSION['user_id'];
    $fullName = trim($_POST['full_name']) ?: null; // Cho phép rỗng
    $phone = trim($_POST['phone']) ?: null;
    $birthday = !empty($_POST['birthday']) ? trim($_POST['birthday']) : null; // Validate date format if needed
    $gender = $_POST['gender'] ?? null; // 'male', 'female', 'other'

    // Validation (Thêm nếu cần: định dạng SĐT, ngày sinh hợp lệ...)
    if (!in_array($gender, ['male', 'female', 'other', null])) {
        $_SESSION['profile_message'] = "Giới tính không hợp lệ.";
        $_SESSION['profile_error'] = true;
        header('Location: ../trangcanhan.php');
        exit;
    }

    try {
        // Cập nhật thông tin trong CSDL
        // Lưu ý: Không cập nhật email hoặc username ở đây (cần quy trình phức tạp hơn)
        $stmt = $pdo->prepare(
            "UPDATE users
             SET full_name = :full_name, phone = :phone, birthday = :birthday, gender = :gender
             WHERE id = :user_id"
        );
        $result = $stmt->execute([
            ':full_name' => $fullName,
            ':phone' => $phone,
            ':birthday' => $birthday,
            ':gender' => $gender,
            ':user_id' => $userId
        ]);

        if ($result) {
            $_SESSION['profile_message'] = "Cập nhật thông tin cá nhân thành công!";
        } else {
            // Có thể không có gì thay đổi hoặc lỗi
             $_SESSION['profile_message'] = "Không có thông tin nào được cập nhật.";
             // Hoặc $_SESSION['profile_error'] = true; nếu muốn báo lỗi
        }

    } catch (PDOException $e) {
        error_log("Update Profile Error: " . $e->getMessage());
        $_SESSION['profile_message'] = "Đã xảy ra lỗi khi cập nhật thông tin.";
        $_SESSION['profile_error'] = true;
    }

    header('Location: ../trangcanhan.php'); // Quay lại trang cá nhân
    exit;

} else {
    header('Location: ../trangcanhan.php'); // Không cho truy cập trực tiếp
    exit;
}
?>