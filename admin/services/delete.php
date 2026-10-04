<?php
/**
 * Delete a service.
 * Only POST, with CSRF. Checks for related content before deletion.
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$page_title = 'Delete Service';
$current_page = 'services';

require_once __DIR__ . '/../../includes/functions.php';

// Validate ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    setFlash('error', 'Invalid service ID.');
    header('Location: index.php');
    exit;
}

// Fetch the service
$stmt = $db->prepare("SELECT * FROM services WHERE id = :id");
$stmt->execute([':id' => $id]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$service) {
    setFlash('error', 'Service not found.');
    header('Location: index.php');
    exit;
}

// Check for related content in child tables
$related_tables = [
    'service_sections' => 'service_id',
    'service_features' => 'service_id',
    'service_process_steps' => 'service_id',
    'service_stats' => 'service_id',
    'service_technologies' => 'service_id',
    'service_faqs' => 'service_id'
];

$has_related = false;
$related_details = [];
foreach ($related_tables as $table => $column) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM $table WHERE $column = :id");
    $stmt->execute([':id' => $id]);
    $count = $stmt->fetchColumn();
    if ($count > 0) {
        $has_related = true;
        $related_details[] = "$table ($count records)";
    }
}

if ($has_related) {
    setFlash('error', 'This service cannot be deleted because it contains detailed content. Remove its related content first. (Found: ' . implode(', ', $related_details) . ')');
    header('Location: index.php');
    exit;
}

// If POST and confirmed, delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        header('Location: index.php');
        exit;
    }

    try {
        // Since we verified no related content, we can delete safely.
        // (Foreign keys will cascade, but we already blocked, so it's safe.)
        $stmt = $db->prepare("DELETE FROM services WHERE id = :id");
        $stmt->execute([':id' => $id]);
        setFlash('success', 'Service deleted successfully.');
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        setFlash('error', 'Database error: ' . $e->getMessage());
        header('Location: index.php');
        exit;
    }
}

// If not POST, show confirmation page
include __DIR__ . '/../../includes/admin-header.php';
include __DIR__ . '/../../includes/admin-navbar.php';
?>

<div class="service-delete-confirm">
    <h2>Delete Service</h2>
    <p>Are you sure you want to delete <strong><?= htmlspecialchars($service['title']) ?></strong>?</p>
    <p>This action cannot be undone.</p>
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="confirm" value="yes">
        <button type="submit" class="btn btn-danger">Yes, Delete</button>
        <a href="<?= BASE_URL ?>/admin/services/index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php
include __DIR__ . '/../../includes/admin-footer.php';
?>