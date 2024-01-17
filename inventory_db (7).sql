-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 17, 2024 at 04:52 PM
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
-- Database: `inventory_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `ExpenseID` int(11) NOT NULL,
  `ExpenseType` varchar(100) DEFAULT NULL,
  `ExpenseReason` varchar(255) DEFAULT NULL,
  `ExpenseDate` date DEFAULT current_timestamp(),
  `TotalCost` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`ExpenseID`, `ExpenseType`, `ExpenseReason`, `ExpenseDate`, `TotalCost`) VALUES
(2, 'transportation', 'wezader', '2024-01-10', 1510.00);

-- --------------------------------------------------------

--
-- Table structure for table `expensetypes`
--

CREATE TABLE `expensetypes` (
  `ExpenseTypeID` int(11) NOT NULL,
  `ExpenseTypeName` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expensetypes`
--

INSERT INTO `expensetypes` (`ExpenseTypeID`, `ExpenseTypeName`) VALUES
(0, 'others'),
(1, 'Rent'),
(2, 'Utilities'),
(3, 'Supplies'),
(4, 'Salaries'),
(5, 'Maintenance'),
(6, 'Transportation');

-- --------------------------------------------------------

--
-- Table structure for table `losses`
--

CREATE TABLE `losses` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity_lost` decimal(11,3) NOT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `losses`
--

INSERT INTO `losses` (`id`, `product_id`, `quantity_lost`, `reason`, `created_at`) VALUES
(1, 1, 8.000, 'damage', '2024-01-12 11:16:47');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `quantity` decimal(11,3) NOT NULL,
  `price` decimal(11,3) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `quantity`, `price`, `created_at`) VALUES
(1, 'Avocado', 40.000, 72.000, '2024-01-10 13:49:52'),
(2, 'Mango', 93.000, 16.000, '2024-01-10 13:52:48'),
(3, 'Papaya', 59.000, 21.000, '2024-01-10 13:53:06'),
(4, 'Water Melon', 59.000, 40.000, '2024-01-10 13:53:38'),
(5, 'Orange', 40.000, 70.000, '2024-01-10 13:53:53'),
(6, 'Carrot', 7.000, 45.000, '2024-01-10 13:54:48'),
(7, 'Lemon', 2.000, 250.000, '2024-01-10 13:55:40');

-- --------------------------------------------------------

--
-- Table structure for table `product_inventory`
--

CREATE TABLE `product_inventory` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `quantity` decimal(11,3) NOT NULL,
  `price` decimal(11,3) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_inventory`
--

INSERT INTO `product_inventory` (`id`, `name`, `quantity`, `price`, `created_at`) VALUES
(0, 'apples', 12.000, 2.000, '2024-01-12 09:42:53'),
(1, 'Avocado', 204.000, 72.000, '2024-01-10 10:49:52'),
(2, 'Mango', 93.000, 16.000, '2024-01-10 10:52:48'),
(3, 'Papaya', 59.000, 21.000, '2024-01-10 10:53:06'),
(4, 'Water Melon', 59.000, 40.000, '2024-01-10 10:53:38'),
(5, 'Orange', 40.000, 70.000, '2024-01-10 10:53:53'),
(6, 'Carrot', 7.000, 45.000, '2024-01-10 10:54:48'),
(7, 'Lemon', 2.000, 250.000, '2024-01-10 10:55:40');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity_sold` decimal(11,3) NOT NULL,
  `total_price` decimal(11,3) NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `product_id`, `quantity_sold`, `total_price`, `created_at`) VALUES
(1, 1, 4.000, 78.000, '2024-01-10'),
(2, 1, 100.000, 78.000, '2024-01-12'),
(3, 1, 2.000, 78.000, '2024-01-12'),
(4, 1, 50.000, 78.000, '2024-01-12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`ExpenseID`);

--
-- Indexes for table `expensetypes`
--
ALTER TABLE `expensetypes`
  ADD PRIMARY KEY (`ExpenseTypeID`);

--
-- Indexes for table `losses`
--
ALTER TABLE `losses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_inventory`
--
ALTER TABLE `product_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `ExpenseID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `losses`
--
ALTER TABLE `losses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `losses`
--
ALTER TABLE `losses`
  ADD CONSTRAINT `losses_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
