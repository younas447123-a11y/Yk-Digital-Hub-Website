<?php
/**
 * Admin Header – opening HTML, head, CSS, and body start.
 * Assumes the user is already authenticated.
 */
// Include config to get BASE_URL and other constants
require_once __DIR__ . '/../config/config.php';
// Include functions for flash messages
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' - ' : '' ?>YK Digital Hub Admin</title>
    <link rel="icon" type="image/webp" href="<?= BASE_URL ?>/uploads/components/logo.webp?v=2">
    <!-- Admin CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css?v=4">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <!-- Font Awesome (for icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="<?= BASE_URL ?>/assets/js/admin-uploader.js?v=2" defer></script>
</head>

<body>
    <!-- Flash message display (if any) -->
    <?php displayFlash(); ?>

    <div class="admin-wrapper">
        <!-- Sidebar -->
        <?php include __DIR__ . '/admin-sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="admin-main">
            <!-- Top Navbar -->
            <?php include __DIR__ . '/admin-navbar.php'; ?>
            <?php define('YK_ADMIN_NAVBAR_RENDERED', true); ?>

            <!-- Page Content -->
            <div class="admin-content">