<?php
/**
 * Web Design & Development Case Study Frontend
 * URL: /portfolio/web-design-development/{child-slug}/{project-slug}/
 * Dynamically renders any active project under the "Web Design & Development" parent.
 */
require_once __DIR__ . '/../../config/config.php';

$response_code = 200;
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
if ($slug === '' || !preg_match('/^[a-z0-9-]+$/', $slug)) {
    $response_code = 404;
}

$project = $child = $parent = null;

if ($response_code === 200) {
    // Fetch project + verify full hierarchy belongs to Web Design & Development
    $stmt = $db->prepare("
        SELECT
            p.*,
            c.id AS child_id, c.name AS child_name, c.slug AS child_slug, c.status AS child_status,
            par.id AS parent_id, par.name AS parent_name, par.slug AS parent_slug, par.status AS parent_status
        FROM portfolio_projects p
        JOIN portfolio_categories c ON p.category_id = c.id
        JOIN portfolio_categories par ON c.parent_id = par.id
        WHERE p.slug = :slug
          AND p.status = 1
          AND c.status = 1
          AND par.status = 1
          AND c.is_fixed = 0
          AND c.parent_id IS NOT NULL
          AND par.is_fixed = 1
          AND par.parent_id IS NULL
          AND par.slug = 'web-design-development'
        LIMIT 1
    ");
    $stmt->execute([':slug' => $slug]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        $response_code = 404;
    } else {
        $child  = ['id' => $project['child_id'],  'name' => $project['child_name'],  'slug' => $project['child_slug']];
        $parent = ['id' => $project['parent_id'], 'name' => $project['parent_name'], 'slug' => $project['parent_slug']];
    }
}

// ---- 404 ----
if ($response_code === 404) {
    http_response_code(404);
    if (file_exists(ROOT_PATH . '/404.php')) { include ROOT_PATH . '/404.php'; }
    else { echo "<h1>404 — Case study not found.</h1>"; }
    exit;
}

// ---- Load child content ----
$pid = (int)$project['id'];
$fetch = function($table, $extra_where = '') use ($db, $pid) {
    $sql = "SELECT * FROM `$table` WHERE project_id = :pid AND status = 1 $extra_where ORDER BY sort_order ASC, id ASC";
    $s = $db->prepare($sql);
    $s->execute([':pid' => $pid]);
    return $s->fetchAll(PDO::FETCH_ASSOC);
};

$challenges  = $fetch('portfolio_challenges');
$solutions   = $fetch('portfolio_solutions');
$features    = $fetch('portfolio_features');
$sections    = $fetch('portfolio_sections');
$results     = $fetch('portfolio_results');

// Images: split by type
$all_images = $fetch('portfolio_images');
$hero_imgs   = array_values(array_filter($all_images, fn($i) => $i['image_type'] === 'hero'));
$before_imgs = array_values(array_filter($all_images, fn($i) => $i['image_type'] === 'before'));
$after_imgs  = array_values(array_filter($all_images, fn($i) => $i['image_type'] === 'after'));
$gallery     = array_values(array_filter($all_images, fn($i) => in_array($i['image_type'], ['gallery','desktop','mobile','tablet','mockup','result',''], true)));

// Technologies: comma-separated string → array
$technologies = [];
if (!empty($project['technologies'])) {
    $technologies = array_values(array_filter(array_map('trim', explode(',', $project['technologies']))));
}

// Related projects: same child category first, then other Web Dev projects
$rel_stmt = $db->prepare("
    SELECT p.id, p.title, p.slug, p.card_description, p.short_description, p.hero_image,
           c.slug AS child_slug, c.name AS child_name
    FROM portfolio_projects p
    JOIN portfolio_categories c ON p.category_id = c.id
    JOIN portfolio_categories par ON c.parent_id = par.id
    WHERE p.status = 1
      AND p.id != :pid
      AND par.slug = 'web-design-development'
      AND par.is_fixed = 1
      AND c.status = 1
      AND par.status = 1
    ORDER BY (c.id = :cid) DESC, p.featured DESC, p.sort_order ASC, p.id DESC
    LIMIT 6
");
$rel_stmt->execute([':pid' => $pid, ':cid' => $child['id']]);
$related = $rel_stmt->fetchAll(PDO::FETCH_ASSOC);

// ---- SEO ----
$seo_title       = $project['seo_title']       ?: ($project['title'] . ' — Case Study | YK Digital Hub');
$meta_description= $project['meta_description'] ?: ($project['short_description'] ?: $project['card_description'] ?: '');
$canonical       = $project['canonical_url']   ?: (BASE_URL . '/portfolio/web-design-development/' . $child['slug'] . '/' . $project['slug'] . '/');
$og_title        = $project['og_title']        ?: $seo_title;
$og_description  = $project['og_description']  ?: $meta_description;
$og_image        = $project['og_image']        ?: ($project['hero_image'] ?: '');
$robots          = $project['robots']          ?: 'index, follow';

$page_title       = $seo_title;
$page_description = $meta_description;
$page_canonical   = $canonical;

// Hero fallbacks
$hero_title  = $project['hero_title']       ?: $project['title'];
$hero_sub    = $project['hero_subtitle']    ?: '';
$hero_desc   = $project['hero_description'] ?: ($project['short_description'] ?: $project['card_description'] ?: '');
$hero_image  = $project['hero_image']       ?: ($hero_imgs[0]['image'] ?? '');

ob_start();
include ROOT_PATH . '/includes/header.php';
if (file_exists(ROOT_PATH . '/includes/navbar.php')) include ROOT_PATH . '/includes/navbar.php';
?>

<!-- ===================== BREADCRUMB ===================== -->
<nav aria-label="Breadcrumb" class="breadcrumb-wrapper">
    <div class="container">
        <ol class="breadcrumb">
            <li><a href="<?= BASE_URL ?>">Home</a></li>
            <li><a href="<?= BASE_URL ?>/portfolio/">Portfolio</a></li>
            <li><a href="<?= BASE_URL ?>/portfolio/web-design-development/"><?= htmlspecialchars($parent['name']) ?></a></li>
            <li><a href="<?= BASE_URL ?>/portfolio/web-design-development/<?= htmlspecialchars($child['slug']) ?>/"><?= htmlspecialchars($child['name']) ?></a></li>
            <li aria-current="page"><?= htmlspecialchars($project['title']) ?></li>
        </ol>
    </div>
</nav>

<main class="case-study">

    <!-- ===================== HERO ===================== -->
    <section class="cs-hero<?= $hero_image ? ' has-image' : '' ?>">
        <div class="container">
            <div class="cs-hero-grid">
                <div class="cs-hero-content">
                    <span class="cs-badge"><?= htmlspecialchars($parent['name']) ?> · <?= htmlspecialchars($child['name']) ?></span>
                    <h1><?= htmlspecialchars($hero_title) ?></h1>
                    <?php if ($hero_sub): ?><p class="cs-hero-sub"><?= htmlspecialchars($hero_sub) ?></p><?php endif; ?>
                    <?php if ($hero_desc): ?><div class="cs-hero-desc"><?= nl2br(htmlspecialchars($hero_desc)) ?></div><?php endif; ?>

                    <div class="cs-hero-cta">
                        <?php if (!empty($project['cta_button_text']) && !empty($project['cta_button_url'])): ?>
                            <a href="<?= htmlspecialchars($project['cta_button_url']) ?>" class="btn btn-primary cs-audit-btn"><?= htmlspecialchars($project['cta_button_text']) ?></a>
                        <?php endif; ?>
                        <?php if (!empty($project['website_url'])): ?>
                            <a href="<?= htmlspecialchars($project['website_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-secondary cs-visit-btn">Visit Live Site ↗</a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($hero_image): ?>
                    <div class="cs-hero-image">
                        <img src="<?= htmlspecialchars($hero_image) ?>" alt="<?= htmlspecialchars($project['title']) ?>" loading="eager">
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ===================== PROJECT INFO BAR ===================== -->
    <?php
    $info = array_filter([
        'Client'      => $project['client_name'],
        'Industry'    => $project['industry'],
        'Location'    => $project['location'],
        'Year'        => $project['project_year'],
        'Duration'    => $project['project_duration'],
        'Platform'    => $project['platform'],
        'Team'        => $project['team_size'] ? $project['team_size'] . ' people' : '',
    ]);
    if ($info): ?>
    <section class="cs-info-bar">
        <div class="container">
            <ul class="cs-info-list">
                <?php foreach ($info as $label => $val): ?>
                    <li>
                        <span class="label"><?= htmlspecialchars($label) ?></span>
                        <span class="value"><?= htmlspecialchars((string)$val) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===================== PROJECT OVERVIEW ===================== -->
    <?php if (!empty($project['description']) || !empty($project['project_summary']) || !empty($project['project_goal'])): ?>
    <section class="cs-overview">
        <div class="container cs-narrow">
            <h2>Project Overview</h2>
            <?php if (!empty($project['description'])): ?>
                <div class="cs-text"><?= nl2br(htmlspecialchars($project['description'])) ?></div>
            <?php endif; ?>
            <?php if (!empty($project['project_goal'])): ?>
                <h3>Project Goal</h3>
                <div class="cs-text"><?= nl2br(htmlspecialchars($project['project_goal'])) ?></div>
            <?php endif; ?>
            <?php if (!empty($project['project_summary'])): ?>
                <h3>Summary</h3>
                <div class="cs-text"><?= nl2br(htmlspecialchars($project['project_summary'])) ?></div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===================== CHALLENGE ===================== -->
    <?php if (!empty($project['client_challenge']) || !empty($challenges)): ?>
    <section class="cs-challenge">
        <div class="container">
            <h2>The Challenge</h2>
            <?php if (!empty($project['client_challenge'])): ?>
                <div class="cs-text cs-narrow"><?= nl2br(htmlspecialchars($project['client_challenge'])) ?></div>
            <?php endif; ?>

            <?php if (!empty($challenges)): ?>
                <div class="cs-challenge-grid">
                    <?php foreach ($challenges as $i => $c): ?>
                        <div class="cs-challenge-card">
                            <span class="cs-num"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                            <?php if (!empty($c['icon'])): ?><div class="icon"><i class="<?= htmlspecialchars($c['icon']) ?>"></i></div><?php endif; ?>
                            <h3><?= htmlspecialchars($c['title']) ?></h3>
                            <?php if (!empty($c['description'])): ?><p><?= htmlspecialchars($c['description']) ?></p><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===================== SOLUTION ===================== -->
    <?php if (!empty($solutions)): ?>
    <section class="cs-solutions">
        <div class="container">
            <h2>The Solution</h2>
            <div class="cs-solutions-grid">
                <?php foreach ($solutions as $s): ?>
                    <div class="cs-solution-card">
                        <?php if (!empty($s['icon'])): ?><div class="icon"><i class="<?= htmlspecialchars($s['icon']) ?>"></i></div><?php endif; ?>
                        <h3><?= htmlspecialchars($s['title']) ?></h3>
                        <?php if (!empty($s['description'])): ?><p><?= htmlspecialchars($s['description']) ?></p><?php endif; ?>
                        <?php if (!empty($s['image'])): ?><img src="<?= htmlspecialchars($s['image']) ?>" alt="<?= htmlspecialchars($s['title']) ?>" loading="lazy"><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===================== CUSTOM SECTIONS ===================== -->
    <?php if (!empty($sections)): ?>
        <?php foreach ($sections as $i => $sec):
            $has_image = !empty($sec['image']);
            $layout = ($has_image && ($i % 2 === 0)) ? 'image-right' : ($has_image ? 'image-left' : 'full');
        ?>
        <section class="cs-section cs-section-<?= $layout ?>" <?= !empty($sec['background_image']) ? 'style="background-image:url(' . htmlspecialchars($sec['background_image']) . ');"' : '' ?>>
            <div class="container">
                <div class="cs-section-inner">
                    <div class="cs-section-text">
                        <?php if (!empty($sec['section_label'])): ?><span class="cs-label"><?= htmlspecialchars($sec['section_label']) ?></span><?php endif; ?>
                        <?php if (!empty($sec['heading'])): ?><h2><?= htmlspecialchars($sec['heading']) ?></h2><?php endif; ?>
                        <?php if (!empty($sec['subheading'])): ?><p class="subheading"><?= htmlspecialchars($sec['subheading']) ?></p><?php endif; ?>
                        <?php if (!empty($sec['content'])): ?><div class="cs-text"><?= nl2br(htmlspecialchars($sec['content'])) ?></div><?php endif; ?>
                        <?php if (!empty($sec['video_url'])): ?>
                            <div class="cs-video"><iframe src="<?= htmlspecialchars($sec['video_url']) ?>" allowfullscreen loading="lazy"></iframe></div>
                        <?php endif; ?>
                    </div>
                    <?php if ($has_image): ?>
                        <div class="cs-section-image">
                            <img src="<?= htmlspecialchars($sec['image']) ?>" alt="<?= htmlspecialchars($sec['heading'] ?: $project['title']) ?>" loading="lazy">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- ===================== FEATURES ===================== -->
    <?php if (!empty($features)): ?>
    <section class="cs-features">
        <div class="container">
            <h2>Key Features</h2>
            <div class="cs-features-grid">
                <?php foreach ($features as $f): ?>
                    <div class="cs-feature">
                        <?php if (!empty($f['icon'])): ?><div class="icon"><i class="<?= htmlspecialchars($f['icon']) ?>"></i></div><?php endif; ?>
                        <h3><?= htmlspecialchars($f['title']) ?></h3>
                        <?php if (!empty($f['description'])): ?><p><?= htmlspecialchars($f['description']) ?></p><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===================== TECHNOLOGIES ===================== -->
    <?php if (!empty($technologies)): ?>
    <section class="cs-technologies">
        <div class="container">
            <h2>Technologies Used</h2>
            <ul class="cs-tech-list">
                <?php foreach ($technologies as $tech): ?>
                    <li class="cs-tech"><?= htmlspecialchars($tech) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===================== BEFORE / AFTER ===================== -->
    <?php if (!empty($before_imgs) || !empty($after_imgs)): ?>
    <section class="cs-before-after">
        <div class="container">
            <h2>Before &amp; After</h2>
            <div class="cs-ba-grid">
                <?php if (!empty($before_imgs)): $b = $before_imgs[0]; ?>
                <figure class="cs-ba-item">
                    <span class="cs-ba-label">Before</span>
                    <img src="<?= htmlspecialchars($b['image']) ?>" alt="<?= htmlspecialchars($b['alt_text'] ?: 'Before') ?>" loading="lazy">
                    <?php if (!empty($b['caption'])): ?><figcaption><?= htmlspecialchars($b['caption']) ?></figcaption><?php endif; ?>
                </figure>
                <?php endif; ?>
                <?php if (!empty($after_imgs)): $a = $after_imgs[0]; ?>
                <figure class="cs-ba-item">
                    <span class="cs-ba-label">After</span>
                    <img src="<?= htmlspecialchars($a['image']) ?>" alt="<?= htmlspecialchars($a['alt_text'] ?: 'After') ?>" loading="lazy">
                    <?php if (!empty($a['caption'])): ?><figcaption><?= htmlspecialchars($a['caption']) ?></figcaption><?php endif; ?>
                </figure>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===================== GALLERY ===================== -->
    <?php if (!empty($gallery)): ?>
    <section class="cs-gallery">
        <div class="container">
            <h2>Project Gallery</h2>
            <div class="cs-gallery-grid">
                <?php foreach ($gallery as $img): ?>
                    <figure class="cs-gallery-item">
                        <img src="<?= htmlspecialchars($img['image']) ?>" alt="<?= htmlspecialchars($img['alt_text'] ?: $project['title']) ?>" loading="lazy">
                        <?php if (!empty($img['caption'])): ?><figcaption><?= htmlspecialchars($img['caption']) ?></figcaption><?php endif; ?>
                    </figure>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===================== RESULTS ===================== -->
    <?php if (!empty($results)): ?>
    <section class="cs-results">
        <div class="container">
            <h2>Results &amp; Impact</h2>
            <div class="cs-results-grid">
                <?php foreach ($results as $r): ?>
                    <div class="cs-result">
                        <div class="cs-result-value"><?= htmlspecialchars($r['metric_value']) ?></div>
                        <div class="cs-result-label"><?= htmlspecialchars($r['metric_label']) ?></div>
                        <?php if (!empty($r['description'])): ?><div class="cs-result-desc"><?= htmlspecialchars($r['description']) ?></div><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===================== CTA ===================== -->
    <section class="cs-cta">
        <div class="container">
            <div class="cs-cta-box">
                <?php if (!empty($project['cta_title'])): ?>
                    <h2><?= htmlspecialchars($project['cta_title']) ?></h2>
                <?php else: ?>
                    <h2>Want a website like this?</h2>
                <?php endif; ?>
                <?php if (!empty($project['cta_description'])): ?>
                    <p><?= htmlspecialchars($project['cta_description']) ?></p>
                <?php endif; ?>
                <?php
                $cta_text = $project['cta_button_text'] ?: 'Start Your Project';
                $cta_url  = $project['cta_button_url']  ?: (BASE_URL . '/contact');
                ?>
                <a href="<?= htmlspecialchars($cta_url) ?>" class="btn btn-primary"><?= htmlspecialchars($cta_text) ?></a>
            </div>
        </div>
    </section>

    <!-- ===================== RELATED CASE STUDIES ===================== -->
    <?php if (!empty($related)): ?>
    <section class="cs-related">
        <div class="container">
            <h2>More Case Studies</h2>
            <div class="cs-related-grid">
                <?php foreach ($related as $rp): ?>
                    <a class="cs-related-card" href="<?= BASE_URL ?>/portfolio/web-design-development/<?= htmlspecialchars($rp['child_slug']) ?>/<?= htmlspecialchars($rp['slug']) ?>/">
                        <?php if (!empty($rp['hero_image'])): ?>
                            <img src="<?= htmlspecialchars($rp['hero_image']) ?>" alt="<?= htmlspecialchars($rp['title']) ?>" loading="lazy">
                        <?php endif; ?>
                        <span class="cs-related-cat"><?= htmlspecialchars($rp['child_name']) ?></span>
                        <h3><?= htmlspecialchars($rp['title']) ?></h3>
                        <p><?= htmlspecialchars(mb_strimwidth((string)($rp['card_description'] ?: $rp['short_description'] ?: ''), 0, 120, '…')) ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

</main>

<?php
include ROOT_PATH . '/includes/footer.php';
ob_end_flush();

// ---- JSON-LD ----
$json_ld = [
    '@context' => 'https://schema.org',
    '@type'    => 'CreativeWork',
    'name'     => $project['title'],
    'about'    => $child['name'],
    'creator'  => ['@type' => 'Organization', 'name' => 'YK Digital Hub'],
    'url'      => $canonical,
];
if ($meta_description) $json_ld['description'] = $meta_description;
if ($og_image)         $json_ld['image']       = $og_image;
?>
<script type="application/ld+json"><?= json_encode($json_ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>