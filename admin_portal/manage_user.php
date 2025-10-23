<?php
session_start();
// Bảo vệ trang: chỉ admin mới vào được
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
require_once('../../includes/db.php');

// Logic tìm kiếm và phân trang (tương tự khaosat.php/lsdt.php)
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$sql = "SELECT id, username, email, points, level, join_date, is_active FROM users WHERE is_admin = FALSE"; // Không hiển thị admin khác
$countSql = "SELECT COUNT(*) FROM users WHERE is_admin = FALSE";
$params = [];
$countParams = [];

if (!empty($search)) {
    $sql .= " AND (username LIKE ? OR email LIKE ?)";
    $countSql .= " AND (username LIKE ? OR email LIKE ?)";
    $searchTerm = "%" . $search . "%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $countParams[] = $searchTerm;
    $countParams[] = $searchTerm;
}

$sql .= " ORDER BY join_date DESC LIMIT ? OFFSET ?";

// Đếm tổng số users
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($countParams);
$totalUsers = $countStmt->fetchColumn();
$totalPages = ceil($totalUsers / $limit);

// Thêm limit và offset vào params
$params[] = $limit;
$params[] = $offset;

// Lấy danh sách users
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$message = $_SESSION['user_manage_message'] ?? '';
$is_error = $_SESSION['user_manage_error'] ?? false;
unset($_SESSION['user_manage_message'], $_SESSION['user_manage_error']);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <style>
        /* CSS tương tự approve_surveys.php */
        body { font-family: sans-serif; margin: 0; }
        .admin-header { background-color: #2c3e50; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .admin-header h1 { margin: 0; font-size: 1.5em; }
        .admin-header a { color: #ecf0f1; text-decoration: none; margin-left: 15px;}
        .admin-container { padding: 20px; }
        .search-form { margin-bottom: 15px; }
        .search-form input { padding: 8px; margin-right: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .actions form, .actions a { display: inline-block; margin-right: 5px; }
        .actions button, .actions .btn-link { padding: 5px 8px; cursor: pointer; text-decoration: none; border-radius: 3px; border: none; }
        .edit-btn { background-color: #f1c40f; color: #333;}
        .ban-btn { background-color: #e74c3c; color: white; }
        .unban-btn { background-color: #2ecc71; color: white; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        .pagination a, .pagination span { margin: 0 5px; text-decoration: none; padding: 5px 10px; border: 1px solid #ccc; border-radius: 3px; }
        .pagination .active { background-color: #2980b9; color: white; border-color: #2980b9;}
        .pagination { margin-top: 20px; text-align: center; }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1>Manage Users</h1>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="actions/handle_admin_logout.php">Logout</a>
        </div>
    </header>
    <div class="admin-container">
        <?php if ($message): ?>
            <p class="message <?php echo $is_error ? 'error' : 'success'; ?>"><?php echo $message; ?></p>
        <?php endif; ?>

        <form method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search username or email..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
            <?php if (!empty($search)): ?>
                <a href="manage_users.php">Clear Search</a>
            <?php endif; ?>
        </form>

        <h2>User List (<?php echo $totalUsers; ?>)</h2>
        <?php if (empty($users)): ?>
            <p>No users found matching your criteria.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Points</th>
                        <th>Level</th>
                        <th>Joined</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr style="<?php echo !$user['is_active'] ? 'background-color: #fdd;' : ''; ?>">
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo number_format($user['points']); ?></td>
                            <td><?php echo ucfirst($user['level'] ?? 'N/A'); ?></td>
                            <td><?php echo date('Y-m-d', strtotime($user['join_date'])); ?></td>
                            <td><?php echo $user['is_active'] ? 'Active' : 'Banned'; ?></td>
                            <td class="actions">
                                <button class="edit-btn" onclick="openEditModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars(addslashes($user['username'])); ?>', <?php echo $user['points']; ?>)">Edit Points</button>

                                <?php if ($user['is_active']): ?>
                                <form action="actions/handle_user_status.php" method="POST" onsubmit="return confirm('Ban this user?');">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="action" value="ban">
                                    <button type="submit" class="ban-btn">Ban</button>
                                </form>
                                <?php else: ?>
                                <form action="actions/handle_user_status.php" method="POST" onsubmit="return confirm('Unban this user?');">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="action" value="unban">
                                    <button type="submit" class="unban-btn">Unban</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

             <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">Previous</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" class="<?php if ($i == $page) echo 'active'; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next</a>
                <?php endif; ?>
            </div>

        <?php endif; ?>
    </div>

    <div id="edit-points-modal" style="display:none; position: fixed; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1001;">
         <div style="background: #fff; padding: 20px; border-radius: 5px; width: 350px;">
             <h3>Edit Points for <span id="modal-username"></span></h3>
             <form action="actions/handle_edit_points.php" method="POST">
                 <input type="hidden" id="modal-user-id" name="user_id">
                 <div class="form-group">
                     <label for="new_points">New Points Total:</label>
                     <input type="number" id="modal-current-points" name="new_points" required style="width: 100%; padding: 8px;">
                 </div>
                 <div class="form-group">
                     <label for="reason">Reason (Optional):</label>
                     <input type="text" name="reason" placeholder="e.g., Manual adjustment" style="width: 100%; padding: 8px;">
                 </div>
                 <div style="text-align: right; margin-top: 15px;">
                     <button type="button" onclick="closeModal('edit-points-modal')" style="background-color: #ccc; margin-right: 5px;">Cancel</button>
                     <button type="submit" style="background-color: #27ae60; color: white;">Save Changes</button>
                 </div>
             </form>
         </div>
    </div>

<script>
    function openEditModal(userId, username, currentPoints) {
        document.getElementById('modal-user-id').value = userId;
        document.getElementById('modal-username').textContent = username;
        document.getElementById('modal-current-points').value = currentPoints;
        document.getElementById('edit-points-modal').style.display = 'flex';
    }
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }
</script>

</body>
</html>