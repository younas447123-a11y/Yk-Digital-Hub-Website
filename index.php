<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/testimonials.php';
require_once __DIR__ . '/includes/portfolio-featured.php';
require_once __DIR__ . '/includes/services-featured.php';
require_once __DIR__ . '/includes/faq.php';
require_once ROOT_PATH . '/includes/country-flags.php';
// --- SEO ---
$site_name = getSiteSetting('site_name', 'YK Digital Hub');
$page_title = getSiteSetting('default_seo_title', 'YK Digital Hub — Web Design & Digital Marketing Agency');
$page_description = getSiteSetting('default_meta_description', 'We design high-performance websites and run data-driven marketing campaigns that help businesses grow.');
$page_canonical = BASE_URL . '/';

// --- Data: Services (top categories + featured services) ---
$parents = $db->query("SELECT id, name, slug, short_description, icon FROM service_categories WHERE parent_id IS NULL AND is_fixed = 1 AND status = 1 ORDER BY sort_order")->fetchAll(PDO::FETCH_ASSOC);

$featured_services = $db->query("
    SELECT s.id, s.title, s.slug, s.short_description, s.card_description, s.hero_image, s.icon,
           c.name AS child_name, c.slug AS child_slug, p.slug AS parent_slug
    FROM services s
    JOIN service_categories c ON s.category_id = c.id
    JOIN service_categories p ON c.parent_id = p.id
    WHERE s.status = 1 AND c.status = 1 AND p.status = 1 AND c.is_fixed = 0 AND p.is_fixed = 1
    ORDER BY s.featured DESC, s.sort_order ASC, s.id DESC
    LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

// --- Data: Portfolio (featured) ---
$featured_projects = $db->query("
    SELECT p.id, p.title, p.slug, p.card_description, p.short_description, p.hero_image,
           c.name AS child_name, c.slug AS child_slug,
           par.name AS parent_name, par.slug AS parent_slug
    FROM portfolio_projects p
    JOIN portfolio_categories c ON p.category_id = c.id
    JOIN portfolio_categories par ON c.parent_id = par.id
    WHERE p.status = 1 AND c.status = 1 AND par.status = 1 AND c.is_fixed = 0 AND par.is_fixed = 1
    ORDER BY p.featured DESC, p.sort_order ASC, p.id DESC
    LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

// --- Data: Blog ---
$recent_posts = $db->query("
    SELECT p.id, p.title, p.slug, p.excerpt, p.featured_image, p.featured_image_alt,
           p.published_at, p.reading_time, c.name AS category_name, c.slug AS category_slug,
           u.name AS author_name
    FROM blog_posts p
    LEFT JOIN blog_categories c ON p.category_id = c.id
    LEFT JOIN users u ON p.author_id = u.id
    WHERE p.status = 'published' AND (p.published_at IS NULL OR p.published_at <= NOW())
    ORDER BY p.published_at DESC, p.id DESC
    LIMIT 3
")->fetchAll(PDO::FETCH_ASSOC);

ob_start();
include ROOT_PATH . '/includes/header.php';
if (file_exists(ROOT_PATH . '/includes/navbar.php')) include ROOT_PATH . '/includes/navbar.php';
?>
<!-- ===================== HERO ===================== -->
<section class="relative min-h-[92vh] flex items-center overflow-hidden isolate">

        <!-- ==== 3 Rotating Backgrounds (zoom-in) ==== -->
    <div class="absolute inset-0 z-0" aria-hidden="true">
        <div class="absolute inset-0 bg-cover bg-center opacity-0 animate-zoom-in"
               style="background-image:url('<?= BASE_URL ?>/uploads/components/hero1.png'); animation-delay: 0s;"></div>
        <div class="absolute inset-0 bg-cover bg-center opacity-0 animate-zoom-in"
               style="background-image:url('<?= BASE_URL ?>/uploads/components/hero2.png'); animation-delay: 3s;"></div>
        <div class="absolute inset-0 bg-cover bg-center opacity-0 animate-zoom-in"
               style="background-image:url('<?= BASE_URL ?>/uploads/components/hero3.png'); animation-delay: 6s;"></div>
    </div>

    <!-- ==== Gradient Overlays ==== -->
    <div class="absolute inset-0 z-[1] bg-gradient-to-br from-slate-950/90 via-slate-950/70 to-slate-950/40" aria-hidden="true"></div>
    <div class="absolute inset-0 z-[1] bg-[radial-gradient(ellipse_at_top_right,rgba(37,99,235,0.4),transparent_60%)]" aria-hidden="true"></div>

    <!-- ==== Content ==== -->
    <div class="relative z-[2] max-w-7xl mx-auto px-6 py-24 w-full">

        <!-- Badge -->
        <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 border border-white/20 backdrop-blur-md rounded-full text-slate-200 text-xs font-semibold tracking-wide mb-6 opacity-0 animate-fade-up"
              style="animation-delay: 0.15s;">
            <span class="w-2 h-2 bg-cyan-400 rounded-full animate-dot-pulse" style="animation-duration: 2s;"></span>
            Web Design · Development · Digital Marketing
        </span>

        <!-- Headline -->
        <h1 class="text-white font-extrabold tracking-tight leading-[1.08] max-w-4xl mb-6 text-4xl sm:text-5xl lg:text-6xl xl:text-7xl" aria-label="We Design &amp; Build High-Performance Websites That Grow Your Business.">
            <span class="block hero-type-line" data-type-text="We Design &amp; Build">We Design &amp; Build</span>
            <span class="block hero-type-line">
                <span class="hero-type-segment bg-gradient-to-r from-blue-400 via-cyan-400 to-blue-400 bg-clip-text text-transparent" data-type-text="High-Performance">High-Performance</span><span class="hero-type-segment" data-type-text=" Websites"> Websites</span>
            </span>
            <span class="block hero-type-line" data-type-text="That Grow Your Business.">That Grow Your Business.</span>
        </h1>

        <script>
        (function () {
            var parts = document.querySelectorAll('.hero-type-line [data-type-text], .hero-type-line[data-type-text]');
            var partIndex = 0;
            var startDelay = 350;
            var typingSpeed = 48;

            function typePart(part) {
                var text = part.getAttribute('data-type-text');
                var characterIndex = 0;
                part.textContent = '';
                part.classList.add('is-typing');

                function writeCharacter() {
                    part.textContent += text.charAt(characterIndex++);
                    if (characterIndex < text.length) {
                        window.setTimeout(writeCharacter, typingSpeed);
                    } else {
                        part.classList.remove('is-typing');
                        partIndex++;
                        if (partIndex < parts.length) window.setTimeout(function () { typePart(parts[partIndex]); }, 120);
                    }
                }

                writeCharacter();
            }

            window.setTimeout(function () { typePart(parts[partIndex]); }, startDelay);
        }());
        </script>

        <!-- Lead -->
        <p class="text-slate-300 text-base sm:text-lg leading-relaxed max-w-xl mb-8 opacity-0 animate-fade-up"
           style="animation-delay: 1.05s;">
            YK Digital Hub is a full-service digital agency. We build conversion-focused websites
            and run data-driven marketing campaigns for businesses that want real, measurable growth.
        </p>

        <!-- Buttons -->
        <div class="flex flex-wrap gap-3 mb-10 opacity-0 animate-fade-up" style="animation-delay: 1.25s;">

            <!-- Primary CTA -->
            <a href="<?= BASE_URL ?>/contact.php"
               class="group inline-flex items-center gap-2 px-7 py-4 bg-gradient-to-br from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold text-[15px] rounded-full shadow-[0_10px_25px_rgba(37,99,235,0.35)] hover:shadow-[0_16px_34px_rgba(37,99,235,0.5)] hover:-translate-y-0.5 hover:scale-[1.03] transition-all duration-300 animate-pulse-glow">
                Start Your Project
                <i class="fas fa-arrow-right text-sm transition-transform duration-300 group-hover:translate-x-1"></i>
            </a>

            <!-- Secondary CTA -->
            <a href="<?= BASE_URL ?>/portfolio/web-design-development/"
                    class="inline-flex items-center gap-2 px-7 py-4 bg-gradient-to-r from-[#ffb000] to-[#ff8a00] hover:from-[#ffc12a] hover:to-[#ff6b00] text-white font-bold text-[15px] rounded-full shadow-[0_10px_25px_rgba(255,138,0,0.32)] hover:shadow-[0_16px_34px_rgba(255,107,0,0.45)] transition-all duration-300 hover:-translate-y-0.5 hover:scale-[1.03]">
                <i class="fas fa-play text-xs"></i>
                View Our Work
            </a>
        </div>

        <!-- Trust points -->
        <ul class="flex flex-wrap gap-x-7 gap-y-3 opacity-0 animate-fade-up" style="animation-delay: 1.45s;">
            <li class="inline-flex items-center gap-2.5 text-slate-300 text-sm font-medium">
                <span class="w-5 h-5 inline-flex items-center justify-center bg-cyan-400/15 text-cyan-400 rounded-full text-[10px]">
                    <i class="fas fa-check"></i>
                </span>
                Custom Web Development
            </li>
            <li class="inline-flex items-center gap-2.5 text-slate-300 text-sm font-medium">
                <span class="w-5 h-5 inline-flex items-center justify-center bg-cyan-400/15 text-cyan-400 rounded-full text-[10px]">
                    <i class="fas fa-check"></i>
                </span>
                WordPress &amp; Full-Stack
            </li>
            <li class="inline-flex items-center gap-2.5 text-slate-300 text-sm font-medium">
                <span class="w-5 h-5 inline-flex items-center justify-center bg-cyan-400/15 text-cyan-400 rounded-full text-[10px]">
                    <i class="fas fa-check"></i>
                </span>
                SEO &amp; Paid Marketing
            </li>
        </ul>
    </div>

    <!-- ==== Floating Glass Cards (desktop only) ==== -->
    <div class="hidden lg:flex absolute right-6 top-1/2 -translate-y-1/2 w-[min(400px,34vw)] flex-col gap-4 z-[2] opacity-0 animate-fade-up"
         style="animation-delay: 1.65s;" aria-hidden="true">

        <div class="bg-white/[0.07] border border-white/15 backdrop-blur-xl p-6 rounded-2xl shadow-2xl animate-float-y">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mb-3 bg-gradient-to-br from-blue-600/40 to-cyan-400/25 text-blue-300">
                <i class="fas fa-code"></i>
            </div>
            <h3 class="text-white text-base font-semibold mb-1.5">Development</h3>
            <p class="text-slate-300 text-sm leading-snug m-0">Fast, secure, scalable websites engineered to convert.</p>
        </div>

        <div class="bg-white/[0.07] border border-white/15 backdrop-blur-xl p-6 rounded-2xl shadow-2xl animate-float-y" style="animation-delay: 1.5s;">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mb-3 bg-gradient-to-br from-blue-600/40 to-cyan-400/25 text-blue-300">
                <i class="fas fa-chart-line"></i>
            </div>
            <h3 class="text-white text-base font-semibold mb-1.5">Marketing</h3>
            <p class="text-slate-300 text-sm leading-snug m-0">Strategy, SEO and paid growth that delivers real results.</p>
        </div>
    </div>

    <!-- ==== Dots indicator ==== -->
    <div class="absolute bottom-7 left-1/2 -translate-x-1/2 flex gap-2.5 z-[3]" aria-hidden="true">
        <span class="w-2.5 h-2.5 rounded-full bg-white/40 animate-dot-pulse" style="animation-delay: 0s;"></span>
        <span class="w-2.5 h-2.5 rounded-full bg-white/40 animate-dot-pulse" style="animation-delay: 7s;"></span>
        <span class="w-2.5 h-2.5 rounded-full bg-white/40 animate-dot-pulse" style="animation-delay: 14s;"></span>
    </div>
</section>

<?php renderCountryFlags(['title' => 'Trusted by clients worldwide']); ?>
<!-- ===================== WHAT WE DO ===================== -->
<section class="what-we-do-section relative py-20 lg:py-32 text-slate-900 overflow-hidden">

    <!-- Subtle brand glow backgrounds -->
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,rgba(37,99,235,0.18),transparent_55%)]" aria-hidden="true"></div>
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_right,rgba(34,211,238,0.12),transparent_55%)]" aria-hidden="true"></div>

    <!-- WIDER CONTAINER (max-w-[1600px] instead of max-w-7xl) -->
    <div class="relative w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">

        <!-- Section header -->
        <div class="text-center max-w-4xl mx-auto mb-16 lg:mb-20">
            <span class="inline-block px-4 py-1.5 text-xs font-bold tracking-[0.2em] uppercase text-blue-400 border border-blue-500/30 bg-blue-500/10 rounded-full mb-6">
                What We Do
            </span>
            <h2 class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-extrabold tracking-tight text-white mb-6 leading-[1.05]">
                Everything Your Business Needs to <span class="bg-gradient-to-r from-blue-400 to-cyan-400 bg-clip-text text-transparent">Grow Online</span>
            </h2>
            <p class="text-slate-400 text-lg sm:text-xl leading-relaxed max-w-3xl mx-auto">
                One agency. Two core divisions. Websites, redesigns and local SEO — built for
                <strong class="text-slate-200">healthcare providers</strong> and
                <strong class="text-slate-200">small businesses</strong> across
                the USA, UK, Canada, Australia, Germany &amp; Pakistan.
            </p>
        </div>

        <!-- Service grid — tighter gaps, larger cards -->
        <div class="what-we-do-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-5">

            <!-- 01 · Website Design -->
            <div class="group relative bg-slate-900/60 border border-slate-800 hover:border-blue-500/60 rounded-2xl p-7 lg:p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_20px_45px_-15px_rgba(37,99,235,0.35)]">
                <span class="inline-block px-3 py-1 text-[11px] font-bold tracking-wider uppercase rounded-md bg-blue-500/10 text-blue-400 border border-blue-500/20 mb-5">Design</span>
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-600/25 to-cyan-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 text-2xl mb-5">
                    <i class="fas fa-palette"></i>
                </div>
                <h3 class="text-white font-bold text-xl lg:text-2xl mb-3">Website Design</h3>
                <p class="text-slate-400 text-base leading-relaxed mb-6">
                    Custom, responsive, conversion-focused website design for small businesses, clinics and startups.
                </p>
                <a href="<?= BASE_URL ?>/services/web-design-development/" class="inline-flex items-center gap-2 text-blue-400 text-xs font-bold tracking-[0.15em] uppercase group-hover:gap-3 transition-all">
                    Explore <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>

            <!-- 02 · Website Development -->
            <div class="group relative bg-slate-900/60 border border-slate-800 hover:border-violet-500/60 rounded-2xl p-7 lg:p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_20px_45px_-15px_rgba(139,92,246,0.35)]">
                <span class="inline-block px-3 py-1 text-[11px] font-bold tracking-wider uppercase rounded-md bg-violet-500/10 text-violet-400 border border-violet-500/20 mb-5">Development</span>
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-violet-600/25 to-fuchsia-500/10 border border-violet-500/20 flex items-center justify-center text-violet-400 text-2xl mb-5">
                    <i class="fas fa-code"></i>
                </div>
                <h3 class="text-white font-bold text-xl lg:text-2xl mb-3">Website Development</h3>
                <p class="text-slate-400 text-base leading-relaxed mb-6">
                    Full-stack development — WordPress, Shopify, custom CMS, APIs and web applications.
                </p>
                <a href="<?= BASE_URL ?>/services/web-design-development/" class="inline-flex items-center gap-2 text-violet-400 text-xs font-bold tracking-[0.15em] uppercase group-hover:gap-3 transition-all">
                    Explore <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>

            <!-- 03 · Website Redesign -->
            <div class="group relative bg-slate-900/60 border border-slate-800 hover:border-amber-500/60 rounded-2xl p-7 lg:p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_20px_45px_-15px_rgba(245,158,11,0.35)]">
                <span class="inline-block px-3 py-1 text-[11px] font-bold tracking-wider uppercase rounded-md bg-amber-500/10 text-amber-400 border border-amber-500/20 mb-5">Redesign</span>
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-amber-600/25 to-orange-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 text-2xl mb-5">
                    <i class="fas fa-sync-alt"></i>
                </div>
                <h3 class="text-white font-bold text-xl lg:text-2xl mb-3">Website Redesign</h3>
                <p class="text-slate-400 text-base leading-relaxed mb-6">
                    Modernize outdated websites without losing rankings, traffic or hard-earned SEO authority.
                </p>
                <a href="<?= BASE_URL ?>/services/web-design-development/" class="inline-flex items-center gap-2 text-amber-400 text-xs font-bold tracking-[0.15em] uppercase group-hover:gap-3 transition-all">
                    Explore <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>

            <!-- 04 · Local SEO (POPULAR) -->
            <div class="group relative bg-slate-900/60 border border-slate-800 hover:border-emerald-500/60 rounded-2xl p-7 lg:p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_20px_45px_-15px_rgba(16,185,129,0.35)]">
                <div class="flex items-center gap-2 mb-5">
                    <span class="inline-block px-3 py-1 text-[11px] font-bold tracking-wider uppercase rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">SEO</span>
                    <span class="inline-block px-2.5 py-0.5 text-[10px] font-bold tracking-wider uppercase rounded bg-emerald-500 text-white">Popular</span>
                </div>
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-emerald-600/25 to-teal-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 text-2xl mb-5">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h3 class="text-white font-bold text-xl lg:text-2xl mb-3">Local SEO Services</h3>
                <p class="text-slate-400 text-base leading-relaxed mb-6">
                    Google Business Profile optimization, local citations and map-pack rankings for clinics and local businesses.
                </p>
                <a href="<?= BASE_URL ?>/services/digital-marketing/" class="inline-flex items-center gap-2 text-emerald-400 text-xs font-bold tracking-[0.15em] uppercase group-hover:gap-3 transition-all">
                    Explore <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>

            <!-- 05 · Healthcare Websites (CORE SPECIALIZATION — spans 2 columns) -->
            <div class="group relative bg-gradient-to-br from-red-950/60 to-slate-900/60 border border-red-500/30 hover:border-red-500/70 rounded-2xl p-8 lg:p-10 transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_20px_45px_-15px_rgba(239,68,68,0.4)] lg:col-span-2">
                <div class="flex items-center gap-2 mb-5">
                    <span class="inline-block px-3 py-1 text-[11px] font-bold tracking-wider uppercase rounded-md bg-red-500/15 text-red-400 border border-red-500/25">
                        <i class="fas fa-star text-[10px] mr-1"></i> Healthcare
                    </span>
                    <span class="inline-block px-2.5 py-0.5 text-[10px] font-bold tracking-wider uppercase rounded bg-red-500 text-white">Core Specialization</span>
                </div>
                <div class="flex items-start gap-5">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-red-600/30 to-orange-500/15 border border-red-500/25 flex items-center justify-center text-red-400 text-2xl shrink-0">
                        <i class="fas fa-heart-pulse"></i>
                    </div>
                    <div>
                        <h3 class="text-white font-bold text-xl lg:text-2xl mb-3">Healthcare Website Design &amp; SEO</h3>
                        <p class="text-slate-300 text-base leading-relaxed mb-6">
                            HIPAA-aware, GDPR-ready websites and SEO for clinics, dentists, doctors, hospitals and medical practices. Our deepest specialization — with patient-friendly UX, compliance language and healthcare-specific case studies.
                        </p>
                        <a href="<?= BASE_URL ?>/contact.php" class="inline-flex items-center gap-2 text-red-400 text-xs font-bold tracking-[0.15em] uppercase group-hover:gap-3 transition-all">
                            Book a Healthcare Consult <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 06 · Ecommerce -->
            <div class="group relative bg-slate-900/60 border border-slate-800 hover:border-cyan-500/60 rounded-2xl p-7 lg:p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_20px_45px_-15px_rgba(6,182,212,0.35)]">
                <span class="inline-block px-3 py-1 text-[11px] font-bold tracking-wider uppercase rounded-md bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 mb-5">Ecommerce</span>
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-cyan-600/25 to-sky-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 text-2xl mb-5">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3 class="text-white font-bold text-xl lg:text-2xl mb-3">Ecommerce Development</h3>
                <p class="text-slate-400 text-base leading-relaxed mb-6">
                    Fast, secure online stores built on Shopify, WooCommerce or custom stacks — engineered to convert.
                </p>
                <a href="<?= BASE_URL ?>/services/web-design-development/" class="inline-flex items-center gap-2 text-cyan-400 text-xs font-bold tracking-[0.15em] uppercase group-hover:gap-3 transition-all">
                    Explore <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>

            <!-- 07 · Digital Marketing -->
            <div class="group relative bg-slate-900/60 border border-slate-800 hover:border-pink-500/60 rounded-2xl p-7 lg:p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_20px_45px_-15px_rgba(236,72,153,0.35)]">
                <span class="inline-block px-3 py-1 text-[11px] font-bold tracking-wider uppercase rounded-md bg-pink-500/10 text-pink-400 border border-pink-500/20 mb-5">Marketing</span>
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-pink-600/25 to-rose-500/10 border border-pink-500/20 flex items-center justify-center text-pink-400 text-2xl mb-5">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <h3 class="text-white font-bold text-xl lg:text-2xl mb-3">Digital Marketing</h3>
                <p class="text-slate-400 text-base leading-relaxed mb-6">
                    SEO, content, paid ads and social — data-driven campaigns that turn visitors into patients and customers.
                </p>
                <a href="<?= BASE_URL ?>/services/digital-marketing/" class="inline-flex items-center gap-2 text-pink-400 text-xs font-bold tracking-[0.15em] uppercase group-hover:gap-3 transition-all">
                    Explore <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>

            <!-- 08 · WordPress & CMS -->
            <div class="group relative bg-slate-900/60 border border-slate-800 hover:border-slate-500/60 rounded-2xl p-7 lg:p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_20px_45px_-15px_rgba(148,163,184,0.35)]">
                <span class="inline-block px-3 py-1 text-[11px] font-bold tracking-wider uppercase rounded-md bg-slate-500/10 text-slate-300 border border-slate-500/20 mb-5">CMS</span>
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-slate-600/25 to-slate-500/10 border border-slate-500/20 flex items-center justify-center text-slate-300 text-2xl mb-5">
                    <i class="fab fa-wordpress"></i>
                </div>
                <h3 class="text-white font-bold text-xl lg:text-2xl mb-3">WordPress Development</h3>
                <p class="text-slate-400 text-base leading-relaxed mb-6">
                    Custom WordPress themes, plugins and headless CMS builds — easy to manage, fast to load.
                </p>
                <a href="<?= BASE_URL ?>/services/web-design-development/" class="inline-flex items-center gap-2 text-slate-300 text-xs font-bold tracking-[0.15em] uppercase group-hover:gap-3 transition-all">
                    Explore <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>

        </div>

        <!-- Bottom location strip -->
        <div class="mt-16 pt-10 border-t border-slate-800 text-center">
            <p class="text-slate-500 text-base">
                <span class="text-slate-400 font-semibold">Serving clinics, healthcare providers and businesses across</span>
                <span class="inline-flex flex-wrap justify-center gap-x-4 gap-y-2 mt-3 text-slate-300 font-semibold text-lg">
                    <span>USA</span>·<span>UK</span>·<span>Canada</span>·<span>Australia</span>·<span>Germany</span>·<span>Pakistan</span>
                </span>
            </p>
        </div>

    </div>
</section>

<?php renderFeaturedServices(); ?>
 <!-- ===================== HOW WE WORK ===================== -->
<section style="position: relative; padding: 6rem 0; background: linear-gradient(180deg, #FFFDF5 0%, #FFF8E1 40%, #FFEFC2 70%, #FFFDF5 100%); overflow: hidden;">

    <!-- Soft yellow radial glow -->
    <div style="position: absolute; inset: 0; background: radial-gradient(ellipse at top center, rgba(255, 200, 60, 0.25), transparent 60%); pointer-events: none;"></div>
    <div style="position: absolute; inset: 0; background: radial-gradient(ellipse at bottom right, rgba(255, 138, 0, 0.12), transparent 65%); pointer-events: none;"></div>

    <!-- Decorative dotted pattern -->
    <div style="position: absolute; inset: 0; background-image: radial-gradient(circle at 1px 1px, rgba(120, 80, 0, 0.08) 1px, transparent 0); background-size: 32px 32px; pointer-events: none; opacity: 0.5;"></div>

    <div style="max-width: 1400px; margin: 0 auto; padding: 0 1.5rem; position: relative;">

        <!-- Header -->
        <div style="text-align: center; max-width: 780px; margin: 0 auto 4rem auto;">
            <span style="display: inline-block; padding: 8px 20px; font-family: 'Space Grotesk', 'Inter', system-ui, sans-serif; font-size: 13px; font-weight: 800; letter-spacing: 0.24em; text-transform: uppercase; color: #92400E; background: rgba(255, 255, 255, 0.75); border: 1px solid rgba(146, 64, 14, 0.25); border-radius: 999px; margin-bottom: 22px; backdrop-filter: blur(8px); box-shadow: 0 4px 14px rgba(180, 120, 0, 0.08);">
                <i class="fas fa-route" style="font-size: 11px; margin-right: 6px; color: #D97706;"></i> Our Process
            </span>

            <h2 style="font-family: 'Space Grotesk', 'Inter', system-ui, sans-serif; font-size: 56px; line-height: 1.05; font-weight: 800; letter-spacing: -0.03em; color: #78350F; margin: 0 0 18px 0;">
                How We <span style="background: linear-gradient(135deg, #F59E0B 0%, #D97706 50%, #B45309 100%); -webkit-background-clip: text; background-clip: text; color: transparent; font-style: italic;">Work</span>
            </h2>

            <p style="font-family: 'Inter', system-ui, sans-serif; font-size: 19px; line-height: 1.7; color: #78350F; opacity: 0.75; margin: 0; max-width: 620px; margin-left: auto; margin-right: auto;">
                A clear, collaborative process from kickoff to launch — and long after.
            </p>
        </div>

        <!-- Process Grid -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 26px;" class="yk-hww-grid">

            <?php
            $steps = [
                [
                    'num'  => '01',
                    'icon' => 'fas fa-compass',
                    'title' => 'Discover',
                    'desc' => 'We dig into your business, audience, and goals to understand what success actually looks like for you.',
                ],
                [
                    'num'  => '02',
                    'icon' => 'fas fa-drafting-compass',
                    'title' => 'Plan',
                    'desc' => 'We map the scope, structure, and success metrics — so every decision has a clear purpose from day one.',
                ],
                [
                    'num'  => '03',
                    'icon' => 'fas fa-pencil-ruler',
                    'title' => 'Design',
                    'desc' => 'Wireframes and polished UI that reflect your brand, guide users, and quietly do the work of converting.',
                ],
                [
                    'num'  => '04',
                    'icon' => 'fas fa-code',
                    'title' => 'Build',
                    'desc' => 'Clean, standards-compliant development with content integration, testing, and quality checks at every step.',
                ],
                [
                    'num'  => '05',
                    'icon' => 'fas fa-rocket',
                    'title' => 'Launch',
                    'desc' => 'We deploy, set up tracking, and stay by your side through go-live so nothing slips through the cracks.',
                ],
                [
                    'num'  => '06',
                    'icon' => 'fas fa-chart-line',
                    'title' => 'Improve',
                    'desc' => 'Real data, real users, real results. We iterate, refine, and optimize for long-term growth.',
                ],
            ];

            foreach ($steps as $i => $step):
            ?>
                <div style="position: relative; padding: 36px 32px 34px 32px; background: rgba(255, 255, 255, 0.72); border: 1.5px solid rgba(180, 120, 0, 0.15); border-radius: 22px; backdrop-filter: blur(12px); transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 4px 18px rgba(180, 120, 0, 0.06);"
                     class="yk-hww-card"
                     onmouseover="this.style.background='rgba(255,255,255,0.92)'; this.style.transform='translateY(-8px)'; this.style.boxShadow='0 28px 60px -16px rgba(180, 120, 0, 0.28)'; this.style.borderColor='rgba(217, 119, 6, 0.4)';"
                     onmouseout="this.style.background='rgba(255,255,255,0.72)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 18px rgba(180, 120, 0, 0.06)'; this.style.borderColor='rgba(180, 120, 0, 0.15)';">

                    <!-- Step number (top right, faded) -->
                    <span style="position: absolute; top: 20px; right: 26px; font-family: 'Space Grotesk', system-ui, sans-serif; font-size: 54px; line-height: 1; font-weight: 800; letter-spacing: -0.05em; color: rgba(217, 119, 6, 0.14); pointer-events: none;">
                        <?= $step['num'] ?>
                    </span>

                    <!-- Icon badge -->
                    <div style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; border-radius: 18px; background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); color: #fff; font-size: 26px; margin-bottom: 22px; box-shadow: 0 12px 28px -8px rgba(217, 119, 6, 0.5); position: relative; z-index: 1;">
                        <i class="<?= $step['icon'] ?>"></i>
                    </div>

                    <!-- Title -->
                    <h3 style="font-family: 'Space Grotesk', system-ui, sans-serif; font-size: 24px; line-height: 1.25; font-weight: 800; letter-spacing: -0.02em; color: #78350F; margin: 0 0 12px 0; position: relative; z-index: 1;">
                        <?= htmlspecialchars($step['title']) ?>
                    </h3>

                    <!-- Description -->
                    <p style="font-family: 'Inter', system-ui, sans-serif; font-size: 15.5px; line-height: 1.75; color: #92400E; opacity: 0.85; margin: 0; position: relative; z-index: 1;">
                        <?= htmlspecialchars($step['desc']) ?>
                    </p>

                    <!-- Bottom accent line -->
                    <div style="position: absolute; bottom: 0; left: 32px; right: 32px; height: 3px; background: linear-gradient(90deg, transparent, #F59E0B, transparent); border-radius: 3px 3px 0 0; opacity: 0; transition: opacity 0.3s ease;" class="yk-hww-accent"></div>
                </div>
            <?php endforeach; ?>

        </div>

        <!-- Bottom CTA row -->
        <div style="text-align: center; margin-top: 4rem;">
            <p style="font-family: 'Space Grotesk', system-ui, sans-serif; font-size: 18px; font-weight: 600; color: #78350F; margin: 0 0 22px 0; letter-spacing: -0.01em;">
                Ready to start your project?
            </p>
            <div style="display: flex; flex-wrap: wrap; gap: 14px; justify-content: center;">
                <a href="<?= BASE_URL ?>/contact.php"
                   style="display: inline-flex; align-items: center; gap: 10px; padding: 16px 34px; background: linear-gradient(90deg, #F59E0B 0%, #D97706 100%); color: #fff; font-family: 'Space Grotesk', system-ui, sans-serif; font-weight: 800; font-size: 16px; border-radius: 999px; text-decoration: none; box-shadow: 0 12px 28px -8px rgba(217, 119, 6, 0.5); transition: transform 0.2s ease;"
                   onmouseover="this.style.transform='translateY(-3px)';"
                   onmouseout="this.style.transform='translateY(0)';">
                    Get a Free Consultation <i class="fas fa-arrow-right" style="font-size: 13px;"></i>
                </a>
                <a href="<?= BASE_URL ?>/portfolio/web-design-development/"
                   style="display: inline-flex; align-items: center; gap: 10px; padding: 16px 34px; background: rgba(255, 255, 255, 0.75); color: #78350F; font-family: 'Space Grotesk', system-ui, sans-serif; font-weight: 800; font-size: 16px; border-radius: 999px; text-decoration: none; border: 1.5px solid rgba(120, 53, 15, 0.2); backdrop-filter: blur(8px);"
                   onmouseover="this.style.background='rgba(255,255,255,0.95)';"
                   onmouseout="this.style.background='rgba(255,255,255,0.75)';">
                    View Our Work
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Load Space Grotesk font -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<!-- Responsive -->
<style>
@media (max-width: 1024px) {
    .yk-hww-grid { grid-template-columns: repeat(2, 1fr) !important; }
}
@media (max-width: 640px) {
    .yk-hww-grid { grid-template-columns: 1fr !important; }
    .yk-hww-card { padding: 30px 24px !important; }
    section h2 { font-size: 36px !important; }
}
.yk-hww-card:hover .yk-hww-accent { opacity: 1; }
</style>

<!-- ===================== TESTIMONIALS ===================== -->
<?php
renderTestimonials([
    'carousel' => true,
    'featured' => false,       // show ALL active, not just featured
    'limit'    => 30,
    'autoplay' => 7000,        // change to 0 to disable
    'title'    => 'What Our Clients Say About Us',
    'subtitle' => 'Feedback from clinics, healthcare providers and businesses we have worked with.',
]);
?>
<?php
renderFAQ([
    'title'    => 'Frequently Asked Questions',
    'subtitle' => "Can't find your answer? WhatsApp us anytime — we respond fast.",
    'items'    => [
        [
            'question' => 'How much does a website cost?',
            'answer'   => 'Every project is different. A simple business website typically starts around a few hundred dollars, while custom web applications and e-commerce stores cost more. We provide a fixed quote after understanding your goals — no hidden fees.',
        ],
        [
            'question' => 'How long does it take to build a website?',
            'answer'   => 'Most websites take 2 to 6 weeks from kickoff to launch, depending on the number of pages, features, and how quickly content is ready. Larger custom builds may take longer.',
        ],
        [
            'question' => 'How long does SEO take to show results?',
            'answer'   => 'SEO is a long-term investment. You will usually see initial improvements within 3 months, with significant ranking and traffic gains in 6 to 12 months. Results depend on competition, industry and website history.',
        ],
        [
            'question' => 'Do you design HIPAA-aware and GDPR-compliant websites?',
            'answer'   => 'Yes. We build healthcare websites with HIPAA-aware practices for the USA and GDPR-compliant practices for the UK and EU — including secure forms, privacy policies and consent-friendly UX.',
        ],
        [
            'question' => 'Do you provide support after the project is complete?',
            'answer'   => 'Yes. We offer ongoing maintenance, updates and support plans so your website stays fast, secure and up to date after launch.',
        ],
        [
            'question' => 'Can you work with clients outside your country?',
            'answer'   => 'Absolutely. We work with clients across the USA, UK, Canada, Australia, Germany and Pakistan. All communication is done over email, WhatsApp and video calls.',
        ],
        [
            'question' => 'What payment methods do you accept?',
            'answer'   => 'We accept bank transfers, credit/debit cards, and popular online payment methods. For international clients, we use secure payment processors. A deposit is required to start, with the balance due at completion.',
        ],
        [
            'question' => 'Do you provide social media management for small businesses?',
            'answer'   => 'Yes. We offer social media strategy, content creation, posting and community management for small businesses, clinics and healthcare providers — on Instagram, Facebook, LinkedIn and more.',
        ],
    ],
]);
?>

<!-- ===================== BLOG ===================== -->
<?php if (!empty($recent_posts)): ?>
<section class="home-blog">
    <div class="container">
        <div class="section-head">
            <h2>Insights &amp; Articles</h2>
            <p>Practical advice on websites, SEO and digital growth.</p>
        </div>
        <div class="blog-grid">
            <?php foreach ($recent_posts as $p): ?>
                <article class="blog-card">
                    <a href="<?= BASE_URL ?>/blog/<?= htmlspecialchars($p['slug']) ?>/" class="blog-card-image">
                        <?php if (!empty($p['featured_image'])): ?>
                            <img src="<?= htmlspecialchars($p['featured_image']) ?>" alt="<?= htmlspecialchars($p['featured_image_alt'] ?: $p['title']) ?>" loading="lazy">
                        <?php else: ?><div class="blog-card-placeholder"><i class="fas fa-newspaper"></i></div><?php endif; ?>
                    </a>
                    <div class="blog-card-body">
                        <?php if (!empty($p['category_name'])): ?>
                            <span class="blog-cat"><?= htmlspecialchars($p['category_name']) ?></span>
                        <?php endif; ?>
                        <h3><a href="<?= BASE_URL ?>/blog/<?= htmlspecialchars($p['slug']) ?>/"><?= htmlspecialchars($p['title']) ?></a></h3>
                        <?php if (!empty($p['excerpt'])): ?><p><?= htmlspecialchars(mb_strimwidth($p['excerpt'], 0, 140, '…')) ?></p><?php endif; ?>
                        <div class="blog-meta">
                            <?php if (!empty($p['published_at'])): ?><span><i class="fas fa-calendar"></i> <?= date('M j, Y', strtotime($p['published_at'])) ?></span><?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===================== FINAL CTA ===================== -->
<?php
$home_whatsapp_raw = getSiteSetting('whatsapp', '923069776937');
$home_whatsapp_num = preg_replace('/[^0-9]/', '', $home_whatsapp_raw);
$home_whatsapp_url = 'https://wa.me/' . ($home_whatsapp_num ?: '923069776937');
?>
<section class="home-cta">
    <div class="container home-transform-grid">
        <div class="home-transform-visual">
            <img src="<?= BASE_URL ?>/uploads/components/ready%20to%20transform.png" alt="Ready to transform your business" loading="lazy">
        </div>
        <div class="home-cta-box home-transform-copy">
            <h2>Ready to Transform Your Business?</h2>
            <p>Tell us about your goals and let's build the right website or marketing strategy to move your business forward.</p>
            <div class="home-cta-actions">
                <a href="<?= htmlspecialchars($home_whatsapp_url) ?>" target="_blank" rel="noopener noreferrer" class="btn home-whatsapp-cta">
                    <i class="fab fa-whatsapp" aria-hidden="true"></i> Start a Conversation
                </a>
                <a href="<?= BASE_URL ?>/services/" class="btn home-services-cta">
                    Explore Services <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<?php
include ROOT_PATH . '/includes/footer.php';
ob_end_flush();