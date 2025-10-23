<?php
session_start();
// Bảo vệ trang: chỉ admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php'); exit;
}
require_once('../../includes/db.php');

// Lấy danh sách phần thưởng
$stmt = $pdo->query("SELECT * FROM rewards ORDER BY is_active DESC, points_cost ASC");
$rewards = $stmt->fetchAll();

$message = $_SESSION['reward_manage_message'] ?? '';
$is_error = $_SESSION['reward_manage_error'] ?? false;
unset($_SESSION['reward_manage_message'], $_SESSION['reward_manage_error']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Rewards</title>
     <style>
        /* CSS tương tự manage_users.php */
        body { font-family: sans-serif; margin: 0; }
        .admin-header { background-color: #2c3e50; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .admin-header h1 { margin: 0; font-size: 1.5em; }
        .admin-header a { color: #ecf0f1; text-decoration: none; margin-left: 15px;}
        .admin-container { padding: 20px; }
        .create-btn { background-color: #27ae60; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; display: inline-block; margin-bottom: 15px;}
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .actions form, .actions a { display: inline-block; margin-right: 5px; }
        .actions button, .actions .btn-link { padding: 5px 8px; cursor: pointer; text-decoration: none; border-radius: 3px; border: none; }
        .edit-btn { background-color: #f1c40f; color: #333;}
        .deactivate-btn { background-color: #e67e22; color: white; }
        .activate-btn { background-color: #2ecc71; color: white; }
        .delete-btn { background-color: #e74c3c; color: white; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        img.reward-thumb { max-width: 50px; max-height: 50px; vertical-align: middle; }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1>Manage Rewards</h1>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="actions/handle_admin_logout.php">Logout</a>
        </div>
    </header>
    <div class="admin-container">
        <?php if ($message): ?>
            <p class="message <?php echo $is_error ? 'error' : 'success'; ?>"><?php echo $message; ?></p>
        <?php endif; ?>

        <a href="edit_reward.php" class="create-btn">Add New Reward</a>

        <h2>Reward List</h2>
        <?php if (empty($rewards)): ?>
            <p>No rewards found. Add one!</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Points Cost</th>
                        <th>Stock</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rewards as $reward): ?>
                         <tr style="<?php echo !$reward['is_active'] ? 'opacity: 0.6;' : ''; ?>">
                            <td><?php echo $reward['reward_id']; ?></td>
                            <td><img src="../<?php echo htmlspecialchars($reward['image_url'] ?? 'image/placeholder_voucher.png'); ?>" alt="Thumb" class="reward-thumb"></td>
                            <td><?php echo htmlspecialchars($reward['title']); ?></td>
                            <td><?php echo number_format($reward['points_cost']); ?></td>
                            <td><?php echo $reward['stock'] ?? 'Unlimited'; ?></td>
                            <td><?php echo htmlspecialchars($reward['category'] ?? 'N/A'); ?></td>
                            <td><?php echo $reward['is_active'] ? 'Active' : 'Inactive'; ?></td>
                            <td class="actions">
                                <a href="edit_reward.php?id=<?php echo $reward['reward_id']; ?>" class="edit-btn btn-link">Edit</a>
                                <?php if ($reward['is_active']): ?>
                                <form action="actions/handle_reward_status.php" method="POST" onsubmit="return confirm('Deactivate this reward?');">
                                    <input type="hidden" name="reward_id" value="<?php echo $reward['reward_id']; ?>">
                                    <input type="hidden" name="action" value="deactivate">
                                    <button type="submit" class="deactivate-btn">Deactivate</button>
                                </form>
                                <?php else: ?>
                                <form action="actions/handle_reward_status.php" method="POST" onsubmit="return confirm('Activate this reward?');">
                                    <input type="hidden" name="reward_id" value="<?php echo $reward['reward_id']; ?>">
                                    <input type="hidden" name="action" value="activate">
                                    <button type="submit" class="activate-btn">Activate</button>
                                </form>
                                <?php endif; ?>
                                 <form action="actions/handle_delete_reward.php" method="POST" onsubmit="return confirm('DELETE this reward permanently? This cannot be undone!');">
                                    <input type="hidden" name="reward_id" value="<?php echo $reward['reward_id']; ?>">
                                    <button type="submit" class="delete-btn">Delete</button>
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