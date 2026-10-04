<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
$page_title = 'Delete Blog Category';
$current_page = 'blog-categories';
require_once __DIR__ . '/../../includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { setFlash('error', 'Invalid category ID.'); header('Location: categories.php'); exit; }

$stmt = $db->prepare("SELECT * FROM blog_categories WHERE id = :id");
$stmt->execute([':id' => $id]);
$cat = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$cat) { setFlash('error', 'Category not found.'); header('Location: categories.php'); exit; }

// Block if posts attached
$count = $db->prepare("SELECT COUNT(*) FROM blog_posts WHERE category_id = :id");
$count->execute([':id' => $id]);
$post_count = (int)$count->fetchColumn();

if ($post_count > 0) {
    setFlash('error', "This category cannot be deleted because it contains {$post_count} blog post(s).");
    header('Location: categories.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['confirm'] ?? '') === 'yes') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
    } else {
        try {
            $db->prepare("DELETE FROM blog_categories WHERE id = :id")->execute([':id' => $id]);
            setFlash('success', 'Blog category deleted successfully.');
        } catch (PDOException $e) {
            setFlash('error', 'Database error while deleting.');
        }
    }
    header('Location: categories.php'); exit;
}

include __DIR__ . '/../../includes/admin-header.php';
include __DIR__ . '/../../includes/admin-navbar.php';
?>
<div class="category-delete-confirm">
    <h2>Delete Blog Category</h2>
    <p>Are you sure you want to delete <strong><?= htmlspecialchars($cat['name']) ?></strong>?</p>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="confirm" value="yes">
        <button class="btn btn-danger">Yes, Delete</button>
        <a href="<?= BASE_URL ?>/admin/blog/categories.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<?php include __DIR__ . '/../../includes/admin-footer.php'; ?>