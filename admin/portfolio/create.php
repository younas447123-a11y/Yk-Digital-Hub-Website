<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$page_title = 'Add Portfolio Project';
$current_page = 'portfolio';

require_once __DIR__ . '/../../includes/functions.php';

// Fetch valid child categories
$stmt = $db->query("
    SELECT c.id, c.name, p.name AS parent_name
    FROM portfolio_categories c
    LEFT JOIN portfolio_categories p ON c.parent_id = p.id
    WHERE c.parent_id IS NOT NULL AND c.is_fixed = 0
    ORDER BY p.order_num, c.order_num, c.name
");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($categories)) {
    setFlash('error', 'No subcategories available. Please create a portfolio subcategory first.');
    header('Location: categories.php');
    exit;
}
$valid_cat_ids = array_column($categories, 'id');

// Field list
$fields = ['category_id','title','slug','client_name','project_type','industry','location','website_url','short_description','card_description','description','hero_title','hero_subtitle','hero_description','hero_image','hero_video_url','project_year','project_duration','team_size','platform','technologies','project_scope','client_challenge','project_goal','project_summary','cta_title','cta_description','cta_button_text','cta_button_url','featured','sort_order','status','seo_title','meta_description','focus_keyword','canonical_url','og_title','og_description','og_image','robots'];

$project = array_fill_keys($fields, '');
$project['featured'] = 0;
$project['status'] = 1;
$project['sort_order'] = 0;
$project['robots'] = 'index, follow';

$errors = [];
$action_url = BASE_URL . '/admin/portfolio/create.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
        foreach ($fields as $f) {
            $project[$f] = isset($_POST[$f]) ? trim($_POST[$f]) : '';
        }
        $project['category_id'] = (int)$project['category_id'];
        $project['featured'] = (int)$project['featured'];
        $project['status'] = (int)$project['status'];
        $project['sort_order'] = (int)$project['sort_order'];

        // Validation
        if (!in_array($project['category_id'], $valid_cat_ids)) {
            $errors[] = 'Please select a valid subcategory.';
        }
        if ($project['title'] === '') {
            $errors[] = 'Title is required.';
        }
        if ($project['slug'] === '') {
            $project['slug'] = strtolower(trim(preg_replace('/[^a-zA-Z0-9-]+/', '-', $project['title']), '-'));
        }
        if (!preg_match('/^[a-z0-9-]+$/', $project['slug'])) {
            $errors[] = 'Slug must contain only lowercase letters, numbers, and hyphens.';
        }
        if (empty($errors)) {
            $stmt = $db->prepare("SELECT id FROM portfolio_projects WHERE slug = :slug");
            $stmt->execute([':slug' => $project['slug']]);
            if ($stmt->fetch()) {
                $errors[] = 'Slug already exists. Please choose a unique slug.';
            }
        }

        if (empty($errors)) {
            try {
                $columns = implode(', ', array_map(fn($c) => "`$c`", $fields));
                $placeholders = ':' . implode(', :', $fields);
                $stmt = $db->prepare("INSERT INTO portfolio_projects ($columns) VALUES ($placeholders)");
                foreach ($fields as $f) {
                    $val = $project[$f];
                    if ($val === '') $val = null;
                    $stmt->bindValue(':' . $f, $val);
                }
                $stmt->execute();
                setFlash('success', 'Portfolio project created successfully.');
                header('Location: index.php');
                exit;
            } catch (PDOException $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

include __DIR__ . '/../../includes/admin-header.php';
include __DIR__ . '/../../includes/admin-navbar.php';
?>
<div class="service-form-container">
    <h2>Add Portfolio Project</h2>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error"><ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?php include __DIR__ . '/_form.php'; ?>
</div>
<?php include __DIR__ . '/../../includes/admin-footer.php'; ?>