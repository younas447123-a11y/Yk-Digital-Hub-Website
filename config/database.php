<?php
/**
 * YK Digital Hub - Database Configuration
 * 
 * This file establishes a PDO connection to the MySQL database.
 * It uses the constants defined in config.php (if available) or falls back to defaults.
 */

// =====================================================
// 1. Database Connection Parameters
// =====================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'yk_digital_hub');
define('DB_USER', 'root');
define('DB_PASS', '');        // Change for production
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', 'utf8mb4_unicode_ci');

// =====================================================
// 2. PDO DSN (Data Source Name)
// =====================================================
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

// =====================================================
// 3. PDO Options
// =====================================================
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_STRINGIFY_FETCHES  => false,
];

// =====================================================
// 4. Create the PDO Connection Object (global variable)
// =====================================================
try {
    $db = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // In development, show the error; in production, log it and show a generic message.
    if (ENVIRONMENT === 'development') {
        die('Database connection failed: ' . $e->getMessage());
    } else {
        // Log the error (you could use error_log() or a logging library)
        error_log('DB Connection Error: ' . $e->getMessage());
        die('We are experiencing technical difficulties. Please try again later.');
    }
}

// =====================================================
// 5. Optional: Set the timezone for the connection
// =====================================================
$db->exec("SET time_zone = '+00:00'"); // UTC

// =====================================================
// 6. Helper function to get the connection (if needed)
// =====================================================
function getDbConnection() {
    global $db;
    return $db;
}

// End of database.php