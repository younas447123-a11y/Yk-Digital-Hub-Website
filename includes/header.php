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

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind config -->
    <script>
    tailwind.config = {
        corePlugins: {
            preflight: false,
        },
        theme: {
            extend: {
                colors: {
                    brand: {
                        50:  '#eff6ff',
                        100: '#dbeafe',
                        500: '#3b82f6',
                        600: '#2563eb',
                        700: '#1d4ed8',
                    }
                },
                fontFamily: {
                    sans: ['Inter', 'system-ui', '-apple-system', 'Segoe UI', 'sans-serif']
                },
                keyframes: {
                    zoomIn: {
                        '0%':   { transform: 'scale(1)',    opacity: '0' },
                        '8%':   { opacity: '1' },
                        '30%':  { transform: 'scale(1.08)', opacity: '1' },
                        '38%':  { transform: 'scale(1.1)',  opacity: '0' },
                        '100%': { transform: 'scale(1.1)',  opacity: '0' },
                    },
                    fadeUp: {
                        '0%':   { opacity: '0', transform: 'translateY(30px)' },
                        '100%': { opacity: '1', transform: 'translateY(0)' },
                    },
                    slideRight: {
                        '0%':   { opacity: '0', transform: 'translateX(-40px)' },
                        '100%': { opacity: '1', transform: 'translateX(0)' },
                    },
                    pulseGlow: {
                        '0%, 100%': { boxShadow: '0 0 0 0 rgba(37, 99, 235, 0.55)' },
                        '50%':      { boxShadow: '0 0 0 14px rgba(37, 99, 235, 0)' },
                    },
                    floatY: {
                        '0%, 100%': { transform: 'translateY(0)' },
                        '50%':      { transform: 'translateY(-12px)' },
                    },
                    dotPulse: {
                        '0%, 100%': { opacity: '0.35', transform: 'scale(1)' },
                        '50%':      { opacity: '1',    transform: 'scale(1.4)' },
                    },
                },
                animation: {
                    'zoom-in':     'zoomIn 9s ease-in-out infinite',
                    'fade-up':     'fadeUp 0.8s ease-out forwards',
                    'slide-right': 'slideRight 0.9s cubic-bezier(0.22, 1, 0.36, 1) forwards',
                    'pulse-glow':  'pulseGlow 3s ease-in-out 2s infinite',
                    'float-y':     'floatY 6s ease-in-out infinite',
                    'dot-pulse':   'dotPulse 7s ease-in-out infinite',
                },
            }
        }
    }
    </script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Blog typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">

    <!-- Site styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/frontend.css">

    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
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