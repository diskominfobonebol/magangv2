-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: kominfo_aset
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `aset_peralatan_mesin`
--

DROP TABLE IF EXISTS `aset_peralatan_mesin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aset_peralatan_mesin` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `no_reg_pemda` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_reg_kominfo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `penanggung_jawab` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Belum Ditentukan',
  `jenis_barang` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `merek_tipe` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tahun` int DEFAULT NULL,
  `harga_perolehan` bigint unsigned NOT NULL DEFAULT '0',
  `no_rangka_seri` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_mesin` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_polisi` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_bpkb` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kondisi` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Baik',
  `keterangan_lokasi_unit` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_sk_bast` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_mulai_pinjam` date DEFAULT NULL,
  `tgl_selesai_pinjam` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aset_peralatan_mesin`
--

LOCK TABLES `aset_peralatan_mesin` WRITE;
/*!40000 ALTER TABLE `aset_peralatan_mesin` DISABLE KEYS */;
INSERT INTO `aset_peralatan_mesin` VALUES (1,'REG-2024-001',NULL,'Dinas Kominfo','Proyektor Epson EB-X400','Epson EB-X400',2024,8500000,'EPSON-X400-8812','-','-','-','Baik','Bidang E-Government','SK/001/KOMINFO/2024','2026-01-12','2027-01-12','2026-09-13 21:26:06','2026-09-13 21:26:06'),(2,'REG-2024-002',NULL,'Bagian Umum','Toyota Innova Reborn 2.0 V','Toyota Innova',2023,380000000,'MHFK1111223344','1TR-FE-998822','DM 1024 AB','BPKB-8827361','Baik','Garasi Dinas Kominfo','SK/002/KOMINFO/2024','2026-02-01','2026-08-01','2026-09-13 21:26:06','2026-09-13 21:26:06'),(3,'REG-2024-003',NULL,'Bidang Aptika','Modem Portable Orbit Star H1','Telkomsel Orbit H1',2024,650000,'SN-ORB991823','-','-','-','Baik','Ruang Lab Aptika','SK/003/KOMINFO/2024',NULL,NULL,'2026-09-13 21:26:06','2026-09-13 21:26:06'),(4,'REG-2026-001','KOMINFO-2026-0101','Rizal Pratama, S.Kom','Laptop','Lenovo ThinkPad X1 Carbon Gen 11',2026,32000000,'SN-LNV-X1-2026-00191',NULL,NULL,NULL,'Baik','Bidang Pengelolaan E-Government, Lantai 2','SK/TI/001/DISKOMINFO/2026',NULL,NULL,'2026-09-15 00:27:30','2026-09-15 00:27:30'),(5,'REG-2026-002','KOMINFO-2026-0102','Fikram Nur, M.T.','Router','MikroTik CCR2216-1G-12XS-2XQ',2025,42000000,'MT-CCR2216-2025-99012',NULL,NULL,NULL,'Baik','Ruang Server NOC, Gedung Diskominfo','SK/NET/002/DISKOMINFO/2025',NULL,NULL,'2026-09-15 00:27:30','2026-09-15 00:27:30'),(6,'REG-2026-003',NULL,'Drs. H. Mulyadi R.','Mobil Dinas','Toyota Innova Zenix 2.0 V CVT',2025,495000000,'MHFXA31E7P2098234','M20A-FKS-99123','DM 3723 AB','BPKB-G-9919231','Baik','Sekretariat Diskominfo, Garasi Dinas','BAST/KND/003/DISKOMINFO/2025',NULL,NULL,'2026-09-15 00:27:30','2026-09-15 00:27:30'),(7,'REG-2026-004','KOMINFO-2026-0104','Siti Rahmawati, S.AP','Proyektor','Epson EB-FH52 4000 Lumens',2024,18500000,'EPS-FH52-2024-00912',NULL,NULL,NULL,'Baik','Ruang Rapat Utama, Lantai 1 Diskominfo','BAST/AV/004/DISKOMINFO/2024',NULL,NULL,'2026-09-15 00:27:30','2026-09-15 00:27:30'),(8,'REG-2026-005',NULL,'Ir. Hendra Wijaya','Server','Dell PowerEdge R750 2U Rack Server',2024,175000000,'DELL-R750-2024-110045',NULL,NULL,NULL,'Baik','Data Center Utama Diskominfo Bone Bolango','SK/SRV/005/DISKOMINFO/2024',NULL,NULL,'2026-09-15 00:27:30','2026-09-15 00:27:30'),(9,'REG-2026-006','KOMINFO-2026-0106','Nurlaila Isa, A.Md','Printer','HP LaserJet Pro M404dn',2024,6800000,'HP-M404DN-2024-44123',NULL,NULL,NULL,'Baik','Subbagian Umum & Kepegawaian, Lantai 1','BAST/PRN/006/DISKOMINFO/2024',NULL,NULL,'2026-09-15 00:27:30','2026-09-15 00:27:30'),(10,'REG-2026-007',NULL,'Bayu Krisna, S.I.Kom','Kamera','Canon EOS R6 Mark II Mirrorless',2025,41500000,'CANON-R6M2-2025-550291',NULL,NULL,NULL,'Baik','Bidang Informasi & Komunikasi Publik (IKP)','BAST/IKP/007/DISKOMINFO/2025',NULL,NULL,'2026-09-15 00:27:30','2026-09-15 00:27:30'),(11,'REG-2026-008','KOMINFO-2026-0108','Rizal Pratama, S.Kom','AC Split','Daikin Inverter FTKF35EVM 1.5 PK',2025,9500000,'DKN-FTKF35-2025-00821',NULL,NULL,NULL,'Baik','Ruang Command Center, Lantai 3 Diskominfo','BAST/AHU/008/DISKOMINFO/2025',NULL,NULL,'2026-09-15 00:27:30','2026-09-15 00:27:30'),(12,'REG-2026-009','KOMINFO-2026-0109','Aditya Firmansyah','Laptop','Acer Aspire 5 A515-56G Intel Core i5',2022,12500000,'ACR-A515-2022-88204',NULL,NULL,NULL,'Rusak Ringan','Subbagian Tata Usaha (layar retak, masih berfungsi)','BAST/TI/009/DISKOMINFO/2022',NULL,NULL,'2026-09-15 00:27:30','2026-09-15 00:27:30'),(13,'REG-2026-010',NULL,'Siti Rahmawati, S.AP','Printer','Canon PIXMA G2010 Ink Tank',2021,3200000,'CNPX-G2010-2021-10294',NULL,NULL,NULL,'Rusak Ringan','Ruang Rapat Lantai 1 (paper-jam intermiten)','BAST/PRN/010/DISKOMINFO/2021',NULL,NULL,'2026-09-15 00:27:30','2026-09-15 00:27:30'),(14,'REG-2026-011','KOMINFO-2026-0111','Ir. Hendra Wijaya','Switch Manageable','Cisco Catalyst 2960-X 24-Port',2020,28000000,'CSC-C2960X-2020-77130',NULL,NULL,NULL,'Rusak Ringan','Ruang Server NOC (2 port mati, sisanya normal)','SK/NET/011/DISKOMINFO/2020',NULL,NULL,'2026-09-15 00:27:30','2026-09-15 00:27:30'),(15,'REG-2026-012',NULL,'Aditya Firmansyah','Mesin Penghancur Kertas','Ideal 2604 Cross Cut Heavy-Duty',2021,18900000,'IDL-2604-2021-00392',NULL,NULL,NULL,'Rusak Ringan','Subbagian Tata Usaha & Kearsipan (pisau tumpul)','BAST/MK/012/DISKOMINFO/2021',NULL,NULL,'2026-09-15 00:27:30','2026-09-15 00:27:30'),(16,'REG-2026-013','KOMINFO-2026-0113','Nurlaila Isa, A.Md','UPS','APC Back-UPS Pro 1500VA BR1500G',2022,7500000,'APC-BR1500-2022-33091',NULL,NULL,NULL,'Rusak Ringan','Ruang Kepala Bidang (baterai melemah)','BAST/EL/013/DISKOMINFO/2022',NULL,NULL,'2026-09-15 00:27:30','2026-09-15 00:27:30'),(17,'REG-2026-014',NULL,'Rizal Pratama, S.Kom','Laptop','Dell Latitude 5480 Intel Core i7',2019,18000000,'DELL-L5480-2019-50182',NULL,NULL,NULL,'Rusak Berat','Gudang Penyimpanan Aset Tidak Layak Pakai','BAST/TI/014/DISKOMINFO/2019',NULL,NULL,'2026-09-15 00:27:30','2026-09-15 00:27:30'),(18,'REG-2026-015','KOMINFO-2026-0115','Drs. H. Mulyadi R.','Mobil Dinas','Daihatsu Gran Max Pick Up 1.5 D',2017,145000000,'MHKV5EA1J7K119283','DE-VR-882901','DM 8800 ZZ','BPKB-D-007123','Rusak Berat','Bengkel Dinas Kabupaten (perbaikan besar)','BAST/KND/015/DISKOMINFO/2017',NULL,NULL,'2026-09-15 00:27:30','2026-09-15 00:27:30'),(19,'REG-2026-016',NULL,'Fikram Nur, M.T.','Server','HP ProLiant DL180 Gen 9 (End-of-Life)',2018,98000000,'HP-DL180G9-2018-28819',NULL,NULL,NULL,'Rusak Berat','Gudang Penyimpanan Aset Tidak Layak Pakai','SK/SRV/016/DISKOMINFO/2018',NULL,NULL,'2026-09-15 00:27:30','2026-09-15 00:27:30'),(20,'REG-2026-017','KOMINFO-2026-0117','Siti Rahmawati, S.AP','Proyektor','BenQ MX503 XGA DLP Projector',2016,9800000,'BNQ-MX503-2016-10192',NULL,NULL,NULL,'Rusak Berat','Gudang Penyimpanan Aset Tidak Layak Pakai','BAST/AV/017/DISKOMINFO/2016',NULL,NULL,'2026-09-15 00:27:30','2026-09-15 00:27:30'),(21,'REG-2026-018',NULL,'Bayu Krisna, S.I.Kom','Modem','Huawei B525s-23a 4G LTE Router',2022,3800000,'HW-B525-2022-55012',NULL,NULL,NULL,'Hilang','Tidak diketahui (hilang saat peminjaman luar kantor 2023)','BAST/NET/018/DISKOMINFO/2022',NULL,NULL,'2026-09-15 00:27:30','2026-09-15 00:27:30'),(22,'REG-2026-019','KOMINFO-2026-0119','Aditya Firmansyah','Laptop','Asus VivoBook S14 S430FA Intel i5',2021,10500000,'ASUS-S430FA-2021-77293',NULL,NULL,NULL,'Hilang','Tidak diketahui (dilaporkan hilang Maret 2024)','BAST/TI/019/DISKOMINFO/2021',NULL,NULL,'2026-09-15 00:27:30','2026-09-15 00:27:30'),(23,'REG-2026-020',NULL,'Nurlaila Isa, A.Md','Sound System','Bose S1 Pro System Portable Speaker',2023,13800000,'BOSE-S1P-2023-91204',NULL,NULL,NULL,'Hilang','Tidak diketahui (dalam pelacakan Tim Pengelola Aset)','SK/SND/020/DISKOMINFO/2023',NULL,NULL,'2026-09-15 00:27:30','2026-09-15 00:27:30');
/*!40000 ALTER TABLE `aset_peralatan_mesin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_user_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_job_table',1),(4,'2026_08_31_041303_create_aset_peralatan_mesin_table',1),(5,'2026_08_31_041330_create_pendaftaran_magang_table',1),(6,'2026_08_31_041338_create_surat_balasan_magang_table',1),(7,'2026_08_31_041344_create_peminjaman_aset_table',1),(8,'2026_09_07_000001_add_admin_master_to_roles',1),(9,'2026_09_08_000001_update_roles_to_three_roles',1),(10,'2026_09_08_000002_clean_orphaned_peminjaman_data',1),(11,'2026_09_08_000003_drop_peminjaman_aset_table',1),(12,'2026_09_08_000004_update_status_ketersediaan_options',1),(13,'2026_09_08_000005_drop_status_ketersediaan_column',1),(14,'2026_09_10_000001_enhance_users_management',1),(15,'2026_09_11_000001_add_no_reg_kominfo_to_aset_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
INSERT INTO `password_reset_tokens` VALUES ('nazwaalhabsygtlo@gmail.com','$2y$12$8pex/z80yS/bXX2LoA6XF.KnD8k/L/WBcktmHwv0aCe42kutabnO2','2026-09-15 07:03:04');
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pendaftaran_magang`
--

DROP TABLE IF EXISTS `pendaftaran_magang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pendaftaran_magang` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `nim` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `universitas` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jurusan` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_hp` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_mulai` date NOT NULL,
  `tgl_selesai` date NOT NULL,
  `surat_pengantar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Menunggu',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pendaftaran_magang_user_id_foreign` (`user_id`),
  CONSTRAINT `pendaftaran_magang_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pendaftaran_magang`
--

LOCK TABLES `pendaftaran_magang` WRITE;
/*!40000 ALTER TABLE `pendaftaran_magang` DISABLE KEYS */;
/*!40000 ALTER TABLE `pendaftaran_magang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('QMz84bVzEzbnJPoOWE1oTu0JXV0QyBT7fOvRvboe',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoieFV5d25hakdUdXVIbW9td2syem43RTBWb3hhTWtmQXJlR09NeVJUcCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9hc2V0P2FzZXRfcGFnZT0yIjtzOjU6InJvdXRlIjtzOjEwOiJhZG1pbi5hc2V0Ijt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9',1789954541);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `surat_balasan_magang`
--

DROP TABLE IF EXISTS `surat_balasan_magang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `surat_balasan_magang` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pendaftaran_id` bigint unsigned NOT NULL,
  `no_surat` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_surat` date DEFAULT NULL,
  `file_surat_balasan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `catatan_admin` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `surat_balasan_magang_pendaftaran_id_foreign` (`pendaftaran_id`),
  CONSTRAINT `surat_balasan_magang_pendaftaran_id_foreign` FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran_magang` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `surat_balasan_magang`
--

LOCK TABLES `surat_balasan_magang` WRITE;
/*!40000 ALTER TABLE `surat_balasan_magang` DISABLE KEYS */;
/*!40000 ALTER TABLE `surat_balasan_magang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','admin_kasubag','bendahara_barang','pegawai','mahasiswa') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mahasiswa',
  `instansi_bidang` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Administrator Kominfo','admin@kominfo.go.id',NULL,'$2y$12$x5GSKaY6/I7NJs0GENXJT.rDHtukVDlKfbBZeO0xCfanRuA32QwZS','admin','Diskominfo Bonebol',1,NULL,'2026-09-13 21:26:04','2026-09-13 21:26:04'),(2,'Bendahara Barang','bendahara@kominfo.go.id',NULL,'$2y$12$r/u25Yil7UFlvKN29MIxpeJtQBrOb1kuunjuabS98up77d7TQxNXi','bendahara_barang','Bidang Pengelolaan Aset Diskominfo',1,NULL,'2026-09-13 21:26:05','2026-09-13 21:26:05'),(3,'Mahasiswa Magang','mahasiswa@gmail.com',NULL,'$2y$12$3uFlPkHMU0N52pqmlhAn2u.C7sU0BadP0NNYPm/E.7gBnCoYgAVLu','mahasiswa','Universitas Negeri Gorontalo',1,NULL,'2026-09-13 21:26:06','2026-09-15 06:54:08');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-21 10:41:19
