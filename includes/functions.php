<?php
/**
 * General helper functions for YK Digital Hub.
 * Includes: flash messages, CSRF tokens, blog HTML sanitization, reading time.
 */

// Ensure session is started for flash/CSRF features
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Set a flash message for the next page load.
 *
 * @param string $type    success | error | warning | info
 * @param string $message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message,
    ];
}

/**
 * Retrieve and clear the flash message.
 *
 * @return array|null
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Display the flash message HTML (if any) and clear it.
 */
function displayFlash() {
    $flash = getFlash();
    if ($flash) {
        $type = htmlspecialchars($flash['type']);
        $msg  = htmlspecialchars($flash['message']);
        echo "<div class=\"flash-message flash-{$type}\">{$msg}</div>";
    }
}

/**
 * Generate or retrieve a CSRF token.
 *
 * @return string
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify a submitted CSRF token against the session token.
 *
 * @param string $token
 * @return bool
 */
function csrf_verify($token) {
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], (string)$token);
}

/**
 * Sanitize admin-submitted blog HTML.
 * Allows a safe subset of tags/attributes.
 *
 * @param string $html
 * @return string
 */
function sanitize_post_html($html) {
    $html = (string)$html;
    $allowed = '<p><br><h2><h3><h4><h5><h6><strong><b><em><i><u><ul><ol><li><a><blockquote><img><figure><figcaption><pre><code><hr><table><thead><tbody><tr><th><td><span><div>';
    $html = strip_tags($html, $allowed);
    // Strip inline event handlers
    $html = preg_replace('/\son\w+\s*=\s*"[^"]*"/i', '', $html);
    $html = preg_replace("/\son\w+\s*=\s*'[^']*'/i", '', $html);
    // Strip javascript: URLs
    $html = preg_replace('/javascript\s*:/i', '', $html);
    return $html;
}

/**
 * Estimate reading time (in minutes) from HTML content.
 *
 * @param string $html
 * @return int
 */
function estimate_reading_time($html) {
    $text = trim(strip_tags((string)$html));
    $words = $text === '' ? 0 : count(preg_split('/\s+/', $text));
    return max(1, (int)ceil($words / 200));
}/**
 * Fetch a site setting by key. Cached per request.
 */
function getSiteSetting($key, $default = '') {
    global $db;
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            $rows = $db->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                $cache[$r['setting_key']] = $r['setting_value'];
            }
        } catch (PDOException $e) {
            $cache = [];
        }
    }
    return array_key_exists($key, $cache) ? $cache[$key] : $default;
}/**
 * Request-level cached service navigation.
 * Returns: [ ['id','name','slug','children'=>[ ['id','name','slug','services'=>[...]] ]], ... ]
 */
function getServiceNavigation() {
    static $cache = null;
    if ($cache !== null) return $cache;
    global $db;

    $cache = [];
    try {
        // Parents (fixed)
        $parents = $db->query("
            SELECT id, name, slug FROM service_categories
            WHERE parent_id IS NULL AND is_fixed = 1 AND status = 1
            ORDER BY sort_order, name
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Children keyed by parent
        $children = $db->query("
            SELECT id, parent_id, name, slug FROM service_categories
            WHERE parent_id IS NOT NULL AND is_fixed = 0 AND status = 1
            ORDER BY sort_order, name
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Active services
        $services = $db->query("
            SELECT id, category_id, title, slug FROM services
            WHERE status = 1
            ORDER BY featured DESC, sort_order, title
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Group services by child category
        $services_by_cat = [];
        foreach ($services as $s) {
            $services_by_cat[$s['category_id']][] = $s;
        }

        // Group children by parent, attach services
        $children_by_parent = [];
        foreach ($children as $c) {
            $c['services'] = $services_by_cat[$c['id']] ?? [];
            $children_by_parent[$c['parent_id']][] = $c;
        }

        // Assemble
        foreach ($parents as $p) {
            $p['children'] = $children_by_parent[$p['id']] ?? [];
            $cache[] = $p;
        }
    } catch (PDOException $e) {
        $cache = [];
    }
    return $cache;
}

/**
 * Request-level cached portfolio navigation.
 * Returns: [ ['id','name','slug','children'=>[ ['id','name','slug','projects'=>[...]] ]], ... ]
 */
function getPortfolioNavigation() {
    static $cache = null;
    if ($cache !== null) return $cache;
    global $db;

    $cache = [];
    try {
        $parents = $db->query("
            SELECT id, name, slug FROM portfolio_categories
            WHERE parent_id IS NULL AND is_fixed = 1 AND status = 1
            ORDER BY order_num, name
        ")->fetchAll(PDO::FETCH_ASSOC);

        $children = $db->query("
            SELECT id, parent_id, name, slug FROM portfolio_categories
            WHERE parent_id IS NOT NULL AND is_fixed = 0 AND status = 1
            ORDER BY order_num, name
        ")->fetchAll(PDO::FETCH_ASSOC);

        $projects = $db->query("
            SELECT id, category_id, title, slug FROM portfolio_projects
            WHERE status = 1
            ORDER BY featured DESC, sort_order, title
            LIMIT 200
        ")->fetchAll(PDO::FETCH_ASSOC);

        $projects_by_cat = [];
        foreach ($projects as $p) {
            $projects_by_cat[$p['category_id']][] = $p;
        }

        $children_by_parent = [];
        foreach ($children as $c) {
            $c['projects'] = $projects_by_cat[$c['id']] ?? [];
            $children_by_parent[$c['parent_id']][] = $c;
        }

        foreach ($parents as $p) {
            $p['children'] = $children_by_parent[$p['id']] ?? [];
            $cache[] = $p;
        }
    } catch (PDOException $e) {
        $cache = [];
    }
    return $cache;
}

/**
 * Detect which top-level nav item is active from the current URL.
 * Returns one of: home, about, services, portfolio, blog, contact, ''
 */
function getActiveNav() {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($uri, PHP_URL_PATH) ?: '/';

    // Strip the project subdirectory (e.g. /YK-Digital-hub)
    $base = parse_url(BASE_URL, PHP_URL_PATH) ?: '';
    if ($base && strpos($path, $base) === 0) {
        $path = substr($path, strlen($base));
    }
    $path = '/' . ltrim($path, '/');

    if ($path === '/' || $path === '/index.php') return 'home';
    if (preg_match('#^/about#', $path))     return 'about';
    if (preg_match('#^/services#', $path))  return 'services';
    if (preg_match('#^/portfolio#', $path)) return 'portfolio';
    if (preg_match('#^/blog#', $path))      return 'blog';
    if (preg_match('#^/contact#', $path))   return 'contact';
    return '';
}