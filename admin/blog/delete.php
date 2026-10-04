<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
$page_title = 'Delete Blog Post';
$current_page = 'blog';
require_once __DIR__ . '/../../includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { setFlash('error', 'Invalid post ID.'); header('Location: index.php'); exit; }

$stmt = $db->prepare("SELECT id, title FROM blog_posts WHERE id = :id");
$stmt->execute([':id' => $id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$post) { setFlash('error', 'Post not found.'); header('Location: index.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['confirm'] ?? '') === 'yes') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
    } else {
        try {
            // blog_post_tags has ON DELETE CASCADE, so tag links are removed automatically
            $db->prepare("DELETE FROM blog_posts WHERE id = :id")->execute([':id' => $id]);
            setFlash('success', 'Post deleted successfully.');
        } catch (PDOException $e) {
            setFlash('error', 'Database error while deleting.');
        }
    }
    header('Location: index.php');
    exit;
}

include __DIR__ . '/../../includes/admin-header.php';
include __DIR__ . '/../../includes/admin-navbar.php';
?>
<div class="category-delete-confirm">
    <h2>Delete Post</h2>
    <p>Are you sure you want to delete <strong><?= htmlspecialchars($post['title']) ?></strong>?</p>
    <p>This action cannot be undone.</p>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="confirm" value="yes">
        <button class="btn btn-danger">Yes, Delete</button>
        <a href="<?= BASE_URL ?>/admin/blog/index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<?php include __DIR__ . '/../../includes/admin-footer.php'; ?>