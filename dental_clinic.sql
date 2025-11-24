-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 24, 2025 at 03:29 PM
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
  `qr_code` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `qr_code_url` varchar(255) DEFAULT NULL,
  `calendar_link` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `dentist_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'Dr. Sandy Granita', 'Dental Clinic, Lipa City', 'Mon-Fri', 1, '2025-11-21 14:50:31'),
(2, 'Dr. Kristine Mae Bautista', 'Dental Clinic, Lipa City', 'Sat-Sun', 1, '2025-11-21 14:50:31'),
(3, 'Dr. Patrick Del Rosario', 'Dental Clinic, San Pablo City', 'Mon-Fri', 1, '2025-11-21 14:50:31'),
(4, 'Dr. Roselle V. Manalo', 'Dental Clinic, San Pablo City', 'Sat-Sun', 1, '2025-11-21 14:50:31');

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
(9, 'Jimae Zabdiel', 'Austria', NULL, 'austriajimaezabdiel@gmail.com', '09709353043', 'Female', '085 Brgy. San Jose, Alitagtag, Batangas', 'Patient', 'IMG_6005.jpeg', '$2y$10$4sUPHDSA3vatR5FKiiwS8eyPBsHDmJvIL3JCfdmRlWRoig40rOuJi', '2025-11-21 01:34:54'),
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=289;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
