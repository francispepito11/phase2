-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
<<<<<<< HEAD
-- Generation Time: May 19, 2025 at 12:28 PM
=======
-- Generation Time: May 19, 2025 at 02:04 PM
>>>>>>> cee8926e22792b87765fc83013c8bbf472d8ec0f
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
-- Table structure for table `tech_support_requests`
--

CREATE TABLE `tech_support_requests` (
  `id` int(11) NOT NULL,
<<<<<<< HEAD
  `client_name` varchar(100) NOT NULL,
  `agency` varchar(100) NOT NULL,
  `age` int(11) NOT NULL,
  `gender` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
=======
  `firstname` varchar(50) NOT NULL,
  `surname` varchar(50) NOT NULL,
  `middle_initial` char(1) DEFAULT NULL,
  `client_name` varchar(100) NOT NULL,
  `agency` varchar(100) NOT NULL,
  `gender` enum('Male','Female','Other','Prefer not to say') NOT NULL,
  `age` int(11) NOT NULL,
  `region` varchar(50) NOT NULL,
>>>>>>> cee8926e22792b87765fc83013c8bbf472d8ec0f
  `region_id` int(11) NOT NULL,
  `province_id` int(11) NOT NULL,
  `district_id` int(11) DEFAULT NULL,
  `municipality_id` int(11) NOT NULL,
  `support_type` varchar(100) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
<<<<<<< HEAD
=======
  `message` text NOT NULL,
>>>>>>> cee8926e22792b87765fc83013c8bbf472d8ec0f
  `issue_description` text NOT NULL,
  `date_requested` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_assisted` timestamp NULL DEFAULT NULL,
  `date_resolved` timestamp NULL DEFAULT NULL,
  `assisted_by_id` int(11) DEFAULT NULL,
  `status` enum('Pending','In Progress','Resolved','Cancelled') DEFAULT 'Pending',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tech_support_requests`
--

<<<<<<< HEAD
INSERT INTO `tech_support_requests` (`id`, `client_name`, `agency`, `age`, `gender`, `email`, `phone`, `region_id`, `province_id`, `district_id`, `municipality_id`, `support_type`, `subject`, `issue_description`, `date_requested`, `date_assisted`, `date_resolved`, `assisted_by_id`, `status`, `remarks`, `created_at`, `updated_at`) VALUES
(10, 'Francis Pepito', 'DICT', 0, '', 'support@example.com', '1234567890', 12, 18, 13, 78, 'Use of Office Facility', 'HEHE', 'HEHE', '2025-05-18 01:15:27', NULL, '2025-05-19 03:03:11', NULL, 'Resolved', 'hehe', '2025-05-18 07:15:27', '2025-05-19 09:03:11'),
(11, 'Joshua Paltingca', 'PNP', 0, '', 'support@example.com', '1234567890', 12, 18, 13, 20, 'Use of ICT Equipment', 'hehe', 'hehe', '2025-05-18 01:18:49', NULL, NULL, NULL, 'Pending', NULL, '2025-05-18 07:18:49', '2025-05-18 07:18:49'),
(12, 'hehe xd', 'hehe', 0, '', 'support@example.com', '1234567890', 12, 12, 1, 66, 'Sim Card Registration', 'hehe', 'hehe', '2025-05-18 01:19:08', NULL, NULL, NULL, 'Pending', NULL, '2025-05-18 07:19:08', '2025-05-18 07:19:08'),
(13, 'test', 'test', 0, '', 'support@example.com', '1234567890', 12, 17, 10, 53, 'Wifi Installation/Configuration', 'test', 'test', '2025-05-18 06:35:22', NULL, '2025-05-19 03:45:32', NULL, 'Resolved', 'hehe', '2025-05-18 12:35:22', '2025-05-19 09:45:32'),
(14, 'Nidzmhar Tuttuh', 'DICT', 23, 'Male', 'support@example.com', '1234567890', 12, 18, 13, 78, 'Use of ICT Equipment', 'HEHE', 'HEHE', '2025-05-19 04:24:53', NULL, NULL, NULL, 'Pending', NULL, '2025-05-19 10:24:53', '2025-05-19 10:24:53');
=======
INSERT INTO `tech_support_requests` (`id`, `firstname`, `surname`, `middle_initial`, `client_name`, `agency`, `gender`, `age`, `region`, `region_id`, `province_id`, `district_id`, `municipality_id`, `support_type`, `subject`, `message`, `issue_description`, `date_requested`, `date_assisted`, `date_resolved`, `assisted_by_id`, `status`, `remarks`, `created_at`, `updated_at`) VALUES
(15, '', '', NULL, 'Bertizal', 'SCC', 'Male', 25, '', 12, 18, 13, 78, 'Use of Space, ICT Equipment and Internet Connectivity', 'Computer USE', '', '1', '2025-05-19 05:12:50', NULL, NULL, NULL, 'Pending', NULL, '2025-05-19 11:12:50', '2025-05-19 11:12:50'),
(16, 'Rasheed', 'Heding', 'M', '', 'SCC', 'Male', 25, '12', 0, 18, 13, 29, 'Use of Space, ICT Equipment and Internet Connectivity', '1', '1', '', '2025-05-19 11:19:07', NULL, NULL, NULL, 'Pending', NULL, '2025-05-19 11:19:07', '2025-05-19 11:19:07'),
(17, '', '', NULL, 'Nidzmmhar J Tuttuh', 'SCC', 'Male', 24, '', 12, 18, 13, 20, 'Use of Space, ICT Equipment and Internet Connectivity', '12', '', '12', '2025-05-19 05:20:26', NULL, NULL, NULL, 'Pending', NULL, '2025-05-19 11:20:26', '2025-05-19 11:20:26'),
(18, 'Francis', 'Pepito', 'K', 'Francis K Pepito', 'SCC', 'Male', 25, '12', 12, 18, 13, 78, 'Use of Space, ICT Equipment and Internet Connectivity', '23', '', '23SFSF', '2025-05-19 05:24:37', '2025-05-19 05:26:03', '2025-05-19 05:26:32', NULL, 'Resolved', 'FIx', '2025-05-19 11:24:37', '2025-05-19 11:26:32');
>>>>>>> cee8926e22792b87765fc83013c8bbf472d8ec0f

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tech_support_requests`
--
ALTER TABLE `tech_support_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `region_id` (`region_id`),
  ADD KEY `province_id` (`province_id`),
  ADD KEY `district_id` (`district_id`),
  ADD KEY `municipality_id` (`municipality_id`),
  ADD KEY `assisted_by_id` (`assisted_by_id`),
  ADD KEY `date_requested` (`date_requested`),
  ADD KEY `status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tech_support_requests`
--
ALTER TABLE `tech_support_requests`
<<<<<<< HEAD
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
=======
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
>>>>>>> cee8926e22792b87765fc83013c8bbf472d8ec0f
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
