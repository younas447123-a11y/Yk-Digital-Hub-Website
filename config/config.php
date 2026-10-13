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

$site_base_path = null;
$site_root_path = realpath(ROOT_PATH);
$document_root_path = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
if ($site_root_path !== false && $document_root_path !== false) {
    $site_root_path = str_replace('\\', '/', $site_root_path);
    $document_root_path = rtrim(str_replace('\\', '/', $document_root_path), '/');
    if (strcasecmp($site_root_path, $document_root_path) === 0) {
        $site_base_path = '';
    } elseif (stripos($site_root_path, $document_root_path . '/') === 0) {
        $site_base_path = substr($site_root_path, strlen($document_root_path));
    }
}
if ($site_base_path === null) {
    $site_base_path = parse_url(BASE_URL, PHP_URL_PATH) ?: '';
}
define('SITE_BASE_PATH', rtrim(str_replace('\\', '/', $site_base_path), '/'));

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