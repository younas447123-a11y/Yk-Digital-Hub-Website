<?php
/**
 * Create a new service subcategory.
 * Only child categories (parent_id points to a fixed parent) can be created.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$page_title = 'Add Subcategory';
$current_page = 'service-categories';

require_once __DIR__ . '/../../includes/functions.php';

// Fetch the two fixed parent categories for dropdown
$stmt = $db->prepare("SELECT id, name FROM service_categories WHERE parent_id IS NULL AND is_fixed = 1 ORDER BY sort_order");
$stmt->execute();
$parents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If no parents exist, abort (should not happen)
if (empty($parents)) {
    setFlash('error', 'No parent categories found. Please ensure the database is seeded.');
    header('Location: categories.php');
    exit;
}

// Default parent from GET (optional)
$default_parent = isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : 0;
// Validate that the default parent is a fixed parent
$valid_parents = array_column($parents, 'id');
if ($default_parent && !in_array($default_parent, $valid_parents)) {
    $default_parent = 0;
}

// Handle form submission
$errors = [];
$form_data = [
    'parent_id' => $default_parent,
    'name' => '',
    'slug' => '',
    'short_description' => '',
    'description' => '',
    'image' => '',
    'icon' => '',
    'sort_order' => 0,
    'status' => 1
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        // Sanitize and validate inputs
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
        // Parent must be one of the fixed parents
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
        // Validate slug format
        if (!preg_match('/^[a-z0-9-]+$/', $form_data['slug'])) {
            $errors[] = 'Slug must contain only lowercase letters, numbers, and hyphens.';
        }

        // Check for duplicate slug globally
        if (empty($errors)) {
            $stmt = $db->prepare("SELECT id FROM service_categories WHERE slug = :slug AND id != :id");
            $stmt->execute([':slug' => $form_data['slug'], ':id' => 0]);
            if ($stmt->fetch()) {
                $errors[] = 'Slug already exists. Please choose a unique slug.';
            }
        }

        // Check duplicate name under same parent
        if (empty($errors)) {
            $stmt = $db->prepare("SELECT id FROM service_categories WHERE parent_id = :parent_id AND name = :name");
            $stmt->execute([':parent_id' => $form_data['parent_id'], ':name' => $form_data['name']]);
            if ($stmt->fetch()) {
                $errors[] = 'A subcategory with this name already exists under the selected parent.';
            }
        }

        // Ensure only two levels: parent_id must be a fixed parent (is_fixed=1) not a child
        // Already enforced by $valid_parents check.

        // If no errors, insert
        if (empty($errors)) {
            try {
                $stmt = $db->prepare("INSERT INTO service_categories 
                    (parent_id, name, slug, short_description, description, image, icon, sort_order, status, is_fixed)
                    VALUES (:parent_id, :name, :slug, :short_description, :description, :image, :icon, :sort_order, :status, 0)");
                $stmt->execute([
                    ':parent_id' => $form_data['parent_id'],
                    ':name' => $form_data['name'],
                    ':slug' => $form_data['slug'],
                    ':short_description' => $form_data['short_description'],
                    ':description' => $form_data['description'],
                    ':image' => $form_data['image'],
                    ':icon' => $form_data['icon'],
                    ':sort_order' => $form_data['sort_order'],
                    ':status' => $form_data['status']
                ]);
                setFlash('success', 'Subcategory created successfully.');
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
    <h2>Add Subcategory</h2>
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
            <small>Enter a relative URL or path to the image. (File uploads will be added later.)</small>
        </div>

        <div class="form-group">
            <label for="icon">Icon (CSS class)</label>
            <input type="text" name="icon" id="icon" value="<?= htmlspecialchars($form_data['icon']) ?>" placeholder="e.g., fas fa-code">
        </div>

        <div class="form-group">
            <label for="sort_order">Sort Order</label>
            <input type="number" name="sort_order" id="sort_order" value="<?= (int)$form_data['sort_order'] ?>" min="0">
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
            <a href="<?= BASE_URL ?>/admin/services/categories.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php
include __DIR__ . '/../../includes/admin-footer.php';
?>