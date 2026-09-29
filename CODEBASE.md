# CODEBASE.md — BDC Music Studio

**Living architecture document for the BDC Music Studio web application.**

> This file is a map, not the source of truth. If this document conflicts with the
> source code, **the source code wins**. Update this file whenever architecture,
> routes, APIs, data model, auth, business logic, or security posture change.
>
> Secrets are deliberately omitted. No credential, key, token, or password appears
> in this document, by design.

**Last updated:** initial full audit (Phase 1–3 of `prompt.txt`). No application code
was modified to produce it.

---

## 1. Project Overview

### What the application does

A single-site PHP/MySQL web application for a music studio that (a) markets six
services, (b) sells them through a self-hosted multi-step checkout with Razorpay
payments, (c) lets customers track orders, service progress, invoices and music
releases in a dashboard, and (d) gives staff an admin panel for orders, artists,
artist enquiries, customers and releases.

It is **not** a CMS. Content lives in the database (`services`, `service_plans`,
`artists`, `artist_categories`) and in PHP partials; there is no template engine,
no admin content editor for pages, and no plugin/module system.

### Main technologies

| Layer | Technology |
|---|---|
| Language | PHP 8 (procedural, no framework, no autoloader, no DI container) |
| Database | MySQL / MariaDB via PDO (`utf8mb4`) |
| Templating | Raw PHP with `include_once` partials |
| Frontend | Server-rendered HTML + vanilla ES5-style JS (no framework, no bundler runtime) |
| Styling | SCSS compiled to a single `assets/dist/main.css` by Webpack 5 + Dart Sass |
| Payments | Razorpay Checkout.js + server-side signature verification |
| Email | PHP `mail()` (no SMTP library) |
| Images | GD (`imageResizeAndConvert()` in `includes/helpers.php`) |
| Web server | Apache with `mod_rewrite`; project assumes it is served from a `/bdcmusic/` subdirectory |

### Runtime / environment

- **Required PHP extensions:** `pdo_mysql`, `gd`, `fileinfo` (MIME sniffing),
  `json`, `session`. `finfo` is used for content-based upload validation.
- **Config:** a flat `.env` file in the project root, parsed by
  `includes/config.php:10-26`. There is **no environment switch** — only `.env` is
  ever read, so `.env.production` is documentation, not a loaded file.
- **Path assumption:** the `/bdcmusic/` path segment is hard-coded in
  `header.php` and in the `BASE_URL` fallback (`includes/config.php:38`). The
  application cannot be relocated without a code edit.
- **No `RewriteBase`** in `.htaccess`, for the same reason.

### Major dependencies

Runtime dependencies are only third-party CDNs, all loaded from `header.php:31-37`
and `booking.php` / `footer.php`:

- Google Fonts (two separate stylesheet requests)
- Font Awesome 6.6.0 (cdnjs)
- Swiper 11 (jsdelivr) — CSS on all public pages, JS initialised on the homepage only
- Razorpay Checkout.js

All `package.json` dependencies are **build-time only** (`devDependencies`): webpack,
webpack-cli, sass, sass-loader, css-loader, mini-css-extract-plugin,
css-minimizer-webpack-plugin, and playwright (declared but unused — see §14).

`package-lock.json` marks **all 227 packages `dev: true`**, so `npm ci --omit=dev`
installs nothing; producing CSS requires a full dev install.

### Overall architecture

```
                    ┌──────────────────────────────────────────┐
  Browser  ────────►│  Apache + .htaccess clean-URL rewrite    │
                    └───────────────┬──────────────────────────┘
                                    ▼
        ┌───────────────────────────────────────────────────────────┐
        │  PHP page (public / dashboard / bdc-admin)                  │
        │    session_start() → config.php → database.php             │
        │    → header/nav partial → page body → footer partial       │
        └───────┬───────────────────────────────┬───────────────────┘
                │                               │
                ▼ (fetch/XHR, JSON)             ▼ (direct PHP entry)
   ┌────────────────────────────┐   ┌────────────────────────────────────┐
   │ includes/admin/*.php (13)  │   │ includes/auth.php                   │
   │ includes/customer/*.php(6) │   │ includes/booking-create.php         │
   │ includes/booking-*.php     │   │ includes/razorpay-verify.php        │
   │ includes/upload-*.php      │   │ includes/download-file.php          │
   │ includes/update-profile.php│   │ includes/artist-enquiry-submit.php  │
   │ includes/change-password   │   └──────────────┬─────────────────────┘
   │ includes/logout.php        │                  ▼
   └─────────────┬──────────────┘        PDO (ATTR_EMULATE_PREPARES=false)
                 ▼                              │
        ┌────────────────────┐                 ▼
        │ assets/js/*.js     │        ┌─────────────────────┐
        │ (innerHTML render) │        │ MySQL: 18 tables     │
        └────────────────────┘        └─────────────────────┘
```

There is no separate API tier: every "API" is a PHP file under `includes/` that is
both directly web-reachable and called by `fetch()`. Each such file repeats its own
`session_start()`, includes, `Content-Type` header, and role check.

---

## 2. Directory Structure

| Path | Responsibility |
|---|---|
| `*.php` (root) | Public marketing pages, `header.php`/`footer.php` shell, `login.php`, `booking.php`, `booking-thank-you.php` |
| `includes/` | Config, DB, shared helpers, booking engine, and all non-admin endpoints |
| `includes/admin/` | 13 admin JSON endpoints (order, artist, customer, enquiry, release) |
| `includes/customer/` | 6 customer JSON endpoints (orders, releases, service overview) |
| `includes/components/` | Partial components — **all three are currently unused** (see §12) |
| `bdc-admin/` | Admin screens + `admin-guard`, `admin-header/nav/footer`, modals |
| `dashboard/` | Customer screens + `panel-guard`, `panel-config`, `panel-nav`, order modal |
| `services/` | The six service marketing/checkout pages |
| `artists/` | Artist directory (`index.php`) and artist profile (`detail.php`) |
| `assets/js/` | 14 vanilla JS files: 4 admin, 3 customer, 3 service-form, `app.js`, `shared.js`, `login.js`, `booking-checkout.js` |
| `assets/scss/` | SCSS sources: `abstracts/`, `base/`, `components/`, `layout/`, `pages/`, entry `main.scss` |
| `assets/dist/` | Generated `main.css` — **git-ignored**; the committed tree has no stylesheet |
| `assets/images/` | Static imagery, including ~4.6 MB of unreferenced files (§13) |
| `assets/src/scss-entry.js` | Webpack entry that pulls in `main.scss` |
| `database/bdcmusic.sql` | Full schema **and** seed data. No migration framework exists |
| `data/uploads/` | Customer-uploaded files at runtime. `profile/`, `_draft/`, per-booking dirs |
| `prompt.txt` | The standing engineering brief governing how changes are made |
| `CODEBASE.md` | This document |

Not documented in detail (generated / vendored): `node_modules/`, `assets/dist/`,
`data/uploads/`.

---

## 3. Application Architecture

### Frontend architecture

Server-rendered PHP emits complete HTML documents. There is **no hydration and no
client-side router**; every route is a full page load.

Three tiers of JS:

1. **Per-page feature scripts** — `booking-checkout.js`, `login.js`,
   `admin-*.js`, `customer-*.js`. Each is an IIFE or `DOMContentLoaded` block that
   fetches JSON and re-renders table/panel regions via `innerHTML`.
2. **Cross-cutting scripts** — `shared.js` (sidebar toggle, status/payment badges,
   pagination HTML, FAQ accordion) loaded on every page; `app.js` (nav, dropdowns,
   sliders, reveal-on-scroll, FAQ) loaded on every public page.
3. **Legacy per-service form scripts** — `audio-video-services.js`,
   `distribution-form.js`, `online-offline-classes-form.js`. All three target
   selectors that do not exist in the current markup, so none of them do anything.

`booking-checkout.js` is the one script written as strict progressive enhancement:
it only decides what is *visible*, and the server re-validates everything on POST.

Output escaping is inconsistent between files (see §12): five files define a local
`esc()` helper (`admin-dashboard.js:6`, `admin-releases.js:22`,
`customer-dashboard.js:10`, `customer-releases.js:13`, `customer-service.js:26`),
`shared.js:48-50` has an inline variant, and `admin-enquiries.js` +
`admin-artists.js` have none.

### Backend architecture

Procedural PHP. Every entry point performs the same manual bootstrap:

```php
if ( session_status() === PHP_SESSION_NONE ) { session_start(); }
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
```

`config.php` loads `.env`, defines `DB_*`, `BASE_URL`/`$assetPath`, Razorpay
constants, `IS_PRODUCTION`, `BOOKING_DEMO`, and derives `$currentPage` from
`REQUEST_URI`. `database.php` provides `db_connect()` — a lazily-created,
process-local PDO singleton.

Access control is a **two-layer convention**, not middleware:

- **Page layer** — `bdc-admin/includes/admin-guard.php` and
  `dashboard/includes/panel-guard.php` redirect or exit before any output.
- **Endpoint layer** — every file in `includes/admin/`, `includes/customer/` and
  each upload/update endpoint repeats its own inline session+role check.

Both layers read `$_SESSION['user_role']` and never re-validate against the
database.

### API architecture

- Transport: `POST` with `application/x-www-form-urlencoded`, `multipart/form-data`,
  or `application/json`; `GET` for list/read endpoints.
- Content type: `application/json` on all endpoints except `includes/logout.php`
  (which redirects) and `includes/artist-enquiry-submit.php` (dual-mode).
- Body parsing: a recurring two-line idiom —
  `$input = json_decode(file_get_contents('php://input'), true); if (!$input) { $input = $_POST; }`
  — present in `artist-save.php:21-24`, `order-update.php:43-46`,
  `release-save.php` (both admin and customer), `upload-artist-image.php`, and
  `artist-enquiry-submit.php:15-19`. This is what makes several JSON endpoints
  reachable by a plain cross-site form.
- Envelope: `{"success": bool, "message": string, ...payload}` with a shared
  `pagination` object (`currentPage`, `totalPages`, `totalRecords`, `perPage`,
  `hasPrev`, `hasNext`) on list endpoints. Two endpoints (`update-profile.php`,
  `change-password.php`) return a flat envelope with no payload key.
- **Status codes are not part of the contract.** Auth (403) and method (405)
  failures are real; every validation and most DB failures return HTTP 200 with
  `success:false`. All clients branch on `data.success` and ignore
  `response.status`. `booking-create.php` and `razorpay-verify.php` are the
  exceptions (419 CSRF, 502 gateway, 503 unconfigured).

### Database architecture

Single MySQL/MariaDB database, 18 tables, accessed only through PDO prepared
statements. `db_connect()` sets `PDO::ATTR_EMULATE_PREPARES => false`, so
placeholder binding is real. There are **no ORM models and no repository layer** —
SQL lives inside the endpoint or partial that needs it.

No foreign keys are defined anywhere in the schema; only `PRIMARY KEY`,
`UNIQUE KEY` and plain `KEY` indexes. Relationships are maintained by application
code. There is no migration tool: `database/bdcmusic.sql` is a single
create-and-seed dump that must be applied by hand.

### Authentication

Single shared-credential model in `includes/auth.php`, called by `login.php` +
`assets/js/login.js` for both sign-in and sign-up.

- Passwords: `password_hash(..., PASSWORD_BCRYPT)` / `password_verify()`.
- On success: `session_regenerate_id(true)`, then
  `$_SESSION['user_id']`, `user_name`, `user_email`, `user_role`.
- Roles: binary — `users.role enum('customer','admin')`. No permissions table, no
  per-role capability model.
- Session cookie flags (`HttpOnly`, `Secure`, `SameSite`) are **never set** anywhere
  in the project; there is no `session_set_cookie_params()` call and no
  `session.use_strict_mode`.

### Authorization

| Surface | Check |
|---|---|
| Admin pages | `admin-guard.php`: `user_id` set **and** `user_role === 'admin'`, else redirect to `login` |
| Admin endpoints | Same predicate repeated inline in all 13 files → 403 JSON |
| Customer pages | `panel-guard.php`: `user_id` set **and** `user_role === 'customer'`, else redirect |
| Customer endpoints | Same predicate repeated inline in 6 files → 403 JSON |
| Entitlement (per-service unlock) | `panel-guard.php:81-82` builds `$purchasedServices` from paid/completed bookings; `customer_has_service()`; enforced on `service.php` and `service-overview.php` **but not on the four release endpoints** |
| Row ownership | `WHERE customer_id = :cid` on customer read paths; **absent** in `includes/customer/release-save.php` child writes |
| File access | `download-file.php` — no check at all (§11) |

### State management

- **Server-side session state:** the booking draft (`$_SESSION['booking_draft']`),
  guest booking ownership marker (`$_SESSION['booking_confirmed']`), identity
  (`user_id`/`user_role`/`user_name`/`user_email`/`user_picture`), and the CSRF
  token. Managed by `includes/booking-session.php`.
- **Client-side state:** transient only — current filter, pagination page, selected
  tab, modal content. Nothing persisted to `localStorage`/`sessionStorage`.
- **Database state:** bookings + `booking_payments` + `service_records` +
  `release_*` are the durable model. There is no cache layer of any kind.

### External services

| Service | Direction | Notes |
|---|---|---|
| Razorpay Orders API | server → Razorpay | `curl` in `booking-create.php`; key secret never leaves the server |
| Razorpay Checkout.js | client → Razorpay | key id only (public by design) |
| Razorpay signature verify | server-side, local | `razorpay-verify.php:193` |
| PHP `mail()` | server → SMTP | `enquiry-reply.php:70` (suppressed, unchecked), booking notification in `booking-create.php` |
| Font Awesome / Swiper / Google Fonts | client → CDN | no SRI attributes |

### Important data flows

**Booking purchase (the core flow):**

```
service page  ──booking_plan_grid()──►  renders plan cards from services/service_plans
      │  booking_preselect_url($slug, $planId)  → /booking?service=…&plan=…
      ▼
booking.php?service=…  ──(no service/package step)──►  default plan auto-selected
      │  booking_steps() computes which steps apply for THIS service:
      │  details | contact | review | payment   (quote mode drops payment)
      │  the "related packages / related services" block below the form posts
      │  form=packages and rewrites plan_ids (booking.php case 'packages')
      │  each POST → booking_draft_set() ; files → data/uploads/_draft/<token>/
      ▼
payment step → assets/js/booking-checkout.js
      │  POST includes/booking-create.php   (CSRF, re-walks the draft's plan_ids,
      │                                      re-prices every line server-side,
      │                                      INSERT bookings + one booking_items
      │                                      row per package at qty 1, promote
      │                                      staged uploads into
      │                                      data/uploads/<id>/, create Razorpay
      │                                      order)
      │  ◄── {success, booking_id, order_id, amount, key_id} or {demo:true}
      │  Razorpay modal ──► handler(razorpay_payment_id, order_id, signature)
      ▼
POST includes/razorpay-verify.php  (CSRF, hash_equals session ownership,
      HMAC signature, amount match, replay guard, single transaction)
      ▼
booking-thank-you.php?order=BDCM-XXXXXX   (owner-or-guest-session check)
```

**Guest → account:** a guest booking has `customer_type='guest'` and
`customer_id = NULL`; guest ownership of a confirmation is proven by
`hash_equals($_SESSION['booking_confirmed'], $booking_id)`, never by the id alone.

**Admin order update:** `order-update.php` — `SELECT … FOR UPDATE` inside a
transaction, status allow-list, `progress` validated against
`service_progress_options()`, upsert into `service_records`.

---

## 4. Routes / Screens / Pages

Clean URLs come from `.htaccess` (22 lines, 4 rules). Artist routes have dedicated
rules; everything else maps `^(.+?)/?$ → $1.php`. Because `!-f` guards every rule,
direct `.php` URLs are also live and indexable — there is no canonical redirect.

### Public pages (no auth; all start a session via `header.php:2-4`)

| Route | File | Purpose | Key logic |
|---|---|---|---|
| `/` | `index.php` | Home, hero slider, service grid, testimonials | 0 own queries |
| `/about-bdc-music` | `about-bdc-music.php` | About | 0 queries; re-requires `config.php` redundantly |
| `/all-services` | `all-services.php` | Services index | 0 queries |
| `/contact` | `contact.php` | Contact | 0 queries |
| `/privacy-policy`, `/terms-and-conditions` | `privacy-policy.php`, `terms-and-conditions.php` | Legal | 0 queries; **unused `policy-checkbox.php` component exists** |
| `/services/bdc-artists-marketplace` | `services/bdc-artists-marketplace.php` | Membership packages | `booking_plan_grid('artists-marketplace')` |
| `/services/audio-video-services` | `services/audio-video-services.php` | Audio/video | `booking_plan_grid('audio-video')` + 4 hand-written video tiers |
| `/services/digital-music-distribution` | `services/digital-music-distribution.php` | Distribution | `booking_plan_grid('digital-distribution')` |
| `/services/iprs-services` | `services/iprs-services.php` | IPRS | `booking_plan_grid('iprs')` |
| `/services/online-offline-classes` | `services/online-offline-classes.php` | Classes, grouped plans | `booking_service()` + 4× `booking_plans()` in a loop |
| `/services/promotion-services` | `services/promotion-services.php` | Promotion | `booking_is_quote_mode()` → `booking_quote_note('promotion')`, no payment |
| `/artists/` , `/artists/<category>/` | `artists/index.php` | Artist directory, category filter, `paginate()` | 4–5 queries |
| `/artists/<category>/<artist>/` | `artists/detail.php` | Artist profile, pricing, enquiry modal | 4 queries (one is dead — `$allCats`) |
| `/login` | `login.php` | Combined sign-in / sign-up form | redirects if session set |
| `/booking` | `booking.php` | **Variable-step checkout** (see §9) — service/package are no longer steps; the default plan is auto-selected | requires a usable session (`:30`) |
| `/booking-thank-you?order=…` | `booking-thank-you.php` | Confirmation | owner-or-guest-session check |

### Customer dashboard — all require `user_id` + `user_role === 'customer'`

| Route | File | Purpose |
|---|---|---|
| `/dashboard/customer-dashboard` | `customer-dashboard.php` | Orders list, profile, profile picture, password change |
| `/dashboard/orders` | `orders.php` | Order history + order detail modal + invoice/payment |
| `/dashboard/releases` | `releases.php` | Releases list + create/edit form, platform links |
| `/dashboard/service` | `service.php` | Per-service progress and files for the purchased service |
| `/dashboard/customer`, `/dashboard/provider` | `customer.php`, `provider.php` | **Unauthenticated placeholder pages, not linked from anywhere** — see §12 |

### Admin panel — all require `user_role === 'admin'`

| Route | File | Purpose |
|---|---|---|
| `/bdc-admin/` | `bdc-admin/index.php` | Overview stats + 10 recent orders |
| `/bdc-admin/orders` | `bdc-admin/orders.php` | Orders list, filters, order modal, status/progress update |
| `/bdc-admin/releases` (+`?status=`) | `bdc-admin/releases.php` | Releases list + release editor (status, tracks, platform links) |
| `/bdc-admin/customers` | `bdc-admin/customers.php` | Customers, order counts, lifetime spend |
| `/bdc-admin/artists` | `bdc-admin/artists.php` | Artists CRUD + pricing editor + image upload |
| `/bdc-admin/enquiries` | `bdc-admin/enquiries.php` | Artist enquiries list, status, reply |
| `/bdc-admin/services` | `bdc-admin/services.php` | Services CRUD + packages CRUD (JS in `assets/js/admin-plans.js`, JSON in `includes/admin/{services,plans}-list.php`, `plan-save/delete`, `service-save/delete`) |

---

## 5. Components

### Shared across the whole site

| Component | Location | Responsibility |
|---|---|---|
| `header.php` | root | `<head>`, meta/OG/canonical, CDN tags, logo, primary nav, auth-aware CTA. Included by **22** pages. Runs one `artist_categories` query on every render (`:93`) |
| `footer.php` | root | Footer markup, social links, Swiper JS, `shared.js`, `app.js` |
| `includes/config.php` | `includes/` | Env loading, path constants, feature flags, `$currentPage` detection |
| `includes/database.php` | `includes/` | `db_connect()` PDO singleton |
| `includes/helpers.php` | `includes/` | 24 functions: sanitisation, CSRF, booking id, URLs, `e()`, badge HTML, `imageResizeAndConvert()`, order amount/plan/addon/payment shaping |
| `assets/js/shared.js` | `assets/js/` | Sidebar toggle, `statusBadgeHtml`, `paymentBadgeHtml`, `renderPaginationHtml`, `initFaqAccordion` |
| `assets/js/app.js` | `assets/js/` | Nav, dropdowns, hero slider, scroll reveal, FAQ, footer year |

### Feature-specific, high-value

| Component | Responsibility | Complexity |
|---|---|---|
| `includes/booking-catalog.php` | The single source of truth for the service catalogue: `booking_services`, `booking_service`, `booking_is_quote_mode`, `booking_plan_groups`, `booking_plans`, `booking_plan`, **`booking_resolve_selection`** (price authority), `booking_money`, paise conversion | **High** — the security and pricing boundary of the whole site |
| `includes/booking-session.php` | Session draft, `booking_steps()` dynamic step machine, `booking_step_satisfied`, `booking_furthest_reachable`, staging dir, upload discard | **High** — the checkout state machine |
| `includes/booking-registry.php` | Per-service field definitions (`booking_fields`), visibility rules, validation (`booking_validate_details`, `booking_validate_contact`), field rendering (`booking_render_field`), `booking_esc` | **High** — declarative form spec + renderer |
| `includes/service-fields.php` | Per-service metadata: nav label/icon, meta fields, arrangement fields, `service_progress_options`, status→unlock map, `service_meta_pairs` | Medium-high |
| `includes/booking-marketing.php` | `booking_plan_card`, `booking_plan_grid`, `booking_cta`, `booking_quote_note` — server-rendered plan marketing blocks | Medium |
| `includes/razorpay-verify.php` | Payment settlement: CSRF, session ownership via `hash_equals`, HMAC verify, amount match, replay guard, single transaction, notification mail | **High** — best-written file in the project |
| `includes/file-upload.php` | MIME allow-lists per service, size caps, per-service directories, `process_file_upload`/`process_multiple_uploads` (content-sniffed via `finfo`, server-generated names) | Medium |
| `includes/pagination.php` | `paginate()`, `paginate_meta()`, `render_pagination()` (7-button window, ellipses) | Low-medium — the PHP half is dead |
| `dashboard/includes/panel-guard.php` | Customer auth redirect + `customer_has_service()` + purchased-service aggregation | Medium — a DB failure silently empties entitlements |
| `bdc-admin/includes/admin-guard.php` | Admin auth redirect only | Low |

### Refactoring candidates

- The **role guard is copy-pasted 20+ times** (`includes/admin/*` 13×,
  `includes/customer/*` 6×, guards). One shared `require_*_role('admin')` helper
  would remove ~60 duplicated lines and one class of drift (which already produced
  the integer-vs-string role bug, §9).
- `esc()` is duplicated in 5 JS files. It belongs in `shared.js`.
- `renderOrderPlan()` is implemented twice (`customer-dashboard.js:126` and
  `customer-service.js:165`) with the same logic.
- `render_pagination()` (`pagination.php:102-191`) and `renderPaginationHtml()`
  (`shared.js:76-154`) are line-for-line twins; only the JS one is used.

---

## 6. Services and APIs

### Booking / payment endpoints

| Endpoint | Method | Auth | Validation | Notes |
|---|---|---|---|---|
| `includes/booking-create.php` | POST | none (guest allowed) | CSRF (`:52-56`, 419); re-walks the draft; `booking_resolve_selection()` re-prices; `booking_validate_details` + `booking_validate_contact` | Transaction; `curl` to Razorpay; 502/503 on gateway failure; **defect at `:64`, see §9** |
| `includes/razorpay-verify.php` | POST | session + `hash_equals` owner check (`:112-118`) | CSRF (`:103`); HMAC signature (`:193`); amount match (`:233`); replay guard (`:162`) | Single settlement transaction; notification mail |
| `includes/download-file.php` | GET | **none** | `realpath` containment only | See §11 — unauthenticated read of the uploads tree |

### Auth / account endpoints

| Endpoint | Method | Auth | CSRF | Notes |
|---|---|---|---|---|
| `includes/auth.php` | POST (`action=login`/`signup`) | n/a | none | bcrypt; `session_regenerate_id(true)`; no throttling; min password length 6 |
| `includes/logout.php` | GET | none | n/a | `session_unset` + `session_destroy` + redirect; **cookie not deleted** |
| `includes/update-profile.php` | POST | `user_id` | **none** | Exactly two columns bound (name, mobile) — no mass assignment |
| `includes/change-password.php` | POST | `user_id` | **none** | Requires current password; does not regenerate session or invalidate |
| `includes/upload-profile-pic.php` | POST multipart | `user_id` (no role check) | **none** | `finfo` MIME, extension from MIME, filename from session id; no re-encode; DB failure reported as success |

### Customer endpoints (all require `user_role === 'customer'`)

| Endpoint | Method | Owner scoping | CSRF |
|---|---|---|---|
| `includes/customer/orders-list.php` | GET | `b.customer_id = :cid` (`:24`) | n/a |
| `includes/customer/order-detail.php` | GET | `b.customer_id = :cid` (`:51`) | n/a |
| `includes/customer/service-overview.php` | GET | `:64` + `customer_has_service()` (`:70-73`) | n/a |
| `includes/customer/releases-list.php` | GET | `customer_id` (`:23`) — **no entitlement check** | n/a |
| `includes/customer/release-save.php` | POST | `UPDATE` only; child `DELETE`/`INSERT` unscoped and non-transactional | **none** |
| `includes/customer/release-submit.php`, `release-takedown.php` | POST | owner-scoped + `rowCount()` checked | **none** |

### Admin endpoints (all require `user_role === 'admin'`, none have CSRF)

`artist-save.php`, `artist-delete.php`, `artists-list.php`, `categories-list.php`,
`customers-list.php`, `enquiries-list.php`, `enquiry-reply.php`,
`enquiry-update-status.php`, `order-detail.php`, `orders-list.php`,
`order-update.php`, `release-save.php`, `releases-list.php`, plus
`includes/upload-artist-image.php`.

CSRF helpers already exist and are correct — `helpers.php:73-92`
(`generate_csrf_token`, `csrf_field`, `verify_csrf_token` with `hash_equals`) — and
are used only in `booking.php`, `booking-create.php` and `razorpay-verify.php`.

### External integrations

- **Razorpay Orders API** — server-side `curl`; key id to the client, key secret
  never leaves `config.php`. `BOOKING_DEMO` (a payment bypass) is force-disabled
  whenever `IS_PRODUCTION`, so a deployment that forgets `APP_ENV` refuses payment
  rather than confirming unpaid bookings.
- **`mail()`** — booking notification, and admin enquiry replies. No SMTP transport,
  no queue, no logging.

---

## 7. Database / Data Model

18 tables, no foreign keys, no migrations framework. Schema + seed live in one dump
(`database/bdcmusic.sql`).

### Tables

| Table | Purpose | Key columns / constraints |
|---|---|---|
| `users` | Accounts | `id` **varchar(20)** PK (values like `CUST-A1B2C3D4`; admin is `CUST-ADMIN0001`), `email` UNIQUE, `password_hash`, `role enum('customer','admin')`, `mobile`, `profile_picture`, `source` |
| `services` | Service catalogue | `slug` (UNIQUE), `name`, `booking_mode enum('packages','quote')`, `price_note`, `is_active` |
| `service_plans` | Packages | `service_id`, `group_key`, `group_label`, `price`, `price_note` (e.g. `Custom Quote`), `features` (JSON), `sort_order`, `is_default`, `is_enquiry`, `is_orderable` |
| `bookings` | An order | `booking_id` varchar PK `BDCM-XXXXXX` (3 random bytes hex), `customer_id` varchar **nullable** (NULL = guest), `customer_type enum('registered','guest')`, snapshot columns (`customer_name/email/phone/whatsapp`), `status enum`, `payment_status`, `payment_provider` (`razorpay`/`manual`), `razorpay_order_id`, `razorpay_signature`, `payment_failure_reason`, `plan_*` snapshots, `subtotal`, `addons_total` (kept NOT NULL for old orders; new orders write 0), `price`, `meta` JSON, `invoice_no`, `service_slug`, `service_name` |
| `booking_items` | One package line per order | `booking_id`, `plan_id`, `plan_name`, `plan_group`, `plan_group_label`, `unit_price`, `qty` (always 1), `line_total`; UNIQUE (`booking_id`,`plan_id`) |
| `booking_addons` | **History only, read-only** | `booking_id`, `addon_id` (no longer a FK), `addon_name`, `unit_price`, `qty`, `line_total` (snapshotted). Holds the lines of the six orders placed before add-ons left the catalogue; nothing writes to it now. |
| `booking_items` | Per-package lines | `booking_id`, `plan_id`, `plan_name`, `plan_group`, `plan_group_label`, `unit_price`, `qty`, `line_total`; UNIQUE `(booking_id,plan_id)`; FK cascade on booking delete |
| `booking_payments` | Payment attempts | `booking_id`, `provider`, `razorpay_order_id/payment_id/signature`, `amount`, `status`, `method`, `failure_reason` |
| `service_records` | Customer-visible progress | UNIQUE `booking_id`, `headline`, `sub_headline`, `progress`, `starts_on`, `ends_on`, `location`, `notes` |
| `uploaded_files` | Uploaded files | `booking_id`, `field_name`, `original_name`, `file_path`, `mime_type`, `file_size` |
| `artists` | Artist profiles | `slug` UNIQUE, `image`, `category_id`, `bio`, `is_active` |
| `artist_categories` | Marketplace categories | `slug`, `sort_order`, `is_active` |
| `artist_pricing` | Per-artist per-service price | `artist_id`, `service_type`, `price` — **no unique key, no FK** |
| `artist_enquiries` | Marketplace leads | `artist_id`, `name`, `email`, `phone`, `message text`, `status` |
| `releases` | Digital distribution releases | `booking_id`, `customer_id`, `title`, `type`, `isrc`, `upc`, `status enum`, `go_live_date`, `dolby`, `apple_itunes` |
| `release_artists` | Release contributors | `release_id`, `role`, `name` |
| `release_tracks` | Track list | UNIQUE `(release_id, track_no)` |
| `release_platform_links` | Store links | UNIQUE `(release_id, platform)`, `url`, `is_active` |
| `release_history` | Audit trail | `release_id`, `action`, `message`, `reviewer_note` — written on every admin change |

### Relationships (enforced only in application code)

```
services 1─* service_plans
services 1─* bookings 1─* booking_items      (one row per package, qty 1)
                1─* booking_addons            (history only, no longer written)
                1─* booking_payments
                1─1 service_records
                1─* uploaded_files
users   1─* bookings            (NULL customer_id = guest)
users   1─* releases
artist_categories 1─* artists 1─* artist_pricing
artists 1─* artist_enquiries
releases 1─* release_tracks / release_platform_links / release_artists / release_history
```

### Important business fields

- `bookings` carries **denormalised snapshots** of customer, plan and price names,
  and `booking_items` carries the per-package snapshot for every line. The booking
  rows, not a join back to `service_plans`, are the money source of truth — so a
  later price or plan edit never rewrites history. Preserve this.
- An order is **one service and a list of its packages**. `bookings.plan_id` /
  `plan_name` / `plan_group` mirror only the first line so existing single-package
  readers keep working; `booking_items` is the full list.
- `booking_addons` is **read-only history**. Add-ons are no longer sold, so nothing
  inserts into it and the `addon_id` foreign key is gone. Do not "restore" the table
  it pointed at.
- `service_plans.is_orderable` separates "published" from "buyable". A `0` row
  still renders its name and price on a service page but can never be added to an
  order, even by a hand-edited `?plan_id=`. A/V uses this for its 48 sub-service
  rate cards, which stay on the page as a price list while only the 4 studio
  bundles are sold. The default is `1`, so other services are unaffected. Do not
  implement this as a hardcoded service slug or plan id.
- Money is stored as `DECIMAL`-style floats but **compared and summed in integer
  paise** (`booking-catalog.php`, `booking_to_paise`/`booking_from_paise`).
- `bookings.customer_id` is nullable *by design* for guests. Do not "tighten" it to
  `NOT NULL`.
- `users.id` is a **varchar string**, not an integer. Any `(int)` cast of a user id
  is a bug — see §9 and §11.

### Data access patterns

- Page lists: `paginate()` wraps a hand-written base SQL in
  `SELECT COUNT(*) … FROM (<sql>) _count_table`, then re-runs the SQL with
  `LIMIT/OFFSET`. Callers pass hardcoded SQL (only `artists/index.php` today).
- Child rows for a page of parents are fetched in **one** `IN (…)` query, not per
  row: `booking_order_addons` / `booking_order_items` in `helpers.php`,
  `artists-list.php`, `releases-list.php`, `service-overview.php`. This is
  deliberate and good; preserve the batched pattern when adding endpoints.
- `LIKE` search terms are bound but **not** wildcard-escaped.

### Migrations

None. Schema changes are hand-edits to `database/bdcmusic.sql` (or ad-hoc
`ALTER TABLE` on the server, which is what the `upload-profile-pic.php` comment
"Column may not exist yet" implies has already happened at least once). There is no
version table.

---

## 8. Authentication & Authorization

### Login flow

```
login.php → assets/js/login.js → POST includes/auth.php (action=login|signup)
  ├─ password_verify() against users.password_hash
  ├─ session_regenerate_id(true)                    auth.php:61 / :134
  ├─ $_SESSION[user_id|user_name|user_email|user_role]
  └─ {success, message, redirect} → login.js redirects after 1s
```

**Sign-up** generates the customer id as
`'CUST-' . strtoupper(substr(md5(strtolower($email) . microtime(true)), 0, 8))`
(`auth.php:116`) — a string, not a sequence. The `(int)` cast that breaks this is
documented in §9.

### Session handling

- `session_start()` is called on every entry point, guarded by
  `session_status() === PHP_SESSION_NONE` in most files but **unconditionally** in
  `login.php:2`.
- **No cookie flags anywhere** — no `HttpOnly`, no `Secure`, no `SameSite`, no
  `use_strict_mode`, no idle or absolute timeout. `SameSite=Lax` is inherited from
  browser defaults only; nothing enforces it.
- Roles are cached in the session for its whole lifetime and never re-checked
  against the DB.
- Logout (`includes/logout.php`) destroys the server session but never deletes the
  session cookie, so the id survives in the browser.

### Roles and permissions

Exactly two: `customer`, `admin`. No capability/permission table, no per-action
authorisation, no admin sub-roles. **Granting admin is a single `UPDATE users SET
role='admin'`.**

### Protected routes

Admin: 6 pages via `admin-guard.php`, 14 endpoints inline.
Customer: 4 real pages via `panel-guard.php`, 6 endpoints inline.
Unprotected but sensitive: `includes/download-file.php`,
`includes/artist-enquiry-submit.php`, `dashboard/customer.php`,
`dashboard/provider.php`, `includes/prompt.txt`-adjacent build files, `database/`,
`data/uploads/`, and both `.env` files (see §11).

### Security-sensitive implementation notes

1. **`razorpay-verify.php` is the reference implementation** for anything touching
   money: CSRF check, `hash_equals` session-ownership proof, HMAC signature verify,
   amount match, replay guard, and a single settlement transaction. New payment code
   should be modelled on it.
2. **Guest ownership is proven by session marker, never by id**
   (`booking-thank-you.php:52-54`, `razorpay-verify.php:112-118`). Do not simplify
   this to "if you know the booking id".
3. **The booking price is always re-resolved server-side** in `booking-create.php`
   via `booking_resolve_selection()`. Never trust a client-supplied amount.
4. **The admin/customer role check is duplicated, not centralised.** It has already
   drifted once (integer `1` vs string `'customer'`, §9).
5. **Uploads are validated by content** (`finfo`), not by `$_FILES['type']`, and
   filenames are server-generated. `upload-artist-image.php` additionally
   re-encodes to WebP through GD, which strips appended payloads.
6. **`sanitize_input()` is not output encoding.** It is
   `trim(strip_tags((string)$value))` — no escaping, no length cap, leaves quotes
   intact. Every sink must escape separately. The name invites misuse, and the
   misuse already exists in three admin JS files (§11).

---

## 9. Important Business Logic

### The checkout state machine

Step ids in journey order (`booking-session.php` → `booking_steps()`):
`details → contact → review → payment`.
**`service`, `package` and `addons` are no longer steps.** The customer picks the
service on its own page, packages are picked either there, via `?plan_id=`, or
through the "related packages" block rendered under the form, and add-ons are gone
from the catalogue entirely.

`booking_retired_steps()` maps the dead identifiers (`addons`, `addon`,
`package`, `packages`, `service`) onto `details`, so an old bookmark is redirected
to a live step rather than rendering one underneath a dead `step` parameter.

**`booking_steps()` computes which steps actually apply, per service:**

- No service resolved yet → `[]` (the bare service chooser, no step indicator).
- Quote-mode service (`booking_is_quote_mode()` → `payment_provider = 'manual'`):
  `details, contact, review`. **No** `payment`. `promotion` is the only such service
  today.
- Non-quote: `details, contact, review, payment`.

The progress `<ol>` is the only step indicator. There is no "STEP n OF m" counter
and the `h1` is the service name, not the step word.

**Package selection:** opening `booking.php?service=<slug>` auto-selects the
service's default package via `booking_apply_default_plan()` (active, non-enquiry,
`is_default DESC, sort_order, id`); with no default it redirects to
`booking_service_page_url()`. `?plan_id=` and `?plan_ids[]` are **additive** — the
ids are validated against the service, enquiry tiers and duplicates are ignored, and
anything already on the order stays. The related block posts `form=packages` to a
`case 'packages'` handler that re-validates the ids against the service, clamps to
one when the service is not multi-select, and redirects back — it never advances
the step. `booking_service_allows_multi_plan()` is true for every package-mode
service.

`booking_furthest_reachable()` gates forward navigation;
`booking_step_satisfied()` decides whether a step may be left.

**Draft reset:** `booking_draft_reset()` is called when a booking is created. It
also discards staged uploads. Switching service goes through
`booking_service_page_url()`, which builds a fresh draft.

### Pricing — the authority chain

`booking_resolve_selection( $pdb, $slug, $groupKey, $planId, $addonIds, $planIds )`
(`booking-catalog.php`) is the only place a total is computed:

1. Resolve service by slug (active only).
2. If not quote mode, require at least one plan. `$planIds` (the whole list) wins
   over the single mirrored `$planId`. Every plan must belong to the service; an
   inactive or enquiry tier is refused, and for a **single**-line order the plan
   must also match `$groupKey`, which is what stops a Classes customer pricing a
   Singing package while the UI shows another group. A multi-line order carries
   one group per line, so each line is checked against its own group instead.
3. `$addonIds` is accepted and ignored. Add-ons left the catalogue; the parameter
   only remains so existing positional callers keep working.
4. A plan with `is_orderable = 0` is refused as well. It is published as a price
   list and may be read for its name and price, but it can never price an order
   and can never be the auto-selected default.
5. `subtotal = Σ plan.price` over every line, summed in **integer paise**.
   `total = subtotal`; `addons_total` is always 0 and `addons` always empty, kept
   only so the NOT NULL `bookings.addons_total` and existing readers still work.

Quote mode returns `plan = null` → `subtotal = 0` → "Quoted on request".
`booking.php` and `booking-create.php` both call this. Both must pass the draft's
**whole** package list — `booking_draft_plan_ids( $draft )` — not just the mirrored
`plan_id`, or a multi-package order silently gets charged for one package. A `null`
result resets the draft and bounces to the service chooser.

### Payment settlement

1. `booking-create.php` re-validates the draft from scratch, re-resolves the price
   over the whole package list, inserts `bookings` + one `booking_items` row per
   package (at `qty` 1) in a transaction, promotes staged uploads
   from `data/uploads/_draft/<token>/` to `data/uploads/<booking_id>/`, records
   `uploaded_files` rows, then creates a Razorpay order.
2. `razorpay-verify.php` proves ownership (`hash_equals` against
   `$_SESSION['booking_confirmed']` / the session user), verifies the HMAC
   signature, checks the amount matches the stored total, blocks replay, then settles
   in one transaction (`payment_status='paid'`, `invoice_no`, `paid_at`,
   `service_records` row, notification mail).
3. `BOOKING_DEMO` short-circuits the gateway **only** outside production. This is
   intentional and load-bearing — do not "simplify" it.

### Booking id format

`BDCM-` + 6 uppercase hex chars = 3 random bytes (`helpers.php:96-98`). Only
~16.7M values, and seed data shows sequential-looking values (`BDCM-172401`).
It is **not** an authorisation token — ownership always requires a session check.

### Entitlement unlocks

`panel-guard.php` aggregates the customer's bookings into `$purchasedServices`.
`service_status_unlocks()` (`service-fields.php:42`) maps a booking status to
whether the purchased service is "unlocked" for the customer.
`customer_has_service( $slug )` is the gate. **Enforced on `service.php` and
`service-overview.php`; missing on the four release endpoints** (§11).

### Release lifecycle

`releases` rows belong to a distribution booking and a customer. Admin edits status,
tracks, platform links and an audit row in `release_history`. Platform URLs are
validated server-side with `FILTER_VALIDATE_URL` plus an `^https?://` check
(`release-save.php` admin, `:71-86`). Track numbers are renumbered server-side.

### Artist enquiry → reply

Public form posts to `includes/artist-enquiry-submit.php` (no auth, no CSRF, no
rate limit) → `artist_enquiries` row. Admin replies from the Enquiries screen, which
sets `status='replied'` **and then** calls `@mail()`; the mail result is ignored.

---

## 10. Known Technical Debt

| # | Location | Problem | Why it matters | Suggested improvement | Risk |
|---|---|---|---|---|---|
| TD-1 | `.env.production` + `.gitignore:5-7` | Production DB credentials are committed, and `.gitignore` explicitly re-includes the file with `!.env.production` | Anyone with the repo has live hosting credentials; also likely web-readable (no deny rule) | Rotate the credential, purge from history, remove the negation, add a dotfile deny rule | CRITICAL |
| TD-2 | `database/bdcmusic.sql:706` | The seeded admin row uses the well-known published bcrypt hash of `password`, and every seed row shares it | A dump loaded on a reachable host gives a full admin login | Change seeds to random passwords or a forced-reset flow; never ship a known hash | CRITICAL |
| TD-3 | `includes/download-file.php:7-40` | Serves the whole uploads tree to anyone; session is started and never read | Aadhaar/address-proof/portfolio documents are publicly retrievable by guessable path | Add session + ownership check joining `uploaded_files` → `bookings.customer_id` | HIGH |
| TD-4 | `includes/booking-create.php:64` | `(int) $_SESSION['user_id']` against varchar ids | Every paid booking by a logged-in customer is orphaned from that customer | Drop the cast | HIGH |
| TD-5 | `includes/auth.php:121,139,146` | Signup stores integer `1` for role/session | Every guard requires the string `'customer'`, so a fresh signup is bounced to login | Use `'customer'` | HIGH |
| TD-6 | `includes/customer/release-save.php:47-53` | Child `DELETE`/`INSERT` unscoped and outside a transaction; `rowCount()` never checked | Cross-tenant write + destruction of another customer's `release_artists` / `release_history`, reported as success | Transaction + `rowCount()` gate, mirroring `release-submit.php:35` | HIGH |
| TD-7 | 6 endpoints + 4 admin endpoints | No CSRF tokens outside the booking flow | Cross-site writes to profile, password, uploads, artist data, order status, releases | Reuse `helpers.php` CSRF helpers in `panel-config.php`/`admin-config.php` and verify per endpoint | HIGH |
| TD-8 | `assets/js/admin-enquiries.js:56-59`, `admin-artists.js:50-61,113,166-169` | `innerHTML` built from DB values with no `esc()`; `admin-enquiries.js` has no `esc()` at all | Stored XSS reachable from an unauthenticated public form → admin session | Use the existing `esc()` helper (already present in 4 sibling files) | HIGH |
| TD-9 | `includes/admin/artist-save.php:50-67` | Pricing delete-all + re-insert with no transaction | An empty `pricing` array wipes all pricing; a mid-loop failure leaves the artist with none | Transaction + explicit empty-array handling | HIGH |
| TD-10 | `bdc-admin/index.php:17-27`, `bdc-admin/orders.php:21-34` | Unbounded full-table order queries, result inlined into page JSON | Every customer PII ships in every admin page source; grows without limit | `LIMIT 10` + `COUNT`/`SUM` aggregates; drop the dead payload on `orders.php` | MEDIUM |
| TD-11 | 10 endpoints, 1 `error_log` call project-wide | Exceptions swallowed, `$e` discarded | A DB blip in `panel-guard.php:75-77` empties entitlements → customer is told they bought nothing | Log server-side; make entitlement failure explicit | MEDIUM |
| TD-12 | 4 endpoints (`release-save` ×2, `order-update`, `order-detail`, `releases-list`) | `'Database error: ' . $e->getMessage()` returned to the client | Leaks SQL, table and column names | Return a generic message, log the detail | MEDIUM |
| TD-13 | All endpoints except booking | Validation and most DB failures return HTTP 200 | Clients cannot distinguish "session expired" from "invalid input" from "DB down" | Adopt the status codes `booking-create.php` already uses | MEDIUM |
| TD-14 | `includes/auth.php:37-58` | No attempt counter, lockout, or delay; min password 6 chars | Unlimited online password guessing | Throttle per IP/email; raise the minimum | MEDIUM |
| TD-15 | Project-wide | No session cookie flags, no timeouts, roles trusted for the session lifetime | Weaker session baseline; demoted admins keep access | `session_set_cookie_params()` in `config.php` | MEDIUM |
| TD-16 | `includes/pagination.php:102-191` vs `shared.js:76-154` | Two identical pagination implementations, PHP one unused | Divergence risk; the PHP caller set was removed | Keep one | LOW |
| TD-17 | 14+ confirmed dead items (§12) | Unused components, endpoints, functions, JS, ~4.6 MB of images | Maintenance surface with no purpose; misleading inventory | Delete, or adopt deliberately | LOW |
| TD-18 | `includes/customer/release-submit.php`, `release-takedown.php` | Correct, owner-scoped, but unreachable from any UI | Widens attack surface for no benefit | Wire up or delete | LOW |
| TD-19 | `dashboard/includes/panel-config.php`, `bdc-admin/includes/admin-config.php` | Bare `json_encode()` inlined into `<script>` | Safe today only because `/` is escaped by default; invalid UTF-8 makes `json_encode` return `false` and emits `basePath: ,`, a JS syntax error that kills the whole config | Add `JSON_HEX_TAG\|JSON_HEX_AMP\|JSON_HEX_APOS\|JSON_HEX_QUOT\|JSON_INVALID_UTF8_SUBSTITUTE` | LOW |
| TD-20 | `includes/customer/*`, `includes/admin/*` | Mixed flat and enveloped JSON shapes; inconsistent key naming (`payment_status` vs `paymentStatus`, `total_spent` pre-formatted as a string) | Consumers must special-case each endpoint | Document the contract per endpoint; normalise later if a consumer is added | LOW |

---

## 11. Security Audit Findings

Classification: **CONFIRMED** = proven by the code; **POTENTIAL** = real risk whose
exploitability depends on runtime/deployment; **NEEDS VERIFICATION** = cannot be
settled from source alone.

### S-1 · Unauthenticated read of customer uploads — CONFIRMED · CRITICAL

**Evidence:** `includes/download-file.php:7-40`

```php
 7: session_start();                                   // started, never read
 9: $relativePath = $_GET['file'] ?? '';
16: $realBase      = realpath( dirname( __DIR__ ) . '/data/uploads' );
17: $requestedFile = realpath( dirname( __DIR__ ) . '/' . $relativePath );
19: if ( $requestedFile === false || strpos( $requestedFile, $realBase ) !== 0 ) { ... }
40: readfile( $requestedFile );
```

No `$_SESSION['user_id']` check, no role check, no comparison against
`uploaded_files`/`bookings.customer_id`. Called from
`customer-dashboard.js:312` and `customer-service.js:66`.

**Impact:** identity documents stored by customers are retrievable by an anonymous
visitor. Profile pictures are deterministic — `upload-profile-pic.php:55` names them
`<userId>.<ext>` — so every avatar is enumerable from a customer id, which is
already visible in `bookings.customer_id`. Booking uploads are
`time()_<8 hex>_<sanitized name>`, i.e. unguessable rather than protected, and there
is no expiry or revocation once a path leaks.

**Compounding:** `data/uploads/` has no `.htaccess` and there is no deny rule
anywhere, so files are *also* directly fetchable at `/bdcmusic/data/uploads/...`.

**Recommendation:** require a session; for admins allow any path, for customers
resolve the path through `uploaded_files → bookings.customer_id`, for guests require
`hash_equals` against `$_SESSION['booking_confirmed']` (the model already
documented at `booking-thank-you.php:9-12` and implemented at
`razorpay-verify.php:112-118`). Add a deny rule for `data/`.

**Risk:** CRITICAL. **Behaviour impact:** none for legitimate users; this restores
the intended model.

### S-2 · Production database credentials committed and probably web-readable — CONFIRMED · CRITICAL

**Evidence:** `.env.production` is tracked; `.gitignore:5-7` is

```
.env
.env.*
!.env.production
```

It contains real hosting `DB_HOST`/`DB_NAME`/`DB_USER`/`DB_USER`/`DB_PASS` values
(secret value withheld from this document) and `BASE_URL`. The only `.htaccess` in
the project is 22 lines of rewrite rules with no `FilesMatch` deny for `.env*`.

**Impact:** anyone with the repo — or anyone who can request `/.env.production` —
gets live database credentials. `RAZORPAY_KEY_SECRET` is read from the same env
family (`config.php:57-58`), so a live deployment storing it in the same shape would
expose the payment secret too.

**Recommendation:** treat the credential as compromised — rotate, purge from git
history, `git rm --cached`, remove the `!.env.production` negation, add
`<FilesMatch "^\.env">Require all denied</FilesMatch>`.

**Risk:** CRITICAL. **Behaviour impact:** none, once the new value is deployed.

### S-3 · Seeded admin account with a publicly known password hash — CONFIRMED · HIGH

**Evidence:** `database/bdcmusic.sql:706` seeds the admin row with
`$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi`, the widely published
bcrypt hash of the literal string `password`; the same hash is reused for all seed
rows (705, 707-716).

**Impact:** if the dump was ever loaded on a reachable host, that is a full admin
login, which combined with S-7 becomes full data exfiltration.

**Recommendation:** ship seeds with random or forced-reset passwords; never commit a
recognisable hash.

**Risk:** HIGH. **Behaviour impact:** none if seeds are not in production use.

### S-4 · Stored XSS: unauthenticated public enquiry → admin session — CONFIRMED · HIGH

**Evidence:** `includes/artist-enquiry-submit.php:24` stores the message with only
`trim()`; the endpoint has no auth, no CSRF, no rate limit. `includes/admin/enquiries-list.php`
returns the row. The sink is `assets/js/admin-enquiries.js:56-59`:

```js
'<td><strong>' + e.name + '</strong></td>' +
'<td>' + e.email + '</td>' +
'<td>' + (e.phone || '-') + '</td>' +
'<td title="' + (e.message || '').replace(/"/g, '&quot;') + '">' + msg + '</td>' +
```

`msg` is inserted as element content with no escaping; the attribute escapes only
`"`. `admin-enquiries.js` defines no `esc()` at all, while four sibling files do.

**Impact:** any anonymous visitor plants script that runs in an admin's session. With
S-7 there is no CSRF token to steal, and with no CSP (S-12) there is no backstop, so
the payload can drive the admin APIs directly.

**Recommendation:** route every interpolated value through `esc()`; additionally
`sanitize_input()` the message server-side and cap its length.

**Risk:** HIGH. **Behaviour impact:** none; only the rendering is corrected.

### S-5 · Stored XSS: self-service signup/profile name → admin Customers — CONFIRMED · HIGH

**Evidence:** `includes/auth.php:81` and `includes/update-profile.php:19` write
`users.name` from `trim($_POST['name'])` with no filtering. Sink:
`assets/js/admin-dashboard.js:190-199` — name, email and phone are concatenated into
`innerHTML` for the Customers table, even though the same file *does* define and use
`esc()` for the orders table (`:40-47, :96-108`).

**Impact:** an anonymous visitor signs up with a payload as their name and it fires
for every admin who opens Customers. `update-profile.php` lets a logged-in customer
re-plant it indefinitely (that endpoint also has no CSRF).

**Recommendation:** escape in `admin-dashboard.js:190-199`; keep escaping at the
render boundary (do not rely on input filtering).

**Risk:** HIGH. **Behaviour impact:** none.

### S-6 · Path containment in `download-file.php` fails open — CONFIRMED (logic defect) · MEDIUM

**Evidence:** `includes/download-file.php:19`

```php
if ( $requestedFile === false || strpos( $requestedFile, $realBase ) !== 0 ) { ... }
```

(a) `strpos(...) !== 0` is a **string-prefix** test with no directory-separator
boundary, so a sibling directory whose name merely starts with `data/uploads` would
pass. `data/` currently contains only `uploads/`, so that variant is **not**
exploitable today. (b) If `data/uploads` ever fails to exist, `realpath()` returns
`false`, `strpos($x, false)` coerces to `strpos($x, '')` which returns `0`, the
`!== 0` test **passes**, and the guard authorises any existing file under the project
root. That is a confirmed logic defect, reachable only if the directory is missing.

**Recommendation:** explicitly reject `$realBase === false`, compare against
`$realBase . DIRECTORY_SEPARATOR`, and use `str_starts_with`.

**Risk:** MEDIUM. **Behaviour impact:** none for well-formed requests.

### S-7 · No CSRF protection on state-changing endpoints — CONFIRMED · HIGH

**Evidence:** helpers exist and are correct — `includes/helpers.php:73-92`
(`generate_csrf_token`, `csrf_field`, `verify_csrf_token` via `hash_equals`) — and
are used only in `booking.php`, `includes/booking-create.php:52-56` and
`includes/razorpay-verify.php:103`. Absent from:

- `includes/update-profile.php`, `includes/change-password.php`,
  `includes/upload-profile-pic.php` (multipart POST — the easiest CSRF target)
- `includes/upload-artist-image.php`
- `includes/admin/artist-save.php`, `artist-delete.php`, `enquiry-reply.php`,
  `enquiry-update-status.php`, `order-update.php`, `release-save.php`
- `includes/customer/release-save.php`, `release-submit.php`, `release-takedown.php`

**Compounding factor:** the recurring `if (!$input) { $input = $_POST; }` fallback
means several endpoints that "require JSON" in fact accept a plain form body, so
attackers do not need CORS. Separately, no session cookie sets `SameSite` anywhere
(S-10), so the browser-default `Lax` is the only thing currently limiting the POST
attack, and it is not an enforced control.

**Recommendation:** emit the token in `dashboard/includes/panel-config.php` and
`bdc-admin/includes/admin-config.php`, send it from the JS, and verify it at the top
of each listed endpoint.

**Risk:** HIGH. **Behaviour impact:** none for same-origin clients.

### S-8 · Payment-taking booking detached from its customer — CONFIRMED · HIGH

**Evidence:** `includes/booking-create.php:64`

```php
$customerId = ! empty( $_SESSION['user_id'] ) ? (int) $_SESSION['user_id'] : null;
```

`users.id` is `varchar(20)` with values like `CUST-A1B2C3D4` (`auth.php:116`), so
the cast yields `0`. `booking-thank-you.php:43-45` documents exactly this trap:

> *"Both sides are the varchar customer id … Casting to int would make it 0 for
> every real customer and lock them out of their own booking."*

The same file compares as strings (`:46-48`). Downstream,
`customer_id = 0` matches no `users.id`, so `orders-list.php:24`,
`order-detail.php:51`, `service-overview.php:64` and `panel-guard.php:58` never
return the booking.

**Impact:** a **paid** booking is invisible in the customer's dashboard and unlocks
no service section — while `customer_type` is recorded as `'registered'`
(`:210`, `:378`), producing a self-contradictory row. Money is captured by Razorpay.

**Recommendation:** remove the cast. Compare as strings everywhere.

**Risk:** HIGH. **Behaviour impact:** restores intended behaviour; existing broken
rows are not repaired by the code change.

### S-9 · Cross-tenant write and audit-history destruction in customer release save — CONFIRMED · HIGH

**Evidence:** `includes/customer/release-save.php:47-53`

```php
$stmt = $pdo->prepare( 'UPDATE releases SET ... WHERE id = :id AND customer_id = :cid' );
$stmt->execute( [ ... ] );                                    // rowCount() never read
$pdo->prepare( 'DELETE FROM release_artists  WHERE release_id = :rid' )->execute( [ ':rid' => $id ] );
$pdo->prepare( 'DELETE FROM release_history  WHERE release_id = :rid' )->execute( [ ':rid' => $id ] );
$hstmt = $pdo->execute( ... 'Release Updated' ... );
```

Only the `UPDATE` is owner-scoped. `$id` comes from `intval($input['id'])` with no
ownership predicate and no transaction. Because the `UPDATE` silently affects 0 rows
for a foreign id, the endpoint proceeds to delete the victim's artists and history
and reports success. `release-submit.php:35` and `release-takedown.php:37` both check
`rowCount() > 0`; this is the only one of the three that does not. The response
message is derived from the request, so the response also confirms that a given
release id exists.

**Recommendation:** wrap in a transaction; treat `rowCount() === 0` on the
owner-scoped `UPDATE` as a 404/403 and return before touching child rows.

**Risk:** HIGH. **Behaviour impact:** none for legitimate owners.

### S-10 · Signup writes an integer role every guard rejects — CONFIRMED · HIGH (functional)

**Evidence:** `includes/auth.php:121` (INSERT role `1`), `:139`
(`$_SESSION['user_role'] = 1;`), `:146` (response `role => 1`). Guards compare
against the string: `dashboard/includes/panel-guard.php:21`,
`includes/customer/orders-list.php:10`, `releases-list.php:10`,
`order-detail.php:26`, `service-overview.php:30`, `customer/release-save.php:9`,
`release-submit.php:9`, `release-takedown.php:9`.

`1 !== 'customer'`, so a freshly registered customer is auto-logged-in and then
immediately bounced to `login` by `panel-guard.php:22`. The second login reads the
role from the DB and works. The `INSERT` itself is not a privilege-escalation issue
because MySQL stores the enum index `1` as the first element (`'customer'`), but it
should still be the literal string.

**Recommendation:** use `'customer'` at all three sites.

**Risk:** HIGH. **Behaviour impact:** fixes a broken signup path; no other flow
changes.

### S-11 · Release endpoints skip the entitlement gate the page enforces — CONFIRMED · MEDIUM-HIGH

**Evidence:** `dashboard/releases.php:13-16` redirects unless `$hasDistribution`
(from `panel-guard.php:81-82`). `includes/customer/releases-list.php:10-14` and
`release-save.php:9` check only `user_role === 'customer'`. `service.php:22` and
`service-overview.php:70-73` do it correctly.

**Impact:** any logged-in customer who never purchased distribution can read and
write releases through the APIs.

**Recommendation:** add `customer_has_service( 'digital-distribution' )` to the four
release endpoints.

**Risk:** MEDIUM-HIGH. **Behaviour impact:** none for customers who bought the
service; this closes the bypass.

### S-12 · Unescaped shared sink `statusBadgeHtml()` — POTENTIAL · MEDIUM

**Evidence:** `assets/js/shared.js:31-33`

```js
function statusBadgeHtml(status) {
    return '<span class="panel-status-badge status-' + status.toLowerCase() + '">' + status + '</span>';
}
```

No escaping in either the class attribute or the body, and no null guard — a null
status throws a `TypeError` and aborts the enclosing `map()`, leaving a
half-rendered table. Used by `customer-dashboard.js:64,217`,
`customer-releases.js:65`, `customer-service.js:127,260`. The sibling
`paymentBadgeHtml()` (`shared.js:44-53`) whitelists its class and escapes its text —
same file, opposite posture. There is also a correct, unused PHP twin at
`helpers.php:120-123`.

Every current caller passes a DB `enum` column, so this is **not** currently
exploitable; it becomes stored XSS the first time a caller passes free text — and
`customer-service.js:38-41` already had to invent `progressBadgeHtml()` for exactly
that reason.

**Recommendation:** port `paymentBadgeHtml`'s approach to `statusBadgeHtml`.

**Risk:** MEDIUM. **Behaviour impact:** none; badges render identically.

### S-13 · `javascript:` URLs accepted on admin-managed platform links — POTENTIAL · MEDIUM

**Evidence:** `assets/js/customer-releases.js:128` renders
`href="' + esc(l.url) + '"`. `esc()` neutralises quotes and angle brackets, so there
is no attribute breakout, but it does not constrain the **scheme**. The value comes
from `release_platform_links.url`, an admin-managed column. A `javascript:` value
executes on click in the customer's session. The dead `isExternalUrl()` at
`customer-releases.js:23-25` is the vestigial guard for this. The admin write path
*does* validate (`FILTER_VALIDATE_URL` + `^https?://`), so this is a defence-in-depth
gap that depends on that validation never being bypassed by a direct DB write or a
future code path.

**Recommendation:** enforce the scheme server-side on write and/or at render.

**Risk:** MEDIUM. **Behaviour impact:** none for legitimate `http(s)` links.

### S-14 · No rate limiting, CSRF, or anti-automation on the public enquiry form — CONFIRMED · HIGH

**Evidence:** `includes/artist-enquiry-submit.php` — no session requirement, no
`verify_csrf_token()`, no honeypot, no captcha, no throttle, no length cap on
`message`, and it accepts `application/x-www-form-urlencoded` (`:15-19`).

**Impact:** unbounded lead injection and spam, and a CSRF-driven vector for planting
S-4. Because the admin reply path (`:44,46,68-70`) mails the stored address with the
studio's `From:`, the host can also be used as a spam relay — it will fail SPF/DKIM
and eventually be blocklisted.

**Recommendation:** add CSRF + a honeypot + a throttle; cap lengths; send via
`Reply-To:` on the studio domain.

**Risk:** HIGH. **Behaviour impact:** intended rejections only; no existing
legitimate flow is affected.

### S-15 · No authentication throttling and weak password policy — CONFIRMED · MEDIUM

**Evidence:** `includes/auth.php:37-58` has no attempt counter, lockout or delay.
`includes/auth.php:102-105` and `includes/change-password.php:28-31` require only
6 characters. `change-password.php` correctly requires the current password (`:44`)
but does not require the new password to differ, is not rate-limited, and after
success (`:56`) neither regenerates the session nor invalidates the old one — so a
stolen session cookie survives a password reset.

**Recommendation:** throttle per IP/email, raise the minimum, regenerate the session
and invalidate on password change.

**Risk:** MEDIUM. **Behaviour impact:** new passwords under 6 chars would be
rejected (a real, intended change).

### S-16 · Logout does not delete the session cookie — CONFIRMED · LOW-MEDIUM

**Evidence:** `includes/logout.php:8-13` — `session_unset()`, `session_destroy()`,
then a redirect. No `setcookie()`.

**Impact:** the session id persists in the browser; on shared machines it is
recyclable, and there is no `use_strict_mode`. It is also the only endpoint that does
not return JSON, so `customer-dashboard.js:525` depends on a full navigation.

**Recommendation:** delete the cookie before redirecting.

**Risk:** LOW-MEDIUM. **Behaviour impact:** none.

### S-17 · No security headers anywhere — CONFIRMED · LOW

**Evidence:** `bdc-admin/includes/admin-header.php:7-9,18` sets only cache headers and
`<meta name="robots" content="noindex, nofollow">`. `header.php` sets none. The root
`.htaccess` is rewrite rules only.

**Missing:** `Content-Security-Policy` (the only real backstop for S-4/S-5),
`X-Content-Type-Options: nosniff`, `Referrer-Policy`, `X-Frame-Options` /
`frame-ancestors` (admin pages are clickjackable, and clickjacking plus S-7 is a
complete admin compromise), `Permissions-Policy`. No CDN tag carries an `integrity`
attribute.

**Risk:** LOW (defence in depth). **Behaviour impact:** adding CSP or
`frame-ancestors` can break third-party embeds — needs verification first.

### S-18 · No access control on sensitive paths — POTENTIAL · HIGH

**Evidence:** exactly one `.htaccess` exists (project root, 22 lines of rewrites).
There is no deny rule for:

| Path | Contents |
|---|---|
| `.env`, `.env.production` | DB credentials |
| `database/bdcmusic.sql` | full schema + seed rows including upload filenames |
| `data/uploads/` | customer identity documents |
| `includes/` | `config.php`, `download-file.php`, 20 more |
| `prompt.txt`, `package.json`, `webpack.config.js` | internal brief, stack disclosure |

`POTENTIAL` → **CONFIRMED** on any Apache without a parent-level deny rule.
**Needs verification** on the live host.

**Recommendation:** add deny rules for dotfiles, `database/`, `data/`, and
`prompt.txt`.

**Risk:** HIGH. **Behaviour impact:** none for the application.

### S-19 · `BASE_URL` falls back to the `Host` request header — POTENTIAL · LOW

**Evidence:** `includes/config.php:34-40`

```php
$basePath = env( 'BASE_URL', $protocol . $_SERVER['HTTP_HOST'] . '/bdcmusic/' );
```

Both committed env files set `BASE_URL`, so this is dormant. If a deployment omits
it, `$basePath` becomes attacker-controlled and is then used for the guard redirect
(`admin-guard.php:14`), every asset URL, and every JS API base — a Host-header
poisoning sink. `config.php:30` also has a precedence bug:
`$_ENV[$key] ?? getenv($key) ?: $default` parses as
`(($_ENV[$key] ?? getenv($key)) ?: $default)` because `??` binds tighter than `?:`,
so an intentionally-empty variable can never be read as empty.

**Risk:** LOW. **Behaviour impact:** none while `BASE_URL` is set.

### S-20 · Predictable and unreclaimed upload filenames — CONFIRMED · LOW

**Evidence:** `includes/upload-profile-pic.php:55` uses
`$userId . '.' . $ext` (fully deterministic, and S-1 serves it anonymously);
`includes/upload-artist-image.php:46` uses `'artist-' . uniqid() . '.webp'`
(`uniqid()` is `microtime`-derived) in a web-served `assets/images/artist/`
directory. Neither endpoint deletes a replaced file, so avatars and artist images
accumulate; profile images are stored raw with EXIF intact and no dimension cap,
despite `imageResizeAndConvert()` already existing in `helpers.php:127-180` and being
used by the artist path only.

**Risk:** LOW. **Behaviour impact:** filename changes invalidate existing stored
paths, so this needs a data migration.

### S-21 · Broken "Get This Package" CTAs — CONFIRMED · RESOLVED · MEDIUM (functional)

**Evidence:** `services/audio-video-services.php:176,193,210,227` call
`booking_preselect_url( 'video-production', 'basic-video' )`. `video-production` is
not a `services.slug` (valid: `artists-marketplace`, `audio-video`,
`online-offline-classes`, `digital-distribution`, `promotion`, `iprs`), and
`booking_preselect_url()` expects an **int** plan id — `(int)'basic-video'` is `0`, so
no `plan_id` is emitted. `booking.php:61-67` then gets `null` from
`booking_service()`, resets the draft, and shows the bare service chooser.

**Impact:** all four buttons are decorative. The same page also hand-writes four
video delivery tiers (₹10k/25k/50k/1,00,000) that exist in no table, cannot be
edited by an admin, and cannot be charged.

**Risk:** MEDIUM. **Behaviour impact:** fixing them changes where a customer lands —
that is the point, but it is a user-visible change requiring approval.

**Resolution:** `services/audio-video-services.php` was rewritten to render both
the A/V bundle cards and the four delivery tiers from `service_plans`
(`booking_plan_grid()` + `booking_rate_card()`); every tier now carries a real plan
id and a working `booking_preselect_url()` link. The `service_type` select for
`audio-video` was dropped from `includes/booking-registry.php`, and the page
includes `includes/plan-enquiry-modal.php` for the non-bookable enquiry plan.

### S-22 · `catch (Exception)` cannot catch `TypeError` on two admin endpoints — CONFIRMED · MEDIUM

**Evidence:** `includes/admin/enquiry-reply.php:21-23` and
`enquiry-update-status.php:21-23`

```php
$input = json_decode( file_get_contents( 'php://input' ), true );
$id    = intval( $input['id'] ?? 0 );
```

No `is_array()` guard and no `$_POST` fallback (unlike the other endpoints). A body
of `"x"` is valid JSON that decodes to a *string*, so `$input['id']` becomes a
non-numeric string offset — a `TypeError` on PHP 8, which `catch ( Exception $e )`
at `:73` does not catch. The request dies as an uncaught fatal, with a stack trace
if `display_errors` is on.

**Recommendation:** `if ( ! is_array( $input ) ) { $input = $_POST; }` and catch
`Throwable`.

**Risk:** MEDIUM. **Behaviour impact:** only converts a fatal into a clean 400.

### S-23 · SQL injection — NONE FOUND (verified clean)

Every request value reaches MySQL as a bound parameter, and
`db_connect()` sets `PDO::ATTR_EMULATE_PREPARES => false`, so binding is real and
correctly used (one placeholder per occurrence). The only SQL built from strings is
generated placeholder lists (`array_fill()` + `implode()` over integer or already-bound
values) and fixed placeholder lists in `panel-guard.php:47-54`. `IN (…)` lists and
`ORDER BY` are never built from input. `pagination.php:28` interpolates a
caller-supplied SQL *template* (`SELECT COUNT(*) … FROM (<sql>)`), which is a
contract risk for future callers but is currently only ever given a hardcoded literal.

### S-24 · `sanitize_input()` invites misuse — CONFIRMED · LOW

**Evidence:** `includes/helpers.php:13-15` is `trim(strip_tags((string)$value))`. It
does not escape, does not cap length, and leaves `"` and `'` intact. It is
misleadingly named for what it does and is not applied consistently:
`includes/artist-enquiry-submit.php:24` (message), `artist-save.php:29` (bio),
`order-update.php:112` (`record.notes`), `customer/release-save.php:29` (lyrics) and
`update-profile.php:19` (name) bypass it entirely.

**Risk:** LOW today (output is escaped at render), but it is the mechanism that
produced S-4, S-5 and TD-8. Consider renaming to `clean_text()` and keeping escaping
strictly at the render boundary.

### S-25 · Seed data exposes real-looking personal file names — CONFIRMED · LOW

`database/bdcmusic.sql:669-675` seeds `uploaded_files` with names such as
`sonia_pan.pdf`, `sonia_aadhaar.jpg`, `rahul_address.pdf`. Combined with the
sequential-looking booking ids and S-1, this documents the exact guessing pattern.
Remove or anonymise seed rows before the dump is shared anywhere.

---

## 12. Code Quality Findings

### DRY violations

| Item | Locations | Note |
|---|---|---|
| `esc()` helper | `admin-dashboard.js:6`, `admin-releases.js:22`, `customer-dashboard.js:10`, `customer-releases.js:13`, `customer-service.js:26`, plus an inline variant at `shared.js:48-50` | 6 copies; belongs in `shared.js` |
| Role guard | 13× in `includes/admin/*`, 6× in `includes/customer/*`, 3× in upload/update endpoints | ~20 copies of the same 3-line predicate |
| `session_start` + include preamble | every entry point | Convention, but a single bootstrap include would remove the drift that caused S-10 |
| `renderOrderPlan()` | `customer-dashboard.js:126` and `customer-service.js:165` | Same logic implemented twice |
| `render_meta_fields` | `customer-service.js:44-54` (defined, never called) and inlined at `:145-150` | Dead function plus an inline duplicate |
| Pagination window | `pagination.php:102-191` and `shared.js:76-154` | Line-for-line twins; PHP side unused |
| FAQ accordion | `app.js:321-339` and `shared.js:159-175` (`initFaqAccordion`, never called) | Dead copy of live code |
| JSON body parsing | `artist-save.php:21-24`, `order-update.php:43-46`, both `release-save.php`, `upload-artist-image.php`, `artist-enquiry-submit.php:15-19` | Same 2-line idiom 5× |
| `catch (Exception) → generic JSON` | 10+ endpoints | Not wrong, but should be one helper |
| Breadcrumb markup | hand-written in 15 pages; `includes/components/breadcrumb.php` exists and is **never used** | A safe component sits unused beside 15 divergent copies |
| Dashboard shell | `panel-nav` + mobile toggle + `<main>` repeated across 4 dashboard pages | Template not extracted |
| `DOMContentLoaded` registrations in `app.js` | `:63`, `:265`, `:287` | Three listeners instead of one |
| Redundant `config.php` require | `about-bdc-music.php:7`, `footer.php` | Already loaded by `header.php` |

### Other quality findings

- **Large/complex files:** `includes/booking-registry.php` (field definitions +
  validation + renderer, ~850 lines, three responsibilities in one file);
  `booking.php` (multi-step page, ~810 lines); `bdc-admin/includes/order-modal.php`;
  `assets/js/admin-dashboard.js` (589 lines, 6 features).
- **Poor separation of concerns:** SQL lives inside the endpoint or partial that needs
  it — `bdc-admin/index.php:17-26` contains a join that belongs in a query module.
  `includes/booking-registry.php` both defines the field spec and renders HTML from it.
- **Tight coupling:** the booking engine reads session state and uploads at every
  layer; `panel-guard.php` mixes auth, entitlement aggregation and redirects.
- **Error handling:** §10 TD-11/TD-12/TD-13, plus `catch (Exception)` rather than
  `Throwable` (S-22) and `intval()` used as validation — it silently turns `"abc"`
  into `0` and `"1abc"` into `1` instead of rejecting.
- **Inconsistent patterns:** flat vs enveloped JSON; `payment_status` vs
  `paymentStatus`; `total_spent` pre-formatted server-side while other money fields
  are raw; `app.js` and `admin-*.js` use different module styles.
- **Type safety:** no type declarations anywhere; `booking_resolve_selection()` returns
  a loosely-shaped array that callers destructure positionally; `$_POST` values are
  read with `??` defaults and then `intval`'d rather than validated.
- **Difficult-to-test code:** no seams — no dependency injection, global `$pdo`,
  superglobals read directly, output emitted inline with `echo`.

### Dead / unreachable code (all verified)

| Item | Location |
|---|---|
| Create-release form | `dashboard/releases.php:155-211` — `display:none`, 0 references → makes all of `release-save.php` unreachable |
| `includes/components/breadcrumb.php`, `faq.php`, `policy-checkbox.php` | 0 includes anywhere |
| `initFaqAccordion()` | `shared.js:159-175`, 0 call sites |
| `booking_cta()` | `includes/booking-marketing.php:205`, 0 call sites |
| `isExternalUrl()` | `customer-releases.js:23-25`, 0 call sites |
| `renderMetaFields()` | `customer-service.js:44-54`, 0 call sites |
| `customer_service_unlocked()` | `dashboard/includes/panel-guard.php:107-110`, 0 call sites |
| `status_badge_html()` | `helpers.php:120-123`, 0 call sites |
| `load_json_file()` / `save_json_file()` | `helpers.php:34-59`, 0 call sites |
| `render_pagination()` / `paginate_meta()` / `pagination_url()` | `pagination.php` — server callers removed |
| `BOOKING_STATUSES` | `helpers.php:63-69` — Title-case, contradicts the lowercase DB enum, unreferenced |
| `release-submit.php`, `release-takedown.php` | Live and correct, but called by nothing |
| `dashboard/customer.php`, `dashboard/provider.php` | Publicly reachable placeholder dashboards, no guard, no query, linked from nowhere — they look like the real portal one path away |
| `$allCats` | `artists/detail.php:10,52` — a dead query on every artist profile view |
| `$pageTitle` | `artists/index.php:52` and `artists/detail.php:42-43` — assigned **after** `header.php` has already emitted `<title>` |
| `audio-video-services.js`, `distribution-form.js`, `online-offline-classes-form.js` | Target selectors absent from the current markup |
| `admin-dashboard.js:162` `customersSearch` | Never assigned; `customers.php` has no search input although the endpoint supports one |
| `reply-modal.php:26-29` previous-reply block | Hard-coded hidden, never populated |
| `artist-modal.php:80` `#artist-modal-close-btn` | `admin-artists.js:161` binds only `#artist-modal-close` — **the Cancel button does nothing** |
| `admin-releases.js:117` track-number input | Rendered, ignored (`:288`), and `release-save.php:142-148` renumbers anyway |
| `admin-releases.js` sidebar toggle | Never initialised (contrast `:7-8` in the other three admin scripts) — the mobile menu is dead on that page |
| ~14 unreferenced images in `assets/images/` | ≈4.6 MB, including a 2.1 MB and a 1.7 MB orphan |

---

## 13. Performance Findings

Only code-supported observations; nothing is optimised prematurely here.

| # | Finding | Evidence | Impact |
|---|---|---|---|
| P-1 | A DB query on **every** public page render | `header.php:93` — `SELECT name, slug FROM artist_categories` inside the shared header, inlined into the nav of 22 pages | One extra round trip per page load, including `/privacy-policy` and `/terms-and-conditions`. Cacheable in-session |
| P-2 | ~7.6 MB of hero imagery above the fold | `index.php:27,33,39,45` — four PNGs (1.7–2.0 MB each), no `loading`, no `width`/`height` | Multi-megabyte LCP and layout shift on the highest-traffic page |
| P-3 | Four render-blocking third-party stylesheets, two to the same host | `header.php:31-37` — preconnects, then Outfit, Font Awesome 6.6.0, Swiper 11, and a second Google Fonts call (Fraunces + Inter) | Directly delays first paint on all 22 pages |
| P-4 | Swiper CSS on all public pages, initialised only on the homepage | `header.php:36` vs `footer.php:54` / `app.js` | Dead bytes on 21 pages |
| P-5 | `shared.js` shipped to every public page but dashboard-only in practice | `footer.php:55`; `initSidebarToggle` is called only from admin/customer dashboard scripts | Dead JS on 13 content pages |
| P-6 | Unbounded full-table order queries on the admin overview and orders pages | `bdc-admin/index.php:17-26`, `bdc-admin/orders.php:21-33` — no `LIMIT`, result inlined into the page via `admin-config.php:16` | Grows with the order table; ships all customer PII in page source; the `orders.php` copy is not used by its own JS |
| P-7 | `COUNT(*)` computed over an `ORDER BY`-bearing derived table on every paginated request | `pagination.php:28` | Scales poorly on large tables; a plain `COUNT(*)` without the inner `ORDER BY` is equivalent |
| P-8 | Correlated `COUNT(*)` per release row | `releases-list.php:68` | Recomputes what `releases-list.php:130-134` already returns as a `tracks` array |
| P-9 | Catalogue queried in a loop on the classes page | `services/online-offline-classes.php:45-54` — 4× `booking_plans()`; page total 6 queries vs 2 needed | Small, but avoidable |
| P-10 | No `loading="lazy"`, no `width`/`height`, no `srcset` on any below-the-fold image; large CSS background bitmaps (`_home.scss:26` 514 KB `banner.webp`, `_service.scss:68-88`) | — | Unbounded image transfer on long pages |
| P-11 | No cache headers on public pages, and a session cookie is issued to every anonymous visitor | `header.php:2-4` starts a session on all 22 pages | 13 content pages cannot be cached by any CDN/proxy |
| P-12 | `assets/dist/main.css` is git-ignored, so a fresh clone has **no stylesheet** | `.gitignore:2`; `header.php:38` hard-requires it. The on-disk copy is a **development** build (168 KB, `sourceMappingURL`, unminified) | A deploy from a clean clone renders unstyled |
| P-13 | No cache busting on `dist/main.css` | `header.php:38` uses a fixed path with no version or hash | Stale CSS after deploy |

**No N+1 query problem exists in the list endpoints** — child rows are batched per
page with a single `IN (…)` query (`helpers.php:264-292`, `artists-list.php:52`,
`releases-list.php:82-127`, `service-overview.php:80-108`). Preserve that pattern.

---

## 14. Testing Status

### Existing framework

**There is none.** No PHPUnit, no test directory, no CI configuration, no lint or
static-analysis config (`phpstan`, `psalm`, `phpcs`, `eslint`) anywhere in the repo.
`playwright ^1.63.0` is declared in `devDependencies` but **there are no test or spec
files** — the dependency is unused.

`package.json` exposes only `npm run dev` and `npm run build`. There is no `test`
script, so there is no command to run.

### Critical business logic with zero tests

Ordered by blast radius if it regresses:

1. **Payment settlement** — `includes/razorpay-verify.php`: signature verification,
   amount match, replay rejection, session-ownership proof, and single-transaction
   settlement. Nothing verifies that a tampered or replayed payload is refused.
2. **Price resolution** — `includes/booking-catalog.php`: plan/service/group
   matching, multi-line totals, paise arithmetic, quote mode. A regression here
   changes what customers are charged. The specific trap: every caller must pass
   the draft's whole `plan_ids` list, not just `plan_id`, or a multi-package order
   is charged for one package.
3. **The checkout state machine** — `includes/booking-session.php`: `booking_steps()`,
   `booking_retired_steps()`, `booking_step_satisfied()`,
   `booking_furthest_reachable()`. Nothing pins the per-service step list, which is
   exactly the kind of rule that gets broken invisibly (as S-21 already shows).
4. **Field validation and visibility** — `includes/booking-registry.php`:
   `booking_validate_details`, `booking_validate_contact`, `booking_field_visible`.
5. **Entitlement unlocks** — `service_status_unlocks()`, `customer_has_service()`.
6. **Authorization scoping** — every `WHERE customer_id = :cid` clause, and the
   `release-save.php` omission (S-9).
7. **Upload validation** — `includes/file-upload.php` MIME/size rejection.
8. **Escape/format helpers** — `booking_money()`, paise conversion, `booking_esc()`.

### Suggested coverage, in order

1. **Unit tests for the pure functions** (no DB needed): `booking_money`,
   `booking_to_paise`/`booking_from_paise`, `booking_resolve_selection` against a
   fixture catalogue, `booking_steps()` per service, `booking_field_visible()`,
   `generate_booking_id()` format, `sanitize_input`/`validate_email`.
2. **PHPUnit + a seeded test database** for the endpoints, asserting the things that
   are currently regressions: customer-id string comparison (S-8), `rowCount()`
   gating in `release-save.php` (S-9), entitlement enforcement (S-11), and CSRF
   rejection.
3. **A security smoke test** that walks the endpoint inventory and asserts every
   state-changing endpoint enforces method, auth, role and CSRF — this would have
   caught S-7, S-9, S-10 and S-11 automatically.
4. **Playwright end-to-end** for the two critical user journeys: the multi-step
   checkout in demo mode, and the dashboard order/release views. Playwright is already a
   declared dependency.
5. **CI** running PHP lint over all files plus the unit suite, so a parse error or a
   broken require cannot ship.

---

## 15. Refactoring Opportunities

Each row states whether behaviour can be preserved. Nothing here should be
implemented without explicit approval.

| # | Location | Current problem | Suggested approach | Expected benefit | Risk | Behaviour unchanged? |
|---|---|---|---|---|---|---|
| R-1 | `includes/helpers.php`, all 20+ guards | Role predicate copy-pasted, already drifted (S-10) | One `require_role( $role )` helper that exits with the right response type (redirect vs JSON) | Removes ~60 duplicated lines; makes the S-10 class of bug impossible | LOW | Yes — must preserve both the redirect and the 403 JSON shapes |
| R-2 | `assets/js/shared.js` | `esc()` duplicated in 5 files; `statusBadgeHtml` unsafe (S-12) | Move `esc()` into `shared.js`; rewrite `statusBadgeHtml` on `paymentBadgeHtml`'s pattern | One escaping primitive; closes S-12; makes S-4/S-5 a one-line fix | LOW | Yes |
| R-3 | `includes/download-file.php` | No auth, fails-open containment (S-1, S-6) | Session + ownership check joining `uploaded_files` → `bookings.customer_id`; `str_starts_with` with a separator | Closes the most serious finding in the codebase | MEDIUM | Yes for legitimate users; adds a redirect/error for direct anonymous links |
| R-4 | `includes/booking-create.php:64` | `(int)` cast on a varchar id (S-8) | Delete the cast | Restores paid bookings in customer dashboards | LOW | Yes — this is the intended behaviour |
| R-5 | `includes/auth.php` | Integer role literal (S-10) | `'customer'` at `:121`, `:139`, `:146` | Fixes the signup bounce | LOW | Yes |
| R-6 | `includes/customer/release-save.php` | Unscoped child writes, no transaction, no `rowCount()` (S-9) | Transaction + `rowCount()` gate, mirroring `release-submit.php:35` | Closes cross-tenant write | MEDIUM | Yes for owners |
| R-7 | `panel-config.php` / `admin-config.php` + endpoints | CSRF helpers exist but are unused (S-7) | Emit the token once per panel; verify in each state-changing endpoint | Closes CSRF across 10+ endpoints | MEDIUM | Yes, once JS sends the token |
| R-8 | `includes/admin/*` | Validation and most DB failures return HTTP 200 (TD-13) | Adopt `booking-create.php`'s status codes | Clients can distinguish failure classes | MEDIUM | **No** — `response.status` becomes meaningful; all clients currently ignore it, so verify each consumer |
| R-9 | `includes/admin/artist-save.php:50-67` | Destructive pricing replace with no transaction (TD-9) | Transaction + explicit empty-array handling | Prevents accidental pricing wipe | MEDIUM | **Partly** — an empty `pricing` array would start meaning "leave alone" rather than "delete all" |
| R-10 | 10 endpoints | Swallowed exceptions, no logging (TD-11) | Log `$e` server-side; keep the generic client message | Restores observability; removes the "you bought nothing" failure mode | LOW | Yes |
| R-11 | `includes/config.php` | No session cookie flags (TD-15); `env()` precedence bug (S-19) | `session_set_cookie_params()` once at bootstrap; parenthesise the `??`/`?:` expression | Stronger session baseline; correct empty-value handling | LOW | Mostly — `Secure` requires HTTPS everywhere |
| R-12 | `includes/pagination.php` / `shared.js` | Two identical implementations, PHP one dead (TD-16) | Keep the JS one; delete the PHP one and its helpers | Removes ~120 lines of duplication | LOW | Yes — `artists/index.php` is the only PHP caller, so it must be migrated to JS rendering first |
| R-13 | `header.php:93` | A query on all 22 pages (P-1) | Memoise the category list per request/session, or render it from a cached variable | One fewer round trip per page | LOW | Yes |
| R-14 | 20+ dead items (§12) | Unused components, endpoints, functions, JS | Delete in reviewable batches; adopt `breadcrumb.php` or delete all 15 hand-written copies | Smaller surface; honest inventory | LOW | Yes, except R-15 |
| R-15 | `services/audio-video-services.php:176-233` | Four broken CTAs and four unmanageable hand-written price tiers (S-21) | Create real `service_plans` rows and point the CTAs at them | Makes video tiers purchasable and admin-editable | MEDIUM | **Done** — the page is fully DB-driven (`booking_rate_card()`, `booking_plan_grid(..., 'group_key'=>'')`); the hand-written tiers and the broken `video-production` links are gone |
| R-16 | `includes/booking-registry.php` (~990 lines) | Field definitions, validation and rendering in one file | Split into `booking-fields` (data), `booking-validate`, `booking-render` | Testable units; clearer ownership | MEDIUM | Yes, if the field spec is preserved byte-for-byte |
| R-17 | `bdc-admin/index.php`, `bdc-admin/orders.php` | Unbounded queries inlined into page JSON (P-6) | `LIMIT 10` + aggregates; drop the dead payload on `orders.php` | Faster admin pages; stops shipping all customer PII in page source | MEDIUM | Yes for the overview; `orders.php` must switch fully to `orders-list.php` |
| R-18 | 22 pages | A session is started (and a cookie issued) for 13 anonymous content pages (P-11) | Start the session lazily, only where state is used | Enables CDN/proxy caching of content | MEDIUM | **No** — cookies would disappear from content pages, so any analytics/consent assumption must be checked |
| R-19 | `.htaccess` + build | No path denies, no committed production CSS, no cache busting (S-18, P-12, P-13) | Deny rules for dotfiles/`database/`/`data/`; commit a production-built `main.css`; add a content hash | Removes S-18; unblocks cache busting; a clean clone renders correctly | LOW | No UX change, but deploying a new `main.css` filename touches one line in `header.php` |
| R-20 | `includes/artist-enquiry-submit.php` | No CSRF/rate limit/honeypot (S-14) | CSRF token + honeypot + throttle + length caps | Stops spam injection and the stored-XSS delivery path | LOW | Yes for humans |

---

## 16. Safe Change Rules

### Never change without explicit approval

| Area | Why | Files |
|---|---|---|
| **Payment settlement logic** | Money movement; a subtle change can confirm unpaid bookings or reject paid ones | `includes/razorpay-verify.php`, the settlement block in `includes/booking-create.php` |
| **Price resolution and money arithmetic** | The single authority for what a customer is charged; paise rounding is deliberate | `includes/booking-catalog.php:309-382`, `booking_money`, `booking_to_paise`, `booking_from_paise` |
| **`BOOKING_DEMO` / `IS_PRODUCTION` logic** | Load-bearing safety interlock — an unset `APP_ENV` must refuse payment, not bypass the gateway | `includes/config.php:60-75` |
| **The `bookings` snapshot columns** | The booking row, not a join, is the historical money record; rewrites destroy auditability | `customer_*`, `plan_*`, `price`, `subtotal`, `addons_total` in `bookings` |
| **The checkout step machine** | Step list, gating and reachability drive the entire user flow and its "Step N of M" copy | `includes/booking-session.php`, `booking.php` |
| **Per-service field definitions** | They are simultaneously the form spec, the validation rules and the dashboard display model | `includes/booking-registry.php`, `includes/service-fields.php` |
| **Guest ownership proof** | Session-marker comparison, not id knowledge, is the security boundary | `booking-thank-you.php:46-56`, `razorpay-verify.php:112-118` |
| **Schema (`database/bdcmusic.sql`)** | No migration framework exists; hand edits are not reproducible and there is no version table | all |
| **`users.id` / `bookings.customer_id` typing** | Both are deliberately `varchar`; a "cleanup" to integers would break every customer link | see S-8 |
| **API response contracts** | ~14 JS files branch on these shapes; renaming a key silently breaks a screen | all `includes/admin/*`, `includes/customer/*`, `panel-config.php`, `admin-config.php` |
| **Route files and clean URLs** | `.htaccess` has no `RewriteBase` and `/bdcmusic/` is hard-coded in `header.php`; any rename breaks inbound links and SEO | `.htaccess`, `header.php` |
| **Anything user-visible** | UI, copy, spacing, colours, navigation behaviour and user flows are out of scope unless requested | all SCSS, all page markup, `booking*.php` |

### Handling rules for this repository

1. **Read `CODEBASE.md` first, then read the actual source.** The document is a map;
   the source is the truth. If they disagree, the source wins — fix the document.
2. **Make the smallest safe change.** Do not rewrite working code for style.
3. **Search before adding.** Check for an existing helper first; five copies of `esc()`
   and two of the pagination renderer already exist.
4. **Escape at the render boundary.** `sanitize_input()` is input filtering, not
   output encoding. Every `echo` of DB or request data in PHP and every `innerHTML`
   build in JS needs its own escaping.
5. **Preserve the two-layer auth model** when adding an endpoint: a role check inside
   the endpoint itself, not only in the page that calls it.
6. **New state-changing endpoints need CSRF** and a real HTTP status code from day
   one, using the existing helpers in `includes/helpers.php`.
7. **Never add a secret to the repository or to this document.**
8. **Keep this file current.** Any change to architecture, routes, APIs, data model,
   auth, business logic, dependencies or security posture requires a corresponding
   edit to the relevant section here — replacing outdated text rather than appending.
