<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
$page_title = 'Add Blog Tag';
$current_page = 'blog-tags';
require_once __DIR__ . '/../../includes/functions.php';

$form = ['name' => '', 'slug' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
        $form['name'] = trim($_POST['name'] ?? '');
        $form['slug'] = trim($_POST['slug'] ?? '');
        if ($form['name'] === '') $errors[] = 'Tag name is required.';
        if ($form['slug'] === '') $form['slug'] = strtolower(trim(preg_replace('/[^a-zA-Z0-9-]+/', '-', $form['name']), '-'));
        if (!preg_match('/^[a-z0-9-]+$/', $form['slug'])) $errors[] = 'Slug must contain only lowercase letters, numbers, and hyphens.';
        if (empty($errors)) {
            $s = $db->prepare("SELECT id FROM blog_tags WHERE slug = :slug");
            $s->execute([':slug' => $form['slug']]);
            if ($s->fetch()) $errors[] = 'Slug already exists.';
        }
        if (empty($errors)) {
            try {
                $db->prepare("INSERT INTO blog_tags (name, slug) VALUES (:name, :slug)")
                   ->execute([':name' => $form['name'], ':slug' => $form['slug']]);
                setFlash('success', 'Blog tag created successfully.');
                header('Location: tags.php'); exit;
            } catch (PDOException $e) { $errors[] = 'Database error.'; }
        }
    }
}

include __DIR__ . '/../../includes/admin-header.php';
include __DIR__ . '/../../includes/admin-navbar.php';
?>
<div class="category-form-container">
    <h2>Add Blog Tag</h2>
    <?php if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <div class="form-group"><label>Tag Name *</label><input type="text" name="name" value="<?= htmlspecialchars($form['name']) ?>" required></div>
        <div class="form-group"><label>Slug</label><input type="text" name="slug" value="<?= htmlspecialchars($form['slug']) ?>" placeholder="Auto-generated from name"></div>
        <div class="form-actions"><button class="btn btn-primary">Create Tag</button><a href="<?= BASE_URL ?>/admin/blog/tags.php" class="btn btn-secondary">Cancel</a></div>
    </form>
</div>
<?php include __DIR__ . '/../../includes/admin-footer.php'; ?>