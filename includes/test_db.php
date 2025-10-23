<?php
// test_db.php
ini_set('display_errors', 1); // Bật hiển thị lỗi chi tiết (CHỈ DÙNG ĐỂ TEST!)
error_reporting(E_ALL);

$host = 'localhost'; // Thay đổi nếu cần
$db   = 'fcmyboomhosting_survey_db';
$user = 'fcmyboomhosting_Nhan_7626';
$pass = 'Nhan_7626'; // Dùng mật khẩu MỚI nếu bạn đã reset
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

echo "<h2>Testing Database Connection...</h2>";
echo "Host: " . htmlspecialchars($host) . "<br>";
echo "Database: " . htmlspecialchars($db) . "<br>";
echo "User: " . htmlspecialchars($user) . "<br>";
echo "Password: [HIDDEN]<br><hr>";

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     echo "<h3 style='color:green;'>SUCCESS: Connected to the database '$db' successfully!</h3>";

     // Thử truy vấn đơn giản
     $stmt = $pdo->query("SHOW TABLES");
     echo "Tables found:<br><ul>";
     while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
         echo "<li>" . htmlspecialchars($row[0]) . "</li>";
     }
     echo "</ul>";

} catch (\PDOException $e) {
     echo "<h3 style='color:red;'>ERROR: Connection Failed!</h3>";
     echo "<b>Error Code:</b> " . $e->getCode() . "<br>";
     echo "<b>Error Message:</b> " . $e->getMessage() . "<br><hr>";

     // Gợi ý dựa trên mã lỗi
     if ($e->getCode() == 1045) { // Access denied
        echo "<p><b>Suggestion:</b> Lỗi Access Denied. Hãy kiểm tra lại <b>Username</b> ('$user') và <b>Password</b>. Đồng thời, xác nhận user này đã được <b>gán quyền</b> cho database '$db' trong hosting control panel.</p>";
     } elseif ($e->getCode() == 2002 || $e->getCode() == 2003) { // Cannot connect
        echo "<p><b>Suggestion:</b> Không thể kết nối đến MySQL server. Hãy kiểm tra lại <b>Hostname</b> ('$host'). Nếu là 'localhost' thì có thể MySQL server trên hosting đang gặp sự cố. Liên hệ nhà cung cấp hosting nếu bạn không chắc chắn.</p>";
     } elseif ($e->getCode() == 1044) { // Access denied for user to database
         echo "<p><b>Suggestion:</b> User '$user' không có quyền truy cập database '$db'. Hãy vào hosting control panel và <b>gán (Add User To Database)</b> user này vào database đó, cấp đủ quyền (PRIVILEGES).</p>";
     } elseif ($e->getCode() == 1049) { // Unknown database
          echo "<p><b>Suggestion:</b> Database '$db' không tồn tại trên server. Hãy kiểm tra lại <b>tên database</b> xem có chính xác không.</p>";
     } else {
         echo "<p><b>Suggestion:</b> Đã xảy ra lỗi không xác định. Liên hệ nhà cung cấp hosting để được hỗ trợ.</p>";
     }
}
?>