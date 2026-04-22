-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 20, 2026 at 01:38 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `event_tiket`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendee`
--

CREATE TABLE `attendee` (
  `id_attendee` int NOT NULL,
  `id_detail` int DEFAULT NULL,
  `kode_tiket` varchar(50) NOT NULL,
  `status_checkin` enum('belum','sudah') DEFAULT 'belum',
  `waktu_checkin` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `attendee`
--

INSERT INTO `attendee` (`id_attendee`, `id_detail`, `kode_tiket`, `status_checkin`, `waktu_checkin`) VALUES
(1, 1, 'TIX-2AA721F9A1', 'belum', NULL),
(2, 2, 'TIX-CEBD8BEF16', 'belum', NULL),
(3, 3, 'TIX-04F55BA3FF', 'sudah', '2026-04-16 12:49:16'),
(4, 4, 'TIX-5BB9DDE1DC', 'belum', NULL),
(5, 5, 'TIX-37AE9239E3', 'belum', NULL),
(6, 6, 'TIX-33A22ACDA7', 'belum', NULL),
(7, 7, 'TIX-5D50114AC7', 'belum', NULL),
(8, 8, 'TIX-F20EE75B43', 'belum', NULL),
(9, 9, 'TIX-424E0AF448', 'belum', NULL),
(10, 10, 'TIX-78C4E8778F', 'belum', NULL),
(11, 11, 'TIX-36EF491300', 'belum', NULL),
(12, 12, 'TIX-C823DDF0C9', 'belum', NULL),
(13, 13, 'TIX-8EB9A11974', 'belum', NULL),
(14, 14, 'TIX-7FA92F516E', 'belum', NULL),
(15, 15, 'TIX-C5919339D3', 'belum', NULL),
(16, 16, 'TIX-645B9EF82F', 'belum', NULL),
(17, 17, 'TIX-7E29D8517B', 'belum', NULL),
(18, 18, 'TIX-163BD5E75B', 'belum', NULL),
(19, 19, 'TIX-99136AC44F', 'belum', NULL),
(20, 20, 'TIX-D14ACC7F9F', 'belum', NULL),
(21, 21, 'TIX-181D775094', 'belum', NULL),
(22, 22, 'TIX-BC9CCBBE75', 'belum', NULL),
(23, 23, 'TIX-D532A1AA70', 'belum', NULL),
(24, 24, 'TIX-2C8AB92FB3', 'belum', NULL),
(25, 25, 'TIX-A13B132933', 'belum', NULL),
(26, 26, 'TIX-12D98882BB', 'belum', NULL),
(27, 27, 'TIX-B4BBB4B287', 'belum', NULL),
(28, 28, 'TIX-55FFF39131', 'belum', NULL),
(29, 29, 'TIX-46FACCC8AF', 'belum', NULL),
(30, 30, 'TIX-E04B8C3B7E', 'sudah', '2026-04-19 21:51:36'),
(31, 31, 'TIX-40286003FC', 'belum', NULL),
(32, 32, 'TIX-78A723848C', 'belum', NULL),
(33, 33, 'TIX-217270516D', 'belum', NULL),
(34, 34, 'TIX-76E6C161ED', 'belum', NULL),
(35, 35, 'TIX-367A9AD44C', 'belum', NULL),
(36, 36, 'TIX-B38B0C9BE9', 'belum', NULL),
(37, 36, 'TIX-9B6AB963B5', 'belum', NULL),
(38, 36, 'TIX-69FFED90A8', 'belum', NULL),
(39, 36, 'TIX-DF3D8FE2F0', 'belum', NULL),
(40, 37, 'TIX-A24B1E50A8', 'belum', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `id_event` int NOT NULL,
  `nama_event` varchar(150) NOT NULL,
  `tanggal` date NOT NULL,
  `id_venue` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `event`
--

INSERT INTO `event` (`id_event`, `nama_event`, `tanggal`, `id_venue`) VALUES
(1, 'Tournament epep hadiah 1M', '2026-04-16', 6);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id_order` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `tanggal_order` datetime DEFAULT CURRENT_TIMESTAMP,
  `total` int NOT NULL,
  `status` enum('pending','paid','cancel') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'pending',
  `id_voucher` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id_order`, `id_user`, `tanggal_order`, `total`, `status`, `id_voucher`) VALUES
(1, 5, '2026-04-16 08:20:42', 25000, 'paid', NULL),
(2, 5, '2026-04-16 08:25:30', 25000, 'paid', NULL),
(3, 5, '2026-04-16 08:26:27', 25000, 'paid', NULL),
(4, 5, '2026-04-16 12:48:49', 25000, 'paid', NULL),
(5, 5, '2026-04-16 12:49:50', 25000, 'paid', NULL),
(6, 5, '2026-04-16 12:55:04', 25000, 'paid', NULL),
(7, 5, '2026-04-16 13:16:34', 15000, 'paid', 1),
(8, 5, '2026-04-16 13:40:43', 25000, 'paid', NULL),
(9, 5, '2026-04-16 13:49:10', 25000, 'cancel', NULL),
(10, 5, '2026-04-16 13:51:44', 25000, 'paid', NULL),
(11, 5, '2026-04-16 13:54:39', 25000, 'cancel', NULL),
(12, 5, '2026-04-16 13:55:07', 25000, 'cancel', NULL),
(13, 5, '2026-04-16 13:56:27', 25000, 'cancel', NULL),
(14, 5, '2026-04-16 13:58:03', 25000, 'cancel', NULL),
(15, 5, '2026-04-16 13:58:34', 25000, 'cancel', NULL),
(16, 5, '2026-04-16 14:00:59', 25000, 'cancel', NULL),
(17, 5, '2026-04-16 14:03:43', 25000, 'cancel', NULL),
(18, 5, '2026-04-16 14:07:18', 25000, 'cancel', NULL),
(19, 5, '2026-04-16 14:08:58', 25000, 'cancel', NULL),
(20, 5, '2026-04-16 14:10:06', 25000, 'cancel', NULL),
(21, 5, '2026-04-16 14:12:13', 25000, 'cancel', NULL),
(22, 5, '2026-04-16 14:15:49', 25000, 'cancel', NULL),
(23, 5, '2026-04-16 14:34:10', 25000, 'cancel', NULL),
(24, 5, '2026-04-16 14:39:09', 25000, 'cancel', NULL),
(25, 5, '2026-04-16 14:41:53', 25000, 'cancel', NULL),
(26, 5, '2026-04-16 14:42:18', 25000, 'cancel', NULL),
(27, 5, '2026-04-16 14:43:54', 25000, 'cancel', NULL),
(28, 5, '2026-04-17 09:50:43', 25000, 'cancel', NULL),
(29, 5, '2026-04-17 09:51:45', 25000, 'cancel', NULL),
(30, 5, '2026-04-19 20:58:19', 25000, 'paid', NULL),
(31, 5, '2026-04-19 22:03:12', 25000, 'paid', NULL),
(32, 5, '2026-04-19 22:03:28', 25000, 'pending', NULL),
(33, 5, '2026-04-19 22:08:25', 25000, 'pending', NULL),
(34, 5, '2026-04-19 22:14:49', 25000, 'paid', NULL),
(35, 5, '2026-04-19 22:18:48', 25000, 'pending', NULL),
(36, 5, '2026-04-19 22:21:39', 100000, 'cancel', NULL),
(37, 5, '2026-04-20 08:33:53', 25000, 'pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_detail`
--

CREATE TABLE `order_detail` (
  `id_detail` int NOT NULL,
  `id_order` int DEFAULT NULL,
  `id_tiket` int DEFAULT NULL,
  `qty` int NOT NULL,
  `subtotal` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_detail`
--

INSERT INTO `order_detail` (`id_detail`, `id_order`, `id_tiket`, `qty`, `subtotal`) VALUES
(1, 1, 1, 1, 25000),
(2, 2, 1, 1, 25000),
(3, 3, 1, 1, 25000),
(4, 4, 1, 1, 25000),
(5, 5, 1, 1, 25000),
(6, 6, 1, 1, 25000),
(7, 7, 1, 1, 25000),
(8, 8, 1, 1, 25000),
(9, 9, 1, 1, 25000),
(10, 10, 1, 1, 25000),
(11, 11, 1, 1, 25000),
(12, 12, 1, 1, 25000),
(13, 13, 1, 1, 25000),
(14, 14, 1, 1, 25000),
(15, 15, 1, 1, 25000),
(16, 16, 1, 1, 25000),
(17, 17, 1, 1, 25000),
(18, 18, 1, 1, 25000),
(19, 19, 1, 1, 25000),
(20, 20, 1, 1, 25000),
(21, 21, 1, 1, 25000),
(22, 22, 1, 1, 25000),
(23, 23, 1, 1, 25000),
(24, 24, 1, 1, 25000),
(25, 25, 1, 1, 25000),
(26, 26, 1, 1, 25000),
(27, 27, 1, 1, 25000),
(28, 28, 1, 1, 25000),
(29, 29, 1, 1, 25000),
(30, 30, 1, 1, 25000),
(31, 31, 1, 1, 25000),
(32, 32, 1, 1, 25000),
(33, 33, 1, 1, 25000),
(34, 34, 1, 1, 25000),
(35, 35, 1, 1, 25000),
(36, 36, 1, 4, 100000),
(37, 37, 1, 1, 25000);

-- --------------------------------------------------------

--
-- Table structure for table `tiket`
--

CREATE TABLE `tiket` (
  `id_tiket` int NOT NULL,
  `id_event` int DEFAULT NULL,
  `nama_tiket` varchar(50) NOT NULL,
  `harga` int NOT NULL,
  `kuota` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tiket`
--

INSERT INTO `tiket` (`id_tiket`, `id_event`, `nama_tiket`, `harga`, `kuota`) VALUES
(1, 1, 'TOURNAMENT EPEP', 25000, 20);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','petugas','admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama`, `email`, `password`, `role`) VALUES
(4, 'Syaka Dev', 'syaka@gmail.com', '$2y$10$.RGC.QFgYZbjEcHqBTE4UOCV34eHLiZPUDzuqWq1g48S1y5BJZ5L.', 'admin'),
(5, 'Budi Santoso', 'budi@gmail.com', '$2y$10$.RGC.QFgYZbjEcHqBTE4UOCV34eHLiZPUDzuqWq1g48S1y5BJZ5L.', 'user'),
(6, 'Siti Aminah', 'siti@gmail.com', '$2y$10$.RGC.QFgYZbjEcHqBTE4UOCV34eHLiZPUDzuqWq1g48S1y5BJZ5L.', 'user'),
(7, 'Andi Wijaya', 'andi@gmail.com', '$2y$10$.RGC.QFgYZbjEcHqBTE4UOCV34eHLiZPUDzuqWq1g48S1y5BJZ5L.', 'user');

-- --------------------------------------------------------

--
-- Table structure for table `venue`
--

CREATE TABLE `venue` (
  `id_venue` int NOT NULL,
  `nama_venue` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `kapasitas` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `venue`
--

INSERT INTO `venue` (`id_venue`, `nama_venue`, `alamat`, `kapasitas`) VALUES
(6, 'The Alon Alon epep', 'Kota Magelang', 10);

-- --------------------------------------------------------

--
-- Table structure for table `voucher`
--

CREATE TABLE `voucher` (
  `id_voucher` int NOT NULL,
  `kode_voucher` varchar(20) NOT NULL,
  `potongan` int NOT NULL,
  `kuota` int NOT NULL,
  `status` enum('aktif','nonaktif') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `voucher`
--

INSERT INTO `voucher` (`id_voucher`, `kode_voucher`, `potongan`, `kuota`, `status`) VALUES
(1, 'SYAK1010', 10000, 99, 'aktif');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendee`
--
ALTER TABLE `attendee`
  ADD PRIMARY KEY (`id_attendee`),
  ADD UNIQUE KEY `kode_tiket` (`kode_tiket`),
  ADD KEY `id_detail` (`id_detail`);

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`id_event`),
  ADD KEY `id_venue` (`id_venue`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id_order`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_voucher` (`id_voucher`);

--
-- Indexes for table `order_detail`
--
ALTER TABLE `order_detail`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_order` (`id_order`),
  ADD KEY `id_tiket` (`id_tiket`);

--
-- Indexes for table `tiket`
--
ALTER TABLE `tiket`
  ADD PRIMARY KEY (`id_tiket`),
  ADD KEY `id_event` (`id_event`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `venue`
--
ALTER TABLE `venue`
  ADD PRIMARY KEY (`id_venue`);

--
-- Indexes for table `voucher`
--
ALTER TABLE `voucher`
  ADD PRIMARY KEY (`id_voucher`),
  ADD UNIQUE KEY `kode_voucher` (`kode_voucher`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendee`
--
ALTER TABLE `attendee`
  MODIFY `id_attendee` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
  MODIFY `id_event` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id_order` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `order_detail`
--
ALTER TABLE `order_detail`
  MODIFY `id_detail` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `tiket`
--
ALTER TABLE `tiket`
  MODIFY `id_tiket` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `venue`
--
ALTER TABLE `venue`
  MODIFY `id_venue` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `voucher`
--
ALTER TABLE `voucher`
  MODIFY `id_voucher` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendee`
--
ALTER TABLE `attendee`
  ADD CONSTRAINT `attendee_ibfk_1` FOREIGN KEY (`id_detail`) REFERENCES `order_detail` (`id_detail`) ON DELETE CASCADE;

--
-- Constraints for table `event`
--
ALTER TABLE `event`
  ADD CONSTRAINT `event_ibfk_1` FOREIGN KEY (`id_venue`) REFERENCES `venue` (`id_venue`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`id_voucher`) REFERENCES `voucher` (`id_voucher`);

--
-- Constraints for table `order_detail`
--
ALTER TABLE `order_detail`
  ADD CONSTRAINT `order_detail_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_detail_ibfk_2` FOREIGN KEY (`id_tiket`) REFERENCES `tiket` (`id_tiket`);

--
-- Constraints for table `tiket`
--
ALTER TABLE `tiket`
  ADD CONSTRAINT `tiket_ibfk_1` FOREIGN KEY (`id_event`) REFERENCES `event` (`id_event`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
