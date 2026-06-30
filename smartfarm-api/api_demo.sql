-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 30, 2026 at 05:38 AM
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
-- Database: `api_demo`
--

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `id` int(11) NOT NULL,
  `device_name` varchar(100) NOT NULL,
  `device_type` enum('LED','SENSOR') NOT NULL,
  `pin` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'OFF',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `devices`
--

INSERT INTO `devices` (`id`, `device_name`, `device_type`, `pin`, `status`, `created_at`) VALUES
(1, 'LED1', 'LED', 13, 'ON', '2026-06-30 03:20:39'),
(2, 'LED2', 'LED', 12, 'ON', '2026-06-30 03:20:39'),
(3, 'Temperature', 'SENSOR', 34, 'ACTIVE', '2026-06-30 03:20:39'),
(4, 'Humidity', 'SENSOR', 35, 'ACTIVE', '2026-06-30 03:20:39'),
(5, 'Soil Moisture', 'SENSOR', 36, 'ACTIVE', '2026-06-30 03:20:39');

-- --------------------------------------------------------

--
-- Table structure for table `led_status`
--

CREATE TABLE `led_status` (
  `id` int(11) NOT NULL,
  `led_name` varchar(100) NOT NULL,
  `status` varchar(50) NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `led_status`
--

INSERT INTO `led_status` (`id`, `led_name`, `status`, `datetime`) VALUES
(1, 'LED1', 'ON', '2026-06-30 03:27:54'),
(2, 'LED2', 'ON', '2026-06-30 03:28:10'),
(3, 'LED2', 'OFF', '2026-06-30 03:28:16'),
(4, 'LED2', 'ON', '2026-06-30 03:28:17'),
(5, 'LED1', 'OFF', '2026-06-30 03:28:36'),
(6, 'LED1', 'ON', '2026-06-30 03:28:37'),
(7, 'LED2', 'OFF', '2026-06-30 03:28:40'),
(8, 'LED2', 'ON', '2026-06-30 03:28:40'),
(9, 'LED1', 'OFF', '2026-06-30 03:28:41'),
(10, 'LED1', 'ON', '2026-06-30 03:28:41'),
(11, 'LED2', 'OFF', '2026-06-30 03:28:42'),
(12, 'LED2', 'ON', '2026-06-30 03:28:43');

-- --------------------------------------------------------

--
-- Table structure for table `sensor_data`
--

CREATE TABLE `sensor_data` (
  `id` int(11) NOT NULL,
  `sensor_name` varchar(100) NOT NULL,
  `sensor_value` float NOT NULL,
  `unit` varchar(20) NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `created_at`) VALUES
(1, 'Jane Smith', 'jane.smith@example.com', '2026-06-23 07:38:06'),
(2, 'Bob Johnson', 'bob.j@example.com', '2026-06-23 07:38:06'),
(3, 'Charlie Brown', 'cbrown@example.com', '2026-06-23 07:38:06'),
(4, 'Diana Prince', 'diana.p@example.com', '2026-06-23 07:38:06'),
(5, 'Evan Wright', 'evan.wright@example.com', '2026-06-23 07:38:06'),
(6, 'Fiona Gallagher', 'fionag@example.com', '2026-06-23 07:38:06'),
(7, 'George Miller', 'gmiller@example.com', '2026-06-23 07:38:06'),
(8, 'Hannah Abbott', 'hannah.a@example.com', '2026-06-23 07:38:06'),
(9, 'Ian Malcolm', 'ian.m@example.com', '2026-06-23 07:38:06'),
(10, 'Julia Roberts', 'jroberts@example.com', '2026-06-23 07:38:06'),
(11, 'Kevin Hart', 'khart@example.com', '2026-06-23 07:38:06'),
(12, 'Laura Palmer', 'laurap@example.com', '2026-06-23 07:38:06'),
(13, 'Michael Scott', 'mscott@example.com', '2026-06-23 07:38:06'),
(14, 'Nina Simone', 'nsimone@example.com', '2026-06-23 07:38:06'),
(15, 'Oscar Martinez', 'omartinez@example.com', '2026-06-23 07:38:06'),
(16, 'Pam Beesly', 'pamb@example.com', '2026-06-23 07:38:06'),
(17, 'Quentin Tarantino', 'qtarantino@example.com', '2026-06-23 07:38:06'),
(18, 'Rachel Green', 'rgreen@example.com', '2026-06-23 07:38:06'),
(19, 'Steve Rogers', 'srogers@example.com', '2026-06-23 07:38:06'),
(20, 'Tony Stark', 'tstark@example.com', '2026-06-23 07:38:06'),
(21, 'Jane Doe', 'jane@example.com', '2026-06-23 08:10:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `led_status`
--
ALTER TABLE `led_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sensor_data`
--
ALTER TABLE `sensor_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `led_status`
--
ALTER TABLE `led_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `sensor_data`
--
ALTER TABLE `sensor_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
