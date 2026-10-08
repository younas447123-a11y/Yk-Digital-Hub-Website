<?php
if (!function_exists('getSiteSetting')) { require_once __DIR__ . '/functions.php'; }

$__fe = getSiteSetting('primary_email', '');
$__fp = getSiteSetting('phone', '');
$__fw = getSiteSetting('whatsapp', '');
$__fa = getSiteSetting('address', '');

$__socials = [
    'facebook_url'  => ['icon' => 'fab fa-facebook-f', 'url' => 'https://www.facebook.com/younas4471'],
    'instagram_url' => ['icon' => 'fab fa-instagram', 'url' => 'https://www.instagram.com/ykdigitalhub447123/'],
    'tiktok_url'    => ['icon' => 'fab fa-tiktok', 'url' => 'https://www.tiktok.com/@ykdigitalhub4471'],
    'linkedin_url'  => ['icon' => 'fab fa-linkedin-in', 'url' => 'https://www.linkedin.com/in/younas-khan-78b4a9355/'],
];
?>

<footer class="yk-site-footer bg-white text-slate-700 border-t border-slate-200">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 pt-16 pb-8 lg:pt-20">

        <!-- ===== TOP: 4 columns ===== -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-12 lg:gap-10">

            <!-- Column 1 — Brand + description + socials -->
            <div class="lg:col-span-4 lg:pr-8">
                <a href="<?= BASE_URL ?>/" class="inline-flex items-center gap-3 mb-6">
                    <img src="<?= BASE_URL ?>/uploads/components/logo.webp" alt="YK Digital Hub logo" width="48" height="48" class="w-12 h-12 rounded-xl object-contain bg-white">
                    <span class="text-2xl font-extrabold tracking-tight" style="color:#2B3F5C;">
                        YK <span style="color:#0084FF;">Digital</span> <span style="color:#FF8A00;">Hub</span>
                    </span>
                </a>

                <p class="text-slate-600 text-base leading-relaxed mb-7 max-w-md">
                    We are a <strong class="font-semibold" style="color:#2B3F5C;">results-driven digital agency</strong>
                    focused on delivering innovative solutions to help businesses grow online. With expertise in
                    website development, digital marketing, SEO, and design, we partner with brands to create
                    <strong class="font-semibold" style="color:#2B3F5C;">impactful and measurable results</strong>.
                    Specialist focus on <strong class="font-semibold" style="color:#2B3F5C;">healthcare providers</strong>
                    and small businesses across the <strong class="font-semibold" style="color:#2B3F5C;">USA, UK, Canada,
                    Australia, Germany &amp; Pakistan</strong>.
                </p>

                <!-- Social icons -->
                <div class="flex items-center gap-3">
                    <?php foreach ($__socials as $key => $social):
                        $url = $social['url'];
                    ?>
                        <a href="<?= htmlspecialchars($url) ?>" target="_blank" rel="noopener noreferrer"
                           aria-label="<?= htmlspecialchars(str_replace('_url', '', $key)) ?>"
                           class="w-11 h-11 inline-flex items-center justify-center rounded-full border transition-all duration-200 hover:-translate-y-0.5"
                           style="color:#0084FF; border-color:#0084FF;">
                            <i class="<?= $social['icon'] ?> text-base"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Column 2 — Digital Marketing -->
            <div class="lg:col-span-3">
                <h4 class="text-xl font-bold mb-6" style="color:#FF8A00;">Digital Marketing</h4>
                <ul class="space-y-4">
                    <li>
                        <a href="<?= BASE_URL ?>/services/digital-marketing/" class="group inline-flex items-center gap-3 text-slate-700 hover:text-[#0084FF] transition-colors text-base">
                            <i class="fas fa-magnifying-glass w-5 text-center" style="color:#FF8A00;"></i>
                            <span>SEO Services</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/services/digital-marketing/" class="group inline-flex items-center gap-3 text-slate-700 hover:text-[#0084FF] transition-colors text-base">
                            <i class="fas fa-map-marker-alt w-5 text-center" style="color:#FF8A00;"></i>
                            <span>Local SEO</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/services/digital-marketing/" class="group inline-flex items-center gap-3 text-slate-700 hover:text-[#0084FF] transition-colors text-base">
                            <i class="fas fa-bullhorn w-5 text-center" style="color:#FF8A00;"></i>
                            <span>Social Media Marketing</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/services/digital-marketing/" class="group inline-flex items-center gap-3 text-slate-700 hover:text-[#0084FF] transition-colors text-base">
                            <i class="fas fa-pen-fancy w-5 text-center" style="color:#FF8A00;"></i>
                            <span>Content Marketing</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/services/digital-marketing/" class="group inline-flex items-center gap-3 text-slate-700 hover:text-[#0084FF] transition-colors text-base">
                            <i class="fas fa-bullseye w-5 text-center" style="color:#FF8A00;"></i>
                            <span>Paid Ads &amp; PPC</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/services/digital-marketing/" class="group inline-flex items-center gap-3 text-slate-700 hover:text-[#0084FF] transition-colors text-base">
                            <i class="fas fa-star w-5 text-center" style="color:#FF8A00;"></i>
                            <span>Google Business Profile</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/services/digital-marketing/" class="group inline-flex items-center gap-3 text-slate-700 hover:text-[#0084FF] transition-colors text-base">
                            <i class="fas fa-chart-line w-5 text-center" style="color:#FF8A00;"></i>
                            <span>Digital Strategy</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Column 3 — Website Design & Development -->
            <div class="lg:col-span-3">
                <h4 class="text-xl font-bold mb-6" style="color:#FF8A00;">Website Design &amp; Development</h4>
                <ul class="space-y-4">
                    <li>
                        <a href="<?= BASE_URL ?>/services/web-design-development/" class="group inline-flex items-center gap-3 text-slate-700 hover:text-[#0084FF] transition-colors text-base">
                            <i class="fas fa-laptop-code w-5 text-center" style="color:#FF8A00;"></i>
                            <span>Custom Website Design</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/services/web-design-development/" class="group inline-flex items-center gap-3 text-slate-700 hover:text-[#0084FF] transition-colors text-base">
                            <i class="fab fa-wordpress w-5 text-center" style="color:#FF8A00;"></i>
                            <span>WordPress Development</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/services/web-design-development/" class="group inline-flex items-center gap-3 text-slate-700 hover:text-[#0084FF] transition-colors text-base">
                            <i class="fab fa-shopify w-5 text-center" style="color:#FF8A00;"></i>
                            <span>Shopify &amp; Ecommerce</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/services/web-design-development/" class="group inline-flex items-center gap-3 text-slate-700 hover:text-[#0084FF] transition-colors text-base">
                            <i class="fas fa-sync-alt w-5 text-center" style="color:#FF8A00;"></i>
                            <span>Website Redesign</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/services/web-design-development/" class="group inline-flex items-center gap-3 text-slate-700 hover:text-[#0084FF] transition-colors text-base">
                            <i class="fas fa-heart-pulse w-5 text-center" style="color:#FF8A00;"></i>
                            <span>Healthcare Websites</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/services/web-design-development/" class="group inline-flex items-center gap-3 text-slate-700 hover:text-[#0084FF] transition-colors text-base">
                            <i class="fas fa-palette w-5 text-center" style="color:#FF8A00;"></i>
                            <span>UI / UX Design</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/services/web-design-development/" class="group inline-flex items-center gap-3 text-slate-700 hover:text-[#0084FF] transition-colors text-base">
                            <i class="fas fa-mobile-screen-button w-5 text-center" style="color:#FF8A00;"></i>
                            <span>Responsive Design</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Column 4 — Company + Contact -->
            <div class="lg:col-span-2">
                <h4 class="text-xl font-bold mb-6" style="color:#FF8A00;">Company</h4>
                <ul class="space-y-4 mb-9">
                    <li><a href="<?= BASE_URL ?>/about" class="text-slate-700 hover:text-[#0084FF] transition-colors text-base">About Us</a></li>
                    <li><a href="<?= BASE_URL ?>/services/" class="text-slate-700 hover:text-[#0084FF] transition-colors text-base">Services</a></li>
                    <li><a href="<?= BASE_URL ?>/portfolio/" class="text-slate-700 hover:text-[#0084FF] transition-colors text-base">Portfolio</a></li>
                    <li><a href="<?= BASE_URL ?>/blog/" class="text-slate-700 hover:text-[#0084FF] transition-colors text-base">Blog</a></li>
                    <li><a href="<?= BASE_URL ?>/contact" class="text-slate-700 hover:text-[#0084FF] transition-colors text-base">Contact</a></li>
                </ul>

                <h4 class="text-base font-bold mb-4" style="color:#2B3F5C;">Get in Touch</h4>
                <ul class="space-y-3 text-sm">
                    <?php if ($__fe): ?>
                    <li>
                        <a href="mailto:<?= htmlspecialchars($__fe) ?>" class="inline-flex items-center gap-2 text-slate-600 hover:text-[#0084FF] transition">
                            <i class="fas fa-envelope" style="color:#FF8A00;"></i> <?= htmlspecialchars($__fe) ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($__fp): ?>
                    <li>
                        <a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $__fp)) ?>" class="inline-flex items-center gap-2 text-slate-600 hover:text-[#0084FF] transition">
                            <i class="fas fa-phone" style="color:#FF8A00;"></i> <?= htmlspecialchars($__fp) ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($__fw): ?>
                    <li>
                        <a href="https://wa.me/<?= htmlspecialchars(preg_replace('/[^0-9]/', '', $__fw)) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 font-semibold" style="color:#25D366;">
                            <i class="fab fa-whatsapp"></i> Chat on WhatsApp
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>

        </div>
        <!-- /.grid -->

        <!-- ===== BOTTOM BAR ===== -->
        <div class="mt-16 pt-6 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-slate-600 text-sm">
                <span class="font-semibold" style="color:#2B3F5C;">© <?= date('Y') ?></span>
                <span class="font-bold" style="color:#2B3F5C;">
                    YK <span style="color:#0084FF;">Digital</span> <span style="color:#FF8A00;">Hub</span>
                </span>.
                All rights reserved.
            </p>
            <div class="flex items-center gap-5 text-sm">
                <a href="<?= BASE_URL ?>/privacy-policy.php" class="text-slate-600 hover:text-[#0084FF] transition">Privacy Policy</a>
                <span class="w-px h-4 bg-slate-300"></span>
                <a href="<?= BASE_URL ?>/terms.php" class="text-slate-600 hover:text-[#0084FF] transition">Terms of Service</a>

<script>// FAQ toggle behavior
document.querySelectorAll('.yk-faq details').forEach(function (d) {
    d.addEventListener('toggle', function () {
        if (!d.open) return;
        document.querySelectorAll('.yk-faq details').forEach(function (other) {
            if (other !== d) other.open = false;
        });
    });
});
</script>
                

            </div>
        </div>
    </div>
</footer>

</body>
</html>