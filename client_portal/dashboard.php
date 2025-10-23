<?php
session_start();
// Bảo vệ trang: chỉ client mới vào được
if (!isset($_SESSION['client_logged_in']) || $_SESSION['client_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
require_once('../../includes/db.php');

$clientId = $_SESSION['client_id'];
$companyName = $_SESSION['client_company_name'];

// Lấy danh sách khảo sát của client này
$stmt = $pdo->prepare(
    "SELECT survey_id, title, status, points_reward, created_at,
           (SELECT COUNT(*) FROM user_responses ur JOIN questions q ON ur.question_id = q.question_id WHERE q.survey_id = s.survey_id GROUP BY q.survey_id) as response_count
     FROM surveys s
     WHERE client_id = ?
     ORDER BY created_at DESC"
);
$stmt->execute([$clientId]);
$surveys = $stmt->fetchAll();

$message = $_SESSION['client_message'] ?? '';
$is_error = $_SESSION['client_error'] ?? false;
unset($_SESSION['client_message'], $_SESSION['client_error']);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Client Dashboard</title>
     <style>
        /* CSS tương tự admin dashboard */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .actions form, .actions a { display: inline-block; margin-right: 5px; }
        .actions button, .actions .btn-link { padding: 5px 10px; cursor: pointer; text-decoration: none; border: none; border-radius: 3px; }
        .create-btn { background-color: #27ae60; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; display: inline-block; margin-bottom: 15px;}
        .status-draft { color: gray; font-style: italic; }
        .status-pending_approval { color: orange; font-weight: bold; }
        .status-published { color: green; font-weight: bold; }
        .status-rejected { color: red; text-decoration: line-through; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <header class="admin-header"> <h1>Client Dashboard</h1>
        <div>
            <span>Welcome, <?php echo htmlspecialchars($companyName); ?>!</span>
            <a href="actions/handle_client_logout.php">Logout</a>
        </div>
    </header>
    <div class="admin-container"> <?php if ($message): ?>
            <p class="message <?php echo $is_error ? 'error' : 'success'; ?>"><?php echo $message; ?></p>
        <?php endif; ?>

        <a href="create_survey.php" class="create-btn">Create New Survey</a>

        <h2>Your Surveys</h2>
        <?php if (empty($surveys)): ?>
            <p>You haven't created any surveys yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Points</th>
                        <th>Responses</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($surveys as $survey): ?>
                        <tr>
                            <td><?php echo $survey['survey_id']; ?></td>
                            <td><?php echo htmlspecialchars($survey['title']); ?></td>
                            <td><span class="status-<?php echo $survey['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $survey['status'])); ?></span></td>
                            <td><?php echo $survey['points_reward']; ?></td>
                            <td><?php echo $survey['response_count'] ?? 0; ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($survey['created_at'])); ?></td>
                            <td class="actions">
                                <?php if ($survey['status'] == 'draft' || $survey['status'] == 'rejected'): ?>
                                    <a href="edit_survey.php?id=<?php echo $survey['survey_id']; ?>" class="btn-link" style="background-color: #f1c40f;">Edit</a>
                                    <form action="actions/submit_for_approval.php" method="POST" onsubmit="return confirm('Submit this survey for approval?');">
                                        <input type="hidden" name="survey_id" value="<?php echo $survey['survey_id']; ?>">
                                        <button type="submit" style="background-color: #3498db; color: white;">Submit for Approval</button>
                                    </form>
                                <?php elseif ($survey['status'] == 'published' || $survey['status'] == 'closed'): ?>
                                    <a href="view_results.php?id=<?php echo $survey['survey_id']; ?>" class="btn-link" style="background-color: #1abc9c; color: white;">View Results</a>
                                    <?php elseif ($survey['status'] == 'pending_approval'): ?>
                                    <span>Pending</span>
                                <?php endif; ?>
                                 </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>