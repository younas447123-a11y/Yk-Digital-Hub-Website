<?php
/**
 * Portfolio Categories Management – List view
 * Uses actual schema: id, parent_id, name, slug, description, icon, order_num, status, is_fixed, created_at, updated_at
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$page_title = 'Portfolio Categories';
$current_page = 'portfolio-categories';

require_once __DIR__ . '/../../includes/functions.php';

// --- Filters ---
$search = trim($_GET['search'] ?? '');
$parent_filter = isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : 0;
$status_filter = $_GET['status'] ?? '';

// Fetch all fixed parent categories (for dropdown)
$all_parents_stmt = $db->query("SELECT id, name FROM portfolio_categories WHERE parent_id IS NULL AND is_fixed = 1 ORDER BY order_num, id");
$all_parents = $all_parents_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch fixed parent categories (respecting parent filter)
$parent_sql = "SELECT * FROM portfolio_categories WHERE parent_id IS NULL AND is_fixed = 1";
$parent_params = [];
if ($parent_filter > 0) {
    $parent_sql .= " AND id = :parent_id";
    $parent_params[':parent_id'] = $parent_filter;
}
$parent_sql .= " ORDER BY order_num, id";
$stmt = $db->prepare($parent_sql);
$stmt->execute($parent_params);
$parents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// For each parent, fetch its children applying filters
foreach ($parents as &$parent) {
    $child_where = ["parent_id = :parent_id", "is_fixed = 0"];
    $child_params = [':parent_id' => $parent['id']];

    if ($search !== '') {
        $child_where[] = "(name LIKE :search OR slug LIKE :search)";
        $child_params[':search'] = '%' . $search . '%';
    }
    if ($status_filter === 'active') {
        $child_where[] = "status = 1";
    } elseif ($status_filter === 'inactive') {
        $child_where[] = "status = 0";
    }

    $child_sql = "SELECT * FROM portfolio_categories WHERE " . implode(' AND ', $child_where) . " ORDER BY order_num ASC, id ASC";
    $child_stmt = $db->prepare($child_sql);
    $child_stmt->execute($child_params);
    $parent['children'] = $child_stmt->fetchAll(PDO::FETCH_ASSOC);
}
unset($parent);

include __DIR__ . '/../../includes/admin-header.php';
include __DIR__ . '/../../includes/admin-navbar.php';
?>

<div class="category-management">
    <div class="category-header">
        <div>
            <h2>Portfolio Categories</h2>
            <p>Manage portfolio subcategories. The two main parent categories are fixed and cannot be changed.</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/portfolio/category-create.php" class="btn btn-primary">Add Subcategory</a>
    </div>

    <!-- Filters -->
    <form method="GET" action="" class="filter-form">
        <div class="filter-row">
            <div class="filter-group">
                <input type="text" name="search" placeholder="Search by name or slug..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="filter-group">
                <select name="parent_id">
                    <option value="0">All Parents</option>
                    <?php foreach ($all_parents as $p): ?>
                        <option value="<?= (int)$p['id'] ?>" <?= ($parent_filter == $p['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['name']) ?>
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
            <div class="filter-actions">
                <button type="submit" class="btn btn-secondary">Filter</button>
                <a href="<?= BASE_URL ?>/admin/portfolio/categories.php" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>

    <?php if (empty($parents)): ?>
        <div class="empty-state">
            <p>No parent categories found. Please ensure the fixed parents were seeded.</p>
        </div>
    <?php endif; ?>

    <?php foreach ($parents as $parent): ?>
        <div class="category-parent">
            <div class="parent-header">
                <h3><?= htmlspecialchars($parent['name']) ?></h3>
                <span class="badge badge-fixed">System / Fixed</span>
                <span class="badge badge-status <?= $parent['status'] ? 'active' : 'inactive' ?>">
                    <?= $parent['status'] ? 'Active' : 'Inactive' ?>
                </span>
                <span class="badge badge-locked"><i class="fas fa-lock"></i> Protected</span>
            </div>
            <div class="parent-children">
                <?php if (empty($parent['children'])): ?>
                    <p class="empty-state">No subcategories yet.</p>
                <?php else: ?>
                    <table class="category-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Status</th>
                                <th>Sort</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($parent['children'] as $child): ?>
                                <tr>
                                    <td><?= htmlspecialchars($child['name']) ?></td>
                                    <td><code><?= htmlspecialchars($child['slug']) ?></code></td>
                                    <td>
                                        <span class="badge-status <?= $child['status'] ? 'active' : 'inactive' ?>">
                                            <?= $child['status'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td><?= (int)$child['order_num'] ?></td>
                                    <td><?= date('M j, Y', strtotime($child['created_at'])) ?></td>
                                    <td class="actions">
                                        <a href="<?= BASE_URL ?>/admin/portfolio/category-edit.php?id=<?= (int)$child['id'] ?>" class="btn-edit">Edit</a>
                                        <a href="<?= BASE_URL ?>/admin/portfolio/category-delete.php?id=<?= (int)$child['id'] ?>" class="btn-delete" onclick="return confirm('Delete this subcategory?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
                <div class="add-subcategory">
                    <a href="<?= BASE_URL ?>/admin/portfolio/category-create.php?parent_id=<?= (int)$parent['id'] ?>" class="btn-add">Add Subcategory under <?= htmlspecialchars($parent['name']) ?></a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
include __DIR__ . '/../../includes/admin-footer.php';
?>