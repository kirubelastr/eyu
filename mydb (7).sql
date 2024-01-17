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
-- Database: `mydb`
--

-- --------------------------------------------------------

--
-- Table structure for table `combinations`
--

CREATE TABLE `combinations` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `rowID` varchar(255) NOT NULL,
  `rawMaterial` varchar(255) NOT NULL,
  `quantity` varchar(255) NOT NULL,
  `price` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `combinations`
--

INSERT INTO `combinations` (`id`, `name`, `rowID`, `rawMaterial`, `quantity`, `price`) VALUES
(5, 'cupsasdasd', '1,1', 'salad,salad', '213,1', '11,1'),
(6, 'afasf', '1,1', 'takeaway cup,salad', '2,213', '10,11'),
(7, 'test', '22', 'a', '0.2', '2.5'),
(8, 'testingdecimal', '22', 'a', '0.2', '2.5');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `quantity` decimal(11,3) NOT NULL,
  `price` decimal(11,3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `name`, `quantity`, `price`) VALUES
(1, 'salad', 1.000, 70.000),
(22, 'a', 66.000, 12.000),
(23, 'b', 180.000, 13.000),
(24, 'wwq', 152.000, 14.000),
(25, 'asfaf', 2.000, 1.000),
(26, 'gsd', 3242.230, 235.500),
(27, 'asfasf', 123.000, 12.000),
(28, 'qqqqqq', 12.000, 112.000),
(29, 'asfasf', 12312.000, 12312.000),
(30, 'applafk', 234.000, 234234.000),
(31, 'qqqqqwerqwreqw', 6265.230, 23.120),
(32, 'asf', 2.000, 2.000),
(33, 'asf', 564.000, 64.000),
(34, 'ASKFASN', 55.000, 54.000);

-- --------------------------------------------------------

--
-- Table structure for table `losses`
--

CREATE TABLE `losses` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` decimal(11,3) NOT NULL,
  `reason` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `losses`
--

INSERT INTO `losses` (`id`, `item_id`, `quantity`, `reason`) VALUES
(10, 23, 10.000, 'adsad');

-- --------------------------------------------------------

--
-- Table structure for table `product_inventory`
--

CREATE TABLE `product_inventory` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `quantity` decimal(11,3) NOT NULL,
  `price` decimal(11,3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_inventory`
--

INSERT INTO `product_inventory` (`id`, `name`, `quantity`, `price`) VALUES
(1, 'salad', 2.000, 70.000),
(2, 'takeaway cup', 50.000, 9.000),
(3, 'a', 100.000, 12.000),
(4, 'b', 200.000, 13.000),
(5, 'c', 300.000, 14.000),
(6, 'asfaf', 2.000, 1.000),
(7, 'gsd', 3242.230, 235.500),
(8, 'asfasf', 123.000, 12.000),
(9, 'qqqqqq', 12.000, 112.000),
(10, 'asfasf', 12312.000, 12312.000),
(11, 'applafk', 234.000, 234234.000),
(12, 'qqqqqwerqwreqw', 6265.230, 23.120),
(13, 'asf', 2.000, 2.000),
(14, 'asf', 564.000, 64.000),
(15, 'ASKFASN', 55.000, 54.000);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` decimal(11,3) NOT NULL,
  `selling_price` decimal(11,3) NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `item_id`, `quantity`, `selling_price`, `created_at`) VALUES
(1, 5, 3.000, 3.000, '2024-01-11'),
(2, 5, 1.000, 1.000, '2024-01-11'),
(3, 7, 1.000, 25.000, '2024-01-11'),
(4, 7, 4.000, 100.000, '2024-01-11');

-- --------------------------------------------------------

--
-- Table structure for table `sales_and_losses`
--

CREATE TABLE `sales_and_losses` (
  `id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `action` varchar(255) DEFAULT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `quantity` decimal(11,3) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `selling_price` decimal(11,3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_and_losses`
--

INSERT INTO `sales_and_losses` (`id`, `date`, `action`, `item_name`, `quantity`, `reason`, `selling_price`) VALUES
(1, '2024-01-11 12:55:18', 'sell', 'cups', 3.000, NULL, 3.000),
(2, '2024-01-11 13:09:45', 'sell', 'cups', 1.000, NULL, 1.000),
(3, '2024-01-11 13:51:30', 'sell', 'test', 3.000, NULL, 25.000),
(4, '2024-01-11 14:13:36', 'sell', 'test', 12.000, NULL, 100.000),
(5, '2024-01-12 18:44:27', 'loss', 'b', 10.000, 'adsad', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `combinations`
--
ALTER TABLE `combinations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `losses`
--
ALTER TABLE `losses`
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
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales_and_losses`
--
ALTER TABLE `sales_and_losses`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `combinations`
--
ALTER TABLE `combinations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `losses`
--
ALTER TABLE `losses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `product_inventory`
--
ALTER TABLE `product_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sales_and_losses`
--
ALTER TABLE `sales_and_losses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
