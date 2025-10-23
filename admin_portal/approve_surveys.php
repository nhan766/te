<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
require_once('../../includes/db.php');

// Lấy danh sách khảo sát chờ duyệt, join với bảng clients để biết ai tạo
$stmt = $pdo->query(
    "SELECT s.*, c.company_name
     FROM surveys s
     JOIN clients c ON s.client_id = c.client_id
     WHERE s.status = 'pending_approval'
     ORDER BY s.created_at ASC"
);
$pendingSurveys = $stmt->fetchAll();

$message = $_SESSION['approval_message'] ?? '';
$is_error = $_SESSION['approval_error'] ?? false;
unset($_SESSION['approval_message'], $_SESSION['approval_error']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Approve Surveys</title>
    <style>
        /* CSS tương tự dashboard */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .actions form { display: inline-block; margin-right: 5px; }
        .actions button { padding: 5px 10px; cursor: pointer; }
        .approve-btn { background-color: #27ae60; color: white; border: none; }
        .reject-btn { background-color: #e74c3c; color: white; border: none; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
     <header class="admin-header">
        <h1>Approve Surveys</h1>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="actions/handle_admin_logout.php">Logout</a>
        </div>
    </header>
    <div class="admin-container">
         <?php if ($message): ?>
            <p class="message <?php echo $is_error ? 'error' : 'success'; ?>"><?php echo $message; ?></p>
        <?php endif; ?>

        <h2>Surveys Pending Approval (<?php echo count($pendingSurveys); ?>)</h2>
        <?php if (empty($pendingSurveys)): ?>
            <p>No surveys are currently pending approval.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Client</th>
                        <th>Points</th>
                        <th>Submitted At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingSurveys as $survey): ?>
                        <tr>
                            <td><?php echo $survey['survey_id']; ?></td>
                            <td><?php echo htmlspecialchars($survey['title']); ?></td>
                            <td><?php echo htmlspecialchars($survey['company_name']); ?></td>
                            <td><?php echo $survey['points_reward']; ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($survey['created_at'])); ?></td>
                            <td class="actions">
                                <button onclick="alert('View details functionality to be added');">View Details</button>
                                <form action="actions/handle_approval.php" method="POST">
                                    <input type="hidden" name="survey_id" value="<?php echo $survey['survey_id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="approve-btn">Approve</button>
                                </form>
                                <form action="actions/handle_approval.php" method="POST" onsubmit="return confirm('Reject this survey?');">
                                    <input type="hidden" name="survey_id" value="<?php echo $survey['survey_id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                     <button type="submit" class="reject-btn">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>