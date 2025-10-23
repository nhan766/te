<?php
// Bắt đầu session ngay đầu file
session_start();

// Kiểm tra nếu đã đăng nhập thì chuyển hướng về dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Lấy thông báo lỗi/thành công từ session (nếu có)
$error_message = $_SESSION['error_message'] ?? '';
$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['error_message'], $_SESSION['success_message']); // Xóa sau khi lấy
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập / Đăng ký - SurveyForGood</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        /* --- CSS của trang login --- */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Poppins", sans-serif; text-decoration: none; list-style: none; }

        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background: linear-gradient(90deg, #e2e2e2, #c9d6ff); }

        /* Container chính */
        .auth-container { position: relative; width: 850px; height: 580px; /* Tăng chiều cao nhẹ */ background: #fff; margin: 20px; border-radius: 12px; /* Giảm bo tròn */ box-shadow: 0 5px 25px rgba(0, 0, 0, .15); overflow: hidden; }

        /* Các form (login/register) */
        .form-box { position: absolute; top: 0; width: 50%; height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 40px; background: #fff; transition: all 0.6s ease-in-out; }

        .form-box h1 { font-size: 32px; margin-bottom: 10px; color: #333; }
        .form-box p { font-size: 14px; margin: 10px 0; color: #555;}

        .form-box.login { left: 0; z-index: 2; /* Login nằm trên mặc định */ }
        .form-box.register { left: 0; opacity: 0; z-index: 1; /* Register ẩn và nằm dưới */ }

        /* Hiệu ứng khi container có class 'active' */
        .auth-container.active .form-box.login { transform: translateX(100%); opacity: 0; z-index: 1; }
        .auth-container.active .form-box.register { transform: translateX(100%); opacity: 1; z-index: 5; animation: show 0.6s; }

        /* Animation đơn giản để đảm bảo register hiện rõ */
        @keyframes show {
            0%, 49.99% { opacity: 0; z-index: 1; }
            50%, 100% { opacity: 1; z-index: 5; }
        }

        /* Input fields */
        .input-box { position: relative; margin: 15px 0; width: 100%; max-width: 320px; }
        .input-box input { width: 100%; padding: 10px 40px 10px 15px; background: #f0f0f0; border-radius: 6px; border: 1px solid #eee; outline: none; font-size: 15px; color: #333; font-weight: 500; }
        .input-box input:focus { border-color: #3498db; }
        .input-box input::placeholder { color: #999; font-weight: 400; }
        .input-box i { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); font-size: 18px; color: #777; }

        .forgot-link { margin: -5px 0 10px; width: 100%; max-width: 320px; text-align: right; }
        .forgot-link a { font-size: 13px; color: #555; text-decoration: underline;}

        /* Buttons */
        .btn { width: 100%; max-width: 320px; height: 45px; background: #3498db; border-radius: 6px; box-shadow: 0 4px 8px rgba(0, 0, 0, .1); border: none; cursor: pointer; font-size: 15px; color: #fff; font-weight: 600; margin-top: 10px; transition: background-color 0.3s; }
        .btn:hover { background-color: #2980b9; }

        .social-icons { display: flex; justify-content: center; margin-top: 15px;}
        .social-icons a { display: inline-flex; justify-content: center; align-items: center; width: 40px; height: 40px; border: 1px solid #ddd; border-radius: 50%; font-size: 18px; color: #555; margin: 0 5px; transition: background-color 0.3s, color 0.3s; }
        .social-icons a:hover { background-color: #eee; color: #333; }

        /* Overlay (phần chuyển động) */
        .toggle-box { position: absolute; top: 0; left: 50%; width: 50%; height: 100%; overflow: hidden; z-index: 100; transition: all .6s ease-in-out; border-radius: 0 12px 12px 0; }
        .auth-container.active .toggle-box { transform: translateX(-100%); border-radius: 12px 0 0 12px; }

        .toggle-box::before { content: ''; position: absolute; width: 200%; height: 100%; background: linear-gradient(to right, #3498db, #2980b9); top: 0; left: -100%; z-index: 2; transform: translateX(0); transition: all .6s ease-in-out; }
        .auth-container.active .toggle-box::before { transform: translateX(50%); }

        .toggle-panel { position: absolute; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; flex-direction: column; padding: 0 40px; text-align: center; top: 0; z-index: 3; color: #fff; transform: translateX(0); transition: all .6s ease-in-out; }
        .toggle-left { transform: translateX(-200%); }
        .auth-container.active .toggle-left { transform: translateX(0); }
        .toggle-right { transform: translateX(0); }
        .auth-container.active .toggle-right { transform: translateX(200%); }

        .toggle-panel h1 { font-size: 30px; margin-bottom: 10px; }
        .toggle-panel p { margin-bottom: 20px; font-size: 14px; line-height: 1.5; }
        .toggle-panel .btn { width: 140px; height: 40px; background: transparent; border: 1px solid #fff; box-shadow: none; font-size: 14px;}

         /* Thông báo lỗi/thành công */
        .message { text-align: center; margin-top: 5px; margin-bottom: 5px; font-weight: bold; font-size: 14px; width: 100%; max-width: 320px; }
        .error { color: #e74c3c; }
        .success { color: #27ae60; }

        /* Responsive */
        @media screen and (max-width: 880px) { /* Tăng breakpoint một chút */
             .auth-container { width: 90%; max-width: 450px; height: auto; margin: 30px auto; display: flex; flex-direction: column; overflow: visible; box-shadow: 0 5px 15px rgba(0,0,0,0.15); border-radius: 10px; }
             .form-box { position: relative; width: 100%; height: auto; padding: 30px 25px; transition: none; opacity: 1 !important; transform: none !important; z-index: 1 !important; box-shadow: none; }
             .form-box.register { display: none; }
             .auth-container.active .form-box.login { display: none; }
             .auth-container.active .form-box.register { display: block; }
             .toggle-box { display: none; } /* Ẩn overlay */
            /* Nút chuyển đổi mobile */
            .mobile-toggle { text-align: center; margin-top: 20px; font-size: 14px; padding-bottom: 10px; color: #555;}
            .mobile-toggle a { color: #2980b9; font-weight: bold; cursor: pointer; text-decoration: underline;}
            .input-box { max-width: none; } /* Bỏ max-width input trên mobile */
            .btn { max-width: none; } /* Bỏ max-width button trên mobile */
        }
    </style>
</head>
<body>
    <div class="auth-container" id="auth-container">
        <div class="form-box login">
            <form action="actions/handle_login.php" method="POST">
                <h1>Đăng nhập</h1>
                <?php if ($error_message && !isset($_SESSION['register_error'])): ?>
                    <p class="message error"><?php echo $error_message; ?></p>
                <?php endif; ?>
                <?php if ($success_message): ?>
                    <p class="message success"><?php echo $success_message; ?></p>
                <?php endif; ?>
                <div class="input-box">
                    <input type="text" name="username" placeholder="Tên đăng nhập hoặc Email" required>
                    <i class='bx bxs-user'></i>
                </div>
                <div class="input-box">
                    <input type="password" name="password" placeholder="Mật khẩu" required>
                    <i class='bx bxs-lock-alt' ></i>
                </div>
                <div class="forgot-link">
                    <a href="forgot_password.php">Quên mật khẩu?</a>
                </div>
                <button type="submit" class="btn">Đăng nhập</button>
                 <p>hoặc đăng nhập bằng</p>
                 <div class="social-icons">
                     <a href="#"><i class='bx bxl-google' ></i></a>
                     <a href="#"><i class='bx bxl-facebook' ></i></a>
                     <a href="#"><i class='bx bxl-github' ></i></a>
                 </div>
                 <div class="mobile-toggle">Chưa có tài khoản? <a id="show-register-mobile">Đăng ký</a></div>
            </form>
        </div>

        <div class="form-box register">
            <form action="actions/handle_register.php" method="POST">
                <h1>Đăng kí</h1>
                 <?php if ($error_message && isset($_SESSION['register_error'])): ?>
                    <p class="message error"><?php echo $error_message; ?></p>
                <?php endif; ?>
                <div class="input-box">
                    <input type="text" name="username" placeholder="Tên đăng nhập" required>
                    <i class='bx bxs-user'></i>
                </div>
                <div class="input-box">
                    <input type="email" name="email" placeholder="Email" required>
                    <i class='bx bxs-envelope' ></i>
                </div>
                <div class="input-box">
                    <input type="password" name="password" placeholder="Mật khẩu" required>
                    <i class='bx bxs-lock-alt' ></i>
                </div>
                 <div class="input-box">
                    <input type="password" name="confirm_password" placeholder="Xác nhận Mật khẩu" required>
                    <i class='bx bxs-lock-alt' ></i>
                </div>
                <button type="submit" class="btn">Đăng kí</button>
                <p>hoặc đăng ký bằng</p>
                 <div class="social-icons">
                     <a href="#"><i class='bx bxl-google' ></i></a>
                     <a href="#"><i class='bx bxl-facebook' ></i></a>
                     <a href="#"><i class='bx bxl-github' ></i></a>
                 </div>
                 <div class="mobile-toggle">Đã có tài khoản? <a id="show-login-mobile">Đăng nhập</a></div>
            </form>
        </div>

        <div class="toggle-box">
             <div class="toggle-panel toggle-left">
                <h1>Chào mừng trở lại!</h1>
                <p>Đăng nhập để tiếp tục hành trình chia sẻ ý kiến và nhận thưởng.</p>
                <button class="btn login-btn" id="login-btn-toggle">Đăng nhập</button>
            </div>
            <div class="toggle-panel toggle-right">
                 <h1>Xin chào!</h1>
                <p>Đăng ký và bắt đầu kiếm điểm thưởng ngay hôm nay!</p>
                <button class="btn register-btn" id="register-btn-toggle">Đăng kí</button>
            </div>
        </div>
    </div>

<script>
    // JS để chuyển đổi form (Desktop và Mobile)
    const container = document.getElementById('auth-container');
    const registerBtnToggle = document.getElementById('register-btn-toggle');
    const loginBtnToggle = document.getElementById('login-btn-toggle');
    const showRegisterMobile = document.getElementById('show-register-mobile');
    const showLoginMobile = document.getElementById('show-login-mobile');

    function activateRegister() {
        if(container) container.classList.add('active');
    }
    function deactivateRegister() {
         if(container) container.classList.remove('active');
    }

    if (registerBtnToggle) registerBtnToggle.addEventListener('click', activateRegister);
    if (loginBtnToggle) loginBtnToggle.addEventListener('click', deactivateRegister);
    if (showRegisterMobile) showRegisterMobile.addEventListener('click', (e) => { e.preventDefault(); activateRegister(); });
    if (showLoginMobile) showLoginMobile.addEventListener('click', (e) => { e.preventDefault(); deactivateRegister(); });

    // Kiểm tra session lỗi đăng ký từ PHP
    <?php if (isset($_SESSION['register_error'])): ?>
        activateRegister(); // Tự động chuyển sang form đăng ký nếu có lỗi
        <?php unset($_SESSION['register_error']); // Xóa session lỗi ?>
    <?php endif; ?>

</script>

</body>
</html>