<?php
session_start();
// Bảo vệ trang: chỉ admin mới vào được
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
require_once('../../includes/db.php');

// Logic tìm kiếm và phân trang
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20; // Số client mỗi trang
$offset = ($page - 1) * $limit;

// --- Sửa SQL ---
// Chọn các cột đúng từ bảng clients
$sql = "SELECT client_id, company_name, contact_email, created_at, is_active FROM clients";
$countSql = "SELECT COUNT(*) FROM clients";
$params = [];
$countParams = [];

// Thêm điều kiện tìm kiếm (sửa tên cột email)
if (!empty($search)) {
    $sql .= " WHERE (company_name LIKE ? OR contact_email LIKE ?)";
    $countSql .= " WHERE (company_name LIKE ? OR contact_email LIKE ?)";
    $searchTerm = "%" . $search . "%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $countParams[] = $searchTerm;
    $countParams[] = $searchTerm;
}

// Sửa ORDER BY (dùng created_at thay vì join_date)
$sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";

// Đếm tổng số clients
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($countParams);
$totalClients = $countStmt->fetchColumn(); // Sửa tên biến
$totalPages = ceil($totalClients / $limit); // Sửa tên biến

// Thêm limit và offset vào params
$params[] = $limit;
$params[] = $offset;

// Lấy danh sách clients
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clients = $stmt->fetchAll();

// Sửa tên biến session message
$message = $_SESSION['client_manage_message'] ?? '';
$is_error = $_SESSION['client_manage_error'] ?? false;
unset($_SESSION['client_manage_message'], $_SESSION['client_manage_error']);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Clients</title>
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
        .edit-btn { background-color: #f1c40f; color: #333;} /* Giữ lại nút Edit */
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
        <h1>Manage Clients</h1>
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
            <input type="text" name="search" placeholder="Search company or email..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
            <?php if (!empty($search)): ?>
                <a href="manage_clients.php">Clear Search</a>
            <?php endif; ?>
        </form>

        <h2>Client List (<?php echo $totalClients; ?>)</h2>
        <?php if (empty($clients)): ?>
            <p>No clients found matching your criteria.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Company Name</th>
                        <th>Contact Email</th>
                        <th>Registered</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $client): ?>
                        <tr style="<?php echo !$client['is_active'] ? 'background-color: #fdd;' : ''; ?>">
                            <td><?php echo $client['client_id']; ?></td>
                            <td><?php echo htmlspecialchars($client['company_name']); ?></td>
                            <td><?php echo htmlspecialchars($client['contact_email']); ?></td>
                            <td><?php echo date('Y-m-d', strtotime($client['created_at'])); ?></td>
                            <td><?php echo $client['is_active'] ? 'Active' : 'Banned'; ?></td>
                            <td class="actions">
                                <a href="edit_client.php?id=<?php echo $client['client_id']; ?>" class="btn-link edit-btn">Edit</a>

                                <?php if ($client['is_active']): ?>
                                <form action="actions/handle_client_status.php" method="POST" onsubmit="return confirm('Ban this client? Their surveys might be affected.');">
                                    <input type="hidden" name="client_id" value="<?php echo $client['client_id']; ?>">
                                    <input type="hidden" name="action" value="ban">
                                    <button type="submit" class="ban-btn">Ban</button>
                                </form>
                                <?php else: ?>
                                <form action="actions/handle_client_status.php" method="POST" onsubmit="return confirm('Unban this client?');">
                                    <input type="hidden" name="client_id" value="<?php echo $client['client_id']; ?>">
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

    </body>
</html>