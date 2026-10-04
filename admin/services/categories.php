<?php
/**
 * Service Categories Management - List view
 * Shows the two fixed parent categories with their children.
 */

// Include config (for DB) and auth
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

// Set page variables for layout
$page_title = 'Service Categories';
$current_page = 'service-categories';

// Include flash system and helpers
require_once __DIR__ . '/../../includes/functions.php';

// Fetch fixed parent categories (is_fixed = 1)
$stmt = $db->prepare("SELECT * FROM service_categories WHERE parent_id IS NULL AND is_fixed = 1 ORDER BY sort_order, id");
$stmt->execute();
$parents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// For each parent, fetch its children (subcategories)
foreach ($parents as &$parent) {
    $stmt = $db->prepare("SELECT * FROM service_categories WHERE parent_id = :parent_id AND is_fixed = 0 ORDER BY sort_order, name");
    $stmt->execute([':parent_id' => $parent['id']]);
    $parent['children'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
unset($parent); // break reference

// Include header and navbar
include __DIR__ . '/../../includes/admin-header.php';
include __DIR__ . '/../../includes/admin-navbar.php';
?>

<div class="category-management">
    <div class="category-header">
        <h2>Service Categories</h2>
        <p>Manage service categories and subcategories. The two main parent categories are fixed and cannot be changed.</p>
    </div>

    <?php foreach ($parents as $parent): ?>
        <div class="category-parent">
            <div class="parent-header">
                <h3><?= htmlspecialchars($parent['name']) ?></h3>
                <span class="badge badge-fixed">Fixed</span>
                <span class="badge badge-status <?= $parent['status'] ? 'active' : 'inactive' ?>">
                    <?= $parent['status'] ? 'Active' : 'Inactive' ?>
                </span>
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
                                        <span class="badge badge-status <?= $child['status'] ? 'active' : 'inactive' ?>">
                                            <?= $child['status'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td><?= (int)$child['sort_order'] ?></td>
                                    <td><?= date('M j, Y', strtotime($child['created_at'])) ?></td>
                                    <td class="actions">
                                        <a href="<?= BASE_URL ?>/admin/services/category-edit.php?id=<?= (int)$child['id'] ?>" class="btn-edit">Edit</a>
                                        <a href="<?= BASE_URL ?>/admin/services/category-delete.php?id=<?= (int)$child['id'] ?>" class="btn-delete" onclick="return confirm('Delete this subcategory?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
                <div class="add-subcategory">
                    <a href="<?= BASE_URL ?>/admin/services/category-create.php?parent_id=<?= (int)$parent['id'] ?>" class="btn-add">Add Subcategory</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
include __DIR__ . '/../../includes/admin-footer.php';
?>