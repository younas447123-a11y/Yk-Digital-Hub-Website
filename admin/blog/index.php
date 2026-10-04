<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
$page_title = 'Blog Posts';
$current_page = 'blog';
require_once __DIR__ . '/../../includes/functions.php';

$search = trim($_GET['search'] ?? '');
$cat_filter = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$status_filter = $_GET['status'] ?? '';

$where = []; $params = [];
if ($search !== '') { $where[] = "(p.title LIKE :search OR p.slug LIKE :search)"; $params[':search'] = "%$search%"; }
if ($cat_filter > 0) { $where[] = "p.category_id = :cat"; $params[':cat'] = $cat_filter; }
if (in_array($status_filter, ['draft','published','archived'], true)) { $where[] = "p.status = :status"; $params[':status'] = $status_filter; }
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $db->prepare("
    SELECT p.id, p.title, p.slug, p.status, p.featured, p.views, p.published_at, p.created_at,
           c.name AS category_name,
           u.name AS author_name
    FROM blog_posts p
    LEFT JOIN blog_categories c ON p.category_id = c.id
    LEFT JOIN users u ON p.author_id = u.id
    $where_sql
    ORDER BY p.created_at DESC
    LIMIT 100
");
$stmt->execute($params);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = $db->query("SELECT id, name FROM blog_categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../../includes/admin-header.php';
include __DIR__ . '/../../includes/admin-navbar.php';
?>
<div class="services-index">
    <div class="index-header">
        <h2>Blog Posts</h2>
        <a href="<?= BASE_URL ?>/admin/blog/create.php" class="btn btn-primary">Add Post</a>
    </div>

    <form method="GET" class="filter-form">
        <div class="filter-row">
            <div class="filter-group"><input type="text" name="search" placeholder="Search title or slug..." value="<?= htmlspecialchars($search) ?>"></div>
            <div class="filter-group">
                <select name="category_id">
                    <option value="0">All Categories</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= $cat_filter == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <select name="status">
                    <option value="">All Status</option>
                    <option value="draft"     <?= $status_filter === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= $status_filter === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="archived"  <?= $status_filter === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
            <div class="filter-actions">
                <button class="btn btn-secondary">Filter</button>
                <a href="<?= BASE_URL ?>/admin/blog/index.php" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>

    <?php if (empty($posts)): ?>
        <div class="empty-state"><p>No posts found.</p><a href="<?= BASE_URL ?>/admin/blog/create.php" class="btn btn-primary">Create your first post</a></div>
    <?php else: ?>
        <table class="services-table">
            <thead><tr><th>Title</th><th>Category</th><th>Author</th><th>Status</th><th>Published</th><th>Views</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($posts as $p): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($p['title']) ?></strong><?= $p['featured'] ? ' ⭐' : '' ?><br><small><code><?= htmlspecialchars($p['slug']) ?></code></small></td>
                    <td><?= htmlspecialchars($p['category_name'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($p['author_name'] ?? '—') ?></td>
                    <td><span class="badge-status <?= $p['status'] === 'published' ? 'active' : 'inactive' ?>"><?= ucfirst($p['status']) ?></span></td>
                    <td><?= $p['published_at'] ? date('M j, Y', strtotime($p['published_at'])) : '—' ?></td>
                    <td><?= (int)$p['views'] ?></td>
                    <td class="actions">
                        <a href="<?= BASE_URL ?>/admin/blog/edit.php?id=<?= (int)$p['id'] ?>" class="btn-edit">Edit</a>
                        <a href="<?= BASE_URL ?>/admin/blog/delete.php?id=<?= (int)$p['id'] ?>" class="btn-delete" onclick="return confirm('Delete this post?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../../includes/admin-footer.php'; ?>
