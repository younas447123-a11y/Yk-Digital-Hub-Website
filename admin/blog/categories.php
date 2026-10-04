<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
$page_title = 'Blog Categories';
$current_page = 'blog-categories';
require_once __DIR__ . '/../../includes/functions.php';

// Per-category post count
$categories = $db->query("
    SELECT c.*, (SELECT COUNT(*) FROM blog_posts p WHERE p.category_id = c.id) AS post_count
    FROM blog_categories c
    ORDER BY c.sort_order ASC, c.name ASC
")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../../includes/admin-header.php';
include __DIR__ . '/../../includes/admin-navbar.php';
?>
<div class="category-management">
    <div class="category-header">
        <div><h2>Blog Categories</h2><p>Organize your blog posts into categories.</p></div>
        <a href="<?= BASE_URL ?>/admin/blog/category-create.php" class="btn btn-primary">Add Category</a>
    </div>

    <?php if (empty($categories)): ?>
        <div class="empty-state"><p>No categories yet.</p><a href="<?= BASE_URL ?>/admin/blog/category-create.php" class="btn btn-primary">Create your first category</a></div>
    <?php else: ?>
        <table class="category-table">
            <thead><tr><th>Name</th><th>Slug</th><th>Posts</th><th>Status</th><th>Sort</th><th>Created</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($categories as $c): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
                    <td><code><?= htmlspecialchars($c['slug']) ?></code></td>
                    <td><?= (int)$c['post_count'] ?></td>
                    <td><span class="badge-status <?= $c['status'] ? 'active' : 'inactive' ?>"><?= $c['status'] ? 'Active' : 'Inactive' ?></span></td>
                    <td><?= (int)$c['sort_order'] ?></td>
                    <td><?= date('M j, Y', strtotime($c['created_at'])) ?></td>
                    <td class="actions">
                        <a href="<?= BASE_URL ?>/admin/blog/category-edit.php?id=<?= (int)$c['id'] ?>" class="btn-edit">Edit</a>
                        <a href="<?= BASE_URL ?>/admin/blog/category-delete.php?id=<?= (int)$c['id'] ?>" class="btn-delete" onclick="return confirm('Delete this category?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../../includes/admin-footer.php'; ?>