<?php
session_start();
// Không cần require_once('db.php') nếu chỉ gửi email

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    // --- Validation ---
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $_SESSION['contact_message'] = "Vui lòng điền đầy đủ thông tin.";
        $_SESSION['contact_error'] = true;
        header('Location: ../lienhe.php');
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['contact_message'] = "Địa chỉ email không hợp lệ.";
        $_SESSION['contact_error'] = true;
        header('Location: ../lienhe.php');
        exit;
    }

    // --- Gửi Email ---
    $to = "your_admin_email@example.com"; // Thay bằng email của bạn
    $email_subject = "New Contact Form Submission: " . $subject;
    $headers = "From: " . $name . " <" . $email . ">\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n"; // Để hỗ trợ tiếng Việt

    $email_body = "You have received a new message from the contact form:\n\n";
    $email_body .= "Name: $name\n";
    $email_body .= "Email: $email\n";
    $email_body .= "Subject: $subject\n";
    $email_body .= "Message:\n$message\n";

    // Sử dụng hàm mail() cơ bản của PHP (cần server cấu hình)
    if (mail($to, $email_subject, $email_body, $headers)) {
        $_SESSION['contact_message'] = "Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất có thể.";
    } else {
        // Lỗi gửi mail
        error_log("Mail sending failed for contact form."); // Ghi log lỗi
        $_SESSION['contact_message'] = "Đã xảy ra lỗi khi gửi tin nhắn. Vui lòng thử lại sau hoặc liên hệ qua email trực tiếp.";
        $_SESSION['contact_error'] = true;
    }

    // --- Hoặc Lưu vào CSDL ---
    /*
    require_once('../includes/db.php');
    try {
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $subject, $message]);
        $_SESSION['contact_message'] = "Cảm ơn bạn đã liên hệ!";
    } catch (PDOException $e) {
        error_log("Contact Save Error: " . $e->getMessage());
        $_SESSION['contact_message'] = "Lỗi khi lưu tin nhắn.";
        $_SESSION['contact_error'] = true;
    }
    */

    header('Location: ../lienhe.php'); // Quay lại trang liên hệ
    exit;

} else {
    header('Location: ../lienhe.php');
    exit;
}
?>