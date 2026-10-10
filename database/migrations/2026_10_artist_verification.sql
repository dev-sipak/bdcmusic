-- ---------------------------------------------------------------------------
-- Artist verification workflow: admin summary cards, the Orders unread badge,
-- and pending BDC Marketplace registrations.
--
-- Run this against an existing installation:
--   mysql -u root -p bdcmusic < database/migrations/2026_10_artist_verification.sql
--
-- database/bdcmusic.sql already contains all of it, so a fresh install does not
-- need this file. It exists because a dump is not applied to a database that is
-- already in use, and every statement below is written so that running it twice
-- changes nothing the second time.
-- ---------------------------------------------------------------------------

-- 1. Orders the admin has not opened yet.
--
-- MySQL has no "ADD COLUMN IF NOT EXISTS", so the information_schema check is
-- what makes this repeatable. admin_read_at stays NULL for every existing order,
-- which makes all of them unread: they have not been looked at through the
-- current admin UI yet.
SET @has_admin_read_at := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
	AND TABLE_NAME = 'bookings'
	AND COLUMN_NAME = 'admin_read_at'
);

SET @add_admin_read_at := IF(
  @has_admin_read_at = 0,
  'ALTER TABLE `bookings` ADD COLUMN `admin_read_at` datetime DEFAULT NULL AFTER `paid_at`',
  'SELECT 1'
);

PREPARE stmt FROM @add_admin_read_at;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_admin_read_idx := (
  SELECT COUNT(*)
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
	AND TABLE_NAME = 'bookings'
	AND INDEX_NAME = 'idx_admin_read'
);

SET @add_admin_read_idx := IF(
  @has_admin_read_idx = 0,
  'ALTER TABLE `bookings` ADD KEY `idx_admin_read` (`admin_read_at`)',
  'SELECT 1'
);

PREPARE stmt FROM @add_admin_read_idx;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. Marketplace registrations.
SET @has_artist_registrations := (
  SELECT COUNT(*)
  FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE()
	AND TABLE_NAME = 'artist_registrations'
);

SET @create_artist_registrations := IF(
  @has_artist_registrations = 0,
  'CREATE TABLE IF NOT EXISTS `artist_registrations` (
	 `id` int NOT NULL AUTO_INCREMENT,
	 `booking_id` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
	 `customer_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
	 `artist_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
	 `artist_email` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
	 `artist_phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
	 `artist_code` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
	 `category_slug` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
	 `experience` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
	 `city` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
	 `goal` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
	 `about` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
	 `portfolio_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
	 `plan_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
	 `plan_price` decimal(10,2) NOT NULL DEFAULT ''0.00'',
	 `currency` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''INR'',
	 `details` json DEFAULT NULL,
	 `status` enum(''pending'',''verified'',''rejected'') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''pending'',
	 `review_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
	 `reviewed_by` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
	 `reviewed_at` datetime DEFAULT NULL,
	 `id_card_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
	 `certificate_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
	 `documents_generated_at` datetime DEFAULT NULL,
	 `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	 `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	 PRIMARY KEY (`id`),
	 UNIQUE KEY `uq_registration_booking` (`booking_id`),
	 UNIQUE KEY `uq_registration_code` (`artist_code`),
	 KEY `idx_registration_customer` (`customer_id`),
	 KEY `idx_registration_status` (`status`),
	 KEY `idx_registration_created` (`created_at`)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
  'SELECT 1'
);

PREPARE stmt FROM @create_artist_registrations;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. Backfill: marketplace orders that have already been paid for are pending
--    registrations today, and without this the studio opens the verification
--    queue to an empty list while paid orders sit in the orders table.
--
--    Only paid orders are backfilled. A cancelled or unpaid order was never
--    completed, so there is nothing to verify, and the NOT EXISTS guard keeps
--    the insert idempotent now that the table exists.
--
--    The column names here are the marketplace form's meta keys as declared in
--    includes/booking-registry.php, and b.meta is copied into details so a field
--    this script does not denormalise is still available to the admin review.
INSERT INTO `artist_registrations` (
  booking_id, customer_id, artist_name, artist_email, artist_phone,
  category_slug, experience, city, goal, about, portfolio_url,
  plan_name, plan_price, currency, details, status
)
SELECT
  b.booking_id,
  b.customer_id,
  NULLIF(b.customer_name, ''),
  b.customer_email,
  NULLIF(b.customer_phone, ''),
  NULLIF(JSON_UNQUOTE(JSON_EXTRACT(b.meta, '$.artist_category')), ''),
  NULLIF(JSON_UNQUOTE(JSON_EXTRACT(b.meta, '$.experience')), ''),
  NULLIF(JSON_UNQUOTE(JSON_EXTRACT(b.meta, '$.event_location')), ''),
  NULLIF(JSON_UNQUOTE(JSON_EXTRACT(b.meta, '$.artist_goal')), ''),
  NULLIF(JSON_UNQUOTE(JSON_EXTRACT(b.meta, '$.about')), ''),
  NULLIF(JSON_UNQUOTE(JSON_EXTRACT(b.meta, '$.portfolio')), ''),
  b.plan_name,
  b.price,
  b.currency,
  b.meta,
  'pending'
FROM bookings b
WHERE b.service_slug = 'artists-marketplace'
  AND b.payment_status = 'paid'
  AND NULLIF(b.customer_name, '') IS NOT NULL
  AND NOT EXISTS (
	SELECT 1 FROM `artist_registrations` r WHERE r.booking_id = b.booking_id
  );
