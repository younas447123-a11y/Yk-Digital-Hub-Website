<?php
/**
 * Digital Marketing Service Detail Page — Full Field Display
 * Shows every field from the services table + all child content tables.
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$response_code = 200;
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
if ($slug === '' || !preg_match('/^[a-z0-9-]+$/', $slug)) $response_code = 404;

$service = $child = $parent = null;

if ($response_code === 200) {
    $stmt = $db->prepare("
        SELECT s.*,
            c.id AS child_id, c.name AS child_name, c.slug AS child_slug, c.status AS child_status,
            par.id AS parent_id, par.name AS parent_name, par.slug AS parent_slug, par.status AS parent_status
        FROM services s
        JOIN service_categories c   ON s.category_id = c.id
        JOIN service_categories par ON c.parent_id = par.id
        WHERE s.slug = :slug
          AND s.status = 1 AND c.status = 1 AND par.status = 1
          AND c.is_fixed = 0 AND c.parent_id IS NOT NULL
          AND par.is_fixed = 1 AND par.parent_id IS NULL
          AND par.slug = 'digital-marketing'
        LIMIT 1
    ");
    $stmt->execute([':slug' => $slug]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$service) $response_code = 404;
    else {
        $child  = ['id' => $service['child_id'],  'name' => $service['child_name'],  'slug' => $service['child_slug']];
        $parent = ['id' => $service['parent_id'], 'name' => $service['parent_name'], 'slug' => $service['parent_slug']];
    }
}

if ($response_code === 404) {
    http_response_code(404);
    if (file_exists(ROOT_PATH . '/404.php')) include ROOT_PATH . '/404.php';
    else echo '<h1>404 — Service not found</h1>';
    exit;
}

$pid = (int)$service['id'];

$fetch = function($table, $extra = '') use ($db, $pid) {
    $sql = "SELECT * FROM `$table` WHERE service_id = :pid AND status = 1 $extra ORDER BY sort_order ASC, id ASC";
    $s = $db->prepare($sql);
    $s->execute([':pid' => $pid]);
    return $s->fetchAll(PDO::FETCH_ASSOC);
};

$stats        = $fetch('service_stats');
$features     = $fetch('service_features');
$process      = $fetch('service_process_steps');
$technologies = $fetch('service_technologies');
$sections     = $fetch('service_sections');
$faqs         = $fetch('service_faqs');

// Related
$rel = $db->prepare("
    SELECT s.id, s.title, s.slug, s.short_description, s.card_description, s.hero_image, s.icon,
           c.slug AS child_slug, c.name AS child_name
    FROM services s
    JOIN service_categories c   ON s.category_id = c.id
    JOIN service_categories par ON c.parent_id = par.id
    WHERE s.status = 1 AND s.id != :pid
      AND par.slug = 'digital-marketing' AND par.is_fixed = 1
      AND c.is_fixed = 0 AND c.status = 1
    ORDER BY (c.id = :cid) DESC, s.featured DESC, s.sort_order, s.id DESC
    LIMIT 3
");
$rel->execute([':pid' => $pid, ':cid' => $child['id']]);
$related = $rel->fetchAll(PDO::FETCH_ASSOC);

// SEO
$seo_title        = $service['seo_title']        ?: ($service['title'] . ' | YK Digital Hub');
$meta_description = $service['meta_description'] ?: ($service['short_description'] ?: $service['card_description'] ?: '');
$canonical        = $service['canonical_url']    ?: (BASE_URL . '/services/digital-marketing/' . $child['slug'] . '/' . $service['slug'] . '/');
$og_title         = $service['og_title']         ?: $seo_title;
$og_description   = $service['og_description']   ?: $meta_description;
$og_image         = $service['og_image']         ?: ($service['hero_image'] ?: '');
$robots           = $service['robots']           ?: 'index, follow';

$page_title       = $seo_title;
$page_description = $meta_description;
$page_canonical   = $canonical;

// Hero fallbacks
$hero_title  = $service['hero_title']       ?: $service['title'];
$hero_sub    = $service['hero_subtitle']    ?: '';
$hero_desc   = $service['hero_description'] ?: ($service['short_description'] ?: $service['card_description'] ?: '');
$hero_image  = $service['hero_image']       ?: '';
$hero_badge  = $service['hero_badge']       ?: '';
$icon        = $service['icon']             ?: '';

// Commercial
$starting_price   = $service['starting_price']   ?? '';
$price_label      = $service['price_label']      ?: '';
$delivery_time    = $service['delivery_time']    ?: '';
$service_location = $service['service_location'] ?: '';

// CTA
$cta_text = !empty($service['primary_cta_text']) ? $service['primary_cta_text'] : 'Request a Strategy Call';
$cta_url  = !empty($service['primary_cta_url'])  ? $service['primary_cta_url']  : BASE_URL . '/contact.php';
$cta2_text = $service['secondary_cta_text'] ?? '';
$cta2_url  = $service['secondary_cta_url']  ?? '';

// WhatsApp
$wa_raw = getSiteSetting('whatsapp', '923069776937');
$wa_num = preg_replace('/[^0-9]/', '', $wa_raw);
$wa_url = 'https://wa.me/' . ($wa_num ?: '923069776937');

// Palette
$DM_PRIMARY   = '#7c3aed';
$DM_SECONDARY = '#ec4899';
$DM_DARK      = '#2B3F5C';
$DM_LIGHT     = '#faf5ff';
$DM_BORDER    = '#ede9fe';

ob_start();
include ROOT_PATH . '/includes/header.php';
if (file_exists(ROOT_PATH . '/includes/navbar.php')) include ROOT_PATH . '/includes/navbar.php';
?>

<!-- ===================== HERO ===================== -->
<section style="position: relative; padding: 3rem 0 4.5rem; background: linear-gradient(180deg, <?= $DM_LIGHT ?> 0%, #ffffff 100%); overflow: hidden;">
    <div style="position: absolute; inset: 0; background-image: radial-gradient(circle at 1px 1px, rgba(124,58,237,0.08) 1px, transparent 0); background-size: 28px 28px; pointer-events: none;"></div>
    <div style="max-width: 1400px; margin: 0 auto; padding: 0 1.5rem; position: relative;">

        <!-- Breadcrumb -->
        <nav style="font-family: 'Inter', system-ui, sans-serif; font-size: 15px; color: #64748b; margin-bottom: 2rem; font-weight: 500;">
            <a href="<?= BASE_URL ?>/" style="color: #64748b; text-decoration: none;">Home</a>
            <span style="margin: 0 10px;">›</span>
            <a href="<?= BASE_URL ?>/services/" style="color: #64748b; text-decoration: none;">Services</a>
            <span style="margin: 0 10px;">›</span>
            <a href="<?= BASE_URL ?>/services/<?= htmlspecialchars($parent['slug']) ?>/" style="color: #64748b; text-decoration: none;"><?= htmlspecialchars($parent['name']) ?></a>
            <span style="margin: 0 10px;">›</span>
            <a href="<?= BASE_URL ?>/services/<?= htmlspecialchars($parent['slug']) ?>/<?= htmlspecialchars($child['slug']) ?>/" style="color: #64748b; text-decoration: none;"><?= htmlspecialchars($child['name']) ?></a>
            <span style="margin: 0 10px;">›</span>
            <span style="color: <?= $DM_DARK ?>; font-weight: 700;"><?= htmlspecialchars($service['title']) ?></span>
        </nav>

        <div style="display: grid; grid-template-columns: 1.1fr 1fr; gap: 3.5rem; align-items: center;" class="yk-dm-hero-grid">
            <div>
                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-bottom: 22px;">
                    <span style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 18px; font-family: 'Inter', system-ui, sans-serif; font-size: 13px; font-weight: 800; letter-spacing: 0.15em; text-transform: uppercase; border-radius: 999px; color: #fff; background: linear-gradient(135deg, <?= $DM_PRIMARY ?> 0%, <?= $DM_SECONDARY ?> 100%);">
                        <?php if ($icon): ?><i class="<?= htmlspecialchars($icon) ?>"></i><?php else: ?><i class="fas fa-bullhorn"></i><?php endif; ?>
                        <?= htmlspecialchars($child['name']) ?>
                    </span>
                    <?php if ($hero_badge): ?>
                        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; font-family: 'Inter', system-ui, sans-serif; font-size: 13px; font-weight: 800; letter-spacing: 0.15em; text-transform: uppercase; border-radius: 999px; color: #FF8A00; background: #FFF4E6; border: 1px solid #FFE1C2;">
                            <i class="fas fa-star" style="font-size: 11px;"></i> <?= htmlspecialchars($hero_badge) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <h1 style="font-family: 'Inter', system-ui, sans-serif; font-size: 56px; line-height: 1.05; font-weight: 800; letter-spacing: -0.025em; color: <?= $DM_DARK ?>; margin: 0 0 22px 0;">
                    <?= htmlspecialchars($hero_title) ?>
                </h1>

                <?php if ($hero_sub): ?>
                    <p style="font-family: 'Inter', system-ui, sans-serif; font-size: 22px; line-height: 1.5; font-weight: 500; color: #475569; margin: 0 0 18px 0;">
                        <?= htmlspecialchars($hero_sub) ?>
                    </p>
                <?php endif; ?>

                <?php if ($hero_desc): ?>
                    <p style="font-family: 'Inter', system-ui, sans-serif; font-size: 18px; line-height: 1.75; color: #64748b; margin: 0 0 32px 0; max-width: 620px;">
                        <?= nl2br(htmlspecialchars($hero_desc)) ?>
                    </p>
                <?php endif; ?>

                <div style="display: flex; flex-wrap: wrap; gap: 14px; margin-bottom: 28px;">
                    <a href="<?= htmlspecialchars($cta_url) ?>"
                       style="display: inline-flex; align-items: center; gap: 10px; padding: 17px 34px; background: linear-gradient(90deg, <?= $DM_PRIMARY ?> 0%, <?= $DM_SECONDARY ?> 100%); color: #fff; font-family: 'Inter', system-ui, sans-serif; font-weight: 800; font-size: 17px; border-radius: 999px; text-decoration: none; box-shadow: 0 10px 28px rgba(124,58,237,0.35);">
                        <?= htmlspecialchars($cta_text) ?> <i class="fas fa-arrow-right" style="font-size: 14px;"></i>
                    </a>
                    <a href="<?= htmlspecialchars($wa_url) ?>" target="_blank" rel="noopener noreferrer"
                       style="display: inline-flex; align-items: center; gap: 10px; padding: 17px 34px; background: #25D366; color: #fff; font-family: 'Inter', system-ui, sans-serif; font-weight: 800; font-size: 17px; border-radius: 999px; text-decoration: none; box-shadow: 0 10px 28px rgba(37,211,102,0.3);">
                        <i class="fab fa-whatsapp" style="font-size: 20px;"></i> WhatsApp
                    </a>
                    <?php if ($cta2_text && $cta2_url): ?>
                        <a href="<?= htmlspecialchars($cta2_url) ?>"
                           style="display: inline-flex; align-items: center; gap: 10px; padding: 17px 34px; border: 2px solid <?= $DM_DARK ?>; color: <?= $DM_DARK ?>; font-family: 'Inter', system-ui, sans-serif; font-weight: 800; font-size: 17px; border-radius: 999px; text-decoration: none;">
                            <?= htmlspecialchars($cta2_text) ?>
                        </a>
                    <?php endif; ?>
                </div>

                <?php if ($hero_sub): ?>
                <div style="display: flex; flex-wrap: wrap; gap: 24px; font-family: 'Inter', system-ui, sans-serif; font-size: 16px; font-weight: 600; color: #475569;">
                    <span style="display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fas fa-check-circle" style="color: <?= $DM_PRIMARY ?>; font-size: 18px;"></i> Data-Driven
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fas fa-check-circle" style="color: <?= $DM_PRIMARY ?>; font-size: 18px;"></i> Measurable ROI
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fas fa-check-circle" style="color: <?= $DM_PRIMARY ?>; font-size: 18px;"></i> Free Audit
                    </span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Hero image or info card -->
            <div style="position: relative;">
                <?php if ($hero_image): ?>
                    <div style="background: #fff; border-radius: 22px; box-shadow: 0 24px 70px rgba(124,58,237,0.18); overflow: hidden; border: 1px solid <?= $DM_BORDER ?>;">
                        <img src="<?= htmlspecialchars($hero_image) ?>" alt="<?= htmlspecialchars($service['title']) ?>" style="width: 100%; display: block;">
                    </div>
                <?php else: ?>
                    <div style="background: #fff; border-radius: 22px; box-shadow: 0 24px 70px rgba(124,58,237,0.15); overflow: hidden; border: 1px solid <?= $DM_BORDER ?>;">
                        <div style="background: linear-gradient(135deg, <?= $DM_PRIMARY ?> 0%, <?= $DM_SECONDARY ?> 100%); padding: 26px 30px;">
                            <div style="font-family: 'Inter', system-ui, sans-serif; font-size: 13px; font-weight: 800; letter-spacing: 0.15em; text-transform: uppercase; color: rgba(255,255,255,0.9); margin-bottom: 6px;">Free Audit</div>
                            <div style="font-family: 'Inter', system-ui, sans-serif; font-size: 24px; font-weight: 800; color: #fff;">Marketing Strategy Call</div>
                        </div>
                        <div style="padding: 24px 30px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 16px 0; border-bottom: 1px solid #f1f5f9;">
                                <span style="font-family: 'Inter', system-ui, sans-serif; font-size: 15px; color: #64748b;">Response time</span>
                                <span style="font-family: 'Inter', system-ui, sans-serif; font-size: 15px; font-weight: 800; color: <?= $DM_DARK ?>;">Within 24 hours</span>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 16px 0; border-bottom: 1px solid #f1f5f9;">
                                <span style="font-family: 'Inter', system-ui, sans-serif; font-size: 15px; color: #64748b;">You receive</span>
                                <span style="font-family: 'Inter', system-ui, sans-serif; font-size: 15px; font-weight: 800; color: <?= $DM_DARK ?>;">Custom Growth Plan</span>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 16px 0;">
                                <span style="font-family: 'Inter', system-ui, sans-serif; font-size: 15px; color: #64748b;">Cost</span>
                                <span style="font-family: 'Inter', system-ui, sans-serif; font-size: 15px; font-weight: 800; color: <?= $DM_PRIMARY ?>;">Zero. Free.</span>
                            </div>
                            <a href="<?= BASE_URL ?>/contact.php"
                               style="display: block; text-align: center; padding: 16px; margin-top: 10px; background: linear-gradient(90deg, <?= $DM_PRIMARY ?> 0%, <?= $DM_SECONDARY ?> 100%); color: #fff; font-family: 'Inter', system-ui, sans-serif; font-weight: 800; font-size: 16px; border-radius: 12px; text-decoration: none;">
                                Get Your Free Audit →
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- ===================== COMMERCIAL INFO BAR (NEW) ===================== -->
<?php if ($starting_price || $price_label || $delivery_time || $service_location): ?>
<section style="background: #fff; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; padding: 2rem 0;">
    <div style="max-width: 1400px; margin: 0 auto; padding: 0 1.5rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; align-items: center;">

            <?php if ($starting_price): ?>
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #fff; background: linear-gradient(135deg, <?= $DM_PRIMARY ?>, <?= $DM_SECONDARY ?>); flex-shrink: 0;">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div>
                    <div style="font-family: 'Inter', system-ui, sans-serif; font-size: 12px; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase; color: #64748b; margin-bottom: 3px;">
                        <?= htmlspecialchars($price_label ?: 'Starting From') ?>
                    </div>
                    <div style="font-family: 'Inter', system-ui, sans-serif; font-size: 22px; font-weight: 800; color: <?= $DM_DARK ?>;">
                        <?= htmlspecialchars(number_format((float)$starting_price)) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($delivery_time): ?>
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #fff; background: linear-gradient(135deg, <?= $DM_PRIMARY ?>, <?= $DM_SECONDARY ?>); flex-shrink: 0;">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <div style="font-family: 'Inter', system-ui, sans-serif; font-size: 12px; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase; color: #64748b; margin-bottom: 3px;">
                        Delivery Time
                    </div>
                    <div style="font-family: 'Inter', system-ui, sans-serif; font-size: 22px; font-weight: 800; color: <?= $DM_DARK ?>;">
                        <?= htmlspecialchars($delivery_time) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($service_location): ?>
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #fff; background: linear-gradient(135deg, <?= $DM_PRIMARY ?>, <?= $DM_SECONDARY ?>); flex-shrink: 0;">
                    <i class="fas fa-globe"></i>
                </div>
                <div>
                    <div style="font-family: 'Inter', system-ui, sans-serif; font-size: 12px; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase; color: #64748b; margin-bottom: 3px;">
                        Service Location
                    </div>
                    <div style="font-family: 'Inter', system-ui, sans-serif; font-size: 22px; font-weight: 800; color: <?= $DM_DARK ?>;">
                        <?= htmlspecialchars($service_location) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===================== STATS ===================== -->
<?php if (!empty($stats)): ?>
<section style="background: #0f172a; padding: 3.5rem 0;">
    <div style="max-width: 1400px; margin: 0 auto; padding: 0 1.5rem;">
        <div style="display: grid; grid-template-columns: repeat(<?= min(count($stats), 4) ?>, 1fr); gap: 2.5rem; text-align: center;" class="yk-dm-stats">
            <?php foreach ($stats as $s): ?>
                <div>
                    <?php if (!empty($s['icon'])): ?>
                        <div style="font-size: 32px; color: #c4b5fd; margin-bottom: 14px;">
                            <i class="<?= htmlspecialchars($s['icon']) ?>"></i>
                        </div>
                    <?php endif; ?>
                    <div style="font-family: 'Inter', system-ui, sans-serif; font-size: 48px; font-weight: 800; line-height: 1; letter-spacing: -0.02em; background: linear-gradient(135deg, <?= $DM_PRIMARY ?>, <?= $DM_SECONDARY ?>); -webkit-background-clip: text; background-clip: text; color: transparent;">
                        <?= htmlspecialchars($s['value']) ?>
                    </div>
                    <div style="font-family: 'Inter', system-ui, sans-serif; font-size: 16px; font-weight: 700; color: #e2e8f0; margin-top: 12px;">
                        <?= htmlspecialchars($s['label']) ?>
                    </div>
                    <?php if (!empty($s['description'])): ?>
                        <div style="font-family: 'Inter', system-ui, sans-serif; font-size: 14px; color: #94a3b8; margin-top: 6px;">
                            <?= htmlspecialchars($s['description']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===================== FEATURES (with image support) ===================== -->
<?php if (!empty($features)): ?>
<section style="padding: 5.5rem 0; background: #fff;">
    <div style="max-width: 1400px; margin: 0 auto; padding: 0 1.5rem;">
        <div style="text-align: center; max-width: 820px; margin: 0 auto 3.5rem auto;">
            <span style="display: inline-block; padding: 8px 18px; font-family: 'Inter', system-ui, sans-serif; font-size: 13px; font-weight: 800; letter-spacing: 0.2em; text-transform: uppercase; color: <?= $DM_PRIMARY ?>; background: <?= $DM_LIGHT ?>; border: 1px solid <?= $DM_BORDER ?>; border-radius: 999px; margin-bottom: 20px;">
                Our Capabilities
            </span>
            <h2 style="font-family: 'Inter', system-ui, sans-serif; font-size: 48px; line-height: 1.1; font-weight: 800; letter-spacing: -0.025em; color: <?= $DM_DARK ?>; margin: 0 0 18px 0;">
                What's Included in <span style="color: <?= $DM_PRIMARY ?>;">This Service</span>
            </h2>
        </div>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 26px;" class="yk-dm-grid-3">
            <?php foreach ($features as $f): ?>
                <div style="background: #fff; border: 2px solid #e2e8f0; border-radius: 20px; padding: 34px 30px; transition: all 0.3s ease; display: flex; flex-direction: column;"
                     onmouseover="this.style.borderColor='<?= $DM_PRIMARY ?>'; this.style.transform='translateY(-6px)'; this.style.boxShadow='0 22px 48px -14px rgba(124,58,237,0.25)';"
                     onmouseout="this.style.borderColor='#e2e8f0'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                    <?php if (!empty($f['image'])): ?>
                        <div style="margin: -34px -30px 24px -30px; border-radius: 18px 18px 0 0; overflow: hidden; background: #f1f5f9;">
                            <img src="<?= htmlspecialchars($f['image']) ?>" alt="<?= htmlspecialchars($f['title']) ?>" loading="lazy" style="width: 100%; height: 180px; object-fit: cover; display: block;">
                        </div>
                    <?php elseif (!empty($f['icon'])): ?>
                        <div style="width: 62px; height: 62px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #fff; background: linear-gradient(135deg, <?= $DM_PRIMARY ?> 0%, <?= $DM_SECONDARY ?> 100%); margin-bottom: 22px; box-shadow: 0 10px 24px rgba(124,58,237,0.25);">
                            <i class="<?= htmlspecialchars($f['icon']) ?>"></i>
                        </div>
                    <?php endif; ?>
                    <h3 style="font-family: 'Inter', system-ui, sans-serif; font-size: 21px; line-height: 1.3; font-weight: 800; color: <?= $DM_DARK ?>; margin: 0 0 12px 0;">
                        <?= htmlspecialchars($f['title']) ?>
                    </h3>
                    <?php if (!empty($f['description'])): ?>
                        <p style="font-family: 'Inter', system-ui, sans-serif; font-size: 16px; line-height: 1.7; color: #64748b; margin: 0;">
                            <?= htmlspecialchars($f['description']) ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===================== CONTENT SECTIONS (with background + video) ===================== -->
<?php foreach ($sections as $i => $sec):
    $has_img = !empty($sec['image']);
    $has_bg  = !empty($sec['background_image']);
    $has_vid = !empty($sec['video_url']);
    $flip = $i % 2 === 1;
    $bg_style = $has_bg ? 'background-image: url(' . htmlspecialchars($sec['background_image']) . '); background-size: cover; background-position: center;' : '';
    $bg_color = $has_bg ? 'rgba(255,255,255,0.94)' : ($flip ? $DM_LIGHT : '#ffffff');
?>
<section style="padding: 5.5rem 0; background-color: <?= $flip && !$has_bg ? $DM_LIGHT : '#ffffff' ?>; <?= $bg_style ?>">
    <div style="max-width: 1400px; margin: 0 auto; padding: 0 1.5rem;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4.5rem; align-items: center;" class="yk-dm-sec-grid">
            <div style="<?= $flip ? 'order: 2;' : '' ?>">
                <?php if (!empty($sec['section_label'])): ?>
                    <span style="display: inline-block; padding: 8px 18px; font-family: 'Inter', system-ui, sans-serif; font-size: 13px; font-weight: 800; letter-spacing: 0.18em; text-transform: uppercase; color: #FF8A00; background: #FFF4E6; border: 1px solid #FFE1C2; border-radius: 999px; margin-bottom: 20px;">
                        <?= htmlspecialchars($sec['section_label']) ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($sec['heading'])): ?>
                    <h2 style="font-family: 'Inter', system-ui, sans-serif; font-size: 44px; line-height: 1.15; font-weight: 800; letter-spacing: -0.025em; color: <?= $DM_DARK ?>; margin: 0 0 20px 0;">
                        <?= htmlspecialchars($sec['heading']) ?>
                    </h2>
                <?php endif; ?>
                <?php if (!empty($sec['subheading'])): ?>
                    <p style="font-family: 'Inter', system-ui, sans-serif; font-size: 20px; line-height: 1.55; font-weight: 500; color: #475569; margin: 0 0 22px 0;">
                        <?= htmlspecialchars($sec['subheading']) ?>
                    </p>
                <?php endif; ?>
                <?php if (!empty($sec['content'])): ?>
                    <div style="font-family: 'Inter', system-ui, sans-serif; font-size: 18px; line-height: 1.85; color: #475569;">
                        <?= nl2br(htmlspecialchars($sec['content'])) ?>
                    </div>
                <?php endif; ?>

                <?php if ($has_vid): ?>
                    <div style="margin-top: 28px; border-radius: 14px; overflow: hidden; box-shadow: 0 20px 50px rgba(124,58,237,0.2);">
                        <div style="aspect-ratio: 16/9;">
                            <iframe src="<?= htmlspecialchars($sec['video_url']) ?>" style="width: 100%; height: 100%; border: 0;" allowfullscreen loading="lazy"></iframe>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($has_img): ?>
                <div style="<?= $flip ? 'order: 1;' : '' ?>">
                    <img src="<?= htmlspecialchars($sec['image']) ?>" alt="<?= htmlspecialchars($sec['heading'] ?? $service['title']) ?>" loading="lazy"
                         style="width: 100%; border-radius: 22px; box-shadow: 0 28px 70px rgba(124,58,237,0.2); display: block;">
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endforeach; ?>

<!-- ===================== TECHNOLOGIES ===================== -->
<?php if (!empty($technologies)): ?>
<section style="padding: 5.5rem 0; background: #0f172a; position: relative; overflow: hidden;">
    <div style="position: absolute; inset: 0; background: radial-gradient(ellipse at top right, rgba(124,58,237,0.2), transparent 65%);"></div>
    <div style="position: absolute; inset: 0; background: radial-gradient(ellipse at bottom left, rgba(236,72,153,0.15), transparent 65%);"></div>
    <div style="max-width: 1400px; margin: 0 auto; padding: 0 1.5rem; position: relative; text-align: center;">
        <span style="display: inline-block; padding: 8px 18px; font-family: 'Inter', system-ui, sans-serif; font-size: 13px; font-weight: 800; letter-spacing: 0.2em; text-transform: uppercase; color: #c4b5fd; background: rgba(196,181,253,0.1); border: 1px solid rgba(196,181,253,0.3); border-radius: 999px; margin-bottom: 20px;">
            Platforms &amp; Tools
        </span>
        <h2 style="font-family: 'Inter', system-ui, sans-serif; font-size: 48px; line-height: 1.1; font-weight: 800; letter-spacing: -0.025em; color: #fff; margin: 0 0 44px 0;">
            Tools We <span style="background: linear-gradient(135deg, <?= $DM_PRIMARY ?>, <?= $DM_SECONDARY ?>); -webkit-background-clip: text; background-clip: text; color: transparent;">Master</span>
        </h2>
        <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 14px; max-width: 980px; margin: 0 auto;">
            <?php foreach ($technologies as $t): ?>
                <div style="display: inline-flex; align-items: center; gap: 12px; padding: 14px 26px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.14); border-radius: 999px; backdrop-filter: blur(10px);">
                    <?php if (!empty($t['icon'])): ?>
                        <i class="<?= htmlspecialchars($t['icon']) ?>" style="color: #c4b5fd; font-size: 18px;"></i>
                    <?php endif; ?>
                    <span style="font-family: 'Inter', system-ui, sans-serif; font-size: 16px; font-weight: 700; color: #fff;">
                        <?= htmlspecialchars($t['name']) ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===================== PROCESS (with step image support) ===================== -->
<?php if (!empty($process)): ?>
<section style="padding: 5.5rem 0; background: #fff;">
    <div style="max-width: 1400px; margin: 0 auto; padding: 0 1.5rem;">
        <div style="text-align: center; max-width: 820px; margin: 0 auto 3.5rem auto;">
            <span style="display: inline-block; padding: 8px 18px; font-family: 'Inter', system-ui, sans-serif; font-size: 13px; font-weight: 800; letter-spacing: 0.2em; text-transform: uppercase; color: #FF8A00; background: #FFF4E6; border: 1px solid #FFE1C2; border-radius: 999px; margin-bottom: 20px;">
                Our Process
            </span>
            <h2 style="font-family: 'Inter', system-ui, sans-serif; font-size: 48px; line-height: 1.1; font-weight: 800; letter-spacing: -0.025em; color: <?= $DM_DARK ?>; margin: 0;">
                How We <span style="color: <?= $DM_PRIMARY ?>;">Deliver Results</span>
            </h2>
        </div>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 26px;" class="yk-dm-grid-3">
            <?php foreach ($process as $i => $p): ?>
                <div style="position: relative; padding: 36px 30px; background: linear-gradient(135deg, <?= $DM_LIGHT ?>, #fff); border-radius: 20px; border: 2px solid <?= $DM_BORDER ?>; display: flex; flex-direction: column;">
                    <?php if (!empty($p['image'])): ?>
                        <div style="margin: -36px -30px 24px -30px; border-radius: 18px 18px 0 0; overflow: hidden;">
                            <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['title']) ?>" loading="lazy" style="width: 100%; height: 180px; object-fit: cover; display: block;">
                        </div>
                    <?php endif; ?>

                    <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 16px;">
                        <div style="font-family: 'Inter', system-ui, sans-serif; font-size: 44px; line-height: 1; font-weight: 800; letter-spacing: -0.03em; background: linear-gradient(135deg, <?= $DM_PRIMARY ?>, <?= $DM_SECONDARY ?>); -webkit-background-clip: text; background-clip: text; color: transparent;">
                            <?= str_pad((string)($p['step_number'] ?? ($i + 1)), 2, '0', STR_PAD_LEFT) ?>
                        </div>
                        <?php if (!empty($p['icon'])): ?>
                            <div style="width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; color: #fff; background: linear-gradient(135deg, <?= $DM_PRIMARY ?>, <?= $DM_SECONDARY ?>);">
                                <i class="<?= htmlspecialchars($p['icon']) ?>"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <h3 style="font-family: 'Inter', system-ui, sans-serif; font-size: 21px; font-weight: 800; color: <?= $DM_DARK ?>; margin: 0 0 12px 0;">
                        <?= htmlspecialchars($p['title']) ?>
                    </h3>
                    <?php if (!empty($p['description'])): ?>
                        <p style="font-family: 'Inter', system-ui, sans-serif; font-size: 16px; line-height: 1.7; color: #64748b; margin: 0;">
                            <?= htmlspecialchars($p['description']) ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===================== FAQ ===================== -->
<?php if (!empty($faqs)): ?>
<section style="padding: 5.5rem 0; background: <?= $DM_LIGHT ?>;">
    <div style="max-width: 900px; margin: 0 auto; padding: 0 1.5rem;">
        <div style="text-align: center; margin-bottom: 3.5rem;">
            <span style="display: inline-block; padding: 8px 18px; font-family: 'Inter', system-ui, sans-serif; font-size: 13px; font-weight: 800; letter-spacing: 0.2em; text-transform: uppercase; color: <?= $DM_PRIMARY ?>; background: #fff; border: 1px solid <?= $DM_BORDER ?>; border-radius: 999px; margin-bottom: 20px;">
                FAQ
            </span>
            <h2 style="font-family: 'Inter', system-ui, sans-serif; font-size: 48px; line-height: 1.1; font-weight: 800; letter-spacing: -0.025em; color: <?= $DM_DARK ?>; margin: 0;">
                Frequently Asked <span style="color: <?= $DM_PRIMARY ?>;">Questions</span>
            </h2>
        </div>

        <div style="display: flex; flex-direction: column; gap: 16px;">
            <?php foreach ($faqs as $f): ?>
                <details style="background: #fff; border: 2px solid <?= $DM_BORDER ?>; border-radius: 18px; overflow: hidden;">
                    <summary style="cursor: pointer; padding: 26px 32px; display: flex; align-items: center; justify-content: space-between; gap: 24px; list-style: none; outline: none; font-family: 'Inter', system-ui, sans-serif; font-size: 20px; font-weight: 800; color: <?= $DM_DARK ?>; line-height: 1.4;">
                        <span><?= htmlspecialchars($f['question']) ?></span>
                        <span style="flex-shrink: 0; width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: <?= $DM_LIGHT ?>; color: <?= $DM_PRIMARY ?>; font-size: 16px;">
                            <i class="fas fa-plus"></i>
                        </span>
                    </summary>
                    <div style="padding: 0 32px 28px 32px; font-family: 'Inter', system-ui, sans-serif; font-size: 18px; line-height: 1.8; color: #475569;">
                        <?= nl2br(htmlspecialchars($f['answer'])) ?>
                    </div>
                </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===================== RELATED ===================== -->
<?php if (!empty($related)): ?>
<section style="padding: 5.5rem 0; background: #fff;">
    <div style="max-width: 1400px; margin: 0 auto; padding: 0 1.5rem;">
        <div style="text-align: center; max-width: 820px; margin: 0 auto 3.5rem auto;">
            <h2 style="font-family: 'Inter', system-ui, sans-serif; font-size: 44px; line-height: 1.15; font-weight: 800; letter-spacing: -0.025em; color: <?= $DM_DARK ?>; margin: 0;">
                You May Also <span style="color: <?= $DM_PRIMARY ?>;">Like</span>
            </h2>
        </div>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 26px;" class="yk-dm-grid-3">
            <?php foreach ($related as $r):
                $url = BASE_URL . '/services/' . htmlspecialchars($parent['slug']) . '/' . htmlspecialchars($r['child_slug']) . '/' . htmlspecialchars($r['slug']) . '/';
                $rdesc = $r['card_description'] ?: $r['short_description'] ?: '';
            ?>
                <a href="<?= $url ?>"
                   style="display: flex; flex-direction: column; background: #fff; border: 2px solid #e2e8f0; border-radius: 20px; overflow: hidden; text-decoration: none; transition: all 0.3s ease;"
                   onmouseover="this.style.borderColor='<?= $DM_PRIMARY ?>'; this.style.transform='translateY(-6px)'; this.style.boxShadow='0 22px 48px -14px rgba(124,58,237,0.3)';"
                   onmouseout="this.style.borderColor='#e2e8f0'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                    <?php if (!empty($r['hero_image'])): ?>
                        <div style="aspect-ratio: 16/10; background: #f1f5f9; overflow: hidden;">
                            <img src="<?= htmlspecialchars($r['hero_image']) ?>" alt="<?= htmlspecialchars($r['title']) ?>" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    <?php else: ?>
                        <div style="aspect-ratio: 16/10; background: linear-gradient(135deg, <?= $DM_LIGHT ?>, #fdf2f8); display: flex; align-items: center; justify-content: center; font-size: 46px;">
                            <i class="<?= htmlspecialchars($r['icon'] ?: 'fas fa-bullhorn') ?>" style="color: <?= $DM_PRIMARY ?>;"></i>
                        </div>
                    <?php endif; ?>
                    <div style="padding: 26px 28px; display: flex; flex-direction: column; flex: 1;">
                        <span style="font-family: 'Inter', system-ui, sans-serif; font-size: 12px; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase; color: #FF8A00; margin-bottom: 10px;">
                            <?= htmlspecialchars($r['child_name']) ?>
                        </span>
                        <h3 style="font-family: 'Inter', system-ui, sans-serif; font-size: 21px; line-height: 1.3; font-weight: 800; color: <?= $DM_DARK ?>; margin: 0 0 12px 0;">
                            <?= htmlspecialchars($r['title']) ?>
                        </h3>
                        <?php if ($rdesc): ?>
                            <p style="font-family: 'Inter', system-ui, sans-serif; font-size: 15px; line-height: 1.65; color: #64748b; margin: 0 0 18px 0; flex: 1;">
                                <?= htmlspecialchars(mb_strimwidth($rdesc, 0, 120, '…')) ?>
                            </p>
                        <?php endif; ?>
                        <span style="font-family: 'Inter', system-ui, sans-serif; font-size: 14px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: <?= $DM_PRIMARY ?>; display: inline-flex; align-items: center; gap: 8px;">
                            Learn More <i class="fas fa-arrow-right" style="font-size: 11px;"></i>
                        </span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===================== FINAL CTA ===================== -->
<section style="padding: 5.5rem 0; background: linear-gradient(135deg, <?= $DM_PRIMARY ?> 0%, <?= $DM_SECONDARY ?> 100%); color: #fff; text-align: center;">
    <div style="max-width: 820px; margin: 0 auto; padding: 0 1.5rem;">
        <h2 style="font-family: 'Inter', system-ui, sans-serif; font-size: 46px; line-height: 1.1; font-weight: 800; letter-spacing: -0.02em; color: #fff; margin: 0 0 18px 0;">
            <?= htmlspecialchars($cta_text) ?>
        </h2>
        <p style="font-family: 'Inter', system-ui, sans-serif; font-size: 19px; line-height: 1.65; color: rgba(255,255,255,0.9); margin: 0 0 36px 0;">
            Let's design a marketing strategy tailored to your business goals.
        </p>
        <div style="display: flex; flex-wrap: wrap; gap: 14px; justify-content: center;">
            <a href="<?= htmlspecialchars($cta_url) ?>"
               style="display: inline-flex; align-items: center; gap: 10px; padding: 18px 38px; background: #fff; color: <?= $DM_PRIMARY ?>; font-family: 'Inter', system-ui, sans-serif; font-weight: 800; font-size: 17px; border-radius: 999px; text-decoration: none; box-shadow: 0 14px 32px rgba(0,0,0,0.18);">
                Start Your Project <i class="fas fa-arrow-right" style="font-size: 14px;"></i>
            </a>
            <a href="<?= htmlspecialchars($wa_url) ?>" target="_blank" rel="noopener noreferrer"
               style="display: inline-flex; align-items: center; gap: 10px; padding: 18px 38px; background: #25D366; color: #fff; font-family: 'Inter', system-ui, sans-serif; font-weight: 800; font-size: 17px; border-radius: 999px; text-decoration: none;">
                <i class="fab fa-whatsapp" style="font-size: 20px;"></i> Chat on WhatsApp
            </a>
        </div>
    </div>
</section>

<!-- Responsive -->
<style>
@media (max-width: 1024px) {
    .yk-dm-hero-grid { grid-template-columns: 1fr !important; gap: 2.5rem !important; }
    .yk-dm-grid-3    { grid-template-columns: repeat(2, 1fr) !important; }
    .yk-dm-sec-grid  { grid-template-columns: 1fr !important; gap: 2.5rem !important; }
    .yk-dm-sec-grid > div { order: unset !important; }
    .yk-dm-stats     { grid-template-columns: repeat(2, 1fr) !important; }
}
@media (max-width: 640px) {
    .yk-dm-grid-3    { grid-template-columns: 1fr !important; }
    .yk-dm-stats     { grid-template-columns: repeat(2, 1fr) !important; gap: 1.5rem !important; }
    section h1 { font-size: 34px !important; }
    section h2 { font-size: 28px !important; }
}
</style>

<?php
include ROOT_PATH . '/includes/footer.php';
ob_end_flush();

$json_ld = [
    '@context' => 'https://schema.org',
    '@type'    => 'Service',
    'name'     => $service['title'],
    'provider' => ['@type' => 'Organization', 'name' => 'YK Digital Hub'],
    'url'      => $canonical,
];
if ($meta_description) $json_ld['description'] = $meta_description;
if ($og_image)         $json_ld['image']       = $og_image;
if ($starting_price)   $json_ld['offers']      = ['@type' => 'Offer', 'price' => $starting_price, 'priceCurrency' => 'USD'];
?>
<script type="application/ld+json"><?= json_encode($json_ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>