-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 14, 2026 at 08:51 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.5.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `drawingdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `photo` varchar(2048) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `username`, `email`, `phone`, `email_verified_at`, `password`, `status`, `photo`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Super Admin', 'admin', 'admin@gmail.com', '01700000000', NULL, '$2y$12$aHNwIXcwFg1/ua6xM4JUou7VxZ1oEYoTCQn3SMWYn.ZJg498GT9iK', 1, NULL, '0X6aeVZ7jr15u5dgsnozUrs9QNo3oxRWwg7EChhS2NCdfe0Vh3C0TytOy48p', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `admin_password_reset_tokens`
--

CREATE TABLE `admin_password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `actor_type` varchar(255) DEFAULT NULL,
  `actor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(150) NOT NULL,
  `auditable_type` varchar(255) NOT NULL,
  `auditable_id` bigint(20) UNSIGNED DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:5:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"d\";s:10:\"group_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:419:{i:0;a:5:{s:1:\"a\";i:1;s:1:\"b\";s:14:\"dashboard_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Dashboard\";s:1:\"r\";a:1:{i:0;i:1;}}i:1;a:5:{s:1:\"a\";i:2;s:1:\"b\";s:19:\"system_tools_manage\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"System Tools\";s:1:\"r\";a:1:{i:0;i:1;}}i:2;a:5:{s:1:\"a\";i:3;s:1:\"b\";s:11:\"cache_clear\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"System Tools\";s:1:\"r\";a:1:{i:0;i:1;}}i:3;a:5:{s:1:\"a\";i:4;s:1:\"b\";s:12:\"queue_manage\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"System Tools\";s:1:\"r\";a:1:{i:0;i:1;}}i:4;a:5:{s:1:\"a\";i:5;s:1:\"b\";s:13:\"backup_manage\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"System Tools\";s:1:\"r\";a:1:{i:0;i:1;}}i:5;a:5:{s:1:\"a\";i:6;s:1:\"b\";s:17:\"site_setting_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Site Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:6;a:5:{s:1:\"a\";i:7;s:1:\"b\";s:19:\"site_setting_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Site Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:7;a:5:{s:1:\"a\";i:8;s:1:\"b\";s:19:\"site_setting_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Site Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:8;a:5:{s:1:\"a\";i:9;s:1:\"b\";s:19:\"site_setting_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Site Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:9;a:5:{s:1:\"a\";i:10;s:1:\"b\";s:18:\"site_setting_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Site Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:10;a:5:{s:1:\"a\";i:11;s:1:\"b\";s:20:\"site_setting_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Site Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:11;a:5:{s:1:\"a\";i:12;s:1:\"b\";s:25:\"site_setting_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Site Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:12;a:5:{s:1:\"a\";i:13;s:1:\"b\";s:19:\"header_setting_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Header Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:13;a:5:{s:1:\"a\";i:14;s:1:\"b\";s:21:\"header_setting_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Header Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:14;a:5:{s:1:\"a\";i:15;s:1:\"b\";s:16:\"header_menu_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:11:\"Header Menu\";s:1:\"r\";a:1:{i:0;i:1;}}i:15;a:5:{s:1:\"a\";i:16;s:1:\"b\";s:16:\"header_menu_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:11:\"Header Menu\";s:1:\"r\";a:1:{i:0;i:1;}}i:16;a:5:{s:1:\"a\";i:17;s:1:\"b\";s:18:\"header_menu_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:11:\"Header Menu\";s:1:\"r\";a:1:{i:0;i:1;}}i:17;a:5:{s:1:\"a\";i:18;s:1:\"b\";s:18:\"header_menu_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:11:\"Header Menu\";s:1:\"r\";a:1:{i:0;i:1;}}i:18;a:5:{s:1:\"a\";i:19;s:1:\"b\";s:18:\"header_menu_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:11:\"Header Menu\";s:1:\"r\";a:1:{i:0;i:1;}}i:19;a:5:{s:1:\"a\";i:20;s:1:\"b\";s:18:\"header_menu_toggle\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:11:\"Header Menu\";s:1:\"r\";a:1:{i:0;i:1;}}i:20;a:5:{s:1:\"a\";i:21;s:1:\"b\";s:19:\"header_menu_reorder\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:11:\"Header Menu\";s:1:\"r\";a:1:{i:0;i:1;}}i:21;a:5:{s:1:\"a\";i:22;s:1:\"b\";s:19:\"footer_setting_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Footer Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:22;a:5:{s:1:\"a\";i:23;s:1:\"b\";s:21:\"footer_setting_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Footer Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:23;a:5:{s:1:\"a\";i:24;s:1:\"b\";s:16:\"footer_link_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"Footer Links\";s:1:\"r\";a:1:{i:0;i:1;}}i:24;a:5:{s:1:\"a\";i:25;s:1:\"b\";s:16:\"footer_link_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"Footer Links\";s:1:\"r\";a:1:{i:0;i:1;}}i:25;a:5:{s:1:\"a\";i:26;s:1:\"b\";s:18:\"footer_link_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"Footer Links\";s:1:\"r\";a:1:{i:0;i:1;}}i:26;a:5:{s:1:\"a\";i:27;s:1:\"b\";s:18:\"footer_link_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"Footer Links\";s:1:\"r\";a:1:{i:0;i:1;}}i:27;a:5:{s:1:\"a\";i:28;s:1:\"b\";s:18:\"footer_link_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"Footer Links\";s:1:\"r\";a:1:{i:0;i:1;}}i:28;a:5:{s:1:\"a\";i:29;s:1:\"b\";s:18:\"footer_link_toggle\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"Footer Links\";s:1:\"r\";a:1:{i:0;i:1;}}i:29;a:5:{s:1:\"a\";i:30;s:1:\"b\";s:19:\"footer_link_reorder\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"Footer Links\";s:1:\"r\";a:1:{i:0;i:1;}}i:30;a:5:{s:1:\"a\";i:31;s:1:\"b\";s:9:\"menu_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Menus\";s:1:\"r\";a:1:{i:0;i:1;}}i:31;a:5:{s:1:\"a\";i:32;s:1:\"b\";s:9:\"menu_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Menus\";s:1:\"r\";a:1:{i:0;i:1;}}i:32;a:5:{s:1:\"a\";i:33;s:1:\"b\";s:11:\"menu_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Menus\";s:1:\"r\";a:1:{i:0;i:1;}}i:33;a:5:{s:1:\"a\";i:34;s:1:\"b\";s:11:\"menu_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Menus\";s:1:\"r\";a:1:{i:0;i:1;}}i:34;a:5:{s:1:\"a\";i:35;s:1:\"b\";s:11:\"menu_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Menus\";s:1:\"r\";a:1:{i:0;i:1;}}i:35;a:5:{s:1:\"a\";i:36;s:1:\"b\";s:10:\"menu_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Menus\";s:1:\"r\";a:1:{i:0;i:1;}}i:36;a:5:{s:1:\"a\";i:37;s:1:\"b\";s:12:\"menu_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Menus\";s:1:\"r\";a:1:{i:0;i:1;}}i:37;a:5:{s:1:\"a\";i:38;s:1:\"b\";s:17:\"menu_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Menus\";s:1:\"r\";a:1:{i:0;i:1;}}i:38;a:5:{s:1:\"a\";i:39;s:1:\"b\";s:9:\"page_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Pages\";s:1:\"r\";a:1:{i:0;i:1;}}i:39;a:5:{s:1:\"a\";i:40;s:1:\"b\";s:9:\"page_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Pages\";s:1:\"r\";a:1:{i:0;i:1;}}i:40;a:5:{s:1:\"a\";i:41;s:1:\"b\";s:11:\"page_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Pages\";s:1:\"r\";a:1:{i:0;i:1;}}i:41;a:5:{s:1:\"a\";i:42;s:1:\"b\";s:11:\"page_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Pages\";s:1:\"r\";a:1:{i:0;i:1;}}i:42;a:5:{s:1:\"a\";i:43;s:1:\"b\";s:11:\"page_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Pages\";s:1:\"r\";a:1:{i:0;i:1;}}i:43;a:5:{s:1:\"a\";i:44;s:1:\"b\";s:10:\"page_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Pages\";s:1:\"r\";a:1:{i:0;i:1;}}i:44;a:5:{s:1:\"a\";i:45;s:1:\"b\";s:12:\"page_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Pages\";s:1:\"r\";a:1:{i:0;i:1;}}i:45;a:5:{s:1:\"a\";i:46;s:1:\"b\";s:17:\"page_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Pages\";s:1:\"r\";a:1:{i:0;i:1;}}i:46;a:5:{s:1:\"a\";i:47;s:1:\"b\";s:12:\"page_publish\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Pages\";s:1:\"r\";a:1:{i:0;i:1;}}i:47;a:5:{s:1:\"a\";i:48;s:1:\"b\";s:14:\"page_unpublish\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Pages\";s:1:\"r\";a:1:{i:0;i:1;}}i:48;a:5:{s:1:\"a\";i:49;s:1:\"b\";s:12:\"page_preview\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Pages\";s:1:\"r\";a:1:{i:0;i:1;}}i:49;a:5:{s:1:\"a\";i:50;s:1:\"b\";s:14:\"page_duplicate\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Pages\";s:1:\"r\";a:1:{i:0;i:1;}}i:50;a:5:{s:1:\"a\";i:51;s:1:\"b\";s:17:\"page_section_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Page Sections\";s:1:\"r\";a:1:{i:0;i:1;}}i:51;a:5:{s:1:\"a\";i:52;s:1:\"b\";s:17:\"page_section_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Page Sections\";s:1:\"r\";a:1:{i:0;i:1;}}i:52;a:5:{s:1:\"a\";i:53;s:1:\"b\";s:19:\"page_section_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Page Sections\";s:1:\"r\";a:1:{i:0;i:1;}}i:53;a:5:{s:1:\"a\";i:54;s:1:\"b\";s:19:\"page_section_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Page Sections\";s:1:\"r\";a:1:{i:0;i:1;}}i:54;a:5:{s:1:\"a\";i:55;s:1:\"b\";s:19:\"page_section_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Page Sections\";s:1:\"r\";a:1:{i:0;i:1;}}i:55;a:5:{s:1:\"a\";i:56;s:1:\"b\";s:19:\"page_section_toggle\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Page Sections\";s:1:\"r\";a:1:{i:0;i:1;}}i:56;a:5:{s:1:\"a\";i:57;s:1:\"b\";s:20:\"page_section_reorder\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Page Sections\";s:1:\"r\";a:1:{i:0;i:1;}}i:57;a:5:{s:1:\"a\";i:58;s:1:\"b\";s:22:\"page_section_duplicate\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Page Sections\";s:1:\"r\";a:1:{i:0;i:1;}}i:58;a:5:{s:1:\"a\";i:59;s:1:\"b\";s:18:\"page_section_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Page Sections\";s:1:\"r\";a:1:{i:0;i:1;}}i:59;a:5:{s:1:\"a\";i:60;s:1:\"b\";s:20:\"page_section_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Page Sections\";s:1:\"r\";a:1:{i:0;i:1;}}i:60;a:5:{s:1:\"a\";i:61;s:1:\"b\";s:25:\"page_section_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Page Sections\";s:1:\"r\";a:1:{i:0;i:1;}}i:61;a:5:{s:1:\"a\";i:62;s:1:\"b\";s:18:\"section_media_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Section Media\";s:1:\"r\";a:1:{i:0;i:1;}}i:62;a:5:{s:1:\"a\";i:63;s:1:\"b\";s:18:\"section_media_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Section Media\";s:1:\"r\";a:1:{i:0;i:1;}}i:63;a:5:{s:1:\"a\";i:64;s:1:\"b\";s:20:\"section_media_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Section Media\";s:1:\"r\";a:1:{i:0;i:1;}}i:64;a:5:{s:1:\"a\";i:65;s:1:\"b\";s:20:\"section_media_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Section Media\";s:1:\"r\";a:1:{i:0;i:1;}}i:65;a:5:{s:1:\"a\";i:66;s:1:\"b\";s:20:\"section_media_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Section Media\";s:1:\"r\";a:1:{i:0;i:1;}}i:66;a:5:{s:1:\"a\";i:67;s:1:\"b\";s:19:\"section_media_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Section Media\";s:1:\"r\";a:1:{i:0;i:1;}}i:67;a:5:{s:1:\"a\";i:68;s:1:\"b\";s:21:\"section_media_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Section Media\";s:1:\"r\";a:1:{i:0;i:1;}}i:68;a:5:{s:1:\"a\";i:69;s:1:\"b\";s:26:\"section_media_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Section Media\";s:1:\"r\";a:1:{i:0;i:1;}}i:69;a:5:{s:1:\"a\";i:70;s:1:\"b\";s:23:\"section_definition_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:19:\"Section Definitions\";s:1:\"r\";a:1:{i:0;i:1;}}i:70;a:5:{s:1:\"a\";i:71;s:1:\"b\";s:23:\"section_definition_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:19:\"Section Definitions\";s:1:\"r\";a:1:{i:0;i:1;}}i:71;a:5:{s:1:\"a\";i:72;s:1:\"b\";s:25:\"section_definition_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:19:\"Section Definitions\";s:1:\"r\";a:1:{i:0;i:1;}}i:72;a:5:{s:1:\"a\";i:73;s:1:\"b\";s:25:\"section_definition_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:19:\"Section Definitions\";s:1:\"r\";a:1:{i:0;i:1;}}i:73;a:5:{s:1:\"a\";i:74;s:1:\"b\";s:25:\"section_definition_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:19:\"Section Definitions\";s:1:\"r\";a:1:{i:0;i:1;}}i:74;a:5:{s:1:\"a\";i:75;s:1:\"b\";s:24:\"section_definition_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:19:\"Section Definitions\";s:1:\"r\";a:1:{i:0;i:1;}}i:75;a:5:{s:1:\"a\";i:76;s:1:\"b\";s:26:\"section_definition_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:19:\"Section Definitions\";s:1:\"r\";a:1:{i:0;i:1;}}i:76;a:5:{s:1:\"a\";i:77;s:1:\"b\";s:31:\"section_definition_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:19:\"Section Definitions\";s:1:\"r\";a:1:{i:0;i:1;}}i:77;a:5:{s:1:\"a\";i:78;s:1:\"b\";s:25:\"section_definition_toggle\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:19:\"Section Definitions\";s:1:\"r\";a:1:{i:0;i:1;}}i:78;a:5:{s:1:\"a\";i:79;s:1:\"b\";s:12:\"service_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:8:\"Services\";s:1:\"r\";a:1:{i:0;i:1;}}i:79;a:5:{s:1:\"a\";i:80;s:1:\"b\";s:12:\"service_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:8:\"Services\";s:1:\"r\";a:1:{i:0;i:1;}}i:80;a:5:{s:1:\"a\";i:81;s:1:\"b\";s:14:\"service_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:8:\"Services\";s:1:\"r\";a:1:{i:0;i:1;}}i:81;a:5:{s:1:\"a\";i:82;s:1:\"b\";s:14:\"service_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:8:\"Services\";s:1:\"r\";a:1:{i:0;i:1;}}i:82;a:5:{s:1:\"a\";i:83;s:1:\"b\";s:14:\"service_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:8:\"Services\";s:1:\"r\";a:1:{i:0;i:1;}}i:83;a:5:{s:1:\"a\";i:84;s:1:\"b\";s:13:\"service_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:8:\"Services\";s:1:\"r\";a:1:{i:0;i:1;}}i:84;a:5:{s:1:\"a\";i:85;s:1:\"b\";s:15:\"service_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:8:\"Services\";s:1:\"r\";a:1:{i:0;i:1;}}i:85;a:5:{s:1:\"a\";i:86;s:1:\"b\";s:20:\"service_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:8:\"Services\";s:1:\"r\";a:1:{i:0;i:1;}}i:86;a:5:{s:1:\"a\";i:87;s:1:\"b\";s:15:\"service_publish\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:8:\"Services\";s:1:\"r\";a:1:{i:0;i:1;}}i:87;a:5:{s:1:\"a\";i:88;s:1:\"b\";s:17:\"service_unpublish\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:8:\"Services\";s:1:\"r\";a:1:{i:0;i:1;}}i:88;a:5:{s:1:\"a\";i:89;s:1:\"b\";s:17:\"service_duplicate\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:8:\"Services\";s:1:\"r\";a:1:{i:0;i:1;}}i:89;a:5:{s:1:\"a\";i:90;s:1:\"b\";s:20:\"service_feature_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Service Features\";s:1:\"r\";a:1:{i:0;i:1;}}i:90;a:5:{s:1:\"a\";i:91;s:1:\"b\";s:20:\"service_feature_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Service Features\";s:1:\"r\";a:1:{i:0;i:1;}}i:91;a:5:{s:1:\"a\";i:92;s:1:\"b\";s:22:\"service_feature_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Service Features\";s:1:\"r\";a:1:{i:0;i:1;}}i:92;a:5:{s:1:\"a\";i:93;s:1:\"b\";s:22:\"service_feature_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Service Features\";s:1:\"r\";a:1:{i:0;i:1;}}i:93;a:5:{s:1:\"a\";i:94;s:1:\"b\";s:22:\"service_feature_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Service Features\";s:1:\"r\";a:1:{i:0;i:1;}}i:94;a:5:{s:1:\"a\";i:95;s:1:\"b\";s:22:\"service_feature_toggle\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Service Features\";s:1:\"r\";a:1:{i:0;i:1;}}i:95;a:5:{s:1:\"a\";i:96;s:1:\"b\";s:23:\"service_feature_reorder\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Service Features\";s:1:\"r\";a:1:{i:0;i:1;}}i:96;a:5:{s:1:\"a\";i:97;s:1:\"b\";s:25:\"service_feature_duplicate\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Service Features\";s:1:\"r\";a:1:{i:0;i:1;}}i:97;a:5:{s:1:\"a\";i:98;s:1:\"b\";s:21:\"service_feature_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Service Features\";s:1:\"r\";a:1:{i:0;i:1;}}i:98;a:5:{s:1:\"a\";i:99;s:1:\"b\";s:23:\"service_feature_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Service Features\";s:1:\"r\";a:1:{i:0;i:1;}}i:99;a:5:{s:1:\"a\";i:100;s:1:\"b\";s:28:\"service_feature_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Service Features\";s:1:\"r\";a:1:{i:0;i:1;}}i:100;a:5:{s:1:\"a\";i:101;s:1:\"b\";s:13:\"location_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Locations\";s:1:\"r\";a:1:{i:0;i:1;}}i:101;a:5:{s:1:\"a\";i:102;s:1:\"b\";s:13:\"location_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Locations\";s:1:\"r\";a:1:{i:0;i:1;}}i:102;a:5:{s:1:\"a\";i:103;s:1:\"b\";s:15:\"location_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Locations\";s:1:\"r\";a:1:{i:0;i:1;}}i:103;a:5:{s:1:\"a\";i:104;s:1:\"b\";s:15:\"location_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Locations\";s:1:\"r\";a:1:{i:0;i:1;}}i:104;a:5:{s:1:\"a\";i:105;s:1:\"b\";s:15:\"location_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Locations\";s:1:\"r\";a:1:{i:0;i:1;}}i:105;a:5:{s:1:\"a\";i:106;s:1:\"b\";s:14:\"location_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Locations\";s:1:\"r\";a:1:{i:0;i:1;}}i:106;a:5:{s:1:\"a\";i:107;s:1:\"b\";s:16:\"location_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Locations\";s:1:\"r\";a:1:{i:0;i:1;}}i:107;a:5:{s:1:\"a\";i:108;s:1:\"b\";s:21:\"location_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Locations\";s:1:\"r\";a:1:{i:0;i:1;}}i:108;a:5:{s:1:\"a\";i:109;s:1:\"b\";s:16:\"location_publish\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Locations\";s:1:\"r\";a:1:{i:0;i:1;}}i:109;a:5:{s:1:\"a\";i:110;s:1:\"b\";s:18:\"location_unpublish\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Locations\";s:1:\"r\";a:1:{i:0;i:1;}}i:110;a:5:{s:1:\"a\";i:111;s:1:\"b\";s:18:\"location_duplicate\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Locations\";s:1:\"r\";a:1:{i:0;i:1;}}i:111;a:5:{s:1:\"a\";i:112;s:1:\"b\";s:20:\"pricing_package_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Pricing Packages\";s:1:\"r\";a:1:{i:0;i:1;}}i:112;a:5:{s:1:\"a\";i:113;s:1:\"b\";s:20:\"pricing_package_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Pricing Packages\";s:1:\"r\";a:1:{i:0;i:1;}}i:113;a:5:{s:1:\"a\";i:114;s:1:\"b\";s:22:\"pricing_package_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Pricing Packages\";s:1:\"r\";a:1:{i:0;i:1;}}i:114;a:5:{s:1:\"a\";i:115;s:1:\"b\";s:22:\"pricing_package_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Pricing Packages\";s:1:\"r\";a:1:{i:0;i:1;}}i:115;a:5:{s:1:\"a\";i:116;s:1:\"b\";s:22:\"pricing_package_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Pricing Packages\";s:1:\"r\";a:1:{i:0;i:1;}}i:116;a:5:{s:1:\"a\";i:117;s:1:\"b\";s:22:\"pricing_package_toggle\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Pricing Packages\";s:1:\"r\";a:1:{i:0;i:1;}}i:117;a:5:{s:1:\"a\";i:118;s:1:\"b\";s:25:\"pricing_package_duplicate\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Pricing Packages\";s:1:\"r\";a:1:{i:0;i:1;}}i:118;a:5:{s:1:\"a\";i:119;s:1:\"b\";s:21:\"pricing_package_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Pricing Packages\";s:1:\"r\";a:1:{i:0;i:1;}}i:119;a:5:{s:1:\"a\";i:120;s:1:\"b\";s:23:\"pricing_package_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Pricing Packages\";s:1:\"r\";a:1:{i:0;i:1;}}i:120;a:5:{s:1:\"a\";i:121;s:1:\"b\";s:28:\"pricing_package_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Pricing Packages\";s:1:\"r\";a:1:{i:0;i:1;}}i:121;a:5:{s:1:\"a\";i:122;s:1:\"b\";s:17:\"pricing_item_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Pricing Items\";s:1:\"r\";a:1:{i:0;i:1;}}i:122;a:5:{s:1:\"a\";i:123;s:1:\"b\";s:17:\"pricing_item_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Pricing Items\";s:1:\"r\";a:1:{i:0;i:1;}}i:123;a:5:{s:1:\"a\";i:124;s:1:\"b\";s:19:\"pricing_item_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Pricing Items\";s:1:\"r\";a:1:{i:0;i:1;}}i:124;a:5:{s:1:\"a\";i:125;s:1:\"b\";s:19:\"pricing_item_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Pricing Items\";s:1:\"r\";a:1:{i:0;i:1;}}i:125;a:5:{s:1:\"a\";i:126;s:1:\"b\";s:19:\"pricing_item_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Pricing Items\";s:1:\"r\";a:1:{i:0;i:1;}}i:126;a:5:{s:1:\"a\";i:127;s:1:\"b\";s:19:\"pricing_item_toggle\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Pricing Items\";s:1:\"r\";a:1:{i:0;i:1;}}i:127;a:5:{s:1:\"a\";i:128;s:1:\"b\";s:22:\"pricing_item_duplicate\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Pricing Items\";s:1:\"r\";a:1:{i:0;i:1;}}i:128;a:5:{s:1:\"a\";i:129;s:1:\"b\";s:18:\"pricing_item_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Pricing Items\";s:1:\"r\";a:1:{i:0;i:1;}}i:129;a:5:{s:1:\"a\";i:130;s:1:\"b\";s:20:\"pricing_item_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Pricing Items\";s:1:\"r\";a:1:{i:0;i:1;}}i:130;a:5:{s:1:\"a\";i:131;s:1:\"b\";s:25:\"pricing_item_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Pricing Items\";s:1:\"r\";a:1:{i:0;i:1;}}i:131;a:5:{s:1:\"a\";i:132;s:1:\"b\";s:18:\"pricing_addon_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Pricing Add-ons\";s:1:\"r\";a:1:{i:0;i:1;}}i:132;a:5:{s:1:\"a\";i:133;s:1:\"b\";s:18:\"pricing_addon_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Pricing Add-ons\";s:1:\"r\";a:1:{i:0;i:1;}}i:133;a:5:{s:1:\"a\";i:134;s:1:\"b\";s:20:\"pricing_addon_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Pricing Add-ons\";s:1:\"r\";a:1:{i:0;i:1;}}i:134;a:5:{s:1:\"a\";i:135;s:1:\"b\";s:20:\"pricing_addon_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Pricing Add-ons\";s:1:\"r\";a:1:{i:0;i:1;}}i:135;a:5:{s:1:\"a\";i:136;s:1:\"b\";s:20:\"pricing_addon_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Pricing Add-ons\";s:1:\"r\";a:1:{i:0;i:1;}}i:136;a:5:{s:1:\"a\";i:137;s:1:\"b\";s:20:\"pricing_addon_toggle\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Pricing Add-ons\";s:1:\"r\";a:1:{i:0;i:1;}}i:137;a:5:{s:1:\"a\";i:138;s:1:\"b\";s:23:\"pricing_addon_duplicate\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Pricing Add-ons\";s:1:\"r\";a:1:{i:0;i:1;}}i:138;a:5:{s:1:\"a\";i:139;s:1:\"b\";s:19:\"pricing_addon_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Pricing Add-ons\";s:1:\"r\";a:1:{i:0;i:1;}}i:139;a:5:{s:1:\"a\";i:140;s:1:\"b\";s:21:\"pricing_addon_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Pricing Add-ons\";s:1:\"r\";a:1:{i:0;i:1;}}i:140;a:5:{s:1:\"a\";i:141;s:1:\"b\";s:26:\"pricing_addon_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Pricing Add-ons\";s:1:\"r\";a:1:{i:0;i:1;}}i:141;a:5:{s:1:\"a\";i:142;s:1:\"b\";s:26:\"pricing_package_addon_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:23:\"Pricing Package Add-ons\";s:1:\"r\";a:1:{i:0;i:1;}}i:142;a:5:{s:1:\"a\";i:143;s:1:\"b\";s:26:\"pricing_package_addon_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:23:\"Pricing Package Add-ons\";s:1:\"r\";a:1:{i:0;i:1;}}i:143;a:5:{s:1:\"a\";i:144;s:1:\"b\";s:28:\"pricing_package_addon_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:23:\"Pricing Package Add-ons\";s:1:\"r\";a:1:{i:0;i:1;}}i:144;a:5:{s:1:\"a\";i:145;s:1:\"b\";s:28:\"pricing_package_addon_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:23:\"Pricing Package Add-ons\";s:1:\"r\";a:1:{i:0;i:1;}}i:145;a:5:{s:1:\"a\";i:146;s:1:\"b\";s:28:\"pricing_package_addon_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:23:\"Pricing Package Add-ons\";s:1:\"r\";a:1:{i:0;i:1;}}i:146;a:5:{s:1:\"a\";i:147;s:1:\"b\";s:27:\"pricing_package_addon_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:23:\"Pricing Package Add-ons\";s:1:\"r\";a:1:{i:0;i:1;}}i:147;a:5:{s:1:\"a\";i:148;s:1:\"b\";s:29:\"pricing_package_addon_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:23:\"Pricing Package Add-ons\";s:1:\"r\";a:1:{i:0;i:1;}}i:148;a:5:{s:1:\"a\";i:149;s:1:\"b\";s:34:\"pricing_package_addon_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:23:\"Pricing Package Add-ons\";s:1:\"r\";a:1:{i:0;i:1;}}i:149;a:5:{s:1:\"a\";i:150;s:1:\"b\";s:10:\"media_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Media\";s:1:\"r\";a:1:{i:0;i:1;}}i:150;a:5:{s:1:\"a\";i:151;s:1:\"b\";s:10:\"media_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Media\";s:1:\"r\";a:1:{i:0;i:1;}}i:151;a:5:{s:1:\"a\";i:152;s:1:\"b\";s:12:\"media_upload\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Media\";s:1:\"r\";a:1:{i:0;i:1;}}i:152;a:5:{s:1:\"a\";i:153;s:1:\"b\";s:12:\"media_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Media\";s:1:\"r\";a:1:{i:0;i:1;}}i:153;a:5:{s:1:\"a\";i:154;s:1:\"b\";s:12:\"media_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Media\";s:1:\"r\";a:1:{i:0;i:1;}}i:154;a:5:{s:1:\"a\";i:155;s:1:\"b\";s:11:\"media_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Media\";s:1:\"r\";a:1:{i:0;i:1;}}i:155;a:5:{s:1:\"a\";i:156;s:1:\"b\";s:13:\"media_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Media\";s:1:\"r\";a:1:{i:0;i:1;}}i:156;a:5:{s:1:\"a\";i:157;s:1:\"b\";s:18:\"media_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Media\";s:1:\"r\";a:1:{i:0;i:1;}}i:157;a:5:{s:1:\"a\";i:158;s:1:\"b\";s:14:\"media_download\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Media\";s:1:\"r\";a:1:{i:0;i:1;}}i:158;a:5:{s:1:\"a\";i:159;s:1:\"b\";s:13:\"media_replace\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Media\";s:1:\"r\";a:1:{i:0;i:1;}}i:159;a:5:{s:1:\"a\";i:160;s:1:\"b\";s:27:\"media_regenerate_conversion\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Media\";s:1:\"r\";a:1:{i:0;i:1;}}i:160;a:5:{s:1:\"a\";i:161;s:1:\"b\";s:12:\"gallery_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Galleries\";s:1:\"r\";a:1:{i:0;i:1;}}i:161;a:5:{s:1:\"a\";i:162;s:1:\"b\";s:12:\"gallery_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Galleries\";s:1:\"r\";a:1:{i:0;i:1;}}i:162;a:5:{s:1:\"a\";i:163;s:1:\"b\";s:14:\"gallery_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Galleries\";s:1:\"r\";a:1:{i:0;i:1;}}i:163;a:5:{s:1:\"a\";i:164;s:1:\"b\";s:14:\"gallery_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Galleries\";s:1:\"r\";a:1:{i:0;i:1;}}i:164;a:5:{s:1:\"a\";i:165;s:1:\"b\";s:14:\"gallery_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Galleries\";s:1:\"r\";a:1:{i:0;i:1;}}i:165;a:5:{s:1:\"a\";i:166;s:1:\"b\";s:14:\"gallery_toggle\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Galleries\";s:1:\"r\";a:1:{i:0;i:1;}}i:166;a:5:{s:1:\"a\";i:167;s:1:\"b\";s:15:\"gallery_reorder\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Galleries\";s:1:\"r\";a:1:{i:0;i:1;}}i:167;a:5:{s:1:\"a\";i:168;s:1:\"b\";s:13:\"gallery_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Galleries\";s:1:\"r\";a:1:{i:0;i:1;}}i:168;a:5:{s:1:\"a\";i:169;s:1:\"b\";s:15:\"gallery_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Galleries\";s:1:\"r\";a:1:{i:0;i:1;}}i:169;a:5:{s:1:\"a\";i:170;s:1:\"b\";s:20:\"gallery_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Galleries\";s:1:\"r\";a:1:{i:0;i:1;}}i:170;a:5:{s:1:\"a\";i:171;s:1:\"b\";s:17:\"gallery_item_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Gallery Items\";s:1:\"r\";a:1:{i:0;i:1;}}i:171;a:5:{s:1:\"a\";i:172;s:1:\"b\";s:17:\"gallery_item_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Gallery Items\";s:1:\"r\";a:1:{i:0;i:1;}}i:172;a:5:{s:1:\"a\";i:173;s:1:\"b\";s:19:\"gallery_item_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Gallery Items\";s:1:\"r\";a:1:{i:0;i:1;}}i:173;a:5:{s:1:\"a\";i:174;s:1:\"b\";s:19:\"gallery_item_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Gallery Items\";s:1:\"r\";a:1:{i:0;i:1;}}i:174;a:5:{s:1:\"a\";i:175;s:1:\"b\";s:19:\"gallery_item_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Gallery Items\";s:1:\"r\";a:1:{i:0;i:1;}}i:175;a:5:{s:1:\"a\";i:176;s:1:\"b\";s:19:\"gallery_item_toggle\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Gallery Items\";s:1:\"r\";a:1:{i:0;i:1;}}i:176;a:5:{s:1:\"a\";i:177;s:1:\"b\";s:20:\"gallery_item_reorder\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Gallery Items\";s:1:\"r\";a:1:{i:0;i:1;}}i:177;a:5:{s:1:\"a\";i:178;s:1:\"b\";s:18:\"gallery_item_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Gallery Items\";s:1:\"r\";a:1:{i:0;i:1;}}i:178;a:5:{s:1:\"a\";i:179;s:1:\"b\";s:20:\"gallery_item_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Gallery Items\";s:1:\"r\";a:1:{i:0;i:1;}}i:179;a:5:{s:1:\"a\";i:180;s:1:\"b\";s:25:\"gallery_item_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:13:\"Gallery Items\";s:1:\"r\";a:1:{i:0;i:1;}}i:180;a:5:{s:1:\"a\";i:181;s:1:\"b\";s:10:\"video_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:6:\"Videos\";s:1:\"r\";a:1:{i:0;i:1;}}i:181;a:5:{s:1:\"a\";i:182;s:1:\"b\";s:10:\"video_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:6:\"Videos\";s:1:\"r\";a:1:{i:0;i:1;}}i:182;a:5:{s:1:\"a\";i:183;s:1:\"b\";s:12:\"video_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:6:\"Videos\";s:1:\"r\";a:1:{i:0;i:1;}}i:183;a:5:{s:1:\"a\";i:184;s:1:\"b\";s:12:\"video_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:6:\"Videos\";s:1:\"r\";a:1:{i:0;i:1;}}i:184;a:5:{s:1:\"a\";i:185;s:1:\"b\";s:12:\"video_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:6:\"Videos\";s:1:\"r\";a:1:{i:0;i:1;}}i:185;a:5:{s:1:\"a\";i:186;s:1:\"b\";s:11:\"video_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:6:\"Videos\";s:1:\"r\";a:1:{i:0;i:1;}}i:186;a:5:{s:1:\"a\";i:187;s:1:\"b\";s:13:\"video_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:6:\"Videos\";s:1:\"r\";a:1:{i:0;i:1;}}i:187;a:5:{s:1:\"a\";i:188;s:1:\"b\";s:18:\"video_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:6:\"Videos\";s:1:\"r\";a:1:{i:0;i:1;}}i:188;a:5:{s:1:\"a\";i:189;s:1:\"b\";s:12:\"video_upload\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:6:\"Videos\";s:1:\"r\";a:1:{i:0;i:1;}}i:189;a:5:{s:1:\"a\";i:190;s:1:\"b\";s:11:\"video_embed\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:6:\"Videos\";s:1:\"r\";a:1:{i:0;i:1;}}i:190;a:5:{s:1:\"a\";i:191;s:1:\"b\";s:13:\"video_process\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:6:\"Videos\";s:1:\"r\";a:1:{i:0;i:1;}}i:191;a:5:{s:1:\"a\";i:192;s:1:\"b\";s:12:\"video_toggle\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:6:\"Videos\";s:1:\"r\";a:1:{i:0;i:1;}}i:192;a:5:{s:1:\"a\";i:193;s:1:\"b\";s:13:\"video_reorder\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:6:\"Videos\";s:1:\"r\";a:1:{i:0;i:1;}}i:193;a:5:{s:1:\"a\";i:194;s:1:\"b\";s:16:\"testimonial_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"Testimonials\";s:1:\"r\";a:1:{i:0;i:1;}}i:194;a:5:{s:1:\"a\";i:195;s:1:\"b\";s:18:\"testimonial_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"Testimonials\";s:1:\"r\";a:1:{i:0;i:1;}}i:195;a:5:{s:1:\"a\";i:196;s:1:\"b\";s:16:\"testimonial_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"Testimonials\";s:1:\"r\";a:1:{i:0;i:1;}}i:196;a:5:{s:1:\"a\";i:197;s:1:\"b\";s:18:\"testimonial_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"Testimonials\";s:1:\"r\";a:1:{i:0;i:1;}}i:197;a:5:{s:1:\"a\";i:198;s:1:\"b\";s:18:\"testimonial_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"Testimonials\";s:1:\"r\";a:1:{i:0;i:1;}}i:198;a:5:{s:1:\"a\";i:199;s:1:\"b\";s:17:\"testimonial_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"Testimonials\";s:1:\"r\";a:1:{i:0;i:1;}}i:199;a:5:{s:1:\"a\";i:200;s:1:\"b\";s:19:\"testimonial_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"Testimonials\";s:1:\"r\";a:1:{i:0;i:1;}}i:200;a:5:{s:1:\"a\";i:201;s:1:\"b\";s:24:\"testimonial_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"Testimonials\";s:1:\"r\";a:1:{i:0;i:1;}}i:201;a:5:{s:1:\"a\";i:202;s:1:\"b\";s:11:\"review_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:7:\"Reviews\";s:1:\"r\";a:1:{i:0;i:1;}}i:202;a:5:{s:1:\"a\";i:203;s:1:\"b\";s:11:\"review_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:7:\"Reviews\";s:1:\"r\";a:1:{i:0;i:1;}}i:203;a:5:{s:1:\"a\";i:204;s:1:\"b\";s:13:\"review_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:7:\"Reviews\";s:1:\"r\";a:1:{i:0;i:1;}}i:204;a:5:{s:1:\"a\";i:205;s:1:\"b\";s:13:\"review_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:7:\"Reviews\";s:1:\"r\";a:1:{i:0;i:1;}}i:205;a:5:{s:1:\"a\";i:206;s:1:\"b\";s:13:\"review_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:7:\"Reviews\";s:1:\"r\";a:1:{i:0;i:1;}}i:206;a:5:{s:1:\"a\";i:207;s:1:\"b\";s:14:\"review_publish\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:7:\"Reviews\";s:1:\"r\";a:1:{i:0;i:1;}}i:207;a:5:{s:1:\"a\";i:208;s:1:\"b\";s:16:\"review_unpublish\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:7:\"Reviews\";s:1:\"r\";a:1:{i:0;i:1;}}i:208;a:5:{s:1:\"a\";i:209;s:1:\"b\";s:19:\"review_set_featured\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:7:\"Reviews\";s:1:\"r\";a:1:{i:0;i:1;}}i:209;a:5:{s:1:\"a\";i:210;s:1:\"b\";s:8:\"faq_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:4:\"FAQs\";s:1:\"r\";a:1:{i:0;i:1;}}i:210;a:5:{s:1:\"a\";i:211;s:1:\"b\";s:8:\"faq_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:4:\"FAQs\";s:1:\"r\";a:1:{i:0;i:1;}}i:211;a:5:{s:1:\"a\";i:212;s:1:\"b\";s:10:\"faq_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:4:\"FAQs\";s:1:\"r\";a:1:{i:0;i:1;}}i:212;a:5:{s:1:\"a\";i:213;s:1:\"b\";s:10:\"faq_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:4:\"FAQs\";s:1:\"r\";a:1:{i:0;i:1;}}i:213;a:5:{s:1:\"a\";i:214;s:1:\"b\";s:10:\"faq_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:4:\"FAQs\";s:1:\"r\";a:1:{i:0;i:1;}}i:214;a:5:{s:1:\"a\";i:215;s:1:\"b\";s:10:\"faq_toggle\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:4:\"FAQs\";s:1:\"r\";a:1:{i:0;i:1;}}i:215;a:5:{s:1:\"a\";i:216;s:1:\"b\";s:11:\"faq_reorder\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:4:\"FAQs\";s:1:\"r\";a:1:{i:0;i:1;}}i:216;a:5:{s:1:\"a\";i:217;s:1:\"b\";s:9:\"faq_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:4:\"FAQs\";s:1:\"r\";a:1:{i:0;i:1;}}i:217;a:5:{s:1:\"a\";i:218;s:1:\"b\";s:11:\"faq_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:4:\"FAQs\";s:1:\"r\";a:1:{i:0;i:1;}}i:218;a:5:{s:1:\"a\";i:219;s:1:\"b\";s:16:\"faq_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:4:\"FAQs\";s:1:\"r\";a:1:{i:0;i:1;}}i:219;a:5:{s:1:\"a\";i:220;s:1:\"b\";s:18:\"blog_category_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Blog Categories\";s:1:\"r\";a:1:{i:0;i:1;}}i:220;a:5:{s:1:\"a\";i:221;s:1:\"b\";s:18:\"blog_category_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Blog Categories\";s:1:\"r\";a:1:{i:0;i:1;}}i:221;a:5:{s:1:\"a\";i:222;s:1:\"b\";s:20:\"blog_category_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Blog Categories\";s:1:\"r\";a:1:{i:0;i:1;}}i:222;a:5:{s:1:\"a\";i:223;s:1:\"b\";s:20:\"blog_category_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Blog Categories\";s:1:\"r\";a:1:{i:0;i:1;}}i:223;a:5:{s:1:\"a\";i:224;s:1:\"b\";s:20:\"blog_category_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Blog Categories\";s:1:\"r\";a:1:{i:0;i:1;}}i:224;a:5:{s:1:\"a\";i:225;s:1:\"b\";s:19:\"blog_category_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Blog Categories\";s:1:\"r\";a:1:{i:0;i:1;}}i:225;a:5:{s:1:\"a\";i:226;s:1:\"b\";s:21:\"blog_category_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Blog Categories\";s:1:\"r\";a:1:{i:0;i:1;}}i:226;a:5:{s:1:\"a\";i:227;s:1:\"b\";s:26:\"blog_category_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Blog Categories\";s:1:\"r\";a:1:{i:0;i:1;}}i:227;a:5:{s:1:\"a\";i:228;s:1:\"b\";s:20:\"blog_category_toggle\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Blog Categories\";s:1:\"r\";a:1:{i:0;i:1;}}i:228;a:5:{s:1:\"a\";i:229;s:1:\"b\";s:14:\"blog_post_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:10:\"Blog Posts\";s:1:\"r\";a:1:{i:0;i:1;}}i:229;a:5:{s:1:\"a\";i:230;s:1:\"b\";s:14:\"blog_post_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:10:\"Blog Posts\";s:1:\"r\";a:1:{i:0;i:1;}}i:230;a:5:{s:1:\"a\";i:231;s:1:\"b\";s:16:\"blog_post_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:10:\"Blog Posts\";s:1:\"r\";a:1:{i:0;i:1;}}i:231;a:5:{s:1:\"a\";i:232;s:1:\"b\";s:16:\"blog_post_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:10:\"Blog Posts\";s:1:\"r\";a:1:{i:0;i:1;}}i:232;a:5:{s:1:\"a\";i:233;s:1:\"b\";s:16:\"blog_post_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:10:\"Blog Posts\";s:1:\"r\";a:1:{i:0;i:1;}}i:233;a:5:{s:1:\"a\";i:234;s:1:\"b\";s:15:\"blog_post_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:10:\"Blog Posts\";s:1:\"r\";a:1:{i:0;i:1;}}i:234;a:5:{s:1:\"a\";i:235;s:1:\"b\";s:17:\"blog_post_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:10:\"Blog Posts\";s:1:\"r\";a:1:{i:0;i:1;}}i:235;a:5:{s:1:\"a\";i:236;s:1:\"b\";s:22:\"blog_post_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:10:\"Blog Posts\";s:1:\"r\";a:1:{i:0;i:1;}}i:236;a:5:{s:1:\"a\";i:237;s:1:\"b\";s:17:\"blog_post_publish\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:10:\"Blog Posts\";s:1:\"r\";a:1:{i:0;i:1;}}i:237;a:5:{s:1:\"a\";i:238;s:1:\"b\";s:19:\"blog_post_unpublish\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:10:\"Blog Posts\";s:1:\"r\";a:1:{i:0;i:1;}}i:238;a:5:{s:1:\"a\";i:239;s:1:\"b\";s:17:\"blog_post_preview\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:10:\"Blog Posts\";s:1:\"r\";a:1:{i:0;i:1;}}i:239;a:5:{s:1:\"a\";i:240;s:1:\"b\";s:19:\"blog_post_duplicate\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:10:\"Blog Posts\";s:1:\"r\";a:1:{i:0;i:1;}}i:240;a:5:{s:1:\"a\";i:241;s:1:\"b\";s:20:\"contact_channel_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Contact Channels\";s:1:\"r\";a:1:{i:0;i:1;}}i:241;a:5:{s:1:\"a\";i:242;s:1:\"b\";s:20:\"contact_channel_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Contact Channels\";s:1:\"r\";a:1:{i:0;i:1;}}i:242;a:5:{s:1:\"a\";i:243;s:1:\"b\";s:22:\"contact_channel_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Contact Channels\";s:1:\"r\";a:1:{i:0;i:1;}}i:243;a:5:{s:1:\"a\";i:244;s:1:\"b\";s:22:\"contact_channel_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Contact Channels\";s:1:\"r\";a:1:{i:0;i:1;}}i:244;a:5:{s:1:\"a\";i:245;s:1:\"b\";s:22:\"contact_channel_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Contact Channels\";s:1:\"r\";a:1:{i:0;i:1;}}i:245;a:5:{s:1:\"a\";i:246;s:1:\"b\";s:22:\"contact_channel_toggle\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Contact Channels\";s:1:\"r\";a:1:{i:0;i:1;}}i:246;a:5:{s:1:\"a\";i:247;s:1:\"b\";s:23:\"contact_channel_reorder\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Contact Channels\";s:1:\"r\";a:1:{i:0;i:1;}}i:247;a:5:{s:1:\"a\";i:248;s:1:\"b\";s:27:\"contact_channel_set_default\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Contact Channels\";s:1:\"r\";a:1:{i:0;i:1;}}i:248;a:5:{s:1:\"a\";i:249;s:1:\"b\";s:21:\"contact_channel_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Contact Channels\";s:1:\"r\";a:1:{i:0;i:1;}}i:249;a:5:{s:1:\"a\";i:250;s:1:\"b\";s:23:\"contact_channel_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Contact Channels\";s:1:\"r\";a:1:{i:0;i:1;}}i:250;a:5:{s:1:\"a\";i:251;s:1:\"b\";s:28:\"contact_channel_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Contact Channels\";s:1:\"r\";a:1:{i:0;i:1;}}i:251;a:5:{s:1:\"a\";i:252;s:1:\"b\";s:20:\"contact_message_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Contact Messages\";s:1:\"r\";a:1:{i:0;i:1;}}i:252;a:5:{s:1:\"a\";i:253;s:1:\"b\";s:20:\"contact_message_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Contact Messages\";s:1:\"r\";a:1:{i:0;i:1;}}i:253;a:5:{s:1:\"a\";i:254;s:1:\"b\";s:22:\"contact_message_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Contact Messages\";s:1:\"r\";a:1:{i:0;i:1;}}i:254;a:5:{s:1:\"a\";i:255;s:1:\"b\";s:25:\"contact_message_mark_read\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Contact Messages\";s:1:\"r\";a:1:{i:0;i:1;}}i:255;a:5:{s:1:\"a\";i:256;s:1:\"b\";s:27:\"contact_message_mark_unread\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Contact Messages\";s:1:\"r\";a:1:{i:0;i:1;}}i:256;a:5:{s:1:\"a\";i:257;s:1:\"b\";s:21:\"contact_message_reply\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Contact Messages\";s:1:\"r\";a:1:{i:0;i:1;}}i:257;a:5:{s:1:\"a\";i:258;s:1:\"b\";s:19:\"contact_target_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Contact Targets\";s:1:\"r\";a:1:{i:0;i:1;}}i:258;a:5:{s:1:\"a\";i:259;s:1:\"b\";s:19:\"contact_target_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Contact Targets\";s:1:\"r\";a:1:{i:0;i:1;}}i:259;a:5:{s:1:\"a\";i:260;s:1:\"b\";s:21:\"contact_target_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Contact Targets\";s:1:\"r\";a:1:{i:0;i:1;}}i:260;a:5:{s:1:\"a\";i:261;s:1:\"b\";s:21:\"contact_target_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Contact Targets\";s:1:\"r\";a:1:{i:0;i:1;}}i:261;a:5:{s:1:\"a\";i:262;s:1:\"b\";s:21:\"contact_target_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Contact Targets\";s:1:\"r\";a:1:{i:0;i:1;}}i:262;a:5:{s:1:\"a\";i:263;s:1:\"b\";s:20:\"contact_target_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Contact Targets\";s:1:\"r\";a:1:{i:0;i:1;}}i:263;a:5:{s:1:\"a\";i:264;s:1:\"b\";s:22:\"contact_target_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Contact Targets\";s:1:\"r\";a:1:{i:0;i:1;}}i:264;a:5:{s:1:\"a\";i:265;s:1:\"b\";s:27:\"contact_target_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:15:\"Contact Targets\";s:1:\"r\";a:1:{i:0;i:1;}}i:265;a:5:{s:1:\"a\";i:266;s:1:\"b\";s:9:\"lead_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Leads\";s:1:\"r\";a:1:{i:0;i:1;}}i:266;a:5:{s:1:\"a\";i:267;s:1:\"b\";s:9:\"lead_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Leads\";s:1:\"r\";a:1:{i:0;i:1;}}i:267;a:5:{s:1:\"a\";i:268;s:1:\"b\";s:11:\"lead_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Leads\";s:1:\"r\";a:1:{i:0;i:1;}}i:268;a:5:{s:1:\"a\";i:269;s:1:\"b\";s:11:\"lead_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Leads\";s:1:\"r\";a:1:{i:0;i:1;}}i:269;a:5:{s:1:\"a\";i:270;s:1:\"b\";s:11:\"lead_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Leads\";s:1:\"r\";a:1:{i:0;i:1;}}i:270;a:5:{s:1:\"a\";i:271;s:1:\"b\";s:10:\"lead_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Leads\";s:1:\"r\";a:1:{i:0;i:1;}}i:271;a:5:{s:1:\"a\";i:272;s:1:\"b\";s:12:\"lead_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Leads\";s:1:\"r\";a:1:{i:0;i:1;}}i:272;a:5:{s:1:\"a\";i:273;s:1:\"b\";s:17:\"lead_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Leads\";s:1:\"r\";a:1:{i:0;i:1;}}i:273;a:5:{s:1:\"a\";i:274;s:1:\"b\";s:11:\"lead_assign\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Leads\";s:1:\"r\";a:1:{i:0;i:1;}}i:274;a:5:{s:1:\"a\";i:275;s:1:\"b\";s:18:\"lead_change_status\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Leads\";s:1:\"r\";a:1:{i:0;i:1;}}i:275;a:5:{s:1:\"a\";i:276;s:1:\"b\";s:11:\"lead_export\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Leads\";s:1:\"r\";a:1:{i:0;i:1;}}i:276;a:5:{s:1:\"a\";i:277;s:1:\"b\";s:18:\"lead_internal_note\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Leads\";s:1:\"r\";a:1:{i:0;i:1;}}i:277;a:5:{s:1:\"a\";i:278;s:1:\"b\";s:16:\"lead_public_note\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Leads\";s:1:\"r\";a:1:{i:0;i:1;}}i:278;a:5:{s:1:\"a\";i:279;s:1:\"b\";s:24:\"lead_attachment_download\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Leads\";s:1:\"r\";a:1:{i:0;i:1;}}i:279;a:5:{s:1:\"a\";i:280;s:1:\"b\";s:20:\"lead_form_field_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Lead Form Fields\";s:1:\"r\";a:1:{i:0;i:1;}}i:280;a:5:{s:1:\"a\";i:281;s:1:\"b\";s:20:\"lead_form_field_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Lead Form Fields\";s:1:\"r\";a:1:{i:0;i:1;}}i:281;a:5:{s:1:\"a\";i:282;s:1:\"b\";s:22:\"lead_form_field_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Lead Form Fields\";s:1:\"r\";a:1:{i:0;i:1;}}i:282;a:5:{s:1:\"a\";i:283;s:1:\"b\";s:22:\"lead_form_field_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Lead Form Fields\";s:1:\"r\";a:1:{i:0;i:1;}}i:283;a:5:{s:1:\"a\";i:284;s:1:\"b\";s:22:\"lead_form_field_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Lead Form Fields\";s:1:\"r\";a:1:{i:0;i:1;}}i:284;a:5:{s:1:\"a\";i:285;s:1:\"b\";s:22:\"lead_form_field_toggle\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Lead Form Fields\";s:1:\"r\";a:1:{i:0;i:1;}}i:285;a:5:{s:1:\"a\";i:286;s:1:\"b\";s:23:\"lead_form_field_reorder\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Lead Form Fields\";s:1:\"r\";a:1:{i:0;i:1;}}i:286;a:5:{s:1:\"a\";i:287;s:1:\"b\";s:21:\"lead_form_field_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Lead Form Fields\";s:1:\"r\";a:1:{i:0;i:1;}}i:287;a:5:{s:1:\"a\";i:288;s:1:\"b\";s:23:\"lead_form_field_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Lead Form Fields\";s:1:\"r\";a:1:{i:0;i:1;}}i:288;a:5:{s:1:\"a\";i:289;s:1:\"b\";s:28:\"lead_form_field_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:16:\"Lead Form Fields\";s:1:\"r\";a:1:{i:0;i:1;}}i:289;a:5:{s:1:\"a\";i:290;s:1:\"b\";s:16:\"seo_setting_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"SEO Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:290;a:5:{s:1:\"a\";i:291;s:1:\"b\";s:18:\"seo_setting_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"SEO Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:291;a:5:{s:1:\"a\";i:292;s:1:\"b\";s:18:\"seo_sitemap_manage\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"SEO Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:292;a:5:{s:1:\"a\";i:293;s:1:\"b\";s:17:\"seo_schema_manage\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"SEO Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:293;a:5:{s:1:\"a\";i:294;s:1:\"b\";s:17:\"seo_social_manage\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"SEO Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:294;a:5:{s:1:\"a\";i:295;s:1:\"b\";s:13:\"seo_meta_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"SEO Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:295;a:5:{s:1:\"a\";i:296;s:1:\"b\";s:13:\"seo_meta_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"SEO Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:296;a:5:{s:1:\"a\";i:297;s:1:\"b\";s:15:\"seo_meta_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"SEO Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:297;a:5:{s:1:\"a\";i:298;s:1:\"b\";s:15:\"seo_meta_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"SEO Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:298;a:5:{s:1:\"a\";i:299;s:1:\"b\";s:15:\"seo_meta_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"SEO Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:299;a:5:{s:1:\"a\";i:300;s:1:\"b\";s:14:\"seo_meta_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"SEO Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:300;a:5:{s:1:\"a\";i:301;s:1:\"b\";s:16:\"seo_meta_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"SEO Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:301;a:5:{s:1:\"a\";i:302;s:1:\"b\";s:21:\"seo_meta_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:12:\"SEO Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:302;a:5:{s:1:\"a\";i:303;s:1:\"b\";s:13:\"redirect_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Redirects\";s:1:\"r\";a:1:{i:0;i:1;}}i:303;a:5:{s:1:\"a\";i:304;s:1:\"b\";s:13:\"redirect_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Redirects\";s:1:\"r\";a:1:{i:0;i:1;}}i:304;a:5:{s:1:\"a\";i:305;s:1:\"b\";s:15:\"redirect_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Redirects\";s:1:\"r\";a:1:{i:0;i:1;}}i:305;a:5:{s:1:\"a\";i:306;s:1:\"b\";s:15:\"redirect_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Redirects\";s:1:\"r\";a:1:{i:0;i:1;}}i:306;a:5:{s:1:\"a\";i:307;s:1:\"b\";s:15:\"redirect_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Redirects\";s:1:\"r\";a:1:{i:0;i:1;}}i:307;a:5:{s:1:\"a\";i:308;s:1:\"b\";s:15:\"redirect_toggle\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Redirects\";s:1:\"r\";a:1:{i:0;i:1;}}i:308;a:5:{s:1:\"a\";i:309;s:1:\"b\";s:15:\"redirect_import\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Redirects\";s:1:\"r\";a:1:{i:0;i:1;}}i:309;a:5:{s:1:\"a\";i:310;s:1:\"b\";s:15:\"redirect_export\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Redirects\";s:1:\"r\";a:1:{i:0;i:1;}}i:310;a:5:{s:1:\"a\";i:311;s:1:\"b\";s:14:\"redirect_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Redirects\";s:1:\"r\";a:1:{i:0;i:1;}}i:311;a:5:{s:1:\"a\";i:312;s:1:\"b\";s:16:\"redirect_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Redirects\";s:1:\"r\";a:1:{i:0;i:1;}}i:312;a:5:{s:1:\"a\";i:313;s:1:\"b\";s:21:\"redirect_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Redirects\";s:1:\"r\";a:1:{i:0;i:1;}}i:313;a:5:{s:1:\"a\";i:314;s:1:\"b\";s:21:\"tracking_setting_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Tracking Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:314;a:5:{s:1:\"a\";i:315;s:1:\"b\";s:23:\"tracking_setting_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Tracking Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:315;a:5:{s:1:\"a\";i:316;s:1:\"b\";s:21:\"tracking_event_manage\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Tracking Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:316;a:5:{s:1:\"a\";i:317;s:1:\"b\";s:19:\"tracking_test_event\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Tracking Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:317;a:5:{s:1:\"a\";i:318;s:1:\"b\";s:23:\"tracking_consent_manage\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Tracking Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:318;a:5:{s:1:\"a\";i:319;s:1:\"b\";s:22:\"tracking_provider_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Tracking Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:319;a:5:{s:1:\"a\";i:320;s:1:\"b\";s:22:\"tracking_provider_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Tracking Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:320;a:5:{s:1:\"a\";i:321;s:1:\"b\";s:24:\"tracking_provider_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Tracking Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:321;a:5:{s:1:\"a\";i:322;s:1:\"b\";s:24:\"tracking_provider_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Tracking Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:322;a:5:{s:1:\"a\";i:323;s:1:\"b\";s:24:\"tracking_provider_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Tracking Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:323;a:5:{s:1:\"a\";i:324;s:1:\"b\";s:24:\"tracking_provider_toggle\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Tracking Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:324;a:5:{s:1:\"a\";i:325;s:1:\"b\";s:23:\"tracking_provider_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Tracking Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:325;a:5:{s:1:\"a\";i:326;s:1:\"b\";s:25:\"tracking_provider_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Tracking Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:326;a:5:{s:1:\"a\";i:327;s:1:\"b\";s:30:\"tracking_provider_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Tracking Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:327;a:5:{s:1:\"a\";i:328;s:1:\"b\";s:24:\"tracking_event_rule_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Tracking Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:328;a:5:{s:1:\"a\";i:329;s:1:\"b\";s:24:\"tracking_event_rule_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Tracking Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:329;a:5:{s:1:\"a\";i:330;s:1:\"b\";s:26:\"tracking_event_rule_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Tracking Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:330;a:5:{s:1:\"a\";i:331;s:1:\"b\";s:26:\"tracking_event_rule_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Tracking Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:331;a:5:{s:1:\"a\";i:332;s:1:\"b\";s:26:\"tracking_event_rule_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Tracking Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:332;a:5:{s:1:\"a\";i:333;s:1:\"b\";s:26:\"tracking_event_rule_toggle\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Tracking Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:333;a:5:{s:1:\"a\";i:334;s:1:\"b\";s:25:\"tracking_event_rule_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Tracking Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:334;a:5:{s:1:\"a\";i:335;s:1:\"b\";s:27:\"tracking_event_rule_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Tracking Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:335;a:5:{s:1:\"a\";i:336;s:1:\"b\";s:32:\"tracking_event_rule_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Tracking Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:336;a:5:{s:1:\"a\";i:337;s:1:\"b\";s:15:\"meta_pixel_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:10:\"Meta Pixel\";s:1:\"r\";a:1:{i:0;i:1;}}i:337;a:5:{s:1:\"a\";i:338;s:1:\"b\";s:17:\"meta_pixel_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:10:\"Meta Pixel\";s:1:\"r\";a:1:{i:0;i:1;}}i:338;a:5:{s:1:\"a\";i:339;s:1:\"b\";s:17:\"meta_pixel_toggle\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:10:\"Meta Pixel\";s:1:\"r\";a:1:{i:0;i:1;}}i:339;a:5:{s:1:\"a\";i:340;s:1:\"b\";s:15:\"meta_pixel_test\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:10:\"Meta Pixel\";s:1:\"r\";a:1:{i:0;i:1;}}i:340;a:5:{s:1:\"a\";i:341;s:1:\"b\";s:16:\"meta_capi_manage\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:10:\"Meta Pixel\";s:1:\"r\";a:1:{i:0;i:1;}}i:341;a:5:{s:1:\"a\";i:342;s:1:\"b\";s:10:\"admin_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:6:\"Admins\";s:1:\"r\";a:1:{i:0;i:1;}}i:342;a:5:{s:1:\"a\";i:343;s:1:\"b\";s:10:\"admin_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:6:\"Admins\";s:1:\"r\";a:1:{i:0;i:1;}}i:343;a:5:{s:1:\"a\";i:344;s:1:\"b\";s:12:\"admin_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:6:\"Admins\";s:1:\"r\";a:1:{i:0;i:1;}}i:344;a:5:{s:1:\"a\";i:345;s:1:\"b\";s:12:\"admin_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:6:\"Admins\";s:1:\"r\";a:1:{i:0;i:1;}}i:345;a:5:{s:1:\"a\";i:346;s:1:\"b\";s:12:\"admin_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:6:\"Admins\";s:1:\"r\";a:1:{i:0;i:1;}}i:346;a:5:{s:1:\"a\";i:347;s:1:\"b\";s:11:\"admin_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:6:\"Admins\";s:1:\"r\";a:1:{i:0;i:1;}}i:347;a:5:{s:1:\"a\";i:348;s:1:\"b\";s:13:\"admin_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:6:\"Admins\";s:1:\"r\";a:1:{i:0;i:1;}}i:348;a:5:{s:1:\"a\";i:349;s:1:\"b\";s:18:\"admin_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:6:\"Admins\";s:1:\"r\";a:1:{i:0;i:1;}}i:349;a:5:{s:1:\"a\";i:350;s:1:\"b\";s:19:\"admin_change_status\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:6:\"Admins\";s:1:\"r\";a:1:{i:0;i:1;}}i:350;a:5:{s:1:\"a\";i:351;s:1:\"b\";s:17:\"admin_assign_role\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:6:\"Admins\";s:1:\"r\";a:1:{i:0;i:1;}}i:351;a:5:{s:1:\"a\";i:352;s:1:\"b\";s:20:\"admin_reset_password\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:6:\"Admins\";s:1:\"r\";a:1:{i:0;i:1;}}i:352;a:5:{s:1:\"a\";i:353;s:1:\"b\";s:9:\"user_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Users\";s:1:\"r\";a:1:{i:0;i:1;}}i:353;a:5:{s:1:\"a\";i:354;s:1:\"b\";s:9:\"user_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Users\";s:1:\"r\";a:1:{i:0;i:1;}}i:354;a:5:{s:1:\"a\";i:355;s:1:\"b\";s:11:\"user_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Users\";s:1:\"r\";a:1:{i:0;i:1;}}i:355;a:5:{s:1:\"a\";i:356;s:1:\"b\";s:11:\"user_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Users\";s:1:\"r\";a:1:{i:0;i:1;}}i:356;a:5:{s:1:\"a\";i:357;s:1:\"b\";s:11:\"user_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Users\";s:1:\"r\";a:1:{i:0;i:1;}}i:357;a:5:{s:1:\"a\";i:358;s:1:\"b\";s:18:\"user_change_status\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Users\";s:1:\"r\";a:1:{i:0;i:1;}}i:358;a:5:{s:1:\"a\";i:359;s:1:\"b\";s:19:\"user_view_enquiries\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Users\";s:1:\"r\";a:1:{i:0;i:1;}}i:359;a:5:{s:1:\"a\";i:360;s:1:\"b\";s:19:\"user_reset_password\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Users\";s:1:\"r\";a:1:{i:0;i:1;}}i:360;a:5:{s:1:\"a\";i:361;s:1:\"b\";s:9:\"role_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Roles\";s:1:\"r\";a:1:{i:0;i:1;}}i:361;a:5:{s:1:\"a\";i:362;s:1:\"b\";s:9:\"role_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Roles\";s:1:\"r\";a:1:{i:0;i:1;}}i:362;a:5:{s:1:\"a\";i:363;s:1:\"b\";s:11:\"role_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Roles\";s:1:\"r\";a:1:{i:0;i:1;}}i:363;a:5:{s:1:\"a\";i:364;s:1:\"b\";s:11:\"role_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Roles\";s:1:\"r\";a:1:{i:0;i:1;}}i:364;a:5:{s:1:\"a\";i:365;s:1:\"b\";s:11:\"role_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Roles\";s:1:\"r\";a:1:{i:0;i:1;}}i:365;a:5:{s:1:\"a\";i:366;s:1:\"b\";s:10:\"role_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Roles\";s:1:\"r\";a:1:{i:0;i:1;}}i:366;a:5:{s:1:\"a\";i:367;s:1:\"b\";s:12:\"role_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Roles\";s:1:\"r\";a:1:{i:0;i:1;}}i:367;a:5:{s:1:\"a\";i:368;s:1:\"b\";s:17:\"role_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Roles\";s:1:\"r\";a:1:{i:0;i:1;}}i:368;a:5:{s:1:\"a\";i:369;s:1:\"b\";s:22:\"role_assign_permission\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:5:\"Roles\";s:1:\"r\";a:1:{i:0;i:1;}}i:369;a:5:{s:1:\"a\";i:370;s:1:\"b\";s:15:\"permission_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:11:\"Permissions\";s:1:\"r\";a:1:{i:0;i:1;}}i:370;a:5:{s:1:\"a\";i:371;s:1:\"b\";s:15:\"permission_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:11:\"Permissions\";s:1:\"r\";a:1:{i:0;i:1;}}i:371;a:5:{s:1:\"a\";i:372;s:1:\"b\";s:17:\"permission_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:11:\"Permissions\";s:1:\"r\";a:1:{i:0;i:1;}}i:372;a:5:{s:1:\"a\";i:373;s:1:\"b\";s:17:\"permission_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:11:\"Permissions\";s:1:\"r\";a:1:{i:0;i:1;}}i:373;a:5:{s:1:\"a\";i:374;s:1:\"b\";s:17:\"permission_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:11:\"Permissions\";s:1:\"r\";a:1:{i:0;i:1;}}i:374;a:5:{s:1:\"a\";i:375;s:1:\"b\";s:16:\"permission_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:11:\"Permissions\";s:1:\"r\";a:1:{i:0;i:1;}}i:375;a:5:{s:1:\"a\";i:376;s:1:\"b\";s:18:\"permission_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:11:\"Permissions\";s:1:\"r\";a:1:{i:0;i:1;}}i:376;a:5:{s:1:\"a\";i:377;s:1:\"b\";s:23:\"permission_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:11:\"Permissions\";s:1:\"r\";a:1:{i:0;i:1;}}i:377;a:5:{s:1:\"a\";i:378;s:1:\"b\";s:14:\"audit_log_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:10:\"Audit Logs\";s:1:\"r\";a:1:{i:0;i:1;}}i:378;a:5:{s:1:\"a\";i:379;s:1:\"b\";s:14:\"audit_log_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:10:\"Audit Logs\";s:1:\"r\";a:1:{i:0;i:1;}}i:379;a:5:{s:1:\"a\";i:380;s:1:\"b\";s:16:\"audit_log_export\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:10:\"Audit Logs\";s:1:\"r\";a:1:{i:0;i:1;}}i:380;a:5:{s:1:\"a\";i:381;s:1:\"b\";s:16:\"audit_log_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:10:\"Audit Logs\";s:1:\"r\";a:1:{i:0;i:1;}}i:381;a:5:{s:1:\"a\";i:382;s:1:\"b\";s:15:\"audit_log_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:10:\"Audit Logs\";s:1:\"r\";a:1:{i:0;i:1;}}i:382;a:5:{s:1:\"a\";i:383;s:1:\"b\";s:17:\"audit_log_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:10:\"Audit Logs\";s:1:\"r\";a:1:{i:0;i:1;}}i:383;a:5:{s:1:\"a\";i:384;s:1:\"b\";s:22:\"audit_log_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:10:\"Audit Logs\";s:1:\"r\";a:1:{i:0;i:1;}}i:384;a:5:{s:1:\"a\";i:385;s:1:\"b\";s:13:\"campaign_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Campaigns\";s:1:\"r\";a:1:{i:0;i:1;}}i:385;a:5:{s:1:\"a\";i:386;s:1:\"b\";s:13:\"campaign_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Campaigns\";s:1:\"r\";a:1:{i:0;i:1;}}i:386;a:5:{s:1:\"a\";i:387;s:1:\"b\";s:15:\"campaign_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Campaigns\";s:1:\"r\";a:1:{i:0;i:1;}}i:387;a:5:{s:1:\"a\";i:388;s:1:\"b\";s:15:\"campaign_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Campaigns\";s:1:\"r\";a:1:{i:0;i:1;}}i:388;a:5:{s:1:\"a\";i:389;s:1:\"b\";s:15:\"campaign_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Campaigns\";s:1:\"r\";a:1:{i:0;i:1;}}i:389;a:5:{s:1:\"a\";i:390;s:1:\"b\";s:14:\"campaign_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Campaigns\";s:1:\"r\";a:1:{i:0;i:1;}}i:390;a:5:{s:1:\"a\";i:391;s:1:\"b\";s:16:\"campaign_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Campaigns\";s:1:\"r\";a:1:{i:0;i:1;}}i:391;a:5:{s:1:\"a\";i:392;s:1:\"b\";s:21:\"campaign_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Campaigns\";s:1:\"r\";a:1:{i:0;i:1;}}i:392;a:5:{s:1:\"a\";i:393;s:1:\"b\";s:16:\"campaign_publish\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Campaigns\";s:1:\"r\";a:1:{i:0;i:1;}}i:393;a:5:{s:1:\"a\";i:394;s:1:\"b\";s:18:\"campaign_unpublish\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Campaigns\";s:1:\"r\";a:1:{i:0;i:1;}}i:394;a:5:{s:1:\"a\";i:395;s:1:\"b\";s:16:\"campaign_preview\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Campaigns\";s:1:\"r\";a:1:{i:0;i:1;}}i:395;a:5:{s:1:\"a\";i:396;s:1:\"b\";s:18:\"campaign_duplicate\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Campaigns\";s:1:\"r\";a:1:{i:0;i:1;}}i:396;a:5:{s:1:\"a\";i:397;s:1:\"b\";s:20:\"campaign_set_default\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Campaigns\";s:1:\"r\";a:1:{i:0;i:1;}}i:397;a:5:{s:1:\"a\";i:398;s:1:\"b\";s:21:\"campaign_media_manage\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:9:\"Campaigns\";s:1:\"r\";a:1:{i:0;i:1;}}i:398;a:5:{s:1:\"a\";i:399;s:1:\"b\";s:21:\"campaign_section_list\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Campaign Sections\";s:1:\"r\";a:1:{i:0;i:1;}}i:399;a:5:{s:1:\"a\";i:400;s:1:\"b\";s:21:\"campaign_section_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Campaign Sections\";s:1:\"r\";a:1:{i:0;i:1;}}i:400;a:5:{s:1:\"a\";i:401;s:1:\"b\";s:23:\"campaign_section_create\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Campaign Sections\";s:1:\"r\";a:1:{i:0;i:1;}}i:401;a:5:{s:1:\"a\";i:402;s:1:\"b\";s:23:\"campaign_section_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Campaign Sections\";s:1:\"r\";a:1:{i:0;i:1;}}i:402;a:5:{s:1:\"a\";i:403;s:1:\"b\";s:23:\"campaign_section_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Campaign Sections\";s:1:\"r\";a:1:{i:0;i:1;}}i:403;a:5:{s:1:\"a\";i:404;s:1:\"b\";s:23:\"campaign_section_toggle\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Campaign Sections\";s:1:\"r\";a:1:{i:0;i:1;}}i:404;a:5:{s:1:\"a\";i:405;s:1:\"b\";s:24:\"campaign_section_reorder\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Campaign Sections\";s:1:\"r\";a:1:{i:0;i:1;}}i:405;a:5:{s:1:\"a\";i:406;s:1:\"b\";s:26:\"campaign_section_duplicate\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Campaign Sections\";s:1:\"r\";a:1:{i:0;i:1;}}i:406;a:5:{s:1:\"a\";i:407;s:1:\"b\";s:22:\"campaign_section_trash\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Campaign Sections\";s:1:\"r\";a:1:{i:0;i:1;}}i:407;a:5:{s:1:\"a\";i:408;s:1:\"b\";s:24:\"campaign_section_restore\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Campaign Sections\";s:1:\"r\";a:1:{i:0;i:1;}}i:408;a:5:{s:1:\"a\";i:409;s:1:\"b\";s:29:\"campaign_section_force_delete\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Campaign Sections\";s:1:\"r\";a:1:{i:0;i:1;}}i:409;a:5:{s:1:\"a\";i:410;s:1:\"b\";s:21:\"campaign_setting_view\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Campaign Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:410;a:5:{s:1:\"a\";i:411;s:1:\"b\";s:23:\"campaign_setting_update\";s:1:\"c\";s:5:\"admin\";s:1:\"d\";s:17:\"Campaign Settings\";s:1:\"r\";a:1:{i:0;i:1;}}i:411;a:5:{s:1:\"a\";i:412;s:1:\"b\";s:12:\"profile_view\";s:1:\"c\";s:3:\"web\";s:1:\"d\";s:10:\"User Panel\";s:1:\"r\";a:1:{i:0;i:2;}}i:412;a:5:{s:1:\"a\";i:413;s:1:\"b\";s:14:\"profile_update\";s:1:\"c\";s:3:\"web\";s:1:\"d\";s:10:\"User Panel\";s:1:\"r\";a:1:{i:0;i:2;}}i:413;a:5:{s:1:\"a\";i:414;s:1:\"b\";s:15:\"password_update\";s:1:\"c\";s:3:\"web\";s:1:\"d\";s:10:\"User Panel\";s:1:\"r\";a:1:{i:0;i:2;}}i:414;a:5:{s:1:\"a\";i:415;s:1:\"b\";s:14:\"enquiry_create\";s:1:\"c\";s:3:\"web\";s:1:\"d\";s:10:\"User Panel\";s:1:\"r\";a:1:{i:0;i:2;}}i:415;a:5:{s:1:\"a\";i:416;s:1:\"b\";s:16:\"enquiry_own_list\";s:1:\"c\";s:3:\"web\";s:1:\"d\";s:10:\"User Panel\";s:1:\"r\";a:1:{i:0;i:2;}}i:416;a:5:{s:1:\"a\";i:417;s:1:\"b\";s:16:\"enquiry_own_view\";s:1:\"c\";s:3:\"web\";s:1:\"d\";s:10:\"User Panel\";s:1:\"r\";a:1:{i:0;i:2;}}i:417;a:5:{s:1:\"a\";i:418;s:1:\"b\";s:29:\"enquiry_own_attachment_upload\";s:1:\"c\";s:3:\"web\";s:1:\"d\";s:10:\"User Panel\";s:1:\"r\";a:1:{i:0;i:2;}}i:418;a:5:{s:1:\"a\";i:419;s:1:\"b\";s:31:\"enquiry_own_attachment_download\";s:1:\"c\";s:3:\"web\";s:1:\"d\";s:10:\"User Panel\";s:1:\"r\";a:1:{i:0;i:2;}}}s:5:\"roles\";a:2:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:5:\"admin\";s:1:\"c\";s:5:\"admin\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:4:\"user\";s:1:\"c\";s:3:\"web\";}}}', 1792046968);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `campaigns`
--

CREATE TABLE `campaigns` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(190) NOT NULL,
  `slug` varchar(190) NOT NULL,
  `custom_route` varchar(255) DEFAULT NULL,
  `summary` text DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'draft',
  `hero_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`hero_config`)),
  `published_at` timestamp NULL DEFAULT NULL,
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `campaigns`
--

INSERT INTO `campaigns` (`id`, `title`, `slug`, `custom_route`, `summary`, `status`, `hero_config`, `published_at`, `starts_at`, `ends_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Starter Campaign', 'starter-campaign', NULL, '<p>Default draft campaign. Configure content and media before publishing.</p>', 'draft', '{\"heading\":\"Starter Campaign\",\"subheading\":\"Configure your campaign hero.\",\"rating\":5,\"overlay\":35,\"cta_label\":\"Get a Quote\",\"cta_url\":\"#quote\",\"embed_provider\":null,\"embed_url\":null,\"autoplay\":false,\"muted\":true,\"controls\":true,\"loop\":false}', NULL, NULL, NULL, 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(2, '[Draft] Campaign 01', 'draft-campaign-01', NULL, '<p>Inactive planning example; do not publish as a real offer.</p>', 'draft', '{\"heading\":\"[Draft] Campaign 01\"}', NULL, NULL, NULL, 1, 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(3, '[Draft] Campaign 02', 'draft-campaign-02', NULL, '<p>Inactive planning example; do not publish as a real offer.</p>', 'draft', '{\"heading\":\"[Draft] Campaign 02\"}', NULL, NULL, NULL, 1, 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(4, '[Draft] Campaign 03', 'draft-campaign-03', NULL, '<p>Inactive planning example; do not publish as a real offer.</p>', 'draft', '{\"heading\":\"[Draft] Campaign 03\"}', NULL, NULL, NULL, 1, 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(5, '[Draft] Campaign 04', 'draft-campaign-04', NULL, '<p>Inactive planning example; do not publish as a real offer.</p>', 'draft', '{\"heading\":\"[Draft] Campaign 04\"}', NULL, NULL, NULL, 1, 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(6, '[Draft] Campaign 05', 'draft-campaign-05', NULL, '<p>Inactive planning example; do not publish as a real offer.</p>', 'draft', '{\"heading\":\"[Draft] Campaign 05\"}', NULL, NULL, NULL, 1, 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(7, '[Draft] Campaign 06', 'draft-campaign-06', NULL, '<p>Inactive planning example; do not publish as a real offer.</p>', 'draft', '{\"heading\":\"[Draft] Campaign 06\"}', NULL, NULL, NULL, 1, 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(8, '[Draft] Campaign 07', 'draft-campaign-07', NULL, '<p>Inactive planning example; do not publish as a real offer.</p>', 'draft', '{\"heading\":\"[Draft] Campaign 07\"}', NULL, NULL, NULL, 1, 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(9, '[Draft] Campaign 08', 'draft-campaign-08', NULL, '<p>Inactive planning example; do not publish as a real offer.</p>', 'draft', '{\"heading\":\"[Draft] Campaign 08\"}', NULL, NULL, NULL, 1, 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(10, '[Draft] Campaign 09', 'draft-campaign-09', NULL, '<p>Inactive planning example; do not publish as a real offer.</p>', 'draft', '{\"heading\":\"[Draft] Campaign 09\"}', NULL, NULL, NULL, 1, 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `campaign_sections`
--

CREATE TABLE `campaign_sections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `campaign_id` bigint(20) UNSIGNED NOT NULL,
  `section_key` varchar(80) NOT NULL,
  `heading` varchar(190) DEFAULT NULL,
  `subheading` varchar(255) DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payload`)),
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `campaign_sections`
--

INSERT INTO `campaign_sections` (`id`, `campaign_id`, `section_key`, `heading`, `subheading`, `payload`, `is_enabled`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'hero', 'Starter Campaign', 'Configure this section before publishing.', '{\"headline\":\"Starter Campaign\",\"subheadline\":\"Configure this section before publishing.\",\"cta_label\":\"Get a Quote\",\"cta_url\":\"#quote\"}', 1, 0, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(2, 2, 'hero', '[Draft] Campaign 01', 'Draft campaign section', '[]', 0, 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(3, 3, 'hero', '[Draft] Campaign 02', 'Draft campaign section', '[]', 0, 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(4, 4, 'hero', '[Draft] Campaign 03', 'Draft campaign section', '[]', 0, 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(5, 5, 'hero', '[Draft] Campaign 04', 'Draft campaign section', '[]', 0, 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(6, 6, 'hero', '[Draft] Campaign 05', 'Draft campaign section', '[]', 0, 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(7, 7, 'hero', '[Draft] Campaign 06', 'Draft campaign section', '[]', 0, 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(8, 8, 'hero', '[Draft] Campaign 07', 'Draft campaign section', '[]', 0, 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(9, 9, 'hero', '[Draft] Campaign 08', 'Draft campaign section', '[]', 0, 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(10, 10, 'hero', '[Draft] Campaign 09', 'Draft campaign section', '[]', 0, 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `campaign_settings`
--

CREATE TABLE `campaign_settings` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `default_campaign_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fallback_mode` varchar(30) NOT NULL DEFAULT 'homepage',
  `fallback_page_id` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `campaign_settings`
--

INSERT INTO `campaign_settings` (`id`, `default_campaign_id`, `fallback_mode`, `fallback_page_id`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, NULL, 'homepage', 1, 1, '2026-10-10 13:47:18', '2026-10-10 13:47:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(190) NOT NULL,
  `slug` varchar(190) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'General', 'general', '<p>Default blog category.</p>', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(2, 'Painting Guides', 'painting-guides', 'Painting and renovation planning information.', 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(3, 'Wall Preparation', 'wall-preparation', 'Painting and renovation planning information.', 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(4, 'HDB Renovation', 'hdb-renovation', 'Painting and renovation planning information.', 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(5, 'Condo Renovation', 'condo-renovation', 'Painting and renovation planning information.', 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(6, 'Plastering Guides', 'plastering-guides', 'Painting and renovation planning information.', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(7, 'Paint Selection', 'paint-selection', 'Painting and renovation planning information.', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(8, 'Renovation Checklists', 'renovation-checklists', 'Painting and renovation planning information.', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(9, 'Colour Planning', 'colour-planning', 'Painting and renovation planning information.', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(10, 'Surface Care', 'surface-care', 'Painting and renovation planning information.', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(11, 'Project Planning', 'project-planning', 'Painting and renovation planning information.', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `contact_channels`
--

CREATE TABLE `contact_channels` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(30) NOT NULL,
  `label` varchar(120) NOT NULL,
  `region` varchar(120) DEFAULT NULL,
  `value` varchar(255) NOT NULL,
  `display_value` varchar(120) DEFAULT NULL,
  `message_template` text DEFAULT NULL,
  `icon` varchar(100) NOT NULL,
  `colour` varchar(20) NOT NULL,
  `availability_text` varchar(120) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `track_clicks` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_channels`
--

INSERT INTO `contact_channels` (`id`, `type`, `label`, `region`, `value`, `display_value`, `message_template`, `icon`, `colour`, `availability_text`, `is_default`, `is_active`, `track_clicks`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'whatsapp', 'WhatsApp(Any Location)', NULL, '+6523654645', '+6523654645', NULL, 'whatsapp', 'whatsapp', 'Sat-Thu 10 AM-6Pm', 0, 1, 1, 0, '2026-10-10 13:47:19', '2026-10-10 15:06:52', NULL),
(2, 'whatsapp', 'WhatsApp(East)', NULL, '+6591234542', '+6591234542', NULL, 'whatsapp', 'whatsapp', 'Sat-Thu 10 AM-6Pm', 0, 1, 1, 1, '2026-10-10 13:47:22', '2026-10-10 15:07:01', NULL),
(3, 'whatsapp', 'WhatsApp(West)', NULL, '+6591234542', '+6591234542', NULL, 'whatsapp', 'whatsapp', 'Sat-Thu 10 AM-6Pm', 0, 1, 1, 2, '2026-10-10 13:47:22', '2026-10-10 15:07:45', NULL),
(4, 'whatsapp', 'WhatsApp(South)', NULL, '+6591234542', '+6591234542', NULL, 'whatsapp', 'whatsapp', NULL, 1, 1, 1, 3, '2026-10-10 13:47:22', '2026-10-10 15:09:02', NULL),
(5, 'email', '[Draft] Channel 04', NULL, 'not-configured-04@example.invalid', 'not-configured-04@example.invalid', NULL, 'mail', '#216D61', NULL, 0, 0, 0, 4, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(6, 'email', '[Draft] Channel 05', NULL, 'not-configured-05@example.invalid', 'not-configured-05@example.invalid', NULL, 'mail', '#216D61', NULL, 0, 0, 0, 5, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(7, 'email', '[Draft] Channel 06', NULL, 'not-configured-06@example.invalid', 'not-configured-06@example.invalid', NULL, 'mail', '#216D61', NULL, 0, 0, 0, 6, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(8, 'email', '[Draft] Channel 07', NULL, 'not-configured-07@example.invalid', 'not-configured-07@example.invalid', NULL, 'mail', '#216D61', NULL, 0, 0, 0, 7, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(9, 'email', '[Draft] Channel 08', NULL, 'not-configured-08@example.invalid', 'not-configured-08@example.invalid', NULL, 'mail', '#216D61', NULL, 0, 0, 0, 8, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(10, 'email', '[Draft] Channel 09', NULL, 'not-configured-09@example.invalid', 'not-configured-09@example.invalid', NULL, 'mail', '#216D61', NULL, 0, 0, 0, 9, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(11, 'email', '[Draft] Channel 10', NULL, 'not-configured-10@example.invalid', 'not-configured-10@example.invalid', NULL, 'mail', '#216D61', NULL, 0, 0, 0, 10, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `contact_targets`
--

CREATE TABLE `contact_targets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `contact_channel_id` bigint(20) UNSIGNED NOT NULL,
  `targetable_type` varchar(255) NOT NULL,
  `targetable_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_targets`
--

INSERT INTO `contact_targets` (`id`, `contact_channel_id`, `targetable_type`, `targetable_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'App\\Models\\Page', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(2, 2, 'App\\Models\\Page', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(3, 3, 'App\\Models\\Page', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(4, 4, 'App\\Models\\Page', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(5, 5, 'App\\Models\\Page', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(6, 6, 'App\\Models\\Page', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(7, 7, 'App\\Models\\Page', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(8, 8, 'App\\Models\\Page', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(9, 9, 'App\\Models\\Page', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(10, 10, 'App\\Models\\Page', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(11, 11, 'App\\Models\\Page', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faqables`
--

CREATE TABLE `faqables` (
  `faq_id` bigint(20) UNSIGNED NOT NULL,
  `faqable_type` varchar(255) NOT NULL,
  `faqable_id` bigint(20) UNSIGNED NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faqables`
--

INSERT INTO `faqables` (`faq_id`, `faqable_type`, `faqable_id`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\Service', 1, 0, '2026-10-10 13:47:19', '2026-10-10 13:47:19'),
(2, 'App\\Models\\MenuItem', 1, 0, '2026-10-10 13:47:21', '2026-10-10 13:47:21'),
(2, 'App\\Models\\Page', 1, 30, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(3, 'App\\Models\\MenuItem', 1, 1, '2026-10-10 13:47:21', '2026-10-10 13:47:21'),
(3, 'App\\Models\\Page', 1, 30, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(4, 'App\\Models\\MenuItem', 1, 2, '2026-10-10 13:47:21', '2026-10-10 13:47:21'),
(4, 'App\\Models\\Page', 1, 30, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(5, 'App\\Models\\MenuItem', 2, 0, '2026-10-10 13:47:21', '2026-10-10 13:47:21'),
(5, 'App\\Models\\Page', 2, 30, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(6, 'App\\Models\\MenuItem', 2, 1, '2026-10-10 13:47:21', '2026-10-10 13:47:21'),
(6, 'App\\Models\\Page', 2, 30, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(7, 'App\\Models\\MenuItem', 2, 2, '2026-10-10 13:47:21', '2026-10-10 13:47:21'),
(7, 'App\\Models\\Page', 2, 30, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(8, 'App\\Models\\MenuItem', 2, 3, '2026-10-10 13:47:21', '2026-10-10 13:47:21'),
(8, 'App\\Models\\Page', 2, 30, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(9, 'App\\Models\\MenuItem', 3, 0, '2026-10-10 13:47:21', '2026-10-10 13:47:21'),
(9, 'App\\Models\\Page', 3, 30, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(10, 'App\\Models\\MenuItem', 3, 1, '2026-10-10 13:47:21', '2026-10-10 13:47:21'),
(10, 'App\\Models\\Page', 3, 30, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(11, 'App\\Models\\MenuItem', 3, 2, '2026-10-10 13:47:21', '2026-10-10 13:47:21'),
(11, 'App\\Models\\Page', 3, 30, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(12, 'App\\Models\\MenuItem', 4, 0, '2026-10-10 13:47:21', '2026-10-10 13:47:21'),
(12, 'App\\Models\\Page', 4, 30, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(13, 'App\\Models\\MenuItem', 4, 1, '2026-10-10 13:47:21', '2026-10-10 13:47:21'),
(13, 'App\\Models\\Page', 4, 30, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(14, 'App\\Models\\Page', 1, 100, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(15, 'App\\Models\\Page', 1, 101, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(16, 'App\\Models\\Page', 1, 102, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(17, 'App\\Models\\Page', 1, 103, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(18, 'App\\Models\\Page', 1, 104, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(19, 'App\\Models\\Page', 1, 105, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(20, 'App\\Models\\Page', 1, 106, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(21, 'App\\Models\\Page', 1, 107, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(22, 'App\\Models\\Page', 1, 108, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(23, 'App\\Models\\Page', 1, 109, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(24, 'App\\Models\\MenuItem', 1, 10, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(24, 'App\\Models\\Page', 1, 10, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(25, 'App\\Models\\MenuItem', 1, 11, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(25, 'App\\Models\\Page', 1, 11, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(26, 'App\\Models\\MenuItem', 1, 12, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(26, 'App\\Models\\Page', 1, 12, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(27, 'App\\Models\\MenuItem', 1, 13, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(27, 'App\\Models\\Page', 1, 13, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(28, 'App\\Models\\MenuItem', 1, 14, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(28, 'App\\Models\\Page', 1, 14, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(29, 'App\\Models\\MenuItem', 1, 15, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(29, 'App\\Models\\Page', 1, 15, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(30, 'App\\Models\\MenuItem', 1, 16, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(30, 'App\\Models\\Page', 1, 16, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(31, 'App\\Models\\MenuItem', 1, 17, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(31, 'App\\Models\\Page', 1, 17, '2026-10-10 13:47:22', '2026-10-10 13:47:22'),
(32, 'App\\Models\\Page', 2, 10, '2026-10-11 01:31:52', '2026-10-11 01:31:52'),
(33, 'App\\Models\\Page', 2, 20, '2026-10-11 01:31:52', '2026-10-11 01:31:52'),
(34, 'App\\Models\\Page', 2, 30, '2026-10-11 01:31:52', '2026-10-11 01:31:52'),
(35, 'App\\Models\\Page', 2, 40, '2026-10-11 01:31:52', '2026-10-11 01:31:52'),
(36, 'App\\Models\\Page', 2, 50, '2026-10-11 01:31:52', '2026-10-11 01:31:52'),
(37, 'App\\Models\\Page', 2, 60, '2026-10-11 01:31:52', '2026-10-11 01:31:52'),
(38, 'App\\Models\\Page', 3, 10, '2026-10-11 03:06:27', '2026-10-11 03:06:27'),
(39, 'App\\Models\\Page', 3, 20, '2026-10-11 03:06:27', '2026-10-11 03:06:27'),
(40, 'App\\Models\\Page', 3, 30, '2026-10-11 03:06:27', '2026-10-11 03:06:27'),
(41, 'App\\Models\\Page', 3, 40, '2026-10-11 03:06:27', '2026-10-11 03:06:27'),
(42, 'App\\Models\\Page', 3, 50, '2026-10-11 03:06:27', '2026-10-11 03:06:27'),
(43, 'App\\Models\\Page', 4, 30, '2026-10-14 03:08:25', '2026-10-14 03:08:25'),
(44, 'App\\Models\\Page', 4, 40, '2026-10-14 03:08:25', '2026-10-14 03:08:25'),
(45, 'App\\Models\\Page', 4, 50, '2026-10-14 03:08:25', '2026-10-14 03:08:25'),
(46, 'App\\Models\\Page', 4, 60, '2026-10-14 03:08:25', '2026-10-14 03:08:25'),
(47, 'App\\Models\\Page', 4, 70, '2026-10-14 03:08:25', '2026-10-14 03:08:25'),
(48, 'App\\Models\\Page', 4, 80, '2026-10-14 03:08:25', '2026-10-14 03:08:25');

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `question` text NOT NULL,
  `answer` longtext NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faqs`
--

INSERT INTO `faqs` (`id`, `question`, `answer`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Do you provide an on-site quotation?', '<p>Use the enquiry form to request a quotation and ask whether a site visit is needed for the work described.</p>', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:22', NULL),
(2, 'Which paint grades are shown in the comparison?', '<p>Available brands and finishes depend on the agreed quotation. Ask which products and grades are proposed for your rooms.</p>', 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(3, 'Does the painting price depend on flat size?', '<p>The number of rooms, surface conditions, preparation work and paint choice all affect quotation amounts.</p>', 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(4, 'Is the calculator estimate a final quotation?', '<p>No. Published guide figures are not a final quotation; confirm site conditions and work scope before accepting.</p>', 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(5, 'Should existing wall tiles be removed before plastering?', '<p>An appropriate method depends on the substrate and bond. Ask for a site assessment before approving coating over tiles.</p>', 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(6, 'How long does plastering normally take?', '<p>Duration depends on room size, substrate repairs, product cure times and site access.</p>', 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(7, 'How many coats of plaster are needed?', '<p>The number of coats depends on the chosen system, surface flatness and manufacturer instructions.</p>', 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(8, 'When can a new coat be applied?', '<p>Follow the selected product instructions and site conditions, especially temperature, ventilation and moisture.</p>', 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(9, 'Is approval necessary before wall or tile hacking?', '<p>Certain works require permits or approvals. Check current HDB and building-management rules before starting.</p>', 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(10, 'Which rooms and fittings may need removal?', '<p>Identify the exact wall, floor, tiling and fixture areas and ask for a written scope including debris removal.</p>', 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(11, 'Can hacking be scheduled on weekends?', '<p>Work times depend on building rules and local restrictions. Ask the contractor to confirm permitted days.</p>', 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(12, 'Can the quotation include a false-ceiling design?', '<p>Describe the room dimensions, desired layout, access needs and finishes when asking for a quotation.</p>', 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(13, 'Can lights and access panels be included?', '<p>Specify light placement, access panels and electrical coordination before confirming the ceiling scope.</p>', 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(14, 'How are room measurements assessed?', '<p>Provide approximate room dimensions or a floor plan; ask which measurements are needed for the quotation.</p>', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(15, 'Can you quote from photos?', '<p>Photos are useful for a first discussion, but the final method and quote may require more information or a site review.</p>', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(16, 'What should be prepared before a site visit?', '<p>Identify the affected areas, existing defects, access arrangements and any reference photographs.</p>', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(17, 'What materials should be confirmed?', '<p>Check the proposed paint or plaster product, primer, surface preparation, finish and any optional materials.</p>', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(18, 'Is there a minimum work scope?', '<p>Minimum scope varies by service and scheduling. Ask the team to confirm when submitting your enquiry.</p>', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(19, 'Do you include surface protection?', '<p>Confirm flooring, furniture and fixture protection with the team and check what the written quotation includes.</p>', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(20, 'Are colour samples available?', '<p>Ask about paint-colour samples, testing on site and the colour-approval process before work begins.</p>', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(21, 'What affects drying time?', '<p>Product choice, substrate, room ventilation, temperature and humidity can all affect drying times.</p>', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(22, 'How are change requests handled?', '<p>Describe any additional work clearly and agree on scope, price and timing before proceeding.</p>', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(23, 'What information is needed for a quote?', '<p>Share your location, property type, rooms, surface condition and the proposed work in the enquiry form.</p>', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(24, 'What information should I provide for a painting quotation?', '<p>Include the property type, approximate room count, areas needing work, wall condition and any photos that help explain your requirements.</p>', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(25, 'Can I describe plastering and painting in the same enquiry?', '<p>Yes. You can describe multiple types of work in the message box so the team can review the full request.</p>', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(26, 'How do I request a quotation through the website?', '<p>Enter your name, WhatsApp or phone number, select any applicable fields, then submit the enquiry form.</p>', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(27, 'Is a calculator price the same as a final quotation?', '<p>Calculator values are guide prices from published packages. A final quotation depends on the actual agreed scope.</p>', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(28, 'What should I confirm about surface preparation?', '<p>Ask about cleaning, patching, crack treatment, masking and any primer or sealer requirements.</p>', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(29, 'Can I send photos when discussing my enquiry?', '<p>You can contact an active WhatsApp number displayed on the website to ask how to share your project photos.</p>', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(30, 'Should I ask about furniture and floor protection?', '<p>Yes. Confirm what needs moving or covering, which party is responsible, and whether those tasks are included in the quote.</p>', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(31, 'How will I know which contact number is available?', '<p>Use the WhatsApp contact cards on the Home page. Only active numbers configured by the administrator appear there.</p>', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(32, 'Do existing tiles need to be removed before plastering?', 'The required surface preparation depends on the existing tiles, bond and coating system. Have the substrate assessed before deciding on hacking or an alternative approach.', 1, '2026-10-11 01:31:52', '2026-10-11 01:31:52', NULL),
(33, 'How long might a wall plastering project take?', 'The time depends on wall area, surface condition, repair requirements, product curing time and site access. Request a schedule based on your property.', 1, '2026-10-11 01:31:52', '2026-10-11 01:31:52', NULL),
(34, 'How many plaster coats might be required?', 'The number of coats depends on the selected product and the wall condition. Your final quotation should specify the proposed application system.', 1, '2026-10-11 01:31:52', '2026-10-11 01:31:52', NULL),
(35, 'How long should plaster dry before further work?', 'Drying and curing depend on the product, coat thickness, humidity and ventilation. Always follow product guidance and your contractor’s site assessment.', 1, '2026-10-11 01:31:52', '2026-10-11 01:31:52', NULL),
(36, 'Can plastering be included with painting or hacking?', 'You can request a combined quotation. Describe each work area so the required trades, preparation and sequence can be reviewed together.', 1, '2026-10-11 01:31:52', '2026-10-11 01:31:52', NULL),
(37, 'What information helps when asking for a plastering quote?', 'Share your property type, rooms involved, approximate work area, close-up photos of problem walls and the desired finish.', 1, '2026-10-11 01:31:52', '2026-10-11 01:31:52', NULL),
(38, 'Is approval required before HDB wall hacking?', 'Some renovation or structural work requires approval. Verify the applicable HDB, MCST and structural rules with qualified contractors before proceeding.', 1, '2026-10-11 03:06:27', '2026-10-11 03:06:27', NULL),
(39, 'How long does tile or wall hacking take?', 'Duration depends on area, material, access, approvals and waste removal. Request an estimate for your specific project.', 1, '2026-10-11 03:06:27', '2026-10-11 03:06:27', NULL),
(40, 'Can hacking work take place on weekends?', 'Allowed dates and hours depend on local and building-specific restrictions. Confirm permissions and scheduling before booking.', 1, '2026-10-11 03:06:27', '2026-10-11 03:06:27', NULL),
(41, 'Can floor screeding or wall plastering be included after hacking?', 'Yes, you can request a combined quotation for related work. The team must confirm scope, timing and prices before work starts.', 1, '2026-10-11 03:06:27', '2026-10-11 03:06:27', NULL),
(42, 'What details should I provide for a hacking quote?', 'Share room measurements, surface photos, the items to remove, property type, access conditions and known approval requirements.', 1, '2026-10-11 03:06:27', '2026-10-11 03:06:27', NULL),
(43, 'Can a false ceiling be installed in a bathroom or kitchen?', 'The appropriate board system depends on moisture, ventilation and exposure. Discuss moisture-resistant materials and maintenance access before deciding.', 1, '2026-10-14 03:08:25', '2026-10-14 03:08:25', NULL),
(44, 'How low can a false ceiling be installed?', 'The final ceiling level depends on room height, services and applicable building requirements. Confirm the allowed clearance and measurements before installation.', 1, '2026-10-14 03:08:25', '2026-10-14 03:08:25', NULL),
(45, 'What is the difference between regular and moisture-resistant boards?', 'They are designed for different exposure conditions. Ask which board specifications and finishing system are suitable for the room.', 1, '2026-10-14 03:08:25', '2026-10-14 03:08:25', NULL),
(46, 'Can recessed lighting and access panels be included?', 'Yes, they can be discussed as part of the project scope. Confirm electrical coordination, fitting sizes, safe access and separate charges before booking.', 1, '2026-10-14 03:08:25', '2026-10-14 03:08:25', NULL),
(47, 'What affects the false ceiling or partition wall price?', 'Area, board specification, frame design, complexity, access, finishing and additional fittings all affect the price. Published rates are only a guide until the site scope is confirmed.', 1, '2026-10-14 03:08:25', '2026-10-14 03:08:25', NULL),
(48, 'Do I need building-management approval for partition work?', 'Requirements depend on the property and proposed work. Check applicable HDB, MCST and other building rules before proceeding.', 1, '2026-10-14 03:08:25', '2026-10-14 03:08:25', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `galleries`
--

CREATE TABLE `galleries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `attachable_type` varchar(255) DEFAULT NULL,
  `attachable_id` bigint(20) UNSIGNED DEFAULT NULL,
  `layout` varchar(255) NOT NULL DEFAULT 'grid',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `galleries`
--

INSERT INTO `galleries` (`id`, `name`, `attachable_type`, `attachable_id`, `layout`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Demo Portfolio', 'App\\Models\\Service', 1, 'grid', 0, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(2, '[Draft] Living Rooms', NULL, NULL, 'grid', 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(3, '[Draft] Bedrooms', NULL, NULL, 'grid', 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(4, '[Draft] Kitchen Painting', NULL, NULL, 'grid', 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(5, '[Draft] Corridor Painting', NULL, NULL, 'grid', 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(6, '[Draft] Ceiling Works', NULL, NULL, 'grid', 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(7, '[Draft] Wall Plastering', NULL, NULL, 'grid', 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(8, '[Draft] Surface Preparation', NULL, NULL, 'grid', 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(9, '[Draft] Accent Walls', NULL, NULL, 'grid', 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(10, '[Draft] Commercial Sites', NULL, NULL, 'grid', 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(11, '[Draft] Completed Homes', NULL, NULL, 'grid', 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `gallery_items`
--

CREATE TABLE `gallery_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `gallery_id` bigint(20) UNSIGNED NOT NULL,
  `item_type` varchar(255) NOT NULL DEFAULT 'image',
  `title` varchar(255) DEFAULT NULL,
  `caption` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gallery_items`
--

INSERT INTO `gallery_items` (`id`, `gallery_id`, `item_type`, `title`, `caption`, `sort_order`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 'image', '[Draft] Living Rooms — Upload photo here', 'Inactive gallery placeholder without real media.', 0, 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(2, 3, 'image', '[Draft] Bedrooms — Upload photo here', 'Inactive gallery placeholder without real media.', 1, 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(3, 4, 'image', '[Draft] Kitchen Painting — Upload photo here', 'Inactive gallery placeholder without real media.', 2, 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(4, 5, 'image', '[Draft] Corridor Painting — Upload photo here', 'Inactive gallery placeholder without real media.', 3, 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(5, 6, 'image', '[Draft] Ceiling Works — Upload photo here', 'Inactive gallery placeholder without real media.', 4, 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(6, 7, 'image', '[Draft] Wall Plastering — Upload photo here', 'Inactive gallery placeholder without real media.', 5, 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(7, 8, 'image', '[Draft] Surface Preparation — Upload photo here', 'Inactive gallery placeholder without real media.', 6, 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(8, 9, 'image', '[Draft] Accent Walls — Upload photo here', 'Inactive gallery placeholder without real media.', 7, 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(9, 10, 'image', '[Draft] Commercial Sites — Upload photo here', 'Inactive gallery placeholder without real media.', 8, 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(10, 11, 'image', '[Draft] Completed Homes — Upload photo here', 'Inactive gallery placeholder without real media.', 9, 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) UNSIGNED NOT NULL,
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
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reference` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(190) DEFAULT NULL,
  `phone` varchar(32) NOT NULL,
  `location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `message` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `status` varchar(30) NOT NULL DEFAULT 'new',
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `source_page_url` varchar(255) DEFAULT NULL,
  `utm` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`utm`)),
  `consent` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`consent`)),
  `pricing_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`pricing_snapshot`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lead_form_answers`
--

CREATE TABLE `lead_form_answers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lead_id` bigint(20) UNSIGNED NOT NULL,
  `lead_form_field_id` bigint(20) UNSIGNED DEFAULT NULL,
  `field_key` varchar(100) NOT NULL,
  `field_label` varchar(150) NOT NULL,
  `answer` text NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lead_form_fields`
--

CREATE TABLE `lead_form_fields` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(150) NOT NULL,
  `field_key` varchar(100) NOT NULL,
  `placeholder` varchar(190) DEFAULT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`options`)),
  `is_required` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lead_form_fields`
--

INSERT INTO `lead_form_fields` (`id`, `label`, `field_key`, `placeholder`, `options`, `is_required`, `is_active`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Property Type', 'property_type', 'Select property type', '[\"HDB\",\"Condo\",\"Landed\",\"Commercial\"]', 1, 1, 0, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(2, 'Property category', 'property_type_extra', 'Choose an option', '[\"HDB\",\"Condo\",\"Landed\",\"Commercial\"]', 0, 1, 100, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(3, 'Number of rooms', 'room_count', 'Choose an option', '[\"1\",\"2\",\"3\",\"4\",\"5+\"]', 0, 1, 100, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(4, 'Project scope', 'work_scope', 'Choose an option', '[\"Painting\",\"Plastering\",\"Hacking\",\"Other\"]', 0, 1, 100, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(5, 'Property condition', 'property_status', 'Choose an option', '[\"Occupied\",\"Vacant\",\"Under renovation\"]', 0, 1, 100, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(6, 'Access type', 'site_access', 'Choose an option', '[\"Lift\",\"Stairs\",\"Ground floor\"]', 0, 1, 100, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(7, 'Preferred contact', 'preferred_contact', 'Choose an option', '[\"WhatsApp\",\"Phone\",\"Email\"]', 0, 1, 100, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(8, 'Wall condition', 'wall_condition', 'Choose an option', '[\"Good\",\"Uneven\",\"Peeling\",\"Unsure\"]', 0, 1, 100, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(9, 'Preferred finish', 'paint_finish', 'Choose an option', '[\"Matte\",\"Eggshell\",\"Satin\",\"Unsure\"]', 0, 1, 100, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(10, 'Include ceiling', 'ceiling_included', 'Choose an option', '[\"Yes\",\"No\",\"Unsure\"]', 0, 1, 100, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(11, 'Quotation preference', 'quotation_type', 'Choose an option', '[\"On-site\",\"Remote first\",\"Unsure\"]', 0, 1, 100, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lead_notes`
--

CREATE TABLE `lead_notes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lead_id` bigint(20) UNSIGNED NOT NULL,
  `author_type` varchar(255) DEFAULT NULL,
  `author_id` bigint(20) UNSIGNED DEFAULT NULL,
  `note` text NOT NULL,
  `visible_to_user` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lead_services`
--

CREATE TABLE `lead_services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lead_id` bigint(20) UNSIGNED NOT NULL,
  `service_id` bigint(20) UNSIGNED NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lead_status_histories`
--

CREATE TABLE `lead_status_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lead_id` bigint(20) UNSIGNED NOT NULL,
  `changed_by_type` varchar(255) DEFAULT NULL,
  `changed_by_id` bigint(20) UNSIGNED DEFAULT NULL,
  `from_status` varchar(30) DEFAULT NULL,
  `to_status` varchar(30) NOT NULL,
  `reason` varchar(1000) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(190) NOT NULL,
  `slug` varchar(190) NOT NULL,
  `region` varchar(120) DEFAULT NULL,
  `postal_codes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`postal_codes`)),
  `summary` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `hero_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`hero_config`)),
  `status` varchar(30) NOT NULL DEFAULT 'draft',
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `name`, `slug`, `region`, `postal_codes`, `summary`, `content`, `hero_config`, `status`, `sort_order`, `published_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Singapore', 'singapore', 'Singapore', '[]', 'Location information for Singapore. Contact the team to confirm coverage and access requirements.', '<p>Location information for Singapore. Contact the team to confirm coverage and access requirements.</p>', '[]', 'published', 0, '2026-10-10 13:47:22', '2026-10-10 13:47:19', '2026-10-10 13:47:22', NULL),
(2, 'Central Singapore', 'central-singapore', 'Singapore', '[]', 'Location information for Central Singapore. Contact the team to confirm coverage and access requirements.', '<p>Location information for Central Singapore. Contact the team to confirm coverage and access requirements.</p>', '[]', 'published', 100, '2026-10-10 13:47:22', '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(3, 'North Singapore', 'north-singapore', 'Singapore', '[]', 'Location information for North Singapore. Contact the team to confirm coverage and access requirements.', '<p>Location information for North Singapore. Contact the team to confirm coverage and access requirements.</p>', '[]', 'published', 100, '2026-10-10 13:47:22', '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(4, 'South Singapore', 'south-singapore', 'Singapore', '[]', 'Location information for South Singapore. Contact the team to confirm coverage and access requirements.', '<p>Location information for South Singapore. Contact the team to confirm coverage and access requirements.</p>', '[]', 'published', 100, '2026-10-10 13:47:22', '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(5, 'East Singapore', 'east-singapore', 'Singapore', '[]', 'Location information for East Singapore. Contact the team to confirm coverage and access requirements.', '<p>Location information for East Singapore. Contact the team to confirm coverage and access requirements.</p>', '[]', 'published', 100, '2026-10-10 13:47:22', '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(6, 'West Singapore', 'west-singapore', 'Singapore', '[]', 'Location information for West Singapore. Contact the team to confirm coverage and access requirements.', '<p>Location information for West Singapore. Contact the team to confirm coverage and access requirements.</p>', '[]', 'published', 100, '2026-10-10 13:47:22', '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(7, 'Ang Mo Kio', 'ang-mo-kio', 'Singapore', '[]', 'Location information for Ang Mo Kio. Contact the team to confirm coverage and access requirements.', '<p>Location information for Ang Mo Kio. Contact the team to confirm coverage and access requirements.</p>', '[]', 'published', 100, '2026-10-10 13:47:22', '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(8, 'Bishan', 'bishan', 'Singapore', '[]', 'Location information for Bishan. Contact the team to confirm coverage and access requirements.', '<p>Location information for Bishan. Contact the team to confirm coverage and access requirements.</p>', '[]', 'published', 100, '2026-10-10 13:47:22', '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(9, 'Tampines', 'tampines', 'Singapore', '[]', 'Location information for Tampines. Contact the team to confirm coverage and access requirements.', '<p>Location information for Tampines. Contact the team to confirm coverage and access requirements.</p>', '[]', 'published', 100, '2026-10-10 13:47:22', '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(10, 'Jurong East', 'jurong-east', 'Singapore', '[]', 'Location information for Jurong East. Contact the team to confirm coverage and access requirements.', '<p>Location information for Jurong East. Contact the team to confirm coverage and access requirements.</p>', '[]', 'published', 100, '2026-10-10 13:47:22', '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(11, 'Woodlands', 'woodlands', 'Singapore', '[]', 'Location information for Woodlands. Contact the team to confirm coverage and access requirements.', '<p>Location information for Woodlands. Contact the team to confirm coverage and access requirements.</p>', '[]', 'published', 100, '2026-10-10 13:47:22', '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) DEFAULT NULL,
  `collection_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `disk` varchar(255) NOT NULL,
  `conversions_disk` varchar(255) DEFAULT NULL,
  `size` bigint(20) UNSIGNED NOT NULL,
  `manipulations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`manipulations`)),
  `custom_properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`custom_properties`)),
  `generated_conversions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`generated_conversions`)),
  `responsive_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`responsive_images`)),
  `order_column` int(10) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `media`
--

INSERT INTO `media` (`id`, `model_type`, `model_id`, `uuid`, `collection_name`, `name`, `file_name`, `mime_type`, `disk`, `conversions_disk`, `size`, `manipulations`, `custom_properties`, `generated_conversions`, `responsive_images`, `order_column`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\Admin', 1, 'b9529c10-6e40-48bc-bac1-4d501f04596f', 'media_library', 'hero', 'e247ee1c-fda9-4dd1-a7a1-6075b9abcca7.png', 'image/png', 'public', 'public', 1908550, '[]', '{\"uploaded_by_admin_id\":1,\"original_name\":\"hero.png\",\"source\":\"global_media_picker\",\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 1, NULL, '2026-10-10 14:19:10', '2026-10-10 14:19:10'),
(2, 'App\\Models\\Page', 1, '73099658-d313-405e-8c6c-6e67966e53f0', 'hero_desktop', 'hero', 'e247ee1c-fda9-4dd1-a7a1-6075b9abcca7.png', 'image/png', 'public', 'public', 1908550, '[]', '{\"uploaded_by_admin_id\":1,\"original_name\":\"hero.png\",\"source\":\"global_media_picker\",\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 1, NULL, '2026-10-10 14:24:14', '2026-10-10 14:24:14'),
(3, 'App\\Models\\Page', 1, '9825ff49-cbe4-4977-b135-7b7fa177c364', 'hero_mobile', 'hero', 'e247ee1c-fda9-4dd1-a7a1-6075b9abcca7.png', 'image/png', 'public', 'public', 1908550, '[]', '{\"uploaded_by_admin_id\":1,\"original_name\":\"hero.png\",\"source\":\"global_media_picker\",\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 1, NULL, '2026-10-10 14:24:14', '2026-10-10 14:24:14'),
(4, 'App\\Models\\Admin', 1, '091d915d-3de1-4b4a-aaf7-e1120ddd1bb9', 'media_library', 'why choose-1', 'd6ed9924-21ac-42a9-9abd-758f0d43aeb9.png', 'image/png', 'public', 'public', 1051627, '[]', '{\"uploaded_by_admin_id\":1,\"original_name\":\"why choose-1.png\",\"source\":\"global_media_picker\",\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 2, NULL, '2026-10-10 14:31:31', '2026-10-10 14:31:31'),
(5, 'App\\Models\\Admin', 1, 'c9057dc6-8d4e-4c09-8434-3d0a0d580a1a', 'media_library', 'why choose-2', 'b19d80ce-87cd-4988-8a1e-e981e1bb7ed4.png', 'image/png', 'public', 'public', 511550, '[]', '{\"uploaded_by_admin_id\":1,\"original_name\":\"why choose-2.png\",\"source\":\"global_media_picker\",\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 3, NULL, '2026-10-10 14:31:38', '2026-10-10 14:31:38'),
(6, 'App\\Models\\Admin', 1, '603fb742-47ed-47a5-9b63-ec892ac585f7', 'media_library', 'why choose-5', '634efe6a-85a3-438f-9a47-d2d5588a3a04.png', 'image/png', 'public', 'public', 720831, '[]', '{\"uploaded_by_admin_id\":1,\"original_name\":\"why choose-5.png\",\"source\":\"global_media_picker\",\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 4, NULL, '2026-10-10 14:33:48', '2026-10-10 14:33:48'),
(7, 'App\\Models\\Admin', 1, 'cbc827d2-ea9b-4ba7-84e9-82455a94aea6', 'media_library', 'why choose-4', '5d835335-387e-4cc6-8444-fb7688f64038.png', 'image/png', 'public', 'public', 863741, '[]', '{\"uploaded_by_admin_id\":1,\"original_name\":\"why choose-4.png\",\"source\":\"global_media_picker\",\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 5, NULL, '2026-10-10 14:33:48', '2026-10-10 14:33:48'),
(8, 'App\\Models\\Admin', 1, '090929a0-61cb-48b6-9d85-f4d35609ff7c', 'media_library', 'why choose-3', '6294759f-dedb-47dd-82d2-007e1ade1626.png', 'image/png', 'public', 'public', 704072, '[]', '{\"uploaded_by_admin_id\":1,\"original_name\":\"why choose-3.png\",\"source\":\"global_media_picker\",\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 6, NULL, '2026-10-10 14:33:48', '2026-10-10 14:33:48'),
(9, 'App\\Models\\Admin', 1, 'd2126436-a073-4441-b99c-bddb2fbb8b4a', 'media_library', 'gaurantee', 'e85ce457-9663-4bf9-9aee-6bf0814986df.jpeg', 'image/jpeg', 'public', 'public', 58180, '[]', '{\"uploaded_by_admin_id\":1,\"original_name\":\"gaurantee.jpeg\",\"source\":\"global_media_picker\",\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 7, NULL, '2026-10-10 14:43:10', '2026-10-10 14:43:10'),
(10, 'App\\Models\\Testimonial', 11, 'e493da73-5c3d-4177-a1f1-8075d3f09efd', 'photo', 'sfashanto', 'sfashanto.png', 'image/png', 'public', 'public', 437362, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 1, NULL, '2026-10-10 14:52:21', '2026-10-10 14:52:21'),
(11, 'App\\Models\\Testimonial', 11, '9d1965da-2e4f-4b96-a591-0d82c43412ed', 'testimonial_screenshot', 'review-image-1', 'review-image-1.png', 'image/png', 'public', 'public', 515139, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 2, '2026-10-10 15:12:32', '2026-10-10 14:52:21', '2026-10-10 15:12:32'),
(12, 'App\\Models\\Testimonial', 10, '01242a3b-f999-470e-b963-bd9cfbb47eb3', 'photo', 'sfashanto', 'sfashanto.png', 'image/png', 'public', 'public', 437362, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 1, NULL, '2026-10-10 14:53:07', '2026-10-10 14:53:07'),
(13, 'App\\Models\\Testimonial', 10, '479c266e-8cd6-49da-aaed-c1b44fe88abf', 'testimonial_screenshot', 'review-image-2', 'review-image-2.png', 'image/png', 'public', 'public', 469568, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 2, '2026-10-10 15:12:47', '2026-10-10 14:53:08', '2026-10-10 15:12:47'),
(14, 'App\\Models\\Testimonial', 9, 'f25f2223-28a7-4a80-b406-82cb97a694dd', 'photo', 'sfashanto', 'sfashanto.png', 'image/png', 'public', 'public', 437362, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 1, NULL, '2026-10-10 14:53:48', '2026-10-10 14:53:48'),
(15, 'App\\Models\\Testimonial', 9, 'a4f33fc1-a87a-480f-84f0-b527edb33cac', 'testimonial_screenshot', 'review-image-1', 'review-image-1.png', 'image/png', 'public', 'public', 515139, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 2, '2026-10-10 15:13:03', '2026-10-10 14:53:48', '2026-10-10 15:13:03'),
(16, 'App\\Models\\Testimonial', 8, 'ffb1a9be-45bf-4c91-b4e0-a188d70e557f', 'photo', 'sfashanto', 'sfashanto.png', 'image/png', 'public', 'public', 437362, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 1, NULL, '2026-10-10 14:54:55', '2026-10-10 14:54:55'),
(17, 'App\\Models\\Testimonial', 8, '461a4dc7-98fb-43e0-ad62-5c3766bf5cc9', 'testimonial_screenshot', 'review-image-1', 'review-image-1.png', 'image/png', 'public', 'public', 515139, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 2, '2026-10-10 15:13:15', '2026-10-10 14:54:55', '2026-10-10 15:13:15'),
(18, 'App\\Models\\Testimonial', 7, 'ac5e26a8-ec3c-4404-bcb5-f5e0ad2b58e1', 'testimonial_screenshot', 'review-image-1', 'review-image-1.png', 'image/png', 'public', 'public', 515139, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 2, NULL, '2026-10-10 15:11:58', '2026-10-10 15:11:58'),
(19, 'App\\Models\\Testimonial', 6, '11dd36dd-85fa-43cb-a0bf-e769eefb43db', 'testimonial_screenshot', 'review-image-1', 'review-image-1.png', 'image/png', 'public', 'public', 515139, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 2, NULL, '2026-10-10 15:13:52', '2026-10-10 15:13:52'),
(20, 'App\\Models\\Testimonial', 5, 'e719b594-2832-4cf3-b4cc-5a4e27ddc3b6', 'testimonial_screenshot', 'review-image-1', 'review-image-1.png', 'image/png', 'public', 'public', 515139, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 2, NULL, '2026-10-10 15:14:33', '2026-10-10 15:14:33'),
(21, 'App\\Models\\Testimonial', 4, '6948b189-ada7-4c86-9a12-f2049b0bad50', 'testimonial_screenshot', 'review-image-1', 'review-image-1.png', 'image/png', 'public', 'public', 515139, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 2, NULL, '2026-10-10 15:14:58', '2026-10-10 15:14:58'),
(22, 'App\\Models\\Admin', 1, '61464c7e-c5ed-4a9e-93d1-ae49ef76e435', 'media_library', 'before-1', 'e912fba9-3e31-4a01-a8d4-390f07f52800.jpeg', 'image/jpeg', 'public', 'public', 47773, '[]', '{\"uploaded_by_admin_id\":1,\"original_name\":\"before-1.jpeg\",\"source\":\"global_media_picker\",\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 8, NULL, '2026-10-10 15:16:24', '2026-10-10 15:16:24'),
(23, 'App\\Models\\Admin', 1, '04113001-5176-4eb9-9ae6-91ce4f1cbd34', 'media_library', 'after-1', '6b615ff0-eafa-41aa-aed4-e130bb67c4da.jpeg', 'image/jpeg', 'public', 'public', 59053, '[]', '{\"uploaded_by_admin_id\":1,\"original_name\":\"after-1.jpeg\",\"source\":\"global_media_picker\",\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 9, NULL, '2026-10-10 15:16:31', '2026-10-10 15:16:31'),
(24, 'App\\Models\\Admin', 1, '8f499c4b-51d1-4101-b258-3e9be448d0b1', 'media_library', 'before-2', '0a9cca34-4e65-4428-beda-c44d001ace15.webp', 'image/webp', 'public', 'public', 311942, '[]', '{\"uploaded_by_admin_id\":1,\"original_name\":\"before-2.webp\",\"source\":\"global_media_picker\",\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 10, NULL, '2026-10-10 15:16:39', '2026-10-10 15:16:39'),
(25, 'App\\Models\\Admin', 1, '8f471df4-5664-4b50-abe4-53a434ed7319', 'media_library', 'after-2', 'a5ae97e9-cd8f-4dd5-8d23-91e9eebbb5ce.webp', 'image/webp', 'public', 'public', 369960, '[]', '{\"uploaded_by_admin_id\":1,\"original_name\":\"after-2.webp\",\"source\":\"global_media_picker\",\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 11, NULL, '2026-10-10 15:16:44', '2026-10-10 15:16:44'),
(26, 'App\\Models\\Page', 2, 'cebc3cac-5e37-44c3-a7d1-82b19d846a10', 'hero_desktop', 'Plastering hero', '68eb2ffb-cd82-4267-b5cb-a7260e75d46c.png', 'image/png', 'public', 'public', 579921, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 1, NULL, '2026-10-11 01:30:27', '2026-10-11 01:30:27'),
(27, 'App\\Models\\Page', 2, '30035f46-435c-4c24-bf63-6b0886d7e923', 'hero_mobile', 'Plastering hero', '6629fdfc-640f-4334-b557-11329aa1c3ec.png', 'image/png', 'public', 'public', 579921, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 2, NULL, '2026-10-11 01:30:27', '2026-10-11 01:30:27'),
(30, 'App\\Models\\SiteSetting', 23, '621015d0-6f06-40ab-bfc5-0f1bcc5e21ce', 'site_logo', 'cropped-Artboard-1-copy-2@2x', '7b23fa36-7e23-4dce-b83e-dd448d77a1f0.webp', 'image/webp', 'public', 'public', 11362, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 2, NULL, '2026-10-11 02:36:09', '2026-10-11 02:36:09'),
(31, 'App\\Models\\SiteSetting', 23, 'a8445a41-b973-4e1d-9249-ab3bb471e46c', 'site_favicon', 'cropped-Artboard-1-copy-2@2x', '64c53207-427f-4d59-a8a3-bf5029547914.webp', 'image/webp', 'public', 'public', 11362, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 1, NULL, '2026-10-11 02:36:10', '2026-10-11 02:36:10'),
(32, 'App\\Models\\Admin', 1, '6dc6f8b0-df58-4579-91cb-a38ce920be11', 'media_library', 'hacking', '4fce5d5f-8949-4084-b6bb-ec22b4ceb236.png', 'image/png', 'public', 'public', 1272141, '[]', '{\"uploaded_by_admin_id\":1,\"original_name\":\"hacking.png\",\"source\":\"global_media_picker\",\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 12, NULL, '2026-10-11 03:10:59', '2026-10-11 03:10:59'),
(33, 'App\\Models\\Service', 2, 'f5c08368-843d-4ed8-bef7-01203c49378d', 'hero_desktop', 'hacking-1', '0ffd6ae9-edad-43d6-ae20-a15308a919de.jpg', 'image/jpeg', 'public', 'public', 87511, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 1, NULL, '2026-10-11 03:56:36', '2026-10-11 03:56:36'),
(34, 'App\\Models\\Service', 2, 'a66744b2-bc09-4bcb-b7fe-4036385ffa76', 'hero_mobile', 'hacking-1', 'd42d750f-5ffa-4500-8eb6-5159eb3411a6.jpg', 'image/jpeg', 'public', 'public', 87511, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 2, NULL, '2026-10-11 03:56:36', '2026-10-11 03:56:36'),
(35, 'App\\Models\\Service', 3, '4a3f8965-0fd7-47c4-82d1-1fe6203b4083', 'hero_desktop', 'hacking-2', 'd3040404-a56b-4a88-b48e-10d8f7a2bb4e.jpeg', 'image/jpeg', 'public', 'public', 33169, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 1, NULL, '2026-10-11 03:57:28', '2026-10-11 03:57:28'),
(36, 'App\\Models\\Service', 3, '0fb8753d-8826-4ff8-b4ac-591d911a07de', 'hero_mobile', 'hacking-2', 'f9946cb1-d6da-4d29-93bb-f999807927bb.jpeg', 'image/jpeg', 'public', 'public', 33169, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 2, NULL, '2026-10-11 03:57:28', '2026-10-11 03:57:28'),
(37, 'App\\Models\\Service', 4, '61b402d0-6185-48e2-95c6-5f809e982efd', 'gallery', 'hacking-3', '47cb7485-9ef3-42c6-b6ed-c2b37dbe19b1.jpg', 'image/jpeg', 'public', 'public', 19896, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 1, NULL, '2026-10-11 03:58:07', '2026-10-11 03:58:07'),
(38, 'App\\Models\\Admin', 1, 'fde6639b-4c76-4185-8bad-574b3f72be09', 'media_library', 'Screenshot_1', 'c36e91cb-471a-45cf-9052-dc57c3e0f4d5.png', 'image/png', 'public', 'public', 873729, '[]', '{\"uploaded_by_admin_id\":1,\"original_name\":\"Screenshot_1.png\",\"source\":\"global_media_picker\",\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 13, NULL, '2026-10-14 03:06:11', '2026-10-14 03:06:11'),
(39, 'App\\Models\\Post', 11, '749f3134-82eb-41d8-9b2b-28f206dbe6b8', 'featured', 'logo', 'faeb2edc-b739-454e-bcf6-304f2fcf0fd6.png', 'image/png', 'public', 'public', 62518, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 1, NULL, '2026-10-14 06:04:35', '2026-10-14 06:04:35'),
(40, 'App\\Models\\Post', 11, 'bab1c415-45ca-4e91-be46-9460886b5b07', 'content_images', '47cb7485-9ef3-42c6-b6ed-c2b37dbe19b1', '0b161349-f212-4751-af4c-be7e3fd28fd4.jpg', 'image/jpeg', 'public', 'public', 19896, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 2, NULL, '2026-10-14 06:37:32', '2026-10-14 06:37:32'),
(41, 'App\\Models\\Post', 10, '42532265-f9e5-4fb6-8d0e-4121af0dedd1', 'featured', 'hacking-2', '68082f2a-dbf2-4b4a-bbeb-7bf16776a707.jpeg', 'image/jpeg', 'public', 'public', 33169, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 1, NULL, '2026-10-14 06:38:32', '2026-10-14 06:38:32'),
(42, 'App\\Models\\Post', 10, '4c36e97f-db68-49f1-8d54-ce1462299d0b', 'content_images', 'hacking-2', '87f08d3a-3a51-4478-87ba-d27a7b54446b.jpeg', 'image/jpeg', 'public', 'public', 33169, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 2, NULL, '2026-10-14 06:38:32', '2026-10-14 06:38:32'),
(43, 'App\\Models\\Post', 9, 'a234b398-36fd-4122-bb38-f4304cbea435', 'featured', 'hacking-1', 'e4b3beba-d63f-4ede-927f-77d9fa71b72e.jpg', 'image/jpeg', 'public', 'public', 87511, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 1, NULL, '2026-10-14 06:38:48', '2026-10-14 06:38:48'),
(44, 'App\\Models\\Post', 9, 'f6ec5a6e-b476-48c7-bcec-8bbbdae42dbf', 'content_images', 'hacking-1', '78d0a828-d59c-4619-9879-73b2dfada19a.jpg', 'image/jpeg', 'public', 'public', 87511, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 2, NULL, '2026-10-14 06:38:48', '2026-10-14 06:38:48'),
(45, 'App\\Models\\Post', 8, 'b83d9bbf-745b-4e3b-a630-818601eb3507', 'featured', 'hacking', '6c4834c4-8593-4d02-943a-3e44032bb5d5.png', 'image/png', 'public', 'public', 1272141, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 1, NULL, '2026-10-14 06:39:06', '2026-10-14 06:39:06'),
(46, 'App\\Models\\Post', 8, '9e079188-7f89-472a-b5bd-ac3caaffa3c3', 'content_images', 'hacking', 'f5ee3ad8-7075-4b89-a74c-a06d3932e6fe.png', 'image/png', 'public', 'public', 1272141, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 2, NULL, '2026-10-14 06:39:06', '2026-10-14 06:39:06'),
(47, 'App\\Models\\Post', 7, '40e20b3d-8640-4ece-b747-923df218acea', 'featured', 'hacking-3', '8537791a-1a67-4dd6-9716-d84982078c5d.jpg', 'image/jpeg', 'public', 'public', 19896, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 1, NULL, '2026-10-14 06:39:23', '2026-10-14 06:39:23'),
(48, 'App\\Models\\Post', 7, 'cddf293c-d3a0-440f-9cb7-44192296ad30', 'content_images', 'hacking-3', 'd6cef198-0b45-4557-b1c8-d79283bc52bf.jpg', 'image/jpeg', 'public', 'public', 19896, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 2, NULL, '2026-10-14 06:39:23', '2026-10-14 06:39:23'),
(49, 'App\\Models\\Service', 1, 'aacd18a3-fa76-4ce0-9d0d-7d8881b8cc6b', 'gallery', 'hacking-3', '8d2acac4-8843-4766-8f1f-6f2edc34c643.jpg', 'image/jpeg', 'public', 'public', 19896, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 1, NULL, '2026-10-14 06:42:05', '2026-10-14 06:42:05'),
(50, 'App\\Models\\Service', 1, '744c1c0e-54bc-4e7a-93dd-6c4b8a46982f', 'hero_desktop', 'hacking-3', 'e7ac3fd7-36a9-4aac-b191-105f5b7c48ff.jpg', 'image/jpeg', 'public', 'public', 19896, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 2, NULL, '2026-10-14 06:42:53', '2026-10-14 06:42:53'),
(51, 'App\\Models\\Service', 1, 'b98075ea-f791-4be2-beda-440c701eb00b', 'hero_mobile', 'hacking-2', '4b619cce-a015-4b0e-8e4f-5b0967cdb8d2.jpeg', 'image/jpeg', 'public', 'public', 33169, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 3, NULL, '2026-10-14 06:42:53', '2026-10-14 06:42:53'),
(52, 'App\\Models\\Service', 4, 'f10dd30a-a41c-4b22-919d-adc0acc494fe', 'hero_desktop', 'hacking-1', '949bae73-7aae-4987-9eb5-d67b56db6c26.jpg', 'image/jpeg', 'public', 'public', 87511, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 2, NULL, '2026-10-14 06:43:09', '2026-10-14 06:43:09'),
(53, 'App\\Models\\Service', 4, '3fe16c3c-e19c-4ff5-8102-315513acb3fa', 'hero_mobile', 'hacking', '66a7ab33-11ba-4acf-a39c-b8ab38041681.png', 'image/png', 'public', 'public', 1272141, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 3, NULL, '2026-10-14 06:43:09', '2026-10-14 06:43:09'),
(54, 'App\\Models\\Service', 5, '104d3eae-9bc8-4c22-af6f-f77b03cd1d25', 'hero_desktop', 'hacking-2', '54462065-6aa6-4f08-9515-a0d1c8a63cdd.jpeg', 'image/jpeg', 'public', 'public', 33169, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 1, NULL, '2026-10-14 06:43:40', '2026-10-14 06:43:40'),
(55, 'App\\Models\\Service', 5, 'c6c7f5a5-560e-42d0-9e16-37c812d6cceb', 'hero_mobile', 'hacking-2', '3ac11abd-7a5c-4bd6-a841-956b34106a1f.jpeg', 'image/jpeg', 'public', 'public', 33169, '[]', '{\"storage_path_scheme\":\"model_section_v1\"}', '[]', '[]', 2, NULL, '2026-10-14 06:43:40', '2026-10-14 06:43:40');

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `location` varchar(80) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `name`, `location`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Header Primary', 'header-primary', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:22', NULL),
(2, 'Header Top', 'header-top', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:22', NULL),
(3, 'Footer Services', 'footer-services', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:22', NULL),
(4, 'Footer Company', 'footer-company', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:22', NULL),
(5, 'Footer Legal', 'footer-legal', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `menu_id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `label` varchar(150) NOT NULL,
  `link_type` varchar(30) NOT NULL DEFAULT 'url',
  `linkable_type` varchar(255) DEFAULT NULL,
  `linkable_id` bigint(20) UNSIGNED DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `target` varchar(10) NOT NULL DEFAULT '_self',
  `css_class` varchar(150) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `faq_section_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `menu_id`, `parent_id`, `label`, `link_type`, `linkable_type`, `linkable_id`, `url`, `icon`, `target`, `css_class`, `sort_order`, `faq_section_enabled`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, NULL, 'Home', 'url', NULL, NULL, '/', NULL, '_self', NULL, 0, 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(2, 1, NULL, 'Plastering', 'url', NULL, NULL, '/plastering', NULL, '_self', NULL, 10, 1, 1, '2026-10-10 13:47:20', '2026-10-10 13:47:22', NULL),
(3, 1, NULL, 'Hacking', 'url', NULL, NULL, '/hacking', NULL, '_self', NULL, 20, 1, 1, '2026-10-10 13:47:20', '2026-10-10 13:47:22', NULL),
(4, 1, NULL, 'False Ceiling', 'url', NULL, NULL, '/false-ceiling', NULL, '_self', NULL, 30, 1, 1, '2026-10-10 13:47:20', '2026-10-10 13:47:22', NULL),
(5, 1, NULL, 'Pricing', 'url', NULL, NULL, '/pricing', NULL, '_self', NULL, 40, 1, 1, '2026-10-10 13:47:20', '2026-10-10 13:47:22', NULL),
(6, 1, NULL, 'Contact', 'url', NULL, NULL, '/contact', NULL, '_self', NULL, 50, 1, 1, '2026-10-10 13:47:20', '2026-10-10 13:47:22', NULL),
(7, 1, NULL, 'Blog', 'url', NULL, NULL, '/blog', NULL, '_self', NULL, 60, 1, 1, '2026-10-10 13:47:20', '2026-10-10 13:47:22', NULL),
(8, 1, NULL, 'Cost Calculator', 'url', NULL, NULL, '/cost-calculator', NULL, '_self', NULL, 70, 1, 1, '2026-10-10 13:47:20', '2026-10-10 13:47:22', NULL),
(9, 2, NULL, 'Request a Quote', 'url', NULL, NULL, '/#quote-form', NULL, '_self', NULL, 0, 1, 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(10, 2, NULL, 'Contact Us', 'url', NULL, NULL, '/contact', NULL, '_self', NULL, 10, 1, 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(11, 4, NULL, 'Home', 'url', NULL, NULL, '/', NULL, '_self', NULL, 0, 1, 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(12, 4, NULL, 'About Us', 'url', NULL, NULL, '/about', NULL, '_self', NULL, 10, 1, 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(13, 4, NULL, 'Contact Us', 'url', NULL, NULL, '/contact', NULL, '_self', NULL, 20, 1, 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(14, 4, NULL, 'Service Areas', 'url', NULL, NULL, '/service-areas', NULL, '_self', NULL, 30, 1, 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(15, 4, NULL, 'Project Gallery', 'url', NULL, NULL, '/gallery', NULL, '_self', NULL, 40, 1, 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(16, 4, NULL, 'Request a Quote', 'url', NULL, NULL, '/request-quote', NULL, '_self', NULL, 50, 1, 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(17, 4, NULL, 'Blog', 'url', NULL, NULL, '/blog', NULL, '_self', NULL, 60, 1, 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(18, 3, NULL, 'HDB Painting', 'url', NULL, NULL, '/services/hdb-painting', NULL, '_self', NULL, 0, 1, 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(19, 3, NULL, 'House Painting', 'url', NULL, NULL, '/services/house-painting', NULL, '_self', NULL, 10, 1, 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(20, 3, NULL, 'Wall Plastering', 'url', NULL, NULL, '/plastering', NULL, '_self', NULL, 20, 1, 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(21, 3, NULL, 'Wall & Tile Hacking', 'url', NULL, NULL, '/hacking', NULL, '_self', NULL, 30, 1, 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(22, 3, NULL, 'False Ceiling', 'url', NULL, NULL, '/false-ceiling', NULL, '_self', NULL, 40, 1, 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(23, 3, NULL, 'Condo Painting', 'url', NULL, NULL, '/services/condo-painting', NULL, '_self', NULL, 50, 1, 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(24, 3, NULL, 'Landed House Painting', 'url', NULL, NULL, '/services/landed-house-painting', NULL, '_self', NULL, 60, 1, 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(25, 3, NULL, 'Ceiling Painting', 'url', NULL, NULL, '/services/ceiling-painting', NULL, '_self', NULL, 70, 1, 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(26, 5, NULL, 'Privacy Policy', 'url', NULL, NULL, '/privacy-policy', NULL, '_self', NULL, 0, 0, 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(27, 5, NULL, 'Terms & Conditions', 'url', NULL, NULL, '/terms-and-conditions', NULL, '_self', NULL, 10, 0, 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(28, 5, NULL, 'Cookie Policy', 'url', NULL, NULL, '/cookie-policy', NULL, '_self', NULL, 20, 0, 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL);

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
(4, '2026_09_27_145134_create_permission_tables', 1),
(5, '2026_09_27_145158_create_media_table', 1),
(6, '2026_09_27_160114_create_admins_table', 1),
(7, '2026_09_28_000101_create_site_settings_table', 1),
(8, '2026_09_28_000102_create_menus_table', 1),
(9, '2026_09_29_171925_create_pages_table', 1),
(10, '2026_09_29_194005_create_section_definitions_table', 1),
(11, '2026_09_29_203930_create_page_sections_table', 1),
(12, '2026_09_29_212431_create_section_media_table', 1),
(13, '2026_09_29_235720_create_services_table', 1),
(14, '2026_09_30_004055_create_service_features_table', 1),
(15, '2026_09_30_112426_create_locations_table', 1),
(16, '2026_09_30_125034_create_pricing_packages_table', 1),
(17, '2026_09_30_131515_create_pricing_items_table', 1),
(18, '2026_09_30_144201_create_pricing_addons_table', 1),
(19, '2026_09_30_185203_create_galleries_table', 1),
(20, '2026_09_30_185214_create_gallery_items_table', 1),
(21, '2026_10_01_144233_create_testimonials_table', 1),
(22, '2026_10_01_144555_create_testimonialables_table', 1),
(23, '2026_10_01_151755_create_videos_table', 1),
(24, '2026_10_05_103807_create_pricing_package_addon_table', 1),
(25, '2026_10_05_104757_create_faqs_table', 1),
(26, '2026_10_05_104758_create_faqables_table', 1),
(27, '2026_10_05_114849_create_categories_table', 1),
(28, '2026_10_05_122407_create_posts_table', 1),
(29, '2026_10_05_131305_create_contact_channels_table', 1),
(30, '2026_10_05_142644_create_contact_targets_table', 1),
(31, '2026_10_05_153117_create_leads_table', 1),
(32, '2026_10_05_153118_create_lead_form_fields_table', 1),
(33, '2026_10_05_153119_create_lead_form_answers_table', 1),
(34, '2026_10_05_153120_create_lead_services_table', 1),
(35, '2026_10_05_153121_create_lead_status_histories_table', 1),
(36, '2026_10_05_153122_create_lead_notes_table', 1),
(37, '2026_10_05_174801_create_seo_metas_table', 1),
(38, '2026_10_05_174802_create_redirects_table', 1),
(39, '2026_10_05_174803_create_tracking_providers_table', 1),
(40, '2026_10_05_174804_create_tracking_event_rules_table', 1),
(41, '2026_10_06_000319_create_audit_logs_table', 1),
(42, '2026_10_06_000320_create_campaigns_table', 1),
(43, '2026_10_06_000321_create_campaign_settings_table', 1),
(44, '2026_10_06_000322_create_campaign_sections_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\Admin', 1);

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(190) NOT NULL,
  `slug` varchar(190) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `template` varchar(100) NOT NULL,
  `hero_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`hero_config`)),
  `status` varchar(30) NOT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `is_homepage` tinyint(1) NOT NULL DEFAULT 0,
  `show_header` tinyint(1) NOT NULL DEFAULT 1,
  `show_footer` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `faq_section_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `title`, `slug`, `excerpt`, `template`, `hero_config`, `status`, `published_at`, `is_homepage`, `show_header`, `show_footer`, `created_by`, `updated_by`, `faq_section_enabled`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Home', 'home', '<p>Explore painting, plastering and home-service options, project examples, practical planning guides and ways to request a quotation.</p>', 'default', '{\"heading\":\"House Painting and Home Services\",\"overlay\":0.3,\"focal_position\":\"center center\",\"cta\":{\"label\":\"Request a Quote\",\"url\":\"#quote-form\"},\"slider_mode\":false}', 'published', '2026-10-10 13:47:00', 1, 1, 1, 1, 1, 1, '2026-10-10 13:47:19', '2026-10-10 14:24:14', NULL),
(2, 'Wall Plastering', 'plastering', '<p>Learn about wall preparation, plastering and the information needed to request a quotation.</p>', 'default', '{\"heading\":\"Wall Plastering\",\"overlay\":null,\"focal_position\":\"center center\",\"cta\":{\"label\":null,\"url\":null},\"slider_mode\":false}', 'published', '2026-10-10 13:47:00', 0, 1, 1, NULL, 1, 1, '2026-10-10 13:47:19', '2026-10-11 01:30:27', NULL),
(3, 'Wall & Tile Hacking', 'hacking', 'Discuss the scope of planned removal work, site access, debris management and applicable approvals.', 'default', '{\"heading\":\"Wall & Tile Hacking\"}', 'published', '2026-10-10 13:47:22', 0, 1, 1, NULL, NULL, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:22', NULL),
(4, 'False Ceiling', 'false-ceiling', 'Discuss ceiling layouts, materials, access panels and coordination with licensed electrical works where required.', 'default', '{\"heading\":\"False Ceiling\"}', 'published', '2026-10-10 13:47:22', 0, 1, 1, NULL, NULL, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:22', NULL),
(5, 'Painting Prices', 'pricing', 'Review published quotation options, or ask for a tailored price based on your work scope.', 'default', '{\"heading\":\"Painting Prices\"}', 'published', '2026-10-10 13:47:22', 0, 1, 1, NULL, NULL, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:22', NULL),
(6, 'Contact', 'contact', 'Send an enquiry using the quotation form or the current contact channels listed on this website.', 'default', '{\"heading\":\"Contact\"}', 'published', '2026-10-10 13:47:22', 0, 1, 1, NULL, NULL, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:22', NULL),
(7, 'Painting & Renovation Guides', 'blog', 'Practical articles about planning, preparation and choosing materials for interior painting projects.', 'default', '{\"heading\":\"Painting & Renovation Guides\"}', 'published', '2026-10-10 13:47:22', 0, 1, 1, NULL, NULL, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:22', NULL),
(8, 'Painting Cost Calculator', 'cost-calculator', 'Explore available guide prices. A final price depends on the actual job scope.', 'default', '{\"heading\":\"Painting Cost Calculator\"}', 'published', '2026-10-10 13:47:22', 0, 1, 1, NULL, NULL, 1, '2026-10-10 13:47:20', '2026-10-10 13:47:22', NULL),
(9, 'About Us', 'about', 'Learn about the services listed on this website and request details of the work process.', 'default', '{\"heading\":\"About Us\"}', 'published', '2026-10-10 13:47:22', 0, 1, 1, 1, 1, 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(10, 'Project Gallery', 'gallery', 'View approved project images and before-and-after photos when they are available.', 'default', '{\"heading\":\"Project Gallery\"}', 'published', '2026-10-10 13:47:22', 0, 1, 1, 1, 1, 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(11, 'Service Areas', 'service-areas', 'Explore Singapore locations and enquire about availability for your property.', 'default', '{\"heading\":\"Service Areas\"}', 'published', '2026-10-10 13:47:22', 0, 1, 1, 1, 1, 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(12, 'Request a Quote', 'request-quote', 'Tell us your property type, location and proposed scope of work for a quotation.', 'default', '{\"heading\":\"Request a Quote\"}', 'published', '2026-10-10 13:47:22', 0, 1, 1, 1, 1, 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `page_sections`
--

CREATE TABLE `page_sections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `page_id` bigint(20) UNSIGNED NOT NULL,
  `section_definition_id` bigint(20) UNSIGNED NOT NULL,
  `heading` varchar(190) DEFAULT NULL,
  `subheading` varchar(255) DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payload`)),
  `theme` varchar(80) NOT NULL DEFAULT 'default',
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `page_sections`
--

INSERT INTO `page_sections` (`id`, `page_id`, `section_definition_id`, `heading`, `subheading`, `payload`, `theme`, `sort_order`, `is_active`, `starts_at`, `ends_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'Professional Painting Services', 'Reliable painting solutions for homes and businesses.', '[]', 'default', 0, 1, NULL, NULL, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(2, 2, 1, 'Wall Plastering', 'Explore this page and contact us for project details.', '[]', 'default', 0, 1, NULL, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(3, 3, 1, 'Wall & Tile Hacking', 'Explore this page and contact us for project details.', '[]', 'default', 0, 1, NULL, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(4, 4, 1, 'False Ceiling', 'Explore this page and contact us for project details.', '[]', 'default', 0, 1, NULL, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(5, 5, 1, 'Painting Prices', 'Explore this page and contact us for project details.', '[]', 'default', 0, 1, NULL, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(6, 6, 1, 'Contact', 'Explore this page and contact us for project details.', '[]', 'default', 0, 1, NULL, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(7, 7, 1, 'Blog', 'Explore this page and contact us for project details.', '[]', 'default', 0, 1, NULL, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(8, 8, 1, 'Painting Cost Calculator', 'Explore this page and contact us for project details.', '[]', 'default', 0, 1, NULL, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(9, 9, 1, 'About Us', 'Explore this page and contact us for project details.', '[]', 'default', 0, 1, NULL, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(10, 10, 1, 'Project Gallery', 'Explore this page and contact us for project details.', '[]', 'default', 0, 1, NULL, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(11, 11, 1, 'Service Areas', 'Explore this page and contact us for project details.', '[]', 'default', 0, 1, NULL, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(12, 12, 1, 'Request a Quote', 'Explore this page and contact us for project details.', '[]', 'default', 0, 1, NULL, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(13, 1, 4, 'WHY CHOOSE OUR HOUSE PAINTERS?', 'Project planning details to review before booking', '{\"items\":[{\"title\":\"Plan the work scope\",\"description\":\"Discuss which rooms, walls and ceilings need work before requesting a quotation.\"},{\"title\":\"Choose suitable materials\",\"description\":\"Compare proposed paint grades and preparation steps for your property.\"},{\"title\":\"Ask about protection\",\"description\":\"Confirm how floors, furniture and fixtures will be protected during the job.\"},{\"title\":\"Clarify the schedule\",\"description\":\"Discuss access, work stages and suitable dates with the team.\"},{\"title\":\"Review the quotation\",\"description\":\"Request a breakdown of the planned work and any possible extras.\"},{\"title\":\"Arrange final inspection\",\"description\":\"Discuss the handover and how any remaining concerns can be raised.\"}],\"guarantees\":[{\"title\":\"Scope confirmed before booking\",\"description\":\"Confirm the exact work and materials before accepting an offer.\"},{\"title\":\"Clear preparation expectations\",\"description\":\"Ask what protection and surface preparation is included.\"},{\"title\":\"A practical work plan\",\"description\":\"Discuss the schedule and access needs before work starts.\"},{\"title\":\"Handover discussion\",\"description\":\"Review the completed scope together.\"}]}', 'default', 10, 1, NULL, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(14, 1, 2, 'Before you proceed', 'Things to confirm before work starts', '{\"text\":\"<p>Confirm the areas to be worked on.</p><p>Ask which materials will be used.</p><p>Clarify preparation and cleanup expectations.</p><p>Agree on the work schedule and site access.</p><p>Review the final scope and quotation.</p>\"}', 'default', 20, 1, NULL, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(15, 1, 9, 'See how our work is done', 'Published project videos are shown here when available.', '[]', 'default', 30, 1, NULL, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(16, 1, 10, 'Customer feedback', 'Verified customer feedback appears after approval.', '[]', 'default', 40, 1, NULL, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(17, 1, 12, 'Explore painting price options', 'Select an available package to review its current published information.', '[]', 'default', 50, 1, NULL, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(18, 1, 14, 'Let us help plan your project', 'Share your details to discuss the next steps.', '{\"steps\":[{\"title\":\"REQUEST A QUOTE\",\"description\":\"Describe your rooms and service requirements.\",\"button\":\"Get a Free Quote\"},{\"title\":\"REVIEW THE WORK\",\"description\":\"Discuss surfaces, photos and the scope of preparation.\",\"button\":\"Ask a Question\"},{\"title\":\"CONFIRM THE DETAILS\",\"description\":\"Review pricing and work terms provided by the team.\",\"button\":\"Enquire Now\"},{\"title\":\"ARRANGE THE PROJECT\",\"description\":\"Coordinate a suitable date after agreeing to the quotation.\",\"button\":\"Contact Us\"},{\"title\":\"CHECK THE FINISH\",\"description\":\"Review the result and share feedback after completion.\",\"button\":\"View Work\"}],\"items\":[\"Discuss your painting or plastering requirements before arranging work.\",\"Receive a quotation based on the proposed scope and site condition.\",\"Confirm the chosen paint, coating or plastering materials.\",\"Ask whether masking, furniture protection and cleanup are included.\",\"Agree on the work dates and site-access arrangements.\",\"Review project images and videos once approved media is available.\",\"Check the active FAQs or contact a member of the team.\",\"Submit a quotation enquiry using the website form.\"]}', 'default', 60, 1, NULL, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(19, 1, 8, 'Before & After Workmanship Gallery', 'Real project photos appear here once uploaded.', '[]', 'default', 70, 1, NULL, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(20, 1, 11, 'Client messages', 'Approved WhatsApp testimonial screenshots appear when available.', '[]', 'default', 80, 1, NULL, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(21, 1, 13, 'Frequently Asked Questions', 'General information about quotes and preparing for painting work.', '[]', 'default', 90, 1, NULL, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(22, 1, 3, 'Our House Painting Services Guarantees to You!', 'What you can expect from our published service information.', '[]', 'default', 100, 1, NULL, NULL, '2026-10-10 14:41:05', '2026-10-10 14:42:44', NULL),
(23, 2, 4, 'Wall Plastering for Your Home', 'Discuss suitable preparation, surface condition and finishes before work begins.', '{\"items\":[{\"title\":\"Surface inspection\",\"description\":\"Identify cracks, uneven walls, loose coatings or water damage before preparing the quotation.\"},{\"title\":\"Material selection\",\"description\":\"Confirm suitable products and application methods for the existing substrate.\"},{\"title\":\"Clear project scope\",\"description\":\"Agree on protection, preparation, coats, scheduling and cleanup.\"}]}', 'default', 10, 1, NULL, NULL, '2026-10-11 01:31:52', '2026-10-11 01:31:52', NULL),
(24, 2, 10, 'Customer Reviews', 'Feedback saved and activated in Testimonials Management.', '[]', 'default', 20, 1, NULL, NULL, '2026-10-11 01:31:52', '2026-10-11 01:31:52', NULL),
(25, 2, 9, 'See How Wall Plastering Is Done', 'Watch published project videos added through Admin -> Videos.', '[]', 'default', 30, 1, NULL, NULL, '2026-10-11 01:31:52', '2026-10-11 01:31:52', NULL),
(26, 2, 6, 'Wall Plastering Pricing & Quotation', 'Review approved pricing options or request a detailed quotation.', '[]', 'default', 40, 1, NULL, NULL, '2026-10-11 01:31:52', '2026-10-11 01:31:52', NULL),
(27, 2, 8, 'Before & After Wall Plastering', 'Project photographs added through Gallery Management and Section Media.', '[]', 'default', 50, 1, NULL, NULL, '2026-10-11 01:31:52', '2026-10-11 01:31:52', NULL),
(28, 2, 11, 'Customer Chat Screenshots', 'Approved screenshot reviews from Testimonials Management.', '[]', 'default', 60, 1, NULL, NULL, '2026-10-11 01:31:52', '2026-10-11 01:31:52', NULL),
(29, 2, 13, 'Frequently Asked Plastering Questions', 'Common questions to clarify with the team before starting.', '[]', 'default', 70, 1, NULL, NULL, '2026-10-11 01:31:52', '2026-10-11 01:31:52', NULL),
(30, 2, 15, 'Request a Plastering Quotation', 'Tell us about your property and the work you need.', '[]', 'default', 80, 1, NULL, NULL, '2026-10-11 01:31:52', '2026-10-11 01:31:52', NULL),
(31, 3, 4, 'Plan Your Hacking Project with Confidence', 'Discuss the work scope, site requirements and applicable approvals.', '{\"items\":[{\"title\":\"Define the removal scope\",\"description\":\"Confirm rooms, tiles, surfaces and fittings to remove.\"},{\"title\":\"Check necessary permissions\",\"description\":\"Verify applicable HDB or building-management approvals before work.\"},{\"title\":\"Protect your home\",\"description\":\"Discuss dust control and site access with the contractor.\"},{\"title\":\"Plan debris clearing\",\"description\":\"Confirm disposal, cleanup and handover requirements.\"}]}', 'default', 10, 1, NULL, NULL, '2026-10-11 03:06:27', '2026-10-11 03:06:27', NULL),
(32, 3, 2, 'What Wall & Tile Hacking Can Include', 'Choose what is relevant to your project and confirm the exact scope in writing.', '{\"groups\":[{\"title\":\"Kitchen\",\"items\":[\"Wall tile removal\",\"Floor tile and skirting removal\",\"Cabinet dismantling\",\"Countertop removal where suitable\"]},{\"title\":\"Bathroom \\/ Toilets\",\"items\":[\"Wall and floor tile removal\",\"Removal of selected sanitary fixtures\",\"Door and frame removal if approved\",\"Debris clearing\"]},{\"title\":\"Living Room \\/ Bedrooms\",\"items\":[\"Floor tile and skirting removal\",\"Approved non-structural wall work\",\"Selected built-in carpentry removal\",\"Coordination of floor preparation\"]},{\"title\":\"Planning & Additional Work\",\"items\":[\"Applicable HDB or MCST permissions\",\"Protection and haulage\",\"Site access and work schedule\",\"Follow-on plastering, tiling or painting\"]}]}', 'default', 15, 1, NULL, NULL, '2026-10-11 03:06:27', '2026-10-11 03:06:27', NULL),
(33, 3, 10, 'Customer Experiences', 'Published customer feedback from Testimonials Management.', '[]', 'default', 20, 1, NULL, NULL, '2026-10-11 03:06:27', '2026-10-11 03:06:27', NULL),
(34, 3, 5, 'What We Can Help With', 'Explore published related renovation services.', '[]', 'default', 25, 1, NULL, NULL, '2026-10-11 03:06:27', '2026-10-11 03:06:27', NULL),
(35, 3, 9, 'See How the Work Is Done', 'Published project videos from Video Management.', '[]', 'default', 30, 1, NULL, NULL, '2026-10-11 03:06:27', '2026-10-11 03:06:27', NULL),
(36, 3, 6, 'Wall & Tile Hacking Quotation', 'Prices depend on the approved project scope and site conditions.', '[]', 'default', 40, 1, NULL, NULL, '2026-10-11 03:06:27', '2026-10-11 03:06:27', NULL),
(37, 3, 8, 'Project Photos', 'Images from approved Hacking projects, when available.', '[]', 'default', 50, 1, NULL, NULL, '2026-10-11 03:06:27', '2026-10-11 03:06:27', NULL),
(38, 3, 11, 'Customer Chat Screenshots', 'Published WhatsApp testimonial screenshots.', '[]', 'default', 60, 1, NULL, NULL, '2026-10-11 03:06:27', '2026-10-11 03:06:27', NULL),
(39, 3, 13, 'Frequently Asked Hacking Questions', 'Important questions about approvals, scheduling and finishing.', '[]', 'default', 70, 1, NULL, NULL, '2026-10-11 03:06:27', '2026-10-11 03:06:27', NULL),
(40, 3, 15, 'Request a Hacking Quote', 'Share the work areas and property details.', '[]', 'default', 80, 1, NULL, NULL, '2026-10-11 03:06:27', '2026-10-11 03:06:27', NULL),
(41, 4, 4, 'False Ceiling Installation for Your Space', 'From ceiling profiles to material options, discuss the right setup for your property.', '{\"items\":[{\"title\":\"Review the ceiling layout\",\"description\":\"Discuss flat ceiling profiles, L-boxes and lighting coves.\"},{\"title\":\"Choose suitable materials\",\"description\":\"Consider moisture resistance, room conditions and access panels.\"},{\"title\":\"Coordinate lighting and services\",\"description\":\"Confirm light fittings, access and any licensed electrical work.\"}]}', 'default', 10, 1, NULL, NULL, '2026-10-14 03:08:25', '2026-10-14 03:08:25', NULL),
(42, 4, 2, 'What We Can Help With', 'Explore common ceiling and partition options; your project quotation will confirm which items are included.', '{\"groups\":[{\"title\":\"False Ceiling Designs\",\"description\":\"Discuss your preferred ceiling profile and finish.\",\"items\":[\"Standard flat false ceiling\",\"L-box and perimeter detail\",\"Lighting cove and feature detailing\",\"Access panels and maintenance clearance\"]},{\"title\":\"Partition Wall Installation\",\"description\":\"Choose materials appropriate to each room.\",\"items\":[\"Regular drywall partitions\",\"Acoustic partition options\",\"Moisture-resistant board options\",\"Fire-rated specifications if required\"]},{\"title\":\"Preparation & Handover\",\"description\":\"Confirm site requirements before work starts.\",\"items\":[\"Room measurements and ceiling height\",\"Lighting and electrical coordination\",\"Building-management requirements\",\"Finishing, painting and cleanup scope\"]}]}', 'default', 15, 1, NULL, NULL, '2026-10-14 03:08:25', '2026-10-14 03:08:25', NULL),
(43, 4, 10, 'Customer Feedback', 'Active customer reviews from your CMS.', '[]', 'default', 20, 1, NULL, NULL, '2026-10-14 03:08:25', '2026-10-14 03:08:25', NULL),
(44, 4, 6, 'False Ceiling & Partition Wall Prices', 'Review live published package items. Confirm design, site conditions and the final quotation with the team.', '[]', 'default', 30, 1, NULL, NULL, '2026-10-14 03:08:25', '2026-10-14 03:08:25', NULL),
(45, 4, 9, 'See Our Ceiling & Partition Projects', 'Watch videos assigned to the False Ceiling page or service when available.', '[]', 'default', 40, 1, NULL, NULL, '2026-10-14 03:08:25', '2026-10-14 03:08:25', NULL),
(46, 4, 8, 'Ceiling & Partition Project Gallery', 'Photographs from published false ceiling and partition projects.', '[]', 'default', 50, 1, NULL, NULL, '2026-10-14 03:08:25', '2026-10-14 03:08:25', NULL),
(47, 4, 11, 'Client Chat Screenshots', 'Approved customer messages shared through the existing testimonials module.', '[]', 'default', 60, 1, NULL, NULL, '2026-10-14 03:08:25', '2026-10-14 03:08:25', NULL),
(48, 4, 13, 'Frequently Asked False Ceiling Questions', 'Useful information about ceiling materials, measurements, lighting and installation.', '[]', 'default', 70, 1, NULL, NULL, '2026-10-14 03:08:25', '2026-10-14 03:08:25', NULL),
(49, 4, 15, 'Request a False Ceiling Quotation', 'Share your preferred design, approximate size and required services.', '[]', 'default', 80, 1, NULL, NULL, '2026-10-14 03:08:25', '2026-10-14 03:08:25', NULL);

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
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `group_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `group_name`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'dashboard_view', 'admin', 'Dashboard', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(2, 'system_tools_manage', 'admin', 'System Tools', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(3, 'cache_clear', 'admin', 'System Tools', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(4, 'queue_manage', 'admin', 'System Tools', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(5, 'backup_manage', 'admin', 'System Tools', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(6, 'site_setting_view', 'admin', 'Site Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(7, 'site_setting_update', 'admin', 'Site Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(8, 'site_setting_create', 'admin', 'Site Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(9, 'site_setting_delete', 'admin', 'Site Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(10, 'site_setting_trash', 'admin', 'Site Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(11, 'site_setting_restore', 'admin', 'Site Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(12, 'site_setting_force_delete', 'admin', 'Site Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(13, 'header_setting_view', 'admin', 'Header Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(14, 'header_setting_update', 'admin', 'Header Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(15, 'header_menu_list', 'admin', 'Header Menu', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(16, 'header_menu_view', 'admin', 'Header Menu', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(17, 'header_menu_create', 'admin', 'Header Menu', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(18, 'header_menu_update', 'admin', 'Header Menu', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(19, 'header_menu_delete', 'admin', 'Header Menu', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(20, 'header_menu_toggle', 'admin', 'Header Menu', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(21, 'header_menu_reorder', 'admin', 'Header Menu', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(22, 'footer_setting_view', 'admin', 'Footer Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(23, 'footer_setting_update', 'admin', 'Footer Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(24, 'footer_link_list', 'admin', 'Footer Links', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(25, 'footer_link_view', 'admin', 'Footer Links', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(26, 'footer_link_create', 'admin', 'Footer Links', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(27, 'footer_link_update', 'admin', 'Footer Links', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(28, 'footer_link_delete', 'admin', 'Footer Links', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(29, 'footer_link_toggle', 'admin', 'Footer Links', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(30, 'footer_link_reorder', 'admin', 'Footer Links', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(31, 'menu_list', 'admin', 'Menus', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(32, 'menu_view', 'admin', 'Menus', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(33, 'menu_create', 'admin', 'Menus', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(34, 'menu_update', 'admin', 'Menus', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(35, 'menu_delete', 'admin', 'Menus', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(36, 'menu_trash', 'admin', 'Menus', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(37, 'menu_restore', 'admin', 'Menus', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(38, 'menu_force_delete', 'admin', 'Menus', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(39, 'page_list', 'admin', 'Pages', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(40, 'page_view', 'admin', 'Pages', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(41, 'page_create', 'admin', 'Pages', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(42, 'page_update', 'admin', 'Pages', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(43, 'page_delete', 'admin', 'Pages', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(44, 'page_trash', 'admin', 'Pages', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(45, 'page_restore', 'admin', 'Pages', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(46, 'page_force_delete', 'admin', 'Pages', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(47, 'page_publish', 'admin', 'Pages', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(48, 'page_unpublish', 'admin', 'Pages', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(49, 'page_preview', 'admin', 'Pages', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(50, 'page_duplicate', 'admin', 'Pages', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(51, 'page_section_list', 'admin', 'Page Sections', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(52, 'page_section_view', 'admin', 'Page Sections', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(53, 'page_section_create', 'admin', 'Page Sections', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(54, 'page_section_update', 'admin', 'Page Sections', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(55, 'page_section_delete', 'admin', 'Page Sections', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(56, 'page_section_toggle', 'admin', 'Page Sections', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(57, 'page_section_reorder', 'admin', 'Page Sections', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(58, 'page_section_duplicate', 'admin', 'Page Sections', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(59, 'page_section_trash', 'admin', 'Page Sections', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(60, 'page_section_restore', 'admin', 'Page Sections', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(61, 'page_section_force_delete', 'admin', 'Page Sections', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(62, 'section_media_list', 'admin', 'Section Media', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(63, 'section_media_view', 'admin', 'Section Media', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(64, 'section_media_create', 'admin', 'Section Media', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(65, 'section_media_update', 'admin', 'Section Media', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(66, 'section_media_delete', 'admin', 'Section Media', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(67, 'section_media_trash', 'admin', 'Section Media', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(68, 'section_media_restore', 'admin', 'Section Media', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(69, 'section_media_force_delete', 'admin', 'Section Media', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(70, 'section_definition_list', 'admin', 'Section Definitions', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(71, 'section_definition_view', 'admin', 'Section Definitions', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(72, 'section_definition_create', 'admin', 'Section Definitions', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(73, 'section_definition_update', 'admin', 'Section Definitions', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(74, 'section_definition_delete', 'admin', 'Section Definitions', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(75, 'section_definition_trash', 'admin', 'Section Definitions', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(76, 'section_definition_restore', 'admin', 'Section Definitions', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(77, 'section_definition_force_delete', 'admin', 'Section Definitions', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(78, 'section_definition_toggle', 'admin', 'Section Definitions', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(79, 'service_list', 'admin', 'Services', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(80, 'service_view', 'admin', 'Services', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(81, 'service_create', 'admin', 'Services', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(82, 'service_update', 'admin', 'Services', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(83, 'service_delete', 'admin', 'Services', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(84, 'service_trash', 'admin', 'Services', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(85, 'service_restore', 'admin', 'Services', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(86, 'service_force_delete', 'admin', 'Services', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(87, 'service_publish', 'admin', 'Services', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(88, 'service_unpublish', 'admin', 'Services', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(89, 'service_duplicate', 'admin', 'Services', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(90, 'service_feature_list', 'admin', 'Service Features', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(91, 'service_feature_view', 'admin', 'Service Features', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(92, 'service_feature_create', 'admin', 'Service Features', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(93, 'service_feature_update', 'admin', 'Service Features', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(94, 'service_feature_delete', 'admin', 'Service Features', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(95, 'service_feature_toggle', 'admin', 'Service Features', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(96, 'service_feature_reorder', 'admin', 'Service Features', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(97, 'service_feature_duplicate', 'admin', 'Service Features', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(98, 'service_feature_trash', 'admin', 'Service Features', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(99, 'service_feature_restore', 'admin', 'Service Features', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(100, 'service_feature_force_delete', 'admin', 'Service Features', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(101, 'location_list', 'admin', 'Locations', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(102, 'location_view', 'admin', 'Locations', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(103, 'location_create', 'admin', 'Locations', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(104, 'location_update', 'admin', 'Locations', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(105, 'location_delete', 'admin', 'Locations', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(106, 'location_trash', 'admin', 'Locations', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(107, 'location_restore', 'admin', 'Locations', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(108, 'location_force_delete', 'admin', 'Locations', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(109, 'location_publish', 'admin', 'Locations', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(110, 'location_unpublish', 'admin', 'Locations', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(111, 'location_duplicate', 'admin', 'Locations', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(112, 'pricing_package_list', 'admin', 'Pricing Packages', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(113, 'pricing_package_view', 'admin', 'Pricing Packages', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(114, 'pricing_package_create', 'admin', 'Pricing Packages', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(115, 'pricing_package_update', 'admin', 'Pricing Packages', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(116, 'pricing_package_delete', 'admin', 'Pricing Packages', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(117, 'pricing_package_toggle', 'admin', 'Pricing Packages', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(118, 'pricing_package_duplicate', 'admin', 'Pricing Packages', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(119, 'pricing_package_trash', 'admin', 'Pricing Packages', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(120, 'pricing_package_restore', 'admin', 'Pricing Packages', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(121, 'pricing_package_force_delete', 'admin', 'Pricing Packages', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(122, 'pricing_item_list', 'admin', 'Pricing Items', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(123, 'pricing_item_view', 'admin', 'Pricing Items', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(124, 'pricing_item_create', 'admin', 'Pricing Items', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(125, 'pricing_item_update', 'admin', 'Pricing Items', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(126, 'pricing_item_delete', 'admin', 'Pricing Items', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(127, 'pricing_item_toggle', 'admin', 'Pricing Items', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(128, 'pricing_item_duplicate', 'admin', 'Pricing Items', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(129, 'pricing_item_trash', 'admin', 'Pricing Items', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(130, 'pricing_item_restore', 'admin', 'Pricing Items', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(131, 'pricing_item_force_delete', 'admin', 'Pricing Items', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(132, 'pricing_addon_list', 'admin', 'Pricing Add-ons', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(133, 'pricing_addon_view', 'admin', 'Pricing Add-ons', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(134, 'pricing_addon_create', 'admin', 'Pricing Add-ons', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(135, 'pricing_addon_update', 'admin', 'Pricing Add-ons', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(136, 'pricing_addon_delete', 'admin', 'Pricing Add-ons', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(137, 'pricing_addon_toggle', 'admin', 'Pricing Add-ons', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(138, 'pricing_addon_duplicate', 'admin', 'Pricing Add-ons', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(139, 'pricing_addon_trash', 'admin', 'Pricing Add-ons', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(140, 'pricing_addon_restore', 'admin', 'Pricing Add-ons', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(141, 'pricing_addon_force_delete', 'admin', 'Pricing Add-ons', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(142, 'pricing_package_addon_list', 'admin', 'Pricing Package Add-ons', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(143, 'pricing_package_addon_view', 'admin', 'Pricing Package Add-ons', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(144, 'pricing_package_addon_create', 'admin', 'Pricing Package Add-ons', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(145, 'pricing_package_addon_update', 'admin', 'Pricing Package Add-ons', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(146, 'pricing_package_addon_delete', 'admin', 'Pricing Package Add-ons', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(147, 'pricing_package_addon_trash', 'admin', 'Pricing Package Add-ons', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(148, 'pricing_package_addon_restore', 'admin', 'Pricing Package Add-ons', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(149, 'pricing_package_addon_force_delete', 'admin', 'Pricing Package Add-ons', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(150, 'media_list', 'admin', 'Media', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(151, 'media_view', 'admin', 'Media', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(152, 'media_upload', 'admin', 'Media', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(153, 'media_update', 'admin', 'Media', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(154, 'media_delete', 'admin', 'Media', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(155, 'media_trash', 'admin', 'Media', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(156, 'media_restore', 'admin', 'Media', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(157, 'media_force_delete', 'admin', 'Media', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(158, 'media_download', 'admin', 'Media', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(159, 'media_replace', 'admin', 'Media', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(160, 'media_regenerate_conversion', 'admin', 'Media', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(161, 'gallery_list', 'admin', 'Galleries', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(162, 'gallery_view', 'admin', 'Galleries', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(163, 'gallery_create', 'admin', 'Galleries', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(164, 'gallery_update', 'admin', 'Galleries', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(165, 'gallery_delete', 'admin', 'Galleries', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(166, 'gallery_toggle', 'admin', 'Galleries', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(167, 'gallery_reorder', 'admin', 'Galleries', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(168, 'gallery_trash', 'admin', 'Galleries', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(169, 'gallery_restore', 'admin', 'Galleries', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(170, 'gallery_force_delete', 'admin', 'Galleries', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(171, 'gallery_item_list', 'admin', 'Gallery Items', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(172, 'gallery_item_view', 'admin', 'Gallery Items', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(173, 'gallery_item_create', 'admin', 'Gallery Items', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(174, 'gallery_item_update', 'admin', 'Gallery Items', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(175, 'gallery_item_delete', 'admin', 'Gallery Items', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(176, 'gallery_item_toggle', 'admin', 'Gallery Items', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(177, 'gallery_item_reorder', 'admin', 'Gallery Items', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(178, 'gallery_item_trash', 'admin', 'Gallery Items', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(179, 'gallery_item_restore', 'admin', 'Gallery Items', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(180, 'gallery_item_force_delete', 'admin', 'Gallery Items', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(181, 'video_list', 'admin', 'Videos', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(182, 'video_view', 'admin', 'Videos', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(183, 'video_create', 'admin', 'Videos', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(184, 'video_update', 'admin', 'Videos', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(185, 'video_delete', 'admin', 'Videos', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(186, 'video_trash', 'admin', 'Videos', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(187, 'video_restore', 'admin', 'Videos', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(188, 'video_force_delete', 'admin', 'Videos', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(189, 'video_upload', 'admin', 'Videos', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(190, 'video_embed', 'admin', 'Videos', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(191, 'video_process', 'admin', 'Videos', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(192, 'video_toggle', 'admin', 'Videos', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(193, 'video_reorder', 'admin', 'Videos', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(194, 'testimonial_list', 'admin', 'Testimonials', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(195, 'testimonial_create', 'admin', 'Testimonials', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(196, 'testimonial_view', 'admin', 'Testimonials', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(197, 'testimonial_update', 'admin', 'Testimonials', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(198, 'testimonial_delete', 'admin', 'Testimonials', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(199, 'testimonial_trash', 'admin', 'Testimonials', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(200, 'testimonial_restore', 'admin', 'Testimonials', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(201, 'testimonial_force_delete', 'admin', 'Testimonials', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(202, 'review_list', 'admin', 'Reviews', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(203, 'review_view', 'admin', 'Reviews', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(204, 'review_create', 'admin', 'Reviews', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(205, 'review_update', 'admin', 'Reviews', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(206, 'review_delete', 'admin', 'Reviews', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(207, 'review_publish', 'admin', 'Reviews', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(208, 'review_unpublish', 'admin', 'Reviews', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(209, 'review_set_featured', 'admin', 'Reviews', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(210, 'faq_list', 'admin', 'FAQs', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(211, 'faq_view', 'admin', 'FAQs', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(212, 'faq_create', 'admin', 'FAQs', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(213, 'faq_update', 'admin', 'FAQs', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(214, 'faq_delete', 'admin', 'FAQs', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(215, 'faq_toggle', 'admin', 'FAQs', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(216, 'faq_reorder', 'admin', 'FAQs', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(217, 'faq_trash', 'admin', 'FAQs', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(218, 'faq_restore', 'admin', 'FAQs', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(219, 'faq_force_delete', 'admin', 'FAQs', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(220, 'blog_category_list', 'admin', 'Blog Categories', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(221, 'blog_category_view', 'admin', 'Blog Categories', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(222, 'blog_category_create', 'admin', 'Blog Categories', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(223, 'blog_category_update', 'admin', 'Blog Categories', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(224, 'blog_category_delete', 'admin', 'Blog Categories', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(225, 'blog_category_trash', 'admin', 'Blog Categories', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(226, 'blog_category_restore', 'admin', 'Blog Categories', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(227, 'blog_category_force_delete', 'admin', 'Blog Categories', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(228, 'blog_category_toggle', 'admin', 'Blog Categories', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(229, 'blog_post_list', 'admin', 'Blog Posts', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(230, 'blog_post_view', 'admin', 'Blog Posts', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(231, 'blog_post_create', 'admin', 'Blog Posts', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(232, 'blog_post_update', 'admin', 'Blog Posts', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(233, 'blog_post_delete', 'admin', 'Blog Posts', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(234, 'blog_post_trash', 'admin', 'Blog Posts', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(235, 'blog_post_restore', 'admin', 'Blog Posts', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(236, 'blog_post_force_delete', 'admin', 'Blog Posts', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(237, 'blog_post_publish', 'admin', 'Blog Posts', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(238, 'blog_post_unpublish', 'admin', 'Blog Posts', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(239, 'blog_post_preview', 'admin', 'Blog Posts', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(240, 'blog_post_duplicate', 'admin', 'Blog Posts', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(241, 'contact_channel_list', 'admin', 'Contact Channels', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(242, 'contact_channel_view', 'admin', 'Contact Channels', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(243, 'contact_channel_create', 'admin', 'Contact Channels', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(244, 'contact_channel_update', 'admin', 'Contact Channels', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(245, 'contact_channel_delete', 'admin', 'Contact Channels', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(246, 'contact_channel_toggle', 'admin', 'Contact Channels', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(247, 'contact_channel_reorder', 'admin', 'Contact Channels', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(248, 'contact_channel_set_default', 'admin', 'Contact Channels', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(249, 'contact_channel_trash', 'admin', 'Contact Channels', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(250, 'contact_channel_restore', 'admin', 'Contact Channels', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(251, 'contact_channel_force_delete', 'admin', 'Contact Channels', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(252, 'contact_message_list', 'admin', 'Contact Messages', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(253, 'contact_message_view', 'admin', 'Contact Messages', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(254, 'contact_message_delete', 'admin', 'Contact Messages', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(255, 'contact_message_mark_read', 'admin', 'Contact Messages', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(256, 'contact_message_mark_unread', 'admin', 'Contact Messages', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(257, 'contact_message_reply', 'admin', 'Contact Messages', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(258, 'contact_target_list', 'admin', 'Contact Targets', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(259, 'contact_target_view', 'admin', 'Contact Targets', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(260, 'contact_target_create', 'admin', 'Contact Targets', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(261, 'contact_target_update', 'admin', 'Contact Targets', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(262, 'contact_target_delete', 'admin', 'Contact Targets', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(263, 'contact_target_trash', 'admin', 'Contact Targets', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(264, 'contact_target_restore', 'admin', 'Contact Targets', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(265, 'contact_target_force_delete', 'admin', 'Contact Targets', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(266, 'lead_list', 'admin', 'Leads', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(267, 'lead_view', 'admin', 'Leads', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(268, 'lead_create', 'admin', 'Leads', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(269, 'lead_update', 'admin', 'Leads', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(270, 'lead_delete', 'admin', 'Leads', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(271, 'lead_trash', 'admin', 'Leads', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(272, 'lead_restore', 'admin', 'Leads', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(273, 'lead_force_delete', 'admin', 'Leads', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(274, 'lead_assign', 'admin', 'Leads', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(275, 'lead_change_status', 'admin', 'Leads', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(276, 'lead_export', 'admin', 'Leads', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(277, 'lead_internal_note', 'admin', 'Leads', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(278, 'lead_public_note', 'admin', 'Leads', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(279, 'lead_attachment_download', 'admin', 'Leads', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(280, 'lead_form_field_list', 'admin', 'Lead Form Fields', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(281, 'lead_form_field_view', 'admin', 'Lead Form Fields', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(282, 'lead_form_field_create', 'admin', 'Lead Form Fields', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(283, 'lead_form_field_update', 'admin', 'Lead Form Fields', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(284, 'lead_form_field_delete', 'admin', 'Lead Form Fields', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(285, 'lead_form_field_toggle', 'admin', 'Lead Form Fields', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(286, 'lead_form_field_reorder', 'admin', 'Lead Form Fields', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(287, 'lead_form_field_trash', 'admin', 'Lead Form Fields', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(288, 'lead_form_field_restore', 'admin', 'Lead Form Fields', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(289, 'lead_form_field_force_delete', 'admin', 'Lead Form Fields', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(290, 'seo_setting_view', 'admin', 'SEO Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(291, 'seo_setting_update', 'admin', 'SEO Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(292, 'seo_sitemap_manage', 'admin', 'SEO Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(293, 'seo_schema_manage', 'admin', 'SEO Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(294, 'seo_social_manage', 'admin', 'SEO Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(295, 'seo_meta_list', 'admin', 'SEO Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(296, 'seo_meta_view', 'admin', 'SEO Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(297, 'seo_meta_create', 'admin', 'SEO Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(298, 'seo_meta_update', 'admin', 'SEO Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(299, 'seo_meta_delete', 'admin', 'SEO Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(300, 'seo_meta_trash', 'admin', 'SEO Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(301, 'seo_meta_restore', 'admin', 'SEO Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(302, 'seo_meta_force_delete', 'admin', 'SEO Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(303, 'redirect_list', 'admin', 'Redirects', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(304, 'redirect_view', 'admin', 'Redirects', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(305, 'redirect_create', 'admin', 'Redirects', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(306, 'redirect_update', 'admin', 'Redirects', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(307, 'redirect_delete', 'admin', 'Redirects', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(308, 'redirect_toggle', 'admin', 'Redirects', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(309, 'redirect_import', 'admin', 'Redirects', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(310, 'redirect_export', 'admin', 'Redirects', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(311, 'redirect_trash', 'admin', 'Redirects', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(312, 'redirect_restore', 'admin', 'Redirects', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(313, 'redirect_force_delete', 'admin', 'Redirects', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(314, 'tracking_setting_view', 'admin', 'Tracking Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(315, 'tracking_setting_update', 'admin', 'Tracking Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(316, 'tracking_event_manage', 'admin', 'Tracking Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(317, 'tracking_test_event', 'admin', 'Tracking Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(318, 'tracking_consent_manage', 'admin', 'Tracking Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(319, 'tracking_provider_list', 'admin', 'Tracking Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(320, 'tracking_provider_view', 'admin', 'Tracking Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(321, 'tracking_provider_create', 'admin', 'Tracking Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(322, 'tracking_provider_update', 'admin', 'Tracking Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(323, 'tracking_provider_delete', 'admin', 'Tracking Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(324, 'tracking_provider_toggle', 'admin', 'Tracking Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(325, 'tracking_provider_trash', 'admin', 'Tracking Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(326, 'tracking_provider_restore', 'admin', 'Tracking Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(327, 'tracking_provider_force_delete', 'admin', 'Tracking Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(328, 'tracking_event_rule_list', 'admin', 'Tracking Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(329, 'tracking_event_rule_view', 'admin', 'Tracking Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(330, 'tracking_event_rule_create', 'admin', 'Tracking Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(331, 'tracking_event_rule_update', 'admin', 'Tracking Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(332, 'tracking_event_rule_delete', 'admin', 'Tracking Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(333, 'tracking_event_rule_toggle', 'admin', 'Tracking Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(334, 'tracking_event_rule_trash', 'admin', 'Tracking Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(335, 'tracking_event_rule_restore', 'admin', 'Tracking Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(336, 'tracking_event_rule_force_delete', 'admin', 'Tracking Settings', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(337, 'meta_pixel_view', 'admin', 'Meta Pixel', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(338, 'meta_pixel_update', 'admin', 'Meta Pixel', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(339, 'meta_pixel_toggle', 'admin', 'Meta Pixel', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(340, 'meta_pixel_test', 'admin', 'Meta Pixel', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(341, 'meta_capi_manage', 'admin', 'Meta Pixel', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(342, 'admin_list', 'admin', 'Admins', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(343, 'admin_view', 'admin', 'Admins', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(344, 'admin_create', 'admin', 'Admins', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(345, 'admin_update', 'admin', 'Admins', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(346, 'admin_delete', 'admin', 'Admins', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(347, 'admin_trash', 'admin', 'Admins', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(348, 'admin_restore', 'admin', 'Admins', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(349, 'admin_force_delete', 'admin', 'Admins', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(350, 'admin_change_status', 'admin', 'Admins', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(351, 'admin_assign_role', 'admin', 'Admins', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(352, 'admin_reset_password', 'admin', 'Admins', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(353, 'user_list', 'admin', 'Users', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(354, 'user_view', 'admin', 'Users', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(355, 'user_create', 'admin', 'Users', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(356, 'user_update', 'admin', 'Users', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(357, 'user_delete', 'admin', 'Users', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(358, 'user_change_status', 'admin', 'Users', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(359, 'user_view_enquiries', 'admin', 'Users', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(360, 'user_reset_password', 'admin', 'Users', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(361, 'role_list', 'admin', 'Roles', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(362, 'role_view', 'admin', 'Roles', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(363, 'role_create', 'admin', 'Roles', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(364, 'role_update', 'admin', 'Roles', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(365, 'role_delete', 'admin', 'Roles', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(366, 'role_trash', 'admin', 'Roles', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(367, 'role_restore', 'admin', 'Roles', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(368, 'role_force_delete', 'admin', 'Roles', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(369, 'role_assign_permission', 'admin', 'Roles', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(370, 'permission_list', 'admin', 'Permissions', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(371, 'permission_view', 'admin', 'Permissions', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(372, 'permission_create', 'admin', 'Permissions', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(373, 'permission_update', 'admin', 'Permissions', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(374, 'permission_delete', 'admin', 'Permissions', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(375, 'permission_trash', 'admin', 'Permissions', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(376, 'permission_restore', 'admin', 'Permissions', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(377, 'permission_force_delete', 'admin', 'Permissions', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(378, 'audit_log_list', 'admin', 'Audit Logs', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(379, 'audit_log_view', 'admin', 'Audit Logs', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(380, 'audit_log_export', 'admin', 'Audit Logs', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(381, 'audit_log_delete', 'admin', 'Audit Logs', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(382, 'audit_log_trash', 'admin', 'Audit Logs', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(383, 'audit_log_restore', 'admin', 'Audit Logs', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(384, 'audit_log_force_delete', 'admin', 'Audit Logs', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(385, 'campaign_list', 'admin', 'Campaigns', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(386, 'campaign_view', 'admin', 'Campaigns', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(387, 'campaign_create', 'admin', 'Campaigns', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(388, 'campaign_update', 'admin', 'Campaigns', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(389, 'campaign_delete', 'admin', 'Campaigns', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(390, 'campaign_trash', 'admin', 'Campaigns', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(391, 'campaign_restore', 'admin', 'Campaigns', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(392, 'campaign_force_delete', 'admin', 'Campaigns', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(393, 'campaign_publish', 'admin', 'Campaigns', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(394, 'campaign_unpublish', 'admin', 'Campaigns', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(395, 'campaign_preview', 'admin', 'Campaigns', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(396, 'campaign_duplicate', 'admin', 'Campaigns', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(397, 'campaign_set_default', 'admin', 'Campaigns', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(398, 'campaign_media_manage', 'admin', 'Campaigns', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(399, 'campaign_section_list', 'admin', 'Campaign Sections', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(400, 'campaign_section_view', 'admin', 'Campaign Sections', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(401, 'campaign_section_create', 'admin', 'Campaign Sections', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(402, 'campaign_section_update', 'admin', 'Campaign Sections', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(403, 'campaign_section_delete', 'admin', 'Campaign Sections', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(404, 'campaign_section_toggle', 'admin', 'Campaign Sections', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(405, 'campaign_section_reorder', 'admin', 'Campaign Sections', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(406, 'campaign_section_duplicate', 'admin', 'Campaign Sections', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(407, 'campaign_section_trash', 'admin', 'Campaign Sections', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(408, 'campaign_section_restore', 'admin', 'Campaign Sections', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(409, 'campaign_section_force_delete', 'admin', 'Campaign Sections', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(410, 'campaign_setting_view', 'admin', 'Campaign Settings', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(411, 'campaign_setting_update', 'admin', 'Campaign Settings', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(412, 'profile_view', 'web', 'User Panel', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(413, 'profile_update', 'web', 'User Panel', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(414, 'password_update', 'web', 'User Panel', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(415, 'enquiry_create', 'web', 'User Panel', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(416, 'enquiry_own_list', 'web', 'User Panel', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(417, 'enquiry_own_view', 'web', 'User Panel', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(418, 'enquiry_own_attachment_upload', 'web', 'User Panel', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(419, 'enquiry_own_attachment_download', 'web', 'User Panel', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `author_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(190) NOT NULL,
  `slug` varchar(190) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `body` longtext NOT NULL,
  `status` varchar(30) NOT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `reading_minutes` int(10) UNSIGNED DEFAULT NULL,
  `allow_comments` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `author_id`, `category_id`, `title`, `slug`, `excerpt`, `body`, `status`, `published_at`, `reading_minutes`, `allow_comments`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'Painting & Plastering Planning Basics', 'welcome-to-our-blog', 'Start planning painting or plastering with a clear scope, room measurements, surface-condition photos, suitable materials and an agreed written quotation.', '<p>A home painting or plastering project is easier to plan when the key decisions are made before work starts. You do not need to know every technical detail, but you should be able to explain what you want to improve and which surfaces are involved.</p><h2>Describe the project clearly</h2><p>List the rooms, walls and ceilings involved. Take photographs of peeling paint, cracks, uneven plaster, stains or other areas of concern. Where possible, provide measurements and note whether the property is occupied.</p><p>Explain the finish you want, including colour preferences and whether cleaning, durability or low odour matters for the room. A site inspection may still be needed before the final scope can be confirmed.</p><h2>Compare preparation and materials</h2><p>Ask what surface preparation is included, whether problem areas need assessment, and what primer or finishing system the contractor proposes. The visible colour is only one part of the specification.</p><p>Review sample colours under the room’s lighting and keep the exact product and finish references. Do not assume that all existing coatings or damaged surfaces can be treated in the same way.</p><h2>Confirm the quotation and handover</h2><p>A written quotation should state what is included and excluded, the planned programme and how variations will be approved. Check site protection, waste removal, cleaning and payment arrangements.</p><p>Before handover, inspect the agreed work together and record outstanding touch-ups. Keep the final scope and colour schedule for future maintenance.</p><h2>Next steps</h2><p>Begin with a concise checklist and photographs, then ask the painting team to verify the actual site conditions. Use the agreed written scope to guide the job from preparation to final inspection.</p>', 'published', '2026-10-10 13:47:22', 2, 0, '2026-10-10 13:47:19', '2026-10-14 06:46:10', NULL),
(2, 1, 2, 'Preparing a Room for Painting', 'painting-guides-starter', 'Prepare your room before painters arrive: clear access, protect furniture and floors, check wall damage, and agree on the work scope, materials and cleanup.', '<p>A well-prepared room makes it easier to inspect surfaces, plan the work and protect belongings during painting. Preparation is more than moving furniture: it includes deciding which areas will be painted, recording existing defects and discussing how the room will be used while work is under way.</p><h2>Clear and protect the work area</h2><p>Remove small objects, wall decorations and valuables before work begins. Move larger furniture away from the walls where practical, and ask the painting team how they will cover items that must remain in the room.</p><p>Discuss floor protection, masking around switches and fittings, and safe access to windows, doors and corners. Confirm who will remove or reinstall fixtures so nothing is damaged or accidentally discarded.</p><h2>Inspect walls and ceilings</h2><p>Look for loose paint, small cracks, stains, uneven patches or areas that feel damp. Take clear photographs and tell the team where each issue occurs instead of assuming a new coat of paint will conceal it.</p><p>Some surfaces need cleaning, filling, sanding or a suitable primer before finishing coats. Moisture problems should be investigated before painting because covering the visible mark does not remove its underlying cause.</p><h2>Agree on the painting sequence</h2><p>List the rooms, surfaces, doors and ceilings included in the quotation. Ask about paint type, sheen, number of coats, colour confirmation and how unexpected repairs will be priced.</p><p>Plan room access, ventilation and the movement of occupants or pets. Keep walkways clear, and follow the product manufacturer’s recommendations for drying and reoccupation.</p><h2>Next steps</h2><p>Use a written room-by-room checklist before work starts. Review the final scope and any changes with the contractor, then inspect the completed surfaces together before removing protective coverings.</p>', 'published', '2026-10-10 13:47:22', 2, 0, '2026-10-10 13:47:21', '2026-10-14 06:46:10', NULL),
(3, 1, 3, 'What to Check Before Wall Preparation', 'wall-preparation-starter', 'Inspect peeling paint, cracks, stains, uneven plaster and possible moisture before repainting. Learn what to photograph and which repairs to discuss first.', '<p>Paint quality depends partly on the condition of the surface beneath it. Before deciding on colours or finishes, inspect the walls in good light and note any damaged, powdery or uneven areas. Different symptoms may require different preparation methods.</p><h2>Identify visible surface problems</h2><p>Peeling paint may indicate poor adhesion, contamination or moisture. Fine hairline cracks are different from recurring or widening cracks, and deep holes need a different repair method from shallow surface marks.</p><p>Check around windows, bathrooms, air-conditioning areas and previously repaired patches. Photograph defects with an object for scale, and record whether the problem is new, recurring or spreading.</p><h2>Decide what must happen before painting</h2><p>Discuss cleaning, removal of loose material, filling, sanding and priming with the contractor. A sound existing coating may need relatively little work, while flaking or uneven areas can require more extensive treatment.</p><p>Do not simply paint over active leaks or persistent dampness. The source of moisture should be identified and addressed first, with professional assessment when the cause is unclear.</p><h2>Check the proposed materials and finish</h2><p>Ask which filler, plaster, primer and topcoat will be used, and whether they suit the existing substrate. Request sample patches where texture or colour matching is important.</p><p>Confirm what level of smoothness is included and whether old repairs may remain visible in certain lighting. A written scope avoids confusion between touch-up work and complete surface preparation.</p><h2>Next steps</h2><p>Keep photos and a room-by-room defect list with your quotation. Recheck repaired areas before the final coats so concerns can be discussed while the work remains accessible.</p>', 'published', '2026-10-10 13:47:22', 2, 0, '2026-10-10 13:47:21', '2026-10-14 06:46:10', NULL),
(4, 1, 4, 'Planning Painting Work in an HDB Flat', 'hdb-renovation-starter', 'Plan HDB flat painting with room access, furniture protection, ventilation and renovation rules in mind. Confirm timing and scope before booking contractors.', '<p>Painting an occupied HDB flat requires practical scheduling as well as a clear work scope. Family routines, stored belongings, shared access and other ongoing renovation jobs can affect the order in which rooms should be prepared and painted.</p><h2>Check access and building requirements</h2><p>Before confirming a start date, verify any current requirements relevant to your work with the appropriate building or estate authority. Ordinary painting and work involving structural changes, electrical installations or other regulated activities should not be treated as interchangeable.</p><p>Discuss delivery access, parking or loading, protection of common areas and arrangements for workers entering the flat. Make sure the contractor knows who can provide access during working hours.</p><h2>Plan around occupied rooms</h2><p>Decide whether the home will remain occupied during painting. Identify which rooms need to stay usable and where movable items can be stored while walls and ceilings are prepared.</p><p>Talk about ventilation, paint selection and manufacturer-stated drying times. Keep children and pets away from active work areas and follow appropriate safety guidance for coatings and cleaning products.</p><h2>Coordinate the work scope</h2><p>Write down the room dimensions, surfaces, existing damage, paint colours and finishes. Ask whether ceiling work, patch repairs, door frames and cleanup are part of the quoted amount.</p><p>If carpentry, plastering or other renovation is scheduled, agree on the sequence so freshly painted surfaces are not unnecessarily damaged by later work. Confirm who handles touch-ups after other trades leave.</p><h2>Next steps</h2><p>Use a shared schedule and a written quotation to track preparations, agreed materials and handover checks. Confirm any building-specific restrictions before work begins.</p>', 'published', '2026-10-10 13:47:22', 2, 0, '2026-10-10 13:47:21', '2026-10-14 06:46:10', NULL),
(5, 1, 5, 'Condo Painting Preparation Checklist', 'condo-renovation-starter', 'Before condo painting, check management approval, permitted work hours, service-lift access, delivery arrangements and protection of shared spaces.', '<p>Painting work in a condominium may involve management procedures in addition to the preparation required inside the unit. Requirements vary between developments, so homeowners and contractors should confirm the current rules rather than relying on assumptions.</p><h2>Confirm management procedures</h2><p>Ask your management office whether the planned scope needs an application, contractor registration, security arrangements or other documents. Check permitted work periods and any conditions that apply to deliveries or noisy preparation.</p><p>If more than decorative painting is proposed, such as changes to fixtures or other renovation work, confirm whether separate approvals or qualified trades are needed. Keep written responses for the contractor.</p><h2>Prepare access and protection</h2><p>Find out how materials may be brought to the unit, whether a service lift must be booked and where workers may load or unload. Discuss protecting lift interiors, corridors and the route into the property.</p><p>Inside the unit, plan furniture movement, surface coverings and access to windows, built-in cabinets and ceiling edges. Identify fragile finishes that need special masking or handling.</p><h2>Set a realistic sequence</h2><p>Review wall condition and expected preparation before selecting start and handover dates. Drying conditions, room availability and coordination with other contractors can influence the programme.</p><p>Confirm the proposed paint system, colours, cleaning responsibilities and how changes will be approved. A room-by-room checklist helps distinguish work included in the quotation from optional repairs.</p><h2>Next steps</h2><p>Keep the management requirements, access plan and approved work scope together. Confirm the handover inspection and removal of protective materials before considering the job complete.</p>', 'published', '2026-10-10 13:47:22', 2, 0, '2026-10-10 13:47:21', '2026-10-14 06:46:10', NULL),
(6, 1, 6, 'Understanding Wall Plastering', 'plastering-guides-starter', 'Understand when wall plastering may be needed, how surface defects affect preparation, and what finish, drying and quotation details to confirm.', '<p>Wall plastering and skim-coating can help prepare an uneven surface for decoration, but the right approach depends on the existing substrate and the finish you want. A visual inspection is the starting point; not every mark or crack requires the same treatment.</p><h2>Assess the substrate</h2><p>Identify loose material, uneven patches, old repairs and signs of moisture. Ask whether the existing surface is suitable for the proposed plastering system and what cleaning or removal is required first.</p><p>For recurring cracks, water damage or areas that sound hollow, request an appropriate assessment rather than immediately covering the symptoms. Surface defects can reappear if their cause remains unresolved.</p><h2>Discuss the proposed finish</h2><p>Clarify whether the work is a local patch, a full skim coat or another preparation method. Different levels of smoothness can affect labour, material selection and the visibility of imperfections under lighting.</p><p>Ask what primer or finishing coat is recommended after plastering and whether a sample area can be prepared for your approval. Confirm which walls and ceilings are included.</p><h2>Understand curing and handover</h2><p>The material manufacturer and site conditions determine appropriate drying and subsequent coating guidance. Avoid assuming that all plaster products can be painted after the same interval.</p><p>Check repaired edges, corners, surface continuity and any agreed touch-up work before final painting. Request a clear explanation of exclusions and how changes to damaged areas will be handled.</p><h2>Next steps</h2><p>Provide clear photographs and the desired finish when requesting a quotation. Agree on surface preparation, materials, drying guidance and the final inspection criteria in writing.</p>', 'published', '2026-10-10 13:47:22', 2, 0, '2026-10-10 13:47:22', '2026-10-14 06:46:10', NULL),
(7, 1, 7, 'Choosing a Suitable Paint Finish', 'paint-selection-starter', '<p>Compare finish, durability, cleaning needs and manufacturer guidance for each room.</p>', '<p>Compare finish, durability, cleaning needs and manufacturer guidance for each room.</p>\r\n<p>For a specific home, ask the team to review the site conditions and provide an agreed written scope.</p>', 'published', '2026-10-10 13:47:00', 1, 0, '2026-10-10 13:47:22', '2026-10-14 06:39:23', NULL),
(8, 1, 8, 'Questions to Ask Before Accepting a Quote', 'renovation-checklists-starter', '<p>Confirm material specifications, included preparation, scope changes and cleanup responsibilities.</p>', '<p>Confirm material specifications, included preparation, scope changes and cleanup responsibilities.</p>\r\n<p>For a specific home, ask the team to review the site conditions and provide an agreed written scope.</p>', 'published', '2026-10-10 13:47:00', 1, 0, '2026-10-10 13:47:22', '2026-10-14 06:39:06', NULL),
(9, 1, 9, 'Planning Interior Paint Colours', 'colour-planning-starter', '<p>Try colour samples in your room under daylight and evening lighting before final selection.</p>', '<p>Try colour samples in your room under daylight and evening lighting before final selection.</p>\r\n<p>For a specific home, ask the team to review the site conditions and provide an agreed written scope.</p>', 'published', '2026-10-10 13:47:00', 1, 0, '2026-10-10 13:47:22', '2026-10-14 06:38:48', NULL),
(10, 1, 10, 'Looking After Newly Painted Walls', 'surface-care-starter', '<p>Follow the paint manufacturer&rsquo;s curing and cleaning instructions before washing surfaces.</p>', '<p>Follow the paint manufacturer&rsquo;s curing and cleaning instructions before washing surfaces.</p>\r\n<p>For a specific home, ask the team to review the site conditions and provide an agreed written scope.</p>', 'published', '2026-10-10 13:47:00', 1, 0, '2026-10-10 13:47:22', '2026-10-14 06:38:32', NULL),
(11, 1, 11, 'Planning a Smooth Home Painting Schedule', 'project-planning-starter', '<p>Coordinate room access, furnishings, occupants and any other renovation works before scheduling.</p>', '<p>Coordinate room access, furnishings, occupants and any other renovation works before scheduling.</p>\r\n<p>For a specific home, ask the team to review the site conditions and provide an agreed written scope.</p>', 'published', '2026-10-10 13:47:00', 1, 0, '2026-10-10 13:47:22', '2026-10-14 06:04:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pricing_addons`
--

CREATE TABLE `pricing_addons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(190) NOT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `amount_max` decimal(12,2) DEFAULT NULL,
  `price_type` varchar(30) NOT NULL DEFAULT 'fixed',
  `unit` varchar(80) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pricing_addons`
--

INSERT INTO `pricing_addons` (`id`, `name`, `description`, `amount`, `amount_max`, `price_type`, `unit`, `is_active`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Ceiling Painting', '<p>Default pricing add-on. Configure amount before activation.</p>', NULL, NULL, 'call', 'job', 1, 0, '2026-10-10 13:47:19', '2026-10-10 13:47:22', NULL),
(2, 'Reference · Sealer (1 room)', '<p>UNVERIFIED reference range from https://budgetpainting.sg/. Review source rules and scope.</p>', 50.00, NULL, 'fixed', 'room', 0, 100, '2026-10-10 13:47:21', '2026-10-10 13:47:21', NULL),
(3, 'Reference · Sealer (whole house)', '<p>UNVERIFIED reference range from https://budgetpainting.sg/. Review source rules and scope.</p>', 150.00, 350.00, 'range', 'house', 1, 101, '2026-10-10 13:47:21', '2026-10-10 15:00:48', NULL),
(4, 'Reference · Door frame', '<p>UNVERIFIED reference range from https://budgetpainting.sg/. Review source rules and scope.</p>', 30.00, NULL, 'fixed', 'frame', 0, 102, '2026-10-10 13:47:21', '2026-10-10 13:47:21', NULL),
(5, 'Reference · Door + frame set', '<p>UNVERIFIED reference range from https://budgetpainting.sg/. Review source rules and scope.</p>', 60.00, NULL, 'fixed', 'set', 1, 103, '2026-10-10 13:47:21', '2026-10-10 15:00:35', NULL),
(6, 'Reference · Anti-mould ceiling upgrade', '<p>UNVERIFIED reference range from https://budgetpainting.sg/. Review source rules and scope.</p>', 50.00, NULL, 'fixed', 'bedroom', 0, 104, '2026-10-10 13:47:21', '2026-10-10 13:47:21', NULL),
(7, 'Reference · Balcony painting', '<p>UNVERIFIED reference range from https://budgetpainting.sg/. Review source rules and scope.</p>', 80.00, 100.00, 'range', 'balcony', 0, 105, '2026-10-10 13:47:21', '2026-10-10 13:47:21', NULL),
(8, 'Reference · Major peeling + putty', '<p>UNVERIFIED reference range from https://budgetpainting.sg/. Review source rules and scope.</p>', 50.00, 300.00, 'range', 'job', 0, 106, '2026-10-10 13:47:21', '2026-10-10 13:47:21', NULL),
(9, 'Reference · Extra colour', '<p>UNVERIFIED reference range from https://budgetpainting.sg/. Review source rules and scope.</p>', 50.00, 100.00, 'range', 'wall/room', 0, 107, '2026-10-10 13:47:21', '2026-10-10 13:47:21', NULL),
(10, 'Reference · High ceiling (over 2.8m)', '<p>UNVERIFIED reference range from https://budgetpainting.sg/. Review source rules and scope.</p>', 100.00, 400.00, 'range', 'job', 1, 108, '2026-10-10 13:47:21', '2026-10-10 15:00:24', NULL),
(11, 'Reference · Pipe painting', '<p>UNVERIFIED reference range from https://budgetpainting.sg/. Review source rules and scope.</p>', 50.00, NULL, 'fixed', 'toilet/yard', 0, 109, '2026-10-10 13:47:21', '2026-10-10 13:47:21', NULL),
(12, 'Reference · Wallpaper removal', '<p>UNVERIFIED reference range from https://budgetpainting.sg/. Review source rules and scope.</p>', 50.00, 100.00, 'range', 'wall/room', 1, 110, '2026-10-10 13:47:21', '2026-10-10 15:00:20', NULL),
(13, 'Reference · Touch-up trip', '<p>UNVERIFIED reference range from https://budgetpainting.sg/. Review source rules and scope.</p>', 150.00, 250.00, 'range', 'trip', 1, 111, '2026-10-10 13:47:21', '2026-10-10 15:00:17', NULL),
(14, 'Ceiling Touch-up', 'Optional work; inspect surfaces and approve the quotation first.', NULL, NULL, 'call', 'job', 0, 200, '2026-10-10 13:47:22', '2026-10-10 14:59:36', NULL),
(15, 'Door & Grille Painting', 'Optional work; inspect surfaces and approve the quotation first.', NULL, NULL, 'call', 'job', 0, 201, '2026-10-10 13:47:22', '2026-10-10 14:59:39', NULL),
(16, 'Feature Wall Painting', 'Optional work; inspect surfaces and approve the quotation first.', NULL, NULL, 'call', 'job', 0, 202, '2026-10-10 13:47:22', '2026-10-10 15:01:48', NULL),
(17, 'Minor Wall Surface Repair', 'Optional work; inspect surfaces and approve the quotation first.', NULL, NULL, 'call', 'job', 0, 203, '2026-10-10 13:47:22', '2026-10-10 15:01:43', NULL),
(18, 'Anti-Mould Ceiling Assessment', 'Optional work; inspect surfaces and approve the quotation first.', NULL, NULL, 'call', 'job', 0, 204, '2026-10-10 13:47:22', '2026-10-10 15:01:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pricing_items`
--

CREATE TABLE `pricing_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pricing_package_id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(190) NOT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `amount_max` decimal(12,2) DEFAULT NULL,
  `price_type` varchar(30) NOT NULL DEFAULT 'fixed',
  `unit` varchar(80) DEFAULT NULL,
  `prefix` varchar(40) DEFAULT NULL,
  `suffix` varchar(40) DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pricing_items`
--

INSERT INTO `pricing_items` (`id`, `pricing_package_id`, `label`, `amount`, `amount_max`, `price_type`, `unit`, `prefix`, `suffix`, `sort_order`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Standard Property', NULL, NULL, 'call', 'property', NULL, NULL, 0, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:22', NULL),
(2, 2, 'Per Room', 280.00, NULL, 'fixed', 'room', NULL, NULL, 0, 0, '2026-10-10 13:47:20', '2026-10-10 13:47:20', NULL),
(3, 2, '2 Room HDB', 599.00, NULL, 'fixed', 'property', NULL, NULL, 1, 0, '2026-10-10 13:47:20', '2026-10-10 13:47:20', NULL),
(4, 2, '3 Room HDB', 649.00, NULL, 'fixed', 'property', NULL, NULL, 2, 0, '2026-10-10 13:47:20', '2026-10-11 01:49:24', NULL),
(5, 2, '4 Room HDB', 699.00, NULL, 'fixed', 'property', NULL, NULL, 3, 0, '2026-10-10 13:47:20', '2026-10-10 13:47:20', NULL),
(6, 2, '5 Room HDB', 899.00, NULL, 'fixed', 'property', NULL, NULL, 4, 0, '2026-10-10 13:47:20', '2026-10-10 13:47:20', NULL),
(7, 2, 'Executive Apartment', 999.00, NULL, 'fixed', 'property', NULL, NULL, 5, 0, '2026-10-10 13:47:20', '2026-10-10 13:47:20', NULL),
(8, 2, 'Executive Maisonette', 1249.00, NULL, 'fixed', 'property', NULL, NULL, 6, 0, '2026-10-10 13:47:20', '2026-10-10 13:47:20', NULL),
(9, 3, 'Per Room', 320.00, NULL, 'fixed', 'room', NULL, NULL, 0, 1, '2026-10-10 13:47:20', '2026-10-10 14:58:40', NULL),
(10, 3, '2 Room HDB', 649.00, NULL, 'fixed', 'property', NULL, NULL, 1, 0, '2026-10-10 13:47:20', '2026-10-10 13:47:20', NULL),
(11, 3, '3 Room HDB', 699.00, NULL, 'fixed', 'property', NULL, NULL, 2, 0, '2026-10-10 13:47:20', '2026-10-11 01:49:24', NULL),
(12, 3, '4 Room HDB', 799.00, NULL, 'fixed', 'property', NULL, NULL, 3, 0, '2026-10-10 13:47:20', '2026-10-10 13:47:20', NULL),
(13, 3, '5 Room HDB', 999.00, NULL, 'fixed', 'property', NULL, NULL, 4, 0, '2026-10-10 13:47:20', '2026-10-10 13:47:20', NULL),
(14, 3, 'Executive Apartment', 1099.00, NULL, 'fixed', 'property', NULL, NULL, 5, 0, '2026-10-10 13:47:20', '2026-10-10 13:47:20', NULL),
(15, 3, 'Executive Maisonette', 1299.00, NULL, 'fixed', 'property', NULL, NULL, 6, 0, '2026-10-10 13:47:20', '2026-10-10 13:47:20', NULL),
(16, 4, 'Per Room', 400.00, NULL, 'fixed', 'room', NULL, NULL, 0, 0, '2026-10-10 13:47:20', '2026-10-10 13:47:20', NULL),
(17, 4, '2 Room HDB', 699.00, NULL, 'fixed', 'property', NULL, NULL, 1, 0, '2026-10-10 13:47:20', '2026-10-10 13:47:20', NULL),
(18, 4, '3 Room HDB', 949.00, NULL, 'fixed', 'property', NULL, NULL, 2, 0, '2026-10-10 13:47:20', '2026-10-11 01:49:24', NULL),
(19, 4, '4 Room HDB', 1099.00, NULL, 'fixed', 'property', NULL, NULL, 3, 0, '2026-10-10 13:47:20', '2026-10-10 13:47:20', NULL),
(20, 4, '5 Room HDB', 1249.00, NULL, 'fixed', 'property', NULL, NULL, 4, 0, '2026-10-10 13:47:20', '2026-10-10 13:47:20', NULL),
(21, 4, 'Executive Apartment', 1299.00, NULL, 'fixed', 'property', NULL, NULL, 5, 0, '2026-10-10 13:47:20', '2026-10-10 13:47:20', NULL),
(22, 4, 'Executive Maisonette', 1499.00, NULL, 'fixed', 'property', NULL, NULL, 6, 0, '2026-10-10 13:47:20', '2026-10-10 13:47:20', NULL),
(23, 5, 'Per Room', 450.00, NULL, 'fixed', 'room', NULL, NULL, 0, 1, '2026-10-10 13:47:20', '2026-10-10 14:58:16', NULL),
(24, 5, '2 Room HDB', 1000.00, NULL, 'fixed', 'property', NULL, NULL, 1, 0, '2026-10-10 13:47:20', '2026-10-10 13:47:20', NULL),
(25, 5, '3 Room HDB', 1200.00, NULL, 'fixed', 'property', NULL, NULL, 2, 0, '2026-10-10 13:47:20', '2026-10-11 01:49:24', NULL),
(26, 5, '4 Room HDB', 1350.00, NULL, 'fixed', 'property', NULL, NULL, 3, 0, '2026-10-10 13:47:20', '2026-10-10 13:47:20', NULL),
(27, 5, '5 Room HDB', 1450.00, NULL, 'fixed', 'property', NULL, NULL, 4, 0, '2026-10-10 13:47:20', '2026-10-10 13:47:20', NULL),
(28, 5, 'Executive Apartment', 1550.00, NULL, 'fixed', 'property', NULL, NULL, 5, 0, '2026-10-10 13:47:20', '2026-10-10 13:47:20', NULL),
(29, 5, 'Executive Maisonette', 1900.00, NULL, 'fixed', 'property', NULL, NULL, 6, 0, '2026-10-10 13:47:20', '2026-10-10 13:47:20', NULL),
(30, 6, 'Per Room', 800.00, 1100.00, 'range', 'room', NULL, NULL, 0, 1, '2026-10-10 13:47:20', '2026-10-10 14:58:28', NULL),
(31, 6, '2 Room HDB (up to 600 sqft)', 1700.00, 2100.00, 'range', 'property', NULL, NULL, 1, 0, '2026-10-10 13:47:20', '2026-10-10 13:47:20', NULL),
(32, 6, '3 Room HDB (up to 800 sqft)', 2100.00, 2800.00, 'range', 'property', NULL, NULL, 2, 0, '2026-10-10 13:47:20', '2026-10-11 01:49:24', NULL),
(33, 6, '4 Room HDB (up to 1000 sqft)', 2800.00, 3400.00, 'range', 'property', NULL, NULL, 3, 0, '2026-10-10 13:47:20', '2026-10-10 13:47:20', NULL),
(34, 6, '5 Room HDB (up to 1200 sqft)', 3400.00, 4100.00, 'range', 'property', NULL, NULL, 4, 0, '2026-10-10 13:47:21', '2026-10-10 13:47:21', NULL),
(35, 6, 'Executive Apartment (up to 1500 sqft)', 4100.00, 4700.00, 'range', 'property', NULL, NULL, 5, 0, '2026-10-10 13:47:21', '2026-10-10 13:47:21', NULL),
(36, 6, 'Executive Maisonette (up to 1500 sqft)', 4700.00, 5600.00, 'range', 'property', NULL, NULL, 6, 0, '2026-10-10 13:47:21', '2026-10-10 13:47:21', NULL),
(37, 7, 'Per Room', 300.00, 350.00, 'range', 'room', NULL, NULL, 0, 0, '2026-10-10 13:47:21', '2026-10-10 13:47:21', NULL),
(38, 7, '2 Room HDB', 599.00, NULL, 'fixed', 'property', NULL, NULL, 1, 0, '2026-10-10 13:47:21', '2026-10-10 13:47:21', NULL),
(39, 7, '3 Room HDB', 799.00, NULL, 'fixed', 'property', NULL, NULL, 2, 0, '2026-10-10 13:47:21', '2026-10-11 01:49:24', NULL),
(40, 7, '4 Room HDB', 999.00, NULL, 'fixed', 'property', NULL, NULL, 3, 0, '2026-10-10 13:47:21', '2026-10-10 13:47:21', NULL),
(41, 7, '5 Room HDB', 1299.00, NULL, 'fixed', 'property', NULL, NULL, 4, 0, '2026-10-10 13:47:21', '2026-10-10 13:47:21', NULL),
(42, 7, 'Executive Apartment', 1499.00, NULL, 'fixed', 'property', NULL, NULL, 5, 0, '2026-10-10 13:47:21', '2026-10-10 13:47:21', NULL),
(43, 7, 'Executive Maisonette', 1699.00, NULL, 'fixed', 'property', NULL, NULL, 6, 0, '2026-10-10 13:47:21', '2026-10-10 13:47:21', NULL),
(44, 8, 'Custom quotation', NULL, NULL, 'call', 'job', NULL, NULL, 0, 0, '2026-10-10 13:47:21', '2026-10-10 14:58:50', NULL),
(45, 9, 'Custom quotation', NULL, NULL, 'call', 'job', NULL, NULL, 0, 0, '2026-10-10 13:47:21', '2026-10-10 14:57:51', NULL),
(46, 10, 'Custom quotation', NULL, NULL, 'call', 'job', NULL, NULL, 0, 0, '2026-10-10 13:47:21', '2026-10-10 14:57:47', NULL),
(47, 11, 'Custom quotation', NULL, NULL, 'call', 'job', NULL, NULL, 0, 0, '2026-10-10 13:47:21', '2026-10-10 14:57:42', NULL),
(48, 12, 'Tailored quotation', NULL, NULL, 'call', 'job', NULL, NULL, 0, 0, '2026-10-10 13:47:22', '2026-10-10 14:57:39', NULL),
(49, 13, 'Standard Interior Painting', 300.00, 400.00, 'range', 'property', NULL, NULL, 0, 1, '2026-10-10 13:47:22', '2026-10-11 02:08:32', NULL),
(50, 13, 'Low-Odour Interior Painting', NULL, NULL, 'call', 'property', NULL, NULL, 1, 0, '2026-10-10 13:47:22', '2026-10-11 01:48:55', NULL),
(51, 13, 'Washable Interior Painting', NULL, NULL, 'call', 'property', NULL, NULL, 2, 0, '2026-10-10 13:47:22', '2026-10-11 01:49:24', NULL),
(52, 14, 'Standard Interior Painting', 350.00, 500.00, 'range', 'property', NULL, NULL, 0, 1, '2026-10-10 13:47:22', '2026-10-11 02:08:54', NULL),
(53, 14, 'Low-Odour Interior Painting', NULL, NULL, 'call', 'property', NULL, NULL, 1, 0, '2026-10-10 13:47:22', '2026-10-11 01:48:13', NULL),
(54, 14, 'Washable Interior Painting', NULL, NULL, 'call', 'property', NULL, NULL, 2, 0, '2026-10-10 13:47:22', '2026-10-11 01:49:24', NULL),
(55, 15, 'Standard Interior Painting', 320.00, 480.00, 'range', 'property', NULL, NULL, 0, 1, '2026-10-10 13:47:22', '2026-10-11 02:09:20', NULL),
(56, 15, 'Low-Odour Interior Painting', NULL, NULL, 'call', 'property', NULL, NULL, 1, 0, '2026-10-10 13:47:22', '2026-10-11 01:48:06', NULL),
(57, 15, 'Washable Interior Painting', NULL, NULL, 'call', 'property', NULL, NULL, 2, 0, '2026-10-10 13:47:22', '2026-10-11 01:49:24', NULL),
(58, 16, 'Standard Interior Painting', 500.00, 550.00, 'range', 'property', NULL, NULL, 0, 0, '2026-10-10 13:47:22', '2026-10-11 02:10:36', NULL),
(59, 16, 'Low-Odour Interior Painting', NULL, NULL, 'call', 'property', NULL, NULL, 1, 0, '2026-10-10 13:47:22', '2026-10-11 01:48:02', NULL),
(60, 16, 'Washable Interior Painting', NULL, NULL, 'call', 'property', NULL, NULL, 2, 0, '2026-10-10 13:47:22', '2026-10-11 01:49:24', NULL),
(61, 17, 'Standard Interior Painting', NULL, NULL, 'call', 'property', NULL, NULL, 0, 0, '2026-10-10 13:47:22', '2026-10-11 01:48:55', NULL),
(62, 17, 'Low-Odour Interior Painting', NULL, NULL, 'call', 'property', NULL, NULL, 1, 0, '2026-10-10 13:47:22', '2026-10-11 01:47:54', NULL),
(63, 17, 'Washable Interior Painting', NULL, NULL, 'call', 'property', NULL, NULL, 2, 0, '2026-10-10 13:47:22', '2026-10-11 01:49:24', NULL),
(64, 18, 'Standard Interior Painting', NULL, NULL, 'call', 'property', NULL, NULL, 0, 0, '2026-10-10 13:47:22', '2026-10-11 01:48:55', NULL),
(65, 18, 'Low-Odour Interior Painting', NULL, NULL, 'call', 'property', NULL, NULL, 1, 0, '2026-10-10 13:47:22', '2026-10-11 01:49:24', NULL),
(66, 18, 'Washable Interior Painting', NULL, NULL, 'call', 'property', NULL, NULL, 2, 0, '2026-10-10 13:47:22', '2026-10-11 01:49:24', NULL),
(67, 19, 'Standard Interior Painting', NULL, NULL, 'call', 'property', NULL, NULL, 0, 0, '2026-10-10 13:47:22', '2026-10-11 01:48:55', NULL),
(68, 19, 'Low-Odour Interior Painting', NULL, NULL, 'call', 'property', NULL, NULL, 1, 0, '2026-10-10 13:47:22', '2026-10-11 01:49:24', NULL),
(69, 19, 'Washable Interior Painting', NULL, NULL, 'call', 'property', NULL, NULL, 2, 0, '2026-10-10 13:47:22', '2026-10-11 01:49:24', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pricing_packages`
--

CREATE TABLE `pricing_packages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `service_id` bigint(20) UNSIGNED DEFAULT NULL,
  `location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(190) NOT NULL,
  `subtitle` varchar(190) DEFAULT NULL,
  `badge` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `currency` char(3) NOT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pricing_packages`
--

INSERT INTO `pricing_packages` (`id`, `service_id`, `location_id`, `name`, `subtitle`, `badge`, `description`, `currency`, `is_featured`, `is_active`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, NULL, 'Starter Painting Package', 'Default draft pricing package', NULL, '<p>Review pricing before activating this package.</p>', 'SGD', 0, 1, 0, '2026-10-10 13:47:19', '2026-10-10 13:47:22', NULL),
(2, 2, NULL, 'Reference · Basic', 'Nippon Matex White', 'UNVERIFIED', '<p>COMPETITOR REFERENCE — NOT YOUR APPROVED PRICE. Confirm before activation.</p><p>Source: https://budgetpainting.sg/</p>', 'SGD', 0, 0, 0, '2026-10-10 13:47:20', '2026-10-10 13:47:20', NULL),
(3, 2, NULL, 'Reference · Standard', 'Nippon Vinilex 5000', 'UNVERIFIED', '<p>COMPETITOR REFERENCE — NOT YOUR APPROVED PRICE. Confirm before activation.</p><p>Source: https://budgetpainting.sg/</p>', 'SGD', 0, 0, 1, '2026-10-10 13:47:20', '2026-10-10 13:47:20', NULL),
(4, 2, NULL, 'Reference · Superior', 'Nippon Odourless', 'UNVERIFIED', '<p>COMPETITOR REFERENCE — NOT YOUR APPROVED PRICE. Confirm before activation.</p><p>Source: https://budgetpainting.sg/</p>', 'SGD', 0, 0, 2, '2026-10-10 13:47:20', '2026-10-10 13:47:20', NULL),
(5, 2, NULL, 'Reference · Luxury', 'Nippon Odourless Ultra Durable', 'UNVERIFIED', '<p>COMPETITOR REFERENCE — NOT YOUR APPROVED PRICE. Confirm before activation.</p><p>Source: https://budgetpainting.sg/</p>', 'SGD', 0, 0, 3, '2026-10-10 13:47:20', '2026-10-10 13:47:20', NULL),
(6, 3, NULL, 'Reference · Wall Plastering Only', 'Published size-based ranges', 'UNVERIFIED', '<p>COMPETITOR REFERENCE — NOT YOUR APPROVED PRICE. Confirm before activation.</p><p>Source: https://budgetpainting.sg/wall-plastering/</p>', 'SGD', 0, 1, 50, '2026-10-10 13:47:20', '2026-10-11 02:14:02', NULL),
(7, 2, NULL, 'Reference · Painting on Plastering Page', 'Nippon Vinilex 5000', 'UNVERIFIED', '<p>COMPETITOR REFERENCE — NOT YOUR APPROVED PRICE. Confirm before activation.</p><p>Source: https://budgetpainting.sg/wall-plastering/</p>', 'SGD', 0, 0, 60, '2026-10-10 13:47:21', '2026-10-10 13:47:21', NULL),
(8, 6, NULL, '[Draft] Condo Painting Enquiry', 'Request a tailored quote', NULL, '<p>Placeholder package, no validated price.</p>', 'SGD', 0, 1, 200, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(9, 7, NULL, '[Draft] Landed House Painting Enquiry', 'Request a tailored quote', NULL, '<p>Placeholder package, no validated price.</p>', 'SGD', 0, 1, 201, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(10, 8, NULL, '[Draft] Office Painting Enquiry', 'Request a tailored quote', NULL, '<p>Placeholder package, no validated price.</p>', 'SGD', 0, 1, 202, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(11, 9, NULL, '[Draft] Ceiling Painting Enquiry', 'Request a tailored quote', NULL, '<p>Placeholder package, no validated price.</p>', 'SGD', 0, 1, 203, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(12, NULL, NULL, 'Request a Custom Painting Quote', 'Price confirmed after scope review', NULL, '<p>Price not published. Contact the team for a tailored quotation.</p>', 'SGD', 0, 1, 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(13, NULL, NULL, 'HDB 2-Room Flat', 'Choose a paint grade to request a tailored quote', 'Quotation options', 'No fixed price is promised until approved by the business.', 'SGD', 1, 1, 10, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(14, NULL, NULL, 'HDB 3-Room Flat', 'Choose a paint grade to request a tailored quote', 'Quotation options', 'No fixed price is promised until approved by the business.', 'SGD', 1, 1, 11, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(15, NULL, NULL, 'HDB 4-Room Flat', 'Choose a paint grade to request a tailored quote', 'Quotation options', 'No fixed price is promised until approved by the business.', 'SGD', 1, 1, 12, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(16, NULL, NULL, 'HDB 5-Room Flat', 'Choose a paint grade to request a tailored quote', 'Quotation options', 'No fixed price is promised until approved by the business.', 'SGD', 1, 1, 13, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(17, NULL, NULL, 'Executive Apartment', 'Choose a paint grade to request a tailored quote', 'Quotation options', 'No fixed price is promised until approved by the business.', 'SGD', 1, 1, 14, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(18, NULL, NULL, 'Condominium', 'Choose a paint grade to request a tailored quote', 'Quotation options', 'No fixed price is promised until approved by the business.', 'SGD', 1, 1, 15, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(19, NULL, NULL, 'Landed Property', 'Choose a paint grade to request a tailored quote', 'Quotation options', 'No fixed price is promised until approved by the business.', 'SGD', 1, 1, 16, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(20, 4, 2, 'Wall & Tile Hacking', 'Professional Wall & Tile Hacking Services in Singapore', '400', '<p>Professional wall and tile hacking services for HDB flats, condominiums, and residential renovation projects across Singapore.</p>\r\n<p>Our services cover:</p>\r\n<ul>\r\n<li>\r\n<p>Wall and floor tile hacking for kitchens, bathrooms, and living areas.</p>\r\n</li>\r\n<li>\r\n<p>Removal of existing floor skirting and selected wall finishes.</p>\r\n</li>\r\n<li>\r\n<p>Dismantling of kitchen cabinets, countertops, and bathroom fixtures where required.</p>\r\n</li>\r\n<li>\r\n<p>Removal of approved non-structural partitions.</p>\r\n</li>\r\n<li>\r\n<p>Site protection, debris removal, and disposal where included in the quotation.</p>\r\n</li>\r\n<li>\r\n<p>Preparation of affected areas for subsequent plastering, tiling, or painting.</p>\r\n</li>\r\n</ul>', 'SGD', 1, 1, 0, '2026-10-11 04:35:19', '2026-10-11 04:39:06', NULL),
(21, 4, NULL, 'Wall Tile Hacking', NULL, '350', '<p>Removal of existing kitchen and bathroom wall tiles, including debris collection.</p>', 'SGD', 1, 1, 0, '2026-10-11 04:38:45', '2026-10-11 04:39:12', NULL),
(23, 4, 3, 'Non-Structural Wall Hacking', NULL, '350', '<p>Removal of approved non-load-bearing partition walls, subject to required permits.</p>', 'SGD', 1, 1, 0, '2026-10-11 04:41:48', '2026-10-11 04:41:48', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pricing_package_addon`
--

CREATE TABLE `pricing_package_addon` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pricing_package_id` bigint(20) UNSIGNED NOT NULL,
  `pricing_addon_id` bigint(20) UNSIGNED NOT NULL,
  `override_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`override_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pricing_package_addon`
--

INSERT INTO `pricing_package_addon` (`id`, `pricing_package_id`, `pricing_addon_id`, `override_data`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, NULL, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(2, 13, 14, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(3, 13, 15, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(4, 13, 16, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(5, 13, 17, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(6, 13, 18, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(7, 14, 14, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(8, 14, 15, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(9, 14, 16, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(10, 14, 17, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(11, 14, 18, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(12, 15, 14, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(13, 15, 15, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(14, 15, 16, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(15, 15, 17, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(16, 15, 18, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(17, 16, 14, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(18, 16, 15, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(19, 16, 16, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(20, 16, 17, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(21, 16, 18, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(22, 17, 14, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(23, 17, 15, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(24, 17, 16, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(25, 17, 17, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(26, 17, 18, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(27, 18, 14, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(28, 18, 15, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(29, 18, 16, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(30, 18, 17, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(31, 18, 18, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(32, 19, 14, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(33, 19, 15, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(34, 19, 16, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(35, 19, 17, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(36, 19, 18, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `redirects`
--

CREATE TABLE `redirects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `from_path` varchar(500) NOT NULL,
  `to_url` varchar(2048) NOT NULL,
  `status_code` smallint(5) UNSIGNED NOT NULL DEFAULT 301,
  `hits` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_hit_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `redirects`
--

INSERT INTO `redirects` (`id`, `from_path`, `to_url`, `status_code`, `hits`, `is_active`, `last_hit_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '/old-home', '/', 301, 0, 0, NULL, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(2, '/draft-old-page-1', '/', 301, 0, 0, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(3, '/draft-old-page-2', '/', 301, 0, 0, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(4, '/draft-old-page-3', '/', 301, 0, 0, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(5, '/draft-old-page-4', '/', 301, 0, 0, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(6, '/draft-old-page-5', '/', 301, 0, 0, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(7, '/draft-old-page-6', '/', 301, 0, 0, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(8, '/draft-old-page-7', '/', 301, 0, 0, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(9, '/draft-old-page-8', '/', 301, 0, 0, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(10, '/draft-old-page-9', '/', 301, 0, 0, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(11, '/draft-old-page-10', '/', 301, 0, 0, NULL, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'admin', 'admin', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL),
(2, 'user', 'web', '2026-10-10 13:47:18', '2026-10-10 13:47:18', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(33, 1),
(34, 1),
(35, 1),
(36, 1),
(37, 1),
(38, 1),
(39, 1),
(40, 1),
(41, 1),
(42, 1),
(43, 1),
(44, 1),
(45, 1),
(46, 1),
(47, 1),
(48, 1),
(49, 1),
(50, 1),
(51, 1),
(52, 1),
(53, 1),
(54, 1),
(55, 1),
(56, 1),
(57, 1),
(58, 1),
(59, 1),
(60, 1),
(61, 1),
(62, 1),
(63, 1),
(64, 1),
(65, 1),
(66, 1),
(67, 1),
(68, 1),
(69, 1),
(70, 1),
(71, 1),
(72, 1),
(73, 1),
(74, 1),
(75, 1),
(76, 1),
(77, 1),
(78, 1),
(79, 1),
(80, 1),
(81, 1),
(82, 1),
(83, 1),
(84, 1),
(85, 1),
(86, 1),
(87, 1),
(88, 1),
(89, 1),
(90, 1),
(91, 1),
(92, 1),
(93, 1),
(94, 1),
(95, 1),
(96, 1),
(97, 1),
(98, 1),
(99, 1),
(100, 1),
(101, 1),
(102, 1),
(103, 1),
(104, 1),
(105, 1),
(106, 1),
(107, 1),
(108, 1),
(109, 1),
(110, 1),
(111, 1),
(112, 1),
(113, 1),
(114, 1),
(115, 1),
(116, 1),
(117, 1),
(118, 1),
(119, 1),
(120, 1),
(121, 1),
(122, 1),
(123, 1),
(124, 1),
(125, 1),
(126, 1),
(127, 1),
(128, 1),
(129, 1),
(130, 1),
(131, 1),
(132, 1),
(133, 1),
(134, 1),
(135, 1),
(136, 1),
(137, 1),
(138, 1),
(139, 1),
(140, 1),
(141, 1),
(142, 1),
(143, 1),
(144, 1),
(145, 1),
(146, 1),
(147, 1),
(148, 1),
(149, 1),
(150, 1),
(151, 1),
(152, 1),
(153, 1),
(154, 1),
(155, 1),
(156, 1),
(157, 1),
(158, 1),
(159, 1),
(160, 1),
(161, 1),
(162, 1),
(163, 1),
(164, 1),
(165, 1),
(166, 1),
(167, 1),
(168, 1),
(169, 1),
(170, 1),
(171, 1),
(172, 1),
(173, 1),
(174, 1),
(175, 1),
(176, 1),
(177, 1),
(178, 1),
(179, 1),
(180, 1),
(181, 1),
(182, 1),
(183, 1),
(184, 1),
(185, 1),
(186, 1),
(187, 1),
(188, 1),
(189, 1),
(190, 1),
(191, 1),
(192, 1),
(193, 1),
(194, 1),
(195, 1),
(196, 1),
(197, 1),
(198, 1),
(199, 1),
(200, 1),
(201, 1),
(202, 1),
(203, 1),
(204, 1),
(205, 1),
(206, 1),
(207, 1),
(208, 1),
(209, 1),
(210, 1),
(211, 1),
(212, 1),
(213, 1),
(214, 1),
(215, 1),
(216, 1),
(217, 1),
(218, 1),
(219, 1),
(220, 1),
(221, 1),
(222, 1),
(223, 1),
(224, 1),
(225, 1),
(226, 1),
(227, 1),
(228, 1),
(229, 1),
(230, 1),
(231, 1),
(232, 1),
(233, 1),
(234, 1),
(235, 1),
(236, 1),
(237, 1),
(238, 1),
(239, 1),
(240, 1),
(241, 1),
(242, 1),
(243, 1),
(244, 1),
(245, 1),
(246, 1),
(247, 1),
(248, 1),
(249, 1),
(250, 1),
(251, 1),
(252, 1),
(253, 1),
(254, 1),
(255, 1),
(256, 1),
(257, 1),
(258, 1),
(259, 1),
(260, 1),
(261, 1),
(262, 1),
(263, 1),
(264, 1),
(265, 1),
(266, 1),
(267, 1),
(268, 1),
(269, 1),
(270, 1),
(271, 1),
(272, 1),
(273, 1),
(274, 1),
(275, 1),
(276, 1),
(277, 1),
(278, 1),
(279, 1),
(280, 1),
(281, 1),
(282, 1),
(283, 1),
(284, 1),
(285, 1),
(286, 1),
(287, 1),
(288, 1),
(289, 1),
(290, 1),
(291, 1),
(292, 1),
(293, 1),
(294, 1),
(295, 1),
(296, 1),
(297, 1),
(298, 1),
(299, 1),
(300, 1),
(301, 1),
(302, 1),
(303, 1),
(304, 1),
(305, 1),
(306, 1),
(307, 1),
(308, 1),
(309, 1),
(310, 1),
(311, 1),
(312, 1),
(313, 1),
(314, 1),
(315, 1),
(316, 1),
(317, 1),
(318, 1),
(319, 1),
(320, 1),
(321, 1),
(322, 1),
(323, 1),
(324, 1),
(325, 1),
(326, 1),
(327, 1),
(328, 1),
(329, 1),
(330, 1),
(331, 1),
(332, 1),
(333, 1),
(334, 1),
(335, 1),
(336, 1),
(337, 1),
(338, 1),
(339, 1),
(340, 1),
(341, 1),
(342, 1),
(343, 1),
(344, 1),
(345, 1),
(346, 1),
(347, 1),
(348, 1),
(349, 1),
(350, 1),
(351, 1),
(352, 1),
(353, 1),
(354, 1),
(355, 1),
(356, 1),
(357, 1),
(358, 1),
(359, 1),
(360, 1),
(361, 1),
(362, 1),
(363, 1),
(364, 1),
(365, 1),
(366, 1),
(367, 1),
(368, 1),
(369, 1),
(370, 1),
(371, 1),
(372, 1),
(373, 1),
(374, 1),
(375, 1),
(376, 1),
(377, 1),
(378, 1),
(379, 1),
(380, 1),
(381, 1),
(382, 1),
(383, 1),
(384, 1),
(385, 1),
(386, 1),
(387, 1),
(388, 1),
(389, 1),
(390, 1),
(391, 1),
(392, 1),
(393, 1),
(394, 1),
(395, 1),
(396, 1),
(397, 1),
(398, 1),
(399, 1),
(400, 1),
(401, 1),
(402, 1),
(403, 1),
(404, 1),
(405, 1),
(406, 1),
(407, 1),
(408, 1),
(409, 1),
(410, 1),
(411, 1),
(412, 2),
(413, 2),
(414, 2),
(415, 2),
(416, 2),
(417, 2),
(418, 2),
(419, 2);

-- --------------------------------------------------------

--
-- Table structure for table `section_definitions`
--

CREATE TABLE `section_definitions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(100) NOT NULL,
  `name` varchar(190) NOT NULL,
  `description` text DEFAULT NULL,
  `schema_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`schema_json`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `section_definitions`
--

INSERT INTO `section_definitions` (`id`, `key`, `name`, `description`, `schema_json`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'hero', 'Hero', 'Default Hero section definition.', '[]', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(2, 'rich_text', 'Rich Text', 'Default Rich Text section definition.', '[]', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(3, 'guarantee', 'Guarantee', 'Default Guarantee section definition.', '[]', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(4, 'benefit_grid', 'Benefit Grid', 'Default Benefit Grid section definition.', '[]', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(5, 'service_carousel', 'Service Carousel', 'Default Service Carousel section definition.', '[]', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(6, 'pricing', 'Pricing', 'Default Pricing section definition.', '[]', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(7, 'gallery', 'Gallery', 'Default Gallery section definition.', '[]', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(8, 'before_after', 'Before / After', 'Default Before / After section definition.', '[]', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(9, 'video_gallery', 'Video Gallery', 'Default Video Gallery section definition.', '[]', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(10, 'testimonials', 'Testimonials', 'Default Testimonials section definition.', '[]', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(11, 'whatsapp_reviews', 'WhatsApp Reviews', 'Default WhatsApp Reviews section definition.', '[]', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(12, 'paint_calculator', 'Paint Calculator', 'Default Paint Calculator section definition.', '[]', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(13, 'faq', 'FAQ', 'Default FAQ section definition.', '[]', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(14, 'cta', 'CTA', 'Default CTA section definition.', '[]', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(15, 'contact_form', 'Contact Form', 'Default Contact Form section definition.', '[]', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(16, 'safe_embed', 'Safe Embed', 'Default Safe Embed section definition.', '[]', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(17, 'spacer', 'Spacer', 'Default Spacer section definition.', '[]', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `section_media`
--

CREATE TABLE `section_media` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `page_section_id` bigint(20) UNSIGNED NOT NULL,
  `media_id` bigint(20) UNSIGNED NOT NULL,
  `role` varchar(80) NOT NULL,
  `caption_override` text DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `section_media`
--

INSERT INTO `section_media` (`id`, `page_section_id`, `media_id`, `role`, `caption_override`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 13, 4, 'primary', NULL, 10, '2026-10-10 14:33:59', '2026-10-10 14:33:59', NULL),
(3, 13, 5, 'primary', NULL, 20, '2026-10-10 14:34:16', '2026-10-10 14:34:16', NULL),
(4, 13, 8, 'primary', NULL, 30, '2026-10-10 14:34:44', '2026-10-10 14:34:44', NULL),
(5, 13, 7, 'primary', NULL, 40, '2026-10-10 14:35:59', '2026-10-10 14:35:59', NULL),
(6, 13, 6, 'primary', NULL, 50, '2026-10-10 14:36:21', '2026-10-10 14:36:21', NULL),
(7, 22, 9, 'primary', NULL, 10, '2026-10-10 14:43:22', '2026-10-10 14:43:22', NULL),
(8, 19, 22, 'primary', NULL, 10, '2026-10-10 15:16:56', '2026-10-10 15:16:56', NULL),
(9, 19, 23, 'primary', NULL, 20, '2026-10-10 15:17:41', '2026-10-10 15:17:41', NULL),
(10, 19, 24, 'primary', NULL, 30, '2026-10-10 15:18:18', '2026-10-10 15:18:18', NULL),
(11, 19, 25, 'primary', NULL, 40, '2026-10-10 15:18:37', '2026-10-10 15:18:37', NULL),
(12, 19, 7, 'primary', NULL, 50, '2026-10-10 15:19:03', '2026-10-10 15:19:03', NULL),
(13, 19, 14, 'primary', NULL, 60, '2026-10-10 15:19:14', '2026-10-10 15:19:14', NULL),
(14, 19, 20, 'primary', NULL, 70, '2026-10-10 15:19:31', '2026-10-10 15:19:31', NULL),
(15, 19, 21, 'primary', NULL, 80, '2026-10-10 15:20:05', '2026-10-10 15:20:05', NULL),
(16, 3, 32, 'primary', NULL, 10, '2026-10-11 03:11:06', '2026-10-11 03:11:06', NULL),
(17, 4, 38, 'primary', NULL, 10, '2026-10-14 03:06:20', '2026-10-14 03:06:20', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `seo_metas`
--

CREATE TABLE `seo_metas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `seoable_type` varchar(255) NOT NULL,
  `seoable_id` bigint(20) UNSIGNED NOT NULL,
  `meta_title` varchar(190) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `canonical_url` varchar(2048) DEFAULT NULL,
  `robots` varchar(100) NOT NULL DEFAULT 'index,follow',
  `og_title` varchar(190) DEFAULT NULL,
  `og_description` text DEFAULT NULL,
  `schema_overrides` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`schema_overrides`)),
  `include_in_sitemap` tinyint(1) NOT NULL DEFAULT 1,
  `sitemap_priority` decimal(2,1) DEFAULT NULL,
  `sitemap_changefreq` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `seo_metas`
--

INSERT INTO `seo_metas` (`id`, `seoable_type`, `seoable_id`, `meta_title`, `meta_description`, `canonical_url`, `robots`, `og_title`, `og_description`, `schema_overrides`, `include_in_sitemap`, `sitemap_priority`, `sitemap_changefreq`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'App\\Models\\Page', 1, 'Professional Painting Services', 'Configure the final homepage SEO description in Admin.', NULL, 'index,follow', 'Professional Painting Services', 'Configure the final social description in Admin.', NULL, 1, 1.0, 'weekly', '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(2, 'App\\Models\\Page', 2, 'Wall Plastering', 'Learn about wall preparation, plastering and the information needed to request a quotation.', NULL, 'index,follow', NULL, NULL, NULL, 1, NULL, 'monthly', '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(3, 'App\\Models\\Page', 3, 'Wall & Tile Hacking', 'Discuss the scope of planned removal work, site access, debris management and applicable approvals.', NULL, 'index,follow', NULL, NULL, NULL, 1, NULL, 'monthly', '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(4, 'App\\Models\\Page', 4, 'False Ceiling', 'Discuss ceiling layouts, materials, access panels and coordination with licensed electrical works where required.', NULL, 'index,follow', NULL, NULL, NULL, 1, NULL, 'monthly', '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(5, 'App\\Models\\Page', 5, 'Painting Prices', 'Review published quotation options, or ask for a tailored price based on your work scope.', NULL, 'index,follow', NULL, NULL, NULL, 1, NULL, 'monthly', '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(6, 'App\\Models\\Page', 6, 'Contact', 'Send an enquiry using the quotation form or the current contact channels listed on this website.', NULL, 'index,follow', NULL, NULL, NULL, 1, NULL, 'monthly', '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(7, 'App\\Models\\Page', 7, 'Painting & Renovation Guides', 'Practical articles about planning, preparation and choosing materials for interior painting projects.', NULL, 'index,follow', NULL, NULL, NULL, 1, NULL, 'monthly', '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(8, 'App\\Models\\Page', 8, 'Painting Cost Calculator', 'Explore available guide prices. A final price depends on the actual job scope.', NULL, 'index,follow', NULL, NULL, NULL, 1, NULL, 'monthly', '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(9, 'App\\Models\\Page', 9, 'About Us', 'Learn about the services listed on this website and request details of the work process.', NULL, 'index,follow', NULL, NULL, NULL, 1, NULL, 'monthly', '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(10, 'App\\Models\\Page', 10, 'Project Gallery', 'View approved project images and before-and-after photos when they are available.', NULL, 'index,follow', NULL, NULL, NULL, 1, NULL, 'monthly', '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(11, 'App\\Models\\Page', 11, 'Service Areas', 'Explore Singapore locations and enquire about availability for your property.', NULL, 'index,follow', NULL, NULL, NULL, 1, NULL, 'monthly', '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(12, 'App\\Models\\Page', 12, 'Request a Quote', 'Tell us your property type, location and proposed scope of work for a quotation.', NULL, 'index,follow', NULL, NULL, NULL, 1, NULL, 'monthly', '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(190) NOT NULL,
  `slug` varchar(190) NOT NULL,
  `summary` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `hero_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`hero_config`)),
  `status` varchar(30) NOT NULL DEFAULT 'draft',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `slug`, `summary`, `content`, `icon`, `hero_config`, `status`, `is_featured`, `sort_order`, `published_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'HDB Painting', 'hdb-painting', '<p>Planning for painting rooms, ceilings and common surfaces in HDB flats.</p>', '<p>Planning for painting rooms, ceilings and common surfaces in HDB flats.</p>\r\n<p>Share photographs, measurements and existing surface conditions when requesting an assessment. Availability and scope are confirmed by the team.</p>', 'paint-roller', '{\"heading\":null,\"overlay\":null,\"focal_position\":\"center center\",\"cta\":{\"label\":null,\"url\":null},\"slider_mode\":false}', 'published', 1, 0, '2026-10-10 13:47:00', '2026-10-10 13:47:19', '2026-10-14 06:42:05', NULL),
(2, 'House Painting', 'house-painting', '<p>Discuss the rooms, surfaces, preparation and finishes in your home painting project.</p>', '<p>Discuss the rooms, surfaces, preparation and finishes in your home painting project.</p>\r\n<p>Share photographs, measurements and existing surface conditions when requesting an assessment. Availability and scope are confirmed by the team.</p>', 'paint-roller', '{\"heading\":null,\"overlay\":null,\"focal_position\":\"center center\",\"cta\":{\"label\":null,\"url\":null},\"slider_mode\":false}', 'published', 1, 0, '2026-10-10 13:47:00', '2026-10-10 13:47:20', '2026-10-11 03:56:36', NULL),
(3, 'Wall Plastering', 'wall-plastering', '<p>Plastering, levelling and preparation for suitable wall surfaces.</p>', '<p>Plastering, levelling and preparation for suitable wall surfaces.</p>\r\n<p>Share photographs, measurements and existing surface conditions when requesting an assessment. Availability and scope are confirmed by the team.</p>', 'paint-roller', '{\"heading\":null,\"overlay\":null,\"focal_position\":\"center center\",\"cta\":{\"label\":null,\"url\":null},\"slider_mode\":false}', 'published', 1, 1, '2026-10-10 13:47:00', '2026-10-10 13:47:20', '2026-10-11 03:57:28', NULL),
(4, 'Wall & Tile Hacking', 'wall-hacking', '<p>Discuss selected wall and tile removal, protection, approvals and debris handling.</p>', '<p>Discuss selected wall and tile removal, protection, approvals and debris handling.</p>\r\n<p>Share photographs, measurements and existing surface conditions when requesting an assessment. Availability and scope are confirmed by the team.</p>', 'paint-roller', '{\"heading\":null,\"overlay\":null,\"focal_position\":\"center center\",\"cta\":{\"label\":null,\"url\":null},\"slider_mode\":false}', 'published', 1, 2, '2026-10-10 13:47:00', '2026-10-10 13:47:20', '2026-10-11 03:58:07', NULL),
(5, 'False Ceiling', 'false-ceiling', '<p>Discuss materials, lighting coordination and access requirements for ceiling works.</p>', '<p>Discuss materials, lighting coordination and access requirements for ceiling works.</p>\r\n<p>Share photographs, measurements and existing surface conditions when requesting an assessment. Availability and scope are confirmed by the team.</p>', 'paint-roller', '{\"heading\":null,\"overlay\":null,\"focal_position\":\"center center\",\"cta\":{\"label\":null,\"url\":null},\"slider_mode\":false}', 'published', 1, 3, '2026-10-10 13:47:00', '2026-10-10 13:47:20', '2026-10-14 06:43:40', NULL),
(6, 'Condo Painting', 'condo-painting', 'Painting planning for condominium interiors and shared-access restrictions.', '<p>Painting planning for condominium interiors and shared-access restrictions.</p><p>Share photographs, measurements and existing surface conditions when requesting an assessment. Availability and scope are confirmed by the team.</p>', 'paint-roller', '[]', 'published', 0, 100, '2026-10-10 13:47:22', '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(7, 'Landed House Painting', 'landed-house-painting', 'Plan preparation and finishing work for landed property interiors and exteriors.', '<p>Plan preparation and finishing work for landed property interiors and exteriors.</p><p>Share photographs, measurements and existing surface conditions when requesting an assessment. Availability and scope are confirmed by the team.</p>', 'paint-roller', '[]', 'published', 0, 100, '2026-10-10 13:47:22', '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(8, 'Office Painting', 'office-painting', 'Discuss commercial access, timing, surface protection and work scope.', '<p>Discuss commercial access, timing, surface protection and work scope.</p><p>Share photographs, measurements and existing surface conditions when requesting an assessment. Availability and scope are confirmed by the team.</p>', 'paint-roller', '[]', 'published', 0, 100, '2026-10-10 13:47:22', '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(9, 'Ceiling Painting', 'ceiling-painting', 'Consider ceiling condition, appropriate coatings, access and preparation.', '<p>Consider ceiling condition, appropriate coatings, access and preparation.</p><p>Share photographs, measurements and existing surface conditions when requesting an assessment. Availability and scope are confirmed by the team.</p>', 'paint-roller', '[]', 'published', 0, 100, '2026-10-10 13:47:22', '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(10, 'Wall Repair', 'wall-repair', 'Assess cracks, holes and surface defects before choosing a repair method.', '<p>Assess cracks, holes and surface defects before choosing a repair method.</p><p>Share photographs, measurements and existing surface conditions when requesting an assessment. Availability and scope are confirmed by the team.</p>', 'paint-roller', '[]', 'published', 0, 100, '2026-10-10 13:47:22', '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(11, 'Waterproofing Consultation', 'waterproofing-consultation', 'Discuss moisture symptoms and suitable specialist assessment before repairs.', '<p>Discuss moisture symptoms and suitable specialist assessment before repairs.</p><p>Share photographs, measurements and existing surface conditions when requesting an assessment. Availability and scope are confirmed by the team.</p>', 'paint-roller', '[]', 'published', 0, 100, '2026-10-10 13:47:22', '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(12, 'Door Painting', 'door-painting', 'Compare preparation, coating and finishing requirements for doors and frames.', '<p>Compare preparation, coating and finishing requirements for doors and frames.</p><p>Share photographs, measurements and existing surface conditions when requesting an assessment. Availability and scope are confirmed by the team.</p>', 'paint-roller', '[]', 'published', 0, 100, '2026-10-10 13:47:22', '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(13, 'Commercial Painting', 'commercial-painting', 'Discuss work stages, access and suitable coatings for commercial premises.', '<p>Discuss work stages, access and suitable coatings for commercial premises.</p><p>Share photographs, measurements and existing surface conditions when requesting an assessment. Availability and scope are confirmed by the team.</p>', 'paint-roller', '[]', 'published', 0, 100, '2026-10-10 13:47:22', '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `service_features`
--

CREATE TABLE `service_features` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `service_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(190) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_features`
--

INSERT INTO `service_features` (`id`, `service_id`, `title`, `description`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Professional workmanship', 'Default feature. Edit this text before publishing the service.', 'check-circle', 0, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(2, 1, 'Surface assessment', 'Confirm whether this preparation or finishing step is included in the agreed work scope.', 'check-circle', 1, 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(3, 1, 'Work-area protection', 'Confirm whether this preparation or finishing step is included in the agreed work scope.', 'check-circle', 2, 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(4, 1, 'Wall cleaning', 'Confirm whether this preparation or finishing step is included in the agreed work scope.', 'check-circle', 3, 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(5, 1, 'Surface preparation', 'Confirm whether this preparation or finishing step is included in the agreed work scope.', 'check-circle', 4, 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(6, 1, 'Primer evaluation', 'Confirm whether this preparation or finishing step is included in the agreed work scope.', 'check-circle', 5, 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(7, 1, 'Paint selection', 'Confirm whether this preparation or finishing step is included in the agreed work scope.', 'check-circle', 6, 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(8, 1, 'Coat application', 'Confirm whether this preparation or finishing step is included in the agreed work scope.', 'check-circle', 7, 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(9, 1, 'Edge detailing', 'Confirm whether this preparation or finishing step is included in the agreed work scope.', 'check-circle', 8, 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(10, 1, 'Final inspection', 'Confirm whether this preparation or finishing step is included in the agreed work scope.', 'check-circle', 9, 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(11, 1, 'Post-work cleanup', 'Confirm whether this preparation or finishing step is included in the agreed work scope.', 'check-circle', 10, 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL);

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
('drAdmLmHBFU4bVjJXOXyRUZ09S08u4q6YWrltaZW', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJXVTJHYVA3U2trV3dtYVV5Sm5FaWFXYXpqRzNqTzd0TWVlTW1Kek44IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9hZG1pblwvcG9zdHMiLCJyb3V0ZSI6ImFkbWluLnBvc3RzLmluZGV4In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwibG9naW5fYWRtaW5fNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MX0=', 1791960606);

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `group_name` varchar(80) NOT NULL,
  `setting_key` varchar(150) NOT NULL,
  `setting_value` longtext DEFAULT NULL,
  `value_type` varchar(30) NOT NULL DEFAULT 'string',
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `group_name`, `setting_key`, `setting_value`, `value_type`, `is_public`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'branding', 'site.name', 'Painting Services', 'string', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(2, 'localization', 'site.timezone', 'Asia/Singapore', 'string', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(3, 'localization', 'site.currency', 'SGD', 'string', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(4, 'header', 'header.sticky', '1', 'boolean', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(5, 'footer', 'footer.description', 'Professional painting and home-service solutions.', 'string', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(6, 'widget', 'widget.position', 'bottom-right', 'string', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(7, 'widget', 'widget.scroll_threshold', '300', 'integer', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(8, 'registration', 'registration.enabled', '0', 'boolean', 0, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(9, 'consent', 'consent.marketing_default', '0', 'boolean', 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(10, 'header', 'header.announcement', 'Contact our team for a quotation', 'string', 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(11, 'footer', 'footer.copyright', '© 2026 Painting Services. All rights reserved.', 'string', 1, '2026-10-10 13:47:21', '2026-10-10 13:47:22', NULL),
(12, 'contact', 'contact.quote_heading', 'Request a quotation', 'string', 1, '2026-10-10 13:47:21', '2026-10-10 13:47:21', NULL),
(13, 'header', 'header.cta_label', 'Get a Free Quote', 'string', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(14, 'header', 'header.cta_url', '/#quote-form', 'string', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(15, 'header', 'header.top_contact_label', 'Contact Us', 'string', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(16, 'footer', 'footer.company_heading', 'Quick Links', 'string', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(17, 'footer', 'footer.services_heading', 'Popular Services', 'string', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(18, 'footer', 'footer.legal_heading', 'Legal', 'string', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(19, 'footer', 'footer.contact_heading', 'Contact Information', 'string', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(20, 'footer', 'footer.cta_label', 'Get a Free Quote', 'string', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(21, 'footer', 'footer.cta_url', '/#quote-form', 'string', 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(23, 'logo', 'logo.site', '<p>logo</p>', 'string', 1, '2026-10-11 02:36:09', '2026-10-11 02:36:09', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `testimonialables`
--

CREATE TABLE `testimonialables` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `testimonial_id` bigint(20) UNSIGNED NOT NULL,
  `testimonialable_type` varchar(255) NOT NULL,
  `testimonialable_id` bigint(20) UNSIGNED NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `testimonialables`
--

INSERT INTO `testimonialables` (`id`, `testimonial_id`, `testimonialable_type`, `testimonialable_id`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'App\\Models\\Service', 1, 0, '2026-10-10 13:47:19', '2026-10-10 13:47:19'),
(2, 11, 'App\\Models\\Service', 8, 0, '2026-10-10 14:52:21', '2026-10-10 14:52:21'),
(3, 11, 'App\\Models\\Location', 1, 0, '2026-10-10 14:52:21', '2026-10-10 14:52:21'),
(4, 10, 'App\\Models\\Service', 1, 0, '2026-10-10 14:53:07', '2026-10-10 14:53:07'),
(5, 10, 'App\\Models\\Location', 4, 0, '2026-10-10 14:53:07', '2026-10-10 14:53:07'),
(6, 9, 'App\\Models\\Service', 4, 0, '2026-10-10 14:53:48', '2026-10-10 14:53:48'),
(7, 9, 'App\\Models\\Location', 5, 0, '2026-10-10 14:53:48', '2026-10-10 14:53:48'),
(8, 8, 'App\\Models\\Service', 6, 0, '2026-10-10 14:54:55', '2026-10-10 14:54:55'),
(9, 8, 'App\\Models\\Location', 10, 0, '2026-10-10 14:54:55', '2026-10-10 14:54:55'),
(10, 7, 'App\\Models\\Service', 8, 0, '2026-10-10 15:11:58', '2026-10-10 15:11:58'),
(11, 7, 'App\\Models\\Location', 1, 0, '2026-10-10 15:11:58', '2026-10-10 15:11:58'),
(12, 6, 'App\\Models\\Service', 1, 0, '2026-10-10 15:13:52', '2026-10-10 15:13:52'),
(13, 6, 'App\\Models\\Location', 10, 0, '2026-10-10 15:13:52', '2026-10-10 15:13:52'),
(14, 5, 'App\\Models\\Service', 8, 0, '2026-10-10 15:14:33', '2026-10-10 15:14:33'),
(15, 5, 'App\\Models\\Location', 3, 0, '2026-10-10 15:14:33', '2026-10-10 15:14:33'),
(16, 4, 'App\\Models\\Service', 2, 0, '2026-10-10 15:14:58', '2026-10-10 15:14:58'),
(17, 4, 'App\\Models\\Location', 8, 0, '2026-10-10 15:14:58', '2026-10-10 15:14:58');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(30) NOT NULL DEFAULT 'text',
  `customer_name` varchar(150) NOT NULL,
  `customer_title` varchar(150) DEFAULT NULL,
  `rating` tinyint(3) UNSIGNED DEFAULT NULL,
  `review` text DEFAULT NULL,
  `source` varchar(50) NOT NULL DEFAULT 'direct',
  `source_url` varchar(255) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `type`, `customer_name`, `customer_title`, `rating`, `review`, `source`, `source_url`, `reviewed_at`, `is_featured`, `is_active`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'text', 'Demo Customer — Replace Me', NULL, NULL, 'Inactive demo testimonial. Replace this record with verified customer feedback.', 'direct', NULL, NULL, 0, 0, 0, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(2, 'text', '[Draft] Review Slot 01', NULL, NULL, 'UNVERIFIED EMPTY SLOT — enter a genuine customer review.', 'direct', NULL, NULL, 0, 0, 1, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(3, 'text', '[Draft] Review Slot 02', NULL, NULL, 'UNVERIFIED EMPTY SLOT — enter a genuine customer review.', 'direct', NULL, NULL, 0, 0, 2, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(4, 'whatsapp_screenshot', '[Draft] Review Slot 03', NULL, NULL, '<p>UNVERIFIED EMPTY SLOT &mdash; enter a genuine customer review.</p>', 'direct', NULL, NULL, 0, 1, 3, '2026-10-10 13:47:22', '2026-10-10 15:14:58', NULL),
(5, 'whatsapp_screenshot', '[Draft] Review Slot 04', NULL, NULL, '<p>UNVERIFIED EMPTY SLOT &mdash; enter a genuine customer review.</p>', 'direct', NULL, NULL, 0, 1, 4, '2026-10-10 13:47:22', '2026-10-10 15:14:33', NULL),
(6, 'whatsapp_screenshot', '[Draft] Review Slot 05', NULL, NULL, '<p>UNVERIFIED EMPTY SLOT &mdash; enter a genuine customer review.</p>', 'direct', NULL, NULL, 0, 1, 5, '2026-10-10 13:47:22', '2026-10-10 15:14:02', NULL),
(7, 'whatsapp_screenshot', '[Draft] Review Slot 06', NULL, 5, '<p>UNVERIFIED EMPTY SLOT &mdash; enter a genuine customer review.</p>', 'whatsapp', NULL, NULL, 0, 1, 6, '2026-10-10 13:47:22', '2026-10-10 15:11:58', NULL),
(8, 'text', 'Review 4', NULL, 5, '<p>\"Great job on the plastering service for our resale flat. The before and after look like brand new house. Good job and will recommend to our friends getting their houses!\"</p>', 'facebook', NULL, NULL, 0, 1, 7, '2026-10-10 13:47:22', '2026-10-10 14:54:55', NULL),
(9, 'text', 'Review 3', NULL, 5, '<p>\"Great job on the plastering service for our resale flat. The before and after look like brand new house. Good job and will recommend to our friends getting their houses!\"</p>', 'facebook', NULL, NULL, 0, 1, 8, '2026-10-10 13:47:22', '2026-10-10 14:53:48', NULL),
(10, 'text', 'Review 2', NULL, 5, '<p>\"Great job on the plastering service for our resale flat. The before and after look like brand new house. Good job and will recommend to our friends getting their houses!\"</p>', 'whatsapp', NULL, NULL, 0, 1, 9, '2026-10-10 13:47:22', '2026-10-10 14:53:07', NULL),
(11, 'text', 'Review 1', NULL, 5, '<p>\"Great job on the plastering service for our resale flat. The before and after look like brand new house. Good job and will recommend to our friends getting their houses!\"</p>', 'google', NULL, NULL, 0, 1, 10, '2026-10-10 13:47:22', '2026-10-10 14:52:21', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tracking_event_rules`
--

CREATE TABLE `tracking_event_rules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tracking_provider_id` bigint(20) UNSIGNED NOT NULL,
  `internal_event` varchar(100) NOT NULL,
  `provider_event` varchar(120) NOT NULL,
  `parameter_map` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parameter_map`)),
  `requires_marketing_consent` tinyint(1) NOT NULL DEFAULT 1,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tracking_event_rules`
--

INSERT INTO `tracking_event_rules` (`id`, `tracking_provider_id`, `internal_event`, `provider_event`, `parameter_map`, `requires_marketing_consent`, `is_enabled`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'page_view', 'PageView', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(2, 1, 'view_content', 'ViewContent', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(3, 1, 'view_pricing', 'ViewPricing', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(4, 1, 'contact', 'Contact', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(5, 1, 'click_whatsapp', 'ClickWhatsApp', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(6, 1, 'click_call', 'ClickCall', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(7, 1, 'submit_quote', 'SubmitQuote', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(8, 1, 'lead', 'Lead', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(9, 1, 'scroll_depth', 'ScrollDepth', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(10, 2, 'page_view', 'PageView', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(11, 2, 'view_content', 'ViewContent', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(12, 2, 'view_pricing', 'ViewPricing', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(13, 2, 'contact', 'Contact', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(14, 2, 'click_whatsapp', 'ClickWhatsApp', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(15, 2, 'click_call', 'ClickCall', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(16, 2, 'submit_quote', 'SubmitQuote', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(17, 2, 'lead', 'Lead', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(18, 2, 'scroll_depth', 'ScrollDepth', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(19, 3, 'page_view', 'page_view', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(20, 3, 'view_content', 'view_item', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(21, 3, 'view_pricing', 'view_pricing', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(22, 3, 'contact', 'contact', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(23, 3, 'click_whatsapp', 'click_whatsapp', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(24, 3, 'click_call', 'click_call', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(25, 3, 'submit_quote', 'generate_lead', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(26, 3, 'lead', 'generate_lead', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(27, 3, 'scroll_depth', 'scroll', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(28, 4, 'page_view', 'page_view', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(29, 4, 'view_content', 'view_content', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(30, 4, 'view_pricing', 'view_pricing', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(31, 4, 'contact', 'contact', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(32, 4, 'click_whatsapp', 'click_whatsapp', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(33, 4, 'click_call', 'click_call', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(34, 4, 'submit_quote', 'submit_quote', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(35, 4, 'lead', 'lead', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(36, 4, 'scroll_depth', 'scroll_depth', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(37, 5, 'page_view', 'PageView', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(38, 5, 'view_content', 'ViewContent', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(39, 5, 'view_pricing', 'Viewpricing', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(40, 5, 'contact', 'Contact', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(41, 5, 'click_whatsapp', 'Clickwhatsapp', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(42, 5, 'click_call', 'Clickcall', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(43, 5, 'submit_quote', 'SubmitForm', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(44, 5, 'lead', 'SubmitForm', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(45, 5, 'scroll_depth', 'Scrolldepth', '[]', 1, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tracking_providers`
--

CREATE TABLE `tracking_providers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `provider` varchar(30) NOT NULL,
  `public_identifier` varchar(255) DEFAULT NULL,
  `secret` varchar(2048) DEFAULT NULL,
  `config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`config`)),
  `is_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `test_mode` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tracking_providers`
--

INSERT INTO `tracking_providers` (`id`, `provider`, `public_identifier`, `secret`, `config`, `is_enabled`, `test_mode`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'meta_pixel', NULL, NULL, NULL, 0, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(2, 'meta_capi', NULL, NULL, NULL, 0, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(3, 'ga4', NULL, NULL, NULL, 0, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(4, 'gtm', NULL, NULL, NULL, 0, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL),
(5, 'tiktok', NULL, NULL, NULL, 0, 1, '2026-10-10 13:47:19', '2026-10-10 13:47:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `videos`
--

CREATE TABLE `videos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `attachable_type` varchar(255) DEFAULT NULL,
  `attachable_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(190) NOT NULL,
  `caption` text DEFAULT NULL,
  `source_type` varchar(30) NOT NULL,
  `provider` varchar(50) DEFAULT NULL,
  `provider_video_id` varchar(190) DEFAULT NULL,
  `source_url` varchar(2048) DEFAULT NULL,
  `duration_seconds` int(10) UNSIGNED DEFAULT NULL,
  `autoplay` tinyint(1) NOT NULL DEFAULT 0,
  `muted` tinyint(1) NOT NULL DEFAULT 0,
  `controls` tinyint(1) NOT NULL DEFAULT 1,
  `loop` tinyint(1) NOT NULL DEFAULT 0,
  `processing_status` varchar(30) NOT NULL DEFAULT 'ready',
  `processing_error` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `videos`
--

INSERT INTO `videos` (`id`, `attachable_type`, `attachable_id`, `title`, `caption`, `source_type`, `provider`, `provider_video_id`, `source_url`, `duration_seconds`, `autoplay`, `muted`, `controls`, `loop`, `processing_status`, `processing_error`, `sort_order`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'App\\Models\\Service', 1, 'Demo Service Video', '<p>Inactive demo video. Replace with your own video before activation.</p>', 'youtube', 'youtube', 'dvGgqlhF7u0', NULL, NULL, 1, 1, 1, 0, 'ready', NULL, 0, 1, '2026-10-10 13:47:19', '2026-10-10 14:46:15', NULL),
(2, NULL, NULL, 'Demo Video Slot 01', '<p>Upload or link a video owned/licensed by your business.</p>', 'youtube', 'youtube', 'dvGgqlhF7u0', NULL, NULL, 1, 1, 1, 0, 'ready', NULL, 1, 1, '2026-10-10 13:47:22', '2026-10-10 14:48:51', NULL),
(3, NULL, NULL, 'Demo  Video Slot 02', '<p>Upload or link a video owned/licensed by your business.</p>', 'youtube', 'youtube', 'dvGgqlhF7u0', NULL, NULL, 1, 1, 1, 0, 'ready', NULL, 2, 1, '2026-10-10 13:47:22', '2026-10-10 14:49:03', NULL),
(4, NULL, NULL, 'Demo Video Slot 03', '<p>Upload or link a video owned/licensed by your business.</p>', 'youtube', 'youtube', 'dvGgqlhF7u0', NULL, NULL, 1, 1, 1, 0, 'ready', NULL, 3, 1, '2026-10-10 13:47:22', '2026-10-10 14:49:13', NULL),
(5, NULL, NULL, 'Demo  Video Slot 04', '<p>Upload or link a video owned/licensed by your business.</p>', 'youtube', 'youtube', 'dvGgqlhF7u0', NULL, NULL, 1, 1, 1, 0, 'ready', NULL, 4, 1, '2026-10-10 13:47:22', '2026-10-10 14:49:24', NULL),
(6, NULL, NULL, '[Draft] Video Slot 05', 'Upload or link a video owned/licensed by your business.', 'youtube', 'youtube', NULL, NULL, NULL, 0, 0, 1, 0, 'pending', NULL, 5, 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(7, NULL, NULL, '[Draft] Video Slot 06', 'Upload or link a video owned/licensed by your business.', 'youtube', 'youtube', NULL, NULL, NULL, 0, 0, 1, 0, 'pending', NULL, 6, 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(8, NULL, NULL, '[Draft] Video Slot 07', 'Upload or link a video owned/licensed by your business.', 'youtube', 'youtube', NULL, NULL, NULL, 0, 0, 1, 0, 'pending', NULL, 7, 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(9, NULL, NULL, '[Draft] Video Slot 08', 'Upload or link a video owned/licensed by your business.', 'youtube', 'youtube', NULL, NULL, NULL, 0, 0, 1, 0, 'pending', NULL, 8, 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(10, NULL, NULL, '[Draft] Video Slot 09', 'Upload or link a video owned/licensed by your business.', 'youtube', 'youtube', NULL, NULL, NULL, 0, 0, 1, 0, 'pending', NULL, 9, 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL),
(11, NULL, NULL, '[Draft] Video Slot 10', 'Upload or link a video owned/licensed by your business.', 'youtube', 'youtube', NULL, NULL, NULL, 0, 0, 1, 0, 'pending', NULL, 10, 0, '2026-10-10 13:47:22', '2026-10-10 13:47:22', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admins_username_unique` (`username`),
  ADD UNIQUE KEY `admins_email_unique` (`email`),
  ADD UNIQUE KEY `admins_phone_unique` (`phone`);

--
-- Indexes for table `admin_password_reset_tokens`
--
ALTER TABLE `admin_password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_logs_actor_idx` (`actor_type`,`actor_id`),
  ADD KEY `audit_logs_auditable_idx` (`auditable_type`,`auditable_id`),
  ADD KEY `audit_logs_action_created_idx` (`action`,`created_at`),
  ADD KEY `audit_logs_created_idx` (`created_at`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `campaigns_slug_unique` (`slug`),
  ADD UNIQUE KEY `campaigns_custom_route_unique` (`custom_route`),
  ADD KEY `campaigns_created_by_foreign` (`created_by`),
  ADD KEY `campaigns_updated_by_foreign` (`updated_by`),
  ADD KEY `campaigns_public_schedule_idx` (`status`,`starts_at`,`ends_at`),
  ADD KEY `campaigns_published_at_index` (`published_at`);

--
-- Indexes for table `campaign_sections`
--
ALTER TABLE `campaign_sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `campaign_sections_campaign_enabled_order_idx` (`campaign_id`,`is_enabled`,`sort_order`),
  ADD KEY `campaign_sections_key_enabled_idx` (`section_key`,`is_enabled`);

--
-- Indexes for table `campaign_settings`
--
ALTER TABLE `campaign_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `campaign_settings_default_campaign_id_foreign` (`default_campaign_id`),
  ADD KEY `campaign_settings_fallback_page_id_foreign` (`fallback_page_id`),
  ADD KEY `campaign_settings_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`);

--
-- Indexes for table `contact_channels`
--
ALTER TABLE `contact_channels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contact_channels_type_active_order_idx` (`type`,`is_active`,`sort_order`),
  ADD KEY `contact_channels_default_idx` (`is_default`);

--
-- Indexes for table `contact_targets`
--
ALTER TABLE `contact_targets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `contact_targets_channel_target_unique` (`contact_channel_id`,`targetable_type`,`targetable_id`),
  ADD KEY `contact_targets_targetable_idx` (`targetable_type`,`targetable_id`),
  ADD KEY `contact_targets_channel_deleted_idx` (`contact_channel_id`,`deleted_at`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indexes for table `faqables`
--
ALTER TABLE `faqables`
  ADD UNIQUE KEY `faqable_unique` (`faq_id`,`faqable_type`,`faqable_id`),
  ADD KEY `faqables_target_order_index` (`faqable_type`,`faqable_id`,`sort_order`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `faqs_is_active_index` (`is_active`);

--
-- Indexes for table `galleries`
--
ALTER TABLE `galleries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `galleries_attachable_type_attachable_id_index` (`attachable_type`,`attachable_id`);

--
-- Indexes for table `gallery_items`
--
ALTER TABLE `gallery_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gallery_items_gallery_id_foreign` (`gallery_id`);

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
-- Indexes for table `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `leads_reference_unique` (`reference`),
  ADD KEY `leads_status_index` (`status`),
  ADD KEY `leads_created_at_index` (`created_at`),
  ADD KEY `leads_assigned_to_index` (`assigned_to`),
  ADD KEY `leads_user_id_index` (`user_id`),
  ADD KEY `leads_location_id_index` (`location_id`);

--
-- Indexes for table `lead_form_answers`
--
ALTER TABLE `lead_form_answers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lead_form_answers_lead_key_unique` (`lead_id`,`field_key`),
  ADD KEY `lead_form_answers_lead_form_field_id_foreign` (`lead_form_field_id`),
  ADD KEY `lead_form_answers_lead_order_idx` (`lead_id`,`sort_order`,`id`);

--
-- Indexes for table `lead_form_fields`
--
ALTER TABLE `lead_form_fields`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lead_form_fields_field_key_unique` (`field_key`),
  ADD KEY `lead_form_fields_active_order_idx` (`is_active`,`sort_order`,`id`);

--
-- Indexes for table `lead_notes`
--
ALTER TABLE `lead_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lead_notes_lead_created_idx` (`lead_id`,`created_at`),
  ADD KEY `lead_notes_author_idx` (`author_type`,`author_id`),
  ADD KEY `lead_notes_visibility_idx` (`lead_id`,`visible_to_user`);

--
-- Indexes for table `lead_services`
--
ALTER TABLE `lead_services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lead_services_lead_service_unique` (`lead_id`,`service_id`),
  ADD KEY `lead_services_service_id_foreign` (`service_id`),
  ADD KEY `lead_services_lead_deleted_idx` (`lead_id`,`deleted_at`);

--
-- Indexes for table `lead_status_histories`
--
ALTER TABLE `lead_status_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lead_status_histories_lead_created_idx` (`lead_id`,`created_at`),
  ADD KEY `lead_status_histories_actor_idx` (`changed_by_type`,`changed_by_id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `locations_slug_unique` (`slug`),
  ADD KEY `locations_status_published_at_index` (`status`,`published_at`),
  ADD KEY `locations_region_sort_order_index` (`region`,`sort_order`),
  ADD KEY `locations_region_index` (`region`),
  ADD KEY `locations_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `media_uuid_unique` (`uuid`),
  ADD KEY `media_model_type_model_id_index` (`model_type`,`model_id`),
  ADD KEY `media_order_column_index` (`order_column`),
  ADD KEY `media_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `menus_location_unique` (`location`),
  ADD KEY `menus_is_active_index` (`is_active`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `menu_items_parent_id_foreign` (`parent_id`),
  ADD KEY `menu_items_menu_id_parent_id_sort_order_index` (`menu_id`,`parent_id`,`sort_order`),
  ADD KEY `menu_items_linkable_type_linkable_id_index` (`linkable_type`,`linkable_id`),
  ADD KEY `menu_items_is_active_index` (`is_active`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pages_slug_unique` (`slug`),
  ADD KEY `pages_created_by_foreign` (`created_by`),
  ADD KEY `pages_updated_by_foreign` (`updated_by`),
  ADD KEY `pages_status_published_at_index` (`status`,`published_at`),
  ADD KEY `pages_is_homepage_index` (`is_homepage`);

--
-- Indexes for table `page_sections`
--
ALTER TABLE `page_sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `page_sections_page_id_is_active_sort_order_index` (`page_id`,`is_active`,`sort_order`),
  ADD KEY `page_sections_section_definition_id_is_active_index` (`section_definition_id`,`is_active`),
  ADD KEY `page_sections_starts_at_ends_at_index` (`starts_at`,`ends_at`),
  ADD KEY `page_sections_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `posts_slug_unique` (`slug`),
  ADD KEY `posts_status_published_at_index` (`status`,`published_at`),
  ADD KEY `posts_category_id_status_index` (`category_id`,`status`),
  ADD KEY `posts_author_id_status_index` (`author_id`,`status`);

--
-- Indexes for table `pricing_addons`
--
ALTER TABLE `pricing_addons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pricing_addons_price_type_index` (`price_type`),
  ADD KEY `pricing_addons_is_active_index` (`is_active`),
  ADD KEY `pricing_addons_sort_order_index` (`sort_order`);

--
-- Indexes for table `pricing_items`
--
ALTER TABLE `pricing_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pricing_items_package_scope_idx` (`pricing_package_id`,`is_active`,`sort_order`),
  ADD KEY `pricing_items_price_type_is_active_index` (`price_type`,`is_active`),
  ADD KEY `pricing_items_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `pricing_packages`
--
ALTER TABLE `pricing_packages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pricing_packages_location_id_foreign` (`location_id`),
  ADD KEY `pricing_packages_scope_idx` (`service_id`,`location_id`,`is_active`,`sort_order`),
  ADD KEY `pricing_packages_is_featured_is_active_index` (`is_featured`,`is_active`),
  ADD KEY `pricing_packages_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `pricing_package_addon`
--
ALTER TABLE `pricing_package_addon`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pricing_package_addon_unique` (`pricing_package_id`,`pricing_addon_id`),
  ADD KEY `pricing_package_addon_package_idx` (`pricing_package_id`,`deleted_at`),
  ADD KEY `pricing_package_addon_addon_idx` (`pricing_addon_id`,`deleted_at`);

--
-- Indexes for table `redirects`
--
ALTER TABLE `redirects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `redirects_from_path_unique` (`from_path`),
  ADD KEY `redirects_is_active_from_path_index` (`is_active`,`from_path`),
  ADD KEY `redirects_last_hit_at_index` (`last_hit_at`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `section_definitions`
--
ALTER TABLE `section_definitions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `section_definitions_key_unique` (`key`),
  ADD KEY `section_definitions_is_active_index` (`is_active`),
  ADD KEY `section_definitions_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `section_media`
--
ALTER TABLE `section_media`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `section_media_unique_assignment` (`page_section_id`,`media_id`,`role`),
  ADD KEY `section_media_media_id_foreign` (`media_id`),
  ADD KEY `section_media_render_order_index` (`page_section_id`,`role`,`sort_order`),
  ADD KEY `section_media_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `seo_metas`
--
ALTER TABLE `seo_metas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `seo_metas_owner_unique` (`seoable_type`,`seoable_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `services_slug_unique` (`slug`),
  ADD KEY `services_status_published_at_index` (`status`,`published_at`),
  ADD KEY `services_is_featured_sort_order_index` (`is_featured`,`sort_order`),
  ADD KEY `services_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `service_features`
--
ALTER TABLE `service_features`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_features_service_id_is_active_sort_order_index` (`service_id`,`is_active`,`sort_order`),
  ADD KEY `service_features_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `site_settings_setting_key_unique` (`setting_key`),
  ADD KEY `site_settings_group_name_index` (`group_name`),
  ADD KEY `site_settings_is_public_index` (`is_public`);

--
-- Indexes for table `testimonialables`
--
ALTER TABLE `testimonialables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `testimonialable_unique` (`testimonial_id`,`testimonialable_type`,`testimonialable_id`),
  ADD KEY `testimonialables_testimonialable_type_testimonialable_id_index` (`testimonialable_type`,`testimonialable_id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `testimonials_is_featured_index` (`is_featured`),
  ADD KEY `testimonials_is_active_index` (`is_active`);

--
-- Indexes for table `tracking_event_rules`
--
ALTER TABLE `tracking_event_rules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tracking_event_provider_internal_unique` (`tracking_provider_id`,`internal_event`),
  ADD KEY `tracking_event_rules_is_enabled_internal_event_index` (`is_enabled`,`internal_event`);

--
-- Indexes for table `tracking_providers`
--
ALTER TABLE `tracking_providers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tracking_providers_provider_unique` (`provider`),
  ADD KEY `tracking_providers_is_enabled_provider_index` (`is_enabled`,`provider`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `videos_attachable_type_attachable_id_index` (`attachable_type`,`attachable_id`),
  ADD KEY `videos_source_type_is_active_sort_order_index` (`source_type`,`is_active`,`sort_order`),
  ADD KEY `videos_processing_status_index` (`processing_status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `campaigns`
--
ALTER TABLE `campaigns`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `campaign_sections`
--
ALTER TABLE `campaign_sections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `contact_channels`
--
ALTER TABLE `contact_channels`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `contact_targets`
--
ALTER TABLE `contact_targets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `galleries`
--
ALTER TABLE `galleries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `gallery_items`
--
ALTER TABLE `gallery_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lead_form_answers`
--
ALTER TABLE `lead_form_answers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lead_form_fields`
--
ALTER TABLE `lead_form_fields`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `lead_notes`
--
ALTER TABLE `lead_notes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lead_services`
--
ALTER TABLE `lead_services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lead_status_histories`
--
ALTER TABLE `lead_status_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `page_sections`
--
ALTER TABLE `page_sections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=420;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `pricing_addons`
--
ALTER TABLE `pricing_addons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `pricing_items`
--
ALTER TABLE `pricing_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `pricing_packages`
--
ALTER TABLE `pricing_packages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `pricing_package_addon`
--
ALTER TABLE `pricing_package_addon`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `redirects`
--
ALTER TABLE `redirects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `section_definitions`
--
ALTER TABLE `section_definitions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `section_media`
--
ALTER TABLE `section_media`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `seo_metas`
--
ALTER TABLE `seo_metas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `service_features`
--
ALTER TABLE `service_features`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `testimonialables`
--
ALTER TABLE `testimonialables`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tracking_event_rules`
--
ALTER TABLE `tracking_event_rules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `tracking_providers`
--
ALTER TABLE `tracking_providers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `videos`
--
ALTER TABLE `videos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD CONSTRAINT `campaigns_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `campaigns_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `campaign_sections`
--
ALTER TABLE `campaign_sections`
  ADD CONSTRAINT `campaign_sections_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `campaign_settings`
--
ALTER TABLE `campaign_settings`
  ADD CONSTRAINT `campaign_settings_default_campaign_id_foreign` FOREIGN KEY (`default_campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `campaign_settings_fallback_page_id_foreign` FOREIGN KEY (`fallback_page_id`) REFERENCES `pages` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `campaign_settings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `contact_targets`
--
ALTER TABLE `contact_targets`
  ADD CONSTRAINT `contact_targets_contact_channel_id_foreign` FOREIGN KEY (`contact_channel_id`) REFERENCES `contact_channels` (`id`);

--
-- Constraints for table `faqables`
--
ALTER TABLE `faqables`
  ADD CONSTRAINT `faqables_faq_id_foreign` FOREIGN KEY (`faq_id`) REFERENCES `faqs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gallery_items`
--
ALTER TABLE `gallery_items`
  ADD CONSTRAINT `gallery_items_gallery_id_foreign` FOREIGN KEY (`gallery_id`) REFERENCES `galleries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leads`
--
ALTER TABLE `leads`
  ADD CONSTRAINT `leads_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leads_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  ADD CONSTRAINT `leads_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lead_form_answers`
--
ALTER TABLE `lead_form_answers`
  ADD CONSTRAINT `lead_form_answers_lead_form_field_id_foreign` FOREIGN KEY (`lead_form_field_id`) REFERENCES `lead_form_fields` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lead_form_answers_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lead_notes`
--
ALTER TABLE `lead_notes`
  ADD CONSTRAINT `lead_notes_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lead_services`
--
ALTER TABLE `lead_services`
  ADD CONSTRAINT `lead_services_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lead_services_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Constraints for table `lead_status_histories`
--
ALTER TABLE `lead_status_histories`
  ADD CONSTRAINT `lead_status_histories_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_menu_id_foreign` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `menu_items_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `menu_items` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pages`
--
ALTER TABLE `pages`
  ADD CONSTRAINT `pages_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pages_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `page_sections`
--
ALTER TABLE `page_sections`
  ADD CONSTRAINT `page_sections_page_id_foreign` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `page_sections_section_definition_id_foreign` FOREIGN KEY (`section_definition_id`) REFERENCES `section_definitions` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `admins` (`id`),
  ADD CONSTRAINT `posts_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `pricing_items`
--
ALTER TABLE `pricing_items`
  ADD CONSTRAINT `pricing_items_pricing_package_id_foreign` FOREIGN KEY (`pricing_package_id`) REFERENCES `pricing_packages` (`id`);

--
-- Constraints for table `pricing_packages`
--
ALTER TABLE `pricing_packages`
  ADD CONSTRAINT `pricing_packages_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  ADD CONSTRAINT `pricing_packages_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Constraints for table `pricing_package_addon`
--
ALTER TABLE `pricing_package_addon`
  ADD CONSTRAINT `pricing_package_addon_pricing_addon_id_foreign` FOREIGN KEY (`pricing_addon_id`) REFERENCES `pricing_addons` (`id`),
  ADD CONSTRAINT `pricing_package_addon_pricing_package_id_foreign` FOREIGN KEY (`pricing_package_id`) REFERENCES `pricing_packages` (`id`);

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `section_media`
--
ALTER TABLE `section_media`
  ADD CONSTRAINT `section_media_media_id_foreign` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `section_media_page_section_id_foreign` FOREIGN KEY (`page_section_id`) REFERENCES `page_sections` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `service_features`
--
ALTER TABLE `service_features`
  ADD CONSTRAINT `service_features_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `testimonialables`
--
ALTER TABLE `testimonialables`
  ADD CONSTRAINT `testimonialables_testimonial_id_foreign` FOREIGN KEY (`testimonial_id`) REFERENCES `testimonials` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tracking_event_rules`
--
ALTER TABLE `tracking_event_rules`
  ADD CONSTRAINT `tracking_event_rules_tracking_provider_id_foreign` FOREIGN KEY (`tracking_provider_id`) REFERENCES `tracking_providers` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
