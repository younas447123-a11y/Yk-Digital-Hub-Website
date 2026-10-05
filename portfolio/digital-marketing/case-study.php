<?php
/**
 * Digital Marketing Case Study Frontend
 * URL: /portfolio/digital-marketing/{child-slug}/{project-slug}/
 * Renders any active project under the "Digital Marketing" fixed parent.
 */
require_once __DIR__ . '/../../config/config.php';

$response_code = 200;
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
if ($slug === '' || !preg_match('/^[a-z0-9-]+$/', $slug)) {
    $response_code = 404;
}

$project = $child = $parent = null;

if ($response_code === 200) {
    // Load project + verify hierarchy belongs to Digital Marketing
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
          AND par.slug = 'digital-marketing'
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

$pid = (int)$project['id'];

// ---- Helper: fetch active child records for this project ----
$fetch = function($table, $extra = '') use ($db, $pid) {
    $sql = "SELECT * FROM `$table` WHERE project_id = :pid AND status = 1 $extra ORDER BY sort_order ASC, id ASC";
    $s = $db->prepare($sql);
    $s->execute([':pid' => $pid]);
    return $s->fetchAll(PDO::FETCH_ASSOC);
};

// ---- Marketing case study main record (one-to-one) ----
$mcs_stmt = $db->prepare("SELECT * FROM marketing_case_studies WHERE project_id = :pid LIMIT 1");
$mcs_stmt->execute([':pid' => $pid]);
$mcs = $mcs_stmt->fetch(PDO::FETCH_ASSOC) ?: [];

// ---- Marketing-specific collections ----
$mkt_services  = $fetch('portfolio_marketing_services');
$mkt_channels  = $fetch('portfolio_marketing_channels');
$mkt_results   = $fetch('portfolio_marketing_results');

// ---- Shared collections ----
$challenges = $fetch('portfolio_challenges');
$solutions  = $fetch('portfolio_solutions');
$features   = $fetch('portfolio_features');
$sections   = $fetch('portfolio_sections');
$results    = $fetch('portfolio_results');

// ---- Images ----
$all_images  = $fetch('portfolio_images');
$hero_imgs   = array_values(array_filter($all_images, fn($i) => $i['image_type'] === 'hero'));
$before_imgs = array_values(array_filter($all_images, fn($i) => $i['image_type'] === 'before'));
$after_imgs  = array_values(array_filter($all_images, fn($i) => $i['image_type'] === 'after'));
$gallery     = array_values(array_filter($all_images, fn($i) => in_array($i['image_type'], ['gallery','desktop','mobile','tablet','mockup','result',''], true)));

// ---- Technologies (comma-separated) ----
$technologies = [];
if (!empty($project['technologies'])) {
    $technologies = array_values(array_filter(array_map('trim', explode(',', $project['technologies']))));
}

// ---- Related Digital Marketing projects ----
$rel_stmt = $db->prepare("
    SELECT p.id, p.title, p.slug, p.card_description, p.short_description, p.hero_image,
           c.slug AS child_slug, c.name AS child_name
    FROM portfolio_projects p
    JOIN portfolio_categories c ON p.category_id = c.id
    JOIN portfolio_categories par ON c.parent_id = par.id
    WHERE p.status = 1
      AND p.id != :pid
      AND par.slug = 'digital-marketing'
      AND par.is_fixed = 1
      AND c.status = 1
      AND par.status = 1
    ORDER BY (c.id = :cid) DESC, p.featured DESC, p.sort_order ASC, p.id DESC
    LIMIT 3
");
$rel_stmt->execute([':pid' => $pid, ':cid' => $child['id']]);
$related = $rel_stmt->fetchAll(PDO::FETCH_ASSOC);

// ---- SEO ----
$seo_title        = $project['seo_title']        ?: ($project['title'] . ' — Digital Marketing Case Study | YK Digital Hub');
$meta_description = $project['meta_description'] ?: ($project['short_description'] ?: $project['card_description'] ?: '');
$canonical        = $project['canonical_url']    ?: (BASE_URL . '/portfolio/digital-marketing/' . $child['slug'] . '/' . $project['slug'] . '/');
$og_title         = $project['og_title']         ?: $seo_title;
$og_description   = $project['og_description']   ?: $meta_description;
$og_image         = $project['og_image']         ?: ($project['hero_image'] ?: '');
$robots           = $project['robots']           ?: 'index, follow';

$page_title       = $seo_title;
$page_description = $meta_description;
$page_canonical   = $canonical;

// Hero fallbacks
$hero_title = $project['hero_title']       ?: $project['title'];
$hero_sub   = $project['hero_subtitle']    ?: '';
$hero_desc  = $project['hero_description'] ?: ($project['short_description'] ?: $project['card_description'] ?: '');
$hero_image = $project['hero_image']       ?: ($hero_imgs[0]['image'] ?? '');

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
            <li><a href="<?= BASE_URL ?>/portfolio/digital-marketing/"><?= htmlspecialchars($parent['name']) ?></a></li>
            <li><a href="<?= BASE_URL ?>/portfolio/digital-marketing/<?= htmlspecialchars($child['slug']) ?>/"><?= htmlspecialchars($child['name']) ?></a></li>
            <li aria-current="page"><?= htmlspecialchars($project['title']) ?></li>
        </ol>
    </div>
</nav>

<main class="dm-case-study">

    <!-- ===================== HERO ===================== -->
    <section class="dm-hero<?= $hero_image ? ' has-image' : '' ?>">
        <div class="container">
            <div class="dm-hero-grid">
                <div class="dm-hero-content">
                    <span class="dm-badge"><?= htmlspecialchars($parent['name']) ?> · <?= htmlspecialchars($child['name']) ?></span>
                    <h1><?= htmlspecialchars($hero_title) ?></h1>
                    <?php if ($hero_sub): ?><p class="dm-hero-sub"><?= htmlspecialchars($hero_sub) ?></p><?php endif; ?>
                    <?php if ($hero_desc): ?><div class="dm-hero-desc"><?= nl2br(htmlspecialchars($hero_desc)) ?></div><?php endif; ?>

                    <div class="dm-hero-cta">
                        <?php if (!empty($project['cta_button_text']) && !empty($project['cta_button_url'])): ?>
                            <a href="<?= htmlspecialchars($project['cta_button_url']) ?>" class="btn btn-primary dm-audit-btn"><?= htmlspecialchars($project['cta_button_text']) ?></a>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/contact" class="btn btn-primary">Request a Marketing Strategy</a>
                        <?php endif; ?>
                        <?php if (!empty($project['website_url'])): ?>
                            <a href="<?= htmlspecialchars($project['website_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-secondary dm-visit-btn">Visit Site ↗</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($hero_image): ?>
                    <div class="dm-hero-image">
                        <img src="<?= htmlspecialchars($hero_image) ?>" alt="<?= htmlspecialchars($project['title']) ?>" width="1600" height="900" loading="eager">
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ===================== PROJECT INFO BAR ===================== -->
    <?php
    $info = array_filter([
        'Client'       => $project['client_name'],
        'Industry'     => $project['industry'],
        'Location'     => $project['location'],
        'Duration'     => $project['project_duration'],
        'Campaign'     => $project['project_type'],
        'Platform'     => $project['platform'],
    ]);
    if ($info): ?>
    <section class="dm-info-bar">
        <div class="container">
            <ul class="dm-info-list">
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

    <!-- ===================== MARKETING OVERVIEW (from marketing_case_studies) ===================== -->
    <?php if (!empty($mcs['marketing_objective']) || !empty($mcs['target_audience']) || !empty($project['description'])):
        // The "overview" section is a summary combination of project description + marketing objective
    ?>
    <section class="dm-overview">
        <div class="container dm-narrow">
            <h2>Campaign Overview</h2>
            <?php if (!empty($project['description'])): ?>
                <div class="dm-text"><?= nl2br(htmlspecialchars($project['description'])) ?></div>
            <?php endif; ?>

            <div class="dm-overview-grid">
                <?php if (!empty($mcs['marketing_objective'])): ?>
                    <div class="dm-overview-card">
                        <h3>Marketing Objective</h3>
                        <p><?= nl2br(htmlspecialchars($mcs['marketing_objective'])) ?></p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($mcs['target_audience'])): ?>
                    <div class="dm-overview-card">
                        <h3>Target Audience</h3>
                        <p><?= nl2br(htmlspecialchars($mcs['target_audience'])) ?></p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($mcs['target_location'])): ?>
                    <div class="dm-overview-card">
                        <h3>Target Location</h3>
                        <p><?= htmlspecialchars($mcs['target_location']) ?></p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($mcs['campaign_duration']) || !empty($mcs['campaign_budget'])): ?>
                    <div class="dm-overview-card">
                        <h3>Campaign</h3>
                        <?php if (!empty($mcs['campaign_duration'])): ?><p><strong>Duration:</strong> <?= htmlspecialchars($mcs['campaign_duration']) ?></p><?php endif; ?>
                        <?php if (!empty($mcs['campaign_budget'])): ?><p><strong>Budget:</strong> <?= htmlspecialchars(number_format((float)$mcs['campaign_budget'], 2)) ?></p><?php endif; ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($mcs['starting_position'])): ?>
                    <div class="dm-overview-card dm-full">
                        <h3>Starting Position</h3>
                        <p><?= nl2br(htmlspecialchars($mcs['starting_position'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===================== THE CHALLENGE ===================== -->
    <?php if (!empty($project['client_challenge']) || !empty($challenges)): ?>
    <section class="dm-challenge">
        <div class="container">
            <h2>The Challenge</h2>
            <?php if (!empty($project['client_challenge'])): ?>
                <div class="dm-text dm-narrow"><?= nl2br(htmlspecialchars($project['client_challenge'])) ?></div>
            <?php endif; ?>

            <?php if (!empty($challenges)): ?>
                <div class="dm-challenge-grid">
                    <?php foreach ($challenges as $i => $c): ?>
                        <div class="dm-challenge-card">
                            <span class="dm-num"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
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

    <!-- ===================== RESEARCH & ANALYSIS / STRATEGY (from marketing_case_studies) ===================== -->
    <?php if (!empty($mcs['strategy_summary']) || !empty($mcs['execution_summary'])): ?>
    <section class="dm-strategy">
        <div class="container">
            <h2>Strategy &amp; Execution</h2>
            <div class="dm-strategy-grid">
                <?php if (!empty($mcs['strategy_summary'])): ?>
                    <div class="dm-strategy-card">
                        <div class="dm-strategy-icon"><i class="fas fa-lightbulb"></i></div>
                        <h3>Strategy</h3>
                        <div class="dm-text"><?= nl2br(htmlspecialchars($mcs['strategy_summary'])) ?></div>
                    </div>
                <?php endif; ?>
                <?php if (!empty($mcs['execution_summary'])): ?>
                    <div class="dm-strategy-card">
                        <div class="dm-strategy-icon"><i class="fas fa-rocket"></i></div>
                        <h3>Execution</h3>
                        <div class="dm-text"><?= nl2br(htmlspecialchars($mcs['execution_summary'])) ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===================== MARKETING SERVICES ===================== -->
    <?php if (!empty($mkt_services)): ?>
    <section class="dm-services">
        <div class="container">
            <h2>Marketing Services</h2>
            <div class="dm-services-grid">
                <?php foreach ($mkt_services as $s): ?>
                    <div class="dm-service-card">
                        <?php if (!empty($s['icon'])): ?><div class="icon"><i class="<?= htmlspecialchars($s['icon']) ?>"></i></div><?php endif; ?>
                        <h3><?= htmlspecialchars($s['service_name']) ?></h3>
                        <?php if (!empty($s['description'])): ?><p><?= htmlspecialchars($s['description']) ?></p><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===================== MARKETING CHANNELS ===================== -->
    <?php if (!empty($mkt_channels)): ?>
    <section class="dm-channels">
        <div class="container">
            <h2>Channels Used</h2>
            <ul class="dm-channel-list">
                <?php foreach ($mkt_channels as $ch): ?>
                    <li class="dm-channel">
                        <?php if (!empty($ch['icon'])): ?><span class="icon"><i class="<?= htmlspecialchars($ch['icon']) ?>"></i></span><?php endif; ?>
                        <span class="name"><?= htmlspecialchars($ch['channel_name']) ?></span>
                        <?php if (!empty($ch['description'])): ?><span class="desc"><?= htmlspecialchars($ch['description']) ?></span><?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===================== SOLUTIONS ===================== -->
    <?php if (!empty($solutions)): ?>
    <section class="dm-solutions">
        <div class="container">
            <h2>Our Solutions</h2>
            <div class="dm-solutions-grid">
                <?php foreach ($solutions as $s): ?>
                    <div class="dm-solution-card">
                        <?php if (!empty($s['icon'])): ?><div class="icon"><i class="<?= htmlspecialchars($s['icon']) ?>"></i></div><?php endif; ?>
                        <h3><?= htmlspecialchars($s['title']) ?></h3>
                        <?php if (!empty($s['description'])): ?><p><?= htmlspecialchars($s['description']) ?></p><?php endif; ?>
                        <?php if (!empty($s['image'])): ?><img src="<?= htmlspecialchars($s['image']) ?>" alt="<?= htmlspecialchars($s['title']) ?>" width="1200" height="800" loading="lazy"><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===================== FEATURES / ACTIVITIES ===================== -->
    <?php if (!empty($features)): ?>
    <section class="dm-features">
        <div class="container">
            <h2>Marketing Activities</h2>
            <div class="dm-features-grid">
                <?php foreach ($features as $f): ?>
                    <div class="dm-feature">
                        <?php if (!empty($f['icon'])): ?><div class="icon"><i class="<?= htmlspecialchars($f['icon']) ?>"></i></div><?php endif; ?>
                        <h3><?= htmlspecialchars($f['title']) ?></h3>
                        <?php if (!empty($f['description'])): ?><p><?= htmlspecialchars($f['description']) ?></p><?php endif; ?>
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
        <section class="dm-section dm-section-<?= $layout ?>" <?= !empty($sec['background_image']) ? 'style="background-image:url(' . htmlspecialchars($sec['background_image']) . ');"' : '' ?>>
            <div class="container">
                <div class="dm-section-inner">
                    <div class="dm-section-text">
                        <?php if (!empty($sec['section_label'])): ?><span class="dm-label"><?= htmlspecialchars($sec['section_label']) ?></span><?php endif; ?>
                        <?php if (!empty($sec['heading'])): ?><h2><?= htmlspecialchars($sec['heading']) ?></h2><?php endif; ?>
                        <?php if (!empty($sec['subheading'])): ?><p class="subheading"><?= htmlspecialchars($sec['subheading']) ?></p><?php endif; ?>
                        <?php if (!empty($sec['content'])): ?><div class="dm-text"><?= nl2br(htmlspecialchars($sec['content'])) ?></div><?php endif; ?>
                        <?php if (!empty($sec['video_url'])): ?>
                            <div class="dm-video"><iframe src="<?= htmlspecialchars($sec['video_url']) ?>" allowfullscreen loading="lazy"></iframe></div>
                        <?php endif; ?>
                    </div>
                    <?php if ($has_image): ?>
                        <div class="dm-section-image">
                            <img src="<?= htmlspecialchars($sec['image']) ?>" alt="<?= htmlspecialchars($sec['heading'] ?: $project['title']) ?>" width="1200" height="800" loading="lazy">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- ===================== MARKETING RESULTS (portfolio_marketing_results) ===================== -->
    <?php if (!empty($mkt_results)): ?>
    <section class="dm-mkt-results">
        <div class="container">
            <h2>Performance Metrics</h2>
            <div class="dm-mkt-results-grid">
                <?php foreach ($mkt_results as $r): ?>
                    <div class="dm-mkt-result">
                        <div class="dm-mkt-value"><?= htmlspecialchars($r['metric_value']) ?></div>
                        <div class="dm-mkt-label"><?= htmlspecialchars($r['metric_name']) ?></div>
                        <?php if (!empty($r['improvement_percentage'])): ?>
                            <div class="dm-mkt-improvement">Improvement: <?= htmlspecialchars($r['improvement_percentage']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($r['description'])): ?>
                            <div class="dm-mkt-desc"><?= htmlspecialchars($r['description']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($r['source'])): ?>
                            <div class="dm-mkt-source">Source: <?= htmlspecialchars($r['source']) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (!empty($mcs['results_summary'])): ?>
                <div class="dm-results-summary"><?= nl2br(htmlspecialchars($mcs['results_summary'])) ?></div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===================== GENERAL RESULTS (portfolio_results) ===================== -->
    <?php if (!empty($results)): ?>
    <section class="dm-results">
        <div class="container">
            <h2>Results at a Glance</h2>
            <div class="dm-results-grid">
                <?php foreach ($results as $r): ?>
                    <div class="dm-result">
                        <div class="dm-result-value"><?= htmlspecialchars($r['metric_value']) ?></div>
                        <div class="dm-result-label"><?= htmlspecialchars($r['metric_label']) ?></div>
                        <?php if (!empty($r['description'])): ?><div class="dm-result-desc"><?= htmlspecialchars($r['description']) ?></div><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===================== BEFORE / AFTER ===================== -->
    <?php if (!empty($before_imgs) || !empty($after_imgs)): ?>
    <section class="dm-before-after">
        <div class="container">
            <h2>Before &amp; After</h2>
            <div class="dm-ba-grid">
                <?php if (!empty($before_imgs)): $b = $before_imgs[0]; ?>
                <figure class="dm-ba-item">
                    <span class="dm-ba-label">Before</span>
                    <img src="<?= htmlspecialchars($b['image']) ?>" alt="<?= htmlspecialchars($b['alt_text'] ?: 'Before') ?>" width="1200" height="800" loading="lazy">
                    <?php if (!empty($b['caption'])): ?><figcaption><?= htmlspecialchars($b['caption']) ?></figcaption><?php endif; ?>
                </figure>
                <?php endif; ?>
                <?php if (!empty($after_imgs)): $a = $after_imgs[0]; ?>
                <figure class="dm-ba-item">
                    <span class="dm-ba-label">After</span>
                    <img src="<?= htmlspecialchars($a['image']) ?>" alt="<?= htmlspecialchars($a['alt_text'] ?: 'After') ?>" width="1200" height="800" loading="lazy">
                    <?php if (!empty($a['caption'])): ?><figcaption><?= htmlspecialchars($a['caption']) ?></figcaption><?php endif; ?>
                </figure>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===================== GALLERY ===================== -->
    <?php if (!empty($gallery)): ?>
    <section class="dm-gallery">
        <div class="container">
            <h2>Campaign Gallery</h2>
            <div class="dm-gallery-grid">
                <?php foreach ($gallery as $img): ?>
                    <figure class="dm-gallery-item">
                        <img src="<?= htmlspecialchars($img['image']) ?>" alt="<?= htmlspecialchars($img['alt_text'] ?: $project['title']) ?>" width="1200" height="800" loading="lazy">
                        <?php if (!empty($img['caption'])): ?><figcaption><?= htmlspecialchars($img['caption']) ?></figcaption><?php endif; ?>
                    </figure>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===================== TECHNOLOGIES / TOOLS ===================== -->
    <?php if (!empty($technologies)): ?>
    <section class="dm-tools">
        <div class="container">
            <h2>Tools &amp; Platforms</h2>
            <ul class="dm-tool-list">
                <?php foreach ($technologies as $tech): ?>
                    <li class="dm-tool"><?= htmlspecialchars($tech) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===================== CONCLUSION ===================== -->
    <?php if (!empty($mcs['results_summary']) && !empty($mcs['roi'])): // only show a distinct conclusion if we have something new ?>
    <!-- Handled above -->
    <?php endif; ?>

    <!-- ===================== CTA ===================== -->
    <section class="dm-cta">
        <div class="container">
            <div class="dm-cta-box">
                <?php if (!empty($project['cta_title'])): ?>
                    <h2><?= htmlspecialchars($project['cta_title']) ?></h2>
                <?php else: ?>
                    <h2>Ready to grow your business with data-driven marketing?</h2>
                <?php endif; ?>
                <?php if (!empty($project['cta_description'])): ?>
                    <p><?= htmlspecialchars($project['cta_description']) ?></p>
                <?php else: ?>
                    <p>Let's design a marketing strategy tailored to your goals.</p>
                <?php endif; ?>
                <?php
                $cta_text = $project['cta_button_text'] ?: 'Request a Strategy Call';
                $cta_url  = $project['cta_button_url']  ?: (BASE_URL . '/contact');
                ?>
                <a href="<?= htmlspecialchars($cta_url) ?>" class="btn btn-primary"><?= htmlspecialchars($cta_text) ?></a>
            </div>
        </div>
    </section>

    <!-- ===================== RELATED DIGITAL MARKETING CASE STUDIES ===================== -->
    <?php if (!empty($related)): ?>
    <section class="dm-related">
        <div class="container">
            <h2>More Marketing Case Studies</h2>
            <div class="dm-related-grid">
                <?php foreach ($related as $rp): ?>
                    <a class="dm-related-card" href="<?= BASE_URL ?>/portfolio/digital-marketing/<?= htmlspecialchars($rp['child_slug']) ?>/<?= htmlspecialchars($rp['slug']) ?>/">
                        <?php if (!empty($rp['hero_image'])): ?>
                            <img src="<?= htmlspecialchars($rp['hero_image']) ?>" alt="<?= htmlspecialchars($rp['title']) ?>" width="800" height="500" loading="lazy">
                        <?php endif; ?>
                        <span class="dm-related-cat"><?= htmlspecialchars($rp['child_name']) ?></span>
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




