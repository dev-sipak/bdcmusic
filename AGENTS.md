# AGENTS.md

BDC Music: procedural PHP 8.2 + MySQL, vanilla JavaScript, SCSS compiled by Webpack.
`CODEBASE.md` is the engineering brief (routes, tables, workflows). This file is the coding
standard. Follow it on every change, including small ones.

## Formatting

- Indent with **tab characters**, tab width **4**. Never indent with spaces. The only exception
  is alignment inside a string, heredoc, comment body or SQL continuation, where spaces are
  content rather than indentation.
- No trailing whitespace on any line. Exactly one newline at the end of the file.
- LF is the stored form. This Windows checkout shows CRLF because `core.autocrlf=true`; never
  rewrite a file's line endings just to change them.
- One blank line between logical blocks, none inside one.

### Blank lines that do not belong

- directly after an opening tag, `<?php` block, or `{`
- directly before a closing tag or `}`
- two or more in a row

```php
<ul class="feature-list">
	<?php foreach ( $features as $feature ) : ?>
		<li><?php echo htmlspecialchars( $feature ); ?></li>
	<?php endforeach; ?>
</ul>
```

### `<li>` and `<a>` stay on one line

Both elements are written on a single line, attributes separated by one space, with the text
inline. This is not optional and applies to new and edited markup:

```php
<li><a href="<?php echo $basePath; ?>about-bdc-music" class="<?php echo $isAbout ? 'active' : ''; ?>">About</a></li>
```

Other elements may stack one attribute per line when the tag would otherwise be unreadable, for
example `.learning-program-tabs` in `services/online-offline-classes.php`. The one exception to
the single-line rule is an `<a>` that wraps child elements: put its attributes on one line and
keep the children indented underneath, as the admin and panel navigation buttons do. Markup
already in the repository is left as it is until the surrounding code is reworked; do not
reformat untouched lines as a side effect of another change.

## PHP

- WordPress-style spacing: `function name( $arg, $arg ) {`, `if ( $x ) {`, `! $x`, `array()`.
- Escape on output with `htmlspecialchars( $value )`. Never echo raw database or request data.
- Guard clauses and early `return` over nesting.
- Every admin write endpoint checks the admin session, verifies a CSRF token, and answers with
  JSON. Reuse the existing helpers instead of writing new ones.
- Docblocks explain why, not what. Short `//` notes only where the reason is not obvious.
- No commented-out code, no banner comment separators.

## JavaScript

- Tabs, `const`/`let` (never `var`), double quotes, semicolons, `===`.
- Small page scripts run in an IIFE or a `DOMContentLoaded` handler and bail out early:
  `if ( ! tablist ) return;`.
- Progressive enhancement: the page must work with JavaScript disabled. Markup stays in the
  server-rendered HTML and JS only adds behaviour on top of it.
- Behaviour goes in `assets/js/`, never in an inline `onclick`. The artist ID Card and
  Certificate are standalone printable pages and keep their own `onclick="window.print()"`.
- No new CDN dependency.

## SCSS

- Tabs. Colours and spacing come from the custom properties in
  `assets/scss/abstracts/_variables.scss` (`var(--primary)`), never raw hex inside a component.
- Class names describe the element or pattern (`learning-program-tabs`, `stat-card`), not how it
  looks. An active state is `.is-active`, applied by JavaScript.
- Desktop-first, as the rest of the stylesheet is: base rules target desktop and
  `@media (max-width: ...)` blocks adjust downwards.
- `assets/dist/main.css` is generated. Never edit it, and never commit it.

## SQL

- Tabs for statement indentation; two-space continuation alignment inside `( ... )` is fine.
- Migrations must be idempotent, so use an `information_schema` check before altering a table.
- `database/bdcmusic.sql` is the fresh-install dump and must be kept in step with migrations.

## Before finishing

```bash
php -l <each touched PHP file>
node --check <each touched JS file>
npm run build
```

Then load every touched page and confirm: HTTP 200, no console errors, and the behaviour you
changed actually works. A change to markup is not done until it has been loaded in a browser.

Do not commit unless asked.

## Layout

| Path | Holds |
| --- | --- |
| `header.php`, `footer.php` | Shared public chrome |
| `includes/` | Shared helpers, session, config, endpoints under `includes/admin/` |
| `services/`, `bdc-admin/`, `dashboard/` | Public service pages, admin panel, customer panel |
| `assets/scss/`, `assets/js/`, `assets/dist/` | Styles, scripts, generated CSS |
| `database/` | Schema dump and migrations |
| `data/documents/` | Generated artist documents, never web-readable |