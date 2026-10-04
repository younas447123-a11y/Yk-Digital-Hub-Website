<?php
/**
 * YK Digital Hub — Dynamic XML Sitemap
 * URL: /sitemap.xml (via .htaccess) or /sitemap.php
 *
 * Generates a sitemap from:
 *  - Static pages (Home, About, Contact, Services index, Portfolio index, Blog index)
 *  - Service parent categories, child categories, and services
 *  - Portfolio parent categories, child categories, and projects
 *  - Blog posts and blog categories
 */

require_once __DIR__ . '/config/config.php';

// Send XML content-type header
header('Content-Type: application/xml; charset=utf-8');

// Current timestamp for fallback lastmod
$now = date('c');
$base = rtrim(BASE_URL, '/');

// =====================================================
// Collect all URLs
// =====================================================
$urls = [];

// Helper: escape URL for XML
$esc = function ($url) {
    return htmlspecialchars($url, ENT_XML1 | ENT_QUOTES, 'UTF-8');
};

// Helper: format date as ISO 8601 (W3C format)
$iso = function ($date) use ($now) {
    if (empty($date) || $date === '0000-00-00 00:00:00') return $now;
    $ts = strtotime($date);
    return $ts ? date('c', $ts) : $now;
};

// -----------------------------------------------------
// 1. Static pages
// -----------------------------------------------------
$urls[] = [
    'loc'        => $base . '/',
    'lastmod'    => $now,
    'changefreq' => 'weekly',
    'priority'   => '1.0',
];
$urls[] = [
    'loc'        => $base . '/about',
    'lastmod'    => $now,
    'changefreq' => 'monthly',
    'priority'   => '0.8',
];
$urls[] = [
    'loc'        => $base . '/contact',
    'lastmod'    => $now,
    'changefreq' => 'monthly',
    'priority'   => '0.8',
];
$urls[] = [
    'loc'        => $base . '/services/',
    'lastmod'    => $now,
    'changefreq' => 'weekly',
    'priority'   => '0.9',
];
$urls[] = [
    'loc'        => $base . '/portfolio/',
    'lastmod'    => $now,
    'changefreq' => 'weekly',
    'priority'   => '0.9',
];
$urls[] = [
    'loc'        => $base . '/blog/',
    'lastmod'    => $now,
    'changefreq' => 'weekly',
    'priority'   => '0.8',
];

// -----------------------------------------------------
// 2. Services — parents, children, individual services
// -----------------------------------------------------
try {
    // Parent categories
    $parents = $db->query("
        SELECT id, slug, updated_at
        FROM service_categories
        WHERE parent_id IS NULL AND is_fixed = 1 AND status = 1
        ORDER BY sort_order, id
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($parents as $p) {
        $urls[] = [
            'loc'        => $base . '/services/' . $p['slug'] . '/',
            'lastmod'    => $iso($p['updated_at']),
            'changefreq' => 'weekly',
            'priority'   => '0.9',
        ];
    }

    // Child categories
    $children = $db->query("
        SELECT c.id, c.slug, c.updated_at, p.slug AS parent_slug
        FROM service_categories c
        JOIN service_categories p ON c.parent_id = p.id
        WHERE c.parent_id IS NOT NULL AND c.is_fixed = 0 AND c.status = 1
          AND p.status = 1 AND p.is_fixed = 1
        ORDER BY c.sort_order, c.id
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($children as $c) {
        $urls[] = [
            'loc'        => $base . '/services/' . $c['parent_slug'] . '/' . $c['slug'] . '/',
            'lastmod'    => $iso($c['updated_at']),
            'changefreq' => 'weekly',
            'priority'   => '0.8',
        ];
    }

    // Individual services
    $services = $db->query("
        SELECT s.slug, s.updated_at, c.slug AS child_slug, p.slug AS parent_slug
        FROM services s
        JOIN service_categories c   ON s.category_id = c.id
        JOIN service_categories p   ON c.parent_id = p.id
        WHERE s.status = 1 AND c.status = 1 AND p.status = 1
          AND c.is_fixed = 0 AND p.is_fixed = 1
        ORDER BY s.sort_order, s.id
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($services as $s) {
        $urls[] = [
            'loc'        => $base . '/services/' . $s['parent_slug'] . '/' . $s['child_slug'] . '/' . $s['slug'] . '/',
            'lastmod'    => $iso($s['updated_at']),
            'changefreq' => 'monthly',
            'priority'   => '0.7',
        ];
    }
} catch (PDOException $e) {
    // Services tables may not be seeded yet — skip silently
}

// -----------------------------------------------------
// 3. Portfolio — parents, children, projects
// -----------------------------------------------------
try {
    $port_parents = $db->query("
        SELECT id, slug, updated_at
        FROM portfolio_categories
        WHERE parent_id IS NULL AND is_fixed = 1 AND status = 1
        ORDER BY order_num, id
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($port_parents as $p) {
        $urls[] = [
            'loc'        => $base . '/portfolio/' . $p['slug'] . '/',
            'lastmod'    => $iso($p['updated_at']),
            'changefreq' => 'weekly',
            'priority'   => '0.8',
        ];
    }

    $port_children = $db->query("
        SELECT c.slug, c.updated_at, p.slug AS parent_slug
        FROM portfolio_categories c
        JOIN portfolio_categories p ON c.parent_id = p.id
        WHERE c.parent_id IS NOT NULL AND c.is_fixed = 0 AND c.status = 1
          AND p.status = 1 AND p.is_fixed = 1
        ORDER BY c.order_num, c.id
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($port_children as $c) {
        $urls[] = [
            'loc'        => $base . '/portfolio/' . $c['parent_slug'] . '/' . $c['slug'] . '/',
            'lastmod'    => $iso($c['updated_at']),
            'changefreq' => 'weekly',
            'priority'   => '0.7',
        ];
    }

    $projects = $db->query("
        SELECT pr.slug, pr.updated_at, c.slug AS child_slug, p.slug AS parent_slug
        FROM portfolio_projects pr
        JOIN portfolio_categories c ON pr.category_id = c.id
        JOIN portfolio_categories p ON c.parent_id = p.id
        WHERE pr.status = 1 AND c.status = 1 AND p.status = 1
          AND c.is_fixed = 0 AND p.is_fixed = 1
        ORDER BY pr.sort_order, pr.id
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($projects as $pr) {
        $urls[] = [
            'loc'        => $base . '/portfolio/' . $pr['parent_slug'] . '/' . $pr['child_slug'] . '/' . $pr['slug'] . '/',
            'lastmod'    => $iso($pr['updated_at']),
            'changefreq' => 'monthly',
            'priority'   => '0.7',
        ];
    }
} catch (PDOException $e) {
    // Portfolio tables may not be seeded yet — skip silently
}

// -----------------------------------------------------
// 4. Blog — categories + published posts
// -----------------------------------------------------
try {
    $blog_cats = $db->query("
        SELECT slug, updated_at
        FROM blog_categories
        WHERE status = 1
        ORDER BY sort_order, id
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($blog_cats as $c) {
        $urls[] = [
            'loc'        => $base . '/blog/category/' . $c['slug'] . '/',
            'lastmod'    => $iso($c['updated_at']),
            'changefreq' => 'weekly',
            'priority'   => '0.6',
        ];
    }

    $posts = $db->query("
        SELECT slug, published_at, updated_at
        FROM blog_posts
        WHERE status = 'published'
          AND (published_at IS NULL OR published_at <= NOW())
        ORDER BY published_at DESC, id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($posts as $post) {
        $urls[] = [
            'loc'        => $base . '/blog/' . $post['slug'] . '/',
            'lastmod'    => $iso($post['updated_at'] ?: $post['published_at']),
            'changefreq' => 'monthly',
            'priority'   => '0.6',
        ];
    }
} catch (PDOException $e) {
    // Blog tables may not exist yet — skip silently
}

// =====================================================
// Output XML
// =====================================================
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
                            http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
<?php foreach ($urls as $u): ?>
    <url>
        <loc><?= $esc($u['loc']) ?></loc>
        <lastmod><?= $esc($u['lastmod']) ?></lastmod>
        <changefreq><?= $esc($u['changefreq']) ?></changefreq>
        <priority><?= $esc($u['priority']) ?></priority>
    </url>
<?php endforeach; ?>
</urlset>