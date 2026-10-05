<?php
require_once __DIR__ . '/../config/config.php';

$slug = trim($_GET['slug'] ?? '');
$cat_slug = trim($_GET['category_slug'] ?? '');
$response_code = 200;
$mode = null; $post = null; $category = null; $posts = [];

if ($cat_slug !== '') {
    // Explicit category route
    if (!preg_match('/^[a-z0-9-]+$/', $cat_slug)) { $response_code = 404; }
    else {
        $stmt = $db->prepare("SELECT * FROM blog_categories WHERE slug = :s AND status = 1");
        $stmt->execute([':s' => $cat_slug]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$category) { $response_code = 404; }
        else { $mode = 'category'; }
    }
} elseif ($slug !== '') {
    if (!preg_match('/^[a-z0-9-]+$/', $slug)) { $response_code = 404; }
    else {
        // Try category first (so /blog/seo/ works as category)
        $stmt = $db->prepare("SELECT * FROM blog_categories WHERE slug = :s AND status = 1");
        $stmt->execute([':s' => $slug]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($category) { $mode = 'category'; }
        else {
            // Try post
            $stmt = $db->prepare("
                SELECT p.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name, u.bio AS author_bio, u.profile_image AS author_image
                FROM blog_posts p
                LEFT JOIN blog_categories c ON p.category_id = c.id
                LEFT JOIN users u ON p.author_id = u.id
                WHERE p.slug = :s AND p.status = 'published' AND (p.published_at IS NULL OR p.published_at <= NOW())
                LIMIT 1
            ");
            $stmt->execute([':s' => $slug]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$post) { $response_code = 404; }
            else { $mode = 'post'; }
        }
    }
} else { $response_code = 404; }

if ($response_code === 404) {
    http_response_code(404);
    if (file_exists(ROOT_PATH . '/404.php')) include ROOT_PATH . '/404.php';
    else echo '<h1>404 — Not found</h1>';
    exit;
}

if ($mode === 'category') {
    // Category listing
    $stmt = $db->prepare("
        SELECT p.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name
        FROM blog_posts p
        LEFT JOIN blog_categories c ON p.category_id = c.id
        LEFT JOIN users u ON p.author_id = u.id
        WHERE p.category_id = :cid AND p.status = 'published' AND (p.published_at IS NULL OR p.published_at <= NOW())
        ORDER BY p.published_at DESC, p.id DESC
    ");
    $stmt->execute([':cid' => $category['id']]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $page_title = $category['name'] . ' — Blog | YK Digital Hub';
    $page_description = $category['description'] ?: ('Posts in ' . $category['name']);
    $page_canonical = BASE_URL . '/blog/category/' . $category['slug'] . '/';
} else {
    // Individual post
    $page_title = $post['seo_title'] ?: ($post['title'] . ' | YK Digital Hub');
    $page_description = $post['meta_description'] ?: ($post['excerpt'] ?: '');
    $page_canonical = $post['canonical_url'] ?: (BASE_URL . '/blog/' . $post['slug'] . '/');

    // Tags
    $ts = $db->prepare("SELECT t.id, t.name, t.slug FROM blog_tags t JOIN blog_post_tags pt ON pt.tag_id = t.id WHERE pt.post_id = :pid");
    $ts->execute([':pid' => $post['id']]);
    $post_tags = $ts->fetchAll(PDO::FETCH_ASSOC);

    // Related (same category, else shared tags)
    $rel = $db->prepare("
        SELECT p.id, p.title, p.slug, p.featured_image, p.excerpt, p.published_at
        FROM blog_posts p
        WHERE p.status = 'published' AND p.id != :pid
          AND (p.published_at IS NULL OR p.published_at <= NOW())
          AND p.category_id = :cid
        ORDER BY p.published_at DESC LIMIT 3
    ");
    $rel->execute([':pid' => $post['id'], ':cid' => $post['category_id']]);
    $related = $rel->fetchAll(PDO::FETCH_ASSOC);

    // View counter
    $db->prepare("UPDATE blog_posts SET views = views + 1 WHERE id = :id")->execute([':id' => $post['id']]);
}

ob_start();
include ROOT_PATH . '/includes/header.php';
if (file_exists(ROOT_PATH . '/includes/navbar.php')) include ROOT_PATH . '/includes/navbar.php';
?>

<nav aria-label="Breadcrumb" class="breadcrumb-wrapper">
    <div class="container">
        <ol class="breadcrumb">
            <li><a href="<?= BASE_URL ?>">Home</a></li>
            <li><a href="<?= BASE_URL ?>/blog/">Blog</a></li>
            <?php if ($mode === 'category'): ?>
                <li aria-current="page"><?= htmlspecialchars($category['name']) ?></li>
            <?php else: ?>
                <?php if (!empty($post['category_name'])): ?>
                    <li><a href="<?= BASE_URL ?>/blog/category/<?= htmlspecialchars($post['category_slug']) ?>/"><?= htmlspecialchars($post['category_name']) ?></a></li>
                <?php endif; ?>
                <li aria-current="page"><?= htmlspecialchars($post['title']) ?></li>
            <?php endif; ?>
        </ol>
    </div>
</nav>

<?php if ($mode === 'category'): ?>
    <article class="blog-article blog-category-page">
        <div class="container container-narrow">
            <header class="blog-article-header">
                <h1><?= htmlspecialchars($category['name']) ?></h1>
                <?php if (!empty($category['description'])): ?><p class="lead"><?= htmlspecialchars($category['description']) ?></p><?php endif; ?>
            </header>

            <?php if (empty($posts)): ?>
                <p class="empty-state">No posts in this category yet.</p>
            <?php else: ?>
                <div class="blog-grid">
                    <?php foreach ($posts as $p): ?>
                        <article class="blog-card">
                            <a href="<?= BASE_URL ?>/blog/<?= htmlspecialchars($p['slug']) ?>/" class="blog-card-image">
                                <?php if (!empty($p['featured_image'])): ?>
                                    <img src="<?= htmlspecialchars($p['featured_image']) ?>" alt="<?= htmlspecialchars($p['featured_image_alt'] ?: $p['title']) ?>" width="800" height="500" loading="lazy">
                                <?php else: ?><div class="blog-card-placeholder"><i class="fas fa-newspaper"></i></div><?php endif; ?>
                            </a>
                            <div class="blog-card-body">
                                <h2><a href="<?= BASE_URL ?>/blog/<?= htmlspecialchars($p['slug']) ?>/"><?= htmlspecialchars($p['title']) ?></a></h2>
                                <?php if (!empty($p['excerpt'])): ?><p><?= htmlspecialchars(mb_strimwidth($p['excerpt'], 0, 160, '…')) ?></p><?php endif; ?>
                                <div class="blog-meta">
                                    <?php if (!empty($p['author_name'])): ?><span><i class="fas fa-user"></i> <?= htmlspecialchars($p['author_name']) ?></span><?php endif; ?>
                                    <?php if (!empty($p['published_at'])): ?><span><i class="fas fa-calendar"></i> <?= date('M j, Y', strtotime($p['published_at'])) ?></span><?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </article>

<?php else: ?>
    <article class="blog-article blog-post-page">
        <div class="container container-narrow">
            <header class="blog-article-header">
                <?php if (!empty($post['category_name'])): ?>
                    <a class="blog-cat" href="<?= BASE_URL ?>/blog/category/<?= htmlspecialchars($post['category_slug']) ?>/"><?= htmlspecialchars($post['category_name']) ?></a>
                <?php endif; ?>
                <h1><?= htmlspecialchars($post['title']) ?></h1>
                <div class="blog-meta">
                    <?php if (!empty($post['author_name'])): ?><span><i class="fas fa-user"></i> <?= htmlspecialchars($post['author_name']) ?></span><?php endif; ?>
                    <?php if (!empty($post['published_at'])): ?><span><i class="fas fa-calendar"></i> <?= date('M j, Y', strtotime($post['published_at'])) ?></span><?php endif; ?>
                    <?php if (!empty($post['reading_time'])): ?><span><i class="fas fa-clock"></i> <?= (int)$post['reading_time'] ?> min read</span><?php endif; ?>
                </div>
            </header>

            <?php if (!empty($post['featured_image'])): ?>
                <figure class="blog-featured-image">
                    <img src="<?= htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['featured_image_alt'] ?: $post['title']) ?>" width="1200" height="800">
                </figure>
            <?php endif; ?>

            <div class="blog-content">
                <?= sanitize_post_html($post['content']) ?>
            </div>

            <?php if (!empty($post_tags)): ?>
                <div class="blog-tags">
                    <strong>Tags:</strong>
                    <?php foreach ($post_tags as $t): ?>
                        <span class="blog-tag">#<?= htmlspecialchars($t['name']) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($related)): ?>
        <section class="blog-related">
            <div class="container">
                <h2>Related Posts</h2>
                <div class="blog-grid">
                    <?php foreach ($related as $r): ?>
                        <article class="blog-card">
                            <a href="<?= BASE_URL ?>/blog/<?= htmlspecialchars($r['slug']) ?>/" class="blog-card-image">
                                <?php if (!empty($r['featured_image'])): ?>
                                    <img src="<?= htmlspecialchars($r['featured_image']) ?>" alt="<?= htmlspecialchars($r['title']) ?>" width="800" height="500" loading="lazy">
                                <?php else: ?><div class="blog-card-placeholder"><i class="fas fa-newspaper"></i></div><?php endif; ?>
                            </a>
                            <div class="blog-card-body">
                                <h3><a href="<?= BASE_URL ?>/blog/<?= htmlspecialchars($r['slug']) ?>/"><?= htmlspecialchars($r['title']) ?></a></h3>
                                <?php if (!empty($r['excerpt'])): ?><p><?= htmlspecialchars(mb_strimwidth($r['excerpt'], 0, 120, '…')) ?></p><?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </article>
<?php endif; ?>

<?php
include ROOT_PATH . '/includes/footer.php';
ob_end_flush();

// JSON-LD
if ($mode === 'post') {
    $ld = [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => $post['title'],
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $page_canonical],
        'publisher' => ['@type' => 'Organization', 'name' => 'YK Digital Hub'],
    ];
    if (!empty($post['excerpt'])) $ld['description'] = $post['excerpt'];
    if (!empty($post['featured_image'])) $ld['image'] = BASE_URL . $post['featured_image'];
    if (!empty($post['author_name'])) $ld['author'] = ['@type' => 'Person', 'name' => $post['author_name']];
    if (!empty($post['published_at'])) $ld['datePublished'] = date('c', strtotime($post['published_at']));
    if (!empty($post['updated_at'])) $ld['dateModified'] = date('c', strtotime($post['updated_at']));
    echo '<script type="application/ld+json">' . json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
}