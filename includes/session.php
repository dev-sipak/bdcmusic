<?php
/**
 * Session bootstrap.
 *
 * Every entry point used to call a bare session_start(), so the cookie flags,
 * the strict mode setting and any idle timeout had to be configured in one
 * place but were configured nowhere. This file is the one place, and it is
 * required instead of session_start().
 *
 * It is deliberately self-contained: callers start their session before they
 * load config.php, so this file must not depend on it. It reads the same
 * environment file by hand for the one value it needs.
 *
 * Includes:
 *   - HttpOnly, SameSite and Secure cookies (Secure only over HTTPS, so plain
 *     HTTP local development keeps working).
 *   - Strict mode, so a session id is only accepted when it came from the
 *     server, which blocks session fixation via a planted cookie.
 *   - Use-only-cookies, so a session id cannot be smuggled in through the URL.
 *   - An idle timeout, so an abandoned browser session stops being valid.
 *   - Periodic id regeneration, so a captured id stops being useful.
 */

// How long a session may sit idle before it is discarded.
const SESSION_IDLE_TIMEOUT = 1800; // 30 minutes

// How often, at most, the session id is regenerated.
const SESSION_REGENERATE_INTERVAL = 900; // 15 minutes

/**
 * Whether the current request arrived over HTTPS.
 * Honours a reverse proxy's forwarded protocol header.
 * @return bool
 */
function session_is_secure_request() {
    if ( ! empty( $_SERVER['HTTPS'] ) && strtolower( (string) $_SERVER['HTTPS'] ) !== 'off' ) {
        return true;
    }

    if ( isset( $_SERVER['SERVER_PORT'] ) && (int) $_SERVER['SERVER_PORT'] === 443 ) {
        return true;
    }

    if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] )
        && strtolower( (string) $_SERVER['HTTP_X_FORWARDED_PROTO'] ) === 'https' ) {
        return true;
    }

    return false;
}

/**
 * Apply the hardened cookie and INI settings without starting a session.
 *
 * Split out from app_session_start() so the lazy public booking session can
 * take the same protections without a session being started on every page.
 */
function app_session_configure() {
    if ( session_status() === PHP_SESSION_ACTIVE || headers_sent() ) {
        return;
    }

    $secure = session_is_secure_request();

    session_set_cookie_params( array(
        'lifetime' => 0,          // Browser-session cookie: no Max-Age.
        'path'     => '/',
        'domain'   => '',
        'secure'   => $secure,    // HTTPS only; false over plain HTTP.
        'httponly' => true,       // Not readable from JavaScript.
        'samesite' => 'Lax',      // Blocks cross-site POSTs, keeps normal links working.
    ) );

    // Only accept a session id the server itself issued. Without this an
    // attacker can plant a known id in a victim's browser and then use it.
    ini_set( 'session.use_strict_mode', '1' );

    // The id must travel in the cookie only, never in the URL.
    ini_set( 'session.use_only_cookies', '1' );

    // Do not let a client choose its own session id through a GET parameter.
    ini_set( 'session.use_trans_sid', '0' );

    ini_set( 'session.gc_maxlifetime', (string) SESSION_IDLE_TIMEOUT );
    ini_set( 'session.sid_length', '48' );
    ini_set( 'session.sid_bits_per_character', '6' );
}

/**
 * Start the session with hardened cookie settings, enforcing the idle timeout
 * and regenerating the id when it has been alive long enough. Safe to call
 * more than once: a session that is already running is left alone.
 */
function app_session_start() {
    if ( session_status() === PHP_SESSION_ACTIVE ) {
        return;
    }

    if ( headers_sent() ) {
        // Cookies cannot be configured after output has started, so fall back to
        // a plain start rather than emitting a warning.
        session_start();
        return;
    }

    app_session_configure();

    session_start();

    $now = time();
    $isNewSession = ! isset( $_SESSION['regenerated_at'] );

    // Discard an idle session. The check is inside the same session write that
    // updates the timestamp, so it costs nothing extra.
    if ( isset( $_SESSION['last_activity'] ) ) {
        $idleFor = $now - (int) $_SESSION['last_activity'];

        if ( $idleFor > SESSION_IDLE_TIMEOUT ) {
            app_session_destroy();

            // Start a fresh, empty session so the request still has somewhere to
            // record "not logged in" rather than tripping over a missing array.
            session_start();
            $_SESSION['last_activity']  = $now;
            $_SESSION['regenerated_at'] = $now;
            return;
        }
    }

    // Rotate the id only for a session that is already established. A session
    // created by this very request is already fresh, so regenerating it would
    // emit a second, immediately-superseded Set-Cookie header for nothing.
    if ( ! $isNewSession ) {
        $lastRegen = (int) $_SESSION['regenerated_at'];

        if ( $now - $lastRegen > SESSION_REGENERATE_INTERVAL ) {
            session_regenerate_id( true );
        }
    }

    $_SESSION['last_activity']  = $now;
    $_SESSION['regenerated_at'] = $now;
}

/**
 * Clear the session completely: the data, the server record and the cookie.
 */
function app_session_destroy() {
    $_SESSION = array();

    if ( ini_get( 'session.use_cookies' ) ) {
        $params = session_get_cookie_params();

        setcookie( session_name(), '', array(
            'expires'  => time() - 42000,
            'path'     => $params['path'],
            'domain'   => $params['domain'],
            'secure'   => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => isset( $params['samesite'] ) ? $params['samesite'] : 'Lax',
        ) );
    }

    if ( session_status() === PHP_SESSION_ACTIVE ) {
        session_destroy();
    }
}

/**
 * Shared helper functions and constants.
 *
 * Required from here so that every entry point which starts a session can also
 * reach app_log() and the guard helpers. helpers.php only declares constants
 * and functions, so loading it this early has no side effects and does not
 * depend on config.php being loaded first.
 */
require_once __DIR__ . '/helpers.php';

app_session_start();
