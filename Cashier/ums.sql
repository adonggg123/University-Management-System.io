-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 13, 2025 at 02:17 AM
-- Server version: 10.4.22-MariaDB
-- PHP Version: 8.1.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ums`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs1` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `details`, `created_at`) VALUES
(1, NULL, 'login_failed', 'Username: admin', '2025-05-12 23:58:15'),
(2, 4, 'login', 'User logged in', '2025-05-12 23:59:45'),
(3, 4, 'logout', 'User logged out', '2025-05-13 00:03:43'),
(4, NULL, 'login_failed', 'Username: cashier', '2025-05-13 00:03:57'),
(5, NULL, 'login_failed', 'Username: cashier', '2025-05-13 00:04:11'),
(6, NULL, 'login_failed', 'Username: cashier1', '2025-05-13 00:04:31'),
(7, 5, 'login', 'User logged in', '2025-05-13 00:04:39'),
(8, 5, 'logout', 'User logged out', '2025-05-13 00:05:25'),
(9, 6, 'login', 'User logged in', '2025-05-13 00:05:30'),
(10, 6, 'logout', 'User logged out', '2025-05-13 00:06:46'),
(11, 4, 'login', 'User logged in', '2025-05-13 00:06:50'),
(12, 4, 'logout', 'User logged out', '2025-05-13 00:10:02'),
(13, 6, 'login', 'User logged in', '2025-05-13 00:10:09'),
(14, 6, 'create_order', 'Order for student_id=2, type=Tuition, amount=100', '2025-05-13 00:10:30'),
(15, 6, 'logout', 'User logged out', '2025-05-13 00:10:51'),
(16, 5, 'login', 'User logged in', '2025-05-13 00:10:58'),
(17, 4, 'login', 'User logged in', '2025-05-13 00:13:33'),
(18, 4, 'logout', 'User logged out', '2025-05-13 00:15:43'),
(19, 4, 'login', 'User logged in', '2025-05-13 00:15:48'),
(20, 4, 'logout', 'User logged out', '2025-05-13 00:16:02');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders1` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `order_type` enum('Tuition','Miscellaneous','Fees','Fines','TOR','Honorable Dismissal','Good Moral Certificate') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders1` (`id`, `student_id`, `order_type`, `amount`, `status`, `created_at`) VALUES
(1, 2, 'Tuition', '100.00', 'paid', '2025-05-13 00:10:30');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments1` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `cashier_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments1` (`id`, `order_id`, `cashier_id`, `amount`, `payment_date`) VALUES
(1, 1, 5, '100.00', '2025-05-13 00:11:16');

-- --------------------------------------------------------

--
-- Table structure for table `receipts`
--

CREATE TABLE `receipts1` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `receipt_no` varchar(30) NOT NULL,
  `issued_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `receipts`
--

INSERT INTO `receipts1` (`id`, `payment_id`, `receipt_no`, `issued_at`) VALUES
(1, 1, 'RCPT-20250513-000001', '2025-05-13 00:11:16');

-- --------------------------------------------------------

--
-- Table structure for table `refunds`
--

CREATE TABLE `refunds1` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `processed_by` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `processed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `refunds`
--

INSERT INTO `refunds1` (`id`, `payment_id`, `processed_by`, `amount`, `reason`, `processed_at`) VALUES
(1, 1, 5, '100.00', '100', '2025-05-13 00:11:43');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students1` (
  `id` int(11) NOT NULL,
  `student_no` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `course` varchar(50) DEFAULT NULL,
  `year_level` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `students`
--

INSERT INTO `students1` (`id`, `student_no`, `first_name`, `last_name`, `middle_name`, `course`, `year_level`, `created_at`) VALUES
(1, '120231', 'John', 'Smith', 'Duke', 'BSIT', 4, '2025-05-13 00:01:19'),
(2, '145342', 'Johanna', 'Smith', 'Duke', 'BSIT', 3, '2025-05-13 00:01:49');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users1` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','cashier','staff') NOT NULL DEFAULT 'staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users1` (`id`, `username`, `password_hash`, `full_name`, `role`, `created_at`) VALUES
(4, 'admin', '$2y$10$aTVX8Pipuk228kmKcLTp3OhEUMZ0n/LwRNH06ZyxXPiPqZ3MLjzFm', 'System Administrator', 'admin', '2025-05-12 23:59:31'),
(5, 'cashier1', '$2y$10$lUJd.Kp2pmErWzJNL4gvdOjKVP1KFJnyVp/xIJOPk9Ei83NgM7rg.', 'Default Cashier', 'cashier', '2025-05-12 23:59:31'),
(6, 'staff1', '$2y$10$ybpIzYjqdnKIbgDGVQUoTesHZxnljvYNwKcKD529HEFLQlD7440bG', 'Default Staff', 'staff', '2025-05-12 23:59:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs1`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders1`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments1`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `cashier_id` (`cashier_id`);

--
-- Indexes for table `receipts`
--
ALTER TABLE `receipts1`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `receipt_no` (`receipt_no`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indexes for table `refunds`
--
ALTER TABLE `refunds1`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `processed_by` (`processed_by`);

--
-- Indexes for table `students`
--
ALTER TABLE `students1`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_no` (`student_no`);

--
-- Indexes for table `users`
--
ALTER TABLE `users1`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `receipts`
--
ALTER TABLE `receipts1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `refunds`
--
ALTER TABLE `refunds1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs1`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders1`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments1`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `receipts`
--
ALTER TABLE `receipts1`
  ADD CONSTRAINT `receipts_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`);

--
-- Constraints for table `refunds`
--
ALTER TABLE `refunds1`
  ADD CONSTRAINT `refunds_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`),
  ADD CONSTRAINT `refunds_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
