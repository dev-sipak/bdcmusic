# Changelog — 2026-09-15

## PHP Improvements

### New Files
- `includes/helpers.php` — Shared sanitize, JSON load/save, CSRF, URL, output helpers, service constants
- `includes/components/breadcrumb.php` — Reusable breadcrumb partial
- `includes/components/policy-checkbox.php` — Reusable privacy/terms checkbox partial
- `includes/components/faq.php` — Reusable FAQ accordion partial
- `includes/razorpay-verify.php` — Server-side Razorpay payment verification endpoint

### Modified Files
- `booking.php` — Extracted inline CSS/JS, fixed mkdir 0777→0755, added guest booking (optional password), Razorpay server-side verification, booking stored in session until payment confirmed, auto account creation after payment
- `booking-thank-you.php` — Extracted inline CSS, shows temporary password for guest accounts
- `login.php` — Extracted inline JS, added phone number field for signup, replaced inline `style="display:none"` with `.d-none` utility class
- `dashboard/customer-dashboard.php` — Extracted inline JS, replaced inline `style="display:none"` with `.d-none`
- `bdc-admin/index.php` — Extracted inline JS, replaced inline `style="display:none"` with `.d-none`
- `services/online-offline-classes.php` — Removed duplicate inline JS, replaced `.center` with `.text-center`
- `services/audio-video-services.php` — Fixed mkdir 0777→0755, added `session_start()`, integrated Razorpay checkout with server-side verification
- `services/iprs-services.php` — Fixed FAQ section: added `<div class="container">` wrapper, changed `section-block reveal` to `section-heading reveal`
- `services/bdc-artists-marketplace.php` — Fixed FAQ section: added `<div class="container">` wrapper, changed `section-block reveal` to `section-heading reveal`
- `footer.php` — Fixed terms link, removed GSAP/ScrollTrigger/SplitType, added shared.js
- `services/promotion-services.php` — Fixed terms link
- `services/digital-music-distribution.php` — Fixed terms link, removed hardcoded localhost canonical URL
- `dashboard/provider.php` — Removed hardcoded localhost canonical URL
- `dashboard/customer.php` — Removed hardcoded localhost canonical URL

## JavaScript Improvements

### New Files
- `assets/js/shared.js` — `initTabNav()`, `statusBadgeHtml()`, `orderRowHtml()`, `historyRowHtml()`, `initFaqAccordion()`
- `assets/js/login.js` — Extracted from login.php, phone validation, uses `.d-none` class toggle
- `assets/js/customer-dashboard.js` — Extracted from customer-dashboard.php, uses `.d-none` class toggle
- `assets/js/admin-dashboard.js` — Extracted from bdc-admin/index.php, uses `.d-none` class toggle
- `assets/js/booking.js` — Extracted from booking.php

### Modified Files
- `assets/js/online-offline-classes-form.js` — Expanded to handle course selection + class mode
- `assets/js/audio-video-services.js` — Added null guard for `serviceSelect`
- `assets/js/app.js` — Fixed testimonial-slider and FAQ accordion wrapped in DOMContentLoaded

## Bug Fixes
- Fixed `terms-and-condition` → `terms-and-conditions` across 8 files
- Fixed directory permissions `0777` → `0755` in `booking.php` and `audio-video-services.php`
- Fixed hardcoded localhost canonical URLs in 3 files
- Fixed `app.js` DOMContentLoaded for testimonial-slider and FAQ accordion
- Removed unused GSAP/ScrollTrigger/SplitType (~150KB saved)
- Fixed IPRS FAQ section visibility: added missing `<div class="container">` wrapper and corrected heading class
- Fixed BDC Artists Marketplace FAQ section: same container wrapper fix

## Razorpay Payment Integration (Task 5)

### Server-Side Verification
- Created `includes/razorpay-verify.php` — Verifies payment signature using HMAC SHA256
- Supports demo mode (rzp_test_demo) with signature format validation
- Supports production mode with real Razorpay signature verification

### Guest Booking Flow
- Password fields now optional in `booking.php`
- If no password provided: booking stored as pending in session, account auto-created after successful payment
- Auto-generated temporary password shown on thank-you page
- Existing accounts detected by email to prevent duplicates

### Booking Confirmation Flow
1. User fills form → booking stored in `$_SESSION['pending_booking']`
2. Razorpay order created server-side
3. User completes payment in Razorpay checkout
4. JS sends payment details to `razorpay-verify.php`
5. Server verifies signature → saves booking to JSON → creates account if guest
6. Redirects to thank-you page with booking ID

### Updated Service Pages
- `booking.php` — Full Razorpay integration with server-side verification
- `services/audio-video-services.php` — Razorpay checkout added with session-based pending booking

## Manual Signup — Contact Number (Task 6)

- Added phone number field to `login.php` signup form (hidden by default, shown on toggle)
- Frontend validation: required, 10-15 digits, strips spaces/dashes/parens
- Updated `assets/js/login.js` with phone validation logic

## Refactor PHP Classes (Task 3)

### Inline Styles → Utility Classes
- Replaced 8 instances of `style="display:none;"` with `.d-none` utility class across:
  - `login.php` (3 instances)
  - `dashboard/customer-dashboard.php` (2 instances)
  - `bdc-admin/index.php` (1 instance)
- Updated JS files to use `classList.add/remove('d-none')` instead of `style.display`

### Duplicate Class Cleanup
- Removed `.center { text-align: center; }` from `_utilities.scss` (duplicate of `.text-center`)
- Updated `services/online-offline-classes.php` to use `.text-center` instead of `.center`

## SCSS Recompiled
- `assets/dist/main.css` — Recompiled with all SCSS changes
