# Booking flow - implementation plan

## Decisions taken

1. **One service and one package per order.** `bookings.service_id` stays
   meaningful, so no schema migration, and the dashboards, invoices and fulfilment
   are unchanged. The switch is the single `$allowMulti = false` assignment in
   `booking.php`. Preselect still *replaces* the selection rather than adding to
   it, because an additive picker on a one-package order would silently do
   nothing. Once a package is chosen on the service page there is no way to
   change it in the checkout: the "Change your package" link, the related-packages
   block and the `form=packages` handler are all gone. The review step states
   `You can book one service at a time.` instead, so the constraint is explained
   rather than merely enforced.
2. **Service Category is the first step for A/V only.** `booking_steps()` puts
   `category` ahead of `details` for `audio-video` and leaves every other
   service's step list untouched. The answer is stored in
   `$_SESSION['booking_draft']['details']['service_category']`, and it decides the
   upload label, the accepted file types and the delivery formats in one pass.
   The step is enforced server-side, and a hand-crafted `?step=details` cannot
   skip past it: `booking_furthest_reachable()` clamps to the first unanswered
   step, and the details handler merges the stored category back over any posted
   value so a client cannot contradict what was already answered.
2. **Drop the add-ons concept entirely.** The step, the label, the summary row, the
   review row, the four add-on query helpers, `booking_addons()`,
   `booking_addons_meta()` and the `booking_addons` write are gone. The
   `service_addons` table is dropped along with the `booking_addons.addon_id`
   foreign key. The original four add-ons were deliberately NOT converted into
   packages. **`booking_addons` is now dropped outright** — it held no rows, and
   keeping an empty table plus a "read-only history" contract was only an
   invitation to read a table that has nothing in it. No endpoint returns an
   `addons` key any more, and the add-on sections are removed from both order
   modals and all three dashboard scripts. `bookings.addons_total` is kept as a
   NOT NULL column that new orders write as 0.
3. **No quantity.** No steppers, no `qty` on `booking_items`; it stays 1.
4. **Keep the progress `<ol>`** as the single progress indicator, now that the
   "STEP n OF m" counter and the step-word `h1` are gone.
5. **Keep the "Get This Package" copy** on the service-page CTAs.
6. **Payment is optional at runtime, not just at build time.** A booking is
   committed before any gateway call, so a missing or unreachable Razorpay costs
   the customer nothing: the order is saved `awaiting` with
   `payment_provider = 'manual'` and the checkout sends them to the confirmation
   page with a note instead of opening a payment window that cannot complete.

## Status

- **Phase 1 - DONE.** `STEP n OF m` and the step-word `h1` are gone. The `h1` is
  the service name; the page title is `Book <Service> - BDC Music`.
  `booking_step_position()` and the `.booking-step-count` CSS deleted, CSS rebuilt.
- **Phase 2 - DONE (superseded).** Preselect was additive when orders were
   multi-line; it now replaces, matching the one-package rule. The checkout-side
   picker that went with it has since been removed; see decision 1.
- **Phase 4 - DONE.** No add-ons step and no add-ons anywhere in the customer
   flow. A/V is 5 steps and starts on Service Category. `booking_retired_steps()`
   now redirects the dead `?step=addons`, `?step=package` and `?step=service` URLs
   to the first live step instead of rendering a step underneath a dead `step`
   parameter.
- **Phases 3, 5, 6 - DONE / NOT NEEDED.** With no quantity there is no basket page
  to build. `booking_items.qty` stays hardcoded to 1. The CTA copy is
  deliberately left as "Get This Package".

## Also fixed

- **A booking could not be placed at all with no gateway configured.** The old
  flow created the Razorpay order *inside* the same block that inserted the
  booking, so an unconfigured gateway meant a 500 and no order. Order creation and
  payment are now separate stages; see §9 "Payment settlement" in `CODEBASE.md`.
- **A required file re-triggered its own error.** `booking_validate_details()`
  treated "no `$_FILES` entry" as "no file attached", so a customer who had already
  attached a document on an earlier pass and then edited another answer was told
  the document was missing. It now takes the draft's staged uploads as a fourth
  argument and only fails when neither a new file nor a staged one exists.
- **Offline orders were filed as card payments.** `payment_provider` was hardcoded
  to `razorpay`; it is now `manual` when no gateway is available, matching what
  quote orders already did.
- **A signed-in customer could never leave the Details step.** The `details` POST
  handler redirected to a hardcoded `contact` step. Once the contact step is
  skipped that URL is not in `$steps`, so the guard above clamped the customer
  back to Details and the Continue button did nothing. Both the `details` and
  `contact` handlers now redirect through `booking_next_step( $steps, … )`, the
  helper that was already defined but unused.
- **A later step could contradict the Service Category answer.** The details
  handler seeded `service_category` from the draft only when the request omitted
  it, so a hand-crafted post could restate it as the other value and have the
  stored booking, its upload rules and its delivery formats all disagree with the
  answer the customer gave. The draft is now authoritative: the stored value is
  written back over the request before validation, and again over the result, so
  the choice made on the category step is the one that is kept.

## Later work in this pass

- **Contact step skipped for signed-in customers.** `booking_steps()` drops
  `contact` when the customer is signed in and their account has an email.
  `booking_account_contact()` in `includes/booking-session.php` pre-fills the
  draft before the step list is built, and the review block shows the account
  name/email/phone with a "From your account" note and no Change link, because
  there is no step to link to.
- **One release, not one EP.** The `releases.type` enum is now `single` /
  `album`; EP rows and their `release_tracks` links are gone. The four
  multi-package seed bookings were flattened into one package each
  (`BDCM-2B0005` 11500, `BDCM-3C0009` 8999, `BDCM-3C0011` 17999,
  `BDCM-3C0012` 19999) in both `database/bdcmusic.sql` and the live database, so
  no booking has more than one `booking_items` row.
- **Email notifications and customer accounts.** `includes/notifications.php`
  sends PHP `mail()` notifications; `includes/customer-accounts.php` normalises a
  contact, finds or creates the `customer` user and links the booking. A paid
  booking now creates/links the account and mails the order; quote and enquiry
  flows notify without creating an account.
- **Order date and time.** Admin and customer order lists, details and modals
  format `b.created_at` as `%Y-%m-%d %H:%i` and the labels read "Date & Time".
- **Font Awesome replaced by Lucide.** Pinned to
  `https://cdn.jsdelivr.net/npm/lucide@1.48.0/dist/umd/lucide.min.js`, all
  static FA classes converted to `data-lucide`, `shared.js` renders both the
  initial page and later DOM insertions, and the handful of social marks that
  have no Lucide equivalent come from `includes/brand-icons.php`. New rules live
  in `assets/scss/base/_icons.scss`.

## Verification

PHP 8.2 lint clean across all 105 tracked PHP files, `node --check` clean on all
17 tracked scripts, `npm run build` green, and the HTTP-level assertions below all
passing against the live WAMP site.

An earlier pass of 66 assertions, retained here as the record of the offline and
add-on work:

- 25 on the `artists-marketplace` offline flow (single package, no "Add the
  services you need" / "You might also need" copy, `payment_required: false`,
  a real `payment_note`, and a saved order with `payment_status = awaiting`,
  `payment_provider = manual` and a NULL `razorpay_order_id`).
- 14 on the `audio-video` flow (grouped option cards, the media switch hooks, a
  real multipart upload promoted to `uploaded_files`, and — the actual bug — a
  resubmit *without* re-sending the file not being blocked).
- 27 across the admin and customer panels (no add-on keys in any JSON payload, the
  orders table header has no Phone column while the order modal still does, and
  `admin@bdcmusic.in` absent from `customers-list.php`).

Server-side option filtering was also checked directly: a Video order posting
`MP3` stores only `MP4`, and an Audio order posting `MP4` stores only the audio
formats.

The Service Category step was then driven end to end, as a guest, over HTTP:

- A fresh session lands on **Choose a Service Category** for A/V, and a
  hand-crafted `?step=details`, `?step=contact`, `?step=review` or
  `?step=payment` is clamped back to it.
- An empty category post returns `Please select service Category.`
- Answering `Video` moves to Details; posting `MP4` *and* `MP3` stores only
  `MP4`.
- The review page reads `Video` / `MP4`, carries no "Change your package" link,
  and does carry `You can book one service at a time.`
- **Tamper check:** with `Video` already stored, posting
  `service_category=Audio` on the details form leaves the stored category as
  `Video` and the formats as `MP4`, so the request cannot restate an answer
  already given.
- Every other service still starts on Details with its previous step list:
  IPRS and Distribution / Marketplace / Classes are Details / Contact / Review /
  Payment, and Promotion (a quote service) is Details / Contact / Review.

The booking was also placed against the live database to prove the values are
persisted, not just rendered: order `BDCM-5CCDFF` stored category `Audio`,
formats `["MP3"]`, service type `Audio` and plan `Basic`. The test rows and the
test account were deleted afterwards, leaving the bookings table empty.

The contact-step skip was then driven through a real browser, which is how the
dead `contact` redirect surfaced. On a signed-in customer, for every service, the
progress bar is Details / Review / Payment (Details / Review for a quote
service) and Continue moves Details straight to Review. The review block read
`Rahul Sharma / rahul@example.com / 9876543210`, had no
`a[href*="step=contact"]`, and showed the "From your account" note. The same run
as a guest kept the four-step progress and went Details -> Contact -> Review.

Date & time was confirmed on the customer orders table, and the Lucide sweep was
checked on every public page, the admin pages, the customer pages and the
injected order modals: no `fa-` class or Font Awesome CDN link survives
(`git grep -E "fa-|fontawesome|Font Awesome"` is empty), and no
`[data-lucide]` element is left unrendered or visible at zero size.

## A/V rate cards: four bundles bookable, twelve groups reference only

The A/V question is answered: **the four studio bundles are the only orderable
packages, and the twelve rate-card groups stay on the page as a reference price
list.**

How that was done, rather than by hardcoding plan ids or a service slug:

- New `service_plans.is_orderable` column, `NOT NULL DEFAULT 1`, so every other
  service's plans keep behaving exactly as before. The 48 A/V rate-card rows are
  set to `0`; the 4 bundles stay `1`, and `is_default` is back on id `1`
  (Basic, Rs. 11,500), which is what the checkout already opened with.
- `booking_resolve_selection()` refuses a plan with `is_orderable = 0`, so
  hiding the button is the polite half. A hand-crafted `?plan_id=91` or a posted
  `plan_ids[]=91` is rejected rather than priced. `booking_default_plan()` also
  requires it, so a reference-only plan can never be auto-selected.
- `booking.php` drops those ids from the preselect path, and a package already
  chosen on the service page carries into the checkout unchanged.
- `booking_rate_card()` renders an orderable cell as a buy link and an
  enquiry cell as "Enquire Now", but a reference-only cell as plain text with
  neither.
- The admin plan modal has a **Bookable** checkbox, the packages table shows a
  **Reference only** badge, and `plan-save.php` / `plans-list.php` carry the
  column. An older admin client that does not send `is_orderable` still creates
  bookable plans, so nothing new can silently arrive unbookable.
- The copy under the video rate card no longer claims each price is bookable.

Verified: the A/V page has exactly 4 booking links (ids 1-4), 48 reference
cells, 0 enquiry buttons and 4 "Get This Package" CTAs; `?plan_id=91` is ignored
and falls back to the default; IPRS/Classes/Distribution/Marketplace are
untouched.
