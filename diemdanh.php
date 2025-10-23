<?php
$page_title = "Điểm danh hàng ngày";
require_once('includes/header.php');
if (!$current_user) {
    header('Location: login.php?redirect=diemdanh.php');
    exit;
}

$userId = $current_user['id'];
$pointsPerCheckin = 10; // Số điểm thưởng mỗi lần điểm danh

// Lấy tháng và năm hiện tại hoặc từ request (để xem lịch tháng khác)
$year = isset($_GET['y']) ? (int)$_GET['y'] : date('Y');
$month = isset($_GET['m']) ? (int)$_GET['m'] : date('n'); // Tháng từ 1-12

// --- Lấy lịch sử điểm danh của tháng đang xem ---
$startDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
$endDate = date('Y-m-t', strtotime($startDate)); // Ngày cuối cùng của tháng

$checkinStmt = $pdo->prepare("SELECT checkin_date FROM checkin_history WHERE user_id = ? AND checkin_date BETWEEN ? AND ?");
$checkinStmt->execute([$userId, $startDate, $endDate]);
$checkedInDates = $checkinStmt->fetchAll(PDO::FETCH_COLUMN, 0); // Lấy mảng các ngày đã điểm danh (YYYY-MM-DD)

// --- Kiểm tra xem hôm nay đã điểm danh chưa ---
$todayStr = date('Y-m-d');
$alreadyCheckedInToday = in_array($todayStr, $checkedInDates);

?>
<link rel="stylesheet" href="css/diemdanh.css"> <main class="checkin-page">
    <div class="container">
        <div class="section-title">
            <h2>Điểm danh hàng ngày</h2>
            <p>Hãy điểm danh mỗi ngày để nhận điểm thưởng!</p>
            <?php if (isset($_SESSION['checkin_message'])): ?>
                <p class="checkin-message <?php echo isset($_SESSION['checkin_error']) ? 'error' : 'success'; ?>">
                    <?php echo $_SESSION['checkin_message']; ?>
                </p>
                <?php unset($_SESSION['checkin_message'], $_SESSION['checkin_error']); ?>
            <?php endif; ?>
        </div>

        <div class="checkin-container">
            <div class="calendar-header">
                 <?php
                    $prevMonth = $month - 1; $prevYear = $year;
                    if ($prevMonth == 0) { $prevMonth = 12; $prevYear--; }
                    $nextMonth = $month + 1; $nextYear = $year;
                    if ($nextMonth == 13) { $nextMonth = 1; $nextYear++; }
                 ?>
                 <a href="?y=<?php echo $prevYear; ?>&m=<?php echo $prevMonth; ?>" class="month-nav"><i class="fas fa-chevron-left"></i></a>
                 <h3 id="current-month-year">Tháng <?php echo $month; ?>, <?php echo $year; ?></h3>
                 <a href="?y=<?php echo $nextYear; ?>&m=<?php echo $nextMonth; ?>" class="month-nav"><i class="fas fa-chevron-right"></i></a>
            </div>
            <div class="calendar-weekdays">
                <div>CN</div><div>T2</div><div>T3</div><div>T4</div><div>T5</div><div>T6</div><div>T7</div>
            </div>
            <div class="calendar-grid" id="calendar-grid">
                <?php
                    $firstDayOfMonth = date('w', strtotime($startDate)); // 0 (CN) đến 6 (T7)
                    $daysInMonth = date('t', strtotime($startDate)); // Số ngày trong tháng

                    // In các ô trống đầu tháng
                    for ($i = 0; $i < $firstDayOfMonth; $i++) {
                        echo '<div class="day-cell"></div>';
                    }

                    // In các ngày trong tháng
                    $currentDay = date('j');
                    $currentMonthNow = date('n');
                    $currentYearNow = date('Y');

                    for ($day = 1; $day <= $daysInMonth; $day++) {
                        $cellDateStr = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($day, 2, '0', STR_PAD_LEFT);
                        $classes = 'day-cell valid-day';
                        if ($day == $currentDay && $month == $currentMonthNow && $year == $currentYearNow) {
                            $classes .= ' today';
                        }
                        if (in_array($cellDateStr, $checkedInDates)) {
                            $classes .= ' checked-in';
                        }
                        echo "<div class=\"$classes\">";
                        echo "<span class=\"day-number\">$day</span>";
                        echo "</div>";
                    }

                     // In các ô trống cuối tháng (nếu cần để đủ 6 dòng * 7 cột)
                     $totalCells = $firstDayOfMonth + $daysInMonth;
                     $remainingCells = (7 - ($totalCells % 7)) % 7;
                     for ($i = 0; $i < $remainingCells; $i++) {
                         echo '<div class="day-cell"></div>';
                     }
                ?>
            </div>
            <div class="checkin-action">
                <p>Phần thưởng điểm danh hôm nay: <strong>+<?php echo $pointsPerCheckin; ?> điểm</strong></p>
                <form action="actions/handle_checkin.php" method="POST">
                    <button type="submit" class="cta-button" <?php echo $alreadyCheckedInToday ? 'disabled' : ''; ?>>
                        <?php echo $alreadyCheckedInToday ? 'Đã điểm danh hôm nay' : 'Điểm danh ngay'; ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>

<?php require_once('includes/footer.php'); ?>