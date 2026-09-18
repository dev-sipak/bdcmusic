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

// Current page detection
$currentPage = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
if ( strpos( $currentPage, 'bdcmusic' ) !== false ) {
    $currentPage = str_replace( 'bdcmusic', '', $currentPage );
}
$currentPage = trim( $currentPage, '/' );

$isServicesPage = $currentPage === 'services'
    || strpos( $currentPage, 'services/' ) === 0;
?>
