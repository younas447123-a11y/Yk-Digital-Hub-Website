<?php
/**
 * Reusable Image Uploader Component
 *
 * Usage:
 *   renderImageUploader([
 *       'name'   => 'hero_image',
 *       'value'  => $service['hero_image'] ?? '',
 *       'folder' => 'services',
 *       'label'  => 'Hero Image',
 *       'help'   => 'Recommended 1600×900px, max 5MB',
 *   ]);
 */

if (!function_exists('renderImageUploader')) {
    function renderImageUploader($opts = []) {
        $name     = $opts['name']     ?? 'image';
        $value    = $opts['value']    ?? '';
        $folder   = $opts['folder']   ?? 'general';
        $label    = $opts['label']    ?? 'Image';
        $required = !empty($opts['required']);
        $help     = $opts['help']     ?? 'JPG, PNG, WebP, GIF · Max 5MB';
        $showText = $opts['show_text'] ?? true;
        $width    = $opts['width']    ?? '100%';
        ?>
        <div class="yk-uploader"
             data-uploader
             data-folder="<?= htmlspecialchars($folder) ?>"
             data-upload-url="<?= BASE_URL ?>/admin/upload.php"
             data-csrf="<?= csrf_token() ?>">

            <?php if ($label): ?>
                <label style="display:block; font-family:'Inter',sans-serif; font-weight:600; font-size:14px; color:#0f172a; margin-bottom:8px;">
                    <?= htmlspecialchars($label) ?><?= $required ? ' *' : '' ?>
                </label>
            <?php endif; ?>

            <input type="hidden" name="<?= htmlspecialchars($name) ?>" value="<?= htmlspecialchars($value) ?>" data-hidden>

            <div style="border:2px dashed #cbd5e1; border-radius:12px; padding:16px; background:#f8fafc; transition: border-color 0.2s;">

                <div data-preview style="margin-bottom:12px; text-align:center; min-height:100px; display:flex; align-items:center; justify-content:center; background:#fff; border-radius:8px; padding:8px; overflow:hidden;">
                    <?php if ($value): ?>
                        <img src="<?= htmlspecialchars($value) ?>" alt="Preview" width="320" height="220" style="max-width:100%; max-height:220px; border-radius:6px; display:block;">
                    <?php else: ?>
                        <div style="color:#94a3b8; font-family:'Inter',sans-serif; font-size:14px; padding:24px 0;">
                            <i class="fas fa-image" style="font-size:32px; display:block; margin-bottom:8px; opacity:.5;"></i>
                            No image selected
                        </div>
                    <?php endif; ?>
                </div>

                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <button type="button" data-upload-btn
                            style="display:inline-flex; align-items:center; gap:6px; padding:8px 16px; background:#0084FF; color:#fff; border:none; border-radius:8px; font-family:'Inter',sans-serif; font-weight:600; font-size:13px; cursor:pointer; transition: opacity .2s;">
                        <i class="fas fa-upload"></i> Upload Image
                    </button>
                    <button type="button" data-remove-btn
                            style="display:<?= $value ? 'inline-flex' : 'none' ?>; align-items:center; gap:6px; padding:8px 16px; background:#ef4444; color:#fff; border:none; border-radius:8px; font-family:'Inter',sans-serif; font-weight:600; font-size:13px; cursor:pointer;">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                    <input type="file" accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml" style="display:none;" data-file-input>
                </div>

                <?php if ($help): ?>
                    <div style="margin-top:10px; color:#64748b; font-family:'Inter',sans-serif; font-size:12px;">
                        <i class="fas fa-info-circle"></i> <?= htmlspecialchars($help) ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($showText): ?>
                <input type="text" data-text-input placeholder="Or paste image path manually"
                       value="<?= htmlspecialchars($value) ?>"
                       style="width:100%; margin-top:8px; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; font-family:'Inter',sans-serif; font-size:13px; background:#fff;">
            <?php endif; ?>

        </div>
        <?php
    }
}