-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 24, 2026 at 04:08 PM
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
-- Database: `ignisense_db`
--
CREATE DATABASE IF NOT EXISTS `ignisense_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `ignisense_db`;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `type` enum('user','system') NOT NULL,
  `lat` double NOT NULL,
  `lng` double NOT NULL,
  `location_name` varchar(255) DEFAULT NULL,
  `reporter_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `type`, `lat`, `lng`, `location_name`, `reporter_id`, `created_at`) VALUES
(10, 'user', 14.039750019281, 121.09014742209, 'User Report (14.0398, 121.0901)', 2, '2026-01-06 13:34:37'),
(12, 'user', 14.109145777109, 121.1452893915, 'User Report (14.1091, 121.1453)', 2, '2026-01-06 14:06:41'),
(16, 'user', 14.067146171761, 121.1389599941, 'User Report (14.0671, 121.1390)', 2, '2026-01-08 16:23:29'),
(17, 'user', 14.081202039003, 121.14320238325, 'User Report (14.0812, 121.1432)', 2, '2026-01-08 16:37:21'),
(18, 'user', 14.067575734933, 121.15299054371, 'User Report (14.0676, 121.1530)', 2, '2026-01-08 16:44:20'),
(19, 'user', 14.114537766271, 121.11613782224, 'User Report (14.1145, 121.1161)', 2, '2026-01-08 16:48:14');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'System Admin', 'admin@ignisense.ph', '$2y$10$nyckXMkcHeJ6ry/F.TUZyOjia.zotNf7/CxjCj3wdSIq89vhw0TRO', 'admin', '2026-01-06 13:04:09'),
(2, 'Jp', 'jp@gmail.com', '$2y$10$IsSNCG0Yv2wOMLjq1eEfg.OHtNULI.yHoEiDWzZJ05ZjjNt4DfsgO', 'user', '2026-01-06 13:34:15'),
(4, 'godoyjp', 'jp123@gmail.com', '$2y$10$tW30PdGHgOywEclCwYY09OUluSm1jIRVv0NFlUObeCBs6Zl.2uvQa', 'user', '2026-03-24 15:01:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reporter_id` (`reporter_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
