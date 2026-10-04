<?php
/**
 * Edit an existing service subcategory.
 * Only child categories (is_fixed=0) can be edited.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$page_title = 'Edit Subcategory';
$current_page = 'service-categories';

require_once __DIR__ . '/../../includes/functions.php';

// Validate ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    setFlash('error', 'Invalid category ID.');
    header('Location: categories.php');
    exit;
}

// Fetch the category
$stmt = $db->prepare("SELECT * FROM service_categories WHERE id = :id");
$stmt->execute([':id' => $id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$category) {
    setFlash('error', 'Category not found.');
    header('Location: categories.php');
    exit;
}

// Ensure it's a child category (is_fixed = 0) and has a parent
if ($category['is_fixed'] == 1 || $category['parent_id'] === null) {
    setFlash('error', 'Fixed parent categories cannot be edited.');
    header('Location: categories.php');
    exit;
}

// Fetch the two fixed parents for dropdown
$stmt = $db->prepare("SELECT id, name FROM service_categories WHERE parent_id IS NULL AND is_fixed = 1 ORDER BY sort_order");
$stmt->execute();
$parents = $stmt->fetchAll(PDO::FETCH_ASSOC);
$valid_parents = array_column($parents, 'id');

// Handle form submission
$errors = [];
$form_data = [
    'parent_id' => $category['parent_id'],
    'name' => $category['name'],
    'slug' => $category['slug'],
    'short_description' => $category['short_description'] ?? '',
    'description' => $category['description'] ?? '',
    'image' => $category['image'] ?? '',
    'icon' => $category['icon'] ?? '',
    'sort_order' => (int)$category['sort_order'],
    'status' => (int)$category['status']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        // Get and sanitize input
        $form_data['parent_id'] = (int)($_POST['parent_id'] ?? 0);
        $form_data['name'] = trim($_POST['name'] ?? '');
        $form_data['slug'] = trim($_POST['slug'] ?? '');
        $form_data['short_description'] = trim($_POST['short_description'] ?? '');
        $form_data['description'] = trim($_POST['description'] ?? '');
        $form_data['image'] = trim($_POST['image'] ?? '');
        $form_data['icon'] = trim($_POST['icon'] ?? '');
        $form_data['sort_order'] = (int)($_POST['sort_order'] ?? 0);
        $form_data['status'] = isset($_POST['status']) ? (int)$_POST['status'] : 1;

        // Validation
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

        // Check duplicate slug (excluding self)
        if (empty($errors)) {
            $stmt = $db->prepare("SELECT id FROM service_categories WHERE slug = :slug AND id != :id");
            $stmt->execute([':slug' => $form_data['slug'], ':id' => $id]);
            if ($stmt->fetch()) {
                $errors[] = 'Slug already exists. Please choose a unique slug.';
            }
        }

        // Check duplicate name under same parent (excluding self)
        if (empty($errors)) {
            $stmt = $db->prepare("SELECT id FROM service_categories WHERE parent_id = :parent_id AND name = :name AND id != :id");
            $stmt->execute([':parent_id' => $form_data['parent_id'], ':name' => $form_data['name'], ':id' => $id]);
            if ($stmt->fetch()) {
                $errors[] = 'A subcategory with this name already exists under the selected parent.';
            }
        }

        // Update if no errors
        if (empty($errors)) {
            try {
                $stmt = $db->prepare("UPDATE service_categories SET 
                    parent_id = :parent_id,
                    name = :name,
                    slug = :slug,
                    short_description = :short_description,
                    description = :description,
                    image = :image,
                    icon = :icon,
                    sort_order = :sort_order,
                    status = :status
                    WHERE id = :id");
                $stmt->execute([
                    ':parent_id' => $form_data['parent_id'],
                    ':name' => $form_data['name'],
                    ':slug' => $form_data['slug'],
                    ':short_description' => $form_data['short_description'],
                    ':description' => $form_data['description'],
                    ':image' => $form_data['image'],
                    ':icon' => $form_data['icon'],
                    ':sort_order' => $form_data['sort_order'],
                    ':status' => $form_data['status'],
                    ':id' => $id
                ]);
                setFlash('success', 'Subcategory updated successfully.');
                header('Location: categories.php');
                exit;
            } catch (PDOException $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Include layout
include __DIR__ . '/../../includes/admin-header.php';
include __DIR__ . '/../../includes/admin-navbar.php';
?>

<div class="category-form-container">
    <h2>Edit Subcategory</h2>
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
            <label for="short_description">Short Description</label>
            <input type="text" name="short_description" id="short_description" value="<?= htmlspecialchars($form_data['short_description']) ?>">
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" rows="4"><?= htmlspecialchars($form_data['description']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="image">Image Path</label>
            <input type="text" name="image" id="image" value="<?= htmlspecialchars($form_data['image']) ?>" placeholder="e.g., /uploads/services/example.jpg">
        </div>

        <div class="form-group">
            <label for="icon">Icon (CSS class)</label>
            <input type="text" name="icon" id="icon" value="<?= htmlspecialchars($form_data['icon']) ?>" placeholder="e.g., fas fa-code">
        </div>

        <div class="form-group">
            <label for="sort_order">Sort Order</label>
            <input type="number" name="sort_order" id="sort_order" value="<?= (int)$form_data['sort_order'] ?>" min="0">
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
            <a href="<?= BASE_URL ?>/admin/services/categories.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php
include __DIR__ . '/../../includes/admin-footer.php';
?>