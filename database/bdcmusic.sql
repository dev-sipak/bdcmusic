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
  `experience_years` tinyint(3) unsigned DEFAULT NULL,
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

INSERT INTO `artists` (`id`, `category_id`, `name`, `slug`, `image`, `location`, `experience_years`, `bio`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 11, 'Abhinav Singh', 'abhinav-singh', 'assets/images/artist/artist-1.png', 'Kanpur, Uttar Pradesh', 8, 'Independent singer blending Bollywood melodies with Hindustani classical vocals. Known for a style reminiscent of Arijit Singh and Darshan Raval, with Sufi influences. Trained in acoustic and fusion singing, and has been teaching vocals and classical music since 2021. 8 years in the industry.', 1, '2026-09-20 01:13:25', '2026-09-20 01:13:25'),
(2, 11, 'Anshuman Nigaar', 'anshuman-nigaar', NULL, 'Khalilabad, Gorakhpur', 7, 'Multi-talented artist - singer, lyricist, composer, scriptwriter, and live performer. A complete entertainment package backed by 7 years of hands-on experience in the music industry.', 1, '2026-09-20 01:13:25', '2026-09-20 01:13:25'),
(3, 11, 'Gunjan Jha', 'gunjan-jha', 'assets/images/artist/gunjan-jha.webp', 'Delhi, India', NULL, 'Singer, music director, and vocal trainer with credits on Shabad (Pankaj Udhas, Times Music) and Mohabbat Me Tere Sanam (Kumar Sanu, Vusic Records). Available for live shows, studio sessions, and online classes.', 1, '2026-09-20 01:13:25', '2026-09-20 01:16:03'),
(4, 11, 'Nishaad', 'nishaad', 'assets/images/artist/nishaad.webp', 'Haryana, India', 5, 'Versatile singer, lyricist, and music composer from Haryana. Brings 5 years of dedicated experience in crafting original music and live performances.', 1, '2026-09-20 01:13:25', '2026-09-20 01:16:03'),
(5, 11, 'Alaap Gahlaut', 'alaap-gahlaut', 'assets/images/artist/alaap-gahlaut.webp', 'New Delhi, India', 10, 'Singer and short-form video creator with a decade of experience in the music industry. Combines vocal talent with a strong presence in the digital content space.', 1, '2026-09-20 01:13:25', '2026-09-20 01:16:03'),
(6, 8, 'Music PWN', 'music-pwn', NULL, 'Delhi, India', 5, 'Music producer with 5 years of experience spanning multiple genres. Specializes in beat production, arrangement, and mixing for independent artists looking to create original tracks.', 1, '2026-09-20 01:13:25', '2026-09-20 01:13:25'),
(7, 8, 'Rohit Tiwari', 'rohit-tiwari', 'assets/images/artist/rohit-tiwari.webp', 'Chhatarpur, Delhi', 8, 'Seasoned music producer with 8 years of experience producing tracks across diverse genres. Dedicated to helping artists bring their musical vision to life from concept to final master.', 1, '2026-09-20 01:13:25', '2026-09-20 01:16:02'),
(8, 8, 'Alagu Chandhiran', 'alagu-chandhiran', NULL, 'India', 5, 'Music producer and background scoring specialist with 5 years of experience. Skilled in producing tracks for independent releases and short film soundtracks.', 1, '2026-09-20 01:13:25', '2026-09-20 01:13:25'),
(9, 10, 'Susma Das', 'susma-das', 'assets/images/artist/susma-das.webp', 'Kolkata, Bengal', NULL, 'Content creator and reels specialist from Kolkata. Creates engaging short-form videos and offers professional short video services for brands and artists.', 1, '2026-09-20 01:13:25', '2026-09-20 01:16:03'),
(10, 10, 'Knk Best Beats', 'knk-best-beats', NULL, 'Noida, Uttar Pradesh', NULL, 'Reels creator and short-form video specialist. Produces trending content and offers short video production services for music promotions and brand collaborations.', 1, '2026-09-20 01:13:25', '2026-09-20 01:13:25'),
(11, 10, 'Sitara', 'sitara', 'assets/images/artist/sitara.webp', 'Noida, India', NULL, 'Dynamic reels creator with a flair for performance-based content. Offers short video services for artists, brands, and social media campaigns.', 1, '2026-09-20 01:13:25', '2026-09-20 01:16:03'),
(12, 10, 'Ayush Sachdeva', 'ayush-sachdeva', 'assets/images/artist/ayush-sachdeva.webp', 'Ghaziabad, India', NULL, 'Reels creator, short-form video specialist, and model. Combines on-screen presence with content creation expertise for music videos, brand shoots, and social media campaigns.', 1, '2026-09-20 01:13:25', '2026-09-20 01:16:03'),
(13, 1, 'Basant Pathak', 'basant-pathak', '', 'Uttar Pradesh, India', 5, 'Versatile actor with 5 years of experience in lead and supporting roles. Also active in modelling and commercial shoots across UP and nearby regions.', 1, '2026-09-20 01:13:25', '2026-09-20 19:19:41'),
(14, 1, 'Rajendra Rajawat', 'rajendra-rajawat', 'assets/images/artist/rajendra-rajawat.webp', 'Delhi, India', 2, 'Actor and model with 2 years of on-screen experience. Has appeared in multiple video projects and offers short-form video services for music and commercial content.', 1, '2026-09-20 01:13:25', '2026-09-20 01:16:03'),
(15, 5, 'Jatin Shrivastav', 'jatin-shrivastav', NULL, 'Delhi, India', 3, 'Director specializing in music videos, pre-wedding shoots, wedding films, and event coverage. 3 years of professional experience delivering cinematic content across India.', 1, '2026-09-20 01:13:25', '2026-09-20 01:13:25'),
(16, 5, 'Lalit Thakur', 'lalit-thakur', 'assets/images/artist/lalit-thakur.webp', 'Delhi, India', 10, 'Experienced director with a decade in music videos, wedding cinematography, and event coverage. Also offers drone videography services. Available for projects across India.', 1, '2026-09-20 01:13:25', '2026-09-20 01:16:03'),
(17, 5, 'Amit Sati', 'amit-sati', 'assets/images/artist/amit-sati.webp', 'Rishikesh, Uttarakhand', NULL, 'Multi-faceted director offering music videos, wedding films, travel content, aerial videography, real estate shoots, vlogs, and drone mapping. Full-service production available pan-India.', 1, '2026-09-20 01:13:25', '2026-09-20 01:16:03'),
(18, 6, 'Vishal Kumar', 'vishal-kumar', 'assets/images/artist/vishal-kumar.webp', 'Delhi, India', 6, 'Lead guitarist with 6 years of live performance experience. Has performed at numerous shows and events across India. Available for studio sessions and live gigs.', 1, '2026-09-20 01:13:25', '2026-09-20 01:16:03');

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
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_id`, `invoice_no`, `customer_id`, `customer_name`, `customer_email`, `customer_phone`, `customer_whatsapp`, `customer_type`, `service_id`, `service_name`, `service_slug`, `plan_id`, `plan_name`, `plan_group`, `plan_group_label`, `meta`, `message`, `price`, `subtotal`, `addons_total`, `currency`, `status`, `payment_status`, `payment_provider`, `payment_id`, `razorpay_order_id`, `razorpay_signature`, `payment_method`, `payment_failure_reason`, `auto_create_account`, `auto_password`, `created_at`, `updated_at`, `paid_at`) VALUES
(1,'BDCM-1A0001',NULL,'CUST-A1B2C3D4','Rahul Sharma','rahul@example.com','9876543210','','registered',1,'BDC Artists Marketplace','artists-marketplace',12,'Verified Pro','','Membership','{"artist_category":"singer","experience":"4 Years, Working Professional","artist_goal":"Build an independent Hindi pop career with a verified BDC profile.","event_type":"Concert / Live Show","event_date":"2026-07-18","event_location":"Kanpur, Uttar Pradesh","budget":"Rs.25,000","portfolio":"https://rahulsharma.example.com","about":"Trained vocalist with 200+ live performances and a strong Hindi pop following.","service_type":"Membership"}','','4999.00','4999.00','0.00','INR','delivered','paid','razorpay','pay_seed_a6bf1e032be1f1','order_seed_a11675740fed03','05136c102576830ec3b56e7900a6dfaa1813176aa8ebde74dce09a9821004add','card',NULL,0,NULL,'2026-06-12 10:20:00','2026-07-19 16:40:00','2026-06-12 10:26:00'),
(2,'BDCM-1A0002',NULL,'CUST-C9D0E1F2','Deepika Nair','deepika@example.com','9119887766','','registered',1,'BDC Artists Marketplace','artists-marketplace',11,'Premium','','Membership','{"artist_category":"video-editor","experience":"6 Years, Senior Level","artist_goal":"Take on brand editing retainers and publish a verified showreel.","event_type":"Corporate Event","event_location":"Bengaluru, Karnataka","budget":"Rs.40,000","portfolio":"https://deepikanair.example.com","about":"Editor for live event multi-cam and short-form content. Mac and Premiere certified.","service_type":"Membership"}','','2499.00','2499.00','0.00','INR','processing','paid','razorpay','pay_seed_b6ae1f06de8303','order_seed_f1ec36c0164940','6d9386f94ae3eb1c05c689ced0523512d09e5f8ff6ef026636ad9aea5c8672c9','upi',NULL,0,NULL,'2026-09-04 11:12:00','2026-09-18 10:05:00','2026-09-04 11:18:00'),
(3,'BDCM-1A0003',NULL,'CUST-E5F6G7H8','Priya Patel','priya@example.com','9988776655','','registered',1,'BDC Artists Marketplace','artists-marketplace',10,'Standard','','Membership','{"artist_category":"music-producer","experience":"2 Years","artist_goal":"Find session work and beat-selling opportunities.","portfolio":"https://priyapatel.example.com","about":"Home studio producer working in hip hop and melodic trap.","service_type":"Membership"}','','999.00','999.00','0.00','INR','pending','awaiting','razorpay',NULL,'order_seed_eee7a0e94ff5be',NULL,NULL,NULL,0,NULL,'2026-09-21 16:45:00','2026-09-21 16:45:00',NULL),
(4,'BDCM-1A0004',NULL,'CUST-G3H4I5J6','Arjun Reddy','arjun@example.com','9228776655','','registered',1,'BDC Artists Marketplace','artists-marketplace',9,'Basic','','Membership','{"artist_category":"dj","artist_goal":"Get booked for club residencies and festival sets.","event_type":"Festival","event_date":"2026-12-05","event_location":"Pune, Maharashtra","budget":"Rs.12,000","about":"Open-format DJ with 8 years of club experience.","service_type":"Membership"}','','499.00','499.00','0.00','INR','hold','paid','razorpay','pay_seed_1d1d68f34779c7','order_seed_148fbbb6a39797','a5a0a473c17d676dbf500fb1c3acbe103a0ced6d96cc2b73df9e46628441817f','card',NULL,0,NULL,'2026-08-02 09:30:00','2026-08-20 14:10:00','2026-08-02 09:36:00'),
(5,'BDCM-1A0005',NULL,'CUST-I9J0K1L2','Amit Kumar','amit@example.com','9112233445','','registered',1,'BDC Artists Marketplace','artists-marketplace',12,'Verified Pro','','Membership','{"artist_category":"composer","experience":"8 Years, Senior Level","artist_goal":"Sync placements and film scoring work.","portfolio":"https://amitkumar.example.com","about":"Film and web-series composer with three released soundtracks.","service_type":"Membership"}','','4999.00','4999.00','0.00','INR','cancelled','refunded','razorpay','pay_seed_d058df23656735','order_seed_601929767aff59','1099a0ec9b260dbf5453ff66c12a855887fe6b2d5ac44039e0d2cdfcdfdb5ce9','upi',NULL,0,NULL,'2026-08-11 13:22:00','2026-08-14 11:45:00','2026-08-11 13:28:00'),
(6,'BDCM-1A0006',NULL,'CUST-M3N4O5P6','Neha Gupta','neha@example.com','9001122334','','registered',1,'BDC Artists Marketplace','artists-marketplace',11,'Premium','','Membership','{"artist_category":"lyricist","artist_goal":"Register as a BDC lyricist and get writing assignments.","portfolio":"https://nehagupta.example.com","about":"Hindi and Punjabi lyricist with three published singles.","service_type":"Membership"}','','2499.00','2499.00','0.00','INR','pending','awaiting','razorpay',NULL,'order_seed_cea39160b0a0c0',NULL,NULL,NULL,0,NULL,'2026-09-24 08:05:00','2026-09-24 08:05:00',NULL),
(7,'BDCM-1A0007',NULL,'CUST-U1V2W3X4','Sonia Verma','sonia@example.com','9911223344','','registered',1,'BDC Artists Marketplace','artists-marketplace',9,'Basic','','Membership','{"artist_category":"graphic-designer","artist_goal":"Take on album artwork and poster design work.","portfolio":"https://soniaverma.example.com","about":"Designer specialising in album art and live event posters.","service_type":"Membership"}','','499.00','499.00','0.00','INR','cancelled','cancelled','razorpay',NULL,'order_seed_7474dab8a75af7',NULL,NULL,NULL,0,NULL,'2026-09-08 19:40:00','2026-09-08 19:40:00',NULL),
(8,'BDCM-2B0001',NULL,'CUST-K7L8M9N0','Kavita Desai','kavita@example.com','9337665544','','registered',2,'Audio & Video Services','audio-video',1,'Basic','','Audio','{"service_category":"Audio","service_type":"Audio","delivery_formats":["WAV","FLAC"],"deadline":"10 working days","budget":"Rs.15,000","notes":"Five-track folk EP. Please book the same engineer for all sessions."}','','11500.00','11500.00','0.00','INR','delivered','paid','razorpay','pay_seed_804c553a2c754c','order_seed_68afea4067b89f','ed15713ad0da93149070cc3683c2973a02e75e674248269a7e2709b964c7b487','card',NULL,0,NULL,'2026-07-06 10:15:00','2026-07-25 12:00:00','2026-07-06 10:21:00'),
(9,'BDCM-2B0002',NULL,'CUST-M3N4O5P6','Neha Gupta','neha@example.com','9001122334','','registered',2,'Audio & Video Services','audio-video',2,'Standard','','Audio','{"service_category":"Video","service_type":"Audio","delivery_formats":["MP4","4K"],"deadline":"21 working days","budget":"Rs.30,000","notes":"Cinematic music video. Two shoot days, one drone insert if the weather allows."}','','23500.00','23500.00','0.00','INR','processing','paid','razorpay','pay_seed_0b098b5d90ab7e','order_seed_bac5ad579df1d1','7f7c0e64d7ea44ecd4ad7fc23aea48f9e67a534baab0d620c844380e38701f7c','upi',NULL,0,NULL,'2026-08-24 14:20:00','2026-09-22 17:30:00','2026-08-24 14:26:00'),
(10,'BDCM-2B0003',NULL,'CUST-Q7R8S9T0','Vikram Singh','vikram@example.com','9871234567','','registered',2,'Audio & Video Services','audio-video',3,'Premium','','Audio','{"service_category":"Audio","service_type":"Audio","delivery_formats":["WAV","MP3"],"deadline":"15 working days","budget":"Rs.40,000","notes":"Full album of nine tracks, same producer throughout."}','','36500.00','36500.00','0.00','INR','hold','paid','razorpay','pay_seed_f82003aec7a77c','order_seed_851309cadc1044','e245b5d3581743d4ed1b2f73c14e6531efbedb74b15f312bd81b6e465cc604d0','card',NULL,0,NULL,'2026-09-02 11:05:00','2026-09-15 09:20:00','2026-09-02 11:11:00'),
(11,'BDCM-2B0004',NULL,'CUST-U1V2W3X4','Sonia Verma','sonia@example.com','9911223344','','registered',2,'Audio & Video Services','audio-video',4,'Enterprise','','Audio','{"service_category":"Video","service_type":"Audio","delivery_formats":["MP4","Full HD","4K"],"deadline":"45 working days","budget":"Rs.90,000","notes":"Brand film plus six vertical cutdowns for social. Full production with a dedicated PM."}','','75000.00','75000.00','0.00','INR','delivered','paid','razorpay','pay_seed_03279b3de4073b','order_seed_8f06041ccb5dae','a9955b327d3e2a0654fbb93f6bbce10ec4566a561f31262fe1dafaf2d64e3aba','netbanking',NULL,0,NULL,'2026-06-18 09:50:00','2026-08-15 18:00:00','2026-06-18 09:56:00'),
(12,'BDCM-2B0005',NULL,'CUST-Y5Z6A7B8','Ravi Joshi','ravi@example.com','9009887766','','registered',2,'Audio & Video Services','audio-video',1,'Basic','','Audio','{"service_category":"Audio","service_type":"Audio","delivery_formats":["WAV","FLAC","MP3"],"deadline":"18 working days","budget":"Rs.38,000","notes":"Tracking session for a debut release. Same engineer throughout."}','','11500.00','11500.00','0.00','INR','processing','paid','razorpay','pay_seed_2a35e44c3f34b5','order_seed_99ffcbd38d81d1','043cdeb7e797100bc2820a024181f0b592b92b208a0b136779ce5b11ed5fe4e6','card',NULL,0,NULL,'2026-09-09 15:35:00','2026-09-23 11:15:00','2026-09-09 15:41:00'),
(13,'BDCM-2B0006',NULL,'CUST-A1B2C3D4','Rahul Sharma','rahul@example.com','9876543210','','registered',2,'Audio & Video Services','audio-video',3,'Premium','','Audio','{"service_category":"Audio","service_type":"Audio","delivery_formats":["WAV"],"deadline":"20 working days","budget":"Rs.36,500","notes":"Three singles, same vocalist, delivered as one batch."}','','36500.00','36500.00','0.00','INR','pending','failed','razorpay',NULL,'order_seed_43ef0d9f98274f',NULL,NULL,NULL,0,NULL,'2026-09-17 12:20:00','2026-09-17 12:26:00',NULL),
(14,'BDCM-2B0007',NULL,NULL,'Rohit Malhotra','rohit.malhotra@example.com','9812345670','9812345670','guest',2,'Audio & Video Services','audio-video',1,'Basic','','Audio','{"service_category":"Audio","service_type":"Audio","delivery_formats":["WAV"],"deadline":"7 working days","budget":"Rs.12,000","notes":"Voiceover session, home studio kit, two hours booked."}','','11500.00','11500.00','0.00','INR','delivered','paid','razorpay','pay_seed_f876cf8404ccab','order_seed_6d9c39b7186da2','3c8b5c33c94cc36cffa75d04f5444434e829f091c5dfe2633b4841ac24e201d7','card',NULL,0,NULL,'2026-08-30 17:00:00','2026-09-07 10:00:00','2026-08-30 17:06:00'),
(15,'BDCM-2B0008',NULL,'CUST-E5F6G7H8','Priya Patel','priya@example.com','9988776655','','registered',2,'Audio & Video Services','audio-video',2,'Standard','','Audio','{"service_category":"Video","service_type":"Audio","delivery_formats":["MP4","Full HD"],"deadline":"20 working days","budget":"Rs.24,000","notes":"Two-camera workshop edit, no grade pass needed yet."}','','23500.00','23500.00','0.00','INR','pending','awaiting','razorpay',NULL,'order_seed_3233ca90101d5a',NULL,NULL,NULL,0,NULL,'2026-09-25 10:10:00','2026-09-25 10:10:00',NULL),
(16,'BDCM-3C0001',NULL,'CUST-I9J0K1L2','Amit Kumar','amit@example.com','9112233445','','registered',3,'Online/Offline Classes','online-offline-classes',13,'Basic','singing','Singing','{"class_mode":"Online","level":"Beginner","age":"22","service_type":"Singing"}','','2999.00','2999.00','0.00','INR','delivered','paid','razorpay','pay_seed_1b2f1227b4e126','order_seed_6bf3f726477127','5479d8c63b7b659073d6748da2a2c10f43540e4c3f28d68a90f66621bcaea6f7','upi',NULL,0,NULL,'2026-06-22 09:00:00','2026-07-23 12:00:00','2026-06-22 09:06:00'),
(17,'BDCM-3C0002',NULL,'CUST-C9D0E1F2','Deepika Nair','deepika@example.com','9119887766','','registered',3,'Online/Offline Classes','online-offline-classes',16,'Enterprise','singing','Singing','{"class_mode":"Offline","level":"Advanced","age":"26","service_type":"Singing"}','','32999.00','32999.00','0.00','INR','processing','paid','razorpay','pay_seed_edfcbf3425edc7','order_seed_aa8c5d9079998e','7b699a475b2d1dca17459aa52b0d87ec7c0fb9c7ee0ab4183fd32bf741387084','netbanking',NULL,0,NULL,'2026-09-01 18:30:00','2026-09-20 16:00:00','2026-09-01 18:36:00'),
(18,'BDCM-3C0003',NULL,'CUST-Q7R8S9T0','Vikram Singh','vikram@example.com','9871234567','','registered',3,'Online/Offline Classes','online-offline-classes',17,'Basic','music-production','Music Production','{"class_mode":"Online","level":"Beginner","age":"31","service_type":"Music Production"}','','3999.00','3999.00','0.00','INR','delivered','paid','razorpay','pay_seed_187a9914003f47','order_seed_e7aaea14cc8371','5cb2952d79bd7b7165e4adaa14605c085ef942fa10dde956b3611e97a02f3b1c','card',NULL,0,NULL,'2026-07-14 16:05:00','2026-08-14 17:30:00','2026-07-14 16:11:00'),
(19,'BDCM-3C0004',NULL,'CUST-M3N4O5P6','Neha Gupta','neha@example.com','9001122334','','registered',3,'Online/Offline Classes','online-offline-classes',19,'Premium','music-production','Music Production','{"class_mode":"Online","level":"Intermediate","age":"24","service_type":"Music Production"}','','14999.00','14999.00','0.00','INR','pending','awaiting','razorpay',NULL,'order_seed_e1941e3cbd35b5',NULL,NULL,NULL,0,NULL,'2026-09-23 13:40:00','2026-09-23 13:40:00',NULL),
(20,'BDCM-3C0005',NULL,'CUST-K7L8M9N0','Kavita Desai','kavita@example.com','9337665544','','registered',3,'Online/Offline Classes','online-offline-classes',21,'Basic','instrument','Instrument','{"class_mode":"Offline","level":"Beginner","age":"19","service_type":"Instrument"}','','2999.00','2999.00','0.00','INR','processing','paid','razorpay','pay_seed_e2c55a2acb5b86','order_seed_ec23b8c4ff9543','dd2f6bed9dfcad47b6b5147c604e084dec95c7cab2d41c67bd439cef35111db1','upi',NULL,0,NULL,'2026-08-18 11:20:00','2026-09-12 10:40:00','2026-08-18 11:26:00'),
(21,'BDCM-3C0006',NULL,NULL,'Tanya Bhattacharya','tanya.b@example.com','9900112233','','guest',3,'Online/Offline Classes','online-offline-classes',23,'Premium','instrument','Instrument','{"class_mode":"Online","level":"Advanced","age":"28","service_type":"Instrument"}','','9999.00','9999.00','0.00','INR','delivered','paid','razorpay','pay_seed_da06b17aa07e14','order_seed_2d86ad1ff226f4','6ed48441812873c0ec06f6e25cb44172b7ab7bb0034aab214e8f64f59936c25b','card',NULL,0,NULL,'2026-07-02 14:45:00','2026-07-26 15:00:00','2026-07-02 14:51:00'),
(22,'BDCM-3C0007',NULL,'CUST-Y5Z6A7B8','Ravi Joshi','ravi@example.com','9009887766','','registered',3,'Online/Offline Classes','online-offline-classes',25,'Basic','video-editing','Video Editing','{"class_mode":"Offline","level":"Beginner","age":"21","service_type":"Video Editing"}','','3999.00','3999.00','0.00','INR','hold','awaiting','razorpay',NULL,'order_seed_724787d4f10949',NULL,NULL,NULL,0,NULL,'2026-08-30 09:15:00','2026-08-30 09:15:00',NULL),
(23,'BDCM-3C0008',NULL,'CUST-G3H4I5J6','Arjun Reddy','arjun@example.com','9228776655','','registered',3,'Online/Offline Classes','online-offline-classes',28,'Enterprise','video-editing','Video Editing','{"class_mode":"Online","level":"Intermediate","age":"35","service_type":"Video Editing"}','','29999.00','29999.00','0.00','INR','processing','paid','razorpay','pay_seed_21233208c3f6f8','order_seed_605477360c5b90','6e8687f1c81ac10033096079e2955e3e8b00a0c7f10b3ce831e6ca48b4061fa3','netbanking',NULL,0,NULL,'2026-09-11 12:30:00','2026-09-21 09:50:00','2026-09-11 12:36:00'),
(24,'BDCM-3C0009',NULL,'CUST-U1V2W3X4','Sonia Verma','sonia@example.com','9911223344','','registered',3,'Online/Offline Classes','online-offline-classes',14,'Standard','singing','Singing','{"class_mode":"Online","level":"Intermediate","age":"23","service_type":"Singing"}','','8999.00','8999.00','0.00','INR','processing','paid','razorpay','pay_seed_bf205f36d10178','order_seed_0d1cc667cc807d','ebc339cb5b1a9f5a43e33e8ac4fd8d7b1626c1fa720c32b82f3991b2c57eb260','upi',NULL,0,NULL,'2026-09-06 10:25:00','2026-09-19 11:10:00','2026-09-06 10:31:00'),
(25,'BDCM-3C0010',NULL,'CUST-E5F6G7H8','Priya Patel','priya@example.com','9988776655','','registered',3,'Online/Offline Classes','online-offline-classes',22,'Standard','instrument','Instrument','{"class_mode":"Offline","level":"Beginner","age":"17","service_type":"Instrument"}','','5999.00','5999.00','0.00','INR','cancelled','refunded','razorpay','pay_seed_080edc063acba2','order_seed_644e3665ea7c29','ad594266afdef3856c1100baeb1777abb9349f2a51b71b30a91a09c35bc86ad9','card',NULL,0,NULL,'2026-08-05 16:00:00','2026-08-08 10:20:00','2026-08-05 16:06:00'),
(26,'BDCM-3C0011',NULL,'CUST-Y5Z6A7B8','Ravi Joshi','ravi@example.com','9009887766','','registered',3,'Online/Offline Classes','online-offline-classes',15,'Premium','singing','Singing','{"class_mode":"Offline","level":"Advanced","age":"29","service_type":"Singing"}','','17999.00','17999.00','0.00','INR','processing','paid','razorpay','pay_seed_adfe7b864c86d1','order_seed_5faf2a31ebb6c8','5029a0f69cc760c0880c4cc9dfaf905d46d89e23aaf8e34368ac1a09c3680977','netbanking',NULL,0,NULL,'2026-09-19 10:30:00','2026-09-25 08:45:00','2026-09-19 10:36:00'),
(27,'BDCM-3C0012',NULL,'CUST-C9D0E1F2','Deepika Nair','deepika@example.com','9119887766','','registered',3,'Online/Offline Classes','online-offline-classes',24,'Enterprise','instrument','Instrument','{"class_mode":"Online","level":"Intermediate","age":"33","service_type":"Instrument"}','','19999.00','19999.00','0.00','INR','pending','awaiting','razorpay',NULL,'order_seed_52848f97c05dfb',NULL,NULL,NULL,0,NULL,'2026-09-27 15:05:00','2026-09-27 15:05:00',NULL),
(28,'BDCM-4D0001',NULL,'CUST-I9J0K1L2','Amit Kumar','amit@example.com','9112233445','','registered',4,'Digital Music Distribution','digital-distribution',5,'Release Plan','','Distribution','{"release_type":"Single","release_title":"Dil Ki Awaaz","artist_name":"Amit Kumar","genre":"Pop","language":"Hindi","release_date":"2026-09-15","isrc":"Yes","existing_isrc":"IN-R5S-23-00001","upc":"No","copyright_help":"Yes","youtube_link":"https://youtube.com/watch?v=example1","notes":"Debut single, ISRC already assigned. Please do not re-assign.","service_type":"Distribution"}','','199.00','199.00','0.00','INR','delivered','paid','razorpay','pay_seed_ba9d743c7da270','order_seed_37ff6018719333','4f4ddafec5a6b1fc06dc63c14c3404aaa657b167e9f22d0677fa3693bce705ef','card',NULL,0,NULL,'2026-07-28 14:30:00','2026-09-15 06:00:00','2026-07-28 14:36:00'),
(29,'BDCM-4D0002',NULL,'CUST-G3H4I5J6','Arjun Reddy','arjun@example.com','9228776655','','registered',4,'Digital Music Distribution','digital-distribution',6,'Artist Unlimited','','Distribution','{"release_type":"Album","release_title":"Echoes of Soul","artist_name":"Arjun Reddy","genre":"Rock","language":"English","release_date":"2026-10-01","isrc":"No","upc":"Yes","existing_upc":"123456789012","copyright_help":"No","notes":"Six-track album, all artwork attached. Unlimited plan so I can add an EP later this year.","service_type":"Distribution"}','','1199.00','1199.00','0.00','INR','processing','paid','razorpay','pay_seed_1a0ed2fe8bc130','order_seed_2ab49970e27576','195975ca18246dbc87612f83dd33217b832e2a211e71b1ab4b94a2997a2d8051','netbanking',NULL,0,NULL,'2026-09-10 09:00:00','2026-09-18 14:00:00','2026-09-10 09:06:00'),
(30,'BDCM-4D0003',NULL,'CUST-K7L8M9N0','Kavita Desai','kavita@example.com','9337665544','','registered',4,'Digital Music Distribution','digital-distribution',5,'Release Plan','','Distribution','{"release_type":"Single","release_title":"Monsoon Dreams","artist_name":"Kavita Desai","genre":"Folk","language":"Hindi","release_date":"2026-10-20","isrc":"No","upc":"No","copyright_help":"Yes","youtube_link":"https://youtube.com/watch?v=example2","notes":"Independent folk release, need an ISRC assigned and Content ID on.","service_type":"Distribution"}','','199.00','199.00','0.00','INR','pending','awaiting','razorpay',NULL,'order_seed_8602773c293d38',NULL,NULL,NULL,0,NULL,'2026-09-22 11:15:00','2026-09-22 11:15:00',NULL),
(31,'BDCM-4D0004',NULL,'CUST-A1B2C3D4','Rahul Sharma','rahul@example.com','9876543210','','registered',4,'Digital Music Distribution','digital-distribution',7,'PRO Label','','Distribution','{"release_type":"Album","release_title":"Sapno Ka Safar","artist_name":"Rahul Sharma","genre":"Bollywood","language":"Hindi","release_date":"2026-11-10","isrc":"No","upc":"No","copyright_help":"No","notes":"Full-length album with Dolby Atmos masters. Registering a label so my artists can release under it.","service_type":"Distribution"}','','7999.00','7999.00','0.00','INR','processing','paid','razorpay','pay_seed_b7e4fdb044ad1f','order_seed_dfd1721dec4693','0305bbeb4fe947aa3ed77ca44c5b32c830876acefbf9dc31fd917c63da600808','upi',NULL,0,NULL,'2026-09-12 16:40:00','2026-09-20 10:10:00','2026-09-12 16:46:00'),
(32,'BDCM-4D0005',NULL,'CUST-M3N4O5P6','Neha Gupta','neha@example.com','9001122334','','registered',4,'Digital Music Distribution','digital-distribution',8,'Limitless Label','','Distribution','{"release_type":"Album","release_title":"Beparwah","artist_name":"Neha Gupta","genre":"Hip Hop","language":"Hindi","release_date":"2026-12-01","isrc":"No","upc":"No","copyright_help":"Yes","youtube_link":"https://youtube.com/watch?v=example3","notes":"Second album. Need monthly royalty statements and support for two feature artists.","service_type":"Distribution"}','','14999.00','14999.00','0.00','INR','delivered','paid','razorpay','pay_seed_12abeeae122d6e','order_seed_f22e814cfc58a1','703540fa467daba03407b852d3e7460ddd4f956195e2c947f3e9947feeb6d54a','netbanking',NULL,0,NULL,'2026-08-20 10:50:00','2026-09-22 11:30:00','2026-08-20 10:56:00'),
(33,'BDCM-4D0006',NULL,NULL,'Harsh Vardhan','harsh.v@example.com','9700223344','','guest',4,'Digital Music Distribution','digital-distribution',5,'Release Plan','','Distribution','{"release_type":"Single","release_title":"Raat Ka Safar","artist_name":"Harsh Vardhan","genre":"Folk","language":"Hindi","release_date":"2026-10-28","isrc":"No","upc":"No","copyright_help":"No","notes":"First release, no codes yet. Split with a co-writer.","service_type":"Distribution"}','','199.00','199.00','0.00','INR','pending','awaiting','razorpay',NULL,'order_seed_694c3f716615e2',NULL,NULL,NULL,0,NULL,'2026-09-26 19:20:00','2026-09-26 19:20:00',NULL),
(34,'BDCM-4D0007',NULL,'CUST-E5F6G7H8','Priya Patel','priya@example.com','9988776655','','registered',4,'Digital Music Distribution','digital-distribution',6,'Artist Unlimited','','Distribution','{"release_type":"Album","release_title":"Bittersweet","artist_name":"Priya Patel","genre":"R&B","language":"English","release_date":"2026-10-05","isrc":"No","upc":"No","copyright_help":"No","notes":"Five-track album. Cancelling, going with a different distributor.","service_type":"Distribution"}','','1199.00','1199.00','0.00','INR','cancelled','refunded','razorpay','pay_seed_cfb3e857a5ee1e','order_seed_9c58e61f35fd8f','1d746c336c65a6de14b9136a8361f8079f481086fb47bd320c4117ae8ca9ae09','card',NULL,0,NULL,'2026-08-14 13:15:00','2026-08-16 09:40:00','2026-08-14 13:21:00'),
(35,'BDCM-5E0001',NULL,'CUST-M3N4O5P6','Neha Gupta','neha@example.com','9001122334','','registered',5,'Promotion Services','promotion',NULL,NULL,'',NULL,'{"content_type":"Instagram Reels","promotion_goal":"Audience Growth","campaign_type":"Social Media","target_platform":"Instagram, YouTube","release_title":"Beparwah","campaign_duration":"2 weeks","budget":"Rs.15,000","project_link":"https://youtube.com/watch?v=example3","notes":"Album launch campaign, two reels a week for a fortnight."}','','0.00','0.00','0.00','INR','pending','awaiting','manual',NULL,NULL,NULL,NULL,NULL,0,NULL,'2026-09-05 12:00:00','2026-09-05 12:00:00',NULL),
(36,'BDCM-5E0002',NULL,'CUST-I9J0K1L2','Amit Kumar','amit@example.com','9112233445','','registered',5,'Promotion Services','promotion',NULL,NULL,'',NULL,'{"content_type":"Music Video","promotion_goal":"More Views","campaign_type":"Music Video Promotion","target_platform":"YouTube","release_title":"Dil Ki Awaaz","campaign_duration":"4 weeks","budget":"Rs.45,000","project_link":"https://youtube.com/watch?v=example1","notes":"Want 1M views in the first month of the video."}','','0.00','0.00','0.00','INR','processing','awaiting','manual',NULL,NULL,NULL,NULL,NULL,0,NULL,'2026-08-08 09:30:00','2026-09-16 11:00:00',NULL),
(37,'BDCM-5E0003',NULL,NULL,'Anurag Biswas','anurag.b@example.com','9833445566','9833445566','guest',5,'Promotion Services','promotion',NULL,NULL,'',NULL,'{"content_type":"Brand Video","promotion_goal":"Brand Awareness","campaign_type":"Other","target_platform":"Instagram, LinkedIn","release_title":"Studio Launch Film","campaign_duration":"6 weeks","budget":"Rs.80,000","notes":"Launch film for a new studio, plus a six-week content run."}','','0.00','0.00','0.00','INR','pending','awaiting','manual',NULL,NULL,NULL,NULL,NULL,0,NULL,'2026-09-19 17:45:00','2026-09-19 17:45:00',NULL),
(38,'BDCM-5E0004',NULL,'CUST-K7L8M9N0','Kavita Desai','kavita@example.com','9337665544','','registered',5,'Promotion Services','promotion',NULL,NULL,'',NULL,'{"content_type":"Short Film","promotion_goal":"Release Promotion","campaign_type":"YouTube Ads","target_platform":"YouTube","release_title":"Bhakti Sagar","campaign_duration":"3 weeks","budget":"Rs.25,000","notes":"Devotional short film, festival season push."}','','0.00','0.00','0.00','INR','hold','awaiting','manual',NULL,NULL,NULL,NULL,NULL,0,NULL,'2026-07-30 10:20:00','2026-08-08 10:00:00',NULL),
(39,'BDCM-5E0005',NULL,'CUST-Q7R8S9T0','Vikram Singh','vikram@example.com','9871234567','','registered',5,'Promotion Services','promotion',NULL,NULL,'',NULL,'{"content_type":"Music Video","promotion_goal":"More Views","campaign_type":"Release Launch","target_platform":"YouTube, Instagram","release_title":"Sapno Ka Safar","campaign_duration":"4 weeks","budget":"Rs.60,000","project_link":"https://youtube.com/watch?v=example4","notes":"EP launch, four-week run, want daily reporting."}','','0.00','0.00','0.00','INR','delivered','awaiting','manual',NULL,NULL,NULL,NULL,NULL,0,NULL,'2026-06-15 08:40:00','2026-07-18 17:00:00',NULL),
(40,'BDCM-6F0001',NULL,'CUST-U1V2W3X4','Sonia Verma','sonia@example.com','9911223344','','registered',6,'IPRS Services','iprs',29,'Author / Composer','','Membership','{"applicant_type":"composer","song_released":"yes","song_title":"Raat Ki Rani","artist_name":"Sonia Verma","song_links":"https://open.spotify.com/track/example1","account_holder":"Sonia Verma","account_number":"123456789012","bank_name":"State Bank of India","ifsc":"SBIN0001234","message":"I compose and write the lyrics myself. Registering three original compositions.","service_type":"Membership"}','I compose and write the lyrics myself. Registering three original compositions.','2499.00','2499.00','0.00','INR','pending','awaiting','razorpay',NULL,'order_seed_fa9306ff94d91d',NULL,NULL,NULL,0,NULL,'2026-09-03 08:45:00','2026-09-03 08:45:00',NULL),
(41,'BDCM-6F0002',NULL,'CUST-A1B2C3D4','Rahul Sharma','rahul@example.com','9876543210','','registered',6,'IPRS Services','iprs',29,'Author / Composer','','Membership','{"applicant_type":"author-composer","song_released":"yes","song_title":"Sapno Ka Safar","artist_name":"Rahul Sharma","song_links":"https://youtube.com/watch?v=example2","account_holder":"Rahul Sharma","account_number":"987654321012","bank_name":"HDFC Bank","ifsc":"HDFC0001234","message":"Four original songs, words and music both mine. Registration number needed for the label.","service_type":"Membership"}','Four original songs, words and music both mine. Registration number needed for the label.','2499.00','2499.00','0.00','INR','processing','paid','razorpay','pay_seed_719f7208a5347e','order_seed_c14779eda9b978','884d48a7acd13c5b1f54bdd610c288df155d97c10d9f9d70c4ae542c1df1113d','upi',NULL,0,NULL,'2026-09-10 14:00:00','2026-09-17 12:20:00','2026-09-10 14:06:00'),
(42,'BDCM-6F0003',NULL,'CUST-K7L8M9N0','Kavita Desai','kavita@example.com','9337665544','','registered',6,'IPRS Services','iprs',30,'Publisher','','Membership','{"applicant_type":"publisher","song_released":"no","song_title":"Bhakti Sagar","artist_name":"Kavita Devi","account_holder":"Kavita Desai","account_number":"567890123456","bank_name":"ICICI Bank","ifsc":"ICIC0005678","message":"Publishing rights for a devotional catalogue of 22 works, none released yet.","service_type":"Membership"}','Publishing rights for a devotional catalogue of 22 works, none released yet.','4999.00','4999.00','0.00','INR','delivered','paid','razorpay','pay_seed_5828a09b4f030e','order_seed_36cfa9b53f0fd5','259efb9a22179de011ebe04cdc1611914060bc0f89c106c65992bad1b4cb5ed9','netbanking',NULL,0,NULL,'2026-07-08 11:30:00','2026-08-29 15:00:00','2026-07-08 11:36:00'),
(43,'BDCM-6F0004',NULL,NULL,'Faisal Khan','faisal.khan@example.com','9654332211','9654332211','guest',6,'IPRS Services','iprs',29,'Author / Composer','','Membership','{"applicant_type":"author","song_released":"no","song_title":"Dast-e-Saba","artist_name":"Faisal Khan","account_holder":"Faisal Khan","account_number":"445566778899","bank_name":"Axis Bank","ifsc":"UTIB0001234","message":"Five original ghazals, unreleased. Registering as author only.","service_type":"Membership"}','Five original ghazals, unreleased. Registering as author only.','2499.00','2499.00','0.00','INR','pending','awaiting','razorpay',NULL,'order_seed_64b9b96b904dad',NULL,NULL,NULL,0,NULL,'2026-09-27 10:05:00','2026-09-27 10:05:00',NULL),
(44,'BDCM-6F0005',NULL,'CUST-G3H4I5J6','Arjun Reddy','arjun@example.com','9228776655','','registered',6,'IPRS Services','iprs',30,'Publisher','','Membership','{"applicant_type":"publisher","song_released":"yes","song_title":"Echoes of Soul","artist_name":"Arjun Reddy","song_links":"https://open.spotify.com/album/example1","account_holder":"Arjun Reddy","account_number":"778899001122","bank_name":"Kotak Mahindra Bank","ifsc":"KKBK0000261","message":"Publishing for the album currently being distributed. Six tracks, all original.","service_type":"Membership"}','Publishing for the album currently being distributed. Six tracks, all original.','4999.00','4999.00','0.00','INR','hold','paid','razorpay','pay_seed_6b727e7c35d12d','order_seed_0bd687755c0dd4','034865606039404a78cb6fd08dbb66f6eacb5d1a6d9c1c9c84427205b4a4f3f2','card',NULL,0,NULL,'2026-09-14 16:20:00','2026-09-22 09:30:00','2026-09-14 16:26:00');

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
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booking_payments`
--

INSERT INTO `booking_payments` (`id`, `booking_id`, `provider`, `razorpay_order_id`, `razorpay_payment_id`, `razorpay_signature`, `amount`, `currency`, `status`, `method`, `failure_reason`, `created_at`, `updated_at`, `paid_at`) VALUES
(1,'BDCM-1A0001','razorpay','order_seed_a11675740fed03','pay_seed_a6bf1e032be1f1','05136c102576830ec3b56e7900a6dfaa1813176aa8ebde74dce09a9821004add','4999.00','INR','paid','card',NULL,'2026-06-12 10:20:00','2026-07-19 16:40:00','2026-06-12 10:26:00'),
(2,'BDCM-1A0002','razorpay','order_seed_f1ec36c0164940','pay_seed_b6ae1f06de8303','6d9386f94ae3eb1c05c689ced0523512d09e5f8ff6ef026636ad9aea5c8672c9','2499.00','INR','paid','upi',NULL,'2026-09-04 11:12:00','2026-09-18 10:05:00','2026-09-04 11:18:00'),
(3,'BDCM-1A0003','razorpay','order_seed_eee7a0e94ff5be',NULL,NULL,'999.00','INR','created',NULL,NULL,'2026-09-21 16:45:00','2026-09-21 16:45:00',NULL),
(4,'BDCM-1A0004','razorpay','order_seed_148fbbb6a39797','pay_seed_1d1d68f34779c7','a5a0a473c17d676dbf500fb1c3acbe103a0ced6d96cc2b73df9e46628441817f','499.00','INR','paid','card',NULL,'2026-08-02 09:30:00','2026-08-20 14:10:00','2026-08-02 09:36:00'),
(5,'BDCM-1A0005','razorpay','order_seed_601929767aff59','pay_seed_d058df23656735','1099a0ec9b260dbf5453ff66c12a855887fe6b2d5ac44039e0d2cdfcdfdb5ce9','4999.00','INR','refunded','upi',NULL,'2026-08-11 13:22:00','2026-08-14 11:45:00','2026-08-11 13:28:00'),
(6,'BDCM-1A0006','razorpay','order_seed_cea39160b0a0c0',NULL,NULL,'2499.00','INR','created',NULL,NULL,'2026-09-24 08:05:00','2026-09-24 08:05:00',NULL),
(7,'BDCM-1A0007','razorpay','order_seed_7474dab8a75af7',NULL,NULL,'499.00','INR','cancelled',NULL,'Payment window expired before it was completed.','2026-09-08 19:40:00','2026-09-08 19:40:00',NULL),
(8,'BDCM-2B0001','razorpay','order_seed_68afea4067b89f','pay_seed_804c553a2c754c','ed15713ad0da93149070cc3683c2973a02e75e674248269a7e2709b964c7b487','11500.00','INR','paid','card',NULL,'2026-07-06 10:15:00','2026-07-25 12:00:00','2026-07-06 10:21:00'),
(9,'BDCM-2B0002','razorpay','order_seed_bac5ad579df1d1','pay_seed_0b098b5d90ab7e','7f7c0e64d7ea44ecd4ad7fc23aea48f9e67a534baab0d620c844380e38701f7c','23500.00','INR','paid','upi',NULL,'2026-08-24 14:20:00','2026-09-22 17:30:00','2026-08-24 14:26:00'),
(10,'BDCM-2B0003','razorpay','order_seed_851309cadc1044','pay_seed_f82003aec7a77c','e245b5d3581743d4ed1b2f73c14e6531efbedb74b15f312bd81b6e465cc604d0','36500.00','INR','paid','card',NULL,'2026-09-02 11:05:00','2026-09-15 09:20:00','2026-09-02 11:11:00'),
(11,'BDCM-2B0004','razorpay','order_seed_8f06041ccb5dae','pay_seed_03279b3de4073b','a9955b327d3e2a0654fbb93f6bbce10ec4566a561f31262fe1dafaf2d64e3aba','75000.00','INR','paid','netbanking',NULL,'2026-06-18 09:50:00','2026-08-15 18:00:00','2026-06-18 09:56:00'),
(12,'BDCM-2B0005','razorpay','order_seed_99ffcbd38d81d1','pay_seed_2a35e44c3f34b5','043cdeb7e797100bc2820a024181f0b592b92b208a0b136779ce5b11ed5fe4e6','11500.00','INR','paid','card',NULL,'2026-09-09 15:35:00','2026-09-23 11:15:00','2026-09-09 15:41:00'),
(13,'BDCM-2B0006','razorpay','order_seed_43ef0d9f98274f',NULL,NULL,'36500.00','INR','failed',NULL,'Payment failed: card declined by issuing bank.','2026-09-17 12:20:00','2026-09-17 12:26:00',NULL),
(14,'BDCM-2B0007','razorpay','order_seed_6d9c39b7186da2','pay_seed_f876cf8404ccab','3c8b5c33c94cc36cffa75d04f5444434e829f091c5dfe2633b4841ac24e201d7','11500.00','INR','paid','card',NULL,'2026-08-30 17:00:00','2026-09-07 10:00:00','2026-08-30 17:06:00'),
(15,'BDCM-2B0008','razorpay','order_seed_3233ca90101d5a',NULL,NULL,'23500.00','INR','created',NULL,NULL,'2026-09-25 10:10:00','2026-09-25 10:10:00',NULL),
(16,'BDCM-3C0001','razorpay','order_seed_6bf3f726477127','pay_seed_1b2f1227b4e126','5479d8c63b7b659073d6748da2a2c10f43540e4c3f28d68a90f66621bcaea6f7','2999.00','INR','paid','upi',NULL,'2026-06-22 09:00:00','2026-07-23 12:00:00','2026-06-22 09:06:00'),
(17,'BDCM-3C0002','razorpay','order_seed_aa8c5d9079998e','pay_seed_edfcbf3425edc7','7b699a475b2d1dca17459aa52b0d87ec7c0fb9c7ee0ab4183fd32bf741387084','32999.00','INR','paid','netbanking',NULL,'2026-09-01 18:30:00','2026-09-20 16:00:00','2026-09-01 18:36:00'),
(18,'BDCM-3C0003','razorpay','order_seed_e7aaea14cc8371','pay_seed_187a9914003f47','5cb2952d79bd7b7165e4adaa14605c085ef942fa10dde956b3611e97a02f3b1c','3999.00','INR','paid','card',NULL,'2026-07-14 16:05:00','2026-08-14 17:30:00','2026-07-14 16:11:00'),
(19,'BDCM-3C0004','razorpay','order_seed_e1941e3cbd35b5',NULL,NULL,'14999.00','INR','created',NULL,NULL,'2026-09-23 13:40:00','2026-09-23 13:40:00',NULL),
(20,'BDCM-3C0005','razorpay','order_seed_ec23b8c4ff9543','pay_seed_e2c55a2acb5b86','dd2f6bed9dfcad47b6b5147c604e084dec95c7cab2d41c67bd439cef35111db1','2999.00','INR','paid','upi',NULL,'2026-08-18 11:20:00','2026-09-12 10:40:00','2026-08-18 11:26:00'),
(21,'BDCM-3C0006','razorpay','order_seed_2d86ad1ff226f4','pay_seed_da06b17aa07e14','6ed48441812873c0ec06f6e25cb44172b7ab7bb0034aab214e8f64f59936c25b','9999.00','INR','paid','card',NULL,'2026-07-02 14:45:00','2026-07-26 15:00:00','2026-07-02 14:51:00'),
(22,'BDCM-3C0007','razorpay','order_seed_724787d4f10949',NULL,NULL,'3999.00','INR','created',NULL,NULL,'2026-08-30 09:15:00','2026-08-30 09:15:00',NULL),
(23,'BDCM-3C0008','razorpay','order_seed_605477360c5b90','pay_seed_21233208c3f6f8','6e8687f1c81ac10033096079e2955e3e8b00a0c7f10b3ce831e6ca48b4061fa3','29999.00','INR','paid','netbanking',NULL,'2026-09-11 12:30:00','2026-09-21 09:50:00','2026-09-11 12:36:00'),
(24,'BDCM-3C0009','razorpay','order_seed_0d1cc667cc807d','pay_seed_bf205f36d10178','ebc339cb5b1a9f5a43e33e8ac4fd8d7b1626c1fa720c32b82f3991b2c57eb260','8999.00','INR','paid','upi',NULL,'2026-09-06 10:25:00','2026-09-19 11:10:00','2026-09-06 10:31:00'),
(25,'BDCM-3C0010','razorpay','order_seed_644e3665ea7c29','pay_seed_080edc063acba2','ad594266afdef3856c1100baeb1777abb9349f2a51b71b30a91a09c35bc86ad9','5999.00','INR','refunded','card',NULL,'2026-08-05 16:00:00','2026-08-08 10:20:00','2026-08-05 16:06:00'),
(26,'BDCM-3C0011','razorpay','order_seed_5faf2a31ebb6c8','pay_seed_adfe7b864c86d1','5029a0f69cc760c0880c4cc9dfaf905d46d89e23aaf8e34368ac1a09c3680977','17999.00','INR','paid','netbanking',NULL,'2026-09-19 10:30:00','2026-09-25 08:45:00','2026-09-19 10:36:00'),
(27,'BDCM-3C0012','razorpay','order_seed_52848f97c05dfb',NULL,NULL,'19999.00','INR','created',NULL,NULL,'2026-09-27 15:05:00','2026-09-27 15:05:00',NULL),
(28,'BDCM-4D0001','razorpay','order_seed_37ff6018719333','pay_seed_ba9d743c7da270','4f4ddafec5a6b1fc06dc63c14c3404aaa657b167e9f22d0677fa3693bce705ef','199.00','INR','paid','card',NULL,'2026-07-28 14:30:00','2026-09-15 06:00:00','2026-07-28 14:36:00'),
(29,'BDCM-4D0002','razorpay','order_seed_2ab49970e27576','pay_seed_1a0ed2fe8bc130','195975ca18246dbc87612f83dd33217b832e2a211e71b1ab4b94a2997a2d8051','1199.00','INR','paid','netbanking',NULL,'2026-09-10 09:00:00','2026-09-18 14:00:00','2026-09-10 09:06:00'),
(30,'BDCM-4D0003','razorpay','order_seed_8602773c293d38',NULL,NULL,'199.00','INR','created',NULL,NULL,'2026-09-22 11:15:00','2026-09-22 11:15:00',NULL),
(31,'BDCM-4D0004','razorpay','order_seed_dfd1721dec4693','pay_seed_b7e4fdb044ad1f','0305bbeb4fe947aa3ed77ca44c5b32c830876acefbf9dc31fd917c63da600808','7999.00','INR','paid','upi',NULL,'2026-09-12 16:40:00','2026-09-20 10:10:00','2026-09-12 16:46:00'),
(32,'BDCM-4D0005','razorpay','order_seed_f22e814cfc58a1','pay_seed_12abeeae122d6e','703540fa467daba03407b852d3e7460ddd4f956195e2c947f3e9947feeb6d54a','14999.00','INR','paid','netbanking',NULL,'2026-08-20 10:50:00','2026-09-22 11:30:00','2026-08-20 10:56:00'),
(33,'BDCM-4D0006','razorpay','order_seed_694c3f716615e2',NULL,NULL,'199.00','INR','created',NULL,NULL,'2026-09-26 19:20:00','2026-09-26 19:20:00',NULL),
(34,'BDCM-4D0007','razorpay','order_seed_9c58e61f35fd8f','pay_seed_cfb3e857a5ee1e','1d746c336c65a6de14b9136a8361f8079f481086fb47bd320c4117ae8ca9ae09','1199.00','INR','refunded','card',NULL,'2026-08-14 13:15:00','2026-08-16 09:40:00','2026-08-14 13:21:00'),
(35,'BDCM-5E0001','manual',NULL,NULL,NULL,'0.00','INR','created',NULL,NULL,'2026-09-05 12:00:00','2026-09-05 12:00:00',NULL),
(36,'BDCM-5E0002','manual',NULL,NULL,NULL,'0.00','INR','created',NULL,NULL,'2026-08-08 09:30:00','2026-09-16 11:00:00',NULL),
(37,'BDCM-5E0003','manual',NULL,NULL,NULL,'0.00','INR','created',NULL,NULL,'2026-09-19 17:45:00','2026-09-19 17:45:00',NULL),
(38,'BDCM-5E0004','manual',NULL,NULL,NULL,'0.00','INR','created',NULL,NULL,'2026-07-30 10:20:00','2026-08-08 10:00:00',NULL),
(39,'BDCM-5E0005','manual',NULL,NULL,NULL,'0.00','INR','created',NULL,NULL,'2026-06-15 08:40:00','2026-07-18 17:00:00',NULL),
(40,'BDCM-6F0001','razorpay','order_seed_fa9306ff94d91d',NULL,NULL,'2499.00','INR','created',NULL,NULL,'2026-09-03 08:45:00','2026-09-03 08:45:00',NULL),
(41,'BDCM-6F0002','razorpay','order_seed_c14779eda9b978','pay_seed_719f7208a5347e','884d48a7acd13c5b1f54bdd610c288df155d97c10d9f9d70c4ae542c1df1113d','2499.00','INR','paid','upi',NULL,'2026-09-10 14:00:00','2026-09-17 12:20:00','2026-09-10 14:06:00'),
(42,'BDCM-6F0003','razorpay','order_seed_36cfa9b53f0fd5','pay_seed_5828a09b4f030e','259efb9a22179de011ebe04cdc1611914060bc0f89c106c65992bad1b4cb5ed9','4999.00','INR','paid','netbanking',NULL,'2026-07-08 11:30:00','2026-08-29 15:00:00','2026-07-08 11:36:00'),
(43,'BDCM-6F0004','razorpay','order_seed_64b9b96b904dad',NULL,NULL,'2499.00','INR','created',NULL,NULL,'2026-09-27 10:05:00','2026-09-27 10:05:00',NULL),
(44,'BDCM-6F0005','razorpay','order_seed_0bd687755c0dd4','pay_seed_6b727e7c35d12d','034865606039404a78cb6fd08dbb66f6eacb5d1a6d9c1c9c84427205b4a4f3f2','4999.00','INR','paid','card',NULL,'2026-09-14 16:20:00','2026-09-22 09:30:00','2026-09-14 16:26:00');

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
  `type` enum('single','album') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'single',
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `releases`
--

INSERT INTO `releases` (`id`, `customer_id`, `booking_id`, `title`, `type`, `artwork_path`, `isrc`, `upc`, `go_live_date`, `status`, `lyrics`, `dolby`, `apple_itunes`, `created_at`, `updated_at`) VALUES
(1,'CUST-I9J0K1L2','BDCM-4D0001','Dil Ki Awaaz','single','data/uploads/booking/BDCM-4D0001/4D0001_dil_ki_awaaz_cover.jpg','IN-R5S-23-00001',NULL,'2026-09-15','live','Pehli raat ka chand, teri yaad
Dil ki aawaaz, door se aayi
',0,1,'2026-07-28 14:30:00','2026-09-15 06:00:00'),
(2,'CUST-G3H4I5J6','BDCM-4D0002','Echoes of Soul','album','data/uploads/booking/BDCM-4D0002/4D0002_echoes_album_cover.png',NULL,'123456789012','2026-10-01','approved',NULL,0,0,'2026-09-10 09:00:00','2026-09-18 14:00:00'),
(3,'CUST-K7L8M9N0','BDCM-4D0003','Monsoon Dreams','single','data/uploads/booking/BDCM-4D0003/4D0003_monsoon_dreams_cover.jpg',NULL,NULL,'2026-10-20','verification','Baarish ka saman, mitti ki cheekh
Bheegi mitti, geeli raat
',0,0,'2026-09-22 11:15:00','2026-09-25 10:30:00');

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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `release_artists`
--

INSERT INTO `release_artists` (`id`, `release_id`, `role`, `name`, `created_at`) VALUES
(1,1,'primary','Amit Kumar','2026-09-27 12:00:00'),
(2,1,'composer','Rohit Sharma','2026-09-27 12:00:00'),
(3,1,'lyricist','Neha Gupta','2026-09-27 12:00:00'),
(4,2,'primary','Arjun Reddy','2026-09-27 12:00:00'),
(5,2,'producer','Rohit Tiwari','2026-09-27 12:00:00'),
(6,2,'featured','Priya Mehta','2026-09-27 12:00:00'),
(7,3,'primary','Kavita Desai','2026-09-27 12:00:00'),
(8,3,'composer','Jatin Shrivastav','2026-09-27 12:00:00'),
(13,2,'composer','Nikhil Warrier','2026-09-27 12:00:00');

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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `release_history`
--

INSERT INTO `release_history` (`id`, `release_id`, `action`, `message`, `reviewer_note`, `created_at`) VALUES
(1,1,'Initial Submission','Release created by customer.',NULL,'2026-07-28 14:30:00'),
(2,1,'Submitted for Review','Release submitted for admin review.',NULL,'2026-07-28 15:05:00'),
(3,1,'verification','Status updated to verification by the BDC Music team.','Metadata and artwork pulled in, checking the ISRC holder name.','2026-07-29 10:00:00'),
(4,1,'approved','Status updated to approved by the BDC Music team.','All metadata and artwork verified. Approved for distribution.','2026-07-30 14:00:00'),
(5,1,'live','Status updated to live by the BDC Music team.','Live on Spotify, Apple Music, JioSaavn, Amazon Music, YouTube Music and Gaana.','2026-09-15 06:00:00'),
(6,2,'Initial Submission','Release created by customer.',NULL,'2026-09-10 09:00:00'),
(7,2,'Submitted for Review','Release submitted for admin review.',NULL,'2026-09-10 09:40:00'),
(8,2,'verification','Status updated to verification by the BDC Music team.','Reviewing audio quality and metadata for all 6 tracks.','2026-09-11 10:00:00'),
(9,2,'approved','Status updated to approved by the BDC Music team.','Album approved. Audio mastering quality is excellent.','2026-09-18 14:00:00'),
(10,3,'Initial Submission','Release created by customer.',NULL,'2026-09-22 11:15:00'),
(11,3,'Submitted for Review','Release submitted for admin review.',NULL,'2026-09-22 11:50:00'),
(12,3,'verification','Status updated to verification by the BDC Music team.','ISRC code requested. Awaiting assignment.','2026-09-25 10:30:00');

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
(1,1,'Spotify','https://open.spotify.com/track/dil-ki-awaaz',1,1,'2026-09-27 12:00:00','2026-09-27 12:00:00'),
(2,1,'Apple Music','https://music.apple.com/in/artist/rahul-sharma',1,2,'2026-09-27 12:00:00','2026-09-27 12:00:00'),
(3,1,'YouTube Music','https://music.youtube.com/watch?v=dil-ki-awaaz',1,3,'2026-09-27 12:00:00','2026-09-27 12:00:00'),
(4,1,'Amazon Music','https://music.amazon.com/albums/dil-ki-awaaz',1,4,'2026-09-27 12:00:00','2026-09-27 12:00:00'),
(5,1,'JioSaavn','https://www.jiosaavn.com/song/dil-ki-awaaz',1,5,'2026-09-27 12:00:00','2026-09-27 12:00:00'),
(6,1,'Gaana','https://gaana.com/song/dil-ki-awaaz',1,6,'2026-09-27 12:00:00','2026-09-27 12:00:00');

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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `release_tracks`
--

INSERT INTO `release_tracks` (`id`, `release_id`, `track_no`, `title`, `isrc`, `duration`, `audio_file`, `created_at`, `updated_at`) VALUES
(1,1,1,'Dil Ki Awaaz','IN-R5S-23-00001','3:58','data/uploads/booking/BDCM-4D0001/4D0001_dil_ki_awaaz_master.wav','2026-09-27 12:00:00','2026-09-27 12:00:00'),
(2,2,1,'Echoes of Soul','IN-R5S-23-10001','4:12',NULL,'2026-09-27 12:00:00','2026-09-27 12:00:00'),
(3,2,2,'Midnight Reverb','IN-R5S-23-10002','3:48',NULL,'2026-09-27 12:00:00','2026-09-27 12:00:00'),
(4,2,3,'Paper Lanterns','IN-R5S-23-10003','4:35',NULL,'2026-09-27 12:00:00','2026-09-27 12:00:00'),
(5,2,4,'Static Hearts','IN-R5S-23-10004','3:27',NULL,'2026-09-27 12:00:00','2026-09-27 12:00:00'),
(6,2,5,'Long Way Home','IN-R5S-23-10005','5:04',NULL,'2026-09-27 12:00:00','2026-09-27 12:00:00'),
(7,2,6,'Echoes of Soul (Reprise)','IN-R5S-23-10006','4:12',NULL,'2026-09-27 12:00:00','2026-09-27 12:00:00'),
(8,3,1,'Monsoon Dreams',NULL,'4:21','data/uploads/booking/BDCM-4D0003/4D0003_monsoon_dreams_master.wav','2026-09-27 12:00:00','2026-09-27 12:00:00');

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
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (1,2,'audio-bundles','Audio','Basic',11500.00,NULL,'Studio access and raw tracking',NULL,'["Studio access", "Recording engineer", "Basic microphone setup", "RAW audio files"]',1,1,0,1,1,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (2,2,'audio-bundles','Audio','Standard',23500.00,NULL,'Professional microphone and editing',NULL,'["Professional microphone", "Multiple recording takes", "Studio access included", "Basic audio editing"]',0,1,0,1,2,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (3,2,'audio-bundles','Audio','Premium',36500.00,NULL,'Premium setup with vocal comping',NULL,'["Premium studio setup", "Professional equipment", "Vocal comping", "Basic editing included"]',0,1,0,1,3,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (4,2,'audio-bundles','Audio','Enterprise',75000.00,NULL,'Complete studio booking with dedicated engineer',NULL,'["Complete studio booking", "Dedicated sound engineer", "Priority support", "Custom production workflow"]',0,1,0,1,4,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (5,4,'','Distribution','Release Plan',199.00,NULL,'Single song distribution',NULL,'["Single song distribution", "Global music platforms", "Quarterly royalty payments", "90% streaming revenue share"]',1,1,0,1,1,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (6,4,'','Distribution','Artist Unlimited',1199.00,NULL,'Unlimited releases for one artist',NULL,'["Unlimited song releases", "One artists", "YouTube Content ID", "80% streaming revenue share"]',0,1,0,1,2,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (7,4,'','Distribution','PRO Label',7999.00,NULL,'Label registration with unlimited artists',NULL,'["Label registration", "Unlimited releases", "Unlimited artists", "90% streaming revenue share"]',0,1,0,1,3,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (8,4,'','Distribution','Limitless Label',14999.00,NULL,'Label support with monthly royalties',NULL,'["Unlimited song release", "Monthly royalty payment", "Label support", "80% revenue share"]',0,1,0,1,4,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (9,1,'','Membership','Basic',499.00,NULL,'Artist profile with up to 3 service listings','New artists, singers, producers, lyricists, musicians, DJs, editors and freelancers.','["Professional Artist Profile", "Portfolio Upload", "Up to 3 Service Listings", "Client Contact Form", "Apply for Projects", "Community Access", "Email Support", "Official BDC Artist ID"]',1,1,0,1,1,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (10,1,'','Membership','Standard',999.00,NULL,'Verified profile with priority project access','Freelance artists, bands and growing creative professionals.','["Everything in Basic", "Verified Artist Profile", "Up to 10 Service Listings", "Featured Search Listing", "Priority Project Access", "Social Media Promotion", "WhatsApp Support", "Artist Certificate"]',0,1,0,1,2,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (11,1,'','Membership','Premium',2499.00,NULL,'Featured artist with a dedicated artist manager','Professional artists, influencers, bands and music businesses.','["Everything in Standard", "Homepage Featured Artist", "Premium Verification Badge", "Unlimited Service Listings", "Priority Client Leads", "Dedicated Artist Manager", "Monthly Promotion", "Premium Support"]',0,1,0,1,3,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (12,1,'','Membership','Verified Pro',4999.00,NULL,'Multi-artist and company management','Music labels, agencies, production houses and established creative businesses.','["Multi-Artist Management", "Company Profile", "Unlimited Team Members", "Unlimited Service Listings", "Dedicated Account Manager", "Marketing Campaigns", "Recruitment Support", "Corporate Partnerships"]',0,1,0,1,4,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (13,3,'singing','Singing','Basic',2999.00,NULL,'1 month, 8 classes',NULL,'["Duration: 1 Month", "Classes: 8", "Mode: Online / Offline", "Vocal warm-up", "Breathing techniques", "Basic voice training", "Alankars", "Pitch and rhythm", "Beginner singing exercises", "Practice material", "Certificate", "WhatsApp support"]',1,1,0,1,1,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (14,3,'singing','Singing','Standard',8999.00,NULL,'3 months, 24 classes',NULL,'["Duration: 3 Months", "Classes: 24", "Voice training", "Bollywood singing", "Classical basics", "Song practice", "Performance techniques", "Priority WhatsApp support"]',0,1,0,1,2,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (15,3,'singing','Singing','Premium',17999.00,NULL,'6 months, 48 classes with studio recording',NULL,'["Duration: 6 Months", "Classes: 48", "Professional vocal training", "Advanced techniques", "Semi classical", "Stage performance", "Studio recording", "Artist grooming", "Personalized practice", "Premium support"]',0,1,0,1,3,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (16,3,'singing','Singing','Enterprise',32999.00,NULL,'12 months, 96+ classes',NULL,'["Duration: 12 Months", "96+ classes", "Complete professional training", "Recording sessions", "Live performance", "Artist grooming", "Portfolio building", "Career guidance", "Dedicated mentor"]',0,1,0,1,4,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (17,3,'music-production','Music Production','Basic',3999.00,NULL,'DAW introduction and beat making',NULL,'["DAW introduction", "Beat making", "MIDI basics", "Mixing", "Practice projects"]',1,1,0,1,5,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (18,3,'music-production','Music Production','Standard',7999.00,NULL,'Advanced beat making and mastering basics',NULL,'["Advanced beat making", "Melody", "Chords", "Drum programming", "Recording", "Mastering basics"]',0,1,0,1,6,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (19,3,'music-production','Music Production','Premium',14999.00,NULL,'Advanced mixing, mastering and arrangement',NULL,'["Advanced mixing", "Mastering", "Sound design", "Vocal processing", "Music arrangement", "Portfolio projects"]',0,1,0,1,7,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (20,3,'music-production','Music Production','Enterprise',29999.00,NULL,'Industry production including film and OTT',NULL,'["Industry-level production", "Film music", "OTT music", "Dolby Atmos basics", "Music release strategy", "Client projects", "Career guidance", "Dedicated mentor"]',0,1,0,1,8,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (21,3,'instrument','Instrument','Basic',2999.00,NULL,'Posture, scales and beginner songs',NULL,'["Posture and hand positioning", "Scale practice", "Rhythm basics", "Beginner songs", "Practice routine"]',1,1,0,1,9,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (22,3,'instrument','Instrument','Standard',5999.00,NULL,'Chords, notation and song practice',NULL,'["Chords and progressions", "Finger exercises", "Notation basics", "Song practice", "Performance confidence"]',0,1,0,1,10,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (23,3,'instrument','Instrument','Premium',9999.00,NULL,'Advanced technique and improvisation',NULL,'["Advanced techniques", "Improvisation", "Genre-based practice", "Recording readiness", "Personalized feedback"]',0,1,0,1,11,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (24,3,'instrument','Instrument','Enterprise',19999.00,NULL,'Professional repertoire and stage performance',NULL,'["Professional repertoire", "Stage performance", "Studio preparation", "Portfolio building", "Dedicated mentor"]',0,1,0,1,12,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (25,3,'video-editing','Video Editing','Basic',3999.00,NULL,'Timeline editing and export settings',NULL,'["Software introduction", "Timeline editing", "Cuts and transitions", "Audio sync", "Export settings"]',1,1,0,1,13,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (26,3,'video-editing','Video Editing','Standard',7999.00,NULL,'Story flow, colour correction and reels',NULL,'["Story flow", "Color correction", "Text and titles", "Reels and shorts editing", "Project workflow"]',0,1,0,1,14,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (27,3,'video-editing','Video Editing','Premium',14999.00,NULL,'Colour grading, motion graphics and sound design',NULL,'["Advanced color grading", "Motion graphics basics", "Music video editing", "Sound design", "Portfolio projects"]',0,1,0,1,15,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (28,3,'video-editing','Video Editing','Enterprise',29999.00,NULL,'Commercial and multi-camera editing',NULL,'["Commercial editing workflow", "Multi-camera editing", "Brand video packaging", "Client projects", "Career guidance"]',0,1,0,1,16,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (29,6,'','Membership','Author / Composer',2499.00,NULL,'Register original works as an author or composer',NULL,'["Registration of original compositions", "Author and composer royalty split", "Copyright registration support"]',1,1,0,1,1,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (30,6,'','Membership','Publisher',4999.00,NULL,'Register a catalogue or publish on behalf of others',NULL,'["Publisher registration", "Catalogue-level rights management", "Publishing administration support"]',0,1,0,1,2,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (91,2,'recording','Recording','Basic',1000.00,'/hr','Hourly booth time with an engineer','Single songs, demos and quick tracking','["1 hour in the booth", "Recording engineer", "Raw WAV files", "Basic cleanup"]',0,1,0,0,5,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (92,2,'recording','Recording','Standard',2500.00,'/session','Half-day tracking session','EPs and singles with multiple takes','["Up to 4 hours", "Recording engineer", "Multiple takes", "Basic editing"]',0,1,0,0,6,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (93,2,'recording','Recording','Premium',5000.00,'/session','Full-day session with overdubs','Full tracks with layered vocals and instruments','["Full day session", "Vocal comping", "Edited stems", "Rush turnaround"]',0,1,0,0,7,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (94,2,'recording','Recording','Enterprise',0.00,'Custom Quote','Multi-day or album-scale tracking','Albums, labels and long-form studio residencies','["Custom schedule", "Dedicated engineer", "Album-scale tracking", "Quote on request"]',0,1,1,0,8,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (95,2,'music-production','Music Production','Basic',5000.00,NULL,'Beat, arrangement and rough mix','First releases and bedroom producers','["Beat making", "Arrangement", "Rough mix", "1 revision"]',0,1,0,0,9,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (96,2,'music-production','Music Production','Standard',10000.00,NULL,'Full production with vocals','Singles ready for release','["Full arrangement", "Vocal production", "Mix-ready stems", "2 revisions"]',0,1,0,0,10,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (97,2,'music-production','Music Production','Premium',15000.00,NULL,'Premium production with sound design','Commercial singles and brand tracks','["Custom sound design", "Vocal editing", "Detailed mix prep", "3 revisions"]',0,1,0,0,11,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (98,2,'music-production','Music Production','Enterprise',40000.00,NULL,'Complete production with a dedicated producer','Labels, albums and campaign releases','["Dedicated producer", "Unlimited revisions", "Stem delivery", "Priority schedule"]',0,1,0,0,12,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (99,2,'mixing','Mixing','Basic',3000.00,NULL,'Straightforward stereo mix','Demos and single-track projects','["Stereo mix", "1 revision", "WAV and MP3 delivery"]',0,1,0,0,13,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (100,2,'mixing','Mixing','Standard',6000.00,NULL,'Stereo mix with an instrumental version','Releases that need clean and vocal versions','["Stereo mix", "Instrumental version", "3 revisions", "Radio-ready balance"]',0,1,0,0,14,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (101,2,'mixing','Mixing','Premium',9000.00,NULL,'Detailed mix with stem mastering prep','Competitive releases and sync submissions','["Detailed mix", "Stem mastering prep", "5 revisions", "Reference matching"]',0,1,0,0,15,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (102,2,'mixing','Mixing','Enterprise',20000.00,NULL,'Dedicated mix engineer','Albums and long-form catalogues','["Dedicated mix engineer", "Unlimited revisions", "Atmos-ready stems", "Priority delivery"]',0,1,0,0,16,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (103,2,'mastering','Mastering','Basic',2500.00,NULL,'Streaming-ready master','One finished track','["Streaming master", "1 revision", "WAV and MP3 delivery"]',0,1,0,0,17,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (104,2,'mastering','Mastering','Standard',5000.00,NULL,'Masters for up to four tracks','EPs and singles with versions','["Master for up to 4 tracks", "Loudness compliance", "2 revisions"]',0,1,0,0,18,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (105,2,'mastering','Mastering','Premium',7500.00,NULL,'Masters for up to ten tracks','Albums and full projects','["Master for up to 10 tracks", "Analog-style chain", "3 revisions"]',0,1,0,0,19,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (106,2,'mastering','Mastering','Enterprise',15000.00,NULL,'Album mastering with a dedicated engineer','Label releases and catalogue remasters','["Album mastering", "Dedicated mastering engineer", "Unlimited revisions", "DDP delivery"]',0,1,0,0,20,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (107,2,'video-production','Video Production','Basic',10000.00,NULL,'Single-camera shoot with full HD delivery','Simple promo and performance videos','["1 Professional Camera", "Full HD Recording", "1 Free Revision", "Delivery in 5-7 Working Days"]',0,1,0,0,21,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (108,2,'video-production','Video Production','Standard',25000.00,NULL,'Two-camera shoot with 4K delivery','Brand films and studio sessions','["1-2 Professional Cameras", "Full HD / 4K Recording", "2 Free Revisions", "Delivery in 3-5 Working Days"]',0,1,0,0,22,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (109,2,'video-production','Video Production','Premium',50000.00,NULL,'Multi-camera production with priority delivery','Campaigns and premium releases','["Multi-camera Setup", "4K Ultra HD Delivery", "Unlimited Minor Revisions", "Priority Delivery"]',0,1,0,0,23,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (110,2,'video-production','Video Production','Enterprise',100000.00,NULL,'Commercial production workflow','Commercial films and long-form productions','["Custom Camera Setup", "Dedicated Project Manager", "Priority Timeline", "Commercial Production Workflow"]',0,1,0,0,24,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (111,2,'video-editing','Video Editing','Basic',3000.00,NULL,'Timeline edit and export settings','Short clips and rough cuts','["Timeline edit", "Cuts and transitions", "1 revision", "HD export"]',0,1,0,0,25,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (112,2,'video-editing','Video Editing','Standard',8000.00,NULL,'Edited storyline with colour correction','Social films and music content','["Edited storyline", "Colour correction", "2 revisions", "HD / 4K export"]',0,1,0,0,26,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (113,2,'video-editing','Video Editing','Premium',15000.00,NULL,'Advanced grade with sound design','Polished releases and client work','["Advanced grade", "Sound design", "3 revisions", "4K delivery"]',0,1,0,0,27,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (114,2,'video-editing','Video Editing','Enterprise',30000.00,NULL,'Dedicated editor with multi-format delivery','Agencies and high-volume channels','["Dedicated editor", "Unlimited revisions", "Multi-format delivery", "Priority turnaround"]',0,1,0,0,28,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (115,2,'music-video','Music Video','Basic',20000.00,NULL,'Concept board and single location','Independent single releases','["Concept board", "Single location", "1 revision", "HD delivery"]',0,1,0,0,29,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (116,2,'music-video','Music Video','Standard',50000.00,NULL,'Storyboarded shoot across one or two locations','Growing artists with a release plan','["Storyboard", "1-2 locations", "2 revisions", "4K delivery"]',0,1,0,0,30,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (117,2,'music-video','Music Video','Premium',100000.00,NULL,'Full creative direction with VFX passes','Label singles and premium campaigns','["Full creative direction", "Multi-location", "VFX passes", "4K delivery"]',0,1,0,0,31,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (118,2,'music-video','Music Video','Enterprise',250000.00,NULL,'Crew, cast and studio build','Flagship videos and commercial releases','["Crew and cast", "Studio build", "Unlimited minor revisions", "Cinema-grade delivery"]',0,1,0,0,32,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (119,2,'social-media','Social Media Videos','Basic',1500.00,NULL,'One captioned vertical video','Testing short-form content','["1 vertical video", "Captioned", "1 revision"]',0,1,0,0,33,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (120,2,'social-media','Social Media Videos','Standard',3500.00,NULL,'Three vertical videos with hooks','Consistent weekly posting','["3 vertical videos", "Captions and hooks", "2 revisions"]',0,1,0,0,34,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (121,2,'social-media','Social Media Videos','Premium',7500.00,NULL,'Eight platform-specific videos','Active campaigns and launches','["8 videos", "Platform-specific edits", "3 revisions"]',0,1,0,0,35,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (122,2,'social-media','Social Media Videos','Enterprise',15000.00,NULL,'Monthly content pack with a dedicated editor','Brands and labels running always-on content','["Monthly content pack", "Dedicated editor", "Unlimited minor revisions"]',0,1,0,0,36,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (123,2,'motion-graphics','Motion Graphics','Basic',5000.00,NULL,'Logo animation and lower thirds','Channels that need consistent branding','["Logo animation", "Lower thirds", "1 revision"]',0,1,0,0,37,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (124,2,'motion-graphics','Motion Graphics','Standard',12000.00,NULL,'Animated explainer with custom icons','Product and service explainers','["Animated explainer", "Custom icons", "2 revisions"]',0,1,0,0,38,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (125,2,'motion-graphics','Motion Graphics','Premium',25000.00,NULL,'Full motion package with character animation','Campaigns and series content','["Full motion package", "Character animation", "3 revisions"]',0,1,0,0,39,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (126,2,'motion-graphics','Motion Graphics','Enterprise',50000.00,NULL,'Brand motion system','Brands building an animated identity','["Brand motion system", "Dedicated animator", "Unlimited revisions"]',0,1,0,0,40,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (127,2,'youtube-services','YouTube Services','Basic',3000.00,NULL,'One edited video with a thumbnail','New channels finding their format','["1 video edit", "Thumbnail", "1 revision"]',0,1,0,0,41,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (128,2,'youtube-services','YouTube Services','Standard',8000.00,NULL,'Four edits with custom thumbnails','Channels publishing weekly','["4 video edits", "Custom thumbnails", "2 revisions"]',0,1,0,0,42,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (129,2,'youtube-services','YouTube Services','Premium',15000.00,NULL,'Eight edits plus a channel trailer','Channels scaling their output','["8 video edits", "Channel trailer", "3 revisions"]',0,1,0,0,43,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (130,2,'youtube-services','YouTube Services','Enterprise',30000.00,NULL,'Monthly channel management','Brands and media companies','["Monthly channel management", "Dedicated editor", "Unlimited revisions"]',0,1,0,0,44,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (131,2,'event-videos','Event Videos','Basic',15000.00,NULL,'Coverage of up to three hours','Small events and private functions','["Coverage up to 3 hours", "1 editor", "1 revision", "HD delivery"]',0,1,0,0,45,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (132,2,'event-videos','Event Videos','Standard',35000.00,NULL,'Coverage of up to six hours','Conferences, launches and showcases','["Coverage up to 6 hours", "2 cameras", "2 revisions", "HD / 4K delivery"]',0,1,0,0,46,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (133,2,'event-videos','Event Videos','Premium',70000.00,NULL,'Full-day multi-camera coverage','Festivals, tours and full-day programmes','["Full-day coverage", "Multi-camera", "Highlight and full film", "3 revisions"]',0,1,0,0,47,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (134,2,'event-videos','Event Videos','Enterprise',150000.00,NULL,'Multi-day event crew with a dedicated producer','Tours, festivals and corporate programmes','["Multi-day event crew", "Dedicated producer", "Unlimited revisions", "Priority delivery"]',0,1,0,0,48,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (135,2,'corporate-videos','Corporate Videos','Basic',20000.00,NULL,'Script support and a single location','Company profiles and internal films','["Script support", "Single location", "1 revision", "HD delivery"]',0,1,0,0,49,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (136,2,'corporate-videos','Corporate Videos','Standard',50000.00,NULL,'Script, storyboard and professional talent','Recruitment and product films','["Script and storyboard", "Professional talent", "2 revisions", "4K delivery"]',0,1,0,0,50,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (137,2,'corporate-videos','Corporate Videos','Premium',100000.00,NULL,'Full production with motion graphics','Brand campaigns and case studies','["Full production", "Motion graphics", "3 revisions", "4K delivery"]',0,1,0,0,51,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (138,2,'corporate-videos','Corporate Videos','Enterprise',200000.00,NULL,'Campaign-scale production','Multi-film campaigns and long-term retainers','["Campaign-scale production", "Dedicated project manager", "Unlimited revisions", "Commercial usage rights"]',0,1,0,0,52,'2026-09-27 09:12:55');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (139,2,'video-bundles','Video','Basic',10000.00,NULL,'Single-camera shoot and a straight edit',NULL,'["One shoot day", "Single camera setup", "Basic edit and colour", "HD delivery"]',0,1,0,1,1,'2026-09-30 06:07:26');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (140,2,'video-bundles','Video','Standard',25000.00,NULL,'Multi-camera coverage with a full edit',NULL,'["Up to two shoot days", "Multi-camera setup", "Full edit and sound mix", "HD delivery"]',0,1,0,1,2,'2026-09-30 06:07:26');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (141,2,'video-bundles','Video','Premium',50000.00,NULL,'Produced piece with motion graphics',NULL,'["Pre-production planning", "Multi-camera and lighting", "Motion graphics", "4K delivery"]',0,1,0,1,3,'2026-09-30 06:07:26');
INSERT INTO `service_plans` (`id`, `service_id`, `group_key`, `group_label`, `name`, `price`, `price_note`, `description`, `best_for`, `features`, `is_default`, `is_active`, `is_enquiry`, `is_orderable`, `sort_order`, `created_at`) VALUES (142,2,'video-bundles','Video','Enterprise',100000.00,NULL,'Full production with a dedicated crew',NULL,'["Full production crew", "Script and storyboarding", "Dedicated editor and colourist", "4K delivery"]',0,1,0,1,4,'2026-09-30 06:07:26');
-- --------------------------------------------------------

--
-- Table structure for table `booking_items`
--
-- The package snapshot for an order. Multi-package orders are no longer
-- allowed, so every booking carries exactly one line here; the table is kept
-- separate from bookings so the charged price stays a snapshot. booking_id +
-- plan_id is unique and both foreign keys cascade.
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
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- One package line per order, at the price that order was charged, mirrored by
-- bookings.plan_id. Multi-package orders are no longer allowed, so every
-- booking here carries exactly one line.
--
INSERT INTO `booking_items` (`id`, `booking_id`, `plan_id`, `plan_name`, `plan_group`, `plan_group_label`, `unit_price`, `qty`, `line_total`) VALUES
(1,'BDCM-1A0001',12,'Verified Pro','','Membership','4999.00',1,'4999.00'),
(2,'BDCM-1A0002',11,'Premium','','Membership','2499.00',1,'2499.00'),
(3,'BDCM-1A0003',10,'Standard','','Membership','999.00',1,'999.00'),
(4,'BDCM-1A0004',9,'Basic','','Membership','499.00',1,'499.00'),
(5,'BDCM-1A0005',12,'Verified Pro','','Membership','4999.00',1,'4999.00'),
(6,'BDCM-1A0006',11,'Premium','','Membership','2499.00',1,'2499.00'),
(7,'BDCM-1A0007',9,'Basic','','Membership','499.00',1,'499.00'),
(8,'BDCM-2B0001',1,'Basic','','Audio','11500.00',1,'11500.00'),
(9,'BDCM-2B0002',2,'Standard','','Audio','23500.00',1,'23500.00'),
(10,'BDCM-2B0003',3,'Premium','','Audio','36500.00',1,'36500.00'),
(11,'BDCM-2B0004',4,'Enterprise','','Audio','75000.00',1,'75000.00'),
(12,'BDCM-2B0005',1,'Basic','','Audio','11500.00',1,'11500.00'),
(14,'BDCM-2B0006',3,'Premium','','Audio','36500.00',1,'36500.00'),
(15,'BDCM-2B0007',1,'Basic','','Audio','11500.00',1,'11500.00'),
(16,'BDCM-2B0008',2,'Standard','','Audio','23500.00',1,'23500.00'),
(17,'BDCM-3C0001',13,'Basic','singing','Singing','2999.00',1,'2999.00'),
(18,'BDCM-3C0002',16,'Enterprise','singing','Singing','32999.00',1,'32999.00'),
(19,'BDCM-3C0003',17,'Basic','music-production','Music Production','3999.00',1,'3999.00'),
(20,'BDCM-3C0004',19,'Premium','music-production','Music Production','14999.00',1,'14999.00'),
(21,'BDCM-3C0005',21,'Basic','instrument','Instrument','2999.00',1,'2999.00'),
(22,'BDCM-3C0006',23,'Premium','instrument','Instrument','9999.00',1,'9999.00'),
(23,'BDCM-3C0007',25,'Basic','video-editing','Video Editing','3999.00',1,'3999.00'),
(24,'BDCM-3C0008',28,'Enterprise','video-editing','Video Editing','29999.00',1,'29999.00'),
(25,'BDCM-3C0009',14,'Standard','singing','Singing','8999.00',1,'8999.00'),
(27,'BDCM-3C0010',22,'Standard','instrument','Instrument','5999.00',1,'5999.00'),
(28,'BDCM-3C0011',15,'Premium','singing','Singing','17999.00',1,'17999.00'),
(30,'BDCM-3C0012',24,'Enterprise','instrument','Instrument','19999.00',1,'19999.00'),
(33,'BDCM-4D0001',5,'Release Plan','','Distribution','199.00',1,'199.00'),
(34,'BDCM-4D0002',6,'Artist Unlimited','','Distribution','1199.00',1,'1199.00'),
(35,'BDCM-4D0003',5,'Release Plan','','Distribution','199.00',1,'199.00'),
(36,'BDCM-4D0004',7,'PRO Label','','Distribution','7999.00',1,'7999.00'),
(37,'BDCM-4D0005',8,'Limitless Label','','Distribution','14999.00',1,'14999.00'),
(38,'BDCM-4D0006',5,'Release Plan','','Distribution','199.00',1,'199.00'),
(39,'BDCM-4D0007',6,'Artist Unlimited','','Distribution','1199.00',1,'1199.00'),
(40,'BDCM-6F0001',29,'Author / Composer','','Membership','2499.00',1,'2499.00'),
(41,'BDCM-6F0002',29,'Author / Composer','','Membership','2499.00',1,'2499.00'),
(42,'BDCM-6F0003',30,'Publisher','','Membership','4999.00',1,'4999.00'),
(43,'BDCM-6F0004',29,'Author / Composer','','Membership','2499.00',1,'2499.00'),
(44,'BDCM-6F0005',30,'Publisher','','Membership','4999.00',1,'4999.00');

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
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_records`
--

INSERT INTO `service_records` (`id`, `booking_id`, `service_id`, `headline`, `sub_headline`, `progress`, `starts_on`, `ends_on`, `location`, `notes`, `created_at`, `updated_at`) VALUES
(1,'BDCM-1A0001',1,'Abhinav Singh','Solo Vocalist - Live Show','Completed','2026-07-18','2026-07-18','Kanpur, Uttar Pradesh','Setlist confirmed. Backline provided by the artist.','2026-07-19 16:40:00','2026-07-19 16:40:00'),
(2,'BDCM-1A0002',1,'Ritika Bhardwaj','Video Editor - Retainer','Profile Shared','2026-09-20',NULL,'Bengaluru, Karnataka','Profile and day-rate sheet shared. Awaiting client sign-off on the retainer.','2026-09-18 10:05:00','2026-09-18 10:05:00'),
(3,'BDCM-1A0004',1,'Shortlisting in progress','DJ / Open Format','Shortlisting',NULL,NULL,'Pune, Maharashtra','Profile shortlisted with two other DJs. Booking on hold until the client confirms the December date.','2026-08-20 14:10:00','2026-08-20 14:10:00'),
(4,'BDCM-2B0001',2,'Rough Mix + Masters','Audio Basic','Delivered','2026-07-08','2026-07-24','https://deliveries.bdc.example/B2-0001','Five masters delivered in WAV and FLAC. One revision included and used.','2026-07-25 12:00:00','2026-07-25 12:00:00'),
(5,'BDCM-2B0002',2,'Cinematic Music Video','Audio Standard','In Production','2026-08-26','2026-10-06','Delhi NCR','Shoot wrapped. Offline edit and grade in progress.','2026-09-22 17:30:00','2026-09-22 17:30:00'),
(6,'BDCM-2B0003',2,'Album Production Slot','Audio Premium','Scheduled','2026-10-05',NULL,'BDC Studio A, Lucknow','Slot held for October. Producer unavailable earlier, waiting on the customer to confirm dates.','2026-09-15 09:20:00','2026-09-15 09:20:00'),
(7,'BDCM-2B0004',2,'Brand Film + 6 Cutdowns','Audio Enterprise','Delivered','2026-06-22','2026-08-14','https://deliveries.bdc.example/2B-0004','Master film and all six verticals delivered. Two revision rounds used.','2026-08-15 18:00:00','2026-08-15 18:00:00'),
(8,'BDCM-2B0005',2,'Studio Session + Production','Audio Basic','Editing','2026-09-11','2026-10-09','BDC Studio A, Lucknow','Tracking complete, edit and mix in progress.','2026-09-23 11:15:00','2026-09-23 11:15:00'),
(9,'BDCM-2B0007',2,'Radio Spot Voiceover','Audio Basic','Delivered','2026-09-01','2026-09-06','https://deliveries.bdc.example/2B-0007','Two-hour session, WAV masters delivered.','2026-09-07 10:00:00','2026-09-07 10:00:00'),
(10,'BDCM-3C0001',3,'Batch 2026-06 - Anjali Menon','Beginner','Completed','2026-06-24','2026-07-22','https://meet.google.com/example-batch','Eight sessions completed. All practice recordings shared.','2026-07-23 12:00:00','2026-07-23 12:00:00'),
(11,'BDCM-3C0002',3,'Batch 2026-09 - Farhan Qureshi','Advanced','Ongoing','2026-09-05','2027-09-04','BDC Studio B, Lucknow','Weekly studio slots booked. First assessment done.','2026-09-20 16:00:00','2026-09-20 16:00:00'),
(12,'BDCM-3C0003',3,'Batch 2026-07 - Devansh Rao','Beginner','Completed','2026-07-16','2026-08-13','https://meet.google.com/example-batch','Beat-making fundamentals covered, two portfolio tracks produced.','2026-08-14 17:30:00','2026-08-14 17:30:00'),
(13,'BDCM-3C0005',3,'Batch 2026-08 - Ishaan Kapoor','Beginner','Scheduled','2026-08-22','2026-09-19','BDC Studio C, Lucknow','Weekly slot confirmed, instrument provided by the academy.','2026-09-12 10:40:00','2026-09-12 10:40:00'),
(14,'BDCM-3C0006',3,'Batch 2026-07 - Neel Shah','Advanced','Completed','2026-07-04','2026-07-25','https://meet.google.com/example-batch','Twelve sessions completed, final recording submitted.','2026-07-26 15:00:00','2026-07-26 15:00:00'),
(15,'BDCM-3C0008',3,'Batch 2026-09 - Sana Iqbal','Intermediate','Ongoing','2026-09-14','2026-10-12','https://meet.google.com/example-batch','Mentor assigned, first portfolio review done.','2026-09-21 09:50:00','2026-09-21 09:50:00'),
(16,'BDCM-3C0009',3,'Batch 2026-09 - Priyanka Deol','Singing Standard','Ongoing','2026-09-08','2026-12-08','https://meet.google.com/example-batch','Weekly online slots booked. First assessment done.','2026-09-19 11:10:00','2026-09-19 11:10:00'),
(17,'BDCM-3C0011',3,'Vocal Intensive','Singing Premium','Ongoing','2026-09-22','2027-01-22','BDC Studio A, Lucknow','Mentor assigned from the first week. Sessions run on Saturdays.','2026-09-25 08:45:00','2026-09-25 08:45:00'),
(18,'BDCM-5E0002',5,'Dil Ki Awaaz Launch','Music Video Promotion','Campaign Planning','2026-09-15','2026-10-13','','Quote sent at Rs.42,000. Media plan being finalised.','2026-09-16 11:00:00','2026-09-16 11:00:00'),
(19,'BDCM-5E0004',5,'Bhakti Sagar Festival Push','YouTube Ads','Brief Received',NULL,NULL,'','Brief received, waiting on the final cut before costing the campaign.','2026-08-08 10:00:00','2026-08-08 10:00:00'),
(20,'BDCM-5E0005',5,'Sapno Ka Safar Launch','Release Launch','Completed','2026-06-18','2026-07-16','https://reports.bdc.example/5E-0005','Campaign ran to plan. Final report shared, 1.8M views across the two platforms.','2026-07-18 17:00:00','2026-07-18 17:00:00'),
(21,'BDCM-6F0002',6,'IPRS/2026/44821','Author / Composer','Submitted to IPRS','2026-09-11',NULL,'','PAN and address proof received. Application submitted to IPRS.','2026-09-17 12:20:00','2026-09-17 12:20:00'),
(22,'BDCM-6F0003',6,'IPRS/2026/40117','Publisher','Completed','2026-07-09','2026-08-28','https://iprs.example/catalogue/40117','All 22 works registered. Registration certificate issued.','2026-08-29 15:00:00','2026-08-29 15:00:00'),
(23,'BDCM-6F0005',6,'Application under review','Publisher','Under Review','2026-09-15',NULL,'','On hold pending a signed publishing agreement from the label.','2026-09-22 09:30:00','2026-09-22 09:30:00');

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
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `uploaded_files`
--

-- Seed rows only; none of these files exist on disk. file_path mirrors the
-- layout booking_promote_uploads() actually writes (data/uploads/booking/<id>/)
-- and the names are neutral placeholders rather than real-looking documents.
INSERT INTO `uploaded_files` (`id`, `booking_id`, `field_name`, `original_name`, `stored_name`, `file_path`, `mime_type`, `file_size`, `uploaded_at`) VALUES
(1,'BDCM-1A0001','portfolio_file','rahul-portfolio-2026.pdf','1A0001_rahul-portfolio-2026.pdf','data/uploads/booking/BDCM-1A0001/1A0001_rahul-portfolio-2026.pdf','application/pdf',2841600,'2026-06-12 10:20:00'),
(2,'BDCM-2B0001','project_upload','monsoon_demos_v1.wav','2B0001_monsoon_demos_v1.wav','data/uploads/booking/BDCM-2B0001/2B0001_monsoon_demos_v1.wav','audio/wav',8388608,'2026-07-06 10:15:00'),
(3,'BDCM-2B0001','project_upload','monsoon_demos_v2.wav','2B0001_monsoon_demos_v2.wav','data/uploads/booking/BDCM-2B0001/2B0001_monsoon_demos_v2.wav','audio/wav',11534336,'2026-07-06 10:15:00'),
(4,'BDCM-2B0002','project_upload','treatment_v3_reel.mp4','2B0002_treatment_v3_reel.mp4','data/uploads/booking/BDCM-2B0002/2B0002_treatment_v3_reel.mp4','video/mp4',20971520,'2026-08-24 14:20:00'),
(5,'BDCM-2B0004','project_upload','brand_film_storyboard.mov','2B0004_brand_film_storyboard.mov','data/uploads/booking/BDCM-2B0004/2B0004_brand_film_storyboard.mov','video/quicktime',15728640,'2026-06-18 09:50:00'),
(6,'BDCM-2B0004','project_upload','location_scouting.mp4','2B0004_location_scouting.mp4','data/uploads/booking/BDCM-2B0004/2B0004_location_scouting.mp4','video/mp4',26214400,'2026-06-18 09:50:00'),
(7,'BDCM-4D0001','audio_file','dil_ki_awaaz_master.wav','4D0001_dil_ki_awaaz_master.wav','data/uploads/booking/BDCM-4D0001/4D0001_dil_ki_awaaz_master.wav','audio/wav',41943040,'2026-07-28 14:30:00'),
(8,'BDCM-4D0001','cover_artwork','dil_ki_awaaz_cover.jpg','4D0001_dil_ki_awaaz_cover.jpg','data/uploads/booking/BDCM-4D0001/4D0001_dil_ki_awaaz_cover.jpg','image/jpeg',2097152,'2026-07-28 14:30:00'),
(9,'BDCM-4D0001','metadata_file','dil_ki_awaaz_metadata.csv','4D0001_dil_ki_awaaz_metadata.csv','data/uploads/booking/BDCM-4D0001/4D0001_dil_ki_awaaz_metadata.csv','text/csv',8192,'2026-07-28 14:30:00'),
(10,'BDCM-4D0002','audio_file','echoes_of_soul_master.wav','4D0002_echoes_of_soul_master.wav','data/uploads/booking/BDCM-4D0002/4D0002_echoes_of_soul_master.wav','audio/wav',62914560,'2026-09-10 09:00:00'),
(11,'BDCM-4D0002','cover_artwork','echoes_album_cover.png','4D0002_echoes_album_cover.png','data/uploads/booking/BDCM-4D0002/4D0002_echoes_album_cover.png','image/png',3145728,'2026-09-10 09:00:00'),
(12,'BDCM-4D0002','metadata_file','echoes_of_soul_metadata.csv','4D0002_echoes_of_soul_metadata.csv','data/uploads/booking/BDCM-4D0002/4D0002_echoes_of_soul_metadata.csv','text/csv',12288,'2026-09-10 09:00:00'),
(13,'BDCM-4D0003','audio_file','monsoon_dreams_master.wav','4D0003_monsoon_dreams_master.wav','data/uploads/booking/BDCM-4D0003/4D0003_monsoon_dreams_master.wav','audio/wav',73400320,'2026-09-22 11:15:00'),
(14,'BDCM-4D0003','cover_artwork','monsoon_dreams_cover.jpg','4D0003_monsoon_dreams_cover.jpg','data/uploads/booking/BDCM-4D0003/4D0003_monsoon_dreams_cover.jpg','image/jpeg',1835008,'2026-09-22 11:15:00'),
(15,'BDCM-4D0004','audio_file','sapno_ka_safar_master.wav','4D0004_sapno_ka_safar_master.wav','data/uploads/booking/BDCM-4D0004/4D0004_sapno_ka_safar_master.wav','audio/wav',104857600,'2026-09-12 16:40:00'),
(16,'BDCM-4D0004','cover_artwork','sapno_ka_safar_cover.jpg','4D0004_sapno_ka_safar_cover.jpg','data/uploads/booking/BDCM-4D0004/4D0004_sapno_ka_safar_cover.jpg','image/jpeg',2621440,'2026-09-12 16:40:00'),
(17,'BDCM-4D0004','metadata_file','sapno_metadata.xlsx','4D0004_sapno_metadata.xlsx','data/uploads/booking/BDCM-4D0004/4D0004_sapno_metadata.xlsx','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',24576,'2026-09-12 16:40:00'),
(18,'BDCM-4D0005','audio_file','beparwah_final_master.wav','4D0005_beparwah_final_master.wav','data/uploads/booking/BDCM-4D0005/4D0005_beparwah_final_master.wav','audio/wav',94371840,'2026-08-20 10:50:00'),
(19,'BDCM-4D0005','cover_artwork','beparwah_cover.jpg','4D0005_beparwah_cover.jpg','data/uploads/booking/BDCM-4D0005/4D0005_beparwah_cover.jpg','image/jpeg',2293760,'2026-08-20 10:50:00'),
(20,'BDCM-4D0006','audio_file','raat_ka_safar_master.wav','4D0006_raat_ka_safar_master.wav','data/uploads/booking/BDCM-4D0006/4D0006_raat_ka_safar_master.wav','audio/wav',37748736,'2026-09-26 19:20:00'),
(21,'BDCM-4D0006','cover_artwork','raat_ka_safar_cover.jpg','4D0006_raat_ka_safar_cover.jpg','data/uploads/booking/BDCM-4D0006/4D0006_raat_ka_safar_cover.jpg','image/jpeg',1887436,'2026-09-26 19:20:00'),
(22,'BDCM-4D0007','audio_file','bittersweet_master.wav','4D0007_bittersweet_master.wav','data/uploads/booking/BDCM-4D0007/4D0007_bittersweet_master.wav','audio/wav',46137344,'2026-08-14 13:15:00'),
(23,'BDCM-4D0007','cover_artwork','bittersweet_cover.png','4D0007_bittersweet_cover.png','data/uploads/booking/BDCM-4D0007/4D0007_bittersweet_cover.png','image/png',2411724,'2026-08-14 13:15:00'),
(24,'BDCM-6F0001','pan_card','sonia_pan.pdf','6F0001_sonia_pan.pdf','data/uploads/booking/BDCM-6F0001/6F0001_sonia_pan.pdf','application/pdf',524288,'2026-09-03 08:45:00'),
(25,'BDCM-6F0001','address_proof','sonia_address_proof.jpg','6F0001_sonia_address_proof.jpg','data/uploads/booking/BDCM-6F0001/6F0001_sonia_address_proof.jpg','image/jpeg',1048576,'2026-09-03 08:45:00'),
(26,'BDCM-6F0001','photo','sonia_photo.jpg','6F0001_sonia_photo.jpg','data/uploads/booking/BDCM-6F0001/6F0001_sonia_photo.jpg','image/jpeg',275251,'2026-09-03 08:45:00'),
(27,'BDCM-6F0002','pan_card','rahul_pan.pdf','6F0002_rahul_pan.pdf','data/uploads/booking/BDCM-6F0002/6F0002_rahul_pan.pdf','application/pdf',614400,'2026-09-10 14:00:00'),
(28,'BDCM-6F0002','address_proof','rahul_address_proof.pdf','6F0002_rahul_address_proof.pdf','data/uploads/booking/BDCM-6F0002/6F0002_rahul_address_proof.pdf','application/pdf',716800,'2026-09-10 14:00:00'),
(29,'BDCM-6F0002','photo','rahul_photo.jpg','6F0002_rahul_photo.jpg','data/uploads/booking/BDCM-6F0002/6F0002_rahul_photo.jpg','image/jpeg',327680,'2026-09-10 14:00:00'),
(30,'BDCM-6F0002','song_proof','sapno_lyrics.pdf','6F0002_sapno_lyrics.pdf','data/uploads/booking/BDCM-6F0002/6F0002_sapno_lyrics.pdf','application/pdf',184320,'2026-09-10 14:00:00'),
(31,'BDCM-6F0003','pan_card','kavita_pan.pdf','6F0003_kavita_pan.pdf','data/uploads/booking/BDCM-6F0003/6F0003_kavita_pan.pdf','application/pdf',491520,'2026-07-08 11:30:00'),
(32,'BDCM-6F0003','address_proof','kavita_address_proof.pdf','6F0003_kavita_address_proof.pdf','data/uploads/booking/BDCM-6F0003/6F0003_kavita_address_proof.pdf','application/pdf',802816,'2026-07-08 11:30:00'),
(33,'BDCM-6F0003','photo','kavita_photo.png','6F0003_kavita_photo.png','data/uploads/booking/BDCM-6F0003/6F0003_kavita_photo.png','image/png',294912,'2026-07-08 11:30:00'),
(34,'BDCM-6F0004','pan_card','faisal_pan.pdf','6F0004_faisal_pan.pdf','data/uploads/booking/BDCM-6F0004/6F0004_faisal_pan.pdf','application/pdf',458752,'2026-09-27 10:05:00'),
(35,'BDCM-6F0004','address_proof','faisal_address_proof.jpg','6F0004_faisal_address_proof.jpg','data/uploads/booking/BDCM-6F0004/6F0004_faisal_address_proof.jpg','image/jpeg',943718,'2026-09-27 10:05:00'),
(36,'BDCM-6F0004','photo','faisal_photo.jpg','6F0004_faisal_photo.jpg','data/uploads/booking/BDCM-6F0004/6F0004_faisal_photo.jpg','image/jpeg',262144,'2026-09-27 10:05:00'),
(37,'BDCM-6F0005','pan_card','arjun_pan.pdf','6F0005_arjun_pan.pdf','data/uploads/booking/BDCM-6F0005/6F0005_arjun_pan.pdf','application/pdf',512000,'2026-09-14 16:20:00'),
(38,'BDCM-6F0005','address_proof','arjun_address_proof.jpg','6F0005_arjun_address_proof.jpg','data/uploads/booking/BDCM-6F0005/6F0005_arjun_address_proof.jpg','image/jpeg',870400,'2026-09-14 16:20:00'),
(39,'BDCM-6F0005','photo','arjun_photo.jpg','6F0005_arjun_photo.jpg','data/uploads/booking/BDCM-6F0005/6F0005_arjun_photo.jpg','image/jpeg',311296,'2026-09-14 16:20:00');


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
