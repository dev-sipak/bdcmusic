<?php
/**
 * Database Connection
 *
 * Uses values defined in config.php (loaded from .env).
 */

function db_connect() {
    static $pdo = null;
    if ( $pdo === null ) {
        $host    = defined( 'DB_HOST' ) ? DB_HOST : 'localhost';
        $name    = defined( 'DB_NAME' ) ? DB_NAME : 'bdcmusic';
        $user    = defined( 'DB_USER' ) ? DB_USER : 'root';
        $pass    = defined( 'DB_PASS' ) ? DB_PASS : '';
        $charset = defined( 'DB_CHARSET' ) ? DB_CHARSET : 'utf8mb4';

        $port    = defined( 'DB_PORT' ) ? DB_PORT : '3306';

        $dsn = 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $name . ';charset=' . $charset;
        $pdo = new PDO( $dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ] );
    }
    return $pdo;
}
