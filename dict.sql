-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2025 at 01:13 PM
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
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `client_name` varchar(100) NOT NULL,
  `contact_person` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text DEFAULT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `status` enum('Active','Inactive','Prospect') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `client_name`, `contact_person`, `email`, `phone`, `address`, `company_name`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Acme Corporation', 'John Smith', 'john@acme.com', '(555) 123-4567', '123 Main St, Anytown, USA', 'Acme Corporation', 'Active', '2025-05-14 07:36:56', '2025-05-14 07:36:56'),
(2, 'TechStart Inc.', 'Jane Doe', 'jane@techstart.com', '(555) 987-6543', '456 Tech Blvd, Innovation City, USA', 'TechStart Inc.', 'Active', '2025-05-14 07:36:56', '2025-05-14 07:36:56'),
(3, 'Global Solutions', 'Robert Johnson', 'robert@globalsolutions.com', '(555) 456-7890', '789 Global Ave, Worldtown, USA', 'Global Solutions', 'Prospect', '2025-05-14 07:36:56', '2025-05-14 07:36:56'),
(4, 'Innovative Systems', 'Sarah Williams', 'sarah@innovative.com', '(555) 234-5678', '321 Innovation Dr, Techville, USA', 'Innovative Systems', 'Inactive', '2025-05-14 07:36:56', '2025-05-14 07:36:56'),
(5, 'Future Technologies', 'Michael Brown', 'michael@futuretech.com', '(555) 876-5432', '654 Future St, Tomorrow City, USA', 'Future Technologies', 'Active', '2025-05-14 07:36:56', '2025-05-14 07:36:56');

-- --------------------------------------------------------

--
-- Table structure for table `client_notes`
--

CREATE TABLE `client_notes` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `note_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client_notes`
--

INSERT INTO `client_notes` (`id`, `client_id`, `user_id`, `note_text`, `created_at`) VALUES
(1, 1, 1, 'Initial meeting went well. Client is interested in our premium package.', '2023-06-10 06:30:00'),
(2, 1, 1, 'Follow-up call scheduled for next week.', '2023-06-12 02:15:00'),
(3, 2, 1, 'Client requested a demo of our new features.', '2023-06-11 01:45:00'),
(4, 3, 1, 'Sent proposal for review.', '2023-06-13 08:20:00'),
(5, 4, 1, 'Client is currently using a competitor product. Need to highlight our advantages.', '2023-06-09 03:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `client_training_sessions`
--

CREATE TABLE `client_training_sessions` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `attendance_status` enum('Confirmed','Attended','No-Show','Cancelled') DEFAULT 'Confirmed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client_training_sessions`
--

INSERT INTO `client_training_sessions` (`id`, `client_id`, `session_id`, `attendance_status`) VALUES
(1, 1, 1, 'Confirmed'),
(2, 2, 1, 'Confirmed'),
(3, 1, 2, 'Confirmed'),
(4, 3, 3, 'Confirmed'),
(5, 2, 4, 'Attended'),
(6, 4, 5, 'Cancelled');

-- --------------------------------------------------------

--
-- Table structure for table `districts`
--

CREATE TABLE `districts` (
  `id` int(11) NOT NULL,
  `district_name` varchar(100) NOT NULL,
  `district_code` varchar(20) DEFAULT NULL,
  `province_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `districts`
--

INSERT INTO `districts` (`id`, `district_name`, `district_code`, `province_id`, `created_at`, `updated_at`) VALUES
(1, 'District 1', 'D01', 12, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(2, 'District 2', 'D02', 12, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(3, 'District 1', 'D01', 13, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(4, 'District 1', 'D01', 16, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(5, 'District 1', 'D01', 1, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(6, 'District 2', 'D02', 1, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(7, 'District 3', 'D03', 1, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(8, 'District 4', 'D04', 1, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(10, 'District 1', 'D01', 17, '2025-05-16 08:12:47', '2025-05-16 08:21:25'),
(11, 'District 2', 'D02', 17, '2025-05-16 08:13:11', '2025-05-16 08:21:32'),
(13, 'District 1', 'D01', 18, '2025-05-16 08:14:41', '2025-05-18 07:13:54'),
(14, 'District 2', 'D02', 18, '2025-05-16 08:14:41', '2025-05-18 07:14:00');

-- --------------------------------------------------------

--
-- Table structure for table `municipalities`
--

CREATE TABLE `municipalities` (
  `id` int(11) NOT NULL,
  `municipality_name` varchar(100) NOT NULL,
  `municipality_code` varchar(20) DEFAULT NULL,
  `province_id` int(11) NOT NULL,
  `district_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `municipalities`
--

INSERT INTO `municipalities` (`id`, `municipality_name`, `municipality_code`, `province_id`, `district_id`, `created_at`, `updated_at`) VALUES
(1, 'Quezon City', 'QC', 1, 5, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(2, 'Manila', 'MLA', 1, 6, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(3, 'Makati', 'MKT', 1, 7, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(4, 'Taguig', 'TGG', 1, 8, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(5, 'Baguio City', 'BAG', 2, NULL, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(6, 'Laoag City', 'LAO', 3, NULL, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(7, 'Vigan City', 'VIG', 4, NULL, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(8, 'San Fernando', 'SFD', 5, NULL, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(9, 'Dagupan City', 'DAG', 6, NULL, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(10, 'Batangas City', 'BAT', 7, NULL, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(11, 'Cavite City', 'CAV', 8, NULL, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(12, 'Calamba', 'CAL', 9, NULL, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(13, 'Lucena City', 'LUC', 10, NULL, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(14, 'Antipolo', 'ANT', 11, NULL, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(16, 'Davao City', 'DAV', 13, 3, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(17, 'Tagum City', 'TAG', 14, NULL, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(18, 'Cotabato City', 'COT', 15, NULL, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(19, 'Jolo', 'JOL', 16, 4, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(20, 'Pagadian City', 'ZDS', 18, 13, '2025-05-16 08:22:38', '2025-05-18 07:16:38'),
(21, 'Labangan', 'ZDS', 18, 13, '2025-05-16 08:22:38', '2025-05-18 07:16:38'),
(22, 'Tukuran', 'ZDS', 18, 13, '2025-05-16 08:23:37', '2025-05-18 07:16:38'),
(23, 'Aurora', 'ZDS', 18, 13, '2025-05-16 08:23:37', '2025-05-18 07:16:38'),
(24, 'Molave', 'ZDS', 18, 13, '2025-05-16 08:28:41', '2025-05-18 07:16:38'),
(25, 'Mahayag', 'ZDS', 18, 13, '2025-05-16 08:28:41', '2025-05-18 07:16:38'),
(26, 'Dumingag', 'ZDS', 18, 13, '2025-05-16 08:29:18', '2025-05-18 07:16:38'),
(27, 'Suminot', 'ZDS', 18, 13, '2025-05-16 08:29:18', '2025-05-18 07:17:15'),
(28, 'Midsalip', 'ZDS', 18, 13, '2025-05-16 08:29:46', '2025-05-18 07:17:15'),
(29, 'Ramon Magsaysay', 'ZDS', 18, 13, '2025-05-16 08:29:46', '2025-05-18 07:17:15'),
(30, 'Tambulig', 'ZDS', 18, 13, '2025-05-16 08:30:18', '2025-05-18 07:17:15'),
(31, 'Josefina', 'ZDS', 18, 13, '2025-05-16 08:30:18', '2025-05-18 07:18:18'),
(32, 'Lakewood', 'ZDS', 18, 14, '2025-05-16 08:31:09', '2025-05-18 07:18:18'),
(33, 'Bayog', 'ZDS', 18, 14, '2025-05-16 08:31:09', '2025-05-18 07:18:18'),
(34, 'Kumalarang', 'ZDS', 18, 14, '2025-05-16 08:32:10', '2025-05-18 07:18:18'),
(35, 'Tigbao', 'ZDS', 18, 143, '2025-05-16 08:32:10', '2025-05-18 07:18:18'),
(36, 'Dumalinao', 'ZDS', 18, 14, '2025-05-16 08:32:43', '2025-05-18 07:18:18'),
(37, 'Guipos', 'ZDS', 18, 14, '2025-05-16 08:32:43', '2025-05-18 07:18:18'),
(38, 'San Miguel', 'ZDS', 18, 14, '2025-05-16 08:33:12', '2025-05-18 07:18:18'),
(39, 'Lapuyan', 'ZDS', 18, 14, '2025-05-16 08:33:12', '2025-05-18 07:18:18'),
(40, 'Margosatubig', 'ZDS', 18, 14, '2025-05-16 08:33:40', '2025-05-18 07:18:18'),
(41, 'Dinas', 'ZDS', 18, 14, '2025-05-16 08:33:40', '2025-05-18 07:18:18'),
(42, 'San Pablo', 'ZDS', 18, 14, '2025-05-16 08:34:10', '2025-05-18 07:18:18'),
(43, 'Tabina', 'ZDS', 18, 14, '2025-05-16 08:34:10', '2025-05-18 07:18:18'),
(44, 'Dimataling', 'ZDS', 18, 14, '2025-05-16 08:34:38', '2025-05-18 07:18:18'),
(45, 'Vincenzo Sagun', 'ZDS', 18, 14, '2025-05-16 08:34:38', '2025-05-18 07:18:18'),
(46, 'Pitogo', 'ZDS', 18, 14, '2025-05-16 08:34:59', '2025-05-18 07:18:18'),
(47, 'Alicia', 'ZSP', 17, 10, '2025-05-16 08:38:43', '2025-05-16 08:38:43'),
(48, 'Buug', 'ZSP', 17, 10, '2025-05-16 08:38:43', '2025-05-16 08:38:43'),
(49, 'Diplahan', 'ZSP', 17, 10, '2025-05-16 08:39:37', '2025-05-16 08:39:37'),
(50, 'Imelda', 'ZSP', 17, 10, '2025-05-16 08:39:37', '2025-05-16 08:39:37'),
(51, 'Mabuhay', 'ZSP', 17, 10, '2025-05-16 08:40:10', '2025-05-16 08:40:10'),
(52, 'Malangas', 'ZSP', 17, 10, '2025-05-16 08:40:10', '2025-05-16 08:40:10'),
(53, 'Olutanga', 'ZSP', 17, 10, '2025-05-16 08:40:48', '2025-05-16 08:40:48'),
(54, 'Payao', 'ZSP', 17, 10, '2025-05-16 08:40:48', '2025-05-16 08:40:48'),
(55, 'Talusan', 'ZSP', 17, 10, '2025-05-16 08:41:12', '2025-05-16 08:41:12'),
(56, 'Kabasalan', 'ZSP', 17, 11, '2025-05-16 08:42:41', '2025-05-16 08:42:41'),
(57, 'Ipil', 'ZSP', 17, 11, '2025-05-16 08:42:41', '2025-05-16 08:42:41'),
(58, 'Naga', 'ZSP', 17, 11, '2025-05-16 08:43:20', '2025-05-16 08:43:20'),
(59, 'Siay', 'ZSP', 17, 11, '2025-05-16 08:43:20', '2025-05-16 08:45:26'),
(62, 'R.T. Lim', 'ZSP', 17, 11, '2025-05-16 08:46:11', '2025-05-16 08:46:11'),
(63, 'Titay', 'ZSP', 17, 11, '2025-05-16 08:46:11', '2025-05-16 08:46:11'),
(64, 'Tungawan', 'ZSP', 17, 11, '2025-05-16 08:46:32', '2025-05-16 08:46:32'),
(65, 'La Libertad', 'ZDN', 12, 1, '2025-05-16 08:50:02', '2025-05-16 08:50:02'),
(66, 'Mutia', 'ZDN', 12, 1, '2025-05-16 08:50:02', '2025-05-16 08:50:02'),
(67, 'Piñan', 'ZDN', 12, 1, '2025-05-16 08:50:30', '2025-05-16 08:50:30'),
(68, 'Polanco', 'ZDN', 12, 1, '2025-05-16 08:50:30', '2025-05-16 08:50:30'),
(69, 'Sergio Osmeña Sr.', 'ZDN', 12, 1, '2025-05-16 08:51:04', '2025-05-16 08:51:04'),
(70, 'Rizal', 'ZDN', 12, 1, '2025-05-16 08:51:04', '2025-05-16 08:51:04'),
(71, 'Sibutad', 'ZDN', 12, 1, '2025-05-16 08:51:15', '2025-05-16 08:51:15'),
(72, 'Jose Dalman', 'ZDN', 12, 2, '2025-05-16 08:53:44', '2025-05-16 08:53:44'),
(73, 'Katipunan', 'ZDN', 12, 2, '2025-05-16 08:53:44', '2025-05-16 08:53:44'),
(74, 'Manukan', 'ZDN', 12, 2, '2025-05-16 08:54:11', '2025-05-16 08:54:11'),
(75, 'President Manuel A. Roxas', 'ZDN', 12, 2, '2025-05-16 08:54:11', '2025-05-16 08:54:11'),
(76, 'Siayan', 'ZDN', 12, 2, '2025-05-16 08:54:34', '2025-05-16 08:54:34'),
(77, 'Sindangan', 'ZDN', 12, 2, '2025-05-16 08:54:34', '2025-05-16 08:54:34'),
(78, 'City of Zamboanga\r\n', 'ZDS', 18, 13, '2025-05-16 08:56:55', '2025-05-18 07:15:07');

-- --------------------------------------------------------

--
-- Table structure for table `provinces`
--

CREATE TABLE `provinces` (
  `id` int(11) NOT NULL,
  `province_name` varchar(100) NOT NULL,
  `province_code` varchar(20) DEFAULT NULL,
  `region_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `provinces`
--

INSERT INTO `provinces` (`id`, `province_name`, `province_code`, `region_id`, `created_at`, `updated_at`) VALUES
(1, 'Metro Manila', 'MM', 1, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(2, 'Benguet', 'BEN', 2, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(3, 'Ilocos Norte', 'ILN', 3, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(4, 'Ilocos Sur', 'ILS', 3, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(5, 'La Union', 'LUN', 3, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(6, 'Pangasinan', 'PAN', 3, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(7, 'Batangas', 'BAT', 6, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(8, 'Cavite', 'CAV', 6, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(9, 'Laguna', 'LAG', 6, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(10, 'Quezon', 'QUE', 6, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(11, 'Rizal', 'RIZ', 6, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(12, 'Zamboanga Del Norte', 'ZDN', 12, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(13, 'Davao Del Sur', 'DDS', 14, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(14, 'Davao Del Norte', 'DDN', 14, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(15, 'Maguindanao', 'MAG', 17, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(16, 'Sulu', 'SUL', 17, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(17, 'Zamboanga Sibugay', 'ZSP', 12, '2025-05-16 08:06:39', '2025-05-16 08:06:39'),
(18, 'Zamboanga Del Sur', 'ZDS', 12, '2025-05-16 08:04:01', '2025-05-18 07:13:17');

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

-- --------------------------------------------------------

--
-- Table structure for table `service_requests`
--

CREATE TABLE `service_requests` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `service_type_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `date_requested` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_assisted` timestamp NULL DEFAULT NULL,
  `date_resolved` timestamp NULL DEFAULT NULL,
  `assisted_by_id` int(11) DEFAULT NULL,
  `status` enum('Pending','In Progress','Resolved','Cancelled') DEFAULT 'Pending',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_types`
--

CREATE TABLE `service_types` (
  `id` int(11) NOT NULL,
  `service_code` varchar(50) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_types`
--

INSERT INTO `service_types` (`id`, `service_code`, `service_name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'WIFI', 'WiFi Installation/Configuration', 'Setup and configuration of WiFi networks for government offices and public spaces', 1, '2025-05-15 01:58:39', '2025-05-15 01:58:39'),
(2, 'GOVNET', 'GovNet Installation/Maintenance', 'Installation and maintenance of government network infrastructure', 1, '2025-05-15 01:58:39', '2025-05-15 01:58:39'),
(3, 'IBPLS', 'iBPLS Virtual Assistance', 'Virtual assistance for integrated Business Permits and Licensing System', 1, '2025-05-15 01:58:39', '2025-05-15 01:58:39'),
(4, 'PNPKI', 'PNPKI Tech Support', 'Technical support for Philippine National Public Key Infrastructure', 1, '2025-05-15 01:58:39', '2025-05-15 01:58:39'),
(5, 'EQUIP', 'ICT Equipment Lending', 'Lending of ICT equipment for government agencies and events', 1, '2025-05-15 01:58:39', '2025-05-15 01:58:39'),
(6, 'CYBER', 'Cybersecurity Support', 'Assistance with cybersecurity and data privacy concerns', 1, '2025-05-15 01:58:39', '2025-05-15 01:58:39'),
(7, 'OFFICE', 'Use of Office Facility', 'Use of DICT office facilities for meetings and events', 1, '2025-05-15 01:58:39', '2025-05-15 01:58:39'),
(8, 'SPACE', 'Use of Space, ICT Equipment and Internet Connectivity', 'Provision of space, equipment and connectivity', 1, '2025-05-15 01:58:39', '2025-05-17 13:08:48'),
(9, 'SIM', 'Sim Card Registration', 'Assistance with SIM card registration', 1, '2025-05-15 01:58:39', '2025-05-15 01:58:39'),
(10, 'COMMS', 'Comms-related concern', 'Support for communications-related issues', 1, '2025-05-15 01:58:39', '2025-05-15 01:58:39'),
(11, 'TECH', 'Provision of Technical Personnel', 'Provision of technical personnel for events and projects', 1, '2025-05-15 01:58:39', '2025-05-15 01:58:39'),
(12, 'OTHER', 'Other Services', 'Other ICT-related services not listed above', 1, '2025-05-15 01:58:39', '2025-05-15 01:58:39');

-- --------------------------------------------------------

--
-- Table structure for table `support_requests`
--

CREATE TABLE `support_requests` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `agency` varchar(255) NOT NULL,
  `region` varchar(50) NOT NULL,
  `province_id` int(11) DEFAULT NULL,
  `district_id` int(11) DEFAULT NULL,
  `municipality_id` int(11) DEFAULT NULL,
  `support_type` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','in_progress','resolved','closed') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tech_support_requests`
--

CREATE TABLE `tech_support_requests` (
  `id` int(11) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `surname` varchar(50) NOT NULL,
  `middle_initial` char(1) DEFAULT NULL,
  `client_name` varchar(100) NOT NULL,
  `agency` varchar(100) NOT NULL,
  `gender` enum('Male','Female','Other','Prefer not to say') NOT NULL,
  `age` int(11) NOT NULL,
  `region` varchar(50) NOT NULL,
  `region_id` int(11) NOT NULL,
  `province_id` int(11) NOT NULL,
  `district_id` int(11) DEFAULT NULL,
  `municipality_id` int(11) NOT NULL,
  `support_type` varchar(100) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
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

INSERT INTO `tech_support_requests` (`id`, `firstname`, `surname`, `middle_initial`, `client_name`, `agency`, `gender`, `age`, `region`, `region_id`, `province_id`, `district_id`, `municipality_id`, `support_type`, `subject`, `message`, `issue_description`, `date_requested`, `date_assisted`, `date_resolved`, `assisted_by_id`, `status`, `remarks`, `created_at`, `updated_at`) VALUES
(15, '', '', NULL, 'Bertizal', 'SCC', 'Male', 25, '', 12, 18, 13, 78, 'Use of Space, ICT Equipment and Internet Connectivity', 'Computer USE', '', '1', '2025-05-19 05:12:50', NULL, NULL, NULL, 'Pending', NULL, '2025-05-19 11:12:50', '2025-05-19 11:12:50');

-- --------------------------------------------------------

--
-- Table structure for table `training_events`
--

CREATE TABLE `training_events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(255) NOT NULL,
  `seats_available` int(11) NOT NULL DEFAULT 0,
  `event_type` enum('in-person','virtual','hybrid') NOT NULL DEFAULT 'in-person',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `training_registrations`
--

CREATE TABLE `training_registrations` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `participant_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `agency` varchar(100) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `attendance_status` enum('Registered','Confirmed','Attended','Cancelled') DEFAULT 'Registered',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `training_sessions`
--

CREATE TABLE `training_sessions` (
  `id` int(11) NOT NULL,
  `session_title` varchar(100) NOT NULL,
  `session_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `trainer_id` int(11) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `meeting_link` varchar(255) DEFAULT NULL,
  `status` enum('Scheduled','Completed','Cancelled') NOT NULL DEFAULT 'Scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `training_sessions`
--

INSERT INTO `training_sessions` (`id`, `session_title`, `session_date`, `start_time`, `end_time`, `trainer_id`, `location`, `meeting_link`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Product Training', '2023-06-20', '10:00:00', '12:00:00', 1, 'Conference Room A', '', 'Scheduled', '2025-05-14 07:36:56', '2025-05-14 07:36:56'),
(2, 'New Features Overview', '2023-06-22', '14:00:00', '15:30:00', 1, '', 'https://zoom.us/j/123456789', 'Scheduled', '2025-05-14 07:36:56', '2025-05-14 07:36:56'),
(3, 'Advanced Techniques', '2023-06-25', '09:00:00', '11:00:00', 1, 'Training Room B', '', 'Scheduled', '2025-05-14 07:36:56', '2025-05-14 07:36:56'),
(4, 'Onboarding Session', '2023-06-15', '13:00:00', '14:00:00', 1, '', 'https://zoom.us/j/987654321', 'Completed', '2025-05-14 07:36:56', '2025-05-14 07:36:56'),
(5, 'Quarterly Review', '2023-06-10', '11:00:00', '12:30:00', 1, 'Conference Room C', '', 'Cancelled', '2025-05-14 07:36:56', '2025-05-14 07:36:56');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('Administrator','Manager','Staff') NOT NULL DEFAULT 'Staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$8KQT.Oj9.ym4F0mZVx9mAOUJA8xQyWN.DMB.xJvGt4XhUeU/UPmOy', 'admin@example.com', 'Administrator', '2025-05-14 07:36:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client_notes`
--
ALTER TABLE `client_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `client_training_sessions`
--
ALTER TABLE `client_training_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `client_id` (`client_id`,`session_id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `districts`
--
ALTER TABLE `districts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `province_id` (`province_id`);

--
-- Indexes for table `municipalities`
--
ALTER TABLE `municipalities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `province_id` (`province_id`),
  ADD KEY `district_id` (`district_id`);

--
-- Indexes for table `provinces`
--
ALTER TABLE `provinces`
  ADD PRIMARY KEY (`id`),
  ADD KEY `region_id` (`region_id`);

--
-- Indexes for table `regions`
--
ALTER TABLE `regions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `region_code` (`region_code`);

--
-- Indexes for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `service_type_id` (`service_type_id`),
  ADD KEY `assisted_by_id` (`assisted_by_id`);

--
-- Indexes for table `service_types`
--
ALTER TABLE `service_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `service_code` (`service_code`);

--
-- Indexes for table `support_requests`
--
ALTER TABLE `support_requests`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `training_events`
--
ALTER TABLE `training_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_date` (`event_date`),
  ADD KEY `is_active` (`is_active`);

--
-- Indexes for table `training_registrations`
--
ALTER TABLE `training_registrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `registration_date` (`registration_date`),
  ADD KEY `attendance_status` (`attendance_status`);

--
-- Indexes for table `training_sessions`
--
ALTER TABLE `training_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trainer_id` (`trainer_id`);

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
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `client_notes`
--
ALTER TABLE `client_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `client_training_sessions`
--
ALTER TABLE `client_training_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `districts`
--
ALTER TABLE `districts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `municipalities`
--
ALTER TABLE `municipalities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `regions`
--
ALTER TABLE `regions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `support_requests`
--
ALTER TABLE `support_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tech_support_requests`
--
ALTER TABLE `tech_support_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
