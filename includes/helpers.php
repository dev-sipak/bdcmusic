<?php
/**
 * Shared helper functions and constants.
 * Include this file wherever needed: require_once __DIR__ . '/helpers.php';
 */

if ( ! defined( 'HELPERS_LOADED' ) ) {
    define( 'HELPERS_LOADED', true );
}

// ─── Sanitization ───────────────────────────────

function sanitize_input( $value ) {
    return trim( strip_tags( (string) $value ) );
}

function sanitize_email( $value ) {
    return filter_var( trim( $value ), FILTER_SANITIZE_EMAIL );
}

function validate_email( $value ) {
    return filter_var( $value, FILTER_VALIDATE_EMAIL );
}

// ─── JSON Storage ───────────────────────────────

function ensure_storage_dir() {
    $dir = __DIR__ . '/../data';
    if ( ! is_dir( $dir ) ) {
        mkdir( $dir, 0755, true );
    }
}

function load_json_file( $path, $default = [] ) {
    if ( ! file_exists( $path ) ) {
        return $default;
    }
    $content = file_get_contents( $path );
    if ( $content === false ) {
        return $default;
    }
    $data = json_decode( $content, true );
    if ( ! is_array( $data ) ) {
        return $default;
    }
    return $data;
}

function save_json_file( $path, $data ) {
    ensure_storage_dir();
    $json = json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
    if ( $json === false ) {
        return false;
    }
    return file_put_contents( $path, $json ) !== false;
}

// ─── Service Constants ──────────────────────────

define( 'SERVICES', [
    'Artist Management Services',
    'Audio and Video Services',
    'Digital Music Distribution',
    'Online and Offline Classes',
    'Promotional Services',
    'IPRS Services',
    'Creator Marketplace',
]);

define( 'BOOKING_STATUSES', [
    'Pending',
    'Processing',
    'Shipped',
    'Delivered',
    'Cancelled',
]);

// ─── CSRF Protection ────────────────────────────

function generate_csrf_token() {
    if ( session_status() === PHP_SESSION_NONE ) {
        session_start();
    }
    if ( empty( $_SESSION['csrf_token'] ) ) {
        $_SESSION['csrf_token'] = bin2hex( random_bytes( 32 ) );
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars( generate_csrf_token() ) . '">';
}

function verify_csrf_token( $token ) {
    if ( session_status() === PHP_SESSION_NONE ) {
        session_start();
    }
    return isset( $_SESSION['csrf_token'] ) && hash_equals( $_SESSION['csrf_token'], $token );
}

// ─── URL Helpers ────────────────────────────────

function url( $path = '' ) {
    global $siteUrl;
    $base = defined( 'APP_BASE_URL' ) ? APP_BASE_URL : ( $siteUrl ?? '/' );
    return $base . ltrim( $path, '/' );
}

function asset( $path = '' ) {
    global $assetPath;
    $assets = defined( 'APP_BASE_URL' ) ? APP_BASE_URL . 'assets/' : ( $assetPath ?? '/assets/' );
    return $assets . ltrim( $path, '/' );
}

// ─── Output Helpers ─────────────────────────────

function e( $value ) {
    echo htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
}

function status_badge_html( $status ) {
    $class = 'panel-status-badge status-' . strtolower( htmlspecialchars( $status ) );
    return '<span class="' . $class . '">' . htmlspecialchars( $status ) . '</span>';
}
?>
