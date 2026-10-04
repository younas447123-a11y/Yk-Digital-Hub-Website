<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
$page_title = 'Testimonials';
$current_page = 'testimonials';
require_once __DIR__ . '/../../includes/functions.php';

$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';
$featured_filter = $_GET['featured'] ?? '';

$where = []; $params = [];
if ($search !== '') { $where[] = "(t.client_name LIKE :search OR t.company_name LIKE :search OR t.testimonial LIKE :search)"; $params[':search'] = "%$search%"; }
if ($status_filter === 'active')   $where[] = "t.status = 1";
if ($status_filter === 'inactive') $where[] = "t.status = 0";
if ($featured_filter === 'featured')     $where[] = "t.featured = 1";
if ($featured_filter === 'not-featured') $where[] = "t.featured = 0";
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $db->prepare("
    SELECT t.*, s.title AS service_title, p.title AS project_title
    FROM testimonials t
    LEFT JOIN services s ON t.service_id = s.id
    LEFT JOIN portfolio_projects p ON t.project_id = p.id
    $where_sql
    ORDER BY t.sort_order ASC, t.id DESC
");
$stmt->execute($params);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../../includes/admin-header.php';
include __DIR__ . '/../../includes/admin-navbar.php';
?>
<div class="services-index">
    <div class="index-header">
        <h2>Testimonials</h2>
        <a href="<?= BASE_URL ?>/admin/testimonials/create.php" class="btn btn-primary">Add Testimonial</a>
    </div>

    <form method="GET" class="filter-form">
        <div class="filter-row">
            <div class="filter-group"><input type="text" name="search" placeholder="Search client, company, or review..." value="<?= htmlspecialchars($search) ?>"></div>
            <div class="filter-group">
                <select name="status">
                    <option value="">All Status</option>
                    <option value="active"   <?= $status_filter === 'active'   ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="filter-group">
                <select name="featured">
                    <option value="">All Featured</option>
                    <option value="featured"     <?= $featured_filter === 'featured'     ? 'selected' : '' ?>>Featured</option>
                    <option value="not-featured" <?= $featured_filter === 'not-featured' ? 'selected' : '' ?>>Not Featured</option>
                </select>
            </div>
            <div class="filter-actions">
                <button class="btn btn-secondary">Filter</button>
                <a href="<?= BASE_URL ?>/admin/testimonials/index.php" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>

    <?php if (empty($items)): ?>
        <div class="empty-state">
            <p>No testimonials found.</p>
            <a href="<?= BASE_URL ?>/admin/testimonials/create.php" class="btn btn-primary">Create your first testimonial</a>
        </div>
    <?php else: ?>
        <table class="services-table">
            <thead><tr><th>Client</th><th>Company</th><th>Position</th><th>Rating</th><th>Service</th><th>Project</th><th>Status</th><th>Featured</th><th>Sort</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($items as $t): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($t['client_name']) ?></strong></td>
                    <td><?= htmlspecialchars($t['company_name'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($t['position'] ?? '—') ?></td>
                    <td><?= !empty($t['rating']) ? str_repeat('★', (int)$t['rating']) . str_repeat('☆', 5 - (int)$t['rating']) : '—' ?></td>
                    <td><?= htmlspecialchars($t['service_title'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($t['project_title'] ?? '—') ?></td>
                    <td><span class="badge-status <?= $t['status'] ? 'active' : 'inactive' ?>"><?= $t['status'] ? 'Active' : 'Inactive' ?></span></td>
                    <td><?= $t['featured'] ? '⭐ Yes' : '—' ?></td>
                    <td><?= (int)$t['sort_order'] ?></td>
                    <td class="actions">
                        <a href="<?= BASE_URL ?>/admin/testimonials/edit.php?id=<?= (int)$t['id'] ?>" class="btn-edit">Edit</a>
                        <a href="<?= BASE_URL ?>/admin/testimonials/delete.php?id=<?= (int)$t['id'] ?>" class="btn-delete" onclick="return confirm('Delete this testimonial?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../../includes/admin-footer.php'; ?>