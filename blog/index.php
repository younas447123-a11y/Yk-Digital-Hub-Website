<?php
require_once __DIR__ . '/../config/config.php';

$page_title = 'Blog | YK Digital Hub';
$page_description = 'Insights, guides, and news from YK Digital Hub.';
$page_canonical = BASE_URL . '/blog/';

$stmt = $db->query("
    SELECT p.*, c.name AS category_name, c.slug AS category_slug,
           u.name AS author_name
    FROM blog_posts p
    LEFT JOIN blog_categories c ON p.category_id = c.id
    LEFT JOIN users u ON p.author_id = u.id
    WHERE p.status = 'published' AND (p.published_at IS NULL OR p.published_at <= NOW())
    ORDER BY p.published_at DESC, p.id DESC
    LIMIT 30
");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cats = $db->query("
    SELECT c.*, (SELECT COUNT(*) FROM blog_posts p WHERE p.category_id = c.id AND p.status='published') AS cnt
    FROM blog_categories c WHERE c.status = 1 ORDER BY c.sort_order, c.name
")->fetchAll(PDO::FETCH_ASSOC);

ob_start();
include ROOT_PATH . '/includes/header.php';
if (file_exists(ROOT_PATH . '/includes/navbar.php')) include ROOT_PATH . '/includes/navbar.php';
?>
<section class="blog-hero">
    <div class="container">
        <h1>Blog &amp; Insights</h1>
        <p>Strategy, design, and marketing insights from YK Digital Hub.</p>
    </div>
</section>

<section class="blog-listing">
    <div class="container">
        <?php if (!empty($cats)): ?>
            <ul class="blog-cat-nav">
                <?php foreach ($cats as $c): ?>
                    <li><a href="<?= BASE_URL ?>/blog/category/<?= htmlspecialchars($c['slug']) ?>/"><?= htmlspecialchars($c['name']) ?> <span>(<?= (int)$c['cnt'] ?>)</span></a></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if (empty($posts)): ?>
            <p class="empty-state">No blog posts yet. Check back soon.</p>
        <?php else: ?>
            <div class="blog-grid">
                <?php foreach ($posts as $p): ?>
                    <article class="blog-card">
                        <a href="<?= BASE_URL ?>/blog/<?= htmlspecialchars($p['slug']) ?>/" class="blog-card-image">
                            <?php if (!empty($p['featured_image'])): ?>
                                <img src="<?= htmlspecialchars($p['featured_image']) ?>" alt="<?= htmlspecialchars($p['featured_image_alt'] ?: $p['title']) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="blog-card-placeholder"><i class="fas fa-newspaper"></i></div>
                            <?php endif; ?>
                        </a>
                        <div class="blog-card-body">
                            <?php if (!empty($p['category_name'])): ?>
                                <a class="blog-cat" href="<?= BASE_URL ?>/blog/category/<?= htmlspecialchars($p['category_slug']) ?>/"><?= htmlspecialchars($p['category_name']) ?></a>
                            <?php endif; ?>
                            <h2><a href="<?= BASE_URL ?>/blog/<?= htmlspecialchars($p['slug']) ?>/"><?= htmlspecialchars($p['title']) ?></a></h2>
                            <?php if (!empty($p['excerpt'])): ?><p><?= htmlspecialchars(mb_strimwidth($p['excerpt'], 0, 160, '…')) ?></p><?php endif; ?>
                            <div class="blog-meta">
                                <?php if (!empty($p['author_name'])): ?><span><i class="fas fa-user"></i> <?= htmlspecialchars($p['author_name']) ?></span><?php endif; ?>
                                <?php if (!empty($p['published_at'])): ?><span><i class="fas fa-calendar"></i> <?= date('M j, Y', strtotime($p['published_at'])) ?></span><?php endif; ?>
                                <?php if (!empty($p['reading_time'])): ?><span><i class="fas fa-clock"></i> <?= (int)$p['reading_time'] ?> min</span><?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
include ROOT_PATH . '/includes/footer.php';
ob_end_flush();