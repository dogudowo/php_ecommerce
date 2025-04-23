-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- 主機： localhost:3306
-- 產生時間： 2025 年 04 月 22 日 08:31
-- 伺服器版本： 10.11.11-MariaDB-0ubuntu0.24.04.2
-- PHP 版本： 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `D1256977`
--

-- --------------------------------------------------------

--
-- 資料表結構 `Cart`
--

CREATE TABLE `Cart` (
  `MemberID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `CartItem`
--

CREATE TABLE `CartItem` (
  `MemberID` int(11) NOT NULL,
  `OptionID` int(11) NOT NULL,
  `Quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `Member`
--

CREATE TABLE `Member` (
  `MemberID` int(11) NOT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Password` varchar(100) DEFAULT NULL,
  `Name` varchar(100) DEFAULT NULL,
  `City` varchar(255) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `Phone` varchar(50) DEFAULT NULL,
  `IsAdmin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `Options`
--

CREATE TABLE `Options` (
  `OptionID` int(11) NOT NULL,
  `ProductID` int(11) DEFAULT NULL,
  `Color` varchar(100) DEFAULT NULL,
  `Size` int(11) DEFAULT NULL,
  `Price` int(11) DEFAULT NULL,
  `Stock` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `OrderItem`
--

CREATE TABLE `OrderItem` (
  `OrderID` int(11) NOT NULL,
  `OptionID` int(11) NOT NULL,
  `Quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `Orders`
--

CREATE TABLE `Orders` (
  `OrderID` int(11) NOT NULL,
  `MembersID` int(11) DEFAULT NULL,
  `OrderDate` date DEFAULT NULL,
  `Status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `Product`
--

CREATE TABLE `Product` (
  `ProductID` int(11) NOT NULL,
  `Type` varchar(100) DEFAULT NULL,
  `ProductName` varchar(100) DEFAULT NULL,
  `Introdution` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `Receipt`
--

CREATE TABLE `Receipt` (
  `ReceiptID` int(11) NOT NULL,
  `MemberID` int(11) DEFAULT NULL,
  `OrderID` int(11) DEFAULT NULL,
  `PaymentMethod` varchar(50) DEFAULT NULL,
  `PaymentDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `ReceiptItem`
--

CREATE TABLE `ReceiptItem` (
  `ReceiptID` int(11) NOT NULL,
  `OptionID` int(11) NOT NULL,
  `WarrantyID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `Repairs`
--

CREATE TABLE `Repairs` (
  `RepairID` int(11) NOT NULL,
  `WarrantyID` int(11) DEFAULT NULL,
  `RepairDate` date DEFAULT NULL,
  `RepairStatus` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `Warranty`
--

CREATE TABLE `Warranty` (
  `WarrantyID` int(11) NOT NULL,
  `WarrantyDate` tinyint(4) DEFAULT NULL,
  `WarrantyStatus` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `Cart`
--
ALTER TABLE `Cart`
  ADD PRIMARY KEY (`MemberID`);

--
-- 資料表索引 `CartItem`
--
ALTER TABLE `CartItem`
  ADD PRIMARY KEY (`MemberID`,`OptionID`),
  ADD KEY `OptionID` (`OptionID`);

--
-- 資料表索引 `Member`
--
ALTER TABLE `Member`
  ADD PRIMARY KEY (`MemberID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- 資料表索引 `Options`
--
ALTER TABLE `Options`
  ADD PRIMARY KEY (`OptionID`),
  ADD KEY `ProductID` (`ProductID`);

--
-- 資料表索引 `OrderItem`
--
ALTER TABLE `OrderItem`
  ADD PRIMARY KEY (`OrderID`,`OptionID`),
  ADD KEY `OptionID` (`OptionID`);

--
-- 資料表索引 `Orders`
--
ALTER TABLE `Orders`
  ADD PRIMARY KEY (`OrderID`),
  ADD KEY `MembersID` (`MembersID`);

--
-- 資料表索引 `Product`
--
ALTER TABLE `Product`
  ADD PRIMARY KEY (`ProductID`);

--
-- 資料表索引 `Receipt`
--
ALTER TABLE `Receipt`
  ADD PRIMARY KEY (`ReceiptID`),
  ADD KEY `OrderID` (`OrderID`),
  ADD KEY `MemberID` (`MemberID`);

--
-- 資料表索引 `ReceiptItem`
--
ALTER TABLE `ReceiptItem`
  ADD PRIMARY KEY (`ReceiptID`,`OptionID`),
  ADD KEY `OptionID` (`OptionID`),
  ADD KEY `WarrantyID` (`WarrantyID`);

--
-- 資料表索引 `Repairs`
--
ALTER TABLE `Repairs`
  ADD PRIMARY KEY (`RepairID`),
  ADD KEY `WarrantyID` (`WarrantyID`);

--
-- 資料表索引 `Warranty`
--
ALTER TABLE `Warranty`
  ADD PRIMARY KEY (`WarrantyID`);

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `Cart`
--
ALTER TABLE `Cart`
  ADD CONSTRAINT `Cart_ibfk_1` FOREIGN KEY (`MemberID`) REFERENCES `Member` (`MemberID`);

--
-- 資料表的限制式 `CartItem`
--
ALTER TABLE `CartItem`
  ADD CONSTRAINT `CartItem_ibfk_1` FOREIGN KEY (`MemberID`) REFERENCES `Member` (`MemberID`),
  ADD CONSTRAINT `CartItem_ibfk_2` FOREIGN KEY (`OptionID`) REFERENCES `Options` (`OptionID`);

--
-- 資料表的限制式 `Options`
--
ALTER TABLE `Options`
  ADD CONSTRAINT `Options_ibfk_1` FOREIGN KEY (`ProductID`) REFERENCES `Product` (`ProductID`);

--
-- 資料表的限制式 `OrderItem`
--
ALTER TABLE `OrderItem`
  ADD CONSTRAINT `OrderItem_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `Orders` (`OrderID`),
  ADD CONSTRAINT `OrderItem_ibfk_2` FOREIGN KEY (`OptionID`) REFERENCES `Options` (`OptionID`);

--
-- 資料表的限制式 `Orders`
--
ALTER TABLE `Orders`
  ADD CONSTRAINT `Orders_ibfk_1` FOREIGN KEY (`MembersID`) REFERENCES `Member` (`MemberID`);

--
-- 資料表的限制式 `Receipt`
--
ALTER TABLE `Receipt`
  ADD CONSTRAINT `MemberID` FOREIGN KEY (`MemberID`) REFERENCES `Member` (`MemberID`),
  ADD CONSTRAINT `Receipt_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `Orders` (`OrderID`);

--
-- 資料表的限制式 `ReceiptItem`
--
ALTER TABLE `ReceiptItem`
  ADD CONSTRAINT `ReceiptItem_ibfk_1` FOREIGN KEY (`ReceiptID`) REFERENCES `Receipt` (`ReceiptID`),
  ADD CONSTRAINT `ReceiptItem_ibfk_2` FOREIGN KEY (`OptionID`) REFERENCES `Options` (`OptionID`),
  ADD CONSTRAINT `ReceiptItem_ibfk_3` FOREIGN KEY (`WarrantyID`) REFERENCES `Warranty` (`WarrantyID`);

--
-- 資料表的限制式 `Repairs`
--
ALTER TABLE `Repairs`
  ADD CONSTRAINT `Repairs_ibfk_1` FOREIGN KEY (`WarrantyID`) REFERENCES `Warranty` (`WarrantyID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
