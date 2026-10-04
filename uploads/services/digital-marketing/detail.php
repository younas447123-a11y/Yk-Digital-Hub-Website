<?php
/**
 * Digital Marketing Service Detail Page
 * Dynamically displays any service under the "Digital Marketing" parent category.
 * URL: /services/digital-marketing/{subcategory-slug}/{service-slug}/
 */

require_once __DIR__ . '/../../config/config.php';

$response_code = 200;
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
if (empty($slug)) {
    $response_code = 404;
}

$service = null;
$parent_category = null;
$child_category = null;

if ($response_code === 200) {
    // Fetch service and verify it belongs to Digital Marketing
    $stmt = $db->prepare("
        SELECT 
            s.*,
            child.id AS child_id,
            child.name AS child_name,
            child.slug AS child_slug,
            parent.id AS parent_id,
            parent.name AS parent_name,
            parent.slug AS parent_slug
        FROM services s
        JOIN service_categories child ON s.category_id = child.id
        JOIN service_categories parent ON child.parent_id = parent.id
        WHERE s.slug = :slug
          AND s.status = 1
          AND child.status = 1
          AND parent.status = 1
          AND parent.is_fixed = 1
          AND parent.parent_id IS NULL
          AND parent.slug = 'digital-marketing'
        LIMIT 1
    ");
    $stmt->execute([':slug' => $slug]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$service) {
        $response_code = 404;
    } else {
        $parent_category = [
            'id'   => $service['parent_id'],
            'name' => $service['parent_name'],
            'slug' => $service['parent_slug']
        ];
        $child_category = [
            'id'   => $service['child_id'],
            'name' => $service['child_name'],
            'slug' => $service['child_slug']
        ];
    }
}

// Handle 404
if ($response_code === 404) {
    http_response_code(404);
    if (file_exists(ROOT_PATH . '/404.php')) {
        include ROOT_PATH . '/404.php';
    } else {
        echo "<h1>404 - Service Not Found</h1>";
        echo "<p>The requested service could not be found.</p>";
    }
    exit;
}

// Load child content (same as Step 7)
$stmt = $db->prepare("SELECT * FROM service_stats WHERE service_id = :id AND status = 1 ORDER BY sort_order ASC, id ASC");
$stmt->execute([':id' => $service['id']]);
$stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT * FROM service_features WHERE service_id = :id AND status = 1 ORDER BY sort_order ASC, id ASC");
$stmt->execute([':id' => $service['id']]);
$features = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT * FROM service_process_steps WHERE service_id = :id AND status = 1 ORDER BY sort_order ASC, id ASC");
$stmt->execute([':id' => $service['id']]);
$process_steps = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT * FROM service_technologies WHERE service_id = :id AND status = 1 ORDER BY sort_order ASC, id ASC");
$stmt->execute([':id' => $service['id']]);
$technologies = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT * FROM service_sections WHERE service_id = :id AND status = 1 ORDER BY sort_order ASC, id ASC");
$stmt->execute([':id' => $service['id']]);
$sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT * FROM service_faqs WHERE service_id = :id AND status = 1 ORDER BY sort_order ASC, id ASC");
$stmt->execute([':id' => $service['id']]);
$faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- SEO ---
$seo_title = !empty($service['seo_title']) ? $service['seo_title'] : $service['title'] . ' - YK Digital Hub';
$meta_description = !empty($service['meta_description']) ? $service['meta_description'] : ( !empty($service['short_description']) ? $service['short_description'] : '' );
$canonical_url = !empty($service['canonical_url']) ? $service['canonical_url'] : BASE_URL . '/services/digital-marketing/' . $child_category['slug'] . '/' . $service['slug'] . '/';
$og_title = !empty($service['og_title']) ? $service['og_title'] : $seo_title;
$og_description = !empty($service['og_description']) ? $service['og_description'] : $meta_description;
$og_image = !empty($service['og_image']) ? $service['og_image'] : ( !empty($service['hero_image']) ? $service['hero_image'] : '' );
$robots = !empty($service['robots']) ? $service['robots'] : 'index, follow';

$page_title = $seo_title;
$page_description = $meta_description;
$page_canonical = $canonical_url;

ob_start();

include ROOT_PATH . '/includes/header.php';
if (file_exists(ROOT_PATH . '/includes/navbar.php')) {
    include ROOT_PATH . '/includes/navbar.php';
}
?>

<!-- Breadcrumb -->
<nav aria-label="Breadcrumb" class="breadcrumb-wrapper">
    <div class="container">
        <ol class="breadcrumb">
            <li><a href="<?= BASE_URL ?>">Home</a></li>
            <li><a href="<?= BASE_URL ?>/services/">Services</a></li>
            <li><a href="<?= BASE_URL ?>/services/digital-marketing/">Digital Marketing</a></li>
            <li><a href="<?= BASE_URL ?>/services/digital-marketing/<?= $child_category['slug'] ?>/"><?= htmlspecialchars($child_category['name']) ?></a></li>
            <li aria-current="page"><?= htmlspecialchars($service['title']) ?></li>
        </ol>
    </div>
</nav>

<main class="service-detail marketing-template">
    <!-- ===== HERO ===== -->
    <section class="service-hero marketing-hero" <?= !empty($service['hero_image']) ? 'style="background-image: url(' . htmlspecialchars($service['hero_image']) . ');"' : '' ?>>
        <div class="container">
            <div class="hero-content">
                <?php if (!empty($service['hero_badge'])): ?>
                    <span class="badge"><?= htmlspecialchars($service['hero_badge']) ?></span>
                <?php endif; ?>
                <h1><?= !empty($service['hero_title']) ? htmlspecialchars($service['hero_title']) : htmlspecialchars($service['title']) ?></h1>
                <?php if (!empty($service['hero_subtitle'])): ?>
                    <p class="subtitle"><?= htmlspecialchars($service['hero_subtitle']) ?></p>
                <?php endif; ?>
                <?php
                    $hero_desc = !empty($service['hero_description']) ? $service['hero_description'] : ( !empty($service['short_description']) ? $service['short_description'] : '' );
                ?>
                <?php if (!empty($hero_desc)): ?>
                    <div class="description"><?= nl2br(htmlspecialchars($hero_desc)) ?></div>
                <?php endif; ?>
                <div class="cta-buttons">
                    <?php if (!empty($service['primary_cta_text']) && !empty($service['primary_cta_url'])): ?>
                        <a href="<?= htmlspecialchars($service['primary_cta_url']) ?>" class="btn btn-primary"><?= htmlspecialchars($service['primary_cta_text']) ?></a>
                    <?php endif; ?>
                    <?php if (!empty($service['secondary_cta_text']) && !empty($service['secondary_cta_url'])): ?>
                        <a href="<?= htmlspecialchars($service['secondary_cta_url']) ?>" class="btn btn-secondary"><?= htmlspecialchars($service['secondary_cta_text']) ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== STATISTICS ===== -->
    <?php if (!empty($stats)): ?>
    <section class="service-stats marketing-stats">
        <div class="container">
            <div class="stats-grid">
                <?php foreach ($stats as $stat): ?>
                    <div class="stat-item">
                        <div class="stat-value"><?= htmlspecialchars($stat['value']) ?></div>
                        <div class="stat-label"><?= htmlspecialchars($stat['label']) ?></div>
                        <?php if (!empty($stat['description'])): ?>
                            <div class="stat-description"><?= htmlspecialchars($stat['description']) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===== FEATURES / CAPABILITIES ===== -->
    <?php if (!empty($features)): ?>
    <section class="service-features marketing-features">
        <div class="container">
            <h2>Our Marketing Capabilities</h2>
            <div class="features-grid">
                <?php foreach ($features as $feature): ?>
                    <div class="feature-card">
                        <?php if (!empty($feature['icon'])): ?>
                            <div class="icon"><i class="<?= htmlspecialchars($feature['icon']) ?>"></i></div>
                        <?php endif; ?>
                        <h3><?= htmlspecialchars($feature['title']) ?></h3>
                        <?php if (!empty($feature['description'])): ?>
                            <p><?= htmlspecialchars($feature['description']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===== CONTENT SECTIONS (Strategy, Approach, etc.) ===== -->
    <?php if (!empty($sections)): ?>
        <?php foreach ($sections as $section): ?>
            <section class="service-section marketing-section <?= !empty($section['section_type']) ? 'section-type-' . htmlspecialchars($section['section_type']) : '' ?>" <?= !empty($section['background_image']) ? 'style="background-image: url(' . htmlspecialchars($section['background_image']) . ');"' : '' ?>>
                <div class="container">
                    <?php if (!empty($section['heading'])): ?>
                        <h2><?= htmlspecialchars($section['heading']) ?></h2>
                    <?php endif; ?>
                    <?php if (!empty($section['subheading'])): ?>
                        <p class="subheading"><?= htmlspecialchars($section['subheading']) ?></p>
                    <?php endif; ?>
                    <div class="section-content <?= !empty($section['image']) ? 'has-image' : '' ?>">
                        <?php if (!empty($section['content'])): ?>
                            <div class="text"><?= nl2br(htmlspecialchars($section['content'])) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($section['image'])): ?>
                            <div class="image">
                                <img src="<?= htmlspecialchars($section['image']) ?>" alt="<?= !empty($section['heading']) ? htmlspecialchars($section['heading']) : 'Section image' ?>" loading="lazy">
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($section['video_url'])): ?>
                            <div class="video">
                                <iframe src="<?= htmlspecialchars($section['video_url']) ?>" frameborder="0" allowfullscreen></iframe>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- ===== TECHNOLOGIES / CHANNELS ===== -->
    <?php if (!empty($technologies)): ?>
    <section class="service-technologies marketing-tech">
        <div class="container">
            <h2>Marketing Platforms & Tools</h2>
            <ul class="tech-list">
                <?php foreach ($technologies as $tech): ?>
                    <li class="tech-item">
                        <?php if (!empty($tech['icon'])): ?>
                            <span class="icon"><i class="<?= htmlspecialchars($tech['icon']) ?>"></i></span>
                        <?php endif; ?>
                        <span class="name"><?= htmlspecialchars($tech['name']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===== PROCESS ===== -->
    <?php if (!empty($process_steps)): ?>
    <section class="service-process marketing-process">
        <div class="container">
            <h2>Our Marketing Process</h2>
            <ol class="process-timeline">
                <?php foreach ($process_steps as $step): ?>
                    <li class="process-step">
                        <div class="step-number"><?= (int)$step['step_number'] ?></div>
                        <div class="step-content">
                            <h3><?= htmlspecialchars($step['title']) ?></h3>
                            <?php if (!empty($step['description'])): ?>
                                <p><?= htmlspecialchars($step['description']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($step['image'])): ?>
                                <img src="<?= htmlspecialchars($step['image']) ?>" alt="<?= htmlspecialchars($step['title']) ?>" loading="lazy">
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===== FAQ ===== -->
    <?php if (!empty($faqs)): ?>
    <section class="service-faqs marketing-faqs">
        <div class="container">
            <h2>Frequently Asked Questions</h2>
            <div class="faq-accordion">
                <?php foreach ($faqs as $index => $faq): ?>
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false" aria-controls="faq-answer-<?= $index ?>" id="faq-btn-<?= $index ?>">
                            <?= htmlspecialchars($faq['question']) ?>
                            <span class="icon" aria-hidden="true">+</span>
                        </button>
                        <div class="faq-answer" id="faq-answer-<?= $index ?>" role="region" aria-labelledby="faq-btn-<?= $index ?>">
                            <p><?= nl2br(htmlspecialchars($faq['answer'])) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===== FINAL CTA ===== -->
    <section class="service-cta marketing-cta">
        <div class="container">
            <div class="cta-box">
                <?php
                    $cta_text = !empty($service['primary_cta_text']) ? $service['primary_cta_text'] : 'Let\'s Grow Your Business';
                    $cta_url = !empty($service['primary_cta_url']) ? $service['primary_cta_url'] : BASE_URL . '/contact';
                ?>
                <h2><?= htmlspecialchars($cta_text) ?></h2>
                <?php if (!empty($service['secondary_cta_text'])): ?>
                    <p><?= htmlspecialchars($service['secondary_cta_text']) ?></p>
                <?php endif; ?>
                <a href="<?= htmlspecialchars($cta_url) ?>" class="btn btn-primary"><?= htmlspecialchars($cta_text) ?></a>
            </div>
        </div>
    </section>
</main>

<?php
include ROOT_PATH . '/includes/footer.php';
ob_end_flush();
?>

<!-- JSON-LD Structured Data -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Service",
  "name": "<?= htmlspecialchars($service['title']) ?>",
  "description": "<?= htmlspecialchars(strip_tags($hero_desc)) ?>",
  "provider": {
    "@type": "Organization",
    "name": "YK Digital Hub"
  },
  "url": "<?= $canonical_url ?>",
  "image": "<?= !empty($og_image) ? htmlspecialchars($og_image) : '' ?>"
}
</script>