<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
$page_title = 'Delete Blog Tag';
$current_page = 'blog-tags';
require_once __DIR__ . '/../../includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { setFlash('error', 'Invalid tag ID.'); header('Location: tags.php'); exit; }

$stmt = $db->prepare("SELECT * FROM blog_tags WHERE id = :id");
$stmt->execute([':id' => $id]);
$tag = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$tag) { setFlash('error', 'Tag not found.'); header('Location: tags.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['confirm'] ?? '') === 'yes') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
    } else {
        try {
            // blog_post_tags has ON DELETE CASCADE
            $db->prepare("DELETE FROM blog_tags WHERE id = :id")->execute([':id' => $id]);
            setFlash('success', 'Blog tag deleted successfully.');
        } catch (PDOException $e) {
            setFlash('error', 'Database error while deleting.');
        }
    }
    header('Location: tags.php'); exit;
}

include __DIR__ . '/../../includes/admin-header.php';
include __DIR__ . '/../../includes/admin-navbar.php';
?>
<div class="category-delete-confirm">
    <h2>Delete Blog Tag</h2>
    <p>Are you sure you want to delete <strong><?= htmlspecialchars($tag['name']) ?></strong>?</p>
    <p>This will remove it from all posts.</p>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="confirm" value="yes">
        <button class="btn btn-danger">Yes, Delete</button>
        <a href="<?= BASE_URL ?>/admin/blog/tags.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<?php include __DIR__ . '/../../includes/admin-footer.php'; ?>