-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 12, 2025 at 04:55 PM
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
(2, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 'Flooding', '2025-10-10 15:13:00', '', '', '', 1, '2025-10-10 07:13:38', '2025-10-12 07:45:01', 0),
(3, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Asassi', 'Flooding', '2025-10-12 17:20:00', 'dakkel danumen', 'The LDRRMO deployed emergency response teams to the affected area', '', 1, '2025-10-12 09:21:15', '2025-10-12 09:21:15', 0),
(4, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 'Landslide', '2025-10-12 12:14:00', '', '', '', 6, '2025-10-12 12:14:45', '2025-10-12 13:53:54', 0),
(5, 'REGION II', 'CAGAYAN', 'BAGGAO', 'MDRRMO-BAGGAO', 'Landslide', '2025-10-12 22:21:00', '', '', '', 1, '2025-10-12 14:22:01', '2025-10-12 14:22:01', 0);

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
(3, 'REGION II', 'CAGAYAN', 'BAGGAO', 'Alba', 4, 5, 9, '54000', '', 6, '2025-10-12 14:53:15', '2025-10-12 14:53:15', 0);

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
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `annex5_agriculture_damage`
--

INSERT INTO `annex5_agriculture_damage` (`id`, `region`, `province`, `city`, `barangay`, `classification`, `type`, `affected_people`, `damage_value`, `no_recovery_area`, `with_recovery_area`, `total_crop_area`, `production_loss_volume`, `animal_heads`, `fisheries_details`, `totally_damaged`, `partially_damaged`, `total_damaged`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'REGION II', 'CAGAYAN', 'BAGGAO', 'asassi', 'Fisheries', '', 1, 5000.00, 0.00, 0.00, 0.00, 0.00, 0, '', 0, 0, 0, 1, '2025-10-10 03:54:34', '2025-10-10 03:54:34');

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
(1, 6, 'annex4_submission', 'Submitted damaged houses report for Alba', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-10-12 14:53:15');

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
(1, 6, 'annex4_submission', 1760280795, '::1');

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
(39, 6, '08b181b0a3ff131232ec92a94158f03aeffebf6d768833c7d1f54abb43d68617', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-10-12 14:28:39', '2025-10-12 14:53:15', 1);

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
  ADD KEY `idx_location` (`region`,`province`,`city`,`barangay`),
  ADD KEY `idx_classification` (`classification`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_created_at` (`created_at`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `annex4_damaged_houses`
--
ALTER TABLE `annex4_damaged_houses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `annex5_agriculture_damage`
--
ALTER TABLE `annex5_agriculture_damage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `barangay_locks`
--
ALTER TABLE `barangay_locks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `security_logs`
--
ALTER TABLE `security_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

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
-- Constraints for table `annex5_agriculture_damage`
--
ALTER TABLE `annex5_agriculture_damage`
  ADD CONSTRAINT `annex5_agriculture_damage_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
