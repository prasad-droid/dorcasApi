-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 01, 2026 at 10:36 PM
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
-- Database: `u322583024_dorcas`
--

-- --------------------------------------------------------

--
-- Table structure for table `banner`
--

CREATE TABLE `banner` (
  `id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `mobile_img` varchar(255) NOT NULL,
  `deleted_at` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog`
--

CREATE TABLE `blog` (
  `id` int(11) NOT NULL,
  `url_name` varchar(255) NOT NULL,
  `blog_name` varchar(255) NOT NULL,
  `blog_description` longtext NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `meta_tittle` varchar(255) DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `booking_name` varchar(100) NOT NULL,
  `booking_phone` varchar(15) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(80) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `service_date` date NOT NULL,
  `service_time` time NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','confirmed','completed','cancelled','no_vendor') DEFAULT 'pending',
  `payment_mode` enum('cash','online') DEFAULT 'cash',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `amount_paid` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking_notifications`
--

CREATE TABLE `booking_notifications` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `type` varchar(50) DEFAULT 'no_vendor',
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(255) NOT NULL,
  `category_img` varchar(255) NOT NULL,
  `banner_img` varchar(255) DEFAULT NULL,
  `category_name` varchar(255) NOT NULL,
  `meta_tittle` varchar(255) NOT NULL,
  `meta_keywords` varchar(255) NOT NULL,
  `meta_description` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ccav_orders`
--

CREATE TABLE `ccav_orders` (
  `order_id` varchar(60) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `payment_ids` text NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(200) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `service` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','replied') NOT NULL DEFAULT 'new',
  `ip_address` varchar(45) DEFAULT NULL,
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(80) DEFAULT NULL,
  `latitude` decimal(10,6) DEFAULT NULL,
  `longitude` decimal(10,6) DEFAULT NULL,
  `last_seen_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  `referral_code` varchar(12) DEFAULT NULL,
  `referred_by` int(11) DEFAULT NULL,
  `otp` varchar(6) DEFAULT NULL,
  `otp_expires_at` datetime DEFAULT NULL,
  `phone_verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_wallet`
--

CREATE TABLE `customer_wallet` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `balance` int(11) NOT NULL DEFAULT 0,
  `total_earned` int(11) NOT NULL DEFAULT 0,
  `bookings_count` int(11) NOT NULL DEFAULT 0,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_redeemed_at` datetime DEFAULT NULL,
  `total_redeemed` int(11) DEFAULT 0,
  `referral_points` int(11) DEFAULT 0,
  `referral_balance` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `data_deletion_requests`
--

CREATE TABLE `data_deletion_requests` (
  `id` int(11) NOT NULL,
  `user_type` enum('customer','vendor') NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `delete_types` text NOT NULL COMMENT 'comma-separated: account,bookings,payments,location,all',
  `status` enum('pending','processing','completed','rejected') DEFAULT 'pending',
  `ip_address` varchar(45) DEFAULT NULL,
  `submitted_at` datetime DEFAULT current_timestamp(),
  `processed_at` datetime DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `processed_note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deleted_data_archive`
--

CREATE TABLE `deleted_data_archive` (
  `id` int(11) NOT NULL,
  `deletion_request_id` int(11) NOT NULL,
  `user_type` enum('customer','vendor') NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `user_phone` varchar(20) DEFAULT NULL,
  `data_type` varchar(50) NOT NULL COMMENT 'account/bookings/payments/location/reviews',
  `archived_data` longtext NOT NULL COMMENT 'JSON snapshot of deleted rows',
  `deleted_by` int(11) NOT NULL COMMENT 'admin user id',
  `deleted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `expense_date` date NOT NULL,
  `category` varchar(50) NOT NULL,
  `type` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `paid_by` varchar(50) DEFAULT 'Cash',
  `receipt_note` varchar(200) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `indian_states`
--

CREATE TABLE `indian_states` (
  `id` int(11) NOT NULL,
  `state_name` varchar(100) NOT NULL,
  `state_code` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_type` enum('customer','vendor') NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `type` varchar(60) NOT NULL,
  `title` varchar(200) NOT NULL,
  `body` text NOT NULL,
  `icon` varchar(10) NOT NULL DEFAULT '?',
  `color` varchar(20) NOT NULL DEFAULT '#2e85fd',
  `link` varchar(300) DEFAULT NULL,
  `booking_id` int(10) UNSIGNED DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `label` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1 COMMENT '1=Active, 0=Inactive',
  `stock` int(11) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `featured_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_new_arrival` tinyint(1) DEFAULT 0 COMMENT '1=New Arrival, 0=Not New Arrival',
  `new_arrival_order` int(11) DEFAULT 0 COMMENT 'Display order for new arrivals section',
  `is_best_seller` tinyint(1) DEFAULT 0 COMMENT '1=Best Seller, 0=Not Best Seller',
  `best_seller_order` int(11) DEFAULT 0 COMMENT 'Display order for best sellers section',
  `is_goal_product` tinyint(1) DEFAULT 0 COMMENT '1=Goal Product, 0=Not Goal Product',
  `goal_order` int(11) DEFAULT 0 COMMENT 'Display order for goals section',
  `goal_type` varchar(50) DEFAULT NULL COMMENT 'e.g., Weight Loss, Muscle Gain, Energy Boost, etc.',
  `is_benefit_product` tinyint(1) DEFAULT 0 COMMENT '1=Benefit Product, 0=Not Benefit Product',
  `benefit_order` int(11) DEFAULT 0 COMMENT 'Display order for benefits section',
  `benefit_type` varchar(100) DEFAULT NULL COMMENT 'e.g., Bone Health, Heart Health, Brain Support, etc.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `redeem_requests`
--

CREATE TABLE `redeem_requests` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `payment_method` enum('upi','bank') DEFAULT 'upi',
  `upi_id` varchar(100) DEFAULT NULL,
  `bank_account` varchar(30) DEFAULT NULL,
  `bank_ifsc` varchar(15) DEFAULT NULL,
  `account_name` varchar(100) DEFAULT NULL,
  `razorpay_payout_id` varchar(100) DEFAULT NULL,
  `razorpay_fund_account_id` varchar(100) DEFAULT NULL,
  `payout_status` varchar(30) DEFAULT NULL,
  `payout_error` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_note` varchar(255) DEFAULT NULL,
  `requested_at` datetime DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referral_logs`
--

CREATE TABLE `referral_logs` (
  `id` int(11) NOT NULL,
  `referrer_id` int(11) NOT NULL,
  `referee_id` int(11) NOT NULL,
  `points_awarded` int(11) DEFAULT 100,
  `rupees_value` decimal(10,2) DEFAULT 10.00,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `review_text` text DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `scratch_cards`
--

CREATE TABLE `scratch_cards` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `reward_amount` int(11) NOT NULL DEFAULT 0,
  `is_scratched` tinyint(1) NOT NULL DEFAULT 0,
  `scratched_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `scratch_card_config`
--

CREATE TABLE `scratch_card_config` (
  `position` tinyint(3) UNSIGNED NOT NULL,
  `amount` int(10) UNSIGNED NOT NULL DEFAULT 50,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `scratch_card_counter`
--

CREATE TABLE `scratch_card_counter` (
  `customer_id` int(11) NOT NULL DEFAULT 0,
  `current_position` tinyint(3) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `subcategory_id` int(11) DEFAULT NULL,
  `service_img` varchar(255) DEFAULT NULL,
  `service_name` varchar(255) NOT NULL,
  `service_price` decimal(10,2) DEFAULT 0.00,
  `service_duration` varchar(100) DEFAULT NULL,
  `service_desc` text DEFAULT NULL,
  `meta_tittle` varchar(255) DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Inactive',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subcategories`
--

CREATE TABLE `subcategories` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `subcategory_img` varchar(255) DEFAULT NULL,
  `subcategory_name` varchar(255) NOT NULL,
  `meta_tittle` varchar(255) DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `name` varchar(500) NOT NULL,
  `email` varchar(500) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `role` varchar(500) NOT NULL,
  `allowed_tabs` text DEFAULT NULL COMMENT 'Comma-separated list of sidebar tab keys this user can access. NULL/empty = all tabs (admin).',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `allowed_columns` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`allowed_columns`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE `vendors` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `otp` varchar(6) DEFAULT NULL,
  `otp_expires_at` datetime DEFAULT NULL,
  `phone_verified` tinyint(1) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `rating` decimal(3,2) DEFAULT 0.00,
  `total_jobs` int(11) DEFAULT 0,
  `kyc_status` enum('none','pending','verified','rejected') NOT NULL DEFAULT 'none',
  `kyc_doc_path` varchar(255) DEFAULT NULL,
  `kyc_doc_type` enum('aadhaar','pan','other') DEFAULT NULL,
  `kyc_verified_at` datetime DEFAULT NULL,
  `kyc_reject_reason` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `referral_code` varchar(12) DEFAULT NULL,
  `referred_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_booking_requests`
--

CREATE TABLE `vendor_booking_requests` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `status` enum('pending','accepted','declined','expired') DEFAULT 'pending',
  `sent_at` datetime DEFAULT current_timestamp(),
  `responded_at` datetime DEFAULT NULL,
  `queue_position` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_payments`
--

CREATE TABLE `vendor_payments` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid') DEFAULT 'pending',
  `due_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `paid_at` timestamp NULL DEFAULT NULL,
  `razorpay_order_id` varchar(100) DEFAULT NULL,
  `razorpay_payment_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_redeem_requests`
--

CREATE TABLE `vendor_redeem_requests` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('upi','bank') DEFAULT 'upi',
  `upi_id` varchar(100) DEFAULT NULL,
  `bank_account` varchar(50) DEFAULT NULL,
  `bank_ifsc` varchar(20) DEFAULT NULL,
  `account_name` varchar(100) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_note` varchar(255) DEFAULT NULL,
  `requested_at` datetime DEFAULT current_timestamp(),
  `processed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_referral_logs`
--

CREATE TABLE `vendor_referral_logs` (
  `id` int(11) NOT NULL,
  `referrer_id` int(11) NOT NULL,
  `referee_id` int(11) NOT NULL,
  `points_awarded` int(11) DEFAULT 100,
  `rupees_value` decimal(10,2) DEFAULT 10.00,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_remember_tokens`
--

CREATE TABLE `vendor_remember_tokens` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_services`
--

CREATE TABLE `vendor_services` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_service_areas`
--

CREATE TABLE `vendor_service_areas` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `radius_km` int(11) DEFAULT 15
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_wallet`
--

CREATE TABLE `vendor_wallet` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `balance` decimal(10,2) DEFAULT 0.00,
  `referral_points` int(11) DEFAULT 0,
  `referral_balance` decimal(10,2) DEFAULT 0.00,
  `total_earned` decimal(10,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `banner`
--
ALTER TABLE `banner`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blog`
--
ALTER TABLE `blog`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bookings_customer_status_date` (`customer_id`,`status`,`updated_at`);

--
-- Indexes for table `booking_notifications`
--
ALTER TABLE `booking_notifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_booking_notif` (`booking_id`,`type`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ccav_orders`
--
ALTER TABLE `ccav_orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD UNIQUE KEY `referral_code` (`referral_code`);

--
-- Indexes for table `customer_wallet`
--
ALTER TABLE `customer_wallet`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_id` (`customer_id`);

--
-- Indexes for table `data_deletion_requests`
--
ALTER TABLE `data_deletion_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deleted_data_archive`
--
ALTER TABLE `deleted_data_archive`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `indian_states`
--
ALTER TABLE `indian_states`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_type`,`user_id`,`is_read`),
  ADD KEY `idx_booking` (`booking_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_new_arrival` (`is_new_arrival`,`new_arrival_order`),
  ADD KEY `idx_best_seller` (`is_best_seller`,`best_seller_order`),
  ADD KEY `idx_goal_product` (`is_goal_product`,`goal_order`),
  ADD KEY `idx_benefit_product` (`is_benefit_product`,`benefit_order`);

--
-- Indexes for table `redeem_requests`
--
ALTER TABLE `redeem_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `referral_logs`
--
ALTER TABLE `referral_logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_referee` (`referee_id`);

--
-- Indexes for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `token_2` (`token`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_id` (`booking_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `vendor_id` (`vendor_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `scratch_cards`
--
ALTER TABLE `scratch_cards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_id` (`booking_id`);

--
-- Indexes for table `scratch_card_config`
--
ALTER TABLE `scratch_card_config`
  ADD PRIMARY KEY (`position`);

--
-- Indexes for table `scratch_card_counter`
--
ALTER TABLE `scratch_card_counter`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `uq_customer` (`customer_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_service_category` (`category_id`),
  ADD KEY `fk_service_subcategory` (`subcategory_id`);

--
-- Indexes for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_subcategory_category` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_google_id` (`google_id`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD UNIQUE KEY `referral_code` (`referral_code`);

--
-- Indexes for table `vendor_booking_requests`
--
ALTER TABLE `vendor_booking_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_booking_vendor` (`booking_id`,`vendor_id`);

--
-- Indexes for table `vendor_payments`
--
ALTER TABLE `vendor_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_payments_ibfk_1` (`vendor_id`),
  ADD KEY `vendor_payments_ibfk_2` (`booking_id`);

--
-- Indexes for table `vendor_redeem_requests`
--
ALTER TABLE `vendor_redeem_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vendor_referral_logs`
--
ALTER TABLE `vendor_referral_logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_referee` (`referee_id`);

--
-- Indexes for table `vendor_remember_tokens`
--
ALTER TABLE `vendor_remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `token_2` (`token`),
  ADD KEY `vendor_id` (`vendor_id`);

--
-- Indexes for table `vendor_services`
--
ALTER TABLE `vendor_services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_vendor_service` (`vendor_id`,`service_id`),
  ADD KEY `fk_vs_category` (`category_id`),
  ADD KEY `fk_vs_service` (`service_id`);

--
-- Indexes for table `vendor_service_areas`
--
ALTER TABLE `vendor_service_areas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_id` (`vendor_id`);

--
-- Indexes for table `vendor_wallet`
--
ALTER TABLE `vendor_wallet`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vendor_id` (`vendor_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `banner`
--
ALTER TABLE `banner`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blog`
--
ALTER TABLE `blog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking_notifications`
--
ALTER TABLE `booking_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_wallet`
--
ALTER TABLE `customer_wallet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `data_deletion_requests`
--
ALTER TABLE `data_deletion_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deleted_data_archive`
--
ALTER TABLE `deleted_data_archive`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `indian_states`
--
ALTER TABLE `indian_states`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `redeem_requests`
--
ALTER TABLE `redeem_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referral_logs`
--
ALTER TABLE `referral_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `scratch_cards`
--
ALTER TABLE `scratch_cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subcategories`
--
ALTER TABLE `subcategories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_booking_requests`
--
ALTER TABLE `vendor_booking_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_payments`
--
ALTER TABLE `vendor_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_redeem_requests`
--
ALTER TABLE `vendor_redeem_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_referral_logs`
--
ALTER TABLE `vendor_referral_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_remember_tokens`
--
ALTER TABLE `vendor_remember_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_services`
--
ALTER TABLE `vendor_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_service_areas`
--
ALTER TABLE `vendor_service_areas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_wallet`
--
ALTER TABLE `vendor_wallet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`),
  ADD CONSTRAINT `reviews_ibfk_4` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `fk_service_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_service_subcategory` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD CONSTRAINT `fk_subcategory_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `vendor_payments`
--
ALTER TABLE `vendor_payments`
  ADD CONSTRAINT `vendor_payments_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vendor_payments_ibfk_2` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_services`
--
ALTER TABLE `vendor_services`
  ADD CONSTRAINT `fk_vs_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `fk_vs_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `fk_vs_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_service_areas`
--
ALTER TABLE `vendor_service_areas`
  ADD CONSTRAINT `vendor_service_areas_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
