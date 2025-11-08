-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 14, 2025 at 10:51 AM
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
(8, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 'Landslide', '2025-10-14 10:02:00', '', '', '', 6, '2025-10-14 02:02:14', '2025-10-14 02:02:56', 0);

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
(8, 'Region II', 'Cagayan', 'Baggao', 'Alba', 'Crops', 'Rice', 20, 30000.00, 20.00, 30.00, 50.00, 120000.00, 0, NULL, 0, 0, 0, 0, 6, '2025-10-14 07:32:07', '2025-10-14 07:32:07'),
(9, 'Region II', 'Cagayan', 'Baggao', 'Alba', 'Crops', 'Rice', 20, 20.00, 20.00, 20.00, 40.00, 200000.00, NULL, '', NULL, NULL, NULL, 0, 6, '2025-10-14 07:39:48', '2025-10-14 08:12:09');

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
(7, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 'Road', 'National', 'MASICAL BRIDGE ZONE 6', 'Passable to Heavy Vehicles Only', '2025-10-14 08:39:00', NULL, '', 'annex8/68ed9bc820819_20251014_083936.jpg', 6, '2025-10-14 00:39:36', '2025-10-14 01:38:04', 0);

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
(1, 'Region II', 'Cagayan', 'Baggao', 'Alba', 'PLDT', '2025-10-14 16:21:00', '2025-10-14 16:34:00', '', 0, 6, '2025-10-14 08:21:10', '2025-10-14 08:49:21');

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

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 6, 'annex4_submission', 'Submitted damaged houses report for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-10-12 14:53:15'),
(2, 6, 'annex8_archive', 'Archived record #2 for Alba: Road - TAYTAY BRIDGE', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 01:37:06'),
(3, 6, 'annex8_update', 'Updated record #4 for Alba. Changes: No significant changes', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 01:45:38'),
(4, 6, 'annex8_view_archived', 'Viewed archived record #2 for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 01:47:20'),
(5, 6, 'annex8_archive', 'Archived record #3 for Alba: Road - TAYTAY BRIDGE', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 01:47:34'),
(6, 6, 'annex8_view_archived', 'Viewed archived record #2 for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 01:47:40'),
(7, 6, 'annex8_restore', 'Restored record #3 for Alba: Road - TAYTAY BRIDGE', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 01:47:44'),
(8, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 02:20:52'),
(9, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 02:21:05'),
(10, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 02:22:37'),
(11, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 02:22:43'),
(12, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 02:22:43'),
(13, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 02:22:43'),
(14, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 02:22:43'),
(15, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 02:22:43'),
(16, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 02:23:03'),
(17, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 02:23:44'),
(18, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 02:23:45'),
(19, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 02:25:27'),
(20, 6, 'annex8_update', 'Updated record #4 for Alba. Changes: Status: Passable to Heavy Vehicles Only → Not Passable', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 02:28:54'),
(21, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 02:41:14'),
(22, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 02:41:38'),
(23, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 02:41:47'),
(24, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 02:59:20'),
(25, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:01:00'),
(26, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:02:33'),
(27, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:04:33'),
(28, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:04:35'),
(29, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:04:36'),
(30, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:04:38'),
(31, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:04:48'),
(32, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:10:47'),
(33, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:11:10'),
(34, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:14:05'),
(35, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:14:07'),
(36, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:15:31'),
(37, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:17:17'),
(38, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:17:18'),
(39, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:20:50'),
(40, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:20:53'),
(41, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:28:18'),
(42, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:30:56'),
(43, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:32:09'),
(44, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:36:42'),
(45, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:37:39'),
(46, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:38:37'),
(47, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:38:41'),
(48, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:38:43'),
(49, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:38:45'),
(50, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:38:47'),
(51, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:38:49'),
(52, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:38:50'),
(53, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:39:01'),
(54, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:39:05'),
(55, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:39:09'),
(56, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:39:10'),
(57, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:40:06'),
(58, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:41:10'),
(59, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:44:09'),
(60, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:44:32'),
(61, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:44:55'),
(62, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:45:33'),
(63, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:45:41'),
(64, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:46:30'),
(65, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:48:16'),
(66, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:49:32'),
(67, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:53:21'),
(68, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:53:30'),
(69, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:53:32'),
(70, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:54:19'),
(71, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:54:25'),
(72, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:54:27'),
(73, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:54:29'),
(74, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:54:30'),
(75, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:54:32'),
(76, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:56:41'),
(77, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:56:53'),
(78, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:56:59'),
(79, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:57:14'),
(80, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:57:19'),
(81, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 05:21:27'),
(82, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 05:37:27'),
(83, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 05:42:28'),
(84, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 05:42:46'),
(85, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 05:43:40'),
(86, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 05:44:20'),
(87, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 05:53:36'),
(88, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 05:53:39'),
(89, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 05:55:36'),
(90, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 05:56:10'),
(91, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 05:56:15'),
(92, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 05:56:39'),
(93, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 05:56:42'),
(94, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 05:57:31'),
(95, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 05:57:49'),
(96, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 06:02:24'),
(97, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 06:03:02'),
(98, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 06:04:59'),
(99, 6, 'submit_annex14', 'Saved 1 work suspension entries', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 06:19:08'),
(100, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 06:20:17'),
(101, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 06:20:24'),
(102, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 06:20:38'),
(103, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 06:20:48'),
(104, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 06:21:01'),
(105, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 06:21:06'),
(106, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 06:53:09'),
(107, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:11:35'),
(108, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:11:38'),
(109, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:12:14'),
(110, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:12:16'),
(111, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:12:17'),
(112, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:12:21'),
(113, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:15:30'),
(114, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:15:33'),
(115, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:15:34'),
(116, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:15:44'),
(117, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:15:46'),
(118, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:15:59'),
(119, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:16:01'),
(120, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:16:14'),
(121, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:16:16'),
(122, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:16:16'),
(123, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:16:17'),
(124, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:17:08'),
(125, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:17:15'),
(126, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:25:21'),
(127, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:26:47'),
(128, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:26:48'),
(129, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:26:50'),
(130, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:26:56'),
(131, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:36:51'),
(132, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:36:55'),
(133, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:36:57'),
(134, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:49:45'),
(135, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:49:49'),
(136, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:49:51'),
(137, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:49:53'),
(138, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:49:57'),
(139, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:50:01'),
(140, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:50:11'),
(141, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:57:17'),
(142, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:57:19'),
(143, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:57:54'),
(144, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 07:59:24'),
(145, 6, 'annex14_form_access', 'Accessed Annex 14 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 08:16:32'),
(146, 6, 'annex14_submission', 'Submitted suspension work report for Alba: Government', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 08:43:57'),
(147, 6, 'annex14_submission', 'Submitted suspension work report for Alba: Government', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 08:48:43'),
(148, 6, 'annex14_submission', 'Submitted suspension work report for Alba: Private', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 08:50:09'),
(149, 6, 'annex14_submission', 'Submitted suspension work report for Alba: Private', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 08:53:26'),
(150, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 00:33:49'),
(151, 6, 'annex8_submission', 'Submitted road/bridge status for Alba: Road - TAYTAY BRIDGE (Passable to Light Vehicles Only)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 00:35:14'),
(152, 6, 'annex8_batch_submission', 'Successfully submitted 1 road/bridge status reports for barangays: Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 00:35:14'),
(153, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 00:35:14'),
(154, 6, 'annex8_submission', 'Submitted road/bridge status for Alba: Road - TAYTAY BRIDGE (Passable to Light Vehicles Only)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 00:36:04'),
(155, 6, 'annex8_batch_submission', 'Successfully submitted 1 road/bridge status reports for barangays: Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 00:36:04'),
(156, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 00:36:04'),
(157, 6, 'annex8_update', 'Updated record #6 for Alba. Changes: No significant changes', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 00:37:07'),
(158, 6, 'annex8_submission', 'Submitted road/bridge status for Alba: Road - MASICAL BRIDGE ZONE 6 (Passable to Light Vehicles Only)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 00:39:36'),
(159, 6, 'annex8_batch_submission', 'Successfully submitted 1 road/bridge status reports for barangays: Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 00:39:36'),
(160, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 00:39:36'),
(161, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 01:04:11'),
(162, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 01:05:41'),
(163, 6, 'annex8_update', 'Updated record #4 for Alba. Changes: Status: Not Passable → Passable', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 01:08:42'),
(164, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 01:37:38'),
(165, 6, 'annex8_update', 'Updated record #7 (Alba). Changes: Status: Passable to Light Vehicles Only → Passable to Heavy Vehicles Only', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 01:38:04'),
(166, 6, 'annex1_edit_access', 'Accessed edit form for Annex1 record ID 7 (Landslide) in barangay Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 01:55:56'),
(167, 6, 'annex1_update', 'Updated record #7 (Flooding) - Incident Type: Landslide → Flooding, Occurrence Date updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 01:56:04'),
(168, 6, 'annex1_submission', 'Submitted incident report for Alba: Flooding', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:02:14'),
(169, 6, 'annex1_batch_submission', 'Successfully saved 1 related incident report(s)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:02:14'),
(170, 6, 'annex1_edit_access', 'Accessed edit form for Annex1 record ID 8 (Flooding) in barangay Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:02:51'),
(171, 6, 'annex1_update', 'Updated record #8 (Landslide) - Incident Type: Flooding → Landslide, Occurrence Date updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:02:56'),
(172, 6, 'annex4_submission', 'Submitted damaged houses report for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:17:41'),
(173, 6, 'annex4_submission', 'Submitted damaged houses report for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:17:47'),
(174, 6, 'annex4_submission', 'Submitted damaged houses report for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:18:00'),
(175, 6, 'annex4_form_access', 'User accessed the Annex 4 Damaged Houses form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:27:49'),
(176, 6, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:29:02'),
(177, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:29:02'),
(178, 6, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:30:40'),
(179, 6, 'annex1_form_access', 'User accessed the Annex 1 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:31:26'),
(180, 6, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:33:25'),
(181, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:33:25'),
(182, 6, 'annex4_form_access', 'User accessed the Annex 4 Damaged Houses form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:33:28'),
(183, 6, 'annex1_form_access', 'User accessed the Annex 1 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:33:31'),
(184, 6, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:33:37'),
(185, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:33:37'),
(186, 6, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:34:38'),
(187, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:34:38'),
(188, 6, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:34:54'),
(189, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:34:54'),
(190, 6, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:35:03'),
(191, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:35:03'),
(192, 6, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:35:13'),
(193, 6, 'annex8_form_access', 'Accessed Annex 8 form for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:35:13'),
(194, 6, 'annex4_form_access', 'User accessed the Annex 4 Damaged Houses form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:35:18'),
(195, 6, 'annex4_form_access', 'User accessed the Annex 4 Damaged Houses form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:39:26'),
(196, 6, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 03:28:20'),
(197, 6, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 03:28:59'),
(198, 6, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 03:30:51'),
(199, 6, 'annex4_form_access', 'User accessed the Annex 4 Damaged Houses form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 03:31:09'),
(200, 6, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 03:36:37'),
(201, 6, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 03:42:27'),
(202, 6, 'annex5_form_access', 'Accessed Annex 5 save handler', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 03:43:07'),
(203, 6, 'annex5_submission', 'Successfully submitted agriculture damage report with 1 entries for barangay: Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 03:43:07'),
(204, 6, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 03:43:07'),
(205, 6, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 03:43:11'),
(206, 6, 'annex5_archive', 'Archived record #2 for Alba: Crops - Corn', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 05:03:50'),
(207, 6, 'annex5_restore', 'Restored record #2 for Alba: Crops - Corn', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 05:09:05'),
(208, 6, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 05:10:00'),
(209, 6, 'annex5_form_access', 'Accessed Annex 5 save handler', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 05:10:23'),
(210, 6, 'annex5_submission', 'Successfully submitted agriculture damage report with 1 entries (IDs: 3) for barangay: Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 05:10:23'),
(211, 6, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 05:10:23'),
(212, 6, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 05:10:27'),
(213, 6, 'annex5_edit_form_access', 'Opened edit form for record #3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 05:10:36'),
(214, 6, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 05:11:04'),
(215, 6, 'annex5_form_access', 'Accessed Annex 5 save handler', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 05:11:28'),
(216, 6, 'annex5_submission', 'Successfully submitted agriculture damage report with 1 entries (IDs: 4) for barangay: Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 05:11:28'),
(217, 6, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 05:11:28'),
(218, 6, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 05:11:32'),
(219, 6, 'annex5_edit_form_access', 'Opened edit form for record #4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 05:11:42'),
(220, 6, 'annex5_view', 'Viewed agriculture damage record #4 for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 05:57:54'),
(221, 6, 'annex5_view', 'Viewed agriculture damage record #3 for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 05:58:26'),
(222, 6, 'annex5_view', 'Viewed agriculture damage record #4 for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:01:20'),
(223, 6, 'annex5_view', 'Viewed agriculture damage record #4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:03:06'),
(224, 6, 'annex5_edit_form_access', 'Opened edit form for record #4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:21:46'),
(225, 6, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:22:07'),
(226, 6, 'annex5_edit_form_access', 'Opened edit form for record #4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:24:16');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(227, 6, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:24:37'),
(228, 6, 'annex5_form_access', 'Accessed Annex 5 save handler', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:25:23'),
(229, 6, 'annex5_submission', 'Successfully submitted agriculture damage report with 1 entries (IDs: 5) for barangay: Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:25:23'),
(230, 6, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:25:23'),
(231, 6, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:26:57'),
(232, 6, 'annex5_view', 'Viewed agriculture damage record # for ', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:28:00'),
(233, 6, 'annex5_view', 'Viewed agriculture damage record #5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:28:00'),
(234, 6, 'annex5_edit_form_access', 'Opened edit form for record #5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:35:12'),
(235, 6, 'annex5_edit_form_access', 'Opened edit form for record #5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:41:03'),
(236, 6, 'annex5_edit_form_access', 'Opened edit form for record #5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:46:02'),
(237, 6, 'annex5_edit_form_access', 'Opened edit form for record #5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:50:34'),
(238, 6, 'annex5_edit_form_access', 'Opened edit form for record #5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:52:11'),
(239, 6, 'annex5_edit_form_access', 'Opened edit form for record #5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:52:23'),
(240, 6, 'annex5_archive', 'Archived record #5 for Alba: Fisheries - Fishing gears/paraphernalia', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:52:27'),
(241, 6, 'annex5_archived_access', 'Accessed Annex 5 archived records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:52:30'),
(242, 6, 'annex5_view', 'Viewed agriculture damage record # for ', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:52:35'),
(243, 6, 'annex5_view', 'Viewed agriculture damage record #5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:52:35'),
(244, 6, 'annex5_archived_access', 'Accessed Annex 5 archived records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:52:53'),
(245, 6, 'annex5_restore', 'Restored record #5 for Alba: Fisheries - Fishing gears/paraphernalia', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:52:53'),
(246, 6, 'annex5_archived_access', 'Accessed Annex 5 archived records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:52:53'),
(247, 6, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:57:59'),
(248, 6, 'annex5_edit_form_access', 'Opened edit form for record #5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:02:13'),
(249, 6, 'annex5_update', 'Updated record #5 for Alba: Machineries and Equipment - Facilities and equipment', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:02:28'),
(250, 6, 'annex5_update', 'Updated record #5 for Alba: Machineries and Equipment - Facilities and equipment', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:08:18'),
(251, 6, 'annex5_view', 'Viewed agriculture damage record # for ', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:08:33'),
(252, 6, 'annex5_view', 'Viewed agriculture damage record #5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:08:33'),
(253, 6, 'annex5_view', 'Viewed agriculture damage record # for ', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:08:44'),
(254, 6, 'annex5_view', 'Viewed agriculture damage record #4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:08:44'),
(255, 6, 'annex5_view', 'Viewed agriculture damage record #5 for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:13:06'),
(256, 6, 'annex5_edit_form_access', 'Opened edit form for record #5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:13:18'),
(257, 6, 'annex5_delete', 'Permanently deleted record #5 for Alba: Machineries and Equipment - Facilities and equipment', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:13:34'),
(258, 6, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:13:37'),
(259, 6, 'annex5_form_access', 'Accessed Annex 5 save handler', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:14:11'),
(260, 6, 'annex5_submission', 'Successfully submitted agriculture damage report with 1 entries for barangay: Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:14:11'),
(261, 6, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:14:11'),
(262, 6, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:15:02'),
(263, 6, 'annex5_edit_form_access', 'Opened edit form for record #6', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:15:10'),
(264, 6, 'annex5_update', 'Updated record #6 for Alba: Crops - Rice', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:15:14'),
(265, 6, 'annex5_archive', 'Archived record #6 for Alba: Crops - Rice', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:15:40'),
(266, 6, 'annex5_archived_access', 'Accessed Annex 5 archived records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:15:42'),
(267, 6, 'annex5_view', 'Viewed agriculture damage record #6 for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:15:44'),
(268, 6, 'annex5_archived_access', 'Accessed Annex 5 archived records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:15:58'),
(269, 6, 'annex5_restore', 'Restored record #6 for Alba: Crops - Rice', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:15:58'),
(270, 6, 'annex5_archived_access', 'Accessed Annex 5 archived records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:15:58'),
(271, 6, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:28:59'),
(272, 6, 'annex5_form_access', 'Accessed Annex 5 save handler', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:29:57'),
(273, 6, 'annex5_submission', 'Successfully submitted agriculture damage report with 1 entries (IDs: 7) for barangay: Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:29:57'),
(274, 6, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:29:57'),
(275, 6, 'annex5_archived_access', 'Accessed Annex 5 archived records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:30:42'),
(276, 6, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:31:33'),
(277, 6, 'annex5_form_access', 'Accessed Annex 5 save handler', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:32:07'),
(278, 6, 'annex5_submission', 'Successfully submitted agriculture damage report with 1 entries (IDs: 8) for barangay: Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:32:07'),
(279, 6, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:32:07'),
(280, 6, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:39:28'),
(281, 6, 'annex5_form_access', 'Accessed Annex 5 save handler', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:39:48'),
(282, 6, 'annex5_submission', 'Successfully submitted agriculture damage report with 1 entries (IDs: 9) for barangay: Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:39:48'),
(283, 6, 'annex5_edit_form_access', 'Opened edit form for record #9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:40:33'),
(284, 6, 'annex5_update', 'Updated record #9 for Alba: Crops - Corn', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:40:37'),
(285, 6, 'annex5_update', 'Updated record #9 for Alba: Crops - Corn', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:12:00'),
(286, 6, 'annex5_edit_form_access', 'Opened edit form for record #9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:12:07'),
(287, 6, 'annex5_update', 'Updated record #9 for Alba: Crops - Rice', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:12:09'),
(288, 6, 'annex11_form_access', 'User accessed the Annex 11 Communication Lines form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:16:25'),
(289, 6, 'annex11_save_access', 'Accessed Annex 11 save handler', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:21:10'),
(290, 6, 'annex11_submission', 'Successfully submitted communication lines report with 1 entries (IDs: 1) for barangay: Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:21:10'),
(291, 6, 'annex11_archived_access', 'Accessed Annex 11 archived records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:24:44'),
(292, 6, 'annex11_records_access', 'Accessed Annex 11 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:28:32'),
(293, 6, 'annex11_records_access', 'Accessed Annex 11 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:31:19'),
(294, 6, 'annex11_view', 'Viewed record #1 for Alba: PLDT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:31:21'),
(295, 6, 'annex11_view', 'Viewed record #1 for Alba: PLDT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:31:21'),
(296, 6, 'annex11_records_access', 'Accessed Annex 11 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:32:11'),
(297, 6, 'annex11_view', 'Viewed record #1 for Alba: PLDT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:32:12'),
(298, 6, 'annex11_records_access', 'Accessed Annex 11 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:33:47'),
(299, 6, 'annex11_edit_form_access', 'Opened edit form for record #1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:33:51'),
(300, 6, 'annex11_update', 'Updated record #1 for Alba: Globe', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:34:06'),
(301, 6, 'annex11_records_access', 'Accessed Annex 11 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:36:00'),
(302, 6, 'annex11_view', 'Viewed record #1 for Alba: Globe', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:36:03'),
(303, 6, 'annex11_records_access', 'Accessed Annex 11 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:37:22'),
(304, 6, 'annex11_edit_form_access', 'Opened edit form for record #1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:37:26'),
(305, 6, 'annex11_update', 'Updated record #1 for Alba: Smart', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:37:31'),
(306, 6, 'annex11_update', 'Updated record #1 for Alba: Smart', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:39:03'),
(307, 6, 'annex11_records_access', 'Accessed Annex 11 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:39:06'),
(308, 6, 'annex11_edit_form_access', 'Opened edit form for record #1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:39:11'),
(309, 6, 'annex11_update', 'Updated record #1 for Alba: DITO', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:39:15'),
(310, 6, 'annex11_records_access', 'Accessed Annex 11 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:39:58'),
(311, 6, 'annex11_records_access', 'Accessed Annex 11 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:40:01'),
(312, 6, 'annex11_edit_form_access', 'Opened edit form for record #1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:40:04'),
(313, 6, 'annex11_update', 'Updated record #1 for Alba: PLDT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:40:08'),
(314, 6, 'annex11_records_access', 'Accessed Annex 11 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:40:36'),
(315, 1, 'annex11_form_access', 'User accessed the Annex 11 Communication Lines form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 08:41:11'),
(316, 6, 'annex11_form_access', 'User accessed the Annex 11 Communication Lines form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 08:41:37'),
(317, 6, 'annex11_form_access', 'User accessed the Annex 11 Communication Lines form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 08:41:44'),
(318, 6, 'annex11_records_access', 'Accessed Annex 11 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 08:41:51'),
(319, 6, 'annex11_edit_form_access', 'Opened edit form for record #1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 08:41:56'),
(320, 6, 'annex11_update', 'Updated record #1 for Alba: Globe', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 08:41:59'),
(321, 6, 'annex11_records_access', 'Accessed Annex 11 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:42:56'),
(322, 6, 'annex11_edit_form_access', 'Opened edit form for record #1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:43:00'),
(323, 6, 'annex11_records_access', 'Accessed Annex 11 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:43:03'),
(324, 6, 'annex11_view', 'Viewed record #1 for Alba: Globe', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:43:07'),
(325, 6, 'annex11_edit_form_access', 'Opened edit form for record #1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:43:12'),
(326, 6, 'annex11_records_access', 'Accessed Annex 11 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:43:15'),
(327, 6, 'annex11_edit_form_access', 'Opened edit form for record #1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:43:19'),
(328, 6, 'annex11_records_access', 'Accessed Annex 11 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:43:22'),
(329, 6, 'annex11_records_access', 'Accessed Annex 11 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:43:35'),
(330, 6, 'annex11_edit_form_access', 'Opened edit form for record #1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:43:37'),
(331, 6, 'annex11_update', 'Updated record #1 for Alba: PLDT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:43:40'),
(332, 6, 'annex11_records_access', 'Accessed Annex 11 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:43:46'),
(333, 6, 'annex11_edit_form_access', 'Opened edit form for record #1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:44:14'),
(334, 6, 'annex11_update', 'Updated record #1 for Alba: Globe', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:44:18'),
(335, 6, 'annex11_update', 'Updated record #1 for Alba: Globe', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:46:58'),
(336, 6, 'annex11_records_access', 'Accessed Annex 11 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:47:00'),
(337, 6, 'annex11_records_access', 'Accessed Annex 11 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:47:04'),
(338, 6, 'annex11_archive', 'Archived record #1 for Alba: Globe', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:47:04'),
(339, 6, 'annex11_records_access', 'Accessed Annex 11 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:47:04'),
(340, 6, 'annex11_form_access', 'User accessed the Annex 11 Communication Lines form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:47:07'),
(341, 6, 'annex11_records_access', 'Accessed Annex 11 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:47:13'),
(342, 6, 'annex11_archived_access', 'Accessed Annex 11 archived records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:47:16'),
(343, 6, 'annex11_view', 'Viewed record #1 for Alba: Globe', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:47:20'),
(344, 6, 'annex11_archived_access', 'Accessed Annex 11 archived records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:47:24'),
(345, 6, 'annex11_restore', 'Restored record #1 for Alba: Globe', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:47:24'),
(346, 6, 'annex11_archived_access', 'Accessed Annex 11 archived records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:47:24'),
(347, 6, 'annex11_records_access', 'Accessed Annex 11 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:47:26'),
(348, 6, 'annex11_edit_form_access', 'Opened edit form for record #1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:47:30'),
(349, 6, 'annex11_update', 'Updated record #1 for Alba: PLDT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:47:33'),
(350, 6, 'annex11_update', 'Updated record #1 for Alba: PLDT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:48:10'),
(351, 6, 'annex11_records_access', 'Accessed Annex 11 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:49:13'),
(352, 6, 'annex11_edit_form_access', 'Opened edit form for record #1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:49:19'),
(353, 6, 'annex11_update', 'Updated record #1 for Alba: PLDT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:49:21'),
(354, 6, 'annex11_records_access', 'Accessed Annex 11 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:49:27');

-- --------------------------------------------------------

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
(199, 6, 'annex5_update', 1760429520, '::1'),
(200, 6, 'annex5_update', 1760429529, '::1'),
(201, 6, 'annex11_form_access', 1760429785, '::1'),
(202, 6, 'annex11_save', 1760430070, '::1'),
(203, 6, 'annex11_update', 1760430846, '::1'),
(204, 6, 'annex11_update', 1760431051, '::1'),
(205, 6, 'annex11_update', 1760431143, '::1'),
(206, 6, 'annex11_update', 1760431155, '::1'),
(207, 6, 'annex11_update', 1760431208, '::1'),
(208, 1, 'annex11_form_access', 1760431271, '::1'),
(209, 6, 'annex11_form_access', 1760431297, '::1'),
(210, 6, 'annex11_form_access', 1760431304, '::1'),
(211, 6, 'annex11_update', 1760431319, '::1'),
(212, 6, 'annex11_update', 1760431420, '::1'),
(213, 6, 'annex11_update', 1760431458, '::1'),
(214, 6, 'annex11_update', 1760431618, '::1'),
(215, 6, 'annex11_form_access', 1760431627, '::1'),
(216, 6, 'annex11_update', 1760431653, '::1'),
(217, 6, 'annex11_update', 1760431690, '::1'),
(218, 6, 'annex11_update', 1760431761, '::1');

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

INSERT INTO `security_logs` (`id`, `user_id`, `event_type`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 6, 'rate_limit_exceeded', 'Annex8 form access rate limit exceeded', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:17:19'),
(2, 6, 'rate_limit_exceeded', 'Annex8 form access rate limit exceeded', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:17:23'),
(3, 6, 'rate_limit_exceeded', 'Annex8 form access rate limit exceeded', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:17:29'),
(4, 6, 'rate_limit_exceeded', 'Annex8 form access rate limit exceeded', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:17:34'),
(5, 6, 'rate_limit_exceeded', 'Annex8 form access rate limit exceeded', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:18:09'),
(6, 6, 'rate_limit_exceeded', 'Annex8 form access rate limit exceeded', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:18:22'),
(7, 6, 'rate_limit_exceeded', 'Annex8 form access rate limit exceeded', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:18:34'),
(8, 6, 'rate_limit_exceeded', 'Annex8 form access rate limit exceeded', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:18:43'),
(9, 6, 'rate_limit_exceeded', 'Annex8 form access rate limit exceeded', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:19:00'),
(10, 6, 'rate_limit_exceeded', 'Annex8 form access rate limit exceeded', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:19:19'),
(11, 6, 'rate_limit_exceeded', 'Annex8 form access rate limit exceeded', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:19:39'),
(12, 6, 'rate_limit_exceeded', 'Annex8 form access rate limit exceeded', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 03:20:06'),
(13, 6, 'annex14_success', 'Successfully submitted 1 suspension work entries', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 08:43:57'),
(14, 6, 'annex14_success', 'Successfully submitted 1 suspension work entries', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 08:48:43'),
(15, 6, 'annex14_success', 'Successfully submitted 1 suspension work entries', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 08:50:09'),
(16, 6, 'annex14_success', 'Successfully submitted 1 suspension work entries', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 08:53:26'),
(17, 6, 'annex1_update_success', 'Successfully updated Annex1 record #7', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 01:56:04'),
(18, 6, 'annex1_success', '1 Annex1 entries saved successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:02:14'),
(19, 6, 'annex1_update_success', 'Successfully updated Annex1 record #8', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 02:02:56'),
(20, 6, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:22:07'),
(21, 6, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:24:37'),
(22, 6, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:25:23'),
(23, 6, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:26:57'),
(24, 6, 'record_view', 'Viewed Annex 5 record #', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:28:00'),
(25, 6, 'record_view', 'Viewed Annex 5 record #', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:52:35'),
(26, 6, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:57:59'),
(27, 6, 'annex5_update_success', 'Successfully updated Annex5 record #5 for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:02:28'),
(28, 6, 'annex5_update_success', 'Successfully updated Annex5 record #5 for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:08:18'),
(29, 6, 'record_view', 'Viewed Annex 5 record #', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:08:33'),
(30, 6, 'record_view', 'Viewed Annex 5 record #', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:08:44'),
(31, 6, 'record_view', 'Viewed Annex 5 record #5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:13:06'),
(32, 6, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:13:37'),
(33, 6, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:14:11'),
(34, 6, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:15:02'),
(35, 6, 'annex5_update_success', 'Successfully updated Annex5 record #6 for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:15:14'),
(36, 6, 'record_view', 'Viewed Annex 5 record #6', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:15:44'),
(37, 6, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:28:59'),
(38, 6, 'annex5_submission_success', 'Successfully submitted Annex5 record #7 for Alba: Crops - Rice', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:29:57'),
(39, 6, 'annex5_submission_success', 'Successfully submitted Annex5 records #7 for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:29:57'),
(40, 6, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:29:57'),
(41, 6, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:31:33'),
(42, 6, 'annex5_submission_success', 'Successfully submitted Annex5 record #8 for Alba: Crops - Rice', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:32:07'),
(43, 6, 'annex5_submission_success', 'Successfully submitted Annex5 records #8 for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:32:07'),
(44, 6, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:32:07'),
(45, 6, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:39:28'),
(46, 6, 'annex5_submission_success', 'Successfully submitted Annex5 record #9 for Alba: Crops - Rice', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:39:48'),
(47, 6, 'annex5_submission_success', 'Successfully submitted Annex5 records #9 for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:39:48'),
(48, 6, 'annex5_update_success', 'Successfully updated Annex5 record #9 for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:40:37'),
(49, 6, 'annex5_update_success', 'Successfully updated Annex5 record #9 for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:12:00'),
(50, 6, 'annex5_update_success', 'Successfully updated Annex5 record #9 for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:12:09'),
(51, 6, 'form_access', 'Accessed Annex 11 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:16:25'),
(52, 6, 'annex11_submission_success', 'Successfully submitted Annex11 record #1 for Alba: PLDT communication line', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:21:10'),
(53, 6, 'annex11_submission_success', 'Successfully submitted Annex11 records #1 for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:21:10'),
(54, 6, 'annex11_update_success', 'Successfully updated Annex11 record #1 for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:34:06'),
(55, 6, 'annex11_update_success', 'Successfully updated Annex11 record #1 for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:37:31'),
(56, 6, 'annex11_update_success', 'Successfully updated Annex11 record #1 for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:39:03'),
(57, 6, 'annex11_update_success', 'Successfully updated Annex11 record #1 for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:39:15'),
(58, 6, 'annex11_update_success', 'Successfully updated Annex11 record #1 for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:40:08'),
(59, 1, 'form_access', 'Accessed Annex 11 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 08:41:11'),
(60, 6, 'form_access', 'Accessed Annex 11 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 08:41:37'),
(61, 6, 'form_access', 'Accessed Annex 11 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 08:41:44'),
(62, 6, 'annex11_update_success', 'Successfully updated Annex11 record #1 for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 08:41:59'),
(63, 6, 'annex11_update_success', 'Successfully updated Annex11 record #1 for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:43:40'),
(64, 6, 'annex11_update_success', 'Successfully updated Annex11 record #1 for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:44:18'),
(65, 6, 'annex11_update_success', 'Successfully updated Annex11 record #1 for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:46:58'),
(66, 6, 'form_access', 'Accessed Annex 11 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:47:07'),
(67, 6, 'annex11_update_success', 'Successfully updated Annex11 record #1 for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:47:33'),
(68, 6, 'annex11_update_success', 'Successfully updated Annex11 record #1 for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:48:10'),
(69, 6, 'annex11_update_success', 'Successfully updated Annex11 record #1 for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 08:49:21');

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `barangay`, `contact_number`, `username`, `password`, `user_role`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'System Administrator', 'MDRRMO-BAGGAO', '09123456789', 'admin', '$2y$10$YxKi2fr1PyOTAKf2sniSIOOAYz/T5TFHT0k7WF8I06DBXZWMZKUu.', 'admin', 1, NULL, '2025-10-10 03:19:02', '2025-10-12 11:43:29'),
(2, 'Juan Dela Cruz', 'Aggugaddan', '09111111111', 'juan_agg', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 1, NULL, '2025-10-10 03:19:02', '2025-10-10 03:19:02'),
(3, 'Maria Santos', 'Alba', '09122222222', 'maria_alb', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 1, NULL, '2025-10-10 03:19:02', '2025-10-10 03:19:02'),
(4, 'Pedro Reyes', 'Asassi', '09133333333', 'pedro_asa', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 1, NULL, '2025-10-10 03:19:02', '2025-10-10 03:19:02'),
(5, 'richmond', 'Asassi', '09917819410', 'rich', '$2y$10$DE4bHIPnfxmYGsXkl13u3.oU5n0rKSDfH/BSHJGFtFI3/3ilz3Oku', 'admin', 1, 1, '2025-10-10 03:23:14', '2025-10-10 03:43:45'),
(6, 'MON', 'Alba', '09876543212', 'MON', '$2y$10$tptUSkRGITiq.VwwZJt21u.O7boWk4zcxv0UMbfLxlJ5gONnk9jgy', 'user', 1, 1, '2025-10-10 06:37:09', '2025-10-12 09:30:44');

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

INSERT INTO `user_sessions` (`id`, `user_id`, `session_token`, `ip_address`, `user_agent`, `login_time`, `last_activity`, `is_active`) VALUES
(1, 1, 'a7361da72f58abc0641c3a03b99751752fcf7f516f6e6ff4fd2b70a965922473', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-10 03:42:47', '2025-10-10 03:44:39', 0),
(2, 1, 'edbadcbfbd3d7475b4ebe84c07fd80597f0f39e0df42b77cd2ec6f1d7499aa80', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-10 03:49:25', '2025-10-10 06:18:00', 0),
(3, 1, '4c640c9cbdb19f5278468499864f56b0d3e34e47c88c6fd16839357c1f71a9df', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-10 06:06:22', '2025-10-10 06:37:29', 0),
(4, 1, '31a4d739ba41dd99e52c4e79ffd363193e886c0f9025774c8b22aca343c9ae0b', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-10 06:18:17', '2025-10-10 06:18:40', 0),
(5, 1, '812b5c6ff4228c358567df684ea978d3dedf56f6b8b85924e047bdc3d12e482b', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-10 06:26:13', '2025-10-10 07:23:17', 0),
(6, 6, 'f90d983ce4d8a98763727e7e831cb4aeae22bd97d536cb80ca259ea1f36840cf', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-10 06:37:42', '2025-10-10 07:09:44', 0),
(7, 6, '875ecc1e9435b5b2ee9ce85a5e6e4b84f3bb62afb4d6029ba80d750d7e319d54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-10 07:10:03', '2025-10-10 07:12:38', 0),
(8, 6, 'ca798d6924bb4a620e15e9450169c961ec13259eb1db0d04bcb74d50f2ccf26f', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-10 07:12:50', '2025-10-10 07:15:28', 0),
(9, 6, '92c39df0af0925350c3854fd5d5b468f6d11185f01186764bbd560acd8c5be58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-10 07:15:41', '2025-10-10 07:16:55', 0),
(10, 1, 'cffe0e0279cd2ebc726324dd97ad798978346303a05952bb104ffbb5dffffd4d', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-10 07:23:07', '2025-10-10 07:36:57', 0),
(11, 6, 'ca77887622c917a42f26d2b109f2f2c77ea42d5337f42496f508c25f3f9226b3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-10 07:23:31', '2025-10-10 07:24:16', 0),
(12, 6, '851ef8325180e47eaf0a42e790cf41e4527b4e57b13d890b39c8acc9a2514d2d', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-10 07:24:31', '2025-10-10 07:35:20', 0),
(13, 6, 'b4fd88c6e3ead9fb2500b8b652f2e1df921b9abecccdc698dd3a5c1f91c5b4da', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-10 07:35:32', '2025-10-10 07:37:17', 0),
(14, 1, 'a1b573f8bd8f7955676bad6672d2bc453ec34fcac6d4904b7c104dc5a942b247', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-10 07:37:04', '2025-10-10 07:37:04', 1),
(15, 6, '2346c2841449b8e2f31cd68b0aa1550d262a92e6891f457f9eac4900630367d6', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-10 07:37:30', '2025-10-10 07:37:30', 0),
(16, 1, '408b83bab92128b7049171bdf4690a47d7054114d590cf4e9afa94d55d2a2317', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-10-12 08:41:43', '2025-10-12 09:30:04', 0),
(17, 1, 'fcd1936a87d6b774c6b9cb7b5fd2d0d1a5f39011a9163776b4a377377240d415', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-10-12 09:19:30', '2025-10-12 09:34:59', 0),
(18, 6, '6cd3456e3a8dc0bea1effc184a5b35ff26a8c0fe25c6e0061546a08f831e6819', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-10-12 09:31:25', '2025-10-12 09:37:25', 0),
(19, 6, '8dce65511157845b4e54ce7155473c67417a68caf370f030968e559592b3ed16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-10-12 09:35:13', '2025-10-12 09:37:16', 0),
(20, 1, '2ca95b6dbb56df5199b793fc212d31da41c9f8a7decb1b93bf7f914f468703ad', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-10-12 09:37:38', '2025-10-12 10:42:27', 0),
(21, 6, 'cef0a182b48c772d4153608db15678d4965b86002f3c86ad43c1afba68d53463', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-10-12 09:40:53', '2025-10-12 09:56:23', 0),
(22, 6, '08a732a061af5da53fb96d714f44313874b8f9167b52ade37cf3b872b7a863a6', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-10-12 09:56:35', '2025-10-12 09:56:59', 0),
(23, 6, '452c706dc81b6cd351f19516ec4f8a99693acc0b2f52774f6b44b98585577b9b', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-10-12 09:57:34', '2025-10-12 10:00:41', 0),
(24, 6, '498da483edff2c5243c95b32cfa0604963a2bc1ddb71252aa70ecd03a559bb67', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-10-12 10:01:03', '2025-10-12 10:06:20', 0),
(25, 6, 'd7dd800ca8562434f9c305441079465fb485712848bb80c7de4735c6468ad2bf', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-10-12 10:06:28', '2025-10-12 10:06:31', 0),
(26, 1, 'fcccc0dffdca5ec0b4077d866586b584f5220178f2399b138ab9f1fe2a841eb9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-10-12 10:39:39', '2025-10-12 11:00:02', 0),
(27, 6, '4a1f0fb441bcf143ea359ab8fe8a88ed0ab008a67d29dd428c62bb5ef9c7cef6', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-10-12 10:42:33', '2025-10-12 10:50:11', 0),
(28, 6, '7ebb8759111a85a4a8170d0dc7e2c9d4dfc204e916665bd10fad8b3f2acb149e', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-10-12 10:50:43', '2025-10-12 10:51:37', 0),
(29, 1, '78d82590022bb26ce7b207edffc67b97d30e578d73e64d28b864956ef9125794', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-10-12 11:00:09', '2025-10-12 11:00:26', 0),
(30, 1, '2142fbfad375ccba70bcc8849c4179d29b409fbb10198740c5e98f660d3333e1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-10-12 11:00:52', '2025-10-12 11:43:38', 0),
(31, 6, 'f0bf998779c8d895a3661848491a653933c506f5d06915d83c16debdf295d549', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-10-12 11:01:13', '2025-10-12 11:35:32', 0),
(32, 6, 'cb7de3000f23794ce821d644e790296e447cc6e2da5403fa3d8f20238d41c7d5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-10-12 11:35:40', '2025-10-12 11:37:18', 0),
(33, 6, 'e0e847d253a4c55a8e32cbe97722ff46f71d8b215d008fd981be21f20d9a36c5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-10-12 11:38:25', '2025-10-12 11:39:59', 0),
(34, 1, '9cf092825c1ecc32977eadd36f7309ce6e02ab0002ac2235de3abfd089600ac1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-10-12 11:43:56', '2025-10-12 11:48:57', 0),
(35, 6, '64b8f09e008bd3988f48405115b37dea438d746b5f145658804aa8cf48a3776e', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-10-12 11:49:06', '2025-10-12 14:01:30', 0),
(36, 1, 'c73c45aa63529a0cdf5f278fa38aa559d5aa10a82cc7ce657fa50c663613d1b9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-10-12 14:01:55', '2025-10-12 14:06:34', 0),
(37, 6, '431fe372cbda2a4d9d810cc02a3040e322028bc778928e0ce9c20b9662dd305f', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-10-12 14:06:44', '2025-10-12 14:10:56', 0),
(38, 1, 'ded7597d145ad6a94b9132275bdb768298d7f232c813d87af3c127395f380b25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-10-12 14:11:12', '2025-10-12 14:28:23', 0),
(39, 6, '08b181b0a3ff131232ec92a94158f03aeffebf6d768833c7d1f54abb43d68617', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-10-12 14:28:39', '2025-10-12 14:53:15', 1),
(40, 1, '7a834788c4d7082fa0ac6f4a500432e7d2bd759aa48a8397fd82e9aaeb26ab67', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 00:08:42', '2025-10-13 08:39:55', 1),
(41, 6, '4aa8c0eacae4ad8cd72dd8296c17cd3e3fc62aaaf84ac1b014530ef820838da2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-13 00:15:27', '2025-10-13 08:53:28', 1),
(42, 1, '76b03141ae340a7403d43ec0b9fbc92274ee4b4b51a72d30f2e8fd3104bcda68', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 00:09:48', '2025-10-14 00:16:01', 0),
(43, 1, '6c45cdbbffeb48293b33dc3c9a410335929d1797812f4dedd8d347fa32b54492', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 00:13:22', '2025-10-14 03:41:57', 0),
(44, 6, '3b655bf7a79a702867144dbcb4aabcee8899f12e275de5c944b1dc2194275d22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 00:16:11', '2025-10-14 00:21:00', 0),
(45, 6, '457dc5cb0246f2f69023954e318a482b200bae26586a85dccb8cedac73255755', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 00:21:08', '2025-10-14 00:21:10', 0),
(46, 6, '405995ed8c178e888253931a95dd256e76fdee1e5c859329e31d05a4a4339d2d', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 00:33:31', '2025-10-14 08:49:27', 1),
(47, 1, '48410bd810373590c57ed61b0b463b83e9e5633d357d2d83206e120954e77c87', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 08:41:03', '2025-10-14 08:41:24', 0),
(48, 6, '25b6e5675723418d2ca6542d06b372ea44b68f9ac7aee3cc1c6aaefcf46246d2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 08:41:33', '2025-10-14 08:41:59', 1);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=355;

--
-- AUTO_INCREMENT for table `barangay_locks`
--
ALTER TABLE `barangay_locks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=219;

--
-- AUTO_INCREMENT for table `security_logs`
--
ALTER TABLE `security_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

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
-- Constraints for table `annex11_communication_lines`
--
ALTER TABLE `annex11_communication_lines`
  ADD CONSTRAINT `annex11_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
