-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 16, 2025 at 04:19 AM
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
-- Database: `annex_management_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `annex1_related_incidents`
--

CREATE TABLE `annex1_related_incidents` (
  `id` int(11) NOT NULL,
  `region` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `barangay` varchar(100) NOT NULL,
  `incident_type` enum('Landslide','Flooding','Fire','Earthquake','Storm Surge','Tsunami','Volcanic Eruption','Other') NOT NULL,
  `occurrence_date` datetime NOT NULL,
  `description` text DEFAULT NULL,
  `actions_taken` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `annex1_related_incidents`
--

INSERT INTO `annex1_related_incidents` (`id`, `region`, `province`, `city`, `barangay`, `incident_type`, `occurrence_date`, `description`, `actions_taken`, `remarks`, `created_by`, `created_at`, `updated_at`, `is_archived`) VALUES
(2, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 'Flooding', '2025-10-10 15:13:00', '', '', '', 1, '2025-10-10 07:13:38', '2025-10-13 00:09:22', 0),
(3, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Asassi', 'Flooding', '2025-10-12 17:20:00', 'dakkel danumen', 'The LDRRMO deployed emergency response teams to the affected area', '', 1, '2025-10-12 09:21:15', '2025-10-13 00:09:26', 0),
(5, 'REGION II', 'CAGAYAN', 'BAGGAO', 'MDRRMO-BAGGAO', 'Landslide', '2025-10-12 22:21:00', 'kkk', 'ok', 'ok', 1, '2025-10-12 14:22:01', '2025-10-13 00:10:00', 0),
(7, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 'Flooding', '2025-10-14 09:55:00', '', '', '', 6, '2025-10-14 01:55:18', '2025-10-14 01:56:04', 0),
(8, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 'Flooding', '2025-10-14 10:02:00', '', '', '', 6, '2025-10-14 02:02:14', '2025-10-15 01:53:39', 0);

-- --------------------------------------------------------

--
-- Table structure for table `annex4_damaged_houses`
--

CREATE TABLE `annex4_damaged_houses` (
  `id` int(11) NOT NULL,
  `region` varchar(100) NOT NULL DEFAULT 'REGION II',
  `province` varchar(100) NOT NULL DEFAULT 'CAGAYAN',
  `city` varchar(100) NOT NULL DEFAULT 'BAGGAO',
  `barangay` varchar(100) NOT NULL,
  `totally_damaged` int(11) DEFAULT 0,
  `partially_damaged` int(11) DEFAULT 0,
  `total_damaged` int(11) DEFAULT 0,
  `cost` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `annex4_damaged_houses`
--

INSERT INTO `annex4_damaged_houses` (`id`, `region`, `province`, `city`, `barangay`, `totally_damaged`, `partially_damaged`, `total_damaged`, `cost`, `remarks`, `created_by`, `created_at`, `updated_at`, `is_archived`) VALUES
(2, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 10, 17, 27, '54000', '', 6, '2025-10-12 12:18:35', '2025-10-12 12:51:56', 0),
(3, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 5, 5, 10, '54000', '', 6, '2025-10-12 14:53:15', '2025-10-13 00:37:07', 0),
(4, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 23, 45, 68, '100000', '', 6, '2025-10-14 02:17:41', '2025-10-14 02:17:41', 0);

--
-- Triggers `annex4_damaged_houses`
--
DELIMITER $$
CREATE TRIGGER `annex4_before_insert` BEFORE INSERT ON `annex4_damaged_houses` FOR EACH ROW BEGIN
    SET NEW.total_damaged = NEW.totally_damaged + NEW.partially_damaged;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `annex4_before_update` BEFORE UPDATE ON `annex4_damaged_houses` FOR EACH ROW BEGIN
    -- Always ensure total_damaged equals the sum
    SET NEW.total_damaged = NEW.totally_damaged + NEW.partially_damaged;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `annex5_agriculture_damage`
--

CREATE TABLE `annex5_agriculture_damage` (
  `id` int(11) NOT NULL,
  `region` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `barangay` varchar(100) NOT NULL,
  `classification` enum('Crops','Livestock and Poultry','Fisheries','Agricultural Infrastructure','Machineries and Equipment') NOT NULL,
  `type` varchar(100) NOT NULL,
  `affected_people` int(11) DEFAULT 0,
  `damage_value` decimal(15,2) DEFAULT 0.00,
  `no_recovery_area` decimal(10,2) DEFAULT 0.00,
  `with_recovery_area` decimal(10,2) DEFAULT 0.00,
  `total_crop_area` decimal(10,2) DEFAULT 0.00,
  `production_loss_volume` decimal(10,2) DEFAULT 0.00,
  `animal_heads` int(11) DEFAULT 0,
  `fisheries_details` text DEFAULT NULL,
  `totally_damaged` int(11) DEFAULT 0,
  `partially_damaged` int(11) DEFAULT 0,
  `total_damaged` int(11) DEFAULT 0,
  `is_archived` tinyint(1) DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `annex5_agriculture_damage`
--

INSERT INTO `annex5_agriculture_damage` (`id`, `region`, `province`, `city`, `barangay`, `classification`, `type`, `affected_people`, `damage_value`, `no_recovery_area`, `with_recovery_area`, `total_crop_area`, `production_loss_volume`, `animal_heads`, `fisheries_details`, `totally_damaged`, `partially_damaged`, `total_damaged`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'REGION II', 'CAGAYAN', 'BAGGAO', 'asassi', 'Fisheries', '', 1, 5000.00, 0.00, 0.00, 0.00, 0.00, 0, '', 0, 0, 0, 0, 1, '2025-10-09 19:54:34', '2025-10-09 19:54:34'),
(2, 'Region II', 'Cagayan', 'Baggao', 'Alba', 'Crops', 'Corn', 15, 30000.00, 20.00, 10.00, 30.00, 0.00, 0, NULL, 0, 0, 0, 0, 6, '2025-10-14 03:43:07', '2025-10-14 05:09:05'),
(3, 'Region II', 'Cagayan', 'Baggao', 'Alba', 'Livestock and Poultry', 'Livestock', 20, 12000.00, 0.00, 0.00, 0.00, 0.00, 20, NULL, 0, 0, 0, 0, 6, '2025-10-14 05:10:23', '2025-10-14 05:10:23'),
(4, 'Region II', 'Cagayan', 'Baggao', 'Alba', 'Livestock and Poultry', 'Poultry', 12, 12000.00, 0.00, 0.00, 0.00, 0.00, 20, NULL, 0, 0, 0, 0, 6, '2025-10-14 05:11:28', '2025-10-14 05:11:28'),
(6, 'Region II', 'Cagayan', 'Baggao', 'Alba', 'Crops', 'Rice', 20, 50000.00, 12.00, 6.00, 18.00, 300000.00, NULL, '', NULL, NULL, NULL, 0, 6, '2025-10-14 07:14:11', '2025-10-14 07:15:58'),
(7, 'Region II', 'Cagayan', 'Baggao', 'Alba', 'Crops', 'Rice', 20, 50000.00, 20.00, 30.00, 50.00, 120000.00, 0, NULL, 0, 0, 0, 0, 6, '2025-10-14 07:29:57', '2025-10-14 07:29:57'),
(8, 'Region II', 'Cagayan', 'Baggao', 'Alba', 'Crops', 'Corn', 20, 30000.00, 20.00, 30.00, 50.00, 120000.00, NULL, '', NULL, NULL, NULL, 0, 6, '2025-10-14 07:32:07', '2025-10-15 01:55:14'),
(9, 'Region II', 'Cagayan', 'Baggao', 'Alba', 'Crops', 'Livestock', 20, 20.00, 20.00, 20.00, 40.00, 200000.00, NULL, '', NULL, NULL, NULL, 0, 6, '2025-10-14 07:39:48', '2025-10-15 01:54:47');

-- --------------------------------------------------------

--
-- Table structure for table `annex8_road_bridge_status`
--

CREATE TABLE `annex8_road_bridge_status` (
  `id` int(11) NOT NULL,
  `region` varchar(100) NOT NULL DEFAULT 'REGION II',
  `province` varchar(100) NOT NULL DEFAULT 'CAGAYAN',
  `city` varchar(100) NOT NULL DEFAULT 'BAGGAO',
  `barangay` varchar(100) NOT NULL,
  `type` enum('Road','Bridge') NOT NULL,
  `classification` enum('National','Provincial','City/Municipal','Barangay') NOT NULL,
  `road_section` varchar(255) NOT NULL,
  `status` enum('Passable','Not Passable','Passable to Heavy Vehicles Only','Passable to Light Vehicles Only') NOT NULL,
  `date_not_passable` datetime DEFAULT NULL,
  `date_passable` datetime DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `annex8_road_bridge_status`
--

INSERT INTO `annex8_road_bridge_status` (`id`, `region`, `province`, `city`, `barangay`, `type`, `classification`, `road_section`, `status`, `date_not_passable`, `date_passable`, `remarks`, `image_path`, `created_by`, `created_at`, `updated_at`, `is_archived`) VALUES
(1, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 'Road', 'City/Municipal', 'TAYTAY BRIDGE', 'Passable to Light Vehicles Only', '2025-10-13 09:01:00', NULL, '', NULL, 6, '2025-10-13 01:01:39', '2025-10-13 01:05:47', 1),
(2, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 'Road', 'City/Municipal', 'TAYTAY BRIDGE', 'Not Passable', '2025-10-13 09:08:00', NULL, '', NULL, 6, '2025-10-13 01:08:23', '2025-10-13 01:37:06', 1),
(3, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 'Road', 'Provincial', 'TAYTAY BRIDGE', 'Passable to Heavy Vehicles Only', '2025-10-13 09:09:00', NULL, '', 'annex8/68ec514bb1e47_20251013_090931.png', 6, '2025-10-13 01:09:31', '2025-10-13 01:47:44', 0),
(4, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 'Road', 'City/Municipal', 'TAYTAY BRIDGE', 'Passable', '2025-10-13 09:19:00', '2025-10-13 18:30:00', '', 'annex8/68ec54aa8101f_20251013_092354.png', 6, '2025-10-13 01:19:46', '2025-10-14 01:08:42', 0),
(5, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 'Road', 'National', 'TAYTAY BRIDGE', 'Passable to Light Vehicles Only', '2025-10-14 08:35:00', NULL, '', 'annex8/68ed9ac261156_20251014_083514.jpg', 6, '2025-10-14 00:35:14', '2025-10-14 00:35:14', 0),
(6, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 'Road', 'City/Municipal', 'TAYTAY BRIDGE', 'Passable to Light Vehicles Only', '2025-10-14 08:35:00', '2025-10-14 17:00:00', '', 'annex8/68ed9af402663_20251014_083604.jpg', 6, '2025-10-14 00:36:04', '2025-10-14 00:37:07', 0),
(7, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 'Road', 'National', 'MASICAL BRIDGE ZONE 6', 'Passable', '2025-10-15 08:39:00', NULL, '', 'annex8/68ed9bc820819_20251014_083936.jpg', 6, '2025-10-14 00:39:36', '2025-10-15 04:54:06', 0);

-- --------------------------------------------------------

--
-- Table structure for table `annex9_power_supply`
--

CREATE TABLE `annex9_power_supply` (
  `id` int(11) NOT NULL,
  `region` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `barangay` varchar(100) NOT NULL,
  `service_provider` varchar(200) NOT NULL,
  `interruption_datetime` datetime NOT NULL,
  `restored_datetime` datetime DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `is_archived` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `annex9_power_supply`
--

INSERT INTO `annex9_power_supply` (`id`, `region`, `province`, `city`, `barangay`, `service_provider`, `interruption_datetime`, `restored_datetime`, `remarks`, `image_path`, `created_by`, `is_archived`, `created_at`, `updated_at`) VALUES
(1, 'Region II', 'Cagayan', 'Baggao', 'Alba', 'CAGELCO', '2025-10-15 15:43:00', '2025-10-15 15:44:00', NULL, '68f04141b2852_20251016_085009_flood-pictures-9g7ob3wnw4p1ark5.jpg', 6, 0, '2025-10-15 07:43:30', '2025-10-16 00:50:09'),
(2, 'Region II', 'Cagayan', 'Baggao', 'Alba', 'CAGELCO', '2025-10-15 15:44:00', '2025-10-15 16:02:00', NULL, NULL, 6, 0, '2025-10-15 07:44:34', '2025-10-16 01:10:44'),
(4, 'Region II', 'Cagayan', 'Baggao', 'Alba', 'CAGELCO', '2025-10-14 09:09:00', '2025-10-16 09:42:00', NULL, '68f04d6fa9ac1_20251016_094207_flood-pictures-9g7ob3wnw4p1ark5.jpg', 6, 0, '2025-10-16 01:09:54', '2025-10-16 01:42:07'),
(5, 'Region II', 'Cagayan', 'Baggao', 'Asassi', 'CAGELCO', '2025-10-16 09:13:00', '2025-10-16 09:38:00', NULL, '68f04e0ad9d3a_20251016_094442_flood-pictures-9g7ob3wnw4p1ark5.jpg', 5, 0, '2025-10-16 01:13:58', '2025-10-16 01:44:42'),
(6, 'Region II', 'Cagayan', 'Baggao', 'Asassi', 'CAGELCO', '2025-10-16 09:47:00', NULL, NULL, NULL, 5, 0, '2025-10-16 01:47:13', '2025-10-16 02:12:45'),
(7, 'Region II', 'Cagayan', 'Baggao', 'Asassi', 'CAGELCO', '2025-10-16 10:11:00', NULL, NULL, '68f05450c80f7_20251016_101128_flood-pictures-9g7ob3wnw4p1ark5.jpg', 5, 0, '2025-10-16 02:11:28', '2025-10-16 02:11:28');

-- --------------------------------------------------------

--
-- Table structure for table `annex11_communication_lines`
--

CREATE TABLE `annex11_communication_lines` (
  `id` int(11) NOT NULL,
  `region` varchar(100) NOT NULL DEFAULT 'REGION II',
  `province` varchar(100) NOT NULL DEFAULT 'CAGAYAN',
  `city` varchar(100) NOT NULL DEFAULT 'BAGGAO',
  `barangay` varchar(100) NOT NULL,
  `telecom_provider` enum('PLDT','Globe','Smart','DITO','Other') NOT NULL,
  `interruption_date` datetime NOT NULL,
  `restored_date` datetime DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `is_archived` tinyint(1) DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `annex11_communication_lines`
--

INSERT INTO `annex11_communication_lines` (`id`, `region`, `province`, `city`, `barangay`, `telecom_provider`, `interruption_date`, `restored_date`, `remarks`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Region II', 'Cagayan', 'Baggao', 'Alba', 'Globe', '2025-10-14 16:21:00', '2025-10-14 16:34:00', '', 0, 6, '2025-10-14 08:21:10', '2025-10-15 01:52:45');

-- --------------------------------------------------------

--
-- Table structure for table `annex14_suspension_work`
--

CREATE TABLE `annex14_suspension_work` (
  `id` int(11) NOT NULL,
  `region` varchar(100) NOT NULL DEFAULT 'REGION II',
  `province` varchar(100) NOT NULL DEFAULT 'CAGAYAN',
  `city` varchar(100) NOT NULL DEFAULT 'BAGGAO',
  `barangay` varchar(100) NOT NULL,
  `type` enum('Government','Private','All') NOT NULL,
  `suspension_date` datetime NOT NULL,
  `resumption_date` datetime DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `annex14_suspension_work`
--

INSERT INTO `annex14_suspension_work` (`id`, `region`, `province`, `city`, `barangay`, `type`, `suspension_date`, `resumption_date`, `remarks`, `created_by`, `created_at`, `updated_at`, `is_archived`) VALUES
(1, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 'Government', '2025-10-13 16:43:00', '2025-10-14 16:43:00', NULL, 6, '2025-10-13 08:43:57', '2025-10-13 08:43:57', 0),
(2, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 'Government', '2025-10-13 16:48:00', NULL, NULL, 6, '2025-10-13 08:48:43', '2025-10-13 08:48:43', 0),
(3, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 'Private', '2025-10-13 16:49:00', '2025-10-14 21:49:00', 'TESTING', 6, '2025-10-13 08:50:09', '2025-10-13 08:50:09', 0),
(4, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 'Private', '2025-10-13 16:53:00', NULL, NULL, 6, '2025-10-13 08:53:26', '2025-10-13 08:53:26', 0);

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

--
-- Table structure for table `barangay_locks`
--

CREATE TABLE `barangay_locks` (
  `id` int(11) NOT NULL,
  `barangay` varchar(100) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(100) NOT NULL,
  `lock_time` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barangay_locks`
--

INSERT INTO `barangay_locks` (`id`, `barangay`, `admin_id`, `admin_name`, `lock_time`, `created_at`) VALUES
(23, 'Agaman Norte', 1, 'System Administrator', '2025-10-16 09:49:11', '2025-10-16 01:49:11');

-- --------------------------------------------------------

--
-- Table structure for table `rate_limits`
--

CREATE TABLE `rate_limits` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `attempt_time` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rate_limits`
--

INSERT INTO `rate_limits` (`id`, `user_id`, `action`, `attempt_time`, `ip_address`) VALUES
(329, 5, 'annex9_save', 1760577238, '::1'),
(330, 5, 'annex9_save', 1760579233, '::1'),
(331, 5, 'annex9_submission', 1760579799, '::1'),
(332, 5, 'annex9_save', 1760580688, '::1');

-- --------------------------------------------------------

--
-- Table structure for table `security_logs`
--

CREATE TABLE `security_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `security_logs`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `barangay` varchar(100) NOT NULL,
  `contact_number` varchar(15) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_role` enum('user','admin') DEFAULT 'user',
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `region` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(64) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_sessions`
--

--
-- Indexes for dumped tables
--

--
-- Indexes for table `annex1_related_incidents`
--
ALTER TABLE `annex1_related_incidents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_location` (`region`,`province`,`city`,`barangay`),
  ADD KEY `idx_incident_type` (`incident_type`),
  ADD KEY `idx_occurrence_date` (`occurrence_date`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_annex1_archived` (`is_archived`),
  ADD KEY `idx_annex1_created_by` (`created_by`);

--
-- Indexes for table `annex4_damaged_houses`
--
ALTER TABLE `annex4_damaged_houses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_location` (`region`,`province`,`city`,`barangay`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_annex4_created_by` (`created_by`),
  ADD KEY `idx_annex4_archived` (`is_archived`);

--
-- Indexes for table `annex5_agriculture_damage`
--
ALTER TABLE `annex5_agriculture_damage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_annex5_created_by` (`created_by`),
  ADD KEY `idx_annex5_barangay` (`barangay`),
  ADD KEY `idx_annex5_classification` (`classification`),
  ADD KEY `idx_annex5_created_at` (`created_at`),
  ADD KEY `idx_annex5_archived` (`is_archived`);

--
-- Indexes for table `annex8_road_bridge_status`
--
ALTER TABLE `annex8_road_bridge_status`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_location` (`region`,`province`,`city`,`barangay`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_annex8_archived` (`is_archived`);

--
-- Indexes for table `annex9_power_supply`
--
ALTER TABLE `annex9_power_supply`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `annex11_communication_lines`
--
ALTER TABLE `annex11_communication_lines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_annex11_location` (`region`,`province`,`city`,`barangay`),
  ADD KEY `idx_annex11_telecom` (`telecom_provider`),
  ADD KEY `idx_annex11_interruption_date` (`interruption_date`),
  ADD KEY `idx_annex11_restored_date` (`restored_date`),
  ADD KEY `idx_annex11_created_by` (`created_by`),
  ADD KEY `idx_annex11_created_at` (`created_at`),
  ADD KEY `idx_annex11_archived` (`is_archived`),
  ADD KEY `idx_annex11_barangay_telecom` (`barangay`,`telecom_provider`);

--
-- Indexes for table `annex14_suspension_work`
--
ALTER TABLE `annex14_suspension_work`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_location` (`region`,`province`,`city`,`barangay`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_dates` (`suspension_date`,`resumption_date`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_annex14_archived` (`is_archived`),
  ADD KEY `idx_annex14_barangay_type` (`barangay`,`type`),
  ADD KEY `idx_annex14_suspension_range` (`suspension_date`,`resumption_date`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `barangay_locks`
--
ALTER TABLE `barangay_locks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barangay` (`barangay`),
  ADD UNIQUE KEY `admin_id` (`admin_id`),
  ADD KEY `lock_time` (`lock_time`);

--
-- Indexes for table `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_action` (`user_id`,`action`),
  ADD KEY `idx_time` (`attempt_time`);

--
-- Indexes for table `security_logs`
--
ALTER TABLE `security_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_event_type` (`event_type`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_users_username` (`username`),
  ADD KEY `idx_users_barangay` (`barangay`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sessions_user_id` (`user_id`),
  ADD KEY `idx_sessions_token` (`session_token`),
  ADD KEY `idx_sessions_is_active` (`is_active`),
  ADD KEY `idx_sessions_last_activity` (`last_activity`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `annex1_related_incidents`
--
ALTER TABLE `annex1_related_incidents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `annex4_damaged_houses`
--
ALTER TABLE `annex4_damaged_houses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `annex5_agriculture_damage`
--
ALTER TABLE `annex5_agriculture_damage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `annex8_road_bridge_status`
--
ALTER TABLE `annex8_road_bridge_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `annex9_power_supply`
--
ALTER TABLE `annex9_power_supply`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `annex11_communication_lines`
--
ALTER TABLE `annex11_communication_lines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `annex14_suspension_work`
--
ALTER TABLE `annex14_suspension_work`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=764;

--
-- AUTO_INCREMENT for table `barangay_locks`
--
ALTER TABLE `barangay_locks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=333;

--
-- AUTO_INCREMENT for table `security_logs`
--
ALTER TABLE `security_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=170;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `annex1_related_incidents`
--
ALTER TABLE `annex1_related_incidents`
  ADD CONSTRAINT `annex1_related_incidents_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `annex4_damaged_houses`
--
ALTER TABLE `annex4_damaged_houses`
  ADD CONSTRAINT `annex4_damaged_houses_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `annex8_road_bridge_status`
--
ALTER TABLE `annex8_road_bridge_status`
  ADD CONSTRAINT `annex8_road_bridge_status_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `annex9_power_supply`
--
ALTER TABLE `annex9_power_supply`
  ADD CONSTRAINT `annex9_power_supply_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `annex14_suspension_work`
--
ALTER TABLE `annex14_suspension_work`
  ADD CONSTRAINT `annex14_suspension_work_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `barangay_locks`
--
ALTER TABLE `barangay_locks`
  ADD CONSTRAINT `barangay_locks_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
