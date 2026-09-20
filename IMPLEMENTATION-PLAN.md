# BDC Music - Implementation Plan
> Generated from full technical audit on 2026-09-19

---

## Architecture Overview

Procedural PHP with page-based template includes (no MVC, no framework, no Composer). MySQL via PDO with 4 tables (users, services, bookings, uploaded_files). Vanilla JS frontend with Swiper.js, Webpack SCSS build. Session-based auth with two roles (customer/admin). Razorpay payments. Hybrid data storage (MySQL + JSON flat files).

---

## CRITICAL Issues (Fix Immediately)

### 1. Production DB Credentials Committed to Git
**File:** `.env.production` + `.gitignore` line 8 (`!.env.production`)

The production database password (`Bdcmusic321`) is in the repo and pushed to GitHub. **Rotate this password immediately**, remove `.env.production` from Git history, and fix `.gitignore` to not un-ignore it.

### 2. CSRF Protection Never Used
**File:** `includes/helpers.php:80-98`

Well-implemented CSRF helpers (`generate_csrf_token`, `verify_csrf_token`, `csrf_field`) exist but are **never called anywhere**. All POST handlers are vulnerable:
- `includes/auth.php` (login/signup)
- `includes/change-password.php`
- `includes/update-profile.php`
- `includes/upload-profile-pic.php`
- All service booking forms

**Impact:** An attacker can silently change passwords, update profiles, or submit bookings on behalf of authenticated users.

### 3. Session Role Bug - Signup Breaks Auth
**File:** `includes/auth.php:139` vs `customer-dashboard.php:6`

After signup, `$_SESSION['user_role'] = 1` (integer), but dashboard checks compare against strings (`'customer'`, `'admin'`). A newly signed-up user **will always be redirected away from the dashboard**.

### 4. Dual Storage System - Bookings Lost
`razorpay-verify.php` writes bookings to **JSON flat files** (`data/bookings.json`), but `customer-dashboard.php` reads only from **MySQL**. Bookings created through the payment flow never appear in the dashboard.

### 5. No Admin Status Update Endpoint
`admin-dashboard.js:180-189` modifies order status **only in the local JS array** -- no AJAX call is made to persist the change. The status update is purely visual and lost on refresh. No PHP endpoint exists for this.

---

## HIGH Issues (Fix Soon)

### 6. XSS via innerHTML (22 instances)
**Files:** `shared.js:47-84`, `customer-dashboard.js:30-38,54-64`, `admin-dashboard.js:26-34,110-118,148-163`

All dashboard rendering injects server data (`o.id`, `o.customer`, `o.service`, `o.status`, `u.original_name`) directly into HTML strings with **zero escaping**. User-uploaded filenames (`original_name`) are the highest stored XSS risk.

### 7. Unescaped `$basePath` in JS String Literals
**Files:** `customer-dashboard.php:318`, `bdc-admin/index.php:295`, `login.php:94-95`

`$basePath` is output without `htmlspecialchars()` inside JS single-quoted strings. Should use `json_encode()` for JS contexts.

### 8. No Authentication on File Download
**File:** `includes/download-file.php`

The download endpoint calls `session_start()` but **never checks if the user is logged in** or owns the file. Any visitor can download any file under `data/uploads/`.

### 9. No Brute Force Protection
**File:** `includes/auth.php`

No account lockout, progressive delay, or CAPTCHA. Unlimited login attempts are possible.

### 10. Weak Auto-Generated Passwords
**File:** `includes/razorpay-verify.php:101,210`

`substr(md5($email . time()), 0, 12)` -- MD5 is cryptographically weak and the entropy is predictable (attacker knows approximate creation time). Use `random_bytes()` instead.

### 11. Plaintext Passwords in Database
**File:** `bookings.auto_password` column, `razorpay-verify.php:101`

Temporary passwords are stored in **plaintext** in the database after being sent to the user. Should be hashed immediately or not persisted.

### 12. MD5-Based Customer ID - Collision Risk
**File:** `includes/auth.php:116`

`CUST-` + `substr(md5(microtime()), 0, 8)` -- only 32 bits of entropy. Two users signing up at the same millisecond could get the same ID.

### 13. No CSRF Token in Auth Requests
**File:** `assets/js/login.js:112`

Login/signup AJAX sends no CSRF token, allowing cross-site request forgery on authentication.

### 14. Server Redirect URL Used Without Validation
**File:** `assets/js/login.js:124`

`window.location.href = data.redirect` -- the redirect URL from server response is used directly. If the response were compromised, this could redirect to a malicious URL.

### 15. CDN Resources Lack SRI Hashes
**Files:** `header.php:34-35`, `footer.php:54`

Font Awesome and Swiper.js from CDNs have no `integrity` attributes. A CDN compromise could inject malicious code.

### 16. Massive Code Duplication in razorpay-verify.php
**File:** `includes/razorpay-verify.php`

~100 lines duplicated between demo mode (lines 42-146) and production mode (lines 154-237). Fixes must be applied twice.

---

## MEDIUM Issues (Improve)

### 17. No Rate Limiting on Any Endpoint
All POST handlers accept unlimited requests -- vulnerable to spam, abuse, and brute force.

### 18. No Error Logging
All `catch` blocks silently discard exceptions (`customer-dashboard.php:42-44`, `bdc-admin/index.php:38-41`, `upload-profile-pic.php:72-74`). Production errors are invisible.

### 19. No Security Headers
No CSP, X-Frame-Options, X-Content-Type-Options, or SameSite cookie attributes. Admin panel is vulnerable to clickjacking.

### 20. No Session Timeout
Sessions never expire. An abandoned browser remains logged in indefinitely.

### 21. Duplicate Sanitization Functions
`sanitizeBookingValue()`, `sanitizeDistributionValue()`, `sanitizeIprsValue()` are identical to `sanitize_input()` in `helpers.php`.

### 22. Duplicate JSON Read/Write Pattern
The same JSON file read/append/write block is repeated across `razorpay-verify.php`, `digital-music-distribution.php`, and `iprs-services.php`. Should be extracted to a helper.

### 23. No Server-Side Pagination
Both admin and customer dashboards load **ALL** records via `fetchAll()`. With large datasets, this consumes excessive memory.

### 24. No Transactions in Payment Flow
`razorpay-verify.php` makes multiple `file_put_contents()` calls with no atomicity guarantee. A crash between writes leaves inconsistent data.

### 25. `password_hash` Allows NULL
The `users` table allows `password_hash` to be NULL, enabling passwordless accounts with no OAuth system to justify it.

### 26. Double `session_start()` Call
**File:** `services/audio-video-services.php:53` -- `session_start()` is called after `header.php` already starts the session.

### 27. Role Type Mismatch in Auth Signup
**File:** `includes/auth.php:139` -- `$_SESSION['user_role'] = 1` (integer) but login at line 66 sets `$user['role']` (string). Inconsistent.

### 28. No `response.ok` Check on Fetch Calls
**Files:** `login.js:116`, `customer-dashboard.js:184,246,313`

HTTP error responses are silently treated as success.

### 29. Excessive `will-change` Declarations
**Files:** `_global.scss:82-104`, `_typography.scss:52-66`

12+ elements use `will-change` simultaneously, defeating its purpose and increasing GPU memory consumption.

### 30. Inconsistent Responsive Breakpoints
Some files use the `respond()` mixin, others use raw `@media` with non-standard values (`1270px`, `1100px`, `700px` in `_service.scss`).

---

## LOW Issues (Polish)

| # | Issue | Location |
|---|-------|----------|
| 31 | Logout via GET request (CSRF logout risk) | `logout.php`, `header.php:91` |
| 32 | `@` operator suppressing mail errors | `razorpay-verify.php:130` |
| 33 | Weak password complexity (min 6, no requirements) | `auth.php:102`, `change-password.php:28` |
| 34 | Email uniqueness race condition (check-then-insert) | `auth.php:108-113` |
| 35 | Razorpay demo fallback in production | `razorpay-verify.php:29-30` |
| 36 | Content-Disposition header injection | `download-file.php:35` |
| 37 | Global namespace pollution in shared.js/booking.js | Multiple JS files |
| 38 | Hardcoded prices in JS | `booking.js:17`, `online-offline-classes-form.js:15` |
| 39 | Duplicate SCSS rules (_cards.scss vs _global.scss) | `_cards.scss:130-163` |
| 40 | Unused `$space-*` SCSS variables | `_variables.scss:66-70` |
| 41 | Hardcoded color hex values in page SCSS | `_cards.scss:265`, `_global.scss:57,67` |
| 42 | 117.5 KB compiled CSS with no purging | `assets/css/main.css` |
| 43 | Razorpay script loaded eagerly on page load | `audio-video-services.php:365` |
| 44 | `e()` helper defined but never used | `helpers.php:123` |
| 45 | Dead `item` column alias in queries | `customer-dashboard.php:26` |
| 46 | Playwright in devDependencies but no tests exist | `package.json` |
| 47 | Seed data all use password "password" | `bdcmusic.sql` |

---

## What's Done Well

| Area | Detail |
|------|--------|
| **SQL Injection** | Zero vulnerabilities. All queries use PDO prepared statements with `EMULATE_PREPARES => false` |
| **XSS Escaping** | Most template output uses `htmlspecialchars(ENT_QUOTES, 'UTF-8')` |
| **File Upload Security** | MIME validation via `finfo`, filename sanitization, size limits, per-service whitelists |
| **File Download** | Directory traversal prevention via `realpath()` comparison |
| **Session Fixation** | `session_regenerate_id(true)` on login |
| **Password Hashing** | bcrypt via `password_hash(PASSWORD_BCRYPT)` |
| **Error Messages** | Generic messages that don't leak internal details |
| **SCSS Architecture** | Clean 7-1 pattern with design tokens, mixins, and CSS custom properties |
| **Database Schema** | Well-normalized with proper foreign keys, indexes, and JSON meta for flexibility |
| **No N+1 Queries** | All dashboards batch-fetch data efficiently |
| **Input Trimming** | Consistent `trim()` on all user inputs |
| **Email Validation** | `filter_var(FILTER_VALIDATE_EMAIL)` used throughout |
| **Admin SEO** | `noindex, nofollow` on admin pages |
| **Reusable Components** | FAQ, breadcrumb, policy-checkbox PHP components |

---

## Phased Implementation Plan

### Phase 1: Emergency - Rotate Credentials + Critical Auth Bugs
**Issues:** #1, #3, #26 | **Effort:** ~2h | **Risk:** Low | **Dependencies:** None

- [ ] Rotate production database password
- [ ] Remove `.env.production` from Git history (`git filter-branch` or BFG)
- [ ] Fix `.gitignore` to not un-ignore `.env.production`
- [ ] Fix `includes/auth.php:139` - change `$_SESSION['user_role'] = 1` to `'customer'`
- [ ] Fix `services/audio-video-services.php:53` - remove duplicate `session_start()`

**Verification:** Login/signup works with string roles; `.env.production` removed from history.

---

### Phase 2: CSRF + Authentication Hardening
**Issues:** #2, #13, #9, #14, #27, #20, #33, #34, #31 | **Effort:** ~5h | **Risk:** Medium | **Dependencies:** Phase 1

- [ ] Add `csrf_field()` to all forms in PHP templates
- [ ] Add `verify_csrf_token()` check in all POST handlers (`auth.php`, `change-password.php`, `update-profile.php`, `upload-profile-pic.php`)
- [ ] Append CSRF token to `login.js` FormData
- [ ] Add brute force protection in `auth.php` (session-based attempt tracking with lockout)
- [ ] Validate redirect URL is same-origin in `auth.php`
- [ ] Add password complexity requirements (uppercase, lowercase, number, min 8 chars)
- [ ] Fix email uniqueness race condition (catch UNIQUE constraint exception)
- [ ] Convert logout to POST-only (`logout.php`, `header.php:91`, dashboard JS files)
- [ ] Add session timeout configuration in `config.php`

**Within-phase:** #2 before #13; #3 before #27

---

### Phase 3: Data Architecture Unification
**Issues:** #4, #16, #22, #21, #24 | **Effort:** ~7h | **Risk:** **High** | **Dependencies:** Phase 1-2

- [ ] Write one-time migration script to import existing JSON bookings into MySQL
- [ ] Rewrite `razorpay-verify.php` to use PDO for all booking/customer inserts
- [ ] Remove all JSON file operations from `razorpay-verify.php`
- [ ] Deduplicate demo/production mode code in `razorpay-verify.php`
- [ ] Extract JSON read/write pattern to helper if still needed elsewhere
- [ ] Remove duplicate `sanitizeBookingValue()`, `sanitizeDistributionValue()`, `sanitizeIprsValue()` - use `sanitize_input()` from `helpers.php`
- [ ] Add PDO transactions around payment verification flow
- [ ] Verify `bookings` table schema has all required columns

**WARNING:** Highest risk phase. Test thoroughly on staging before deploying.

---

### Phase 4: Password Security + Identity
**Issues:** #10, #11, #12, #25, #5, #47 | **Effort:** ~4h | **Risk:** Medium | **Dependencies:** Phase 3

- [ ] Replace MD5-based auto-passwords with `bin2hex(random_bytes(8))` in `razorpay-verify.php`
- [ ] Replace MD5-based customer IDs with `bin2hex(random_bytes(4))` in `auth.php:116`
- [ ] Remove `auto_password` column from `bookings` table (send via email only)
- [ ] Change `users.password_hash` from nullable to `NOT NULL`
- [ ] Create `includes/admin-update-status.php` endpoint for order status updates
- [ ] Update `admin-dashboard.js:180-189` to call new endpoint via fetch
- [ ] Update seed data passwords (replace "password" with proper hashes)

---

### Phase 5: XSS + Input Sanitization
**Issues:** #6, #7, #36, #17 | **Effort:** ~5h | **Risk:** Medium | **Dependencies:** Phase 2

- [ ] Create `escapeHtml()` JS helper function in `shared.js`
- [ ] Replace all `innerHTML` injections with escaped data in `shared.js`, `customer-dashboard.js`, `admin-dashboard.js`
- [ ] Escape user-uploaded filenames (`original_name`) before display
- [ ] Escape `$basePath` in JS contexts using `json_encode()` in `login.php`, `customer-dashboard.php`, `bdc-admin/index.php`
- [ ] Sanitize `$fileName` in `Content-Disposition` header (`download-file.php:35`)
- [ ] Add rate limiting middleware using session-based request counters

---

### Phase 6: File + Payment Security
**Issues:** #8, #35, #32 | **Effort:** ~2h | **Risk:** Low | **Dependencies:** Phase 3

- [ ] Add authentication check in `download-file.php` (require `$_SESSION['user_id']` or admin role)
- [ ] Add file ownership verification (user can only download their own files, admin can download all)
- [ ] Remove Razorpay demo fallback (`rzp_test_demo` check) - require real keys
- [ ] Replace `@mail()` with proper error handling in `razorpay-verify.php:130`

---

### Phase 7: Infrastructure Security
**Issues:** #15, #19, #18 | **Effort:** ~3h | **Risk:** Low | **Dependencies:** None

- [ ] Add SRI `integrity` + `crossorigin` attributes to CDN links in `header.php` and `footer.php`
- [ ] Create `includes/security-headers.php` with CSP, X-Frame-Options, X-Content-Type-Options, Referrer-Policy
- [ ] Include security headers in all page templates
- [ ] Add `error_log()` calls in all catch blocks across PHP files

---

### Phase 8: JS Code Quality
**Issues:** #37, #38, #28, #45 | **Effort:** ~3h | **Risk:** Low | **Dependencies:** None

- [ ] Wrap `shared.js`, `booking.js` in IIFE to prevent global namespace pollution
- [ ] Move `priceMap` from `booking.js` to PHP-side config object
- [ ] Move course plans from `online-offline-classes-form.js` to PHP-side config
- [ ] Add `response.ok` check before `.json()` in all fetch calls
- [ ] Fix dead `s.name AS item` alias in dashboard queries

---

### Phase 9: CSS Optimization
**Issues:** #39-#43, #29, #30 | **Effort:** ~4h | **Risk:** Low | **Dependencies:** None

- [ ] Remove duplicate SCSS rules in `_cards.scss:130-163` (duplicated from `_global.scss`)
- [ ] Remove unused `$space-*` variables or replace hardcoded values
- [ ] Replace hardcoded color hex values with CSS custom properties
- [ ] Add PurgeCSS to webpack build pipeline
- [ ] Add `defer` attribute to Razorpay script tag
- [ ] Reduce `will-change` declarations (apply only on hover/animation)
- [ ] Standardize all breakpoints to use `$breakpoints` map values

---

### Phase 10: Developer Experience + Cleanup
**Issues:** #23, #44, #46 | **Effort:** ~2h | **Risk:** Low | **Dependencies:** None

- [ ] Add LIMIT/OFFSET pagination to dashboard queries + frontend controls
- [ ] Either use `e()` consistently or remove it from `helpers.php`
- [ ] Remove `playwright` from devDependencies or add basic test suite

---

## Summary

| Phase | Issues | Effort | Risk | Key Dependency |
|-------|--------|--------|------|----------------|
| 1 | #1, #3, #26 | 2h | Low | None |
| 2 | #2, #13, #9, #14, #27, #20, #33, #34, #31 | 5h | Medium | Phase 1 |
| 3 | #4, #16, #22, #21, #24 | 7h | **High** | Phase 1-2 |
| 4 | #10, #11, #12, #25, #5, #47 | 4h | Medium | Phase 3 |
| 5 | #6, #7, #36, #17 | 5h | Medium | Phase 2 |
| 6 | #8, #35, #32 | 2h | Low | Phase 3 |
| 7 | #15, #19, #18 | 3h | Low | None |
| 8 | #37, #38, #28, #45 | 3h | Low | None |
| 9 | #39, #40, #41, #42, #43, #29, #30 | 4h | Low | None |
| 10 | #23, #44, #46 | 2h | Low | None |
| **Total** | **47 issues** | **~37h** | | |

**Critical path:** Phase 1 → 2 → 3 → 4 (must be sequential, ~18h)
**Parallelizable:** Phases 5-10 can run in parallel after Phase 2 completion, or independently of each other.
