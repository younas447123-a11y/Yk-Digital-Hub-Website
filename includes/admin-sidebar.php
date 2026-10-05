<?php
/**
 * Admin Sidebar – navigation menu.
 * Highlights the current page using $current_page variable.
 */
// Determine current page for active state
$current_page = $current_page ?? 'dashboard';
?>
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
        <a href="<?= BASE_URL ?>/admin/index.php">
            <img src="<?= BASE_URL ?>/uploads/components/logo.png" alt="YK Digital Hub logo" width="52" height="52" class="brand-logo">
            <span class="brand-text">YK Digital Hub</span>
        </a>
    </div>
    <nav class="sidebar-nav">
        <ul>
            <li class="<?= $current_page === 'dashboard' ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/admin/index.php"><i class="fas fa-th-large"></i> Dashboard</a>
            </li>
            <li class="nav-header">Services</li>
            <li class="<?= $current_page === 'services' ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/admin/services/index.php"><i class="fas fa-concierge-bell"></i> All Services</a>
            </li>
            <li class="<?= $current_page === 'service-categories' ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/admin/services/categories.php"><i class="fas fa-tags"></i> Categories</a>
            </li>
            <li class="nav-header">Portfolio</li>
            <li class="<?= $current_page === 'portfolio' ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/admin/portfolio/index.php"><i class="fas fa-briefcase"></i> All Projects</a>
            </li>
            <li class="<?= $current_page === 'portfolio-categories' ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/admin/portfolio/categories.php"><i class="fas fa-tags"></i> Categories</a>
            </li>
            <li class="nav-header">Blog</li>
            <li class="<?= $current_page === 'blog' ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/admin/blog/index.php"><i class="fas fa-newspaper"></i> All Posts</a>
            </li>
            <li class="<?= $current_page === 'blog-categories' ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/admin/blog/categories.php"><i class="fas fa-tags"></i> Categories</a>
            </li>
            <li class="<?= $current_page === 'blog-tags' ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/admin/blog/tags.php"><i class="fas fa-tag"></i> Tags</a>
            </li>
            <li class="nav-header">Other</li>
            <li class="<?= $current_page === 'testimonials' ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/admin/testimonials/index.php"><i class="fas fa-star"></i> Testimonials</a>
            </li>
            <li class="<?= $current_page === 'media' ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/admin/media/index.php"><i class="fas fa-images"></i> Media Library</a>
            </li>
            <li class="<?= $current_page === 'seo' ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/admin/seo/index.php"><i class="fas fa-search"></i> SEO</a>
            </li>
            <li class="<?= $current_page === 'contact' ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/admin/contact/index.php"><i class="fas fa-envelope"></i> Contact Messages</a>
            </li>
            <li class="<?= $current_page === 'settings' ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/admin/settings/index.php"><i class="fas fa-cog"></i> Settings</a>
            </li>
            <li class="nav-divider"></li>
            <li>
                <a href="<?= BASE_URL ?>/admin/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </nav>
</aside>
<!-- Mobile overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<li class="<?= $current_page === 'service-categories' ? 'active' : '' ?>">
    <a href="<?= BASE_URL ?>/admin/services/categories.php"><i class="fas fa-tags"></i> Categories</a>
</li>