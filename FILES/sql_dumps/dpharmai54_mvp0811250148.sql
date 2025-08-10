-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 11, 2025 at 04:18 AM
-- Server version: 10.11.13-MariaDB-cll-lve
-- PHP Version: 8.4.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dpharmai54_mvp`
--

-- --------------------------------------------------------

--
-- Table structure for table `api_tokens`
--

CREATE TABLE `api_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `token` varchar(255) NOT NULL,
  `last_used_at` datetime DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attachments`
--

CREATE TABLE `attachments` (
  `id` int(10) UNSIGNED NOT NULL,
  `visit_id` int(10) UNSIGNED NOT NULL,
  `type` varchar(50) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `filesize` int(11) DEFAULT NULL,
  `mime` varchar(100) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attachments`
--

INSERT INTO `attachments` (`id`, `visit_id`, `type`, `filename`, `filesize`, `mime`, `note`, `created_at`) VALUES
(1, 1, 'rx', '110825-rx-250001.png', 41919, 'image/png', NULL, '2025-08-11 01:09:38'),
(2, 1, 'rx', '110825-rx-250001-03.jpg', 153105, 'image/jpeg', NULL, '2025-08-11 01:13:57');

-- --------------------------------------------------------

--
-- Table structure for table `breeds`
--

CREATE TABLE `breeds` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `species_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `breeds`
--

INSERT INTO `breeds` (`id`, `species_id`, `name`) VALUES
(2, 1, 'German Shepherd'),
(1, 1, 'Labrador Retriever'),
(3, 2, 'Persian'),
(4, 2, 'Siamese');

-- --------------------------------------------------------

--
-- Table structure for table `cert_generated`
--

CREATE TABLE `cert_generated` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `template_id` bigint(20) UNSIGNED NOT NULL,
  `pet_id` bigint(20) UNSIGNED NOT NULL,
  `visit_id` bigint(20) UNSIGNED DEFAULT NULL,
  `path` varchar(255) NOT NULL,
  `filename` varchar(160) NOT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cert_templates`
--

CREATE TABLE `cert_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `category` enum('certificate','report','letter') NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `html` longtext NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `patient_unique_id` char(6) NOT NULL,
  `pet_id` bigint(20) UNSIGNED NOT NULL,
  `visit_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` enum('photo','prescription','doc','xray','lab','usg','invoice','vaccine','deworm','tick','consent','referral','qrcode','barcode','medscan') NOT NULL,
  `subtype` varchar(60) DEFAULT NULL,
  `path` varchar(255) NOT NULL,
  `filename` varchar(180) NOT NULL,
  `source` enum('android','web','pos','ingest','email','whatsapp') NOT NULL,
  `ref_id` varchar(60) DEFAULT NULL,
  `seq` smallint(5) UNSIGNED DEFAULT NULL,
  `mime` varchar(80) DEFAULT NULL,
  `size_bytes` int(10) UNSIGNED DEFAULT NULL,
  `captured_at` datetime NOT NULL,
  `checksum_sha1` char(40) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(1, '20250810_130500', 'App\\Database\\Migrations\\InitialSchema', 'default', 'App', 1754801773, 1),
(2, '20250810191910', 'App\\Database\\Migrations\\AttachmentsAndVisitTweaks_20250810191910', 'default', 'App', 1754856458, 2);

-- --------------------------------------------------------

--
-- Table structure for table `owners`
--

CREATE TABLE `owners` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(60) NOT NULL,
  `middle_name` varchar(60) DEFAULT NULL,
  `last_name` varchar(60) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `locality` varchar(120) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `status` enum('active','provisional','merged') DEFAULT 'active',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `owners`
--

INSERT INTO `owners` (`id`, `first_name`, `middle_name`, `last_name`, `email`, `locality`, `address`, `status`, `created_at`, `updated_at`) VALUES
(1, '', NULL, '', NULL, NULL, NULL, 'provisional', '2025-08-10 12:55:14', '2025-08-10 12:55:14'),
(2, 'Ravi', NULL, 'Sharma', NULL, NULL, NULL, 'active', '2025-08-10 15:51:32', '2025-08-10 15:51:32'),
(3, 'Meera', NULL, 'Patel', NULL, 'Vastrapur', 'Ahmedabad', 'active', '2025-08-10 13:23:02', '2025-08-10 13:23:02');

-- --------------------------------------------------------

--
-- Table structure for table `owner_mobiles`
--

CREATE TABLE `owner_mobiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `owner_id` bigint(20) UNSIGNED NOT NULL,
  `mobile_e164` varchar(20) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `owner_mobiles`
--

INSERT INTO `owner_mobiles` (`id`, `owner_id`, `mobile_e164`, `is_primary`, `is_verified`, `created_at`) VALUES
(1, 1, '9867999773', 1, 0, '2025-08-10 12:55:14'),
(2, 2, '9876543210', 1, 1, '2025-08-10 15:51:32'),
(3, 2, '9123456780', 0, 0, '2025-08-10 15:51:32'),
(6, 3, '9988776655', 1, 1, '2025-08-10 13:23:02');

-- --------------------------------------------------------

--
-- Table structure for table `pets`
--

CREATE TABLE `pets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `owner_id` bigint(20) UNSIGNED NOT NULL,
  `unique_id` char(6) NOT NULL,
  `pet_name` varchar(80) DEFAULT NULL,
  `species_id` bigint(20) UNSIGNED DEFAULT NULL,
  `breed_id` bigint(20) UNSIGNED DEFAULT NULL,
  `gender` enum('male','female','unknown') DEFAULT 'unknown',
  `dob` date DEFAULT NULL,
  `age_years` tinyint(3) UNSIGNED DEFAULT NULL,
  `age_months` tinyint(3) UNSIGNED DEFAULT NULL,
  `color` varchar(60) DEFAULT NULL,
  `microchip` varchar(32) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('active','provisional','archived','merged') DEFAULT 'active',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pets`
--

INSERT INTO `pets` (`id`, `owner_id`, `unique_id`, `pet_name`, `species_id`, `breed_id`, `gender`, `dob`, `age_years`, `age_months`, `color`, `microchip`, `notes`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, '250001', 'Bruno', NULL, NULL, 'unknown', NULL, NULL, NULL, NULL, NULL, NULL, 'provisional', '2025-08-10 12:55:14', '2025-08-10 12:55:14'),
(2, 2, '250002', 'Misty', 2, 3, 'female', NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2025-08-10 13:23:02', '2025-08-10 13:23:02'),
(3, 3, '250003', 'Nala', 2, 4, 'female', NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2025-08-10 13:23:02', '2025-08-10 13:23:02'),
(4, 1, '250004', NULL, 1, NULL, 'unknown', NULL, NULL, NULL, NULL, NULL, NULL, 'provisional', '2025-08-10 13:23:02', '2025-08-10 13:23:02');

-- --------------------------------------------------------

--
-- Table structure for table `pos_invoices`
--

CREATE TABLE `pos_invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pet_id` bigint(20) UNSIGNED NOT NULL,
  `visit_id` bigint(20) UNSIGNED DEFAULT NULL,
  `unique_id` char(6) NOT NULL,
  `invoice_id` varchar(40) NOT NULL,
  `sale_datetime` datetime NOT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `source` enum('webhook','poll') NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pos_invoice_items`
--

CREATE TABLE `pos_invoice_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_db_id` bigint(20) UNSIGNED NOT NULL,
  `product_code` varchar(60) DEFAULT NULL,
  `product_name` varchar(120) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `preventive_events`
--

CREATE TABLE `preventive_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `pet_id` bigint(20) UNSIGNED NOT NULL,
  `date_given` date NOT NULL,
  `visit_id` bigint(20) UNSIGNED DEFAULT NULL,
  `captured_by` enum('android','web','pos','ingest','email','whatsapp') NOT NULL,
  `dose_ml` decimal(5,2) DEFAULT NULL,
  `route` varchar(20) DEFAULT NULL,
  `site` varchar(40) DEFAULT NULL,
  `manufacturer` varchar(80) DEFAULT NULL,
  `batch` varchar(40) DEFAULT NULL,
  `expiry` date DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `preventive_items`
--

CREATE TABLE `preventive_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `plan_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('vaccine','deworm','tickflea') NOT NULL,
  `subtype` varchar(80) DEFAULT NULL,
  `due_date` date NOT NULL,
  `window_start` date DEFAULT NULL,
  `window_end` date DEFAULT NULL,
  `status` enum('scheduled','overdue','done','skipped') DEFAULT 'scheduled',
  `reminder_state` enum('none','pending','sent','confirmed') DEFAULT 'pending',
  `last_reminder_at` datetime DEFAULT NULL,
  `visit_id` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` varchar(180) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `preventive_plans`
--

CREATE TABLE `preventive_plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pet_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('vaccine','deworm','tickflea') NOT NULL,
  `status` enum('active','paused') DEFAULT 'active',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `preventive_templates`
--

CREATE TABLE `preventive_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `species_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` enum('vaccine','deworm','tickflea') NOT NULL,
  `subtype` varchar(80) DEFAULT NULL,
  `json_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`json_rules`)),
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reminders`
--

CREATE TABLE `reminders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pet_id` bigint(20) UNSIGNED NOT NULL,
  `owner_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('visit','vaccine','deworm','tickflea','custom') NOT NULL,
  `subtype` varchar(60) DEFAULT NULL,
  `due_date` date NOT NULL,
  `status` enum('pending','sent','snoozed','skipped','failed') DEFAULT 'pending',
  `channel` enum('whatsapp','sms','both') DEFAULT 'whatsapp',
  `last_attempt_at` datetime DEFAULT NULL,
  `attempts_count` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `species`
--

CREATE TABLE `species` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `species`
--

INSERT INTO `species` (`id`, `name`) VALUES
(3, 'Avian'),
(1, 'Canine'),
(5, 'Exotic'),
(2, 'Feline'),
(4, 'Tortoise');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('frontdesk','doctor','admin') NOT NULL DEFAULT 'frontdesk',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `visits`
--

CREATE TABLE `visits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pet_id` bigint(20) UNSIGNED NOT NULL,
  `visit_date` date NOT NULL,
  `sequence` int(11) NOT NULL DEFAULT 1,
  `visit_seq` int(10) UNSIGNED NOT NULL,
  `status` enum('open','closed') DEFAULT 'open',
  `source` enum('android','web','pos-only','ingest','email','whatsapp') DEFAULT 'web',
  `reason` varchar(200) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `next_visit` date DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `visits`
--

INSERT INTO `visits` (`id`, `pet_id`, `visit_date`, `sequence`, `visit_seq`, `status`, `source`, `reason`, `remarks`, `next_visit`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, '2025-08-11', 1, 0, 'open', 'web', NULL, NULL, NULL, NULL, '2025-08-11 00:41:02', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `visit_seq_counters`
--

CREATE TABLE `visit_seq_counters` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pet_id` bigint(20) UNSIGNED NOT NULL,
  `last_visit_seq` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `year_counters`
--

CREATE TABLE `year_counters` (
  `id` int(10) UNSIGNED NOT NULL,
  `year_two` char(2) NOT NULL,
  `last_seq` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `year_counters`
--

INSERT INTO `year_counters` (`id`, `year_two`, `last_seq`, `updated_at`) VALUES
(1, '25', 3, '2025-08-10 13:23:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `api_tokens`
--
ALTER TABLE `api_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_api_user` (`user_id`);

--
-- Indexes for table `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_attach_visit` (`visit_id`);

--
-- Indexes for table `breeds`
--
ALTER TABLE `breeds`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_species_breed` (`species_id`,`name`);

--
-- Indexes for table `cert_generated`
--
ALTER TABLE `cert_generated`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cg_template` (`template_id`),
  ADD KEY `fk_cg_pet` (`pet_id`),
  ADD KEY `fk_cg_visit` (`visit_id`);

--
-- Indexes for table `cert_templates`
--
ALTER TABLE `cert_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_docs_pet_date` (`pet_id`,`captured_at`),
  ADD KEY `idx_docs_visit` (`visit_id`),
  ADD KEY `idx_docs_type_date` (`type`,`captured_at`),
  ADD KEY `idx_docs_ref` (`ref_id`),
  ADD KEY `idx_docs_unique_id` (`patient_unique_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `owners`
--
ALTER TABLE `owners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `owner_mobiles`
--
ALTER TABLE `owner_mobiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_owner_mobile` (`owner_id`,`mobile_e164`),
  ADD KEY `idx_mobile` (`mobile_e164`);

--
-- Indexes for table `pets`
--
ALTER TABLE `pets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_id` (`unique_id`),
  ADD KEY `idx_pets_owner` (`owner_id`),
  ADD KEY `fk_pets_species` (`species_id`),
  ADD KEY `fk_pets_breed` (`breed_id`);

--
-- Indexes for table `pos_invoices`
--
ALTER TABLE `pos_invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_id` (`invoice_id`),
  ADD KEY `idx_pos_unique` (`unique_id`),
  ADD KEY `fk_pos_pet` (`pet_id`),
  ADD KEY `fk_pos_visit` (`visit_id`);

--
-- Indexes for table `pos_invoice_items`
--
ALTER TABLE `pos_invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pos_item_invoice` (`invoice_db_id`);

--
-- Indexes for table `preventive_events`
--
ALTER TABLE `preventive_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pe_item` (`item_id`),
  ADD KEY `fk_pe_pet` (`pet_id`),
  ADD KEY `fk_pe_visit` (`visit_id`);

--
-- Indexes for table `preventive_items`
--
ALTER TABLE `preventive_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pi_due` (`due_date`,`status`),
  ADD KEY `fk_pi_plan` (`plan_id`),
  ADD KEY `fk_pi_visit` (`visit_id`);

--
-- Indexes for table `preventive_plans`
--
ALTER TABLE `preventive_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pp_pet` (`pet_id`);

--
-- Indexes for table `preventive_templates`
--
ALTER TABLE `preventive_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pt_species` (`species_id`);

--
-- Indexes for table `reminders`
--
ALTER TABLE `reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rem_due` (`due_date`,`status`),
  ADD KEY `fk_rem_pet` (`pet_id`),
  ADD KEY `fk_rem_owner` (`owner_id`);

--
-- Indexes for table `species`
--
ALTER TABLE `species`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `visits`
--
ALTER TABLE `visits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_visit` (`pet_id`,`visit_date`,`visit_seq`);

--
-- Indexes for table `visit_seq_counters`
--
ALTER TABLE `visit_seq_counters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pet_id` (`pet_id`);

--
-- Indexes for table `year_counters`
--
ALTER TABLE `year_counters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `year_two` (`year_two`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `api_tokens`
--
ALTER TABLE `api_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attachments`
--
ALTER TABLE `attachments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `breeds`
--
ALTER TABLE `breeds`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `cert_generated`
--
ALTER TABLE `cert_generated`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cert_templates`
--
ALTER TABLE `cert_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `owners`
--
ALTER TABLE `owners`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `owner_mobiles`
--
ALTER TABLE `owner_mobiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pets`
--
ALTER TABLE `pets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pos_invoices`
--
ALTER TABLE `pos_invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pos_invoice_items`
--
ALTER TABLE `pos_invoice_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `preventive_events`
--
ALTER TABLE `preventive_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `preventive_items`
--
ALTER TABLE `preventive_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `preventive_plans`
--
ALTER TABLE `preventive_plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `preventive_templates`
--
ALTER TABLE `preventive_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reminders`
--
ALTER TABLE `reminders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `species`
--
ALTER TABLE `species`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `visits`
--
ALTER TABLE `visits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `visit_seq_counters`
--
ALTER TABLE `visit_seq_counters`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `year_counters`
--
ALTER TABLE `year_counters`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `api_tokens`
--
ALTER TABLE `api_tokens`
  ADD CONSTRAINT `fk_api_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `breeds`
--
ALTER TABLE `breeds`
  ADD CONSTRAINT `fk_breeds_species` FOREIGN KEY (`species_id`) REFERENCES `species` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cert_generated`
--
ALTER TABLE `cert_generated`
  ADD CONSTRAINT `fk_cg_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`),
  ADD CONSTRAINT `fk_cg_template` FOREIGN KEY (`template_id`) REFERENCES `cert_templates` (`id`),
  ADD CONSTRAINT `fk_cg_visit` FOREIGN KEY (`visit_id`) REFERENCES `visits` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `fk_docs_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_docs_visit` FOREIGN KEY (`visit_id`) REFERENCES `visits` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `owner_mobiles`
--
ALTER TABLE `owner_mobiles`
  ADD CONSTRAINT `fk_owner_mobiles_owner` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pets`
--
ALTER TABLE `pets`
  ADD CONSTRAINT `fk_pets_breed` FOREIGN KEY (`breed_id`) REFERENCES `breeds` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pets_owner` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pets_species` FOREIGN KEY (`species_id`) REFERENCES `species` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pos_invoices`
--
ALTER TABLE `pos_invoices`
  ADD CONSTRAINT `fk_pos_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pos_visit` FOREIGN KEY (`visit_id`) REFERENCES `visits` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pos_invoice_items`
--
ALTER TABLE `pos_invoice_items`
  ADD CONSTRAINT `fk_pos_item_invoice` FOREIGN KEY (`invoice_db_id`) REFERENCES `pos_invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `preventive_events`
--
ALTER TABLE `preventive_events`
  ADD CONSTRAINT `fk_pe_item` FOREIGN KEY (`item_id`) REFERENCES `preventive_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pe_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pe_visit` FOREIGN KEY (`visit_id`) REFERENCES `visits` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `preventive_items`
--
ALTER TABLE `preventive_items`
  ADD CONSTRAINT `fk_pi_plan` FOREIGN KEY (`plan_id`) REFERENCES `preventive_plans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pi_visit` FOREIGN KEY (`visit_id`) REFERENCES `visits` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `preventive_plans`
--
ALTER TABLE `preventive_plans`
  ADD CONSTRAINT `fk_pp_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `preventive_templates`
--
ALTER TABLE `preventive_templates`
  ADD CONSTRAINT `fk_pt_species` FOREIGN KEY (`species_id`) REFERENCES `species` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reminders`
--
ALTER TABLE `reminders`
  ADD CONSTRAINT `fk_rem_owner` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rem_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `visits`
--
ALTER TABLE `visits`
  ADD CONSTRAINT `fk_visits_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `visit_seq_counters`
--
ALTER TABLE `visit_seq_counters`
  ADD CONSTRAINT `fk_visit_seq_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
