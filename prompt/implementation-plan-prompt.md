
---

## Scope Summary

| # | Feature | Complexity | Files Touched |
|---|---------|------------|---------------|
| 1 | Artist Menu (frontend, dynamic from DB) | Medium | `header.php` |
| 2 | Artist Management (admin CRUD) | High | `bdc-admin/index.php`, new endpoints, new JS |
| 3 | Database tables (normalized) | Medium | `database/bdcmusic.sql` |
| 4 | Artist images (WebP, 250×360) | Medium | New upload endpoint |
| 5 | Frontend artist pages | High | New PHP pages, SCSS |
| 6 | Digital Music Distribution dashboard | High | Customer dashboard, new tables, new endpoints |
| 7 | Misc: rupee icon, alphabetical dropdown | Low | JS files, PHP files |

---

## Phase 1: Database — 5 New Tables

### `artist_categories`
| Column | Type | Notes |
|--------|------|-------|
| `id` | `int AUTO_INCREMENT` | PK |
| `name` | `varchar(100) NOT NULL` | UNIQUE |
| `slug` | `varchar(100) NOT NULL` | UNIQUE |
| `sort_order` | `int DEFAULT 0` | Frontend ordering |
| `is_active` | `tinyint(1) DEFAULT 1` | |
| `created_at` | `datetime DEFAULT CURRENT_TIMESTAMP` | |

### `artists`
| Column | Type | Notes |
|--------|------|-------|
| `id` | `int AUTO_INCREMENT` | PK |
| `category_id` | `int NOT NULL` | FK → `artist_categories.id` |
| `name` | `varchar(200) NOT NULL` | |
| `slug` | `varchar(200) NOT NULL` | UNIQUE |
| `image` | `varchar(500)` | Nullable, relative path to WebP |
| `location` | `varchar(200)` | Nullable |
| `bio` | `text` | Nullable |
| `is_active` | `tinyint(1) DEFAULT 1` | Frontend visibility |
| `created_at` | `datetime` | |
| `updated_at` | `datetime ON UPDATE CURRENT_TIMESTAMP` | |

### `artist_pricing`
| Column | Type | Notes |
|--------|------|-------|
| `id` | `int AUTO_INCREMENT` | PK |
| `artist_id` | `int NOT NULL` | FK → `artists.id ON DELETE CASCADE` |
| `service_type` | `varchar(100) NOT NULL` | e.g. "Each Video", "Each Reel" |
| `price` | `decimal(10,2) NOT NULL` | |
| `sort_order` | `int DEFAULT 0` | |
| `created_at` | `datetime DEFAULT CURRENT_TIMESTAMP` | |

### `releases`
| Column | Type | Notes |
|--------|------|-------|
| `id` | `int AUTO_INCREMENT` | PK |
| `customer_id` | `varchar(20)` | FK → `users.id` |
| `booking_id` | `varchar(40)` | FK → `bookings.booking_id` |
| `title` | `varchar(200) NOT NULL` | Song/album title |
| `type` | `enum('single','ep','album')` | Default `single` |
| `artwork_path` | `varchar(500)` | Album art image |
| `isrc` | `varchar(20)` | Nullable |
| `upc` | `varchar(20)` | Nullable, assigned later |
| `go_live_date` | `date` | Nullable |
| `status` | `enum('draft','pending','verification','onhold','rejected','approved','live','takedown')` | Default `draft` |
| `lyrics` | `text` | Nullable |
| `dolby` | `tinyint(1) DEFAULT 0` | |
| `apple_itunes` | `tinyint(1) DEFAULT 0` | |
| `created_at` | `datetime` | |
| `updated_at` | `datetime ON UPDATE CURRENT_TIMESTAMP` | |

### `release_artists`
| Column | Type | Notes |
|--------|------|-------|
| `id` | `int AUTO_INCREMENT` | PK |
| `release_id` | `int NOT NULL` | FK → `releases.id ON DELETE CASCADE` |
| `role` | `varchar(50) NOT NULL` | `singer`, `composer`, `lyricist`, `producer` |
| `name` | `varchar(200) NOT NULL` | Display name |
| `created_at` | `datetime DEFAULT CURRENT_TIMESTAMP` | |

### `release_history`
| Column | Type | Notes |
|--------|------|-------|
| `id` | `int AUTO_INCREMENT` | PK |
| `release_id` | `int NOT NULL` | FK → `releases.id ON DELETE CASCADE` |
| `action` | `varchar(100) NOT NULL` | e.g. "Initial Submission", "Rejected" |
| `message` | `text` | Nullable |
| `reviewer_note` | `text` | Nullable |
| `created_at` | `datetime DEFAULT CURRENT_TIMESTAMP` | |

---

## Phase 2: Artist Image Upload

### New endpoint: `includes/upload-artist-image.php`
- **Auth:** Admin session required
- **Validation:** MIME (`finfo`), max 2MB, JPEG/PNG/WebP
- **Processing:** Convert to WebP, resize/crop to 250×360px, filename `artist-{uniqid}.webp`
- **Storage:** `assets/images/artist/`
- **Cleanup:** Delete old image on update
- **Response:** `{ success: true, url: "..." }`
- Add `imageResizeAndConvert()` helper to `includes/helpers.php`

---

## Phase 3: Admin — Artist Management

### New tab in `bdc-admin/index.php`
- 4th nav button `[data-tab="artists"]` → `adm-tab-artists`
- Table: Image thumbnail, Name, Category, Location, Status, Actions
- "Add New Artist" → modal with dynamic pricing rows
- Pagination (15 per page)

### New PHP endpoints
| Endpoint | Method | Purpose |
|----------|--------|---------|
| `includes/admin/artists-list.php` | GET | Fetch all artists |
| `includes/admin/artist-save.php` | POST | Create/update artist + pricing |
| `includes/admin/artist-delete.php` | POST | Soft-delete |
| `includes/admin/categories-list.php` | GET | Fetch categories |

### New JS: `assets/js/admin-artists.js`

---

## Phase 4: Frontend — Artist Menu + Pages

### Dynamic Artist submenu in `header.php`
- Fetch active categories from DB
- Render as dropdown (same pattern as Services)
- Active state when `$currentPage` starts with `artists/`

### Reusable template: `services/artist-category.php`
- URL: `/artists/{category-slug}`
- Query categories + artists + pricing
- Responsive card grid

### Detail page: `services/artist-detail.php`
- URL: `/artists/{category-slug}/{artist-slug}`
- Hero: Image, Name, Category, Location, Bio
- Pricing table
- CTA: "Book Now" → artists-marketplace with pre-selected artist

### SCSS: `assets/scss/pages/_artists.scss`

---

## Phase 5: Digital Music Distribution Dashboard

### Conditional menu access
- Check if customer has booking with `service_id = 4` AND `status IN ('processing','delivered')`

### New menu items (from screenshots)
- **My Releases** — list with status tabs
- **Create New Release** — submission form

### Release detail (from screenshots)
- Header: Album art, Title, Artist credits, Type, ISRC, Go Live Date, UPC
- Tabs: Live Links, Release Info, History

### New PHP endpoints
| Endpoint | Method | Purpose |
|----------|--------|---------|
| `includes/customer/releases-list.php` | GET | Fetch releases |
| `includes/customer/release-save.php` | POST | Create/update release |
| `includes/customer/release-submit.php` | POST | Submit for review |
| `includes/customer/release-takedown.php` | POST | Request takedown |

---

## Phase 6: Quick Fixes

### Rupee icon on amounts
7 edits across 3 JS files:
- `assets/js/customer-dashboard.js` (lines 35, 101)
- `assets/js/admin-dashboard.js` (lines 32, 72, 142)
- `assets/js/shared.js` (lines 65, 81)

### Alphabetical dropdown
- Sort category `<select>` in `services/bdc-artists-marketplace.php` alphabetically

### Order ID format
- `BDCM-xxxxxx` format already in `includes/helpers.php:103-104`
- Verify consistent usage

### Shipped → Hold
5 edits across 4 PHP files + 1 JS file

### Header: Sign In → My Profile
- `header.php`: Customer sees "My Profile", Admin sees "Admin Panel"

---

## New Files

| File | Purpose |
|------|---------|
| `database/migration-artists.sql` | New tables + seed data |
| `includes/upload-artist-image.php` | Artist image upload endpoint |
| `includes/admin/artists-list.php` | GET artists API |
| `includes/admin/artist-save.php` | POST save artist |
| `includes/admin/artist-delete.php` | POST delete artist |
| `includes/admin/categories-list.php` | GET categories API |
| `includes/customer/releases-list.php` | GET releases API |
| `includes/customer/release-save.php` | POST save release |
| `includes/customer/release-submit.php` | POST submit release |
| `includes/customer/release-takedown.php` | POST takedown |
| `assets/js/admin-artists.js` | Admin artists tab logic |
| `assets/js/customer-releases.js` | Customer releases tab logic |
| `assets/scss/pages/_artists.scss` | Frontend artist page styles |
| `services/artist-category.php` | Frontend artist listing |
| `services/artist-detail.php` | Frontend artist detail |

## Modified Files

| File | Changes |
|------|---------|
| `database/bdcmusic.sql` | Add new tables |
| `includes/helpers.php` | Add image resize helper |
| `header.php` | Add Artists dropdown |
| `bdc-admin/index.php` | Add Artists tab |
| `bdc-admin/includes/admin-footer.php` | Load admin-artists.js |
| `dashboard/customer-dashboard.php` | Add conditional Distribution menu |
| `assets/js/customer-dashboard.js` | Add releases tab, rupee icon |
| `assets/js/admin-dashboard.js` | Rupee icon, status enum |
| `assets/js/shared.js` | Rupee icon in shared row builders |
| `services/bdc-artists-marketplace.php` | Alphabetical category dropdown |
| `assets/scss/pages/_admin.scss` | Admin artists tab styles |

## SQL Migration

```sql
-- Migration: Artist Management + Digital Music Distribution
-- Phase 1: Database Schema

CREATE TABLE `artist_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ac_name` (`name`),
  UNIQUE KEY `uq_ac_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `artists` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_artist_slug` (`slug`),
  KEY `idx_artist_category` (`category_id`),
  CONSTRAINT `fk_artist_category` FOREIGN KEY (`category_id`) REFERENCES `artist_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `artist_pricing` (
  `id` int NOT NULL AUTO_INCREMENT,
  `artist_id` int NOT NULL,
  `service_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sort_order` int NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pricing_artist` (`artist_id`),
  CONSTRAINT `fk_pricing_artist` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `releases` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `booking_id` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('single','ep','album') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'single',
  `artwork_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isrc` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `upc` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `go_live_date` date DEFAULT NULL,
  `status` enum('draft','pending','verification','onhold','rejected','approved','live','takedown') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `lyrics` text COLLATE utf8mb4_unicode_ci,
  `dolby` tinyint(1) NOT NULL DEFAULT 0,
  `apple_itunes` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_release_customer` (`customer_id`),
  KEY `idx_release_booking` (`booking_id`),
  KEY `idx_release_status` (`status`),
  CONSTRAINT `fk_release_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_release_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `release_artists` (
  `id` int NOT NULL AUTO_INCREMENT,
  `release_id` int NOT NULL,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ra_release` (`release_id`),
  CONSTRAINT `fk_ra_release` FOREIGN KEY (`release_id`) REFERENCES `releases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `release_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `release_id` int NOT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `reviewer_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rh_release` (`release_id`),
  CONSTRAINT `fk_rh_release` FOREIGN KEY (`release_id`) REFERENCES `releases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `artist_categories` (`name`, `slug`, `sort_order`) VALUES
('Actor', 'actor', 1),
('Actress', 'actress', 2),
('Composer', 'composer', 3),
('Dancer', 'dancer', 4),
('Director', 'director', 5),
('Instrument Player', 'instrument-player', 6),
('Music Band', 'music-band', 7),
('Music Producer', 'music-producer', 8),
('Producer', 'producer', 9),
('Reels Stars', 'reels-stars', 10),
('Singer', 'singer', 11),
('Writer', 'writer', 12);

ALTER TABLE `bookings`
  MODIFY COLUMN `status` enum('pending','processing','hold','delivered','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending';

UPDATE `bookings` SET `status` = 'hold' WHERE `status` = 'shipped';