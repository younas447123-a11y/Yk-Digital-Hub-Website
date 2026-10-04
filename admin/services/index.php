<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();


/**
 * Admin Services List
 * Displays all services with search, filters, and actions.
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$page_title = 'Services';
$current_page = 'services';

require_once __DIR__ . '/../../includes/functions.php';

// Build filter conditions
$where = [];
$params = [];

// Search by title
$search = trim($_GET['search'] ?? '');
if (!empty($search)) {
    $where[] = "s.title LIKE :search";
    $params[':search'] = '%' . $search . '%';
}

// Category filter (subcategory ID)
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
if ($category_id > 0) {
    $where[] = "s.category_id = :category_id";
    $params[':category_id'] = $category_id;
}

// Status filter
$status_filter = $_GET['status'] ?? '';
if ($status_filter === 'active') {
    $where[] = "s.status = 1";
} elseif ($status_filter === 'inactive') {
    $where[] = "s.status = 0";
}

// Featured filter
$featured_filter = $_GET['featured'] ?? '';
if ($featured_filter === 'featured') {
    $where[] = "s.featured = 1";
} elseif ($featured_filter === 'not-featured') {
    $where[] = "s.featured = 0";
}

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Build the query
$sql = "SELECT 
            s.*,
            c.name as subcategory_name,
            c.slug as subcategory_slug,
            p.id as parent_id,
            p.name as parent_name
        FROM services s
        LEFT JOIN service_categories c ON s.category_id = c.id
        LEFT JOIN service_categories p ON c.parent_id = p.id
        $where_clause
        ORDER BY s.sort_order ASC, s.title ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all child categories for filter dropdown
$cat_stmt = $db->query("SELECT c.id, c.name, p.name as parent_name 
                         FROM service_categories c
                         LEFT JOIN service_categories p ON c.parent_id = p.id
                         WHERE c.parent_id IS NOT NULL AND c.is_fixed = 0
                         ORDER BY p.sort_order, c.sort_order, c.name");
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

// Include layout
include __DIR__ . '/../../includes/admin-header.php';
include __DIR__ . '/../../includes/admin-navbar.php';
?>

<div class="services-index">
    <div class="index-header">
        <h2>Services</h2>
        <a href="<?= BASE_URL ?>/admin/services/create.php" class="btn btn-primary">Add Service</a>
    </div>

    <!-- Filters -->
    <form method="GET" action="" class="filter-form">
        <div class="filter-row">
            <div class="filter-group">
                <input type="text" name="search" placeholder="Search services..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="filter-group">
                <select name="category_id">
                    <option value="">All Subcategories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= ($category_id == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['parent_name'] . ' → ' . $cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <select name="status">
                    <option value="">All Status</option>
                    <option value="active" <?= ($status_filter === 'active') ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($status_filter === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="filter-group">
                <select name="featured">
                    <option value="">All Featured</option>
                    <option value="featured" <?= ($featured_filter === 'featured') ? 'selected' : '' ?>>Featured</option>
                    <option value="not-featured" <?= ($featured_filter === 'not-featured') ? 'selected' : '' ?>>Not Featured</option>
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-secondary">Filter</button>
                <a href="<?= BASE_URL ?>/admin/services/index.php" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>

    <!-- Service Table -->
    <?php if (empty($services)): ?>
        <div class="empty-state">
            <p>No services found.</p>
            <a href="<?= BASE_URL ?>/admin/services/create.php" class="btn btn-primary">Create your first service</a>
        </div>
    <?php else: ?>
        <table class="admin-table services-table">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Subcategory</th>
                    <th>Parent</th>
                    <th>Status</th>
                    <th>Featured</th>
                    <th>Sort</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $svc): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($svc['title']) ?></strong></td>
                        <td><?= htmlspecialchars($svc['subcategory_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($svc['parent_name'] ?? '-') ?></td>
                        <td>
                            <span class="badge-status <?= $svc['status'] ? 'active' : 'inactive' ?>">
                                <?= $svc['status'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td><?= $svc['featured'] ? '⭐ Featured' : '-' ?></td>
                        <td><?= (int)$svc['sort_order'] ?></td>
                        <td><?= date('M j, Y', strtotime($svc['created_at'])) ?></td>
                        <td class="actions">
                            <a href="<?= BASE_URL ?>/admin/services/edit.php?id=<?= (int)$svc['id'] ?>" class="btn-edit">Edit</a>
                            <a href="<?= BASE_URL ?>/admin/services/delete.php?id=<?= (int)$svc['id'] ?>" class="btn-delete" onclick="return confirm('Delete this service?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
include __DIR__ . '/../../includes/admin-footer.php';
?>