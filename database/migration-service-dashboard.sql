-- ============================================================================
-- BDC Music Studio
-- Migration: Service-based Customer Dashboard + Admin-managed service data
-- Run this against the existing `bdcmusic` database AFTER database/bdcmusic.sql
-- ============================================================================
--
-- WHAT THIS ADDS
--   1. release_tracks          - track list per release (albums hold many tracks)
--   2. release_platform_links  - admin-managed platform names + live links per release
--   3. service_records         - admin-managed per-order service arrangements for the
--                                 five non-distribution services (assigned artist,
--                                 class schedule, delivery link, campaign details,
--                                 IPRS registration number, notes, progress)
--
-- WHAT THIS DOES NOT TOUCH
--   - bookings, services, users, releases, release_artists, release_history,
--     uploaded_files, artists, artist_pricing, artist_categories, artist_enquiries
--     are all left exactly as they are.
--   - The unlock rule reuses the existing bookings.status enum
--     ('pending','processing','hold','delivered','cancelled'). No new status value
--     is introduced, so no ALTER is required.
--
-- SAFE TO RE-RUN. Every statement is idempotent: the tables are created with
-- IF NOT EXISTS, each table has the natural unique key its seed data needs
-- (release_tracks: one row per release+track number; release_platform_links:
-- one row per release+platform; service_records: one row per booking), so the
-- seed INSERT ... ON DUPLICATE KEY UPDATE blocks below update in place instead
-- of piling up duplicates.
-- ============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET NAMES utf8mb4;

-- ----------------------------------------------------------------------------
-- 1. release_tracks
--    One row per track. `releases.type` already distinguishes
--    'single' | 'ep' | 'album', so a single just gets one row and an album gets
--    as many rows as it has tracks.
--    (release_id, track_no) is UNIQUE, which both enforces sane track ordering
--    and lets admin edits upsert a track by position.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `release_tracks` (
  `id`         int NOT NULL AUTO_INCREMENT,
  `release_id` int NOT NULL,
  `track_no`   int NOT NULL DEFAULT '1',
  `title`      varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `isrc`       varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration`   varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `audio_file` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_rt_release_no` (`release_id`, `track_no`),
  CONSTRAINT `fk_rt_release` FOREIGN KEY (`release_id`) REFERENCES `releases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2. release_platform_links
--    Platform name + live URL. Fully admin-managed from
--    bdc-admin/releases.php. `is_active` lets admin hide a platform without
--    deleting the row, and `sort_order` controls display order.
--    (release_id, platform) is UNIQUE so a platform is only listed once per
--    release, and re-saving a platform updates the existing link.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `release_platform_links` (
  `id`         int NOT NULL AUTO_INCREMENT,
  `release_id` int NOT NULL,
  `platform`   varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `url`        varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active`  tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_rpl_release_platform` (`release_id`, `platform`),
  CONSTRAINT `fk_rpl_release` FOREIGN KEY (`release_id`) REFERENCES `releases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3. service_records
--    Admin-managed arrangement for a single booking (order). One row per
--    booking. `booking_id` is UNIQUE so the admin form is a simple upsert.
--
--    The columns are deliberately generic (headline / sub_headline / progress /
--    starts_on / ends_on / location / notes) because the same three to four
--    fields mean different things per service. The per-service meaning lives in
--    includes/service-fields.php, e.g. for Online/Offline Classes:
--        headline     -> "Batch & Faculty"
--        sub_headline -> "Level"
--        location     -> "Class Link / Venue"
--        starts_on    -> "Batch Start"
--    and for IPRS Services:
--        headline     -> "Registration Number"
--        sub_headline -> "Membership Type"
--        location     -> "Registered Work / Link"
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `service_records` (
  `id`           int NOT NULL AUTO_INCREMENT,
  `booking_id`   varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_id`   int NOT NULL,
  `headline`     varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_headline` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `progress`     varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Confirmed',
  `starts_on`    date DEFAULT NULL,
  `ends_on`      date DEFAULT NULL,
  `location`     varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes`        text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at`   datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sr_booking` (`booking_id`),
  KEY `idx_sr_service` (`service_id`),
  CONSTRAINT `fk_sr_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sr_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Optional starter data.
-- Seeded so the new admin forms and the new customer sections have something to
-- render immediately. Comment this block out if you would rather add records
-- from bdc-admin/orders.php instead.
-- ----------------------------------------------------------------------------

-- Tracks for the two multi-track releases already in the dump.
--   releases.id 2 = "Echoes of Soul" (album, 6 tracks)
--   releases.id 4 = "Raatein" (ep, 4 tracks)
INSERT INTO `release_tracks` (`release_id`, `track_no`, `title`, `isrc`, `duration`) VALUES
  (2, 1, 'Echoes of Soul',                 'IN-R5S-23-10001', '4:12'),
  (2, 2, 'Midnight Reverb',                'IN-R5S-23-10002', '3:48'),
  (2, 3, 'Paper Lanterns',                 'IN-R5S-23-10003', '4:35'),
  (2, 4, 'Static Hearts',                  'IN-R5S-23-10004', '3:27'),
  (2, 5, 'Long Way Home',                  'IN-R5S-23-10005', '5:04'),
  (2, 6, 'Echoes of Soul (Reprise)',       'IN-R5S-23-10006', '4:12'),
  (4, 1, 'Raatein',                        'IN-R5S-23-00002', '3:41'),
  (4, 2, 'Neon Katha',                     'IN-R5S-23-00003', '4:02'),
  (4, 3, 'Beparwah',                       'IN-R5S-23-00004', '3:19'),
  (4, 4, 'Raatein (Acoustic)',             'IN-R5S-23-00005', '3:44')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

-- Live links for releases.id 1 ("Dil Ki Awaaz"), which is already 'live'.
INSERT INTO `release_platform_links` (`release_id`, `platform`, `url`, `is_active`, `sort_order`) VALUES
  (1, 'Spotify',        'https://open.spotify.com/track/example1', 1, 1),
  (1, 'Apple Music',    'https://music.apple.com/in/artist/example', 1, 2),
  (1, 'YouTube Music',  'https://music.youtube.com/watch?v=example1', 1, 3),
  (1, 'Amazon Music',   'https://music.amazon.com/albums/example1', 1, 4),
  (1, 'JioSaavn',       'https://www.jiosaavn.com/song/example1', 1, 5),
  (1, 'Gaana',          'https://gaana.com/song/example1', 1, 6)
ON DUPLICATE KEY UPDATE `url` = VALUES(`url`);

-- Arrangements for a few of the seeded bookings.
--   BDCM-A1B2C3 = service 1, delivered  (Rahul Sharma)
--   BDCM-E5F6G7 = service 2, processing (Priya Patel)
--   BDCM-Q7R8S9 = service 3, delivered  (Vikram Singh)
--   BDCM-PROMO0 = service 5, pending    (Neha Gupta)
--   BDCM-172421 = service 6, pending    (Rahul Sharma, IPRS)
INSERT INTO `service_records`
  (`booking_id`, `service_id`, `headline`, `sub_headline`, `progress`, `starts_on`, `ends_on`, `location`, `notes`) VALUES
  ('BDCM-A1B2C3', 1, 'Abhinav Singh', 'Solo Vocalist - Live Show', 'Completed', '2026-08-16', '2026-08-16', 'Kanpur, Uttar Pradesh', 'Setlist confirmed. Backline provided by the artist.'),
  ('BDCM-E5F6G7', 2, 'Music Video - Cinematic Cut', 'Full Production Package', 'In Production', '2026-09-02', '2026-09-22', '', 'Shoot scheduled in Delhi. 2 days on location.'),
  ('BDCM-Q7R8S9', 3, 'Batch 2026-04 - Abhinav Singh', 'Intermediate', 'Completed', '2026-07-21', '2026-08-18', 'https://meet.google.com/example-class', '8 sessions completed. All recordings shared.'),
  ('BDCM-PROMO0', 5, 'Single Launch Campaign', 'Social Media', 'Brief Received', NULL, NULL, '', 'Waiting on the final master to start the campaign.'),
  ('BDCM-172421', 6, '', 'Author-Composer', 'Application Received', '2026-09-11', NULL, '', 'PAN and address proof received. IPRS submission pending.')
ON DUPLICATE KEY UPDATE `headline` = VALUES(`headline`);
