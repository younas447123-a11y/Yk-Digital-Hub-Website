<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
$page_title = 'Edit Blog Post';
$current_page = 'blog';
require_once __DIR__ . '/../../includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { setFlash('error', 'Invalid post ID.'); header('Location: index.php'); exit; }

$stmt = $db->prepare("SELECT * FROM blog_posts WHERE id = :id");
$stmt->execute([':id' => $id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$post) { setFlash('error', 'Post not found.'); header('Location: index.php'); exit; }

$ts = $db->prepare("SELECT tag_id FROM blog_post_tags WHERE post_id = :pid");
$ts->execute([':pid' => $id]);
$selected_tags = array_map('intval', array_column($ts->fetchAll(PDO::FETCH_ASSOC), 'tag_id'));

$categories = $db->query("SELECT id, name FROM blog_categories WHERE status = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$authors    = $db->query("SELECT id, name FROM users ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$all_tags   = $db->query("SELECT id, name FROM blog_tags ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
        $fields = ['title','slug','excerpt','content','category_id','author_id','featured_image','featured_image_alt','status','featured','published_at','seo_title','meta_description','focus_keyword','canonical_url','og_title','og_description','og_image','robots'];
        foreach ($fields as $f) { $post[$f] = trim($_POST[$f] ?? ''); }
        $post['category_id'] = $post['category_id'] === '' ? null : (int)$post['category_id'];
        $post['author_id']   = $post['author_id']   === '' ? null : (int)$post['author_id'];
        $post['featured']    = (int)$post['featured'];
        $selected_tags = array_map('intval', $_POST['tags'] ?? []);

        if ($post['title'] === '') $errors[] = 'Title is required.';
        if ($post['content'] === '') $errors[] = 'Content is required.';
        if (!in_array($post['status'], ['draft','published','archived'], true)) $errors[] = 'Invalid status.';

        if ($post['slug'] === '') $post['slug'] = strtolower(trim(preg_replace('/[^a-zA-Z0-9-]+/', '-', $post['title']), '-'));
        if (!preg_match('/^[a-z0-9-]+$/', $post['slug'])) $errors[] = 'Slug must contain only lowercase letters, numbers, and hyphens.';

        if (empty($errors)) {
            $s = $db->prepare("SELECT id FROM blog_posts WHERE slug = :slug AND id != :id");
            $s->execute([':slug' => $post['slug'], ':id' => $id]);
            if ($s->fetch()) $errors[] = 'Slug already exists.';
        }

        if (empty($errors)) {
            $post['content'] = sanitize_post_html($post['content']);
            $post['reading_time'] = estimate_reading_time($post['content']);
            if ($post['status'] === 'published' && empty($post['published_at'])) {
                $post['published_at'] = date('Y-m-d H:i:s');
            } elseif (!empty($post['published_at'])) {
                $post['published_at'] = date('Y-m-d H:i:s', strtotime($post['published_at']));
            } else {
                $post['published_at'] = null;
            }

            try {
                $db->beginTransaction();
                $stmt = $db->prepare("
                    UPDATE blog_posts SET
                        category_id = :category_id, author_id = :author_id,
                        title = :title, slug = :slug, excerpt = :excerpt, content = :content,
                        featured_image = :featured_image, featured_image_alt = :featured_image_alt,
                        reading_time = :reading_time, published_at = :published_at,
                        status = :status, featured = :featured,
                        seo_title = :seo_title, meta_description = :meta_description, focus_keyword = :focus_keyword,
                        canonical_url = :canonical_url, og_title = :og_title, og_description = :og_description,
                        og_image = :og_image, robots = :robots
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':category_id' => $post['category_id'], ':author_id' => $post['author_id'],
                    ':title' => $post['title'], ':slug' => $post['slug'],
                    ':excerpt' => $post['excerpt'] ?: null, ':content' => $post['content'],
                    ':featured_image' => $post['featured_image'] ?: null,
                    ':featured_image_alt' => $post['featured_image_alt'] ?: null,
                    ':reading_time' => $post['reading_time'], ':published_at' => $post['published_at'],
                    ':status' => $post['status'], ':featured' => $post['featured'],
                    ':seo_title' => $post['seo_title'] ?: null, ':meta_description' => $post['meta_description'] ?: null,
                    ':focus_keyword' => $post['focus_keyword'] ?: null, ':canonical_url' => $post['canonical_url'] ?: null,
                    ':og_title' => $post['og_title'] ?: null, ':og_description' => $post['og_description'] ?: null,
                    ':og_image' => $post['og_image'] ?: null, ':robots' => $post['robots'] ?: null,
                    ':id' => $id,
                ]);

                // Refresh tag relationships
                $db->prepare("DELETE FROM blog_post_tags WHERE post_id = :pid")->execute([':pid' => $id]);
                if (!empty($selected_tags)) {
                    $ts = $db->prepare("INSERT INTO blog_post_tags (post_id, tag_id) VALUES (:pid, :tid)");
                    foreach ($selected_tags as $tid) $ts->execute([':pid' => $id, ':tid' => $tid]);
                }
                $db->commit();
                setFlash('success', 'Post updated successfully.');
                header('Location: index.php');
                exit;
            } catch (PDOException $e) {
                $db->rollBack();
                $errors[] = 'Database error while saving post.';
            }
        }
    }
}

include __DIR__ . '/../../includes/admin-header.php';
include __DIR__ . '/../../includes/admin-navbar.php';
?>
<div class="service-form-container">
    <h2>Edit Blog Post</h2>
    <?php if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <?php include __DIR__ . '/_form.php'; ?>
</div>
<?php include __DIR__ . '/../../includes/admin-footer.php'; ?>