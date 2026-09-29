-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 27, 2026 at 12:52 PM
-- Server version: 8.0.31
-- PHP Version: 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
SET FOREIGN_KEY_CHECKS=0;

--
-- Database: `bdcmusic`
--

-- --------------------------------------------------------

--
-- Table structure for table `artists`
--

DROP TABLE IF EXISTS `artists`;
CREATE TABLE IF NOT EXISTS `artists` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_artist_slug` (`slug`),
  KEY `idx_artist_category` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `artists`
--

INSERT INTO `artists` (`id`, `category_id`, `name`, `slug`, `image`, `location`, `bio`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 11, 'Abhinav Singh', 'abhinav-singh', 'assets/images/artist/artist-1.png', 'Kanpur, Uttar Pradesh', 'Independent singer blending Bollywood melodies with Hindustani classical vocals. Known for a style reminiscent of Arijit Singh and Darshan Raval, with Sufi influences. Trained in acoustic and fusion singing, and has been teaching vocals and classical music since 2021. 8 years in the industry.', 1, '2026-09-20 01:13:25', '2026-09-20 01:13:25'),
(2, 11, 'Anshuman Nigaar', 'anshuman-nigaar', NULL, 'Khalilabad, Gorakhpur', 'Multi-talented artist - singer, lyricist, composer, scriptwriter, and live performer. A complete entertainment package backed by 7 years of hands-on experience in the music industry.', 1, '2026-09-20 01:13:25', '2026-09-20 01:13:25'),
(3, 11, 'Gunjan Jha', 'gunjan-jha', 'assets/images/artist/gunjan-jha.webp', 'Delhi, India', 'Singer, music director, and vocal trainer with credits on Shabad (Pankaj Udhas, Times Music) and Mohabbat Me Tere Sanam (Kumar Sanu, Vusic Records). Available for live shows, studio sessions, and online classes.', 1, '2026-09-20 01:13:25', '2026-09-20 01:16:03'),
(4, 11, 'Nishaad', 'nishaad', 'assets/images/artist/nishaad.webp', 'Haryana, India', 'Versatile singer, lyricist, and music composer from Haryana. Brings 5 years of dedicated experience in crafting original music and live performances.', 1, '2026-09-20 01:13:25', '2026-09-20 01:16:03'),
(5, 11, 'Alaap Gahlaut', 'alaap-gahlaut', 'assets/images/artist/alaap-gahlaut.webp', 'New Delhi, India', 'Singer and short-form video creator with a decade of experience in the music industry. Combines vocal talent with a strong presence in the digital content space.', 1, '2026-09-20 01:13:25', '2026-09-20 01:16:03'),
(6, 8, 'Music PWN', 'music-pwn', NULL, 'Delhi, India', 'Music producer with 5 years of experience spanning multiple genres. Specializes in beat production, arrangement, and mixing for independent artists looking to create original tracks.', 1, '2026-09-20 01:13:25', '2026-09-20 01:13:25'),
(7, 8, 'Rohit Tiwari', 'rohit-tiwari', 'assets/images/artist/rohit-tiwari.webp', 'Chhatarpur, Delhi', 'Seasoned music producer with 8 years of experience producing tracks across diverse genres. Dedicated to helping artists bring their musical vision to life from concept to final master.', 1, '2026-09-20 01:13:25', '2026-09-20 01:16:02'),
(8, 8, 'Alagu Chandhiran', 'alagu-chandhiran', NULL, 'India', 'Music producer and background scoring specialist with 5 years of experience. Skilled in producing tracks for independent releases and short film soundtracks.', 1, '2026-09-20 01:13:25', '2026-09-20 01:13:25'),
(9, 10, 'Susma Das', 'susma-das', 'assets/images/artist/susma-das.webp', 'Kolkata, Bengal', 'Content creator and reels specialist from Kolkata. Creates engaging short-form videos and offers professional short video services for brands and artists.', 1, '2026-09-20 01:13:25', '2026-09-20 01:16:03'),
(10, 10, 'Knk Best Beats', 'knk-best-beats', NULL, 'Noida, Uttar Pradesh', 'Reels creator and short-form video specialist. Produces trending content and offers short video production services for music promotions and brand collaborations.', 1, '2026-09-20 01:13:25', '2026-09-20 01:13:25'),
(11, 10, 'Sitara', 'sitara', 'assets/images/artist/sitara.webp', 'Noida, India', 'Dynamic reels creator with a flair for performance-based content. Offers short video services for artists, brands, and social media campaigns.', 1, '2026-09-20 01:13:25', '2026-09-20 01:16:03'),
(12, 10, 'Ayush Sachdeva', 'ayush-sachdeva', 'assets/images/artist/ayush-sachdeva.webp', 'Ghaziabad, India', 'Reels creator, short-form video specialist, and model. Combines on-screen presence with content creation expertise for music videos, brand shoots, and social media campaigns.', 1, '2026-09-20 01:13:25', '2026-09-20 01:16:03'),
(13, 1, 'Basant Pathak', 'basant-pathak', '', 'Uttar Pradesh, India', 'Versatile actor with 5 years of experience in lead and supporting roles. Also active in modelling and commercial shoots across UP and nearby regions.', 1, '2026-09-20 01:13:25', '2026-09-20 19:19:41'),
(14, 1, 'Rajendra Rajawat', 'rajendra-rajawat', 'assets/images/artist/rajendra-rajawat.webp', 'Delhi, India', 'Actor and model with 2 years of on-screen experience. Has appeared in multiple video projects and offers short-form video services for music and commercial content.', 1, '2026-09-20 01:13:25', '2026-09-20 01:16:03'),
(15, 5, 'Jatin Shrivastav', 'jatin-shrivastav', NULL, 'Delhi, India', 'Director specializing in music videos, pre-wedding shoots, wedding films, and event coverage. 3 years of professional experience delivering cinematic content across India.', 1, '2026-09-20 01:13:25', '2026-09-20 01:13:25'),
(16, 5, 'Lalit Thakur', 'lalit-thakur', 'assets/images/artist/lalit-thakur.webp', 'Delhi, India', 'Experienced director with a decade in music videos, wedding cinematography, and event coverage. Also offers drone videography services. Available for projects across India.', 1, '2026-09-20 01:13:25', '2026-09-20 01:16:03'),
(17, 5, 'Amit Sati', 'amit-sati', 'assets/images/artist/amit-sati.webp', 'Rishikesh, Uttarakhand', 'Multi-faceted director offering music videos, wedding films, travel content, aerial videography, real estate shoots, vlogs, and drone mapping. Full-service production available pan-India.', 1, '2026-09-20 01:13:25', '2026-09-20 01:16:03'),
(18, 6, 'Vishal Kumar', 'vishal-kumar', 'assets/images/artist/vishal-kumar.webp', 'Delhi, India', 'Lead guitarist with 6 years of live performance experience. Has performed at numerous shows and events across India. Available for studio sessions and live gigs.', 1, '2026-09-20 01:13:25', '2026-09-20 01:16:03');

-- --------------------------------------------------------

--
-- Table structure for table `artist_categories`
--

DROP TABLE IF EXISTS `artist_categories`;
CREATE TABLE IF NOT EXISTS `artist_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ac_name` (`name`),
  UNIQUE KEY `uq_ac_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `artist_categories`
--

INSERT INTO `artist_categories` (`id`, `name`, `slug`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 'Actor', 'actor', 1, 1, '2026-09-20 00:43:17'),
(2, 'Actress', 'actress', 2, 1, '2026-09-20 00:43:17'),
(3, 'Composer', 'composer', 3, 1, '2026-09-20 00:43:17'),
(4, 'Dancer', 'dancer', 4, 1, '2026-09-20 00:43:17'),
(5, 'Director', 'director', 5, 1, '2026-09-20 00:43:17'),
(6, 'Instrument Player', 'instrument-player', 6, 1, '2026-09-20 00:43:17'),
(7, 'Music Band', 'music-band', 7, 1, '2026-09-20 00:43:17'),
(8, 'Music Producer', 'music-producer', 8, 1, '2026-09-20 00:43:17'),
(9, 'Producer', 'producer', 9, 1, '2026-09-20 00:43:17'),
(10, 'Reels Stars', 'reels-stars', 10, 1, '2026-09-20 00:43:17'),
(11, 'Singer', 'singer', 11, 1, '2026-09-20 00:43:17'),
(12, 'Writer', 'writer', 12, 1, '2026-09-20 00:43:17');

-- --------------------------------------------------------

--
-- Table structure for table `artist_enquiries`
--

DROP TABLE IF EXISTS `artist_enquiries`;
CREATE TABLE IF NOT EXISTS `artist_enquiries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `artist_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `message` text,
  `status` enum('new','read','replied') DEFAULT 'new',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_artist_id` (`artist_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `artist_enquiries`
--

--
-- DEMO ROWS ONLY. The names are fictional and every address is on a reserved
-- example domain, so the dump carries no real contact details. Replace these
-- with the studio's own enquiries before going live.
--

INSERT INTO `artist_enquiries` (`id`, `artist_id`, `name`, `email`, `phone`, `message`, `status`, `created_at`) VALUES
(1, 1, 'Rahul Sharma', 'rahul.sharma@example.com', '9876543210', 'Hi, I want to book this artist for a wedding event on 15th March. Please share availability.', 'read', '2026-09-18 10:30:00'),
(2, 2, 'Priya Mehta', 'priya.mehta@example.com', '9123456780', 'Interested in hiring for a music video shoot. Budget is flexible.', 'read', '2026-09-17 14:15:00'),
(3, 1, 'Amit Verma', 'amit.verma@example.com', '9988776655', 'Need a singer for corporate event in Delhi. 2 hours performance.', 'replied', '2026-09-16 09:45:00'),
(4, 3, 'Sneha Kapoor', 'sneha.k@example.com', '9871234567', 'Looking for a dancer for a TV commercial. Shooting in Mumbai.', 'read', '2026-09-19 11:20:00'),
(5, 2, 'Vikram Singh', 'vikram.s@example.com', '9765432108', 'Can you share the pricing for a live performance at a birthday party?', 'replied', '2026-09-20 08:00:00'),
(6, 4, 'Neha Gupta', 'neha.gupta@example.com', '9654321098', 'We are a production house looking for background singers for an album.', 'read', '2026-09-15 16:30:00'),
(7, 1, 'Rohit Joshi', 'rohit.joshi@example.com', '9543210987', 'Want to discuss pricing for a 3-day music festival.', 'replied', '2026-09-14 12:00:00'),
(8, 5, 'Ananya Reddy', 'ananya.r@example.com', '9432109876', 'Need a music producer for independent album. 5 tracks.', 'read', '2026-09-19 17:45:00'),
(9, 18, 'Test001', 'test001@example.com', '1234567890', 'Wanted to host this guy on my party event for 2 hrs', 'replied', '2026-09-20 18:32:26');

-- --------------------------------------------------------

--
-- Table structure for table `artist_pricing`
--

DROP TABLE IF EXISTS `artist_pricing`;
CREATE TABLE IF NOT EXISTS `artist_pricing` (
  `id` int NOT NULL AUTO_INCREMENT,
  `artist_id` int NOT NULL,
  `service_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pricing_artist` (`artist_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `artist_pricing`
--

INSERT INTO `artist_pricing` (`id`, `artist_id`, `service_type`, `price`, `sort_order`, `created_at`) VALUES
(1, 1, 'Group Classes (8 sessions)', '2500.00', 0, '2026-09-20 01:13:25'),
(2, 1, 'Individual Classes (8 sessions)', '3000.00', 1, '2026-09-20 01:13:25'),
(3, 2, 'Each Composition', '3000.00', 0, '2026-09-20 01:13:25'),
(4, 2, 'Each Song', '3000.00', 1, '2026-09-20 01:13:25'),
(5, 4, 'Each Song', '3000.00', 0, '2026-09-20 01:13:25'),
(7, 14, 'Each Video', '3000.00', 0, '2026-09-20 01:13:25'),
(8, 14, 'Each Reel', '30.00', 1, '2026-09-20 01:13:25'),
(9, 18, 'Live Show', '3000.00', 0, '2026-09-20 01:13:25'),
(19, 13, 'Each Video', '3500.00', 0, '2026-09-20 19:19:41');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoice_no` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `customer_email` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `customer_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_whatsapp` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_type` enum('registered','guest') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'guest',
  `service_id` int NOT NULL,
  `service_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `service_slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `plan_id` int DEFAULT NULL,
  `plan_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plan_group` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `plan_group_label` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta` json DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `addons_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'INR',
  `status` enum('pending','processing','hold','delivered','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_status` enum('awaiting','paid','refunded','failed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'awaiting',
  `payment_provider` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'razorpay',
  `payment_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `razorpay_order_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `razorpay_signature` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_failure_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auto_create_account` tinyint(1) NOT NULL DEFAULT '0',
  `auto_password` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  KEY `fk_bookings_plan` (`plan_id`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_id`, `invoice_no`, `customer_id`, `customer_name`, `customer_email`, `customer_phone`, `customer_whatsapp`, `customer_type`, `service_id`, `service_name`, `service_slug`, `plan_id`, `plan_name`, `plan_group`, `plan_group_label`, `meta`, `message`, `price`, `subtotal`, `addons_total`, `currency`, `status`, `payment_status`, `payment_provider`, `payment_id`, `razorpay_order_id`, `razorpay_signature`, `payment_method`, `payment_failure_reason`, `auto_create_account`, `auto_password`, `created_at`, `updated_at`, `paid_at`) VALUES
(1, 'BDCM-A1B2C3', 'INV-A1B2C3', 'CUST-A1B2C3D4', 'Rahul Sharma', 'rahul@example.com', '9876543210', NULL, 'registered', 1, 'BDC Artists Marketplace', 'artists-marketplace', 12, 'Verified Pro', '', 'Membership', '{\"artist_goal\": \"Build my brand as an independent Hindi pop artist\"}', 'Looking forward to the branding package.', '4999.00', '4999.00', '0.00', 'INR', 'delivered', 'paid', 'razorpay', 'pay_demo_001', 'order_demo_001', NULL, 'card', NULL, 0, NULL, '2026-08-15 10:35:00', '2026-09-27 09:12:55', '2026-08-15 10:40:00'),
(2, 'BDCM-E5F6G7', 'INV-E5F6G7', 'CUST-E5F6G7H8', 'Priya Patel', 'priya@example.com', '9988776655', NULL, 'registered', 2, 'Audio & Video Services', 'audio-video', 1, 'Basic', '', 'Audio', '{\"budget\": \"₹25,000\", \"deadline\": \"15 working days\", \"service_type\": \"Music Video\", \"delivery_formats\": [\"MP4\", \"4K\"], \"service_category\": \"Video\"}', 'Need a cinematic music video.', '15500.00', '11500.00', '4000.00', 'INR', 'processing', 'paid', 'razorpay', 'pay_demo_002', 'order_demo_002', NULL, 'card', NULL, 0, NULL, '2026-09-01 09:00:00', '2026-09-27 09:12:55', '2026-09-01 09:05:00'),
(3, 'BDCM-I9J0K1', 'INV-I9J0K1', 'CUST-I9J0K1L2', 'Amit Kumar', 'amit@example.com', '9112233445', NULL, 'registered', 2, 'Audio & Video Services', 'audio-video', 1, 'Basic', '', 'Audio', '{\"budget\": \"₹5,000\", \"deadline\": \"10 working days\", \"service_type\": \"Recording\", \"delivery_formats\": [\"WAV\", \"FLAC\"], \"service_category\": \"Audio\"}', 'Recording a 5-song EP.', '15500.00', '11500.00', '4000.00', 'INR', 'processing', 'paid', 'razorpay', 'pay_demo_003', 'order_demo_003', NULL, 'card', NULL, 0, NULL, '2026-08-28 14:20:00', '2026-09-27 09:12:55', '2026-08-28 14:25:00'),
(4, 'BDCM-M3N4O5', 'INV-M3N4O5', 'CUST-M3N4O5P6', 'Neha Gupta', 'neha@example.com', '9001122334', NULL, 'registered', 3, 'Online/Offline Classes', 'online-offline-classes', 13, 'Basic', 'singing', 'Singing', '{\"level\": \"Intermediate\", \"course\": \"Singing\", \"class_mode\": \"Online\"}', 'Want to improve my classical vocals.', '2999.00', '2999.00', '0.00', 'INR', 'pending', 'awaiting', 'razorpay', NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-09-05 11:00:00', '2026-09-27 09:12:55', NULL),
(5, 'BDCM-Q7R8S9', 'INV-Q7R8S9', 'CUST-Q7R8S9T0', 'Vikram Singh', 'vikram@example.com', '9871234567', NULL, 'registered', 3, 'Online/Offline Classes', 'online-offline-classes', 17, 'Basic', 'music-production', 'Music Production', '{\"level\": \"Beginner\", \"course\": \"Music Production\", \"class_mode\": \"Online\"}', 'Learning music production from scratch.', '3999.00', '3999.00', '0.00', 'INR', 'delivered', 'paid', 'razorpay', 'pay_demo_005', 'order_demo_005', NULL, 'upi', NULL, 0, NULL, '2026-07-20 16:05:00', '2026-09-27 09:12:55', '2026-07-20 16:10:00'),
(6, 'BDCM-U1V2W3', 'INV-U1V2W3', 'CUST-U1V2W3X4', 'Sonia Verma', 'sonia@example.com', '9911223344', NULL, 'registered', 2, 'Audio & Video Services', 'audio-video', 1, 'Basic', '', 'Audio', '{\"budget\": \"₹7,000\", \"deadline\": \"10 working days\", \"service_type\": \"Recording\", \"delivery_formats\": [\"WAV\", \"FLAC\"], \"service_category\": \"Audio\"}', 'Recording guitar tracks.', '11500.00', '11500.00', '0.00', 'INR', 'cancelled', 'refunded', 'razorpay', 'pay_demo_006', 'order_demo_006', NULL, 'card', NULL, 0, NULL, '2026-09-03 08:35:00', '2026-09-27 09:12:55', '2026-09-03 08:40:00'),
(7, 'BDCM-Y5Z6A7', 'INV-Y5Z6A7', 'CUST-Y5Z6A7B8', 'Ravi Joshi', 'ravi@example.com', '9009887766', NULL, 'registered', 2, 'Audio & Video Services', 'audio-video', 1, 'Basic', '', 'Audio', '{\"budget\": \"₹3,500\", \"deadline\": \"7 working days\", \"service_type\": \"Reel / Promo\", \"delivery_formats\": [\"Social Media Reels\"], \"service_category\": \"Video\"}', 'Need 5 Instagram reels.', '13000.00', '11500.00', '1500.00', 'INR', 'delivered', 'paid', 'razorpay', 'pay_demo_007', 'order_demo_007', NULL, 'card', NULL, 0, NULL, '2026-08-10 13:15:00', '2026-09-27 09:12:55', '2026-08-10 13:20:00'),
(8, 'BDCM-C9D0E1', 'INV-C9D0E1', 'CUST-C9D0E1F2', 'Deepika Nair', 'deepika@example.com', '9119887766', NULL, 'registered', 1, 'BDC Artists Marketplace', 'artists-marketplace', 12, 'Verified Pro', '', 'Membership', '{\"artist_goal\": \"Launch my debut album and get distribution deals\"}', 'Ready for my album launch.', '5498.00', '4999.00', '499.00', 'INR', 'processing', 'paid', 'razorpay', 'pay_demo_008', 'order_demo_008', NULL, 'upi', NULL, 0, NULL, '2026-09-08 10:05:00', '2026-09-27 09:12:55', '2026-09-08 10:10:00'),
(9, 'BDCM-172401', NULL, 'CUST-I9J0K1L2', 'Amit Kumar', 'amit@example.com', '9112233445', NULL, 'registered', 4, 'Digital Music Distribution', 'digital-distribution', 5, 'Release Plan', '', 'Distribution', '{\"upc\": \"No\", \"isrc\": \"Yes\", \"genre\": \"Pop\", \"language\": \"Hindi\", \"artist_name\": \"Amit Kumar\", \"release_date\": \"2026-09-15\", \"release_type\": \"Single\", \"release_title\": \"Dil Ki Awaaz\", \"copyright_help\": \"Yes\"}', 'My debut single, please distribute worldwide.', '1198.00', '199.00', '999.00', 'INR', 'processing', 'awaiting', 'razorpay', NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-08-28 14:30:00', '2026-09-27 09:12:55', NULL),
(10, 'BDCM-172411', NULL, 'CUST-G3H4I5J6', 'Arjun Reddy', 'arjun@example.com', '9228776655', NULL, 'registered', 4, 'Digital Music Distribution', 'digital-distribution', 6, 'Artist Unlimited', '', 'Distribution', '{\"upc\": \"Yes\", \"isrc\": \"Yes\", \"genre\": \"Rock\", \"language\": \"English\", \"artist_name\": \"Arjun Reddy\", \"release_date\": \"2026-10-01\", \"release_type\": \"Album\", \"release_title\": \"Echoes of Soul\", \"copyright_help\": \"No\"}', '6-track album, all artwork attached.', '2198.00', '1199.00', '999.00', 'INR', 'pending', 'awaiting', 'razorpay', NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-09-10 09:00:00', '2026-09-27 09:12:55', NULL),
(11, 'BDCM-172412', NULL, 'CUST-K7L8M9N0', 'Kavita Desai', 'kavita@example.com', '9337665544', NULL, 'registered', 4, 'Digital Music Distribution', 'digital-distribution', 5, 'Release Plan', '', 'Distribution', '{\"upc\": \"No\", \"isrc\": \"No\", \"genre\": \"Folk\", \"language\": \"Hindi\", \"artist_name\": \"Kavita Desai\", \"release_date\": \"2026-09-20\", \"release_type\": \"Single\", \"release_title\": \"Monsoon Dreams\", \"copyright_help\": \"Yes\"}', 'Independent folk release, need ISRC code.', '199.00', '199.00', '0.00', 'INR', 'pending', 'awaiting', 'razorpay', NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-09-12 11:00:00', '2026-09-27 09:12:55', NULL),
(12, 'BDCM-172420', NULL, 'CUST-U1V2W3X4', 'Sonia Verma', 'sonia@example.com', '9911223344', NULL, 'registered', 6, 'IPRS Services', 'iprs', 29, 'Author / Composer', '', 'Membership', '{\"ifsc\": \"SBIN0001234\", \"bank_name\": \"State Bank of India\", \"song_links\": \"https://open.spotify.com/track/example1\", \"song_title\": \"Raat Ki Rani\", \"artist_name\": \"Sonia Verma\", \"song_released\": \"yes\", \"account_holder\": \"Sonia Verma\", \"account_number\": \"123456789012\", \"applicant_type\": \"composer\", \"membership_type\": \"author-composer\"}', 'Need IPRS registration for my compositions.', '2499.00', '2499.00', '0.00', 'INR', 'cancelled', 'awaiting', 'razorpay', NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-09-03 08:45:00', '2026-09-27 09:12:55', NULL),
(13, 'BDCM-172421', NULL, 'CUST-A1B2C3D4', 'Rahul Sharma', 'rahul@example.com', '9876543210', NULL, 'registered', 6, 'IPRS Services', 'iprs', 29, 'Author / Composer', '', 'Membership', '{\"ifsc\": \"HDFC0001234\", \"bank_name\": \"HDFC Bank\", \"song_links\": \"https://youtube.com/watch?v=example2\", \"song_title\": \"Sapno Ka Safar\", \"artist_name\": \"Rahul Sharma\", \"song_released\": \"yes\", \"account_holder\": \"Rahul Sharma\", \"account_number\": \"987654321012\", \"applicant_type\": \"author-composer\", \"membership_type\": \"author-composer\"}', 'Register my song and lyrics with IPRS.', '2499.00', '2499.00', '0.00', 'INR', 'pending', 'awaiting', 'razorpay', NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-09-10 14:00:00', '2026-09-27 09:12:55', NULL),
(14, 'BDCM-172422', NULL, 'CUST-K7L8M9N0', 'Kavita Desai', 'kavita@example.com', '9337665544', NULL, 'registered', 6, 'IPRS Services', 'iprs', 30, 'Publisher', '', 'Membership', '{\"ifsc\": \"ICIC0005678\", \"bank_name\": \"ICICI Bank\", \"song_links\": \"https://gaana.com/track/example3\", \"song_title\": \"Bhakti Sagar\", \"artist_name\": \"Kavita Devi\", \"song_released\": \"yes\", \"account_holder\": \"Kavita Desai\", \"account_number\": \"567890123456\", \"applicant_type\": \"publisher\", \"membership_type\": \"publisher\"}', 'Publishing rights for devotional music catalog.', '4999.00', '4999.00', '0.00', 'INR', 'pending', 'awaiting', 'razorpay', NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-09-12 12:00:00', '2026-09-27 09:12:55', NULL),
(15, 'BDCM-PROMO0', NULL, 'CUST-M3N4O5P6', 'Neha Gupta', 'neha@example.com', '9001122334', NULL, 'registered', 5, 'Promotion Services', 'promotion', NULL, NULL, '', NULL, '{\"budget\": \"₹5,000\", \"campaign_type\": \"Social Media\", \"target_platform\": \"Instagram, YouTube\"}', 'Promote my new single on social media.', '5000.00', '5000.00', '0.00', 'INR', 'pending', 'awaiting', 'manual', NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-09-05 12:00:00', '2026-09-27 10:51:56', NULL),
(61, 'BDCM-95EB0D', NULL, NULL, '', '', NULL, '15726745623', 'guest', 5, 'Promotion Services', 'promotion', NULL, NULL, '', NULL, '{\"notes\": \"In exercitation modi\", \"budget\": \"Odio commodo dolores\", \"content_type\": \"Instagram Reels\", \"project_link\": \"https://www.majyguxocodi.org\", \"campaign_type\": \"Other\", \"release_title\": \"Natus iusto sunt er\", \"promotion_goal\": \"Audience Growth\", \"target_platform\": \"Quisquam voluptatem\", \"campaign_duration\": \"Corrupti aut except\"}', '', '7500.00', '7500.00', '0.00', 'INR', 'pending', 'awaiting', 'manual', NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-09-27 17:24:24', '2026-09-27 18:21:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `booking_addons`
--

DROP TABLE IF EXISTS `booking_addons`;
CREATE TABLE IF NOT EXISTS `booking_addons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `addon_id` int NOT NULL,
  `addon_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `qty` int NOT NULL DEFAULT '1',
  `line_total` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ba` (`booking_id`,`addon_id`),
  KEY `idx_ba_addon` (`addon_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booking_addons`
--

INSERT INTO `booking_addons` (`id`, `booking_id`, `addon_id`, `addon_name`, `unit_price`, `qty`, `line_total`) VALUES
(1, 'BDCM-E5F6G7', 1, 'Mastering & Loudness Pass', '2500.00', 1, '2500.00'),
(2, 'BDCM-E5F6G7', 2, 'Extra Studio Hour', '1500.00', 1, '1500.00'),
(3, 'BDCM-I9J0K1', 1, 'Mastering & Loudness Pass', '2500.00', 1, '2500.00'),
(4, 'BDCM-I9J0K1', 2, 'Extra Studio Hour', '1500.00', 1, '1500.00'),
(5, 'BDCM-Y5Z6A7', 2, 'Extra Studio Hour', '1500.00', 1, '1500.00'),
(6, 'BDCM-172401', 3, 'YouTube Content ID', '999.00', 1, '999.00'),
(7, 'BDCM-172411', 3, 'YouTube Content ID', '999.00', 1, '999.00'),
(8, 'BDCM-C9D0E1', 4, 'Profile Verification Badge', '499.00', 1, '499.00');

-- --------------------------------------------------------

--
-- Table structure for table `booking_payments`
--

DROP TABLE IF EXISTS `booking_payments`;
CREATE TABLE IF NOT EXISTS `booking_payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'razorpay',
  `razorpay_order_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `razorpay_payment_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `razorpay_signature` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'INR',
  `status` enum('created','paid','failed','cancelled','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'created',
  `method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `failure_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `paid_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_bp_rzp_order` (`razorpay_order_id`),
  KEY `idx_bp_booking` (`booking_id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booking_payments`
--

INSERT INTO `booking_payments` (`id`, `booking_id`, `provider`, `razorpay_order_id`, `razorpay_payment_id`, `razorpay_signature`, `amount`, `currency`, `status`, `method`, `failure_reason`, `created_at`, `updated_at`, `paid_at`) VALUES
(40, 'BDCM-95EB0D', 'manual', NULL, NULL, NULL, '7500.00', 'INR', 'created', NULL, NULL, '2026-09-27 17:24:24', '2026-09-27 18:21:00', NULL),
(41, 'BDCM-A1B2C3', 'razorpay', 'order_demo_001', 'pay_demo_001', NULL, '4999.00', 'INR', 'paid', 'card', NULL, '2026-08-15 10:35:00', '2026-08-15 10:40:00', '2026-08-15 10:40:00'),
(42, 'BDCM-E5F6G7', 'razorpay', 'order_demo_002', 'pay_demo_002', NULL, '15500.00', 'INR', 'paid', 'card', NULL, '2026-09-01 09:00:00', '2026-09-01 09:05:00', '2026-09-01 09:05:00'),
(43, 'BDCM-I9J0K1', 'razorpay', 'order_demo_003', 'pay_demo_003', NULL, '15500.00', 'INR', 'paid', 'card', NULL, '2026-08-28 14:20:00', '2026-08-28 14:25:00', '2026-08-28 14:25:00'),
(44, 'BDCM-Q7R8S9', 'razorpay', 'order_demo_005', 'pay_demo_005', NULL, '3999.00', 'INR', 'paid', 'upi', NULL, '2026-07-20 16:05:00', '2026-07-20 16:10:00', '2026-07-20 16:10:00'),
(45, 'BDCM-U1V2W3', 'razorpay', 'order_demo_006', 'pay_demo_006', NULL, '11500.00', 'INR', 'refunded', 'card', NULL, '2026-09-03 08:35:00', '2026-09-04 10:00:00', '2026-09-03 08:40:00'),
(46, 'BDCM-Y5Z6A7', 'razorpay', 'order_demo_007', 'pay_demo_007', NULL, '13000.00', 'INR', 'paid', 'card', NULL, '2026-08-10 13:15:00', '2026-08-10 13:20:00', '2026-08-10 13:20:00'),
(47, 'BDCM-C9D0E1', 'razorpay', 'order_demo_008', 'pay_demo_008', NULL, '5498.00', 'INR', 'paid', 'upi', NULL, '2026-09-08 10:05:00', '2026-09-08 10:10:00', '2026-09-08 10:10:00');

-- --------------------------------------------------------

--
-- Table structure for table `releases`
--

DROP TABLE IF EXISTS `releases`;
CREATE TABLE IF NOT EXISTS `releases` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `booking_id` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('single','ep','album') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'single',
  `artwork_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isrc` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `upc` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `go_live_date` date DEFAULT NULL,
  `status` enum('draft','pending','verification','onhold','rejected','approved','live','takedown') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `lyrics` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `dolby` tinyint(1) NOT NULL DEFAULT '0',
  `apple_itunes` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_release_customer` (`customer_id`),
  KEY `idx_release_booking` (`booking_id`),
  KEY `idx_release_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `releases`
--

INSERT INTO `releases` (`id`, `customer_id`, `booking_id`, `title`, `type`, `artwork_path`, `isrc`, `upc`, `go_live_date`, `status`, `lyrics`, `dolby`, `apple_itunes`, `created_at`, `updated_at`) VALUES
(1, 'CUST-I9J0K1L2', 'BDCM-172401', 'Demo Single One', 'single', 'data/uploads/booking/BDCM-172401/172401_demo_cover_art.jpg', 'IN-R5S-23-00001', NULL, '2026-09-15', 'live', 'Sample lyric placeholder for seeded demo content.\nSecond placeholder line for the release detail view.\nThird placeholder line so the panel has something to show.', 0, 1, '2026-08-28 14:30:00', '2026-09-15 06:00:00'),
(2, 'CUST-G3H4I5J6', 'BDCM-172411', 'Demo Album One', 'album', 'data/uploads/booking/BDCM-172411/172411_demo_album_cover.png', NULL, '123456789012', '2026-10-01', 'approved', NULL, 0, 0, '2026-09-10 09:00:00', '2026-09-18 14:00:00'),
(3, 'CUST-K7L8M9N0', 'BDCM-172412', 'Demo Single Two', 'single', NULL, NULL, NULL, '2026-09-25', 'verification', 'Sample lyric placeholder for seeded demo content.\nSecond placeholder line for the release detail view.', 0, 0, '2026-09-12 11:00:00', '2026-09-19 10:30:00'),
(4, 'CUST-I9J0K1L2', NULL, 'Demo EP One', 'ep', NULL, 'IN-R5S-23-00002', NULL, '2026-11-10', 'pending', NULL, 1, 1, '2026-09-18 16:00:00', '2026-09-18 16:00:00'),
(5, 'CUST-E5F6G7H8', NULL, 'Midnight Vibes', 'single', NULL, NULL, NULL, NULL, 'draft', 'Shehar ki raatein, dil ki baatein...\nChand ke neeche, tera saath ho...\nDhadkanein tez hain, raat gehri hai...\nBas tera intezaar hai, teri baat ho...', 0, 0, '2026-09-20 09:00:00', '2026-09-20 09:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `release_artists`
--

DROP TABLE IF EXISTS `release_artists`;
CREATE TABLE IF NOT EXISTS `release_artists` (
  `id` int NOT NULL AUTO_INCREMENT,
  `release_id` int NOT NULL,
  `role` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ra_release` (`release_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `release_artists`
--

INSERT INTO `release_artists` (`id`, `release_id`, `role`, `name`, `created_at`) VALUES
(1, 1, 'primary', 'Amit Kumar', '2026-08-28 14:30:00'),
(2, 1, 'composer', 'Rohit Sharma', '2026-08-28 14:30:00'),
(3, 1, 'lyricist', 'Neha Gupta', '2026-08-28 14:30:00'),
(4, 2, 'primary', 'Arjun Reddy', '2026-09-10 09:00:00'),
(5, 2, 'producer', 'Rohit Tiwari', '2026-09-10 09:00:00'),
(6, 2, 'featured', 'Priya Mehta', '2026-09-10 09:00:00'),
(7, 3, 'primary', 'Kavita Desai', '2026-09-12 11:00:00'),
(8, 3, 'composer', 'Jatin Shrivastav', '2026-09-12 11:00:00'),
(9, 4, 'primary', 'Amit Kumar', '2026-09-18 16:00:00'),
(10, 4, 'featured', 'Gunjan Jha', '2026-09-18 16:00:00'),
(11, 5, 'primary', 'Priya Patel', '2026-09-20 09:00:00'),
(12, 5, 'producer', 'Music PWN', '2026-09-20 09:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `release_history`
--

DROP TABLE IF EXISTS `release_history`;
CREATE TABLE IF NOT EXISTS `release_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `release_id` int NOT NULL,
  `action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `reviewer_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rh_release` (`release_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `release_history`
--

INSERT INTO `release_history` (`id`, `release_id`, `action`, `message`, `reviewer_note`, `created_at`) VALUES
(1, 1, 'submitted', 'Release submitted for distribution.', NULL, '2026-08-28 14:30:00'),
(2, 1, 'under_review', 'Release is being reviewed by the team.', NULL, '2026-08-29 10:00:00'),
(3, 1, 'approved', 'All metadata and artwork verified. Approved for distribution.', NULL, '2026-08-30 14:00:00'),
(4, 1, 'distributed', 'Release sent to 150+ platforms including Spotify, Apple Music, JioSaavn.', NULL, '2026-09-01 09:00:00'),
(5, 1, 'live', 'Release is now live on all platforms.', NULL, '2026-09-15 06:00:00'),
(6, 2, 'submitted', 'Album submitted with 6 tracks and full artwork package.', NULL, '2026-09-10 09:00:00'),
(7, 2, 'under_review', 'Reviewing audio quality and metadata for all 6 tracks.', NULL, '2026-09-11 10:00:00'),
(8, 2, 'approved', 'Album approved. Audio mastering quality is excellent.', NULL, '2026-09-18 14:00:00'),
(9, 3, 'submitted', 'Folk single submitted for distribution.', NULL, '2026-09-12 11:00:00'),
(10, 3, 'under_review', 'ISRC code requested. Awaiting assignment.', NULL, '2026-09-13 10:00:00'),
(11, 3, 'on_hold', 'On hold: Lyrics need Hindi transliteration for platform metadata.', 'Please provide romanized lyrics for international platforms.', '2026-09-15 14:00:00'),
(12, 3, 'under_review', 'Lyrics updated. Re-reviewing for final approval.', NULL, '2026-09-19 10:30:00'),
(13, 4, 'submitted', 'EP with 4 tracks submitted. Dolby Atmos enabled.', NULL, '2026-09-18 16:00:00'),
(14, 5, 'draft', 'Release created as draft. Awaiting audio upload.', NULL, '2026-09-20 09:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `release_platform_links`
--

DROP TABLE IF EXISTS `release_platform_links`;
CREATE TABLE IF NOT EXISTS `release_platform_links` (
  `id` int NOT NULL AUTO_INCREMENT,
  `release_id` int NOT NULL,
  `platform` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_rpl_release_platform` (`release_id`,`platform`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `release_platform_links`
--

INSERT INTO `release_platform_links` (`id`, `release_id`, `platform`, `url`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'Spotify', 'https://open.spotify.com/track/example1', 1, 1, '2026-09-27 00:47:20', '2026-09-27 00:47:20'),
(2, 1, 'Apple Music', 'https://music.apple.com/in/artist/example', 1, 2, '2026-09-27 00:47:20', '2026-09-27 00:47:20'),
(3, 1, 'YouTube Music', 'https://music.youtube.com/watch?v=example1', 1, 3, '2026-09-27 00:47:20', '2026-09-27 00:47:20'),
(4, 1, 'Amazon Music', 'https://music.amazon.com/albums/example1', 1, 4, '2026-09-27 00:47:20', '2026-09-27 00:47:20'),
(5, 1, 'JioSaavn', 'https://www.jiosaavn.com/song/example1', 1, 5, '2026-09-27 00:47:20', '2026-09-27 00:47:20'),
(6, 1, 'Gaana', 'https://gaana.com/song/example1', 1, 6, '2026-09-27 00:47:20', '2026-09-27 00:47:20');

-- --------------------------------------------------------

--
-- Table structure for table `release_tracks`
--

DROP TABLE IF EXISTS `release_tracks`;
CREATE TABLE IF NOT EXISTS `release_tracks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `release_id` int NOT NULL,
  `track_no` int NOT NULL DEFAULT '1',
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `isrc` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `audio_file` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_rt_release_no` (`release_id`,`track_no`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `release_tracks`
--

INSERT INTO `release_tracks` (`id`, `release_id`, `track_no`, `title`, `isrc`, `duration`, `audio_file`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'Echoes of Soul', 'IN-R5S-23-10001', '4:12', NULL, '2026-09-27 00:47:20', '2026-09-27 00:47:20'),
(2, 2, 2, 'Midnight Reverb', 'IN-R5S-23-10002', '3:48', NULL, '2026-09-27 00:47:20', '2026-09-27 00:47:20'),
(3, 2, 3, 'Paper Lanterns', 'IN-R5S-23-10003', '4:35', NULL, '2026-09-27 00:47:20', '2026-09-27 00:47:20'),
(4, 2, 4, 'Static Hearts', 'IN-R5S-23-10004', '3:27', NULL, '2026-09-27 00:47:20', '2026-09-27 00:47:20'),
(5, 2, 5, 'Long Way Home', 'IN-R5S-23-10005', '5:04', NULL, '2026-09-27 00:47:20', '2026-09-27 00:47:20'),
(6, 2, 6, 'Echoes of Soul (Reprise)', 'IN-R5S-23-10006', '4:12', NULL, '2026-09-27 00:47:20', '2026-09-27 00:47:20'),
(7, 4, 1, 'Raatein', 'IN-R5S-23-00002', '3:41', NULL, '2026-09-27 00:47:20', '2026-09-27 00:47:20'),
(8, 4, 2, 'Neon Katha', 'IN-R5S-23-00003', '4:02', NULL, '2026-09-27 00:47:20', '2026-09-27 00:47:20'),
(9, 4, 3, 'Beparwah', 'IN-R5S-23-00004', '3:19', NULL, '2026-09-27 00:47:20', '2026-09-27 00:47:20'),
(10, 4, 4, 'Raatein (Acoustic)', 'IN-R5S-23-00005', '3:44', NULL, '2026-09-27 00:47:20', '2026-09-27 00:47:20');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE IF NOT EXISTS `services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `slug` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `booking_mode` enum('packages','quote') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'packages',
  `price_note` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `slug`, `name`, `booking_mode`, `price_note`, `is_active`, `created_at`) VALUES
(1, 'artists-marketplace', 'BDC Artists Marketplace', 'packages', 'From Rs.499 / year', 1, '2026-09-18 19:49:01'),
(2, 'audio-video', 'Audio & Video Services', 'packages', 'From Rs.11,500', 1, '2026-09-18 19:49:01'),
(3, 'online-offline-classes', 'Online/Offline Classes', 'packages', 'From Rs.2,999', 1, '2026-09-18 19:49:01'),
(4, 'digital-distribution', 'Digital Music Distribution', 'packages', 'From Rs.199', 1, '2026-09-18 19:49:01'),
(5, 'promotion', 'Promotion Services', 'quote', 'Custom quote', 1, '2026-09-18 19:49:01'),
(6, 'iprs', 'IPRS Services', 'packages', 'From Rs.2,499', 1, '2026-09-18 19:49:01');


--
-- Table structure for table `service_plans`
--

DROP TABLE IF EXISTS `service_plans`;
CREATE TABLE IF NOT EXISTS `service_plans` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int NOT NULL,
  `group_key` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `group_label` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `price_note` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `best_for` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `features` json DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_enquiry` tinyint(1) NOT NULL DEFAULT '0',
  `is_orderable` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_plan` (`service_id`,`group_key`,`name`),
  KEY `idx_sp_service` (`service_id`,`is_active`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=139 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_plans`
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (1,2,'','Audio','Basic',11500.00,NULL,'Studio access and raw tracking',NULL,'[\"Studio access\", \"Recording engineer\", \"Basic microphone setup\", \"RAW audio files\"]',1,1,0,1,1,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (2,2,'','Audio','Standard',23500.00,NULL,'Professional microphone and editing',NULL,'[\"Professional microphone\", \"Multiple recording takes\", \"Studio access included\", \"Basic audio editing\"]',0,1,0,1,2,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (3,2,'','Audio','Premium',36500.00,NULL,'Premium setup with vocal comping',NULL,'[\"Premium studio setup\", \"Professional equipment\", \"Vocal comping\", \"Basic editing included\"]',0,1,0,1,3,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (4,2,'','Audio','Enterprise',75000.00,NULL,'Complete studio booking with dedicated engineer',NULL,'[\"Complete studio booking\", \"Dedicated sound engineer\", \"Priority support\", \"Custom production workflow\"]',0,1,0,1,4,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (5,4,'','Distribution','Release Plan',199.00,NULL,'Single song distribution',NULL,'[\"Single song distribution\", \"Global music platforms\", \"Quarterly royalty payments\", \"90% streaming revenue share\"]',1,1,0,1,1,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (6,4,'','Distribution','Artist Unlimited',1199.00,NULL,'Unlimited releases for one artist',NULL,'[\"Unlimited song releases\", \"One artists\", \"YouTube Content ID\", \"80% streaming revenue share\"]',0,1,0,1,2,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (7,4,'','Distribution','PRO Label',7999.00,NULL,'Label registration with unlimited artists',NULL,'[\"Label registration\", \"Unlimited releases\", \"Unlimited artists\", \"90% streaming revenue share\"]',0,1,0,1,3,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (8,4,'','Distribution','Limitless Label',14999.00,NULL,'Label support with monthly royalties',NULL,'[\"Unlimited song release\", \"Monthly royalty payment\", \"Label support\", \"80% revenue share\"]',0,1,0,1,4,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (9,1,'','Membership','Basic',499.00,NULL,'Artist profile with up to 3 service listings','New artists, singers, producers, lyricists, musicians, DJs, editors and freelancers.','[\"Professional Artist Profile\", \"Portfolio Upload\", \"Up to 3 Service Listings\", \"Client Contact Form\", \"Apply for Projects\", \"Community Access\", \"Email Support\", \"Official BDC Artist ID\"]',1,1,0,1,1,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (10,1,'','Membership','Standard',999.00,NULL,'Verified profile with priority project access','Freelance artists, bands and growing creative professionals.','[\"Everything in Basic\", \"Verified Artist Profile\", \"Up to 10 Service Listings\", \"Featured Search Listing\", \"Priority Project Access\", \"Social Media Promotion\", \"WhatsApp Support\", \"Artist Certificate\"]',0,1,0,1,2,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (11,1,'','Membership','Premium',2499.00,NULL,'Featured artist with a dedicated artist manager','Professional artists, influencers, bands and music businesses.','[\"Everything in Standard\", \"Homepage Featured Artist\", \"Premium Verification Badge\", \"Unlimited Service Listings\", \"Priority Client Leads\", \"Dedicated Artist Manager\", \"Monthly Promotion\", \"Premium Support\"]',0,1,0,1,3,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (12,1,'','Membership','Verified Pro',4999.00,NULL,'Multi-artist and company management','Music labels, agencies, production houses and established creative businesses.','[\"Multi-Artist Management\", \"Company Profile\", \"Unlimited Team Members\", \"Unlimited Service Listings\", \"Dedicated Account Manager\", \"Marketing Campaigns\", \"Recruitment Support\", \"Corporate Partnerships\"]',0,1,0,1,4,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (13,3,'singing','Singing','Basic',2999.00,NULL,'1 month, 8 classes',NULL,'[\"Duration: 1 Month\", \"Classes: 8\", \"Mode: Online / Offline\", \"Vocal warm-up\", \"Breathing techniques\", \"Basic voice training\", \"Alankars\", \"Pitch and rhythm\", \"Beginner singing exercises\", \"Practice material\", \"Certificate\", \"WhatsApp support\"]',1,1,0,1,1,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (14,3,'singing','Singing','Standard',8999.00,NULL,'3 months, 24 classes',NULL,'[\"Duration: 3 Months\", \"Classes: 24\", \"Voice training\", \"Bollywood singing\", \"Classical basics\", \"Song practice\", \"Performance techniques\", \"Priority WhatsApp support\"]',0,1,0,1,2,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (15,3,'singing','Singing','Premium',17999.00,NULL,'6 months, 48 classes with studio recording',NULL,'[\"Duration: 6 Months\", \"Classes: 48\", \"Professional vocal training\", \"Advanced techniques\", \"Semi classical\", \"Stage performance\", \"Studio recording\", \"Artist grooming\", \"Personalized practice\", \"Premium support\"]',0,1,0,1,3,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (16,3,'singing','Singing','Enterprise',32999.00,NULL,'12 months, 96+ classes',NULL,'[\"Duration: 12 Months\", \"96+ classes\", \"Complete professional training\", \"Recording sessions\", \"Live performance\", \"Artist grooming\", \"Portfolio building\", \"Career guidance\", \"Dedicated mentor\"]',0,1,0,1,4,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (17,3,'music-production','Music Production','Basic',3999.00,NULL,'DAW introduction and beat making',NULL,'[\"DAW introduction\", \"Beat making\", \"MIDI basics\", \"Mixing\", \"Practice projects\"]',1,1,0,1,5,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (18,3,'music-production','Music Production','Standard',7999.00,NULL,'Advanced beat making and mastering basics',NULL,'[\"Advanced beat making\", \"Melody\", \"Chords\", \"Drum programming\", \"Recording\", \"Mastering basics\"]',0,1,0,1,6,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (19,3,'music-production','Music Production','Premium',14999.00,NULL,'Advanced mixing, mastering and arrangement',NULL,'[\"Advanced mixing\", \"Mastering\", \"Sound design\", \"Vocal processing\", \"Music arrangement\", \"Portfolio projects\"]',0,1,0,1,7,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (20,3,'music-production','Music Production','Enterprise',29999.00,NULL,'Industry production including film and OTT',NULL,'[\"Industry-level production\", \"Film music\", \"OTT music\", \"Dolby Atmos basics\", \"Music release strategy\", \"Client projects\", \"Career guidance\", \"Dedicated mentor\"]',0,1,0,1,8,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (21,3,'instrument','Instrument','Basic',2999.00,NULL,'Posture, scales and beginner songs',NULL,'[\"Posture and hand positioning\", \"Scale practice\", \"Rhythm basics\", \"Beginner songs\", \"Practice routine\"]',1,1,0,1,9,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (22,3,'instrument','Instrument','Standard',5999.00,NULL,'Chords, notation and song practice',NULL,'[\"Chords and progressions\", \"Finger exercises\", \"Notation basics\", \"Song practice\", \"Performance confidence\"]',0,1,0,1,10,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (23,3,'instrument','Instrument','Premium',9999.00,NULL,'Advanced technique and improvisation',NULL,'[\"Advanced techniques\", \"Improvisation\", \"Genre-based practice\", \"Recording readiness\", \"Personalized feedback\"]',0,1,0,1,11,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (24,3,'instrument','Instrument','Enterprise',19999.00,NULL,'Professional repertoire and stage performance',NULL,'[\"Professional repertoire\", \"Stage performance\", \"Studio preparation\", \"Portfolio building\", \"Dedicated mentor\"]',0,1,0,1,12,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (25,3,'video-editing','Video Editing','Basic',3999.00,NULL,'Timeline editing and export settings',NULL,'[\"Software introduction\", \"Timeline editing\", \"Cuts and transitions\", \"Audio sync\", \"Export settings\"]',1,1,0,1,13,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (26,3,'video-editing','Video Editing','Standard',7999.00,NULL,'Story flow, colour correction and reels',NULL,'[\"Story flow\", \"Color correction\", \"Text and titles\", \"Reels and shorts editing\", \"Project workflow\"]',0,1,0,1,14,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (27,3,'video-editing','Video Editing','Premium',14999.00,NULL,'Colour grading, motion graphics and sound design',NULL,'[\"Advanced color grading\", \"Motion graphics basics\", \"Music video editing\", \"Sound design\", \"Portfolio projects\"]',0,1,0,1,15,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (28,3,'video-editing','Video Editing','Enterprise',29999.00,NULL,'Commercial and multi-camera editing',NULL,'[\"Commercial editing workflow\", \"Multi-camera editing\", \"Brand video packaging\", \"Client projects\", \"Career guidance\"]',0,1,0,1,16,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (29,6,'','Membership','Author / Composer',2499.00,NULL,'Register original works as an author or composer',NULL,'[\"Registration of original compositions\", \"Author and composer royalty split\", \"Copyright registration support\"]',1,1,0,1,1,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (30,6,'','Membership','Publisher',4999.00,NULL,'Register a catalogue or publish on behalf of others',NULL,'[\"Publisher registration\", \"Catalogue-level rights management\", \"Publishing administration support\"]',0,1,0,1,2,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (91,2,'recording','Recording','Basic',1000.00,'/hr','Hourly booth time with an engineer','Single songs, demos and quick tracking','[\"1 hour in the booth\", \"Recording engineer\", \"Raw WAV files\", \"Basic cleanup\"]',0,1,0,0,5,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (92,2,'recording','Recording','Standard',2500.00,'/session','Half-day tracking session','EPs and singles with multiple takes','[\"Up to 4 hours\", \"Recording engineer\", \"Multiple takes\", \"Basic editing\"]',0,1,0,0,6,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (93,2,'recording','Recording','Premium',5000.00,'/session','Full-day session with overdubs','Full tracks with layered vocals and instruments','[\"Full day session\", \"Vocal comping\", \"Edited stems\", \"Rush turnaround\"]',0,1,0,0,7,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (94,2,'recording','Recording','Enterprise',0.00,'Custom Quote','Multi-day or album-scale tracking','Albums, labels and long-form studio residencies','[\"Custom schedule\", \"Dedicated engineer\", \"Album-scale tracking\", \"Quote on request\"]',0,1,1,0,8,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (95,2,'music-production','Music Production','Basic',5000.00,NULL,'Beat, arrangement and rough mix','First releases and bedroom producers','[\"Beat making\", \"Arrangement\", \"Rough mix\", \"1 revision\"]',0,1,0,0,9,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (96,2,'music-production','Music Production','Standard',10000.00,NULL,'Full production with vocals','Singles ready for release','[\"Full arrangement\", \"Vocal production\", \"Mix-ready stems\", \"2 revisions\"]',0,1,0,0,10,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (97,2,'music-production','Music Production','Premium',15000.00,NULL,'Premium production with sound design','Commercial singles and brand tracks','[\"Custom sound design\", \"Vocal editing\", \"Detailed mix prep\", \"3 revisions\"]',0,1,0,0,11,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (98,2,'music-production','Music Production','Enterprise',40000.00,NULL,'Complete production with a dedicated producer','Labels, albums and campaign releases','[\"Dedicated producer\", \"Unlimited revisions\", \"Stem delivery\", \"Priority schedule\"]',0,1,0,0,12,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (99,2,'mixing','Mixing','Basic',3000.00,NULL,'Straightforward stereo mix','Demos and single-track projects','[\"Stereo mix\", \"1 revision\", \"WAV and MP3 delivery\"]',0,1,0,0,13,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (100,2,'mixing','Mixing','Standard',6000.00,NULL,'Stereo mix with an instrumental version','Releases that need clean and vocal versions','[\"Stereo mix\", \"Instrumental version\", \"3 revisions\", \"Radio-ready balance\"]',0,1,0,0,14,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (101,2,'mixing','Mixing','Premium',9000.00,NULL,'Detailed mix with stem mastering prep','Competitive releases and sync submissions','[\"Detailed mix\", \"Stem mastering prep\", \"5 revisions\", \"Reference matching\"]',0,1,0,0,15,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (102,2,'mixing','Mixing','Enterprise',20000.00,NULL,'Dedicated mix engineer','Albums and long-form catalogues','[\"Dedicated mix engineer\", \"Unlimited revisions\", \"Atmos-ready stems\", \"Priority delivery\"]',0,1,0,0,16,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (103,2,'mastering','Mastering','Basic',2500.00,NULL,'Streaming-ready master','One finished track','[\"Streaming master\", \"1 revision\", \"WAV and MP3 delivery\"]',0,1,0,0,17,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (104,2,'mastering','Mastering','Standard',5000.00,NULL,'Masters for up to four tracks','EPs and singles with versions','[\"Master for up to 4 tracks\", \"Loudness compliance\", \"2 revisions\"]',0,1,0,0,18,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (105,2,'mastering','Mastering','Premium',7500.00,NULL,'Masters for up to ten tracks','Albums and full projects','[\"Master for up to 10 tracks\", \"Analog-style chain\", \"3 revisions\"]',0,1,0,0,19,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (106,2,'mastering','Mastering','Enterprise',15000.00,NULL,'Album mastering with a dedicated engineer','Label releases and catalogue remasters','[\"Album mastering\", \"Dedicated mastering engineer\", \"Unlimited revisions\", \"DDP delivery\"]',0,1,0,0,20,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (107,2,'video-production','Video Production','Basic',10000.00,NULL,'Single-camera shoot with full HD delivery','Simple promo and performance videos','[\"1 Professional Camera\", \"Full HD Recording\", \"1 Free Revision\", \"Delivery in 5-7 Working Days\"]',0,1,0,0,21,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (108,2,'video-production','Video Production','Standard',25000.00,NULL,'Two-camera shoot with 4K delivery','Brand films and studio sessions','[\"1-2 Professional Cameras\", \"Full HD / 4K Recording\", \"2 Free Revisions\", \"Delivery in 3-5 Working Days\"]',0,1,0,0,22,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (109,2,'video-production','Video Production','Premium',50000.00,NULL,'Multi-camera production with priority delivery','Campaigns and premium releases','[\"Multi-camera Setup\", \"4K Ultra HD Delivery\", \"Unlimited Minor Revisions\", \"Priority Delivery\"]',0,1,0,0,23,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (110,2,'video-production','Video Production','Enterprise',100000.00,NULL,'Commercial production workflow','Commercial films and long-form productions','[\"Custom Camera Setup\", \"Dedicated Project Manager\", \"Priority Timeline\", \"Commercial Production Workflow\"]',0,1,0,0,24,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (111,2,'video-editing','Video Editing','Basic',3000.00,NULL,'Timeline edit and export settings','Short clips and rough cuts','[\"Timeline edit\", \"Cuts and transitions\", \"1 revision\", \"HD export\"]',0,1,0,0,25,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (112,2,'video-editing','Video Editing','Standard',8000.00,NULL,'Edited storyline with colour correction','Social films and music content','[\"Edited storyline\", \"Colour correction\", \"2 revisions\", \"HD / 4K export\"]',0,1,0,0,26,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (113,2,'video-editing','Video Editing','Premium',15000.00,NULL,'Advanced grade with sound design','Polished releases and client work','[\"Advanced grade\", \"Sound design\", \"3 revisions\", \"4K delivery\"]',0,1,0,0,27,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (114,2,'video-editing','Video Editing','Enterprise',30000.00,NULL,'Dedicated editor with multi-format delivery','Agencies and high-volume channels','[\"Dedicated editor\", \"Unlimited revisions\", \"Multi-format delivery\", \"Priority turnaround\"]',0,1,0,0,28,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (115,2,'music-video','Music Video','Basic',20000.00,NULL,'Concept board and single location','Independent single releases','[\"Concept board\", \"Single location\", \"1 revision\", \"HD delivery\"]',0,1,0,0,29,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (116,2,'music-video','Music Video','Standard',50000.00,NULL,'Storyboarded shoot across one or two locations','Growing artists with a release plan','[\"Storyboard\", \"1-2 locations\", \"2 revisions\", \"4K delivery\"]',0,1,0,0,30,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (117,2,'music-video','Music Video','Premium',100000.00,NULL,'Full creative direction with VFX passes','Label singles and premium campaigns','[\"Full creative direction\", \"Multi-location\", \"VFX passes\", \"4K delivery\"]',0,1,0,0,31,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (118,2,'music-video','Music Video','Enterprise',250000.00,NULL,'Crew, cast and studio build','Flagship videos and commercial releases','[\"Crew and cast\", \"Studio build\", \"Unlimited minor revisions\", \"Cinema-grade delivery\"]',0,1,0,0,32,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (119,2,'social-media','Social Media Videos','Basic',1500.00,NULL,'One captioned vertical video','Testing short-form content','[\"1 vertical video\", \"Captioned\", \"1 revision\"]',0,1,0,0,33,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (120,2,'social-media','Social Media Videos','Standard',3500.00,NULL,'Three vertical videos with hooks','Consistent weekly posting','[\"3 vertical videos\", \"Captions and hooks\", \"2 revisions\"]',0,1,0,0,34,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (121,2,'social-media','Social Media Videos','Premium',7500.00,NULL,'Eight platform-specific videos','Active campaigns and launches','[\"8 videos\", \"Platform-specific edits\", \"3 revisions\"]',0,1,0,0,35,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (122,2,'social-media','Social Media Videos','Enterprise',15000.00,NULL,'Monthly content pack with a dedicated editor','Brands and labels running always-on content','[\"Monthly content pack\", \"Dedicated editor\", \"Unlimited minor revisions\"]',0,1,0,0,36,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (123,2,'motion-graphics','Motion Graphics','Basic',5000.00,NULL,'Logo animation and lower thirds','Channels that need consistent branding','[\"Logo animation\", \"Lower thirds\", \"1 revision\"]',0,1,0,0,37,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (124,2,'motion-graphics','Motion Graphics','Standard',12000.00,NULL,'Animated explainer with custom icons','Product and service explainers','[\"Animated explainer\", \"Custom icons\", \"2 revisions\"]',0,1,0,0,38,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (125,2,'motion-graphics','Motion Graphics','Premium',25000.00,NULL,'Full motion package with character animation','Campaigns and series content','[\"Full motion package\", \"Character animation\", \"3 revisions\"]',0,1,0,0,39,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (126,2,'motion-graphics','Motion Graphics','Enterprise',50000.00,NULL,'Brand motion system','Brands building an animated identity','[\"Brand motion system\", \"Dedicated animator\", \"Unlimited revisions\"]',0,1,0,0,40,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (127,2,'youtube-services','YouTube Services','Basic',3000.00,NULL,'One edited video with a thumbnail','New channels finding their format','[\"1 video edit\", \"Thumbnail\", \"1 revision\"]',0,1,0,0,41,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (128,2,'youtube-services','YouTube Services','Standard',8000.00,NULL,'Four edits with custom thumbnails','Channels publishing weekly','[\"4 video edits\", \"Custom thumbnails\", \"2 revisions\"]',0,1,0,0,42,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (129,2,'youtube-services','YouTube Services','Premium',15000.00,NULL,'Eight edits plus a channel trailer','Channels scaling their output','[\"8 video edits\", \"Channel trailer\", \"3 revisions\"]',0,1,0,0,43,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (130,2,'youtube-services','YouTube Services','Enterprise',30000.00,NULL,'Monthly channel management','Brands and media companies','[\"Monthly channel management\", \"Dedicated editor\", \"Unlimited revisions\"]',0,1,0,0,44,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (131,2,'event-videos','Event Videos','Basic',15000.00,NULL,'Coverage of up to three hours','Small events and private functions','[\"Coverage up to 3 hours\", \"1 editor\", \"1 revision\", \"HD delivery\"]',0,1,0,0,45,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (132,2,'event-videos','Event Videos','Standard',35000.00,NULL,'Coverage of up to six hours','Conferences, launches and showcases','[\"Coverage up to 6 hours\", \"2 cameras\", \"2 revisions\", \"HD / 4K delivery\"]',0,1,0,0,46,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (133,2,'event-videos','Event Videos','Premium',70000.00,NULL,'Full-day multi-camera coverage','Festivals, tours and full-day programmes','[\"Full-day coverage\", \"Multi-camera\", \"Highlight and full film\", \"3 revisions\"]',0,1,0,0,47,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (134,2,'event-videos','Event Videos','Enterprise',150000.00,NULL,'Multi-day event crew with a dedicated producer','Tours, festivals and corporate programmes','[\"Multi-day event crew\", \"Dedicated producer\", \"Unlimited revisions\", \"Priority delivery\"]',0,1,0,0,48,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (135,2,'corporate-videos','Corporate Videos','Basic',20000.00,NULL,'Script support and a single location','Company profiles and internal films','[\"Script support\", \"Single location\", \"1 revision\", \"HD delivery\"]',0,1,0,0,49,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (136,2,'corporate-videos','Corporate Videos','Standard',50000.00,NULL,'Script, storyboard and professional talent','Recruitment and product films','[\"Script and storyboard\", \"Professional talent\", \"2 revisions\", \"4K delivery\"]',0,1,0,0,50,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (137,2,'corporate-videos','Corporate Videos','Premium',100000.00,NULL,'Full production with motion graphics','Brand campaigns and case studies','[\"Full production\", \"Motion graphics\", \"3 revisions\", \"4K delivery\"]',0,1,0,0,51,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (138,2,'corporate-videos','Corporate Videos','Enterprise',200000.00,NULL,'Campaign-scale production','Multi-film campaigns and long-term retainers','[\"Campaign-scale production\", \"Dedicated project manager\", \"Unlimited revisions\", \"Commercial usage rights\"]',0,1,0,0,52,'2026-09-27 09:12:55');
-- --------------------------------------------------------

--
-- Table structure for table `booking_items`
--
-- One booking can now carry several line items instead of exactly one package.
-- booking_id + plan_id is unique so the same sub-service cannot be charged
-- twice in one order, both foreign keys cascade with the row they belong to,
-- and `bookings.plan_id` / `plan_name` / `plan_group` stay as the snapshot of
-- the first line so every existing reader keeps working unchanged.
--

DROP TABLE IF EXISTS `booking_items`;
CREATE TABLE IF NOT EXISTS `booking_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plan_id` int NOT NULL,
  `plan_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plan_group` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `plan_group_label` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `qty` int NOT NULL DEFAULT '1',
  `line_total` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_bi` (`booking_id`,`plan_id`),
  KEY `idx_bi_plan` (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Backfill: every order placed before booking_items existed carries its package
-- as a single line at the price that order was charged, so old orders render
-- identically to new ones.
--
INSERT INTO `booking_items`
  (`booking_id`, `plan_id`, `plan_name`, `plan_group`, `plan_group_label`, `unit_price`, `qty`, `line_total`)
SELECT b.booking_id, b.plan_id, COALESCE(b.plan_name, ''), b.plan_group, b.plan_group_label,
       b.subtotal, 1, b.subtotal
  FROM `bookings` b
 WHERE b.plan_id IS NOT NULL;

-- --------------------------------------------------------

--
-- Table structure for table `service_records`
--

DROP TABLE IF EXISTS `service_records`;
CREATE TABLE IF NOT EXISTS `service_records` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_id` int NOT NULL,
  `headline` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_headline` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `progress` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Confirmed',
  `starts_on` date DEFAULT NULL,
  `ends_on` date DEFAULT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sr_booking` (`booking_id`),
  KEY `idx_sr_service` (`service_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_records`
--

INSERT INTO `service_records` (`id`, `booking_id`, `service_id`, `headline`, `sub_headline`, `progress`, `starts_on`, `ends_on`, `location`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'BDCM-A1B2C3', 1, 'Abhinav Singh', 'Solo Vocalist - Live Show', 'Completed', '2026-08-16', '2026-08-16', 'Kanpur, Uttar Pradesh', 'Setlist confirmed. Backline provided by the artist.', '2026-09-27 00:47:20', '2026-09-27 00:47:20'),
(2, 'BDCM-E5F6G7', 2, 'Music Video - Cinematic Cut', 'Full Production Package', 'In Production', '2026-09-02', '2026-09-22', '', 'Shoot scheduled in Delhi. 2 days on location.', '2026-09-27 00:47:20', '2026-09-27 00:47:20'),
(3, 'BDCM-Q7R8S9', 3, 'Batch 2026-04 - Abhinav Singh', 'Intermediate', 'Completed', '2026-07-21', '2026-08-18', 'https://meet.google.com/example-class', '8 sessions completed. All recordings shared.', '2026-09-27 00:47:20', '2026-09-27 00:47:20'),
(5, 'BDCM-172421', 6, '', 'Author-Composer', 'Application Received', '2026-09-11', NULL, '', 'PAN and address proof received. IPRS submission pending.', '2026-09-27 00:47:20', '2026-09-27 00:47:20');

-- --------------------------------------------------------

--
-- Table structure for table `uploaded_files`
--

DROP TABLE IF EXISTS `uploaded_files`;
CREATE TABLE IF NOT EXISTS `uploaded_files` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `original_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` int UNSIGNED NOT NULL DEFAULT '0',
  `uploaded_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_booking_id` (`booking_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `uploaded_files`
--

-- Seed rows only; none of these files exist on disk. file_path mirrors the
-- layout booking_promote_uploads() actually writes (data/uploads/booking/<id>/)
-- and the names are neutral placeholders rather than real-looking documents.
INSERT INTO `uploaded_files` (`id`, `booking_id`, `field_name`, `original_name`, `stored_name`, `file_path`, `mime_type`, `file_size`, `uploaded_at`) VALUES
(1, 'BDCM-E5F6G7', 'project_upload', 'track_demo_v1.wav', 'E5F6G7_track_demo_v1.wav', 'data/uploads/booking/BDCM-E5F6G7/E5F6G7_track_demo_v1.wav', 'audio/wav', 5242880, '2026-09-01 09:12:00'),
(2, 'BDCM-E5F6G7', 'project_upload', 'reference_video.mp4', 'E5F6G7_reference_video.mp4', 'data/uploads/booking/BDCM-E5F6G7/E5F6G7_reference_video.mp4', 'video/mp4', 15728640, '2026-09-01 09:12:00'),
(3, 'BDCM-172401', 'audio_file', 'demo_master_track.wav', '172401_demo_master_track.wav', 'data/uploads/booking/BDCM-172401/172401_demo_master_track.wav', 'audio/wav', 41943040, '2026-08-28 14:32:00'),
(4, 'BDCM-172401', 'cover_artwork', 'demo_cover_art.jpg', '172401_demo_cover_art.jpg', 'data/uploads/booking/BDCM-172401/172401_demo_cover_art.jpg', 'image/jpeg', 2097152, '2026-08-28 14:32:00'),
(5, 'BDCM-172411', 'audio_file', 'demo_track_1.wav', '172411_demo_track_1.wav', 'data/uploads/booking/BDCM-172411/172411_demo_track_1.wav', 'audio/wav', 62914560, '2026-09-10 09:02:00'),
(6, 'BDCM-172411', 'cover_artwork', 'demo_album_cover.png', '172411_demo_album_cover.png', 'data/uploads/booking/BDCM-172411/172411_demo_album_cover.png', 'image/png', 3145728, '2026-09-10 09:02:00'),
(7, 'BDCM-172420', 'pan_card', 'sample_id_proof_1.pdf', '172420_sample_id_proof_1.pdf', 'data/uploads/booking/BDCM-172420/172420_sample_id_proof_1.pdf', 'application/pdf', 524288, '2026-09-03 08:47:00'),
(8, 'BDCM-172420', 'address_proof', 'sample_address_proof_1.jpg', '172420_sample_address_proof_1.jpg', 'data/uploads/booking/BDCM-172420/172420_sample_address_proof_1.jpg', 'image/jpeg', 1048576, '2026-09-03 08:47:00'),
(9, 'BDCM-172421', 'pan_card', 'sample_id_proof_2.pdf', '172421_sample_id_proof_2.pdf', 'data/uploads/booking/BDCM-172421/172421_sample_id_proof_2.pdf', 'application/pdf', 614400, '2026-09-10 14:02:00'),
(10, 'BDCM-172421', 'address_proof', 'sample_address_proof_2.pdf', '172421_sample_address_proof_2.pdf', 'data/uploads/booking/BDCM-172421/172421_sample_address_proof_2.pdf', 'application/pdf', 716800, '2026-09-10 14:02:00');


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('customer','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `profile_picture` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'website',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email` (`email`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--
-- DEMO ACCOUNTS ONLY. Every row below is an @example.com placeholder and shares
-- one bcrypt hash of the literal string `password`, so admin@bdcmusic.in and all
-- seeded customers sign in with password `password`. Change it through the app
-- (or UPDATE users SET password_hash = ... ) before this database is reachable
-- by anything other than localhost.
--

INSERT INTO `users` (`id`, `name`, `email`, `mobile`, `password_hash`, `role`, `profile_picture`, `source`, `created_at`, `updated_at`) VALUES
('CUST-A1B2C3D4', 'Rahul Sharma', 'rahul@example.com', '9876543210', '$2y$10$2nLILx5p45D7o/YouEPFE.M7cQgevs48M2fru6nbiufPTa8hKLf3y', 'customer', NULL, 'website', '2026-09-18 19:49:03', '2026-09-18 19:49:03'),
('CUST-ADMIN0001', 'BDC Admin', 'admin@bdcmusic.in', '9999999999', '$2y$10$2nLILx5p45D7o/YouEPFE.M7cQgevs48M2fru6nbiufPTa8hKLf3y', 'admin', NULL, 'website', '2026-09-18 19:49:03', '2026-09-18 19:49:03'),
('CUST-C9D0E1F2', 'Deepika Nair', 'deepika@example.com', '9119887766', '$2y$10$2nLILx5p45D7o/YouEPFE.M7cQgevs48M2fru6nbiufPTa8hKLf3y', 'customer', NULL, 'guest_booking', '2026-09-18 19:49:03', '2026-09-18 19:49:03'),
('CUST-E5F6G7H8', 'Priya Patel', 'priya@example.com', '9988776655', '$2y$10$2nLILx5p45D7o/YouEPFE.M7cQgevs48M2fru6nbiufPTa8hKLf3y', 'customer', NULL, 'website', '2026-09-18 19:49:03', '2026-09-18 19:49:03'),
('CUST-G3H4I5J6', 'Arjun Reddy', 'arjun@example.com', '9228776655', '$2y$10$2nLILx5p45D7o/YouEPFE.M7cQgevs48M2fru6nbiufPTa8hKLf3y', 'customer', NULL, 'website', '2026-09-18 19:49:03', '2026-09-18 19:49:03'),
('CUST-I9J0K1L2', 'Amit Kumar', 'amit@example.com', '9112233445', '$2y$10$2nLILx5p45D7o/YouEPFE.M7cQgevs48M2fru6nbiufPTa8hKLf3y', 'customer', NULL, 'guest_booking', '2026-09-18 19:49:03', '2026-09-18 19:49:03'),
('CUST-K7L8M9N0', 'Kavita Desai', 'kavita@example.com', '9337665544', '$2y$10$2nLILx5p45D7o/YouEPFE.M7cQgevs48M2fru6nbiufPTa8hKLf3y', 'customer', NULL, 'website', '2026-09-18 19:49:03', '2026-09-18 19:49:03'),
('CUST-M3N4O5P6', 'Neha Gupta', 'neha@example.com', '9001122334', '$2y$10$2nLILx5p45D7o/YouEPFE.M7cQgevs48M2fru6nbiufPTa8hKLf3y', 'customer', NULL, 'website', '2026-09-18 19:49:03', '2026-09-18 19:49:03'),
('CUST-Q7R8S9T0', 'Vikram Singh', 'vikram@example.com', '9871234567', '$2y$10$2nLILx5p45D7o/YouEPFE.M7cQgevs48M2fru6nbiufPTa8hKLf3y', 'customer', NULL, 'guest_booking', '2026-09-18 19:49:03', '2026-09-18 19:49:03'),
('CUST-U1V2W3X4', 'Sonia Verma', 'sonia@example.com', '9911223344', '$2y$10$2nLILx5p45D7o/YouEPFE.M7cQgevs48M2fru6nbiufPTa8hKLf3y', 'customer', NULL, 'website', '2026-09-18 19:49:03', '2026-09-18 19:49:03'),
('CUST-Y5Z6A7B8', 'Ravi Joshi', 'ravi@example.com', '9009887766', '$2y$10$2nLILx5p45D7o/YouEPFE.M7cQgevs48M2fru6nbiufPTa8hKLf3y', 'customer', NULL, 'website', '2026-09-18 19:49:03', '2026-09-18 19:49:03');

--
-- Login throttling (D4)
--
-- Records failed sign-in attempts so password guessing can be slowed. Keyed by
-- the lowercased email so one account cannot be attacked from many IPs, and by
-- the client IP so one host cannot spray many accounts.
--

DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `scope` enum('email','ip') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'email',
  `attempted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `succeeded` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_identifier_scope_time` (`identifier`,`scope`,`attempted_at`),
  KEY `idx_time` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `artists`
--
ALTER TABLE `artists`
  ADD CONSTRAINT `fk_artist_category` FOREIGN KEY (`category_id`) REFERENCES `artist_categories` (`id`);

--
-- Constraints for table `artist_pricing`
--
ALTER TABLE `artist_pricing`
  ADD CONSTRAINT `fk_pricing_artist` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_bookings_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bookings_plan` FOREIGN KEY (`plan_id`) REFERENCES `service_plans` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bookings_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Constraints for table `booking_addons`
--
-- The addon_id foreign key to `service_addons` was dropped with that table.
-- Rows here are the snapshot of orders placed before add-ons left the
-- catalogue, so the id is now just a historical reference.
ALTER TABLE `booking_addons`
  ADD CONSTRAINT `fk_ba_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE;

--
-- Constraints for table `booking_payments`
--
ALTER TABLE `booking_payments`
  ADD CONSTRAINT `fk_bp_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE;

--
-- Constraints for table `booking_items`
--
ALTER TABLE `booking_items`
  ADD CONSTRAINT `fk_bi_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bi_plan` FOREIGN KEY (`plan_id`) REFERENCES `service_plans` (`id`);

--
-- Constraints for table `releases`
--
ALTER TABLE `releases`
  ADD CONSTRAINT `fk_release_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_release_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `release_artists`
--
ALTER TABLE `release_artists`
  ADD CONSTRAINT `fk_ra_release` FOREIGN KEY (`release_id`) REFERENCES `releases` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `release_history`
--
ALTER TABLE `release_history`
  ADD CONSTRAINT `fk_rh_release` FOREIGN KEY (`release_id`) REFERENCES `releases` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `release_platform_links`
--
ALTER TABLE `release_platform_links`
  ADD CONSTRAINT `fk_rpl_release` FOREIGN KEY (`release_id`) REFERENCES `releases` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `release_tracks`
--
ALTER TABLE `release_tracks`
  ADD CONSTRAINT `fk_rt_release` FOREIGN KEY (`release_id`) REFERENCES `releases` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_plans`
--
ALTER TABLE `service_plans`
  ADD CONSTRAINT `fk_plan_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_records`
--
ALTER TABLE `service_records`
  ADD CONSTRAINT `fk_sr_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sr_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Constraints for table `uploaded_files`
--
ALTER TABLE `uploaded_files`
  ADD CONSTRAINT `fk_files_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE;
COMMIT;
SET FOREIGN_KEY_CHECKS=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
