<?php
/**
 * Edit an existing portfolio subcategory.
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$page_title = 'Edit Portfolio Subcategory';
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

// Server-side protection: fixed parents cannot be edited
if ((int)$category['is_fixed'] === 1 || $category['parent_id'] === null) {
    setFlash('error', 'Fixed portfolio categories cannot be modified.');
    header('Location: categories.php');
    exit;
}

// Fetch fixed parents for dropdown
$stmt = $db->prepare("SELECT id, name FROM portfolio_categories WHERE parent_id IS NULL AND is_fixed = 1 ORDER BY order_num");
$stmt->execute();
$parents = $stmt->fetchAll(PDO::FETCH_ASSOC);
$valid_parents = array_column($parents, 'id');

$errors = [];
$form_data = [
    'parent_id' => $category['parent_id'],
    'name' => $category['name'],
    'slug' => $category['slug'],
    'description' => $category['description'] ?? '',
    'icon' => $category['icon'] ?? '',
    'order_num' => (int)$category['order_num'],
    'status' => (int)$category['status']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
        $form_data['parent_id'] = (int)($_POST['parent_id'] ?? 0);
        $form_data['name'] = trim($_POST['name'] ?? '');
        $form_data['slug'] = trim($_POST['slug'] ?? '');
        $form_data['description'] = trim($_POST['description'] ?? '');
        $form_data['icon'] = trim($_POST['icon'] ?? '');
        $form_data['order_num'] = (int)($_POST['order_num'] ?? 0);
        $form_data['status'] = isset($_POST['status']) ? (int)$_POST['status'] : 1;

        if (!in_array($form_data['parent_id'], $valid_parents)) {
            $errors[] = 'Please select a valid parent category.';
        }
        if (empty($form_data['name'])) {
            $errors[] = 'Category name is required.';
        }

        if (empty($form_data['slug'])) {
            $form_data['slug'] = strtolower(trim(preg_replace('/[^a-zA-Z0-9-]+/', '-', $form_data['name']), '-'));
        }
        if (!preg_match('/^[a-z0-9-]+$/', $form_data['slug'])) {
            $errors[] = 'Slug must contain only lowercase letters, numbers, and hyphens.';
        }

        // Duplicate slug check (excluding self)
        if (empty($errors)) {
            $stmt = $db->prepare("SELECT id FROM portfolio_categories WHERE slug = :slug AND id != :id");
            $stmt->execute([':slug' => $form_data['slug'], ':id' => $id]);
            if ($stmt->fetch()) {
                $errors[] = 'Slug already exists. Please choose a unique slug.';
            }
        }

        // Duplicate name under same parent (excluding self)
        if (empty($errors)) {
            $stmt = $db->prepare("SELECT id FROM portfolio_categories WHERE parent_id = :parent_id AND name = :name AND id != :id");
            $stmt->execute([':parent_id' => $form_data['parent_id'], ':name' => $form_data['name'], ':id' => $id]);
            if ($stmt->fetch()) {
                $errors[] = 'A subcategory with this name already exists under the selected parent.';
            }
        }

        if (empty($errors)) {
            try {
                $stmt = $db->prepare("UPDATE portfolio_categories SET 
                    parent_id = :parent_id,
                    name = :name,
                    slug = :slug,
                    description = :description,
                    icon = :icon,
                    order_num = :order_num,
                    status = :status
                    WHERE id = :id AND is_fixed = 0");
                $stmt->execute([
                    ':parent_id' => $form_data['parent_id'],
                    ':name' => $form_data['name'],
                    ':slug' => $form_data['slug'],
                    ':description' => $form_data['description'],
                    ':icon' => $form_data['icon'],
                    ':order_num' => $form_data['order_num'],
                    ':status' => $form_data['status'],
                    ':id' => $id
                ]);
                setFlash('success', 'Portfolio category updated successfully.');
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
    <h2>Edit Portfolio Subcategory</h2>
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
            <input type="text" name="slug" id="slug" value="<?= htmlspecialchars($form_data['slug']) ?>">
            <small>Leave blank to auto-generate from name.</small>
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
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select name="status" id="status">
                <option value="1" <?= $form_data['status'] == 1 ? 'selected' : '' ?>>Active</option>
                <option value="0" <?= $form_data['status'] == 0 ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Subcategory</button>
            <a href="<?= BASE_URL ?>/admin/portfolio/categories.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php
include __DIR__ . '/../../includes/admin-footer.php';
?>