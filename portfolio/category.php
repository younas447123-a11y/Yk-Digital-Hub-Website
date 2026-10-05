<?php
/**
 * Portfolio Category Listing
 * Handles:
 *   /portfolio/{parent-slug}/
 *   /portfolio/{parent-slug}/{child-slug}/
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

function yk_port_404() {
    http_response_code(404);
    if (file_exists(ROOT_PATH . '/404.php')) { include ROOT_PATH . '/404.php'; }
    else { echo '<h1>404 — Not Found</h1>'; }
    exit;
}

$parent_slug = trim($_GET['parent'] ?? '');
$child_slug  = trim($_GET['child']  ?? '');

if ($parent_slug === '' || !preg_match('/^[a-z0-9-]+$/', $parent_slug)) yk_port_404();

// Fetch parent (uses only real columns: id, name, slug, description, icon, order_num, status, is_fixed)
$stmt = $db->prepare("
    SELECT id, name, slug, description, icon
    FROM portfolio_categories
    WHERE slug = :s AND parent_id IS NULL AND is_fixed = 1 AND status = 1
");
$stmt->execute([':s' => $parent_slug]);
$parent = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$parent) yk_port_404();

$child = null;
if ($child_slug !== '') {
    if (!preg_match('/^[a-z0-9-]+$/', $child_slug)) yk_port_404();
    $stmt = $db->prepare("
        SELECT id, name, slug, description, icon
        FROM portfolio_categories
        WHERE slug = :s AND parent_id = :pid AND is_fixed = 0 AND status = 1
    ");
    $stmt->execute([':s' => $child_slug, ':pid' => $parent['id']]);
    $child = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$child) yk_port_404();
}

// Children of parent (real columns only)
$stmt = $db->prepare("
    SELECT id, name, slug, description, icon
    FROM portfolio_categories
    WHERE parent_id = :pid AND is_fixed = 0 AND status = 1
    ORDER BY order_num, name
");
$stmt->execute([':pid' => $parent['id']]);
$children = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Projects
$where  = ["p.status = 1", "c.status = 1", "par.status = 1", "par.id = :pid"];
$params = [':pid' => $parent['id']];
if ($child) { $where[] = "c.id = :cid"; $params[':cid'] = $child['id']; }

$stmt = $db->prepare("
    SELECT p.id, p.title, p.slug, p.card_description, p.short_description, p.hero_image, p.featured,
           c.name AS child_name, c.slug AS child_slug
    FROM portfolio_projects p
    JOIN portfolio_categories c   ON p.category_id = c.id
    JOIN portfolio_categories par ON c.parent_id = par.id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY p.featured DESC, p.sort_order, p.id DESC
");
$stmt->execute($params);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title_label = $child ? ($parent['name'] . ' — ' . $child['name']) : $parent['name'];
$page_title  = $title_label . ' Portfolio | YK Digital Hub';
$page_description = $child['description'] ?? $parent['description'] ?? ('Portfolio projects under ' . $parent['name']);
$page_canonical = $child
    ? BASE_URL . '/portfolio/' . $parent['slug'] . '/' . $child['slug'] . '/'
    : BASE_URL . '/portfolio/' . $parent['slug'] . '/';

ob_start();
include ROOT_PATH . '/includes/header.php';
if (file_exists(ROOT_PATH . '/includes/navbar.php')) include ROOT_PATH . '/includes/navbar.php';
?>

<section class="py-16 lg:py-20 bg-slate-50">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">

        <nav class="mb-8 text-sm text-slate-500" aria-label="Breadcrumb">
            <a href="<?= BASE_URL ?>/" class="hover:text-[#0084FF] transition">Home</a>
            <span class="mx-2">/</span>
            <a href="<?= BASE_URL ?>/portfolio/" class="hover:text-[#0084FF] transition">Portfolio</a>
            <span class="mx-2">/</span>
            <?php if ($child): ?>
                <a href="<?= BASE_URL ?>/portfolio/<?= htmlspecialchars($parent['slug']) ?>/" class="hover:text-[#0084FF] transition"><?= htmlspecialchars($parent['name']) ?></a>
                <span class="mx-2">/</span>
            <?php endif; ?>
            <span class="font-semibold" style="color:#2B3F5C;"><?= htmlspecialchars($child['name'] ?? $parent['name']) ?></span>
        </nav>

        <div class="text-center max-w-3xl mx-auto mb-14">
            <span class="inline-block px-4 py-1.5 text-xs font-bold tracking-[0.2em] uppercase rounded-full mb-5"
                  style="color:#FF8A00; background:#FFF4E6; border:1px solid #FFE1C2;">
                <span style="color:#FF8A00;">✦</span> <?= htmlspecialchars($parent['name']) ?>
            </span>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight mb-5 leading-[1.05]" style="color:#2B3F5C;">
                <?= htmlspecialchars($child ? $child['name'] : $parent['name']) ?>
            </h1>
            <?php $desc = $child['description'] ?? $parent['description'] ?? ''; ?>
            <?php if ($desc): ?>
                <p class="text-slate-500 text-lg leading-relaxed max-w-2xl mx-auto"><?= htmlspecialchars($desc) ?></p>
            <?php endif; ?>
        </div>

        <!-- Child category tiles (only if no child selected) -->
        <?php if (!$child && !empty($children)): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-16">
                <?php foreach ($children as $c): ?>
                    <a href="<?= BASE_URL ?>/portfolio/<?= htmlspecialchars($parent['slug']) ?>/<?= htmlspecialchars($c['slug']) ?>/"
                       class="group flex items-start gap-4 p-6 bg-white rounded-2xl border border-slate-200 hover:border-[#0084FF] hover:shadow-[0_20px_40px_-15px_rgba(0,132,255,0.25)] transition-all duration-300 hover:-translate-y-1">
                        <div class="flex items-center justify-center w-12 h-12 rounded-xl text-white text-lg shrink-0"
                             style="background: linear-gradient(135deg, #0084FF 0%, #0066CC 100%);">
                            <?php if (!empty($c['icon'])): ?>
                                <i class="<?= htmlspecialchars($c['icon']) ?>"></i>
                            <?php else: ?>
                                <i class="fas fa-folder-open"></i>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-bold mb-1 group-hover:text-[#0084FF] transition" style="color:#2B3F5C;">
                                <?= htmlspecialchars($c['name']) ?>
                            </h3>
                            <?php if (!empty($c['description'])): ?>
                                <p class="text-slate-500 text-sm leading-snug"><?= htmlspecialchars(mb_strimwidth($c['description'], 0, 90, '…')) ?></p>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Projects -->
        <?php if (empty($projects)): ?>
            <div class="text-center py-20">
                <i class="fas fa-briefcase text-5xl text-slate-300 mb-4"></i>
                <p class="text-slate-500 text-lg">No projects published under this category yet.</p>
                <a href="<?= BASE_URL ?>/portfolio/" class="inline-block mt-6 px-6 py-3 rounded-full font-bold text-white text-sm"
                   style="background: linear-gradient(90deg, #0084FF 0%, #0066CC 100%);">
                    View All Projects
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-7">
                <?php foreach ($projects as $p):
                    $url = BASE_URL . '/portfolio/' . htmlspecialchars($parent['slug']) . '/' . htmlspecialchars($p['child_slug']) . '/' . htmlspecialchars($p['slug']) . '/';
                    $d   = $p['card_description'] ?: $p['short_description'] ?: '';
                ?>
                    <article class="group flex flex-col bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-[0_20px_50px_-15px_rgba(43,63,92,0.2)] transition-all duration-300 hover:-translate-y-1">
                        <a href="<?= $url ?>" class="relative block aspect-[16/10] overflow-hidden bg-slate-100">
                            <?php if ($p['hero_image']): ?>
                                <img src="<?= htmlspecialchars($p['hero_image']) ?>"
                                     alt="<?= htmlspecialchars($p['title']) ?> — <?= htmlspecialchars($p['child_name']) ?> project by YK Digital Hub"
                                     width="800" height="500"
                                     loading="lazy"
                                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200">
                                    <i class="fas fa-briefcase text-4xl text-slate-300"></i>
                                </div>
                            <?php endif; ?>
                            <span class="absolute bottom-4 left-4 inline-block px-3 py-1.5 text-[10px] font-extrabold tracking-wider uppercase rounded-md text-white"
                                  style="background: linear-gradient(90deg, #0084FF 0%, #0066CC 100%);">
                                <?= htmlspecialchars($p['child_name']) ?>
                            </span>
                            <?php if ($p['featured']): ?>
                                <span class="absolute top-4 right-4 inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-bold tracking-wider uppercase rounded-md text-white"
                                      style="background:#FF8A00;">
                                    <i class="fas fa-star text-[9px]"></i> Featured
                                </span>
                            <?php endif; ?>
                        </a>
                        <div class="p-6 flex flex-col flex-1">
                            <h3 class="text-xl font-bold mb-2" style="color:#2B3F5C;">
                                <a href="<?= $url ?>" class="hover:text-[#0084FF] transition-colors"><?= htmlspecialchars($p['title']) ?></a>
                            </h3>
                            <?php if ($d): ?>
                                <p class="text-slate-500 text-sm leading-relaxed mb-5"><?= htmlspecialchars(mb_strimwidth($d, 0, 140, '…')) ?></p>
                            <?php endif; ?>
                            <a href="<?= $url ?>" class="mt-auto inline-flex items-center gap-2 text-sm font-bold tracking-wide uppercase group-hover:gap-3 transition-all" style="color:#0084FF;">
                                View Project <i class="fas fa-arrow-right text-xs"></i>
                            </a>
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