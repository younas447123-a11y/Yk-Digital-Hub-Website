<?php
if (!defined('BASE_URL')) { require_once __DIR__ . '/../config/config.php'; }
if (!function_exists('getSiteSetting')) { require_once __DIR__ . '/functions.php'; }
if (!function_exists('getFlash'))        { require_once __DIR__ . '/functions.php'; }

$page_title       = $page_title       ?? 'YK Digital Hub';
$page_description = $page_description ?? '';
$page_canonical   = $page_canonical   ?? (BASE_URL . ($_SERVER['REQUEST_URI'] ?? '/'));
$og_title         = $og_title         ?? $page_title;
$og_description   = $og_description   ?? $page_description;
$og_image         = $og_image         ?? '';
$robots           = $robots           ?? 'index, follow';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>

    <?php if ($page_description): ?>
        <meta name="description" content="<?= htmlspecialchars($page_description) ?>">
    <?php endif; ?>
    <link rel="canonical" href="<?= htmlspecialchars($page_canonical) ?>">
    <meta name="robots" content="<?= htmlspecialchars($robots) ?>">

    <meta property="og:title" content="<?= htmlspecialchars($og_title) ?>">
    <?php if ($og_description): ?><meta property="og:description" content="<?= htmlspecialchars($og_description) ?>"><?php endif; ?>
    <?php if ($og_image): ?><meta property="og:image" content="<?= htmlspecialchars($og_image) ?>"><?php endif; ?>
    <meta property="og:url" content="<?= htmlspecialchars($page_canonical) ?>">
    <meta property="og:type" content="website">

    <!-- Preload LCP image on homepage only -->
    <?php if (!empty($is_homepage)): ?>
        <link rel="preload" as="image" href="<?= BASE_URL ?>/assets/images/hero1.webp" fetchpriority="high">
    <?php endif; ?>

    <!-- Favicon -->
    <link rel="icon" type="image/webp" href="<?= BASE_URL ?>/uploads/components/logo.webp">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/uploads/components/logo.webp">
    <meta name="theme-color" content="#0084FF">

    <!-- Tailwind CDN (replace with compiled build later) -->
  

  <!-- Font Awesome — load async, not blocking -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"></noscript>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/tailwind.css">
    <!-- Site styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/frontend.css">

    <style>
        html, body { overflow-x: hidden; max-width: 100%; width: 100%; }
        *, *::before, *::after { box-sizing: border-box; }
        img, video, iframe, svg { max-width: 100%; height: auto; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', 'Inter Fallback', system-ui, -apple-system, sans-serif; }
        /* Fix hero typing layout shift */
.hero-type-line {
    display: block;
    min-height: 1.08em;
}
@media (max-width: 768px) {
    .hero-type-line {
        min-height: calc(1.08em * 2);
    }
}
/* Hero 1 — visible immediately, no fade-in delay (fixes mobile LCP) */
@keyframes zoomOutFirst {
    0%   { transform: scale(1.25); opacity: 1; }
    33%  { transform: scale(1.0);  opacity: 1; }
    43%  { opacity: 0; }
    100% { transform: scale(1.0);  opacity: 0; }
}
.animate-zoom-out-first {
    animation: zoomOutFirst 21s ease-in-out infinite;
}
/* Force GPU acceleration on animated elements — fixes non-composited animations */


/* Reduce motion for users who prefer it */
@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
    </style>
</head>
<body class="bg-white text-slate-800">

<?php
// ---------- Flash message display ----------
if (function_exists('getFlash')) {
    $__flash = getFlash();
    if ($__flash):
        $__type = $__flash['type'];
        $__msg  = $__flash['message'];
        $__bg   = $__type === 'success' ? '#10b981' : ($__type === 'error' ? '#ef4444' : ($__type === 'warning' ? '#f59e0b' : '#0084FF'));
        $__icon = $__type === 'success' ? 'check-circle' : ($__type === 'error' ? 'exclamation-circle' : 'info-circle');
?>
<div class="yk-flash-toast" style="position:fixed; top:110px; left:50%; transform:translateX(-50%); z-index:9999; max-width:92vw;">
    <div style="display:flex; align-items:center; gap:14px; background:<?= $__bg ?>; color:#fff; padding:16px 26px; border-radius:14px; box-shadow:0 12px 32px rgba(0,0,0,0.18); font-family:'Inter', system-ui, sans-serif; font-size:16px; font-weight:600;">
        <i class="fas fa-<?= $__icon ?>" style="font-size:20px;"></i>
        <span><?= htmlspecialchars($__msg) ?></span>
        <button type="button" onclick="this.parentElement.parentElement.remove()" style="background:none; border:none; color:#fff; margin-left:10px; cursor:pointer; font-size:22px; line-height:1; opacity:.75; padding:0; width:24px;">×</button>
    </div>
</div>
<script>
(function(){
    var t = document.querySelector('.yk-flash-toast');
    if (!t) return;
    setTimeout(function(){
        t.style.transition = 'opacity .4s, transform .4s';
        t.style.opacity = '0';
        t.style.transform = 'translateX(-50%) translateY(-14px)';
        setTimeout(function(){ t.remove(); }, 400);
    }, 5000);
})();
</script>
<?php
    endif;
}
// ---------- /Flash message ----------
?>