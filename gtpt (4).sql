-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1:3307
-- Thời gian đã tạo: Th5 14, 2026 lúc 04:28 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `gtpt`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `ID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`ID`, `Name`) VALUES
(1, 'Phòng trọ'),
(2, 'Chung cư mini'),
(3, 'Ở ghép'),
(4, 'Nhà nguyên căn');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `comments`
--

CREATE TABLE `comments` (
  `ID` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `motel_id` int(10) UNSIGNED DEFAULT NULL,
  `content` text NOT NULL,
  `rating` int(1) DEFAULT 5,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `react_like` int(11) DEFAULT 0,
  `react_love` int(11) DEFAULT 0,
  `react_haha` int(11) DEFAULT 0,
  `react_angry` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `comments`
--

INSERT INTO `comments` (`ID`, `user_id`, `motel_id`, `content`, `rating`, `created_at`, `react_like`, `react_love`, `react_haha`, `react_angry`) VALUES
(1, 13, 1, 'k thích màu hồng ', 4, '2026-05-02 16:26:08', 0, 0, 0, 0),
(2, 13, 1, 'Trọ xinh,tiện, thoáng', 5, '2026-05-02 16:27:32', 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `districts`
--

CREATE TABLE `districts` (
  `ID` int(10) UNSIGNED NOT NULL,
  `Name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `districts`
--

INSERT INTO `districts` (`ID`, `Name`) VALUES
(1, 'Bến Thủy'),
(2, 'Trung Đô'),
(3, 'Trường Thi'),
(4, 'Lê Duẩn'),
(5, 'Bạch Liêu');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `favorites`
--

CREATE TABLE `favorites` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `motel_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `feedbacks`
--

CREATE TABLE `feedbacks` (
  `ID` int(11) NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `Title` varchar(255) DEFAULT NULL,
  `Content` text DEFAULT NULL,
  `AutoReply` text DEFAULT NULL,
  `AdminReply` text DEFAULT NULL,
  `Status` varchar(50) DEFAULT 'pending',
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `IsRead` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `feedbacks`
--

INSERT INTO `feedbacks` (`ID`, `UserID`, `Title`, `Content`, `AutoReply`, `AdminReply`, `Status`, `CreatedAt`, `IsRead`) VALUES
(4, 13, 'ảnh minh hoạ', 'ảnh ít quá không rõ', 'Hệ thống đã nhận phản hồi của bạn. Admin sẽ phản hồi sớm nhất có thể.', 'sẽ có nhiều ảnh hơn và rõ hơn', 'done', '2026-05-12 17:23:40', 1),
(5, 13, 'thử ', 'alo', 'Hệ thống đã nhận phản hồi của bạn. Admin sẽ phản hồi sớm nhất có thể.', 'oke', 'done', '2026-05-12 17:27:35', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `messages`
--

CREATE TABLE `messages` (
  `ID` int(10) UNSIGNED NOT NULL,
  `sender_id` int(10) UNSIGNED DEFAULT NULL,
  `receiver_id` int(10) UNSIGNED DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `motel`
--

CREATE TABLE `motel` (
  `ID` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` int(11) NOT NULL,
  `area` int(11) DEFAULT NULL,
  `count_view` int(11) DEFAULT 0,
  `address` varchar(255) DEFAULT NULL,
  `images` text DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `district_id` int(10) UNSIGNED DEFAULT NULL,
  `utilities` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `phone` varchar(255) DEFAULT NULL,
  `approve` int(11) DEFAULT 0,
  `is_rented` tinyint(1) NOT NULL DEFAULT 0,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `motel`
--

INSERT INTO `motel` (`ID`, `title`, `description`, `price`, `area`, `count_view`, `address`, `images`, `user_id`, `category_id`, `district_id`, `utilities`, `created_at`, `phone`, `approve`, `latitude`, `longitude`) VALUES
(1, 'Phòng trọ tầng 2 - Sát cổng ĐH Vinh', 'Phòng thoáng mát, giờ giấc tự do, có chỗ để xe rộng rãi.', 1200000, 15, 181, 'Số 2 Bạch Liêu', 'phongtro1.jpg,phongtro2.webp', 2, 1, 5, 'Wifi, Nóng lạnh', '2026-04-25 16:50:18', '0987654321', 1, NULL, NULL),
(2, 'Chung cư Vinaconex full nội thất', 'Căn hộ cao cấp view ĐH Vinh, đầy đủ giường tủ, tủ lạnh, máy giặt.', 3500000, 35, 326, '18 Nguyễn Du', 'Phongtro3.jpg\r\n', 3, 2, 4, 'Điều hòa, Tủ lạnh, Máy giặt', '2026-04-25 16:50:18', '0911223344', 1, NULL, NULL),
(3, 'Nhà cấp 4 rộng rãi khối Trung Đô', 'Nhà nguyên căn 2 phòng ngủ, có sân vườn phù hợp nhóm 4 người.', 2500000, 50, 49, 'Ngõ 15 Trần Phú', 'phongtro4.jpg', 2, 4, 2, 'Sân vườn, Khép kín', '2026-04-25 16:50:18', '0987654321', 1, NULL, NULL),
(4, 'Tìm bạn nữ ở ghép - Ký túc xá Bến Thủy', 'Cần 1 bạn nữ ở cùng, share tiền phòng rẻ, điện nước giá dân.', 800000, 12, 92, 'Đường Phong Định Cảng', 'phongtro5.jpg', 4, 3, 1, 'Wifi, Giá rẻ', '2026-04-25 16:50:18', '0944556677', 1, NULL, NULL),
(5, 'Phòng trọ cao cấp đường Trường Thi', 'Phòng mới xây, gác lửng hiện đại, ngay trung tâm thành phố.', 2000000, 20, 15, 'Số 102 Trường Thi', 'phongtro6.webp\r\n', 3, 1, 3, 'Điều hòa, Gác lửng', '2026-04-25 16:50:18', '0911223344', 0, NULL, NULL),
(7, 'Phòng Trọ Tầng 3', 'Đầy đủ tiện nghi', 2000000, 32, 0, '30 Nguyễn DÌnh Cổn', 'phongtro7.jpg', 1, 1, 4, 'Wifi, Điều hoà,', '2026-05-08 03:55:06', '0736452883', 1, 18.66051716657638, 105.69257497787477),
(8, 'Chung Cư Mini Tầng 2', 'ok', 2500000, 45, 0, '68 Nguyễn Du', 'phongtro8.jpg', 5, 2, 1, 'Wifi, Điều hoà ,Máy giặt', '2026-05-08 04:11:07', '063463272', 1, 18.653279652013723, 105.69944679737092);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `likes` int(11) DEFAULT 0,
  `parent_id` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `rating`, `comment`, `created_at`, `likes`, `parent_id`) VALUES
(1, 1, 4, 'dc', '2026-04-30 14:43:49', 0, 0),
(2, 13, 4, '', '2026-05-01 14:46:03', 0, 0),
(3, 13, 4, '', '2026-05-01 14:46:04', 0, 0),
(4, 13, 0, '', '2026-05-01 14:53:09', 0, 0),
(5, 13, 5, 'dc', '2026-05-01 14:58:23', 0, 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user`
--

CREATE TABLE `user` (
  `ID` int(10) UNSIGNED NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Username` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Role` int(11) NOT NULL DEFAULT 0 COMMENT '0: sinh viên, 1: chủ trọ, 2: admin',
  `Phone` varchar(255) DEFAULT NULL,
  `Avatar` varchar(255) DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `user`
--

INSERT INTO `user` (`ID`, `Name`, `Username`, `Email`, `Password`, `Role`, `Phone`, `Avatar`) VALUES
(1, 'Trần Thị Thuỳ Nga', 'ngatran', 'ngatran@vinhuni.edu.vn', '$2y$10$qagZTr/wuztStEOGEUVrGObKVz0eMo6STRN9LccXr0wzBeUS8dVpC', 2, '0912345678', '1777743230_IMG_2032.JPG'),
(2, 'Hoàng Văn Công', 'cong_landlord', 'cong@gmail.com', '', 1, '0987654321', 'cong.jpg'),
(3, 'Trần Thị Hoa', 'hoa_nhatro', 'hoa@gmail.com', '', 1, '0911223344', 'hoa.png'),
(4, 'Lê Bảo Ngọc', 'ngoc_k64', 'ngoc@student.vinhuni.edu.vn', '', 0, '0944556677', 'ngoc.jpg'),
(5, 'Lê Thị Vy', 'vy_vy', 'vy@gmail.com', '', 0, '0900998877', 'vy.png'),
(13, '', 'trucp', 'phthanhtruc@gmail.com', '$2y$10$G/PAqhgWqYRpjKDggsJsBulDO2cluhdI5j.YyKW9PeCO3mXbBZYFm', 0, NULL, 'IMG_2003.JPG');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`ID`);

--
-- Chỉ mục cho bảng `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `motel_id` (`motel_id`);

--
-- Chỉ mục cho bảng `districts`
--
ALTER TABLE `districts`
  ADD PRIMARY KEY (`ID`);

--
-- Chỉ mục cho bảng `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`user_id`,`motel_id`),
  ADD KEY `motel_id` (`motel_id`);

--
-- Chỉ mục cho bảng `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD PRIMARY KEY (`ID`);

--
-- Chỉ mục cho bảng `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Chỉ mục cho bảng `motel`
--
ALTER TABLE `motel`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `district_id` (`district_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Chỉ mục cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `comments`
--
ALTER TABLE `comments`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `districts`
--
ALTER TABLE `districts`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `feedbacks`
--
ALTER TABLE `feedbacks`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `messages`
--
ALTER TABLE `messages`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `motel`
--
ALTER TABLE `motel`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `user`
--
ALTER TABLE `user`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`ID`),
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`motel_id`) REFERENCES `motel` (`ID`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`motel_id`) REFERENCES `motel` (`ID`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `user` (`ID`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `user` (`ID`);

--
-- Các ràng buộc cho bảng `motel`
--
ALTER TABLE `motel`
  ADD CONSTRAINT `motel_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `motel_ibfk_2` FOREIGN KEY (`district_id`) REFERENCES `districts` (`ID`),
  ADD CONSTRAINT `motel_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `categories` (`ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
