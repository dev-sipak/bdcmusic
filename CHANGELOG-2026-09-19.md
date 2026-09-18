# Changelog — 2026-09-19

## Environment Configuration (.env)

### New Files
- `.env` — Environment variables: `BASE_URL`, `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_PORT`, `APP_NAME`, `APP_ENV`, `APP_DEBUG`
- `.env.example` — Template for version control

### Modified Files
- `includes/config.php` — Rewrote to load from `.env` file; added `env()` helper; defines `APP_BASE_URL` constant
- `includes/database.php` — Removed hardcoded DB constants; uses `defined()` fallbacks from `config.php`
- `includes/helpers.php` — `url()` and `asset()` now use `APP_BASE_URL` constant as fallback
- `includes/components/policy-checkbox.php` — Added null-safe fallback for `$siteUrl`
- `includes/auth.php` — Load order fixed: `config.php` before `database.php`

## .htaccess Fix

### Modified Files
- `.htaccess` — Removed 301 redirect rule that stripped `.php` from URLs (caused POST→GET conversion breaking AJAX login). Kept clean URL rewrite rule only.

## Login / Logout Redirect Fixes

### Modified Files
- `includes/logout.php` — Fixed redirect from relative `login.php` (resolved to `includes/login.php`) to absolute `$basePath . 'login'`
- `bdc-admin/index.php` — Removed `.php` from login redirect URL
- `dashboard/customer-dashboard.php` — Removed `.php` from login redirect URL

## Admin Dashboard — Live Data from DB

### Modified Files
- `bdc-admin/index.php` — Replaced hardcoded `$adminOrders` array with `SELECT` query from `bookings` table. Replaced hardcoded `$services` array with `SELECT DISTINCT service FROM bookings`. Added `database.php` include.

## Customer Dashboard — Live Data from DB

### Modified Files
- `dashboard/customer-dashboard.php` — Replaced hardcoded `$mockOrders` with DB query filtered by `customer_id = $_SESSION['user_id']`. Profile phone and member-since now queried from `users` table. Added `database.php` include.

## SCSS Recompiled
- `assets/dist/main.css` — Recompiled via `npm run build`
