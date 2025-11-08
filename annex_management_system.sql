-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 01, 2025 at 10:53 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

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
(8, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 'Flooding', '2025-10-14 10:02:00', '', '', '', 6, '2025-10-14 02:02:14', '2025-10-15 01:53:39', 0),
(9, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Asassi', 'Flooding', '2025-10-16 12:44:00', '', '', '', 5, '2025-10-16 04:44:17', '2025-10-16 04:44:17', 0);

-- --------------------------------------------------------

--
-- Table structure for table `annex2_affected_population`
--

CREATE TABLE `annex2_affected_population` (
  `id` int(11) NOT NULL,
  `region` varchar(100) NOT NULL DEFAULT 'REGION II',
  `province` varchar(100) NOT NULL DEFAULT 'CAGAYAN',
  `city` varchar(100) NOT NULL DEFAULT 'BAGGAO',
  `barangay` varchar(100) NOT NULL,
  `affected_families_cumulative` int(11) DEFAULT 0,
  `affected_families_current` int(11) DEFAULT 0,
  `affected_persons_cumulative` int(11) DEFAULT 0,
  `affected_persons_current` int(11) DEFAULT 0,
  `num_ecs_cumulative` int(11) DEFAULT 0,
  `num_ecs_current` int(11) DEFAULT 0,
  `inside_families_cumulative` int(11) DEFAULT 0,
  `inside_families_current` int(11) DEFAULT 0,
  `inside_persons_cumulative` int(11) DEFAULT 0,
  `inside_persons_current` int(11) DEFAULT 0,
  `outside_families_cumulative` int(11) DEFAULT 0,
  `outside_families_current` int(11) DEFAULT 0,
  `outside_persons_cumulative` int(11) DEFAULT 0,
  `outside_persons_current` int(11) DEFAULT 0,
  `total_families_cumulative` int(11) DEFAULT 0,
  `total_families_current` int(11) DEFAULT 0,
  `total_persons_cumulative` int(11) DEFAULT 0,
  `total_persons_current` int(11) DEFAULT 0,
  `remarks` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `annex2_affected_population`
--

INSERT INTO `annex2_affected_population` (`id`, `region`, `province`, `city`, `barangay`, `affected_families_cumulative`, `affected_families_current`, `affected_persons_cumulative`, `affected_persons_current`, `num_ecs_cumulative`, `num_ecs_current`, `inside_families_cumulative`, `inside_families_current`, `inside_persons_cumulative`, `inside_persons_current`, `outside_families_cumulative`, `outside_families_current`, `outside_persons_cumulative`, `outside_persons_current`, `total_families_cumulative`, `total_families_current`, `total_persons_cumulative`, `total_persons_current`, `remarks`, `created_by`, `created_at`, `updated_at`, `is_archived`) VALUES
(1, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Asassi', 1250, 1200, 5840, 840, 15, 12, 850, 800, 3980, 900, 400, 200, 1860, 400, 1250, 1000, 5840, 1300, '', 5, '2025-10-30 08:51:44', '2025-10-30 12:55:49', 0);

-- --------------------------------------------------------

--
-- Table structure for table `annex3_casualties`
--

CREATE TABLE `annex3_casualties` (
  `id` int(11) NOT NULL,
  `region` varchar(100) NOT NULL DEFAULT 'REGION II',
  `province` varchar(100) NOT NULL DEFAULT 'CAGAYAN',
  `city` varchar(100) NOT NULL DEFAULT 'BAGGAO',
  `barangay` varchar(100) NOT NULL,
  `category` enum('Dead','Injured','Ill','Missing') NOT NULL,
  `surname` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `age` tinyint(3) UNSIGNED NOT NULL,
  `sex` enum('Male','Female') NOT NULL,
  `address` varchar(255) NOT NULL,
  `cause` text NOT NULL,
  `remarks` text DEFAULT NULL,
  `source` enum('MDM Cluster','LGU','DOH','PNP','BFAR','Other') NOT NULL,
  `validated` enum('Yes','No') NOT NULL,
  `is_archived` tinyint(1) DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `annex3_casualties`
--

INSERT INTO `annex3_casualties` (`id`, `region`, `province`, `city`, `barangay`, `category`, `surname`, `first_name`, `middle_name`, `age`, `sex`, `address`, `cause`, `remarks`, `source`, `validated`, `is_archived`, `created_by`, `created_at`, `updated_at`, `updated_by`) VALUES
(5, 'Region II', 'Cagayan', 'Baggao', 'Asassi', 'Ill', 'RRR', 'rich', '', 25, 'Male', 'ASASSI', 'HH', '', 'LGU', 'Yes', 0, 5, '2025-10-31 06:09:20', '2025-10-31 07:49:10', NULL);

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
(4, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 23, 45, 68, '100000', '', 6, '2025-10-14 02:17:41', '2025-10-14 02:17:41', 0),
(7, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Asassi', 23, 10, 33, '20', '', 5, '2025-10-16 06:59:43', '2025-10-16 07:02:25', 0);

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
(7, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 'Road', 'National', 'MASICAL BRIDGE ZONE 6', 'Passable', '2025-10-15 08:39:00', NULL, '', 'annex8/68ed9bc820819_20251014_083936.jpg', 6, '2025-10-14 00:39:36', '2025-10-15 04:54:06', 0),
(8, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Asassi', 'Road', 'Provincial', 'TAYTAY BRIDGE', 'Passable', '2025-10-16 11:58:00', NULL, '', NULL, 5, '2025-10-16 03:58:09', '2025-10-16 03:58:09', 0),
(9, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Asassi', 'Road', 'Provincial', 'TAYTAY BRIDGE', 'Passable to Light Vehicles Only', '2025-10-16 12:00:00', NULL, '', 'annex8/68f07478dc239_20251016_122840_flood-pictures-9g7ob3wnw4p1ark5.jpg', 5, '2025-10-16 04:00:34', '2025-10-16 04:28:40', 0),
(10, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Asassi', 'Road', 'National', 'qqqqqq', 'Passable to Heavy Vehicles Only', '2025-10-16 12:01:00', NULL, '', 'annex8/68f074843bb6e_20251016_122852_CamScanner_10-08-2025_09.05.jpg', 5, '2025-10-16 04:01:42', '2025-10-16 07:08:36', 0),
(11, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 'Road', 'City/Municipal', 'taytay bridge', 'Passable', '2025-10-20 09:44:00', '2025-10-20 09:47:00', '', 'annex8/68f5947cae08f_20251020_094636_logo_png__1_.png', 6, '2025-10-20 01:44:37', '2025-10-20 01:47:41', 0),
(12, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 'Road', 'National', 'ASASSI', 'Passable to Light Vehicles Only', '2025-11-08 12:38:00', NULL, '', 'annex8/68f70e6e1dbd8_20251021_123910_flood-pictures-9g7ob3wnw4p1ark5.jpg', 6, '2025-10-21 04:39:10', '2025-10-21 04:39:10', 0),
(13, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 'Bridge', 'Barangay', 'MASICAL BRIDGE ZONE 6', 'Passable to Light Vehicles Only', '2025-10-21 12:42:00', NULL, '', 'annex8/68f70f442efe4_20251021_124244_ChatGPT_Image_Oct_20__2025__04_15_44_PM.png', 6, '2025-10-21 04:42:44', '2025-10-21 04:42:44', 0),
(14, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 'Road', 'City/Municipal', 'taytay bridge', 'Passable', '2025-10-24 10:22:00', '2025-10-25 10:22:00', '', 'annex8/68fae2dfc5cb3_20251024_102223_474777418_1132192015074258_39180812305378569_n.jpg', 6, '2025-10-24 02:22:23', '2025-10-24 02:23:05', 0);

-- --------------------------------------------------------

--
-- Table structure for table `annex9_power_supply`
--

CREATE TABLE `annex9_power_supply` (
  `id` int(11) NOT NULL,
  `region` varchar(100) NOT NULL DEFAULT 'REGION II',
  `province` varchar(100) NOT NULL DEFAULT 'CAGAYAN',
  `city` varchar(100) NOT NULL DEFAULT 'BAGGAO',
  `barangay` varchar(100) NOT NULL,
  `service_provider` varchar(200) NOT NULL,
  `interruption_datetime` datetime NOT NULL,
  `restored_datetime` datetime DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `annex9_power_supply`
--

INSERT INTO `annex9_power_supply` (`id`, `region`, `province`, `city`, `barangay`, `service_provider`, `interruption_datetime`, `restored_datetime`, `remarks`, `image_path`, `created_by`, `created_at`, `updated_at`, `is_archived`) VALUES
(1, 'Region II', 'Cagayan', 'Baggao', 'Agaman Norte', 'CAGELCO', '2025-10-16 10:20:00', NULL, NULL, '68f056716b5f5_20251016_102033_Picture1.png', 1, '2025-10-16 02:20:33', '2025-10-16 02:20:33', 0),
(2, 'Region II', 'Cagayan', 'Baggao', 'Asassi', 'CAGELCO', '2025-10-16 12:29:00', '2025-10-16 12:29:00', NULL, '68f074acb7038_20251016_122932_flood-pictures-9g7ob3wnw4p1ark5.jpg', 5, '2025-10-16 04:29:32', '2025-10-16 04:29:49', 0);

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
(4, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 'Private', '2025-10-13 16:53:00', NULL, NULL, 6, '2025-10-13 08:53:26', '2025-10-13 08:53:26', 0),
(5, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Asassi', 'Government', '2025-10-16 15:10:00', '2025-10-17 08:00:00', '', 5, '2025-10-16 07:11:28', '2025-10-16 07:11:28', 0),
(6, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Asassi', 'All', '2025-10-16 08:00:00', '2025-10-17 08:00:00', '', 5, '2025-10-16 07:13:10', '2025-10-16 07:16:29', 0),
(7, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 'Government', '2025-10-16 16:00:00', '0000-00-00 00:00:00', '', 6, '2025-10-16 08:00:53', '2025-10-16 08:00:53', 0),
(8, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 'Government', '2025-10-16 16:01:00', '0000-00-00 00:00:00', '', 6, '2025-10-16 08:01:33', '2025-10-16 08:01:33', 0);

-- --------------------------------------------------------

--
-- Table structure for table `annex20_assistance_provided`
--

CREATE TABLE `annex20_assistance_provided` (
  `id` int(11) NOT NULL,
  `region` varchar(100) NOT NULL DEFAULT 'REGION II',
  `province` varchar(100) NOT NULL DEFAULT 'CAGAYAN',
  `city` varchar(100) NOT NULL DEFAULT 'BAGGAO',
  `cluster` enum('Food','WASH','Shelter','Health','Protection','Education','Nutrition','Camp Management','Emergency Telecommunications','Logistics') NOT NULL,
  `type` enum('Food packs','Hygiene kits','Water containers','Temporary shelter materials','Medicines','Blankets','Sleeping mats','Cooking utensils','Mosquito nets','Water purification tablets','Other') NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `cost_per_unit` decimal(15,2) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1281, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:44:14'),
(1282, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:44:18'),
(1283, 5, 'annex2_records_access', 'Accessed Annex 2 affected population records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:44:23'),
(1284, 5, 'annex2_records_access', 'Accessed Annex 2 affected population records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:44:28'),
(1285, 5, 'annex2_records_access', 'Accessed Annex 2 affected population records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:44:29'),
(1286, 5, 'annex2_records_access', 'Accessed Annex 2 affected population records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:50:50'),
(1287, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:50:54'),
(1288, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:51:44'),
(1289, 5, 'annex2_submission', 'Submitted affected population report for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:51:44'),
(1290, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:51:44'),
(1291, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:51:52'),
(1292, 5, 'annex2_records_access', 'Accessed Annex 2 affected population records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:51:52'),
(1293, 5, 'annex2_view', 'Viewed affected population record #1 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:52:02'),
(1294, 5, 'annex2_view', 'Viewed affected population record #1 for Asassi', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:52:24'),
(1295, 5, 'annex2_edit_access', 'Accessed edit form for record #1 for Asassi', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:52:30'),
(1296, 5, 'annex2_records_access', 'Accessed Annex 2 affected population records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:57:02'),
(1297, 5, 'annex2_edit_access', 'Accessed edit form for record #1 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:57:11'),
(1298, 5, 'annex2_records_access', 'Accessed Annex 2 affected population records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:57:18'),
(1299, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:57:25'),
(1300, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:57:38'),
(1301, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:57:44'),
(1302, 5, 'annex4_form_access', 'User accessed the Annex 4 Damaged Houses form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:57:48'),
(1303, 5, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:57:56'),
(1304, 5, 'annex8_form_access', 'Accessed Annex 8 form for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:57:56'),
(1305, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:58:09'),
(1306, 5, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:58:16'),
(1307, 5, 'annex8_form_access', 'Accessed Annex 8 form for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:58:16'),
(1308, 5, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:05:13'),
(1309, 5, 'annex8_form_access', 'Accessed Annex 8 form for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:05:14'),
(1310, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:05:19'),
(1311, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:07:45'),
(1312, 5, 'annex14_form_access', 'User accessed the Annex 14 Suspension of Work form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:07:48'),
(1313, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:08:11'),
(1314, 5, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:08:15'),
(1315, 5, 'annex8_form_access', 'Accessed Annex 8 form for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:08:15'),
(1316, 5, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:12:18'),
(1317, 5, 'annex8_form_access', 'Accessed Annex 8 form for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:12:18'),
(1318, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:12:23'),
(1319, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:23:16'),
(1320, 5, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:23:22'),
(1321, 5, 'annex8_form_access', 'Accessed Annex 8 form for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:23:22'),
(1322, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:23:26'),
(1323, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:28:03'),
(1324, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:29:15'),
(1325, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:30:39'),
(1326, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:30:40'),
(1327, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:30:40'),
(1328, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:30:40'),
(1329, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:30:40'),
(1330, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:30:41'),
(1331, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:30:41'),
(1332, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:30:41'),
(1333, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:30:41'),
(1334, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:30:41'),
(1335, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:30:48'),
(1336, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:30:48'),
(1337, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:39:38'),
(1338, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:40:03'),
(1339, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:41:03'),
(1340, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:41:37'),
(1341, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:41:46'),
(1342, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:42:09'),
(1343, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:43:43'),
(1344, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:43:45'),
(1345, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:43:45'),
(1346, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:43:45'),
(1347, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:43:46'),
(1348, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:44:00'),
(1349, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:44:00'),
(1350, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:44:44'),
(1351, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:44:45'),
(1352, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:44:54'),
(1353, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 09:45:23'),
(1354, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:01:17'),
(1355, 5, 'annex20_form_access', 'Accessed Annex 20 form for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:06:36'),
(1356, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:08:47'),
(1357, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:10:45'),
(1358, 5, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:11:18'),
(1359, 5, 'annex8_form_access', 'Accessed Annex 8 form for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:11:18'),
(1360, 5, 'annex20_form_access', 'Accessed Annex 20 form for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:12:34'),
(1361, 5, 'annex20_form_access', 'Accessed Annex 20 form for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:12:41'),
(1362, 5, 'annex20_form_access', 'Accessed Annex 20 form for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:12:42'),
(1363, 5, 'annex20_form_access', 'Accessed Annex 20 form for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:16:44'),
(1364, 5, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:21:02'),
(1365, 5, 'annex8_form_access', 'Accessed Annex 8 form for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:21:02'),
(1366, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:21:03'),
(1367, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:21:04'),
(1368, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:21:07'),
(1369, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:21:28'),
(1370, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:21:37'),
(1371, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:21:40'),
(1372, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:22:33'),
(1373, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:22:39'),
(1374, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:22:43'),
(1375, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:22:56'),
(1376, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:23:08'),
(1377, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:23:09'),
(1378, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:30:04'),
(1379, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:30:12'),
(1380, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:35:42'),
(1381, 5, 'annex3_submission', 'Saved assistance data for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:36:01'),
(1382, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:36:46'),
(1383, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:36:53'),
(1384, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:37:15'),
(1385, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:37:32'),
(1386, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:37:38'),
(1387, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:37:53'),
(1388, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:38:11'),
(1389, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:39:43'),
(1390, 5, 'annex3_submission', 'Saved assistance data for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:40:12'),
(1391, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:40:33'),
(1392, 5, 'annex3_submission', 'Saved assistance data for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:40:47'),
(1393, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:40:50'),
(1394, 5, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:43:04'),
(1395, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:43:10'),
(1396, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:43:11'),
(1397, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:43:13'),
(1398, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:43:16'),
(1399, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:43:17'),
(1400, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:43:28'),
(1401, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:44:25'),
(1402, 5, 'annex5_records_access', 'Accessed Annex 5 agriculture damage records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:49:39'),
(1403, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:55:33'),
(1404, 5, 'annex2_records_access', 'Accessed Annex 2 affected population records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:55:39'),
(1405, 5, 'annex2_edit_access', 'Accessed edit form for record #1 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:55:45'),
(1406, 5, 'annex2_update', 'Updated record #1 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:55:49'),
(1407, 5, 'annex2_records_access', 'Accessed Annex 2 affected population records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:55:49'),
(1408, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:58:07'),
(1409, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:58:27'),
(1410, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:58:31'),
(1411, 5, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:00:58'),
(1412, 5, 'annex5_records_access', 'Accessed Annex 5 agriculture damage records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:01:07'),
(1413, 5, 'annex5_archived_access', 'Accessed Annex 5 archived records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:01:11'),
(1414, 5, 'annex5_records_access', 'Accessed Annex 5 agriculture damage records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:01:19'),
(1415, 5, 'annex5_records_access', 'Accessed Annex 5 agriculture damage records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:03:53'),
(1416, 5, 'annex5_archived_access', 'Accessed Annex 5 archived records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:03:57'),
(1417, 5, 'annex5_records_access', 'Accessed Annex 5 agriculture damage records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:03:59'),
(1418, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:04:04'),
(1419, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:07:43'),
(1420, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:07:45'),
(1421, 5, 'annex3_form_access', 'Accessed Annex 3 save handler', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:08:05'),
(1422, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:08:05'),
(1423, 5, 'annex3_form_access', 'Accessed Annex 3 save handler', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:08:10'),
(1424, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:08:10'),
(1425, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:10:10'),
(1426, 5, 'annex3_form_access', 'Accessed Annex 3 save handler', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:10:41'),
(1427, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:10:41'),
(1428, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:12:05'),
(1429, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:12:11'),
(1430, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:12:33'),
(1431, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:13:43'),
(1432, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:13:59'),
(1433, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:16:20'),
(1434, 5, 'annex3_view', 'Viewed assistance provided record #3 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:16:22'),
(1435, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:16:33'),
(1436, 5, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:17:15'),
(1437, 5, 'annex5_records_access', 'Accessed Annex 5 agriculture damage records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:17:24'),
(1438, 5, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:17:27'),
(1439, 5, 'annex5_form_access', 'Accessed Annex 5 save handler', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:18:04'),
(1440, 5, 'annex5_submission', 'Successfully submitted agriculture damage report with 1 entries (IDs: 10) for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:18:04'),
(1441, 5, 'annex5_records_access', 'Accessed Annex 5 agriculture damage records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:18:04'),
(1442, 5, 'annex5_view', 'Viewed agriculture damage record #10 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:18:07'),
(1443, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:28:14'),
(1444, 5, 'annex3_view', 'Viewed assistance provided record #3 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:28:16'),
(1445, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:34:19'),
(1446, 5, 'annex3_view', 'Viewed assistance provided record #3 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:34:21'),
(1447, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:34:26'),
(1448, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:34:28'),
(1449, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:34:33'),
(1450, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:35:56'),
(1451, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:35:58'),
(1452, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:36:01'),
(1453, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:36:04'),
(1454, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:36:08'),
(1455, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:40:24'),
(1456, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:40:25'),
(1457, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:40:26'),
(1458, 5, 'annex5_records_access', 'Accessed Annex 5 agriculture damage records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:51:16'),
(1459, 5, 'annex5_view', 'Viewed agriculture damage record #10 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:51:25'),
(1460, 5, 'annex3_view', 'Viewed assistance provided record #3 for Asassi', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 01:13:08'),
(1461, 5, 'annex3_view', 'Viewed assistance provided record #3 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 01:41:55'),
(1462, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 01:54:53'),
(1463, 5, 'annex3_view', 'Viewed assistance provided record #3 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 01:54:57'),
(1464, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 01:55:00'),
(1465, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 01:58:13'),
(1466, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 01:58:56'),
(1467, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 01:59:02'),
(1468, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 01:59:05'),
(1469, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 01:59:07'),
(1470, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:02:12'),
(1471, 5, 'annex3_archived_access', 'Accessed Annex 3 archived records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:02:14'),
(1472, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:02:17'),
(1473, 5, 'annex3_archived_access', 'Accessed Annex 3 archived assistance records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:02:25'),
(1474, 5, 'annex3_archived_access', 'Accessed Annex 3 archived records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:02:51'),
(1475, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:02:57'),
(1476, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:03:01'),
(1477, 5, 'annex3_archive', 'Archived record #3 for Asassi: Education - Cooking utensils', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:03:02'),
(1478, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:03:02'),
(1479, 5, 'annex3_archived_access', 'Accessed Annex 3 archived records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:03:04'),
(1480, 5, 'annex3_view', 'Viewed assistance provided record #3 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:03:07'),
(1481, 5, 'annex3_archived_access', 'Accessed Annex 3 archived records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:03:15'),
(1482, 5, 'annex3_restore', 'Restored record #3 for Asassi: Education - Cooking utensils', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:03:15'),
(1483, 5, 'annex3_archived_access', 'Accessed Annex 3 archived records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:03:15'),
(1484, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:03:18'),
(1485, 5, 'annex3_view', 'Viewed assistance provided record #3 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:07:57'),
(1486, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:08:12');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1487, 5, 'annex3_view', 'Viewed assistance provided record #3 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:08:15'),
(1488, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:09:15'),
(1489, 5, 'annex3_edit_form_access', 'Opened edit form for record #3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:09:18'),
(1490, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:09:30'),
(1491, 5, 'annex3_edit_access', 'Accessed edit form for record #3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:09:33'),
(1492, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:09:45'),
(1493, 5, 'annex3_edit_form_access', 'Opened edit form for record #3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:09:48'),
(1494, 5, 'annex3_update', 'Updated record #3 for Asassi: Education - Cooking utensils', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:13:04'),
(1495, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:13:08'),
(1496, 5, 'annex3_edit_form_access', 'Opened edit form for record #3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:13:18'),
(1497, 5, 'annex3_update', 'Updated record #3 for Asassi: Education - Cooking utensils', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:13:27'),
(1498, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:13:29'),
(1499, 5, 'annex3_edit_form_access', 'Opened edit form for record #3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:13:37'),
(1500, 5, 'annex3_update', 'Updated record #3 for Asassi: Education - Cooking utensils', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:13:46'),
(1501, 5, 'annex3_update', 'Updated record #3 for Asassi: Education - Cooking utensils', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:13:54'),
(1502, 5, 'annex3_view', 'Viewed assistance provided record #3 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:13:58'),
(1503, 5, 'annex3_view', 'Viewed assistance provided record #3 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:13:58'),
(1504, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:14:00'),
(1505, 5, 'annex3_edit_form_access', 'Opened edit form for record #3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:15:14'),
(1506, 5, 'annex3_update', 'Updated record #3 for Asassi: Education - Cooking utensils', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:15:21'),
(1507, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:15:21'),
(1508, 5, 'annex3_edit_form_access', 'Opened edit form for record #3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:15:59'),
(1509, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:20:21'),
(1510, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:28:50'),
(1511, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:31:56'),
(1512, 5, 'annex3_edit_form_access', 'Opened edit form for record #3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:32:00'),
(1513, 5, 'annex3_update', 'Updated record #3 for Asassi: Education - Cooking utensils', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:32:07'),
(1514, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:32:07'),
(1515, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:33:11'),
(1516, 5, 'annex3_edit_form_access', 'Opened edit form for record #2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:33:20'),
(1517, 5, 'annex3_update', 'Updated record #2 for Asassi: WASH - Food packs', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:33:26'),
(1518, 5, 'annex3_records_access', 'Accessed Annex 3 assistance provided records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:33:26'),
(1519, 5, 'annex3_edit_form_access', 'Opened edit form for record #2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:33:32'),
(1520, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:33:47'),
(1521, 5, 'annex3_form_access', 'Accessed Annex 3 save handler', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:34:03'),
(1522, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:34:03'),
(1523, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:34:28'),
(1524, 5, 'annex3_form_access', 'Accessed Annex 3 save handler', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:34:56'),
(1525, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:34:56'),
(1526, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:54:57'),
(1527, 5, 'annex3_form_access', 'Accessed Annex 3 save handler', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:59:10'),
(1528, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:59:10'),
(1529, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:12:10'),
(1530, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:13:01'),
(1531, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:14:27'),
(1532, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:14:45'),
(1533, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:15:05'),
(1534, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:15:09'),
(1535, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:18:40'),
(1536, 5, 'annex3_form_access', 'Accessed Annex 3 save handler', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:18:55'),
(1537, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:19:04'),
(1538, 5, 'annex3_form_access', 'User accessed the Annex 3 Assistance Provided to Families form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:19:18'),
(1539, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:30:24'),
(1540, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:31:15'),
(1541, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:31:15'),
(1542, 5, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:31:18'),
(1543, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:31:34'),
(1544, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:31:35'),
(1545, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:31:38'),
(1546, 5, 'annex4_records_access', 'Accessed Annex 4 damaged houses records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:31:56'),
(1547, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:44:51'),
(1548, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:07:16'),
(1549, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:07:20'),
(1550, 5, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:07:31'),
(1551, 5, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:17:03'),
(1552, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:17:17'),
(1553, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:17:26'),
(1554, 5, 'annex3_entry_created', 'Created casualty entry for: rich ROSETE (Dead) in Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:27:56'),
(1555, 5, 'annex3_submission_success', 'Successfully submitted 1 casualty entries', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:27:56'),
(1556, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:28:04'),
(1557, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:28:11'),
(1558, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:28:17'),
(1559, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:28:24'),
(1560, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:28:28'),
(1561, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:28:30'),
(1562, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:28:31'),
(1563, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:28:34'),
(1564, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:28:36'),
(1565, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:28:38'),
(1566, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:28:41'),
(1567, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:28:43'),
(1568, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:28:49'),
(1569, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:28:51'),
(1570, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:28:55'),
(1571, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:28:56'),
(1572, 5, 'annex3_records_access', 'Accessed Annex 3 records for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:36:28'),
(1573, 5, 'annex5_records_access', 'Accessed Annex 5 agriculture damage records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:36:47'),
(1574, 5, 'annex3_records_access', 'Accessed Annex 3 records for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:38:39'),
(1575, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:48:30'),
(1576, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:49:36'),
(1577, 5, 'annex3_entry_created', 'Created casualty entry for: rich RRR (Dead) in Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:50:07'),
(1578, 5, 'annex3_submission_success', 'Successfully submitted 1 casualty entries', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:50:07'),
(1579, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:50:10'),
(1580, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:50:21'),
(1581, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:50:21'),
(1582, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:50:26'),
(1583, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:50:30'),
(1584, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:50:30'),
(1585, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:55:01'),
(1586, 5, 'annex3_view', 'Viewed casualty record #2 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:55:04'),
(1587, 5, 'annex3_view', 'Viewed casualty record #1 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:55:19'),
(1588, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:56:01'),
(1589, 5, 'annex3_edit_form_access', 'Opened edit form for record #2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:56:05'),
(1590, 5, 'annex3_update', 'Updated record #2 for Asassi: RRR, rich', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:57:35'),
(1591, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:57:37'),
(1592, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:57:45'),
(1593, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:57:45'),
(1594, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:57:49'),
(1595, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:57:49'),
(1596, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:57:49'),
(1597, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:57:53'),
(1598, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:57:53'),
(1599, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:58:19'),
(1600, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:58:19'),
(1601, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:58:29'),
(1602, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:58:30'),
(1603, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:58:40'),
(1604, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:58:40'),
(1605, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:06:56'),
(1606, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:07:00'),
(1607, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:07:00'),
(1608, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:07:08'),
(1609, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:07:22'),
(1610, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:07:28'),
(1611, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:07:28'),
(1612, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:09:47'),
(1613, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:12:42'),
(1614, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:12:46'),
(1615, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:12:46'),
(1616, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:14:17'),
(1617, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:14:21'),
(1618, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:14:21'),
(1619, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:15:33'),
(1620, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:15:35'),
(1621, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:15:35'),
(1622, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:15:39'),
(1623, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:16:54'),
(1624, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:16:57'),
(1625, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:16:57'),
(1626, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:17:02'),
(1627, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:17:02'),
(1628, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:23:13'),
(1629, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:23:15'),
(1630, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:23:17'),
(1631, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:40:42'),
(1632, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:40:48'),
(1633, 5, 'annex5_records_access', 'Accessed Annex 5 agriculture damage records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:40:55'),
(1634, 5, 'annex5_records_access', 'Accessed Annex 5 agriculture damage records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:40:59'),
(1635, 5, 'annex5_delete', 'Permanently deleted record #10 for Asassi: Crops - Rice', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:40:59'),
(1636, 5, 'annex5_records_access', 'Accessed Annex 5 agriculture damage records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:40:59'),
(1637, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:42:28'),
(1638, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:42:32'),
(1639, 5, 'annex3_entry_created', 'Created casualty entry for: rich RRR (Injured) in Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:42:56'),
(1640, 5, 'annex3_submission_success', 'Successfully submitted 1 casualty entries', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:42:56'),
(1641, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:42:58'),
(1642, 5, 'annex3_delete', 'Permanently deleted record #1 for Asassi: RRR, rich', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:43:02'),
(1643, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:43:02'),
(1644, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:43:03'),
(1645, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:43:46'),
(1646, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:43:50'),
(1647, 5, 'annex3_entry_created', 'Created casualty entry for: rich RRR (Injured) in Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:44:13'),
(1648, 5, 'annex3_submission_success', 'Successfully submitted 1 casualty entries', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:44:13'),
(1649, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:44:15'),
(1650, 5, 'annex3_delete', 'Permanently deleted record #2 for Asassi: RRR, rich', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:44:18'),
(1651, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:44:18'),
(1652, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:44:18'),
(1653, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:46:31'),
(1654, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:47:11'),
(1655, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:47:20'),
(1656, 5, 'annex3_entry_created', 'Created casualty entry for: rich RRR (Injured) in Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:47:46'),
(1657, 5, 'annex3_submission_success', 'Successfully submitted 1 casualty entries', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:47:46'),
(1658, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:47:47'),
(1659, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:47:51'),
(1660, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:47:51'),
(1661, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:48:06'),
(1662, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:48:10'),
(1663, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:48:10'),
(1664, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:55:41'),
(1665, 5, 'annex3_delete', 'Permanently deleted record #3 for Asassi: RRR, rich', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:55:45'),
(1666, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:55:45'),
(1667, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:55:45'),
(1668, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:02:33'),
(1669, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:06:51'),
(1670, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:07:02'),
(1671, 5, 'annex3_entry_created', 'Created casualty entry for: rich RRR (Ill) in Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:07:19'),
(1672, 5, 'annex3_submission_success', 'Successfully submitted 1 casualty entries', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:07:19'),
(1673, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:07:21'),
(1674, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:07:25'),
(1675, 5, 'annex3_delete', 'Permanently deleted record #4 for Asassi: RRR, rich', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:07:25'),
(1676, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:07:25'),
(1677, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:08:58'),
(1678, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:09:01'),
(1679, 5, 'annex3_entry_created', 'Created casualty entry for: rich RRR (Ill) in Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:09:20'),
(1680, 5, 'annex3_submission_success', 'Successfully submitted 1 casualty entries', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:09:20'),
(1681, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:09:22'),
(1682, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:09:28'),
(1683, 5, 'annex3_archive', 'Archived record #5 for Asassi: RRR, rich', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:09:28'),
(1684, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:09:28'),
(1685, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:09:57'),
(1686, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:10:01'),
(1687, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:10:04'),
(1688, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:12:39'),
(1689, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:12:41'),
(1690, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:12:43'),
(1691, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:12:51'),
(1692, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:14:21'),
(1693, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:14:23'),
(1694, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:14:24');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1695, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:14:26'),
(1696, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:14:33'),
(1697, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:14:36'),
(1698, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:28:57'),
(1699, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:29:00'),
(1700, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:29:22'),
(1701, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:40:32'),
(1702, 5, 'annex3_archived_access', 'Accessed Annex 3 archived records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:40:35'),
(1703, 5, 'annex3_archived_access', 'Accessed Annex 3 archived records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:40:42'),
(1704, 5, 'annex3_restore', 'Restored record #5 for Asassi: RRR, rich', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:40:42'),
(1705, 5, 'annex3_archived_access', 'Accessed Annex 3 archived records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:40:42'),
(1706, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:40:45'),
(1707, 5, 'annex3_view', 'Viewed casualty record #5 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:40:48'),
(1708, 5, 'annex3_view', 'Viewed casualty record #5 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:45:57'),
(1709, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:48:56'),
(1710, 5, 'annex3_archive', 'Archived record #5 for Asassi: RRR, rich', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:48:56'),
(1711, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:48:56'),
(1712, 5, 'annex3_archived_access', 'Accessed Annex 3 archived records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:48:59'),
(1713, 5, 'annex3_archived_access', 'Accessed Annex 3 archived records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:49:10'),
(1714, 5, 'annex3_restore', 'Restored record #5 for Asassi: RRR, rich', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:49:10'),
(1715, 5, 'annex3_archived_access', 'Accessed Annex 3 archived records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:49:10'),
(1716, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:49:13'),
(1717, 5, 'annex3_view', 'Viewed casualty record #5 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:49:25'),
(1718, 5, 'annex3_records_access', 'Accessed Annex 3 casualties records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:50:08'),
(1719, 5, 'annex3_view', 'Viewed casualty record #5 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:50:10'),
(1720, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:50:45'),
(1721, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:50:46'),
(1722, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:50:47'),
(1723, 5, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:50:51'),
(1724, 5, 'annex8_form_access', 'Accessed Annex 8 form for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:50:51'),
(1725, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:50:54'),
(1726, 5, 'annex14_form_access', 'User accessed the Annex 14 Suspension of Work form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:50:56'),
(1727, 5, 'annex11_form_access', 'User accessed the Annex 11 Communication Lines form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:51:00'),
(1728, 5, 'annex14_form_access', 'User accessed the Annex 14 Suspension of Work form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:51:04'),
(1729, 5, 'annex14_form_access', 'User accessed the Annex 14 Suspension of Work form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:51:05'),
(1730, 5, 'annex11_form_access', 'User accessed the Annex 11 Communication Lines form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:51:07'),
(1731, 5, 'annex14_form_access', 'User accessed the Annex 14 Suspension of Work form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:51:11'),
(1732, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:51:17'),
(1733, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:51:18'),
(1734, 5, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:51:23'),
(1735, 5, 'annex8_form_access', 'Accessed Annex 8 form for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:51:23'),
(1736, 5, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:54:55'),
(1737, 5, 'annex8_form_access', 'Accessed Annex 8 form for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:54:55'),
(1738, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:54:56'),
(1739, 5, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:54:57'),
(1740, 5, 'annex8_form_access', 'Accessed Annex 8 form for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:54:57'),
(1741, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:54:59'),
(1742, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:54:59'),
(1743, 5, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:55:00'),
(1744, 5, 'annex8_form_access', 'Accessed Annex 8 form for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:55:00'),
(1745, 5, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:55:06'),
(1746, 5, 'annex8_form_access', 'Accessed Annex 8 form for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:55:06'),
(1747, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:55:09'),
(1748, 5, 'annex14_form_access', 'User accessed the Annex 14 Suspension of Work form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:55:12'),
(1749, 5, 'annex14_form_access', 'User accessed the Annex 14 Suspension of Work form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:55:23'),
(1750, 5, 'annex14_form_access', 'User accessed the Annex 14 Suspension of Work form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:55:28'),
(1751, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:55:30'),
(1752, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:55:31'),
(1753, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:55:32'),
(1754, 5, 'annex4_form_access', 'User accessed the Annex 4 Damaged Houses form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:55:34'),
(1755, 5, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:55:36'),
(1756, 5, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:55:39'),
(1757, 5, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:56:19'),
(1758, 5, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:56:23'),
(1759, 5, 'annex8_form_access', 'Accessed Annex 8 form for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:56:23'),
(1760, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:56:27'),
(1761, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:56:30'),
(1762, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:56:46'),
(1763, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:56:47'),
(1764, 5, 'annex4_form_access', 'User accessed the Annex 4 Damaged Houses form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:56:51'),
(1765, 5, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:56:52'),
(1766, 5, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:56:56'),
(1767, 5, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:56:58'),
(1768, 5, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:57:01'),
(1769, 5, 'annex8_form_access', 'Accessed Annex 8 form for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:57:01'),
(1770, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:57:03'),
(1771, 5, 'annex11_form_access', 'User accessed the Annex 11 Communication Lines form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:57:05'),
(1772, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:57:09'),
(1773, 5, 'annex14_form_access', 'User accessed the Annex 14 Suspension of Work form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:00:48'),
(1774, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:00:51'),
(1775, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:00:52'),
(1776, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:00:53'),
(1777, 5, 'annex4_form_access', 'User accessed the Annex 4 Damaged Houses form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:01:02'),
(1778, 5, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:01:03'),
(1779, 5, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:01:06'),
(1780, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:02:15'),
(1781, 6, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:16:23'),
(1782, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:22:26'),
(1783, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:22:28'),
(1784, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:22:32'),
(1785, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:22:36'),
(1786, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:22:38'),
(1787, 5, 'annex4_form_access', 'User accessed the Annex 4 Damaged Houses form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:22:42'),
(1788, 5, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:22:45'),
(1789, 5, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:22:49'),
(1790, 5, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:22:53'),
(1791, 5, 'annex8_form_access', 'Accessed Annex 8 form for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:22:53'),
(1792, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:22:55'),
(1793, 5, 'annex11_form_access', 'User accessed the Annex 11 Communication Lines form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:22:57'),
(1794, 5, 'annex14_form_access', 'User accessed the Annex 14 Suspension of Work form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:23:00'),
(1795, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:23:10'),
(1796, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:23:11'),
(1797, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:23:12'),
(1798, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:23:16'),
(1799, 5, 'annex4_form_access', 'User accessed the Annex 4 Damaged Houses form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:23:19'),
(1800, 5, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:23:21'),
(1801, 5, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:23:25'),
(1802, 5, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:23:35'),
(1803, 5, 'annex4_records_access', 'Accessed Annex 4 damaged houses records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:23:49'),
(1804, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:24:24'),
(1805, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:24:26'),
(1806, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:24:28'),
(1807, 5, 'annex4_form_access', 'User accessed the Annex 4 Damaged Houses form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:24:34'),
(1808, 5, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:24:37'),
(1809, 5, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:24:48'),
(1810, 5, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:24:51'),
(1811, 5, 'annex8_form_access', 'Accessed Annex 8 form for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:24:51'),
(1812, 5, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:25:01'),
(1813, 5, 'annex8_form_access', 'Accessed Annex 8 form for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:25:01'),
(1814, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:25:10'),
(1815, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:25:35'),
(1816, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:25:37'),
(1817, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:25:39'),
(1818, 5, 'annex4_form_access', 'User accessed the Annex 4 Damaged Houses form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:25:49'),
(1819, 5, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:25:51'),
(1820, 5, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:25:56'),
(1821, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:25:58'),
(1822, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:00'),
(1823, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:03'),
(1824, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:05'),
(1825, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:08'),
(1826, 5, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:14'),
(1827, 5, 'annex8_form_access', 'Accessed Annex 8 form for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:14'),
(1828, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:17'),
(1829, 5, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:19'),
(1830, 5, 'annex8_form_access', 'Accessed Annex 8 form for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:19'),
(1831, 5, 'annex11_form_access', 'User accessed the Annex 11 Communication Lines form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:21'),
(1832, 5, 'annex14_form_access', 'User accessed the Annex 14 Suspension of Work form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:25'),
(1833, 5, 'annex11_form_access', 'User accessed the Annex 11 Communication Lines form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:29'),
(1834, 5, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:31'),
(1835, 5, 'annex8_form_access', 'Accessed Annex 8 form for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:31'),
(1836, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:32'),
(1837, 5, 'annex11_form_access', 'User accessed the Annex 11 Communication Lines form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:34'),
(1838, 5, 'annex14_form_access', 'User accessed the Annex 14 Suspension of Work form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:36'),
(1839, 5, 'annex14_form_access', 'User accessed the Annex 14 Suspension of Work form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:40'),
(1840, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:41'),
(1841, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:43'),
(1842, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:44'),
(1843, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:47'),
(1844, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:49'),
(1845, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:51'),
(1846, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:53'),
(1847, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:27:11'),
(1848, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:27:14'),
(1849, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:27:30'),
(1850, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:28:00'),
(1851, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:28:05'),
(1852, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:28:06'),
(1853, 5, 'annex4_form_access', 'User accessed the Annex 4 Damaged Houses form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:28:13'),
(1854, 5, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:28:14'),
(1855, 5, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:28:17'),
(1856, 5, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:28:19'),
(1857, 5, 'annex8_form_access', 'Accessed Annex 8 form for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:28:19'),
(1858, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:28:21'),
(1859, 5, 'annex11_form_access', 'User accessed the Annex 11 Communication Lines form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:28:22'),
(1860, 5, 'annex14_form_access', 'User accessed the Annex 14 Suspension of Work form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:28:23'),
(1861, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:28:32'),
(1862, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:28:33'),
(1863, 5, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:28:40'),
(1864, 5, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:28:43'),
(1865, 5, 'annex5_form_access', 'User accessed the Annex 5 Agriculture Damage form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:29:06'),
(1866, 5, 'annex4_form_access', 'User accessed the Annex 4 Damaged Houses form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:29:15'),
(1867, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:29:18'),
(1868, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:29:21'),
(1869, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:29:23'),
(1870, 5, 'annex4_form_access', 'User accessed the Annex 4 Damaged Houses form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:29:31'),
(1871, 5, 'annex1_form_access', 'User accessed the Annex 1 Related Incident form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 10:05:28'),
(1872, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 10:05:30'),
(1873, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 10:05:32'),
(1874, 5, 'annex8_form_access', 'User accessed the Annex 8 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 10:05:36'),
(1875, 5, 'annex8_form_access', 'Accessed Annex 8 form for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 10:05:36'),
(1876, 5, 'annex9_form_access', 'Accessed Annex 9 form for barangay: Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 10:05:38'),
(1877, 5, 'annex11_form_access', 'User accessed the Annex 11 Communication Lines form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 10:05:43'),
(1878, 5, 'annex11_form_access', 'User accessed the Annex 11 Communication Lines form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 10:05:48'),
(1879, 5, 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-01 09:15:10'),
(1880, 5, 'annex3_form_access', 'User accessed the Annex 3 Casualties form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-01 09:16:00'),
(1881, 5, 'form17_form_access', 'User accessed the Form 17 Pre-emptive Evacuation form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-01 09:30:24'),
(1882, 5, 'form17_form_access', 'User accessed the Form 17 Pre-emptive Evacuation form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-01 09:30:43'),
(1883, 5, 'form17_form_access', 'User accessed the Form 17 Pre-emptive Evacuation form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-01 09:34:56'),
(1884, 5, 'form17_form_access', 'User accessed the Form 17 Pre-emptive Evacuation form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-01 09:36:29'),
(1885, 5, 'form17_form_access', 'User accessed the Form 17 Pre-emptive Evacuation form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-01 09:38:08'),
(1886, 5, 'form17_form_access', 'User accessed the Form 17 Pre-emptive Evacuation form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-01 09:38:11'),
(1887, 5, 'form17_form_access', 'User accessed the Form 17 Pre-emptive Evacuation form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-01 09:38:16');

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
(897, 0, '20', 1761988560, '::1'),
(898, 0, '100', 1761989424, '::1'),
(899, 0, '100', 1761989443, '::1'),
(900, 0, '100', 1761989696, '::1'),
(901, 0, '100', 1761989789, '::1'),
(902, 0, '100', 1761989888, '::1'),
(903, 0, '100', 1761989891, '::1'),
(904, 0, '100', 1761989896, '::1');

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
(235, 5, 'page_access', 'Accessed Annex 2 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:44:23'),
(236, 5, 'page_access', 'Accessed Annex 2 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:44:28'),
(237, 5, 'page_access', 'Accessed Annex 2 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:44:29'),
(238, 5, 'page_access', 'Accessed Annex 2 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:50:50'),
(239, 5, 'page_access', 'Accessed Annex 2 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:51:52'),
(240, 5, 'record_view', 'Viewed Annex 2 record #1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:52:02'),
(241, 5, 'record_view', 'Viewed Annex 2 record #1', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:52:24'),
(242, 5, 'edit_form_access', 'Accessed Annex 2 edit form for record #1', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:52:30'),
(243, 5, 'page_access', 'Accessed Annex 2 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:57:02'),
(244, 5, 'edit_form_access', 'Accessed Annex 2 edit form for record #1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:57:11'),
(245, 5, 'page_access', 'Accessed Annex 2 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:57:18'),
(246, 5, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:43:04'),
(247, 5, 'rate_limit_exceeded', 'Annex3 form access rate limit exceeded', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:43:13'),
(248, 5, 'rate_limit_exceeded', 'Annex3 form access rate limit exceeded', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:43:17'),
(249, 5, 'rate_limit_exceeded', 'Annex3 form access rate limit exceeded', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:43:28'),
(250, 5, 'page_access', 'Accessed Annex 5 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:49:39'),
(251, 5, 'page_access', 'Accessed Annex 2 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:55:39'),
(252, 5, 'edit_form_access', 'Accessed Annex 2 edit form for record #1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:55:45'),
(253, 5, 'annex2_update_success', 'Successfully updated Annex2 record #1 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:55:49'),
(254, 5, 'page_access', 'Accessed Annex 2 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:55:49'),
(255, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:58:07'),
(256, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:58:27'),
(257, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 12:58:31'),
(258, 5, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:00:58'),
(259, 5, 'page_access', 'Accessed Annex 5 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:01:07'),
(260, 5, 'page_access', 'Accessed Annex 5 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:01:19'),
(261, 5, 'page_access', 'Accessed Annex 5 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:03:53'),
(262, 5, 'page_access', 'Accessed Annex 5 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:03:59'),
(263, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:04:04'),
(264, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:07:43'),
(265, 5, 'invalid_form_data', 'Missing or invalid field: barangay in Annex 3 submission', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:08:05'),
(266, 5, 'invalid_form_data', 'Missing or invalid field: barangay in Annex 3 submission', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:08:10'),
(267, 5, 'session_hijack_attempt', 'Session fingerprint validation failed', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:09:38'),
(268, 5, 'invalid_form_data', 'Missing or invalid field: barangay in Annex 3 submission', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:10:41'),
(269, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:12:05'),
(270, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:12:11'),
(271, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:12:33'),
(272, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:13:43'),
(273, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:13:59'),
(274, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:16:20'),
(275, 5, 'record_view', 'Viewed Annex 3 record #3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:16:22'),
(276, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:16:33'),
(277, 5, 'session_hijack_attempt', 'Session fingerprint validation failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:16:35'),
(278, 5, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:17:15'),
(279, 5, 'page_access', 'Accessed Annex 5 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:17:24'),
(280, 5, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:17:27'),
(281, 5, 'annex5_submission_success', 'Successfully submitted Annex5 record #10 for Asassi: Crops - Rice', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:18:04'),
(282, 5, 'annex5_submission_success', 'Successfully submitted Annex5 records #10 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:18:04'),
(283, 5, 'page_access', 'Accessed Annex 5 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:18:04'),
(284, 5, 'record_view', 'Viewed Annex 5 record #10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:18:07'),
(285, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:28:14'),
(286, 5, 'record_view', 'Viewed Annex 3 record #3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:28:16'),
(287, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:34:19'),
(288, 5, 'record_view', 'Viewed Annex 3 record #3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:34:21'),
(289, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:34:26'),
(290, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:34:33'),
(291, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:35:56'),
(292, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:36:01'),
(293, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:36:08'),
(294, 5, 'page_access', 'Accessed Annex 5 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:51:16'),
(295, 5, 'record_view', 'Viewed Annex 5 record #10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:51:26'),
(296, 5, 'record_view', 'Viewed Annex 3 record #3', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 01:13:09'),
(297, 5, 'record_view', 'Viewed Annex 3 record #3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 01:41:57'),
(298, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 01:54:53'),
(299, 5, 'record_view', 'Viewed Annex 3 record #3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 01:54:57'),
(300, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 01:55:00'),
(301, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 01:58:14'),
(302, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 01:58:56'),
(303, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 01:59:02'),
(304, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 01:59:05'),
(305, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 01:59:07'),
(306, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:02:12'),
(307, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:02:17'),
(308, 5, 'page_access', 'Accessed Annex 3 archived records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:02:25'),
(309, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:02:57'),
(310, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:03:01'),
(311, 5, 'annex3_archive_success', 'Successfully archived Annex3 record #3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:03:02'),
(312, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:03:02'),
(313, 5, 'record_view', 'Viewed Annex 3 record #3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:03:07'),
(314, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:03:18'),
(315, 5, 'record_view', 'Viewed Annex 3 record #3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:07:57'),
(316, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:08:12'),
(317, 5, 'record_view', 'Viewed Annex 3 record #3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:08:15'),
(318, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:09:15'),
(319, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:09:30'),
(320, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:09:45'),
(321, 5, 'annex3_update_success', 'Successfully updated Annex3 record #3 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:13:04'),
(322, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:13:08'),
(323, 5, 'annex3_update_success', 'Successfully updated Annex3 record #3 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:13:27'),
(324, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:13:29'),
(325, 5, 'annex3_update_success', 'Successfully updated Annex3 record #3 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:13:46'),
(326, 5, 'annex3_update_success', 'Successfully updated Annex3 record #3 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:13:54'),
(327, 5, 'record_view', 'Viewed Annex 3 record #3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:13:58'),
(328, 5, 'record_view', 'Viewed Annex 3 record #3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:13:58'),
(329, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:14:00'),
(330, 5, 'annex3_update_success', 'Successfully updated Annex3 record #3 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:15:21'),
(331, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:15:21'),
(332, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:20:21'),
(333, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:28:50'),
(334, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:31:56'),
(335, 5, 'annex3_update_success', 'Successfully updated Annex3 record #3 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:32:07'),
(336, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:32:07'),
(337, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:33:11'),
(338, 5, 'annex3_update_success', 'Successfully updated Annex3 record #2 for Asassi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:33:26'),
(339, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:33:26'),
(340, 5, 'invalid_form_data', 'Missing or invalid field: barangay in Annex 3 submission', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:34:03'),
(341, 5, 'invalid_form_data', 'Missing or invalid field: barangay in Annex 3 submission', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:34:56'),
(342, 5, 'invalid_form_data', 'Missing or invalid field: barangay in Annex 3 submission', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 02:59:10'),
(343, 5, 'session_hijack_attempt', 'Session fingerprint validation failed', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:11:44'),
(344, 5, 'rate_limit_exceeded', 'Annex3 form access rate limit exceeded', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:15:05'),
(345, 5, 'rate_limit_exceeded', 'Annex3 form access rate limit exceeded', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:15:09'),
(346, 5, 'invalid_form_data', 'Missing or invalid field: barangay in Annex 3 submission', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:18:55'),
(347, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:30:24'),
(348, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:31:15'),
(349, 5, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:31:18'),
(350, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:31:35'),
(351, 5, 'page_access', 'Accessed Annex 4 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:31:56'),
(352, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:44:51'),
(353, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:07:16'),
(354, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:07:20'),
(355, 5, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:07:31'),
(356, 5, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:17:03'),
(357, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:17:17'),
(358, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:17:26'),
(359, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:28:04'),
(360, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:28:11'),
(361, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:28:17'),
(362, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:28:30'),
(363, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:28:38'),
(364, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:28:49'),
(365, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:28:56'),
(366, 5, 'page_access', 'Accessed Annex 5 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:36:47'),
(367, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:48:30'),
(368, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:49:36'),
(369, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:50:10'),
(370, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:50:21'),
(371, 5, 'delete_failure', 'Failed to delete Annex3 record #1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:50:21'),
(372, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:50:21'),
(373, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:50:26'),
(374, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:50:30'),
(375, 5, 'delete_failure', 'Failed to delete Annex3 record #2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:50:30'),
(376, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:50:30'),
(377, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:55:01'),
(378, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:56:01'),
(379, 5, 'annex3_update_success', 'Successfully updated Annex3 record #2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:57:35'),
(380, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:57:37'),
(381, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:57:45'),
(382, 5, 'delete_failure', 'Failed to delete Annex3 record #2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:57:45'),
(383, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:57:45'),
(384, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:57:49'),
(385, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:57:49'),
(386, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:57:49'),
(387, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:57:53'),
(388, 5, 'delete_failure', 'Failed to delete Annex3 record #2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:57:53'),
(389, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:57:53'),
(390, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:58:19'),
(391, 5, 'delete_failure', 'Failed to delete Annex3 record #2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:58:19'),
(392, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:58:19'),
(393, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:58:29'),
(394, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:58:30'),
(395, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:58:40'),
(396, 5, 'delete_failure', 'Failed to delete Annex3 record #2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:58:40'),
(397, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 04:58:40'),
(398, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:09:47'),
(399, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:12:42'),
(400, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:12:46'),
(401, 5, 'delete_failure', 'Failed to delete Annex3 record #2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:12:46'),
(402, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:12:46'),
(403, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:14:17'),
(404, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:14:21'),
(405, 5, 'delete_failure', 'Failed to delete Annex3 record #2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:14:21'),
(406, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:14:21'),
(407, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:15:33'),
(408, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:15:35'),
(409, 5, 'delete_failure', 'Failed to delete Annex3 record #2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:15:35'),
(410, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:15:35'),
(411, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:15:39'),
(412, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:16:54'),
(413, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:16:57'),
(414, 5, 'delete_failure', 'Failed to delete Annex3 record #2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:16:57'),
(415, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:16:57'),
(416, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:17:02'),
(417, 5, 'rate_limit_exceeded', 'Annex3 delete rate limit exceeded', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:17:02'),
(418, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:17:02'),
(419, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:23:17'),
(420, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:40:42'),
(421, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:40:48'),
(422, 5, 'page_access', 'Accessed Annex 5 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:40:55'),
(423, 5, 'page_access', 'Accessed Annex 5 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:40:59'),
(424, 5, 'annex5_delete_success', 'Successfully deleted Annex5 record #10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:40:59'),
(425, 5, 'page_access', 'Accessed Annex 5 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:40:59'),
(426, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:42:28'),
(427, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:42:32'),
(428, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:42:58'),
(429, 5, 'annex3_delete_success', 'Successfully deleted Annex3 record #1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:43:02'),
(430, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:43:02'),
(431, 5, 'rate_limit_exceeded', 'Annex3 delete rate limit exceeded', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:43:03'),
(432, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:43:03'),
(433, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:43:46'),
(434, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:43:50'),
(435, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:44:15'),
(436, 5, 'annex3_delete_success', 'Successfully deleted Annex3 record #2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:44:18'),
(437, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:44:18'),
(438, 5, 'unauthorized_access', 'Attempted to delete Annex3 record #2 without permission', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:44:18'),
(439, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:44:18'),
(440, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:46:31'),
(441, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:47:11'),
(442, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:47:20'),
(443, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:47:47'),
(444, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:47:51'),
(445, 5, 'rate_limit_exceeded', 'Annex3 delete rate limit exceeded', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:47:51'),
(446, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:47:51'),
(447, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:48:06'),
(448, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:48:10'),
(449, 5, 'delete_failure', 'Failed to delete Annex3 record #3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:48:10'),
(450, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:48:10'),
(451, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:55:41'),
(452, 5, 'annex3_delete_success', 'Successfully deleted Annex3 record #3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:55:45'),
(453, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:55:45'),
(454, 5, 'unauthorized_access', 'Attempted to delete Annex3 record #3 without permission', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:55:45'),
(455, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 05:55:45'),
(456, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:02:33'),
(457, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:06:51'),
(458, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:07:02'),
(459, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:07:21'),
(460, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:07:25'),
(461, 5, 'annex3_delete_success', 'Successfully deleted Annex3 record #4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:07:25'),
(462, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:07:25');
INSERT INTO `security_logs` (`id`, `user_id`, `event_type`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(463, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:08:58'),
(464, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:09:01'),
(465, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:09:22'),
(466, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:09:28'),
(467, 5, 'annex3_archive_success', 'Successfully archived Annex3 record #5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:09:28'),
(468, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:09:28'),
(469, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:09:57'),
(470, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:10:01'),
(471, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:10:04'),
(472, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:12:43'),
(473, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:12:51'),
(474, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:14:24'),
(475, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:14:26'),
(476, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:14:33'),
(477, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:14:36'),
(478, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:28:57'),
(479, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:29:00'),
(480, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:29:22'),
(481, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:40:32'),
(482, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:40:45'),
(483, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:48:56'),
(484, 5, 'annex3_archive_success', 'Successfully archived Annex3 record #5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:48:56'),
(485, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:48:56'),
(486, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:49:13'),
(487, 5, 'page_access', 'Accessed Annex 3 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:50:08'),
(488, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:50:47'),
(489, 5, 'form_access', 'Accessed Annex 11 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:51:00'),
(490, 5, 'form_access', 'Accessed Annex 11 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:51:07'),
(491, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:51:18'),
(492, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:55:32'),
(493, 5, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:55:36'),
(494, 5, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:55:39'),
(495, 5, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:56:19'),
(496, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:56:47'),
(497, 5, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:56:52'),
(498, 5, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:56:56'),
(499, 5, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:56:58'),
(500, 5, 'form_access', 'Accessed Annex 11 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:57:05'),
(501, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:00:53'),
(502, 5, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:01:03'),
(503, 5, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:01:06'),
(504, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:22:38'),
(505, 5, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:22:45'),
(506, 5, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:22:49'),
(507, 5, 'form_access', 'Accessed Annex 11 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:22:57'),
(508, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:23:12'),
(509, 5, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:23:21'),
(510, 5, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:23:25'),
(511, 5, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:23:35'),
(512, 5, 'page_access', 'Accessed Annex 4 records page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:23:49'),
(513, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:24:28'),
(514, 5, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:24:37'),
(515, 5, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:24:48'),
(516, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:25:39'),
(517, 5, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:25:51'),
(518, 5, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:25:56'),
(519, 5, 'form_access', 'Accessed Annex 11 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:21'),
(520, 5, 'form_access', 'Accessed Annex 11 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:29'),
(521, 5, 'form_access', 'Accessed Annex 11 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:34'),
(522, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:44'),
(523, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:47'),
(524, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:26:53'),
(525, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:27:30'),
(526, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:28:06'),
(527, 5, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:28:14'),
(528, 5, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:28:17'),
(529, 5, 'form_access', 'Accessed Annex 11 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:28:22'),
(530, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:28:32'),
(531, 5, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:28:40'),
(532, 5, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:28:43'),
(533, 5, 'form_access', 'Accessed Annex 5 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:29:06'),
(534, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:29:23'),
(535, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 10:05:32'),
(536, 5, 'form_access', 'Accessed Annex 11 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 10:05:43'),
(537, 5, 'form_access', 'Accessed Annex 11 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 10:05:48'),
(538, 5, 'form_access', 'Accessed Annex 3 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-01 09:16:00'),
(539, 5, 'form_access', 'Accessed Form 17 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-01 09:30:24'),
(540, 5, 'form_access', 'Accessed Form 17 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-01 09:30:43'),
(541, 5, 'form_access', 'Accessed Form 17 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-01 09:34:56'),
(542, 5, 'form_access', 'Accessed Form 17 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-01 09:36:29'),
(543, 5, 'form_access', 'Accessed Form 17 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-01 09:38:08'),
(544, 5, 'form_access', 'Accessed Form 17 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-01 09:38:11'),
(545, 5, 'form_access', 'Accessed Form 17 form', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-01 09:38:16');

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
--

INSERT INTO `users` (`id`, `full_name`, `barangay`, `contact_number`, `username`, `password`, `user_role`, `is_active`, `created_by`, `created_at`, `updated_at`, `region`, `province`, `city`) VALUES
(1, 'System Administrator', 'MDRRMO-BAGGAO', '09123456789', 'admin', '$2y$10$YxKi2fr1PyOTAKf2sniSIOOAYz/T5TFHT0k7WF8I06DBXZWMZKUu.', 'admin', 1, NULL, '2025-10-10 03:19:02', '2025-10-12 11:43:29', NULL, NULL, NULL),
(2, 'Juan Dela Cruz', 'Aggugaddan', '09111111111', 'juan_agg', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 1, NULL, '2025-10-10 03:19:02', '2025-10-10 03:19:02', NULL, NULL, NULL),
(3, 'Maria Santos', 'Alba', '09122222222', 'maria_alb', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 1, NULL, '2025-10-10 03:19:02', '2025-10-10 03:19:02', NULL, NULL, NULL),
(4, 'Pedro Reyes', 'Asassi', '09133333333', 'pedro_asa', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 1, NULL, '2025-10-10 03:19:02', '2025-10-10 03:19:02', NULL, NULL, NULL),
(5, 'richmond', 'Asassi', '09917819410', 'rich', '$2y$10$DE4bHIPnfxmYGsXkl13u3.oU5n0rKSDfH/BSHJGFtFI3/3ilz3Oku', 'user', 1, 1, '2025-10-10 03:23:14', '2025-10-16 01:13:33', NULL, NULL, NULL),
(6, 'MON', 'Alba', '09876543212', 'MON', '$2y$10$tptUSkRGITiq.VwwZJt21u.O7boWk4zcxv0UMbfLxlJ5gONnk9jgy', 'user', 1, 1, '2025-10-10 06:37:09', '2025-10-12 09:30:44', NULL, NULL, NULL);

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
(66, 5, '0e224bb7a04095bb683c8048cb4e414cbf8d81d8401cff9b454fcf08de324840', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 08:44:10', '2025-10-30 13:09:38', 1),
(67, 5, 'cebb38d8704af798bfb0cf0e3562e16283fab9d6f0ddd83725f50ecad26c0e72', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:09:56', '2025-10-30 13:16:35', 1),
(68, 5, '3c723e65dee0fdcea7871b705af64d1153bd6f4771c4b2e67518ccc3989f3d55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:17:00', '2025-10-31 03:11:44', 1),
(69, 5, 'a5c19b8d4f6e34a04c36656f1368f1e07aef66a41a0d43e6cc32605f7a8a814b', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 03:12:06', '2025-10-31 08:12:58', 0),
(70, 1, '54be75ffdee6367a8d0a7ec1ec0fb8d3b4659bc387dfbf33b2bb53f338b5ab33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:13:11', '2025-10-31 08:17:30', 0),
(71, 5, '3b3b743929eb61276867e35ba96d8cb90c17493e5f9d19f884eac7a8afbedc33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 08:17:38', '2025-10-31 10:05:48', 1),
(72, 5, '404ef7e8e4c9753a7d6dd0304214781b1e5d183ef9c8b20255ee31fe9a2190bc', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-01 09:15:06', '2025-11-01 09:38:16', 1);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_annex3_casualties_summary`
-- (See below for the actual view)
--
CREATE TABLE `vw_annex3_casualties_summary` (
`barangay` varchar(100)
,`category` enum('Dead','Injured','Ill','Missing')
,`validated` enum('Yes','No')
,`total_count` bigint(21)
,`male_count` bigint(21)
,`female_count` bigint(21)
,`average_age` decimal(7,4)
,`first_report` timestamp
,`last_report` timestamp
);

-- --------------------------------------------------------

--
-- Structure for view `vw_annex3_casualties_summary`
--
DROP TABLE IF EXISTS `vw_annex3_casualties_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_annex3_casualties_summary`  AS SELECT `annex3_casualties`.`barangay` AS `barangay`, `annex3_casualties`.`category` AS `category`, `annex3_casualties`.`validated` AS `validated`, count(0) AS `total_count`, count(case when `annex3_casualties`.`sex` = 'Male' then 1 end) AS `male_count`, count(case when `annex3_casualties`.`sex` = 'Female' then 1 end) AS `female_count`, avg(`annex3_casualties`.`age`) AS `average_age`, min(`annex3_casualties`.`created_at`) AS `first_report`, max(`annex3_casualties`.`created_at`) AS `last_report` FROM `annex3_casualties` GROUP BY `annex3_casualties`.`barangay`, `annex3_casualties`.`category`, `annex3_casualties`.`validated` ;

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
-- Indexes for table `annex2_affected_population`
--
ALTER TABLE `annex2_affected_population`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_location` (`region`,`province`,`city`,`barangay`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_annex2_archived` (`is_archived`),
  ADD KEY `idx_annex2_barangay` (`barangay`),
  ADD KEY `idx_annex2_cumulative` (`affected_families_cumulative`,`affected_persons_cumulative`);

--
-- Indexes for table `annex3_casualties`
--
ALTER TABLE `annex3_casualties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_barangay` (`barangay`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_validated` (`validated`),
  ADD KEY `idx_location` (`region`,`province`,`city`,`barangay`),
  ADD KEY `idx_annex3_archived` (`is_archived`),
  ADD KEY `annex3_casualties_ibfk_2` (`updated_by`);

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
  ADD KEY `idx_location` (`region`,`province`,`city`,`barangay`),
  ADD KEY `idx_service_provider` (`service_provider`),
  ADD KEY `idx_interruption_date` (`interruption_datetime`),
  ADD KEY `idx_restored_date` (`restored_datetime`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_annex9_archived` (`is_archived`);

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
-- Indexes for table `annex20_assistance_provided`
--
ALTER TABLE `annex20_assistance_provided`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_location` (`region`,`province`,`city`),
  ADD KEY `idx_cluster` (`cluster`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_annex20_archived` (`is_archived`),
  ADD KEY `idx_amount` (`amount`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `annex2_affected_population`
--
ALTER TABLE `annex2_affected_population`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `annex3_casualties`
--
ALTER TABLE `annex3_casualties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `annex4_damaged_houses`
--
ALTER TABLE `annex4_damaged_houses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `annex5_agriculture_damage`
--
ALTER TABLE `annex5_agriculture_damage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `annex8_road_bridge_status`
--
ALTER TABLE `annex8_road_bridge_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `annex9_power_supply`
--
ALTER TABLE `annex9_power_supply`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `annex11_communication_lines`
--
ALTER TABLE `annex11_communication_lines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `annex14_suspension_work`
--
ALTER TABLE `annex14_suspension_work`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `annex20_assistance_provided`
--
ALTER TABLE `annex20_assistance_provided`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1888;

--
-- AUTO_INCREMENT for table `barangay_locks`
--
ALTER TABLE `barangay_locks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=905;

--
-- AUTO_INCREMENT for table `security_logs`
--
ALTER TABLE `security_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=546;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `annex1_related_incidents`
--
ALTER TABLE `annex1_related_incidents`
  ADD CONSTRAINT `annex1_related_incidents_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `annex2_affected_population`
--
ALTER TABLE `annex2_affected_population`
  ADD CONSTRAINT `fk_annex2_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `annex3_casualties`
--
ALTER TABLE `annex3_casualties`
  ADD CONSTRAINT `annex3_casualties_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `annex3_casualties_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
  ADD CONSTRAINT `annex9_power_supply_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `annex14_suspension_work`
--
ALTER TABLE `annex14_suspension_work`
  ADD CONSTRAINT `annex14_suspension_work_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `annex20_assistance_provided`
--
ALTER TABLE `annex20_assistance_provided`
  ADD CONSTRAINT `annex20_assistance_provided_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
