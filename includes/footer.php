<footer>
    <div id="lh" class="container">
        <div class="footer-content">
            <!-- Cột 1: Giới thiệu -->
            <div class="footer-column">
                <h3>Về SurveyForGood</h3>
                <p>
                    SurveyForGood là nền tảng khảo sát trực tuyến hàng đầu, 
                    giúp người dùng chia sẻ ý kiến, nhận thưởng và đóng góp 
                    cho các hoạt động xã hội.
                </p>
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://github.com/nhan766/" class="social-link"><i class="fab fa-github"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                </div>
            </div>

            <!-- Cột 2: Liên kết nhanh -->
            <div class="footer-column">
                <h3>Liên kết nhanh</h3>
                <ul class="footer-links">
                    <li><a href="<?php echo isset($_SESSION['user_id']) ? 'dashboard.php' : 'index.php'; ?>">Trang chủ</a></li>
                    <li><a href="#ab">Về chúng tôi</a></li>
                    <li><a href="#ct">Cách thức hoạt động</a></li>
                    <li><a href="#">Điều khoản dịch vụ</a></li>
                    <li><a href="#">Chính sách bảo mật</a></li>
                </ul>
            </div>

            <!-- Cột 3: Hỗ trợ -->
            <div class="footer-column">
                <h3>Hỗ trợ</h3>
                <ul class="footer-links">
                    <li><a href="#">Trung tâm hỗ trợ</a></li>
                    <li><a href="<?php echo isset($_SESSION['user_id']) ? 'dashboard.php#faq' : 'index.php#faq'; ?>">Câu hỏi thường gặp</a></li>
                    <li><a href="lienhe.php">Liên hệ</a></li>
                    <li><a href="#">Các tổ chức từ thiện đối tác</a></li>
                    <li><a href="baocao.php">Báo cáo tác động xã hội</a></li>
                </ul>
            </div>

            <!-- Cột 4: Liên hệ -->
            <div class="footer-column">
                <h3>Liên hệ</h3>
                <p><i class="fas fa-envelope"></i> Email: info@surveyforgood.com</p>
                <p><i class="fas fa-phone-alt"></i> Điện thoại: 0345968311</p>
                <p><i class="fas fa-map-marker-alt"></i> Địa chỉ: Hòa Hải, Ngũ Hành Sơn, Đà Nẵng, Việt Nam</p>
            </div>
        </div>

        <!-- Dòng cuối -->
        <div class="footer-bottom">
            <p>&copy; <?php echo date("Y"); ?> SurveyForGood. Tất cả các quyền được bảo lưu.</p>
        </div>
    </div>
</footer>

<!-- JS cuối trang -->
<script src="js/script.js"></script>
