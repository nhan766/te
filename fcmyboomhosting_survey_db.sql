-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 23, 2025 at 01:58 AM
-- Server version: 10.6.19-MariaDB-cll-lve-log
-- PHP Version: 8.2.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fcmyboomhosting_survey_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `checkin_history`
--

CREATE TABLE `checkin_history` (
  `checkin_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `checkin_date` date NOT NULL,
  `points_earned` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `checkin_history`
--

INSERT INTO `checkin_history` (`checkin_id`, `user_id`, `checkin_date`, `points_earned`) VALUES
(1, 2, '2025-10-23', 10);

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `client_id` int(11) NOT NULL,
  `company_name` varchar(100) NOT NULL,
  `contact_email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`client_id`, `company_name`, `contact_email`, `password_hash`, `is_active`, `created_at`) VALUES
(1, 'Sample Company A', 'client_a@example.com', '$2y$10$H6mhtCs2kgs802uzKBLTZ.ESP36k6NpmoAbDKdU2TTsnvYpGlNBue', 1, '2025-10-22 18:46:22');

-- --------------------------------------------------------

--
-- Table structure for table `options`
--

CREATE TABLE `options` (
  `option_id` int(11) NOT NULL,
  `question_id` int(11) DEFAULT NULL,
  `option_text` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `options`
--

INSERT INTO `options` (`option_id`, `question_id`, `option_text`) VALUES
(1, 1, 'Netflix'),
(2, 1, 'FPT Play'),
(3, 1, 'VieON'),
(4, 1, 'Không dùng dịch vụ nào'),
(5, 2, 'Hàng ngày'),
(6, 2, 'Vài lần một tuần'),
(7, 2, 'Vài lần một tháng'),
(8, 2, 'Hiếm khi (vài lần một năm)'),
(9, 3, 'Quần áo, giày dép, phụ kiện thời trang'),
(10, 3, 'Thiết bị điện tử, công nghệ'),
(11, 3, 'Mỹ phẩm, sản phẩm chăm sóc cá nhân'),
(12, 3, 'Sách, văn phòng phẩm'),
(13, 3, 'Đồ gia dụng'),
(14, 3, 'Thực phẩm, đồ uống'),
(15, 3, 'Khác'),
(16, 5, 'Android'),
(17, 5, 'iOS (iPhone)'),
(18, 5, 'Hệ điều hành khác'),
(19, 6, 'Trong vòng 6 tháng tới'),
(20, 6, 'Trong vòng 1 năm tới'),
(21, 6, 'Trong 1-2 năm tới'),
(22, 6, 'Hơn 2 năm nữa / Chưa có kế hoạch'),
(23, 7, 'Chất lượng Camera'),
(24, 7, 'Hiệu năng (Tốc độ xử lý, chơi game)'),
(25, 7, 'Thời lượng Pin'),
(26, 7, 'Thiết kế và Chất liệu'),
(27, 7, 'Giá cả'),
(28, 7, 'Thương hiệu'),
(29, 8, 'Chắc chắn có'),
(30, 8, 'Có thể'),
(31, 8, 'Không'),
(32, 9, 'Nghỉ dưỡng biển'),
(33, 9, 'Khám phá núi rừng, thiên nhiên'),
(34, 9, 'Tham quan thành phố, văn hóa'),
(35, 9, 'Du lịch ẩm thực'),
(36, 9, 'Du lịch mạo hiểm'),
(37, 11, 'Hàng ngày'),
(38, 11, '3-4 lần/tuần'),
(39, 11, '1-2 lần/tuần'),
(40, 11, 'Hiếm khi hoặc không bao giờ'),
(41, 12, 'Chạy bộ/Đi bộ'),
(42, 12, 'Gym/Tạ'),
(43, 12, 'Yoga/Pilates'),
(44, 12, 'Bơi lội'),
(45, 12, 'Đạp xe'),
(46, 12, 'Các môn thể thao đồng đội (Bóng đá, bóng chuyền...)'),
(47, 13, 'Hàng tuần'),
(48, 13, 'Vài lần một tháng'),
(49, 13, 'Vài lần một năm'),
(50, 13, 'Không bao giờ'),
(51, 15, 'Ưu tiên làm tại văn phòng'),
(52, 15, 'Ưu tiên làm từ xa (Remote)'),
(53, 15, 'Linh hoạt (Hybrid)'),
(54, 16, 'Lương thưởng và phúc lợi'),
(55, 16, 'Cơ hội học hỏi và phát triển'),
(56, 16, 'Môi trường làm việc (đồng nghiệp, văn hóa công ty)'),
(57, 16, 'Sự cân bằng giữa công việc và cuộc sống'),
(58, 16, 'Tính chất công việc thú vị, thử thách'),
(59, 17, 'Pop'),
(60, 17, 'Rock'),
(61, 17, 'Hip Hop / Rap'),
(62, 17, 'Nhạc điện tử (EDM)'),
(63, 17, 'Nhạc trữ tình / Ballad'),
(64, 17, 'Nhạc cổ điển'),
(65, 17, 'Khác'),
(66, 18, 'Spotify'),
(67, 18, 'Apple Music'),
(68, 18, 'YouTube Music / YouTube'),
(69, 18, 'Zing MP3'),
(70, 18, 'NhacCuaTui'),
(71, 18, 'Nền tảng khác / Nghe offline'),
(72, 20, 'Cửa hàng thương hiệu lớn (Uniqlo, Zara, H&M...)'),
(73, 20, 'Cửa hàng thời trang địa phương (Local brand)'),
(74, 20, 'Sàn thương mại điện tử (Shopee, Lazada...)'),
(75, 20, 'Mạng xã hội (Facebook, Instagram...)'),
(76, 20, 'Chợ / Cửa hàng nhỏ lẻ'),
(77, 21, 'Facebook'),
(78, 21, 'Instagram'),
(79, 21, 'TikTok'),
(80, 21, 'Zalo'),
(81, 21, 'Twitter (X)'),
(82, 21, 'LinkedIn'),
(83, 22, 'Kết nối với bạn bè, gia đình'),
(84, 22, 'Cập nhật tin tức'),
(85, 22, 'Giải trí (Xem video, memes...)'),
(86, 22, 'Công việc / Học tập'),
(87, 22, 'Mua sắm / Tìm kiếm sản phẩm'),
(88, 24, 'Rất hài lòng'),
(89, 24, 'Hài lòng'),
(90, 24, 'Bình thường'),
(91, 24, 'Không hài lòng'),
(92, 24, 'Rất không hài lòng');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `question_id` int(11) NOT NULL,
  `survey_id` int(11) DEFAULT NULL,
  `question_text` text NOT NULL,
  `question_type` enum('single_choice','multiple_choice','text_input') NOT NULL,
  `is_required` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`question_id`, `survey_id`, `question_text`, `question_type`, `is_required`) VALUES
(1, 1, 'Bạn đang sử dụng dịch vụ xem phim trực tuyến nào?', 'multiple_choice', 1),
(2, 2, 'Bạn mua sắm trực tuyến bao lâu một lần?', 'single_choice', 1),
(3, 2, 'Bạn thường mua những mặt hàng nào trực tuyến? (Chọn tất cả đáp án đúng)', 'multiple_choice', 1),
(4, 2, 'Yếu tố nào quan trọng nhất đối với bạn khi mua sắm trực tuyến?', 'text_input', 0),
(5, 3, 'Bạn đang sử dụng hệ điều hành điện thoại nào?', 'single_choice', 1),
(6, 3, 'Bạn dự định nâng cấp điện thoại trong bao lâu nữa?', 'single_choice', 1),
(7, 3, 'Tính năng nào bạn quan tâm nhất khi chọn mua điện thoại mới?', 'multiple_choice', 1),
(8, 4, 'Bạn có dự định đi du lịch trong vòng 1 năm tới không?', 'single_choice', 1),
(9, 4, 'Loại hình du lịch nào bạn yêu thích? (Chọn tối đa 3)', 'multiple_choice', 1),
(10, 4, 'Nếu có dự định đi, bạn muốn đến đâu nhất?', 'text_input', 0),
(11, 5, 'Bạn có thường xuyên tập thể dục không?', 'single_choice', 1),
(12, 5, 'Bạn thường tập loại hình thể dục nào?', 'multiple_choice', 0),
(13, 6, 'Bạn ăn đồ ăn nhanh bao lâu một lần?', 'single_choice', 1),
(14, 6, 'Thương hiệu đồ ăn nhanh yêu thích của bạn là gì và tại sao?', 'text_input', 0),
(15, 7, 'Bạn ưu tiên làm việc tại văn phòng hay làm việc từ xa?', 'single_choice', 1),
(16, 7, 'Yếu tố nào quan trọng nhất đối với bạn trong công việc?', 'multiple_choice', 1),
(17, 8, 'Bạn thường nghe thể loại nhạc nào?', 'multiple_choice', 1),
(18, 8, 'Bạn sử dụng nền tảng nghe nhạc nào chủ yếu?', 'single_choice', 1),
(19, 9, 'Theo bạn, xu hướng thời trang nào nổi bật nhất trong năm nay?', 'text_input', 0),
(20, 9, 'Bạn thường mua sắm quần áo ở đâu?', 'single_choice', 1),
(21, 10, 'Bạn đang sử dụng mạng xã hội nào thường xuyên?', 'multiple_choice', 1),
(22, 10, 'Mục đích chính bạn sử dụng mạng xã hội là gì?', 'single_choice', 1),
(23, 11, 'Bạn có đề xuất gì để cải thiện trải nghiệm trên SurveyForGood không?', 'text_input', 0),
(24, 11, 'Bạn đánh giá mức độ hài lòng chung với SurveyForGood như thế nào?', 'single_choice', 1);

-- --------------------------------------------------------

--
-- Table structure for table `rewards`
--

CREATE TABLE `rewards` (
  `reward_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `points_cost` int(11) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rewards`
--

INSERT INTO `rewards` (`reward_id`, `title`, `description`, `points_cost`, `image_url`, `category`, `stock`, `is_active`) VALUES
(1, 'Thẻ quà tặng Shopee 50K', 'Voucher giảm giá 50.000đ cho đơn hàng trên Shopee.', 5000, 'image/cung-cap-da-dang-hinh-thuc-giam-gia_2d771983048e4dd8926682683ce22c00_grande.webp', 'giftcard', 100, 1),
(2, 'Thẻ cào Vietnamobile 20K', 'Thẻ nạp tiền điện thoại mạng Vietnamobile mệnh giá 20.000đ.', 2000, 'image/Hinh-The-Vietnamobile-20k-nap-online.png', 'mobilecard', NULL, 1),
(3, 'Quyên góp cho dự án A', 'Đóng góp 10.000đ cho dự án trồng cây.', 1000, 'image/1908_tieuchimoitruong.jpg', 'donation', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `reward_history`
--

CREATE TABLE `reward_history` (
  `history_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `reward_id` int(11) DEFAULT NULL,
  `reward_title` varchar(255) DEFAULT NULL,
  `points_cost` int(11) DEFAULT NULL,
  `voucher_code` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Đã nhận',
  `redeem_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `surveys`
--

CREATE TABLE `surveys` (
  `survey_id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `points_reward` int(11) NOT NULL DEFAULT 10,
  `category` varchar(50) DEFAULT NULL,
  `status` enum('draft','pending_approval','published','rejected','closed') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `published_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `surveys`
--

INSERT INTO `surveys` (`survey_id`, `client_id`, `title`, `description`, `points_reward`, `category`, `status`, `created_at`, `published_at`, `closed_at`) VALUES
(1, 1, 'Khảo sát về thói quen xem phim', 'Chia sẻ về cách bạn xem phim trực tuyến để giúp chúng tôi cải thiện dịch vụ.', 150, 'entertainment', 'published', '2025-10-22 18:47:12', '2025-10-22 18:47:12', NULL),
(2, 1, 'Thói quen mua sắm trực tuyến của bạn', 'Chia sẻ về cách bạn mua sắm online để giúp các nhà bán lẻ hiểu rõ hơn về nhu cầu khách hàng.', 120, 'shopping', 'published', '2025-10-23 01:48:45', '2025-10-23 01:48:45', NULL),
(3, 1, 'Sở thích sử dụng Điện thoại Thông minh', 'Khảo sát nhanh về cách bạn sử dụng smartphone hàng ngày.', 200, 'technology', 'published', '2025-10-23 01:48:45', '2025-10-23 01:48:45', NULL),
(4, 1, 'Kế hoạch du lịch của bạn', 'Chia sẻ về dự định du lịch sắp tới của bạn.', 180, 'travel', 'published', '2025-10-23 01:48:45', '2025-10-23 01:48:45', NULL),
(5, 1, 'Thói quen tập thể dục và sức khỏe', 'Bạn quan tâm đến sức khỏe và việc tập luyện như thế nào?', 100, 'health', 'published', '2025-10-23 01:50:18', '2025-10-23 01:50:18', NULL),
(6, 1, 'Sở thích về đồ ăn nhanh', 'Chia sẻ ý kiến của bạn về các thương hiệu đồ ăn nhanh phổ biến.', 90, 'food', 'published', '2025-10-23 01:50:18', '2025-10-23 01:50:18', NULL),
(7, 1, 'Môi trường làm việc lý tưởng', 'Bạn mong muốn gì ở một môi trường làm việc tốt?', 160, 'work', 'published', '2025-10-23 01:50:18', '2025-10-23 01:50:18', NULL),
(8, 1, 'Thói quen nghe nhạc của bạn', 'Chia sẻ về gu âm nhạc và cách bạn nghe nhạc.', 110, 'entertainment', 'published', '2025-10-23 01:50:18', '2025-10-23 01:50:18', NULL),
(9, 1, 'Xu hướng thời trang bạn quan tâm', 'Ý kiến của bạn về các xu hướng thời trang gần đây.', 130, 'shopping', 'published', '2025-10-23 01:50:18', '2025-10-23 01:50:18', NULL),
(10, 1, 'Việc sử dụng mạng xã hội', 'Chia sẻ về cách bạn tương tác trên các nền tảng mạng xã hội.', 140, 'technology', 'published', '2025-10-23 01:50:18', '2025-10-23 01:50:18', NULL),
(11, 1, 'Góp ý về trang web SurveyForGood', 'Chúng tôi muốn lắng nghe ý kiến của bạn để cải thiện trang web tốt hơn.', 50, 'feedback', 'published', '2025-10-23 01:50:18', '2025-10-23 01:50:18', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `points` int(11) DEFAULT 0,
  `level` varchar(20) DEFAULT 'bronze',
  `join_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `is_admin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `phone`, `birthday`, `gender`, `points`, `level`, `join_date`, `is_active`, `is_admin`) VALUES
(1, 'admin', 'admin@example.com', '$2y$10$4TR26r2acSMsSz3W1O7iouN4T6d.xxTukbtCKqi7fT.yBlRMhl1rW', 'Admin User', NULL, NULL, NULL, 0, 'gold', '2025-10-22 18:41:58', 1, 1),
(2, 'nhan', 'huynhthanhnhan762006@gmail.com', '$2y$10$W9SDWh94AHNO0QRcdvcys.rsJwTcDSJKCci7ibqo0EX23H0Ml/Az6', NULL, NULL, NULL, 'male', 360, 'bronze', '2025-10-22 20:58:14', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_activities`
--

CREATE TABLE `user_activities` (
  `activity_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `activity_description` varchar(255) DEFAULT NULL,
  `points_change` int(11) DEFAULT 0,
  `activity_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_activities`
--

INSERT INTO `user_activities` (`activity_id`, `user_id`, `activity_description`, `points_change`, `activity_time`) VALUES
(1, 2, 'Hoàn thành khảo sát: \"Khảo sát về thói quen xem phim\"', 150, '2025-10-22 21:07:41'),
(2, 2, 'Điểm danh hàng ngày', 10, '2025-10-23 00:47:01'),
(3, 2, 'Hoàn thành khảo sát: \"Sở thích sử dụng Điện thoại Thông minh\"', 200, '2025-10-23 01:50:45');

-- --------------------------------------------------------

--
-- Table structure for table `user_completed_surveys`
--

CREATE TABLE `user_completed_surveys` (
  `completion_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `survey_id` int(11) DEFAULT NULL,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `points_earned` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_completed_surveys`
--

INSERT INTO `user_completed_surveys` (`completion_id`, `user_id`, `survey_id`, `completed_at`, `points_earned`) VALUES
(1, 2, 1, '2025-10-22 21:07:41', 0),
(2, 2, 3, '2025-10-23 01:50:45', 200);

-- --------------------------------------------------------

--
-- Table structure for table `user_responses`
--

CREATE TABLE `user_responses` (
  `response_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `survey_id` int(11) DEFAULT NULL,
  `question_id` int(11) DEFAULT NULL,
  `selected_option_id` int(11) DEFAULT NULL,
  `answer_text` text DEFAULT NULL,
  `responded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_responses`
--

INSERT INTO `user_responses` (`response_id`, `user_id`, `survey_id`, `question_id`, `selected_option_id`, `answer_text`, `responded_at`) VALUES
(1, 2, NULL, 1, 2, NULL, '2025-10-22 21:07:41'),
(2, 2, 3, 5, 16, NULL, '2025-10-23 01:50:45'),
(3, 2, 3, 6, 21, NULL, '2025-10-23 01:50:45'),
(4, 2, 3, 7, 25, NULL, '2025-10-23 01:50:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `checkin_history`
--
ALTER TABLE `checkin_history`
  ADD PRIMARY KEY (`checkin_id`),
  ADD UNIQUE KEY `user_date_unique` (`user_id`,`checkin_date`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`client_id`),
  ADD UNIQUE KEY `contact_email` (`contact_email`);

--
-- Indexes for table `options`
--
ALTER TABLE `options`
  ADD PRIMARY KEY (`option_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`email`,`token`),
  ADD KEY `token` (`token`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `survey_id` (`survey_id`);

--
-- Indexes for table `rewards`
--
ALTER TABLE `rewards`
  ADD PRIMARY KEY (`reward_id`);

--
-- Indexes for table `reward_history`
--
ALTER TABLE `reward_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `reward_id` (`reward_id`);

--
-- Indexes for table `surveys`
--
ALTER TABLE `surveys`
  ADD PRIMARY KEY (`survey_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_activities`
--
ALTER TABLE `user_activities`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_completed_surveys`
--
ALTER TABLE `user_completed_surveys`
  ADD PRIMARY KEY (`completion_id`),
  ADD UNIQUE KEY `user_survey_unique` (`user_id`,`survey_id`),
  ADD KEY `survey_id` (`survey_id`);

--
-- Indexes for table `user_responses`
--
ALTER TABLE `user_responses`
  ADD PRIMARY KEY (`response_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `survey_id` (`survey_id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `selected_option_id` (`selected_option_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `checkin_history`
--
ALTER TABLE `checkin_history`
  MODIFY `checkin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `options`
--
ALTER TABLE `options`
  MODIFY `option_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `rewards`
--
ALTER TABLE `rewards`
  MODIFY `reward_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reward_history`
--
ALTER TABLE `reward_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `surveys`
--
ALTER TABLE `surveys`
  MODIFY `survey_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_activities`
--
ALTER TABLE `user_activities`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_completed_surveys`
--
ALTER TABLE `user_completed_surveys`
  MODIFY `completion_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_responses`
--
ALTER TABLE `user_responses`
  MODIFY `response_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `checkin_history`
--
ALTER TABLE `checkin_history`
  ADD CONSTRAINT `checkin_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `options`
--
ALTER TABLE `options`
  ADD CONSTRAINT `options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`survey_id`) ON DELETE CASCADE;

--
-- Constraints for table `reward_history`
--
ALTER TABLE `reward_history`
  ADD CONSTRAINT `reward_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reward_history_ibfk_2` FOREIGN KEY (`reward_id`) REFERENCES `rewards` (`reward_id`) ON DELETE SET NULL;

--
-- Constraints for table `surveys`
--
ALTER TABLE `surveys`
  ADD CONSTRAINT `surveys_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE SET NULL;

--
-- Constraints for table `user_activities`
--
ALTER TABLE `user_activities`
  ADD CONSTRAINT `user_activities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_completed_surveys`
--
ALTER TABLE `user_completed_surveys`
  ADD CONSTRAINT `user_completed_surveys_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_completed_surveys_ibfk_2` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`survey_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_responses`
--
ALTER TABLE `user_responses`
  ADD CONSTRAINT `user_responses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_responses_ibfk_2` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`survey_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_responses_ibfk_3` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_responses_ibfk_4` FOREIGN KEY (`selected_option_id`) REFERENCES `options` (`option_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
