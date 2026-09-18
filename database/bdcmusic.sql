-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 18, 2026 at 06:45 PM
-- Server version: 8.0.31
-- PHP Version: 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bdcmusic`
--

-- --------------------------------------------------------

--
-- Table structure for table `audio_video_bookings`
--

DROP TABLE IF EXISTS `audio_video_bookings`;
CREATE TABLE IF NOT EXISTS `audio_video_bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_category` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Audio',
  `service_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_email` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_formats` json DEFAULT NULL,
  `deadline` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `budget` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `payment_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Awaiting Payment',
  `payment_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `razorpay_order_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `paid_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_id` (`booking_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audio_video_bookings`
--

INSERT INTO `audio_video_bookings` (`id`, `booking_id`, `customer_id`, `service_category`, `service_type`, `client_name`, `client_email`, `client_phone`, `delivery_formats`, `deadline`, `budget`, `notes`, `price`, `status`, `payment_status`, `payment_id`, `razorpay_order_id`, `created_at`, `paid_at`) VALUES
(1, 'AV-1724001', 'CUST-E5F6G7H8', 'Video', 'Music Video', 'Priya Patel', 'priya@example.com', '9988776655', '[\"MP4\", \"4K\"]', '15 working days', '₹25,000', 'Cinematic look, outdoor shoot required.', '25000.00', 'Processing', 'Paid', 'pay_av_001', 'order_av_001', '2026-09-01 09:10:00', '2026-09-01 09:15:00'),
(2, 'AV-1724002', 'CUST-Y5Z6A7B8', 'Video', 'Social Media Videos', 'Ravi Joshi', 'ravi@example.com', '9009887766', '[\"MP4\", \"MOV\"]', '7 working days', '₹3,500', '5 reels, trending audio hooks.', '3500.00', 'Delivered', 'Paid', 'pay_av_002', 'order_av_002', '2026-08-10 13:25:00', '2026-08-10 13:30:00'),
(3, 'AV-1724003', 'CUST-A1B2C3D4', 'Audio', 'Recording', 'Rahul Sharma', 'rahul@example.com', '9876543210', '[\"WAV\", \"FLAC\"]', '10 working days', '₹5,000', 'Studio vocal recording, 3 tracks.', '5000.00', 'Delivered', 'Paid', 'pay_av_003', 'order_av_003', '2026-08-15 11:00:00', '2026-08-15 11:05:00'),
(4, 'AV-1724004', 'CUST-G3H4I5J6', 'Audio', 'Mixing', 'Arjun Reddy', 'arjun@example.com', '9228776655', '[\"WAV\", \"MP3\"]', '5 working days', '₹6,000', 'Mix 4 songs from my EP.', '6000.00', 'Pending', 'Awaiting Payment', NULL, NULL, '2026-09-10 08:00:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoice_no` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_id` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_email` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_mobile` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_details` json DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending Confirmation',
  `payment_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Awaiting Payment',
  `payment_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `razorpay_order_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auto_create_account` tinyint(1) NOT NULL DEFAULT '0',
  `auto_password` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `paid_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_id` (`booking_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_id`, `invoice_no`, `customer_id`, `customer_name`, `customer_email`, `customer_mobile`, `country`, `state`, `service`, `service_details`, `message`, `price`, `status`, `payment_status`, `payment_id`, `razorpay_order_id`, `auto_create_account`, `auto_password`, `created_at`, `paid_at`) VALUES
(1, 'BDC-20260815-A1B2C3', 'INV-A1B2C3', 'CUST-A1B2C3D4', 'Rahul Sharma', 'rahul@example.com', '9876543210', 'India', 'Maharashtra', 'Artist Management Services', '{\"artist_goal\": \"Build my brand as an independent Hindi pop artist\"}', 'Looking forward to the branding package.', '5000.00', 'Delivered', 'Paid', 'pay_demo_001', 'order_demo_001', 0, NULL, '2026-08-15 10:35:00', '2026-08-15 10:40:00'),
(2, 'BDC-20260901-E5F6G7', 'INV-E5F6G7', 'CUST-E5F6G7H8', 'Priya Patel', 'priya@example.com', '9988776655', 'India', 'Gujarat', 'Audio and Video Services', '{\"audio_video_type\": \"Music Video\", \"audio_video_format\": \"4K / HD\"}', 'Need a cinematic music video.', '12000.00', 'Processing', 'Paid', 'pay_demo_002', 'order_demo_002', 0, NULL, '2026-09-01 09:00:00', '2026-09-01 09:05:00'),
(3, 'BDC-20260828-I9J0K1', 'INV-I9J0K1', 'CUST-I9J0K1L2', 'Amit Kumar', 'amit@example.com', '9112233445', 'India', 'Delhi', 'Recording Services', '{\"recording_package\": \"Full Day\", \"recording_session\": \"Vocal\"}', 'Recording a 5-song EP.', '7000.00', 'Shipped', 'Paid', 'pay_demo_003', 'order_demo_003', 0, NULL, '2026-08-28 14:20:00', '2026-08-28 14:25:00'),
(4, 'BDC-20260905-M3N4O5', 'INV-M3N4O5', 'CUST-M3N4O5P6', 'Neha Gupta', 'neha@example.com', '9001122334', 'India', 'Karnataka', 'Online Classes', '{\"online_level\": \"Intermediate\", \"online_class_type\": \"Vocal\"}', 'Want to improve my classical vocals.', '3000.00', 'Pending', 'Awaiting Payment', NULL, NULL, 0, NULL, '2026-09-05 11:00:00', NULL),
(5, 'BDC-20260720-Q7R8S9', 'INV-Q7R8S9', 'CUST-Q7R8S9T0', 'Vikram Singh', 'vikram@example.com', '9871234567', 'India', 'Uttar Pradesh', 'Online Classes', '{\"online_level\": \"Beginner\", \"online_class_type\": \"Music Production\"}', 'Learning music production from scratch.', '3000.00', 'Delivered', 'Paid', 'pay_demo_005', 'order_demo_005', 0, NULL, '2026-07-20 16:05:00', '2026-07-20 16:10:00'),
(6, 'BDC-20260903-U1V2W3', 'INV-U1V2W3', 'CUST-U1V2W3X4', 'Sonia Verma', 'sonia@example.com', '9911223344', 'India', 'Rajasthan', 'Recording Services', '{\"recording_package\": \"Basic Session\", \"recording_session\": \"Instrumental\"}', 'Recording guitar tracks.', '7000.00', 'Cancelled', 'Refunded', 'pay_demo_006', 'order_demo_006', 0, NULL, '2026-09-03 08:35:00', '2026-09-03 08:40:00'),
(7, 'BDC-20260810-Y5Z6A7', 'INV-Y5Z6A7', 'CUST-Y5Z6A7B8', 'Ravi Joshi', 'ravi@example.com', '9009887766', 'India', 'Tamil Nadu', 'Audio and Video Services', '{\"audio_video_type\": \"Reel / Promo\", \"audio_video_format\": \"Social Media Reels\"}', 'Need 5 Instagram reels.', '12000.00', 'Delivered', 'Paid', 'pay_demo_007', 'order_demo_007', 0, NULL, '2026-08-10 13:15:00', '2026-08-10 13:20:00'),
(8, 'BDC-20260908-C9D0E1', 'INV-C9D0E1', 'CUST-C9D0E1F2', 'Deepika Nair', 'deepika@example.com', '9119887766', 'India', 'Kerala', 'Artist Management Services', '{\"artist_goal\": \"Launch my debut album and get distribution deals\"}', 'Ready for my album launch.', '5000.00', 'Processing', 'Paid', 'pay_demo_008', 'order_demo_008', 0, NULL, '2026-09-08 10:05:00', '2026-09-08 10:10:00');

-- --------------------------------------------------------

--
-- Table structure for table `distribution_bookings`
--

DROP TABLE IF EXISTS `distribution_bookings`;
CREATE TABLE IF NOT EXISTS `distribution_bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `release_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Single',
  `artist_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `release_title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `genre` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `language` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isrc` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `upc` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `copyright_help` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_id` (`booking_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `distribution_bookings`
--

INSERT INTO `distribution_bookings` (`id`, `booking_id`, `customer_id`, `release_type`, `artist_name`, `release_title`, `genre`, `language`, `isrc`, `upc`, `copyright_help`, `release_date`, `notes`, `status`, `created_at`) VALUES
(1, 'DIST-1724010', 'CUST-I9J0K1L2', 'Single', 'Amit Kumar', 'Dil Ki Awaaz', 'Pop', 'Hindi', 'Yes', 'No', 'Yes', '2026-09-15', 'My debut single, please distribute worldwide.', 'Processing', '2026-08-28 14:30:00'),
(2, 'DIST-1724011', 'CUST-G3H4I5J6', 'Album', 'Arjun Reddy', 'Echoes of Soul', 'Rock', 'English', 'Yes', 'Yes', 'No', '2026-10-01', '6-track album, all artwork attached.', 'Pending', '2026-09-10 09:00:00'),
(3, 'DIST-1724012', 'CUST-K7L8M9N0', 'Single', 'Kavita Desai', 'Monsoon Dreams', 'Folk', 'Hindi', 'No', 'No', 'Yes', '2026-09-20', 'Independent folk release, need ISRC code.', 'Pending', '2026-09-12 11:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `iprs_bookings`
--

DROP TABLE IF EXISTS `iprs_bookings`;
CREATE TABLE IF NOT EXISTS `iprs_bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `applicant_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `song_released` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `song_links` text COLLATE utf8mb4_unicode_ci,
  `song_title` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `artist_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_holder` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_number` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ifsc` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `membership_type` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_id` (`booking_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `iprs_bookings`
--

INSERT INTO `iprs_bookings` (`id`, `booking_id`, `customer_id`, `name`, `email`, `phone`, `applicant_type`, `song_released`, `song_links`, `song_title`, `artist_name`, `account_holder`, `account_number`, `ifsc`, `bank_name`, `membership_type`, `message`, `status`, `created_at`) VALUES
(1, 'IPRS-1724020', 'CUST-U1V2W3X4', 'Sonia Verma', 'sonia@example.com', '9911223344', 'composer', 'yes', 'https://open.spotify.com/track/example1', 'Raat Ki Rani', 'Sonia Verma', 'Sonia Verma', '123456789012', 'SBIN0001234', 'State Bank of India', 'author-composer', 'Need IPRS registration for my compositions.', 'Cancelled', '2026-09-03 08:45:00'),
(2, 'IPRS-1724021', 'CUST-A1B2C3D4', 'Rahul Sharma', 'rahul@example.com', '9876543210', 'author-composer', 'yes', 'https://youtube.com/watch?v=example2', 'Sapno Ka Safar', 'Rahul Sharma', 'Rahul Sharma', '987654321012', 'HDFC0001234', 'HDFC Bank', 'author-composer', 'Register my song and lyrics with IPRS.', 'Pending', '2026-09-10 14:00:00'),
(3, 'IPRS-1724022', 'CUST-K7L8M9N0', 'Kavita Desai', 'kavita@example.com', '9337665544', 'publisher', 'yes', 'https://gaana.com/track/example3', 'Bhakti Sagar', 'Kavita Devi', 'Kavita Desai', '567890123456', 'ICIC0005678', 'ICICI Bank', 'publisher', 'Publishing rights for devotional music catalog.', 'Pending', '2026-09-12 12:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `uploaded_files`
--

DROP TABLE IF EXISTS `uploaded_files`;
CREATE TABLE IF NOT EXISTS `uploaded_files` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `booking_id` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` int UNSIGNED NOT NULL DEFAULT '0',
  `uploaded_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_booking` (`booking_type`,`booking_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `uploaded_files`
--

INSERT INTO `uploaded_files` (`id`, `booking_type`, `booking_id`, `field_name`, `original_name`, `stored_name`, `file_path`, `mime_type`, `file_size`, `uploaded_at`) VALUES
(1, 'audio_video', 'AV-1724001', 'project_upload', 'track_demo_v1.wav', '1724001_track_demo_v1.wav', 'data/uploads/audio-video/AV-1724001/1724001_track_demo_v1.wav', 'audio/wav', 5242880, '2026-09-01 09:12:00'),
(2, 'audio_video', 'AV-1724001', 'project_upload', 'reference_video.mp4', '1724001_reference_video.mp4', 'data/uploads/audio-video/AV-1724001/1724001_reference_video.mp4', 'video/mp4', 15728640, '2026-09-01 09:12:00'),
(3, 'audio_video', 'AV-1724002', 'project_upload', 'reel_concept_draft.mp4', '1724002_reel_concept_draft.mp4', 'data/uploads/audio-video/AV-1724002/1724002_reel_concept_draft.mp4', 'video/mp4', 8388608, '2026-08-10 13:26:00'),
(4, 'audio_video', 'AV-1724003', 'project_upload', 'vocal_reference.wav', '1724003_vocal_reference.wav', 'data/uploads/audio-video/AV-1724003/1724003_vocal_reference.wav', 'audio/wav', 3145728, '2026-08-15 11:02:00'),
(5, 'audio_video', 'AV-1724004', 'project_upload', 'raw_mix_stems.zip', '1724004_raw_mix_stems.zip', 'data/uploads/audio-video/AV-1724004/1724004_raw_mix_stems.zip', 'application/zip', 52428800, '2026-09-10 08:02:00'),
(6, 'distribution', 'DIST-1724010', 'audio_file', 'dil_ki_awaaz_master.wav', '1724010_dil_ki_awaaz_master.wav', 'data/uploads/digital-distribution/DIST-1724010/1724010_dil_ki_awaaz_master.wav', 'audio/wav', 41943040, '2026-08-28 14:32:00'),
(7, 'distribution', 'DIST-1724010', 'cover_artwork', 'cover_art_3000.jpg', '1724010_cover_art_3000.jpg', 'data/uploads/digital-distribution/DIST-1724010/1724010_cover_art_3000.jpg', 'image/jpeg', 2097152, '2026-08-28 14:32:00'),
(8, 'distribution', 'DIST-1724011', 'audio_file', 'echoes_of_soul_track1.wav', '1724011_echoes_of_soul_track1.wav', 'data/uploads/digital-distribution/DIST-1724011/1724011_echoes_of_soul_track1.wav', 'audio/wav', 62914560, '2026-09-10 09:02:00'),
(9, 'distribution', 'DIST-1724011', 'cover_artwork', 'album_cover.png', '1724011_album_cover.png', 'data/uploads/digital-distribution/DIST-1724011/1724011_album_cover.png', 'image/png', 3145728, '2026-09-10 09:02:00'),
(10, 'distribution', 'DIST-1724012', 'audio_file', 'monsoon_dreams_final.mp3', '1724012_monsoon_dreams_final.mp3', 'data/uploads/digital-distribution/DIST-1724012/1724012_monsoon_dreams_final.mp3', 'audio/mpeg', 8388608, '2026-09-12 11:02:00'),
(11, 'iprs', 'IPRS-1724020', 'pan_card', 'sonia_pan.pdf', '1724020_sonia_pan.pdf', 'data/uploads/iprs/IPRS-1724020/1724020_sonia_pan.pdf', 'application/pdf', 524288, '2026-09-03 08:47:00'),
(12, 'iprs', 'IPRS-1724020', 'address_proof', 'sonia_aadhaar.jpg', '1724020_sonia_aadhaar.jpg', 'data/uploads/iprs/IPRS-1724020/1724020_sonia_aadhaar.jpg', 'image/jpeg', 1048576, '2026-09-03 08:47:00'),
(13, 'iprs', 'IPRS-1724020', 'photo', 'sonia_photo.jpg', '1724020_sonia_photo.jpg', 'data/uploads/iprs/IPRS-1724020/1724020_sonia_photo.jpg', 'image/jpeg', 786432, '2026-09-03 08:47:00'),
(14, 'iprs', 'IPRS-1724021', 'pan_card', 'rahul_pan.pdf', '1724021_rahul_pan.pdf', 'data/uploads/iprs/IPRS-1724021/1724021_rahul_pan.pdf', 'application/pdf', 614400, '2026-09-10 14:02:00'),
(15, 'iprs', 'IPRS-1724021', 'address_proof', 'rahul_address.pdf', '1724021_rahul_address.pdf', 'data/uploads/iprs/IPRS-1724021/1724021_rahul_address.pdf', 'application/pdf', 716800, '2026-09-10 14:02:00'),
(16, 'iprs', 'IPRS-1724021', 'photo', 'rahul_photo.jpg', '1724021_rahul_photo.jpg', 'data/uploads/iprs/IPRS-1724021/1724021_rahul_photo.jpg', 'image/jpeg', 819200, '2026-09-10 14:02:00'),
(17, 'iprs', 'IPRS-1724021', 'song_proof', 'rahul_song_proof.pdf', '1724021_rahul_song_proof.pdf', 'data/uploads/iprs/IPRS-1724021/1724021_rahul_song_proof.pdf', 'application/pdf', 409600, '2026-09-10 14:02:00'),
(18, 'iprs', 'IPRS-1724022', 'pan_card', 'kavita_pan.jpg', '1724022_kavita_pan.jpg', 'data/uploads/iprs/IPRS-1724022/1724022_kavita_pan.jpg', 'image/jpeg', 921600, '2026-09-12 12:02:00'),
(19, 'iprs', 'IPRS-1724022', 'address_proof', 'kavita_aadhaar.pdf', '1724022_kavita_aadhaar.pdf', 'data/uploads/iprs/IPRS-1724022/1724022_kavita_aadhaar.pdf', 'application/pdf', 665600, '2026-09-12 12:02:00'),
(20, 'iprs', 'IPRS-1724022', 'photo', 'kavita_photo.png', '1724022_kavita_photo.png', 'data/uploads/iprs/IPRS-1724022/1724022_kavita_photo.png', 'image/png', 563200, '2026-09-12 12:02:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=Customer, 2=Admin',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `source` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'website',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `mobile`, `password_hash`, `role`, `created_at`, `source`) VALUES
('CUST-A1B2C3D4', 'Rahul Sharma', 'rahul@example.com', '9876543210', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2026-07-15 10:30:00', 'website'),
('CUST-ADMIN0001', 'BDC Admin', 'admin@bdcmusic.in', '9999999999', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, '2026-01-01 00:00:00', 'website'),
('CUST-C9D0E1F2', 'Deepika Nair', 'deepika@example.com', '9119887766', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2026-09-01 10:00:00', 'guest_booking'),
('CUST-E5F6G7H8', 'Priya Patel', 'priya@example.com', '9988776655', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2026-07-20 14:15:00', 'website'),
('CUST-G3H4I5J6', 'Arjun Reddy', 'arjun@example.com', '9228776655', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2026-09-05 15:30:00', 'website'),
('CUST-I9J0K1L2', 'Amit Kumar', 'amit@example.com', '9112233445', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2026-08-01 09:45:00', 'guest_booking'),
('CUST-K7L8M9N0', 'Kavita Desai', 'kavita@example.com', '9337665544', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2026-09-08 09:00:00', 'website'),
('CUST-M3N4O5P6', 'Neha Gupta', 'neha@example.com', '9001122334', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2026-08-10 11:20:00', 'website'),
('CUST-Q7R8S9T0', 'Vikram Singh', 'vikram@example.com', '9871234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2026-08-15 16:00:00', 'guest_booking'),
('CUST-U1V2W3X4', 'Sonia Verma', 'sonia@example.com', '9911223344', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2026-08-20 08:30:00', 'website'),
('CUST-Y5Z6A7B8', 'Ravi Joshi', 'ravi@example.com', '9009887766', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2026-08-25 13:10:00', 'website');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audio_video_bookings`
--
ALTER TABLE `audio_video_bookings`
  ADD CONSTRAINT `audio_video_bookings_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `distribution_bookings`
--
ALTER TABLE `distribution_bookings`
  ADD CONSTRAINT `distribution_bookings_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `iprs_bookings`
--
ALTER TABLE `iprs_bookings`
  ADD CONSTRAINT `iprs_bookings_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
