<?php
session_start();
// Bảo vệ trang: chỉ admin mới vào được
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
require_once('../../includes/db.php');

$admin_username = $_SESSION['admin_username'];

// Đếm số khảo sát chờ duyệt
$pendingStmt = $pdo->query("SELECT COUNT(*) FROM surveys WHERE status = 'pending_approval'");
$pendingCount = $pendingStmt->fetchColumn();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
         body { font-family: sans-serif; margin: 0; }
         .admin-header { background-color: #2c3e50; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
         .admin-header h1 { margin: 0; font-size: 1.5em; }
         .admin-header a { color: #ecf0f1; text-decoration: none; margin-left: 15px;}
         .admin-container { padding: 20px; }
         .stat-box { border: 1px solid #ccc; padding: 15px; margin-bottom: 15px; border-radius: 5px; }
         .stat-box h3 { margin-top: 0; }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1>Admin Dashboard</h1>
        <div>
            <span>Welcome, <?php echo htmlspecialchars($admin_username); ?>!</span>
            <a href="approve_surveys.php">Approve Surveys (<?php echo $pendingCount; ?>)</a>
            <a href="actions/handle_admin_logout.php">Logout</a>
        </div>
    </header>
    <div class="admin-container">
        <h2>Overview</h2>
        <div class="stat-box">
            <h3>Surveys Pending Approval</h3>
            <p style="font-size: 2em; font-weight: bold;"><?php echo $pendingCount; ?></p>
            <a href="approve_surveys.php">View Pending Surveys</a>
        </div>
        </div>
</body>
</html>