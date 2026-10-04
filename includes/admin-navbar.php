<?php
if (defined('YK_ADMIN_NAVBAR_RENDERED')) return;
/**
 * Admin Top Navbar – shows page title, user info, and mobile toggle.
 */
$user = currentUser(); // from auth.php
$user_name = $user ? htmlspecialchars($user['name']) : 'Admin';
$user_role = $user ? htmlspecialchars($user['role']) : '';
// Page title is expected to be set in the page content file
$page_title_display = $page_title ?? 'Dashboard';
?>
<nav class="admin-navbar">
    <div class="navbar-left">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle Sidebar">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="page-title"><?= htmlspecialchars($page_title_display) ?></h1>
    </div>
    <div class="navbar-right">
        <div class="user-profile">
            <span class="user-name"><?= $user_name ?></span>
            <span class="user-role badge <?= $user_role === 'admin' ? 'badge-admin' : 'badge-editor' ?>">
                <?= $user_role ?>
            </span>
            <span class="user-avatar"><i class="fas fa-user-circle"></i></span>
        </div>
        <a href="<?= BASE_URL ?>/admin/logout.php" class="logout-link" title="Logout">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>
</nav>