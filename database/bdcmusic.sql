-- =====================================================
-- BDC Music Studio — Unified Schema v2
-- Designed for 6 services on a single bookings table
--
-- IMPORT: Drop existing DB and import this file
--   mysql -u root -p < database/bdcmusic.sql
-- =====================================================

DROP DATABASE IF EXISTS `bdcmusic`;
CREATE DATABASE `bdcmusic` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `bdcmusic`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ------------------------------------------------------
-- 1. USERS
-- ------------------------------------------------------

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(200) NOT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `role` enum('customer','admin') NOT NULL DEFAULT 'customer',
  `profile_picture` varchar(500) DEFAULT NULL,
  `source` varchar(50) DEFAULT 'website',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email` (`email`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------
-- 2. SERVICES  (lookup / catalog)
-- ------------------------------------------------------

DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `slug` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `services` (`slug`, `name`) VALUES
('artists-marketplace', 'BDC Artists Marketplace'),
('audio-video', 'Audio & Video Services'),
('online-offline-classes', 'Online/Offline Classes'),
('digital-distribution', 'Digital Music Distribution'),
('promotion', 'Promotion Services'),
('iprs', 'IPRS Services');

-- ------------------------------------------------------
-- 3. BOOKINGS  (unified — one row per order)
-- ------------------------------------------------------

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE `bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` varchar(40) NOT NULL,
  `invoice_no` varchar(30) DEFAULT NULL,
  `customer_id` varchar(20) DEFAULT NULL,
  `service_id` int NOT NULL,
  `meta` json DEFAULT NULL,
  `message` text,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `payment_status` enum('awaiting','paid','refunded','failed') NOT NULL DEFAULT 'awaiting',
  `payment_id` varchar(100) DEFAULT NULL,
  `razorpay_order_id` varchar(100) DEFAULT NULL,
  `auto_create_account` tinyint(1) NOT NULL DEFAULT 0,
  `auto_password` varchar(30) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `paid_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_booking_id` (`booking_id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_service` (`service_id`),
  KEY `idx_status` (`status`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `fk_bookings_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_bookings_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------
-- 4. UPLOADED_FILES
-- ------------------------------------------------------

DROP TABLE IF EXISTS `uploaded_files`;
CREATE TABLE `uploaded_files` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` varchar(40) NOT NULL,
  `field_name` varchar(100) DEFAULT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_size` int unsigned NOT NULL DEFAULT 0,
  `uploaded_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_booking_id` (`booking_id`),
  CONSTRAINT `fk_files_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------
-- SEED DATA
-- ------------------------------------------------------

INSERT INTO `users` (`id`, `name`, `email`, `mobile`, `password_hash`, `role`, `source`) VALUES
('CUST-A1B2C3D4', 'Rahul Sharma', 'rahul@example.com', '9876543210', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'website'),
('CUST-ADMIN0001', 'BDC Admin', 'admin@bdcmusic.in', '9999999999', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'website'),
('CUST-C9D0E1F2', 'Deepika Nair', 'deepika@example.com', '9119887766', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'guest_booking'),
('CUST-E5F6G7H8', 'Priya Patel', 'priya@example.com', '9988776655', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'website'),
('CUST-G3H4I5J6', 'Arjun Reddy', 'arjun@example.com', '9228776655', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'website'),
('CUST-I9J0K1L2', 'Amit Kumar', 'amit@example.com', '9112233445', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'guest_booking'),
('CUST-K7L8M9N0', 'Kavita Desai', 'kavita@example.com', '9337665544', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'website'),
('CUST-M3N4O5P6', 'Neha Gupta', 'neha@example.com', '9001122334', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'website'),
('CUST-Q7R8S9T0', 'Vikram Singh', 'vikram@example.com', '9871234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'guest_booking'),
('CUST-U1V2W3X4', 'Sonia Verma', 'sonia@example.com', '9911223344', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'website'),
('CUST-Y5Z6A7B8', 'Ravi Joshi', 'ravi@example.com', '9009887766', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'website');

-- Unified bookings (migrated from 4 old tables)
-- service_id mapping: 1=artists-marketplace, 2=audio-video, 3=online-offline-classes, 4=digital-distribution, 5=promotion, 6=iprs

INSERT INTO `bookings` (`booking_id`, `invoice_no`, `customer_id`, `service_id`, `meta`, `message`, `price`, `status`, `payment_status`, `payment_id`, `razorpay_order_id`, `created_at`, `paid_at`) VALUES
-- From old `bookings` table
('BDC-20260815-A1B2C3', 'INV-A1B2C3', 'CUST-A1B2C3D4', 1, '{"artist_goal":"Build my brand as an independent Hindi pop artist"}', 'Looking forward to the branding package.', 5000.00, 'delivered', 'paid', 'pay_demo_001', 'order_demo_001', '2026-08-15 10:35:00', '2026-08-15 10:40:00'),
('BDC-20260901-E5F6G7', 'INV-E5F6G7', 'CUST-E5F6G7H8', 2, '{"service_category":"Video","service_type":"Music Video","delivery_formats":["MP4","4K"],"deadline":"15 working days","budget":"₹25,000"}', 'Need a cinematic music video.', 12000.00, 'processing', 'paid', 'pay_demo_002', 'order_demo_002', '2026-09-01 09:00:00', '2026-09-01 09:05:00'),
('BDC-20260828-I9J0K1', 'INV-I9J0K1', 'CUST-I9J0K1L2', 2, '{"service_category":"Audio","service_type":"Recording","delivery_formats":["WAV","FLAC"],"deadline":"10 working days","budget":"₹5,000"}', 'Recording a 5-song EP.', 7000.00, 'shipped', 'paid', 'pay_demo_003', 'order_demo_003', '2026-08-28 14:20:00', '2026-08-28 14:25:00'),
('BDC-20260905-M3N4O5', 'INV-M3N4O5', 'CUST-M3N4O5P6', 3, '{"course":"Singing","level":"Intermediate","class_mode":"Online"}', 'Want to improve my classical vocals.', 3000.00, 'pending', 'awaiting', NULL, NULL, '2026-09-05 11:00:00', NULL),
('BDC-20260720-Q7R8S9', 'INV-Q7R8S9', 'CUST-Q7R8S9T0', 3, '{"course":"Music Production","level":"Beginner","class_mode":"Online"}', 'Learning music production from scratch.', 3000.00, 'delivered', 'paid', 'pay_demo_005', 'order_demo_005', '2026-07-20 16:05:00', '2026-07-20 16:10:00'),
('BDC-20260903-U1V2W3', 'INV-U1V2W3', 'CUST-U1V2W3X4', 2, '{"service_category":"Audio","service_type":"Recording","delivery_formats":["WAV","FLAC"],"deadline":"10 working days","budget":"₹7,000"}', 'Recording guitar tracks.', 7000.00, 'cancelled', 'refunded', 'pay_demo_006', 'order_demo_006', '2026-09-03 08:35:00', '2026-09-03 08:40:00'),
('BDC-20260810-Y5Z6A7', 'INV-Y5Z6A7', 'CUST-Y5Z6A7B8', 2, '{"service_category":"Video","service_type":"Reel / Promo","delivery_formats":["Social Media Reels"],"deadline":"7 working days","budget":"₹3,500"}', 'Need 5 Instagram reels.', 12000.00, 'delivered', 'paid', 'pay_demo_007', 'order_demo_007', '2026-08-10 13:15:00', '2026-08-10 13:20:00'),
('BDC-20260908-C9D0E1', 'INV-C9D0E1', 'CUST-C9D0E1F2', 1, '{"artist_goal":"Launch my debut album and get distribution deals"}', 'Ready for my album launch.', 5000.00, 'processing', 'paid', 'pay_demo_008', 'order_demo_008', '2026-09-08 10:05:00', '2026-09-08 10:10:00'),
-- From old `distribution_bookings`
('DIST-1724010', NULL, 'CUST-I9J0K1L2', 4, '{"release_type":"Single","artist_name":"Amit Kumar","release_title":"Dil Ki Awaaz","genre":"Pop","language":"Hindi","isrc":"Yes","upc":"No","copyright_help":"Yes","release_date":"2026-09-15"}', 'My debut single, please distribute worldwide.', 0.00, 'processing', 'awaiting', NULL, NULL, '2026-08-28 14:30:00', NULL),
('DIST-1724011', NULL, 'CUST-G3H4I5J6', 4, '{"release_type":"Album","artist_name":"Arjun Reddy","release_title":"Echoes of Soul","genre":"Rock","language":"English","isrc":"Yes","upc":"Yes","copyright_help":"No","release_date":"2026-10-01"}', '6-track album, all artwork attached.', 0.00, 'pending', 'awaiting', NULL, NULL, '2026-09-10 09:00:00', NULL),
('DIST-1724012', NULL, 'CUST-K7L8M9N0', 4, '{"release_type":"Single","artist_name":"Kavita Desai","release_title":"Monsoon Dreams","genre":"Folk","language":"Hindi","isrc":"No","upc":"No","copyright_help":"Yes","release_date":"2026-09-20"}', 'Independent folk release, need ISRC code.', 0.00, 'pending', 'awaiting', NULL, NULL, '2026-09-12 11:00:00', NULL),
-- From old `iprs_bookings`
('IPRS-1724020', NULL, 'CUST-U1V2W3X4', 6, '{"applicant_type":"composer","song_released":"yes","song_links":"https://open.spotify.com/track/example1","song_title":"Raat Ki Rani","artist_name":"Sonia Verma","account_holder":"Sonia Verma","account_number":"123456789012","ifsc":"SBIN0001234","bank_name":"State Bank of India","membership_type":"author-composer"}', 'Need IPRS registration for my compositions.', 0.00, 'cancelled', 'awaiting', NULL, NULL, '2026-09-03 08:45:00', NULL),
('IPRS-1724021', NULL, 'CUST-A1B2C3D4', 6, '{"applicant_type":"author-composer","song_released":"yes","song_links":"https://youtube.com/watch?v=example2","song_title":"Sapno Ka Safar","artist_name":"Rahul Sharma","account_holder":"Rahul Sharma","account_number":"987654321012","ifsc":"HDFC0001234","bank_name":"HDFC Bank","membership_type":"author-composer"}', 'Register my song and lyrics with IPRS.', 0.00, 'pending', 'awaiting', NULL, NULL, '2026-09-10 14:00:00', NULL),
('IPRS-1724022', NULL, 'CUST-K7L8M9N0', 6, '{"applicant_type":"publisher","song_released":"yes","song_links":"https://gaana.com/track/example3","song_title":"Bhakti Sagar","artist_name":"Kavita Devi","account_holder":"Kavita Desai","account_number":"567890123456","ifsc":"ICIC0005678","bank_name":"ICICI Bank","membership_type":"publisher"}', 'Publishing rights for devotional music catalog.', 0.00, 'pending', 'awaiting', NULL, NULL, '2026-09-12 12:00:00', NULL),
-- Neha's order (from old `bookings`)
('BDC-20260905-PROMO', NULL, 'CUST-M3N4O5P6', 5, '{"campaign_type":"Social Media","target_platform":"Instagram, YouTube","budget":"₹5,000"}', 'Promote my new single on social media.', 5000.00, 'pending', 'awaiting', NULL, NULL, '2026-09-05 12:00:00', NULL);

-- Migrated uploaded_files (now references booking_id directly)
INSERT INTO `uploaded_files` (`booking_id`, `field_name`, `original_name`, `stored_name`, `file_path`, `mime_type`, `file_size`, `uploaded_at`) VALUES
('BDC-20260901-E5F6G7', 'project_upload', 'track_demo_v1.wav', 'E5F6G7_track_demo_v1.wav', 'data/uploads/BDC-20260901-E5F6G7/track_demo_v1.wav', 'audio/wav', 5242880, '2026-09-01 09:12:00'),
('BDC-20260901-E5F6G7', 'project_upload', 'reference_video.mp4', 'E5F6G7_reference_video.mp4', 'data/uploads/BDC-20260901-E5F6G7/reference_video.mp4', 'video/mp4', 15728640, '2026-09-01 09:12:00'),
('DIST-1724010', 'audio_file', 'dil_ki_awaaz_master.wav', '1724010_dil_ki_awaaz_master.wav', 'data/uploads/DIST-1724010/dil_ki_awaaz_master.wav', 'audio/wav', 41943040, '2026-08-28 14:32:00'),
('DIST-1724010', 'cover_artwork', 'cover_art_3000.jpg', '1724010_cover_art_3000.jpg', 'data/uploads/DIST-1724010/cover_art_3000.jpg', 'image/jpeg', 2097152, '2026-08-28 14:32:00'),
('DIST-1724011', 'audio_file', 'echoes_of_soul_track1.wav', '1724011_echoes_of_soul_track1.wav', 'data/uploads/DIST-1724011/echoes_of_soul_track1.wav', 'audio/wav', 62914560, '2026-09-10 09:02:00'),
('DIST-1724011', 'cover_artwork', 'album_cover.png', '1724011_album_cover.png', 'data/uploads/DIST-1724011/album_cover.png', 'image/png', 3145728, '2026-09-10 09:02:00'),
('IPRS-1724020', 'pan_card', 'sonia_pan.pdf', '1724020_sonia_pan.pdf', 'data/uploads/IPRS-1724020/sonia_pan.pdf', 'application/pdf', 524288, '2026-09-03 08:47:00'),
('IPRS-1724020', 'address_proof', 'sonia_aadhaar.jpg', '1724020_sonia_aadhaar.jpg', 'data/uploads/IPRS-1724020/sonia_aadhaar.jpg', 'image/jpeg', 1048576, '2026-09-03 08:47:00'),
('IPRS-1724021', 'pan_card', 'rahul_pan.pdf', '1724021_rahul_pan.pdf', 'data/uploads/IPRS-1724021/rahul_pan.pdf', 'application/pdf', 614400, '2026-09-10 14:02:00'),
('IPRS-1724021', 'address_proof', 'rahul_address.pdf', '1724021_rahul_address.pdf', 'data/uploads/IPRS-1724021/rahul_address.pdf', 'application/pdf', 716800, '2026-09-10 14:02:00');

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
