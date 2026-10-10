<?php
/**
 * Artist ID Card and Certificate generation.
 *
 * When the studio verifies a BDC Marketplace registration it assigns the artist a
 * code and issues two documents: an ID Card and a Certificate of Registration.
 * Both are written as standalone HTML files under data/documents/<code>/ and are
 * served through includes/artist-document.php, never directly: the .htaccess in
 * the project root denies web access to data/, so the only way to a document is
 * the authenticated endpoint, which checks that the person asking owns the
 * registration or is an admin.
 *
 * HTML rather than PDF on purpose. There is no PDF library in this project and no
 * package manager step to add one, so the documents are self-contained pages with
 * print stylesheet rules: they open in any browser, print to PDF from there, and
 * need nothing installed to stay readable. Nothing here depends on that choice --
 * artist_document_path() and artist_document_html() are the only two things the
 * rest of the code touches, and a PDF renderer could be dropped in behind them.
 */

require_once __DIR__ . '/artist-registrations.php';

/**
 * The document kinds that are generated for a verified artist.
 *
 * @return array Map of kind => column holding its path in artist_registrations.
 */
function artist_document_kinds() {
	return array(
		'id-card'     => 'id_card_path',
		'certificate' => 'certificate_path',
	);
}

/**
 * Allocate the next artist code.
 *
 * BDCA-YYYY-NNNN, with the sequence restarting each year. The year is read from
 * the clock rather than passed in, because a code is only ever issued while an
 * admin is verifying something right now.
 *
 * Uniqueness is decided by the unique key on artist_code, not by a MAX() query:
 * two admins verifying at the same moment would otherwise both read the same
 * highest number and one insert would fail. The caller retries with the next
 * number instead.
 *
 * @param PDO  $pdo
 * @param int  $attempt Zero for the first try.
 * @return string|null Null when the code is taken and a new attempt is needed.
 */
function artist_registration_code( PDO $pdo, $attempt = 0 ) {
	$year = (int) date( 'Y' );

	$stmt = $pdo->prepare(
		"SELECT COUNT(*) FROM artist_registrations WHERE artist_code LIKE :prefix"
	);
	$stmt->execute( array( ':prefix' => 'BDCA-' . $year . '-%' ) );
	$issued = (int) $stmt->fetchColumn();

	$number = $issued + 1 + (int) $attempt;

	return sprintf( 'BDCA-%d-%04d', $year, $number );
}

/**
 * Where a document lives, relative to the project root.
 *
 * @param string $code Artist code.
 * @param string $kind One of artist_document_kinds().
 * @return string
 */
function artist_document_path( $code, $kind ) {
	$safeCode = preg_replace( '/[^A-Za-z0-9\-]/', '', (string) $code );

	return 'data/documents/' . $safeCode . '/' . $safeCode . '-' . $kind . '.html';
}

/**
 * Absolute path for a stored document path.
 *
 * @param string $relativePath Path as stored in the registration row.
 * @return string
 */
function artist_document_absolute_path( $relativePath ) {
	return dirname( __DIR__ ) . '/' . ltrim( str_replace( '\\', '/', (string) $relativePath ), '/' );
}

/**
 * Escape for the document HTML.
 *
 * @param mixed $value
 * @return string
 */
function artist_document_e( $value ) {
	return htmlspecialchars( (string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );
}

/**
 * Lines shared by both documents: the studio's identity and the issue date.
 *
 * @param array $reg Registration row.
 * @return array
 */
function artist_document_shared_fields( array $reg ) {
	$issuedAt = $reg['reviewed_at'] ?? $reg['documents_generated_at'] ?? date( 'Y-m-d H:i:s' );

	return array(
		'code'     => (string) $reg['artist_code'],
		'name'     => (string) $reg['artist_name'],
		'category' => artist_registration_category_label( $reg['category_slug'] ?? '' ),
		'city'     => (string) ( $reg['city'] ?? '' ),
		'experience' => (string) ( $reg['experience'] ?? '' ),
		'plan'     => (string) ( $reg['plan_name'] ?? '' ),
		'issued'   => date( 'F j, Y', strtotime( (string) $issuedAt ) ),
		'issuedId' => $issuedAt,
	);
}

/**
 * Page furniture for a generated document.
 *
 * @param string $title    Document title.
 * @param string $subtitle Line under the title.
 * @param string $body     Inner HTML, already escaped by the caller.
 * @param bool   $compact  True for the ID Card, which is a smaller page.
 * @return string
 */
function artist_document_page( $title, $subtitle, $body, $compact = false ) {
	$baseCss = '
		* { box-sizing: border-box; }
		body {
			margin: 0;
			padding: 32px;
			background: #f4f4f6;
			font-family: "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
			color: #1c1c21;
		}
		.sheet {
			margin: 0 auto;
			background: #fff;
			border: 1px solid #e3e3e8;
			box-shadow: 0 18px 50px rgba(0, 0, 0, .08);
			position: relative;
			overflow: hidden;
		}
		.sheet-inner { padding: 48px 56px; }
		.brand { display: flex; align-items: center; gap: 12px; }
		.brand-mark {
			width: 46px; height: 46px; border-radius: 12px;
			background: #e4002b; color: #fff;
			display: flex; align-items: center; justify-content: center;
			font-weight: 800; letter-spacing: .5px; font-size: 15px;
		}
		.brand-name { font-weight: 700; letter-spacing: .4px; font-size: 15px; }
		.brand-sub { font-size: 11px; letter-spacing: 2.4px; text-transform: uppercase; color: #7a7a86; }
		.accent { height: 4px; background: #e4002b; }
		h1 { margin: 0 0 6px; font-size: 30px; letter-spacing: .5px; }
		.subtitle { margin: 0 0 28px; color: #61616d; font-size: 14px; }
		.grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px 32px; }
		.field-label {
			font-size: 10px; letter-spacing: 1.6px; text-transform: uppercase;
			color: #8a8a96; margin-bottom: 4px;
		}
		.field-value { font-size: 15px; font-weight: 600; }
		.footer {
			margin-top: 40px; padding-top: 20px; border-top: 1px solid #e6e6ec;
			display: flex; justify-content: space-between; align-items: flex-end; gap: 24px;
		}
		.footer-note { font-size: 11px; color: #8a8a96; max-width: 60ch; line-height: 1.6; }
		.stamp { text-align: right; }
		.stamp strong { display: block; font-size: 15px; }
		.stamp span { font-size: 11px; color: #8a8a96; }
		.print-btn {
			position: fixed; top: 16px; right: 16px;
			background: #e4002b; color: #fff; border: 0; border-radius: 999px;
			padding: 11px 20px; font-size: 13px; font-weight: 600; cursor: pointer;
			font-family: inherit;
		}
		@media print {
			body { background: #fff; padding: 0; }
			.print-btn { display: none; }
			.sheet { border: 0; box-shadow: none; }
		}';

	$compactCss = '
		.sheet { width: 640px; }
		.sheet-inner { padding: 32px 36px; }
		.id-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; }
		.photo {
			width: 108px; height: 132px; border-radius: 12px; border: 1px dashed #d6d6de;
			background: #fafafc; display: flex; align-items: center; justify-content: center;
			color: #a0a0ac; font-size: 11px; text-align: center; flex-shrink: 0;
		}
		.id-code {
			display: inline-block; margin-top: 10px; padding: 6px 14px;
			background: #e4002b; color: #fff; border-radius: 999px;
			font-size: 13px; font-weight: 700; letter-spacing: 1.4px;
		}
		.id-name { margin: 14px 0 4px; font-size: 26px; font-weight: 800; letter-spacing: .3px; }
		.id-role { color: #61616d; font-size: 14px; }
		.id-rows { margin-top: 26px; display: grid; gap: 14px; }
		.id-row { display: flex; justify-content: space-between; gap: 18px; border-bottom: 1px dashed #e6e6ec; padding-bottom: 10px; }
		.id-row span:first-child { font-size: 11px; letter-spacing: 1.4px; text-transform: uppercase; color: #8a8a96; }
		.id-row span:last-child { font-weight: 600; font-size: 14px; text-align: right; }
		.certificate-body { text-align: center; }
		.certificate-body .intro { max-width: 62ch; margin: 0 auto 34px; color: #4a4a56; font-size: 14px; line-height: 1.8; }
		.certificate-name { font-size: 38px; font-weight: 800; letter-spacing: .5px; margin: 0 0 8px; }
		.certificate-role { font-size: 16px; color: #e4002b; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 30px; }
		.certificate-meta {
			margin: 0 auto; max-width: 460px; text-align: left;
			border-top: 1px solid #e6e6ec; border-bottom: 1px solid #e6e6ec; padding: 22px 0;
			display: grid; gap: 12px;
		}
		.cert-row { display: flex; justify-content: space-between; gap: 18px; }
		.cert-row span:first-child { font-size: 11px; letter-spacing: 1.4px; text-transform: uppercase; color: #8a8a96; }
		.cert-row span:last-child { font-weight: 600; font-size: 14px; text-align: right; }';

	$sheetWidth = $compact ? 'width: 640px;' : 'width: 820px;';

	return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>' . artist_document_e( $title ) . ' | BDC Music</title>
<style>' . $baseCss . $compactCss . '.sheet { ' . $sheetWidth . ' }' . '</style>
</head>
<body>
<button class="print-btn" type="button" onclick="window.print()">Print / Save as PDF</button>
<div class="sheet">
	<div class="accent"></div>
	<div class="sheet-inner">
		<div class="brand">
			<div class="brand-mark">BDC</div>
			<div>
				<div class="brand-name">BDC Music</div>
				<div class="brand-sub">Artist Registry</div>
			</div>
		</div>
		' . $body . '
	</div>
</div>
</body>
</html>';
}

/**
 * The ID Card for one registration.
 *
 * @param array $reg Registration row.
 * @return string
 */
function artist_id_card_html( array $reg ) {
	$f = artist_document_shared_fields( $reg );

	$rows = array(
		'Artist ID'      => $f['code'],
		'Registered as'  => $f['category'],
		'Based in'       => $f['city'],
		'Experience'     => $f['experience'],
		'Package'        => $f['plan'],
		'Date of issue'  => $f['issued'],
	);

	$rowsHtml = '';
	foreach ( $rows as $label => $value ) {
		if ( trim( (string) $value ) === '' ) {
			continue;
		}

		$rowsHtml .= '<div class="id-row"><span>' . artist_document_e( $label ) . '</span>'
			. '<span>' . artist_document_e( $value ) . '</span></div>';
	}

	$body = '<div class="id-head">'
		. '<div>'
		. '<div class="brand-sub" style="margin-top:26px;">Verified Artist</div>'
		. '<div class="id-code">' . artist_document_e( $f['code'] ) . '</div>'
		. '<div class="id-name">' . artist_document_e( $f['name'] ) . '</div>'
		. '<div class="id-role">' . artist_document_e( $f['category'] ) . '</div>'
		. '</div>'
		. '<div class="photo">Studio<br>photo</div>'
		. '</div>'
		. '<div class="id-rows">' . $rowsHtml . '</div>'
		. '<div class="footer">'
		. '<div class="footer-note">This ID card is issued by BDC Music to a verified '
		. 'artist on the BDC Artists Marketplace. It carries the artist code above and is valid '
		. 'only while the registration remains verified with the studio.</div>'
		. '<div class="stamp"><strong>BDC Music</strong>'
		. '<span>Issued ' . artist_document_e( $f['issued'] ) . '</span></div>'
		. '</div>';

	return artist_document_page(
		'Artist ID Card - ' . $f['name'],
		'Verified artist on the BDC Artists Marketplace',
		$body,
		true
	);
}

/**
 * The Certificate of Registration for one registration.
 *
 * @param array $reg Registration row.
 * @return string
 */
function artist_certificate_html( array $reg ) {
	$f = artist_document_shared_fields( $reg );

	$rows = array(
		'Artist ID'     => $f['code'],
		'Category'      => $f['category'],
		'Based in'      => $f['city'],
		'Package'       => $f['plan'],
		'Date of issue' => $f['issued'],
	);

	$rowsHtml = '';
	foreach ( $rows as $label => $value ) {
		if ( trim( (string) $value ) === '' ) {
			continue;
		}

		$rowsHtml .= '<div class="cert-row"><span>' . artist_document_e( $label ) . '</span>'
			. '<span>' . artist_document_e( $value ) . '</span></div>';
	}

	$body = '<div class="certificate-body">'
		. '<div class="subtitle" style="margin:30px 0 18px;">Certificate of Registration</div>'
		. '<p class="intro">This is to certify that the artist named below is registered with '
		. 'BDC Music through the BDC Artists Marketplace, and that the registration has '
		. 'been verified by the studio.</p>'
		. '<div class="certificate-name">' . artist_document_e( $f['name'] ) . '</div>'
		. '<div class="certificate-role">' . artist_document_e( $f['category'] ) . '</div>'
		. '<div class="certificate-meta">' . $rowsHtml . '</div>'
		. '</div>'
		. '<div class="footer">'
		. '<div class="footer-note">Issued by BDC Music under artist code '
		. artist_document_e( $f['code'] ) . '. The artist code is the studio\'s reference for '
		. 'this registration and appears on both this certificate and the artist\'s ID card.</div>'
		. '<div class="stamp"><strong>BDC Music</strong>'
		. '<span>Issued ' . artist_document_e( $f['issued'] ) . '</span></div>'
		. '</div>';

	return artist_document_page(
		'Certificate of Registration - ' . $f['name'],
		'Verified artist on the BDC Artists Marketplace',
		$body
	);
}

/**
 * Render one document by kind.
 *
 * @param array  $reg  Registration row.
 * @param string $kind id-card or certificate.
 * @return string|null Null for an unknown kind.
 */
function artist_document_html( array $reg, $kind ) {
	if ( $kind === 'id-card' ) {
		return artist_id_card_html( $reg );
	}

	if ( $kind === 'certificate' ) {
		return artist_certificate_html( $reg );
	}

	return null;
}

/**
 * Write both documents for a verified registration.
 *
 * Called by the verification endpoint after the code has been assigned. Returns
 * the paths to store on the registration row.
 *
 * @param array $reg Registration row, including artist_code.
 * @return array|false Map of path column => path, or false when the code is
 *                     missing or a file could not be written.
 */
function artist_generate_documents( array $reg ) {
	$code = (string) ( $reg['artist_code'] ?? '' );

	if ( $code === '' ) {
		return false;
	}

	$paths = array();

	foreach ( artist_document_kinds() as $kind => $column ) {
		$html = artist_document_html( $reg, $kind );

		if ( $html === null ) {
			return false;
		}

		$relative = artist_document_path( $code, $kind );
		$absolute = artist_document_absolute_path( $relative );
		$dir      = dirname( $absolute );

		if ( ! is_dir( $dir ) && ! @mkdir( $dir, 0775, true ) && ! is_dir( $dir ) ) {
			app_log( 'artist-documents', 'could not create ' . $dir );
			return false;
		}

		// LOCK_EX: two admins verifying different artists at the same moment write
		// into the same directory, and an interleaved write would leave a
		// half-written document that still passes file_exists().
		if ( file_put_contents( $absolute, $html, LOCK_EX ) === false ) {
			app_log( 'artist-documents', 'could not write ' . $absolute );
			return false;
		}

		$paths[ $column ] = $relative;
	}

	return $paths;
}

/**
 * The stored paths for a registration, regenerating any file that is missing.
 *
 * Called when a document is opened or downloaded, so a document deleted from the
 * server comes back on the next request instead of leaving the artist with a
 * broken link they cannot report.
 *
 * @param array $reg Registration row.
 * @return array Map of path column => path.
 */
function artist_documents_ensure( array $reg ) {
	$paths = array();
	$dirty = false;

	foreach ( artist_document_kinds() as $kind => $column ) {
		$relative = (string) ( $reg[ $column ] ?? '' );

		if ( $relative !== '' && file_exists( artist_document_absolute_path( $relative ) ) ) {
			$paths[ $column ] = $relative;
			continue;
		}

		// The path can be rebuilt from the code even when it was never stored,
		// which is the case for a registration verified before this feature ran.
		if ( ! empty( $reg['artist_code'] ) ) {
			$paths[ $column ] = artist_document_path( $reg['artist_code'], $kind );
		}

		$dirty = true;
	}

	if ( $dirty && ! empty( $reg['artist_code'] ) ) {
		$generated = artist_generate_documents( $reg );

		if ( $generated ) {
			$paths = array_merge( $paths, $generated );
		}
	}

	return $paths;
}

/**
 * Withdraw a registration's issued documents from the disk.
 *
 * Used when a decision is reversed (a rejection, or a rejected registration put
 * back in the queue). Leaving the files behind would keep a withdrawn ID Card on
 * the server where only a database update stands between it and being served
 * again, and the empty directory has no reason to outlive them.
 *
 * The stored paths are the caller's to clear; this only removes what is on disk.
 *
 * @param array $reg Registration row, before its paths were cleared.
 * @return int Number of files removed.
 */
function artist_documents_discard( array $reg ) {
	$removed = 0;
	$dirs    = array();

	foreach ( artist_document_kinds() as $column ) {
		$relative = trim( (string) ( $reg[ $column ] ?? '' ) );

		if ( $relative === '' ) {
			continue;
		}

		$absolute = artist_document_absolute_path( $relative );

		// artist_document_absolute_path() builds the path from the stored value, so
		// only a path inside data/documents/ may ever be removed here.
		if ( strpos( str_replace( '\\', '/', $absolute ), 'data/documents/' ) === false ) {
			continue;
		}

		if ( is_file( $absolute ) && @unlink( $absolute ) ) {
			$removed++;
		}

		$dirs[ dirname( $absolute ) ] = true;
	}

	foreach ( array_keys( $dirs ) as $dir ) {
		// rmdir() only succeeds on an empty directory, which is exactly the
		// condition for removing it.
		@rmdir( $dir );
	}

	return $removed;
}
