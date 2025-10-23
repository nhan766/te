<?php
session_start();
require_once('../../includes/db.php');

// Chỉ admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php'); exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['client_id'])) {
    $clientId = (int)$_POST['client_id'];
    $companyName = trim($_POST['company_name']);
    $contactEmail = trim($_POST['contact_email']);
    $password = $_POST['password']; // Mật khẩu mới (có thể rỗng)
    $confirmPassword = $_POST['confirm_password'];

    // --- Validation ---
    if (empty($companyName) || empty($contactEmail)) {
        $_SESSION['client_edit_message'] = "Company Name and Contact Email are required.";
        $_SESSION['client_edit_error'] = true;
        header('Location: ../edit_client.php?id=' . $clientId);
        exit;
    }
    if (!filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['client_edit_message'] = "Invalid contact email format.";
        $_SESSION['client_edit_error'] = true;
        header('Location: ../edit_client.php?id=' . $clientId);
        exit;
    }

    // Kiểm tra mật khẩu mới (nếu được nhập)
    $passwordHash = null; // Sẽ giữ nguyên nếu $password rỗng
    if (!empty($password)) {
        if ($password !== $confirmPassword) {
            $_SESSION['client_edit_message'] = "New passwords do not match.";
            $_SESSION['client_edit_error'] = true;
            header('Location: ../edit_client.php?id=' . $clientId);
            exit;
        }
        // Thêm kiểm tra độ mạnh mật khẩu nếu cần
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    }

    // --- Cập nhật CSDL ---
    try {
        // Kiểm tra xem email mới có bị trùng với client khác không
        $stmtCheckEmail = $pdo->prepare("SELECT client_id FROM clients WHERE contact_email = ? AND client_id != ?");
        $stmtCheckEmail->execute([$contactEmail, $clientId]);
        if ($stmtCheckEmail->fetch()) {
             $_SESSION['client_edit_message'] = "Contact email is already used by another client.";
             $_SESSION['client_edit_error'] = true;
             header('Location: ../edit_client.php?id=' . $clientId);
             exit;
        }

        // Câu lệnh UPDATE
        if ($passwordHash) {
            // Cập nhật cả mật khẩu
            $stmtUpdate = $pdo->prepare("UPDATE clients SET company_name = ?, contact_email = ?, password_hash = ? WHERE client_id = ?");
            $params = [$companyName, $contactEmail, $passwordHash, $clientId];
        } else {
            // Chỉ cập nhật tên và email
            $stmtUpdate = $pdo->prepare("UPDATE clients SET company_name = ?, contact_email = ? WHERE client_id = ?");
            $params = [$companyName, $contactEmail, $clientId];
        }

        if ($stmtUpdate->execute($params)) {
             if ($stmtUpdate->rowCount() > 0) {
                 $_SESSION['client_manage_message'] = "Client information updated successfully.";
             } else {
                 $_SESSION['client_manage_message'] = "No changes were made to the client information.";
             }
        } else {
            $_SESSION['client_manage_message'] = "Failed to update client information.";
            $_SESSION['client_manage_error'] = true;
        }

    } catch (PDOException $e) {
        error_log("Edit Client Error: " . $e->getMessage());
        $_SESSION['client_manage_message'] = "Database error during update.";
        $_SESSION['client_manage_error'] = true;
        // Chuyển về trang edit để hiển thị lỗi
        header('Location: ../edit_client.php?id=' . $clientId);
        exit;
    }

    header('Location: ../manage_clients.php'); // Quay về danh sách client
    exit;

} else {
    $_SESSION['client_manage_message'] = "Invalid request.";
    $_SESSION['client_manage_error'] = true;
    header('Location: ../manage_clients.php');
    exit;
}
?>