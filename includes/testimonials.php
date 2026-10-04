<?php
/**
 * Reusable testimonial rendering component (with carousel mode).
 *
 * Usage:
 *   renderTestimonials();                                                // static grid
 *   renderTestimonials(['carousel' => true]);                            // carousel (all)
 *   renderTestimonials(['carousel' => true, 'limit' => 20]);             // carousel, max 20
 *   renderTestimonials(['carousel' => true, 'autoplay' => 6000]);        // custom interval
 *   renderTestimonials(['carousel' => true, 'autoplay' => 0]);           // no autoplay
 */

if (!function_exists('renderTestimonials')) {

    function renderTestimonials($opts = []) {
        global $db;

        $featured   = !empty($opts['featured']);
        $carousel   = !empty($opts['carousel']);
        $limit      = isset($opts['limit']) ? max(1, (int)$opts['limit']) : ($carousel ? 50 : 6);
        $service_id = isset($opts['service_id']) ? (int)$opts['service_id'] : 0;
        $project_id = isset($opts['project_id']) ? (int)$opts['project_id'] : 0;
        $title      = $opts['title'] ?? '';
        $subtitle   = $opts['subtitle'] ?? '';
        $autoplay   = $opts['autoplay'] ?? 7000; // ms; 0 to disable

        $where = ["t.status = 1"];
        $params = [];
        if ($featured)   $where[] = "t.featured = 1";
        if ($service_id) { $where[] = "t.service_id = :sid"; $params[':sid'] = $service_id; }
        if ($project_id) { $where[] = "t.project_id = :pid"; $params[':pid'] = $project_id; }

        $sql = "SELECT t.*, s.title AS service_title, p.title AS project_title
                FROM testimonials t
                LEFT JOIN services s ON t.service_id = s.id
                LEFT JOIN portfolio_projects p ON t.project_id = p.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY t.sort_order ASC, t.id DESC
                LIMIT " . (int)$limit;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($items)) return;

        if ($carousel) {
            yk_render_testimonial_carousel($items, $title, $subtitle, $autoplay);
        } else {
            yk_render_testimonial_grid($items, $title, $subtitle);
        }
    }
}

/** Static grid renderer */
function yk_render_testimonial_grid($items, $title, $subtitle) {
    ?>
    <section class="py-20 bg-slate-50">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
            <?php if ($title): ?>
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <h2 class="text-4xl sm:text-5xl font-extrabold tracking-tight mb-4" style="color:#2B3F5C;">
                        <?= htmlspecialchars($title) ?>
                    </h2>
                    <?php if ($subtitle): ?>
                        <p class="text-slate-500 text-lg"><?= htmlspecialchars($subtitle) ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($items as $t): ?>
                    <?php yk_render_testimonial_card($t); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

/** Carousel renderer */
function yk_render_testimonial_carousel($items, $title, $subtitle, $autoplay = 7000) {
    $uid = 'tst-' . substr(md5(uniqid('', true)), 0, 8);
    ?>
    <section class="py-20 bg-slate-50 overflow-hidden" id="<?= $uid ?>-section">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">

            <?php if ($title): ?>
                <div class="text-center max-w-3xl mx-auto mb-12">
                    <span class="inline-block px-4 py-1.5 text-xs font-bold tracking-[0.2em] uppercase rounded-full mb-5"
                          style="color:#FF8A00; background:#FFF4E6; border:1px solid #FFE1C2;">
                        <span style="color:#FF8A00;">✦</span> Client Reviews
                    </span>
                    <h2 class="text-4xl sm:text-5xl font-extrabold tracking-tight mb-4" style="color:#2B3F5C;">
                        <?php
                        $words = explode(' ', $title);
                        $last  = array_pop($words);
                        $first = implode(' ', $words);
                        ?>
                        <?= htmlspecialchars($first) ?>
                        <span style="color:#FF8A00;"><?= htmlspecialchars($last) ?></span>
                    </h2>
                    <?php if ($subtitle): ?>
                        <p class="text-slate-500 text-lg"><?= htmlspecialchars($subtitle) ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="relative">

                <!-- Prev / Next arrows (desktop) -->
                <button type="button" aria-label="Previous"
                        data-carousel-prev="<?= $uid ?>"
                        class="hidden lg:flex absolute -left-5 xl:-left-6 top-1/2 -translate-y-1/2 z-10 w-12 h-12 items-center justify-center rounded-full bg-white shadow-xl border border-slate-200 hover:bg-[#0084FF] hover:text-white hover:border-[#0084FF] transition group">
                    <i class="fas fa-chevron-left text-sm" style="color:#2B3F5C;"></i>
                </button>
                <button type="button" aria-label="Next"
                        data-carousel-next="<?= $uid ?>"
                        class="hidden lg:flex absolute -right-5 xl:-right-6 top-1/2 -translate-y-1/2 z-10 w-12 h-12 items-center justify-center rounded-full bg-white shadow-xl border border-slate-200 hover:bg-[#0084FF] hover:text-white hover:border-[#0084FF] transition">
                    <i class="fas fa-chevron-right text-sm" style="color:#2B3F5C;"></i>
                </button>

                <!-- Track -->
                <div id="<?= $uid ?>"
                     class="flex gap-6 overflow-x-auto snap-x snap-mandatory scroll-smooth pb-4 [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">

                    <?php foreach ($items as $t): ?>
                        <div class="snap-start shrink-0 w-full md:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)]">
                            <?php yk_render_testimonial_card($t); ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Dots -->
                <?php if (count($items) > 1): ?>
                <div class="flex justify-center gap-2 mt-8" data-carousel-dots="<?= $uid ?>">
                    <?php
                    // One dot per "page" — approximate. Real dots computed in JS.
                    $total_dots = max(2, min(count($items), 6));
                    for ($i = 0; $i < $total_dots; $i++):
                    ?>
                        <button type="button"
                                class="carousel-dot w-2.5 h-2.5 rounded-full bg-slate-300 hover:bg-slate-400 transition"
                                data-index="<?= $i ?>" aria-label="Go to slide <?= $i + 1 ?>"></button>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>

            </div>
        </div>

        <script>
        (function () {
            var uid     = <?= json_encode($uid) ?>;
            var track   = document.getElementById(uid);
            if (!track) return;
            var section = document.getElementById(uid + '-section');
            var prevBtn = document.querySelector('[data-carousel-prev="' + uid + '"]');
            var nextBtn = document.querySelector('[data-carousel-next="' + uid + '"]');
            var dots    = Array.from(document.querySelectorAll('[data-carousel-dots="' + uid + '"] .carousel-dot'));
            var autoplayMs = <?= (int)$autoplay ?>;
            var timerId = null;

            // Scroll by one card width
            function getStep() {
                var first = track.querySelector(':scope > *');
                if (!first) return track.clientWidth;
                var style = window.getComputedStyle(track);
                var gap = parseFloat(style.gap || style.columnGap || 0) || 0;
                return first.offsetWidth + gap;
            }

            function updateActiveDot() {
                if (!dots.length) return;
                var step = getStep();
                if (step <= 0) return;
                var idx = Math.round(track.scrollLeft / step);
                var maxIdx = dots.length - 1;
                if (idx > maxIdx) idx = maxIdx;
                dots.forEach(function (d, i) {
                    if (i === idx) {
                        d.classList.add('bg-[#0084FF]');
                        d.classList.remove('bg-slate-300');
                        d.style.width = '24px';
                    } else {
                        d.classList.remove('bg-[#0084FF]');
                        d.classList.add('bg-slate-300');
                        d.style.width = '10px';
                    }
                });
            }

            function next() {
                var step = getStep();
                var maxScroll = track.scrollWidth - track.clientWidth;
                if (track.scrollLeft >= maxScroll - 5) {
                    track.scrollTo({ left: 0, behavior: 'smooth' });
                } else {
                    track.scrollBy({ left: step, behavior: 'smooth' });
                }
            }
            function prev() {
                var step = getStep();
                if (track.scrollLeft <= 5) {
                    track.scrollTo({ left: track.scrollWidth, behavior: 'smooth' });
                } else {
                    track.scrollBy({ left: -step, behavior: 'smooth' });
                }
            }

            if (nextBtn) nextBtn.addEventListener('click', function () { next(); restartAutoplay(); });
            if (prevBtn) prevBtn.addEventListener('click', function () { prev(); restartAutoplay(); });

            // Dots
            dots.forEach(function (d) {
                d.addEventListener('click', function () {
                    var step = getStep();
                    var i = parseInt(d.getAttribute('data-index'), 10) || 0;
                    track.scrollTo({ left: i * step, behavior: 'smooth' });
                    restartAutoplay();
                });
            });

            track.addEventListener('scroll', function () {
                // throttle to next frame
                window.requestAnimationFrame(updateActiveDot);
            }, { passive: true });

            // Autoplay
            function startAutoplay() {
                if (!autoplayMs || autoplayMs < 2000) return;
                timerId = setInterval(next, autoplayMs);
            }
            function stopAutoplay() {
                if (timerId) { clearInterval(timerId); timerId = null; }
            }
            function restartAutoplay() {
                stopAutoplay();
                startAutoplay();
            }

            // Pause on hover (desktop)
            if (section) {
                section.addEventListener('mouseenter', stopAutoplay);
                section.addEventListener('mouseleave', startAutoplay);
            }

            // Pause when tab hidden
            document.addEventListener('visibilitychange', function () {
                if (document.hidden) stopAutoplay(); else startAutoplay();
            });

            updateActiveDot();
            startAutoplay();

            // Recalculate on resize
            window.addEventListener('resize', updateActiveDot);
        })();
        </script>
    </section>
    <?php
}

/** Single testimonial card */
function yk_render_testimonial_card($t) {
    ?>
    <figure class="h-full flex flex-col bg-white rounded-2xl border border-slate-200 p-7 shadow-sm hover:shadow-lg transition-shadow">
        <?php if (!empty($t['rating'])): ?>
            <div class="flex gap-0.5 mb-4 text-base" aria-label="<?= (int)$t['rating'] ?> out of 5 stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span style="color:<?= $i <= (int)$t['rating'] ? '#FF8A00' : '#cbd5e1' ?>;">★</span>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

        <blockquote class="flex-1 text-slate-600 leading-relaxed mb-6 text-[15px]">
            <?= nl2br(htmlspecialchars($t['testimonial'])) ?>
        </blockquote>

        <figcaption class="flex items-center gap-4 pt-5 border-t border-slate-100">
            <?php if (!empty($t['client_photo'])): ?>
                <img src="<?= htmlspecialchars($t['client_photo']) ?>"
                     alt="<?= htmlspecialchars($t['client_name']) ?>"
                     class="w-12 h-12 rounded-full object-cover shrink-0"
                     loading="lazy">
            <?php else: ?>
                <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg shrink-0"
                     style="background: linear-gradient(135deg, #0084FF 0%, #0066CC 100%);">
                    <?= htmlspecialchars(strtoupper(substr($t['client_name'], 0, 1))) ?>
                </div>
            <?php endif; ?>
            <div class="min-w-0">
                <div class="font-bold text-sm truncate" style="color:#2B3F5C;">
                    <?= htmlspecialchars($t['client_name']) ?>
                </div>
                <?php if (!empty($t['position']) || !empty($t['company_name'])): ?>
                    <div class="text-xs text-slate-500 truncate">
                        <?= htmlspecialchars(trim(($t['position'] ?? '') . (!empty($t['position']) && !empty($t['company_name']) ? ', ' : '') . ($t['company_name'] ?? ''))) ?>
                    </div>
                <?php endif; ?>
            </div>
        </figcaption>
    </figure>
    <?php
}