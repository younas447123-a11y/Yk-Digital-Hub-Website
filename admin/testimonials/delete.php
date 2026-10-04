<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
$page_title = 'Delete Testimonial';
$current_page = 'testimonials';
require_once __DIR__ . '/../../includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { setFlash('error', 'Invalid testimonial ID.'); header('Location: index.php'); exit; }

$stmt = $db->prepare("SELECT id, client_name FROM testimonials WHERE id = :id");
$stmt->execute([':id' => $id]);
$t = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$t) { setFlash('error', 'Testimonial not found.'); header('Location: index.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['confirm'] ?? '') === 'yes') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
    } else {
        try {
            $db->prepare("DELETE FROM testimonials WHERE id = :id")->execute([':id' => $id]);
            setFlash('success', 'Testimonial deleted successfully.');
        } catch (PDOException $e) {
            setFlash('error', 'Database error while deleting.');
        }
    }
    header('Location: index.php'); exit;
}

include __DIR__ . '/../../includes/admin-header.php';
include __DIR__ . '/../../includes/admin-navbar.php';
?>
<div class="category-delete-confirm">
    <h2>Delete Testimonial</h2>
    <p>Are you sure you want to delete the testimonial from <strong><?= htmlspecialchars($t['client_name']) ?></strong>?</p>
    <p>This action cannot be undone.</p>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="confirm" value="yes">
        <button class="btn btn-danger">Yes, Delete</button>
        <a href="<?= BASE_URL ?>/admin/testimonials/index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<?php include __DIR__ . '/../../includes/admin-footer.php'; ?>