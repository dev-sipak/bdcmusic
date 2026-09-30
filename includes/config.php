<?php
/**
 * Application Configuration
 *
 * Loads settings from .env file in the project root.
 * Copy .env.example to .env and update values for your environment.
 */

// Load .env file
$envFile = __DIR__ . '/../.env';
if ( file_exists( $envFile ) ) {
	$lines = file( $envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( $line === '' || $line[0] === '#' ) {
			continue;
		}
		$parts = explode( '=', $line, 2 );
		if ( count( $parts ) === 2 ) {
			$key   = trim( $parts[0] );
			$value = trim( $parts[1], ' "\'');
			$_ENV[ $key ] = $value;
			putenv( $key . '=' . $value );
		}
	}
}

// Helper to get env value with fallback
function env( $key, $default = '' ) {
	return $_ENV[ $key ] ?? getenv( $key ) ?: $default;
}

// Base paths
$protocol = ( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' )
	? 'https://'
	: 'http://';

$basePath  = env( 'BASE_URL', $protocol . $_SERVER['HTTP_HOST'] . '/bdcmusic/' );
$siteUrl   = $basePath;
$assetPath = $basePath . 'assets/';

define( 'APP_BASE_URL', $basePath );

// Database
define( 'DB_HOST', env( 'DB_HOST', 'localhost' ) );
define( 'DB_NAME', env( 'DB_NAME', 'bdcmusic' ) );
define( 'DB_USER', env( 'DB_USER', 'root' ) );
define( 'DB_PASS', env( 'DB_PASS', '' ) );
define( 'DB_CHARSET', env( 'DB_CHARSET', 'utf8mb4' ) );
define( 'DB_PORT', env( 'DB_PORT', '3306' ) );

// Site
define( 'SITE_NAME', env( 'SITE_NAME', 'BDC Music Studio' ) );

// Where this is running. An unset APP_ENV is treated as production, so a
// deployment that forgets to set it still refuses to take a payment rather than
// quietly falling back to a bypass.
define( 'APP_ENV', env( 'APP_ENV', 'production' ) );
define( 'IS_PRODUCTION', APP_ENV === 'production' );

// Demo mode makes the payment step complete without contacting Razorpay, so the
// booking flow can be exercised end to end before real keys exist. It is only
// honoured outside production: in production a missing key is an error, never a
// reason to hand out a confirmed-but-unpaid booking.
define( 'PAYMENT_DEMO_MODE', env( 'PAYMENT_DEMO_MODE', 'false' ) === 'true' );

// Resolved once, here, so no endpoint can enable the bypass on its own: the
// flag is only ever true outside production, whatever .env asks for.
define( 'BOOKING_DEMO', ! IS_PRODUCTION
	&& ( PAYMENT_DEMO_MODE || env( 'BOOKING_DEMO', 'false' ) === 'true' ) );

// Payment
//
// Razorpay is the only payment provider. Add the two credentials to `.env`
// and set PAYMENT_PROVIDER_ENABLED=true to switch it on:
//
//   RAZORPAY_KEY_ID=rzp_test_xxxxxxxx
//   RAZORPAY_KEY_SECRET=xxxxxxxxxxxxxxxx
//   PAYMENT_PROVIDER_ENABLED=true
//
// Nothing else has to change: `includes/booking-create.php` creates the booking
// first and only then asks the gateway for an order, so with no credentials
// the customer can still place the order and it is saved as awaiting payment.
//
// The key secret is read here and never leaves the server: the checkout only
// ever needs the key id, which is public by design.
define( 'RAZORPAY_KEY_ID', env( 'RAZORPAY_KEY_ID', '' ) );
define( 'RAZORPAY_KEY_SECRET', env( 'RAZORPAY_KEY_SECRET', '' ) );

// The switch. Deliberately separate from the credentials, so keys can be
// present in .env on a machine that must not take money yet.
define( 'PAYMENT_PROVIDER_ENABLED', env( 'PAYMENT_PROVIDER_ENABLED', 'false' ) === 'true' );

// A provider is only usable when it is both switched on and actually
// credentialed, so a half-filled .env never sends a customer to a gateway that
// cannot settle. With no provider, orders are still created and are left
// awaiting payment for the studio to collect.
define( 'RAZORPAY_READY', PAYMENT_PROVIDER_ENABLED
	&& RAZORPAY_KEY_ID !== ''
	&& RAZORPAY_KEY_SECRET !== '' );

/**
 * Whether an order placed right now can be paid online immediately.
 *
 * Demo mode counts as ready because it settles without a gateway, which is how
 * the flow is exercised before the real keys exist. Resolved after both flags
 * exist, so neither this function nor any caller can be the thing that decides
 * what "ready" means.
 *
 * @return bool
 */
function booking_payment_ready() {
	return RAZORPAY_READY || BOOKING_DEMO;
}

// Where the new-booking notification goes, so the address is not hard-coded in
// the payment endpoint.
define( 'BOOKING_OWNER_EMAIL', env( 'BOOKING_OWNER_EMAIL', '' ) );

// Current page detection
$currentPage = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
if ( strpos( $currentPage, 'bdcmusic' ) !== false ) {
	$currentPage = str_replace( 'bdcmusic', '', $currentPage );
}
$currentPage = trim( $currentPage, '/' );

$isServicesPage = $currentPage === 'services'
	|| strpos( $currentPage, 'services/' ) === 0;

$isArtistsPage = $currentPage === 'artists'
	|| strpos( $currentPage, 'artists/' ) === 0;
?>
