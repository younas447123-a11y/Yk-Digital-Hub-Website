<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$page_title = 'Delete Portfolio Project';
$current_page = 'portfolio';

require_once __DIR__ . '/../../includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    setFlash('error', 'Invalid project ID.');
    header('Location: index.php');
    exit;
}

$stmt = $db->prepare("SELECT * FROM portfolio_projects WHERE id = :id");
$stmt->execute([':id' => $id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$project) {
    setFlash('error', 'Project not found.');
    header('Location: index.php');
    exit;
}

// Check related content
$related_tables = [
    'portfolio_sections' => 'service_id',   // placeholder, will override
    'portfolio_challenges' => 'project_id',
    'portfolio_solutions' => 'project_id',
    'portfolio_results' => 'project_id',
    'portfolio_features' => 'project_id',
    'portfolio_images' => 'project_id',
    'marketing_case_studies' => 'project_id',
    'portfolio_marketing_services' => 'project_id',
    'portfolio_marketing_channels' => 'project_id',
    'portfolio_marketing_results' => 'project_id',
];
// Fix sections key
$related_tables['portfolio_sections'] = 'project_id';

$has_related = false;
$related_details = [];
foreach ($related_tables as $table => $column) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM `$table` WHERE `$column` = :id");
    $stmt->execute([':id' => $id]);
    $count = $stmt->fetchColumn();
    if ($count > 0) {
        $has_related = true;
        $related_details[] = "$table ($count)";
    }
}

if ($has_related) {
    setFlash('error', 'This project cannot be deleted because it has related content: ' . implode(', ', $related_details));
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['confirm'] ?? '') === 'yes') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        header('Location: index.php');
        exit;
    }
    try {
        $stmt = $db->prepare("DELETE FROM portfolio_projects WHERE id = :id");
        $stmt->execute([':id' => $id]);
        setFlash('success', 'Portfolio project deleted successfully.');
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        setFlash('error', 'Database error: ' . $e->getMessage());
        header('Location: index.php');
        exit;
    }
}

include __DIR__ . '/../../includes/admin-header.php';
include __DIR__ . '/../../includes/admin-navbar.php';
?>
<div class="category-delete-confirm">
    <h2>Delete Portfolio Project</h2>
    <p>Are you sure you want to delete <strong><?= htmlspecialchars($project['title']) ?></strong>?</p>
    <p>This action cannot be undone.</p>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="confirm" value="yes">
        <button type="submit" class="btn btn-danger">Yes, Delete</button>
        <a href="<?= BASE_URL ?>/admin/portfolio/index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<?php include __DIR__ . '/../../includes/admin-footer.php'; ?>