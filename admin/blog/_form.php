<?php
require_once ROOT_PATH . '/includes/image-uploader.php';
?>
<?php
/**
 * Shared Blog Post form.
 * Expects: $post (array), $categories, $authors, $all_tags, $selected_tags
 */
$is_edit = !empty($post['id']);
$action_url = $is_edit
    ? BASE_URL . '/admin/blog/edit.php?id=' . (int)$post['id']
    : BASE_URL . '/admin/blog/create.php';
?>
<form method="POST" action="<?= $action_url ?>" class="service-form">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

    <fieldset class="form-section">
        <legend>Basic Information</legend>
        <div class="form-row">
            <div class="form-group">
                <label for="title">Title *</label>
                <input type="text" name="title" id="title" value="<?= htmlspecialchars($post['title'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="slug">Slug</label>
                <input type="text" name="slug" id="slug" value="<?= htmlspecialchars($post['slug'] ?? '') ?>" placeholder="Auto-generated from title">
                <small>Lowercase letters, numbers, hyphens only.</small>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="category_id">Category</label>
                <select name="category_id" id="category_id">
                    <option value="">— None —</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= (($post['category_id'] ?? 0) == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="author_id">Author</label>
                <select name="author_id" id="author_id">
                    <option value="">— None —</option>
                    <?php foreach ($authors as $a): ?>
                        <option value="<?= (int)$a['id'] ?>" <?= (($post['author_id'] ?? 0) == $a['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="excerpt">Excerpt / Summary</label>
            <textarea name="excerpt" id="excerpt" rows="3"><?= htmlspecialchars($post['excerpt'] ?? '') ?></textarea>
        </div>
    </fieldset>

    <fieldset class="form-section">
        <legend>Content</legend>
        <div class="form-group">
            <label for="content">Post Content *</label>
            <textarea name="content" id="content" rows="18" style="font-family: ui-monospace, Menlo, Consolas, monospace; font-size: 14px;"><?= htmlspecialchars($post['content'] ?? '') ?></textarea>
            <small>Allowed HTML: p, h2-h6, strong, em, ul, ol, li, a, blockquote, img, figure, pre, code, table. Inline <code>on*=</code> attributes and <code>javascript:</code> URLs will be stripped on save.</small>
        </div>
    </fieldset>

    <fieldset class="form-section">
        <legend>Featured Image</legend>
        <div class="form-row">
            <?php renderImageUploader([
                'name' => 'featured_image',
                'value' => $post['featured_image'] ?? '',
                'folder' => 'blog',
                'label' => 'Featured Image',
                'help' => 'Recommended 1600x900px - Max 5MB',
            ]); ?>
            <div class="form-group">
                <label for="featured_image_alt">Image Alt Text</label>
                <input type="text" name="featured_image_alt" id="featured_image_alt" value="<?= htmlspecialchars($post['featured_image_alt'] ?? '') ?>">
            </div>
        </div>
    </fieldset>

    <fieldset class="form-section">
        <legend>Tags</legend>
        <div class="form-group">
            <label>Assign Tags</label>
            <div class="tag-checkboxes">
                <?php foreach ($all_tags as $tag): ?>
                    <label class="tag-checkbox">
                        <input type="checkbox" name="tags[]" value="<?= (int)$tag['id'] ?>"
                            <?= in_array((int)$tag['id'], $selected_tags, true) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($tag['name']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <small>Manage tags from <a href="<?= BASE_URL ?>/admin/blog/tags.php" target="_blank">Blog → Tags</a>.</small>
        </div>
    </fieldset>

    <fieldset class="form-section">
        <legend>Publishing</legend>
        <div class="form-row">
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status">
                    <?php $st = $post['status'] ?? 'draft'; ?>
                    <option value="draft"     <?= $st === 'draft'     ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= $st === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="archived"  <?= $st === 'archived'  ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
            <div class="form-group">
                <label for="published_at">Publish Date</label>
                <input type="datetime-local" name="published_at" id="published_at"
                       value="<?= !empty($post['published_at']) ? date('Y-m-d\TH:i', strtotime($post['published_at'])) : '' ?>">
                <small>Leave blank for auto-set on first publish.</small>
            </div>
            <div class="form-group">
                <label for="featured">Featured</label>
                <select name="featured" id="featured">
                    <option value="0" <?= (($post['featured'] ?? 0) == 0) ? 'selected' : '' ?>>No</option>
                    <option value="1" <?= (($post['featured'] ?? 0) == 1) ? 'selected' : '' ?>>Yes</option>
                </select>
            </div>
        </div>
    </fieldset>

    <fieldset class="form-section">
        <legend>SEO</legend>
        <div class="form-row">
            <div class="form-group">
                <label for="seo_title">SEO Title</label>
                <input type="text" name="seo_title" id="seo_title" value="<?= htmlspecialchars($post['seo_title'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="focus_keyword">Focus Keyword</label>
                <input type="text" name="focus_keyword" id="focus_keyword" value="<?= htmlspecialchars($post['focus_keyword'] ?? '') ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="meta_description">Meta Description</label>
            <textarea name="meta_description" id="meta_description" rows="2"><?= htmlspecialchars($post['meta_description'] ?? '') ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="canonical_url">Canonical URL</label>
                <input type="url" name="canonical_url" id="canonical_url" value="<?= htmlspecialchars($post['canonical_url'] ?? '') ?>">
            </div>
            <?php renderImageUploader([
                'name' => 'og_image',
                'value' => $post['og_image'] ?? '',
                'folder' => 'og',
                'label' => 'OG Image',
                'help' => 'Recommended 1200x630px - Max 5MB',
            ]); ?>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="og_title">OG Title</label>
                <input type="text" name="og_title" id="og_title" value="<?= htmlspecialchars($post['og_title'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="robots">Robots</label>
                <?php $r = $post['robots'] ?? 'index, follow'; ?>
                <select name="robots" id="robots">
                    <option value="index, follow"    <?= $r === 'index, follow'    ? 'selected' : '' ?>>index, follow</option>
                    <option value="noindex, follow"  <?= $r === 'noindex, follow'  ? 'selected' : '' ?>>noindex, follow</option>
                    <option value="index, nofollow"  <?= $r === 'index, nofollow'  ? 'selected' : '' ?>>index, nofollow</option>
                    <option value="noindex, nofollow"<?= $r === 'noindex, nofollow'? 'selected' : '' ?>>noindex, nofollow</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="og_description">OG Description</label>
            <textarea name="og_description" id="og_description" rows="2"><?= htmlspecialchars($post['og_description'] ?? '') ?></textarea>
        </div>
    </fieldset>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= $is_edit ? 'Update Post' : 'Create Post' ?></button>
        <a href="<?= BASE_URL ?>/admin/blog/index.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>