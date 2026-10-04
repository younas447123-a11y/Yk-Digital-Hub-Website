<?php
/**
 * Admin Dashboard – main landing page after login.
 * Displays welcome message, statistics, recent activity, and quick actions.
 */

// Ensure database connection is loaded (config.php includes database.php)
require_once __DIR__ . '/../config/config.php';

// Require authentication
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

// Include functions for flash (if needed)
require_once __DIR__ . '/../includes/functions.php';

// Set page variables
$page_title = 'Dashboard';
$current_page = 'dashboard';

// Get user data
$user = currentUser();
$user_name = $user ? htmlspecialchars($user['name']) : 'Admin';

// ----- Check if required tables exist -----
$tables_missing = false;
$missing_tables = [];

try {
    $stmt = $db->query("SHOW TABLES LIKE 'services'");
    if ($stmt->rowCount() == 0) $missing_tables[] = 'services';
    
    $stmt = $db->query("SHOW TABLES LIKE 'portfolio_projects'");
    if ($stmt->rowCount() == 0) $missing_tables[] = 'portfolio_projects';
    
    $stmt = $db->query("SHOW TABLES LIKE 'blog_posts'");
    if ($stmt->rowCount() == 0) $missing_tables[] = 'blog_posts';
    
    $stmt = $db->query("SHOW TABLES LIKE 'testimonials'");
    if ($stmt->rowCount() == 0) $missing_tables[] = 'testimonials';
    
    $stmt = $db->query("SHOW TABLES LIKE 'contact_messages'");
    if ($stmt->rowCount() == 0) $missing_tables[] = 'contact_messages';
    
    if (!empty($missing_tables)) {
        $tables_missing = true;
    }
} catch (PDOException $e) {
    // In case the database itself doesn't exist
    $tables_missing = true;
    $missing_tables = ['database connection'];
}

// ----- Initialize stats with default values -----
$total_services = 0;
$total_projects = 0;
$total_posts = 0;
$total_testimonials = 0;
$new_messages = 0;
$recent_services = [];
$recent_projects = [];
$recent_posts = [];
$recent_messages = [];

if (!$tables_missing) {
    try {
        // Total Services (active: status=1)
        $stmt = $db->query("SELECT COUNT(*) FROM services WHERE status = 1");
        $total_services = $stmt->fetchColumn();

        // Total Portfolio Projects (active: status=1)
        $stmt = $db->query("SELECT COUNT(*) FROM portfolio_projects WHERE status = 1");
        $total_projects = $stmt->fetchColumn();

        // Total Blog Posts (published)
        $stmt = $db->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'published'");
        $total_posts = $stmt->fetchColumn();

        // Total Testimonials (active: status=1)
        $stmt = $db->query("SELECT COUNT(*) FROM testimonials WHERE status = 1");
        $total_testimonials = $stmt->fetchColumn();

        // New Contact Messages (status='new')
        $stmt = $db->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'");
        $new_messages = $stmt->fetchColumn();

        // ----- Recent Activity -----
        // Recent Services
        $stmt = $db->query("SELECT id, title, created_at FROM services WHERE status = 1 ORDER BY created_at DESC LIMIT 5");
        $recent_services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Recent Portfolio Projects
        $stmt = $db->query("SELECT id, title, created_at FROM portfolio_projects WHERE status = 1 ORDER BY created_at DESC LIMIT 5");
        $recent_projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Recent Blog Posts
        $stmt = $db->query("SELECT id, title, created_at FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC LIMIT 5");
        $recent_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Recent Contact Messages
        $stmt = $db->query("SELECT id, name, email, created_at FROM contact_messages ORDER BY created_at DESC LIMIT 5");
        $recent_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // If a specific query fails, treat as missing tables
        $tables_missing = true;
        $missing_tables = ['some tables (query error)'];
    }
}

// Include admin layout header
include __DIR__ . '/../includes/admin-header.php';
include __DIR__ . '/../includes/admin-navbar.php';
?>

<div class="dashboard-content">
    <!-- Welcome section -->
    <div class="welcome-section">
        <h2>Welcome back, <?= $user_name ?>!</h2>
        <p>Manage your YK Digital Hub website from one place.</p>
    </div>

    <?php if ($tables_missing): ?>
        <div class="alert alert-warning" style="background:#fef3c7; border-left:4px solid #f59e0b; padding:16px; border-radius:8px; margin-bottom:24px;">
            <strong>⚠️ Database tables are missing.</strong><br>
            Please import the database schema (<code>database/schema.sql</code>) to enable full dashboard functionality.
            <br><small>Missing: <?= implode(', ', array_map('htmlspecialchars', $missing_tables)) ?></small>
        </div>
    <?php endif; ?>

    <!-- Statistics cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-concierge-bell"></i></div>
            <div class="stat-info">
                <h3><?= $total_services ?></h3>
                <p>Services</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-briefcase"></i></div>
            <div class="stat-info">
                <h3><?= $total_projects ?></h3>
                <p>Portfolio Projects</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-newspaper"></i></div>
            <div class="stat-info">
                <h3><?= $total_posts ?></h3>
                <p>Blog Posts</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-star"></i></div>
            <div class="stat-info">
                <h3><?= $total_testimonials ?></h3>
                <p>Testimonials</p>
            </div>
        </div>
        <div class="stat-card highlight">
            <div class="stat-icon"><i class="fas fa-envelope"></i></div>
            <div class="stat-info">
                <h3><?= $new_messages ?></h3>
                <p>New Messages</p>
            </div>
        </div>
    </div>

    <!-- Recent Activity & Quick Actions -->
    <div class="dashboard-grid">
        <div class="recent-activity">
            <h3>Recent Activity</h3>
            <div class="activity-tabs">
                <div class="tab-content">
                    <h4>Recent Services</h4>
                    <ul>
                        <?php if (empty($recent_services)): ?>
                            <li class="empty">No services yet.</li>
                        <?php else: ?>
                            <?php foreach ($recent_services as $item): ?>
                                <li>
                                    <span class="item-title"><?= htmlspecialchars($item['title']) ?></span>
                                    <span class="item-date"><?= date('M j, Y', strtotime($item['created_at'])) ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="tab-content">
                    <h4>Recent Projects</h4>
                    <ul>
                        <?php if (empty($recent_projects)): ?>
                            <li class="empty">No projects yet.</li>
                        <?php else: ?>
                            <?php foreach ($recent_projects as $item): ?>
                                <li>
                                    <span class="item-title"><?= htmlspecialchars($item['title']) ?></span>
                                    <span class="item-date"><?= date('M j, Y', strtotime($item['created_at'])) ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="tab-content">
                    <h4>Recent Blog Posts</h4>
                    <ul>
                        <?php if (empty($recent_posts)): ?>
                            <li class="empty">No blog posts yet.</li>
                        <?php else: ?>
                            <?php foreach ($recent_posts as $item): ?>
                                <li>
                                    <span class="item-title"><?= htmlspecialchars($item['title']) ?></span>
                                    <span class="item-date"><?= date('M j, Y', strtotime($item['created_at'])) ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="tab-content">
                    <h4>Recent Messages</h4>
                    <ul>
                        <?php if (empty($recent_messages)): ?>
                            <li class="empty">No messages yet.</li>
                        <?php else: ?>
                            <?php foreach ($recent_messages as $item): ?>
                                <li>
                                    <span class="item-title"><?= htmlspecialchars($item['name']) ?> (<?= htmlspecialchars($item['email']) ?>)</span>
                                    <span class="item-date"><?= date('M j, Y', strtotime($item['created_at'])) ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="quick-actions">
            <h3>Quick Actions</h3>
            <div class="action-buttons">
                <a href="<?= BASE_URL ?>/admin/services/create.php" class="action-btn"><i class="fas fa-plus"></i> Add Service</a>
                <a href="<?= BASE_URL ?>/admin/portfolio/create.php" class="action-btn"><i class="fas fa-plus"></i> Add Project</a>
                <a href="<?= BASE_URL ?>/admin/blog/create.php" class="action-btn"><i class="fas fa-plus"></i> Write Blog Post</a>
                <a href="<?= BASE_URL ?>/admin/testimonials/create.php" class="action-btn"><i class="fas fa-plus"></i> Add Testimonial</a>
            </div>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../includes/admin-footer.php';
?>