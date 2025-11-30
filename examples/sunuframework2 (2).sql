-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 30, 2025 at 03:43 PM
-- Server version: 8.0.30
-- PHP Version: 8.3.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sunuframework2`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_agents`
--

CREATE TABLE `ai_agents` (
  `id` bigint UNSIGNED NOT NULL,
  `module` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `agent_class` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `config` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_logs`
--

CREATE TABLE `ai_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `module` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `agent_class` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prompt` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `response` text COLLATE utf8mb4_unicode_ci,
  `tokens_used` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `backups`
--

CREATE TABLE `backups` (
  `id` bigint UNSIGNED NOT NULL,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `disk` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local',
  `size` bigint NOT NULL DEFAULT '0',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `initiated_by` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'system',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_config`
--

CREATE TABLE `cache_config` (
  `id` bigint UNSIGNED NOT NULL,
  `driver` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'filesystem',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `prefix` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cache_',
  `default_ttl` int NOT NULL DEFAULT '3600',
  `filesystem_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'storage/cache',
  `redis_host` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '127.0.0.1',
  `redis_port` int NOT NULL DEFAULT '6379',
  `redis_password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `redis_database` int NOT NULL DEFAULT '0',
  `memcached_servers` text COLLATE utf8mb4_unicode_ci,
  `apcu_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache_config`
--

INSERT INTO `cache_config` (`id`, `driver`, `enabled`, `prefix`, `default_ttl`, `filesystem_path`, `redis_host`, `redis_port`, `redis_password`, `redis_database`, `memcached_servers`, `apcu_enabled`, `created_at`, `updated_at`) VALUES
(1, 'filesystem', 1, 'cache_', 3600, 'storage/cache', '127.0.0.1', 6379, NULL, 0, '[{\"host\":\"127.0.0.1\",\"port\":11211}]', 0, '2025-11-26 11:06:57', '2025-11-26 11:06:57');

-- --------------------------------------------------------

--
-- Table structure for table `cron_logs`
--

CREATE TABLE `cron_logs` (
  `id` bigint NOT NULL,
  `task_id` int NOT NULL,
  `started_at` datetime NOT NULL,
  `finished_at` datetime DEFAULT NULL,
  `status` enum('running','success','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'running',
  `output` text COLLATE utf8mb4_unicode_ci,
  `error` text COLLATE utf8mb4_unicode_ci,
  `duration_ms` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cron_tasks`
--

CREATE TABLE `cron_tasks` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `class` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expression` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `last_run_at` datetime DEFAULT NULL,
  `next_run_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(1, 'default', '{\"payload\":{\"job\":\"App\\\\Jobs\\\\SendEmailNotification\",\"data\":\"{\\\"recipientId\\\":\\\"10\\\",\\\"template\\\":\\\"test_email\\\",\\\"data\\\":{\\\"name\\\":\\\"Test User\\\",\\\"message\\\":\\\"System test notification\\\"},\\\"attempt\\\":1}\",\"attempts\":0,\"created_at\":1764158016,\"available_at\":1764158016}}', 0, NULL, 1764158016, 1764158016),
(2, 'default', '{\"payload\":{\"job\":\"App\\\\Jobs\\\\SendEmailNotification\",\"data\":\"{\\\"recipientId\\\":\\\"11\\\",\\\"template\\\":\\\"test_email\\\",\\\"data\\\":{\\\"name\\\":\\\"Test User\\\",\\\"message\\\":\\\"System test notification\\\"},\\\"attempt\\\":1}\",\"attempts\":0,\"created_at\":1764158039,\"available_at\":1764158039}}', 0, NULL, 1764158039, 1764158039),
(3, 'default', '{\"payload\":{\"job\":\"App\\\\Jobs\\\\SendEmailNotification\",\"data\":\"{\\\"recipientId\\\":\\\"1\\\",\\\"template\\\":\\\"test_email\\\",\\\"data\\\":{\\\"name\\\":\\\"Test User\\\",\\\"message\\\":\\\"System test notification\\\"},\\\"attempt\\\":1}\",\"attempts\":0,\"created_at\":1764158063,\"available_at\":1764158063}}', 0, NULL, 1764158063, 1764158063),
(4, 'default', '{\"payload\":{\"job\":\"App\\\\Jobs\\\\SendEmailNotification\",\"data\":\"{\\\"recipientId\\\":\\\"1\\\",\\\"template\\\":\\\"test_email\\\",\\\"data\\\":{\\\"name\\\":\\\"Test User\\\",\\\"message\\\":\\\"System test notification\\\"},\\\"attempt\\\":1}\",\"attempts\":0,\"created_at\":1764159466,\"available_at\":1764159466}}', 0, NULL, 1764159466, 1764159466),
(5, 'default', '{\"payload\":{\"job\":\"App\\\\Jobs\\\\SendEmailNotification\",\"data\":\"{\\\"recipientId\\\":\\\"2\\\",\\\"template\\\":\\\"test_email\\\",\\\"data\\\":{\\\"name\\\":\\\"Test User\\\",\\\"message\\\":\\\"System test notification\\\"},\\\"attempt\\\":1}\",\"attempts\":0,\"created_at\":1764159475,\"available_at\":1764159475}}', 0, NULL, 1764159475, 1764159475),
(6, 'default', '{\"payload\":{\"job\":\"App\\\\Jobs\\\\SendEmailNotification\",\"data\":\"{\\\"recipientId\\\":\\\"3\\\",\\\"template\\\":\\\"test_email\\\",\\\"data\\\":{\\\"name\\\":\\\"Test User\\\",\\\"message\\\":\\\"System test notification\\\"},\\\"attempt\\\":1}\",\"attempts\":0,\"created_at\":1764159482,\"available_at\":1764159482}}', 0, NULL, 1764159482, 1764159482),
(7, 'default', '{\"payload\":{\"job\":\"App\\\\Jobs\\\\SendEmailNotification\",\"data\":\"{\\\"recipientId\\\":\\\"4\\\",\\\"template\\\":\\\"test_email\\\",\\\"data\\\":{\\\"name\\\":\\\"Test User\\\",\\\"message\\\":\\\"System test notification\\\"},\\\"attempt\\\":1}\",\"attempts\":0,\"created_at\":1764160077,\"available_at\":1764160077}}', 0, NULL, 1764160077, 1764160077),
(8, 'default', '{\"payload\":{\"job\":\"App\\\\Jobs\\\\SendEmailNotification\",\"data\":\"{\\\"recipientId\\\":\\\"5\\\",\\\"template\\\":\\\"test_email\\\",\\\"data\\\":{\\\"name\\\":\\\"Test User\\\",\\\"message\\\":\\\"System test notification\\\"},\\\"attempt\\\":1}\",\"attempts\":0,\"created_at\":1764160157,\"available_at\":1764160157}}', 0, NULL, 1764160157, 1764160157),
(9, 'default', '{\"payload\":{\"job\":\"App\\\\Jobs\\\\SendEmailNotification\",\"data\":\"{\\\"recipientId\\\":\\\"6\\\",\\\"template\\\":\\\"test_email\\\",\\\"data\\\":{\\\"name\\\":\\\"Test User\\\",\\\"message\\\":\\\"System test notification\\\"},\\\"attempt\\\":1}\",\"attempts\":0,\"created_at\":1764160715,\"available_at\":1764160715}}', 0, NULL, 1764160715, 1764160715),
(10, 'default', '{\"payload\":{\"job\":\"App\\\\Jobs\\\\SendEmailNotification\",\"data\":\"{\\\"recipientId\\\":\\\"7\\\",\\\"template\\\":\\\"test_email\\\",\\\"data\\\":{\\\"name\\\":\\\"Test User\\\",\\\"message\\\":\\\"System test notification\\\"},\\\"attempt\\\":1}\",\"attempts\":0,\"created_at\":1764160965,\"available_at\":1764160965}}', 0, NULL, 1764160965, 1764160965),
(11, 'default', '{\"payload\":{\"job\":\"App\\\\Jobs\\\\SendEmailNotification\",\"data\":\"{\\\"recipientId\\\":\\\"8\\\",\\\"template\\\":\\\"test_email\\\",\\\"data\\\":{\\\"name\\\":\\\"Test User\\\",\\\"message\\\":\\\"System test notification\\\"},\\\"attempt\\\":1}\",\"attempts\":0,\"created_at\":1764175400,\"available_at\":1764175400}}', 0, NULL, 1764175400, 1764175400),
(12, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"3\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(13, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"4\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(14, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"5\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(15, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"6\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(16, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"7\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(17, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"8\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(18, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"9\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(19, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"10\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(20, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"11\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(21, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"12\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(22, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"13\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(23, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"14\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(24, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"15\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(25, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"16\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(26, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"17\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(27, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"18\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(28, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"19\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(29, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"20\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(30, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"21\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(31, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"22\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(32, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"23\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(33, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"24\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(34, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"25\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(35, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"26\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(36, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"27\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(37, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"28\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(38, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"29\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(39, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"30\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(40, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"31\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(41, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"32\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(42, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"33\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(43, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"34\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(44, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"35\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(45, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"36\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062),
(46, 'sms', '{\"payload\":{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":\\\"37\\\"}\",\"attempts\":0,\"created_at\":1764442062,\"available_at\":1764442062}}', 0, NULL, 1764442062, 1764442062);

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` bigint UNSIGNED NOT NULL,
  `channel` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `level_value` int NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `context` text COLLATE utf8mb4_unicode_ci,
  `extra` text COLLATE utf8mb4_unicode_ci,
  `remote_addr` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int NOT NULL,
  `migration` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `created_at`) VALUES
(1, '001_CreateAILogsTable', '2025-11-24 20:43:14'),
(2, '002_CreateAIAgentsTable', '2025-11-24 20:43:14'),
(6, 'CreateUsersTable', '2025-11-24 20:43:14'),
(26, '001_create_cache_config_table', '2025-11-26 11:06:57'),
(27, '001_create_cron_tasks_table', '2025-11-26 11:06:57'),
(28, '001_create_jobs_table', '2025-11-26 11:06:57'),
(29, '002_CreateModulesTable', '2025-11-26 11:06:57'),
(30, '002_create_cron_logs_table', '2025-11-26 11:06:57'),
(31, '002_create_failed_jobs_table', '2025-11-26 11:06:57'),
(32, '2025_01_01_000000_create_logs_table', '2025-11-26 11:06:58'),
(33, '001_CreateRolesTable', '2025-11-26 11:06:58'),
(34, '002_CreatePermissionsTable', '2025-11-26 11:06:58'),
(35, '003_CreateUserRolesTable', '2025-11-26 11:06:58'),
(36, '004_CreateRolePermissionsTable', '2025-11-26 11:06:58'),
(37, '001_create_settings_table', '2025-11-26 11:06:58'),
(38, '002_create_translations_table', '2025-11-26 11:06:58'),
(39, '003_create_translation_history_table', '2025-11-26 11:06:58'),
(40, '004_create_webhooks_table', '2025-11-26 11:06:58'),
(41, '005_create_webhook_logs_table', '2025-11-26 11:06:58'),
(42, '001_create_backups_table', '2025-11-26 22:49:40'),
(43, 'AddApiKeyToUsersTable', '2025-11-29 19:12:07');

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1.0.0',
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `author` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_installed` tinyint(1) NOT NULL DEFAULT '1',
  `config` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `name`, `slug`, `version`, `icon`, `description`, `author`, `is_active`, `is_installed`, `config`, `created_at`, `updated_at`) VALUES
(1, 'Gestion des Utilisateurs', 'users-management', '1.0.0', 'users', 'Gestion des utilisateurs, profils et authentification', NULL, 1, 1, NULL, '2025-11-30 13:54:29', '2025-11-30 13:54:29'),
(2, 'Rôles et Permissions', 'roles-permissions', '1.0.0', 'shield', 'Gestion des rôles, permissions et contrôle d\'accès', NULL, 1, 1, NULL, '2025-11-30 13:54:29', '2025-11-30 13:54:29'),
(3, 'Admin', 'admin', '1.0.0', 'settings', 'Administration générale du système', NULL, 1, 1, NULL, '2025-11-30 13:54:29', '2025-11-30 15:32:01'),
(4, 'Modules', 'modules', '1.0.0', 'package', 'Gestion des modules du système', NULL, 1, 1, NULL, '2025-11-30 13:54:29', '2025-11-30 13:54:29'),
(5, 'Auth', 'auth', '1.0.0', NULL, 'Authentication Module', 'System', 1, 1, '{}', '2025-11-30 14:49:43', '2025-11-30 14:49:43'),
(6, 'Settings', 'settings', '1.0.0', NULL, 'Settings Module', 'System', 1, 1, '{}', '2025-11-30 14:49:43', '2025-11-30 14:49:43'),
(7, 'SmsCore', 'smscore', '1.0.0', NULL, 'SMS Core Module', 'System', 1, 1, '{}', '2025-11-30 14:49:43', '2025-11-30 14:49:43'),
(8, 'Wallet', 'wallet', '1.0.0', NULL, 'Wallet Module', 'System', 1, 1, '{}', '2025-11-30 14:49:43', '2025-11-30 14:49:43'),
(9, 'Queue', 'queue', '1.0.0', NULL, 'Queue Module', 'System', 1, 1, '{}', '2025-11-30 14:49:43', '2025-11-30 14:49:43'),
(10, 'Cron', 'cron', '1.0.0', NULL, 'Cron Module', 'System', 1, 1, '{}', '2025-11-30 14:49:43', '2025-11-30 14:49:43'),
(11, 'I18n', 'i18n', '1.0.0', NULL, 'I18n Module', 'System', 1, 1, '{}', '2025-11-30 14:49:43', '2025-11-30 14:49:43'),
(12, 'RBAC', 'rbac', '1.0.0', NULL, 'RBAC Module', 'System', 1, 1, '{}', '2025-11-30 14:49:43', '2025-11-30 14:49:43');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `data` json NOT NULL,
  `status` enum('pending','processing','completed','failed') DEFAULT 'pending',
  `priority` tinyint DEFAULT '5',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `event_type`, `data`, `status`, `priority`, `scheduled_at`, `created_at`, `updated_at`) VALUES
(1, 'test_event', '{\"name\": \"Test User\", \"message\": \"System test notification\"}', 'pending', 5, NULL, '2025-11-26 12:17:46', '2025-11-26 12:17:46'),
(2, 'test_event', '{\"name\": \"Test User\", \"message\": \"System test notification\"}', 'pending', 5, NULL, '2025-11-26 12:17:55', '2025-11-26 12:17:55'),
(3, 'test_event', '{\"name\": \"Test User\", \"message\": \"System test notification\"}', 'pending', 5, NULL, '2025-11-26 12:18:02', '2025-11-26 12:18:02'),
(4, 'test_event', '{\"name\": \"Test User\", \"message\": \"System test notification\"}', 'pending', 5, NULL, '2025-11-26 12:27:57', '2025-11-26 12:27:57'),
(5, 'test_event', '{\"name\": \"Test User\", \"message\": \"System test notification\"}', 'pending', 5, NULL, '2025-11-26 12:29:17', '2025-11-26 12:29:17'),
(6, 'test_event', '{\"name\": \"Test User\", \"message\": \"System test notification\"}', 'pending', 5, NULL, '2025-11-26 12:38:35', '2025-11-26 12:38:35'),
(7, 'test_event', '{\"name\": \"Test User\", \"message\": \"System test notification\"}', 'pending', 5, NULL, '2025-11-26 12:42:45', '2025-11-26 12:42:45'),
(8, 'test_event', '{\"name\": \"Test User\", \"message\": \"System test notification\"}', 'pending', 5, NULL, '2025-11-26 16:43:20', '2025-11-26 16:43:20');

-- --------------------------------------------------------

--
-- Table structure for table `notification_recipients`
--

CREATE TABLE `notification_recipients` (
  `id` bigint UNSIGNED NOT NULL,
  `notification_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `channels` json NOT NULL,
  `status` enum('pending','sent','failed','cancelled') DEFAULT 'pending',
  `attempts` tinyint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `notification_recipients`
--

INSERT INTO `notification_recipients` (`id`, `notification_id`, `user_id`, `channels`, `status`, `attempts`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '[\"email\"]', 'pending', 0, '2025-11-26 12:17:46', '2025-11-26 12:17:46'),
(2, 2, 1, '[\"email\"]', 'pending', 0, '2025-11-26 12:17:55', '2025-11-26 12:17:55'),
(3, 3, 1, '[\"email\"]', 'pending', 0, '2025-11-26 12:18:02', '2025-11-26 12:18:02'),
(4, 4, 1, '[\"email\"]', 'pending', 0, '2025-11-26 12:27:57', '2025-11-26 12:27:57'),
(5, 5, 1, '[\"email\"]', 'pending', 0, '2025-11-26 12:29:17', '2025-11-26 12:29:17'),
(6, 6, 1, '[\"email\"]', 'pending', 0, '2025-11-26 12:38:35', '2025-11-26 12:38:35'),
(7, 7, 1, '[\"email\"]', 'pending', 0, '2025-11-26 12:42:45', '2025-11-26 12:42:45'),
(8, 8, 1, '[\"email\"]', 'pending', 0, '2025-11-26 16:43:20', '2025-11-26 16:43:20');

-- --------------------------------------------------------

--
-- Table structure for table `notification_templates`
--

CREATE TABLE `notification_templates` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `channel` varchar(20) NOT NULL,
  `version` int DEFAULT '1',
  `subject` varchar(255) DEFAULT NULL,
  `body_html` text,
  `body_text` text,
  `body_sms` varchar(500) DEFAULT NULL,
  `push_title` varchar(100) DEFAULT NULL,
  `push_body` varchar(200) DEFAULT NULL,
  `variables` json DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `notification_templates`
--

INSERT INTO `notification_templates` (`id`, `name`, `channel`, `version`, `subject`, `body_html`, `body_text`, `body_sms`, `push_title`, `push_body`, `variables`, `active`, `created_at`, `updated_at`) VALUES
(1, 'test_email', 'email', 1, 'Test Email - {{ name }}', '<h1>Hello {{ name }}!</h1><p>{{ message }}</p>', 'Hello {{ name }}! {{ message }}', NULL, NULL, NULL, '[\"name\", \"message\"]', 1, '2025-11-26 12:17:46', '2025-11-26 12:17:46'),
(3, 'backup_success', 'email', 1, 'Backup Successful: {{ filename }}', '<h1>Backup Successful</h1><p>Your backup <strong>{{ filename }}</strong> ({{ size }}) was created successfully on {{ date }}.</p>', 'Backup Successful\n\nYour backup {{ filename }} ({{ size }}) was created successfully on {{ date }}.', NULL, NULL, NULL, '[\"filename\", \"size\", \"date\", \"backup_id\"]', 1, '2025-11-26 22:39:01', '2025-11-26 22:39:01'),
(8, 'backup_success_database', 'database', 1, 'Backup Successful', 'Backup {{ filename }} created successfully.', 'Backup {{ filename }} created successfully.', NULL, NULL, NULL, '[\"filename\", \"size\", \"date\", \"backup_id\"]', 1, '2025-11-26 22:52:03', '2025-11-26 22:52:03'),
(9, 'backup_failed_email', 'email', 1, 'Backup Failed', '<h1>Backup Failed</h1><p>The backup process failed with the following error:</p><pre>{{ error }}</pre><p>Date: {{ date }}</p>', 'Backup Failed\n\nThe backup process failed with the following error:\n{{ error }}\nDate: {{ date }}', NULL, NULL, NULL, '[\"error\", \"date\", \"backup_id\"]', 1, '2025-11-26 22:52:03', '2025-11-26 22:52:03'),
(10, 'backup_failed_database', 'database', 1, 'Backup Failed', 'Backup failed: {{ error }}', 'Backup failed: {{ error }}', NULL, NULL, NULL, '[\"error\", \"date\", \"backup_id\"]', 1, '2025-11-26 22:52:03', '2025-11-26 22:52:03');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(1, 'View Users', 'view.users', 'Can view users', '2025-11-30 12:16:00', '2025-11-30 12:16:00'),
(2, 'Create Users', 'create.users', 'Can create users', '2025-11-30 12:16:00', '2025-11-30 12:16:00'),
(3, 'View Posts', 'view.posts', 'Can view posts', '2025-11-30 12:16:00', '2025-11-30 12:16:00'),
(4, 'Create Posts', 'create.posts', 'Can create posts', '2025-11-30 12:16:00', '2025-11-30 12:16:00'),
(5, 'Access Admin', 'access.admin', 'Can access admin panel', '2025-11-30 12:16:00', '2025-11-30 12:16:00');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Administrateur', 'admin', 'Full system access', '2025-11-30 12:16:00', '2025-11-30 12:16:00'),
(2, 'Manager', 'manager', 'Can manage users and content', '2025-11-30 12:16:00', '2025-11-30 12:16:00'),
(3, 'Éditeur', 'editor', 'Can edit and publish content', '2025-11-30 12:16:00', '2025-11-30 12:16:00'),
(4, 'Rédacteur', 'writer', 'Can create content', '2025-11-30 12:16:00', '2025-11-30 12:16:00'),
(5, 'Utilisateur', 'user', 'Basic user access', '2025-11-30 12:16:00', '2025-11-30 12:16:00');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` bigint UNSIGNED NOT NULL,
  `permission_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2025-11-30 12:16:00', '2025-11-30 12:16:00'),
(1, 2, '2025-11-30 12:16:00', '2025-11-30 12:16:00'),
(1, 3, '2025-11-30 12:16:00', '2025-11-30 12:16:00'),
(1, 4, '2025-11-30 12:16:00', '2025-11-30 12:16:00'),
(1, 5, '2025-11-30 12:16:00', '2025-11-30 12:16:00');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint UNSIGNED NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `type` enum('string','integer','float','boolean','json','array') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `setting_group` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `type`, `setting_group`, `description`, `is_public`, `created_at`, `updated_at`) VALUES
(1, 'theme_mode', 'light', 'string', 'theme', NULL, 0, '2025-11-27 16:06:50', '2025-11-27 16:06:50'),
(2, 'openai_api_key', '', 'string', 'api', NULL, 0, '2025-11-27 16:07:00', '2025-11-27 16:07:00'),
(3, 'wallet.currency', 'XOF', 'string', 'wallet', NULL, 0, '2025-11-29 17:42:11', '2025-11-29 18:18:23'),
(4, 'wallet.min_topup', '1000', 'integer', 'wallet', NULL, 0, '2025-11-29 17:42:43', '2025-11-29 18:18:23'),
(5, 'sms_pricing_grid', '{\"CI\":{\"name\":\"C\\u00f4te d\'Ivoire\",\"default\":35,\"networks\":{\"orange\":35,\"mtn\":20,\"moov\":15}},\"default\":{\"price\":35,\"currency\":\"XOF\"}}', 'json', 'sms_pricing', NULL, 0, '2025-11-29 18:59:15', '2025-11-29 19:00:20');

-- --------------------------------------------------------

--
-- Table structure for table `sms_billing_logs`
--

CREATE TABLE `sms_billing_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `sender_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_code` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operator` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gateway` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sms_type` enum('text','otp','marketing') COLLATE utf8mb4_unicode_ci DEFAULT 'text',
  `segments` int DEFAULT '1',
  `unit_cost` decimal(10,4) DEFAULT '0.0000',
  `total_cost` decimal(10,4) DEFAULT '0.0000',
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT 'XOF',
  `status` enum('pending','paid','failed','refunded') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sms_billing_logs`
--

INSERT INTO `sms_billing_logs` (`id`, `user_id`, `sender_id`, `recipient`, `country_code`, `operator`, `gateway`, `sms_type`, `segments`, `unit_cost`, `total_cost`, `currency`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'SMS', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '15.0000', '15.0000', 'XOF', 'paid', NULL, NULL),
(2, 1, 'SMS', '+225', 'CI', 'unknown', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, NULL),
(3, 1, 'TestAPI', '+225', 'CI', 'unknown', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, NULL),
(4, 1, 'TestAPI', '+225', 'CI', 'unknown', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sms_campaigns`
--

CREATE TABLE `sms_campaigns` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `sender_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('draft','scheduled','sending','completed','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `total_recipients` int NOT NULL DEFAULT '0',
  `sent_count` int NOT NULL DEFAULT '0',
  `failed_count` int NOT NULL DEFAULT '0',
  `scheduled_at` datetime DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sms_campaigns`
--

INSERT INTO `sms_campaigns` (`id`, `name`, `message`, `sender_id`, `status`, `total_recipients`, `sent_count`, `failed_count`, `scheduled_at`, `started_at`, `completed_at`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'weekend sms', 'tesrrr', '', 'draft', 35, 0, 0, NULL, NULL, NULL, 1, '2025-11-29 18:42:02', '2025-11-29 18:42:02'),
(2, 'weekend sms', 'tesrrr', '', 'draft', 35, 0, 0, NULL, NULL, NULL, 1, '2025-11-29 18:45:04', '2025-11-29 18:45:04'),
(3, 'weekend sms', 'tesrrr', 'TICAFRIQUE', 'draft', 35, 0, 0, NULL, NULL, NULL, 1, '2025-11-29 18:45:22', '2025-11-29 18:45:22'),
(4, 'weekend sms', 'tesrrr', 'TICAFRIQUE', 'sending', 35, 0, 0, NULL, '2025-11-29 18:47:42', NULL, 1, '2025-11-29 18:47:42', '2025-11-29 18:47:42');

-- --------------------------------------------------------

--
-- Table structure for table `sms_gateways`
--

CREATE TABLE `sms_gateways` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_key` text COLLATE utf8mb4_unicode_ci,
  `api_secret` text COLLATE utf8mb4_unicode_ci,
  `sender_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `priority` int NOT NULL DEFAULT '0',
  `configuration` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sms_gateways`
--

INSERT INTO `sms_gateways` (`id`, `name`, `provider_code`, `api_url`, `api_key`, `api_secret`, `sender_id`, `is_active`, `is_default`, `priority`, `configuration`, `created_at`, `updated_at`) VALUES
(1, 'Orange Côte d\'Ivoire', 'orange_ci', 'https://api.orange.com/smsmessaging/v1/outbound', NULL, NULL, 'TICAFRIQUE', 1, 1, 10, '{\"auth_type\": \"oauth2\", \"token_url\": \"https://api.orange.com/oauth/v3/token\", \"country_code\": \"+225\"}', '2025-11-28 10:21:47', '2025-11-29 16:33:57'),
(2, 'Infobip', 'infobip', 'https://api.infobip.com/sms/2/text/advanced', '', '', '', 0, 0, 5, '{\"auth_type\": \"api_key\", \"max_recipients\": 1000, \"supports_unicode\": true}', '2025-11-28 10:21:47', '2025-11-28 10:21:47');

-- --------------------------------------------------------

--
-- Table structure for table `sms_messages`
--

CREATE TABLE `sms_messages` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint DEFAULT NULL,
  `to` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `gateway` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `message_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gateway_message_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cost` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `metadata` json DEFAULT NULL,
  `gateway_response` json DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `error` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sms_messages`
--

INSERT INTO `sms_messages` (`id`, `user_id`, `to`, `from`, `message`, `gateway`, `status`, `message_id`, `gateway_message_id`, `cost`, `metadata`, `gateway_response`, `scheduled_at`, `sent_at`, `delivered_at`, `error`, `created_at`, `updated_at`) VALUES
(1, 1, '+2250749270077', 'TICAFRIQUE', 'RAS', 'orange_ci', 'failed', 'SMS-6929d3fa1e0ea', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Failed to obtain access token', '2025-11-28 16:55:22', '2025-11-28 16:55:22'),
(2, 1, '+2250749270077', 'TICAFRIQUE', 'RAS', 'orange_ci', 'pending', 'SMS-6929d5f5d2be5', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-28 17:03:49', '2025-11-28 17:03:49'),
(3, 1, '+2250749270077', 'TICAFRIQUE', 'RAS', 'orange_ci', 'sent', 'SMS-6929d779aa02c', 'MOCK-6929D77A357A5', '0.0000', NULL, '{\"to\": \"+2250749270077\", \"cost\": 0.05, \"from\": \"TICAFRIQUE\", \"mock\": true, \"status\": \"success\", \"gateway\": \"orange_ci\", \"message\": \"RAS\", \"messageId\": \"MOCK-6929D77A357A5\", \"timestamp\": \"2025-11-28 17:10:18\"}', NULL, '2025-11-28 17:10:18', NULL, NULL, '2025-11-28 17:10:17', '2025-11-28 17:10:18'),
(4, 1, '+2250749270077', 'TICAFRIQUE', 'test', 'orange_ci', 'sent', 'SMS-6929d886eb364', 'MOCK-6929D8877144A', '0.0000', NULL, '{\"to\": \"+2250749270077\", \"cost\": 0.05, \"from\": \"TICAFRIQUE\", \"mock\": true, \"status\": \"success\", \"gateway\": \"orange_ci\", \"message\": \"test\", \"messageId\": \"MOCK-6929D8877144A\", \"timestamp\": \"2025-11-28 17:14:47\"}', NULL, '2025-11-28 17:14:47', NULL, NULL, '2025-11-28 17:14:46', '2025-11-28 17:14:47'),
(5, 1, '+2250749270077', 'TICAFRIQUE', 'RAS', 'orange_ci', 'sent', 'SMS-6929da879c500', 'MOCK-6929DA88238BB', '0.0000', NULL, '{\"to\": \"+2250749270077\", \"cost\": 0.05, \"from\": \"TICAFRIQUE\", \"mock\": true, \"status\": \"success\", \"gateway\": \"orange_ci\", \"message\": \"RAS\", \"messageId\": \"MOCK-6929DA88238BB\", \"timestamp\": \"2025-11-28 17:23:20\"}', NULL, '2025-11-28 17:23:20', NULL, NULL, '2025-11-28 17:23:19', '2025-11-28 17:23:20'),
(6, 1, '+2250749270077', 'TICAFRIQUE', 'rest', 'orange_ci', 'sent', 'SMS-692a22a557389', 'MOCK-692A22A5D6D89', '0.0000', NULL, '{\"to\": \"+2250749270077\", \"cost\": 0.05, \"from\": \"TICAFRIQUE\", \"mock\": true, \"status\": \"success\", \"gateway\": \"orange_ci\", \"message\": \"rest\", \"messageId\": \"MOCK-692A22A5D6D89\", \"timestamp\": \"2025-11-28 22:31:01\"}', NULL, '2025-11-28 22:31:01', NULL, NULL, '2025-11-28 22:31:01', '2025-11-28 22:31:01'),
(7, 1, '+2250749270077', 'TICAFRIQUE', 'TEST', 'orange_ci', 'sent', 'SMS-692aabc4dfcdc', 'MOCK-692AABC56A2F0', '0.0000', NULL, '{\"to\": \"+2250749270077\", \"cost\": 0.05, \"from\": \"TICAFRIQUE\", \"mock\": true, \"status\": \"success\", \"gateway\": \"orange_ci\", \"message\": \"TEST\", \"messageId\": \"MOCK-692AABC56A2F0\", \"timestamp\": \"2025-11-29 08:16:05\"}', NULL, '2025-11-29 08:16:05', NULL, NULL, '2025-11-29 08:16:04', '2025-11-29 08:16:05'),
(8, 1, '+2250749270077', 'TICAFRIQUE', 'TEST', 'orange_ci', 'sent', 'SMS-692ab0f75f17d', 'MOCK-692AB0F7DCB82', '0.0000', NULL, '{\"to\": \"+2250749270077\", \"cost\": 0.05, \"from\": \"TICAFRIQUE\", \"mock\": true, \"status\": \"success\", \"gateway\": \"orange_ci\", \"message\": \"TEST\", \"messageId\": \"MOCK-692AB0F7DCB82\", \"timestamp\": \"2025-11-29 08:38:15\"}', NULL, '2025-11-29 08:38:15', NULL, NULL, '2025-11-29 08:38:15', '2025-11-29 08:38:15'),
(9, 1, '+2250749270077', 'TICAFRIQUE', 'SMS TEST DEVINCI', 'orange_ci', 'sent', 'SMS-692ab10d494df', 'MOCK-692AB10DC6DA0', '0.0000', NULL, '{\"to\": \"+2250749270077\", \"cost\": 0.05, \"from\": \"TICAFRIQUE\", \"mock\": true, \"status\": \"success\", \"gateway\": \"orange_ci\", \"message\": \"SMS TEST DEVINCI\", \"messageId\": \"MOCK-692AB10DC6DA0\", \"timestamp\": \"2025-11-29 08:38:37\"}', NULL, '2025-11-29 08:38:37', NULL, NULL, '2025-11-29 08:38:37', '2025-11-29 08:38:37'),
(10, 1, '+2250749270077', 'TICAFRIQUE', 'TEST ENvoie', 'orange_ci', 'sent', 'SMS-692abe5ac639d', 'MOCK-692ABE5B4EFA7', '0.0000', NULL, '{\"to\": \"+2250749270077\", \"cost\": 0.05, \"from\": \"TICAFRIQUE\", \"mock\": true, \"status\": \"success\", \"gateway\": \"orange_ci\", \"message\": \"TEST ENvoie\", \"messageId\": \"MOCK-692ABE5B4EFA7\", \"timestamp\": \"2025-11-29 09:35:23\"}', NULL, '2025-11-29 09:35:23', NULL, NULL, '2025-11-29 09:35:22', '2025-11-29 09:35:23'),
(11, 1, '+2250749270077', 'TICAFRIQUE', 'devinci fortt', 'orange_ci', 'failed', 'SMS-692ac0e0d629c', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Failed to obtain access token', '2025-11-29 09:46:08', '2025-11-29 09:46:09'),
(12, 1, '+2250749270077', 'TICAFRIQUE', 'test fulbert', 'orange_ci', 'failed', 'SMS-692ac340e98f4', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Failed to obtain access token', '2025-11-29 09:56:16', '2025-11-29 09:56:17'),
(13, 1, '+2250749270077', 'TICAFRIQUE', 'TEST ILANE', 'orange_ci', 'failed', 'SMS-692ac5ab5d61c', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Failed to obtain access token', '2025-11-29 10:06:35', '2025-11-29 10:06:36'),
(14, 1, '+2250749270077', 'TICAFRIQUE', 'TEST ORANGE API', 'orange_ci', 'failed', 'SMS-692ac5d818c57', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Failed to obtain access token', '2025-11-29 10:07:20', '2025-11-29 10:07:20'),
(15, 1, '+2250749270077', 'TICAFRIQUE', 'ddgdfg dfgdf', 'orange_ci', 'sent', 'SMS-692ae4da6d5a0', 'MOCK-692AE4DAE9A12', '0.0000', NULL, '{\"to\": \"+2250749270077\", \"cost\": 0.05, \"from\": \"TICAFRIQUE\", \"mock\": true, \"status\": \"success\", \"gateway\": \"orange_ci\", \"message\": \"ddgdfg dfgdf\", \"messageId\": \"MOCK-692AE4DAE9A12\", \"timestamp\": \"2025-11-29 12:19:38\"}', NULL, '2025-11-29 12:19:38', NULL, NULL, '2025-11-29 12:19:38', '2025-11-29 12:19:38'),
(16, 1, '+2250749270077', 'TICAFRIQUE', 'test', 'orange_ci', 'failed', 'SMS-692ae841e6032', NULL, '0.0000', NULL, '{\"requestError\": {\"policyException\": {\"text\": \"A policy error occurred. Error code is %1\", \"messageId\": \"POL0001\", \"variables\": [\"Expired contract. You can buy a new bundle on https://developer.orange.com to reactivate it or contact Orange local team via https://developer.orange.com/sms-api-queries/ to renew it.\"]}}}', NULL, NULL, NULL, 'Failed to send SMS', '2025-11-29 12:34:09', '2025-11-29 12:34:11'),
(17, 1, '+2250749270077', 'SMS', 'gfdg', 'orange_ci', 'pending', 'SMS-692b2089936af', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-29 16:34:17', '2025-11-29 16:34:17'),
(18, 1, '+2250749270077', 'SMS', 'gfdg', 'orange_ci', 'pending', 'SMS-692b2093193f1', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-29 16:34:27', '2025-11-29 16:34:27'),
(19, 1, '+2250749270077', 'SMS', 'gfdg', 'orange_ci', 'failed', 'SMS-692b20cee0f3c', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Insufficient balance. Cost: 15 XOF', '2025-11-29 16:35:26', '2025-11-29 16:35:26'),
(20, 1, '+2250700000000', 'TestSMSSMS', 'Test de facturation SMS', 'orange_ci', 'failed', 'SMS-692b21409729b', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Insufficient balance. Cost: 15 XOF', '2025-11-29 16:37:20', '2025-11-29 16:37:20'),
(21, 1, '+2250749270077', 'SMS', 'AZERTRY', 'orange_ci', 'sent', 'SMS-692b30497354f', 'MOCK-692B304A042A7', '0.0000', NULL, '{\"to\": \"+2250749270077\", \"cost\": 0.05, \"from\": \"SMS\", \"mock\": true, \"status\": \"success\", \"gateway\": \"orange_ci\", \"message\": \"AZERTRY\", \"messageId\": \"MOCK-692B304A042A7\", \"timestamp\": \"2025-11-29 17:41:30\"}', NULL, '2025-11-29 17:41:30', NULL, NULL, '2025-11-29 17:41:29', '2025-11-29 17:41:30');

-- --------------------------------------------------------

--
-- Table structure for table `sms_queue`
--

CREATE TABLE `sms_queue` (
  `id` bigint UNSIGNED NOT NULL,
  `campaign_id` int DEFAULT NULL,
  `recipient` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `sender_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','processing','sent','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `attempts` int NOT NULL DEFAULT '0',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `scheduled_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sms_queue`
--

INSERT INTO `sms_queue` (`id`, `campaign_id`, `recipient`, `message`, `sender_id`, `status`, `attempts`, `error_message`, `scheduled_at`, `sent_at`, `created_at`, `updated_at`) VALUES
(1, 2, '2250505569256', 'tesrrr', '', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:45:04', '2025-11-29 18:45:04'),
(2, 3, '2250505569256', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:45:22', '2025-11-29 18:45:22'),
(3, 4, '2250505569256', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(4, 4, '2250749270077', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(5, 4, '2250505500557', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(6, 4, '2250778599242', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(7, 4, '2250505569256', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(8, 4, '2250749270077', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(9, 4, '2250505500557', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(10, 4, '2250778599242', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(11, 4, '2250505569256', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(12, 4, '2250749270077', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(13, 4, '2250505500557', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(14, 4, '2250778599242', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(15, 4, '2250505569256', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(16, 4, '2250749270077', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(17, 4, '2250505500557', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(18, 4, '2250778599242', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(19, 4, '2250505569256', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(20, 4, '2250749270077', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(21, 4, '2250505500557', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(22, 4, '2250778599242', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(23, 4, '2250505569256', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(24, 4, '2250749270077', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(25, 4, '2250505500557', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(26, 4, '2250778599242', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(27, 4, '2250505569256', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(28, 4, '2250749270077', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(29, 4, '2250505500557', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(30, 4, '2250778599242', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(31, 4, '2250505569256', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(32, 4, '2250749270077', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(33, 4, '2250505500557', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(34, 4, '2250778599242', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(35, 4, '2250505569256', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(36, 4, '2250749270077', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42'),
(37, 4, '2250505500557', 'tesrrr', 'TICAFRIQUE', 'pending', 0, NULL, NULL, NULL, '2025-11-29 18:47:42', '2025-11-29 18:47:42');

-- --------------------------------------------------------

--
-- Table structure for table `translations`
--

CREATE TABLE `translations` (
  `id` bigint UNSIGNED NOT NULL,
  `language` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `module` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `updated_by` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `translation_history`
--

CREATE TABLE `translation_history` (
  `id` bigint UNSIGNED NOT NULL,
  `translation_id` int NOT NULL,
  `old_value` text COLLATE utf8mb4_unicode_ci,
  `new_value` text COLLATE utf8mb4_unicode_ci,
  `changed_by` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `api_key` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `api_key_created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `first_name`, `last_name`, `avatar`, `is_active`, `is_verified`, `created_at`, `updated_at`, `api_key`, `api_key_created_at`) VALUES
(1, 'admin', 'admin@example.com', '$2y$10$L/1CBhewnU/lBbTrj9RwuOK8ibm.XP.Zrsh08Jvc/whquiXYMETXS', NULL, NULL, NULL, 1, 0, '2025-11-25 17:27:10', '2025-11-30 10:57:24', 'fb3e2834b0ecb898b04f70509554e0207dd225728a17d0c970b3ae766a7ed05f', '2025-11-29 19:25:39');

-- --------------------------------------------------------

--
-- Table structure for table `user_notification_preferences`
--

CREATE TABLE `user_notification_preferences` (
  `user_id` bigint UNSIGNED NOT NULL,
  `channels_enabled` json DEFAULT NULL,
  `dnd_enabled` tinyint(1) DEFAULT '0',
  `dnd_from` time DEFAULT NULL,
  `dnd_to` time DEFAULT NULL,
  `timezone` varchar(50) DEFAULT 'UTC',
  `email_opt_in` tinyint(1) DEFAULT '1',
  `sms_opt_in` tinyint(1) DEFAULT '1',
  `push_opt_in` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_notification_preferences`
--

INSERT INTO `user_notification_preferences` (`user_id`, `channels_enabled`, `dnd_enabled`, `dnd_from`, `dnd_to`, `timezone`, `email_opt_in`, `sms_opt_in`, `push_opt_in`, `created_at`, `updated_at`) VALUES
(1, '[\"email\"]', 0, NULL, NULL, 'UTC', 1, 1, 1, '2025-11-26 12:31:58', '2025-11-26 12:31:58');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2025-11-30 12:19:52', '2025-11-30 12:19:52');

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `balance` decimal(15,2) DEFAULT '0.00',
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT 'XOF',
  `status` enum('active','frozen','suspended') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`id`, `user_id`, `balance`, `currency`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, '1760.00', 'XOF', 'active', '2025-11-29 17:24:53', '2025-11-29 17:24:53');

-- --------------------------------------------------------

--
-- Table structure for table `wallet_gateways`
--

CREATE TABLE `wallet_gateways` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_key` text COLLATE utf8mb4_unicode_ci,
  `api_secret` text COLLATE utf8mb4_unicode_ci,
  `merchant_id` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'XOF',
  `transaction_fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `configuration` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallet_gateways`
--

INSERT INTO `wallet_gateways` (`id`, `name`, `provider_code`, `api_url`, `api_key`, `api_secret`, `merchant_id`, `is_active`, `is_default`, `currency`, `transaction_fee`, `configuration`, `created_at`, `updated_at`) VALUES
(1, 'PayDunya', 'paydunya', 'https://app.paydunya.com/api/v1', '', '', '', 0, 0, 'XOF', '2.50', '{\"mode\": \"live\", \"supported_methods\": [\"orange_money\", \"mtn\", \"moov\", \"card\"]}', '2025-11-28 10:21:47', '2025-11-28 10:21:47'),
(2, 'CinetPay', 'cinetpay', 'https://api-checkout.cinetpay.com/v2', '', '', '', 0, 1, 'XOF', '3.00', '{\"mode\": \"PRODUCTION\", \"supported_methods\": [\"ORANGE_MONEY_CI\", \"MOOV_CI\", \"MTN_CI\", \"WAVE_CI\"]}', '2025-11-28 10:21:47', '2025-11-28 10:21:47'),
(3, 'Orange Money Côte d\'Ivoire', 'orange_money_ci', 'https://api.orange.com/orange-money-webpay/dev/v1', '', '', '', 1, 0, 'XOF', '1.50', '{\"auth_type\": \"oauth2\", \"country_code\": \"CI\"}', '2025-11-28 10:21:47', '2025-11-29 18:18:23'),
(4, 'Wave Côte d\'Ivoire', 'wave_ci', 'https://api.wave.com/v1', '', '', '', 1, 0, 'XOF', '1.00', '{\"country_code\": \"CI\", \"qr_code_enabled\": true}', '2025-11-28 10:21:47', '2025-11-29 18:18:23');

-- --------------------------------------------------------

--
-- Table structure for table `wallet_transactions`
--

CREATE TABLE `wallet_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `wallet_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `type` enum('credit','debit','refund') COLLATE utf8mb4_unicode_ci DEFAULT 'credit',
  `amount` decimal(15,2) NOT NULL,
  `balance_before` decimal(15,2) NOT NULL,
  `balance_after` decimal(15,2) NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','completed','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'completed',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallet_transactions`
--

INSERT INTO `wallet_transactions` (`id`, `wallet_id`, `user_id`, `type`, `amount`, `balance_before`, `balance_after`, `description`, `metadata`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'credit', '500.00', '1000.00', '1500.00', 'Credit added', NULL, 'completed', NULL, NULL),
(2, 1, 1, 'debit', '15.00', '1500.00', '1485.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL),
(3, 1, 1, 'debit', '15.00', '1485.00', '1470.00', 'SMS Charge: ID #1', NULL, 'completed', NULL, NULL),
(4, 1, 1, 'debit', '35.00', '1470.00', '1435.00', 'SMS to +225 (1 segments)', NULL, 'completed', NULL, NULL),
(5, 1, 1, 'debit', '35.00', '1435.00', '1400.00', 'SMS Charge: ID #2', NULL, 'completed', NULL, NULL),
(6, 1, 1, 'debit', '35.00', '1400.00', '1365.00', 'SMS to +225 (1 segments)', NULL, 'completed', NULL, NULL),
(7, 1, 1, 'debit', '35.00', '1365.00', '1330.00', 'SMS Charge: ID #3', NULL, 'completed', NULL, NULL),
(8, 1, 1, 'debit', '35.00', '1330.00', '1295.00', 'SMS to +225 (1 segments)', NULL, 'completed', NULL, NULL),
(9, 1, 1, 'debit', '35.00', '1295.00', '1260.00', 'SMS Charge: ID #4', NULL, 'completed', NULL, NULL),
(10, 1, 1, 'credit', '500.00', '1260.00', '1760.00', 'Test Credit', NULL, 'completed', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `webhooks`
--

CREATE TABLE `webhooks` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `events` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `headers` text COLLATE utf8mb4_unicode_ci,
  `retry_count` int NOT NULL DEFAULT '3',
  `timeout` int NOT NULL DEFAULT '30',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `webhook_logs`
--

CREATE TABLE `webhook_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `webhook_id` int NOT NULL,
  `payload` text COLLATE utf8mb4_unicode_ci,
  `response` text COLLATE utf8mb4_unicode_ci,
  `http_code` int DEFAULT NULL,
  `error` text COLLATE utf8mb4_unicode_ci,
  `duration` float(8,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_agents`
--
ALTER TABLE `ai_agents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `module` (`module`);

--
-- Indexes for table `ai_logs`
--
ALTER TABLE `ai_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `backups`
--
ALTER TABLE `backups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cache_config`
--
ALTER TABLE `cache_config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cron_logs`
--
ALTER TABLE `cron_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_task_id` (`task_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_started_at` (`started_at`);

--
-- Indexes for table `cron_tasks`
--
ALTER TABLE `cron_tasks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_enabled` (`enabled`),
  ADD KEY `idx_next_run_at` (`next_run_at`),
  ADD KEY `idx_module` (`module`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_queue` (`queue`),
  ADD KEY `idx_failed_at` (`failed_at`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_queue` (`queue`),
  ADD KEY `idx_reserved_at` (`reserved_at`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `logs_channel_index` (`channel`),
  ADD KEY `logs_level_index` (`level`),
  ADD KEY `logs_created_at_index` (`created_at`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status_scheduled` (`status`,`scheduled_at`);

--
-- Indexes for table `notification_recipients`
--
ALTER TABLE `notification_recipients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_status` (`user_id`,`status`),
  ADD KEY `notification_id` (`notification_id`);

--
-- Indexes for table `notification_templates`
--
ALTER TABLE `notification_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD KEY `role_permissions_role_id_permission_id_index` (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`),
  ADD KEY `idx_settings_group` (`setting_group`),
  ADD KEY `idx_settings_key` (`key`);

--
-- Indexes for table `sms_billing_logs`
--
ALTER TABLE `sms_billing_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_status` (`user_id`,`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `sms_campaigns`
--
ALTER TABLE `sms_campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sms_campaigns_status_index` (`status`),
  ADD KEY `sms_campaigns_created_by_index` (`created_by`);

--
-- Indexes for table `sms_gateways`
--
ALTER TABLE `sms_gateways`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `provider_code` (`provider_code`),
  ADD KEY `sms_gateways_provider_code_index` (`provider_code`),
  ADD KEY `sms_gateways_is_active_index` (`is_active`),
  ADD KEY `sms_gateways_is_default_index` (`is_default`),
  ADD KEY `sms_gateways_priority_index` (`priority`);

--
-- Indexes for table `sms_messages`
--
ALTER TABLE `sms_messages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `message_id` (`message_id`),
  ADD KEY `sms_messages_user_id_index` (`user_id`),
  ADD KEY `sms_messages_status_index` (`status`),
  ADD KEY `sms_messages_gateway_index` (`gateway`),
  ADD KEY `sms_messages_message_id_index` (`message_id`),
  ADD KEY `sms_messages_created_at_index` (`created_at`);

--
-- Indexes for table `sms_queue`
--
ALTER TABLE `sms_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sms_queue_campaign_id_index` (`campaign_id`),
  ADD KEY `sms_queue_status_index` (`status`),
  ADD KEY `sms_queue_scheduled_at_index` (`scheduled_at`);

--
-- Indexes for table `translations`
--
ALTER TABLE `translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_translation` (`language`,`key`),
  ADD KEY `idx_translations_language` (`language`),
  ADD KEY `idx_translations_module` (`module`);

--
-- Indexes for table `translation_history`
--
ALTER TABLE `translation_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_translation_history_translation_id` (`translation_id`),
  ADD KEY `idx_translation_history_changed_by` (`changed_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `api_key` (`api_key`);

--
-- Indexes for table `user_notification_preferences`
--
ALTER TABLE `user_notification_preferences`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD KEY `user_roles_user_id_role_id_index` (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `wallet_gateways`
--
ALTER TABLE `wallet_gateways`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `provider_code` (`provider_code`),
  ADD KEY `wallet_gateways_provider_code_index` (`provider_code`),
  ADD KEY `wallet_gateways_is_active_index` (`is_active`),
  ADD KEY `wallet_gateways_is_default_index` (`is_default`);

--
-- Indexes for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_wallet_type` (`wallet_id`,`type`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `webhooks`
--
ALTER TABLE `webhooks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_webhooks_is_active` (`is_active`);

--
-- Indexes for table `webhook_logs`
--
ALTER TABLE `webhook_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_webhook_logs_webhook_id` (`webhook_id`),
  ADD KEY `idx_webhook_logs_created_at` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_agents`
--
ALTER TABLE `ai_agents`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_logs`
--
ALTER TABLE `ai_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `backups`
--
ALTER TABLE `backups`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cache_config`
--
ALTER TABLE `cache_config`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cron_logs`
--
ALTER TABLE `cron_logs`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cron_tasks`
--
ALTER TABLE `cron_tasks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `notification_recipients`
--
ALTER TABLE `notification_recipients`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `notification_templates`
--
ALTER TABLE `notification_templates`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sms_billing_logs`
--
ALTER TABLE `sms_billing_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sms_campaigns`
--
ALTER TABLE `sms_campaigns`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sms_gateways`
--
ALTER TABLE `sms_gateways`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sms_messages`
--
ALTER TABLE `sms_messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `sms_queue`
--
ALTER TABLE `sms_queue`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `translations`
--
ALTER TABLE `translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `translation_history`
--
ALTER TABLE `translation_history`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wallet_gateways`
--
ALTER TABLE `wallet_gateways`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `webhooks`
--
ALTER TABLE `webhooks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `webhook_logs`
--
ALTER TABLE `webhook_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cron_logs`
--
ALTER TABLE `cron_logs`
  ADD CONSTRAINT `cron_logs_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `cron_tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_recipients`
--
ALTER TABLE `notification_recipients`
  ADD CONSTRAINT `notification_recipients_ibfk_1` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notification_recipients_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_notification_preferences`
--
ALTER TABLE `user_notification_preferences`
  ADD CONSTRAINT `user_notification_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
