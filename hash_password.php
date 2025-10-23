<?php
$password = 'nhan'; // Thay mật khẩu của bạn vào đây
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Hashed Password: " . $hash;
?>