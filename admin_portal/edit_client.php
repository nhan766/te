<?php
session_start();
// Bảo vệ trang: chỉ admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php'); exit;
}
require_once('../../includes/db.php'); // Điều chỉnh đường dẫn

$client_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$client_data = null;
$page_title = "Edit Client";

if ($client_id) {
    // Lấy thông tin client hiện tại
    $stmt = $pdo->prepare("SELECT client_id, company_name, contact_email FROM clients WHERE client_id = ?");
    $stmt->execute([$client_id]);
    $client_data = $stmt->fetch();
    if ($client_data) {
        $page_title = "Edit Client: " . htmlspecialchars($client_data['company_name']);
    } else {
        // ID không hợp lệ, chuyển hướng
        $_SESSION['client_manage_message'] = "Client not found.";
        $_SESSION['client_manage_error'] = true;
        header('Location: manage_clients.php');
        exit;
    }
} else {
    // Không có ID, không cho phép truy cập trang này (chỉ để sửa, không tạo mới ở đây)
    $_SESSION['client_manage_message'] = "Client ID not provided for editing.";
    $_SESSION['client_manage_error'] = true;
    header('Location: manage_clients.php');
    exit;
}

// Lấy thông báo nếu có lỗi từ lần submit trước
$message = $_SESSION['client_edit_message'] ?? '';
$is_error = $_SESSION['client_edit_error'] ?? false;
unset($_SESSION['client_edit_message'], $_SESSION['client_edit_error']);
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $page_title; ?></title>
     <style>
        /* CSS tương tự edit_reward.php */
        body { font-family: sans-serif; margin: 0; }
        .admin-header { background-color: #2c3e50; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .admin-header h1 { margin: 0; font-size: 1.5em; }
        .admin-header a { color: #ecf0f1; text-decoration: none; margin-left: 15px;}
        .admin-container { padding: 20px; max-width: 600px; margin: auto; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold;}
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;}
        button { padding: 10px 15px; cursor: pointer; border-radius: 4px; border: none; font-size: 1em;}
        .btn-primary { background-color: #27ae60; color: white; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1><?php echo $page_title; ?></h1>
        <div>
            <a href="manage_clients.php">Back to Client List</a>
            <a href="actions/handle_admin_logout.php">Logout</a>
        </div>
    </header>
    <div class="admin-container">
        <?php if ($message): ?>
            <p class="message <?php echo $is_error ? 'error' : 'success'; ?>"><?php echo $message; ?></p>
        <?php endif; ?>

         <form action="actions/handle_save_client.php" method="POST">
             <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">

            <div class="form-group">
                <label for="company_name">Company Name:</label>
                <input type="text" id="company_name" name="company_name" required value="<?php echo htmlspecialchars($client_data['company_name']); ?>">
            </div>
            <div class="form-group">
                <label for="contact_email">Contact Email:</label>
                <input type="email" id="contact_email" name="contact_email" required value="<?php echo htmlspecialchars($client_data['contact_email']); ?>">
            </div>
            <div class="form-group">
                <label for="password">New Password (Leave blank to keep current):</label>
                <input type="password" id="password" name="password" placeholder="Enter new password if changing">
            </div>
             <div class="form-group">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
            </div>

            <button type="submit" class="btn-primary">Save Changes</button>
        </form>
    </div>
</body>
</html>