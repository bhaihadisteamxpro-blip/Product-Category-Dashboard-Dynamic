-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 27, 2025 at 07:55 PM
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
-- Database: `stock_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_categories`
--

CREATE TABLE `admin_categories` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `assigned_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_categories`
--

INSERT INTO `admin_categories` (`id`, `admin_id`, `category_id`, `assigned_date`) VALUES
(1, 2, 1, '2025-12-27 15:59:16');

-- --------------------------------------------------------

--
-- Table structure for table `admin_products`
--

CREATE TABLE `admin_products` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `assigned_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_products`
--

INSERT INTO `admin_products` (`id`, `admin_id`, `product_id`, `assigned_date`) VALUES
(2, 2, 1, '2025-12-27 15:54:23');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `category_description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category_name`, `category_description`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'cash', 'sdsds', 'active', 1, '2025-12-27 14:39:25', '2025-12-27 14:39:25');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `can_manage_users` tinyint(1) DEFAULT 0,
  `can_manage_products` tinyint(1) DEFAULT 0,
  `can_manage_categories` tinyint(1) DEFAULT 0,
  `can_view_reports` tinyint(1) DEFAULT 0,
  `can_manage_inventory` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `product_description` text DEFAULT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) DEFAULT 0,
  `unit` varchar(20) DEFAULT 'pcs',
  `min_stock` int(11) DEFAULT 10,
  `status` enum('active','inactive','out_of_stock') DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `category_id`, `product_description`, `sku`, `price`, `quantity`, `unit`, `min_stock`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'daal', 1, 'haan', 'DAAL451', 20000.00, 100, '0', 10, 'active', 1, '2025-12-27 14:56:08', '2025-12-27 14:56:56');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `admin_id` varchar(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department` varchar(50) NOT NULL,
  `role` enum('super_admin','admin','staff') DEFAULT 'admin',
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `admin_id`, `full_name`, `username`, `email`, `password`, `phone`, `department`, `role`, `status`, `created_at`, `updated_at`, `created_by`, `last_login`) VALUES
(1, 'SUPER001', 'Super Admin', 'superadmin', 'superadmin@stock.com', '$2y$10$YourHashedPasswordHere', '', 'management', 'super_admin', 'active', '2025-12-27 11:49:26', '2025-12-27 17:37:18', NULL, NULL),
(2, 'ADM20251227855', 'Hadi ABDUL', 'root', 'hadimemon57@gmail.com', '$2y$10$WjZ0XtRxE04wZVUAQk6pMejAouF.aWRm.PXWFBDzUJk7EI6qhOzdS', '03308273801', 'warehouse', 'admin', 'active', '2025-12-27 11:53:00', '2025-12-27 12:13:26', NULL, '2025-12-27 12:13:26'),
(4, 'ADM202401001', 'John Doe', 'johndoe', 'john@stock.com', '$2y$10$YourHashedPasswordHere', NULL, 'inventory', 'admin', 'active', '2025-12-27 12:08:09', '2025-12-27 12:08:09', NULL, NULL),
(5, 'ADM202401002', 'Jane Smith', 'janesmith', 'jane@stock.com', '$2y$10$YourHashedPasswordHere', NULL, 'sales', 'admin', 'active', '2025-12-27 12:08:09', '2025-12-27 12:08:09', NULL, NULL),
(6, 'ADM20251227681', 'Hadi Memon', 'hadimemon', 'gamerxpro247@gmail.com', '$2y$10$JPddJ/Va1btUHHP2vkniTuxbFRdpwytM7hzzUD/775/8nK1FBKPoe', '03308273801', 'inventory', 'admin', 'active', '2025-12-27 13:30:29', '2025-12-27 14:10:39', NULL, '2025-12-27 14:10:39'),
(7, 'ADM20251227410', 'hunain', 'hunain', 'hunain@gmail.com', '$2y$10$.8zUmdsKmRHVkCznoFKJUeSlu3JpyJzrnZA46lqdzz3D6Q9JyQ4z.', '03302071264', 'warehouse', 'admin', 'active', '2025-12-27 14:13:21', '2025-12-27 16:47:31', NULL, '2025-12-27 16:47:31'),
(10, 'ADMIN002', 'Hadi', 'hadi', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'sales', 'admin', 'active', '2025-12-27 17:48:27', '2025-12-27 17:48:27', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_categories`
--
ALTER TABLE `admin_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_admin_category` (`admin_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `admin_products`
--
ALTER TABLE `admin_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_admin_product` (`admin_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category_status` (`status`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `idx_product_status` (`status`),
  ADD KEY `idx_product_category` (`category_id`),
  ADD KEY `idx_product_sku` (`sku`),
  ADD KEY `idx_product_created_by` (`created_by`),
  ADD KEY `idx_product_stock` (`quantity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_id` (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_department` (`department`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_categories`
--
ALTER TABLE `admin_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_products`
--
ALTER TABLE `admin_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_categories`
--
ALTER TABLE `admin_categories`
  ADD CONSTRAINT `admin_categories_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `admin_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_products`
--
ALTER TABLE `admin_products`
  ADD CONSTRAINT `admin_products_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `admin_products_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `permissions`
--
ALTER TABLE `permissions`
  ADD CONSTRAINT `permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
