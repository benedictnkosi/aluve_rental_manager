-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 13, 2023 at 09:37 PM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `aluve_property_manager`
--

-- --------------------------------------------------------

--
-- Table structure for table `application`
--

CREATE TABLE `application` (
  `id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'new',
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `uid` varchar(36) NOT NULL,
  `unit` int(11) NOT NULL,
  `property` int(11) NOT NULL,
  `tenant` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `debit_order`
--

CREATE TABLE `debit_order` (
  `id` int(11) NOT NULL,
  `bank_name` varchar(45) DEFAULT NULL,
  `account_number` varchar(20) DEFAULT NULL,
  `account_holder` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `document`
--

CREATE TABLE `document` (
  `id` int(11) NOT NULL,
  `document_type` int(11) NOT NULL,
  `tenant` int(11) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `document_type_lookup`
--

CREATE TABLE `document_type_lookup` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `document_type_lookup`
--

INSERT INTO `document_type_lookup` (`id`, `name`, `status`) VALUES
(1, 'Bank Statement', 'active'),
(2, 'ID Document', 'active'),
(3, 'Co-Bank Statement', 'active'),
(4, 'Payslip', 'active'),
(5, 'Co-payslip', 'active'),
(6, 'Lease', 'active'),
(7, 'Proof OF Payment', 'active'),
(8, 'Signed Lease', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `expense`
--

CREATE TABLE `expense` (
  `id` int(11) NOT NULL,
  `expense` int(11) NOT NULL,
  `property` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `description` varchar(50) DEFAULT NULL,
  `guid` varchar(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `expense_account`
--

CREATE TABLE `expense_account` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `expense_account`
--

INSERT INTO `expense_account` (`id`, `name`) VALUES
(1, 'Repairs & Maintenance'),
(2, 'Utilities'),
(3, 'Insurance'),
(4, 'Mortgage Interest'),
(5, 'Wages & Salaries'),
(6, 'Professional Services'),
(7, 'Office Supplies ');

-- --------------------------------------------------------

--
-- Table structure for table `inspection`
--

CREATE TABLE `inspection` (
  `id` int(11) NOT NULL,
  `lease` int(11) NOT NULL,
  `json` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(11) DEFAULT 'new',
  `guid` varchar(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `inspection_image`
--

CREATE TABLE `inspection_image` (
  `id` int(11) NOT NULL,
  `inspection` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `leases`
--

CREATE TABLE `leases` (
  `id` int(11) NOT NULL,
  `tenant` int(11) DEFAULT NULL,
  `start` datetime DEFAULT NULL,
  `end` datetime DEFAULT NULL,
  `contract` varchar(45) DEFAULT NULL,
  `unit` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `property` int(11) NOT NULL,
  `guid` varchar(36) NOT NULL,
  `payment_rules` varchar(200) NOT NULL,
  `lease_aggreement` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance`
--

CREATE TABLE `maintenance` (
  `id` int(11) NOT NULL,
  `uid` varchar(36) NOT NULL,
  `unit` int(11) DEFAULT NULL,
  `summary` text NOT NULL,
  `status` varchar(11) NOT NULL,
  `property` int(11) NOT NULL,
  `date_logged` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `name` varchar(45) DEFAULT NULL,
  `address` varchar(45) DEFAULT NULL,
  `late_fee` int(11) DEFAULT 0,
  `lease_file_name` varchar(100) DEFAULT NULL,
  `rent_due` int(11) DEFAULT 1,
  `rent_late_days` int(11) DEFAULT 7,
  `type` varchar(45) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `account_number` varchar(20) DEFAULT NULL,
  `deposit_pecent` int(2) NOT NULL DEFAULT 0,
  `application_fee` int(4) NOT NULL DEFAULT 0,
  `guid` varchar(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `propertyusers`
--

CREATE TABLE `propertyusers` (
  `id` int(11) NOT NULL,
  `user` int(11) DEFAULT NULL,
  `property` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tenant`
--

CREATE TABLE `tenant` (
  `id` int(11) NOT NULL,
  `name` varchar(45) DEFAULT NULL,
  `phone` varchar(45) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  `unit` int(11) DEFAULT NULL,
  `debit_order` int(11) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `adults` int(2) DEFAULT NULL,
  `children` int(2) DEFAULT NULL,
  `id_number` varchar(20) DEFAULT NULL,
  `id_document_type` varchar(20) DEFAULT NULL,
  `salary` int(11) DEFAULT NULL,
  `occupation` varchar(50) DEFAULT NULL,
  `guid` varchar(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

CREATE TABLE `transaction` (
  `id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `description` varchar(100) NOT NULL,
  `amount` int(11) NOT NULL,
  `lease` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` int(11) NOT NULL,
  `name` varchar(45) DEFAULT NULL,
  `property` int(11) DEFAULT NULL,
  `rent` int(6) NOT NULL DEFAULT 0,
  `listed` tinyint(4) DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `parking` tinyint(1) DEFAULT NULL,
  `children_allowed` tinyint(1) DEFAULT NULL,
  `max_occupants` int(11) DEFAULT NULL,
  `min_gross_salary` int(11) DEFAULT NULL,
  `bedrooms` int(11) DEFAULT 1,
  `bathrooms` int(11) DEFAULT 1,
  `guid` varchar(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `idUsers` int(11) NOT NULL,
  `email` varchar(45) DEFAULT NULL,
  `password` varchar(200) DEFAULT NULL,
  `state` varchar(45) DEFAULT NULL,
  `roles` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `application`
--
ALTER TABLE `application`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_unit` (`unit`),
  ADD KEY `application_property` (`property`),
  ADD KEY `application_tenant` (`tenant`);

--
-- Indexes for table `debit_order`
--
ALTER TABLE `debit_order`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `document`
--
ALTER TABLE `document`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_type` (`document_type`),
  ADD KEY `tenant` (`tenant`);

--
-- Indexes for table `document_type_lookup`
--
ALTER TABLE `document_type_lookup`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expense`
--
ALTER TABLE `expense`
  ADD PRIMARY KEY (`id`),
  ADD KEY `expenses_property` (`property`),
  ADD KEY `expenses_expense_account` (`expense`);

--
-- Indexes for table `expense_account`
--
ALTER TABLE `expense_account`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inspection`
--
ALTER TABLE `inspection`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inspection_lease` (`lease`);

--
-- Indexes for table `inspection_image`
--
ALTER TABLE `inspection_image`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inspection_image_inspection` (`inspection`);

--
-- Indexes for table `leases`
--
ALTER TABLE `leases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant`),
  ADD KEY `unit_id` (`unit`),
  ADD KEY `property_id` (`property`);

--
-- Indexes for table `maintenance`
--
ALTER TABLE `maintenance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `propertyusers`
--
ALTER TABLE `propertyusers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property` (`property`),
  ADD KEY `users` (`user`);

--
-- Indexes for table `tenant`
--
ALTER TABLE `tenant`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_unit` (`unit`),
  ADD KEY `debit_order` (`debit_order`);

--
-- Indexes for table `transaction`
--
ALTER TABLE `transaction`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lease_id` (`lease`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`),
  ADD KEY `units_property` (`property`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`idUsers`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `application`
--
ALTER TABLE `application`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `debit_order`
--
ALTER TABLE `debit_order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `document`
--
ALTER TABLE `document`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `document_type_lookup`
--
ALTER TABLE `document_type_lookup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `expense`
--
ALTER TABLE `expense`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT for table `expense_account`
--
ALTER TABLE `expense_account`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `inspection`
--
ALTER TABLE `inspection`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `inspection_image`
--
ALTER TABLE `inspection_image`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `leases`
--
ALTER TABLE `leases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `maintenance`
--
ALTER TABLE `maintenance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `propertyusers`
--
ALTER TABLE `propertyusers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `tenant`
--
ALTER TABLE `tenant`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `transaction`
--
ALTER TABLE `transaction`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=204;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `idUsers` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `application`
--
ALTER TABLE `application`
  ADD CONSTRAINT `application_property` FOREIGN KEY (`property`) REFERENCES `properties` (`id`),
  ADD CONSTRAINT `application_tenant` FOREIGN KEY (`tenant`) REFERENCES `tenant` (`id`),
  ADD CONSTRAINT `application_unit` FOREIGN KEY (`unit`) REFERENCES `units` (`id`);

--
-- Constraints for table `document`
--
ALTER TABLE `document`
  ADD CONSTRAINT `document_type` FOREIGN KEY (`document_type`) REFERENCES `document_type_lookup` (`id`),
  ADD CONSTRAINT `tenant` FOREIGN KEY (`tenant`) REFERENCES `tenant` (`id`);

--
-- Constraints for table `expense`
--
ALTER TABLE `expense`
  ADD CONSTRAINT `expenses_expense_account` FOREIGN KEY (`expense`) REFERENCES `expense_account` (`id`),
  ADD CONSTRAINT `expenses_property` FOREIGN KEY (`property`) REFERENCES `properties` (`id`);

--
-- Constraints for table `inspection`
--
ALTER TABLE `inspection`
  ADD CONSTRAINT `inspection_lease` FOREIGN KEY (`lease`) REFERENCES `leases` (`id`);

--
-- Constraints for table `inspection_image`
--
ALTER TABLE `inspection_image`
  ADD CONSTRAINT `inspection_image_inspection` FOREIGN KEY (`inspection`) REFERENCES `inspection` (`id`);

--
-- Constraints for table `leases`
--
ALTER TABLE `leases`
  ADD CONSTRAINT `property_id` FOREIGN KEY (`property`) REFERENCES `properties` (`id`),
  ADD CONSTRAINT `tenant_id` FOREIGN KEY (`tenant`) REFERENCES `tenant` (`id`),
  ADD CONSTRAINT `unit_id` FOREIGN KEY (`unit`) REFERENCES `units` (`id`);

--
-- Constraints for table `propertyusers`
--
ALTER TABLE `propertyusers`
  ADD CONSTRAINT `property` FOREIGN KEY (`property`) REFERENCES `properties` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `users` FOREIGN KEY (`user`) REFERENCES `user` (`idUsers`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `tenant`
--
ALTER TABLE `tenant`
  ADD CONSTRAINT `debit_order` FOREIGN KEY (`debit_order`) REFERENCES `debit_order` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `tenant_unit` FOREIGN KEY (`unit`) REFERENCES `units` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `transaction`
--
ALTER TABLE `transaction`
  ADD CONSTRAINT `lease_id` FOREIGN KEY (`lease`) REFERENCES `leases` (`id`);

--
-- Constraints for table `units`
--
ALTER TABLE `units`
  ADD CONSTRAINT `units_property` FOREIGN KEY (`property`) REFERENCES `properties` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
