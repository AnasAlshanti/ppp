<?php
/**
 * config.php — central database connection (PDO) + app constants.
 *
 * Included by every page/script. Production targets MySQL; the DSN and
 * credentials can be overridden with environment variables (handy for
 * deployment and automated testing), defaulting to a local MySQL instance.
 */

declare(strict_types=1);

// ---- App constants -------------------------------------------------------
const APP_NAME = 'ShopSphere';
const TAX_RATE = 0.07; // 7% — used for the cart/checkout summary

// ---- Database connection -------------------------------------------------
$DB_DSN  = getenv('APP_DB_DSN')  ?: 'mysql:host=127.0.0.1;dbname=ecommerce_db;charset=utf8mb4';
$DB_USER = getenv('APP_DB_USER') ?: 'root';
$DB_PASS = getenv('APP_DB_PASS') ?: '';

try {
    $pdo = new PDO($DB_DSN, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    // SQLite (used by the test harness) does not enforce foreign keys by default.
    if (str_starts_with($DB_DSN, 'sqlite:')) {
        $pdo->exec('PRAGMA foreign_keys = ON');
    }
} catch (PDOException $e) {
    // Never leak connection details to the browser in a real deployment.
    http_response_code(500);
    die('Database connection failed. Please check config.php and that the database is running.');
}
