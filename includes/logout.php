<?php
/**
 * Logout Handler
 *
 * Destroys the session and redirects to the login page.
 */

require_once __DIR__ . '/session.php';

// Clears $_SESSION, deletes the server-side session record and expires the
// session cookie. session_unset() + session_destroy() on their own left the
// PHPSESSID cookie in the browser, so the next page load resumed the same id.
app_session_destroy();

require_once __DIR__ . '/config.php';
header( 'Location: ' . $basePath . 'login' );
exit;
