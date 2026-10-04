<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = 'Services | YK Digital Hub';
$page_description = 'Web design, development, and digital marketing services for healthcare providers and growing businesses.';
$page_canonical = BASE_URL . '/services/';

// Fetch the two fixed parents
$parents = $db->query("
    SELECT id, name, slug, short_description, description, icon
    FROM service_categories
    WHERE parent_id IS NULL AND is_fixed = 1 AND status = 1
    ORDER BY sort_order, id
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch children for each
foreach ($parents as &$p) {
    $s = $db->prepare("SELECT id, name, slug, short_description, icon FROM service_categories WHERE parent_id = :pid AND is_fixed = 0 AND status = 1 ORDER BY sort_order, name");
    $s->execute([':pid' => $p['id']]);
    $p['children'] = $s->fetchAll(PDO::FETCH_ASSOC);
}
unset($p);

ob_start();
include ROOT_PATH . '/includes/header.php';
if (file_exists(ROOT_PATH . '/includes/navbar.php')) include ROOT_PATH . '/includes/navbar.php';
?>

<section class="py-16 lg:py-20 bg-slate-50">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <div class="text-center max-w-3xl mx-auto mb-14">
            <span class="inline-block px-4 py-1.5 text-xs font-bold tracking-[0.2em] uppercase rounded-full mb-5" style="color:#FF8A00; background:#FFF4E6; border:1px solid #FFE1C2;">
                <span style="color:#FF8A00;">✦</span> Our Services
            </span>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight mb-5 leading-[1.05]" style="color:#2B3F5C;">
                Everything You Need to <span style="color:#FF8A00;">Grow Online</span>
            </h1>
            <p class="text-slate-500 text-lg">Custom websites, redesigns, SEO and digital marketing — built for healthcare providers and growing businesses.</p>
        </div>

        <?php foreach ($parents as $parent): ?>
            <div class="mb-16">
                <div class="flex items-center gap-3 mb-8">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl text-white text-lg" style="background: linear-gradient(135deg, #0084FF 0%, #0066CC 100%);">
                        <i class="<?= htmlspecialchars($parent['icon'] ?: 'fas fa-layer-group') ?>"></i>
                    </div>
                    <h2 class="text-2xl lg:text-3xl font-extrabold" style="color:#2B3F5C;">
                        <a href="<?= BASE_URL ?>/services/<?= htmlspecialchars($parent['slug']) ?>/" class="hover:text-[#0084FF] transition">
                            <?= htmlspecialchars($parent['name']) ?>
                        </a>
                    </h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <?php foreach ($parent['children'] as $c): ?>
                        <a href="<?= BASE_URL ?>/services/<?= htmlspecialchars($parent['slug']) ?>/<?= htmlspecialchars($c['slug']) ?>/"
                           class="group flex items-start gap-4 p-6 bg-white rounded-2xl border border-slate-200 hover:border-[#0084FF] hover:shadow-lg transition-all hover:-translate-y-1">
                            <div class="flex items-center justify-center w-12 h-12 rounded-xl text-white shrink-0" style="background: linear-gradient(135deg, #0084FF 0%, #0066CC 100%);">
                                <i class="<?= htmlspecialchars($c['icon'] ?: 'fas fa-cog') ?>"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold mb-1 group-hover:text-[#0084FF]" style="color:#2B3F5C;"><?= htmlspecialchars($c['name']) ?></h3>
                                <?php if (!empty($c['short_description'])): ?>
                                    <p class="text-slate-500 text-sm"><?= htmlspecialchars(mb_strimwidth($c['short_description'], 0, 90, '…')) ?></p>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php
include ROOT_PATH . '/includes/footer.php';
ob_end_flush();