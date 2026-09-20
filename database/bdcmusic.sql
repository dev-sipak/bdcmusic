-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 20, 2026 at 12:05 PM
-- Server version: 8.0.31
-- PHP Version: 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
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
(13, 2, 'Basant Pathak', 'basant-pathak', NULL, 'Uttar Pradesh, India', 'Versatile actor with 5 years of experience in lead and supporting roles. Also active in modelling and commercial shoots across UP and nearby regions.', 1, '2026-09-20 01:13:25', '2026-09-20 16:13:58'),
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
  `reply` text,
  `replied_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_artist_id` (`artist_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `artist_enquiries`
--

INSERT INTO `artist_enquiries` (`id`, `artist_id`, `name`, `email`, `phone`, `message`, `status`, `created_at`) VALUES
(1, 1, 'Rahul Sharma', 'rahul.sharma@gmail.com', '9876543210', 'Hi, I want to book this artist for a wedding event on 15th March. Please share availability.', 'new', '2026-09-18 10:30:00'),
(2, 2, 'Priya Mehta', 'priya.mehta@outlook.com', '9123456780', 'Interested in hiring for a music video shoot. Budget is flexible.', 'read', '2026-09-17 14:15:00'),
(3, 1, 'Amit Verma', 'amit.verma@yahoo.com', '9988776655', 'Need a singer for corporate event in Delhi. 2 hours performance.', 'replied', '2026-09-16 09:45:00'),
(4, 3, 'Sneha Kapoor', 'sneha.k@gmail.com', '9871234567', 'Looking for a dancer for a TV commercial. Shooting in Mumbai.', 'new', '2026-09-19 11:20:00'),
(5, 2, 'Vikram Singh', 'vikram.s@rediffmail.com', '9765432108', 'Can you share the pricing for a live performance at a birthday party?', 'new', '2026-09-20 08:00:00'),
(6, 4, 'Neha Gupta', 'neha.gupta@gmail.com', '9654321098', 'We are a production house looking for background singers for an album.', 'read', '2026-09-15 16:30:00'),
(7, 1, 'Rohit Joshi', 'rohit.joshi@hotmail.com', '9543210987', 'Want to discuss pricing for a 3-day music festival.', 'replied', '2026-09-14 12:00:00'),
(8, 5, 'Ananya Reddy', 'ananya.r@gmail.com', '9432109876', 'Need a music producer for independent album. 5 tracks.', 'new', '2026-09-19 17:45:00');

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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(16, 13, 'Each Video', '3500.00', 0, '2026-09-20 16:13:58');

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
  `service_id` int NOT NULL,
  `meta` json DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('pending','processing','hold','delivered','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_status` enum('awaiting','paid','refunded','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'awaiting',
  `payment_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `razorpay_order_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auto_create_account` tinyint(1) NOT NULL DEFAULT '0',
  `auto_password` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `paid_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_booking_id` (`booking_id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_service` (`service_id`),
  KEY `idx_status` (`status`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_id`, `invoice_no`, `customer_id`, `service_id`, `meta`, `message`, `price`, `status`, `payment_status`, `payment_id`, `razorpay_order_id`, `auto_create_account`, `auto_password`, `created_at`, `updated_at`, `paid_at`) VALUES
(1, 'BDCM-A1B2C3', 'INV-A1B2C3', 'CUST-A1B2C3D4', 1, '{"artist_goal": "Build my brand as an independent Hindi pop artist"}', 'Looking forward to the branding package.', '5000.00', 'delivered', 'paid', 'pay_demo_001', 'order_demo_001', 0, NULL, '2026-08-15 10:35:00', '2026-09-18 19:49:03', '2026-08-15 10:40:00'),
(2, 'BDCM-E5F6G7', 'INV-E5F6G7', 'CUST-E5F6G7H8', 2, '{"budget": "₹25,000", "deadline": "15 working days", "service_type": "Music Video", "delivery_formats": ["MP4", "4K"], "service_category": "Video"}', 'Need a cinematic music video.', '12000.00', 'processing', 'paid', 'pay_demo_002', 'order_demo_002', 0, NULL, '2026-09-01 09:00:00', '2026-09-18 19:49:03', '2026-09-01 09:05:00'),
(3, 'BDCM-I9J0K1', 'INV-I9J0K1', 'CUST-I9J0K1L2', 2, '{"budget": "₹5,000", "deadline": "10 working days", "service_type": "Recording", "delivery_formats": ["WAV", "FLAC"], "service_category": "Audio"}', 'Recording a 5-song EP.', '7000.00', 'processing', 'paid', 'pay_demo_003', 'order_demo_003', 0, NULL, '2026-08-28 14:20:00', '2026-09-18 19:49:03', '2026-08-28 14:25:00'),
(4, 'BDCM-M3N4O5', 'INV-M3N4O5', 'CUST-M3N4O5P6', 3, '{"level": "Intermediate", "course": "Singing", "class_mode": "Online"}', 'Want to improve my classical vocals.', '3000.00', 'pending', 'awaiting', NULL, NULL, 0, NULL, '2026-09-05 11:00:00', '2026-09-18 19:49:03', NULL),
(5, 'BDCM-Q7R8S9', 'INV-Q7R8S9', 'CUST-Q7R8S9T0', 3, '{"level": "Beginner", "course": "Music Production", "class_mode": "Online"}', 'Learning music production from scratch.', '3000.00', 'delivered', 'paid', 'pay_demo_005', 'order_demo_005', 0, NULL, '2026-07-20 16:05:00', '2026-09-18 19:49:03', '2026-07-20 16:10:00'),
(6, 'BDCM-U1V2W3', 'INV-U1V2W3', 'CUST-U1V2W3X4', 2, '{"budget": "₹7,000", "deadline": "10 working days", "service_type": "Recording", "delivery_formats": ["WAV", "FLAC"], "service_category": "Audio"}', 'Recording guitar tracks.', '7000.00', 'cancelled', 'refunded', 'pay_demo_006', 'order_demo_006', 0, NULL, '2026-09-03 08:35:00', '2026-09-18 19:49:03', '2026-09-03 08:40:00'),
(7, 'BDCM-Y5Z6A7', 'INV-Y5Z6A7', 'CUST-Y5Z6A7B8', 2, '{"budget": "₹3,500", "deadline": "7 working days", "service_type": "Reel / Promo", "delivery_formats": ["Social Media Reels"], "service_category": "Video"}', 'Need 5 Instagram reels.', '12000.00', 'delivered', 'paid', 'pay_demo_007', 'order_demo_007', 0, NULL, '2026-08-10 13:15:00', '2026-09-18 19:49:03', '2026-08-10 13:20:00'),
(8, 'BDCM-C9D0E1', 'INV-C9D0E1', 'CUST-C9D0E1F2', 1, '{"artist_goal": "Launch my debut album and get distribution deals"}', 'Ready for my album launch.', '5000.00', 'processing', 'paid', 'pay_demo_008', 'order_demo_008', 0, NULL, '2026-09-08 10:05:00', '2026-09-18 19:49:03', '2026-09-08 10:10:00'),
(9, 'BDCM-172401', NULL, 'CUST-I9J0K1L2', 4, '{"upc": "No", "isrc": "Yes", "genre": "Pop", "language": "Hindi", "artist_name": "Amit Kumar", "release_date": "2026-09-15", "release_type": "Single", "release_title": "Dil Ki Awaaz", "copyright_help": "Yes"}', 'My debut single, please distribute worldwide.', '0.00', 'processing', 'awaiting', NULL, NULL, 0, NULL, '2026-08-28 14:30:00', '2026-09-18 19:49:03', NULL),
(10, 'BDCM-172411', NULL, 'CUST-G3H4I5J6', 4, '{"upc": "Yes", "isrc": "Yes", "genre": "Rock", "language": "English", "artist_name": "Arjun Reddy", "release_date": "2026-10-01", "release_type": "Album", "release_title": "Echoes of Soul", "copyright_help": "No"}', '6-track album, all artwork attached.', '0.00', 'pending', 'awaiting', NULL, NULL, 0, NULL, '2026-09-10 09:00:00', '2026-09-18 19:49:03', NULL),
(11, 'BDCM-172412', NULL, 'CUST-K7L8M9N0', 4, '{"upc": "No", "isrc": "No", "genre": "Folk", "language": "Hindi", "artist_name": "Kavita Desai", "release_date": "2026-09-20", "release_type": "Single", "release_title": "Monsoon Dreams", "copyright_help": "Yes"}', 'Independent folk release, need ISRC code.', '0.00', 'pending', 'awaiting', NULL, NULL, 0, NULL, '2026-09-12 11:00:00', '2026-09-18 19:49:03', NULL),
(12, 'BDCM-172420', NULL, 'CUST-U1V2W3X4', 6, '{"ifsc": "SBIN0001234", "bank_name": "State Bank of India", "song_links": "https://open.spotify.com/track/example1", "song_title": "Raat Ki Rani", "artist_name": "Sonia Verma", "song_released": "yes", "account_holder": "Sonia Verma", "account_number": "123456789012", "applicant_type": "composer", "membership_type": "author-composer"}', 'Need IPRS registration for my compositions.', '0.00', 'cancelled', 'awaiting', NULL, NULL, 0, NULL, '2026-09-03 08:45:00', '2026-09-18 19:49:03', NULL),
(13, 'BDCM-172421', NULL, 'CUST-A1B2C3D4', 6, '{"ifsc": "HDFC0001234", "bank_name": "HDFC Bank", "song_links": "https://youtube.com/watch?v=example2", "song_title": "Sapno Ka Safar", "artist_name": "Rahul Sharma", "song_released": "yes", "account_holder": "Rahul Sharma", "account_number": "987654321012", "applicant_type": "author-composer", "membership_type": "author-composer"}', 'Register my song and lyrics with IPRS.', '0.00', 'pending', 'awaiting', NULL, NULL, 0, NULL, '2026-09-10 14:00:00', '2026-09-18 19:49:03', NULL),
(14, 'BDCM-172422', NULL, 'CUST-K7L8M9N0', 6, '{"ifsc": "ICIC0005678", "bank_name": "ICICI Bank", "song_links": "https://gaana.com/track/example3", "song_title": "Bhakti Sagar", "artist_name": "Kavita Devi", "song_released": "yes", "account_holder": "Kavita Desai", "account_number": "567890123456", "applicant_type": "publisher", "membership_type": "publisher"}', 'Publishing rights for devotional music catalog.', '0.00', 'pending', 'awaiting', NULL, NULL, 0, NULL, '2026-09-12 12:00:00', '2026-09-18 19:49:03', NULL),
(15, 'BDCM-PROMO0', NULL, 'CUST-M3N4O5P6', 5, '{"budget": "₹5,000", "campaign_type": "Social Media", "target_platform": "Instagram, YouTube"}', 'Promote my new single on social media.', '5000.00', 'pending', 'awaiting', NULL, NULL, 0, NULL, '2026-09-05 12:00:00', '2026-09-18 19:49:03', NULL);

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
(1, 'CUST-I9J0K1L2', 'BDCM-172401', 'Dil Ki Awaaz', 'single', 'data/uploads/BDCM-172401/cover_art_3000.jpg', 'IN-R5S-23-00001', NULL, '2026-09-15', 'live', 'Dil ki awaaz hai tu, mere dil ki sadaa hai tu...\nHar pal mein bas tera naam hai, tera intezaar hai...\nKaise bataaun dil ko, kitna chahta hoon main...\nTu hi meri zindagi hai, tu hi meri duaa hai...', 0, 1, '2026-08-28 14:30:00', '2026-09-15 06:00:00'),
(2, 'CUST-G3H4I5J6', 'BDCM-172411', 'Echoes of Soul', 'album', 'data/uploads/BDCM-172411/album_cover.png', NULL, '123456789012', '2026-10-01', 'approved', NULL, 0, 0, '2026-09-10 09:00:00', '2026-09-18 14:00:00'),
(3, 'CUST-K7L8M9N0', 'BDCM-172412', 'Monsoon Dreams', 'single', NULL, NULL, NULL, '2026-09-25', 'verification', 'Baarish ki boondein gir rahi hain, monsoon ka mausam aaya...\nTeri yaadon mein khoya main, sapno ka safar chal pada...\nHawaon mein tera naam hai, phoolon mein teri khushboo...\nMonsoon dreams mein duba main, teri bahon ki goonjhu...', 0, 0, '2026-09-12 11:00:00', '2026-09-19 10:30:00'),
(4, 'CUST-I9J0K1L2', NULL, 'Raatein', 'ep', NULL, 'IN-R5S-23-00002', NULL, '2026-11-10', 'pending', NULL, 1, 1, '2026-09-18 16:00:00', '2026-09-18 16:00:00'),
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
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE IF NOT EXISTS `services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `slug`, `name`, `is_active`, `created_at`) VALUES
(1, 'artists-marketplace', 'BDC Artists Marketplace', 1, '2026-09-18 19:49:01'),
(2, 'audio-video', 'Audio & Video Services', 1, '2026-09-18 19:49:01'),
(3, 'online-offline-classes', 'Online/Offline Classes', 1, '2026-09-18 19:49:01'),
(4, 'digital-distribution', 'Digital Music Distribution', 1, '2026-09-18 19:49:01'),
(5, 'promotion', 'Promotion Services', 1, '2026-09-18 19:49:01'),
(6, 'iprs', 'IPRS Services', 1, '2026-09-18 19:49:01');

-- --------------------------------------------------------

--
-- Table structure for table `uploaded_files`
--

DROP TABLE IF EXISTS `uploaded_files`;
CREATE TABLE IF NOT EXISTS `uploaded_files` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` int UNSIGNED NOT NULL DEFAULT '0',
  `uploaded_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_booking_id` (`booking_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `uploaded_files`
--

INSERT INTO `uploaded_files` (`id`, `booking_id`, `field_name`, `original_name`, `stored_name`, `file_path`, `mime_type`, `file_size`, `uploaded_at`) VALUES
(1, 'BDCM-E5F6G7', 'project_upload', 'track_demo_v1.wav', 'E5F6G7_track_demo_v1.wav', 'data/uploads/BDCM-E5F6G7/track_demo_v1.wav', 'audio/wav', 5242880, '2026-09-01 09:12:00'),
(2, 'BDCM-E5F6G7', 'project_upload', 'reference_video.mp4', 'E5F6G7_reference_video.mp4', 'data/uploads/BDCM-E5F6G7/reference_video.mp4', 'video/mp4', 15728640, '2026-09-01 09:12:00'),
(3, 'BDCM-172401', 'audio_file', 'dil_ki_awaaz_master.wav', '172401_dil_ki_awaaz_master.wav', 'data/uploads/BDCM-172401/dil_ki_awaaz_master.wav', 'audio/wav', 41943040, '2026-08-28 14:32:00'),
(4, 'BDCM-172401', 'cover_artwork', 'cover_art_3000.jpg', '172401_cover_art_3000.jpg', 'data/uploads/BDCM-172401/cover_art_3000.jpg', 'image/jpeg', 2097152, '2026-08-28 14:32:00'),
(5, 'BDCM-172411', 'audio_file', 'echoes_of_soul_track1.wav', '172411_echoes_of_soul_track1.wav', 'data/uploads/BDCM-172411/echoes_of_soul_track1.wav', 'audio/wav', 62914560, '2026-09-10 09:02:00'),
(6, 'BDCM-172411', 'cover_artwork', 'album_cover.png', '172411_album_cover.png', 'data/uploads/BDCM-172411/album_cover.png', 'image/png', 3145728, '2026-09-10 09:02:00'),
(7, 'BDCM-172420', 'pan_card', 'sonia_pan.pdf', '172420_sonia_pan.pdf', 'data/uploads/BDCM-172420/sonia_pan.pdf', 'application/pdf', 524288, '2026-09-03 08:47:00'),
(8, 'BDCM-172420', 'address_proof', 'sonia_aadhaar.jpg', '172420_sonia_aadhaar.jpg', 'data/uploads/BDCM-172420/sonia_aadhaar.jpg', 'image/jpeg', 1048576, '2026-09-03 08:47:00'),
(9, 'BDCM-172421', 'pan_card', 'rahul_pan.pdf', '172421_rahul_pan.pdf', 'data/uploads/BDCM-172421/rahul_pan.pdf', 'application/pdf', 614400, '2026-09-10 14:02:00'),
(10, 'BDCM-172421', 'address_proof', 'rahul_address.pdf', '172421_rahul_address.pdf', 'data/uploads/BDCM-172421/rahul_address.pdf', 'application/pdf', 716800, '2026-09-10 14:02:00');

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
  `role` enum('customer','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `profile_picture` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'website',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email` (`email`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `mobile`, `password_hash`, `role`, `profile_picture`, `source`, `created_at`, `updated_at`) VALUES
('CUST-A1B2C3D4', 'Rahul Sharma', 'rahul@example.com', '9876543210', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, 'website', '2026-09-18 19:49:03', '2026-09-18 19:49:03'),
('CUST-ADMIN0001', 'BDC Admin', 'admin@bdcmusic.in', '9999999999', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, 'website', '2026-09-18 19:49:03', '2026-09-18 19:49:03'),
('CUST-C9D0E1F2', 'Deepika Nair', 'deepika@example.com', '9119887766', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, 'guest_booking', '2026-09-18 19:49:03', '2026-09-18 19:49:03'),
('CUST-D39EFADC', 'test', 'test@123.com', '7412033323', '$2y$10$99Re0G4A/JwEXF3OpMFbZ.gQLdXo1yvkx6eClQ5bvH3ZQ.sqkkwdK', 'customer', NULL, 'website', '2026-09-19 01:33:48', '2026-09-19 01:33:48'),
('CUST-E5F6G7H8', 'Priya Patel', 'priya@example.com', '9988776655', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, 'website', '2026-09-18 19:49:03', '2026-09-18 19:49:03'),
('CUST-G3H4I5J6', 'Arjun Reddy', 'arjun@example.com', '9228776655', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, 'website', '2026-09-18 19:49:03', '2026-09-18 19:49:03'),
('CUST-I9J0K1L2', 'Amit Kumar', 'amit@example.com', '9112233445', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, 'guest_booking', '2026-09-18 19:49:03', '2026-09-18 19:49:03'),
('CUST-K7L8M9N0', 'Kavita Desai', 'kavita@example.com', '9337665544', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, 'website', '2026-09-18 19:49:03', '2026-09-18 19:49:03'),
('CUST-M3N4O5P6', 'Neha Gupta', 'neha@example.com', '9001122334', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, 'website', '2026-09-18 19:49:03', '2026-09-18 19:49:03'),
('CUST-Q7R8S9T0', 'Vikram Singh', 'vikram@example.com', '9871234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, 'guest_booking', '2026-09-18 19:49:03', '2026-09-18 19:49:03'),
('CUST-U1V2W3X4', 'Sonia Verma', 'sonia@example.com', '9911223344', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, 'website', '2026-09-18 19:49:03', '2026-09-18 19:49:03'),
('CUST-Y5Z6A7B8', 'Ravi Joshi', 'ravi@example.com', '9009887766', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, 'website', '2026-09-18 19:49:03', '2026-09-18 19:49:03');

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
  ADD CONSTRAINT `fk_bookings_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

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
-- Constraints for table `uploaded_files`
--
ALTER TABLE `uploaded_files`
  ADD CONSTRAINT `fk_files_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE;
SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
