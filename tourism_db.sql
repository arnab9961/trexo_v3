-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 28, 2025 at 10:47 AM
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
-- Database: `tourism_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `package_id` int(11) DEFAULT NULL,
  `destination_id` int(11) DEFAULT NULL,
  `booking_date` date NOT NULL,
  `travel_date` date NOT NULL,
  `num_travelers` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `payment_status` enum('pending','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `package_id`, `destination_id`, `booking_date`, `travel_date`, `num_travelers`, `total_price`, `status`, `payment_status`, `created_at`) VALUES
(9, 7, NULL, 16, '2025-03-14', '2025-03-21', 1, 900.00, 'confirmed', 'completed', '2025-03-13 22:09:25'),
(10, 7, 16, NULL, '2025-03-14', '2025-03-19', 1, 70000.00, 'pending', 'completed', '2025-03-14 10:42:31'),
(11, 8, 20, NULL, '2025-03-28', '2025-03-31', 1, 70000.00, 'confirmed', 'completed', '2025-03-28 06:51:32'),
(12, 8, 16, NULL, '2025-03-28', '2025-04-05', 1, 70000.00, 'confirmed', 'completed', '2025-03-28 08:53:18'),
(13, 8, 16, NULL, '2025-03-28', '2025-03-31', 1, 70000.00, 'cancelled', 'pending', '2025-03-28 09:19:46'),
(14, 8, 17, NULL, '2025-03-28', '2025-03-31', 1, 70000.00, 'confirmed', 'completed', '2025-03-28 09:21:37'),
(15, 8, 17, NULL, '2025-03-28', '2025-03-31', 1, 70000.00, 'confirmed', 'completed', '2025-03-28 09:27:15'),
(16, 8, 18, NULL, '2025-03-28', '2025-04-02', 1, 70000.00, 'confirmed', 'completed', '2025-03-28 09:45:46');

-- --------------------------------------------------------

--
-- Table structure for table `destinations`
--

CREATE TABLE `destinations` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `location` varchar(100) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `destinations`
--

INSERT INTO `destinations` (`id`, `name`, `description`, `location`, `image`, `price`, `featured`, `created_at`) VALUES
(16, 'Historical City', 'd', 'Rome, Italy', 'destination_67d34de7d0667.jpg', 900.00, 1, '2025-03-13 21:28:07');

-- --------------------------------------------------------

--
-- Table structure for table `destination_images`
--

CREATE TABLE `destination_images` (
  `id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inquiries`
--

CREATE TABLE `inquiries` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','in_progress','resolved') DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration` varchar(50) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `name`, `description`, `price`, `duration`, `image`, `featured`, `created_at`) VALUES
(16, 'Kashmir Tour Package', 'Sajek Valley is a breathtaking tourist destination in Bangladesh, located in the Rangamati district. It is known for its scenic beauty, rolling hills, and clouds that often float below the hilltops, creating a magical view.', 70000.00, '7', 'package_67d34ce1cb4a5.jpg', 1, '2025-03-13 21:14:59'),
(17, 'Kashmir Tour Package', 'd', 70000.00, '7', 'package_67d355a46b6fc.jpg', 1, '2025-03-13 22:01:08'),
(18, 'Kashmir Tour Package', 'Sajek Valley is a beautiful hill station in Bangladesh, located in the Rangamati district, near the border of India. It&#039;s often called the &quot;Queen of Hills&quot; because of its breathtaking landscapes, rolling green hills, and mesmerizing clouds.', 70000.00, '7', 'package_67d35747721ab.jpg', 1, '2025-03-13 22:08:07'),
(20, 'sajek valey', 'Sajek Valley is a breathtaking tourist destination in Bangladesh, located in the Rangamati district. It is known for its scenic beauty, rolling hills, and clouds that often float below the hilltops, creating a magical view.', 70000.00, '7', 'package_67d712cb14938.png', 1, '2025-03-16 18:04:59');

-- --------------------------------------------------------

--
-- Table structure for table `package_destinations`
--

CREATE TABLE `package_destinations` (
  `id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `package_destinations`
--

INSERT INTO `package_destinations` (`id`, `package_id`, `destination_id`) VALUES
(40, 16, 16),
(43, 20, 16);

-- --------------------------------------------------------

--
-- Table structure for table `package_images`
--

CREATE TABLE `package_images` (
  `id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `package_images`
--

INSERT INTO `package_images` (`id`, `package_id`, `image_path`, `created_at`) VALUES
(1, 20, 'package_img_67e63dd9ca3f1.jpg', '2025-03-28 06:12:41'),
(2, 20, 'package_img_67e63de0e048f.jpg', '2025-03-28 06:12:48'),
(3, 20, 'package_img_67e6453065bb5.jpg', '2025-03-28 06:44:00');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `sender_number` varchar(20) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `payment_method`, `transaction_id`, `sender_number`, `amount`, `status`, `payment_date`) VALUES
(1, 11, 'nagad', '23412341234', '1234123412341234', 70000.00, 'pending', '2025-03-28 06:57:59'),
(2, 11, 'bkash', '23412341234', '1234123412341234', 70000.00, 'pending', '2025-03-28 06:58:05'),
(3, 11, 'bkash', '23412341234', '1234123412341234', 70000.00, 'pending', '2025-03-28 06:58:51'),
(4, 12, 'bkash', '23412341234', '1234123412341234', 70000.00, 'pending', '2025-03-28 08:53:26'),
(5, 13, 'bkash', '23412341234', '1234123412341234', 70000.00, 'pending', '2025-03-28 09:20:03'),
(6, 15, 'nagad', '23412341234', '1234123412341234', 70000.00, 'pending', '2025-03-28 09:27:25'),
(7, 16, 'bkash', '23412341234', '1234123412341234', 70000.00, 'pending', '2025-03-28 09:45:55');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `package_id` int(11) DEFAULT NULL,
  `destination_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `package_id`, `destination_id`, `rating`, `comment`, `created_at`) VALUES
(2, 1, NULL, 16, 5, 's', '2025-03-13 22:06:35');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `user_type` enum('admin','customer') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `full_name`, `phone`, `address`, `user_type`, `created_at`) VALUES
(1, 'admin', '$2y$10$3UB.HqijNFnqyX9ieEg4BOzhp4geN82NJV9K7uNO6TKl4uBmciLBq', 'admin@tourism.com', 'Admin User', NULL, NULL, 'admin', '2025-03-13 18:33:18'),
(7, 'arnab9961', '$2y$10$OsRhGPCJ.8BAbYiRnH6dou828aVDRt6YpLP.36.dHvkoiGkiAKy6u', 'dmrafiun@gmail.com', 'D.M. Rafiun Bin Bin Masud', '+8801670373934', '1383|8|15|3rnSabuj ban nur tower,notunbagh,khilgaon', 'customer', '2025-03-13 18:51:52'),
(8, 'mehrab', '$2y$10$.p6kRkwMFAu86doRPVWHt.UKrOyBNumRpmv5NuoRo5js2Wxwc21t6', 'arnab.ali76@gmail.com', 'D.M. Rafiun', '01670373934', '', 'customer', '2025-03-28 06:51:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `destination_id` (`destination_id`);

--
-- Indexes for table `destinations`
--
ALTER TABLE `destinations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `destination_images`
--
ALTER TABLE `destination_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `destination_id` (`destination_id`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `package_destinations`
--
ALTER TABLE `package_destinations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `destination_id` (`destination_id`);

--
-- Indexes for table `package_images`
--
ALTER TABLE `package_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_id` (`package_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `destination_id` (`destination_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `destinations`
--
ALTER TABLE `destinations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `destination_images`
--
ALTER TABLE `destination_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `package_destinations`
--
ALTER TABLE `package_destinations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `package_images`
--
ALTER TABLE `package_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `destination_images`
--
ALTER TABLE `destination_images`
  ADD CONSTRAINT `destination_images_ibfk_1` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `package_destinations`
--
ALTER TABLE `package_destinations`
  ADD CONSTRAINT `package_destinations_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `package_destinations_ibfk_2` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `package_images`
--
ALTER TABLE `package_images`
  ADD CONSTRAINT `package_images_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
