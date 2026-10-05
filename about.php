<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

// ============ BRAND COLORS ============
$BLUE      = '#0084FF';
$BLUE_DARK = '#0066CC';
$YELLOW    = '#FFC107';
$YELLOW_DK = '#F59E0B';
$BLACK     = '#1F2937';
$SOFT_BLK  = '#2B3F5C';

// ============ SEO ============
$page_title       = 'About YK Digital Hub | Web Development & Digital Marketing Agency';
$page_description = 'Learn about YK Digital Hub — a digital agency providing website development, WordPress, SEO, digital marketing and business-focused digital solutions.';
$page_canonical   = BASE_URL . '/about';
$og_title         = $page_title;
$og_description   = $page_description;
$og_image         = BASE_URL . '/assets/images/og-about.jpg';
$robots           = 'index, follow';

// ============ LOAD TESTIMONIALS (dynamic from DB) ============
$testimonials = [];
try {
    $stmt = $db->query("
        SELECT t.*, s.title AS service_title
        FROM testimonials t
        LEFT JOIN services s ON t.service_id = s.id
        WHERE t.status = 1
        ORDER BY t.featured DESC, t.sort_order ASC, t.id DESC
        LIMIT 3
    ");
    $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { /* table may not exist yet */ }

// WhatsApp
$wa_raw = getSiteSetting('whatsapp', '923069776937');
$wa_num = preg_replace('/[^0-9]/', '', $wa_raw);
$wa_url = 'https://wa.me/' . ($wa_num ?: '923069776937');

// ============ LOCAL HELPER: render stars ============
if (!function_exists('aboutStars')) {
    function aboutStars($n) {
        $n = (int)$n;
        $out = '';
        for ($i = 1; $i <= 5; $i++) {
            $out .= '<span style="color:' . ($i <= $n ? '#FFC107' : '#cbd5e1') . ';">★</span>';
        }
        return $out;
    }
}

ob_start();
include ROOT_PATH . '/includes/header.php';
if (file_exists(ROOT_PATH . '/includes/navbar.php')) include ROOT_PATH . '/includes/navbar.php';
?>

<!-- Load fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
@keyframes ykFadeUp { from { opacity:0; transform:translateY(30px);} to { opacity:1; transform:translateY(0);} }
@keyframes ykFloat { 0%,100% { transform:translateY(0);} 50% { transform:translateY(-12px);} }
@keyframes ykPulse { 0%,100% { opacity:.5; transform:scale(1);} 50% { opacity:.9; transform:scale(1.15);} }
.yk-au-fade { opacity: 0; animation: ykFadeUp .9s cubic-bezier(.22,1,.36,1) forwards; }
.yk-au-card { transition: all .35s cubic-bezier(.4,0,.2,1); }
.yk-au-card:hover { transform: translateY(-8px); }
@media (max-width: 1024px) {
    .yk-au-2col { grid-template-columns: 1fr !important; }
    .yk-au-3col { grid-template-columns: repeat(2,1fr) !important; }
    .yk-au-4col { grid-template-columns: repeat(2,1fr) !important; }
    .yk-au-tl-card { margin-left: 60px !important; margin-right: 0 !important; text-align: left !important; }
    .yk-au-tl-line { left: 24px !important; }
    .yk-au-tl-dot { left: 18px !important; }
}
@media (max-width: 640px) {
    .yk-au-3col, .yk-au-4col, .yk-au-stats { grid-template-columns: 1fr !important; }
    h1 { font-size: 34px !important; }
    h2 { font-size: 28px !important; }
}
@media (prefers-reduced-motion: reduce) {
    .yk-au-fade, [style*="animation"] { animation: none !important; opacity: 1 !important; transform: none !important; }
}
</style>

<!-- ============================================================
     1. HERO
============================================================ -->
<section style="position:relative; padding: 4rem 0 5rem; background: linear-gradient(180deg, #F0F9FF 0%, #ffffff 60%); overflow:hidden;">
    <div style="position:absolute; inset:0; background-image: radial-gradient(circle at 1px 1px, rgba(0,132,255,.08) 1px, transparent 0); background-size: 30px 30px; pointer-events:none;"></div>
    <div style="position:absolute; top:-120px; right:-120px; width:520px; height:520px; background: radial-gradient(circle, rgba(255,193,7,.18), transparent 65%); border-radius:50%; pointer-events:none;"></div>

    <div style="max-width:1400px; margin:0 auto; padding:0 1.5rem; position:relative;">
        <div class="yk-au-2col" style="display:grid; grid-template-columns: 1.15fr 1fr; gap:4rem; align-items:center;">

            <!-- LEFT -->
            <div class="yk-au-fade" style="animation-delay:.1s;">
                <span style="display:inline-flex; align-items:center; gap:8px; padding:8px 18px; font-family:'Space Grotesk',sans-serif; font-size:12px; font-weight:800; letter-spacing:.24em; text-transform:uppercase; color:<?= $BLUE_DARK ?>; background:#E0F2FE; border:1px solid #BAE6FD; border-radius:999px; margin-bottom:26px;">
                    <span style="width:8px; height:8px; background:<?= $YELLOW ?>; border-radius:50%;"></span>
                    About YK Digital Hub
                </span>

                <h1 style="font-family:'Space Grotesk',sans-serif; font-size:58px; line-height:1.05; font-weight:800; letter-spacing:-.03em; color:<?= $SOFT_BLK ?>; margin:0 0 24px 0;">
                    Building Digital<br>
                    Experiences That<br>
                    Help Businesses <span style="position:relative; display:inline-block;">
                        <span style="background: linear-gradient(135deg, <?= $BLUE ?> 0%, <?= $BLUE_DARK ?> 100%); -webkit-background-clip:text; background-clip:text; color:transparent;">Move Forward</span>
                        <span style="position:absolute; left:0; right:0; bottom:6px; height:8px; background:<?= $YELLOW ?>; opacity:.35; z-index:-1; border-radius:4px;"></span>
                    </span>
                </h1>

                <p style="font-family:'Inter',sans-serif; font-size:19px; line-height:1.75; color:#475569; margin:0 0 36px 0; max-width:560px;">
                    YK Digital Hub combines <strong style="color:<?= $SOFT_BLK ?>;">design, development, SEO, digital marketing</strong> and strategy to create meaningful digital experiences for businesses that want to grow online.
                </p>

                <div style="display:flex; flex-wrap:wrap; gap:14px;">
                    <a href="<?= BASE_URL ?>/contact.php"
                       style="display:inline-flex; align-items:center; gap:10px; padding:16px 34px; background: linear-gradient(90deg, <?= $BLUE ?> 0%, <?= $BLUE_DARK ?> 100%); color:#fff; font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:16px; border-radius:999px; text-decoration:none; box-shadow: 0 12px 30px -8px rgba(0,132,255,.5); transition: transform .2s;"
                       onmouseover="this.style.transform='translateY(-3px)';" onmouseout="this.style.transform='translateY(0)';">
                        Let's Work Together <i class="fas fa-arrow-right" style="font-size:13px;"></i>
                    </a>
                    <a href="<?= BASE_URL ?>/services/"
                       style="display:inline-flex; align-items:center; gap:10px; padding:16px 34px; background:#fff; color:<?= $SOFT_BLK ?>; font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:16px; border-radius:999px; text-decoration:none; border:2px solid #e2e8f0; transition: all .2s;"
                       onmouseover="this.style.borderColor='<?= $BLUE ?>';" onmouseout="this.style.borderColor='#e2e8f0';">
                        Explore Our Services
                    </a>
                </div>
            </div>

            <!-- RIGHT — floating UI cards -->
            <div style="position:relative; height:480px;" class="yk-au-fade" aria-hidden="true">
                <!-- Backdrop -->
                <div style="position:absolute; inset:0; background: linear-gradient(135deg, <?= $BLUE ?> 0%, <?= $BLUE_DARK ?> 100%); border-radius:28px; opacity:.08;"></div>

                <!-- Card 1 — Analytics -->
                <div style="position:absolute; top:20px; left:0; right:60px; background:#fff; border-radius:18px; padding:22px; box-shadow: 0 20px 50px rgba(43,63,92,.12); animation: ykFloat 6s ease-in-out infinite;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                        <span style="font-family:'Inter',sans-serif; font-size:12px; font-weight:700; color:#94a3b8; letter-spacing:.08em; text-transform:uppercase;">Growth</span>
                        <span style="padding:4px 10px; background:#DCFCE7; color:#166534; font-family:'Inter',sans-serif; font-size:11px; font-weight:700; border-radius:999px;">+32%</span>
                    </div>
                    <div style="display:flex; align-items:flex-end; gap:6px; height:60px;">
                        <?php foreach ([30,45,38,55,48,68,80] as $h): ?>
                            <div style="flex:1; height:<?= $h ?>%; background: linear-gradient(180deg, <?= $BLUE ?>, <?= $BLUE_DARK ?>); border-radius:4px;"></div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Card 2 — Site Score -->
                <div style="position:absolute; top:180px; right:0; width:220px; background:#fff; border-radius:18px; padding:22px; box-shadow: 0 20px 50px rgba(43,63,92,.12); animation: ykFloat 6s ease-in-out infinite 1.5s;">
                    <div style="font-family:'Inter',sans-serif; font-size:12px; font-weight:700; color:#94a3b8; letter-spacing:.08em; text-transform:uppercase; margin-bottom:10px;">Site Health</div>
                    <div style="position:relative; width:90px; height:90px; margin:0 auto 10px;">
                        <svg viewBox="0 0 36 36" style="width:100%; height:100%; transform: rotate(-90deg);">
                            <circle cx="18" cy="18" r="15" fill="none" stroke="#e2e8f0" stroke-width="3"/>
                            <circle cx="18" cy="18" r="15" fill="none" stroke="<?= $YELLOW ?>" stroke-width="3" stroke-dasharray="85 100" stroke-linecap="round"/>
                        </svg>
                        <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; font-family:'Space Grotesk',sans-serif; font-size:22px; font-weight:800; color:<?= $SOFT_BLK ?>;">A+</div>
                    </div>
                    <div style="text-align:center; font-family:'Inter',sans-serif; font-size:12px; color:#64748b;">Performance Score</div>
                </div>

                <!-- Card 3 — SEO -->
                <div style="position:absolute; bottom:20px; left:30px; width:250px; background:#fff; border-radius:18px; padding:20px; box-shadow: 0 20px 50px rgba(43,63,92,.12); animation: ykFloat 6s ease-in-out infinite 3s;">
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
                        <div style="width:42px; height:42px; border-radius:12px; background: linear-gradient(135deg,<?= $BLUE ?>,<?= $BLUE_DARK ?>); display:flex; align-items:center; justify-content:center; color:#fff; font-size:18px;">
                            <i class="fas fa-search"></i>
                        </div>
                        <div>
                            <div style="font-family:'Space Grotesk',sans-serif; font-weight:800; font-size:15px; color:<?= $SOFT_BLK ?>;">SEO Ready</div>
                            <div style="font-family:'Inter',sans-serif; font-size:12px; color:#94a3b8;">Rank 1-10</div>
                        </div>
                    </div>
                    <div style="font-family:'Inter',sans-serif; font-size:13px; color:#475569; line-height:1.5;">Optimized for search engines and AI visibility.</div>
                </div>

                <!-- Decorative pulse -->
                <div style="position:absolute; top:120px; left:-30px; width:60px; height:60px; background:<?= $YELLOW ?>; border-radius:50%; opacity:.35; animation: ykPulse 3s ease-in-out infinite;"></div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     2. WHO WE ARE
============================================================ -->
<section id="our-story" style="padding: 6rem 0; background:#ffffff;">
    <div style="max-width:1400px; margin:0 auto; padding:0 1.5rem;">
        <div class="yk-au-2col" style="display:grid; grid-template-columns:1fr 1.1fr; gap:5rem; align-items:center;">

            <!-- LEFT — text -->
            <div>
                <span style="font-family:'Space Grotesk',sans-serif; font-size:13px; font-weight:800; letter-spacing:.22em; text-transform:uppercase; color:<?= $YELLOW_DK ?>; display:inline-flex; align-items:center; gap:8px; margin-bottom:20px;">
                    <span style="width:32px; height:2px; background:<?= $YELLOW ?>;"></span> Who We Are
                </span>
                <h2 style="font-family:'Space Grotesk',sans-serif; font-size:46px; line-height:1.1; font-weight:800; letter-spacing:-.025em; color:<?= $SOFT_BLK ?>; margin:0 0 24px 0;">
                    We Build More<br>Than <span style="color:<?= $BLUE ?>;">Websites</span>
                </h2>
                <p style="font-family:'Inter',sans-serif; font-size:17px; line-height:1.8; color:#475569; margin:0 0 20px 0;">
                    A website is only one part of a business's digital presence. What actually drives growth is a connected system — one that helps people <strong style="color:<?= $SOFT_BLK ?>;">discover, trust, understand, contact, and convert</strong>.
                </p>
                <p style="font-family:'Inter',sans-serif; font-size:17px; line-height:1.8; color:#475569; margin:0;">
                    YK Digital Hub works across website experience, technical development, SEO, local visibility, digital marketing, and conversion-focused design — so every piece works together.
                </p>
            </div>

            <!-- RIGHT — 4 feature cards -->
            <div style="display:grid; gap:14px;">
                <?php
                $whoweare = [
                    ['num'=>'01', 'title'=>'Strategy', 'desc'=>'Understanding the business and its audience before building.', 'icon'=>'fas fa-compass', 'accent'=>$BLUE],
                    ['num'=>'02', 'title'=>'Experience', 'desc'=>'Creating clear, modern, user-friendly digital experiences.', 'icon'=>'fas fa-wand-magic-sparkles', 'accent'=>$YELLOW_DK],
                    ['num'=>'03', 'title'=>'Visibility', 'desc'=>'Helping businesses become easier to discover through SEO and marketing.', 'icon'=>'fas fa-search', 'accent'=>$BLUE],
                    ['num'=>'04', 'title'=>'Growth', 'desc'=>'Building digital systems designed around real business goals.', 'icon'=>'fas fa-chart-line', 'accent'=>$YELLOW_DK],
                ];
                foreach ($whoweare as $w):
                ?>
                <div class="yk-au-card" style="display:flex; gap:20px; padding:24px; background:#fff; border:1.5px solid #e2e8f0; border-radius:16px;" onmouseover="this.style.borderColor='<?= $w['accent'] ?>'; this.style.boxShadow='0 20px 45px -15px <?= $w['accent'] ?>55';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                    <div style="flex-shrink:0; width:52px; height:52px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:20px; color:#fff; background: linear-gradient(135deg, <?= $w['accent'] ?>, <?= $w['accent'] ?>cc);">
                        <i class="<?= $w['icon'] ?>"></i>
                    </div>
                    <div>
                        <div style="font-family:'Space Grotesk',sans-serif; font-size:11px; font-weight:800; color:#94a3b8; letter-spacing:.2em; margin-bottom:4px;"><?= $w['num'] ?></div>
                        <h3 style="font-family:'Space Grotesk',sans-serif; font-size:19px; font-weight:800; color:<?= $SOFT_BLK ?>; margin:0 0 6px 0;"><?= $w['title'] ?></h3>
                        <p style="font-family:'Inter',sans-serif; font-size:14.5px; line-height:1.6; color:#64748b; margin:0;"><?= $w['desc'] ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     3. FOUNDER STORY + PHOTO
============================================================ -->
<section style="padding: 6rem 0; background: linear-gradient(180deg, #F8FAFC 0%, #ffffff 100%);">
    <div style="max-width:1400px; margin:0 auto; padding:0 1.5rem;">
        <div class="yk-au-2col" style="display:grid; grid-template-columns: 1fr 1.15fr; gap:5rem; align-items:center;">

            <!-- LEFT — photo -->
            <div style="position:relative;">
                <!-- Frame -->
                <div style="position:relative; border-radius:24px; overflow:hidden; box-shadow: 0 30px 70px -20px rgba(43,63,92,.28); border:1px solid #e2e8f0; background:#f1f5f9;">
                    <img src="<?= BASE_URL ?>/uploads/components/founder.png"
                         alt="Younas Khan — Founder of YK Digital Hub"
                         width="800" height="1000"
                         style="width:100%; display:block; aspect-ratio:4/5; object-fit:cover;"
                         onerror="this.style.display='none'; this.parentElement.style.background='linear-gradient(135deg,#E0F2FE,#FFF9E6)'; this.parentElement.insertAdjacentHTML('beforeend','<div style=\'aspect-ratio:4/5; display:flex; flex-direction:column; align-items:center; justify-content:center; color:#94a3b8; font-family:Inter,sans-serif;\'><i class=\'fas fa-user-tie\' style=\'font-size:80px; margin-bottom:16px; opacity:.5;\'></i><div style=\'font-size:14px; font-weight:600;\'>Add founder.png to</div><code style=\'font-size:12px; color:#64748b; margin-top:4px;\'>/uploads/components/founder.png</code></div>\');">

                    <!-- Yellow accent corner -->
                    <div style="position:absolute; bottom:0; right:0; width:80px; height:80px; background:<?= $YELLOW ?>; opacity:.9; border-radius:24px 0 0 0;"></div>
                    <div style="position:absolute; bottom:20px; right:20px; color:<?= $SOFT_BLK ?>; font-size:26px;">
                        <i class="fas fa-quote-right"></i>
                    </div>
                </div>

                <!-- Floating info card -->
                <div style="position:absolute; bottom:-24px; left:-24px; background:#fff; border-radius:16px; padding:18px 24px; box-shadow: 0 20px 45px -10px rgba(43,63,92,.2); border:1px solid #e2e8f0; display:flex; align-items:center; gap:14px;" class="yk-au-card">
                    <div style="width:48px; height:48px; border-radius:50%; background: linear-gradient(135deg,<?= $BLUE ?>,<?= $BLUE_DARK ?>); color:#fff; display:flex; align-items:center; justify-content:center; font-size:18px;">
                        <i class="fas fa-code"></i>
                    </div>
                    <div>
                        <div style="font-family:'Space Grotesk',sans-serif; font-size:15px; font-weight:800; color:<?= $SOFT_BLK ?>; line-height:1.2;">Founder & Web Developer</div>
                        <div style="font-family:'Inter',sans-serif; font-size:13px; color:#64748b; margin-top:2px;">YK Digital Hub</div>
                    </div>
                </div>
            </div>

            <!-- RIGHT — story -->
            <div>
                <span style="font-family:'Space Grotesk',sans-serif; font-size:13px; font-weight:800; letter-spacing:.22em; text-transform:uppercase; color:<?= $YELLOW_DK ?>; display:inline-flex; align-items:center; gap:8px; margin-bottom:20px;">
                    <span style="width:32px; height:2px; background:<?= $YELLOW ?>;"></span> The Person Behind YK Digital Hub
                </span>
                <h2 style="font-family:'Space Grotesk',sans-serif; font-size:44px; line-height:1.1; font-weight:800; letter-spacing:-.025em; color:<?= $SOFT_BLK ?>; margin:0 0 28px 0;">
                    Built With Curiosity.<br>Driven By <span style="color:<?= $BLUE ?>;">Digital Growth</span>.
                </h2>

                <p style="font-family:'Inter',sans-serif; font-size:17px; line-height:1.85; color:#475569; margin:0 0 20px 0;">
                    YK Digital Hub started with a simple curiosity — understanding how websites actually work and how they can be built to genuinely help businesses.
                </p>
                <p style="font-family:'Inter',sans-serif; font-size:17px; line-height:1.85; color:#475569; margin:0 0 20px 0;">
                    What began as learning web technologies grew into building real websites, solving real problems, and understanding what makes a digital presence effective — not just attractive.
                </p>
                <p style="font-family:'Inter',sans-serif; font-size:17px; line-height:1.85; color:#475569; margin:0 0 32px 0;">
                    Today, YK Digital Hub brings together modern web development, SEO, and marketing to help businesses establish stronger, more meaningful digital presences.
                </p>

                <!-- Signature line -->
                <div style="display:flex; align-items:center; gap:16px; padding-top:24px; border-top:1px solid #e2e8f0;">
                    <div style="width:52px; height:52px; border-radius:50%; background:<?= $YELLOW ?>; color:<?= $SOFT_BLK ?>; display:flex; align-items:center; justify-content:center; font-family:'Space Grotesk',sans-serif; font-weight:800; font-size:20px;">
                        YK
                    </div>
                    <div>
                        <div style="font-family:'Space Grotesk',sans-serif; font-size:16px; font-weight:800; color:<?= $SOFT_BLK ?>;">Younas Khan</div>
                        <div style="font-family:'Inter',sans-serif; font-size:13px; color:#64748b;">Founder & Lead Developer</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     4. WHAT WE DO
============================================================ -->
<section style="padding: 6rem 0; background:#ffffff;">
    <div style="max-width:1400px; margin:0 auto; padding:0 1.5rem;">

        <div style="text-align:center; max-width:780px; margin:0 auto 4rem auto;">
            <span style="font-family:'Space Grotesk',sans-serif; font-size:13px; font-weight:800; letter-spacing:.22em; text-transform:uppercase; color:<?= $YELLOW_DK ?>; display:inline-flex; align-items:center; gap:8px; margin-bottom:20px;">
                <span style="width:32px; height:2px; background:<?= $YELLOW ?>;"></span> What We Do
            </span>
            <h2 style="font-family:'Space Grotesk',sans-serif; font-size:46px; line-height:1.1; font-weight:800; letter-spacing:-.025em; color:<?= $SOFT_BLK ?>; margin:0 0 16px 0;">
                From Digital Ideas to <span style="color:<?= $BLUE ?>;">Real Solutions</span>
            </h2>
            <p style="font-family:'Inter',sans-serif; font-size:18px; line-height:1.7; color:#64748b; margin:0;">
                A complete set of capabilities designed to help businesses build, grow, and sustain a strong digital presence.
            </p>
        </div>

        <div class="yk-au-3col" style="display:grid; grid-template-columns: repeat(3, 1fr); gap:24px;">
            <?php
            $caps = [
                ['Web Design & Development', 'Modern, responsive, conversion-focused websites built to perform.', 'fas fa-laptop-code', $BLUE],
                ['WordPress Development', 'Flexible WordPress websites for easy, powerful content management.', 'fab fa-wordpress', $YELLOW_DK],
                ['SEO & Local SEO', 'Improving search visibility and local presence for real discovery.', 'fas fa-search', $BLUE],
                ['Digital Marketing', 'Strategies designed to attract, engage, and convert the right audience.', 'fas fa-bullhorn', $YELLOW_DK],
                ['UI/UX & Branding', 'Consistent, memorable, user-first digital experiences.', 'fas fa-palette', $BLUE],
                ['Custom Digital Solutions', 'Tailored functionality built around specific business needs.', 'fas fa-cogs', $YELLOW_DK],
            ];
            foreach ($caps as $i => $c):
            ?>
            <div class="yk-au-card" style="padding:32px 28px; background:#fff; border:1.5px solid #e2e8f0; border-radius:20px;" onmouseover="this.style.borderColor='<?= $c[3] ?>'; this.style.boxShadow='0 24px 50px -15px <?= $c[3] ?>55';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:22px;">
                    <div style="width:60px; height:60px; border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:24px; color:#fff; background: linear-gradient(135deg, <?= $c[3] ?>, <?= $c[3] ?>cc);">
                        <i class="<?= $c[2] ?>"></i>
                    </div>
                    <span style="font-family:'Space Grotesk',sans-serif; font-size:36px; font-weight:800; color:#f1f5f9; letter-spacing:-.04em;">0<?= $i + 1 ?></span>
                </div>
                <h3 style="font-family:'Space Grotesk',sans-serif; font-size:20px; line-height:1.3; font-weight:800; color:<?= $SOFT_BLK ?>; margin:0 0 10px 0;"><?= $c[0] ?></h3>
                <p style="font-family:'Inter',sans-serif; font-size:15px; line-height:1.7; color:#64748b; margin:0;"><?= $c[1] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     5. TIMELINE
============================================================ -->
<section style="padding: 6rem 0; background: linear-gradient(180deg, #F8FAFC 0%, #ffffff 100%);">
    <div style="max-width:1100px; margin:0 auto; padding:0 1.5rem;">

        <div style="text-align:center; max-width:780px; margin:0 auto 4rem auto;">
            <span style="font-family:'Space Grotesk',sans-serif; font-size:13px; font-weight:800; letter-spacing:.22em; text-transform:uppercase; color:<?= $YELLOW_DK ?>; display:inline-flex; align-items:center; gap:8px; margin-bottom:20px;">
                <span style="width:32px; height:2px; background:<?= $YELLOW ?>;"></span> Our Journey
            </span>
            <h2 style="font-family:'Space Grotesk',sans-serif; font-size:46px; line-height:1.1; font-weight:800; letter-spacing:-.025em; color:<?= $SOFT_BLK ?>; margin:0 0 16px 0;">
                The Journey Behind <span style="color:<?= $BLUE ?>;">YK Digital Hub</span>
            </h2>
            <p style="font-family:'Inter',sans-serif; font-size:18px; color:#64748b; margin:0;">
                From learning and experimentation to building digital solutions for real businesses.
            </p>
        </div>

        <div style="position:relative;">
            <!-- Vertical line -->
            <div class="yk-au-tl-line" style="position:absolute; left:50%; top:0; bottom:0; width:2px; background: linear-gradient(180deg, transparent, <?= $BLUE ?>33 10%, <?= $BLUE ?>33 90%, transparent); transform:translateX(-50%);"></div>

            <?php
            $timeline = [
                ['year'=>'Stage 1', 'title'=>'Discover', 'desc'=>'Learning the fundamentals of web development, design, and digital technologies.', 'side'=>'left'],
                ['year'=>'Stage 2', 'title'=>'Build',    'desc'=>'Turning ideas into working websites and real digital experiences.', 'side'=>'right'],
                ['year'=>'Stage 3', 'title'=>'Improve',  'desc'=>'Learning from projects, testing new approaches, and improving development workflows.', 'side'=>'left'],
                ['year'=>'Stage 4', 'title'=>'Specialize','desc'=>'Focusing deeper on websites, SEO, marketing, and business-focused digital solutions.', 'side'=>'right'],
                ['year'=>'Now',     'title'=>'YK Digital Hub', 'desc'=>'Bringing these capabilities together under one agency focused on helping businesses grow.', 'side'=>'left'],
            ];
            foreach ($timeline as $i => $t):
            ?>
            <div style="position:relative; padding: 24px 0; min-height: 120px;">
                <!-- Dot -->
                <div class="yk-au-tl-dot" style="position:absolute; left:50%; top:32px; width:20px; height:20px; background:#fff; border:3px solid <?= $BLUE ?>; border-radius:50%; transform:translateX(-50%); z-index:2; box-shadow: 0 0 0 6px #E0F2FE;">
                    <div style="width:100%; height:100%; border-radius:50%; background:<?= $YELLOW ?>; transform:scale(.5);"></div>
                </div>

                <!-- Card -->
                <div class="yk-au-tl-card" style="<?= $t['side'] === 'left' ? 'margin-right: calc(50% + 40px); text-align:right;' : 'margin-left: calc(50% + 40px);' ?>">
                    <div class="yk-au-card" style="background:#fff; padding:26px 28px; border-radius:16px; border:1.5px solid #e2e8f0; display:inline-block; text-align:left; max-width:100%;" onmouseover="this.style.borderColor='<?= $BLUE ?>'; this.style.boxShadow='0 20px 45px -15px <?= $BLUE ?>55';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                        <span style="font-family:'Space Grotesk',sans-serif; font-size:11px; font-weight:800; letter-spacing:.2em; text-transform:uppercase; color:<?= $YELLOW_DK ?>;"><?= $t['year'] ?></span>
                        <h3 style="font-family:'Space Grotesk',sans-serif; font-size:21px; font-weight:800; color:<?= $SOFT_BLK ?>; margin:6px 0 10px 0;"><?= $t['title'] ?></h3>
                        <p style="font-family:'Inter',sans-serif; font-size:15px; line-height:1.7; color:#64748b; margin:0;"><?= $t['desc'] ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     6. WHAT DRIVES US
============================================================ -->
<section id="our-values" style="padding: 6rem 0; background:#ffffff;">
    <div style="max-width:1400px; margin:0 auto; padding:0 1.5rem;">

        <div style="text-align:center; max-width:780px; margin:0 auto 4rem auto;">
            <span style="font-family:'Space Grotesk',sans-serif; font-size:13px; font-weight:800; letter-spacing:.22em; text-transform:uppercase; color:<?= $YELLOW_DK ?>; display:inline-flex; align-items:center; gap:8px; margin-bottom:20px;">
                <span style="width:32px; height:2px; background:<?= $YELLOW ?>;"></span> Our Values
            </span>
            <h2 style="font-family:'Space Grotesk',sans-serif; font-size:46px; line-height:1.1; font-weight:800; letter-spacing:-.025em; color:<?= $SOFT_BLK ?>; margin:0;">
                What Drives <span style="color:<?= $BLUE ?>;">Everything We Do</span>
            </h2>
        </div>

        <div class="yk-au-3col" style="display:grid; grid-template-columns: repeat(3, 1fr); gap:24px;">
            <?php
            $values = [
                ['Business First', 'Every digital decision should connect to a real business objective.', 'fas fa-bullseye'],
                ['Simplicity', 'Complex technology should create simple experiences for users.', 'fas fa-feather'],
                ['Quality', 'Clean design, reliable functionality, and maintainable code.', 'fas fa-gem'],
                ['Continuous Learning', 'Technology changes constantly — so we keep learning and improving.', 'fas fa-graduation-cap'],
                ['User Experience', 'A website should be easy to understand, navigate, and use.', 'fas fa-user-check'],
                ['Long-Term Thinking', 'Building digital foundations businesses can grow with.', 'fas fa-seedling'],
            ];
            foreach ($values as $i => $v):
            ?>
            <div class="yk-au-card" style="padding:32px 28px; background:#fff; border:1.5px solid #e2e8f0; border-radius:20px;" onmouseover="this.style.borderColor='<?= $YELLOW ?>'; this.style.boxShadow='0 24px 50px -15px <?= $YELLOW ?>77';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                <div style="width:56px; height:56px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:22px; color:<?= $SOFT_BLK ?>; background:<?= $YELLOW ?>; margin-bottom:22px; box-shadow: 0 12px 24px -8px <?= $YELLOW ?>aa;">
                    <i class="<?= $v[2] ?>"></i>
                </div>
                <h3 style="font-family:'Space Grotesk',sans-serif; font-size:20px; font-weight:800; color:<?= $SOFT_BLK ?>; margin:0 0 10px 0;"><?= $v[0] ?></h3>
                <p style="font-family:'Inter',sans-serif; font-size:15px; line-height:1.7; color:#64748b; margin:0;"><?= $v[1] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     7. OUR EXPERTISE
============================================================ -->
<section style="padding: 6rem 0; background: linear-gradient(180deg, #F8FAFC 0%, #ffffff 100%);">
    <div style="max-width:1400px; margin:0 auto; padding:0 1.5rem;">

        <div style="text-align:center; max-width:780px; margin:0 auto 4rem auto;">
            <span style="font-family:'Space Grotesk',sans-serif; font-size:13px; font-weight:800; letter-spacing:.22em; text-transform:uppercase; color:<?= $YELLOW_DK ?>; display:inline-flex; align-items:center; gap:8px; margin-bottom:20px;">
                <span style="width:32px; height:2px; background:<?= $YELLOW ?>;"></span> Our Expertise
            </span>
            <h2 style="font-family:'Space Grotesk',sans-serif; font-size:46px; line-height:1.1; font-weight:800; letter-spacing:-.025em; color:<?= $SOFT_BLK ?>; margin:0 0 16px 0;">
                Skills Behind Our <span style="color:<?= $BLUE ?>;">Digital Solutions</span>
            </h2>
        </div>

        <div class="yk-au-4col" style="display:grid; grid-template-columns: repeat(4, 1fr); gap:16px;">
            <?php
            $skills = [
                ['HTML5', 'fab fa-html5'], ['CSS / Tailwind', 'fab fa-css3-alt'],
                ['JavaScript', 'fab fa-js'], ['PHP', 'fab fa-php'],
                ['MySQL', 'fas fa-database'], ['WordPress', 'fab fa-wordpress'],
                ['SEO', 'fas fa-search'], ['Local SEO', 'fas fa-map-marker-alt'],
                ['Digital Marketing', 'fas fa-bullhorn'], ['UI / UX', 'fas fa-palette'],
                ['Content Strategy', 'fas fa-pen-fancy'], ['Responsive Design', 'fas fa-mobile-alt'],
            ];
            foreach ($skills as $s):
            ?>
            <div class="yk-au-card" style="padding:22px 20px; background:#fff; border:1.5px solid #e2e8f0; border-radius:14px; display:flex; align-items:center; gap:14px;" onmouseover="this.style.borderColor='<?= $BLUE ?>'; this.style.transform='translateY(-4px)';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.transform='translateY(0)';">
                <div style="width:42px; height:42px; border-radius:10px; background:#E0F2FE; color:<?= $BLUE ?>; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0;">
                    <i class="<?= $s[1] ?>"></i>
                </div>
                <span style="font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:15px; color:<?= $SOFT_BLK ?>;"><?= $s[0] ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     8. HOW WE WORK
============================================================ -->
<section style="padding: 6rem 0; background:#ffffff;">
    <div style="max-width:1400px; margin:0 auto; padding:0 1.5rem;">

        <div style="text-align:center; max-width:780px; margin:0 auto 4rem auto;">
            <span style="font-family:'Space Grotesk',sans-serif; font-size:13px; font-weight:800; letter-spacing:.22em; text-transform:uppercase; color:<?= $YELLOW_DK ?>; display:inline-flex; align-items:center; gap:8px; margin-bottom:20px;">
                <span style="width:32px; height:2px; background:<?= $YELLOW ?>;"></span> Process
            </span>
            <h2 style="font-family:'Space Grotesk',sans-serif; font-size:46px; line-height:1.1; font-weight:800; letter-spacing:-.025em; color:<?= $SOFT_BLK ?>; margin:0;">
                From First Conversation<br>to <span style="color:<?= $BLUE ?>;">Digital Growth</span>
            </h2>
        </div>

        <div style="display:grid; grid-template-columns: repeat(5, 1fr); gap:16px;" class="yk-au-3col">
            <?php
            $how = [
                ['01', 'Understand', 'We learn about the business, audience, and goals.', 'fas fa-comments'],
                ['02', 'Plan',       'We define structure, strategy, and priorities.',    'fas fa-clipboard-list'],
                ['03', 'Build',      'We design and develop the required digital experience.', 'fas fa-hammer'],
                ['04', 'Optimize',   'We improve usability, performance, SEO, and conversions.', 'fas fa-sliders-h'],
                ['05', 'Grow',       'We help create a stronger long-term digital presence.', 'fas fa-chart-line'],
            ];
            foreach ($how as $h):
            ?>
            <div class="yk-au-card" style="padding:28px 22px; background:#fff; border:1.5px solid #e2e8f0; border-radius:18px; position:relative;" onmouseover="this.style.borderColor='<?= $BLUE ?>'; this.style.transform='translateY(-6px)'; this.style.boxShadow='0 22px 45px -15px <?= $BLUE ?>55';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                <span style="position:absolute; top:18px; right:20px; font-family:'Space Grotesk',sans-serif; font-size:32px; font-weight:800; color:#f1f5f9; letter-spacing:-.04em;"><?= $h[0] ?></span>
                <div style="width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:20px; color:#fff; background: linear-gradient(135deg, <?= $BLUE ?>, <?= $BLUE_DARK ?>); margin-bottom:18px; position:relative; z-index:1;">
                    <i class="<?= $h[3] ?>"></i>
                </div>
                <h3 style="font-family:'Space Grotesk',sans-serif; font-size:18px; font-weight:800; color:<?= $SOFT_BLK ?>; margin:0 0 8px 0; position:relative; z-index:1;"><?= $h[1] ?></h3>
                <p style="font-family:'Inter',sans-serif; font-size:14px; line-height:1.6; color:#64748b; margin:0; position:relative; z-index:1;"><?= $h[2] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     9. WHY BUSINESSES CHOOSE US
============================================================ -->
<section id="why-us" style="padding: 6rem 0; background: linear-gradient(135deg, #F0F9FF 0%, #FFFCF0 100%);">
    <div style="max-width:1400px; margin:0 auto; padding:0 1.5rem;">
        <div class="yk-au-2col" style="display:grid; grid-template-columns: 1fr 1.1fr; gap:5rem; align-items:center;">

            <div>
                <span style="font-family:'Space Grotesk',sans-serif; font-size:13px; font-weight:800; letter-spacing:.22em; text-transform:uppercase; color:<?= $YELLOW_DK ?>; display:inline-flex; align-items:center; gap:8px; margin-bottom:20px;">
                    <span style="width:32px; height:2px; background:<?= $YELLOW ?>;"></span> Why Choose Us
                </span>
                <h2 style="font-family:'Space Grotesk',sans-serif; font-size:46px; line-height:1.1; font-weight:800; letter-spacing:-.025em; color:<?= $SOFT_BLK ?>; margin:0 0 24px 0;">
                    A Digital Partner<br>Focused on the<br><span style="color:<?= $BLUE ?>;">Bigger Picture</span>
                </h2>
                <p style="font-family:'Inter',sans-serif; font-size:19px; line-height:1.7; color:#475569; margin:0;">
                    <strong style="color:<?= $SOFT_BLK ?>;">A website should not just look good. It should have a purpose.</strong>
                </p>
            </div>

            <div class="yk-au-3col" style="display:grid; grid-template-columns: repeat(2, 1fr); gap:14px;">
                <?php
                $why = [
                    'Business-focused thinking', 'Modern technology',
                    'Responsive design', 'SEO-ready architecture',
                    'Clear communication', 'Scalable solutions',
                    'Conversion-focused UX', 'Long-term support',
                ];
                foreach ($why as $w):
                ?>
                <div class="yk-au-card" style="padding:18px 20px; background:#fff; border:1.5px solid #e2e8f0; border-radius:12px; display:flex; align-items:center; gap:12px;" onmouseover="this.style.borderColor='<?= $BLUE ?>'; this.style.transform='translateY(-3px)';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.transform='translateY(0)';">
                    <div style="width:24px; height:24px; border-radius:50%; background:<?= $YELLOW ?>; display:flex; align-items:center; justify-content:center; color:<?= $SOFT_BLK ?>; font-size:11px; flex-shrink:0;">
                        <i class="fas fa-check"></i>
                    </div>
                    <span style="font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:14.5px; color:<?= $SOFT_BLK ?>;"><?= $w ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     10. STATISTICS (DARK)
============================================================ -->
<section style="padding: 6rem 0; background: linear-gradient(135deg, <?= $SOFT_BLK ?> 0%, #1a2b47 100%); position:relative; overflow:hidden;">
    <!-- Yellow glow -->
    <div style="position:absolute; top:-100px; right:-100px; width:500px; height:500px; background: radial-gradient(circle, <?= $YELLOW ?>2e, transparent 65%); pointer-events:none;"></div>
    <div style="position:absolute; bottom:-150px; left:-100px; width:500px; height:500px; background: radial-gradient(circle, <?= $BLUE ?>44, transparent 65%); pointer-events:none;"></div>

    <div style="max-width:1400px; margin:0 auto; padding:0 1.5rem; position:relative;">

        <div style="text-align:center; max-width:780px; margin:0 auto 4rem auto;">
            <span style="font-family:'Space Grotesk',sans-serif; font-size:13px; font-weight:800; letter-spacing:.22em; text-transform:uppercase; color:<?= $YELLOW ?>; display:inline-flex; align-items:center; gap:8px; margin-bottom:20px;">
                <span style="width:32px; height:2px; background:<?= $YELLOW ?>;"></span> By the Numbers
            </span>
            <h2 style="font-family:'Space Grotesk',sans-serif; font-size:46px; line-height:1.1; font-weight:800; letter-spacing:-.025em; color:#fff; margin:0;">
                Built Around Progress,<br>Not Just <span style="color:<?= $YELLOW ?>;">Projects</span>
            </h2>
        </div>

        <div class="yk-au-stats" style="display:grid; grid-template-columns: repeat(4, 1fr); gap:24px;">
            <?php
            $stats = [
                ['50+', 'Projects & Experiences', 'fas fa-briefcase'],
                ['6+',  'Services & Solutions',   'fas fa-cubes'],
                ['11+', 'Industries Served',      'fas fa-industry'],
                ['3+',  'Countries Reached',      'fas fa-globe'],
            ];
            foreach ($stats as $s):
            ?>
            <div class="yk-au-card" style="padding:32px 24px; background: rgba(255,255,255,.04); border:1.5px solid rgba(255,255,255,.1); border-radius:20px; text-align:center; backdrop-filter: blur(10px);" onmouseover="this.style.borderColor='<?= $YELLOW ?>'; this.style.background='rgba(255,255,255,.07)';" onmouseout="this.style.borderColor='rgba(255,255,255,.1)'; this.style.background='rgba(255,255,255,.04)';">
                <div style="width:56px; height:56px; border-radius:14px; display:inline-flex; align-items:center; justify-content:center; font-size:22px; background:<?= $YELLOW ?>; color:<?= $SOFT_BLK ?>; margin-bottom:18px;">
                    <i class="<?= $s[2] ?>"></i>
                </div>
                <div style="font-family:'Space Grotesk',sans-serif; font-size:52px; line-height:1; font-weight:800; letter-spacing:-.03em; color:#fff; margin-bottom:10px;"><?= $s[0] ?></div>
                <div style="font-family:'Inter',sans-serif; font-size:14px; font-weight:600; color:#cbd5e1; letter-spacing:.02em;"><?= $s[1] ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     11. TESTIMONIALS (Dynamic)
============================================================ -->
<?php if (!empty($testimonials)): ?>
<section style="padding: 6rem 0; background:#ffffff;">
    <div style="max-width:1400px; margin:0 auto; padding:0 1.5rem;">

        <div style="text-align:center; max-width:780px; margin:0 auto 4rem auto;">
            <span style="font-family:'Space Grotesk',sans-serif; font-size:13px; font-weight:800; letter-spacing:.22em; text-transform:uppercase; color:<?= $YELLOW_DK ?>; display:inline-flex; align-items:center; gap:8px; margin-bottom:20px;">
                <span style="width:32px; height:2px; background:<?= $YELLOW ?>;"></span> Client Reviews
            </span>
            <h2 style="font-family:'Space Grotesk',sans-serif; font-size:46px; line-height:1.1; font-weight:800; letter-spacing:-.025em; color:<?= $SOFT_BLK ?>; margin:0;">
                What Our <span style="color:<?= $BLUE ?>;">Clients Say</span>
            </h2>
        </div>

        <div class="yk-au-3col" style="display:grid; grid-template-columns: repeat(3, 1fr); gap:24px;">
            <?php foreach ($testimonials as $t): ?>
            <figure class="yk-au-card" style="padding:32px 28px; background:#fff; border:1.5px solid #e2e8f0; border-radius:20px; margin:0; display:flex; flex-direction:column;" onmouseover="this.style.borderColor='<?= $BLUE ?>'; this.style.boxShadow='0 22px 45px -15px <?= $BLUE ?>55';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                <?php if (!empty($t['rating'])): ?>
                    <div style="color:<?= $YELLOW ?>; font-size:18px; letter-spacing:3px; margin-bottom:16px;"><?= aboutStars($t['rating']) ?></div>
                <?php endif; ?>
                <blockquote style="font-family:'Inter',sans-serif; font-size:15.5px; line-height:1.8; color:#475569; margin:0 0 24px 0; flex:1; font-style:italic;">"<?= htmlspecialchars($t['testimonial']) ?>"</blockquote>
                <figcaption style="display:flex; align-items:center; gap:14px; padding-top:20px; border-top:1px solid #f1f5f9;">
                    <?php if (!empty($t['client_photo'])): ?>
                        <img src="<?= htmlspecialchars($t['client_photo']) ?>" alt="<?= htmlspecialchars($t['client_name']) ?>" style="width:48px; height:48px; border-radius:50%; object-fit:cover;">
                    <?php else: ?>
                        <div style="width:48px; height:48px; border-radius:50%; background: linear-gradient(135deg,<?= $BLUE ?>,<?= $BLUE_DARK ?>); color:#fff; display:flex; align-items:center; justify-content:center; font-family:'Space Grotesk',sans-serif; font-weight:800; font-size:18px;">
                            <?= strtoupper(substr($t['client_name'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <div style="font-family:'Space Grotesk',sans-serif; font-size:15px; font-weight:800; color:<?= $SOFT_BLK ?>;"><?= htmlspecialchars($t['client_name']) ?></div>
                        <?php $sub = trim(($t['position'] ?? '') . (!empty($t['position']) && !empty($t['company_name']) ? ', ' : '') . ($t['company_name'] ?? '')); ?>
                        <?php if ($sub): ?><div style="font-family:'Inter',sans-serif; font-size:13px; color:#64748b;"><?= htmlspecialchars($sub) ?></div><?php endif; ?>
                    </div>
                </figcaption>
            </figure>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================================================
     12. FINAL CTA
============================================================ -->
<section style="padding: 6rem 0; background: linear-gradient(135deg, <?= $SOFT_BLK ?> 0%, #1a2b47 100%); position:relative; overflow:hidden; text-align:center;">
    <div style="position:absolute; top:-100px; left:50%; transform:translateX(-50%); width:700px; height:700px; background: radial-gradient(circle, <?= $BLUE ?>44, transparent 65%); pointer-events:none;"></div>
    <div style="position:absolute; bottom:-100px; right:0; width:400px; height:400px; background: radial-gradient(circle, <?= $YELLOW ?>2e, transparent 65%); pointer-events:none;"></div>

    <div style="max-width:800px; margin:0 auto; padding:0 1.5rem; position:relative;">
        <h2 style="font-family:'Space Grotesk',sans-serif; font-size:52px; line-height:1.1; font-weight:800; letter-spacing:-.025em; color:#fff; margin:0 0 22px 0;">
            Have a Digital Idea?<br>Let's <span style="color:<?= $YELLOW ?>;">Build It</span>.
        </h2>
        <p style="font-family:'Inter',sans-serif; font-size:18px; line-height:1.7; color:#cbd5e1; margin:0 0 38px 0;">
            Whether you need a new website, better search visibility, or a stronger digital presence — let's discuss what your business needs next.
        </p>
        <div style="display:flex; flex-wrap:wrap; gap:14px; justify-content:center;">
            <a href="<?= BASE_URL ?>/contact.php"
               style="display:inline-flex; align-items:center; gap:10px; padding:17px 36px; background:<?= $YELLOW ?>; color:<?= $SOFT_BLK ?>; font-family:'Space Grotesk',sans-serif; font-weight:800; font-size:16px; border-radius:999px; text-decoration:none; box-shadow: 0 14px 34px -10px <?= $YELLOW ?>aa; transition: transform .2s;"
               onmouseover="this.style.transform='translateY(-3px)';" onmouseout="this.style.transform='translateY(0)';">
                Start a Project <i class="fas fa-arrow-right" style="font-size:13px;"></i>
            </a>
            <a href="<?= BASE_URL ?>/services/"
               style="display:inline-flex; align-items:center; gap:10px; padding:17px 36px; background: transparent; color:#fff; font-family:'Space Grotesk',sans-serif; font-weight:800; font-size:16px; border-radius:999px; text-decoration:none; border:2px solid rgba(255,255,255,.25); transition: all .2s;"
               onmouseover="this.style.borderColor='<?= $YELLOW ?>'; this.style.color='<?= $YELLOW ?>';" onmouseout="this.style.borderColor='rgba(255,255,255,.25)'; this.style.color='#fff';">
                Explore Our Services
            </a>
        </div>
    </div>
</section>

<?php
include ROOT_PATH . '/includes/footer.php';
ob_end_flush();

// ============ JSON-LD ============
$ld = [
    '@context' => 'https://schema.org',
    '@type'    => 'AboutPage',
    'name'     => 'About YK Digital Hub',
    'url'      => $page_canonical,
    'mainEntity' => [
        '@type' => 'Organization',
        'name'  => 'YK Digital Hub',
        'url'   => BASE_URL,
        'description' => $page_description,
    ],
];
?>
<script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>