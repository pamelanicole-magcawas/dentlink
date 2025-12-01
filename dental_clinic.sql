-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 01, 2025 at 04:59 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dental_clinic`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetUserByEmail` (IN `user_email` VARCHAR(255))   BEGIN
    SELECT * FROM users WHERE email = user_email LIMIT 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SignupUser` (IN `p_first_name` VARCHAR(50), IN `p_last_name` VARCHAR(50), IN `p_email` VARCHAR(100), IN `p_phone` VARCHAR(15), IN `p_address` VARCHAR(255), IN `p_role` VARCHAR(20), IN `p_password` VARCHAR(255))   BEGIN
    INSERT INTO users (first_name, last_name, email, phone, address, role, password_hash)
    VALUES (p_first_name, p_last_name, p_email, p_phone, p_address, p_role, p_password);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `activity` varchar(255) NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `activity`, `timestamp`) VALUES
(87, 9, 'Logged in', '2025-11-24 22:51:46'),
(88, 8, 'Logged in', '2025-11-24 22:52:08'),
(89, 10, 'Logged in', '2025-11-25 08:50:59'),
(90, 8, 'Logged in', '2025-11-25 08:51:58'),
(91, 8, 'Visited page: Activity Logs', '2025-11-25 08:53:47'),
(92, 9, 'Logged in', '2025-11-25 15:00:11'),
(93, 9, 'Logged out', '2025-11-25 15:01:35'),
(94, 8, 'Logged in', '2025-11-25 15:01:53'),
(95, 8, 'Visited page: Activity Logs', '2025-11-25 15:03:10'),
(96, 9, 'Logged in', '2025-11-25 19:58:46'),
(97, 9, 'Logged out', '2025-11-25 20:01:44'),
(98, 8, 'Logged in', '2025-11-25 20:01:51'),
(99, 8, 'Visited page: Activity Logs', '2025-11-25 20:02:05'),
(100, 8, 'Logged out', '2025-11-25 20:07:00'),
(101, 9, 'Logged in', '2025-11-25 20:07:07'),
(102, 9, 'Logged out', '2025-11-25 20:49:42'),
(103, 8, 'Logged in', '2025-11-25 20:50:09'),
(104, 8, 'Visited page: Activity Logs', '2025-11-25 21:29:41'),
(105, 8, 'Logged out', '2025-11-25 21:31:14'),
(106, 9, 'Logged in', '2025-11-25 21:31:20'),
(107, 9, 'Logged out', '2025-11-25 21:33:36'),
(108, 8, 'Logged in', '2025-11-25 21:33:44'),
(109, 8, 'Visited page: Activity Logs', '2025-11-25 21:44:20'),
(110, 8, 'Logged out', '2025-11-25 21:44:46'),
(111, 9, 'Logged in', '2025-11-25 21:45:00'),
(112, 9, 'Logged out', '2025-11-25 21:50:22'),
(113, 9, 'Logged in', '2025-11-25 21:52:26'),
(114, 9, 'Logged in', '2025-11-26 11:23:17'),
(115, 9, 'Logged out', '2025-11-26 14:48:23'),
(116, 9, 'Logged in', '2025-11-26 14:48:37'),
(117, 8, 'Logged in', '2025-11-26 14:51:21'),
(118, 8, 'Visited page: Activity Logs', '2025-11-26 21:43:08'),
(119, 9, 'Logged in', '2025-11-26 23:12:30'),
(120, 9, 'Logged out', '2025-11-26 23:12:34'),
(121, 8, 'Logged in', '2025-11-26 23:12:51'),
(122, 9, 'Logged out', '2025-11-27 00:39:26'),
(123, 8, 'Logged out', '2025-11-27 00:39:41'),
(124, 8, 'Logged in', '2025-11-27 00:39:53'),
(125, 9, 'Logged in', '2025-11-27 19:35:29'),
(126, 9, 'Logged out', '2025-11-27 19:35:52'),
(127, 8, 'Logged in', '2025-11-27 19:36:09'),
(128, 9, 'Logged in', '2025-11-27 19:45:47'),
(129, 8, 'Visited page: Activity Logs', '2025-11-27 20:46:25'),
(130, 8, 'Visited page: Activity Logs', '2025-11-27 20:46:28'),
(131, 8, 'Visited page: Activity Logs', '2025-11-27 21:45:23'),
(132, 8, 'Visited page: Activity Logs', '2025-11-27 22:31:32'),
(133, 9, 'Logged in', '2025-12-01 08:38:52'),
(134, 8, 'Logged in', '2025-12-01 08:40:47'),
(135, 8, 'Logged out', '2025-12-01 08:48:42'),
(136, 8, 'Logged in', '2025-12-01 08:48:54'),
(137, 8, 'Visited page: Activity Logs', '2025-12-01 09:17:21'),
(138, 8, 'Visited page: Activity Logs', '2025-12-01 09:42:54'),
(139, 8, 'Visited page: Activity Logs', '2025-12-01 09:46:50'),
(140, 9, 'Logged out', '2025-12-01 09:49:38'),
(141, 10, 'Logged in', '2025-12-01 09:55:06');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','approved','denied','checked-in','completed') DEFAULT 'pending',
  `denial_reason` text DEFAULT NULL,
  `qr_code` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `qr_code_url` varchar(255) DEFAULT NULL,
  `calendar_link` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `dentist_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `name`, `email`, `date`, `location`, `start_time`, `description`, `status`, `denial_reason`, `qr_code`, `created_at`, `qr_code_url`, `calendar_link`, `updated_at`, `dentist_id`) VALUES
(14, 9, 'Jimae Zabdiel Austria', 'austriajimaezabdiel@gmail.com', '2025-11-29', 'Dental Clinic, Lipa City', '13:00:00', 'Ceramic Braces', 'checked-in', NULL, NULL, '2025-11-25 20:23:23', 'uploads/qr_appointment_14.png', 'https://www.google.com/calendar/event?eid=cmtzdDVnMGZtODdqcHE2MW1zNjI2ZWlhOW8gc2dkZW50YWxjbGluaWNjY0Bt', '2025-11-27 11:46:49', 2),
(15, 9, 'Jimae Zabdiel Austria', 'austriajimaezabdiel@gmail.com', '2025-12-08', 'Dental Clinic, Lipa City', '10:00:00', 'Consultation Fee', 'completed', NULL, NULL, '2025-11-26 17:17:40', 'uploads/qr_appointment_15.png', 'https://www.google.com/calendar/event?eid=ZHJpamVmbWhycWV1ZHRwaHUycHNydjlhZjggc2dkZW50YWxjbGluaWNjY0Bt', '2025-11-26 09:21:14', 1);

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `sender_type` enum('Patient','Admin','System') NOT NULL,
  `message_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `timestamp` datetime NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_options`
--

CREATE TABLE `chat_options` (
  `id` int(11) UNSIGNED NOT NULL,
  `query_id` int(11) UNSIGNED NOT NULL,
  `button_label` varchar(100) NOT NULL,
  `response_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_options`
--

INSERT INTO `chat_options` (`id`, `query_id`, `button_label`, `response_text`) VALUES
(1, 1, 'Show my upcoming appointments', 'FUNC_CHECK_APPOINTMENTS'),
(2, 2, 'What are your clinic hours?', 'FUNC_CLINIC_HOURS'),
(3, 3, 'Show emergency contact', 'FUNC_EMERGENCY'),
(4, 4, 'What slots are available?', 'FUNC_CHECK_SLOTS'),
(5, 5, 'Where is your clinic located?', 'FUNC_CLINIC_LOCATION');

-- --------------------------------------------------------

--
-- Table structure for table `common_medications`
--

CREATE TABLE `common_medications` (
  `id` int(11) NOT NULL,
  `medication_name` varchar(255) NOT NULL,
  `common_dosage` varchar(100) DEFAULT NULL,
  `common_frequency` varchar(100) DEFAULT NULL,
  `common_duration` varchar(100) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `common_medications`
--

INSERT INTO `common_medications` (`id`, `medication_name`, `common_dosage`, `common_frequency`, `common_duration`, `category`, `is_active`) VALUES
(1, 'Amoxicillin', '500mg', '3 times daily', '7 days', 'Antibiotic', 1),
(2, 'Ibuprofen', '400mg', 'Every 6 hours as needed', '3-5 days', 'Pain Relief', 1),
(3, 'Paracetamol', '500mg', 'Every 4-6 hours as needed', '3-5 days', 'Pain Relief', 1),
(4, 'Mefenamic Acid', '500mg', '3 times daily', '3-5 days', 'Pain Relief', 1),
(5, 'Clindamycin', '300mg', '4 times daily', '7 days', 'Antibiotic', 1),
(6, 'Metronidazole', '500mg', '3 times daily', '5-7 days', 'Antibiotic', 1),
(7, 'Chlorhexidine Mouthwash', '10ml', 'Twice daily', '7-14 days', 'Antiseptic', 1),
(8, 'Biogesic', '500mg', 'Every 4-6 hours as needed', '3-5 days', 'Pain Relief', 1);

-- --------------------------------------------------------

--
-- Table structure for table `dentists`
--

CREATE TABLE `dentists` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `location` varchar(100) NOT NULL,
  `schedule_days` varchar(100) NOT NULL COMMENT 'e.g., Mon-Fri or Sat-Sun',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dentists`
--

INSERT INTO `dentists` (`id`, `name`, `location`, `schedule_days`, `is_active`, `created_at`) VALUES
(1, 'Dr. Sandy Granita', '2nd Floor, CL Building, E Mayo St, Brgy. 4, Lipa City, 4217 Batangas', 'Mon-Fri', 1, '2025-11-21 14:50:31'),
(2, 'Dr. Kristine Mae Bautista', '2nd Floor, CL Building, E Mayo St, Brgy. 4, Lipa City, 4217 Batangas', 'Sat-Sun', 1, '2025-11-21 14:50:31'),
(3, 'Dr. Patrick Del Rosario', 'Sta. Rosa Commercial Complex, 468 Garnet Rd, Balibago, City of Santa Rosa, 4026 Laguna', 'Mon-Fri', 1, '2025-11-21 14:50:31'),
(4, 'Dr. Roselle V. Manalo', 'Sta. Rosa Commercial Complex, 468 Garnet Rd, Balibago, City of Santa Rosa, 4026 Laguna', 'Sat-Sun', 1, '2025-11-21 14:50:31');

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `medication_id` int(11) NOT NULL,
  `prescription_date` date NOT NULL,
  `dosage` varchar(100) NOT NULL,
  `frequency` varchar(100) NOT NULL,
  `duration` varchar(100) NOT NULL,
  `prescribed_by` int(11) DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `user_id`, `rating`, `review_text`, `created_at`) VALUES
(10, 10, 5, 'great experience', '2025-12-01 01:55:35');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `birthdate` date DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `gender` enum('Male','Female','Prefer not to say') DEFAULT NULL,
  `address` varchar(255) NOT NULL,
  `role` enum('Admin','Patient') DEFAULT 'Patient',
  `profile_pic` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `birthdate`, `email`, `phone`, `gender`, `address`, `role`, `profile_pic`, `password`, `created_at`) VALUES
(7, 'Gizelle', 'Dayo', NULL, 'dayoangelagizelle@gmail.com', '09944683904', 'Female', '108 Purok 2 Mojon Tampoy, San Jose, Batangas', 'Patient', 'p_6917d183274804.69072716.jpg', '$2y$10$jszwCqYmPmssLcwWhR5fEeW61O6cWRkEGErtY8eg2RhGMq1.iZn0q', '2025-11-13 22:49:28'),
(8, 'DentLink', 'Admin', NULL, 'sgdentalcliniccc@gmail.com', '09684270187', 'Female', '108 Purok 2 Mojon Tampoy, San Jose, Batangas', 'Admin', 'dentlink-log.jpg', '$2y$10$PBOjvnbkV4v2AQIxj/yK1uEZuZAL/4oUONEjhOWhGlGZUvFW.FLoG', '2025-11-15 00:38:21'),
(10, 'Pamela', 'Magcawas', NULL, 'pamelanicolemagcawas@gmail.com', '09278457598', 'Female', 'Lipa City', 'Patient', 'logo-removebg-preview.png', '$2y$10$g34xVXhVAB.r.OcIxiQtle5zS8wiBdhREYUox4oycr/.5aTniZ/z2', '2025-11-23 05:03:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_activity_user` (`user_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_qr_code` (`qr_code`),
  ADD KEY `idx_date_time` (`date`,`start_time`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_chat_user` (`user_id`),
  ADD KEY `idx_user_sender` (`user_id`,`sender_type`);

--
-- Indexes for table `chat_options`
--
ALTER TABLE `chat_options`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `query_id` (`query_id`);

--
-- Indexes for table `common_medications`
--
ALTER TABLE `common_medications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dentists`
--
ALTER TABLE `dentists`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user` (`user_id`),
  ADD KEY `fk_medication` (`medication_id`),
  ADD KEY `fk_dentist` (`prescribed_by`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_rating` (`rating`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=291;

--
-- AUTO_INCREMENT for table `chat_options`
--
ALTER TABLE `chat_options`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `common_medications`
--
ALTER TABLE `common_medications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `dentists`
--
ALTER TABLE `dentists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `fk_chat_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `fk_dentist` FOREIGN KEY (`prescribed_by`) REFERENCES `dentists` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_medication` FOREIGN KEY (`medication_id`) REFERENCES `common_medications` (`id`),
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
