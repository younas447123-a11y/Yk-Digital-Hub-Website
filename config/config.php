<?php


// =====================================================
// 1. Environment Detection
// =====================================================
// Set to 'development', 'staging', or 'production'
define('ENVIRONMENT', 'development');

// =====================================================
// 2. Error Reporting (based on environment)
// =====================================================
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// =====================================================
// 3. Site Base URL (for absolute links)
// =====================================================
// Change this to your actual domain (without trailing slash)
define('BASE_URL', 'http://localhost/yk-digital-hub');

// =====================================================
// 4. Filesystem Paths (absolute server paths)
// =====================================================
define('ROOT_PATH', dirname(__DIR__));                 // /path/to/yk-digital-hub
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('INCLUDES_PATH', ROOT_PATH . '/includes');

// =====================================================
// 5. Upload Directories (relative to UPLOADS_PATH)
// =====================================================
define('UPLOAD_SERVICES', 'services');
define('UPLOAD_PORTFOLIO', 'portfolio');
define('UPLOAD_BLOG', 'blog');
define('UPLOAD_TESTIMONIALS', 'testimonials');

// =====================================================
// =====================================================
// 6. Session Configuration
// =====================================================
// Only set these if no session is active yet (safe practice)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', (ENVIRONMENT === 'production') ? 1 : 0);
}
// =====================================================
// 7. Security Salt (change to a random string)
// =====================================================
define('SALT', 'change-this-to-a-random-secret-string');

// =====================================================
// 8. Pagination Defaults
// =====================================================
define('ITEMS_PER_PAGE', 12);

// =====================================================
// 9. Date/Time Format
// =====================================================
define('DATE_FORMAT', 'F j, Y');
define('DATETIME_FORMAT', 'F j, Y g:i A');

// =====================================================
// 10. Include the Database Configuration
// =====================================================
require_once __DIR__ . '/database.php';

// End of config.php