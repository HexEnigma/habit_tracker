-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 07, 2025 at 06:18 PM
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
-- Database: `habit_tracker`
--

-- --------------------------------------------------------

--
-- Table structure for table `achievement_definitions`
--

CREATE TABLE `achievement_definitions` (
  `id` int(11) NOT NULL,
  `achievement_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon_class` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `achievement_definitions`
--

INSERT INTO `achievement_definitions` (`id`, `achievement_name`, `description`, `icon_class`) VALUES
(1, 'first-habit', 'Created your first habit', 'fa-seedling'),
(2, 'three-day-streak', 'Maintained a 3-day streak on any habit', 'fa-calendar-check'),
(3, 'seven-day-streak', 'Maintained a 7-day streak on any habit', 'fa-fire'),
(4, 'fourteen-day-streak', 'Maintained a 14-day streak on any habit', 'fa-fire-flame-curved'),
(5, 'thirty-day-streak', 'Maintained a 30-day streak on any habit', 'fa-award'),
(6, 'three-habits', 'Created 3 active habits', 'fa-list-check'),
(7, 'ten-points', 'Earned 10 points', 'fa-star'),
(8, 'group-explorer', 'Joined your first group', 'fa-users'),
(9, 'social-butterfly', 'Joined 3 different groups', 'fa-user-friends'),
(10, 'habit-mentor', 'Shared 5 habits with group', 'fa-share-alt');

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `allow_member_visibility` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `name`, `description`, `created_by`, `is_public`, `created_at`, `allow_member_visibility`) VALUES
(1, 'Morning Birds', 'Test', 1, 1, '2025-09-04 20:55:44', 1),
(2, 'দারুল আরকাম', 'প্রতিদিন আমরা প্রত্যেকে পাঁচটি করে কোরআনের আয়াত ও তার ব্যাখ্যা পড়বো এবং বন্ধুদেরকে শেয়ার করব ।', 7, 1, '2025-09-06 05:16:40', 1),
(5, 'Habit with friends', '', 6, 1, '2025-09-07 06:33:21', 1),
(6, 'Night Owl', '', 10, 1, '2025-09-07 06:56:50', 1);

-- --------------------------------------------------------

--
-- Table structure for table `group_members`
--

CREATE TABLE `group_members` (
  `id` int(11) NOT NULL,
  `group_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('admin','member') DEFAULT 'member'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `group_members`
--

INSERT INTO `group_members` (`id`, `group_id`, `user_id`, `joined_at`, `role`) VALUES
(1, 1, 1, '2025-09-04 20:55:44', 'admin'),
(3, 1, 3, '2025-09-04 21:02:30', 'member'),
(4, 1, 5, '2025-09-06 04:01:20', 'member'),
(5, 2, 7, '2025-09-06 05:16:40', 'admin'),
(6, 1, 7, '2025-09-06 05:17:04', 'member'),
(7, 2, 1, '2025-09-06 16:46:49', 'member'),
(13, 5, 6, '2025-09-07 06:33:21', 'admin'),
(14, 2, 6, '2025-09-07 06:33:36', 'member'),
(15, 1, 6, '2025-09-07 06:33:46', 'member'),
(16, 6, 10, '2025-09-07 06:56:50', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `group_messages`
--

CREATE TABLE `group_messages` (
  `id` int(11) NOT NULL,
  `group_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `group_messages`
--

INSERT INTO `group_messages` (`id`, `group_id`, `user_id`, `message`, `created_at`) VALUES
(1, 1, 1, 'hey', '2025-09-04 20:56:21'),
(2, 1, 2, 'hii', '2025-09-05 21:51:02'),
(3, 1, 5, 'hfhxchxbxcfb', '2025-09-06 04:01:35'),
(4, 1, 1, 'kiree', '2025-09-06 04:02:12'),
(5, 1, 5, 'Hamim Gay', '2025-09-06 04:34:47'),
(6, 1, 1, 'you\'re gay', '2025-09-06 04:35:31'),
(7, 2, 1, 'hi', '2025-09-06 16:47:10'),
(9, 1, 6, 'Why are you gay??', '2025-09-07 06:35:00'),
(10, 2, 6, 'Assalamualikum', '2025-09-07 06:35:58');

-- --------------------------------------------------------

--
-- Table structure for table `habits`
--

CREATE TABLE `habits` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `frequency` enum('daily','weekly','custom') NOT NULL,
  `custom_days` varchar(20) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category` enum('health','productivity','learning','fitness','others') DEFAULT 'health',
  `is_public` tinyint(1) DEFAULT 0,
  `is_visible_to_group` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `habits`
--

INSERT INTO `habits` (`id`, `user_id`, `name`, `description`, `frequency`, `custom_days`, `start_date`, `end_date`, `created_at`, `category`, `is_public`, `is_visible_to_group`) VALUES
(7, 1, 'read 10 pages a day', '', 'daily', NULL, '2025-08-20', '2025-08-31', '2025-08-20 04:00:38', 'health', 0, 1),
(8, 3, 'have breakfast daily', '', 'daily', NULL, '2025-08-20', '2025-12-31', '2025-08-20 04:09:52', 'health', 0, 1),
(50, 2, '10 minuets of exercise daily', '', 'daily', NULL, '2025-09-06', '2025-09-30', '2025-09-05 21:51:37', 'health', 0, 1),
(51, 5, 'Daily exercise for 10 minutes', '', 'daily', NULL, '2025-09-06', '2025-09-30', '2025-09-06 03:58:15', 'fitness', 0, 0),
(52, 6, 'Morning walk', 'N/B', 'daily', NULL, '2025-09-06', '2025-09-30', '2025-09-06 04:38:12', 'health', 0, 0),
(54, 7, 'জামাতে সলাত আদায়', 'প্রতিদিন পাঁচ ওয়াক্ত সালাত জামাতে আদায় করতে হবে ।', 'daily', NULL, '2025-09-06', '0000-00-00', '2025-09-06 05:11:19', 'others', 0, 0),
(55, 7, 'এক ঘন্টা ব্যায়াম', 'প্রতিদিন সকালে ঘুম থেকে উঠে কমপক্ষে এক ঘন্টা হাটাহাটি কিংবা শারীর চর্চা করতে হবে ।', 'daily', NULL, '2025-09-06', '0000-00-00', '2025-09-06 05:14:25', 'health', 0, 0),
(56, 1, '10 minuets of exercise daily', '', 'daily', NULL, '2025-09-06', '2025-09-23', '2025-09-06 16:46:04', 'health', 0, 0),
(74, 3, 'exercise', '', 'daily', NULL, '2025-09-07', '0000-00-00', '2025-09-07 06:46:12', 'health', 0, 0),
(75, 3, 'meditation', '', 'daily', NULL, '2025-09-07', '0000-00-00', '2025-09-07 06:46:56', 'health', 0, 0),
(76, 10, 'exercise', '', 'daily', NULL, '2025-09-07', '0000-00-00', '2025-09-07 06:55:04', 'health', 0, 0),
(77, 10, 'meditation', '', 'daily', NULL, '2025-09-07', '0000-00-00', '2025-09-07 06:55:18', 'health', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `habit_progress`
--

CREATE TABLE `habit_progress` (
  `id` int(11) NOT NULL,
  `habit_id` int(11) NOT NULL,
  `progress_date` date NOT NULL,
  `completed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `habit_progress`
--

INSERT INTO `habit_progress` (`id`, `habit_id`, `progress_date`, `completed`, `created_at`) VALUES
(4, 8, '2025-08-20', 1, '2025-08-20 04:10:26'),
(6, 7, '2025-08-24', 1, '2025-08-24 16:34:03'),
(7, 7, '2025-09-03', 1, '2025-09-03 21:54:19'),
(9, 7, '2025-09-04', 1, '2025-09-03 22:28:02'),
(11, 7, '2025-09-05', 1, '2025-09-04 18:49:03'),
(17, 7, '2025-09-06', 1, '2025-09-05 19:35:58'),
(48, 50, '2025-09-06', 1, '2025-09-05 21:51:41'),
(49, 51, '2025-09-06', 1, '2025-09-06 03:58:26'),
(51, 54, '2025-09-06', 1, '2025-09-06 05:11:42'),
(52, 55, '2025-09-06', 1, '2025-09-06 05:14:44'),
(53, 56, '2025-09-06', 1, '2025-09-06 16:46:08'),
(54, 8, '2025-09-05', 0, '2025-09-06 17:05:54'),
(55, 8, '2025-09-06', 1, '2025-09-06 17:05:56'),
(58, 56, '2025-09-07', 1, '2025-09-07 02:35:43'),
(59, 7, '2025-09-07', 1, '2025-09-07 02:35:47'),
(74, 8, '2025-09-07', 1, '2025-09-07 04:27:56'),
(77, 52, '2025-09-06', 0, '2025-09-07 06:30:12'),
(78, 52, '2025-09-07', 1, '2025-09-07 06:30:18'),
(79, 74, '2025-09-07', 1, '2025-09-07 06:52:54'),
(80, 75, '2025-09-07', 1, '2025-09-07 06:52:58'),
(81, 76, '2025-09-07', 1, '2025-09-07 06:55:24'),
(82, 77, '2025-09-07', 1, '2025-09-07 06:55:28');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscriptions`
--

CREATE TABLE `newsletter_subscriptions` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `newsletter_subscriptions`
--

INSERT INTO `newsletter_subscriptions` (`id`, `email`, `subscribed_at`) VALUES
(1, 'pikachu.feb27@gmail.com', '2025-09-07 05:21:53'),
(7, 'info@gmail.com', '2025-09-07 05:29:57'),
(14, 'info2@gmail.com', '2025-09-07 05:48:24'),
(22, 'info3@gmail.com', '2025-09-07 06:05:19');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `created_at`) VALUES
(1, 'Hamim', 'info@gmail.com', '$2y$10$7Awj5Rq1Cpxk54BwnDez.ueRqIyXHMEOj/n/7Pi/nF2bycZyheYm.', '2025-08-19 21:29:19'),
(2, 'Tanim', 'info2@gmail.com', '$2y$10$T76tLvCudQBwBaV9zn8Qs.yi.5yyhGzRbfSHHCJ23SBkUPu5CanFm', '2025-08-19 23:06:08'),
(3, 'Hridy', 'info3@gmail.com', '$2y$10$ldsJx1yDiAL/kTG.U5UoJeHV7QY7ecQKTGIPUfyv9O2zGKh2Yk9I6', '2025-08-20 04:08:49'),
(4, 'Hamim Shahriar', 'pikachu.feb27@gmail.com', '$2y$10$muyvwWvtI5oCJ/AbQgCjo.Eje28YIbBfIq3vszrWMNs.9YaisWAMq', '2025-09-05 22:51:45'),
(5, 'Akash Sarker', 'akshsarker877@gmail.com', '$2y$10$GbdjQ4CujfMhrSxjyC1eZeDx0Jzv8ALTXGX/1NrSD4I3HqY2Lk4SS', '2025-09-06 03:55:34'),
(6, 'tanim', 'tanim@gmail.com', '$2y$10$6ZQIMvtmjGOS0.nxtyHRjeZt037Os6wyjchBKsBbFPJSuNHowRAru', '2025-09-06 04:36:54'),
(7, 'Md Humayun Kabir Shovon', 'mdhumayunkabirshovon2003@gmail.com', '$2y$10$5/IBU0Efs3jbNkJCz/k42OQ5Vexy6Q1zuEnn9Q8w6CIa8MhDKjLk6', '2025-09-06 04:45:16'),
(8, 'nawazemon', 'mnmemon2002@gmail.com', '$2y$10$sNeqkJU4ZKJI8271WvOkoeSKMl4rGJcfT8nyr5JlBb/5fOMBbsYdC', '2025-09-06 06:02:31'),
(9, 'test', 'ciyecec232@cspaus.com', '$2y$10$qJwK5cKlxytr0IyurQlbYekUDczWb935DCMtnt1G67ieUJUDPcFvm', '2025-09-07 06:51:24'),
(10, 'Afrin', 'sadiahridy12@gmail.com', '$2y$10$mihiatqQxID8Yg5CAYmzbezWMZaTa8fWopuC3nQFh4c3NoIe1Gigq', '2025-09-07 06:53:57');

-- --------------------------------------------------------

--
-- Table structure for table `user_achievements`
--

CREATE TABLE `user_achievements` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `achievement_name` varchar(100) NOT NULL,
  `unlocked_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_achievements`
--

INSERT INTO `user_achievements` (`id`, `user_id`, `achievement_name`, `unlocked_at`) VALUES
(1, 1, 'first-habit', '2025-08-19 21:30:17'),
(2, 1, 'three-habits', '2025-08-19 22:11:41'),
(4, 3, 'first-habit', '2025-08-20 04:09:52'),
(5, 1, 'ten-points', '2025-09-06 16:46:04'),
(6, 3, 'three-habits', '2025-09-06 17:08:17'),
(7, 3, 'ten-points', '2025-09-07 04:28:54'),
(8, 3, 'group-explorer', '2025-09-07 04:28:54'),
(9, 6, 'first-habit', '2025-09-07 06:33:36'),
(10, 6, 'group-explorer', '2025-09-07 06:33:36'),
(11, 6, 'ten-points', '2025-09-07 06:33:46'),
(12, 6, 'social-butterfly', '2025-09-07 06:33:46'),
(13, 10, 'first-habit', '2025-09-07 06:55:04');

-- --------------------------------------------------------

--
-- Table structure for table `user_points`
--

CREATE TABLE `user_points` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `points` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_points`
--

INSERT INTO `user_points` (`id`, `user_id`, `points`) VALUES
(1, 1, 67),
(2, 2, 1),
(3, 3, 28),
(4, 4, 0),
(5, 5, 1),
(6, 6, 22),
(7, 7, 3),
(8, 8, 0),
(9, 9, 1),
(10, 10, 8);

-- --------------------------------------------------------

--
-- Table structure for table `user_queries`
--

CREATE TABLE `user_queries` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('new','read','replied') DEFAULT 'new'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_queries`
--

INSERT INTO `user_queries` (`id`, `name`, `email`, `subject`, `message`, `created_at`, `status`) VALUES
(1, 'Hamim Shahriar', 'pikachu.feb27@gmail.com', 'test', 'this is a test message', '2025-09-07 05:05:34', 'new'),
(2, 'Hamim Shahriar', 'pikachu.feb27@gmail.com', 'test', 'this is a test message', '2025-09-07 05:06:26', 'new');

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

CREATE TABLE `user_settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reminder_time` time DEFAULT '09:00:00',
  `enable_notifications` tinyint(1) DEFAULT 0,
  `last_missed_check` date DEFAULT NULL,
  `login_streak` int(11) DEFAULT 0,
  `last_login_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_settings`
--

INSERT INTO `user_settings` (`id`, `user_id`, `reminder_time`, `enable_notifications`, `last_missed_check`, `login_streak`, `last_login_date`) VALUES
(1, 1, '09:00:00', 1, '2025-09-07', 2, '2025-09-07'),
(3, 2, '09:00:00', 0, '2025-09-06', 0, NULL),
(5, 3, '09:00:00', 0, '2025-09-07', 2, '2025-09-07'),
(6, 4, '09:00:00', 0, NULL, 0, NULL),
(7, 5, '09:00:00', 0, '2025-09-06', 0, NULL),
(8, 6, '09:00:00', 0, '2025-09-07', 1, '2025-09-07'),
(9, 7, '09:00:00', 0, '2025-09-06', 0, NULL),
(10, 8, '09:00:00', 0, NULL, 0, NULL),
(11, 9, '09:00:00', 0, NULL, 1, '2025-09-07'),
(12, 10, '09:00:00', 0, '2025-09-07', 1, '2025-09-07');

-- --------------------------------------------------------

--
-- Table structure for table `user_streaks`
--

CREATE TABLE `user_streaks` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `habit_id` int(11) NOT NULL,
  `current_streak` int(11) DEFAULT 0,
  `longest_streak` int(11) DEFAULT 0,
  `last_updated` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_streaks`
--

INSERT INTO `user_streaks` (`id`, `user_id`, `habit_id`, `current_streak`, `longest_streak`, `last_updated`) VALUES
(7, 1, 7, 1, 1, '2025-08-24'),
(8, 3, 8, 1, 1, '2025-08-20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `achievement_definitions`
--
ALTER TABLE `achievement_definitions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `achievement_name` (`achievement_name`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `group_members`
--
ALTER TABLE `group_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_membership` (`group_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `group_messages`
--
ALTER TABLE `group_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `habits`
--
ALTER TABLE `habits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `habit_progress`
--
ALTER TABLE `habit_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_habit_date` (`habit_id`,`progress_date`);

--
-- Indexes for table `newsletter_subscriptions`
--
ALTER TABLE `newsletter_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_achievement` (`user_id`,`achievement_name`);

--
-- Indexes for table `user_points`
--
ALTER TABLE `user_points`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_queries`
--
ALTER TABLE `user_queries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_setting` (`user_id`);

--
-- Indexes for table `user_streaks`
--
ALTER TABLE `user_streaks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_habit` (`user_id`,`habit_id`),
  ADD KEY `habit_id` (`habit_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `achievement_definitions`
--
ALTER TABLE `achievement_definitions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `group_members`
--
ALTER TABLE `group_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `group_messages`
--
ALTER TABLE `group_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `habits`
--
ALTER TABLE `habits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `habit_progress`
--
ALTER TABLE `habit_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `newsletter_subscriptions`
--
ALTER TABLE `newsletter_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `user_achievements`
--
ALTER TABLE `user_achievements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `user_points`
--
ALTER TABLE `user_points`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `user_queries`
--
ALTER TABLE `user_queries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_settings`
--
ALTER TABLE `user_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `user_streaks`
--
ALTER TABLE `user_streaks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `groups`
--
ALTER TABLE `groups`
  ADD CONSTRAINT `groups_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `group_members`
--
ALTER TABLE `group_members`
  ADD CONSTRAINT `group_members_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `group_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `group_messages`
--
ALTER TABLE `group_messages`
  ADD CONSTRAINT `group_messages_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `group_messages_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `habits`
--
ALTER TABLE `habits`
  ADD CONSTRAINT `habits_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `habit_progress`
--
ALTER TABLE `habit_progress`
  ADD CONSTRAINT `habit_progress_ibfk_1` FOREIGN KEY (`habit_id`) REFERENCES `habits` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD CONSTRAINT `user_achievements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_points`
--
ALTER TABLE `user_points`
  ADD CONSTRAINT `user_points_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD CONSTRAINT `user_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_streaks`
--
ALTER TABLE `user_streaks`
  ADD CONSTRAINT `user_streaks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_streaks_ibfk_2` FOREIGN KEY (`habit_id`) REFERENCES `habits` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
