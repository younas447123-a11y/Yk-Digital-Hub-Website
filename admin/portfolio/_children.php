<?php
/**
 * Reusable child-record manager for portfolio projects.
 * Provides:
 *   yk_child_config($tab, $is_webdev, $is_marketing)  → returns config or null
 *   yk_handle_child_post($db, $project_id, $config, $tab)
 *   yk_render_child_manager($db, $project_id, $config, $tab)
 */
require_once ROOT_PATH . '/includes/image-uploader.php';

function yk_child_config($tab, $is_webdev, $is_marketing) {
    $configs = [
        'sections' => [
            'table' => 'portfolio_sections',
            'label' => 'Section',
            'label_plural' => 'Content Sections',
            'display' => ['section_type', 'heading'],
            'sort_column' => 'sort_order',
            'has_status' => true,
            'columns' => [
                'section_type'     => ['label' => 'Section Type', 'type' => 'text', 'required' => true],
                'section_label'    => ['label' => 'Label', 'type' => 'text'],
                'heading'          => ['label' => 'Heading', 'type' => 'text'],
                'subheading'       => ['label' => 'Subheading', 'type' => 'text'],
                'content'          => ['label' => 'Content', 'type' => 'textarea'],
                'image'            => ['label' => 'Image', 'type' => 'image', 'folder' => 'portfolio'],
                'video_url'        => ['label' => 'Video URL', 'type' => 'text'],
                'background_image' => ['label' => 'Background Image', 'type' => 'image', 'folder' => 'portfolio'],
                'sort_order'       => ['label' => 'Sort Order', 'type' => 'number', 'default' => 0],
                'status'           => ['label' => 'Status', 'type' => 'select', 'options' => [1 => 'Active', 0 => 'Inactive'], 'default' => 1],
            ],
        ],
        'features' => [
            'table' => 'portfolio_features',
            'label' => 'Feature',
            'label_plural' => 'Features',
            'display' => ['title', 'icon'],
            'sort_column' => 'sort_order',
            'has_status' => true,
            'columns' => [
                'title'       => ['label' => 'Title', 'type' => 'text', 'required' => true],
                'description' => ['label' => 'Description', 'type' => 'textarea'],
                'icon'        => ['label' => 'Icon (CSS class)', 'type' => 'text'],
                'image'       => ['label' => 'Image', 'type' => 'image', 'folder' => 'portfolio'],
                'sort_order'  => ['label' => 'Sort Order', 'type' => 'number', 'default' => 0],
                'status'      => ['label' => 'Status', 'type' => 'select', 'options' => [1 => 'Active', 0 => 'Inactive'], 'default' => 1],
            ],
        ],
        'images' => [
            'table' => 'portfolio_images',
            'label' => 'Image',
            'label_plural' => 'Images',
            'display' => ['image', 'image_type', 'alt_text'],
            'sort_column' => 'sort_order',
            'has_status' => true,
            'columns' => [
                'image'       => ['label' => 'Image', 'type' => 'image', 'folder' => 'portfolio', 'required' => true],
                'thumbnail'   => ['label' => 'Thumbnail', 'type' => 'image', 'folder' => 'portfolio'],
                'alt_text'    => ['label' => 'Alt Text', 'type' => 'text'],
                'caption'     => ['label' => 'Caption', 'type' => 'text'],
                'image_type'  => ['label' => 'Image Type', 'type' => 'select', 'options' => [
                    'hero' => 'Hero', 'desktop' => 'Desktop', 'mobile' => 'Mobile',
                    'tablet' => 'Tablet', 'before' => 'Before', 'after' => 'After',
                    'gallery' => 'Gallery', 'mockup' => 'Mockup', 'result' => 'Result'
                ], 'default' => 'gallery'],
                'sort_order'  => ['label' => 'Sort Order', 'type' => 'number', 'default' => 0],
                'status'      => ['label' => 'Status', 'type' => 'select', 'options' => [1 => 'Active', 0 => 'Inactive'], 'default' => 1],
            ],
        ],
        'results' => [
            'table' => 'portfolio_results',
            'label' => 'Result',
            'label_plural' => 'Results',
            'display' => ['metric_value', 'metric_label'],
            'sort_column' => 'sort_order',
            'has_status' => true,
            'columns' => [
                'metric_value' => ['label' => 'Value', 'type' => 'text', 'required' => true],
                'metric_label' => ['label' => 'Label', 'type' => 'text', 'required' => true],
                'description'  => ['label' => 'Description', 'type' => 'textarea'],
                'icon'         => ['label' => 'Icon (CSS class)', 'type' => 'text'],
                'image'        => ['label' => 'Image', 'type' => 'image', 'folder' => 'portfolio'],
                'sort_order'   => ['label' => 'Sort Order', 'type' => 'number', 'default' => 0],
                'status'       => ['label' => 'Status', 'type' => 'select', 'options' => [1 => 'Active', 0 => 'Inactive'], 'default' => 1],
            ],
        ],
    ];

    // Web Development-only
    if ($is_webdev) {
        $configs['challenges'] = [
            'table' => 'portfolio_challenges',
            'label' => 'Challenge',
            'label_plural' => 'Challenges',
            'display' => ['title', 'icon'],
            'sort_column' => 'sort_order',
            'has_status' => true,
            'columns' => [
                'title'       => ['label' => 'Title', 'type' => 'text', 'required' => true],
                'description' => ['label' => 'Description', 'type' => 'textarea'],
                'icon'        => ['label' => 'Icon (CSS class)', 'type' => 'text'],
                'image'       => ['label' => 'Image', 'type' => 'image', 'folder' => 'portfolio'],
                'sort_order'  => ['label' => 'Sort Order', 'type' => 'number', 'default' => 0],
                'status'      => ['label' => 'Status', 'type' => 'select', 'options' => [1 => 'Active', 0 => 'Inactive'], 'default' => 1],
            ],
        ];
        $configs['solutions'] = [
            'table' => 'portfolio_solutions',
            'label' => 'Solution',
            'label_plural' => 'Solutions',
            'display' => ['title', 'icon'],
            'sort_column' => 'sort_order',
            'has_status' => true,
            'columns' => [
                'title'       => ['label' => 'Title', 'type' => 'text', 'required' => true],
                'description' => ['label' => 'Description', 'type' => 'textarea'],
                'content'     => ['label' => 'Content', 'type' => 'textarea'],
                'image'       => ['label' => 'Image', 'type' => 'image', 'folder' => 'portfolio'],
                'icon'        => ['label' => 'Icon (CSS class)', 'type' => 'text'],
                'sort_order'  => ['label' => 'Sort Order', 'type' => 'number', 'default' => 0],
                'status'      => ['label' => 'Status', 'type' => 'select', 'options' => [1 => 'Active', 0 => 'Inactive'], 'default' => 1],
            ],
        ];
    }

    // Digital Marketing-only
    if ($is_marketing) {
        $configs['mkt-services'] = [
            'table' => 'portfolio_marketing_services',
            'label' => 'Service',
            'label_plural' => 'Marketing Services',
            'display' => ['service_name', 'icon'],
            'sort_column' => 'sort_order',
            'has_status' => true,
            'columns' => [
                'service_name' => ['label' => 'Service Name', 'type' => 'text', 'required' => true],
                'description'  => ['label' => 'Description', 'type' => 'textarea'],
                'icon'         => ['label' => 'Icon (CSS class)', 'type' => 'text'],
                'sort_order'   => ['label' => 'Sort Order', 'type' => 'number', 'default' => 0],
                'status'       => ['label' => 'Status', 'type' => 'select', 'options' => [1 => 'Active', 0 => 'Inactive'], 'default' => 1],
            ],
        ];
        $configs['mkt-channels'] = [
            'table' => 'portfolio_marketing_channels',
            'label' => 'Channel',
            'label_plural' => 'Marketing Channels',
            'display' => ['channel_name', 'icon'],
            'sort_column' => 'sort_order',
            'has_status' => true,
            'columns' => [
                'channel_name' => ['label' => 'Channel Name', 'type' => 'text', 'required' => true],
                'description'  => ['label' => 'Description', 'type' => 'textarea'],
                'icon'         => ['label' => 'Icon (CSS class)', 'type' => 'text'],
                'sort_order'   => ['label' => 'Sort Order', 'type' => 'number', 'default' => 0],
                'status'       => ['label' => 'Status', 'type' => 'select', 'options' => [1 => 'Active', 0 => 'Inactive'], 'default' => 1],
            ],
        ];
        $configs['mkt-results'] = [
            'table' => 'portfolio_marketing_results',
            'label' => 'Marketing Result',
            'label_plural' => 'Marketing Results',
            'display' => ['metric_name', 'metric_value', 'improvement_percentage'],
            'sort_column' => 'sort_order',
            'has_status' => true,
            'columns' => [
                'metric_name'            => ['label' => 'Metric Name', 'type' => 'text', 'required' => true],
                'metric_value'           => ['label' => 'Value', 'type' => 'text', 'required' => true],
                'previous_value'         => ['label' => 'Previous Value', 'type' => 'text'],
                'improvement_percentage' => ['label' => 'Improvement %', 'type' => 'text'],
                'description'            => ['label' => 'Description', 'type' => 'textarea'],
                'source'                 => ['label' => 'Source', 'type' => 'text'],
                'icon'                   => ['label' => 'Icon (CSS class)', 'type' => 'text'],
                'sort_order'             => ['label' => 'Sort Order', 'type' => 'number', 'default' => 0],
                'status'                 => ['label' => 'Status', 'type' => 'select', 'options' => [1 => 'Active', 0 => 'Inactive'], 'default' => 1],
            ],
        ];
    }

    return $configs[$tab] ?? null;
}

/**
 * Verify that a child record belongs to the given project.
 */
function yk_owns($db, $table, $record_id, $project_id) {
    $stmt = $db->prepare("SELECT project_id FROM `$table` WHERE id = :id");
    $stmt->execute([':id' => $record_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row && (int)$row['project_id'] === (int)$project_id;
}

/**
 * Handle a POST (add / edit / delete) for a child manager.
 * Redirects when done.
 */
function yk_handle_child_post($db, $project_id, $config, $tab) {
    $action = $_POST['action'] ?? '';
    if (!in_array($action, ['add', 'edit', 'delete'], true)) return;

    $table = $config['table'];
    $record_id = isset($_POST['record_id']) ? (int)$_POST['record_id'] : 0;
    $redirect = "edit.php?id=$project_id&tab=" . urlencode($tab);

    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        header("Location: $redirect");
        exit;
    }

    // ---- DELETE ----
    if ($action === 'delete') {
        if ($record_id <= 0 || !yk_owns($db, $table, $record_id, $project_id)) {
            setFlash('error', 'Record not found or does not belong to this project.');
        } else {
            try {
                $stmt = $db->prepare("DELETE FROM `$table` WHERE id = :id AND project_id = :pid");
                $stmt->execute([':id' => $record_id, ':pid' => $project_id]);
                setFlash('success', $config['label'] . ' deleted successfully.');
            } catch (PDOException $e) {
                setFlash('error', 'Could not delete record.');
            }
        }
        header("Location: $redirect");
        exit;
    }

    // ---- ADD / EDIT ----
    $data = [];
    $errors = [];
    foreach ($config['columns'] as $col => $meta) {
        $val = $_POST[$col] ?? '';
        if (($meta['type'] ?? 'text') === 'number') {
            $val = (int)$val;
        } else {
            $val = is_string($val) ? trim($val) : $val;
        }
        if (!empty($meta['required']) && ($val === '' || $val === null)) {
            $errors[] = $meta['label'] . ' is required.';
        }
        $data[$col] = $val;
    }

    if (!empty($errors)) {
        foreach ($errors as $e) setFlash('error', $e);
        header("Location: $redirect");
        exit;
    }

    try {
        if ($action === 'add') {
            $cols = array_keys($config['columns']);
            $col_sql = 'project_id, ' . implode(', ', $cols);
            $ph = ':project_id, :' . implode(', :', $cols);
            $stmt = $db->prepare("INSERT INTO `$table` ($col_sql) VALUES ($ph)");
            $stmt->bindValue(':project_id', $project_id);
            foreach ($data as $k => $v) $stmt->bindValue(':' . $k, $v);
            $stmt->execute();
            setFlash('success', $config['label'] . ' added successfully.');
        } else {
            if ($record_id <= 0 || !yk_owns($db, $table, $record_id, $project_id)) {
                setFlash('error', 'Record does not belong to this project.');
            } else {
                $set_parts = [];
                foreach (array_keys($config['columns']) as $c) $set_parts[] = "`$c` = :$c";
                $stmt = $db->prepare("UPDATE `$table` SET " . implode(', ', $set_parts) . " WHERE id = :id AND project_id = :pid");
                foreach ($data as $k => $v) $stmt->bindValue(':' . $k, $v);
                $stmt->bindValue(':id', $record_id);
                $stmt->bindValue(':pid', $project_id);
                $stmt->execute();
                setFlash('success', $config['label'] . ' updated successfully.');
            }
        }
    } catch (PDOException $e) {
        setFlash('error', 'Database error: ' . $e->getMessage());
    }
    header("Location: $redirect");
    exit;
}

/**
 * Render the child manager UI (list + add/edit form).
 */
function yk_render_child_manager($db, $project_id, $config, $tab) {
    $edit_id = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;
    $sort_col = $config['sort_column'];

    $stmt = $db->prepare("SELECT * FROM `{$config['table']}` WHERE project_id = :pid ORDER BY `$sort_col` ASC, id ASC");
    $stmt->execute([':pid' => $project_id]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $edit_record = null;
    if ($edit_id > 0) {
        $stmt = $db->prepare("SELECT * FROM `{$config['table']}` WHERE id = :id AND project_id = :pid");
        $stmt->execute([':id' => $edit_id, ':pid' => $project_id]);
        $edit_record = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    ?>
    <div class="child-manager">
        <div class="child-header">
            <h3><?= htmlspecialchars($config['label_plural']) ?> <span class="count">(<?= count($records) ?>)</span></h3>
            <?php if ($edit_record): ?>
                <a href="?id=<?= (int)$project_id ?>&tab=<?= urlencode($tab) ?>" class="btn btn-secondary">Cancel Edit</a>
            <?php endif; ?>
        </div>

        <?php if (empty($records)): ?>
            <p class="empty-state">No <?= htmlspecialchars(strtolower($config['label_plural'])) ?> yet. Add the first one below.</p>
        <?php else: ?>
            <table class="child-table">
                <thead>
                    <tr>
                        <?php foreach ($config['display'] as $col): ?>
                            <th><?= htmlspecialchars($config['columns'][$col]['label'] ?? ucfirst($col)) ?></th>
                        <?php endforeach; ?>
                        <?php if (!empty($config['has_status'])): ?><th>Status</th><?php endif; ?>
                        <th>Sort</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $r): ?>
                        <tr>
                            <?php foreach ($config['display'] as $col): ?>
                                <td><?= htmlspecialchars(mb_strimwidth((string)($r[$col] ?? ''), 0, 80, '…')) ?></td>
                            <?php endforeach; ?>
                            <?php if (!empty($config['has_status'])): ?>
                                <td><span class="badge-status <?= $r['status'] ? 'active' : 'inactive' ?>"><?= $r['status'] ? 'Active' : 'Inactive' ?></span></td>
                            <?php endif; ?>
                            <td><?= (int)$r[$sort_col] ?></td>
                            <td class="actions">
                                <a href="?id=<?= (int)$project_id ?>&tab=<?= urlencode($tab) ?>&edit_id=<?= (int)$r['id'] ?>" class="btn-edit">Edit</a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this <?= htmlspecialchars(strtolower($config['label'])) ?>?')">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="record_id" value="<?= (int)$r['id'] ?>">
                                    <button type="submit" class="btn-delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="child-form">
            <h4><?= $edit_record ? 'Edit' : 'Add New' ?> <?= htmlspecialchars($config['label']) ?></h4>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="<?= $edit_record ? 'edit' : 'add' ?>">
                <?php if ($edit_record): ?>
                    <input type="hidden" name="record_id" value="<?= (int)$edit_record['id'] ?>">
                <?php endif; ?>
                <div class="form-row">
                    <?php foreach ($config['columns'] as $col => $meta): ?>
                        <?php
                        $label = $meta['label'] ?? ucfirst($col);
                        $type = $meta['type'] ?? 'text';
                        $required = !empty($meta['required']);
                        $value = $edit_record[$col] ?? ($meta['default'] ?? '');
                        ?>
                        <div class="form-group">
                            <?php if ($type !== 'image'): ?>
                                <label><?= htmlspecialchars($label) ?><?= $required ? ' *' : '' ?></label>
                            <?php endif; ?>
                            <?php if ($type === 'textarea'): ?>
                                <textarea name="<?= $col ?>" rows="3" <?= $required ? 'required' : '' ?>><?= htmlspecialchars((string)$value) ?></textarea>
                            <?php elseif ($type === 'select'): ?>
                                <select name="<?= $col ?>">
                                    <?php foreach ($meta['options'] as $ov => $ol): ?>
                                        <option value="<?= htmlspecialchars((string)$ov) ?>" <?= ((string)$value === (string)$ov) ? 'selected' : '' ?>><?= htmlspecialchars($ol) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php elseif ($type === 'number'): ?>
                                <input type="number" name="<?= $col ?>" value="<?= htmlspecialchars((string)$value) ?>" min="0">
                            <?php elseif ($type === 'image'): ?>
                                <?php renderImageUploader([
                                    'name' => $col,
                                    'value' => $value,
                                    'folder' => $meta['folder'] ?? 'portfolio',
                                    'label' => $label,
                                    'required' => $required,
                                    'show_text' => true,
                                ]); ?>
                            <?php else: ?>
                                <input type="text" name="<?= $col ?>" value="<?= htmlspecialchars((string)$value) ?>" <?= $required ? 'required' : '' ?>>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><?= $edit_record ? 'Update' : 'Add' ?></button>
                    <?php if ($edit_record): ?>
                        <a href="?id=<?= (int)$project_id ?>&tab=<?= urlencode($tab) ?>" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <?php
}