<?php
/**
 * Reusable Featured Portfolio section.
 *
 * Usage:
 *   require_once ROOT_PATH . '/includes/portfolio-featured.php';
 *   renderFeaturedPortfolio();                            // default: 3 featured
 *   renderFeaturedPortfolio(['limit' => 6]);              // show 6
 *   renderFeaturedPortfolio(['featured_only' => false]);  // show any active project
 *   renderFeaturedPortfolio(['parent_slug' => 'web-design-development']); // filter by parent
 *   renderFeaturedPortfolio([
 *       'badge'    => 'Our Recent Work',
 *       'title'    => "Projects We're Proud Of",
 *       'subtitle' => 'Real websites and marketing campaigns...',
 *   ]);
 */

if (!function_exists('renderFeaturedPortfolio')) {

    function renderFeaturedPortfolio($opts = []) {
        global $db;

        $limit          = isset($opts['limit']) ? max(1, (int)$opts['limit']) : 3;
        $featured_only  = $opts['featured_only'] ?? true;
        $parent_slug    = $opts['parent_slug'] ?? '';
        $badge          = $opts['badge']    ?? 'Our Recent Work';
        $title          = $opts['title']    ?? "Projects We're Proud Of";
        $subtitle       = $opts['subtitle'] ?? 'Real websites and marketing campaigns we\'ve delivered for clinics, small businesses, and growing brands.';
        $show_arrows    = $opts['show_arrows'] ?? true;

        // Build query
        $where  = ["p.status = 1", "c.status = 1", "par.status = 1", "c.is_fixed = 0", "par.is_fixed = 1"];
        $params = [];

        if ($featured_only) {
            $where[] = "p.featured = 1";
        }
        if ($parent_slug !== '') {
            $where[] = "par.slug = :parent_slug";
            $params[':parent_slug'] = $parent_slug;
        }

        $where_sql = 'WHERE ' . implode(' AND ', $where);

        $sql = "
            SELECT
                p.id, p.title, p.slug, p.card_description, p.short_description,
                p.hero_image, p.featured, p.sort_order,
                c.name AS child_name, c.slug AS child_slug,
                par.name AS parent_name, par.slug AS parent_slug
            FROM portfolio_projects p
            JOIN portfolio_categories c  ON p.category_id = c.id
            JOIN portfolio_categories par ON c.parent_id = par.id
            $where_sql
            ORDER BY p.sort_order ASC, p.id DESC
            LIMIT $limit
        ";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $projects = [];
        }

        if (empty($projects)) return;
        ?>

        <section class="relative py-20 lg:py-24 bg-white overflow-hidden">
            <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">

                <!-- Header -->
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <span class="inline-block px-4 py-1.5 text-xs font-bold tracking-[0.2em] uppercase rounded-full mb-5"
                          style="color:#FF8A00; background:#FFF4E6; border:1px solid #FFE1C2;">
                        <span style="color:#FF8A00;">✦</span> <?= htmlspecialchars($badge) ?>
                    </span>
                    <h2 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight mb-5 leading-[1.05]" style="color:#2B3F5C;">
                        <?php
                        // Split the title so we can color the last word in orange
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

                <!-- Cards grid -->
                <div class="relative">
                    <?php if ($show_arrows && count($projects) > 1): ?>
                        <!-- Left arrow -->
                        <button type="button" aria-label="Scroll left"
                                class="portfolio-scroll-left hidden md:flex absolute -left-4 lg:-left-6 top-1/2 -translate-y-1/2 z-10 w-11 h-11 items-center justify-center rounded-full bg-white shadow-lg border border-slate-200 hover:bg-slate-50 transition">
                            <i class="fas fa-chevron-left" style="color:#2B3F5C;"></i>
                        </button>
                        <!-- Right arrow -->
                        <button type="button" aria-label="Scroll right"
                                class="portfolio-scroll-right hidden md:flex absolute -right-4 lg:-right-6 top-1/2 -translate-y-1/2 z-10 w-11 h-11 items-center justify-center rounded-full bg-white shadow-lg border border-slate-200 hover:bg-slate-50 transition">
                            <i class="fas fa-chevron-right" style="color:#2B3F5C;"></i>
                        </button>
                    <?php endif; ?>

                    <div id="portfolioScroll"
                         class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-7 overflow-x-auto scroll-smooth pb-2"
                         style="scrollbar-width:none;">

                        <?php foreach ($projects as $p):
                            $url  = BASE_URL . '/portfolio/' . htmlspecialchars($p['parent_slug']) . '/' . htmlspecialchars($p['child_slug']) . '/' . htmlspecialchars($p['slug']) . '/';
                            $desc = $p['card_description'] ?: $p['short_description'] ?: '';
                            $img  = $p['hero_image'] ?: '';
                        ?>
                            <article class="group flex flex-col bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-[0_20px_50px_-15px_rgba(43,63,92,0.2)] transition-all duration-300 hover:-translate-y-1 shrink-0">

                                <!-- Image -->
                                <a href="<?= $url ?>" class="relative block aspect-[16/10] overflow-hidden bg-slate-100">
                                    <?php if ($img): ?>
                                        <img src="<?= htmlspecialchars($img) ?>"
                                             alt="<?= htmlspecialchars($p['title']) ?> — <?= htmlspecialchars($p['child_name']) ?> project by YK Digital Hub"
                                             loading="lazy"
                                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200">
                                            <i class="fas fa-briefcase text-4xl text-slate-300"></i>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Category pill overlay (bottom-left) -->
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

                                <!-- Body -->
                                <div class="p-6 flex flex-col flex-1">
                                    <h3 class="text-xl font-bold mb-2" style="color:#2B3F5C;">
                                        <a href="<?= $url ?>" class="hover:text-[#0084FF] transition-colors">
                                            <?= htmlspecialchars($p['title']) ?>
                                        </a>
                                    </h3>
                                    <?php if ($desc): ?>
                                        <p class="text-slate-500 text-sm leading-relaxed mb-5 line-clamp-3">
                                            <?= htmlspecialchars(mb_strimwidth($desc, 0, 140, '…')) ?>
                                        </p>
                                    <?php endif; ?>

                                    <a href="<?= $url ?>"
                                       class="mt-auto inline-flex items-center gap-2 text-sm font-bold tracking-wide uppercase transition-all group-hover:gap-3"
                                       style="color:#0084FF;">
                                        View Project
                                        <i class="fas fa-arrow-right text-xs"></i>
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>

                    </div>
                </div>

                <!-- View All -->
                <div class="text-center mt-12">
                    <a href="<?= BASE_URL ?>/portfolio/"
                       class="inline-flex items-center gap-2 px-8 py-3.5 text-white font-bold text-sm rounded-full shadow-lg transition-all hover:opacity-90 hover:-translate-y-0.5"
                       style="background: linear-gradient(90deg, #0084FF 0%, #0066CC 100%);">
                        View Full Portfolio
                        <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>

            </div>
        </section>

        <script>
        (function () {
            var scroller = document.getElementById('portfolioScroll');
            if (!scroller) return;
            var left  = document.querySelector('.portfolio-scroll-left');
            var right = document.querySelector('.portfolio-scroll-right');
            var step  = 380;

            if (left)  left.addEventListener('click',  function () { scroller.scrollBy({ left: -step, behavior: 'smooth' }); });
            if (right) right.addEventListener('click', function () { scroller.scrollBy({ left:  step, behavior: 'smooth' }); });
        })();
        </script>

        <?php
    }
}