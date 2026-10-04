<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
$page_title = 'Blog Tags';
$current_page = 'blog-tags';
require_once __DIR__ . '/../../includes/functions.php';

$tags = $db->query("
    SELECT t.*, (SELECT COUNT(*) FROM blog_post_tags pt WHERE pt.tag_id = t.id) AS usage_count
    FROM blog_tags t
    ORDER BY t.name ASC
")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../../includes/admin-header.php';
include __DIR__ . '/../../includes/admin-navbar.php';
?>
<div class="category-management">
    <div class="category-header">
        <div><h2>Blog Tags</h2><p>Tags are reusable across posts.</p></div>
        <a href="<?= BASE_URL ?>/admin/blog/tag-create.php" class="btn btn-primary">Add Tag</a>
    </div>

    <?php if (empty($tags)): ?>
        <div class="empty-state"><p>No tags yet.</p><a href="<?= BASE_URL ?>/admin/blog/tag-create.php" class="btn btn-primary">Create your first tag</a></div>
    <?php else: ?>
        <table class="category-table">
            <thead><tr><th>Name</th><th>Slug</th><th>Used In</th><th>Created</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($tags as $t): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($t['name']) ?></strong></td>
                    <td><code><?= htmlspecialchars($t['slug']) ?></code></td>
                    <td><?= (int)$t['usage_count'] ?> post(s)</td>
                    <td><?= date('M j, Y', strtotime($t['created_at'])) ?></td>
                    <td class="actions">
                        <a href="<?= BASE_URL ?>/admin/blog/tag-edit.php?id=<?= (int)$t['id'] ?>" class="btn-edit">Edit</a>
                        <a href="<?= BASE_URL ?>/admin/blog/tag-delete.php?id=<?= (int)$t['id'] ?>" class="btn-delete" onclick="return confirm('Delete this tag?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../../includes/admin-footer.php'; ?>