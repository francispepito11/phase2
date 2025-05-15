-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2025 at 09:34 AM
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
  `agency` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `region_id` int(11) NOT NULL,
  `province_id` int(11) NOT NULL,
  `district_id` int(11) DEFAULT NULL,
  `municipality_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(16, 'Sulu', 'SUL', 17, '2025-05-14 07:00:00', '2025-05-14 07:00:00');

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
(8, 'District 4', 'D04', 1, '2025-05-14 07:00:00', '2025-05-14 07:00:00');

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
(15, 'Zamboanga City', 'ZAM', 12, 1, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(16, 'Davao City', 'DAV', 13, 3, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(17, 'Tagum City', 'TAG', 14, NULL, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(18, 'Cotabato City', 'COT', 15, NULL, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(19, 'Jolo', 'JOL', 16, 4, '2025-05-14 07:00:00', '2025-05-14 07:00:00');

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `email`, `role`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$ZG4xnGi6lKhcKUYyZjF4UeARNT0UgIRXNY1Kl.z9ZCx6Yh7PXmIHu', 'Administrator', 'admin@dict.gov.ph', 'admin', 1, '2025-05-14 07:00:00', '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(2, 'staff1', '$2y$10$ZG4xnGi6lKhcKUYyZjF4UeARNT0UgIRXNY1Kl.z9ZCx6Yh7PXmIHu', 'Staff Member', 'staff@dict.gov.ph', 'staff', 1, '2025-05-14 07:00:00', '2025-05-14 07:00:00', '2025-05-14 07:00:00');

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `client_name`, `agency`, `email`, `phone`, `region_id`, `province_id`, `district_id`, `municipality_id`, `created_at`, `updated_at`) VALUES
(1, 'Cherry Belle L. Assali', 'IPHO Sulu', 'cherry.assali@ipho.gov.ph', '09123456789', 17, 16, 4, 19, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(2, 'Maria Santos', 'DepEd Zamboanga', 'maria.santos@deped.gov.ph', '09187654321', 12, 12, 1, 15, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(3, 'Juan Dela Cruz', 'City Government of Quezon City', 'juan.delacruz@quezoncity.gov.ph', '09234567890', 1, 1, 5, 1, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(4, 'Francis Pepito', 'DICT', 'francis.pepito@dict.gov.ph', '09765432109', 12, 12, 1, 15, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(5, 'Elena Magtanggol', 'DOST Region IV-A', 'elena.magtanggol@dost.gov.ph', '09567891234', 6, 9, NULL, 12, '2025-05-14 07:00:00', '2025-05-14 07:00:00');

--
-- Dumping data for table `tech_support_requests`
--

INSERT INTO `tech_support_requests` (`id`, `client_name`, `agency`, `email`, `phone`, `region_id`, `province_id`, `district_id`, `municipality_id`, `support_type`, `issue_description`, `date_requested`, `date_assisted`, `date_resolved`, `assisted_by_id`, `status`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 'Cherry Belle L. Assali', 'IPHO Sulu', 'cherry.assali@ipho.gov.ph', '09123456789', 17, 16, 4, 19, 'PNPKI Tech Support', 'Unable to install PNPKI certificate on new computer', '2025-05-14 07:00:00', '2025-05-14 08:30:00', '2025-05-14 10:15:00', 1, 'Resolved', 'Successfully installed PNPKI certificate and provided user training', '2025-05-14 07:00:00', '2025-05-14 10:15:00'),
(2, 'Maria Santos', 'DepEd Zamboanga', 'maria.santos@deped.gov.ph', '09187654321', 12, 12, 1, 15, 'WiFi Installation/configuration', 'Need assistance setting up WiFi network for new office', '2025-05-14 09:45:00', '2025-05-14 13:20:00', NULL, 2, 'In Progress', 'Initial assessment completed. Hardware requirements provided to client.', '2025-05-14 09:45:00', '2025-05-14 13:20:00'),
(3, 'Juan Dela Cruz', 'City Government of Quezon City', 'juan.delacruz@quezoncity.gov.ph', '09234567890', 1, 1, 5, 1, 'GovNet Installation/Maintenance', 'GovNet connection is intermittent during peak hours', '2025-05-14 11:30:00', NULL, NULL, NULL, 'Pending', NULL, '2025-05-14 11:30:00', '2025-05-14 11:30:00');

--
-- Dumping data for table `training_events`
--

INSERT INTO `training_events` (`id`, `title`, `description`, `event_date`, `start_time`, `end_time`, `location`, `seats_available`, `event_type`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Basic ICT Literacy Training', 'Introduction to basic computer operations, internet usage, and common office applications.', '2025-05-25', '09:00:00', '16:00:00', 'DICT Training Room A, Quezon City', 20, 'in-person', 1, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(2, 'Cybersecurity Awareness Seminar', 'Learn about the latest cybersecurity threats and how to protect yourself and your organization.', '2025-06-05', '13:00:00', '17:00:00', 'Virtual (Zoom)', 100, 'virtual', 1, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(3, 'Government Website Development Workshop', 'Hands-on workshop on creating and maintaining government websites using modern web technologies.', '2025-06-15', '09:00:00', '16:00:00', 'DICT Training Room B, Quezon City', 15, 'in-person', 1, '2025-05-14 07:00:00', '2025-05-14 07:00:00'),
(4, 'Data Privacy and Protection Training', 'Comprehensive training on data privacy laws, regulations, and implementation in government agencies.', '2025-06-20', '09:00:00', '12:00:00', 'Virtual (Zoom)', 50, 'virtual', 1, '2025-05-14 07:00:00', '2025-05-14 07:00:00');

--
-- Dumping data for table `training_registrations`
--

INSERT INTO `training_registrations` (`id`, `event_id`, `participant_name`, `email`, `phone`, `agency`, `position`, `registration_date`, `attendance_status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Juan Dela Cruz', 'juan.delacruz@quezoncity.gov.ph', '09234567890', 'City Government of Quezon City', 'IT Officer', '2025-05-14 08:15:00', 'Confirmed', '2025-05-14 08:15:00', '2025-05-14 08:15:00'),
(2, 1, 'Elena Magtanggol', 'elena.magtanggol@dost.gov.ph', '09567891234', 'DOST Region IV-A', 'Science Research Specialist', '2025-05-14 09:30:00', 'Registered', '2025-05-14 09:30:00', '2025-05-14 09:30:00'),
(3, 2, 'Maria Santos', 'maria.santos@deped.gov.ph', '09187654321', 'DepEd Zamboanga', 'ICT Coordinator', '2025-05-14 10:45:00', 'Confirmed', '2025-05-14 10:45:00', '2025-05-14 10:45:00'),
(4, 3, 'Francis Pepito', 'francis.pepito@dict.gov.ph', '09765432109', 'DICT', 'Web Developer', '2025-05-14 13:20:00', 'Confirmed', '2025-05-14 13:20:00', '2025-05-14 13:20:00');

----------------------------------------------------------

--
-- Table structure for table `services_provided`
--

CREATE TABLE `services_provided` (
  `id` int(11) NOT NULL,
  `region` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  `district` varchar(100) DEFAULT NULL,
  `municipality` varchar(100) NOT NULL,
  `client_name` varchar(100) NOT NULL,
  `agency` varchar(100) NOT NULL,
  `support_type` varchar(100) NOT NULL,
  `service_provided` varchar(255) NOT NULL,
  `support_details` text DEFAULT NULL,
  `date_requested` date NOT NULL,
  `date_assisted` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_resolved` timestamp NULL DEFAULT NULL,
  `assisted_by` varchar(100) NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services_provided`
--

INSERT INTO `services_provided` (`id`, `region`, `province`, `district`, `municipality`, `client_name`, `agency`, `support_type`, `service_provided`, `support_details`, `date_requested`, `date_assisted`, `date_resolved`, `assisted_by`, `remarks`, `created_at`, `updated_at`) VALUES
(4, 'Region V', 'Zamboanga Del Norte', 'District 1', 'Zamboanga City', 'Francis Pepito', 'DICT', 'Lending of ICT Equipment', '&amp;amp;quot;Provided technical assistance for the issuance of VaxCerts  through the eGovPH app&amp;amp;quot;', 'asdadsadsa', '2025-05-14', '2025-05-14 07:04:00', NULL, 'System', 'done', '2025-05-14 07:04:37', '2025-05-14 07:06:20');

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

INSERT INTO `service_types` (`id`, `service_code`, `service_name`, `description`, `is_active`) VALUES
(1, 'WIFI', 'WiFi Installation/Configuration', 'Setup and configuration of WiFi networks for government offices and public spaces', 1),
(2, 'GOVNET', 'GovNet Installation/Maintenance', 'Installation and maintenance of government network infrastructure', 1),
(3, 'IBPLS', 'iBPLS Virtual Assistance', 'Virtual assistance for integrated Business Permits and Licensing System', 1),
(4, 'PNPKI', 'PNPKI Tech Support', 'Technical support for Philippine National Public Key Infrastructure', 1),
(5, 'EQUIP', 'ICT Equipment Lending', 'Lending of ICT equipment for government agencies and events', 1),
(6, 'CYBER', 'Cybersecurity Support', 'Assistance with cybersecurity and data privacy concerns', 1),
(7, 'OFFICE', 'Use of Office Facility', 'Use of DICT office facilities for meetings and events', 1),
(8, 'SPACE', 'Use of Space, ICT Equipment & Internet Connectivity', 'Provision of space, equipment and connectivity', 1),
(9, 'SIM', 'Sim Card Registration', 'Assistance with SIM card registration', 1),
(10, 'COMMS', 'Comms-related concern', 'Support for communications-related issues', 1),
(11, 'TECH', 'Provision of Technical Personnel', 'Provision of technical personnel for events and projects', 1),
(12, 'OTHER', 'Other Services', 'Other ICT-related services not listed above', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tech_support_requests`
--

CREATE TABLE `tech_support_requests` (
  `id` int(11) NOT NULL,
  `client_name` varchar(100) NOT NULL,
  `agency` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `region_id` int(11) NOT NULL,
  `province_id` int(11) NOT NULL,
  `district_id` int(11) DEFAULT NULL,
  `municipality_id` int(11) NOT NULL,
  `support_type` varchar(100) NOT NULL,
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
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','staff') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `region_id` (`region_id`),
  ADD KEY `province_id` (`province_id`),
  ADD KEY `district_id` (`district_id`),
  ADD KEY `municipality_id` (`municipality_id`);

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
-- Indexes for table `services_provided`
--
ALTER TABLE `services_provided`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `districts`
--
ALTER TABLE `districts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `municipalities`
--
ALTER TABLE `municipalities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `provinces`
--
ALTER TABLE `provinces`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `regions`
--
ALTER TABLE `regions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `services_provided`
--
ALTER TABLE `services_provided`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `service_requests`
--
ALTER TABLE `service_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_types`
--
ALTER TABLE `service_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tech_support_requests`
--
ALTER TABLE `tech_support_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `training_events`
--
ALTER TABLE `training_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `training_registrations`
--
ALTER TABLE `training_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `clients_ibfk_1` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`),
  ADD CONSTRAINT `clients_ibfk_2` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`),
  ADD CONSTRAINT `clients_ibfk_3` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`),
  ADD CONSTRAINT `clients_ibfk_4` FOREIGN KEY (`municipality_id`) REFERENCES `municipalities` (`id`);

--
-- Constraints for table `districts`
--
ALTER TABLE `districts`
  ADD CONSTRAINT `districts_ibfk_1` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`);

--
-- Constraints for table `municipalities`
--
ALTER TABLE `municipalities`
  ADD CONSTRAINT `municipalities_ibfk_1` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`),
  ADD CONSTRAINT `municipalities_ibfk_2` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`);

--
-- Constraints for table `provinces`
--
ALTER TABLE `provinces`
  ADD CONSTRAINT `provinces_ibfk_1` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`);

--
-- Constraints for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD CONSTRAINT `service_requests_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  ADD CONSTRAINT `service_requests_ibfk_2` FOREIGN KEY (`service_type_id`) REFERENCES `service_types` (`id`),
  ADD CONSTRAINT `service_requests_ibfk_3` FOREIGN KEY (`assisted_by_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `tech_support_requests`
--
ALTER TABLE `tech_support_requests`
  ADD CONSTRAINT `tech_support_requests_ibfk_1` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`),
  ADD CONSTRAINT `tech_support_requests_ibfk_2` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`),
  ADD CONSTRAINT `tech_support_requests_ibfk_3` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`),
  ADD CONSTRAINT `tech_support_requests_ibfk_4` FOREIGN KEY (`municipality_id`) REFERENCES `municipalities` (`id`),
  ADD CONSTRAINT `tech_support_requests_ibfk_5` FOREIGN KEY (`assisted_by_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `training_registrations`
--
ALTER TABLE `training_registrations`
  ADD CONSTRAINT `training_registrations_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `training_events` (`id`);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
