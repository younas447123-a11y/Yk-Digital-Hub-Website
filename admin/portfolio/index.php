<?php
/**
 * Portfolio Projects List with search/filters.
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$page_title = 'Portfolio Projects';
$current_page = 'portfolio';

require_once __DIR__ . '/../../includes/functions.php';

// ---- Filters ----
$search = trim($_GET['search'] ?? '');
$parent_filter = isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : 0;
$category_filter = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$status_filter = $_GET['status'] ?? '';
$featured_filter = $_GET['featured'] ?? '';

// ---- Build WHERE ----
$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(p.title LIKE :search OR p.slug LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}
if ($category_filter > 0) {
    $where[] = "p.category_id = :category_id";
    $params[':category_id'] = $category_filter;
}
if ($parent_filter > 0) {
    $where[] = "parent.id = :parent_id";
    $params[':parent_id'] = $parent_filter;
}
if ($status_filter === 'active') {
    $where[] = "p.status = 1";
} elseif ($status_filter === 'inactive') {
    $where[] = "p.status = 0";
}
if ($featured_filter === 'featured') {
    $where[] = "p.featured = 1";
} elseif ($featured_filter === 'not-featured') {
    $where[] = "p.featured = 0";
}

$where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT p.*, 
               child.name AS category_name, 
               child.slug AS category_slug,
               parent.id AS parent_id, 
               parent.name AS parent_name
        FROM portfolio_projects p
        LEFT JOIN portfolio_categories child ON p.category_id = child.id
        LEFT JOIN portfolio_categories parent ON child.parent_id = parent.id
        $where_sql
        ORDER BY p.sort_order ASC, p.id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Dropdown data
$all_parents = $db->query("SELECT id, name FROM portfolio_categories WHERE parent_id IS NULL AND is_fixed = 1 ORDER BY order_num, id")->fetchAll(PDO::FETCH_ASSOC);
$all_children = $db->query("SELECT c.id, c.name, p.name AS parent_name FROM portfolio_categories c LEFT JOIN portfolio_categories p ON c.parent_id = p.id WHERE c.parent_id IS NOT NULL AND c.is_fixed = 0 ORDER BY p.order_num, c.order_num, c.name")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../../includes/admin-header.php';
include __DIR__ . '/../../includes/admin-navbar.php';
?>

<div class="services-index">
    <div class="index-header">
        <h2>Portfolio Projects</h2>
        <a href="<?= BASE_URL ?>/admin/portfolio/create.php" class="btn btn-primary">Add Project</a>
    </div>

    <form method="GET" action="" class="filter-form">
        <div class="filter-row">
            <div class="filter-group">
                <input type="text" name="search" placeholder="Search title or slug..." value="<?= htmlspecialchars($search) ?>">
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
                <select name="category_id">
                    <option value="0">All Subcategories</option>
                    <?php foreach ($all_children as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= ($category_filter == $c['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['parent_name'] . ' → ' . $c['name']) ?>
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
                <a href="<?= BASE_URL ?>/admin/portfolio/index.php" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>

    <?php if (empty($projects)): ?>
        <div class="empty-state">
            <p>No portfolio projects found.</p>
            <a href="<?= BASE_URL ?>/admin/portfolio/create.php" class="btn btn-primary">Create your first project</a>
        </div>
    <?php else: ?>
        <table class="services-table">
            <thead>
                <tr>
                    <th>Title</th>
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
                <?php foreach ($projects as $p): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($p['title']) ?></strong></td>
                        <td><?= htmlspecialchars($p['category_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($p['parent_name'] ?? '-') ?></td>
                        <td>
                            <span class="badge-status <?= $p['status'] ? 'active' : 'inactive' ?>">
                                <?= $p['status'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td><?= $p['featured'] ? '⭐ Yes' : '-' ?></td>
                        <td><?= (int)$p['sort_order'] ?></td>
                        <td><?= date('M j, Y', strtotime($p['created_at'])) ?></td>
                        <td class="actions">
                            <a href="<?= BASE_URL ?>/admin/portfolio/edit.php?id=<?= (int)$p['id'] ?>" class="btn-edit">Edit</a>
                            <a href="<?= BASE_URL ?>/admin/portfolio/delete.php?id=<?= (int)$p['id'] ?>" class="btn-delete" onclick="return confirm('Delete this project?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<td class="actions">
    <a href="<?= BASE_URL ?>/admin/portfolio/edit.php?id=<?= (int)$p['id'] ?>" class="btn-edit">Edit</a>
    <a href="<?= BASE_URL ?>/admin/portfolio/edit.php?id=<?= (int)$p['id'] ?>&tab=sections" class="btn-edit">Content</a>
    <a href="<?= BASE_URL ?>/admin/portfolio/delete.php?id=<?= (int)$p['id'] ?>" class="btn-delete" onclick="return confirm('Delete this project?')">Delete</a>
</td>
<?php
include __DIR__ . '/../../includes/admin-footer.php';
?>