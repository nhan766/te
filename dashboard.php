<?php


$page_title = "Trang chủ của bạn";
require_once('includes/header.php'); // Header sẽ kiểm tra và lấy thông tin $current_user

// Yêu cầu đăng nhập, nếu $current_user là null, header nên đã redirect, nhưng kiểm tra lại cho chắc
if (!$current_user) {
    header('Location: login.php');
    exit;
}

// Lấy thêm thông tin cần thiết cho dashboard (ví dụ: khảo sát mới nhất, hoạt động gần đây...)
// Ví dụ: Lấy 3 khảo sát mới nhất
try {
    $stmtNewSurveys = $pdo->query("SELECT survey_id, title, points_reward FROM surveys WHERE status = 'published' ORDER BY created_at DESC LIMIT 3");
    $newSurveys = $stmtNewSurveys->fetchAll();
} catch (PDOException $e) {
    $newSurveys = []; // Bỏ qua lỗi và hiển thị rỗng
    error_log("Dashboard New Surveys Error: " . $e->getMessage());
}

// Ví dụ: Lấy 3 hoạt động gần nhất
try {
    $stmtRecentActivities = $pdo->prepare("SELECT activity_description, points_change, DATE_FORMAT(activity_time, '%H:%i %d/%m') as time_formatted FROM user_activities WHERE user_id = ? ORDER BY activity_time DESC LIMIT 3");
    $stmtRecentActivities->execute([$current_user['id']]);
    $recentActivities = $stmtRecentActivities->fetchAll();
} catch (PDOException $e) {
    $recentActivities = [];
    error_log("Dashboard Recent Activities Error: " . $e->getMessage());
}

// Lấy số liệu donation impact (tương tự baocao.php)
$totalDonatedPoints = 0;
$totalProjects = 56; // Giữ giá trị mẫu hoặc lấy từ CSDL
$totalDonatingMembers = 0;
try {
    // Ước tính tổng điểm quyên góp (Cần JOIN với bảng rewards và kiểm tra category='donation')
    $stmtPoints = $pdo->query("SELECT SUM(rh.points_cost) as total_points FROM reward_history rh JOIN rewards r ON rh.reward_id = r.reward_id WHERE r.category = 'donation'");
     // Thêm ?: 0 để tránh lỗi nếu query trả về NULL
    $totalDonatedPoints = $stmtPoints->fetchColumn() ?: 0;

    // Ước tính số thành viên đã quyên góp
    $stmtMembers = $pdo->query("SELECT COUNT(DISTINCT rh.user_id) as total_members FROM reward_history rh JOIN rewards r ON rh.reward_id = r.reward_id WHERE r.category = 'donation'");
    $totalDonatingMembers = $stmtMembers->fetchColumn() ?: 0;

} catch (PDOException $e) {
     error_log("Dashboard Donation Metrics Error: " . $e->getMessage());
     // Gán giá trị mẫu nếu có lỗi
     $totalDonatedPoints = 1200000;
     $totalProjects = 56;
     $totalDonatingMembers = 12000;
}
// Hàm format số (thêm vào nếu chưa có)
function formatLargeNumber($number) {
    if ($number >= 1000000) { return round($number / 1000000, 1) . 'M'; }
    elseif ($number >= 1000) { return round($number / 1000, 0) . 'K'; }
    return $number;
}

?>

<section id="home" class="hero">
    <div class="container">
        <h1>Chào mừng trở lại, <?php echo htmlspecialchars($current_user['username']); ?>!</h1>
        <p>
            Bạn đang có <strong><?php echo number_format($current_user['points']); ?></strong> điểm.
            Hãy tiếp tục chia sẻ ý kiến của bạn để nhận thêm phần thưởng.
        </p>
        <a href="khaosat.php" class="cta-button">Thực hiện khảo sát ngay</a>
    </div>
</section>

<section class="dashboard-overview features" style="background-color: #f9f9f9; padding: 50px 0;">
    <div class="container">
         <div class="section-title" style="margin-bottom: 30px;">
            <h2>Tổng quan nhanh</h2>
        </div>
        <div class="features-grid" style="gap: 20px;">
            <div class="feature-card">
                 <div class="feature-icon">📊</div>
                 <h3>Khảo sát mới</h3>
                 <?php if (!empty($newSurveys)): ?>
                    <ul style="list-style: none; text-align: left; font-size: 0.9em;">
                        <?php foreach($newSurveys as $ns): ?>
                        <li style="margin-bottom: 5px;">
                            <a href="take_survey.php?id=<?php echo $ns['survey_id']; ?>"><?php echo htmlspecialchars($ns['title']); ?></a> (+<?php echo $ns['points_reward']; ?>đ)
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="khaosat.php" style="margin-top: 15px; display: inline-block;">Xem tất cả khảo sát...</a>
                 <?php else: ?>
                    <p>Hiện chưa có khảo sát mới.</p>
                 <?php endif; ?>
            </div>
             <div class="feature-card">
                 <div class="feature-icon">📜</div>
                 <h3>Hoạt động gần đây</h3>
                  <?php if (!empty($recentActivities)): ?>
                    <ul style="list-style: none; text-align: left; font-size: 0.9em;">
                        <?php foreach($recentActivities as $act): ?>
                        <li style="margin-bottom: 5px; color: <?php echo $act['points_change'] >= 0 ? 'green' : 'red'; ?>;">
                            [<?php echo $act['time_formatted']; ?>] <?php echo htmlspecialchars($act['activity_description']); ?> (<?php echo ($act['points_change'] >= 0 ? '+' : '') . $act['points_change']; ?>đ)
                        </li>
                        <?php endforeach; ?>
                    </ul>
                     <a href="trangcanhan.php" style="margin-top: 15px; display: inline-block;">Xem lịch sử đầy đủ...</a>
                 <?php else: ?>
                    <p>Chưa có hoạt động nào.</p>
                 <?php endif; ?>
            </div>
             <div class="feature-card">
                 <div class="feature-icon">🎁</div>
                 <h3>Đổi thưởng</h3>
                 <p>Sử dụng điểm của bạn để nhận voucher, thẻ cào, hoặc quyên góp.</p>
                 <a href="doithuong.php" class="cta-button" style="font-size: 1em; padding: 10px 20px; background-color: #2980b9; margin-top: 15px;">Xem phần thưởng</a>
            </div>
        </div>
    </div>
</section>

<section class="how-it-works fade-in">
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

<section class="impact-section fade-in">
    <div class="container">
        <div class="donation-impact">
             <h3>Tác động từ quyên góp</h3>
            <div class="impact-stats">
                <div class="impact-item">
                    <div class="impact-number"><?php echo formatLargeNumber($totalDonatedPoints); ?></div>
                    <p>Tổng điểm đã quyên góp</p>
                </div>
                <div class="impact-item">
                    <div class="impact-number"><?php echo $totalProjects; ?></div>
                    <p>Dự án được hỗ trợ</p>
                </div>
                <div class="impact-item">
                    <div class="impact-number"><?php echo formatLargeNumber($totalDonatingMembers); ?></div>
                    <p>Thành viên tham gia quyên góp</p>
                </div>
            </div>
            <a href="baocao.php" class="impact-report-link">Xem báo cáo tác động đầy đủ</a>
        </div>
    </div>
</section>

<section id="ct" class="ways-to-earn fade-in">
    <div class="container">
        <div class="section-title">
            <h2>Cách kiếm điểm</h2>
        </div>
        <div class="earn-methods-grid">
            <div class="earn-method-card">
                <div class="earn-method-icon">📝</div>
                <h3>Khảo sát</h3>
                <p><strong>100-500 điểm</strong> cho mỗi khảo sát hoàn thành, tùy thuộc vào độ dài và độ phức tạp</p>
            </div>
            <div class="earn-method-card">
                <div class="earn-method-icon">📱</div>
                <h3>Đăng nhập hàng ngày</h3>
                <p><strong>10 điểm</strong> mỗi ngày khi đăng nhập vào ứng dụng hoặc trang web</p>
            </div>
            <div class="earn-method-card">
                <div class="earn-method-icon">👥</div>
                <h3>Giới thiệu bạn bè</h3>
                <p><strong>1,000 điểm</strong> cho mỗi người bạn giới thiệu tham gia và hoàn thành khảo sát đầu tiên</p>
            </div>
            <div class="earn-method-card">
                <div class="earn-method-icon">🔍</div>
                <h3>Tìm kiếm hàng ngày</h3>
                <p><strong>10 điểm</strong> cho mỗi lượt tìm kiếm, tối đa 50 điểm mỗi ngày</p>
            </div>
            <div class="earn-method-card">
                <div class="earn-method-icon">🎮</div>
                <h3>Trò chơi nhỏ</h3>
                <p><strong>5-20 điểm</strong> cho mỗi lượt chơi các trò chơi nhỏ trên trang web</p>
            </div>
            <div class="earn-method-card featured-earn">
                <div class="earn-method-icon">⭐</div>
                <h3>Thử thách hàng tuần</h3>
                <p><strong>Tối đa 1,000 điểm</strong> khi hoàn thành các mục tiêu hàng tuần</p>
                <div class="earn-badge">Hot</div>
            </div>
        </div>
        <div class="level-benefits">
            <h3>Đặc quyền theo cấp bậc thành viên</h3>
            <div class="level-table">
                <table> <?php // Bọc trong table để CSS hoạt động đúng ?>
                    <thead>
                        <tr class="level-header">
                            <th class="level-cell">Cấp bậc</th>
                            <th class="level-cell">Yêu cầu</th>
                            <th class="level-cell">Đặc quyền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="level-row">
                            <td class="level-cell level-name bronze">Bronze</td>
                            <td class="level-cell">Mới gia nhập</td>
                            <td class="level-cell">
                                <ul><li>Truy cập khảo sát cơ bản</li><li>Rút điểm cơ bản</li></ul>
                            </td>
                        </tr>
                        <tr class="level-row">
                            <td class="level-cell level-name silver">Silver</td>
                            <td class="level-cell">5,000 điểm/năm</td>
                            <td class="level-cell">
                                <ul><li>Tất cả đặc quyền Đồng</li><li>+10% điểm thưởng</li><li>Ưu tiên truy cập khảo sát mới</li></ul>
                            </td>
                        </tr>
                        <tr class="level-row">
                            <td class="level-cell level-name gold">Gold</td>
                            <td class="level-cell">10,000 điểm/năm</td>
                            <td class="level-cell">
                                <ul><li>Tất cả đặc quyền Bạc</li><li>+25% điểm thưởng</li><li>Khảo sát độc quyền</li><li>Ưu đãi đổi thưởng đặc biệt</li></ul>
                            </td>
                        </tr>
                     </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<section class="testimonials fade-in">
    <div class="container">
        <div class="section-title">
            <h2>Ý kiến của thành viên</h2>
        </div>
        <div class="testimonial-grid">
            <div class="testimonial-card">
                <div class="testimonial-text">
                    "Tôi đã kiếm được hơn 2 triệu đồng trong 3 tháng qua chỉ bằng cách dành 15 phút mỗi ngày để làm khảo sát. SurveyForGood là cách tuyệt vời để kiếm thêm thu nhập!"
                </div>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <img src="https://i.pravatar.cc/50?img=4" alt="người dùng" />
                    </div>
                    <div class="author-info">
                        <h4>Nguyễn Văn A</h4>
                        <p>Thành viên từ 2023</p>
                    </div>
                </div>
            </div>
             <div class="testimonial-card">
                <div class="testimonial-text">
                    "Điều tôi thích nhất về SurveyForGood không chỉ là khả năng kiếm thêm thu nhập mà còn là lựa chọn quyên góp điểm cho các tổ chức từ thiện. Tôi đã quyên góp điểm thưởng để hỗ trợ dự án giáo dục ở vùng cao và cảm thấy rất ý nghĩa."
                </div>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <img src="https://i.pravatar.cc/50?img=5" alt="người dùng" />
                    </div>
                    <div class="author-info">
                        <h4>Trần Thị B</h4>
                        <p>Thành viên từ 2022</p>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-text">
                    "Ban đầu tôi khá hoài nghi, nhưng sau khi nhận được khoản thanh toán đầu tiên, tôi đã trở thành người hâm mộ cuồng nhiệt của SurveyForGood. Dịch vụ khách hàng tuyệt vời!"
                </div>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <img src="https://i.pravatar.cc/50?img=6" alt="người dùng" />
                    </div>
                    <div class="author-info">
                        <h4>Lê Văn C</h4>
                        <p>Thành viên từ 2024</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="faq" class="faq fade-in">
     <div class="container">
        <div class="section-title">
            <h2>Câu hỏi thường gặp</h2>
        </div>
        <div class="faq-list"> <?php // Thêm class bao ngoài ?>
            <div class="faq-item">
                <div class="faq-question">
                    SurveyForGood có thực sự miễn phí không?
                    <span class="arrow">+</span>
                </div>
                <div class="faq-answer">
                    Đúng vậy! SurveyForGood hoàn toàn miễn phí. Bạn không bao giờ phải trả tiền để đăng ký hoặc tham gia khảo sát. Thực tế, chúng tôi trả tiền cho BẠN khi bạn hoàn thành khảo sát.
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    Tôi có thể kiếm được bao nhiêu tiền?
                    <span class="arrow">+</span>
                </div>
                <div class="faq-answer">
                    Thu nhập của bạn phụ thuộc vào số lượng khảo sát bạn hoàn thành và giá trị của mỗi khảo sát. Các thành viên tích cực có thể kiếm được từ vài trăm ngàn đến vài triệu đồng mỗi tháng.
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    Làm thế nào để rút tiền hoặc quyên góp?
                    <span class="arrow">+</span>
                </div>
                <div class="faq-answer">
                    Bạn có thể quy đổi điểm thưởng theo hai cách: rút tiền thông qua các phương thức thanh toán phổ biến như ngân hàng điện tử, ví điện tử, thẻ quà tặng, hoặc chọn quyên góp cho các tổ chức từ thiện đối tác của chúng tôi. Số điểm tối thiểu để rút hoặc quyên góp là 10.000 điểm (tương đương 100.000 đồng).
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    Làm thế nào kiếm thêm điểm nhanh hơn?
                    <span class="arrow">+</span>
                </div>
                <div class="faq-answer">
                    Có nhiều cách để kiếm điểm nhanh hơn: hoàn thành hồ sơ khảo sát đầy đủ để nhận khảo sát phù hợp hơn, tham gia khảo sát hàng ngày, mời bạn bè tham gia (nhận 1,000 điểm cho mỗi người), đăng nhập hàng ngày (<?php echo CHECKIN_POINTS ?? 10; ?> điểm/ngày), và tham gia vào các thử thách điểm thưởng định kỳ.
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    Điểm thưởng có hết hạn không?
                    <span class="arrow">+</span>
                </div>
                <div class="faq-answer">
                    Điểm thưởng của bạn có hiệu lực trong 18 tháng kể từ thời điểm bạn kiếm được. Chúng tôi sẽ thông báo cho bạn trước khi điểm sắp hết hạn để bạn có thời gian sử dụng.
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once('includes/footer.php'); ?>