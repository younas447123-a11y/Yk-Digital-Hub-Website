<?php
/**
 * Reusable Featured Services section.
 *
 * Usage:
 *   require_once ROOT_PATH . '/includes/services-featured.php';
 *   renderFeaturedServices();                       // 3 + 3 by default
 *   renderFeaturedServices(['per_parent' => 4]);    // 4 per parent category
 *   renderFeaturedServices(['show_groups' => false]); // flat 6-card grid, no group headings
 *   renderFeaturedServices([
 *       'badge'    => 'What We Do',
 *       'title'    => 'Services That Grow Your Business',
 *       'subtitle' => 'Websites, redesigns, SEO and paid marketing — under one roof.',
 *   ]);
 */

if (!function_exists('renderFeaturedServices')) {

    function renderFeaturedServices($opts = []) {
        global $db;

        $per_parent   = isset($opts['per_parent']) ? max(1, (int)$opts['per_parent']) : 3;
        $show_groups  = $opts['show_groups'] ?? true;
        $badge        = $opts['badge']    ?? 'What We Do';
        $title        = $opts['title']    ?? 'Services That Grow Your Business';
        $subtitle     = $opts['subtitle'] ?? 'Websites, redesigns, and digital marketing — delivered by one team, built for healthcare providers and growing businesses.';

        // ---- Fetch fixed parent categories ----
        try {
            $parents = $db->query("
                SELECT id, name, slug, icon, short_description
                FROM service_categories
                WHERE parent_id IS NULL AND is_fixed = 1 AND status = 1
                ORDER BY sort_order, id
            ")->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return;
        }
        if (empty($parents)) return;

        // ---- Fetch featured services for each parent (one query per parent, but limited) ----
        $groups = [];
        foreach ($parents as $parent) {
            try {
                $stmt = $db->prepare("
                    SELECT s.id, s.title, s.slug, s.short_description, s.card_description,
                           s.hero_image, s.icon, s.featured, s.sort_order,
                           c.name AS child_name, c.slug AS child_slug
                    FROM services s
                    JOIN service_categories c ON s.category_id = c.id
                    WHERE c.parent_id = :pid
                      AND c.is_fixed = 0 AND c.status = 1
                      AND s.status = 1
                    ORDER BY s.featured DESC, s.sort_order ASC, s.id DESC
                    LIMIT " . (int)$per_parent . "
                ");
                $stmt->execute([':pid' => $parent['id']]);
                $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($services)) {
                    $groups[] = ['parent' => $parent, 'services' => $services];
                }
            } catch (PDOException $e) {
                // skip this group on error
            }
        }

        if (empty($groups)) return;
        ?>

        <section class="relative py-20 lg:py-24 bg-white">
            <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">

                <!-- Header -->
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <span class="inline-block px-4 py-1.5 text-xs font-bold tracking-[0.2em] uppercase rounded-full mb-5"
                          style="color:#FF8A00; background:#FFF4E6; border:1px solid #FFE1C2;">
                        <span style="color:#FF8A00;">✦</span> <?= htmlspecialchars($badge) ?>
                    </span>
                    <h2 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight mb-5 leading-[1.05]" style="color:#2B3F5C;">
                        <?php
                        $words = explode(' ', $title);
                        $last  = array_pop($words);
                        $first = implode(' ', $words);
                        ?>
                        <?= htmlspecialchars($first) ?>
                        <span style="color:#FF8A00;"><?= htmlspecialchars($last) ?></span>
                    </h2>
                    <p class="text-slate-500 text-lg leading-relaxed max-w-2xl mx-auto">
                        <?= htmlspecialchars($subtitle) ?>
                    </p>
                </div>

                <?php if ($show_groups): ?>
                    <!-- ===== Grouped view: heading per parent category ===== -->
                    <div class="space-y-14">

                        <?php foreach ($groups as $group):
                            $parent = $group['parent'];
                            $services = $group['services'];
                        ?>
                            <div>
                                <!-- Group heading -->
                                <div class="flex items-center gap-3 mb-7">
                                    <div class="flex items-center justify-center w-11 h-11 rounded-xl text-white text-lg"
                                         style="background: linear-gradient(135deg, #0084FF 0%, #0066CC 100%);">
                                        <?php if (!empty($parent['icon'])): ?>
                                            <i class="<?= htmlspecialchars($parent['icon']) ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-layer-group"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-extrabold tracking-tight" style="color:#2B3F5C;">
                                            <a href="<?= BASE_URL ?>/services/<?= htmlspecialchars($parent['slug']) ?>/" class="hover:text-[#0084FF] transition">
                                                <?= htmlspecialchars($parent['name']) ?>
                                            </a>
                                        </h3>
                                        <?php if (!empty($parent['short_description'])): ?>
                                            <p class="text-sm text-slate-500 mt-0.5"><?= htmlspecialchars($parent['short_description']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Services grid: 3 on desktop, 2 on tablet, 1 on mobile -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <?php foreach ($services as $s):
                                        $url  = BASE_URL . '/services/' . htmlspecialchars($parent['slug']) . '/' . htmlspecialchars($s['child_slug']) . '/' . htmlspecialchars($s['slug']) . '/';
                                        $desc = $s['card_description'] ?: $s['short_description'] ?: '';
                                    ?>
                                        <a href="<?= $url ?>"
                                           class="group flex flex-col bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-[0_20px_50px_-15px_rgba(43,63,92,0.2)] transition-all duration-300 hover:-translate-y-1">

                                            <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
                                                <?php if ($s['hero_image']): ?>
                                                    <img src="<?= htmlspecialchars($s['hero_image']) ?>"
                                                         alt="<?= htmlspecialchars($s['title']) ?> — <?= htmlspecialchars($s['child_name']) ?> service by YK Digital Hub"
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
                                                    <?= htmlspecialchars($s['child_name']) ?>
                                                </span>

                                                <?php if ($s['featured']): ?>
                                                    <span class="absolute top-4 right-4 inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-bold tracking-wider uppercase rounded-md text-white"
                                                          style="background:#FF8A00;">
                                                        <i class="fas fa-star text-[9px]"></i> Featured
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <div class="p-6 flex flex-col flex-1">
                                                <h4 class="text-xl font-bold mb-2" style="color:#2B3F5C;">
                                                    <?= htmlspecialchars($s['title']) ?>
                                                </h4>
                                                <?php if ($desc): ?>
                                                    <p class="text-slate-500 text-sm leading-relaxed mb-5 line-clamp-3">
                                                        <?= htmlspecialchars(mb_strimwidth($desc, 0, 140, '…')) ?>
                                                    </p>
                                                <?php endif; ?>

                                                <span class="mt-auto inline-flex items-center gap-2 text-sm font-bold tracking-wide uppercase transition-all group-hover:gap-3"
                                                      style="color:#0084FF;">
                                                    Learn More
                                                    <i class="fas fa-arrow-right text-xs"></i>
                                                </span>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>

                                <!-- "View all" link for the group -->
                                <div class="mt-6 text-right">
                                    <a href="<?= BASE_URL ?>/services/<?= htmlspecialchars($parent['slug']) ?>/"
                                       class="inline-flex items-center gap-2 text-sm font-bold tracking-wide uppercase hover:gap-3 transition-all"
                                       style="color:#FF8A00;">
                                        View All <?= htmlspecialchars($parent['name']) ?>
                                        <i class="fas fa-arrow-right text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>

                    </div>

                <?php else: ?>
                    <!-- ===== Flat grid: all services mixed ===== -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php
                        $flat = [];
                        foreach ($groups as $g) {
                            foreach ($g['services'] as $s) {
                                $s['parent_slug'] = $g['parent']['slug'];
                                $flat[] = $s;
                            }
                        }
                        foreach ($flat as $s):
                            $url  = BASE_URL . '/services/' . htmlspecialchars($s['parent_slug']) . '/' . htmlspecialchars($s['child_slug']) . '/' . htmlspecialchars($s['slug']) . '/';
                            $desc = $s['card_description'] ?: $s['short_description'] ?: '';
                        ?>
                            <a href="<?= $url ?>"
                               class="group flex flex-col bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-[0_20px_50px_-15px_rgba(43,63,92,0.2)] transition-all duration-300 hover:-translate-y-1">
                                <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
                                    <?php if ($s['hero_image']): ?>
                                        <img src="<?= htmlspecialchars($s['hero_image']) ?>" alt="<?= htmlspecialchars($s['title']) ?>"
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
                                        <?= htmlspecialchars($s['child_name']) ?>
                                    </span>
                                </div>
                                <div class="p-6 flex flex-col flex-1">
                                    <h4 class="text-xl font-bold mb-2" style="color:#2B3F5C;"><?= htmlspecialchars($s['title']) ?></h4>
                                    <?php if ($desc): ?>
                                        <p class="text-slate-500 text-sm leading-relaxed mb-5"><?= htmlspecialchars(mb_strimwidth($desc, 0, 140, '…')) ?></p>
                                    <?php endif; ?>
                                    <span class="mt-auto inline-flex items-center gap-2 text-sm font-bold tracking-wide uppercase group-hover:gap-3 transition-all" style="color:#0084FF;">
                                        Learn More <i class="fas fa-arrow-right text-xs"></i>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- View All Services button -->
                <div class="text-center mt-14">
                    <a href="<?= BASE_URL ?>/services/"
                       class="inline-flex items-center gap-2 px-8 py-3.5 text-white font-bold text-sm rounded-full shadow-lg transition-all hover:opacity-90 hover:-translate-y-0.5"
                       style="background: linear-gradient(90deg, #0084FF 0%, #0066CC 100%);">
                        View All Services
                        <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>

            </div>
        </section>

        <?php
    }
}