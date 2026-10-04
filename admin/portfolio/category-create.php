<?php
/**
 * Create a new portfolio subcategory.
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$page_title = 'Add Portfolio Subcategory';
$current_page = 'portfolio-categories';

require_once __DIR__ . '/../../includes/functions.php';

// Fetch the two fixed parent categories
$stmt = $db->prepare("SELECT id, name FROM portfolio_categories WHERE parent_id IS NULL AND is_fixed = 1 ORDER BY order_num");
$stmt->execute();
$parents = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($parents)) {
    setFlash('error', 'No parent categories found. Please ensure the fixed parents exist.');
    header('Location: categories.php');
    exit;
}

$valid_parents = array_column($parents, 'id');

// Default parent from GET
$default_parent = isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : 0;
if ($default_parent && !in_array($default_parent, $valid_parents)) {
    $default_parent = 0;
}

$errors = [];
$form_data = [
    'parent_id' => $default_parent,
    'name' => '',
    'slug' => '',
    'description' => '',
    'icon' => '',
    'order_num' => 0,
    'status' => 1
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $form_data['parent_id'] = (int)($_POST['parent_id'] ?? 0);
        $form_data['name'] = trim($_POST['name'] ?? '');
        $form_data['slug'] = trim($_POST['slug'] ?? '');
        $form_data['description'] = trim($_POST['description'] ?? '');
        $form_data['icon'] = trim($_POST['icon'] ?? '');
        $form_data['order_num'] = (int)($_POST['order_num'] ?? 0);
        $form_data['status'] = isset($_POST['status']) ? (int)$_POST['status'] : 1;

        // --- Validation ---
        if (!in_array($form_data['parent_id'], $valid_parents)) {
            $errors[] = 'Please select a valid parent category.';
        }

        if (empty($form_data['name'])) {
            $errors[] = 'Category name is required.';
        }

        // Auto-generate slug if empty
        if (empty($form_data['slug'])) {
            $form_data['slug'] = strtolower(trim(preg_replace('/[^a-zA-Z0-9-]+/', '-', $form_data['name']), '-'));
        }
        if (!preg_match('/^[a-z0-9-]+$/', $form_data['slug'])) {
            $errors[] = 'Slug must contain only lowercase letters, numbers, and hyphens.';
        }

        // Check duplicate slug
        if (empty($errors)) {
            $stmt = $db->prepare("SELECT id FROM portfolio_categories WHERE slug = :slug");
            $stmt->execute([':slug' => $form_data['slug']]);
            if ($stmt->fetch()) {
                $errors[] = 'Slug already exists. Please choose a unique slug.';
            }
        }

        // Check duplicate name under same parent
        if (empty($errors)) {
            $stmt = $db->prepare("SELECT id FROM portfolio_categories WHERE parent_id = :parent_id AND name = :name");
            $stmt->execute([':parent_id' => $form_data['parent_id'], ':name' => $form_data['name']]);
            if ($stmt->fetch()) {
                $errors[] = 'A subcategory with this name already exists under the selected parent.';
            }
        }

        if (empty($errors)) {
            try {
                $stmt = $db->prepare("INSERT INTO portfolio_categories 
                    (parent_id, name, slug, description, icon, order_num, status, is_fixed)
                    VALUES (:parent_id, :name, :slug, :description, :icon, :order_num, :status, 0)");
                $stmt->execute([
                    ':parent_id' => $form_data['parent_id'],
                    ':name' => $form_data['name'],
                    ':slug' => $form_data['slug'],
                    ':description' => $form_data['description'],
                    ':icon' => $form_data['icon'],
                    ':order_num' => $form_data['order_num'],
                    ':status' => $form_data['status']
                ]);
                setFlash('success', 'Portfolio category created successfully.');
                header('Location: categories.php');
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

<div class="category-form-container">
    <h2>Add Portfolio Subcategory</h2>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div class="form-group">
            <label for="parent_id">Parent Category *</label>
            <select name="parent_id" id="parent_id" required>
                <option value="">Select parent</option>
                <?php foreach ($parents as $p): ?>
                    <option value="<?= (int)$p['id'] ?>" <?= ($form_data['parent_id'] == $p['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="name">Name *</label>
            <input type="text" name="name" id="name" value="<?= htmlspecialchars($form_data['name']) ?>" required>
        </div>

        <div class="form-group">
            <label for="slug">Slug</label>
            <input type="text" name="slug" id="slug" value="<?= htmlspecialchars($form_data['slug']) ?>" placeholder="Auto-generated from name">
            <small>Leave blank to auto-generate. Use lowercase letters, numbers, and hyphens only.</small>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" rows="4"><?= htmlspecialchars($form_data['description']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="icon">Icon (CSS class)</label>
            <input type="text" name="icon" id="icon" value="<?= htmlspecialchars($form_data['icon']) ?>" placeholder="e.g., fas fa-tooth">
        </div>

        <div class="form-group">
            <label for="order_num">Sort Order</label>
            <input type="number" name="order_num" id="order_num" value="<?= (int)$form_data['order_num'] ?>" min="0">
            <small>Lower numbers appear first.</small>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select name="status" id="status">
                <option value="1" <?= $form_data['status'] == 1 ? 'selected' : '' ?>>Active</option>
                <option value="0" <?= $form_data['status'] == 0 ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create Subcategory</button>
            <a href="<?= BASE_URL ?>/admin/portfolio/categories.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php
include __DIR__ . '/../../includes/admin-footer.php';
?>