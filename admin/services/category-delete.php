<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$page_title = 'Delete Service Category';
$current_page = 'service-categories';
require_once __DIR__ . '/../../includes/functions.php';

// ---- Validate ID ----
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    setFlash('error', 'Invalid category ID.');
    header('Location: categories.php');
    exit;
}

// ---- Fetch category ----
$stmt = $db->prepare("SELECT * FROM service_categories WHERE id = :id");
$stmt->execute([':id' => $id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    setFlash('error', 'Category not found.');
    header('Location: categories.php');
    exit;
}

// ---- Block fixed parents ----
if ((int)$category['is_fixed'] === 1 || $category['parent_id'] === null) {
    setFlash('error', 'Fixed parent categories cannot be deleted.');
    header('Location: categories.php');
    exit;
}

// ---- Count attached services ----
$count_stmt = $db->prepare("SELECT COUNT(*) FROM services WHERE category_id = :cid");
$count_stmt->execute([':cid' => $id]);
$service_count = (int)$count_stmt->fetchColumn();

// ---- Count attached child categories (in case of unexpected hierarchy) ----
$child_stmt = $db->prepare("SELECT COUNT(*) FROM service_categories WHERE parent_id = :cid");
$child_stmt->execute([':cid' => $id]);
$child_count = (int)$child_stmt->fetchColumn();

// ---- Handle POST (actual delete) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['confirm'] ?? '') === 'yes') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        header('Location: categories.php');
        exit;
    }

    if ($service_count > 0) {
        setFlash('error', "This category has {$service_count} service(s) attached. Move or delete them first.");
        header('Location: categories.php');
        exit;
    }

    if ($child_count > 0) {
        setFlash('error', "This category has {$child_count} subcategories. Delete them first.");
        header('Location: categories.php');
        exit;
    }

    try {
        $del = $db->prepare("DELETE FROM service_categories WHERE id = :id AND is_fixed = 0 AND parent_id IS NOT NULL");
        $del->execute([':id' => $id]);
        setFlash('success', 'Category deleted successfully.');
    } catch (PDOException $e) {
        error_log('[YK Category Delete] ' . $e->getMessage());
        setFlash('error', 'Database error: ' . $e->getMessage());
    }
    header('Location: categories.php');
    exit;
}

// ---- Render confirmation page ----
include __DIR__ . '/../../includes/admin-header.php';
include __DIR__ . '/../../includes/admin-navbar.php';
?>

<div class="category-delete-confirm" style="max-width: 560px; margin: 40px auto; background: #fff; padding: 32px; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); text-align: center;">

    <div style="font-size: 48px; margin-bottom: 12px;">⚠️</div>

    <h2 style="font-size: 22px; margin: 0 0 12px; color: #0f172a;">
        Delete Category?
    </h2>

    <p style="color: #475569; font-size: 15px; margin: 0 0 8px;">
        You are about to delete:
    </p>

    <p style="font-size: 18px; font-weight: 700; color: #2B3F5C; margin: 0 0 20px;">
        <?= htmlspecialchars($category['name']) ?>
    </p>

    <?php if ($service_count > 0): ?>
        <div style="background: #fef3c7; border-left: 4px solid #f59e0b; color: #92400e; padding: 14px 18px; border-radius: 8px; text-align: left; margin-bottom: 20px; font-size: 14px;">
            <strong>⚠️ This category cannot be deleted.</strong><br>
            It has <strong><?= $service_count ?> service(s)</strong> attached to it.
            Move or delete those services first, then try again.
        </div>

        <a href="<?= BASE_URL ?>/admin/services/categories.php"
           class="btn btn-secondary"
           style="display: inline-block; padding: 10px 24px; border-radius: 8px; background: #e2e8f0; color: #0f172a; text-decoration: none; font-weight: 600;">
            Back to Categories
        </a>

    <?php elseif ($child_count > 0): ?>
        <div style="background: #fef3c7; border-left: 4px solid #f59e0b; color: #92400e; padding: 14px 18px; border-radius: 8px; text-align: left; margin-bottom: 20px; font-size: 14px;">
            <strong>⚠️ This category has <?= $child_count ?> subcategory(ies).</strong><br>
            Delete those first, then try again.
        </div>

        <a href="<?= BASE_URL ?>/admin/services/categories.php"
           class="btn btn-secondary"
           style="display: inline-block; padding: 10px 24px; border-radius: 8px; background: #e2e8f0; color: #0f172a; text-decoration: none; font-weight: 600;">
            Back to Categories
        </a>

    <?php else: ?>
        <p style="color: #ef4444; font-size: 14px; margin: 0 0 24px;">
            This action cannot be undone.
        </p>

        <form method="POST" style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="confirm" value="yes">
            <button type="submit"
                    class="btn btn-danger"
                    style="padding: 12px 28px; border-radius: 8px; background: #dc2626; color: #fff; border: none; font-weight: 700; cursor: pointer; font-size: 15px;">
                Yes, Delete
            </button>
            <a href="<?= BASE_URL ?>/admin/services/categories.php"
               class="btn btn-secondary"
               style="padding: 12px 28px; border-radius: 8px; background: #e2e8f0; color: #0f172a; text-decoration: none; font-weight: 600; font-size: 15px;">
                Cancel
            </a>
        </form>
    <?php endif; ?>

</div>

<?php
include __DIR__ . '/../../includes/admin-footer.php';
?>