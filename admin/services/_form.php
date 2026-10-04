<?php
/**
 * Reusable form partial for services (create / edit).
 * Expects $service (array) for editing, or empty array for create.
 * Also expects $categories (list of child categories with parent names).
 */
// Determine if we are editing
$is_edit = isset($service) && !empty($service['id']);
$action_url = $is_edit ? BASE_URL . '/admin/services/edit.php?id=' . (int)$service['id'] : BASE_URL . '/admin/services/create.php';

// Default values
$defaults = [
    'category_id' => 0,
    'title' => '',
    'slug' => '',
    'short_description' => '',
    'card_description' => '',
    'hero_title' => '',
    'hero_subtitle' => '',
    'hero_description' => '',
    'hero_image' => '',
    'hero_badge' => '',
    'icon' => '',
    'featured' => 0,
    'sort_order' => 0,
    'status' => 1,
    'starting_price' => null,
    'price_label' => '',
    'delivery_time' => '',
    'service_location' => '',
    'primary_cta_text' => '',
    'primary_cta_url' => '',
    'secondary_cta_text' => '',
    'secondary_cta_url' => '',
    'seo_title' => '',
    'meta_description' => '',
    'focus_keyword' => '',
    'canonical_url' => '',
    'og_title' => '',
    'og_description' => '',
    'og_image' => '',
    'robots' => 'index, follow'
];

if ($is_edit) {
    // Override defaults with service data
    foreach ($defaults as $key => $val) {
        if (isset($service[$key])) {
            $defaults[$key] = $service[$key];
        }
    }
}
$data = $defaults;
?>
<form method="POST" action="<?= $action_url ?>" class="service-form">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

    <!-- SECTION A: BASIC INFORMATION -->
    <fieldset class="form-section">
        <legend>Basic Information</legend>
        <div class="form-row">
            <div class="form-group">
                <label for="category_id">Service Subcategory *</label>
                <select name="category_id" id="category_id" required>
                    <option value="">Select subcategory</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= ($data['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['parent_name'] . ' → ' . $cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="title">Title *</label>
                <input type="text" name="title" id="title" value="<?= htmlspecialchars($data['title']) ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="slug">Slug</label>
                <input type="text" name="slug" id="slug" value="<?= htmlspecialchars($data['slug']) ?>" placeholder="Auto-generated from title">
                <small>Leave blank to auto-generate. Use lowercase letters, numbers, and hyphens.</small>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="short_description">Short Description</label>
                <input type="text" name="short_description" id="short_description" value="<?= htmlspecialchars($data['short_description']) ?>">
            </div>
            <div class="form-group">
                <label for="card_description">Card Description</label>
                <input type="text" name="card_description" id="card_description" value="<?= htmlspecialchars($data['card_description']) ?>">
            </div>
        </div>
    </fieldset>

    <!-- SECTION B: HERO INFORMATION -->
    <fieldset class="form-section">
        <legend>Hero Information</legend>
        <div class="form-row">
            <div class="form-group">
                <label for="hero_title">Hero Title</label>
                <input type="text" name="hero_title" id="hero_title" value="<?= htmlspecialchars($data['hero_title']) ?>">
            </div>
            <div class="form-group">
                <label for="hero_subtitle">Hero Subtitle</label>
                <input type="text" name="hero_subtitle" id="hero_subtitle" value="<?= htmlspecialchars($data['hero_subtitle']) ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="hero_description">Hero Description</label>
                <textarea name="hero_description" id="hero_description" rows="3"><?= htmlspecialchars($data['hero_description']) ?></textarea>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="hero_badge">Hero Badge</label>
                <input type="text" name="hero_badge" id="hero_badge" value="<?= htmlspecialchars($data['hero_badge']) ?>" placeholder="e.g., Featured Service">
            </div>
            <?php
require_once ROOT_PATH . '/includes/image-uploader.php';
renderImageUploader([
    'name'   => 'hero_image',
    'value'  => $data['hero_image'] ?? '',
    'folder' => 'services',
    'label'  => 'Hero Image',
    'help'   => 'Recommended 1600×900px · Max 5MB',
]);
?>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="icon">Icon (CSS class)</label>
                <input type="text" name="icon" id="icon" value="<?= htmlspecialchars($data['icon']) ?>" placeholder="e.g., fas fa-code">
            </div>
        </div>
    </fieldset>

    <!-- SECTION C: DISPLAY SETTINGS -->
    <fieldset class="form-section">
        <legend>Display Settings</legend>
        <div class="form-row">
            <div class="form-group">
                <label for="featured">Featured</label>
                <select name="featured" id="featured">
                    <option value="0" <?= ($data['featured'] == 0) ? 'selected' : '' ?>>No</option>
                    <option value="1" <?= ($data['featured'] == 1) ? 'selected' : '' ?>>Yes</option>
                </select>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status">
                    <option value="1" <?= ($data['status'] == 1) ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= ($data['status'] == 0) ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="form-group">
                <label for="sort_order">Sort Order</label>
                <input type="number" name="sort_order" id="sort_order" value="<?= (int)$data['sort_order'] ?>" min="0">
            </div>
        </div>
    </fieldset>

    <!-- SECTION D: SERVICE INFORMATION -->
    <fieldset class="form-section">
        <legend>Service Information</legend>
        <div class="form-row">
            <div class="form-group">
                <label for="starting_price">Starting Price</label>
                <input type="number" name="starting_price" id="starting_price" step="0.01" value="<?= htmlspecialchars($data['starting_price']) ?>" placeholder="e.g., 30000">
            </div>
            <div class="form-group">
                <label for="price_label">Price Label</label>
                <input type="text" name="price_label" id="price_label" value="<?= htmlspecialchars($data['price_label']) ?>" placeholder="e.g., Starting from">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="delivery_time">Delivery Time</label>
                <input type="text" name="delivery_time" id="delivery_time" value="<?= htmlspecialchars($data['delivery_time']) ?>" placeholder="e.g., 2–4 weeks">
            </div>
            <div class="form-group">
                <label for="service_location">Service Location</label>
                <input type="text" name="service_location" id="service_location" value="<?= htmlspecialchars($data['service_location']) ?>" placeholder="e.g., Worldwide">
            </div>
        </div>
    </fieldset>

    <!-- SECTION E: CALL TO ACTION -->
    <fieldset class="form-section">
        <legend>Call to Action</legend>
        <div class="form-row">
            <div class="form-group">
                <label for="primary_cta_text">Primary CTA Text</label>
                <input type="text" name="primary_cta_text" id="primary_cta_text" value="<?= htmlspecialchars($data['primary_cta_text']) ?>">
            </div>
            <div class="form-group">
                <label for="primary_cta_url">Primary CTA URL</label>
                <input type="url" name="primary_cta_url" id="primary_cta_url" value="<?= htmlspecialchars($data['primary_cta_url']) ?>" placeholder="e.g., /contact">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="secondary_cta_text">Secondary CTA Text</label>
                <input type="text" name="secondary_cta_text" id="secondary_cta_text" value="<?= htmlspecialchars($data['secondary_cta_text']) ?>">
            </div>
            <div class="form-group">
                <label for="secondary_cta_url">Secondary CTA URL</label>
                <input type="url" name="secondary_cta_url" id="secondary_cta_url" value="<?= htmlspecialchars($data['secondary_cta_url']) ?>" placeholder="e.g., /portfolio">
            </div>
        </div>
    </fieldset>

    <!-- SECTION F: SEO -->
    <fieldset class="form-section">
        <legend>SEO</legend>
        <div class="form-row">
            <div class="form-group">
                <label for="seo_title">SEO Title</label>
                <input type="text" name="seo_title" id="seo_title" value="<?= htmlspecialchars($data['seo_title']) ?>">
            </div>
            <div class="form-group">
                <label for="meta_description">Meta Description</label>
                <textarea name="meta_description" id="meta_description" rows="2"><?= htmlspecialchars($data['meta_description']) ?></textarea>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="focus_keyword">Focus Keyword</label>
                <input type="text" name="focus_keyword" id="focus_keyword" value="<?= htmlspecialchars($data['focus_keyword']) ?>">
            </div>
            <div class="form-group">
                <label for="canonical_url">Canonical URL</label>
                <input type="url" name="canonical_url" id="canonical_url" value="<?= htmlspecialchars($data['canonical_url']) ?>" placeholder="e.g., https://example.com/service">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="og_title">OG Title</label>
                <input type="text" name="og_title" id="og_title" value="<?= htmlspecialchars($data['og_title']) ?>">
            </div>
            <div class="form-group">
                <label for="og_description">OG Description</label>
                <textarea name="og_description" id="og_description" rows="2"><?= htmlspecialchars($data['og_description']) ?></textarea>
            </div>
        </div>
        <div class="form-row">
            <?php
renderImageUploader([
    'name'   => 'og_image',
    'value'  => $data['og_image'] ?? '',
    'folder' => 'og',
    'label'  => 'OG Image (Social Share)',
    'help'   => 'Recommended 1200×630px',
]);
?>
            <div class="form-group">
                <label for="robots">Robots</label>
                <select name="robots" id="robots">
                    <option value="index, follow" <?= ($data['robots'] === 'index, follow') ? 'selected' : '' ?>>index, follow</option>
                    <option value="noindex, follow" <?= ($data['robots'] === 'noindex, follow') ? 'selected' : '' ?>>noindex, follow</option>
                    <option value="index, nofollow" <?= ($data['robots'] === 'index, nofollow') ? 'selected' : '' ?>>index, nofollow</option>
                    <option value="noindex, nofollow" <?= ($data['robots'] === 'noindex, nofollow') ? 'selected' : '' ?>>noindex, nofollow</option>
                </select>
            </div>
        </div>
    </fieldset>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= $is_edit ? 'Update Service' : 'Create Service' ?></button>
        <a href="<?= BASE_URL ?>/admin/services/index.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>