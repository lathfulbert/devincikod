-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 27, 2025 at 06:22 AM
-- Server version: 8.0.30
-- PHP Version: 7.4.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sunuframework3`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_agents`
--

CREATE TABLE `ai_agents` (
  `id` bigint UNSIGNED NOT NULL,
  `module` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `agent_class` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `module` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `agent_class` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `prompt` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `response` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tokens_used` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `api_keys`
--

CREATE TABLE `api_keys` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `prefix` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permissions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ip_whitelist` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_used_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `updated_by` int UNSIGNED DEFAULT NULL,
  `deleted_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `api_keys`
--

INSERT INTO `api_keys` (`id`, `user_id`, `name`, `key`, `prefix`, `permissions`, `ip_whitelist`, `last_used_at`, `expires_at`, `is_active`, `created_at`, `created_by`, `updated_at`, `deleted_at`, `updated_by`, `deleted_by`) VALUES
(19, 1, 'Personal API Key', '4cbb2bdeaeb0b28816a4d18398c9eb57a164660cf1245774376fee0459f40bbc', NULL, NULL, NULL, '2025-12-10 15:43:33', NULL, 1, '2025-12-10 15:01:37', 1, '2025-12-19 12:28:46', NULL, 1, NULL),
(20, 4, 'Personal API Key', 'e43242e0baab8090a1aba16abe387c52713e6487d54b432b79eb7e0e266ccd46', NULL, NULL, NULL, '2025-12-19 12:58:30', NULL, 1, NULL, NULL, '2025-12-19 12:32:23', NULL, 4, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `api_request_logs`
--

CREATE TABLE `api_request_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `api_key_id` bigint DEFAULT NULL,
  `user_id` bigint DEFAULT NULL,
  `endpoint` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `method` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `request_headers` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `request_body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status_code` int NOT NULL,
  `response_body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `response_time` int DEFAULT NULL,
  `user_agent` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referer` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requested_at` datetime NOT NULL,
  `created_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `auth_logs`
--

CREATE TABLE `auth_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `event_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `details` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `auth_logs`
--

INSERT INTO `auth_logs` (`id`, `user_id`, `event_type`, `ip_address`, `user_agent`, `details`, `created_at`) VALUES
(1, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-04 22:30:00'),
(2, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-04 22:35:37'),
(3, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-04 22:35:56'),
(4, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-04 22:47:12'),
(5, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-05 09:26:57'),
(6, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-05 09:27:21'),
(7, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-05 09:27:58'),
(8, 1, 'mfa_failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"method\": \"email\", \"reason\": \"no_otp_sent\"}', '2025-12-05 09:28:07'),
(9, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-05 09:28:32'),
(10, 1, 'mfa_failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"method\": \"email\", \"reason\": \"no_otp_sent\"}', '2025-12-05 09:28:37'),
(11, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-05 09:31:22'),
(12, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-05 09:56:18'),
(13, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-05 09:56:30'),
(14, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 09:44:52'),
(15, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 09:45:05'),
(16, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 16:18:18'),
(17, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 16:59:08'),
(18, 1, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', NULL, '2025-12-07 17:14:27'),
(19, 1, 'mfa_failed', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '{\"method\": \"sms\", \"reason\": \"no_otp_sent\"}', '2025-12-07 17:34:33'),
(20, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 17:40:50'),
(21, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 17:43:15'),
(22, 1, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', NULL, '2025-12-07 17:49:40'),
(23, 1, 'mfa_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '{\"method\": \"sms\"}', '2025-12-07 17:50:14'),
(24, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', NULL, '2025-12-07 17:50:34'),
(25, 1, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', NULL, '2025-12-07 17:50:41'),
(26, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 17:54:05'),
(27, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 18:05:47'),
(28, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 18:06:13'),
(29, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 18:06:47'),
(30, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 18:08:09'),
(31, 1, 'mfa_failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"method\": \"sms\", \"reason\": \"otp_expired\"}', '2025-12-07 18:10:56'),
(32, 1, 'mfa_failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"method\": \"sms\", \"reason\": \"no_otp_sent\"}', '2025-12-07 18:11:10'),
(33, 1, 'mfa_failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"method\": \"sms\", \"reason\": \"invalid_code\"}', '2025-12-07 18:11:57'),
(34, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 18:12:32'),
(35, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 18:17:11'),
(36, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 18:22:15'),
(37, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 18:22:20'),
(38, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 18:26:16'),
(39, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 18:26:34'),
(40, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 18:39:22'),
(41, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 18:39:33'),
(42, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 18:41:37'),
(43, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-08 04:48:12'),
(44, 1, 'login_failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"reason\": \"invalid_password\"}', '2025-12-08 04:57:03'),
(45, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-08 04:57:16'),
(46, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-08 04:57:55'),
(47, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-08 04:58:08'),
(48, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-08 05:00:19'),
(49, 1, 'mfa_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"method\": \"sms\"}', '2025-12-08 05:02:05'),
(50, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-08 05:02:14'),
(51, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-08 05:02:23'),
(52, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-08 05:32:41'),
(53, 1, 'login_failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"reason\": \"invalid_password\"}', '2025-12-08 05:41:01'),
(54, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-08 05:41:10'),
(55, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-08 06:12:59'),
(56, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-08 08:26:59'),
(57, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', NULL, '2025-12-08 08:43:05'),
(58, 1, 'mfa_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '{\"method\": \"sms\"}', '2025-12-08 08:43:43'),
(59, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-08 20:46:22'),
(60, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-08 20:59:05'),
(61, 3, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-08 20:59:19'),
(62, 1, 'login_failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"reason\": \"invalid_password\"}', '2025-12-09 13:30:00'),
(63, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-09 13:30:08'),
(64, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-09 20:44:50'),
(65, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-10 07:55:28'),
(66, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-10 08:13:21'),
(67, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-10 08:18:54'),
(68, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-10 14:02:36'),
(69, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-10 21:42:54'),
(70, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-11 21:04:04'),
(71, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-11 21:17:44'),
(72, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-12 05:24:26'),
(73, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-12 06:58:10'),
(74, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-12 06:58:23'),
(75, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-12 07:01:51'),
(76, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-12 08:28:42'),
(77, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-12 13:01:26'),
(78, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-12 13:01:42'),
(79, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-12 13:31:57'),
(80, 4, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-12 13:32:07'),
(81, 4, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-12 20:58:55'),
(82, 1, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 02:02:53'),
(83, 1, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 02:03:28'),
(84, 1, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 05:52:28'),
(85, 1, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 05:52:31'),
(86, 1, 'mfa_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"method\": \"sms\"}', '2025-12-13 05:53:02'),
(87, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 05:57:47'),
(88, 4, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 05:57:59'),
(89, 1, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 05:59:02'),
(90, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 07:49:26'),
(91, 1, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 09:02:49'),
(92, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 09:09:09'),
(93, 4, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 09:09:24'),
(94, 1, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 09:33:04'),
(95, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 09:33:22'),
(96, 4, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 09:33:29'),
(97, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 09:33:40'),
(98, 1, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 09:33:46'),
(99, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 09:36:35'),
(100, 4, 'login_failed', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"reason\": \"invalid_password\"}', '2025-12-13 09:36:41'),
(101, 4, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 09:36:53'),
(102, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 09:53:58'),
(103, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 09:54:13'),
(104, 1, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 09:54:22'),
(105, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 10:01:25'),
(106, 4, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 10:01:35'),
(107, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 10:05:21'),
(108, 4, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 10:05:41'),
(109, 1, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 10:09:31'),
(110, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 10:12:10'),
(111, 4, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 10:12:19'),
(112, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 10:12:25'),
(113, 1, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 10:13:17'),
(114, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 10:13:52'),
(115, 4, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 10:13:59'),
(116, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 10:14:12'),
(117, 1, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 10:14:24'),
(118, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 10:18:22'),
(119, 4, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 10:18:39'),
(120, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 10:19:02'),
(121, 1, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 10:19:13'),
(122, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 10:24:59'),
(123, 4, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 10:25:07'),
(124, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 10:28:14'),
(125, 1, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-13 10:28:23'),
(126, 1, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-14 06:30:33'),
(127, 1, 'mfa_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\": \"sms\"}', '2025-12-14 06:31:05'),
(128, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-14 06:41:52'),
(129, 4, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-14 06:42:03'),
(130, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-14 06:48:07'),
(131, 1, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-14 06:48:13'),
(132, 4, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', NULL, '2025-12-14 06:51:02'),
(133, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-14 07:53:52'),
(134, 1, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-14 07:54:27'),
(135, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-14 09:12:06'),
(136, 4, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-14 09:12:17'),
(137, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-14 09:12:34'),
(138, 1, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-14 09:12:47'),
(139, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', NULL, '2025-12-14 11:42:47'),
(140, 4, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', NULL, '2025-12-14 11:43:06'),
(141, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-14 16:49:22'),
(142, 1, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-14 16:49:31'),
(143, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', NULL, '2025-12-14 21:11:01'),
(144, 4, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', NULL, '2025-12-14 21:11:15'),
(145, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', NULL, '2025-12-14 22:33:26'),
(146, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-14 23:09:39'),
(147, 4, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-14 23:09:46'),
(148, 4, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-14 23:10:51'),
(149, 1, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-14 23:11:00'),
(150, 1, 'login_success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', NULL, '2025-12-14 23:33:39'),
(151, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 08:24:51'),
(152, 1, 'mfa_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\": \"sms\"}', '2025-12-15 08:25:11'),
(153, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 09:09:21'),
(154, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 09:10:36'),
(155, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 12:30:13'),
(156, 4, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 12:30:19'),
(157, 4, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 13:04:28'),
(158, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 13:04:35'),
(159, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 13:11:45'),
(160, 4, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 13:12:01'),
(161, 4, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 13:12:15'),
(162, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 13:12:21'),
(163, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 13:39:10'),
(164, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 13:39:15'),
(165, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 13:47:23'),
(166, 4, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 13:47:30'),
(167, 4, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 13:49:57'),
(168, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 13:50:05'),
(169, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 14:31:37'),
(170, 4, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 14:31:46'),
(171, 4, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 14:32:02'),
(172, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 14:32:09'),
(173, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 14:32:41'),
(174, 4, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 14:32:51'),
(175, 4, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 14:33:12'),
(176, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 14:33:18'),
(177, 4, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', NULL, '2025-12-15 14:34:32'),
(178, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 14:41:46'),
(179, 4, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 14:41:54'),
(180, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 15:00:54'),
(181, 4, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', NULL, '2025-12-15 15:08:08'),
(182, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 17:05:12'),
(183, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-15 17:25:53'),
(184, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-16 09:48:49'),
(185, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-19 10:07:59'),
(186, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-19 10:24:57'),
(187, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-19 10:28:52'),
(188, NULL, 'login_failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\": \"user_not_found\", \"identifier\": \"AKADI\"}', '2025-12-19 10:28:58'),
(189, NULL, 'login_failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\": \"user_not_found\", \"identifier\": \"akadi\"}', '2025-12-19 10:29:13'),
(190, NULL, 'login_failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\": \"user_not_found\", \"identifier\": \"AKADI\"}', '2025-12-19 10:29:21'),
(191, 4, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-19 10:51:46'),
(192, 4, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-19 12:03:05'),
(193, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-19 12:03:17'),
(194, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-19 12:32:06'),
(195, 4, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-19 12:32:15'),
(196, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-20 08:55:27'),
(197, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-20 14:43:20'),
(198, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-20 14:43:31'),
(199, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-21 06:48:47'),
(200, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-21 07:09:43'),
(201, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-21 07:09:50'),
(202, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-21 17:52:38'),
(203, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-21 17:53:03'),
(204, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-21 17:53:07');

-- --------------------------------------------------------

--
-- Table structure for table `auth_settings`
--

CREATE TABLE `auth_settings` (
  `id` int UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `type` varchar(32) DEFAULT 'string',
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `auth_settings`
--

INSERT INTO `auth_settings` (`id`, `key`, `value`, `type`, `description`, `created_at`, `updated_at`) VALUES
(1, 'max_login_retries', '5', 'int', '', '2025-12-20 14:43:36', '2025-12-20 14:43:36'),
(2, 'lockout_period', '15', 'int', '', '2025-12-20 14:43:36', '2025-12-20 14:43:36'),
(3, 'max_lockouts', '3', 'int', '', '2025-12-20 14:43:36', '2025-12-20 14:43:36'),
(4, 'password_reset_retries', '4', 'int', '', '2025-12-20 14:43:36', '2025-12-20 15:04:26');

-- --------------------------------------------------------

--
-- Table structure for table `backups`
--

CREATE TABLE `backups` (
  `id` bigint UNSIGNED NOT NULL,
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `disk` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local',
  `size` bigint NOT NULL DEFAULT '0',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `initiated_by` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'system',
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_config`
--

CREATE TABLE `cache_config` (
  `id` bigint UNSIGNED NOT NULL,
  `driver` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'filesystem',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `prefix` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cache_',
  `default_ttl` int NOT NULL DEFAULT '3600',
  `filesystem_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'storage/cache',
  `redis_host` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '127.0.0.1',
  `redis_port` int NOT NULL DEFAULT '6379',
  `redis_password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `redis_database` int NOT NULL DEFAULT '0',
  `memcached_servers` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `apcu_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache_config`
--

INSERT INTO `cache_config` (`id`, `driver`, `enabled`, `prefix`, `default_ttl`, `filesystem_path`, `redis_host`, `redis_port`, `redis_password`, `redis_database`, `memcached_servers`, `apcu_enabled`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(1, 'filesystem', 1, 'cache_', 3600, 'storage/cache', '127.0.0.1', 6379, NULL, 0, '[{\"host\":\"127.0.0.1\",\"port\":11211}]', 0, '2025-11-26 11:06:57', NULL, '2025-11-26 11:06:57', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `campaign_logs`
--

CREATE TABLE `campaign_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `campaign_id` bigint NOT NULL,
  `campaign_type` enum('email','sms','multichannel','workflow') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel` enum('email','sms','push','whatsapp') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_id` bigint DEFAULT NULL,
  `recipient_identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','sent','delivered','opened','clicked','failed','bounced','unsubscribed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cost` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `gateway` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` bigint UNSIGNED NOT NULL,
  `phone` varchar(20) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `custom_fields` json DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `updated_by` int UNSIGNED DEFAULT NULL,
  `deleted_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_field_definitions`
--

CREATE TABLE `contact_field_definitions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `type` enum('text','number','date','select','textarea') DEFAULT 'text',
  `options` json DEFAULT NULL,
  `is_required` tinyint(1) DEFAULT '0',
  `default_value` varchar(255) DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `order_field` int DEFAULT '0',
  `placeholder` varchar(255) DEFAULT NULL,
  `help_text` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cron_logs`
--

CREATE TABLE `cron_logs` (
  `id` bigint NOT NULL,
  `task_id` int NOT NULL,
  `started_at` datetime NOT NULL,
  `finished_at` datetime DEFAULT NULL,
  `status` enum('running','success','failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'running',
  `output` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `error` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `duration_ms` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cron_logs`
--

INSERT INTO `cron_logs` (`id`, `task_id`, `started_at`, `finished_at`, `status`, `output`, `error`, `duration_ms`, `created_at`, `created_by`) VALUES
(1, 1, '2025-12-03 16:26:01', '2025-12-03 16:26:01', 'success', 'Completed successfully', NULL, 1, '2025-12-03 16:26:01', NULL),
(2, 1, '2025-12-03 16:26:48', '2025-12-03 16:26:48', 'success', 'Completed successfully', NULL, 1, '2025-12-03 16:26:48', NULL),
(3, 1, '2025-12-04 09:39:50', '2025-12-04 09:39:50', 'success', 'Completed successfully', NULL, 1, '2025-12-04 09:39:50', NULL),
(4, 1, '2025-12-04 09:46:44', '2025-12-04 09:46:44', 'success', 'Completed successfully', NULL, 1, '2025-12-04 09:46:44', NULL),
(5, 1, '2025-12-04 09:50:21', '2025-12-04 09:50:21', 'success', 'Completed successfully', NULL, 0, '2025-12-04 09:50:21', NULL),
(6, 1, '2025-12-04 09:50:48', '2025-12-04 09:50:48', 'success', 'Completed successfully', NULL, 0, '2025-12-04 09:50:48', NULL),
(7, 1, '2025-12-04 09:51:05', '2025-12-04 09:51:05', 'success', 'Completed successfully', NULL, 1, '2025-12-04 09:51:05', NULL),
(8, 1, '2025-12-04 09:51:09', '2025-12-04 09:51:09', 'success', 'Completed successfully', NULL, 1, '2025-12-04 09:51:09', NULL),
(9, 1, '2025-12-09 14:38:51', '2025-12-09 14:38:51', 'success', 'Completed successfully', NULL, 10, '2025-12-09 14:38:51', NULL),
(10, 1, '2025-12-09 14:57:28', '2025-12-09 14:57:28', 'success', 'Completed successfully', NULL, 8, '2025-12-09 14:57:28', NULL),
(11, 1, '2025-12-09 15:02:42', '2025-12-09 15:02:42', 'success', 'Completed successfully', NULL, 0, '2025-12-09 15:02:42', NULL),
(12, 1, '2025-12-09 15:05:38', '2025-12-09 15:05:38', 'success', 'Completed successfully', NULL, 9, '2025-12-09 15:05:38', NULL),
(13, 1, '2025-12-09 15:06:50', '2025-12-09 15:06:50', 'success', 'Completed successfully', NULL, 8, '2025-12-09 15:06:50', NULL),
(14, 1, '2025-12-09 15:08:39', '2025-12-09 15:08:39', 'success', 'Completed successfully', NULL, 0, '2025-12-09 15:08:39', NULL),
(15, 1, '2025-12-09 15:16:46', '2025-12-09 15:16:46', 'success', 'Completed successfully', NULL, 8, '2025-12-09 15:16:46', NULL),
(16, 1, '2025-12-09 15:17:06', '2025-12-09 15:17:06', 'success', 'Completed successfully', NULL, 8, '2025-12-09 15:17:06', NULL),
(17, 1, '2025-12-09 15:20:14', '2025-12-09 15:20:14', 'success', 'Completed successfully', NULL, 0, '2025-12-09 15:20:14', NULL),
(18, 1, '2025-12-09 15:24:24', '2025-12-09 15:24:24', 'success', 'Completed successfully', NULL, 1, '2025-12-09 15:24:24', NULL),
(19, 1, '2025-12-09 15:31:31', '2025-12-09 15:31:31', 'success', 'Completed successfully', NULL, 0, '2025-12-09 15:31:31', NULL),
(20, 1, '2025-12-09 15:32:17', '2025-12-09 15:32:17', 'success', 'Completed successfully', NULL, 0, '2025-12-09 15:32:17', NULL),
(21, 1, '2025-12-09 15:39:07', '2025-12-09 15:39:07', 'success', 'Completed successfully', NULL, 0, '2025-12-09 15:39:07', NULL),
(22, 1, '2025-12-09 15:42:02', '2025-12-09 15:42:02', 'success', 'Completed successfully', NULL, 0, '2025-12-09 15:42:02', NULL),
(23, 1, '2025-12-09 15:48:07', '2025-12-09 15:48:07', 'success', 'Completed successfully', NULL, 9, '2025-12-09 15:48:07', NULL),
(24, 1, '2025-12-20 09:44:34', '2025-12-20 09:44:34', 'success', 'Completed successfully', NULL, 6, '2025-12-20 09:44:34', NULL),
(25, 1, '2025-12-20 09:51:31', '2025-12-20 09:51:31', 'success', 'Completed successfully', NULL, 5, '2025-12-20 09:51:31', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cron_tasks`
--

CREATE TABLE `cron_tasks` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `module` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `class` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expression` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `last_run_at` datetime DEFAULT NULL,
  `next_run_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cron_tasks`
--

INSERT INTO `cron_tasks` (`id`, `name`, `module`, `class`, `expression`, `description`, `enabled`, `last_run_at`, `next_run_at`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(1, 'ProcessPendingSmsTask', 'SmsCore', 'Modules\\SmsCore\\Cron\\ProcessPendingSmsTask', '* * * * *', 'Process pending SMS messages in the queue.', 1, '2025-12-20 09:51:31', NULL, '2025-12-03 16:26:01', NULL, '2025-12-20 09:51:31', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `email_campaigns`
--

CREATE TABLE `email_campaigns` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `template_id` bigint DEFAULT NULL,
  `from_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reply_to` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','scheduled','sending','completed','paused','failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `total_recipients` int NOT NULL DEFAULT '0',
  `sent_count` int NOT NULL DEFAULT '0',
  `delivered_count` int NOT NULL DEFAULT '0',
  `opened_count` int NOT NULL DEFAULT '0',
  `clicked_count` int NOT NULL DEFAULT '0',
  `bounced_count` int NOT NULL DEFAULT '0',
  `unsubscribed_count` int NOT NULL DEFAULT '0',
  `failed_count` int NOT NULL DEFAULT '0',
  `total_cost` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `scheduled_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_by` bigint DEFAULT NULL,
  `contact_ids` json DEFAULT NULL,
  `segments` json DEFAULT NULL,
  `use_personalization` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `message_id` bigint NOT NULL,
  `event_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_data` json DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_messages`
--

CREATE TABLE `email_messages` (
  `id` bigint UNSIGNED NOT NULL,
  `campaign_id` bigint DEFAULT NULL,
  `user_id` bigint DEFAULT NULL,
  `to_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `to_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reply_to` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `html_content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `text_content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `message_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','sent','delivered','opened','clicked','failed','bounced') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `sent_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `opened_at` datetime DEFAULT NULL,
  `clicked_at` datetime DEFAULT NULL,
  `bounced_at` datetime DEFAULT NULL,
  `open_count` int NOT NULL DEFAULT '0',
  `click_count` int NOT NULL DEFAULT '0',
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `cost` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `html_content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `updated_by` int UNSIGNED DEFAULT NULL,
  `deleted_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint NOT NULL,
  `connection` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint NOT NULL,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  `created_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`, `created_by`) VALUES
(24, 'default', '{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":54}\",\"attempts\":0,\"created_at\":1766223874,\"available_at\":1766223874}', 0, NULL, 1766223874, 1766223874, NULL),
(25, 'default', '{\"job\":\"Modules\\\\SmsCore\\\\Jobs\\\\SendBulkSmsJob\",\"data\":\"{\\\"queueId\\\":54}\",\"attempts\":0,\"created_at\":1766224291,\"available_at\":1766224291}', 0, NULL, 1766224291, 1766224291, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` bigint UNSIGNED NOT NULL,
  `channel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `level_value` int NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `context` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `extra` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `remote_addr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_mode`
--

CREATE TABLE `maintenance_mode` (
  `id` int UNSIGNED NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Site en Maintenance',
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `background_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `background_color` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#4466f2',
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `allowed_ips` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'JSON array of allowed IPs',
  `allowed_roles` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'JSON array of allowed role IDs',
  `allowed_users` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'JSON array of allowed user IDs',
  `show_countdown` tinyint(1) DEFAULT '1',
  `retry_after` int DEFAULT '3600' COMMENT 'Seconds',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `maintenance_mode`
--

INSERT INTO `maintenance_mode` (`id`, `is_enabled`, `title`, `message`, `background_image`, `background_color`, `start_time`, `end_time`, `allowed_ips`, `allowed_roles`, `allowed_users`, `show_countdown`, `retry_after`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(1, 0, 'Site en Maintenance', 'Maintenance programmée', NULL, '#4466f2', NULL, '2025-12-10 06:00:00', '[\"192.168.253.228\",\"192.168.254.18\"]', '[1]', '[]', 1, 3600, '2025-12-04 14:00:53', NULL, '2025-12-04 14:00:53', 1);

-- --------------------------------------------------------

--
-- Table structure for table `mfa_methods`
--

CREATE TABLE `mfa_methods` (
  `id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_class` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mfa_methods`
--

INSERT INTO `mfa_methods` (`id`, `type`, `provider_class`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'totp', 'Modules\\Auth\\Providers\\TotpProvider', 1, '2025-12-04 23:01:20', '2025-12-04 23:01:20'),
(2, 'sms', 'Modules\\Auth\\Providers\\SmsOtpProvider', 1, '2025-12-04 23:01:20', '2025-12-04 23:01:20'),
(3, 'email', 'Modules\\Auth\\Providers\\EmailOtpProvider', 1, '2025-12-04 23:01:20', '2025-12-04 23:01:20');

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
(43, 'AddApiKeyToUsersTable', '2025-11-29 19:12:07'),
(44, 'CreatePasswordResetsTable', '2025-11-30 16:14:21'),
(45, '001_create_email_campaigns_table', '2025-12-03 11:30:19'),
(46, '002_create_email_messages_table', '2025-12-03 11:32:06'),
(47, '003_create_email_templates_table', '2025-12-03 11:32:06'),
(48, '004_create_email_logs_table', '2025-12-03 11:32:07'),
(49, '005_create_campaign_logs_table', '2025-12-03 11:32:07'),
(50, '006_create_workflows_table', '2025-12-03 11:32:07'),
(51, '007_create_workflow_executions_table', '2025-12-03 11:32:07'),
(52, '001_create_sender_names_table', '2025-12-03 16:37:45'),
(53, '002_create_user_sender_names_table', '2025-12-03 16:37:45'),
(54, '001_add_author_tracking_columns', '2025-12-04 07:46:58'),
(55, '005_add_sms_see_all_permission', '2025-12-15 12:54:44'),
(56, '20251215_add_created_by_to_users_table', '2025-12-15 12:57:07');

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1.0.0',
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `author` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_installed` tinyint(1) NOT NULL DEFAULT '1',
  `config` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `name`, `slug`, `version`, `icon`, `description`, `author`, `is_active`, `is_installed`, `config`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(1, 'Gestion des Utilisateurs', 'users-management', '1.0.0', 'users', 'Gestion des utilisateurs, profils et authentification', NULL, 1, 1, NULL, '2025-11-30 13:54:29', NULL, '2025-11-30 13:54:29', NULL),
(2, 'Rôles et Permissions', 'roles-permissions', '1.0.0', 'shield', 'Gestion des rôles, permissions et contrôle d\'accès', NULL, 1, 1, NULL, '2025-11-30 13:54:29', NULL, '2025-11-30 13:54:29', NULL),
(3, 'Admin', 'admin', '1.0.0', 'settings', 'Administration générale du système', NULL, 1, 1, NULL, '2025-11-30 13:54:29', NULL, '2025-11-30 15:32:01', NULL),
(4, 'Modules', 'modules', '1.0.0', 'package', 'Gestion des modules du système', NULL, 1, 1, NULL, '2025-11-30 13:54:29', NULL, '2025-11-30 13:54:29', NULL),
(5, 'Auth', 'auth', '1.0.0', NULL, 'Authentication Module', 'System', 1, 1, '{}', '2025-11-30 14:49:43', NULL, '2025-11-30 14:49:43', NULL),
(6, 'Settings', 'settings', '1.0.0', NULL, 'Settings Module', 'System', 1, 1, '{}', '2025-11-30 14:49:43', NULL, '2025-11-30 14:49:43', NULL),
(7, 'SmsCore', 'smscore', '1.0.0', NULL, 'SMS Core Module', 'System', 1, 1, '{}', '2025-11-30 14:49:43', NULL, '2025-11-30 14:49:43', NULL),
(8, 'Wallet', 'wallet', '1.0.0', NULL, 'Wallet Module', 'System', 1, 1, '{}', '2025-11-30 14:49:43', NULL, '2025-11-30 14:49:43', NULL),
(9, 'Queue', 'queue', '1.0.0', NULL, 'Queue Module', 'System', 1, 1, '{}', '2025-11-30 14:49:43', NULL, '2025-11-30 14:49:43', NULL),
(10, 'Cron', 'cron', '1.0.0', NULL, 'Cron Module', 'System', 1, 1, '{}', '2025-11-30 14:49:43', NULL, '2025-11-30 14:49:43', NULL),
(11, 'I18n', 'i18n', '1.0.0', NULL, 'I18n Module', 'System', 1, 1, '{}', '2025-11-30 14:49:43', NULL, '2025-11-30 14:49:43', NULL),
(12, 'RBAC', 'rbac', '1.0.0', NULL, 'RBAC Module', 'System', 1, 1, '{}', '2025-11-30 14:49:43', NULL, '2025-11-30 14:49:43', NULL),
(14, 'AI', 'ai', '1.0.0', NULL, 'Artificial Intelligence Module Integration', 'SunuFramework Team', 0, 1, '[]', '2025-11-30 17:46:18', NULL, '2025-12-11 14:19:02', NULL),
(15, 'Akpa', 'akpa', '1.0.0', NULL, 'Module de gestion de akpa - Module de test pour démontrer l\'installation via ZIP', 'SunuFramework Team', 0, 1, '[]', '2025-11-30 17:46:18', NULL, '2025-11-30 17:46:18', NULL),
(16, 'Backup', 'backup', '1.0.0', NULL, 'Module de sauvegarde, restauration et monitoring système', 'SunuFramework', 0, 1, '[]', '2025-11-30 17:46:18', NULL, '2025-11-30 17:46:18', NULL),
(17, 'Blog', 'blog', '1.0.0', NULL, 'Blog and Articles Management Module', 'SunuFramework Team', 0, 1, '[]', '2025-11-30 17:46:18', NULL, '2025-11-30 17:46:18', NULL),
(18, 'Demo', 'demo', '1.0.0', NULL, 'Demo Module for Testing', 'SunuFramework Team', 0, 1, '[]', '2025-11-30 17:46:18', NULL, '2025-11-30 17:46:18', NULL),
(19, 'Contacts', 'contacts', '1.0.0', NULL, 'Gestionnaire de contacts avec champs personnalisés', NULL, 1, 1, NULL, '2025-12-01 05:44:09', NULL, '2025-12-01 05:44:09', NULL),
(20, 'ApiKeys', 'apikeys', '1.0.0', NULL, 'Manage API keys for all system modules and endpoints', 'SunuFramework', 1, 1, '{\"key_length\": 48, \"key_prefix\": \"sk_live\", \"max_keys_per_user\": 10}', '2025-12-01 10:09:06', NULL, '2025-12-01 10:09:06', NULL),
(21, 'Users', 'users', '1.0.0', NULL, 'User Management Module', 'SunuFramework', 1, 1, '[]', '2025-12-02 09:20:32', NULL, '2025-12-02 09:20:32', NULL),
(22, 'Email Marketing', 'email-marketing', '1.0.0', 'mail', 'Module de marketing par email avec campagnes, templates, workflows et analytics', 'SunuFramework Team', 1, 1, NULL, '2025-12-03 11:21:51', NULL, '2025-12-03 11:21:51', NULL),
(23, 'EmailMarketing', 'emailmarketing', '1.0.0', NULL, 'Advanced Email Marketing Module with campaigns, templates, workflows, and analytics', 'SunuFramework Team', 1, 1, '[]', '2025-12-03 11:53:24', NULL, '2025-12-21 07:54:14', NULL),
(25, 'Dashboard', 'dashboard', '1.0.0', NULL, 'Tableau de bord principal du back-office.', 'VotreNom', 1, 1, '[]', '2025-12-14 15:45:20', NULL, '2025-12-14 15:46:10', NULL);

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
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `event_type`, `data`, `status`, `priority`, `scheduled_at`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(1, 'test_event', '{\"name\": \"Test User\", \"message\": \"System test notification\"}', 'pending', 5, NULL, '2025-11-26 12:17:46', NULL, '2025-11-26 12:17:46', NULL),
(2, 'test_event', '{\"name\": \"Test User\", \"message\": \"System test notification\"}', 'pending', 5, NULL, '2025-11-26 12:17:55', NULL, '2025-11-26 12:17:55', NULL),
(3, 'test_event', '{\"name\": \"Test User\", \"message\": \"System test notification\"}', 'pending', 5, NULL, '2025-11-26 12:18:02', NULL, '2025-11-26 12:18:02', NULL),
(4, 'test_event', '{\"name\": \"Test User\", \"message\": \"System test notification\"}', 'pending', 5, NULL, '2025-11-26 12:27:57', NULL, '2025-11-26 12:27:57', NULL),
(5, 'test_event', '{\"name\": \"Test User\", \"message\": \"System test notification\"}', 'pending', 5, NULL, '2025-11-26 12:29:17', NULL, '2025-11-26 12:29:17', NULL),
(6, 'test_event', '{\"name\": \"Test User\", \"message\": \"System test notification\"}', 'pending', 5, NULL, '2025-11-26 12:38:35', NULL, '2025-11-26 12:38:35', NULL),
(7, 'test_event', '{\"name\": \"Test User\", \"message\": \"System test notification\"}', 'pending', 5, NULL, '2025-11-26 12:42:45', NULL, '2025-11-26 12:42:45', NULL),
(8, 'test_event', '{\"name\": \"Test User\", \"message\": \"System test notification\"}', 'pending', 5, NULL, '2025-11-26 16:43:20', NULL, '2025-11-26 16:43:20', NULL);

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
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `notification_recipients`
--

INSERT INTO `notification_recipients` (`id`, `notification_id`, `user_id`, `channels`, `status`, `attempts`, `created_at`, `created_by`, `updated_at`) VALUES
(1, 1, 1, '[\"email\"]', 'pending', 0, '2025-11-26 12:17:46', NULL, '2025-11-26 12:17:46'),
(2, 2, 1, '[\"email\"]', 'pending', 0, '2025-11-26 12:17:55', NULL, '2025-11-26 12:17:55'),
(3, 3, 1, '[\"email\"]', 'pending', 0, '2025-11-26 12:18:02', NULL, '2025-11-26 12:18:02'),
(4, 4, 1, '[\"email\"]', 'pending', 0, '2025-11-26 12:27:57', NULL, '2025-11-26 12:27:57'),
(5, 5, 1, '[\"email\"]', 'pending', 0, '2025-11-26 12:29:17', NULL, '2025-11-26 12:29:17'),
(6, 6, 1, '[\"email\"]', 'pending', 0, '2025-11-26 12:38:35', NULL, '2025-11-26 12:38:35'),
(7, 7, 1, '[\"email\"]', 'pending', 0, '2025-11-26 12:42:45', NULL, '2025-11-26 12:42:45'),
(8, 8, 1, '[\"email\"]', 'pending', 0, '2025-11-26 16:43:20', NULL, '2025-11-26 16:43:20');

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
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `notification_templates`
--

INSERT INTO `notification_templates` (`id`, `name`, `channel`, `version`, `subject`, `body_html`, `body_text`, `body_sms`, `push_title`, `push_body`, `variables`, `active`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(1, 'test_email', 'email', 1, 'Test Email - {{ name }}', '<h1>Hello {{ name }}!</h1><p>{{ message }}</p>', 'Hello {{ name }}! {{ message }}', NULL, NULL, NULL, '[\"name\", \"message\"]', 1, '2025-11-26 12:17:46', NULL, '2025-11-26 12:17:46', NULL),
(3, 'backup_success', 'email', 1, 'Backup Successful: {{ filename }}', '<h1>Backup Successful</h1><p>Your backup <strong>{{ filename }}</strong> ({{ size }}) was created successfully on {{ date }}.</p>', 'Backup Successful\n\nYour backup {{ filename }} ({{ size }}) was created successfully on {{ date }}.', NULL, NULL, NULL, '[\"filename\", \"size\", \"date\", \"backup_id\"]', 1, '2025-11-26 22:39:01', NULL, '2025-11-26 22:39:01', NULL),
(8, 'backup_success_database', 'database', 1, 'Backup Successful', 'Backup {{ filename }} created successfully.', 'Backup {{ filename }} created successfully.', NULL, NULL, NULL, '[\"filename\", \"size\", \"date\", \"backup_id\"]', 1, '2025-11-26 22:52:03', NULL, '2025-11-26 22:52:03', NULL),
(9, 'backup_failed_email', 'email', 1, 'Backup Failed', '<h1>Backup Failed</h1><p>The backup process failed with the following error:</p><pre>{{ error }}</pre><p>Date: {{ date }}</p>', 'Backup Failed\n\nThe backup process failed with the following error:\n{{ error }}\nDate: {{ date }}', NULL, NULL, NULL, '[\"error\", \"date\", \"backup_id\"]', 1, '2025-11-26 22:52:03', NULL, '2025-11-26 22:52:03', NULL),
(10, 'backup_failed_database', 'database', 1, 'Backup Failed', 'Backup failed: {{ error }}', 'Backup failed: {{ error }}', NULL, NULL, NULL, '[\"error\", \"date\", \"backup_id\"]', 1, '2025-11-26 22:52:03', NULL, '2025-11-26 22:52:03', NULL),
(11, 'wallet_topup_request_submitted', 'email', 1, 'Demande de rechargement wallet soumise', '<!DOCTYPE html>\n<html>\n<head>\n    <meta charset=\"UTF-8\">\n    <style>\n        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }\n        .container { max-width: 600px; margin: 0 auto; padding: 20px; }\n        .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; }\n        .content { background-color: #f9f9f9; padding: 30px; }\n        .info-box { background-color: #e3f2fd; border-left: 4px solid #2196F3; padding: 15px; margin: 20px 0; }\n        .amount { font-size: 24px; font-weight: bold; color: #4CAF50; }\n        .footer { text-align: center; padding: 20px; color: #777; font-size: 12px; }\n        .button { background-color: #4CAF50; color: white; padding: 12px 30px; text-decoration: none; display: inline-block; border-radius: 5px; }\n    </style>\n</head>\n<body>\n    <div class=\"container\">\n        <div class=\"header\">\n            <h1>Demande de Rechargement Soumise</h1>\n        </div>\n        <div class=\"content\">\n            <p>Bonjour <strong>{{user_name}}</strong>,</p>\n\n            <p>Votre demande de rechargement wallet a bien ├®t├® soumise avec succ├¿s.</p>\n\n            <div class=\"info-box\">\n                <p><strong>D├®tails de la demande :</strong></p>\n                <ul>\n                    <li><strong>N┬░ de demande :</strong> #{{request_id}}</li>\n                    <li><strong>Montant :</strong> <span class=\"amount\">{{amount}} XOF</span></li>\n                    <li><strong>M├®thode de paiement :</strong> {{payment_method}}</li>\n                    <li><strong>Statut :</strong> En attente de validation</li>\n                    <li><strong>Date :</strong> {{created_at}}</li>\n                </ul>\n            </div>\n\n            <p><strong>Prochaines ├®tapes :</strong></p>\n            <p>Votre demande est actuellement en attente de validation par notre ├®quipe. Vous recevrez une notification par email d├¿s que votre demande sera trait├®e.</p>\n\n            <p>Pour les paiements offline (Mobile Money, Cash, Virement), assurez-vous d\'avoir effectu├® le paiement et conserv├® votre preuve de transaction.</p>\n\n            <p style=\"text-align: center; margin-top: 30px;\">\n                <a href=\"{{view_url}}\" class=\"button\">Voir ma demande</a>\n            </p>\n        </div>\n        <div class=\"footer\">\n            <p>Cet email a ├®t├® envoy├® automatiquement, merci de ne pas y r├®pondre.</p>\n            <p>&copy; {{year}} {{site_name}}. Tous droits r├®serv├®s.</p>\n        </div>\n    </div>\n</body>\n</html>', 'Bonjour {{user_name}},\n\nVotre demande de rechargement wallet a bien ├®t├® soumise avec succ├¿s.\n\nD├®tails de la demande :\n- N┬░ de demande : #{{request_id}}\n- Montant : {{amount}} XOF\n- M├®thode de paiement : {{payment_method}}\n- Statut : En attente de validation\n- Date : {{created_at}}\n\nProchaines ├®tapes :\nVotre demande est actuellement en attente de validation par notre ├®quipe. Vous recevrez une notification par email d├¿s que votre demande sera trait├®e.\n\nPour les paiements offline (Mobile Money, Cash, Virement), assurez-vous d\'avoir effectu├® le paiement et conserv├® votre preuve de transaction.\n\nVoir ma demande : {{view_url}}\n\nCet email a ├®t├® envoy├® automatiquement, merci de ne pas y r├®pondre.\n┬® {{year}} {{site_name}}. Tous droits r├®serv├®s.', NULL, NULL, NULL, '[\"user_name\", \"request_id\", \"amount\", \"payment_method\", \"created_at\", \"view_url\", \"site_name\", \"year\"]', 1, '2025-12-11 06:02:32', NULL, '2025-12-11 06:02:32', NULL),
(12, 'wallet_topup_request_approved', 'email', 1, 'Demande de rechargement approuv├®e - Wallet cr├®dit├®', '<!DOCTYPE html>\n<html>\n<head>\n    <meta charset=\"UTF-8\">\n    <style>\n        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }\n        .container { max-width: 600px; margin: 0 auto; padding: 20px; }\n        .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; }\n        .content { background-color: #f9f9f9; padding: 30px; }\n        .success-box { background-color: #e8f5e9; border-left: 4px solid #4CAF50; padding: 20px; margin: 20px 0; text-align: center; }\n        .amount { font-size: 32px; font-weight: bold; color: #4CAF50; }\n        .info-box { background-color: #fff; border: 1px solid #ddd; padding: 15px; margin: 20px 0; }\n        .footer { text-align: center; padding: 20px; color: #777; font-size: 12px; }\n        .button { background-color: #4CAF50; color: white; padding: 12px 30px; text-decoration: none; display: inline-block; border-radius: 5px; }\n        .check-icon { font-size: 48px; color: #4CAF50; }\n    </style>\n</head>\n<body>\n    <div class=\"container\">\n        <div class=\"header\">\n            <h1>Ô£ô Demande Approuv├®e</h1>\n        </div>\n        <div class=\"content\">\n            <p>Bonjour <strong>{{user_name}}</strong>,</p>\n\n            <p>Excellente nouvelle ! Votre demande de rechargement wallet a ├®t├® approuv├®e.</p>\n\n            <div class=\"success-box\">\n                <div class=\"check-icon\">Ô£ô</div>\n                <h2>Wallet Cr├®dit├®</h2>\n                <p class=\"amount\">+ {{amount}} XOF</p>\n                <p>Votre nouveau solde : <strong>{{new_balance}} XOF</strong></p>\n            </div>\n\n            <div class=\"info-box\">\n                <p><strong>D├®tails de la demande :</strong></p>\n                <ul>\n                    <li><strong>N┬░ de demande :</strong> #{{request_id}}</li>\n                    <li><strong>Montant cr├®dit├® :</strong> {{amount}} XOF</li>\n                    <li><strong>M├®thode de paiement :</strong> {{payment_method}}</li>\n                    <li><strong>Date de soumission :</strong> {{created_at}}</li>\n                    <li><strong>Date d\'approbation :</strong> {{approved_at}}</li>\n                    <li><strong>Approuv├® par :</strong> {{reviewed_by}}</li>\n                </ul>\n\n                {{#admin_notes}}\n                <p><strong>Notes de l\'administrateur :</strong></p>\n                <p style=\"background-color: #f5f5f5; padding: 10px; border-radius: 5px;\">{{admin_notes}}</p>\n                {{/admin_notes}}\n            </div>\n\n            <p>Vous pouvez maintenant utiliser votre solde pour envoyer des SMS, effectuer des transactions, ou utiliser nos autres services.</p>\n\n            <p style=\"text-align: center; margin-top: 30px;\">\n                <a href=\"{{wallet_url}}\" class=\"button\">Voir mon wallet</a>\n            </p>\n        </div>\n        <div class=\"footer\">\n            <p>Cet email a ├®t├® envoy├® automatiquement, merci de ne pas y r├®pondre.</p>\n            <p>&copy; {{year}} {{site_name}}. Tous droits r├®serv├®s.</p>\n        </div>\n    </div>\n</body>\n</html>', 'Bonjour {{user_name}},\n\nExcellente nouvelle ! Votre demande de rechargement wallet a ├®t├® approuv├®e.\n\nÔ£ô WALLET CR├ëDIT├ë\n+ {{amount}} XOF\nVotre nouveau solde : {{new_balance}} XOF\n\nD├®tails de la demande :\n- N┬░ de demande : #{{request_id}}\n- Montant cr├®dit├® : {{amount}} XOF\n- M├®thode de paiement : {{payment_method}}\n- Date de soumission : {{created_at}}\n- Date d\'approbation : {{approved_at}}\n- Approuv├® par : {{reviewed_by}}\n\n{{#admin_notes}}\nNotes de l\'administrateur :\n{{admin_notes}}\n{{/admin_notes}}\n\nVous pouvez maintenant utiliser votre solde pour envoyer des SMS, effectuer des transactions, ou utiliser nos autres services.\n\nVoir mon wallet : {{wallet_url}}\n\nCet email a ├®t├® envoy├® automatiquement, merci de ne pas y r├®pondre.\n┬® {{year}} {{site_name}}. Tous droits r├®serv├®s.', NULL, NULL, NULL, '[\"user_name\", \"request_id\", \"amount\", \"new_balance\", \"payment_method\", \"created_at\", \"approved_at\", \"reviewed_by\", \"admin_notes\", \"wallet_url\", \"site_name\", \"year\"]', 1, '2025-12-11 06:02:32', NULL, '2025-12-11 06:02:32', NULL),
(13, 'wallet_topup_request_rejected', 'email', 1, 'Demande de rechargement rejet├®e', '<!DOCTYPE html>\n<html>\n<head>\n    <meta charset=\"UTF-8\">\n    <style>\n        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }\n        .container { max-width: 600px; margin: 0 auto; padding: 20px; }\n        .header { background-color: #f44336; color: white; padding: 20px; text-align: center; }\n        .content { background-color: #f9f9f9; padding: 30px; }\n        .warning-box { background-color: #fff3e0; border-left: 4px solid #ff9800; padding: 20px; margin: 20px 0; }\n        .reason-box { background-color: #ffebee; border: 1px solid #f44336; padding: 15px; margin: 20px 0; border-radius: 5px; }\n        .info-box { background-color: #fff; border: 1px solid #ddd; padding: 15px; margin: 20px 0; }\n        .footer { text-align: center; padding: 20px; color: #777; font-size: 12px; }\n        .button { background-color: #2196F3; color: white; padding: 12px 30px; text-decoration: none; display: inline-block; border-radius: 5px; }\n        .x-icon { font-size: 48px; color: #f44336; }\n    </style>\n</head>\n<body>\n    <div class=\"container\">\n        <div class=\"header\">\n            <h1>Demande Rejet├®e</h1>\n        </div>\n        <div class=\"content\">\n            <p>Bonjour <strong>{{user_name}}</strong>,</p>\n\n            <p>Nous regrettons de vous informer que votre demande de rechargement wallet #{{request_id}} n\'a pas pu ├¬tre approuv├®e.</p>\n\n            <div class=\"reason-box\">\n                <p><strong>Ô£ù Raison du rejet :</strong></p>\n                <p>{{admin_notes}}</p>\n            </div>\n\n            <div class=\"info-box\">\n                <p><strong>D├®tails de la demande :</strong></p>\n                <ul>\n                    <li><strong>N┬░ de demande :</strong> #{{request_id}}</li>\n                    <li><strong>Montant :</strong> {{amount}} XOF</li>\n                    <li><strong>M├®thode de paiement :</strong> {{payment_method}}</li>\n                    <li><strong>Date de soumission :</strong> {{created_at}}</li>\n                    <li><strong>Date de rejet :</strong> {{rejected_at}}</li>\n                    <li><strong>Rejet├® par :</strong> {{reviewed_by}}</li>\n                </ul>\n            </div>\n\n            <div class=\"warning-box\">\n                <p><strong>Que faire maintenant ?</strong></p>\n                <ul>\n                    <li>V├®rifiez la raison du rejet ci-dessus</li>\n                    <li>Assurez-vous que votre preuve de paiement est valide et lisible</li>\n                    <li>V├®rifiez que les informations de transaction sont correctes</li>\n                    <li>Vous pouvez soumettre une nouvelle demande avec les informations corrig├®es</li>\n                </ul>\n            </div>\n\n            <p>Si vous avez des questions ou si vous pensez qu\'il s\'agit d\'une erreur, n\'h├®sitez pas ├á contacter notre support.</p>\n\n            <p style=\"text-align: center; margin-top: 30px;\">\n                <a href=\"{{support_url}}\" class=\"button\">Contacter le Support</a>\n                <a href=\"{{new_request_url}}\" class=\"button\" style=\"background-color: #4CAF50; margin-left: 10px;\">Nouvelle Demande</a>\n            </p>\n        </div>\n        <div class=\"footer\">\n            <p>Cet email a ├®t├® envoy├® automatiquement, merci de ne pas y r├®pondre.</p>\n            <p>&copy; {{year}} {{site_name}}. Tous droits r├®serv├®s.</p>\n        </div>\n    </div>\n</body>\n</html>', 'Bonjour {{user_name}},\n\nNous regrettons de vous informer que votre demande de rechargement wallet #{{request_id}} n\'a pas pu ├¬tre approuv├®e.\n\nÔ£ù RAISON DU REJET :\n{{admin_notes}}\n\nD├®tails de la demande :\n- N┬░ de demande : #{{request_id}}\n- Montant : {{amount}} XOF\n- M├®thode de paiement : {{payment_method}}\n- Date de soumission : {{created_at}}\n- Date de rejet : {{rejected_at}}\n- Rejet├® par : {{reviewed_by}}\n\nQue faire maintenant ?\n- V├®rifiez la raison du rejet ci-dessus\n- Assurez-vous que votre preuve de paiement est valide et lisible\n- V├®rifiez que les informations de transaction sont correctes\n- Vous pouvez soumettre une nouvelle demande avec les informations corrig├®es\n\nSi vous avez des questions ou si vous pensez qu\'il s\'agit d\'une erreur, n\'h├®sitez pas ├á contacter notre support.\n\nContacter le Support : {{support_url}}\nNouvelle Demande : {{new_request_url}}\n\nCet email a ├®t├® envoy├® automatiquement, merci de ne pas y r├®pondre.\n┬® {{year}} {{site_name}}. Tous droits r├®serv├®s.', NULL, NULL, NULL, '[\"user_name\", \"request_id\", \"amount\", \"payment_method\", \"created_at\", \"rejected_at\", \"reviewed_by\", \"admin_notes\", \"support_url\", \"new_request_url\", \"site_name\", \"year\"]', 1, '2025-12-11 06:02:32', NULL, '2025-12-11 06:02:32', NULL),
(14, 'wallet_topup_request_admin_notification', 'email', 1, '[ADMIN] Nouvelle demande de rechargement en attente', '<!DOCTYPE html>\n<html>\n<head>\n    <meta charset=\"UTF-8\">\n    <style>\n        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }\n        .container { max-width: 600px; margin: 0 auto; padding: 20px; }\n        .header { background-color: #ff9800; color: white; padding: 20px; text-align: center; }\n        .content { background-color: #f9f9f9; padding: 30px; }\n        .alert-box { background-color: #fff3e0; border-left: 4px solid #ff9800; padding: 20px; margin: 20px 0; }\n        .info-box { background-color: #fff; border: 1px solid #ddd; padding: 15px; margin: 20px 0; }\n        .amount { font-size: 24px; font-weight: bold; color: #ff9800; }\n        .footer { text-align: center; padding: 20px; color: #777; font-size: 12px; }\n        .button { background-color: #4CAF50; color: white; padding: 12px 30px; text-decoration: none; display: inline-block; border-radius: 5px; margin: 5px; }\n        .button-reject { background-color: #f44336; }\n    </style>\n</head>\n<body>\n    <div class=\"container\">\n        <div class=\"header\">\n            <h1>ÔÜá Nouvelle Demande en Attente</h1>\n        </div>\n        <div class=\"content\">\n            <p>Bonjour <strong>Admin</strong>,</p>\n\n            <div class=\"alert-box\">\n                <p><strong>Une nouvelle demande de rechargement wallet n├®cessite votre validation.</strong></p>\n            </div>\n\n            <div class=\"info-box\">\n                <p><strong>Informations utilisateur :</strong></p>\n                <ul>\n                    <li><strong>Nom :</strong> {{user_name}}</li>\n                    <li><strong>Email :</strong> {{user_email}}</li>\n                    <li><strong>ID :</strong> {{user_id}}</li>\n                </ul>\n\n                <p><strong>D├®tails de la demande :</strong></p>\n                <ul>\n                    <li><strong>N┬░ de demande :</strong> #{{request_id}}</li>\n                    <li><strong>Montant :</strong> <span class=\"amount\">{{amount}} XOF</span></li>\n                    <li><strong>M├®thode de paiement :</strong> {{payment_method}}</li>\n                    <li><strong>Date de soumission :</strong> {{created_at}}</li>\n                    <li><strong>IP utilisateur :</strong> {{ip_address}}</li>\n                </ul>\n\n                {{#user_notes}}\n                <p><strong>Notes de l\'utilisateur :</strong></p>\n                <p style=\"background-color: #f5f5f5; padding: 10px; border-radius: 5px;\">{{user_notes}}</p>\n                {{/user_notes}}\n            </div>\n\n            <p><strong>Actions requises :</strong></p>\n            <ol>\n                <li>V├®rifier la validit├® du paiement</li>\n                <li>Consulter la preuve de paiement (si fournie)</li>\n                <li>Approuver ou rejeter la demande</li>\n            </ol>\n\n            <p style=\"text-align: center; margin-top: 30px;\">\n                <a href=\"{{admin_review_url}}\" class=\"button\">G├®rer les Demandes</a>\n            </p>\n        </div>\n        <div class=\"footer\">\n            <p>Cet email a ├®t├® envoy├® automatiquement aux administrateurs.</p>\n            <p>&copy; {{year}} {{site_name}}. Tous droits r├®serv├®s.</p>\n        </div>\n    </div>\n</body>\n</html>', 'Bonjour Admin,\n\nÔÜá NOUVELLE DEMANDE EN ATTENTE\n\nUne nouvelle demande de rechargement wallet n├®cessite votre validation.\n\nInformations utilisateur :\n- Nom : {{user_name}}\n- Email : {{user_email}}\n- ID : {{user_id}}\n\nD├®tails de la demande :\n- N┬░ de demande : #{{request_id}}\n- Montant : {{amount}} XOF\n- M├®thode de paiement : {{payment_method}}\n- Date de soumission : {{created_at}}\n- IP utilisateur : {{ip_address}}\n\n{{#user_notes}}\nNotes de l\'utilisateur :\n{{user_notes}}\n{{/user_notes}}\n\nActions requises :\n1. V├®rifier la validit├® du paiement\n2. Consulter la preuve de paiement (si fournie)\n3. Approuver ou rejeter la demande\n\nG├®rer les Demandes : {{admin_review_url}}\n\nCet email a ├®t├® envoy├® automatiquement aux administrateurs.\n┬® {{year}} {{site_name}}. Tous droits r├®serv├®s.', NULL, NULL, NULL, '[\"user_name\", \"user_email\", \"user_id\", \"request_id\", \"amount\", \"payment_method\", \"created_at\", \"ip_address\", \"user_notes\", \"admin_review_url\", \"site_name\", \"year\"]', 1, '2025-12-11 06:02:32', NULL, '2025-12-11 06:02:32', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `oauth_accounts`
--

CREATE TABLE `oauth_accounts` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `provider` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `refresh_token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` bigint UNSIGNED NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `module_slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `module` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `module_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `module_slug`, `name`, `slug`, `module`, `description`, `module_id`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(1, 'akpa', 'View Users', 'viewusers', NULL, 'Can view users', 3, '2025-11-30 12:16:00', NULL, '2025-11-30 12:16:00', NULL),
(2, NULL, 'Create Users', 'create.users', NULL, 'Can create users', NULL, '2025-11-30 12:16:00', NULL, '2025-11-30 12:16:00', NULL),
(3, NULL, 'View Posts', 'view.posts', NULL, 'Can view posts', NULL, '2025-11-30 12:16:00', NULL, '2025-11-30 12:16:00', NULL),
(4, NULL, 'Create Posts', 'create.posts', NULL, 'Can create posts', NULL, '2025-11-30 12:16:00', NULL, '2025-11-30 12:16:00', NULL),
(5, NULL, 'Access Admin', 'access.admin', NULL, 'Can access admin panel', NULL, '2025-11-30 12:16:00', NULL, '2025-11-30 12:16:00', NULL),
(6, NULL, 'posts.edit', 'postsedit', NULL, 'add Post', 18, '2025-11-30 17:53:36', NULL, '2025-11-30 17:53:36', NULL),
(7, NULL, 'Admin Access', 'admin.access', 'Admin', 'Access Admin Dashboard', NULL, '2025-12-01 16:08:02', NULL, '2025-12-01 16:08:02', NULL),
(8, NULL, 'Admin Users View', 'admin.users.view', 'Admin', 'View Users', NULL, '2025-12-01 16:08:02', NULL, '2025-12-01 16:08:02', NULL),
(9, NULL, 'Admin Users Create', 'admin.users.create', 'Admin', 'Create Users', NULL, '2025-12-01 16:08:02', NULL, '2025-12-01 16:08:02', NULL),
(10, NULL, 'Admin Users Edit', 'admin.users.edit', 'Admin', 'Edit Users', NULL, '2025-12-01 16:08:02', NULL, '2025-12-01 16:08:02', NULL),
(11, NULL, 'Admin Users Delete', 'admin.users.delete', 'Admin', 'Delete Users', NULL, '2025-12-01 16:08:02', NULL, '2025-12-01 16:08:02', NULL),
(12, NULL, 'Admin Roles View', 'admin.roles.view', 'Admin', 'View Roles', NULL, '2025-12-01 16:08:02', NULL, '2025-12-01 16:08:02', NULL),
(13, NULL, 'Admin Roles Create', 'admin.roles.create', 'Admin', 'Create Roles', NULL, '2025-12-01 16:08:02', NULL, '2025-12-01 16:08:02', NULL),
(14, NULL, 'Admin Roles Edit', 'admin.roles.edit', 'Admin', 'Edit Roles', NULL, '2025-12-01 16:08:02', NULL, '2025-12-01 16:08:02', NULL),
(15, NULL, 'Admin Roles Delete', 'admin.roles.delete', 'Admin', 'Delete Roles', NULL, '2025-12-01 16:08:02', NULL, '2025-12-01 16:08:02', NULL),
(16, NULL, 'Admin Permissions View', 'admin.permissions.view', 'Admin', 'View Permissions', NULL, '2025-12-01 16:08:02', NULL, '2025-12-01 16:08:02', NULL),
(17, NULL, 'Admin Modules View', 'admin.modules.view', 'Admin', 'View Modules', NULL, '2025-12-01 16:08:02', NULL, '2025-12-01 16:08:02', NULL),
(18, NULL, 'Admin Modules Manage', 'admin.modules.manage', 'Admin', 'Manage Modules', NULL, '2025-12-01 16:08:02', NULL, '2025-12-01 16:08:02', NULL),
(19, NULL, 'Admin Settings View', 'admin.settings.view', 'Admin', 'View Settings', NULL, '2025-12-01 16:08:02', NULL, '2025-12-01 16:08:02', NULL),
(20, NULL, 'Admin Settings Edit', 'admin.settings.edit', 'Admin', 'Edit Settings', NULL, '2025-12-01 16:08:02', NULL, '2025-12-01 16:08:02', NULL),
(21, NULL, 'Auth Profile View', 'auth.profile.view', 'Auth', 'View Own Profile', NULL, '2025-12-01 16:08:02', NULL, '2025-12-01 16:08:02', NULL),
(22, NULL, 'Auth Profile Edit', 'auth.profile.edit', 'Auth', 'Edit Own Profile', NULL, '2025-12-01 16:08:02', NULL, '2025-12-01 16:08:02', NULL),
(23, NULL, 'Backup View', 'backup.view', 'Backup', 'View Backups', NULL, '2025-12-01 16:08:02', NULL, '2025-12-01 16:08:02', NULL),
(24, NULL, 'Backup Create', 'backup.create', 'Backup', 'Create Backup', NULL, '2025-12-01 16:08:02', NULL, '2025-12-01 16:08:02', NULL),
(25, NULL, 'Backup Download', 'backup.download', 'Backup', 'Download Backup', NULL, '2025-12-01 16:08:02', NULL, '2025-12-01 16:08:02', NULL),
(26, NULL, 'Backup Delete', 'backup.delete', 'Backup', 'Delete Backup', NULL, '2025-12-01 16:08:02', NULL, '2025-12-01 16:08:02', NULL),
(27, NULL, 'Backup Restore', 'backup.restore', 'Backup', 'Restore Backup', NULL, '2025-12-01 16:08:02', NULL, '2025-12-01 16:08:02', NULL),
(28, NULL, 'Settings View', 'settings.view', 'Settings', 'View System Settings', NULL, '2025-12-01 16:08:02', NULL, '2025-12-01 16:08:02', NULL),
(29, NULL, 'Settings Edit', 'settings.edit', 'Settings', 'Edit System Settings', NULL, '2025-12-01 16:08:02', NULL, '2025-12-01 16:08:02', NULL),
(30, NULL, 'Settings Sms Manage', 'settings.sms.manage', 'Settings', 'Manage SMS Gateways', NULL, '2025-12-01 16:08:02', NULL, '2025-12-01 16:08:02', NULL),
(32, NULL, 'Apikeys View', 'apikeys.view', 'ApiKeys', 'Voir les clés API', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(33, NULL, 'Apikeys Create', 'apikeys.create', 'ApiKeys', 'Créer une clé API', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(34, NULL, 'Apikeys Revoke', 'apikeys.revoke', 'ApiKeys', 'Révoquer une clé API', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(35, NULL, 'Apikeys Logs View', 'apikeys.logs.view', 'ApiKeys', 'Voir les logs API', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(36, NULL, 'Apikeys Manage', 'apikeys.manage', 'ApiKeys', 'Gérer toutes les clés API', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(37, NULL, 'Contacts View', 'contacts.view', 'Contacts', 'Voir les contacts', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(38, NULL, 'Contacts Create', 'contacts.create', 'Contacts', 'Créer un contact', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(39, NULL, 'Contacts Edit', 'contacts.edit', 'Contacts', 'Modifier un contact', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(40, NULL, 'Contacts Delete', 'contacts.delete', 'Contacts', 'Supprimer un contact', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(41, NULL, 'Contacts Export', 'contacts.export', 'Contacts', 'Exporter les contacts', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(42, NULL, 'Contacts Import', 'contacts.import', 'Contacts', 'Importer des contacts', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(43, NULL, 'Contacts Fields Manage', 'contacts.fields.manage', 'Contacts', 'Gérer les champs personnalisés', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(44, NULL, 'Contacts Groups Manage', 'contacts.groups.manage', 'Contacts', 'Gérer les groupes de contacts', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(45, NULL, 'Cron View', 'cron.view', 'Cron', 'Voir les tâches planifiées', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(46, NULL, 'Cron Manage', 'cron.manage', 'Cron', 'Gérer les tâches planifiées', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(47, NULL, 'Cron Execute', 'cron.execute', 'Cron', 'Exécuter manuellement une tâche', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(48, NULL, 'I18n View', 'i18n.view', 'I18n', 'Voir les traductions', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(49, NULL, 'I18n Edit', 'i18n.edit', 'I18n', 'Modifier les traductions', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(50, NULL, 'I18n Languages Manage', 'i18n.languages.manage', 'I18n', 'Gérer les langues', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(51, NULL, 'Notifications View', 'notifications.view', 'Notifications', 'Voir les notifications', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(52, NULL, 'Notifications Send', 'notifications.send', 'Notifications', 'Envoyer des notifications', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(53, NULL, 'Notifications Templates Manage', 'notifications.templates.manage', 'Notifications', 'Gérer les modèles de notifications', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(54, NULL, 'Notifications Settings Manage', 'notifications.settings.manage', 'Notifications', 'Gérer les paramètres de notifications', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(55, NULL, 'Notifications Delete', 'notifications.delete', 'Notifications', 'Supprimer des notifications', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(56, NULL, 'Queue View', 'queue.view', 'Queue', 'Voir les files d\'attente', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(57, NULL, 'Queue Manage', 'queue.manage', 'Queue', 'Gérer les files d\'attente', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(58, NULL, 'Queue Retry', 'queue.retry', 'Queue', 'Relancer les jobs échoués', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(59, NULL, 'Queue Delete', 'queue.delete', 'Queue', 'Supprimer des jobs', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(60, NULL, 'Sms Send', 'sms.send', 'SmsCore', 'Envoyer des SMS', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(61, NULL, 'Sms History View', 'sms.history.view', 'SmsCore', 'Voir l\'historique des SMS', NULL, '2025-12-01 16:24:09', NULL, '2025-12-01 16:24:09', NULL),
(62, NULL, 'Sms Gateways Manage', 'sms.gateways.manage', 'SmsCore', 'Gérer les passerelles SMS', NULL, '2025-12-01 16:24:10', NULL, '2025-12-01 16:24:10', NULL),
(63, NULL, 'Sms Templates Manage', 'sms.templates.manage', 'SmsCore', 'Gérer les modèles de SMS', NULL, '2025-12-01 16:24:10', NULL, '2025-12-01 16:24:10', NULL),
(64, NULL, 'Sms Stats View', 'sms.stats.view', 'SmsCore', 'Voir les statistiques SMS', NULL, '2025-12-01 16:24:10', NULL, '2025-12-01 16:24:10', NULL),
(71, NULL, 'Files Upload', 'files.upload', 'Admin', 'Upload Files', NULL, '2025-12-02 08:04:23', NULL, '2025-12-02 08:04:23', NULL),
(72, NULL, 'Files List', 'files.list', 'Admin', 'List Files', NULL, '2025-12-02 08:04:23', NULL, '2025-12-02 08:04:23', NULL),
(73, NULL, 'Files Delete', 'files.delete', 'Admin', 'Delete Files', NULL, '2025-12-02 08:04:23', NULL, '2025-12-02 08:04:23', NULL),
(74, NULL, 'Email Marketing View', 'email_marketing.view', 'EmailMarketing', 'View email marketing dashboard', NULL, '2025-12-03 10:58:53', NULL, '2025-12-03 10:58:53', NULL),
(75, NULL, 'Email Marketing Manage', 'email_marketing.manage', 'EmailMarketing', 'Manage email marketing settings', NULL, '2025-12-03 10:58:53', NULL, '2025-12-03 10:58:53', NULL),
(76, NULL, 'Email Marketing Campaigns View', 'email_marketing.campaigns.view', 'EmailMarketing', 'View email campaigns', NULL, '2025-12-03 10:58:53', NULL, '2025-12-03 10:58:53', NULL),
(77, NULL, 'Email Marketing Campaigns Create', 'email_marketing.campaigns.create', 'EmailMarketing', 'Create email campaigns', NULL, '2025-12-03 10:58:53', NULL, '2025-12-03 10:58:53', NULL),
(78, NULL, 'Email Marketing Campaigns Edit', 'email_marketing.campaigns.edit', 'EmailMarketing', 'Edit email campaigns', NULL, '2025-12-03 10:58:53', NULL, '2025-12-03 10:58:53', NULL),
(79, NULL, 'Email Marketing Campaigns Delete', 'email_marketing.campaigns.delete', 'EmailMarketing', 'Delete email campaigns', NULL, '2025-12-03 10:58:53', NULL, '2025-12-03 10:58:53', NULL),
(80, NULL, 'Email Marketing Campaigns Send', 'email_marketing.campaigns.send', 'EmailMarketing', 'Send email campaigns', NULL, '2025-12-03 10:58:53', NULL, '2025-12-03 10:58:53', NULL),
(81, NULL, 'Email Marketing Templates View', 'email_marketing.templates.view', 'EmailMarketing', 'View email templates', NULL, '2025-12-03 10:58:53', NULL, '2025-12-03 10:58:53', NULL),
(82, NULL, 'Email Marketing Templates Create', 'email_marketing.templates.create', 'EmailMarketing', 'Create email templates', NULL, '2025-12-03 10:58:53', NULL, '2025-12-03 10:58:53', NULL),
(83, NULL, 'Email Marketing Templates Edit', 'email_marketing.templates.edit', 'EmailMarketing', 'Edit email templates', NULL, '2025-12-03 10:58:53', NULL, '2025-12-03 10:58:53', NULL),
(84, NULL, 'Email Marketing Templates Delete', 'email_marketing.templates.delete', 'EmailMarketing', 'Delete email templates', NULL, '2025-12-03 10:58:53', NULL, '2025-12-03 10:58:53', NULL),
(85, NULL, 'Email Marketing Workflows View', 'email_marketing.workflows.view', 'EmailMarketing', 'View workflows', NULL, '2025-12-03 10:58:53', NULL, '2025-12-03 10:58:53', NULL),
(86, NULL, 'Email Marketing Workflows Create', 'email_marketing.workflows.create', 'EmailMarketing', 'Create workflows', NULL, '2025-12-03 10:58:53', NULL, '2025-12-03 10:58:53', NULL),
(87, NULL, 'Email Marketing Workflows Edit', 'email_marketing.workflows.edit', 'EmailMarketing', 'Edit workflows', NULL, '2025-12-03 10:58:53', NULL, '2025-12-03 10:58:53', NULL),
(88, NULL, 'Email Marketing Workflows Delete', 'email_marketing.workflows.delete', 'EmailMarketing', 'Delete workflows', NULL, '2025-12-03 10:58:53', NULL, '2025-12-03 10:58:53', NULL),
(89, NULL, 'Email Marketing Workflows Execute', 'email_marketing.workflows.execute', 'EmailMarketing', 'Execute workflows', NULL, '2025-12-03 10:58:53', NULL, '2025-12-03 10:58:53', NULL),
(90, NULL, 'Email Marketing Analytics View', 'email_marketing.analytics.view', 'EmailMarketing', 'View email analytics', NULL, '2025-12-03 10:58:53', NULL, '2025-12-03 10:58:53', NULL),
(91, NULL, 'Email Marketing Analytics Export', 'email_marketing.analytics.export', 'EmailMarketing', 'Export analytics data', NULL, '2025-12-03 10:58:53', NULL, '2025-12-03 10:58:53', NULL),
(92, NULL, 'Email Marketing Api Send', 'email_marketing.api.send', 'EmailMarketing', 'Send emails via API', NULL, '2025-12-03 10:58:53', NULL, '2025-12-03 10:58:53', NULL),
(93, NULL, 'Email Marketing Api Bulk', 'email_marketing.api.bulk', 'EmailMarketing', 'Send bulk emails via API', NULL, '2025-12-03 10:58:53', NULL, '2025-12-03 10:58:53', NULL),
(94, 'sms-core', 'sms.dashboard.view', 'sms-dashboard-view', 'SMS', 'Voir le tableau de bord SMS', NULL, '2025-12-12 05:39:41', NULL, '2025-12-12 05:39:41', NULL),
(95, 'sms-core', 'sms.send', 'sms-send', 'SMS', 'Envoyer des SMS', NULL, '2025-12-12 05:39:41', NULL, '2025-12-12 05:39:41', NULL),
(96, 'sms-core', 'sms.send.view', 'sms-send-view', 'SMS', 'Voir la page d\'envoi SMS', NULL, '2025-12-12 05:39:41', NULL, '2025-12-12 05:39:41', NULL),
(97, 'sms-core', 'sms.bulk', 'sms-bulk', 'SMS', 'Envoyer des SMS en masse', NULL, '2025-12-12 05:39:41', NULL, '2025-12-12 05:39:41', NULL),
(98, 'sms-core', 'sms.bulk.view', 'sms-bulk-view', 'SMS', 'Voir la page d\'envoi en masse', NULL, '2025-12-12 05:39:41', NULL, '2025-12-12 05:39:41', NULL),
(99, 'sms-core', 'sms.campaigns.view', 'sms-campaigns-view', 'SMS', 'Voir les campagnes SMS', NULL, '2025-12-12 05:39:41', NULL, '2025-12-12 05:39:41', NULL),
(100, 'sms-core', 'sms.campaigns.create', 'sms-campaigns-create', 'SMS', 'Cr├®er des campagnes SMS', NULL, '2025-12-12 05:39:41', NULL, '2025-12-12 05:39:41', NULL),
(101, 'sms-core', 'sms.campaigns.edit', 'sms-campaigns-edit', 'SMS', 'Modifier les campagnes SMS', NULL, '2025-12-12 05:39:41', NULL, '2025-12-12 05:39:41', NULL),
(102, 'sms-core', 'sms.campaigns.delete', 'sms-campaigns-delete', 'SMS', 'Supprimer les campagnes SMS', NULL, '2025-12-12 05:39:41', NULL, '2025-12-12 05:39:41', NULL),
(103, 'sms-core', 'sms.history.view', 'sms-history-view', 'SMS', 'Voir l\'historique des SMS', NULL, '2025-12-12 05:39:41', NULL, '2025-12-12 05:39:41', NULL),
(104, 'sms-core', 'sms.history.view_all', 'sms-history-view-all', 'SMS', 'Voir l\'historique de tous les utilisateurs', NULL, '2025-12-12 05:39:41', NULL, '2025-12-12 05:39:41', NULL),
(105, 'sms-core', 'sms.statistics.view', 'sms-statistics-view', 'SMS', 'Voir les statistiques SMS', NULL, '2025-12-12 05:39:41', NULL, '2025-12-12 05:39:41', NULL),
(106, 'sms-core', 'sms.statistics.view_all', 'sms-statistics-view-all', 'SMS', 'Voir les statistiques de tous les utilisateurs', NULL, '2025-12-12 05:39:41', NULL, '2025-12-12 05:39:41', NULL),
(107, 'sms-core', 'sms.sender_names.view', 'sms-sender-names-view', 'SMS', 'Voir les noms d\'exp├®diteur', NULL, '2025-12-12 05:39:41', NULL, '2025-12-12 05:39:41', NULL),
(108, 'sms-core', 'sms.sender_names.manage', 'sms-sender-names-manage', 'SMS', 'G├®rer les noms d\'exp├®diteur', NULL, '2025-12-12 05:39:41', NULL, '2025-12-12 05:39:41', NULL),
(109, 'sms-core', 'sms.sender_names.assign', 'sms-sender-names-assign', 'SMS', 'Assigner les noms d\'exp├®diteur aux utilisateurs', NULL, '2025-12-12 05:39:41', NULL, '2025-12-12 05:39:41', NULL),
(110, 'sms-core', 'sms.pricing.view', 'sms-pricing-view', 'SMS', 'Voir les tarifs SMS', NULL, '2025-12-12 05:39:41', NULL, '2025-12-12 05:39:41', NULL),
(111, 'sms-core', 'sms.pricing.manage', 'sms-pricing-manage', 'SMS', 'G├®rer les tarifs SMS', NULL, '2025-12-12 05:39:41', NULL, '2025-12-12 05:39:41', NULL),
(112, 'sms-core', 'sms.billing.view', 'sms-billing-view', 'SMS', 'Voir la facturation SMS', NULL, '2025-12-12 05:39:41', NULL, '2025-12-12 05:39:41', NULL),
(113, 'sms-core', 'sms.billing.view_all', 'sms-billing-view-all', 'SMS', 'Voir la facturation de tous les utilisateurs', NULL, '2025-12-12 05:39:41', NULL, '2025-12-12 05:39:41', NULL),
(114, 'sms-core', 'sms.providers.view', 'sms-providers-view', 'SMS', 'Voir les statistiques des fournisseurs', NULL, '2025-12-12 05:39:41', NULL, '2025-12-12 05:39:41', NULL),
(115, 'sms-core', 'sms.api.docs', 'sms-api-docs', 'SMS', 'Voir la documentation API SMS', NULL, '2025-12-12 05:39:41', NULL, '2025-12-12 05:39:41', NULL),
(116, 'sms-core', 'sms.api.keys.view', 'sms-api-keys-view', 'SMS', 'Voir ses cl├®s API', NULL, '2025-12-12 05:39:41', NULL, '2025-12-12 05:39:41', NULL),
(117, 'sms-core', 'sms.api.keys.manage', 'sms-api-keys-manage', 'SMS', 'G├®rer ses cl├®s API', NULL, '2025-12-12 05:39:41', NULL, '2025-12-12 05:39:41', NULL),
(118, 'a_i', 'Access A I', 'access.a_i', NULL, 'Accès au module AI', NULL, '2025-12-13 00:31:05', NULL, '2025-12-13 00:31:05', NULL),
(119, 'akpa', 'Access Akpa', 'access.akpa', NULL, 'Accès au module Akpa', NULL, '2025-12-13 00:31:05', NULL, '2025-12-13 00:31:05', NULL),
(120, 'api_keys', 'Access Api Keys', 'access.api_keys', NULL, 'Accès au module ApiKeys', NULL, '2025-12-13 00:31:05', NULL, '2025-12-13 00:31:05', NULL),
(121, 'auth', 'Access Auth', 'access.auth', NULL, 'Accès au module Auth', NULL, '2025-12-13 00:31:05', NULL, '2025-12-13 00:31:05', NULL),
(122, 'backup', 'Access Backup', 'access.backup', NULL, 'Accès au module Backup', NULL, '2025-12-13 00:31:05', NULL, '2025-12-13 00:31:05', NULL),
(123, 'blog', 'Access Blog', 'access.blog', NULL, 'Accès au module Blog', NULL, '2025-12-13 00:31:05', NULL, '2025-12-13 00:31:05', NULL),
(124, 'contacts', 'Access Contacts', 'access.contacts', NULL, 'Accès au module Contacts', NULL, '2025-12-13 00:31:05', NULL, '2025-12-13 00:31:05', NULL),
(125, 'demo', 'Access Demo', 'access.demo', NULL, 'Accès au module Demo', NULL, '2025-12-13 00:31:05', NULL, '2025-12-13 00:31:05', NULL),
(126, 'email_marketing', 'Access Email Marketing', 'access.email_marketing', NULL, 'Accès au module EmailMarketing', NULL, '2025-12-13 00:31:05', NULL, '2025-12-13 00:31:05', NULL),
(127, 'i18n', 'Access I18n', 'access.i18n', NULL, 'Accès au module I18n', NULL, '2025-12-13 00:31:05', NULL, '2025-12-13 00:31:05', NULL),
(128, 'r_b_a_c', 'Access R B A C', 'access.r_b_a_c', NULL, 'Accès au module RBAC', NULL, '2025-12-13 00:31:05', NULL, '2025-12-13 00:31:05', NULL),
(129, 'settings', 'Access Settings', 'access.settings', NULL, 'Accès au module Settings', NULL, '2025-12-13 00:31:05', NULL, '2025-12-13 00:31:05', NULL),
(130, 'sms_core', 'Access Sms Core', 'access.sms_core', NULL, 'Accès au module SmsCore', NULL, '2025-12-13 00:31:05', NULL, '2025-12-13 00:31:05', NULL),
(131, 'users', 'Access Users', 'access.users', NULL, 'Accès au module Users', NULL, '2025-12-13 00:31:05', NULL, '2025-12-13 00:31:05', NULL),
(132, 'wallet', 'Access Wallet', 'access.wallet', NULL, 'Accès au module Wallet', NULL, '2025-12-13 00:31:05', NULL, '2025-12-13 00:31:05', NULL),
(133, 'ai', 'Access Ai', 'access.ai', NULL, 'Accès au module AI', NULL, '2025-12-13 00:31:11', NULL, '2025-12-13 00:31:11', NULL),
(134, 'rbac', 'Access Rbac', 'access.rbac', NULL, 'Accès au module RBAC', NULL, '2025-12-13 00:31:11', NULL, '2025-12-13 00:31:11', NULL),
(135, 'wallet', 'Wallet Dashboard', 'wallet.dashboard', NULL, 'Accès au dashboard wallet avec statistiques', NULL, '2025-12-14 07:26:54', NULL, '2025-12-14 11:01:28', NULL),
(136, 'wallet', 'Manage Wallets', 'wallet.manage', NULL, 'Gérer la liste des wallets', NULL, '2025-12-14 07:26:54', NULL, '2025-12-14 11:01:28', NULL),
(137, 'wallet', 'Topup Wallet', 'wallet.topup', NULL, 'Recharger son propre wallet', NULL, '2025-12-14 07:26:54', NULL, '2025-12-14 11:01:28', NULL),
(138, 'wallet', 'View Wallet Requests', 'wallet.requests.view', NULL, 'Voir ses demandes de recharge', NULL, '2025-12-14 07:26:54', NULL, '2025-12-14 11:01:28', NULL),
(139, 'wallet', 'Manage Wallet Requests', 'wallet.requests.manage', NULL, 'Gérer les demandes de recharge (Admin)', NULL, '2025-12-14 07:26:54', NULL, '2025-12-14 11:01:28', NULL),
(162, NULL, 'Settings Wallet Manage', 'settings.wallet.manage', 'Settings', 'Manage Wallet Gateways', NULL, '2025-12-14 11:03:01', 1, '2025-12-14 11:03:01', 1),
(163, NULL, 'Wallet View', 'wallet.view', 'Wallet', 'Voir les portefeuilles', NULL, '2025-12-14 11:03:01', 1, '2025-12-14 11:03:01', 1),
(164, NULL, 'Wallet Create', 'wallet.create', 'Wallet', 'Créer un portefeuille', NULL, '2025-12-14 11:03:01', 1, '2025-12-14 11:03:01', 1),
(165, NULL, 'Wallet Debit', 'wallet.debit', 'Wallet', 'Débiter un portefeuille', NULL, '2025-12-14 11:03:01', 1, '2025-12-14 11:03:01', 1),
(166, NULL, 'Wallet Credit', 'wallet.credit', 'Wallet', 'Créditer un portefeuille', NULL, '2025-12-14 11:03:01', 1, '2025-12-14 11:03:01', 1),
(167, NULL, 'Wallet History View', 'wallet.history.view', 'Wallet', 'Voir l\'historique des transactions', NULL, '2025-12-14 11:03:01', 1, '2025-12-14 11:03:01', 1),
(168, NULL, 'Wallet Settings Manage', 'wallet.settings.manage', 'Wallet', 'Gérer les paramètres de portefeuille', NULL, '2025-12-14 11:03:01', 1, '2025-12-14 11:03:01', 1),
(169, 'sms_core', 'Voir tous les SMS (Dashboard)', 'sms.see_all', NULL, 'Permet de voir tous les SMS sur le dashboard, même ceux des autres utilisateurs.', NULL, '2025-12-14 11:56:58', NULL, '2025-12-14 11:56:58', NULL),
(170, NULL, 'Access Dashboard', 'access.dashboard', 'Dashboard', 'Permet d\'accéder au module Dashboard', NULL, '2025-12-14 14:33:21', 1, '2025-12-14 14:33:21', 1),
(171, NULL, 'Manage Widgets', 'manage.widgets', 'Dashboard', 'Gérer l\'activation/désactivation des widgets du dashboard', NULL, '2025-12-15 13:11:01', 1, '2025-12-15 13:11:01', 1),
(172, NULL, 'Admin Auth Settings View', 'admin.auth.settings.view', 'Auth', 'Voir les paramètres Auth', NULL, '2025-12-20 12:52:41', 1, '2025-12-20 12:52:41', 1),
(173, NULL, 'Admin Auth Settings Edit', 'admin.auth.settings.edit', 'Auth', 'Modifier les paramètres Auth', NULL, '2025-12-20 12:52:41', 1, '2025-12-20 12:52:41', 1),
(174, NULL, 'Admin Auth Settings Add', 'admin.auth.settings.add', 'Auth', 'Ajouter un paramètre Auth', NULL, '2025-12-20 12:52:41', 1, '2025-12-20 12:52:41', 1);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `updated_by` int UNSIGNED DEFAULT NULL,
  `deleted_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `created_at`, `created_by`, `updated_at`, `deleted_at`, `updated_by`, `deleted_by`) VALUES
(1, 'Administrateur', 'admin', 'Full system access', '2025-11-30 12:16:00', NULL, '2025-11-30 12:16:00', NULL, 1, NULL),
(2, 'Manager', 'manager', 'Can manage users and content', '2025-11-30 12:16:00', NULL, '2025-11-30 12:16:00', NULL, NULL, NULL),
(3, 'Éditeur', 'editor', 'Can edit and publish content', '2025-11-30 12:16:00', NULL, '2025-11-30 12:16:00', NULL, NULL, NULL),
(4, 'Rédacteur', 'writer', 'Can create content', '2025-11-30 12:16:00', NULL, '2025-11-30 12:16:00', NULL, NULL, NULL),
(5, 'Utilisateur', 'user', 'Basic user access', '2025-11-30 12:16:00', NULL, '2025-11-30 12:16:00', NULL, 1, NULL);

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
(3, 21, '2025-12-01 16:37:58', '2025-12-01 16:37:58'),
(3, 22, '2025-12-01 16:37:58', '2025-12-01 16:37:58'),
(3, 37, '2025-12-01 16:37:58', '2025-12-01 16:37:58'),
(3, 38, '2025-12-01 16:37:58', '2025-12-01 16:37:58'),
(3, 39, '2025-12-01 16:37:58', '2025-12-01 16:37:58'),
(3, 51, '2025-12-01 16:37:58', '2025-12-01 16:37:58'),
(3, 52, '2025-12-01 16:37:58', '2025-12-01 16:37:58'),
(3, 60, '2025-12-01 16:37:58', '2025-12-01 16:37:58'),
(3, 61, '2025-12-01 16:37:58', '2025-12-01 16:37:58'),
(2, 94, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(2, 95, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(2, 96, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(2, 97, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(2, 98, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(2, 99, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(2, 100, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(2, 101, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(2, 103, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(2, 104, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(2, 105, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(2, 106, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(2, 107, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(2, 112, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(2, 115, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(2, 116, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(2, 117, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(3, 94, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(3, 95, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(3, 96, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(3, 97, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(3, 98, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(3, 99, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(3, 103, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(3, 105, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(3, 107, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(3, 112, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(3, 115, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(3, 116, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(3, 117, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(4, 94, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(4, 103, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(4, 105, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(4, 115, '2025-12-12 05:43:15', '2025-12-12 05:43:15'),
(2, 5, '2025-12-13 10:17:44', '2025-12-13 10:17:44'),
(2, 120, '2025-12-13 10:17:44', '2025-12-13 10:17:44'),
(2, 121, '2025-12-13 10:17:44', '2025-12-13 10:17:44'),
(2, 124, '2025-12-13 10:17:44', '2025-12-13 10:17:44'),
(2, 127, '2025-12-13 10:17:44', '2025-12-13 10:17:44'),
(2, 129, '2025-12-13 10:17:44', '2025-12-13 10:17:44'),
(2, 130, '2025-12-13 10:17:44', '2025-12-13 10:17:44'),
(2, 131, '2025-12-13 10:17:44', '2025-12-13 10:17:44'),
(2, 132, '2025-12-13 10:17:44', '2025-12-13 10:17:44'),
(5, 33, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 34, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 32, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 22, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 21, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 38, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 40, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 39, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 41, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 43, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 44, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 42, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 37, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 170, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 51, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 115, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 117, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 116, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 112, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 97, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 98, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 100, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 102, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 101, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 99, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 94, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 103, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 95, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 96, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 105, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 120, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 121, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 130, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 132, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 137, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(5, 138, '2025-12-14 23:09:34', '2025-12-14 23:09:34'),
(1, 7, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 18, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 17, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 16, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 13, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 15, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 14, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 12, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 20, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 19, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 9, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 11, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 10, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 8, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 73, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 72, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 71, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 1, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 33, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 35, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 36, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 34, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 32, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 174, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 173, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 172, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 22, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 21, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 24, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 26, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 25, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 27, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 23, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 38, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 40, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 39, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 41, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 43, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 44, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 42, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 37, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 47, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 46, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 45, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 170, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 171, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 6, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 91, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 90, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 93, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 92, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 77, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 79, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 78, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 80, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 76, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 75, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 82, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 84, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 83, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 81, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 74, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 86, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 88, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 87, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 89, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 85, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 49, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 50, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 48, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 55, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 52, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 54, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 53, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 51, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 59, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 57, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 58, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 56, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 115, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 117, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 116, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 112, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 113, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 97, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 98, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 100, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 102, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 101, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 99, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 94, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 103, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 104, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 111, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 110, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 114, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 95, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 96, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 109, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 108, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 107, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 105, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 106, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 118, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 5, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 133, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 119, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 120, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 121, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 122, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 123, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 124, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 125, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 126, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 127, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 128, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 134, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 129, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 130, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 131, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 132, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 4, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 2, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 139, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 136, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 137, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 3, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 138, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 169, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 135, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 29, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 30, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 28, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 162, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 62, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 61, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 60, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 64, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 63, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 164, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 166, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 165, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 167, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 168, '2025-12-20 13:20:04', '2025-12-20 13:20:04'),
(1, 163, '2025-12-20 13:20:04', '2025-12-20 13:20:04');

-- --------------------------------------------------------

--
-- Table structure for table `sender_names`
--

CREATE TABLE `sender_names` (
  `id` int NOT NULL,
  `name` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `operator` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Operator that validated this sender name',
  `status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `validation_date` date DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Admin notes about this sender name',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `updated_by` int UNSIGNED DEFAULT NULL,
  `deleted_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sender_names`
--

INSERT INTO `sender_names` (`id`, `name`, `operator`, `status`, `is_active`, `validation_date`, `notes`, `created_by`, `created_at`, `updated_at`, `deleted_at`, `updated_by`, `deleted_by`) VALUES
(1, 'SMS 483497', 'Orange CI', 'approved', 0, '2035-12-01', '', NULL, '2025-12-03 16:37:57', '2025-12-15 10:53:45', '2025-12-15 10:53:45', 1, 1),
(2, 'AKADI', 'Orange CI', 'approved', 1, '2035-12-01', '', NULL, '2025-12-03 16:37:57', '2025-12-03 17:29:25', NULL, 1, NULL),
(3, 'BtySuccess', 'Orange CI', 'approved', 1, '2035-11-15', NULL, NULL, '2025-12-03 16:37:57', '2025-12-03 17:29:30', NULL, NULL, NULL),
(4, 'CARREFOUR', 'Orange CI', 'approved', 1, '2035-11-15', '', NULL, '2025-12-03 16:37:57', '2025-12-03 17:29:33', NULL, 1, NULL),
(5, 'FOANI', 'Orange CI', 'approved', 1, '2025-12-03', '', 1, '2025-12-03 17:30:00', '2025-12-03 17:30:00', NULL, NULL, NULL),
(7, 'MEIWAY LIVE', 'Orange CI', 'approved', 1, '2025-12-05', '', 1, '2025-12-05 13:47:35', '2025-12-05 13:47:35', NULL, 1, NULL),
(8, 'TICAFRIQUE', 'Orange CI', 'approved', 1, '2025-12-15', '', 1, '2025-12-15 10:22:19', '2025-12-15 10:22:19', NULL, 1, NULL),
(9, 'VIVO ENERGY', 'Orange CI', 'approved', 1, '2025-12-15', '', 1, '2025-12-15 10:54:36', '2025-12-15 10:54:36', NULL, 1, NULL),
(10, 'SURFIN OPTQ', 'Orange CI', 'approved', 1, '2025-12-15', '', 1, '2025-12-15 10:55:30', '2025-12-15 10:55:30', NULL, 1, NULL),
(11, 'UIPA', 'Orange CI', 'approved', 1, '2025-12-15', '', 1, '2025-12-15 10:55:51', '2025-12-15 10:55:51', NULL, 1, NULL),
(12, 'SUPECO', 'Orange CI', 'approved', 1, '2025-12-15', '', 1, '2025-12-15 10:56:11', '2025-12-15 10:56:11', NULL, 1, NULL),
(13, 'PLAYCE', 'Orange CI', 'approved', 1, '2025-12-15', '', 1, '2025-12-15 10:56:30', '2025-12-15 10:56:30', NULL, 1, NULL),
(14, 'FONTAINE G', 'Orange CI', 'approved', 1, '2025-12-15', '', 1, '2025-12-15 10:57:05', '2025-12-15 10:57:05', NULL, 1, NULL),
(15, 'GRANDERECRE', 'Orange CI', 'approved', 1, '2025-12-15', '', 1, '2025-12-15 10:57:28', '2025-12-15 10:59:39', NULL, 1, NULL),
(16, 'SIGMA P', 'Orange CI', 'approved', 1, '2025-12-15', '', 1, '2025-12-15 11:00:19', '2025-12-15 11:00:19', NULL, 1, NULL),
(17, 'KELYSTOURS', 'Orange CI', 'approved', 1, '2025-12-15', '', 1, '2025-12-15 11:01:02', '2025-12-15 11:01:02', NULL, 1, NULL),
(18, 'UNIABIDJAN', 'Orange CI', 'approved', 1, '2025-12-15', '', 1, '2025-12-15 11:01:32', '2025-12-15 11:01:32', NULL, 1, NULL),
(19, 'VISEO', 'Orange CI', 'approved', 1, '2025-12-15', '', 1, '2025-12-15 11:02:37', '2025-12-15 11:02:37', NULL, 1, NULL),
(20, 'JULES', 'Orange CI', 'approved', 1, '2025-12-15', '', 1, '2025-12-15 11:10:08', '2025-12-15 11:10:08', NULL, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint UNSIGNED NOT NULL,
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `type` enum('string','integer','float','boolean','json','array') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `setting_group` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `type`, `setting_group`, `description`, `is_public`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(1, 'theme_mode', 'light', 'string', 'theme', NULL, 0, '2025-11-27 16:06:50', NULL, '2025-12-04 14:05:58', NULL),
(2, 'openai_api_key', '', 'string', 'api', NULL, 0, '2025-11-27 16:07:00', NULL, '2025-12-01 09:40:48', NULL),
(3, 'wallet.currency', 'XOF', 'string', 'wallet', NULL, 0, '2025-11-29 17:42:11', NULL, '2025-11-29 18:18:23', NULL),
(4, 'wallet.min_topup', '1000', 'integer', 'wallet', NULL, 0, '2025-11-29 17:42:43', NULL, '2025-11-29 18:18:23', NULL),
(5, 'sms_pricing_grid', '{\"CI\":{\"name\":\"C\\u00f4te d\'Ivoire\",\"default\":35,\"networks\":{\"orange\":35,\"mtn\":20,\"moov\":15}},\"default\":{\"price\":35,\"currency\":\"XOF\"}}', 'json', 'sms_pricing', NULL, 0, '2025-11-29 18:59:15', NULL, '2025-11-29 19:00:20', NULL),
(6, 'google_api_key', '', 'string', 'api', NULL, 0, '2025-12-01 09:40:48', NULL, '2025-12-01 09:40:48', NULL),
(7, 'stripe_api_key', '', 'string', 'api', NULL, 0, '2025-12-01 09:40:48', NULL, '2025-12-01 09:40:48', NULL),
(8, 'paypal_client_id', '', 'string', 'api', NULL, 0, '2025-12-01 09:40:48', NULL, '2025-12-01 09:40:48', NULL),
(9, 'sms_api_key', '', 'string', 'api', NULL, 0, '2025-12-01 09:40:48', NULL, '2025-12-01 09:40:48', NULL),
(10, 'map_api_key', '', 'string', 'api', NULL, 0, '2025-12-01 09:40:48', NULL, '2025-12-01 09:40:48', NULL),
(11, 'sms_default_country_code', '+225', 'string', 'sms', 'Code pays par d├®faut pour les num├®ros SMS (ex: +225 pour CI)', 0, '2025-12-04 08:52:55', NULL, '2025-12-04 08:52:55', NULL),
(12, 'sms_auto_add_prefix', '1', 'boolean', 'sms', 'Ajouter automatiquement le pr├®fixe pays aux num├®ros', 0, '2025-12-04 08:52:55', NULL, '2025-12-04 08:52:55', NULL),
(13, 'sms_supported_countries', '{\"CI\":\"+225\",\"SN\":\"+221\",\"ML\":\"+223\",\"BF\":\"+226\",\"TG\":\"+228\",\"NE\":\"+227\",\"BJ\":\"+229\"}', 'json', 'sms', 'Liste des pays support├®s avec leurs codes', 0, '2025-12-04 08:52:55', NULL, '2025-12-04 08:52:55', NULL),
(14, 'sms_queue_threshold', '100', 'integer', 'sms', 'Nombre de SMS pour d├®clencher automatiquement la mise en queue', 0, '2025-12-04 13:04:08', NULL, '2025-12-04 13:04:08', NULL),
(15, 'sms_queue_mode', 'auto', 'string', 'sms', 'Mode de queue: auto (automatique selon seuil), always (toujours), never (jamais)', 0, '2025-12-04 13:04:08', NULL, '2025-12-04 13:04:08', NULL),
(16, 'sms_queue_delay', '1', 'integer', 'sms', 'D├®lai en secondes entre chaque SMS lors du traitement de la queue', 0, '2025-12-04 13:04:08', NULL, '2025-12-04 13:04:08', NULL),
(17, 'sms_queue_batch_size', '10', 'integer', 'sms', 'Nombre de SMS ├á traiter par lot lors de l\'ex├®cution cron', 0, '2025-12-04 13:04:08', NULL, '2025-12-04 13:04:08', NULL),
(18, 'primary_color', '#7366ff', 'string', 'theme', NULL, 0, '2025-12-04 14:05:58', 1, '2025-12-04 14:05:58', 1),
(19, 'secondary_color', '#838383', 'string', 'theme', NULL, 0, '2025-12-04 14:05:58', 1, '2025-12-04 14:05:58', 1),
(20, 'success_color', '#65c15c', 'string', 'theme', NULL, 0, '2025-12-04 14:05:58', 1, '2025-12-04 14:05:58', 1),
(21, 'sidebar_type', 'expanded', 'string', 'theme', NULL, 0, '2025-12-04 14:05:58', 1, '2025-12-04 14:05:58', 1),
(22, 'sidebar_icon', 'stroke-svg', 'string', 'theme', NULL, 0, '2025-12-04 14:05:58', 1, '2025-12-04 14:05:58', 1),
(23, 'layout_type', 'ltr', 'string', 'theme', NULL, 0, '2025-12-04 14:05:58', 1, '2025-12-04 14:05:58', 1),
(24, 'font_family', 'Rubik', 'string', 'theme', NULL, 0, '2025-12-04 14:05:58', 1, '2025-12-04 14:05:58', 1),
(25, 'font_size', '14px', 'string', 'theme', NULL, 0, '2025-12-04 14:05:58', 1, '2025-12-04 14:05:58', 1),
(26, 'auth_sms_sender_id', 'SIGMA P', 'string', 'auth', NULL, 0, '2025-12-05 11:02:38', 1, '2025-12-15 11:30:14', 1),
(27, 'auth_sms_gateway_id', 'orange_ci', 'string', 'auth', NULL, 0, '2025-12-05 11:02:38', 1, '2025-12-15 11:30:14', 1),
(28, 'site_name', 'SunuFramework', 'string', 'site', NULL, 0, '2025-12-08 06:03:23', 1, '2025-12-08 06:03:23', 1),
(29, 'site_description', '', 'string', 'site', NULL, 0, '2025-12-08 06:03:23', 1, '2025-12-08 06:03:23', 1),
(30, 'site_author', '', 'string', 'site', NULL, 0, '2025-12-08 06:03:23', 1, '2025-12-08 06:03:23', 1),
(31, 'default_language', 'fr', 'string', 'site', NULL, 0, '2025-12-08 06:03:23', 1, '2025-12-08 06:03:23', 1),
(32, 'default_timezone', 'Africa/Dakar', 'string', 'site', NULL, 0, '2025-12-08 06:03:23', 1, '2025-12-08 06:03:23', 1),
(33, 'date_format', 'Y-m-d', 'string', 'site', NULL, 0, '2025-12-08 06:03:23', 1, '2025-12-08 06:03:23', 1),
(34, 'time_format', 'H:i', 'string', 'site', NULL, 0, '2025-12-08 06:03:23', 1, '2025-12-08 06:03:23', 1),
(35, 'items_per_page', '20', 'integer', 'site', NULL, 0, '2025-12-08 06:03:23', 1, '2025-12-08 06:03:23', 1),
(36, 'maintenance_mode', '0', 'boolean', 'site', NULL, 0, '2025-12-08 06:03:23', 1, '2025-12-08 06:03:23', 1),
(37, 'registration_enabled', '1', 'boolean', 'site', NULL, 0, '2025-12-08 06:03:23', 1, '2025-12-08 06:03:23', 1);

-- --------------------------------------------------------

--
-- Table structure for table `sms_billing_logs`
--

CREATE TABLE `sms_billing_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `sender_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_code` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operator` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gateway` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sms_type` enum('text','otp','marketing') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'text',
  `segments` int DEFAULT '1',
  `unit_cost` decimal(10,4) DEFAULT '0.0000',
  `total_cost` decimal(10,4) DEFAULT '0.0000',
  `currency` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'XOF',
  `status` enum('pending','paid','failed','refunded') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sms_billing_logs`
--

INSERT INTO `sms_billing_logs` (`id`, `user_id`, `sender_id`, `recipient`, `country_code`, `operator`, `gateway`, `sms_type`, `segments`, `unit_cost`, `total_cost`, `currency`, `status`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(1, 1, 'SMS', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '15.0000', '15.0000', 'XOF', 'paid', NULL, NULL, NULL, NULL),
(2, 1, 'SMS', '+225', 'CI', 'unknown', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, NULL, NULL, NULL),
(3, 1, 'TestAPI', '+225', 'CI', 'unknown', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, NULL, NULL, NULL),
(4, 1, 'TestAPI', '+225', 'CI', 'unknown', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, NULL, NULL, NULL),
(5, 1, 'TICAFRIQUE', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, NULL, NULL, NULL),
(6, 1, 'TICAFRIQUE', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, NULL, NULL, NULL),
(7, 1, 'TICAFRIQUE', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, NULL, NULL, NULL),
(8, 1, 'TICAFRIQUE', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, NULL, NULL, NULL),
(9, 1, 'TICAFRIQUE', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, NULL, NULL, NULL),
(10, 1, 'TICAFRIQUE', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, NULL, NULL, NULL),
(11, 1, 'TICAFRIQUE', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, NULL, NULL, NULL),
(12, 1, 'TICAFRIQUE', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, NULL, NULL, NULL),
(13, 1, 'SMS 483497', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'failed', NULL, NULL, NULL, NULL),
(14, 1, 'TICAFRIQUE', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'failed', NULL, NULL, NULL, NULL),
(15, 1, '0778599242', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'failed', NULL, NULL, NULL, NULL),
(16, 1, 'TICAFRIQUE', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, NULL, NULL, NULL),
(17, 1, 'SMS 483497', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, NULL, NULL, NULL),
(18, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, NULL, NULL, NULL),
(19, 1, 'AKADI', '+2250778599242', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'failed', NULL, NULL, NULL, NULL),
(20, 1, 'AKADI', '+2250778599242', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'failed', NULL, 1, NULL, 1),
(21, 1, 'AKADI', '+2250778599242', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'failed', NULL, 1, NULL, 1),
(22, 1, 'AKADI', '+2250778599242', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'failed', NULL, 1, NULL, 1),
(23, 1, 'AKADI', '+2250778599242', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, 1, NULL, 1),
(24, 1, 'AKADI', '+2250505569256', 'CI', 'mtn', 'orange_ci', 'text', 1, '20.0000', '20.0000', 'XOF', 'paid', NULL, 1, NULL, 1),
(25, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, 1, NULL, 1),
(26, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, 1, NULL, 1),
(27, 1, 'MEIWAY LIVE', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'failed', NULL, 1, NULL, 1),
(28, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, 1, NULL, 1),
(29, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, 1, NULL, 1),
(30, 1, 'SMS', '+2250749270077', 'CI', 'orange', 'unknown', 'text', 1, '35.0000', '35.0000', 'XOF', 'failed', NULL, 1, NULL, 1),
(31, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'unknown', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, 1, NULL, 1),
(32, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'unknown', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, NULL, NULL, NULL),
(33, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'unknown', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, NULL, NULL, NULL),
(34, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'unknown', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, NULL, NULL, NULL),
(35, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'unknown', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, NULL, NULL, NULL),
(36, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'unknown', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, NULL, NULL, NULL),
(37, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'unknown', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, NULL, NULL, NULL),
(38, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'unknown', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, NULL, NULL, NULL),
(39, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'unknown', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, NULL, NULL, NULL),
(40, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'unknown', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, NULL, NULL, NULL),
(41, 1, 'AKADI', '+2250505569256', 'CI', 'mtn', 'orange_ci', 'text', 1, '20.0000', '20.0000', 'XOF', 'paid', NULL, NULL, NULL, NULL),
(42, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, 1, NULL, 1),
(43, 1, 'AKADI', '+2250505569256', 'CI', 'mtn', 'orange_ci', 'text', 1, '20.0000', '20.0000', 'XOF', 'paid', NULL, 1, NULL, 1),
(44, 1, 'AKADI', '+2250549270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, 1, NULL, 1),
(45, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, 1, NULL, 1),
(46, 1, 'AKADI', '+2250709876543', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, NULL, NULL, NULL),
(47, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, NULL, NULL, NULL),
(48, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, 1, NULL, 1),
(49, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, 1, NULL, 1),
(50, 1, 'AKADI', '+2250505569256', 'CI', 'mtn', 'orange_ci', 'text', 1, '20.0000', '20.0000', 'XOF', 'paid', NULL, 1, NULL, 1),
(51, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, 1, NULL, 1),
(52, 1, 'AKADI', '+2250505569256', 'CI', 'mtn', 'orange_ci', 'text', 1, '20.0000', '20.0000', 'XOF', 'paid', NULL, 1, NULL, 1),
(53, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, 1, NULL, 1),
(54, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, 1, NULL, 1),
(55, 1, 'AKADI', '+2250505569256', 'CI', 'mtn', 'orange_ci', 'text', 1, '20.0000', '20.0000', 'XOF', 'paid', NULL, 1, NULL, 1),
(56, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, 1, NULL, 1),
(57, 1, 'AKADI', '+2250505569256', 'CI', 'mtn', 'orange_ci', 'text', 1, '20.0000', '20.0000', 'XOF', 'paid', NULL, 1, NULL, 1),
(58, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, 1, NULL, 1),
(59, 1, 'AKADI', '+2250505569256', 'CI', 'mtn', 'orange_ci', 'text', 1, '20.0000', '20.0000', 'XOF', 'paid', NULL, 1, NULL, 1),
(60, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', NULL, 1, NULL, 1),
(61, 1, 'AKADI', '+2250505569256', 'CI', 'mtn', 'orange_ci', 'text', 1, '20.0000', '20.0000', 'XOF', 'paid', NULL, 1, NULL, 1),
(62, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', '2025-12-11 17:49:03', 1, '2025-12-11 17:49:03', 1),
(63, 1, 'AKADI', '+2250505569256', 'CI', 'mtn', 'orange_ci', 'text', 1, '20.0000', '20.0000', 'XOF', 'paid', '2025-12-11 17:49:05', 1, '2025-12-11 17:49:05', 1),
(64, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'unknown', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', '2025-12-13 02:02:54', NULL, '2025-12-13 02:02:54', NULL),
(65, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'unknown', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', '2025-12-13 02:03:28', NULL, '2025-12-13 02:03:28', NULL),
(66, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'unknown', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', '2025-12-13 05:52:29', NULL, '2025-12-13 05:52:29', NULL),
(67, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'unknown', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', '2025-12-14 06:30:33', NULL, '2025-12-14 06:30:33', NULL),
(68, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'unknown', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', '2025-12-14 23:33:39', NULL, '2025-12-14 23:33:39', NULL),
(69, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'unknown', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', '2025-12-15 08:24:51', NULL, '2025-12-15 08:24:51', NULL),
(70, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', '2025-12-15 09:07:35', 1, '2025-12-15 09:07:35', 1),
(71, 1, 'SIGMA P', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', '2025-12-15 16:19:40', 1, '2025-12-15 16:19:40', 1),
(72, 1, 'AKADI', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', '2025-12-16 09:49:27', 1, '2025-12-16 09:49:27', 1),
(73, 4, 'AKADI', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', '2025-12-19 12:01:16', NULL, '2025-12-19 12:01:16', NULL),
(74, 4, 'AKADI', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', '2025-12-19 12:58:30', NULL, '2025-12-19 12:58:30', NULL),
(75, 1, 'TICAFRIQUE', '+2250505569256', 'CI', 'mtn', 'orange_ci', 'text', 1, '20.0000', '20.0000', 'XOF', 'paid', '2025-12-20 15:48:15', 1, '2025-12-20 15:48:15', 1),
(76, 1, 'TICAFRIQUE', '+2250505569256', 'CI', 'mtn', 'orange_ci', 'text', 1, '20.0000', '20.0000', 'XOF', 'paid', '2025-12-20 15:48:17', 1, '2025-12-20 15:48:17', 1),
(77, 1, 'SIGMA P', '+2250749270077', 'CI', 'orange', 'orange_ci', 'text', 1, '35.0000', '35.0000', 'XOF', 'paid', '2025-12-21 07:46:16', 1, '2025-12-21 07:46:16', 1);

-- --------------------------------------------------------

--
-- Table structure for table `sms_campaigns`
--

CREATE TABLE `sms_campaigns` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sender_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('draft','scheduled','sending','completed','failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `total_recipients` int NOT NULL DEFAULT '0',
  `sent_count` int NOT NULL DEFAULT '0',
  `failed_count` int NOT NULL DEFAULT '0',
  `scheduled_at` datetime DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int UNSIGNED DEFAULT NULL,
  `contact_ids` json DEFAULT NULL,
  `use_personalization` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sms_campaigns`
--

INSERT INTO `sms_campaigns` (`id`, `name`, `message`, `sender_id`, `status`, `total_recipients`, `sent_count`, `failed_count`, `scheduled_at`, `started_at`, `completed_at`, `created_by`, `created_at`, `updated_at`, `updated_by`, `contact_ids`, `use_personalization`) VALUES
(1, 'weekend sms', 'tesrrr', '', 'draft', 35, 0, 0, NULL, NULL, NULL, 1, '2025-11-29 18:42:02', '2025-11-29 18:42:02', NULL, NULL, 1),
(2, 'weekend sms', 'tesrrr', '', 'draft', 35, 0, 0, NULL, NULL, NULL, 1, '2025-11-29 18:45:04', '2025-11-29 18:45:04', NULL, NULL, 1),
(3, 'weekend sms', 'tesrrr', 'TICAFRIQUE', 'draft', 35, 0, 0, NULL, NULL, NULL, 1, '2025-11-29 18:45:22', '2025-11-29 18:45:22', NULL, NULL, 1),
(4, 'weekend sms', 'tesrrr', 'TICAFRIQUE', 'sending', 35, 0, 0, NULL, '2025-11-29 18:47:42', NULL, 1, '2025-11-29 18:47:42', '2025-11-29 18:47:42', NULL, NULL, 1),
(5, 'weekend sms', 'TEST', 'TICAFRIQUE', 'scheduled', 2, 2, 0, '2025-12-03 15:59:00', NULL, NULL, 1, '2025-12-03 15:57:20', '2025-12-03 15:57:20', NULL, NULL, 1),
(6, 'Campagne 04/12/2025 09:37', 'TEST', 'AKADI', 'sending', 2, 2, 0, NULL, '2025-12-04 09:38:53', NULL, 1, '2025-12-04 09:38:53', '2025-12-04 09:38:53', 1, NULL, 1),
(7, 'Campagne 09/12/2025 14:32', 'TEST', 'AKADI', 'completed', 2, 2, 0, '2025-12-09 14:32:00', NULL, '2025-12-09 15:38:50', 1, '2025-12-09 14:33:04', '2025-12-09 14:33:04', 1, NULL, 1),
(8, 'Campagne 09/12/2025 14:41', 'TEST TEST', 'AKADI', 'completed', 2, 2, 0, NULL, NULL, NULL, 1, '2025-12-09 14:53:39', '2025-12-09 14:53:42', 1, NULL, 1),
(9, 'Campagne 09/12/2025 14:54', 'test test', 'AKADI', 'completed', 2, 2, 0, '2025-12-09 14:57:00', NULL, '2025-12-09 15:38:50', 1, '2025-12-09 14:55:24', '2025-12-09 14:55:24', 1, NULL, 1),
(10, 'Campagne 09/12/2025 15:01', 'TEST CAMPAGNES', 'AKADI', 'completed', 2, 2, 0, '2025-12-09 15:03:00', NULL, '2025-12-09 15:38:50', 1, '2025-12-09 15:02:00', '2025-12-09 15:02:00', 1, NULL, 1),
(11, 'Campagne 09/12/2025 15:10', '15H14', 'AKADI', 'completed', 2, 2, 0, '2025-12-09 15:14:00', NULL, '2025-12-09 15:38:50', 1, '2025-12-09 15:12:09', '2025-12-09 15:12:09', 1, NULL, 1),
(12, 'Campagne 09/12/2025 15:18', '16H000', 'AKADI', 'scheduled', 2, 0, 0, '2925-12-09 15:21:00', NULL, NULL, 1, '2025-12-09 15:19:49', '2025-12-09 15:19:49', 1, NULL, 1),
(13, 'Campagne 09/12/2025 15:43', 'TEST DOCS', 'AKADI', 'completed', 2, 2, 0, '2025-12-09 15:47:00', '2025-12-09 15:48:13', '2025-12-09 15:48:14', 1, '2025-12-09 15:44:57', '2025-12-09 15:44:57', 1, NULL, 1),
(14, 'Import 09/12/2025 21:17', 'RAS', 'AKADI', 'completed', 2, 2, 0, NULL, NULL, NULL, 1, '2025-12-09 21:20:10', '2025-12-09 21:20:13', 1, NULL, 1),
(15, 'Campagne 10/12/2025 21:51', 'RAS', 'AKADI', 'scheduled', 1, 0, 0, '1985-12-12 12:12:00', NULL, NULL, 1, '2025-12-10 21:52:36', '2025-12-10 21:52:36', 1, NULL, 1),
(16, 'Import 11/12/2025 12:34', 'ETST RAS', 'AKADI', 'completed', 2, 2, 0, NULL, NULL, NULL, 1, '2025-12-11 12:39:48', '2025-12-11 12:39:50', 1, NULL, 1),
(17, 'Import 11/12/2025 12:39', 'Bonjour {{Prenom}} {{Nom}}, \r\nvotre solde .', 'AKADI', 'completed', 2, 2, 0, NULL, NULL, NULL, 1, '2025-12-11 12:45:29', '2025-12-11 12:45:31', 1, NULL, 1),
(18, 'Import 11/12/2025 14:05', 'tes you', 'AKADI', 'completed', 2, 2, 0, NULL, NULL, NULL, 1, '2025-12-11 14:06:04', '2025-12-11 14:06:07', 1, NULL, 1),
(19, 'Import 11/12/2025 16:45', 'Bonjour {{PRENOM}} {{NOM}}, votre soldeFCFA.', 'AKADI', 'completed', 2, 2, 0, NULL, NULL, NULL, 1, '2025-12-11 16:48:55', '2025-12-11 16:48:58', 1, NULL, 1),
(20, 'Import 11/12/2025 17:25', '{{NOM}} Bonjour {{PRENOM}} le message est passé', 'AKADI', 'completed', 2, 2, 0, NULL, NULL, NULL, 1, '2025-12-11 17:26:58', '2025-12-11 17:27:00', 1, NULL, 1),
(21, 'Import 11/12/2025 17:36', '{{NOM}} est en congé {{PRENOM}}', 'AKADI', 'completed', 2, 2, 0, NULL, NULL, NULL, 1, '2025-12-11 17:37:12', '2025-12-11 17:37:14', 1, NULL, 1),
(22, 'Import 11/12/2025 17:48', '{{NOM}} je suis la {{PRENOM}}', 'AKADI', 'completed', 2, 2, 0, NULL, NULL, NULL, 1, '2025-12-11 17:49:03', '2025-12-11 17:49:05', 1, NULL, 1),
(23, 'Campagne 15/12/2025 09:07', 'test opi', 'AKADI', 'completed', 1, 1, 0, NULL, NULL, NULL, 1, '2025-12-15 09:07:35', '2025-12-15 09:07:37', 1, NULL, 1),
(24, 'Campagne 16/12/2025 09:48', 'TEST OVATION', 'AKADI', 'completed', 1, 1, 0, NULL, NULL, NULL, 1, '2025-12-16 09:49:27', '2025-12-16 09:49:29', 1, NULL, 1),
(25, 'Campagne 20/12/2025 15:47', 'TEST', 'TICAFRIQUE', 'completed', 1, 1, 0, NULL, NULL, NULL, 1, '2025-12-20 15:48:15', '2025-12-20 15:48:17', 1, NULL, 1),
(26, 'Campagne 20/12/2025 15:47', 'TEST', 'TICAFRIQUE', 'completed', 1, 1, 0, NULL, NULL, NULL, 1, '2025-12-20 15:48:17', '2025-12-20 15:48:18', 1, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sms_gateways`
--

CREATE TABLE `sms_gateways` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_key` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `api_secret` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sender_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `priority` int NOT NULL DEFAULT '0',
  `configuration` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int UNSIGNED DEFAULT NULL,
  `rate_limit_per_minute` int DEFAULT '60' COMMENT 'Nombre max de SMS par minute',
  `rate_limit_per_hour` int DEFAULT '1000' COMMENT 'Nombre max de SMS par heure',
  `rate_limit_per_day` int DEFAULT '10000' COMMENT 'Nombre max de SMS par jour',
  `rate_limit_enabled` tinyint(1) DEFAULT '1' COMMENT 'Activer le rate limiting'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sms_gateways`
--

INSERT INTO `sms_gateways` (`id`, `name`, `provider_code`, `api_url`, `api_key`, `api_secret`, `sender_id`, `is_active`, `is_default`, `priority`, `configuration`, `created_at`, `created_by`, `updated_at`, `updated_by`, `rate_limit_per_minute`, `rate_limit_per_hour`, `rate_limit_per_day`, `rate_limit_enabled`) VALUES
(1, 'Orange Côte d\'Ivoire', 'orange_ci', 'https://api.orange.com/smsmessaging/v1/outbound', 'VYqHNrIv99ZZfK64PvxGuPqTbBW2sBmp', 'sGE3dBtAvTuZUPNvvuF3fAMl8NJ4K8lLR7oYU09DECkt', '0778599242', 1, 1, 9, '{\"auth_type\": \"oauth2\", \"token_url\": \"https://api.orange.com/oauth/v3/token\", \"country_code\": \"+225\"}', '2025-11-28 10:21:47', NULL, '2025-12-04 13:49:24', NULL, 100, 1000, 10000, 1),
(2, 'Infobip', 'infobip', 'https://api.infobip.com/sms/2/text/advanced', '', '', '', 0, 0, 5, '{\"auth_type\": \"api_key\", \"max_recipients\": 1000, \"supports_unicode\": true}', '2025-11-28 10:21:47', NULL, '2025-11-28 10:21:47', NULL, 60, 1000, 10000, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sms_messages`
--

CREATE TABLE `sms_messages` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint DEFAULT NULL,
  `to` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `from` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gateway` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `message_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gateway_message_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cost` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `metadata` json DEFAULT NULL,
  `gateway_response` json DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `error` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sms_messages`
--

INSERT INTO `sms_messages` (`id`, `user_id`, `to`, `from`, `message`, `gateway`, `status`, `message_id`, `gateway_message_id`, `cost`, `metadata`, `gateway_response`, `scheduled_at`, `sent_at`, `delivered_at`, `error`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(1, 1, '+2250749270077', 'TICAFRIQUE', 'RAS', 'orange_ci', 'failed', 'SMS-6929d3fa1e0ea', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Failed to obtain access token', '2025-11-28 16:55:22', NULL, '2025-11-28 16:55:22', NULL),
(2, 1, '+2250749270077', 'TICAFRIQUE', 'RAS', 'orange_ci', 'pending', 'SMS-6929d5f5d2be5', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-28 17:03:49', NULL, '2025-11-28 17:03:49', NULL),
(3, 1, '+2250749270077', 'TICAFRIQUE', 'RAS', 'orange_ci', 'sent', 'SMS-6929d779aa02c', 'MOCK-6929D77A357A5', '0.0000', NULL, '{\"to\": \"+2250749270077\", \"cost\": 0.05, \"from\": \"TICAFRIQUE\", \"mock\": true, \"status\": \"success\", \"gateway\": \"orange_ci\", \"message\": \"RAS\", \"messageId\": \"MOCK-6929D77A357A5\", \"timestamp\": \"2025-11-28 17:10:18\"}', NULL, '2025-11-28 17:10:18', NULL, NULL, '2025-11-28 17:10:17', NULL, '2025-11-28 17:10:18', NULL),
(4, 1, '+2250749270077', 'TICAFRIQUE', 'test', 'orange_ci', 'sent', 'SMS-6929d886eb364', 'MOCK-6929D8877144A', '0.0000', NULL, '{\"to\": \"+2250749270077\", \"cost\": 0.05, \"from\": \"TICAFRIQUE\", \"mock\": true, \"status\": \"success\", \"gateway\": \"orange_ci\", \"message\": \"test\", \"messageId\": \"MOCK-6929D8877144A\", \"timestamp\": \"2025-11-28 17:14:47\"}', NULL, '2025-11-28 17:14:47', NULL, NULL, '2025-11-28 17:14:46', NULL, '2025-11-28 17:14:47', NULL),
(5, 1, '+2250749270077', 'TICAFRIQUE', 'RAS', 'orange_ci', 'sent', 'SMS-6929da879c500', 'MOCK-6929DA88238BB', '0.0000', NULL, '{\"to\": \"+2250749270077\", \"cost\": 0.05, \"from\": \"TICAFRIQUE\", \"mock\": true, \"status\": \"success\", \"gateway\": \"orange_ci\", \"message\": \"RAS\", \"messageId\": \"MOCK-6929DA88238BB\", \"timestamp\": \"2025-11-28 17:23:20\"}', NULL, '2025-11-28 17:23:20', NULL, NULL, '2025-11-28 17:23:19', NULL, '2025-11-28 17:23:20', NULL),
(6, 1, '+2250749270077', 'TICAFRIQUE', 'rest', 'orange_ci', 'sent', 'SMS-692a22a557389', 'MOCK-692A22A5D6D89', '0.0000', NULL, '{\"to\": \"+2250749270077\", \"cost\": 0.05, \"from\": \"TICAFRIQUE\", \"mock\": true, \"status\": \"success\", \"gateway\": \"orange_ci\", \"message\": \"rest\", \"messageId\": \"MOCK-692A22A5D6D89\", \"timestamp\": \"2025-11-28 22:31:01\"}', NULL, '2025-11-28 22:31:01', NULL, NULL, '2025-11-28 22:31:01', NULL, '2025-11-28 22:31:01', NULL),
(7, 1, '+2250749270077', 'TICAFRIQUE', 'TEST', 'orange_ci', 'sent', 'SMS-692aabc4dfcdc', 'MOCK-692AABC56A2F0', '0.0000', NULL, '{\"to\": \"+2250749270077\", \"cost\": 0.05, \"from\": \"TICAFRIQUE\", \"mock\": true, \"status\": \"success\", \"gateway\": \"orange_ci\", \"message\": \"TEST\", \"messageId\": \"MOCK-692AABC56A2F0\", \"timestamp\": \"2025-11-29 08:16:05\"}', NULL, '2025-11-29 08:16:05', NULL, NULL, '2025-11-29 08:16:04', NULL, '2025-11-29 08:16:05', NULL),
(8, 1, '+2250749270077', 'TICAFRIQUE', 'TEST', 'orange_ci', 'sent', 'SMS-692ab0f75f17d', 'MOCK-692AB0F7DCB82', '0.0000', NULL, '{\"to\": \"+2250749270077\", \"cost\": 0.05, \"from\": \"TICAFRIQUE\", \"mock\": true, \"status\": \"success\", \"gateway\": \"orange_ci\", \"message\": \"TEST\", \"messageId\": \"MOCK-692AB0F7DCB82\", \"timestamp\": \"2025-11-29 08:38:15\"}', NULL, '2025-11-29 08:38:15', NULL, NULL, '2025-11-29 08:38:15', NULL, '2025-11-29 08:38:15', NULL),
(9, 1, '+2250749270077', 'TICAFRIQUE', 'SMS TEST DEVINCI', 'orange_ci', 'sent', 'SMS-692ab10d494df', 'MOCK-692AB10DC6DA0', '0.0000', NULL, '{\"to\": \"+2250749270077\", \"cost\": 0.05, \"from\": \"TICAFRIQUE\", \"mock\": true, \"status\": \"success\", \"gateway\": \"orange_ci\", \"message\": \"SMS TEST DEVINCI\", \"messageId\": \"MOCK-692AB10DC6DA0\", \"timestamp\": \"2025-11-29 08:38:37\"}', NULL, '2025-11-29 08:38:37', NULL, NULL, '2025-11-29 08:38:37', NULL, '2025-11-29 08:38:37', NULL),
(10, 1, '+2250749270077', 'TICAFRIQUE', 'TEST ENvoie', 'orange_ci', 'sent', 'SMS-692abe5ac639d', 'MOCK-692ABE5B4EFA7', '0.0000', NULL, '{\"to\": \"+2250749270077\", \"cost\": 0.05, \"from\": \"TICAFRIQUE\", \"mock\": true, \"status\": \"success\", \"gateway\": \"orange_ci\", \"message\": \"TEST ENvoie\", \"messageId\": \"MOCK-692ABE5B4EFA7\", \"timestamp\": \"2025-11-29 09:35:23\"}', NULL, '2025-11-29 09:35:23', NULL, NULL, '2025-11-29 09:35:22', NULL, '2025-11-29 09:35:23', NULL),
(11, 1, '+2250749270077', 'TICAFRIQUE', 'devinci fortt', 'orange_ci', 'failed', 'SMS-692ac0e0d629c', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Failed to obtain access token', '2025-11-29 09:46:08', NULL, '2025-11-29 09:46:09', NULL),
(12, 1, '+2250749270077', 'TICAFRIQUE', 'test fulbert', 'orange_ci', 'failed', 'SMS-692ac340e98f4', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Failed to obtain access token', '2025-11-29 09:56:16', NULL, '2025-11-29 09:56:17', NULL),
(13, 1, '+2250749270077', 'TICAFRIQUE', 'TEST ILANE', 'orange_ci', 'failed', 'SMS-692ac5ab5d61c', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Failed to obtain access token', '2025-11-29 10:06:35', NULL, '2025-11-29 10:06:36', NULL),
(14, 1, '+2250749270077', 'TICAFRIQUE', 'TEST ORANGE API', 'orange_ci', 'failed', 'SMS-692ac5d818c57', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Failed to obtain access token', '2025-11-29 10:07:20', NULL, '2025-11-29 10:07:20', NULL),
(15, 1, '+2250749270077', 'TICAFRIQUE', 'ddgdfg dfgdf', 'orange_ci', 'sent', 'SMS-692ae4da6d5a0', 'MOCK-692AE4DAE9A12', '0.0000', NULL, '{\"to\": \"+2250749270077\", \"cost\": 0.05, \"from\": \"TICAFRIQUE\", \"mock\": true, \"status\": \"success\", \"gateway\": \"orange_ci\", \"message\": \"ddgdfg dfgdf\", \"messageId\": \"MOCK-692AE4DAE9A12\", \"timestamp\": \"2025-11-29 12:19:38\"}', NULL, '2025-11-29 12:19:38', NULL, NULL, '2025-11-29 12:19:38', NULL, '2025-11-29 12:19:38', NULL),
(16, 1, '+2250749270077', 'TICAFRIQUE', 'test', 'orange_ci', 'failed', 'SMS-692ae841e6032', NULL, '0.0000', NULL, '{\"requestError\": {\"policyException\": {\"text\": \"A policy error occurred. Error code is %1\", \"messageId\": \"POL0001\", \"variables\": [\"Expired contract. You can buy a new bundle on https://developer.orange.com to reactivate it or contact Orange local team via https://developer.orange.com/sms-api-queries/ to renew it.\"]}}}', NULL, NULL, NULL, 'Failed to send SMS', '2025-11-29 12:34:09', NULL, '2025-11-29 12:34:11', NULL),
(17, 1, '+2250749270077', 'SMS', 'gfdg', 'orange_ci', 'pending', 'SMS-692b2089936af', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-29 16:34:17', NULL, '2025-11-29 16:34:17', NULL),
(18, 1, '+2250749270077', 'SMS', 'gfdg', 'orange_ci', 'pending', 'SMS-692b2093193f1', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-29 16:34:27', NULL, '2025-11-29 16:34:27', NULL),
(19, 1, '+2250749270077', 'SMS', 'gfdg', 'orange_ci', 'failed', 'SMS-692b20cee0f3c', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Insufficient balance. Cost: 15 XOF', '2025-11-29 16:35:26', NULL, '2025-11-29 16:35:26', NULL),
(20, 1, '+2250700000000', 'TestSMSSMS', 'Test de facturation SMS', 'orange_ci', 'failed', 'SMS-692b21409729b', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Insufficient balance. Cost: 15 XOF', '2025-11-29 16:37:20', NULL, '2025-11-29 16:37:20', NULL),
(21, 1, '+2250749270077', 'SMS', 'AZERTRY', 'orange_ci', 'sent', 'SMS-692b30497354f', 'MOCK-692B304A042A7', '0.0000', NULL, '{\"to\": \"+2250749270077\", \"cost\": 0.05, \"from\": \"SMS\", \"mock\": true, \"status\": \"success\", \"gateway\": \"orange_ci\", \"message\": \"AZERTRY\", \"messageId\": \"MOCK-692B304A042A7\", \"timestamp\": \"2025-11-29 17:41:30\"}', NULL, '2025-11-29 17:41:30', NULL, NULL, '2025-11-29 17:41:29', NULL, '2025-11-29 17:41:30', NULL),
(22, 1, '+2250749270077', 'TICAFRIQUE', 'TEST', 'orange_ci', 'pending', 'SMS-6930396a96de5', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 13:21:46', NULL, '2025-12-03 13:21:46', NULL),
(23, 1, '+2250749270077', 'TICAFRIQUE', 'TEST', 'orange_ci', 'pending', 'SMS-69303ae2b6837', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 13:28:02', NULL, '2025-12-03 13:28:02', NULL),
(24, 1, '+2250749270077', 'TICAFRIQUE', 'TEST', 'orange_ci', 'pending', 'SMS-69303c3a44285', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 13:33:46', NULL, '2025-12-03 13:33:46', NULL),
(25, 1, '+2250749270077', 'TICAFRIQUE', 'TEST', 'orange_ci', 'sent', 'SMS-69303d959a332', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+225/requests/cc6e9054-e648-4b06-be4c-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+225/requests/cc6e9054-e648-4b06-be4c-8b0cc7fb7b7c\", \"senderAddress\": \"tel:+225\", \"outboundSMSTextMessage\": {\"message\": \"TEST\"}}}', NULL, '2025-12-03 13:39:34', NULL, NULL, '2025-12-03 13:39:33', NULL, '2025-12-03 13:39:34', NULL),
(26, 1, '+2250749270077', 'TICAFRIQUE', 'TEST MESSAGE A ENVOYER', 'orange_ci', 'sent', 'SMS-69303e196a521', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+225/requests/98c68a6e-4a63-4fef-b652-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+225/requests/98c68a6e-4a63-4fef-b652-c2dcf3700410\", \"senderAddress\": \"tel:+225\", \"outboundSMSTextMessage\": {\"message\": \"TEST MESSAGE A ENVOYER\"}}}', NULL, '2025-12-03 13:41:46', NULL, NULL, '2025-12-03 13:41:45', NULL, '2025-12-03 13:41:46', NULL),
(27, 1, '+2250749270077', 'TICAFRIQUE', 'AVEC', 'orange_ci', 'sent', 'SMS-69303ecedb030', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+225/requests/5b5e2da1-7554-41dc-8c64-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+225/requests/5b5e2da1-7554-41dc-8c64-e4181859a474\", \"senderAddress\": \"tel:+225\", \"outboundSMSTextMessage\": {\"message\": \"AVEC\"}}}', NULL, '2025-12-03 13:44:49', NULL, NULL, '2025-12-03 13:44:46', NULL, '2025-12-03 13:44:49', NULL),
(28, 1, '+2250749270077', 'TICAFRIQUE', 'TEST TEST', 'orange_ci', 'sent', 'SMS-693040a12976a', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+225/requests/e23289ce-a34a-4022-93a3-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+225/requests/e23289ce-a34a-4022-93a3-93ef4438a3fc\", \"senderAddress\": \"tel:+225\", \"outboundSMSTextMessage\": {\"message\": \"TEST TEST\"}}}', NULL, '2025-12-03 13:52:34', NULL, NULL, '2025-12-03 13:52:33', NULL, '2025-12-03 13:52:34', NULL),
(29, 1, '+2250749270077', 'TICAFRIQUE', 'TEST JENVOIE', 'orange_ci', 'sent', 'SMS-693045001eccb', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+225/requests/fbddd9e4-9125-40da-a783-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+225/requests/fbddd9e4-9125-40da-a783-d70839a45184\", \"senderAddress\": \"tel:+225\", \"outboundSMSTextMessage\": {\"message\": \"TEST JENVOIE\"}}}', NULL, '2025-12-03 14:11:13', NULL, NULL, '2025-12-03 14:11:12', NULL, '2025-12-03 14:11:13', NULL),
(30, 1, '+2250749270077', 'SMS 483497', 'TEST', 'orange_ci', 'failed', 'SMS-693046528fd40', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Failed to send SMS', '2025-12-03 14:16:50', NULL, '2025-12-03 14:16:50', NULL),
(31, 1, '+2250749270077', 'TICAFRIQUE', 'tic sms test', 'orange_ci', 'failed', 'SMS-693046a8cbe85', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Invalid Sender Address. Please configure a numeric Sender ID in gateway settings.', '2025-12-03 14:18:16', NULL, '2025-12-03 14:18:16', NULL),
(32, 1, '+2250749270077', '0778599242', 'TEST TEST', 'orange_ci', 'failed', 'SMS-6930508876840', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Failed to send SMS', '2025-12-03 15:00:24', NULL, '2025-12-03 15:00:24', NULL),
(33, 1, '+2250749270077', 'TICAFRIQUE', 'TEST TEST', 'orange_ci', 'sent', 'SMS-69305216c3797', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/1c87baba-e958-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"senderName\": \"TICAFRIQUE\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/1c87baba-e958-465d-a9c1-de2d0b46c4d9\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"TEST TEST\"}}}', NULL, '2025-12-03 15:07:04', NULL, NULL, '2025-12-03 15:07:02', NULL, '2025-12-03 15:07:04', NULL),
(34, 1, '+2250749270077', 'SMS 483497', 'TEST', 'orange_ci', 'sent', 'SMS-69307559ef8ce', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/6dd7791d-ffa1-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"senderName\": \"SMS 483497\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/6dd7791d-ffa1-4fa3-8c61-f2adf2d5dcaf\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"TEST\"}}}', NULL, '2025-12-03 17:37:33', NULL, NULL, '2025-12-03 17:37:29', NULL, '2025-12-03 17:37:33', NULL),
(35, 1, '+2250749270077', 'AKADI', 'TEST ENVOIE', 'orange_ci', 'sent', 'SMS-69307584329e8', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/ee4121ae-15c4-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"senderName\": \"AKADI\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/ee4121ae-15c4-4a81-b11c-f750c2ec2bfa\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"TEST ENVOIE\"}}}', NULL, '2025-12-03 17:38:13', NULL, NULL, '2025-12-03 17:38:12', NULL, '2025-12-03 17:38:13', NULL),
(36, 1, '+0778599242', 'AKADI', 'TEST TEST LATH SMS', 'orange_ci', 'failed', 'SMS-69313dc353565', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Billing error: SQLSTATE[22001]: String data, right truncated: 1406 Data too long for column \'country_code\' at row 1', '2025-12-04 07:52:35', 1, '2025-12-04 07:52:35', 1),
(37, 1, '+2250778599242', 'AKADI', 'test test lath', 'orange_ci', 'failed', 'SMS-69313e06d2ae6', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Billing error: SQLSTATE[42S22]: Column not found: 1054 Unknown column \'updated_by\' in \'field list\'', '2025-12-04 07:53:42', 1, '2025-12-04 07:53:42', 1),
(38, 1, '+2250778599242', 'AKADI', 'TEST TEST', 'orange_ci', 'failed', 'SMS-69313edb3c3d0', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Billing error: SQLSTATE[42S22]: Column not found: 1054 Unknown column \'updated_by\' in \'field list\'', '2025-12-04 07:57:15', 1, '2025-12-04 07:57:15', 1),
(39, 1, '+2250778599242', 'AKADI', 'TEST', 'orange_ci', 'failed', 'SMS-69313f14c5305', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Billing error: SQLSTATE[42S22]: Column not found: 1054 Unknown column \'updated_by\' in \'field list\'', '2025-12-04 07:58:12', 1, '2025-12-04 07:58:12', 1),
(40, 1, '+2250778599242', 'AKADI', 'scsfsf', 'orange_ci', 'failed', 'SMS-69313f3c86324', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Billing error: SQLSTATE[42S22]: Column not found: 1054 Unknown column \'updated_by\' in \'field list\'', '2025-12-04 07:58:52', 1, '2025-12-04 07:58:52', 1),
(41, 1, '+2250778599242', 'AKADI', 'TEST TEST', 'orange_ci', 'sent', 'SMS-69313fff90e7a', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/07af6b67-d8d2-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250778599242\"], \"senderName\": \"AKADI\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/07af6b67-d8d2-4a38-9bc7-86f920eddce3\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"TEST TEST\"}}}', NULL, '2025-12-04 08:02:09', NULL, NULL, '2025-12-04 08:02:07', 1, '2025-12-04 08:02:09', 1),
(42, 1, '+2250505569256', 'AKADI', 'TEST', 'orange_ci', 'sent', 'SMS-6931446f4db8e', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/1b370430-6ad1-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250505569256\"], \"senderName\": \"AKADI\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/1b370430-6ad1-4fbb-a143-0077f518cdf6\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"TEST\"}}}', NULL, '2025-12-04 08:21:04', NULL, NULL, '2025-12-04 08:21:03', 1, '2025-12-04 08:21:04', 1),
(43, 1, '+2250749270077', 'AKADI', 'TEST', 'orange_ci', 'sent', 'SMS-693150845a5f7', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/49755039-8bd7-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"senderName\": \"AKADI\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/49755039-8bd7-443e-8e6f-227384dcfdc1\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"TEST\"}}}', NULL, '2025-12-04 09:12:40', NULL, NULL, '2025-12-04 09:12:36', 1, '2025-12-04 09:12:40', 1),
(44, 1, '+2250749270077', 'AKADI', 'test', 'orange_ci', 'sent', 'SMS-6931565b7f095', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/e2424e95-d872-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"senderName\": \"AKADI\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/e2424e95-d872-4c65-96a4-4828987cd154\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"test\"}}}', NULL, '2025-12-04 09:37:33', NULL, NULL, '2025-12-04 09:37:31', 1, '2025-12-04 09:37:33', 1),
(45, 1, '+2250749270077', 'MEIWAY LIVE', 'test', 'orange_ci', 'failed', 'SMS-6932e338e7219', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Undefined array key 1', '2025-12-05 13:50:48', 1, '2025-12-05 13:50:48', 1),
(46, 1, '+2250749270077', 'AKADI', 'test', 'orange_ci', 'failed', 'SMS-6932e355bde1f', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Undefined array key 1', '2025-12-05 13:51:17', 1, '2025-12-05 13:51:17', 1),
(47, 1, '+2250749270077', 'MEIWAY LIVE', 'test', 'orange_ci', 'failed', 'SMS-6932e37d13b03', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Failed to send SMS', '2025-12-05 13:51:57', 1, '2025-12-05 13:51:57', 1),
(48, 1, '+2250749270077', 'AKADI', 'TEST TEST', 'orange_ci', 'sent', 'SMS-6932e39b9fba8', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/e10189bf-bcff-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"senderName\": \"AKADI\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/e10189bf-bcff-4c31-89bf-b7bf0f144ea3\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"TEST TEST\"}}}', NULL, '2025-12-05 13:52:29', NULL, NULL, '2025-12-05 13:52:27', 1, '2025-12-05 13:52:29', 1),
(49, 1, '+2250749270077', 'AKADI', 'AZERTY', 'orange_ci', 'failed', 'SMS-6932e47b984a1', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Undefined array key 1', '2025-12-05 13:56:11', 1, '2025-12-05 13:56:11', 1),
(50, 1, '+2250749270077', 'AKADI', 'TEST', 'orange_ci', 'sent', 'SMS-6935302410482', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/1b236d5a-39e5-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"senderName\": \"AKADI\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/1b236d5a-39e5-4af4-be20-adeb81f1b8f8\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"TEST\"}}}', NULL, '2025-12-07 07:43:33', NULL, NULL, '2025-12-07 07:43:32', 1, '2025-12-07 07:43:33', 1),
(51, 1, '+2250749270077', NULL, 'Votre code de vérification est: 632454. Valide pour 2 minutes.', 'orange_ci', 'failed', 'OTP-69354cec5a754', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Failed to send SMS', '2025-12-07 09:46:20', 1, '2025-12-07 09:46:20', 1),
(52, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 428009. Valide pour 2 minutes.', 'orange_ci', 'sent', 'OTP-693558bd74c07', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/1291cd9e-8b03-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"senderName\": \"AKADI\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/1291cd9e-8b03-47ef-8e35-dbf20c7ca060\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"Votre code de vérification est: 428009. Valide pour 2 minutes.\"}}}', NULL, '2025-12-07 10:36:46', NULL, NULL, '2025-12-07 10:36:45', 1, '2025-12-07 10:36:46', 1),
(53, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 975039. Valide pour 2 minutes.', 'orange_ci', 'sent', 'OTP-6935ba0e34dbe', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/98f44395-1a2a-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"senderName\": \"AKADI\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/98f44395-1a2a-48d2-8d01-ca3ddf8f4416\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"Votre code de vérification est: 975039. Valide pour 2 minutes.\"}}}', NULL, '2025-12-07 17:31:59', NULL, NULL, '2025-12-07 17:31:58', NULL, '2025-12-07 17:31:59', NULL),
(54, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 430788. Valide pour 2 minutes.', 'orange_ci', 'sent', 'OTP-6935be3caf7e2', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/3fda4814-5cde-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"senderName\": \"AKADI\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/3fda4814-5cde-4711-9b46-9729137ed5f3\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"Votre code de vérification est: 430788. Valide pour 2 minutes.\"}}}', NULL, '2025-12-07 17:49:50', NULL, NULL, '2025-12-07 17:49:48', NULL, '2025-12-07 17:49:50', NULL),
(55, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 179564. Valide pour 2 minutes.', 'orange_ci', 'sent', 'OTP-6935be78b7b5e', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/e4e09718-c73c-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"senderName\": \"AKADI\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/e4e09718-c73c-4929-9926-ef2b5a24c641\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"Votre code de vérification est: 179564. Valide pour 2 minutes.\"}}}', NULL, '2025-12-07 17:50:49', NULL, NULL, '2025-12-07 17:50:48', NULL, '2025-12-07 17:50:49', NULL),
(56, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 328819. Valide pour 2 minutes.', 'orange_ci', 'sent', 'OTP-6935bf41825c7', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/7a120d6e-00c5-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"senderName\": \"AKADI\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/7a120d6e-00c5-4563-ad8e-fe38b6b94079\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"Votre code de vérification est: 328819. Valide pour 2 minutes.\"}}}', NULL, '2025-12-07 17:54:10', NULL, NULL, '2025-12-07 17:54:09', NULL, '2025-12-07 17:54:10', NULL),
(57, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 097781. Valide pour 2 minutes.', 'orange_ci', 'sent', 'OTP-6935bf5562715', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/0a68dd5b-6f2b-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"senderName\": \"AKADI\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/0a68dd5b-6f2b-43f4-bc65-3d7e4e49a279\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"Votre code de vérification est: 097781. Valide pour 2 minutes.\"}}}', NULL, '2025-12-07 17:54:30', NULL, NULL, '2025-12-07 17:54:29', NULL, '2025-12-07 17:54:30', NULL),
(58, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 955337. Valide pour 2 minutes.', 'orange_ci', 'sent', 'OTP-6935bfb61e614', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/10e41568-01f5-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"senderName\": \"AKADI\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/10e41568-01f5-4648-8042-0782ce727c13\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"Votre code de vérification est: 955337. Valide pour 2 minutes.\"}}}', NULL, '2025-12-07 17:56:07', NULL, NULL, '2025-12-07 17:56:06', NULL, '2025-12-07 17:56:07', NULL),
(59, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 662416. Valide pour 2 minutes.', 'orange_ci', 'sent', 'OTP-6935c2afba36c', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/7f1098ae-ae8f-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"senderName\": \"AKADI\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/7f1098ae-ae8f-4482-8123-c1aa924b8857\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"Votre code de vérification est: 662416. Valide pour 2 minutes.\"}}}', NULL, '2025-12-07 18:08:48', NULL, NULL, '2025-12-07 18:08:47', NULL, '2025-12-07 18:08:48', NULL),
(60, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 458342. Valide pour 2 minutes.', 'orange_ci', 'failed', 'OTP-6935c345c61a8', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Insufficient balance. Cost: 35 XOF', '2025-12-07 18:11:17', NULL, '2025-12-07 18:11:17', NULL),
(61, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 991371. Valide pour 2 minutes.', 'orange_ci', 'failed', 'OTP-6935c36fec410', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Insufficient balance. Cost: 35 XOF', '2025-12-07 18:11:59', NULL, '2025-12-07 18:11:59', NULL),
(62, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 530438. Valide pour 2 minutes.', 'orange_ci', 'failed', 'OTP-6935c395c2467', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Insufficient balance. Cost: 35 XOF', '2025-12-07 18:12:37', NULL, '2025-12-07 18:12:37', NULL),
(63, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 902004. Valide pour 2 minutes.', 'orange_ci', 'failed', 'OTP-6935c3c129892', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Insufficient balance. Cost: 35 XOF', '2025-12-07 18:13:21', NULL, '2025-12-07 18:13:21', NULL),
(64, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 001279. Valide pour 2 minutes.', 'orange_ci', 'failed', 'OTP-6935c41cd25bc', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Insufficient balance. Cost: 35 XOF', '2025-12-07 18:14:52', NULL, '2025-12-07 18:14:52', NULL),
(65, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 833729. Valide pour 2 minutes.', 'orange_ci', 'failed', 'OTP-6935c44f9ff05', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Insufficient balance. Cost: 35 XOF', '2025-12-07 18:15:43', NULL, '2025-12-07 18:15:43', NULL),
(66, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 527771. Valide pour 2 minutes.', 'orange_ci', 'failed', 'OTP-6935c4c68e51e', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Insufficient balance. Cost: 35 XOF', '2025-12-07 18:17:42', 1, '2025-12-07 18:17:42', 1),
(67, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 283902. Valide pour 2 minutes.', 'orange_ci', 'failed', 'OTP-6935c5864ca15', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Insufficient balance. Cost: 35 XOF', '2025-12-07 18:20:54', 1, '2025-12-07 18:20:54', 1),
(68, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 908035. Valide pour 2 minutes.', 'orange_ci', 'failed', 'OTP-6935c5d1a2a2c', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Insufficient balance. Cost: 35 XOF', '2025-12-07 18:22:09', 1, '2025-12-07 18:22:09', 1),
(69, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 027714. Valide pour 2 minutes.', 'orange_ci', 'failed', 'OTP-6935c600aec0b', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Insufficient balance. Cost: 35 XOF', '2025-12-07 18:22:56', 1, '2025-12-07 18:22:56', 1),
(70, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 115696. Valide pour 2 minutes.', 'orange_ci', 'failed', 'OTP-6935c6a9a1384', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Insufficient balance. Cost: 35 XOF', '2025-12-07 18:25:45', 1, '2025-12-07 18:25:45', 1),
(71, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 213820. Valide pour 2 minutes.', 'orange_ci', 'failed', 'OTP-6935c6f083a34', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Insufficient balance. Cost: 35 XOF', '2025-12-07 18:26:56', 1, '2025-12-07 18:26:56', 1),
(72, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 799606. Valide pour 2 minutes.', 'orange_ci', 'failed', 'OTP-6935c8678a1de', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Insufficient balance. Cost: 35 XOF', '2025-12-07 18:33:11', 1, '2025-12-07 18:33:11', 1),
(73, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 057478. Valide pour 2 minutes.', 'orange_ci', 'failed', 'OTP-6935c881e72ee', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Insufficient balance. Cost: 35 XOF', '2025-12-07 18:33:37', 1, '2025-12-07 18:33:37', 1),
(74, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 789076. Valide pour 2 minutes.', 'orange_ci', 'failed', 'OTP-6935c890701b4', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Insufficient balance. Cost: 35 XOF', '2025-12-07 18:33:52', 1, '2025-12-07 18:33:52', 1),
(75, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 640248. Valide pour 2 minutes.', 'orange_ci', 'failed', 'OTP-6935c9386fd5f', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Insufficient balance. Cost: 35 XOF', '2025-12-07 18:36:40', 1, '2025-12-07 18:36:40', 1),
(76, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 540626. Valide pour 2 minutes.', 'orange_ci', 'failed', 'OTP-6935c9e5e630c', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Insufficient balance. Cost: 35 XOF', '2025-12-07 18:39:33', NULL, '2025-12-07 18:39:33', NULL),
(77, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 779056. Valide pour 2 minutes.', 'orange_ci', 'failed', 'OTP-6935ca0a0f328', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Insufficient balance. Cost: 35 XOF', '2025-12-07 18:40:10', NULL, '2025-12-07 18:40:10', NULL),
(78, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 334909. Valide pour 2 minutes.', 'orange_ci', 'failed', 'OTP-6935ca61b128a', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Insufficient balance. Cost: 35 XOF', '2025-12-07 18:41:37', NULL, '2025-12-07 18:41:37', NULL),
(79, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 789763. Valide pour 2 minutes.', 'orange_ci', 'failed', 'OTP-6935cb44cdd79', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Insufficient balance. Cost: 35 XOF', '2025-12-07 18:45:24', NULL, '2025-12-07 18:45:24', NULL),
(80, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 668597. Valide pour 2 minutes.', 'orange_ci', 'failed', 'OTP-6935cd1b09631', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Insufficient balance. Cost: 35 XOF', '2025-12-07 18:53:15', NULL, '2025-12-07 18:53:15', NULL),
(81, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 289029. Valide pour 2 minutes.', 'orange_ci', 'failed', 'OTP-6935d208871cc', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Insufficient balance. Cost: 35 XOF', '2025-12-07 19:14:16', NULL, '2025-12-07 19:14:16', NULL),
(82, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 893435. Valide pour 2 minutes.', 'orange_ci', 'failed', 'OTP-6935d20c90f88', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Insufficient balance. Cost: 35 XOF', '2025-12-07 19:14:20', NULL, '2025-12-07 19:14:20', NULL),
(83, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 298836. Valide pour 2 minutes.', 'orange_ci', 'failed', 'OTP-6936588c91697', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Insufficient balance. Cost: 35 XOF', '2025-12-08 04:48:12', NULL, '2025-12-08 04:48:12', NULL),
(84, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 524107. Valide pour 2 minutes.', 'orange_ci', 'failed', 'OTP-6936592a6e0b3', NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 'Insufficient balance. Cost: 35 XOF', '2025-12-08 04:50:50', NULL, '2025-12-08 04:50:50', NULL),
(85, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 845955. Valide pour 2 minutes.', 'orange_ci', 'sent', 'OTP-69365b9f8b424', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/9a9561ca-3dbd-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"senderName\": \"AKADI\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/9a9561ca-3dbd-4c08-938b-bad749513b8c\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"Votre code de vérification est: 845955. Valide pour 2 minutes.\"}}}', NULL, '2025-12-08 05:01:21', NULL, NULL, '2025-12-08 05:01:19', NULL, '2025-12-08 05:01:21', NULL),
(86, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 021548. Valide pour 2 minutes.', 'orange_ci', 'sent', 'OTP-69368f99e4dc3', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/3b723037-20fd-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"senderName\": \"AKADI\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/3b723037-20fd-4c21-be40-a7892695d97d\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"Votre code de vérification est: 021548. Valide pour 2 minutes.\"}}}', NULL, '2025-12-08 08:43:07', NULL, NULL, '2025-12-08 08:43:05', NULL, '2025-12-08 08:43:07', NULL),
(87, 1, '+2250749270077', 'AKADI', 'TEST 2025', 'orange_ci', 'sent', 'SMS-693ab78e2aa46', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/bce7b679-d0f4-', '35.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"senderName\": \"AKADI\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/bce7b679-d0f4-48fc-b35c-02d7e86aa497\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"TEST 2025\"}}}', NULL, '2025-12-11 12:22:40', NULL, NULL, '2025-12-11 12:22:38', 1, '2025-12-11 12:22:40', 1),
(88, 1, '+2250749270077', 'AKADI', 'ETST RAS', 'orange_ci', 'sent', 'CAMPAIGN-16-693abb95ada16', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/0aaf08f7-a0d4-', '35.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"senderName\": \"AKADI\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/0aaf08f7-a0d4-48e1-b660-b6ce38fc71f3\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"ETST RAS\"}}}', NULL, '2025-12-11 12:39:49', NULL, NULL, '2025-12-11 12:39:49', 1, '2025-12-11 12:39:49', 1),
(89, 1, '+2250505569256', 'AKADI', 'ETST RAS', 'orange_ci', 'sent', 'CAMPAIGN-16-693abb96c3032', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/f7ea3604-304d-', '20.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250505569256\"], \"senderName\": \"AKADI\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/f7ea3604-304d-4d0d-9b88-e9bb78b7c742\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"ETST RAS\"}}}', NULL, '2025-12-11 12:39:50', NULL, NULL, '2025-12-11 12:39:50', 1, '2025-12-11 12:39:50', 1),
(90, 1, '+2250749270077', 'AKADI', 'Bonjour {{Prenom}} {{Nom}}, \r\nvotre solde .', 'orange_ci', 'sent', 'CAMPAIGN-17-693abcea9d8a4', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/e7f73471-8619-', '35.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"senderName\": \"AKADI\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/e7f73471-8619-45db-80cd-279e148104d3\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"Bonjour {{Prenom}} {{Nom}}, \\r\\nvotre solde .\"}}}', NULL, '2025-12-11 12:45:30', NULL, NULL, '2025-12-11 12:45:30', 1, '2025-12-11 12:45:30', 1),
(91, 1, '+2250505569256', 'AKADI', 'Bonjour {{Prenom}} {{Nom}}, \r\nvotre solde .', 'orange_ci', 'sent', 'CAMPAIGN-17-693abceb91621', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/a35294b9-154f-', '20.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250505569256\"], \"senderName\": \"AKADI\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/a35294b9-154f-438d-953f-6253f34b6ee6\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"Bonjour {{Prenom}} {{Nom}}, \\r\\nvotre solde .\"}}}', NULL, '2025-12-11 12:45:31', NULL, NULL, '2025-12-11 12:45:31', 1, '2025-12-11 12:45:31', 1),
(92, 1, '+2250749270077', 'AKADI', 'om', 'orange_ci', 'sent', 'SMS-693acddbba91a', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/f2a03a35-f0ea-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"senderName\": \"AKADI\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/f2a03a35-f0ea-45db-a73a-f02f6aa22bc2\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"om\"}}}', NULL, '2025-12-11 13:57:49', NULL, NULL, '2025-12-11 13:57:47', 1, '2025-12-11 13:57:49', 1),
(93, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 743442. Valide pour 2 minutes.', 'orange_ci', 'sent', 'OTP-693cc94e0ed25', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/cea618c2-b06f-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"senderName\": \"AKADI\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/cea618c2-b06f-47a0-952e-ab7dc37ee2c2\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"Votre code de vérification est: 743442. Valide pour 2 minutes.\"}}}', NULL, '2025-12-13 02:02:56', NULL, NULL, '2025-12-13 02:02:54', NULL, '2025-12-13 02:02:56', NULL),
(94, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 930531. Valide pour 2 minutes.', 'orange_ci', 'sent', 'OTP-693cc970a4edf', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/e423b8bd-cab3-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"senderName\": \"AKADI\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/e423b8bd-cab3-468f-bcfc-3e0b5b1aa316\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"Votre code de vérification est: 930531. Valide pour 2 minutes.\"}}}', NULL, '2025-12-13 02:03:29', NULL, NULL, '2025-12-13 02:03:28', NULL, '2025-12-13 02:03:29', NULL),
(95, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 604020. Valide pour 2 minutes.', 'orange_ci', 'sent', 'OTP-693cff1d13f24', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/f98d0873-6fd2-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"senderName\": \"AKADI\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/f98d0873-6fd2-4874-86ce-dfd591a90e5e\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"Votre code de vérification est: 604020. Valide pour 2 minutes.\"}}}', NULL, '2025-12-13 05:52:31', NULL, NULL, '2025-12-13 05:52:29', NULL, '2025-12-13 05:52:31', NULL),
(96, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 860923. Valide pour 2 minutes.', 'orange_ci', 'sent', 'OTP-693e59897ce4b', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/16312a6e-0766-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"senderName\": \"AKADI\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/16312a6e-0766-479a-8530-1238bd6c0ac4\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"Votre code de vérification est: 860923. Valide pour 2 minutes.\"}}}', NULL, '2025-12-14 06:30:36', NULL, NULL, '2025-12-14 06:30:33', NULL, '2025-12-14 06:30:36', NULL),
(97, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 440856. Valide pour 2 minutes.', 'orange_ci', 'sent', 'OTP-693f495384066', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/45124856-2768-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"senderName\": \"AKADI\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/45124856-2768-4af6-aa84-a477f3d70de9\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"Votre code de vérification est: 440856. Valide pour 2 minutes.\"}}}', NULL, '2025-12-14 23:33:41', NULL, NULL, '2025-12-14 23:33:39', NULL, '2025-12-14 23:33:41', NULL),
(98, 1, '+2250749270077', 'AKADI', 'Votre code de vérification est: 526252. Valide pour 2 minutes.', 'orange_ci', 'sent', 'OTP-693fc5d3ee181', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/39ebc58d-e635-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"senderName\": \"AKADI\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/39ebc58d-e635-42c3-b22b-c570948a8284\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"Votre code de vérification est: 526252. Valide pour 2 minutes.\"}}}', NULL, '2025-12-15 08:24:53', NULL, NULL, '2025-12-15 08:24:51', NULL, '2025-12-15 08:24:53', NULL),
(99, 1, '+2250749270077', 'SIGMA P', 'TEST REST', 'orange_ci', 'sent', 'SMS-6940351c356d1', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/b248df7b-ef55-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"senderName\": \"SIGMA P\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/b248df7b-ef55-47a4-882a-9e4fb4cb0ba5\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"TEST REST\"}}}', NULL, '2025-12-15 16:19:43', NULL, NULL, '2025-12-15 16:19:40', 1, '2025-12-15 16:19:43', 1),
(100, 1, '+2250749270077', 'SIGMA P', 'test orange', 'orange_ci', 'sent', 'SMS-6947a5c89ac53', 'https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/f091df13-42f5-', '0.0000', NULL, '{\"outboundSMSMessageRequest\": {\"address\": [\"tel:+2250749270077\"], \"senderName\": \"SIGMA P\", \"resourceURL\": \"https://backend.dck.cloud.orange/smsmessaging/v1/outbound/tel:+2250778599242/requests/f091df13-42f5-451b-b7b0-3c62980dbd7e\", \"senderAddress\": \"tel:+2250778599242\", \"outboundSMSTextMessage\": {\"message\": \"test orange\"}}}', NULL, '2025-12-21 07:46:18', NULL, NULL, '2025-12-21 07:46:16', 1, '2025-12-21 07:46:18', 1);

-- --------------------------------------------------------

--
-- Table structure for table `sms_queue`
--

CREATE TABLE `sms_queue` (
  `id` bigint UNSIGNED NOT NULL,
  `campaign_id` int DEFAULT NULL,
  `recipient` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sender_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gateway` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'auto',
  `status` enum('pending','processing','sent','failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `attempts` int NOT NULL DEFAULT '0',
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `scheduled_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sms_queue`
--

INSERT INTO `sms_queue` (`id`, `campaign_id`, `recipient`, `message`, `sender_id`, `gateway`, `status`, `attempts`, `error_message`, `scheduled_at`, `sent_at`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(1, 2, '2250505569256', 'tesrrr', '', 'auto', 'processing', 0, NULL, NULL, NULL, '2025-11-29 18:45:04', NULL, '2025-11-29 18:45:04', NULL),
(2, 3, '2250505569256', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:45:22', NULL, '2025-11-29 18:45:22', NULL),
(3, 4, '2250505569256', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(4, 4, '2250749270077', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(5, 4, '2250505500557', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(6, 4, '2250778599242', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(7, 4, '2250505569256', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(8, 4, '2250749270077', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(9, 4, '2250505500557', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(10, 4, '2250778599242', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(11, 4, '2250505569256', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(12, 4, '2250749270077', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(13, 4, '2250505500557', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(14, 4, '2250778599242', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(15, 4, '2250505569256', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(16, 4, '2250749270077', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(17, 4, '2250505500557', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(18, 4, '2250778599242', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(19, 4, '2250505569256', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(20, 4, '2250749270077', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(21, 4, '2250505500557', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(22, 4, '2250778599242', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(23, 4, '2250505569256', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(24, 4, '2250749270077', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(25, 4, '2250505500557', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(26, 4, '2250778599242', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(27, 4, '2250505569256', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(28, 4, '2250749270077', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(29, 4, '2250505500557', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(30, 4, '2250778599242', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(31, 4, '2250505569256', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(32, 4, '2250749270077', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(33, 4, '2250505500557', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(34, 4, '2250778599242', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(35, 4, '2250505569256', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(36, 4, '2250749270077', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(37, 4, '2250505500557', 'tesrrr', 'TICAFRIQUE', 'auto', 'failed', 1, 'Failed to send SMS', NULL, NULL, '2025-11-29 18:47:42', NULL, '2025-11-29 18:47:42', NULL),
(38, 5, '+2250749270077', 'TEST', 'TICAFRIQUE', 'auto', 'sent', 0, NULL, '2025-12-03 15:59:00', '2025-12-03 16:15:32', '2025-12-03 15:57:20', NULL, '2025-12-03 15:57:20', NULL),
(39, 5, '+2250505569256', 'TEST', 'TICAFRIQUE', 'auto', 'sent', 0, NULL, '2025-12-03 15:59:00', '2025-12-03 16:13:06', '2025-12-03 15:57:20', NULL, '2025-12-03 15:57:20', NULL),
(40, 6, '+2250749270077', 'TEST', 'AKADI', 'auto', 'sent', 0, NULL, NULL, '2025-12-04 09:52:32', '2025-12-04 09:38:53', 1, '2025-12-04 09:38:53', 1),
(41, 6, '+2250505569256', 'TEST', 'AKADI', 'auto', 'sent', 0, NULL, NULL, '2025-12-04 09:52:33', '2025-12-04 09:38:53', 1, '2025-12-04 09:38:53', 1),
(42, 7, '+2250749270077', 'TEST', 'AKADI', 'auto', 'sent', 0, NULL, '2025-12-09 14:32:00', '2025-12-09 14:58:30', '2025-12-09 14:33:04', 1, '2025-12-09 14:33:04', 1),
(43, 7, '+2250505569256', 'TEST', 'AKADI', 'auto', 'sent', 0, NULL, '2025-12-09 14:32:00', '2025-12-09 14:58:31', '2025-12-09 14:33:04', 1, '2025-12-09 14:33:04', 1),
(44, 9, '+2250749270077', 'test test', 'AKADI', 'auto', 'sent', 0, NULL, '2025-12-09 14:57:00', '2025-12-09 14:58:32', '2025-12-09 14:55:24', 1, '2025-12-09 14:55:24', 1),
(45, 9, '+2250505569256', 'test test', 'AKADI', 'auto', 'sent', 0, NULL, '2025-12-09 14:57:00', '2025-12-09 14:58:33', '2025-12-09 14:55:24', 1, '2025-12-09 14:55:24', 1),
(46, 10, '+2250749270077', 'TEST CAMPAGNES', 'AKADI', 'auto', 'sent', 0, NULL, '2025-12-09 15:03:00', '2025-12-09 15:07:25', '2025-12-09 15:02:00', 1, '2025-12-09 15:02:00', 1),
(47, 10, '+2250505569256', 'TEST CAMPAGNES', 'AKADI', 'auto', 'sent', 0, NULL, '2025-12-09 15:03:00', '2025-12-09 15:07:26', '2025-12-09 15:02:00', 1, '2025-12-09 15:02:00', 1),
(48, 11, '+2250749270077', '15H14', 'AKADI', 'auto', 'sent', 0, NULL, '2025-12-09 15:14:00', '2025-12-09 15:17:13', '2025-12-09 15:12:09', 1, '2025-12-09 15:12:09', 1),
(49, 11, '+2250505569256', '15H14', 'AKADI', 'auto', 'sent', 0, NULL, '2025-12-09 15:14:00', '2025-12-09 15:17:14', '2025-12-09 15:12:09', 1, '2025-12-09 15:12:09', 1),
(50, 12, '+2250749270077', '16H000', 'AKADI', 'auto', 'pending', 0, NULL, '2925-12-09 15:21:00', NULL, '2025-12-09 15:19:49', 1, '2025-12-09 15:19:49', 1),
(51, 12, '+2250505569256', '16H000', 'AKADI', 'auto', 'pending', 0, NULL, '2925-12-09 15:21:00', NULL, '2025-12-09 15:19:49', 1, '2025-12-09 15:19:49', 1),
(52, 13, '+2250749270077', 'TEST DOCS', 'AKADI', 'auto', 'sent', 0, NULL, '2025-12-09 15:47:00', '2025-12-09 15:48:13', '2025-12-09 15:44:57', 1, '2025-12-09 15:44:57', 1),
(53, 13, '+2250505569256', 'TEST DOCS', 'AKADI', 'auto', 'sent', 0, NULL, '2025-12-09 15:47:00', '2025-12-09 15:48:14', '2025-12-09 15:44:57', 1, '2025-12-09 15:44:57', 1),
(54, 15, '+2250749270077', 'RAS', 'AKADI', 'auto', 'pending', 0, NULL, '1985-12-12 12:12:00', NULL, '2025-12-10 21:52:36', 1, '2025-12-10 21:52:36', 1);

-- --------------------------------------------------------

--
-- Table structure for table `system_health_checks`
--

CREATE TABLE `system_health_checks` (
  `id` int UNSIGNED NOT NULL,
  `service_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_run_at` timestamp NULL DEFAULT NULL,
  `runs_today` int DEFAULT '0',
  `last_status` enum('ok','warning','error') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ok',
  `last_error` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_health_checks`
--

INSERT INTO `system_health_checks` (`id`, `service_name`, `last_run_at`, `runs_today`, `last_status`, `last_error`, `updated_at`) VALUES
(1, 'cron_job', '2025-12-20 09:51:31', 1, 'ok', NULL, '2025-12-20 09:51:31'),
(2, 'queue_worker', '2025-12-20 09:42:41', 15, 'ok', NULL, '2025-12-20 09:42:41');

-- --------------------------------------------------------

--
-- Table structure for table `translations`
--

CREATE TABLE `translations` (
  `id` bigint UNSIGNED NOT NULL,
  `language` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `module` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `updated_by` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `translation_history`
--

CREATE TABLE `translation_history` (
  `id` bigint UNSIGNED NOT NULL,
  `translation_id` int NOT NULL,
  `old_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `new_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `changed_by` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trusted_devices`
--

CREATE TABLE `trusted_devices` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `device_fingerprint` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` datetime DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trusted_devices`
--

INSERT INTO `trusted_devices` (`id`, `user_id`, `device_fingerprint`, `device_name`, `ip_address`, `user_agent`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'cfff86ffae1eeb8fefaca40f90e513c1fb8bf6ddb7b46cf19df7bb13241dec13', 'Google Chrome', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-12 13:01:42', '2026-01-07 05:02:05', '2025-12-08 05:02:05', '2025-12-08 05:02:05'),
(2, 1, '1c259709ea64920d8552663a5c8d0e8ef2846898257dcd77f37018a1ca95c154', 'Google Chrome', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-08 08:43:43', '2026-01-07 08:43:43', '2025-12-08 08:43:43', '2025-12-08 08:43:43'),
(3, 1, '3064b01f778a7206a84eaac3eafc6f737047944016d4ec792cedf5dc7f4c0b80', 'Google Chrome', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-13 10:28:23', '2026-01-12 05:53:02', '2025-12-13 05:53:02', '2025-12-13 05:53:02'),
(4, 1, '6d83822a912bae9328c83f8738bb0b69851b027640bdf77ab70cb81a6572d9d9', 'Google Chrome', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 23:11:00', '2026-01-13 06:31:05', '2025-12-14 06:31:05', '2025-12-14 06:31:05'),
(5, 1, '87020e317d53bd1fe9f53cb975611a0e4398df5519640d87d40433048549f8d7', 'Google Chrome', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 17:53:03', '2026-01-14 08:25:11', '2025-12-15 08:25:11', '2025-12-15 08:25:11');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `first_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `api_key` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `api_key_created_at` datetime DEFAULT NULL,
  `last_login_ip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `device_fingerprint` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `created_by`, `username`, `email`, `password`, `status`, `first_name`, `last_name`, `avatar`, `is_active`, `is_verified`, `created_at`, `updated_at`, `api_key`, `api_key_created_at`, `last_login_ip`, `last_login_at`, `device_fingerprint`) VALUES
(1, NULL, 'admin', 'admin@example.com', '$2y$10$Rb2amPRz6cmjGpck5vvnmudrZjYwKns10TKiB3lYHMmITiofHIpra', 'active', 'akpa', 'lath', NULL, 1, 0, '2025-11-25 17:27:10', '2025-12-13 01:19:43', '95edb056eb4292096bac888447dc2b7ffc98901a7d78cc81266b493591a430ea', '2025-12-02 10:53:20', '::1', '2025-12-21 17:53:03', NULL),
(3, NULL, 'sando', 'sando@ticafrique.ci', '$2y$10$L/1CBhewnU/lBbTrj9RwuOK8ibm.XP.Zrsh08Jvc/whquiXYMETXS', 'active', 'KONE', 'Sando', NULL, 1, 0, '2025-11-25 11:57:10', '2025-12-08 09:06:28', '', '2025-12-02 10:53:20', '::1', '2025-12-08 20:59:19', NULL),
(4, NULL, 'client', 'client@test.test', '$2y$10$Mw0EjBuz1q5y.Gmylbo9c.4GWqJy7xx.dgxEY0sWkFY4tyEvFW9bK', 'active', NULL, NULL, NULL, 1, 0, '2025-12-12 13:31:47', '2025-12-12 13:31:47', NULL, NULL, '::1', '2025-12-19 12:32:15', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_mfa_setup`
--

CREATE TABLE `user_mfa_setup` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `method_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_verified` tinyint(1) DEFAULT '0',
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_mfa_setup`
--

INSERT INTO `user_mfa_setup` (`id`, `user_id`, `method_type`, `secret`, `is_verified`, `last_used_at`, `created_at`, `updated_at`) VALUES
(7, 1, 'sms', '+2250749270077', 1, '2025-12-15 08:25:11', '2025-12-07 18:22:56', '2025-12-08 04:57:49');

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
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_notification_preferences`
--

INSERT INTO `user_notification_preferences` (`user_id`, `channels_enabled`, `dnd_enabled`, `dnd_from`, `dnd_to`, `timezone`, `email_opt_in`, `sms_opt_in`, `push_opt_in`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(1, '[\"email\"]', 0, NULL, NULL, 'UTC', 1, 1, 1, '2025-11-26 12:31:58', NULL, '2025-11-26 12:31:58', NULL);

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
(4, 5, '2025-12-12 13:31:54', '2025-12-12 13:31:54'),
(3, 1, '2025-12-13 10:14:51', '2025-12-13 10:14:51'),
(1, 1, '2025-12-13 10:14:57', '2025-12-13 10:14:57');

-- --------------------------------------------------------

--
-- Table structure for table `user_sender_names`
--

CREATE TABLE `user_sender_names` (
  `id` int NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `sender_name_id` int NOT NULL,
  `assigned_by` bigint UNSIGNED DEFAULT NULL COMMENT 'Admin who assigned this sender name',
  `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_sender_names`
--

INSERT INTO `user_sender_names` (`id`, `user_id`, `sender_name_id`, `assigned_by`, `assigned_at`, `created_by`) VALUES
(1, 1, 1, 1, '2025-12-03 16:41:49', NULL),
(4, 1, 7, 1, '2025-12-05 13:48:08', NULL),
(5, 1, 8, 1, '2025-12-15 10:45:02', NULL),
(6, 3, 8, 1, '2025-12-15 10:45:02', NULL),
(9, 1, 19, 1, '2025-12-15 11:12:55', NULL),
(10, 3, 19, 1, '2025-12-15 11:12:55', NULL),
(13, 1, 3, 1, '2025-12-15 11:14:46', NULL),
(14, 3, 3, 1, '2025-12-15 11:14:46', NULL),
(17, 1, 2, 1, '2025-12-15 11:19:30', NULL),
(18, 3, 2, 1, '2025-12-15 11:19:30', NULL),
(19, 1, 16, 1, '2025-12-15 11:19:51', NULL),
(20, 3, 16, 1, '2025-12-15 11:19:51', NULL),
(27, 1, 13, 1, '2025-12-15 11:27:47', NULL),
(28, 3, 13, 1, '2025-12-15 11:27:47', NULL),
(37, 1, 20, 1, '2025-12-15 11:39:38', NULL),
(38, 3, 20, 1, '2025-12-15 11:39:38', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `balance` decimal(15,2) DEFAULT '0.00',
  `currency` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'XOF',
  `status` enum('active','frozen','suspended') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`id`, `user_id`, `balance`, `currency`, `status`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(1, 1, '929338.00', 'XOF', 'active', '2025-11-29 17:24:53', NULL, '2025-11-29 17:24:53', 1),
(2, 3, '0.00', 'XOF', 'active', NULL, 3, NULL, 3),
(3, 4, '9860.00', 'XOF', 'active', NULL, 4, NULL, 4);

-- --------------------------------------------------------

--
-- Table structure for table `wallet_gateways`
--

CREATE TABLE `wallet_gateways` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_key` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `api_secret` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `merchant_id` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `currency` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'XOF',
  `transaction_fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `configuration` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallet_gateways`
--

INSERT INTO `wallet_gateways` (`id`, `name`, `provider_code`, `api_url`, `api_key`, `api_secret`, `merchant_id`, `is_active`, `is_default`, `currency`, `transaction_fee`, `configuration`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(1, 'PayDunya', 'paydunya', 'https://app.paydunya.com/api/v1', '', '', '', 0, 0, 'XOF', '2.50', '{\"mode\": \"live\", \"supported_methods\": [\"orange_money\", \"mtn\", \"moov\", \"card\"]}', '2025-11-28 10:21:47', NULL, '2025-11-28 10:21:47', NULL),
(2, 'CinetPay', 'cinetpay', 'https://api-checkout.cinetpay.com/v2', '', '', '', 0, 1, 'XOF', '3.00', '{\"mode\": \"PRODUCTION\", \"supported_methods\": [\"ORANGE_MONEY_CI\", \"MOOV_CI\", \"MTN_CI\", \"WAVE_CI\"]}', '2025-11-28 10:21:47', NULL, '2025-11-28 10:21:47', NULL),
(3, 'Orange Money Côte d\'Ivoire', 'orange_money_ci', 'https://api.orange.com/orange-money-webpay/dev/v1', '', '', '', 1, 0, 'XOF', '1.50', '{\"auth_type\": \"oauth2\", \"country_code\": \"CI\"}', '2025-11-28 10:21:47', NULL, '2025-11-29 18:18:23', NULL),
(4, 'Wave Côte d\'Ivoire', 'wave_ci', 'https://api.wave.com/v1', '', '', '', 1, 0, 'XOF', '1.00', '{\"country_code\": \"CI\", \"qr_code_enabled\": true}', '2025-11-28 10:21:47', NULL, '2025-11-29 18:18:23', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wallet_topup_requests`
--

CREATE TABLE `wallet_topup_requests` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `wallet_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'XOF',
  `payment_method` enum('gateway','cash','mobile_money','bank_transfer','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'gateway',
  `gateway_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Reference to wallet_gateways table if payment_method = gateway',
  `gateway_transaction_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Transaction ID from payment gateway',
  `gateway_response` json DEFAULT NULL COMMENT 'Full response from payment gateway',
  `gateway_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Status from gateway: SUCCESS, PENDING, FAILED',
  `status` enum('pending','processing','approved','rejected','completed','failed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'User notes or payment reference',
  `proof_of_payment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'File path to payment receipt/proof (for offline payments)',
  `reviewed_by` bigint UNSIGNED DEFAULT NULL COMMENT 'Admin user ID who reviewed the request',
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `admin_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Admin notes/reason for approval or rejection',
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `completed_at` timestamp NULL DEFAULT NULL COMMENT 'When the credit was actually added to wallet'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Wallet top-up requests with gateway integration and admin approval workflow';

--
-- Dumping data for table `wallet_topup_requests`
--

INSERT INTO `wallet_topup_requests` (`id`, `user_id`, `wallet_id`, `amount`, `currency`, `payment_method`, `gateway_id`, `gateway_transaction_id`, `gateway_response`, `gateway_status`, `status`, `notes`, `proof_of_payment`, `reviewed_by`, `reviewed_at`, `admin_notes`, `ip_address`, `user_agent`, `created_at`, `updated_at`, `completed_at`) VALUES
(1, 1, 1, '100.00', 'XOF', 'gateway', NULL, NULL, NULL, NULL, 'completed', '', NULL, 1, '2025-12-11 05:56:43', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-11 05:37:42', '2025-12-11 05:37:42', '2025-12-11 05:56:43'),
(2, 1, 1, '500.00', 'XOF', 'cash', NULL, NULL, NULL, NULL, 'completed', 'REASF', NULL, 1, '2025-12-11 15:05:18', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-11 11:30:52', '2025-12-11 11:30:52', '2025-12-11 15:05:18'),
(3, 1, 1, '199999.00', 'XOF', 'mobile_money', NULL, NULL, NULL, NULL, 'completed', 'RAS', NULL, 1, '2025-12-11 15:05:13', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-11 15:03:55', '2025-12-11 15:03:55', '2025-12-11 15:05:13'),
(4, 4, 3, '10000.00', 'XOF', 'cash', NULL, NULL, NULL, NULL, 'completed', '', NULL, 4, '2025-12-14 06:54:30', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-14 06:52:56', '2025-12-14 06:52:56', '2025-12-14 06:54:30'),
(5, 1, 1, '100000.00', 'XOF', 'mobile_money', NULL, NULL, NULL, NULL, 'pending', 'RAS', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 15:50:31', '2025-12-20 15:50:31', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wallet_transactions`
--

CREATE TABLE `wallet_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `wallet_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `type` enum('credit','debit','refund') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'credit',
  `amount` decimal(15,2) NOT NULL,
  `balance_before` decimal(15,2) NOT NULL,
  `balance_after` decimal(15,2) NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','completed','failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'completed',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallet_transactions`
--

INSERT INTO `wallet_transactions` (`id`, `wallet_id`, `user_id`, `type`, `amount`, `balance_before`, `balance_after`, `description`, `metadata`, `status`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(1, 1, 1, 'credit', '500.00', '1000.00', '1500.00', 'Credit added', NULL, 'completed', NULL, NULL, NULL, NULL),
(2, 1, 1, 'debit', '15.00', '1500.00', '1485.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(3, 1, 1, 'debit', '15.00', '1485.00', '1470.00', 'SMS Charge: ID #1', NULL, 'completed', NULL, NULL, NULL, NULL),
(4, 1, 1, 'debit', '35.00', '1470.00', '1435.00', 'SMS to +225 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(5, 1, 1, 'debit', '35.00', '1435.00', '1400.00', 'SMS Charge: ID #2', NULL, 'completed', NULL, NULL, NULL, NULL),
(6, 1, 1, 'debit', '35.00', '1400.00', '1365.00', 'SMS to +225 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(7, 1, 1, 'debit', '35.00', '1365.00', '1330.00', 'SMS Charge: ID #3', NULL, 'completed', NULL, NULL, NULL, NULL),
(8, 1, 1, 'debit', '35.00', '1330.00', '1295.00', 'SMS to +225 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(9, 1, 1, 'debit', '35.00', '1295.00', '1260.00', 'SMS Charge: ID #4', NULL, 'completed', NULL, NULL, NULL, NULL),
(10, 1, 1, 'credit', '500.00', '1260.00', '1760.00', 'Test Credit', NULL, 'completed', NULL, NULL, NULL, NULL),
(11, 1, 1, 'debit', '35.00', '1760.00', '1725.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(12, 1, 1, 'debit', '35.00', '1725.00', '1690.00', 'SMS Charge: ID #5', NULL, 'completed', NULL, NULL, NULL, NULL),
(13, 1, 1, 'debit', '35.00', '1690.00', '1655.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(14, 1, 1, 'debit', '35.00', '1655.00', '1620.00', 'SMS Charge: ID #6', NULL, 'completed', NULL, NULL, NULL, NULL),
(15, 1, 1, 'debit', '35.00', '1620.00', '1585.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(16, 1, 1, 'debit', '35.00', '1585.00', '1550.00', 'SMS Charge: ID #7', NULL, 'completed', NULL, NULL, NULL, NULL),
(17, 1, 1, 'debit', '35.00', '1550.00', '1515.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(18, 1, 1, 'debit', '35.00', '1515.00', '1480.00', 'SMS Charge: ID #8', NULL, 'completed', NULL, NULL, NULL, NULL),
(19, 1, 1, 'debit', '35.00', '1480.00', '1445.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(20, 1, 1, 'debit', '35.00', '1445.00', '1410.00', 'SMS Charge: ID #9', NULL, 'completed', NULL, NULL, NULL, NULL),
(21, 1, 1, 'debit', '35.00', '1410.00', '1375.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(22, 1, 1, 'debit', '35.00', '1375.00', '1340.00', 'SMS Charge: ID #10', NULL, 'completed', NULL, NULL, NULL, NULL),
(23, 1, 1, 'debit', '35.00', '1340.00', '1305.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(24, 1, 1, 'debit', '35.00', '1305.00', '1270.00', 'SMS Charge: ID #11', NULL, 'completed', NULL, NULL, NULL, NULL),
(25, 1, 1, 'debit', '35.00', '1270.00', '1235.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(26, 1, 1, 'debit', '35.00', '1235.00', '1200.00', 'SMS Charge: ID #12', NULL, 'completed', NULL, NULL, NULL, NULL),
(27, 1, 1, 'debit', '35.00', '1200.00', '1165.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(28, 1, 1, 'debit', '35.00', '1165.00', '1130.00', 'SMS Charge: ID #13', NULL, 'completed', NULL, NULL, NULL, NULL),
(29, 1, 1, 'credit', '35.00', '1130.00', '1165.00', 'Refund SMS #13', NULL, 'completed', NULL, NULL, NULL, NULL),
(30, 1, 1, 'credit', '35.00', '1165.00', '1200.00', 'Refund SMS: ID #13', NULL, 'completed', NULL, NULL, NULL, NULL),
(31, 1, 1, 'debit', '35.00', '1200.00', '1165.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(32, 1, 1, 'debit', '35.00', '1165.00', '1130.00', 'SMS Charge: ID #14', NULL, 'completed', NULL, NULL, NULL, NULL),
(33, 1, 1, 'credit', '35.00', '1130.00', '1165.00', 'Refund SMS #14', NULL, 'completed', NULL, NULL, NULL, NULL),
(34, 1, 1, 'credit', '35.00', '1165.00', '1200.00', 'Refund SMS: ID #14', NULL, 'completed', NULL, NULL, NULL, NULL),
(35, 1, 1, 'debit', '35.00', '1200.00', '1165.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(36, 1, 1, 'debit', '35.00', '1165.00', '1130.00', 'SMS Charge: ID #15', NULL, 'completed', NULL, NULL, NULL, NULL),
(37, 1, 1, 'credit', '35.00', '1130.00', '1165.00', 'Refund SMS #15', NULL, 'completed', NULL, NULL, NULL, NULL),
(38, 1, 1, 'credit', '35.00', '1165.00', '1200.00', 'Refund SMS: ID #15', NULL, 'completed', NULL, NULL, NULL, NULL),
(39, 1, 1, 'debit', '35.00', '1200.00', '1165.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(40, 1, 1, 'debit', '35.00', '1165.00', '1130.00', 'SMS Charge: ID #16', NULL, 'completed', NULL, NULL, NULL, NULL),
(41, 1, 1, 'debit', '35.00', '1130.00', '1095.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(42, 1, 1, 'debit', '35.00', '1095.00', '1060.00', 'SMS Charge: ID #17', NULL, 'completed', NULL, NULL, NULL, NULL),
(43, 1, 1, 'debit', '35.00', '1060.00', '1025.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(44, 1, 1, 'debit', '35.00', '1025.00', '990.00', 'SMS Charge: ID #18', NULL, 'completed', NULL, NULL, NULL, NULL),
(45, 1, 1, 'debit', '35.00', '850.00', '815.00', 'SMS to +2250778599242 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(46, 1, 1, 'debit', '35.00', '815.00', '780.00', 'SMS Charge: ID #23', NULL, 'completed', NULL, 1, NULL, 1),
(47, 1, 1, 'debit', '20.00', '780.00', '760.00', 'SMS to +2250505569256 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(48, 1, 1, 'debit', '20.00', '760.00', '740.00', 'SMS Charge: ID #24', NULL, 'completed', NULL, 1, NULL, 1),
(49, 1, 1, 'debit', '35.00', '740.00', '705.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(50, 1, 1, 'debit', '35.00', '705.00', '670.00', 'SMS Charge: ID #25', NULL, 'completed', NULL, 1, NULL, 1),
(51, 1, 1, 'debit', '35.00', '670.00', '635.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(52, 1, 1, 'debit', '35.00', '635.00', '600.00', 'SMS Charge: ID #26', NULL, 'completed', NULL, 1, NULL, 1),
(53, 1, 1, 'credit', '100.00', '600.00', '700.00', 'RAS', NULL, 'completed', NULL, 1, NULL, 1),
(54, 1, 1, 'debit', '35.00', '700.00', '665.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(55, 1, 1, 'debit', '35.00', '665.00', '630.00', 'SMS Charge: ID #27', NULL, 'completed', NULL, 1, NULL, 1),
(56, 1, 1, 'credit', '35.00', '630.00', '665.00', 'Refund SMS #27', NULL, 'completed', NULL, 1, NULL, 1),
(57, 1, 1, 'credit', '35.00', '665.00', '700.00', 'Refund SMS: ID #27', NULL, 'completed', NULL, 1, NULL, 1),
(58, 1, 1, 'debit', '35.00', '700.00', '665.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(59, 1, 1, 'debit', '35.00', '665.00', '630.00', 'SMS Charge: ID #28', NULL, 'completed', NULL, 1, NULL, 1),
(60, 1, 1, 'debit', '35.00', '630.00', '595.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(61, 1, 1, 'debit', '35.00', '595.00', '560.00', 'SMS Charge: ID #29', NULL, 'completed', NULL, 1, NULL, 1),
(62, 1, 1, 'debit', '35.00', '560.00', '525.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(63, 1, 1, 'debit', '35.00', '525.00', '490.00', 'SMS Charge: ID #30', NULL, 'completed', NULL, 1, NULL, 1),
(64, 1, 1, 'credit', '35.00', '490.00', '525.00', 'Refund SMS #30', NULL, 'completed', NULL, 1, NULL, 1),
(65, 1, 1, 'credit', '35.00', '525.00', '560.00', 'Refund SMS: ID #30', NULL, 'completed', NULL, 1, NULL, 1),
(66, 1, 1, 'debit', '35.00', '560.00', '525.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(67, 1, 1, 'debit', '35.00', '525.00', '490.00', 'SMS Charge: ID #31', NULL, 'completed', NULL, 1, NULL, 1),
(68, 1, 1, 'debit', '35.00', '490.00', '455.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(69, 1, 1, 'debit', '35.00', '455.00', '420.00', 'SMS Charge: ID #32', NULL, 'completed', NULL, NULL, NULL, NULL),
(70, 1, 1, 'debit', '35.00', '420.00', '385.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(71, 1, 1, 'debit', '35.00', '385.00', '350.00', 'SMS Charge: ID #33', NULL, 'completed', NULL, NULL, NULL, NULL),
(72, 1, 1, 'debit', '35.00', '350.00', '315.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(73, 1, 1, 'debit', '35.00', '315.00', '280.00', 'SMS Charge: ID #34', NULL, 'completed', NULL, NULL, NULL, NULL),
(74, 1, 1, 'debit', '35.00', '280.00', '245.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(75, 1, 1, 'debit', '35.00', '245.00', '210.00', 'SMS Charge: ID #35', NULL, 'completed', NULL, NULL, NULL, NULL),
(76, 1, 1, 'debit', '35.00', '210.00', '175.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(77, 1, 1, 'debit', '35.00', '175.00', '140.00', 'SMS Charge: ID #36', NULL, 'completed', NULL, NULL, NULL, NULL),
(78, 1, 1, 'debit', '35.00', '140.00', '105.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(79, 1, 1, 'debit', '35.00', '105.00', '70.00', 'SMS Charge: ID #37', NULL, 'completed', NULL, NULL, NULL, NULL),
(80, 1, 1, 'debit', '35.00', '70.00', '35.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(81, 1, 1, 'debit', '35.00', '35.00', '0.00', 'SMS Charge: ID #38', NULL, 'completed', NULL, NULL, NULL, NULL),
(82, 1, 1, 'credit', '200000.00', '0.00', '200000.00', 'Credit added', NULL, 'completed', NULL, 1, NULL, 1),
(83, 1, 1, 'debit', '35.00', '200000.00', '199965.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(84, 1, 1, 'debit', '35.00', '199965.00', '199930.00', 'SMS Charge: ID #39', NULL, 'completed', NULL, NULL, NULL, NULL),
(85, 1, 1, 'debit', '35.00', '199930.00', '199895.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(86, 1, 1, 'debit', '35.00', '199895.00', '199860.00', 'SMS Charge: ID #40', NULL, 'completed', NULL, NULL, NULL, NULL),
(87, 1, 1, 'debit', '20.00', '199860.00', '199840.00', 'SMS to +2250505569256 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(88, 1, 1, 'debit', '20.00', '199840.00', '199820.00', 'SMS Charge: ID #41', NULL, 'completed', NULL, NULL, NULL, NULL),
(89, 1, 1, 'debit', '35.00', '199820.00', '199785.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(90, 1, 1, 'debit', '35.00', '199785.00', '199750.00', 'SMS Charge: ID #42', NULL, 'completed', NULL, 1, NULL, 1),
(91, 1, 1, 'debit', '20.00', '199750.00', '199730.00', 'SMS to +2250505569256 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(92, 1, 1, 'debit', '20.00', '199730.00', '199710.00', 'SMS Charge: ID #43', NULL, 'completed', NULL, 1, NULL, 1),
(93, 1, 1, 'debit', '35.00', '199710.00', '199675.00', 'SMS to +2250549270077 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(94, 1, 1, 'debit', '35.00', '199675.00', '199640.00', 'SMS Charge: ID #44', NULL, 'completed', NULL, 1, NULL, 1),
(95, 1, 1, 'debit', '35.00', '199640.00', '199605.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(96, 1, 1, 'debit', '35.00', '199605.00', '199570.00', 'SMS Charge: ID #45', NULL, 'completed', NULL, 1, NULL, 1),
(97, 1, 1, 'debit', '35.00', '199570.00', '199535.00', 'SMS to +2250709876543 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(98, 1, 1, 'debit', '35.00', '199535.00', '199500.00', 'SMS Charge: ID #46', NULL, 'completed', NULL, NULL, NULL, NULL),
(99, 1, 1, 'debit', '35.00', '199500.00', '199465.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(100, 1, 1, 'debit', '35.00', '199465.00', '199430.00', 'SMS Charge: ID #47', NULL, 'completed', NULL, NULL, NULL, NULL),
(101, 1, 1, 'credit', '10000.00', '199430.00', '209430.00', 'Top-up via payment gateway (paypal)', NULL, 'completed', NULL, 1, NULL, 1),
(102, 1, 1, 'credit', '19999.00', '209430.00', '229429.00', 'Top-up via payment gateway (paypal)', NULL, 'completed', NULL, 1, NULL, 1),
(103, 1, 1, 'credit', '1000.00', '229429.00', '230429.00', 'Top-up via payment gateway (paypal)', NULL, 'completed', NULL, 1, NULL, 1),
(104, 1, 1, 'credit', '500000.00', '230429.00', '730429.00', 'Top-up via payment gateway (paypal)', NULL, 'completed', NULL, 1, NULL, 1),
(105, 1, 1, 'credit', '100.00', '730429.00', '730529.00', 'Recharge manuelle approuvée (Demande #1)', NULL, 'completed', NULL, 1, NULL, 1),
(106, 1, 1, 'debit', '35.00', '730529.00', '730494.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(107, 1, 1, 'debit', '35.00', '730494.00', '730459.00', 'SMS Charge: ID #48', NULL, 'completed', NULL, 1, NULL, 1),
(108, 1, 1, 'debit', '35.00', '730459.00', '730424.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(109, 1, 1, 'debit', '35.00', '730424.00', '730389.00', 'SMS Charge: ID #49', NULL, 'completed', NULL, 1, NULL, 1),
(110, 1, 1, 'debit', '20.00', '730389.00', '730369.00', 'SMS to +2250505569256 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(111, 1, 1, 'debit', '20.00', '730369.00', '730349.00', 'SMS Charge: ID #50', NULL, 'completed', NULL, 1, NULL, 1),
(112, 1, 1, 'debit', '35.00', '730349.00', '730314.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(113, 1, 1, 'debit', '35.00', '730314.00', '730279.00', 'SMS Charge: ID #51', NULL, 'completed', NULL, 1, NULL, 1),
(114, 1, 1, 'debit', '20.00', '730279.00', '730259.00', 'SMS to +2250505569256 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(115, 1, 1, 'debit', '20.00', '730259.00', '730239.00', 'SMS Charge: ID #52', NULL, 'completed', NULL, 1, NULL, 1),
(116, 1, 1, 'debit', '35.00', '730239.00', '730204.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(117, 1, 1, 'debit', '35.00', '730204.00', '730169.00', 'SMS Charge: ID #53', NULL, 'completed', NULL, 1, NULL, 1),
(118, 1, 1, 'debit', '35.00', '730169.00', '730134.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(119, 1, 1, 'debit', '35.00', '730134.00', '730099.00', 'SMS Charge: ID #54', NULL, 'completed', NULL, 1, NULL, 1),
(120, 1, 1, 'debit', '20.00', '730099.00', '730079.00', 'SMS to +2250505569256 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(121, 1, 1, 'debit', '20.00', '730079.00', '730059.00', 'SMS Charge: ID #55', NULL, 'completed', NULL, 1, NULL, 1),
(122, 1, 1, 'credit', '199999.00', '730059.00', '930058.00', 'Recharge manuelle approuvée (Demande #3)', NULL, 'completed', NULL, 1, NULL, 1),
(123, 1, 1, 'credit', '500.00', '930058.00', '930558.00', 'Recharge manuelle approuvée (Demande #2)', NULL, 'completed', NULL, 1, NULL, 1),
(124, 1, 1, 'debit', '35.00', '930558.00', '930523.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(125, 1, 1, 'debit', '35.00', '930523.00', '930488.00', 'SMS Charge: ID #56', NULL, 'completed', NULL, 1, NULL, 1),
(126, 1, 1, 'debit', '20.00', '930488.00', '930468.00', 'SMS to +2250505569256 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(127, 1, 1, 'debit', '20.00', '930468.00', '930448.00', 'SMS Charge: ID #57', NULL, 'completed', NULL, 1, NULL, 1),
(128, 1, 1, 'debit', '35.00', '930448.00', '930413.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(129, 1, 1, 'debit', '35.00', '930413.00', '930378.00', 'SMS Charge: ID #58', NULL, 'completed', NULL, 1, NULL, 1),
(130, 1, 1, 'debit', '20.00', '930378.00', '930358.00', 'SMS to +2250505569256 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(131, 1, 1, 'debit', '20.00', '930358.00', '930338.00', 'SMS Charge: ID #59', NULL, 'completed', NULL, 1, NULL, 1),
(132, 1, 1, 'debit', '35.00', '930338.00', '930303.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(133, 1, 1, 'debit', '35.00', '930303.00', '930268.00', 'SMS Charge: ID #60', NULL, 'completed', NULL, 1, NULL, 1),
(134, 1, 1, 'debit', '20.00', '930268.00', '930248.00', 'SMS to +2250505569256 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(135, 1, 1, 'debit', '20.00', '930248.00', '930228.00', 'SMS Charge: ID #61', NULL, 'completed', NULL, 1, NULL, 1),
(136, 1, 1, 'debit', '35.00', '930228.00', '930193.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(137, 1, 1, 'debit', '35.00', '930193.00', '930158.00', 'SMS Charge: ID #62', NULL, 'completed', NULL, 1, NULL, 1),
(138, 1, 1, 'debit', '20.00', '930158.00', '930138.00', 'SMS to +2250505569256 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(139, 1, 1, 'debit', '20.00', '930138.00', '930118.00', 'SMS Charge: ID #63', NULL, 'completed', NULL, 1, NULL, 1),
(140, 1, 1, 'debit', '35.00', '930118.00', '930083.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(141, 1, 1, 'debit', '35.00', '930083.00', '930048.00', 'SMS Charge: ID #64', NULL, 'completed', NULL, NULL, NULL, NULL),
(142, 1, 1, 'debit', '35.00', '930048.00', '930013.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(143, 1, 1, 'debit', '35.00', '930013.00', '929978.00', 'SMS Charge: ID #65', NULL, 'completed', NULL, NULL, NULL, NULL),
(144, 1, 1, 'debit', '35.00', '929978.00', '929943.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(145, 1, 1, 'debit', '35.00', '929943.00', '929908.00', 'SMS Charge: ID #66', NULL, 'completed', NULL, NULL, NULL, NULL),
(146, 1, 1, 'debit', '35.00', '929908.00', '929873.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(147, 1, 1, 'debit', '35.00', '929873.00', '929838.00', 'SMS Charge: ID #67', NULL, 'completed', NULL, NULL, NULL, NULL),
(148, 3, 4, 'credit', '10000.00', '0.00', '10000.00', 'Recharge manuelle approuvée (Demande #4)', NULL, 'completed', NULL, 4, NULL, 4),
(149, 1, 1, 'debit', '35.00', '929838.00', '929803.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(150, 1, 1, 'debit', '35.00', '929803.00', '929768.00', 'SMS Charge: ID #68', NULL, 'completed', NULL, NULL, NULL, NULL),
(151, 1, 1, 'debit', '35.00', '929768.00', '929733.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(152, 1, 1, 'debit', '35.00', '929733.00', '929698.00', 'SMS Charge: ID #69', NULL, 'completed', NULL, NULL, NULL, NULL),
(153, 1, 1, 'debit', '35.00', '929698.00', '929663.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(154, 1, 1, 'debit', '35.00', '929663.00', '929628.00', 'SMS Charge: ID #70', NULL, 'completed', NULL, 1, NULL, 1),
(155, 1, 1, 'debit', '35.00', '929628.00', '929593.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(156, 1, 1, 'debit', '35.00', '929593.00', '929558.00', 'SMS Charge: ID #71', NULL, 'completed', NULL, 1, NULL, 1),
(157, 1, 1, 'debit', '35.00', '929558.00', '929523.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(158, 1, 1, 'debit', '35.00', '929523.00', '929488.00', 'SMS Charge: ID #72', NULL, 'completed', NULL, 1, NULL, 1),
(159, 3, 4, 'debit', '35.00', '10000.00', '9965.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(160, 3, 4, 'debit', '35.00', '9965.00', '9930.00', 'SMS Charge: ID #73', NULL, 'completed', NULL, NULL, NULL, NULL),
(161, 3, 4, 'debit', '35.00', '9930.00', '9895.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, NULL, NULL, NULL),
(162, 3, 4, 'debit', '35.00', '9895.00', '9860.00', 'SMS Charge: ID #74', NULL, 'completed', NULL, NULL, NULL, NULL),
(163, 1, 1, 'debit', '20.00', '929488.00', '929468.00', 'SMS to +2250505569256 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(164, 1, 1, 'debit', '20.00', '929468.00', '929448.00', 'SMS Charge: ID #75', NULL, 'completed', NULL, 1, NULL, 1),
(165, 1, 1, 'debit', '20.00', '929448.00', '929428.00', 'SMS to +2250505569256 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(166, 1, 1, 'debit', '20.00', '929428.00', '929408.00', 'SMS Charge: ID #76', NULL, 'completed', NULL, 1, NULL, 1),
(167, 1, 1, 'debit', '35.00', '929408.00', '929373.00', 'SMS to +2250749270077 (1 segments)', NULL, 'completed', NULL, 1, NULL, 1),
(168, 1, 1, 'debit', '35.00', '929373.00', '929338.00', 'SMS Charge: ID #77', NULL, 'completed', NULL, 1, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `webhooks`
--

CREATE TABLE `webhooks` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `events` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `headers` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `retry_count` int NOT NULL DEFAULT '3',
  `timeout` int NOT NULL DEFAULT '30',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `webhook_logs`
--

CREATE TABLE `webhook_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `webhook_id` int NOT NULL,
  `payload` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `response` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `http_code` int DEFAULT NULL,
  `error` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `duration` float(8,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `workflows`
--

CREATE TABLE `workflows` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `trigger_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `steps` json NOT NULL,
  `status` enum('draft','active','paused','archived') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `total_executions` int NOT NULL DEFAULT '0',
  `completed_executions` int NOT NULL DEFAULT '0',
  `last_run_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `workflow_executions`
--

CREATE TABLE `workflow_executions` (
  `id` bigint UNSIGNED NOT NULL,
  `workflow_id` bigint NOT NULL,
  `contact_id` bigint NOT NULL,
  `current_step` int NOT NULL DEFAULT '0',
  `status` enum('pending','running','completed','failed','stopped') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `next_step_at` datetime DEFAULT NULL,
  `execution_data` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
-- Indexes for table `api_keys`
--
ALTER TABLE `api_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `idx_api_keys_created_by` (`created_by`),
  ADD KEY `idx_api_keys_updated_by` (`updated_by`),
  ADD KEY `idx_api_keys_deleted_by` (`deleted_by`),
  ADD KEY `idx_api_keys_deleted_at` (`deleted_at`);

--
-- Indexes for table `api_request_logs`
--
ALTER TABLE `api_request_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_api_request_logs_created_by` (`created_by`);

--
-- Indexes for table `auth_logs`
--
ALTER TABLE `auth_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `auth_settings`
--
ALTER TABLE `auth_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Indexes for table `backups`
--
ALTER TABLE `backups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_backups_created_by` (`created_by`),
  ADD KEY `idx_backups_updated_by` (`updated_by`);

--
-- Indexes for table `cache_config`
--
ALTER TABLE `cache_config`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cache_config_created_by` (`created_by`),
  ADD KEY `idx_cache_config_updated_by` (`updated_by`);

--
-- Indexes for table `campaign_logs`
--
ALTER TABLE `campaign_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `campaign_logs_campaign_id_index` (`campaign_id`),
  ADD KEY `campaign_logs_campaign_type_index` (`campaign_type`),
  ADD KEY `campaign_logs_channel_index` (`channel`),
  ADD KEY `campaign_logs_contact_id_index` (`contact_id`),
  ADD KEY `campaign_logs_status_index` (`status`),
  ADD KEY `campaign_logs_created_at_index` (`created_at`),
  ADD KEY `idx_campaign_logs_created_by` (`created_by`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD KEY `phone_2` (`phone`),
  ADD KEY `email` (`email`),
  ADD KEY `is_active` (`is_active`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `idx_contacts_created_by` (`created_by`),
  ADD KEY `idx_contacts_updated_by` (`updated_by`),
  ADD KEY `idx_contacts_deleted_by` (`deleted_by`),
  ADD KEY `idx_contacts_deleted_at` (`deleted_at`);

--
-- Indexes for table `contact_field_definitions`
--
ALTER TABLE `contact_field_definitions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `slug_2` (`slug`),
  ADD KEY `order_num` (`order_field`),
  ADD KEY `idx_contact_field_definitions_created_by` (`created_by`),
  ADD KEY `idx_contact_field_definitions_updated_by` (`updated_by`);

--
-- Indexes for table `cron_logs`
--
ALTER TABLE `cron_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_task_id` (`task_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_started_at` (`started_at`),
  ADD KEY `idx_cron_logs_created_by` (`created_by`);

--
-- Indexes for table `cron_tasks`
--
ALTER TABLE `cron_tasks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_enabled` (`enabled`),
  ADD KEY `idx_next_run_at` (`next_run_at`),
  ADD KEY `idx_module` (`module`),
  ADD KEY `idx_cron_tasks_created_by` (`created_by`),
  ADD KEY `idx_cron_tasks_updated_by` (`updated_by`);

--
-- Indexes for table `email_campaigns`
--
ALTER TABLE `email_campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_campaigns_status_index` (`status`),
  ADD KEY `email_campaigns_created_by_index` (`created_by`),
  ADD KEY `email_campaigns_scheduled_at_index` (`scheduled_at`),
  ADD KEY `idx_email_campaigns_updated_by` (`updated_by`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_logs_message_id_index` (`message_id`),
  ADD KEY `email_logs_event_type_index` (`event_type`),
  ADD KEY `email_logs_created_at_index` (`created_at`),
  ADD KEY `idx_email_logs_created_by` (`created_by`);

--
-- Indexes for table `email_messages`
--
ALTER TABLE `email_messages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `message_id` (`message_id`),
  ADD KEY `email_messages_campaign_id_index` (`campaign_id`),
  ADD KEY `email_messages_user_id_index` (`user_id`),
  ADD KEY `email_messages_to_email_index` (`to_email`),
  ADD KEY `email_messages_status_index` (`status`),
  ADD KEY `email_messages_sent_at_index` (`sent_at`),
  ADD KEY `idx_email_messages_created_by` (`created_by`);

--
-- Indexes for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_templates_is_active_index` (`is_active`),
  ADD KEY `email_templates_category_index` (`category`),
  ADD KEY `idx_email_templates_created_by` (`created_by`),
  ADD KEY `idx_email_templates_updated_by` (`updated_by`),
  ADD KEY `idx_email_templates_deleted_by` (`deleted_by`),
  ADD KEY `idx_email_templates_deleted_at` (`deleted_at`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_queue` (`queue`),
  ADD KEY `idx_failed_at` (`failed_at`),
  ADD KEY `idx_failed_jobs_created_by` (`created_by`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_queue` (`queue`),
  ADD KEY `idx_reserved_at` (`reserved_at`),
  ADD KEY `idx_jobs_created_by` (`created_by`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `logs_channel_index` (`channel`),
  ADD KEY `logs_level_index` (`level`),
  ADD KEY `logs_created_at_index` (`created_at`);

--
-- Indexes for table `maintenance_mode`
--
ALTER TABLE `maintenance_mode`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_is_enabled` (`is_enabled`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_updated_by` (`updated_by`);

--
-- Indexes for table `mfa_methods`
--
ALTER TABLE `mfa_methods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type` (`type`);

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
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_modules_created_by` (`created_by`),
  ADD KEY `idx_modules_updated_by` (`updated_by`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status_scheduled` (`status`,`scheduled_at`),
  ADD KEY `idx_notifications_created_by` (`created_by`),
  ADD KEY `idx_notifications_updated_by` (`updated_by`);

--
-- Indexes for table `notification_recipients`
--
ALTER TABLE `notification_recipients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_status` (`user_id`,`status`),
  ADD KEY `notification_id` (`notification_id`),
  ADD KEY `idx_notification_recipients_created_by` (`created_by`);

--
-- Indexes for table `notification_templates`
--
ALTER TABLE `notification_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_notification_templates_created_by` (`created_by`),
  ADD KEY `idx_notification_templates_updated_by` (`updated_by`);

--
-- Indexes for table `oauth_accounts`
--
ALTER TABLE `oauth_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `provider_user` (`provider`,`provider_user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `password_resets_email_index` (`email`),
  ADD KEY `password_resets_token_index` (`token`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_permissions_module` (`module_id`),
  ADD KEY `idx_permissions_module_slug` (`module_slug`),
  ADD KEY `idx_permissions_created_by` (`created_by`),
  ADD KEY `idx_permissions_updated_by` (`updated_by`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_roles_created_by` (`created_by`),
  ADD KEY `idx_roles_updated_by` (`updated_by`),
  ADD KEY `idx_roles_deleted_by` (`deleted_by`),
  ADD KEY `idx_roles_deleted_at` (`deleted_at`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD KEY `role_permissions_role_id_permission_id_index` (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `sender_names`
--
ALTER TABLE `sender_names`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_sender_names_updated_by` (`updated_by`),
  ADD KEY `idx_sender_names_deleted_by` (`deleted_by`),
  ADD KEY `idx_sender_names_deleted_at` (`deleted_at`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`),
  ADD KEY `idx_settings_group` (`setting_group`),
  ADD KEY `idx_settings_key` (`key`),
  ADD KEY `idx_settings_created_by` (`created_by`),
  ADD KEY `idx_settings_updated_by` (`updated_by`);

--
-- Indexes for table `sms_billing_logs`
--
ALTER TABLE `sms_billing_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_status` (`user_id`,`status`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_sms_billing_logs_created_by` (`created_by`),
  ADD KEY `idx_sms_billing_logs_updated_by` (`updated_by`);

--
-- Indexes for table `sms_campaigns`
--
ALTER TABLE `sms_campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sms_campaigns_status_index` (`status`),
  ADD KEY `sms_campaigns_created_by_index` (`created_by`),
  ADD KEY `idx_sms_campaigns_updated_by` (`updated_by`);

--
-- Indexes for table `sms_gateways`
--
ALTER TABLE `sms_gateways`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `provider_code` (`provider_code`),
  ADD KEY `sms_gateways_provider_code_index` (`provider_code`),
  ADD KEY `sms_gateways_is_active_index` (`is_active`),
  ADD KEY `sms_gateways_is_default_index` (`is_default`),
  ADD KEY `sms_gateways_priority_index` (`priority`),
  ADD KEY `idx_sms_gateways_created_by` (`created_by`),
  ADD KEY `idx_sms_gateways_updated_by` (`updated_by`);

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
  ADD KEY `sms_messages_created_at_index` (`created_at`),
  ADD KEY `idx_sms_messages_created_by` (`created_by`),
  ADD KEY `idx_sms_messages_updated_by` (`updated_by`);

--
-- Indexes for table `sms_queue`
--
ALTER TABLE `sms_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sms_queue_campaign_id_index` (`campaign_id`),
  ADD KEY `sms_queue_status_index` (`status`),
  ADD KEY `sms_queue_scheduled_at_index` (`scheduled_at`),
  ADD KEY `idx_sms_queue_created_by` (`created_by`),
  ADD KEY `idx_sms_queue_updated_by` (`updated_by`);

--
-- Indexes for table `system_health_checks`
--
ALTER TABLE `system_health_checks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `service_name` (`service_name`),
  ADD KEY `idx_service_name` (`service_name`),
  ADD KEY `idx_last_run_at` (`last_run_at`);

--
-- Indexes for table `translations`
--
ALTER TABLE `translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_translation` (`language`,`key`),
  ADD KEY `idx_translations_language` (`language`),
  ADD KEY `idx_translations_module` (`module`),
  ADD KEY `idx_translations_created_by` (`created_by`);

--
-- Indexes for table `translation_history`
--
ALTER TABLE `translation_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_translation_history_translation_id` (`translation_id`),
  ADD KEY `idx_translation_history_changed_by` (`changed_by`),
  ADD KEY `idx_translation_history_created_by` (`created_by`);

--
-- Indexes for table `trusted_devices`
--
ALTER TABLE `trusted_devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_device` (`user_id`,`device_fingerprint`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `idx_device_fingerprint` (`device_fingerprint`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `api_key` (`api_key`),
  ADD KEY `idx_users_created_by` (`created_by`);

--
-- Indexes for table `user_mfa_setup`
--
ALTER TABLE `user_mfa_setup`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_method` (`user_id`,`method_type`);

--
-- Indexes for table `user_notification_preferences`
--
ALTER TABLE `user_notification_preferences`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `idx_user_notification_preferences_created_by` (`created_by`),
  ADD KEY `idx_user_notification_preferences_updated_by` (`updated_by`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD KEY `user_roles_user_id_role_id_index` (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `user_sender_names`
--
ALTER TABLE `user_sender_names`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_sender` (`user_id`,`sender_name_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_sender_name_id` (`sender_name_id`),
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `idx_user_sender_names_created_by` (`created_by`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_wallets_created_by` (`created_by`),
  ADD KEY `idx_wallets_updated_by` (`updated_by`);

--
-- Indexes for table `wallet_gateways`
--
ALTER TABLE `wallet_gateways`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `provider_code` (`provider_code`),
  ADD KEY `wallet_gateways_provider_code_index` (`provider_code`),
  ADD KEY `wallet_gateways_is_active_index` (`is_active`),
  ADD KEY `wallet_gateways_is_default_index` (`is_default`),
  ADD KEY `idx_wallet_gateways_created_by` (`created_by`),
  ADD KEY `idx_wallet_gateways_updated_by` (`updated_by`);

--
-- Indexes for table `wallet_topup_requests`
--
ALTER TABLE `wallet_topup_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_wallet_id` (`wallet_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_payment_method` (`payment_method`),
  ADD KEY `idx_gateway_id` (`gateway_id`),
  ADD KEY `idx_reviewed_by` (`reviewed_by`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_wallet_type` (`wallet_id`,`type`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_wallet_transactions_created_by` (`created_by`),
  ADD KEY `idx_wallet_transactions_updated_by` (`updated_by`);

--
-- Indexes for table `webhooks`
--
ALTER TABLE `webhooks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_webhooks_is_active` (`is_active`),
  ADD KEY `idx_webhooks_created_by` (`created_by`),
  ADD KEY `idx_webhooks_updated_by` (`updated_by`);

--
-- Indexes for table `webhook_logs`
--
ALTER TABLE `webhook_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_webhook_logs_webhook_id` (`webhook_id`),
  ADD KEY `idx_webhook_logs_created_at` (`created_at`),
  ADD KEY `idx_webhook_logs_created_by` (`created_by`);

--
-- Indexes for table `workflows`
--
ALTER TABLE `workflows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `workflows_status_index` (`status`),
  ADD KEY `workflows_trigger_type_index` (`trigger_type`),
  ADD KEY `idx_workflows_created_by` (`created_by`),
  ADD KEY `idx_workflows_updated_by` (`updated_by`);

--
-- Indexes for table `workflow_executions`
--
ALTER TABLE `workflow_executions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `workflow_executions_workflow_id_index` (`workflow_id`),
  ADD KEY `workflow_executions_contact_id_index` (`contact_id`),
  ADD KEY `workflow_executions_status_index` (`status`),
  ADD KEY `workflow_executions_next_step_at_index` (`next_step_at`),
  ADD KEY `idx_workflow_executions_created_by` (`created_by`);

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
-- AUTO_INCREMENT for table `api_keys`
--
ALTER TABLE `api_keys`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `api_request_logs`
--
ALTER TABLE `api_request_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auth_logs`
--
ALTER TABLE `auth_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=205;

--
-- AUTO_INCREMENT for table `auth_settings`
--
ALTER TABLE `auth_settings`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
-- AUTO_INCREMENT for table `campaign_logs`
--
ALTER TABLE `campaign_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_field_definitions`
--
ALTER TABLE `contact_field_definitions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cron_logs`
--
ALTER TABLE `cron_logs`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `cron_tasks`
--
ALTER TABLE `cron_tasks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `email_campaigns`
--
ALTER TABLE `email_campaigns`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_messages`
--
ALTER TABLE `email_messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance_mode`
--
ALTER TABLE `maintenance_mode`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `mfa_methods`
--
ALTER TABLE `mfa_methods`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

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
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `oauth_accounts`
--
ALTER TABLE `oauth_accounts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=175;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sender_names`
--
ALTER TABLE `sender_names`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `sms_billing_logs`
--
ALTER TABLE `sms_billing_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `sms_campaigns`
--
ALTER TABLE `sms_campaigns`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `sms_gateways`
--
ALTER TABLE `sms_gateways`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sms_messages`
--
ALTER TABLE `sms_messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `sms_queue`
--
ALTER TABLE `sms_queue`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `system_health_checks`
--
ALTER TABLE `system_health_checks`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
-- AUTO_INCREMENT for table `trusted_devices`
--
ALTER TABLE `trusted_devices`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_mfa_setup`
--
ALTER TABLE `user_mfa_setup`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_sender_names`
--
ALTER TABLE `user_sender_names`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `wallet_gateways`
--
ALTER TABLE `wallet_gateways`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `wallet_topup_requests`
--
ALTER TABLE `wallet_topup_requests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=169;

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
-- AUTO_INCREMENT for table `workflows`
--
ALTER TABLE `workflows`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `workflow_executions`
--
ALTER TABLE `workflow_executions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `api_keys`
--
ALTER TABLE `api_keys`
  ADD CONSTRAINT `api_keys_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `auth_logs`
--
ALTER TABLE `auth_logs`
  ADD CONSTRAINT `auth_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
-- Constraints for table `oauth_accounts`
--
ALTER TABLE `oauth_accounts`
  ADD CONSTRAINT `oauth_accounts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `permissions`
--
ALTER TABLE `permissions`
  ADD CONSTRAINT `fk_permissions_module` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sender_names`
--
ALTER TABLE `sender_names`
  ADD CONSTRAINT `sender_names_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_mfa_setup`
--
ALTER TABLE `user_mfa_setup`
  ADD CONSTRAINT `user_mfa_setup_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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

--
-- Constraints for table `user_sender_names`
--
ALTER TABLE `user_sender_names`
  ADD CONSTRAINT `user_sender_names_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_sender_names_ibfk_2` FOREIGN KEY (`sender_name_id`) REFERENCES `sender_names` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_sender_names_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `wallet_topup_requests`
--
ALTER TABLE `wallet_topup_requests`
  ADD CONSTRAINT `wallet_topup_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wallet_topup_requests_ibfk_2` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wallet_topup_requests_ibfk_3` FOREIGN KEY (`gateway_id`) REFERENCES `wallet_gateways` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `wallet_topup_requests_ibfk_4` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
