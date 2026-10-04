<?php
require_once ROOT_PATH . '/includes/image-uploader.php';
?>
<?php
/**
 * Shared Testimonials form.
 * Expects: $testimonial (array), $services, $projects, $action_url
 */
$is_edit = !empty($testimonial['id']);
?>
<form method="POST" action="<?= $action_url ?>" class="service-form">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

    <fieldset class="form-section">
        <legend>Client Information</legend>
        <div class="form-row">
            <div class="form-group">
                <label for="client_name">Client Name *</label>
                <input type="text" name="client_name" id="client_name" value="<?= htmlspecialchars($testimonial['client_name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="company_name">Company</label>
                <input type="text" name="company_name" id="company_name" value="<?= htmlspecialchars($testimonial['company_name'] ?? '') ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="position">Position</label>
                <input type="text" name="position" id="position" value="<?= htmlspecialchars($testimonial['position'] ?? '') ?>" placeholder="e.g., CEO, Marketing Director">
            </div>
            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" name="location" id="location" value="<?= htmlspecialchars($testimonial['location'] ?? '') ?>">
            </div>
        </div>
        <?php renderImageUploader([
            'name' => 'client_photo',
            'value' => $testimonial['client_photo'] ?? '',
            'folder' => 'testimonials',
            'label' => 'Client Photo',
            'help' => 'Relative URL to an image. Max 5MB.',
        ]); ?>
    </fieldset>

    <fieldset class="form-section">
        <legend>Testimonial</legend>
        <div class="form-row">
            <div class="form-group">
                <label for="rating">Rating (1–5)</label>
                <?php $rating = $testimonial['rating'] ?? ''; ?>
                <select name="rating" id="rating">
                    <option value="">— No rating —</option>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>" <?= ((string)$rating === (string)$i) ? 'selected' : '' ?>><?= $i ?> star<?= $i > 1 ? 's' : '' ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="testimonial">Review *</label>
            <textarea name="testimonial" id="testimonial" rows="5" required><?= htmlspecialchars($testimonial['testimonial'] ?? '') ?></textarea>
        </div>
    </fieldset>

    <fieldset class="form-section">
        <legend>Associations (Optional)</legend>
        <div class="form-row">
            <div class="form-group">
                <label for="service_id">Related Service</label>
                <select name="service_id" id="service_id">
                    <option value="">— None —</option>
                    <?php foreach ($services as $s): ?>
                        <option value="<?= (int)$s['id'] ?>" <?= (($testimonial['service_id'] ?? 0) == $s['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="project_id">Related Portfolio Project</label>
                <select name="project_id" id="project_id">
                    <option value="">— None —</option>
                    <?php foreach ($projects as $p): ?>
                        <option value="<?= (int)$p['id'] ?>" <?= (($testimonial['project_id'] ?? 0) == $p['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </fieldset>

    <fieldset class="form-section">
        <legend>Display</legend>
        <div class="form-row">
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status">
                    <option value="1" <?= (($testimonial['status'] ?? 1) == 1) ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= (($testimonial['status'] ?? 1) == 0) ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="form-group">
                <label for="featured">Featured</label>
                <select name="featured" id="featured">
                    <option value="0" <?= (($testimonial['featured'] ?? 0) == 0) ? 'selected' : '' ?>>No</option>
                    <option value="1" <?= (($testimonial['featured'] ?? 0) == 1) ? 'selected' : '' ?>>Yes</option>
                </select>
            </div>
            <div class="form-group">
                <label for="sort_order">Sort Order</label>
                <input type="number" name="sort_order" id="sort_order" value="<?= (int)($testimonial['sort_order'] ?? 0) ?>" min="0">
            </div>
        </div>
    </fieldset>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= $is_edit ? 'Update Testimonial' : 'Create Testimonial' ?></button>
        <a href="<?= BASE_URL ?>/admin/testimonials/index.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>