<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
$page_title = 'Edit Blog Category';
$current_page = 'blog-categories';
require_once __DIR__ . '/../../includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { setFlash('error', 'Invalid category ID.'); header('Location: categories.php'); exit; }

$stmt = $db->prepare("SELECT * FROM blog_categories WHERE id = :id");
$stmt->execute([':id' => $id]);
$form = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$form) { setFlash('error', 'Category not found.'); header('Location: categories.php'); exit; }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
        foreach (['name','slug','description','image'] as $f) $form[$f] = trim($_POST[$f] ?? '');
        $form['sort_order'] = (int)($_POST['sort_order'] ?? 0);
        $form['status'] = (int)($_POST['status'] ?? 1);

        if ($form['name'] === '') $errors[] = 'Name is required.';
        if ($form['slug'] === '') $form['slug'] = strtolower(trim(preg_replace('/[^a-zA-Z0-9-]+/', '-', $form['name']), '-'));
        if (!preg_match('/^[a-z0-9-]+$/', $form['slug'])) $errors[] = 'Slug must contain only lowercase letters, numbers, and hyphens.';

        if (empty($errors)) {
            $s = $db->prepare("SELECT id FROM blog_categories WHERE slug = :slug AND id != :id");
            $s->execute([':slug' => $form['slug'], ':id' => $id]);
            if ($s->fetch()) $errors[] = 'Slug already exists.';
        }

        if (empty($errors)) {
            try {
                $stmt = $db->prepare("UPDATE blog_categories SET name = :name, slug = :slug, description = :description, image = :image, sort_order = :sort_order, status = :status WHERE id = :id");
                $stmt->execute([
                    ':name' => $form['name'], ':slug' => $form['slug'],
                    ':description' => $form['description'] ?: null, ':image' => $form['image'] ?: null,
                    ':sort_order' => $form['sort_order'], ':status' => $form['status'], ':id' => $id,
                ]);
                setFlash('success', 'Blog category updated successfully.');
                header('Location: categories.php'); exit;
            } catch (PDOException $e) { $errors[] = 'Database error.'; }
        }
    }
}

include __DIR__ . '/../../includes/admin-header.php';
include __DIR__ . '/../../includes/admin-navbar.php';
?>
<div class="category-form-container">
    <h2>Edit Blog Category</h2>
    <?php if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <div class="form-group"><label>Name *</label><input type="text" name="name" value="<?= htmlspecialchars($form['name']) ?>" required></div>
        <div class="form-group"><label>Slug</label><input type="text" name="slug" value="<?= htmlspecialchars($form['slug']) ?>"></div>
        <div class="form-group"><label>Description</label><textarea name="description" rows="3"><?= htmlspecialchars($form['description'] ?? '') ?></textarea></div>
        <div class="form-group"><label>Image Path</label><input type="text" name="image" value="<?= htmlspecialchars($form['image'] ?? '') ?>"></div>
        <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" value="<?= (int)$form['sort_order'] ?>" min="0"></div>
        <div class="form-group"><label>Status</label><select name="status"><option value="1" <?= $form['status']==1?'selected':'' ?>>Active</option><option value="0" <?= $form['status']==0?'selected':'' ?>>Inactive</option></select></div>
        <div class="form-actions"><button class="btn btn-primary">Update Category</button><a href="<?= BASE_URL ?>/admin/blog/categories.php" class="btn btn-secondary">Cancel</a></div>
    </form>
</div>
<?php include __DIR__ . '/../../includes/admin-footer.php'; ?>