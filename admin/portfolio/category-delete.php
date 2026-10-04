<?php
/**
 * Delete a portfolio subcategory.
 * Only child categories (is_fixed=0) can be deleted.
 * Prevents deletion if portfolio projects are attached.
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$page_title = 'Delete Portfolio Subcategory';
$current_page = 'portfolio-categories';

require_once __DIR__ . '/../../includes/functions.php';

// Validate ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    setFlash('error', 'Invalid category ID.');
    header('Location: categories.php');
    exit;
}

// Fetch the category
$stmt = $db->prepare("SELECT * FROM portfolio_categories WHERE id = :id");
$stmt->execute([':id' => $id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$category) {
    setFlash('error', 'Category not found.');
    header('Location: categories.php');
    exit;
}

// Server-side protection: fixed parents cannot be deleted
if ((int)$category['is_fixed'] === 1 || $category['parent_id'] === null) {
    setFlash('error', 'Fixed portfolio categories cannot be modified or deleted.');
    header('Location: categories.php');
    exit;
}

// Check if any portfolio projects are attached to this category
$stmt = $db->prepare("SELECT COUNT(*) FROM portfolio_projects WHERE category_id = :category_id");
$stmt->execute([':category_id' => $id]);
$project_count = $stmt->fetchColumn();

if ($project_count > 0) {
    setFlash('error', "This category cannot be deleted because {$project_count} portfolio project(s) are assigned to it.");
    header('Location: categories.php');
    exit;
}

// If POST and confirmed, delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        header('Location: categories.php');
        exit;
    }

    try {
        // Double-check at delete time
        $stmt = $db->prepare("DELETE FROM portfolio_categories WHERE id = :id AND is_fixed = 0 AND parent_id IS NOT NULL");
        $stmt->execute([':id' => $id]);
        setFlash('success', 'Portfolio category deleted successfully.');
        header('Location: categories.php');
        exit;
    } catch (PDOException $e) {
        setFlash('error', 'Database error: ' . $e->getMessage());
        header('Location: categories.php');
        exit;
    }
}

include __DIR__ . '/../../includes/admin-header.php';
include __DIR__ . '/../../includes/admin-navbar.php';
?>

<div class="category-delete-confirm">
    <h2>Delete Portfolio Subcategory</h2>
    <p>Are you sure you want to delete the subcategory <strong><?= htmlspecialchars($category['name']) ?></strong>?</p>
    <p>This action cannot be undone.</p>
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="confirm" value="yes">
        <button type="submit" class="btn btn-danger">Yes, Delete</button>
        <a href="<?= BASE_URL ?>/admin/portfolio/categories.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php
include __DIR__ . '/../../includes/admin-footer.php';
?>