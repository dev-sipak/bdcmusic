<?php
/**
 * Logout Handler
 *
 * Destroys the session and redirects to the login page.
 */

session_start();
session_unset();
session_destroy();

require_once __DIR__ . '/config.php';
header( 'Location: ' . $basePath . 'login' );
exit;
