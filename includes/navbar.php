<?php
if (!function_exists('getServiceNavigation')) { require_once __DIR__ . '/functions.php'; }

$nav_services  = getServiceNavigation();
$nav_portfolio = getPortfolioNavigation();
$active        = getActiveNav();

$whatsapp_raw = getSiteSetting('whatsapp', '');
$whatsapp_num = preg_replace('/[^0-9]/', '', $whatsapp_raw);
$whatsapp_url = $whatsapp_num ? 'https://wa.me/' . $whatsapp_num : '#';
?>
<header class="sticky top-0 z-50 bg-white border-b border-slate-200 shadow-sm">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <div class="flex items-center justify-between h-20 lg:h-24">

            <!-- Logo -->
            <a href="<?= BASE_URL ?>/" class="flex items-center gap-2.5 shrink-0">
                <img src="<?= BASE_URL ?>/uploads/components/logo.png" alt="YK Digital Hub logo" class="w-11 h-11 rounded-lg object-contain bg-white">
                <span class="text-2xl font-extrabold tracking-tight" style="color:#2B3F5C;">
                    YK <span style="color:#0084FF;">Digital</span> <span style="color:#FF8A00;">Hub</span>
                </span>
            </a>

            <!-- ============ DESKTOP NAV ============ -->
            <nav class="hidden lg:flex items-center gap-3 ml-10" aria-label="Main navigation">

                <a href="<?= BASE_URL ?>/"
                   class="px-5 py-3 text-[17px] font-semibold transition rounded-lg hover:bg-slate-50 <?= $active === 'home' ? 'text-[#0084FF]' : 'text-slate-700 hover:text-[#0084FF]' ?>">
                    Home
                </a>

                <a href="<?= BASE_URL ?>/about.php"
                   class="px-5 py-3 text-[17px] font-semibold transition rounded-lg hover:bg-slate-50 <?= $active === 'about' ? 'text-[#0084FF]' : 'text-slate-700 hover:text-[#0084FF]' ?>">
                    About
                </a>

                <!-- ====== SERVICES DROPDOWN ====== -->
                <div class="relative group">
                    <button type="button"
                            class="flex items-center gap-2 px-5 py-3 text-[17px] font-semibold transition rounded-lg hover:bg-slate-50 <?= $active === 'services' ? 'text-[#0084FF]' : 'text-slate-700 hover:text-[#0084FF]' ?>"
                            aria-haspopup="true" aria-expanded="false">
                        Our Services
                        <i class="fas fa-chevron-down text-[11px] mt-0.5 transition-transform group-hover:rotate-180"></i>
                    </button>
                    <div class="absolute left-0 top-full pt-3 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 z-50">
                        <div class="rounded-2xl shadow-2xl border border-slate-200 overflow-hidden bg-white" style="min-width: 760px; max-width: 1050px;">
                            <?php if (empty($nav_services)): ?>
                                <div class="px-6 py-6 text-base text-slate-400">No services yet.</div>
                            <?php else: ?>
                                <div class="grid grid-cols-<?= count($nav_services) ?>">
                                    <?php foreach ($nav_services as $idx => $nav_parent): ?>
                                        <div class="p-7 <?= $idx < count($nav_services) - 1 ? 'border-r border-slate-100' : '' ?>">
                                            <a href="<?= BASE_URL ?>/services/<?= htmlspecialchars($nav_parent['slug']) ?>/"
                                               class="block text-lg font-extrabold mb-5 pb-3 border-b border-slate-100 hover:text-[#0084FF] transition"
                                               style="color:#2B3F5C;">
                                                <?= htmlspecialchars($nav_parent['name']) ?>
                                            </a>
                                            <div class="space-y-5 max-h-[440px] overflow-y-auto pr-2">
                                                <?php foreach ($nav_parent['children'] as $nav_child): ?>
                                                    <div>
                                                        <a href="<?= BASE_URL ?>/services/<?= htmlspecialchars($nav_parent['slug']) ?>/<?= htmlspecialchars($nav_child['slug']) ?>/"
                                                           class="block text-[15px] font-bold mb-2 hover:text-[#0084FF] transition"
                                                           style="color:#FF8A00;">
                                                            <?= htmlspecialchars($nav_child['name']) ?>
                                                        </a>
                                                        <?php if (!empty($nav_child['services'])): ?>
                                                            <ul class="space-y-1 pl-2 border-l-2 border-slate-100">
                                                                <?php foreach (array_slice($nav_child['services'], 0, 8) as $svc): ?>
                                                                    <li>
                                                                        <a href="<?= BASE_URL ?>/services/<?= htmlspecialchars($nav_parent['slug']) ?>/<?= htmlspecialchars($nav_child['slug']) ?>/<?= htmlspecialchars($svc['slug']) ?>/"
                                                                           class="block text-[15px] text-slate-600 hover:text-[#0084FF] transition py-1 pl-3 rounded hover:bg-slate-50">
                                                                            <?= htmlspecialchars($svc['title']) ?>
                                                                        </a>
                                                                    </li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                                <?php if (empty($nav_parent['children'])): ?>
                                                    <p class="text-sm text-slate-400 italic">No subcategories yet.</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- ====== PORTFOLIO DROPDOWN ====== -->
                <div class="relative group">
                    <button type="button"
                            class="flex items-center gap-2 px-5 py-3 text-[17px] font-semibold transition rounded-lg hover:bg-slate-50 <?= $active === 'portfolio' ? 'text-[#0084FF]' : 'text-slate-700 hover:text-[#0084FF]' ?>"
                            aria-haspopup="true" aria-expanded="false">
                        Portfolio
                        <i class="fas fa-chevron-down text-[11px] mt-0.5 transition-transform group-hover:rotate-180"></i>
                    </button>
                    <div class="absolute left-0 top-full pt-3 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 z-50">
                        <div class="rounded-2xl shadow-2xl border border-slate-200 overflow-hidden bg-white" style="min-width: 760px; max-width: 1050px;">
                            <?php if (empty($nav_portfolio)): ?>
                                <div class="px-6 py-6 text-base text-slate-400">No projects yet.</div>
                            <?php else: ?>
                                <div class="grid grid-cols-<?= count($nav_portfolio) ?>">
                                    <?php foreach ($nav_portfolio as $idx => $nav_parent): ?>
                                        <div class="p-7 <?= $idx < count($nav_portfolio) - 1 ? 'border-r border-slate-100' : '' ?>">
                                            <a href="<?= BASE_URL ?>/portfolio/<?= htmlspecialchars($nav_parent['slug']) ?>/"
                                               class="block text-lg font-extrabold mb-5 pb-3 border-b border-slate-100 hover:text-[#0084FF] transition"
                                               style="color:#2B3F5C;">
                                                <?= htmlspecialchars($nav_parent['name']) ?>
                                            </a>
                                            <div class="space-y-5 max-h-[440px] overflow-y-auto pr-2">
                                                <?php foreach ($nav_parent['children'] as $nav_child): ?>
                                                    <div>
                                                        <a href="<?= BASE_URL ?>/portfolio/<?= htmlspecialchars($nav_parent['slug']) ?>/<?= htmlspecialchars($nav_child['slug']) ?>/"
                                                           class="block text-[15px] font-bold mb-2 hover:text-[#0084FF] transition"
                                                           style="color:#FF8A00;">
                                                            <?= htmlspecialchars($nav_child['name']) ?>
                                                        </a>
                                                        <?php if (!empty($nav_child['projects'])): ?>
                                                            <ul class="space-y-1 pl-2 border-l-2 border-slate-100">
                                                                <?php foreach (array_slice($nav_child['projects'], 0, 8) as $proj): ?>
                                                                    <li>
                                                                        <a href="<?= BASE_URL ?>/portfolio/<?= htmlspecialchars($nav_parent['slug']) ?>/<?= htmlspecialchars($nav_child['slug']) ?>/<?= htmlspecialchars($proj['slug']) ?>/"
                                                                           class="block text-[15px] text-slate-600 hover:text-[#0084FF] transition py-1 pl-3 rounded hover:bg-slate-50">
                                                                            <?= htmlspecialchars($proj['title']) ?>
                                                                        </a>
                                                                    </li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                                <?php if (empty($nav_parent['children'])): ?>
                                                    <p class="text-sm text-slate-400 italic">No subcategories yet.</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <a href="<?= BASE_URL ?>/blog/"
                   class="px-5 py-3 text-[17px] font-semibold transition rounded-lg hover:bg-slate-50 <?= $active === 'blog' ? 'text-[#0084FF]' : 'text-slate-700 hover:text-[#0084FF]' ?>">
                    Blog
                </a>

                <a href="<?= BASE_URL ?>/contact.php"
                   class="px-5 py-3 text-[17px] font-semibold transition rounded-lg hover:bg-slate-50 <?= $active === 'contact' ? 'text-[#0084FF]' : 'text-slate-700 hover:text-[#0084FF]' ?>">
                    Contact
                </a>
            </nav>

            <!-- ============ RIGHT BUTTONS ============ -->
            <div class="hidden lg:flex items-center gap-3">
                <a href="<?= htmlspecialchars($whatsapp_url) ?>" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2.5 px-6 py-3.5 text-white font-bold text-[15px] rounded-full shadow-sm transition hover:opacity-90 hover:-translate-y-0.5"
                   style="background:#25D366;">
                    <i class="fab fa-whatsapp text-lg"></i> WhatsApp
                </a>
                <a href="<?= BASE_URL ?>/contact.php"
                   class="inline-flex items-center gap-2.5 px-7 py-3.5 text-white font-bold text-[15px] rounded-full shadow-md transition hover:opacity-90 hover:-translate-y-0.5"
                   style="background: linear-gradient(90deg, #FF8A00 0%, #FF6B00 100%);">
                    Get Free Quote <i class="fas fa-arrow-right text-sm"></i>
                </a>
            </div>

            <!-- Mobile toggle -->
            <button id="navToggle"
                    class="lg:hidden inline-flex items-center justify-center w-12 h-12 rounded-lg hover:bg-slate-100 transition"
                    aria-label="Toggle navigation" aria-expanded="false" aria-controls="mobileNav">
                <i class="fas fa-bars text-2xl" style="color:#2B3F5C;"></i>
            </button>
        </div>
    </div>

    <!-- ============ MOBILE NAV ============ -->
    <div id="mobileNav" class="lg:hidden hidden border-t border-slate-200 bg-white max-h-[calc(100vh-5rem)] overflow-y-auto">
        <nav class="max-w-[1600px] mx-auto px-4 py-4 space-y-1" aria-label="Mobile navigation">

            <a href="<?= BASE_URL ?>/"
               class="block px-4 py-3.5 text-[17px] font-semibold rounded-lg <?= $active === 'home' ? 'text-[#0084FF] bg-blue-50' : 'text-slate-700 hover:bg-slate-50' ?>">
                Home
            </a>
            <a href="<?= BASE_URL ?>/about.php"
               class="block px-4 py-3.5 text-[17px] font-semibold rounded-lg <?= $active === 'about' ? 'text-[#0084FF] bg-blue-50' : 'text-slate-700 hover:bg-slate-50' ?>">
                About
            </a>

            <!-- SERVICES ACCORDION -->
            <div>
                <button type="button"
                        class="nav-acc w-full flex items-center justify-between px-4 py-3.5 text-[17px] font-semibold rounded-lg <?= $active === 'services' ? 'text-[#0084FF] bg-blue-50' : 'text-slate-700 hover:bg-slate-50' ?>"
                        data-target="mServices" aria-expanded="false" aria-controls="mServices">
                    Our Services
                    <i class="fas fa-chevron-down text-xs transition-transform"></i>
                </button>
                <div id="mServices" class="hidden pl-4 mt-1 space-y-1">
                    <?php foreach ($nav_services as $nav_parent): ?>
                        <div>
                            <button type="button"
                                    class="nav-acc w-full flex items-center justify-between px-4 py-3 text-base font-bold rounded-lg hover:bg-slate-50"
                                    data-target="mSvc-<?= (int)$nav_parent['id'] ?>"
                                    aria-expanded="false" aria-controls="mSvc-<?= (int)$nav_parent['id'] ?>"
                                    style="color:#2B3F5C;">
                                <span><?= htmlspecialchars($nav_parent['name']) ?></span>
                                <?php if (!empty($nav_parent['children'])): ?>
                                    <i class="fas fa-chevron-down text-xs"></i>
                                <?php endif; ?>
                            </button>
                            <div id="mSvc-<?= (int)$nav_parent['id'] ?>" class="hidden pl-4 space-y-1">
                                <a href="<?= BASE_URL ?>/services/<?= htmlspecialchars($nav_parent['slug']) ?>/"
                                   class="block px-4 py-2.5 text-[15px] font-semibold text-[#0084FF] hover:bg-slate-50 rounded-lg">
                                    View All <?= htmlspecialchars($nav_parent['name']) ?>
                                </a>
                                <?php foreach ($nav_parent['children'] as $nav_child): ?>
                                    <div>
                                        <button type="button"
                                                class="nav-acc w-full flex items-center justify-between px-4 py-2.5 text-[15px] font-bold rounded-lg hover:bg-slate-50"
                                                data-target="mSvcC-<?= (int)$nav_child['id'] ?>"
                                                aria-expanded="false" aria-controls="mSvcC-<?= (int)$nav_child['id'] ?>"
                                                style="color:#FF8A00;">
                                            <span><?= htmlspecialchars($nav_child['name']) ?></span>
                                            <?php if (!empty($nav_child['services'])): ?>
                                                <i class="fas fa-chevron-down text-xs"></i>
                                            <?php endif; ?>
                                        </button>
                                        <?php if (!empty($nav_child['services'])): ?>
                                            <div id="mSvcC-<?= (int)$nav_child['id'] ?>" class="hidden pl-4 space-y-0.5">
                                                <?php foreach ($nav_child['services'] as $svc): ?>
                                                    <a href="<?= BASE_URL ?>/services/<?= htmlspecialchars($nav_parent['slug']) ?>/<?= htmlspecialchars($nav_child['slug']) ?>/<?= htmlspecialchars($svc['slug']) ?>/"
                                                       class="block px-4 py-2.5 text-[14px] text-slate-600 hover:text-[#0084FF] rounded-lg hover:bg-slate-50">
                                                        <?= htmlspecialchars($svc['title']) ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- PORTFOLIO ACCORDION -->
            <div>
                <button type="button"
                        class="nav-acc w-full flex items-center justify-between px-4 py-3.5 text-[17px] font-semibold rounded-lg <?= $active === 'portfolio' ? 'text-[#0084FF] bg-blue-50' : 'text-slate-700 hover:bg-slate-50' ?>"
                        data-target="mPortfolio" aria-expanded="false" aria-controls="mPortfolio">
                    Portfolio
                    <i class="fas fa-chevron-down text-xs transition-transform"></i>
                </button>
                <div id="mPortfolio" class="hidden pl-4 mt-1 space-y-1">
                    <?php foreach ($nav_portfolio as $nav_parent): ?>
                        <div>
                            <button type="button"
                                    class="nav-acc w-full flex items-center justify-between px-4 py-3 text-base font-bold rounded-lg hover:bg-slate-50"
                                    data-target="mPort-<?= (int)$nav_parent['id'] ?>"
                                    aria-expanded="false" aria-controls="mPort-<?= (int)$nav_parent['id'] ?>"
                                    style="color:#2B3F5C;">
                                <span><?= htmlspecialchars($nav_parent['name']) ?></span>
                                <?php if (!empty($nav_parent['children'])): ?>
                                    <i class="fas fa-chevron-down text-xs"></i>
                                <?php endif; ?>
                            </button>
                            <div id="mPort-<?= (int)$nav_parent['id'] ?>" class="hidden pl-4 space-y-1">
                                <a href="<?= BASE_URL ?>/portfolio/<?= htmlspecialchars($nav_parent['slug']) ?>/"
                                   class="block px-4 py-2.5 text-[15px] font-semibold text-[#0084FF] hover:bg-slate-50 rounded-lg">
                                    View All <?= htmlspecialchars($nav_parent['name']) ?>
                                </a>
                                <?php foreach ($nav_parent['children'] as $nav_child): ?>
                                    <div>
                                        <button type="button"
                                                class="nav-acc w-full flex items-center justify-between px-4 py-2.5 text-[15px] font-bold rounded-lg hover:bg-slate-50"
                                                data-target="mPortC-<?= (int)$nav_child['id'] ?>"
                                                aria-expanded="false" aria-controls="mPortC-<?= (int)$nav_child['id'] ?>"
                                                style="color:#FF8A00;">
                                            <span><?= htmlspecialchars($nav_child['name']) ?></span>
                                            <?php if (!empty($nav_child['projects'])): ?>
                                                <i class="fas fa-chevron-down text-xs"></i>
                                            <?php endif; ?>
                                        </button>
                                        <?php if (!empty($nav_child['projects'])): ?>
                                            <div id="mPortC-<?= (int)$nav_child['id'] ?>" class="hidden pl-4 space-y-0.5">
                                                <?php foreach ($nav_child['projects'] as $proj): ?>
                                                    <a href="<?= BASE_URL ?>/portfolio/<?= htmlspecialchars($nav_parent['slug']) ?>/<?= htmlspecialchars($nav_child['slug']) ?>/<?= htmlspecialchars($proj['slug']) ?>/"
                                                       class="block px-4 py-2.5 text-[14px] text-slate-600 hover:text-[#0084FF] rounded-lg hover:bg-slate-50">
                                                        <?= htmlspecialchars($proj['title']) ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <a href="<?= BASE_URL ?>/blog/"
               class="block px-4 py-3.5 text-[17px] font-semibold rounded-lg <?= $active === 'blog' ? 'text-[#0084FF] bg-blue-50' : 'text-slate-700 hover:bg-slate-50' ?>">
                Blog
            </a>
            <a href="<?= BASE_URL ?>/contact.php"
               class="block px-4 py-3.5 text-[17px] font-semibold rounded-lg <?= $active === 'contact' ? 'text-[#0084FF] bg-blue-50' : 'text-slate-700 hover:bg-slate-50' ?>">
                Contact
            </a>

            <div class="pt-4 flex flex-col gap-3">
                <a href="<?= htmlspecialchars($whatsapp_url) ?>" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center justify-center gap-2.5 px-5 py-4 text-white font-bold text-[15px] rounded-full"
                   style="background:#25D366;">
                    <i class="fab fa-whatsapp text-lg"></i> WhatsApp
                </a>
                <a href="<?= BASE_URL ?>/contact.php"
                   class="inline-flex items-center justify-center gap-2.5 px-5 py-4 text-white font-bold text-[15px] rounded-full"
                   style="background: linear-gradient(90deg, #FF8A00 0%, #FF6B00 100%);">
                    Get Free Quote <i class="fas fa-arrow-right text-sm"></i>
                </a>
            </div>
        </nav>
    </div>
</header>

<script>
(function () {
    'use strict';

    var toggle = document.getElementById('navToggle');
    var menu   = document.getElementById('mobileNav');

    if (toggle && menu) {
        toggle.addEventListener('click', function () {
            var isOpen = !menu.classList.contains('hidden');
            menu.classList.toggle('hidden');
            toggle.setAttribute('aria-expanded', String(!isOpen));
            var icon = toggle.querySelector('i');
            if (icon) icon.className = isOpen ? 'fas fa-bars text-2xl' : 'fas fa-times text-2xl';
        });
    }

    document.querySelectorAll('.nav-acc').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var target = document.getElementById(btn.dataset.target);
            if (!target) return;
            var isHidden = target.classList.contains('hidden');
            target.classList.toggle('hidden');
            btn.setAttribute('aria-expanded', String(isHidden));
            var icon = btn.querySelector('i.fa-chevron-down');
            if (icon) icon.style.transform = isHidden ? 'rotate(180deg)' : '';
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && menu && !menu.classList.contains('hidden')) {
            menu.classList.add('hidden');
            if (toggle) {
                toggle.setAttribute('aria-expanded', 'false');
                var icon = toggle.querySelector('i');
                if (icon) icon.className = 'fas fa-bars text-2xl';
            }
        }
    });

    document.addEventListener('click', function (e) {
        if (!menu || menu.classList.contains('hidden')) return;
        if (menu.contains(e.target)) return;
        if (toggle && toggle.contains(e.target)) return;
        menu.classList.add('hidden');
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
    });
})();
</script>