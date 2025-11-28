<?php
/**
 * settings/db.php
 *
 * Database connection for EASYBUY
 *
 * Defaults assume a local XAMPP installation:
 *  - host: 127.0.0.1
 *  - user: root
 *  - password: (empty)
 *  - dbname: easybuy
 *
 * This file attempts to create a PDO connection (recommended). If PDO is not available
 * or the connection fails, it falls back to mysqli. It exposes helper functions
 * getPDO() and getMysqli() for other code to consume.
 */

// Adjust these values for your environment
$DB_HOST = '127.0.0.1';
$DB_NAME = 'easybuy';
$DB_USER = 'root';
$DB_PASS = '';

$pdo = null;
$mysqli = null;

// Try PDO if available
if (class_exists('PDO')) {
    try {
        $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
        $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        // PDO failed; we'll fall back to mysqli below. Log the PDO error.
        error_log('PDO connection failed: ' . $e->getMessage());
        $pdo = null;
    }
}

// If PDO isn't available or failed, try mysqli
if ($pdo === null) {
    $mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    if ($mysqli->connect_errno) {
        // Connection failed for both PDO and mysqli. Log and stop.
        error_log('MySQLi connection failed: ' . $mysqli->connect_error);
        // Fail fast in development; change to a softer behavior if desired.
        die('Database connection failed. Check settings/db.php and the database server.');
    }
}

/**
 * Get the PDO instance if available, otherwise null.
 * @return PDO|null
 */
function getPDO()
{
    global $pdo;
    return $pdo;
}

/**
 * Get the mysqli instance if PDO isn't available. May be null if PDO is used.
 * @return mysqli|null
 */
function getMysqli()
{
    global $mysqli;
    return $mysqli;
}

/**
 * Usage example (in other PHP files):
 *
 * require_once __DIR__ . '/settings/db.php';
 * $pdo = getPDO();
 * if ($pdo) {
 *     // use PDO
 * } else {
 *     $mysqli = getMysqli();
 *     // use mysqli
 * }
 */

// End of settings/db.php
