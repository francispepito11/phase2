-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 15, 2025 at 05:46 AM
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
-- Database: `dict`
--

-- --------------------------------------------------------

--
-- Table structure for table `regions`
--

CREATE TABLE `regions` (
  `id` int(11) NOT NULL,
  `region_code` varchar(20) NOT NULL,
  `region_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `regions`
--

INSERT INTO `regions` (`id`, `region_code`, `region_name`, `created_at`, `updated_at`) VALUES
(1, 'NCR', 'National Capital Region (NCR)', '2025-05-15 02:24:30', '2025-05-15 02:24:30'),
(2, 'CAR', 'Cordillera Administrative Region (CAR)', '2025-05-15 02:24:30', '2025-05-15 02:24:30'),
(3, 'Region I', 'Region I (Ilocos Region)', '2025-05-15 02:24:30', '2025-05-15 02:24:30'),
(4, 'Region II', 'Region II (Cagayan Valley)', '2025-05-15 02:24:30', '2025-05-15 02:24:30'),
(5, 'Region III', 'Region III (Central Luzon)', '2025-05-15 02:24:30', '2025-05-15 02:24:30'),
(6, 'Region IV-A', 'Region IV-A (CALABARZON)', '2025-05-15 02:24:30', '2025-05-15 02:24:30'),
(7, 'Region IV-B', 'Region IV-B (MIMAROPA)', '2025-05-15 02:24:30', '2025-05-15 02:24:30'),
(8, 'Region V', 'Region V (Bicol Region)', '2025-05-15 02:24:30', '2025-05-15 02:24:30'),
(9, 'Region VI', 'Region VI (Western Visayas)', '2025-05-15 02:24:30', '2025-05-15 02:24:30'),
(10, 'Region VII', 'Region VII (Central Visayas)', '2025-05-15 02:24:30', '2025-05-15 02:24:30'),
(11, 'Region VIII', 'Region VIII (Eastern Visayas)', '2025-05-15 02:24:30', '2025-05-15 02:24:30'),
(12, 'Region IX', 'Region IX (Zamboanga Peninsula)', '2025-05-15 02:24:30', '2025-05-15 02:24:30'),
(13, 'Region X', 'Region X (Northern Mindanao)', '2025-05-15 02:24:30', '2025-05-15 02:24:30'),
(14, 'Region XI', 'Region XI (Davao Region)', '2025-05-15 02:24:30', '2025-05-15 02:24:30'),
(15, 'Region XII', 'Region XII (SOCCSKSARGEN)', '2025-05-15 02:24:30', '2025-05-15 02:24:30'),
(16, 'Region XIII', 'Region XIII (Caraga)', '2025-05-15 02:24:30', '2025-05-15 02:24:30'),
(17, 'BARMM', 'Bangsamoro Autonomous Region in Muslim Mindanao', '2025-05-15 02:24:30', '2025-05-15 02:24:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `regions`
--
ALTER TABLE `regions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `region_code` (`region_code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `regions`
--
ALTER TABLE `regions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
