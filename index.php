<?php
$page_title = "SurveyForGood - Kiếm tiền từ khảo sát";
// Include header. Header sẽ tự kiểm tra xem user đã login chưa
// Nếu chưa login, nó sẽ hiển thị menu và nút đăng nhập phù hợp
require_once('includes/header.php');

// Trong file header.php, $current_user sẽ là null nếu chưa đăng nhập.
// Dựa vào đó, header sẽ hiển thị nút "Tham gia khảo sát" (trỏ đến login.php)
?>

<section id="home" class="hero">
    <div class="container">
        <h1>Đổi thưởng bằng cách chia sẻ ý kiến của bạn</h1>
        <p>Tham gia SurveyForGood và nhận thưởng cho mỗi khảo sát bạn hoàn thành. Dễ dàng, nhanh chóng và miễn phí!</p>
        <a href="login.php" class="cta-button">Đăng ký ngay hôm nay</a>
    </div>
</section>

<section id="ab" class="features">
    <div class="container">
        <div class="section-title">
            <h2>Tại sao chọn SurveyForGood?</h2>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">💰</div>
                <h3>Kiếm tiền thật</h3>
                <p>Nhận điểm thưởng cho mỗi khảo sát bạn hoàn thành. Đổi điểm thưởng sang tiền mặt, thẻ quà tặng hoặc quyên góp cho các tổ chức từ thiện.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <h3>An toàn và bảo mật</h3>
                <p>Thông tin cá nhân của bạn luôn được bảo vệ. Chúng tôi không bao giờ chia sẻ dữ liệu của bạn với bên thứ ba.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📱</div>
                <h3>Khảo sát mọi lúc mọi nơi</h3>
                <p>Tham gia khảo sát trên máy tính hoặc thiết bị di động của bạn, bất cứ lúc nào, bất cứ nơi đâu.</p>
            </div>
        </div>
    </div>
</section>

<section id="ct" class="how-it-works fade-in">
     <div class="container">
         <div class="section-title">
             <h2>Cách thức hoạt động</h2>
         </div>
         <div class="steps">
             <div class="step">
                 <div class="step-number">1</div>
                 <h3>Tạo tài khoản</h3>
                 <p>Đăng ký miễn phí và hoàn thành hồ sơ của bạn để chúng tôi có thể tìm các khảo sát phù hợp với bạn.</p>
             </div>
             <div class="step">
                 <div class="step-number">2</div>
                 <h3>Tham gia khảo sát</h3>
                 <p>Nhận thông báo khi có khảo sát mới và hoàn thành chúng để kiếm điểm thưởng.</p>
             </div>
             <div class="step">
                 <div class="step-number">3</div>
                 <h3>Nhận phần thưởng hoặc quyên góp</h3>
                 <p>Đổi điểm thưởng của bạn sang tiền mặt, thẻ quà tặng hoặc quyên góp cho các tổ chức từ thiện mà bạn quan tâm.</p>
             </div>
         </div>
     </div>
</section>

<section id="dd" class="rewards-donation fade-in">
    <div class="container">
        <div class="section-title">
            <h2>Đổi điểm thưởng</h2>
        </div>
        
        <div class="rewards-tabs">
            <div class="rewards-grid">
                <?php
                    // Ví dụ lấy 3 phần thưởng mẫu
                    try {
                        $stmtSampleRewards = $pdo->query("SELECT title, points_cost, image_url FROM rewards WHERE is_active = TRUE ORDER BY RAND() LIMIT 3");
                        $sampleRewards = $stmtSampleRewards->fetchAll();
                        foreach ($sampleRewards as $reward):
                ?>
                        <div class="reward-card">
                             <div class="reward-image">
                                <img src="<?php echo htmlspecialchars($reward['image_url'] ?? 'image/pngtree-voucher-discount-vector-png-image_4609862.png'); ?>" alt="<?php echo htmlspecialchars($reward['title']); ?>" style="max-height: 100px; object-fit: contain;"/>
                             </div>
                             <div class="reward-info">
                                <h3><?php echo htmlspecialchars($reward['title']); ?></h3>
                                <div class="reward-points"><?php echo number_format($reward['points_cost']); ?> điểm</div>
                                <button class="redeem-btn" disabled>Đăng nhập để đổi</button>
                             </div>
                        </div>
                <?php
                        endforeach;
                    } catch (PDOException $e) {
                        echo "<p>Không thể tải ví dụ phần thưởng.</p>";
                        error_log("Sample Rewards Error: " . $e->getMessage());
                    }
                ?>
                <div class="reward-card donations">
                    <div class="reward-image">
                        <img src="image/pngtree-voucher-discount-vector-png-image_4609862.png" alt="Quyên góp" style="max-height: 100px; object-fit: contain;"/>
                    </div>
                    <div class="reward-info">
                        <h3>Quyên góp từ thiện</h3>
                        <div class="reward-points">Từ 1,000 điểm</div>
                        <button class="redeem-btn" disabled>Đăng nhập để quyên góp</button>
                    </div>
                </div>
            </div>
             <p style="text-align: center; margin-top: 20px;"><a href="login.php">Xem tất cả phần thưởng...</a></p>
        </div>

        <div class="donation-impact">
             <h3>Tác động từ quyên góp</h3>
             <div class="impact-stats">
                 <div class="impact-item"><div class="impact-number">1.2M</div><p>Tổng điểm đã quyên góp</p></div>
                 <div class="impact-item"><div class="impact-number">56</div><p>Dự án được hỗ trợ</p></div>
                 <div class="impact-item"><div class="impact-number">12K</div><p>Thành viên tham gia</p></div>
             </div>
             <a href="baocao.php" class="impact-report-link">Xem báo cáo tác động đầy đủ</a> </div>
    </div>
</section>

<section class="ways-to-earn fade-in">
    </section>

<section class="testimonials fade-in">
    </section>

<section id="faq" class="faq fade-in">
    </section>

<?php
require_once('includes/footer.php'); // Include footer
?>