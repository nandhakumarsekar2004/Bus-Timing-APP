-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 28, 2024 at 01:51 PM
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
-- Database: `bus_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`) VALUES
(4, 'Nandhakumar', 'f1e136392ace73f13a7a6d57fc15885f'),
(5, 'Nandhakumar Sekar', 'f1e136392ace73f13a7a6d57fc15885f');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `description`, `file_path`, `created_at`) VALUES
(20, 'ki', 'ko', '../uploads/icon-concept---time-traveler-bus--imagine-an-icon-.png', '2024-11-23 06:53:28'),
(21, 'hgh', 'ghgg', '../../uploads/stock-vector-creative-covers-or-horizontal-posters-concept-in-modern-minimal-style-for-corporate-identity-2163500519.jpg', '2024-11-23 06:54:20');

-- --------------------------------------------------------

--
-- Table structure for table `buses`
--

CREATE TABLE `buses` (
  `bus_id` int(11) NOT NULL,
  `from_location` varchar(255) NOT NULL,
  `to_location` varchar(255) NOT NULL,
  `bus_name` varchar(255) NOT NULL,
  `bus_number` varchar(50) NOT NULL,
  `bus_route` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buses`
--

INSERT INTO `buses` (`bus_id`, `from_location`, `to_location`, `bus_name`, `bus_number`, `bus_route`, `created_at`) VALUES
(5, 'Papparapatti', 'Dharmapuri', 'Goverment Bus', '4A', 'Straight', '2024-10-14 13:07:42'),
(6, 'Papparapatti', 'Pennagaram', 'VRT', 'Private Bus', 'Straight Papparapatti - Pennagaram', '2024-10-14 13:07:42'),
(7, 'Salem', 'Papparapatti', 'Goverment Bus', '12B', 'Straight', '2024-10-14 13:07:42'),
(8, 'Salem', 'Papparapatti', 'Goverment Bus', '12B', 'Straight', '2024-10-14 13:30:00'),
(9, 'Nagadasampatti', 'Papparapatti', 'Government ATC Bus', 'TNSTC ATC ', 'Nagadasampatti to Thirupathur, Krishnagiri', '2024-10-14 14:03:41'),
(10, 'Papparapatti', 'Pennagaram', 'Government Bus Free Ticket for Ladies', '18A', 'Straight Route', '2024-10-15 13:09:44'),
(11, 'Thirupathi', 'Chennai', 'BJP', '12C', 'Straight', '2024-10-18 05:08:47'),
(13, 'Papparapatti', 'Dharmapuri', 'Government Bus (Free Bus Tickets for Ladies)', '50', 'Indur Route Dharmapuri Bus', '2024-10-22 02:07:04'),
(14, 'Papparapatti', 'Dharmapuri', 'Government Bus (Free Bus Tickets for Ladies)', '4', 'Paadi, Sekkodi Route Bus', '2024-10-22 02:07:04'),
(15, 'Papparapatti', 'Dharmapuri', 'Government Bus (Free Bus Tickets for Ladies)', '12C', 'Paadi, Sekkodi Route Bus', '2024-10-22 02:07:04'),
(16, 'Papparapatti', 'Dharmapuri', 'RBS (Private Bus)', '', 'Dharmapuri Route Bus', '2024-10-22 02:07:04'),
(25, 'City A', 'City B', 'Express Bus', '123', 'Route A', '2024-11-23 08:19:18');

-- --------------------------------------------------------

--
-- Table structure for table `bus_routes`
--

CREATE TABLE `bus_routes` (
  `id` int(11) NOT NULL,
  `from_location` varchar(255) DEFAULT NULL,
  `to_location` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bus_timings`
--

CREATE TABLE `bus_timings` (
  `timing_id` int(11) NOT NULL,
  `bus_id` int(11) NOT NULL,
  `departure_time` varchar(8) DEFAULT NULL,
  `arrival_time` varchar(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bus_timings`
--

INSERT INTO `bus_timings` (`timing_id`, `bus_id`, `departure_time`, `arrival_time`) VALUES
(5, 5, '08:20:00', '08:20:00'),
(6, 6, '08:15:00', '08:20:00'),
(7, 7, '06:00:00', '06:00:00'),
(8, 8, '06:00:00', '06:00:00'),
(9, 9, '16:50:00', '16:55:00'),
(10, 10, '08:45:00', '08:50:00'),
(11, 11, '00:00:00', '00:00:00'),
(13, 13, '04:31:00', '04:34:00'),
(14, 14, '05:00:00', '05:02:00'),
(15, 15, '06:15:00', '06:17:00'),
(16, 16, '07:00:00', '07:04:00'),
(25, 25, '02:00 PM', '02:10 PM');

-- --------------------------------------------------------

--
-- Table structure for table `contact_data`
--

CREATE TABLE `contact_data` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `device_notification_status`
--

CREATE TABLE `device_notification_status` (
  `id` int(11) NOT NULL,
  `device_id` varchar(255) NOT NULL,
  `last_viewed_notification_id` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `device_notification_status`
--

INSERT INTO `device_notification_status` (`id`, `device_id`, `last_viewed_notification_id`, `created_at`, `updated_at`) VALUES
(1, 'device_670d28e1ce2790.60999159', 0, '2024-10-14 14:32:40', '2024-10-14 14:32:40'),
(2, 'device_670e033d0cf534.64674399', 0, '2024-10-15 05:53:01', '2024-10-15 05:53:01'),
(3, 'device_670e070f7f4086.93532393', 0, '2024-10-15 06:09:19', '2024-10-15 06:09:19'),
(4, 'device_670e21df1cd196.50175468', 0, '2024-10-15 08:03:43', '2024-10-15 08:03:43'),
(5, 'device_670e631aa246c9.99549608', 0, '2024-10-15 12:42:33', '2024-10-15 12:42:33'),
(6, 'device_670faafb5c4754.70624105', 0, '2024-10-16 12:00:59', '2024-10-16 12:00:59'),
(7, 'device_67110b86811bd1.72099729', 0, '2024-10-17 13:05:10', '2024-10-17 13:05:10'),
(8, 'device_6711e71b4ccab3.07352841', 0, '2024-10-18 04:42:03', '2024-10-18 04:42:03'),
(9, 'device_6711ec1bca5760.22063670', 0, '2024-10-18 05:03:23', '2024-10-18 05:03:23'),
(10, 'device_6711edd98ad030.20388067', 0, '2024-10-18 05:10:49', '2024-10-18 05:10:49'),
(11, 'device_671387876c00e5.68561547', 0, '2024-10-19 10:18:47', '2024-10-19 10:18:47'),
(12, 'device_6714cd0be97e01.74379210', 0, '2024-10-20 09:27:39', '2024-10-20 09:27:39'),
(13, 'device_67165bd861e593.23929201', 0, '2024-10-21 13:49:12', '2024-10-21 13:49:12'),
(14, 'device_67167aa3222ac2.78759386', 0, '2024-10-21 16:00:35', '2024-10-21 16:00:35'),
(15, 'device_67170729b55350.10280290', 0, '2024-10-22 02:00:09', '2024-10-22 02:00:09'),
(16, 'device_671b96e39cd973.12760332', 0, '2024-10-25 13:02:27', '2024-10-25 13:02:27'),
(17, 'device_671bb9381dfa54.57447472', 0, '2024-10-25 15:28:56', '2024-10-25 15:28:56'),
(18, 'device_671cf2da2ce6e0.80194103', 0, '2024-10-26 13:47:06', '2024-10-26 13:47:06'),
(19, 'device_671cf463385563.63747611', 0, '2024-10-26 13:53:39', '2024-10-26 13:53:39'),
(20, 'device_67209ebe5ea9e2.45522768', 0, '2024-10-29 08:37:18', '2024-10-29 08:37:18'),
(21, 'device_6720f03493df10.75729947', 0, '2024-10-29 14:24:52', '2024-10-29 14:24:52'),
(22, 'device_6721df8f476e79.52399163', 0, '2024-10-30 07:26:07', '2024-10-30 07:26:07'),
(23, 'device_67248295baddb0.22234352', 0, '2024-11-01 07:26:13', '2024-11-01 07:26:13'),
(24, 'device_6726027c0aa812.39452633', 0, '2024-11-02 10:44:12', '2024-11-02 10:44:12'),
(25, 'device_672a0df47ca035.25485006', 0, '2024-11-05 12:22:12', '2024-11-05 12:22:12'),
(26, 'device_6732fb2e7e0352.85993535', 0, '2024-11-12 06:52:30', '2024-11-12 06:52:30'),
(27, 'device_673c18fadd96d9.25854302', 0, '2024-11-19 04:50:02', '2024-11-19 04:50:02'),
(28, 'device_67416527df3754.29194790', 0, '2024-11-23 05:16:23', '2024-11-23 05:16:23'),
(29, 'device_67443cb8f17734.43059981', 0, '2024-11-25 09:00:41', '2024-11-25 09:00:41'),
(30, 'device_674470d87ef813.03078121', 0, '2024-11-25 12:43:04', '2024-11-25 12:43:04');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `priority` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `title`, `message`, `created_at`, `is_read`, `priority`) VALUES
(41, 'hi', 'bye', '2024-10-14 15:20:21', 0, NULL),
(44, 'hello', 'hi bye', '2024-11-25 12:42:58', 0, 'low'),
(45, 'hi byet', 'cgffggggggggggggggggg', '2024-11-25 12:43:35', 0, 'high');

-- --------------------------------------------------------

--
-- Table structure for table `user_notification_status`
--

CREATE TABLE `user_notification_status` (
  `user_id` int(11) NOT NULL,
  `last_viewed_notification_id` int(11) DEFAULT NULL,
  `last_viewed_announcement_id` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_notification_status`
--

INSERT INTO `user_notification_status` (`user_id`, `last_viewed_notification_id`, `last_viewed_announcement_id`) VALUES
(1, 44, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `buses`
--
ALTER TABLE `buses`
  ADD PRIMARY KEY (`bus_id`);

--
-- Indexes for table `bus_routes`
--
ALTER TABLE `bus_routes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `from_location` (`from_location`,`to_location`);

--
-- Indexes for table `bus_timings`
--
ALTER TABLE `bus_timings`
  ADD PRIMARY KEY (`timing_id`),
  ADD KEY `bus_id` (`bus_id`);

--
-- Indexes for table `contact_data`
--
ALTER TABLE `contact_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `device_notification_status`
--
ALTER TABLE `device_notification_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_id` (`device_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`);

--
-- Indexes for table `user_notification_status`
--
ALTER TABLE `user_notification_status`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `buses`
--
ALTER TABLE `buses`
  MODIFY `bus_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `bus_routes`
--
ALTER TABLE `bus_routes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bus_timings`
--
ALTER TABLE `bus_timings`
  MODIFY `timing_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `contact_data`
--
ALTER TABLE `contact_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `device_notification_status`
--
ALTER TABLE `device_notification_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bus_timings`
--
ALTER TABLE `bus_timings`
  ADD CONSTRAINT `bus_timings_ibfk_1` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`bus_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
