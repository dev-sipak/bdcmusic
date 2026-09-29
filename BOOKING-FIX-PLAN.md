# Booking flow - implementation plan

## Decisions taken

1. **One service per order**, as many packages of that service as the customer
   wants. `bookings.service_id` stays meaningful, so no schema migration, and the
   dashboards, invoices and fulfilment are unchanged.
2. **Drop the add-ons concept entirely.** The step, the label, the summary row, the
   review row, the four add-on query helpers, `booking_addons()`,
   `booking_addons_meta()` and the `booking_addons` write are gone. The
   `service_addons` table is dropped along with the `booking_addons.addon_id`
   foreign key. The original four add-ons were deliberately NOT converted into
   packages. `booking_addons` is kept as read-only history for the six orders that
   were placed while add-ons still existed, and `bookings.addons_total` is kept as
   a NOT NULL column that new orders write as 0.
3. **No quantity.** No steppers, no `qty` on `booking_items`; it stays 1. Removing
   a package is done by un-ticking it in the block below the form.
4. **Keep the progress `<ol>`** as the single progress indicator, now that the
   "STEP n OF m" counter and the step-word `h1` are gone.
5. **Keep the "Get This Package" copy** on the service-page CTAs.

## Status

- **Phase 1 - DONE.** `STEP n OF m` and the step-word `h1` are gone. The `h1` is
  the service name; the page title is `Book <Service> - BDC Music`.
  `booking_step_position()` and the `.booking-step-count` CSS deleted, CSS rebuilt.
- **Phase 2 - DONE.** Preselect is additive (`?plan_id=N` adds, `?plan_ids[]=`
  takes a list), foreign-service and enquiry plans are refused, re-clicking a
  chosen package does not duplicate it, and
  `booking_service_allows_multi_plan()` is true for every package-mode service
  instead of a hardcoded `audio-video` slug. The silent single-line truncation is
  now a visible error.
- **Phase 4 - DONE.** No add-ons step and no add-ons anywhere in the customer
  flow. A/V is 4 steps and starts on Details instead of Add-ons.
  `booking_retired_steps()` now redirects the dead `?step=addons`,
  `?step=package` and `?step=service` URLs to Details instead of rendering
  Details underneath a dead `step` parameter.
- **Phases 3, 5, 6 - DONE / NOT NEEDED.** With no quantity there is no basket page
  to build: the block below the form is the order, and un-ticking a package is the
  removal. `booking_items.qty` stays hardcoded to 1, which is correct for a list of
  distinct packages. The CTA copy is deliberately left as "Get This Package".

## Fixed while doing this

- **`booking-create.php` only charged the first package.** It called
  `booking_resolve_selection()` with only `$planId`, never the draft's `plan_ids`,
  so the `$planIds` argument defaulted to empty and the function fell back to the
  single mirrored `plan_id`. A three-package order would have been charged for one
  package. It now passes `booking_draft_plan_ids( $draft )`, which is the same call
  `booking.php` already made. Verified: a 3-line A/V order prices at
  Rs. 8,500 (1,000 + 2,500 + 5,000) with `bookings.price = subtotal = 8,500`,
  `addons_total = 0`, three `booking_items` rows all at `qty = 1`, and a
  2-line IPRS order at Rs. 7,498. Confirmed end to end in a browser: walking
  details → contact → review → payment for two A/V packages renders
  `Pay Rs. 6,000` (1,000 + 5,000), not the first line alone.

## Verification

`36/36` in the Playwright harness, PHP lint clean across every touched file, the
schema dump reloads into a scratch database with no `service_addons` and the
correct `booking_addons` constraint, and no page on the site returns 500.
Live DB after the change: `services` 6, `service_plans` 78, `booking_items` 15,
`bookings` 16, `booking_addons` 8 (history), `service_addons` gone.

## Still open

Nothing. The A/V question was answered: **the four studio bundles are the only
orderable packages, and the twelve rate-card groups stay on the page as a
reference price list.**

How that was done, rather than by hardcoding plan ids or a service slug:

- New `service_plans.is_orderable` column, `NOT NULL DEFAULT 1`, so every other
  service's plans keep behaving exactly as before. The 48 A/V rate-card rows are
  set to `0`; the 4 bundles stay `1`, and `is_default` is back on id `1`
  (Basic, Rs. 11,500), which is what the checkout already opened with.
- `booking_resolve_selection()` refuses a plan with `is_orderable = 0`, so
  hiding the button is the polite half. A hand-crafted `?plan_id=91` or a posted
  `plan_ids[]=91` is rejected rather than priced. `booking_default_plan()` also
  requires it, so a reference-only plan can never be auto-selected.
- `booking.php` drops those ids in both the preselect path and the
  `form=packages` handler, and the related-packages block lists only orderable
  plans.
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
and falls back to the default; the related-packages block offers `1,2,3,4`;
IPRS/Classes/Distribution/Marketplace are untouched.
