<?php
/**
 * Country Flags Marquee Section
 * Infinite horizontal scrolling flags showing countries we serve.
 *
 * Usage in any page:
 *   require_once ROOT_PATH . '/includes/country-flags.php';
 *   renderCountryFlags();
 *
 * Optional params:
 *   renderCountryFlags(['title' => 'Trusted by clients worldwide']);
 */

if (!function_exists('renderCountryFlags')) {

    function renderCountryFlags($opts = []) {
        $title    = $opts['title'] ?? '';
        $subtitle = $opts['subtitle'] ?? '';

        // Countries we serve — slug, name, flag code (flagcdn.com)
        $countries = [
            ['code' => 'us', 'name' => 'United States'],
            ['code' => 'gb', 'name' => 'United Kingdom'],
            ['code' => 'ca', 'name' => 'Canada'],
            ['code' => 'au', 'name' => 'Australia'],
            ['code' => 'pk', 'name' => 'Pakistan'],
            ['code' => 'de', 'name' => 'Germany'],
            ['code' => 'fr', 'name' => 'France'],
            ['code' => 'it', 'name' => 'Italy'],
            ['code' => 'es', 'name' => 'Spain'],
            ['code' => 'nl', 'name' => 'Netherlands'],
            ['code' => 'ae', 'name' => 'United Arab Emirates'],
            ['code' => 'sa', 'name' => 'Saudi Arabia'],
        ];

        // Duplicate the list so the marquee can loop seamlessly
        $loop = array_merge($countries, $countries);
        $uid  = 'cf-' . substr(md5(uniqid('', true)), 0, 6);
        ?>

        <style>
            @keyframes <?= $uid ?>-scroll {
                0%   { transform: translateX(0); }
                100% { transform: translateX(-50%); }
            }
            .<?= $uid ?>-track {
                display: flex;
                gap: 28px;
                width: max-content;
                animation: <?= $uid ?>-scroll 40s linear infinite;
                will-change: transform;
            }
            .<?= $uid ?>-track:hover {
                animation-play-state: paused;
            }
            .<?= $uid ?>-wrapper {
                overflow: hidden;
                position: relative;
                -webkit-mask-image: linear-gradient(to right, transparent 0%, black 8%, black 92%, transparent 100%);
                        mask-image: linear-gradient(to right, transparent 0%, black 8%, black 92%, transparent 100%);
            }
            .<?= $uid ?>-flag {
                width: 100px;
                height: 66px;
                border-radius: 10px;
                border: 1px solid #e2e8f0;
                box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
                overflow: hidden;
                flex-shrink: 0;
                background: #fff;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            .<?= $uid ?>-flag:hover {
                transform: translateY(-4px) scale(1.05);
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
            }
            .<?= $uid ?>-flag img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }
            @media (max-width: 640px) {
                .<?= $uid ?>-flag { width: 76px; height: 52px; }
                .<?= $uid ?>-track { gap: 18px; animation-duration: 28s; }
            }
            @media (prefers-reduced-motion: reduce) {
                .<?= $uid ?>-track { animation: none; }
            }
        </style>

        <section style="padding: 3.5rem 0; background: #ffffff; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9;">
            <div style="max-width: 1400px; margin: 0 auto; padding: 0 1.5rem;">

                <?php if ($title || $subtitle): ?>
                    <div style="text-align: center; margin-bottom: 2rem;">
                        <?php if ($title): ?>
                            <div style="font-family: 'Inter', system-ui, sans-serif; font-size: 13px; font-weight: 800; letter-spacing: 0.22em; text-transform: uppercase; color: #FF8A00; margin-bottom: 10px;">
                                <span style="color:#FF8A00;">✦</span> <?= htmlspecialchars($title) ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($subtitle): ?>
                            <p style="font-family: 'Inter', system-ui, sans-serif; font-size: 16px; color: #64748b; margin: 0;">
                                <?= htmlspecialchars($subtitle) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="<?= $uid ?>-wrapper">
                    <div class="<?= $uid ?>-track">
                        <?php foreach ($loop as $c): ?>
                            <div class="<?= $uid ?>-flag" title="<?= htmlspecialchars($c['name']) ?>">
                                <img src="https://flagcdn.com/w160/<?= htmlspecialchars($c['code']) ?>.png"
                                     srcset="https://flagcdn.com/w320/<?= htmlspecialchars($c['code']) ?>.png 2x"
                                     alt="<?= htmlspecialchars($c['name']) ?> flag"
                                     loading="lazy">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        </section>

        <?php
    }
}