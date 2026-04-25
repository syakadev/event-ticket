CREATE TABLE IF NOT EXISTS `metode_pembayaran` (
  `id_metode` int NOT NULL AUTO_INCREMENT,
  `jenis` enum('Bank','E-Wallet','QRIS') NOT NULL,
  `nama_penyedia` varchar(100) NOT NULL,
  `nomor_akun` varchar(100) NOT NULL,
  `nama_bisnis` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_metode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `pembayaran` (
  `id_pembayaran` int NOT NULL AUTO_INCREMENT,
  `id_order` int NOT NULL,
  `id_metode` int NOT NULL,
  `bukti_pembayaran` varchar(255) NOT NULL,
  `waktu_bayar` datetime DEFAULT CURRENT_TIMESTAMP,
  `status_verifikasi` enum('menunggu','terverifikasi','ditolak') DEFAULT 'menunggu',
  PRIMARY KEY (`id_pembayaran`),
  KEY `id_order` (`id_order`),
  KEY `id_metode` (`id_metode`),
  CONSTRAINT `fk_pembayaran_order` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`) ON DELETE CASCADE,
  CONSTRAINT `fk_pembayaran_metode` FOREIGN KEY (`id_metode`) REFERENCES `metode_pembayaran` (`id_metode`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
