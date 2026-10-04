<?php
/**
 * Portfolio Project form partial.
 * Expects: $project (array), $categories (child categories), $action_url
 */
$is_edit = !empty($project['id']);
require_once ROOT_PATH . '/includes/image-uploader.php';
?>
<form method="POST" action="<?= $action_url ?>" class="service-form">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <input type="hidden" name="form" value="basic">   <!-- ADD THIS LINE -->
    ...
    <fieldset class="form-section">
        <legend>Basic Information</legend>
        <div class="form-row">
            <div class="form-group">
                <label for="category_id">Subcategory *</label>
                <select name="category_id" id="category_id" required>
                    <option value="">Select subcategory</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= ($project['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['parent_name'] . ' → ' . $cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="title">Title *</label>
                <input type="text" name="title" id="title" value="<?= htmlspecialchars($project['title'] ?? '') ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="slug">Slug</label>
                <input type="text" name="slug" id="slug" value="<?= htmlspecialchars($project['slug'] ?? '') ?>" placeholder="Auto-generated from title">
                <small>Lowercase letters, numbers, hyphens only.</small>
            </div>
            <div class="form-group">
                <label for="website_url">Website URL</label>
                <input type="url" name="website_url" id="website_url" value="<?= htmlspecialchars($project['website_url'] ?? '') ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="short_description">Short Description</label>
                <input type="text" name="short_description" id="short_description" value="<?= htmlspecialchars($project['short_description'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="card_description">Card Description</label>
                <input type="text" name="card_description" id="card_description" value="<?= htmlspecialchars($project['card_description'] ?? '') ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" rows="4"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
        </div>
    </fieldset>

    <fieldset class="form-section">
        <legend>Project Details</legend>
        <div class="form-row">
            <div class="form-group"><label for="client_name">Client Name</label><input type="text" name="client_name" id="client_name" value="<?= htmlspecialchars($project['client_name'] ?? '') ?>"></div>
            <div class="form-group"><label for="project_type">Project Type</label><input type="text" name="project_type" id="project_type" value="<?= htmlspecialchars($project['project_type'] ?? '') ?>"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label for="industry">Industry</label><input type="text" name="industry" id="industry" value="<?= htmlspecialchars($project['industry'] ?? '') ?>"></div>
            <div class="form-group"><label for="location">Location</label><input type="text" name="location" id="location" value="<?= htmlspecialchars($project['location'] ?? '') ?>"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label for="project_year">Project Year</label><input type="number" name="project_year" id="project_year" value="<?= htmlspecialchars($project['project_year'] ?? '') ?>" min="2000" max="2100"></div>
            <div class="form-group"><label for="project_duration">Duration</label><input type="text" name="project_duration" id="project_duration" value="<?= htmlspecialchars($project['project_duration'] ?? '') ?>" placeholder="e.g., 3 months"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label for="team_size">Team Size</label><input type="number" name="team_size" id="team_size" value="<?= htmlspecialchars($project['team_size'] ?? '') ?>"></div>
            <div class="form-group"><label for="platform">Platform</label><input type="text" name="platform" id="platform" value="<?= htmlspecialchars($project['platform'] ?? '') ?>"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label for="technologies">Technologies (comma-separated)</label><input type="text" name="technologies" id="technologies" value="<?= htmlspecialchars($project['technologies'] ?? '') ?>"></div>
        </div>
        <div class="form-group"><label for="project_scope">Project Scope</label><textarea name="project_scope" id="project_scope" rows="3"><?= htmlspecialchars($project['project_scope'] ?? '') ?></textarea></div>
    </fieldset>

    <fieldset class="form-section">
        <legend>Hero Information</legend>
        <div class="form-row">
            <div class="form-group"><label for="hero_title">Hero Title</label><input type="text" name="hero_title" id="hero_title" value="<?= htmlspecialchars($project['hero_title'] ?? '') ?>"></div>
            <div class="form-group"><label for="hero_subtitle">Hero Subtitle</label><input type="text" name="hero_subtitle" id="hero_subtitle" value="<?= htmlspecialchars($project['hero_subtitle'] ?? '') ?>"></div>
        </div>
        <div class="form-group"><label for="hero_description">Hero Description</label><textarea name="hero_description" id="hero_description" rows="3"><?= htmlspecialchars($project['hero_description'] ?? '') ?></textarea></div>
        <div class="form-row">
            <?php renderImageUploader([
                'name' => 'hero_image',
                'value' => $project['hero_image'] ?? '',
                'folder' => 'portfolio',
                'label' => 'Hero Image',
                'help' => 'Recommended 1600x900px - Max 5MB',
            ]); ?>
            <div class="form-group"><label for="hero_video_url">Hero Video URL</label><input type="url" name="hero_video_url" id="hero_video_url" value="<?= htmlspecialchars($project['hero_video_url'] ?? '') ?>"></div>
        </div>
    </fieldset>

    <fieldset class="form-section">
        <legend>Business Context</legend>
        <div class="form-group"><label for="client_challenge">Client Challenge</label><textarea name="client_challenge" id="client_challenge" rows="3"><?= htmlspecialchars($project['client_challenge'] ?? '') ?></textarea></div>
        <div class="form-group"><label for="project_goal">Project Goal</label><textarea name="project_goal" id="project_goal" rows="3"><?= htmlspecialchars($project['project_goal'] ?? '') ?></textarea></div>
        <div class="form-group"><label for="project_summary">Project Summary</label><textarea name="project_summary" id="project_summary" rows="3"><?= htmlspecialchars($project['project_summary'] ?? '') ?></textarea></div>
    </fieldset>

    <fieldset class="form-section">
        <legend>Call to Action</legend>
        <div class="form-row">
            <div class="form-group"><label for="cta_title">CTA Title</label><input type="text" name="cta_title" id="cta_title" value="<?= htmlspecialchars($project['cta_title'] ?? '') ?>"></div>
            <div class="form-group"><label for="cta_button_text">CTA Button Text</label><input type="text" name="cta_button_text" id="cta_button_text" value="<?= htmlspecialchars($project['cta_button_text'] ?? '') ?>"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label for="cta_description">CTA Description</label><textarea name="cta_description" id="cta_description" rows="2"><?= htmlspecialchars($project['cta_description'] ?? '') ?></textarea></div>
            <div class="form-group"><label for="cta_button_url">CTA Button URL</label><input type="url" name="cta_button_url" id="cta_button_url" value="<?= htmlspecialchars($project['cta_button_url'] ?? '') ?>"></div>
        </div>
    </fieldset>

    <fieldset class="form-section">
        <legend>Publishing</legend>
        <div class="form-row">
            <div class="form-group"><label for="featured">Featured</label>
                <select name="featured" id="featured">
                    <option value="0" <?= (($project['featured'] ?? 0) == 0) ? 'selected' : '' ?>>No</option>
                    <option value="1" <?= (($project['featured'] ?? 0) == 1) ? 'selected' : '' ?>>Yes</option>
                </select>
            </div>
            <div class="form-group"><label for="status">Status</label>
                <select name="status" id="status">
                    <option value="1" <?= (($project['status'] ?? 1) == 1) ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= (($project['status'] ?? 1) == 0) ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="form-group"><label for="sort_order">Sort Order</label>
                <input type="number" name="sort_order" id="sort_order" value="<?= (int)($project['sort_order'] ?? 0) ?>" min="0">
            </div>
        </div>
    </fieldset>

    <fieldset class="form-section">
        <legend>SEO</legend>
        <div class="form-row">
            <div class="form-group"><label for="seo_title">SEO Title</label><input type="text" name="seo_title" id="seo_title" value="<?= htmlspecialchars($project['seo_title'] ?? '') ?>"></div>
            <div class="form-group"><label for="focus_keyword">Focus Keyword</label><input type="text" name="focus_keyword" id="focus_keyword" value="<?= htmlspecialchars($project['focus_keyword'] ?? '') ?>"></div>
        </div>
        <div class="form-group"><label for="meta_description">Meta Description</label><textarea name="meta_description" id="meta_description" rows="2"><?= htmlspecialchars($project['meta_description'] ?? '') ?></textarea></div>
        <div class="form-row">
            <div class="form-group"><label for="canonical_url">Canonical URL</label><input type="url" name="canonical_url" id="canonical_url" value="<?= htmlspecialchars($project['canonical_url'] ?? '') ?>"></div>
            <?php renderImageUploader([
                'name' => 'og_image',
                'value' => $project['og_image'] ?? '',
                'folder' => 'og',
                'label' => 'OG Image',
                'help' => 'Recommended 1200x630px - Max 5MB',
            ]); ?>
        </div>
        <div class="form-row">
            <div class="form-group"><label for="og_title">OG Title</label><input type="text" name="og_title" id="og_title" value="<?= htmlspecialchars($project['og_title'] ?? '') ?>"></div>
            <div class="form-group"><label for="robots">Robots</label>
                <select name="robots" id="robots">
                    <?php $r = $project['robots'] ?? 'index, follow'; ?>
                    <option value="index, follow" <?= $r === 'index, follow' ? 'selected' : '' ?>>index, follow</option>
                    <option value="noindex, follow" <?= $r === 'noindex, follow' ? 'selected' : '' ?>>noindex, follow</option>
                    <option value="index, nofollow" <?= $r === 'index, nofollow' ? 'selected' : '' ?>>index, nofollow</option>
                    <option value="noindex, nofollow" <?= $r === 'noindex, nofollow' ? 'selected' : '' ?>>noindex, nofollow</option>
                </select>
            </div>
        </div>
        <div class="form-group"><label for="og_description">OG Description</label><textarea name="og_description" id="og_description" rows="2"><?= htmlspecialchars($project['og_description'] ?? '') ?></textarea></div>
    </fieldset>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= $is_edit ? 'Update Project' : 'Create Project' ?></button>
        <a href="<?= BASE_URL ?>/admin/portfolio/index.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>