<?php
/**
 * Services Category Listing
 * Handles:
 *   /services/{parent-slug}/
 *   /services/{parent-slug}/{child-slug}/
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
// ===== DEBUG (remove later) =====
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    echo "<pre style='background:#111;color:#0f0;padding:20px;font-size:14px;'>";
    echo "Parent slug from URL: " . htmlspecialchars($_GET['parent'] ?? '(none)') . "\n\n";

    if (isset($parent)) {
        echo "Parent FOUND: " . $parent['name'] . " (id=" . $parent['id'] . ")\n";
    } else {
        echo "Parent NOT FOUND\n";
    }

    echo "Children count: " . (isset($children) ? count($children) : 0) . "\n";
    if (isset($children)) {
        foreach ($children as $c) {
            echo "  → " . $c['name'] . " (id=" . $c['id'] . ")\n";
        }
    }

    echo "\nParent services count: " . (isset($parent_services) ? count($parent_services) : 0) . "\n";
    if (isset($parent_services)) {
        foreach ($parent_services as $s) {
            echo "  → " . $s['title'] . " | slug=" . $s['slug'] . " | child=" . ($s['child_name'] ?? '?') . "\n";
        }
    }

    echo "\n--- RAW SQL (no filters) ---\n";
    if (isset($parent['id'])) {
        $raw = $db->prepare("
            SELECT s.id, s.title, s.status, c.name AS child_name, c.status AS child_status, c.is_fixed
            FROM services s
            JOIN service_categories c ON s.category_id = c.id
            WHERE c.parent_id = :pid
        ");
        $raw->execute([':pid' => $parent['id']]);
        $rows = $raw->fetchAll(PDO::FETCH_ASSOC);
        echo "Rows found (no filter): " . count($rows) . "\n";
        foreach ($rows as $r) {
            echo "  → " . $r['title'] . " | s.status=" . $r['status'] . " | c.status=" . $r['child_status'] . " | c.is_fixed=" . $r['is_fixed'] . " | child=" . $r['child_name'] . "\n";
        }
    }
    echo "</pre>";
}
// ===== END DEBUG =====
// ---- Helper: 404 ----
function yk_404() {
    http_response_code(404);
    if (file_exists(ROOT_PATH . '/404.php')) { include ROOT_PATH . '/404.php'; }
    else { echo '<h1>404 — Not Found</h1>'; }
    exit;
}

$parent_slug = trim($_GET['parent'] ?? '');
$child_slug  = trim($_GET['child']  ?? '');

// Validate slug format
if ($parent_slug === '' || !preg_match('/^[a-z0-9-]+$/', $parent_slug)) yk_404();

// Fetch parent (fixed)
$stmt = $db->prepare("
    SELECT id, name, slug, short_description, description, icon
    FROM service_categories
    WHERE slug = :s AND parent_id IS NULL AND is_fixed = 1 AND status = 1
");
$stmt->execute([':s' => $parent_slug]);
$parent = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$parent) yk_404();

// Optional child
$child = null;
if ($child_slug !== '') {
    if (!preg_match('/^[a-z0-9-]+$/', $child_slug)) yk_404();
    $stmt = $db->prepare("
        SELECT id, name, slug, short_description, description, icon
        FROM service_categories
        WHERE slug = :s AND parent_id = :pid AND is_fixed = 0 AND status = 1
    ");
    $stmt->execute([':s' => $child_slug, ':pid' => $parent['id']]);
    $child = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$child) yk_404();
}

// ---- Fetch child categories (of the parent) ----
$stmt = $db->prepare("
    SELECT id, name, slug, short_description, icon
    FROM service_categories
    WHERE parent_id = :pid AND is_fixed = 0 AND status = 1
    ORDER BY sort_order, name
");
$stmt->execute([':pid' => $parent['id']]);
$children = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If a child is selected, also fetch services of that child
$child_services = [];
if ($child) {
    $stmt = $db->prepare("
        SELECT id, title, slug, short_description, card_description, hero_image, icon, featured
        FROM services
        WHERE category_id = :cid AND status = 1
        ORDER BY featured DESC, sort_order, id DESC
    ");
    $stmt->execute([':cid' => $child['id']]);
    $child_services = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// If no child selected, fetch featured services from each child (1 per child, or all)
$parent_services = [];
if (!$child && !empty($children)) {
    $stmt = $db->prepare("
        SELECT s.id, s.title, s.slug, s.short_description, s.card_description, s.hero_image, s.icon, s.featured,
               c.name AS child_name, c.slug AS child_slug
        FROM services s
        JOIN service_categories c ON s.category_id = c.id
        WHERE c.parent_id = :pid AND c.is_fixed = 0 AND c.status = 1
          AND s.status = 1
        ORDER BY s.featured DESC, s.sort_order, s.id DESC
    ");
    $stmt->execute([':pid' => $parent['id']]);
    $parent_services = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ---- SEO ----
$title_label = $child ? ($parent['name'] . ' — ' . $child['name']) : $parent['name'];
$page_title  = $title_label . ' Services | YK Digital Hub';
$page_description = $child['short_description'] ?? $child['description']
                  ?? $parent['short_description'] ?? $parent['description']
                  ?? ($title_label . ' services from YK Digital Hub.');
$page_canonical = $child
    ? BASE_URL . '/services/' . $parent['slug'] . '/' . $child['slug'] . '/'
    : BASE_URL . '/services/' . $parent['slug'] . '/';

ob_start();
include ROOT_PATH . '/includes/header.php';
if (file_exists(ROOT_PATH . '/includes/navbar.php')) include ROOT_PATH . '/includes/navbar.php';
?>

<section class="py-16 lg:py-20 bg-slate-50">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">

        <!-- Breadcrumb -->
        <nav class="mb-8 text-sm text-slate-500" aria-label="Breadcrumb">
            <a href="<?= BASE_URL ?>/" class="hover:text-[#0084FF] transition">Home</a>
            <span class="mx-2">/</span>
            <a href="<?= BASE_URL ?>/services/" class="hover:text-[#0084FF] transition">Services</a>
            <span class="mx-2">/</span>
            <?php if ($child): ?>
                <a href="<?= BASE_URL ?>/services/<?= htmlspecialchars($parent['slug']) ?>/" class="hover:text-[#0084FF] transition"><?= htmlspecialchars($parent['name']) ?></a>
                <span class="mx-2">/</span>
            <?php endif; ?>
            <span class="font-semibold" style="color:#2B3F5C;"><?= htmlspecialchars($child['name'] ?? $parent['name']) ?></span>
        </nav>

        <!-- Hero -->
        <div class="text-center max-w-3xl mx-auto mb-14">
            <span class="inline-block px-4 py-1.5 text-xs font-bold tracking-[0.2em] uppercase rounded-full mb-5"
                  style="color:#FF8A00; background:#FFF4E6; border:1px solid #FFE1C2;">
                <span style="color:#FF8A00;">✦</span> <?= htmlspecialchars($parent['name']) ?>
            </span>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight mb-5 leading-[1.05]" style="color:#2B3F5C;">
                <?= htmlspecialchars($child ? $child['name'] : $parent['name']) ?>
            </h1>
            <?php $desc = $child['description'] ?? $child['short_description'] ?? $parent['description'] ?? $parent['short_description'] ?? ''; ?>
            <?php if ($desc): ?>
                <p class="text-slate-500 text-lg leading-relaxed max-w-2xl mx-auto"><?= nl2br(htmlspecialchars($desc)) ?></p>
            <?php endif; ?>
        </div>

        <?php if ($child): ?>
            <!-- ============ CHILD CATEGORY PAGE: list its services ============ -->
            <?php if (empty($child_services)): ?>
                <div class="text-center py-20">
                    <i class="fas fa-cogs text-5xl text-slate-300 mb-4"></i>
                    <p class="text-slate-500 text-lg">No services published under this category yet.</p>
                    <a href="<?= BASE_URL ?>/services/<?= htmlspecialchars($parent['slug']) ?>/"
                       class="inline-block mt-6 px-6 py-3 rounded-full font-bold text-white text-sm"
                       style="background: linear-gradient(90deg, #0084FF 0%, #0066CC 100%);">
                        Back to <?= htmlspecialchars($parent['name']) ?>
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($child_services as $s):
                        $url  = BASE_URL . '/services/' . htmlspecialchars($parent['slug']) . '/' . htmlspecialchars($child['slug']) . '/' . htmlspecialchars($s['slug']) . '/';
                        $d    = $s['card_description'] ?: $s['short_description'] ?: '';
                    ?>
                        <a href="<?= $url ?>"
                           class="group flex flex-col bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-[0_20px_50px_-15px_rgba(43,63,92,0.2)] transition-all duration-300 hover:-translate-y-1">
                            <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
                                <?php if (!empty($s['hero_image'])): ?>
                                    <img src="<?= htmlspecialchars($s['hero_image']) ?>"
                                         alt="<?= htmlspecialchars($s['title']) ?> — <?= htmlspecialchars($child['name']) ?> service by YK Digital Hub"
                                         width="800" height="500"
                                         loading="lazy"
                                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200">
                                        <?php if (!empty($s['icon'])): ?>
                                            <i class="<?= htmlspecialchars($s['icon']) ?> text-5xl" style="color:#0084FF;"></i>
                                        <?php else: ?>
                                            <i class="fas fa-cogs text-5xl text-slate-300"></i>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <span class="absolute bottom-4 left-4 inline-block px-3 py-1.5 text-[10px] font-extrabold tracking-wider uppercase rounded-md text-white"
                                      style="background: linear-gradient(90deg, #0084FF 0%, #0066CC 100%);">
                                    <?= htmlspecialchars($child['name']) ?>
                                </span>
                                <?php if (!empty($s['featured'])): ?>
                                    <span class="absolute top-4 right-4 inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-bold tracking-wider uppercase rounded-md text-white"
                                          style="background:#FF8A00;">
                                        <i class="fas fa-star text-[9px]"></i> Featured
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="p-6 flex flex-col flex-1">
                                <h3 class="text-xl font-bold mb-2" style="color:#2B3F5C;"><?= htmlspecialchars($s['title']) ?></h3>
                                <?php if ($d): ?>
                                    <p class="text-slate-500 text-sm leading-relaxed mb-5"><?= htmlspecialchars(mb_strimwidth($d, 0, 140, '…')) ?></p>
                                <?php endif; ?>
                                <span class="mt-auto inline-flex items-center gap-2 text-sm font-bold tracking-wide uppercase group-hover:gap-3 transition-all" style="color:#0084FF;">
                                    Learn More <i class="fas fa-arrow-right text-xs"></i>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- ============ PARENT CATEGORY PAGE: list child categories + services ============ -->

            <?php if (empty($children)): ?>
                <div class="text-center py-20">
                    <i class="fas fa-cogs text-5xl text-slate-300 mb-4"></i>
                    <p class="text-slate-500 text-lg">No subcategories under <?= htmlspecialchars($parent['name']) ?> yet.</p>
                </div>
            <?php else: ?>

                <!-- Child category tiles -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-16">
                    <?php foreach ($children as $c): ?>
                        <a href="<?= BASE_URL ?>/services/<?= htmlspecialchars($parent['slug']) ?>/<?= htmlspecialchars($c['slug']) ?>/"
                           class="group flex items-start gap-4 p-6 bg-white rounded-2xl border border-slate-200 hover:border-[#0084FF] hover:shadow-[0_20px_40px_-15px_rgba(0,132,255,0.25)] transition-all duration-300 hover:-translate-y-1">
                            <div class="flex items-center justify-center w-12 h-12 rounded-xl text-white text-lg shrink-0"
                                 style="background: linear-gradient(135deg, #0084FF 0%, #0066CC 100%);">
                                <?php if (!empty($c['icon'])): ?>
                                    <i class="<?= htmlspecialchars($c['icon']) ?>"></i>
                                <?php else: ?>
                                    <i class="fas fa-cog"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-bold mb-1 group-hover:text-[#0084FF] transition" style="color:#2B3F5C;">
                                    <?= htmlspecialchars($c['name']) ?>
                                </h3>
                                <?php if (!empty($c['short_description'])): ?>
                                    <p class="text-slate-500 text-sm leading-snug"><?= htmlspecialchars(mb_strimwidth($c['short_description'], 0, 90, '…')) ?></p>
                                <?php endif; ?>
                                <span class="inline-flex items-center gap-1.5 mt-3 text-xs font-bold uppercase tracking-wider" style="color:#FF8A00;">
                                    View Services <i class="fas fa-arrow-right text-[10px]"></i>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Services across all children -->
                <?php if (!empty($parent_services)): ?>
                    <div class="mb-10">
                        <h2 class="text-2xl lg:text-3xl font-extrabold mb-6" style="color:#2B3F5C;">
                            <?= htmlspecialchars($parent['name']) ?> Services
                        </h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($parent_services as $s):
                            $url = BASE_URL . '/services/' . htmlspecialchars($parent['slug']) . '/' . htmlspecialchars($s['child_slug']) . '/' . htmlspecialchars($s['slug']) . '/';
                            $d   = $s['card_description'] ?: $s['short_description'] ?: '';
                        ?>
                            <a href="<?= $url ?>"
                               class="group flex flex-col bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-[0_20px_50px_-15px_rgba(43,63,92,0.2)] transition-all duration-300 hover:-translate-y-1">
                                <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
                                    <?php if (!empty($s['hero_image'])): ?>
                                        <img src="<?= htmlspecialchars($s['hero_image']) ?>"
                                             alt="<?= htmlspecialchars($s['title']) ?> — <?= htmlspecialchars($s['child_name']) ?> service by YK Digital Hub"
                                             loading="lazy"
                                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200">
                                            <?php if (!empty($s['icon'])): ?>
                                                <i class="<?= htmlspecialchars($s['icon']) ?> text-5xl" style="color:#0084FF;"></i>
                                            <?php else: ?>
                                                <i class="fas fa-cogs text-5xl text-slate-300"></i>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <span class="absolute bottom-4 left-4 inline-block px-3 py-1.5 text-[10px] font-extrabold tracking-wider uppercase rounded-md text-white"
                                          style="background: linear-gradient(90deg, #0084FF 0%, #0066CC 100%);">
                                        <?= htmlspecialchars($s['child_name']) ?>
                                    </span>
                                </div>
                                <div class="p-6 flex flex-col flex-1">
                                    <h3 class="text-xl font-bold mb-2" style="color:#2B3F5C;"><?= htmlspecialchars($s['title']) ?></h3>
                                    <?php if ($d): ?>
                                        <p class="text-slate-500 text-sm leading-relaxed mb-5"><?= htmlspecialchars(mb_strimwidth($d, 0, 140, '…')) ?></p>
                                    <?php endif; ?>
                                    <span class="mt-auto inline-flex items-center gap-2 text-sm font-bold tracking-wide uppercase group-hover:gap-3 transition-all" style="color:#0084FF;">
                                        Learn More <i class="fas fa-arrow-right text-xs"></i>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            <?php endif; ?>
        <?php endif; ?>

    </div>
</section>

<?php
include ROOT_PATH . '/includes/footer.php';
ob_end_flush();