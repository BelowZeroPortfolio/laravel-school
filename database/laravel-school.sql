-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 05, 2026 at 03:07 PM
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
-- Database: `laravel-school`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `school_year_id` bigint(20) UNSIGNED NOT NULL,
  `attendance_date` date NOT NULL,
  `check_in_time` timestamp NULL DEFAULT NULL,
  `check_out_time` timestamp NULL DEFAULT NULL,
  `status` enum('present','late','absent','excused') NOT NULL DEFAULT 'present',
  `recorded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `grade_level` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  `teacher_id` bigint(20) UNSIGNED DEFAULT NULL,
  `school_year_id` bigint(20) UNSIGNED NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `max_capacity` int(11) NOT NULL DEFAULT 50,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `school_id`, `grade_level`, `section`, `teacher_id`, `school_year_id`, `is_active`, `max_capacity`, `created_at`, `updated_at`) VALUES
(1, 3, '7', 'A', 4, 1, 1, 40, '2026-01-05 06:05:55', '2026-01-05 06:05:55');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_01_02_055324_create_sessions_table', 1),
(5, '2026_01_02_060001_create_school_years_table', 1),
(6, '2026_01_02_060002_create_time_schedules_table', 1),
(7, '2026_01_02_060003_create_time_schedule_logs_table', 1),
(8, '2026_01_02_060004_create_students_table', 1),
(9, '2026_01_02_060005_create_classes_table', 1),
(10, '2026_01_02_060006_create_student_classes_table', 1),
(11, '2026_01_02_060007_create_attendance_table', 1),
(12, '2026_01_02_060008_create_teacher_attendance_table', 1),
(13, '2026_01_02_060009_add_audit_fields_to_student_classes_table', 1),
(14, '2026_01_02_060010_create_settings_table', 1),
(15, '2026_01_02_060011_create_settings_logs_table', 1),
(16, '2026_01_05_150001_create_schools_table', 1),
(17, '2026_01_05_150002_add_school_id_to_users_table', 1),
(18, '2026_01_05_150003_add_school_id_to_school_years_table', 1),
(19, '2026_01_05_150004_add_school_id_to_students_table', 1),
(20, '2026_01_05_150005_add_school_id_to_classes_table', 1),
(21, '2026_01_05_150006_add_school_id_to_time_schedules_table', 1),
(22, '2026_01_05_150007_assign_existing_data_to_default_school', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schools`
--

CREATE TABLE `schools` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(50) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schools`
--

INSERT INTO `schools` (`id`, `name`, `code`, `address`, `phone`, `email`, `logo_path`, `is_active`, `settings`, `created_at`, `updated_at`) VALUES
(1, 'Default School', 'SCH-DEFAULT', NULL, NULL, NULL, NULL, 1, NULL, '2026-01-05 05:53:24', '2026-01-05 05:53:24'),
(2, 'Default School', 'SCH-001', '123 Main Street', '(123) 456-7890', 'info@defaultschool.edu', NULL, 1, NULL, '2026-01-05 05:53:24', '2026-01-05 05:53:24'),
(3, 'sample school', 'SCH-005', 'asdasdasd', '09120391023', 'newschool@gmail.com', NULL, 1, NULL, '2026-01-05 06:04:06', '2026-01-05 06:04:06');

-- --------------------------------------------------------

--
-- Table structure for table `school_years`
--

CREATE TABLE `school_years` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `school_years`
--

INSERT INTO `school_years` (`id`, `school_id`, `name`, `is_active`, `is_locked`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES
(1, 3, '2026-2027', 1, 0, '2026-01-05', '2026-10-20', '2026-01-05 06:05:27', '2026-01-05 06:05:27');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('xZcrFjQRR0fJdrQn2anMSsbdA6URwi5qshCBHpWe', 5, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiS2dudzkyMWx3SVM0aGk0SjRVMTdiNzNhYmlUR0xZbFNENFl1YmtwcSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9jbGFzc2VzLzEiO3M6NToicm91dGUiO3M6MTI6ImNsYXNzZXMuc2hvdyI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjU7fQ==', 1767621956);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`value`)),
  `group` varchar(255) NOT NULL DEFAULT 'general',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings_logs`
--

CREATE TABLE `settings_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `setting_key` varchar(255) NOT NULL,
  `old_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_value`)),
  `new_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_value`)),
  `changed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `student_id` varchar(255) NOT NULL,
  `lrn` varchar(12) DEFAULT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `qrcode_path` varchar(255) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `parent_name` varchar(255) DEFAULT NULL,
  `parent_phone` varchar(255) DEFAULT NULL,
  `parent_email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sms_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_classes`
--

CREATE TABLE `student_classes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `enrolled_at` timestamp NULL DEFAULT NULL,
  `enrolled_by` bigint(20) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `enrollment_type` enum('regular','transferee','returnee') NOT NULL DEFAULT 'regular',
  `enrollment_status` enum('enrolled','dropped','transferred') NOT NULL DEFAULT 'enrolled',
  `status_changed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status_changed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `status_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teacher_attendance`
--

CREATE TABLE `teacher_attendance` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `teacher_id` bigint(20) UNSIGNED NOT NULL,
  `school_year_id` bigint(20) UNSIGNED NOT NULL,
  `attendance_date` date NOT NULL,
  `time_in` datetime DEFAULT NULL,
  `time_out` datetime DEFAULT NULL,
  `first_student_scan` datetime DEFAULT NULL,
  `attendance_status` enum('pending','confirmed','late','absent','no_scan') NOT NULL DEFAULT 'pending',
  `late_status` enum('on_time','late') DEFAULT NULL,
  `time_rule_id` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `time_schedules`
--

CREATE TABLE `time_schedules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `time_in` time NOT NULL,
  `time_out` time NOT NULL,
  `late_threshold_minutes` int(11) NOT NULL DEFAULT 15,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `effective_date` date DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `time_schedules`
--

INSERT INTO `time_schedules` (`id`, `school_id`, `name`, `time_in`, `time_out`, `late_threshold_minutes`, `is_active`, `effective_date`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 3, 'default', '08:00:00', '17:00:00', 0, 1, '2026-01-05', 5, '2026-01-05 06:05:04', '2026-01-05 06:05:04');

-- --------------------------------------------------------

--
-- Table structure for table `time_schedule_logs`
--

CREATE TABLE `time_schedule_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `schedule_id` bigint(20) UNSIGNED NOT NULL,
  `action` enum('create','update','delete','activate','deactivate') NOT NULL,
  `changed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `change_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `time_schedule_logs`
--

INSERT INTO `time_schedule_logs` (`id`, `schedule_id`, `action`, `changed_by`, `old_values`, `new_values`, `change_reason`, `created_at`, `updated_at`) VALUES
(1, 1, 'create', 5, NULL, '{\"name\":\"default\",\"time_in\":\"08:00:00\",\"time_out\":\"17:00:00\",\"late_threshold_minutes\":0,\"effective_date\":\"2026-01-05T00:00:00.000000Z\",\"is_active\":true,\"created_by\":5,\"school_id\":3,\"updated_at\":\"2026-01-05T14:05:04.000000Z\",\"created_at\":\"2026-01-05T14:05:04.000000Z\",\"id\":1}', NULL, '2026-01-05 06:05:04', '2026-01-05 06:05:04');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','principal','teacher') NOT NULL DEFAULT 'teacher',
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_premium` tinyint(1) NOT NULL DEFAULT 0,
  `premium_expires_at` date DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `school_id`, `username`, `password`, `role`, `full_name`, `email`, `is_active`, `is_premium`, `premium_expires_at`, `last_login`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, NULL, 'superadmin', '$2y$12$tXZdBDwbmcfAqpd6W78zNuGDyP2o0XxWs1BqsCLb/53Ci1lpnD.c2', 'super_admin', 'Super Administrator', 'superadmin@example.com', 1, 0, NULL, '2026-01-05 05:54:32', 'JH0fzdAAT1m6jczBHkJHc9RIs0eEELDLsLITvQ6YUPO9SlWHTGyP5sY6Tzod', '2026-01-05 05:53:25', '2026-01-05 05:54:32'),
(2, 2, 'admin', '$2y$12$SYzzyaU2QI24aJ5t0to7xOaye45Hl6W2mRNASdGfVf/eaWvVU8/NG', 'admin', 'School Administrator', 'admin@example.com', 1, 0, NULL, '2026-01-05 05:54:09', 'X4uj3ADgHxRBcHYfoPDT3jCjyv5SmWmTg7zxFD7X59DWOeEfzPVx797OjFlj', '2026-01-05 05:53:25', '2026-01-05 05:54:09'),
(3, 2, 'principal', '$2y$12$BeVHzfcHtP1XmyLbhHVKwuU3qiTHk41h0noqslN4bFDeOrkcG8kwi', 'principal', 'School Principal', 'principal@example.com', 1, 0, NULL, NULL, 'dXUAfj2KrD', '2026-01-05 05:53:25', '2026-01-05 05:53:25'),
(4, 2, 'teacher', '$2y$12$E6rAZA0lhz6gk401xNgf/uRznH0awgQBiIjzYCg7sk.NmBD04wOlq', 'teacher', 'Sample Teacher', 'teacher@example.com', 1, 0, NULL, NULL, 'W5nh2PyrI6', '2026-01-05 05:53:25', '2026-01-05 05:53:25'),
(5, 3, 'sampleadmin', '$2y$12$aZx0yFRf.Z5BDNsTZHXTLeTygja0psjB6IIQbuh5287a3.Pb8dTQK', 'admin', 'sampleadmin', 'sampleadmin@gmail.com', 1, 0, NULL, '2026-01-05 06:04:24', NULL, '2026-01-05 06:04:06', '2026-01-05 06:04:24');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attendance_student_id_attendance_date_unique` (`student_id`,`attendance_date`),
  ADD KEY `attendance_school_year_id_foreign` (`school_year_id`),
  ADD KEY `attendance_recorded_by_foreign` (`recorded_by`),
  ADD KEY `attendance_attendance_date_index` (`attendance_date`),
  ADD KEY `attendance_student_id_index` (`student_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `classes_school_grade_section_year_unique` (`school_id`,`grade_level`,`section`,`school_year_id`),
  ADD KEY `classes_teacher_id_foreign` (`teacher_id`),
  ADD KEY `classes_school_year_id_foreign` (`school_year_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `schools`
--
ALTER TABLE `schools`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `schools_code_unique` (`code`);

--
-- Indexes for table `school_years`
--
ALTER TABLE `school_years`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `school_years_school_id_name_unique` (`school_id`,`name`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`),
  ADD KEY `settings_group_index` (`group`);

--
-- Indexes for table `settings_logs`
--
ALTER TABLE `settings_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `settings_logs_setting_key_index` (`setting_key`),
  ADD KEY `settings_logs_changed_by_index` (`changed_by`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `students_school_id_lrn_unique` (`school_id`,`lrn`),
  ADD UNIQUE KEY `students_school_id_student_id_unique` (`school_id`,`student_id`);

--
-- Indexes for table `student_classes`
--
ALTER TABLE `student_classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_classes_student_id_class_id_unique` (`student_id`,`class_id`),
  ADD KEY `student_classes_class_id_foreign` (`class_id`),
  ADD KEY `student_classes_enrolled_by_foreign` (`enrolled_by`),
  ADD KEY `student_classes_status_changed_by_foreign` (`status_changed_by`);

--
-- Indexes for table `teacher_attendance`
--
ALTER TABLE `teacher_attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacher_attendance_teacher_id_attendance_date_unique` (`teacher_id`,`attendance_date`),
  ADD KEY `teacher_attendance_school_year_id_foreign` (`school_year_id`),
  ADD KEY `teacher_attendance_time_rule_id_foreign` (`time_rule_id`),
  ADD KEY `teacher_attendance_attendance_date_index` (`attendance_date`),
  ADD KEY `teacher_attendance_teacher_id_index` (`teacher_id`);

--
-- Indexes for table `time_schedules`
--
ALTER TABLE `time_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `time_schedules_created_by_foreign` (`created_by`),
  ADD KEY `time_schedules_school_id_index` (`school_id`);

--
-- Indexes for table `time_schedule_logs`
--
ALTER TABLE `time_schedule_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `time_schedule_logs_schedule_id_foreign` (`schedule_id`),
  ADD KEY `time_schedule_logs_changed_by_foreign` (`changed_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD KEY `users_school_id_index` (`school_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `schools`
--
ALTER TABLE `schools`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `school_years`
--
ALTER TABLE `school_years`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings_logs`
--
ALTER TABLE `settings_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_classes`
--
ALTER TABLE `student_classes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teacher_attendance`
--
ALTER TABLE `teacher_attendance`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `time_schedules`
--
ALTER TABLE `time_schedules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `time_schedule_logs`
--
ALTER TABLE `time_schedule_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `attendance_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `classes_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `classes_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `school_years`
--
ALTER TABLE `school_years`
  ADD CONSTRAINT `school_years_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `settings_logs`
--
ALTER TABLE `settings_logs`
  ADD CONSTRAINT `settings_logs_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_classes`
--
ALTER TABLE `student_classes`
  ADD CONSTRAINT `student_classes_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_classes_enrolled_by_foreign` FOREIGN KEY (`enrolled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `student_classes_status_changed_by_foreign` FOREIGN KEY (`status_changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `student_classes_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_attendance`
--
ALTER TABLE `teacher_attendance`
  ADD CONSTRAINT `teacher_attendance_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_attendance_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_attendance_time_rule_id_foreign` FOREIGN KEY (`time_rule_id`) REFERENCES `time_schedules` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `time_schedules`
--
ALTER TABLE `time_schedules`
  ADD CONSTRAINT `time_schedules_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `time_schedules_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `time_schedule_logs`
--
ALTER TABLE `time_schedule_logs`
  ADD CONSTRAINT `time_schedule_logs_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `time_schedule_logs_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `time_schedules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
