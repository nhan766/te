<?php
session_start();
// Bảo vệ trang: chỉ admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php'); exit;
}
require_once('../../includes/db.php');

$reward_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$reward_data = null;
$page_title = "Add New Reward";

if ($reward_id) {
    $stmt = $pdo->prepare("SELECT * FROM rewards WHERE reward_id = ?");
    $stmt->execute([$reward_id]);
    $reward_data = $stmt->fetch();
    if ($reward_data) {
        $page_title = "Edit Reward: " . htmlspecialchars($reward_data['title']);
    } else {
        // ID không hợp lệ, chuyển hướng
        $_SESSION['reward_manage_message'] = "Reward not found.";
        $_SESSION['reward_manage_error'] = true;
        header('Location: manage_rewards.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $page_title; ?></title>
     <style>
        /* CSS tương tự create_survey */
        body { font-family: sans-serif; margin: 0; }
        .admin-header { background-color: #2c3e50; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .admin-header h1 { margin: 0; font-size: 1.5em; }
        .admin-header a { color: #ecf0f1; text-decoration: none; margin-left: 15px;}
        .admin-container { padding: 20px; max-width: 600px; margin: auto; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold;}
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="url"],
        .form-group textarea,
        .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;}
        button { padding: 10px 15px; cursor: pointer; border-radius: 4px; border: none; font-size: 1em;}
        .btn-primary { background-color: #27ae60; color: white; }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1><?php echo $page_title; ?></h1>
        <div>
            <a href="manage_rewards.php">Back to List</a>
            <a href="actions/handle_admin_logout.php">Logout</a>
        </div>
    </header>
    <div class="admin-container">
         <form action="actions/handle_save_reward.php" method="POST">
             <?php if ($reward_id): ?>
                <input type="hidden" name="reward_id" value="<?php echo $reward_id; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="title">Reward Title:</label>
                <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($reward_data['title'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="points_cost">Points Cost:</label>
                <input type="number" id="points_cost" name="points_cost" required min="1" value="<?php echo htmlspecialchars($reward_data['points_cost'] ?? ''); ?>">
            </div>
             <div class="form-group">
                <label for="description">Description (Optional):</label>
                <textarea id="description" name="description"><?php echo htmlspecialchars($reward_data['description'] ?? ''); ?></textarea>
            </div>
             <div class="form-group">
                <label for="image_url">Image URL (relative path e.g., image/voucher.png):</label>
                <input type="text" id="image_url" name="image_url" value="<?php echo htmlspecialchars($reward_data['image_url'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="category">Category (Optional):</label>
                <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($reward_data['category'] ?? ''); ?>">
            </div>
             <div class="form-group">
                <label for="stock">Stock (Leave blank for unlimited):</label>
                <input type="number" id="stock" name="stock" min="0" value="<?php echo htmlspecialchars($reward_data['stock'] ?? ''); ?>">
            </div>
             <div class="form-group">
                 <label>
                     <input type="checkbox" name="is_active" value="1" <?php echo ($reward_data === null || $reward_data['is_active']) ? 'checked' : ''; ?>>
                     Active (Visible to users)
                 </label>
             </div>
            <button type="submit" class="btn-primary">Save Reward</button>
        </form>
    </div>
</body>
</html>