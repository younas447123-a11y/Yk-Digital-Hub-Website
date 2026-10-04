<?php
/**
 * Edit Service – including basic info and all child content (Step 6A + 6B).
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$page_title = 'Edit Service';
$current_page = 'services';

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/image-uploader.php';

// Validate service ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    setFlash('error', 'Invalid service ID.');
    header('Location: index.php');
    exit;
}

// Fetch the service
$stmt = $db->prepare("SELECT * FROM services WHERE id = :id");
$stmt->execute([':id' => $id]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$service) {
    setFlash('error', 'Service not found.');
    header('Location: index.php');
    exit;
}

// Fetch categories for dropdown (child categories only)
$cat_stmt = $db->query("
    SELECT c.id, c.name, p.name as parent_name
    FROM service_categories c
    LEFT JOIN service_categories p ON c.parent_id = p.id
    WHERE c.parent_id IS NOT NULL AND c.is_fixed = 0 AND c.status = 1
    ORDER BY p.sort_order, c.sort_order, c.name
");
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch parent category info for display
$parent_stmt = $db->prepare("
    SELECT p.id as parent_id, p.name as parent_name, c.name as subcategory_name
    FROM service_categories c
    LEFT JOIN service_categories p ON c.parent_id = p.id
    WHERE c.id = :cat_id
");
$parent_stmt->execute([':cat_id' => $service['category_id']]);
$category_info = $parent_stmt->fetch(PDO::FETCH_ASSOC);

// --- Tab handling ---
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'basic';
$allowed_tabs = ['basic', 'sections', 'features', 'process', 'stats', 'technologies', 'faqs'];
if (!in_array($active_tab, $allowed_tabs)) $active_tab = 'basic';

// --- Child record actions (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        header("Location: edit.php?id={$id}&tab={$active_tab}");
        exit;
    }

    // The Basic Information form updates the service itself, not a child record.
    if (!isset($_POST['child_type'])) {
        $service_fields = [
            'category_id', 'title', 'slug', 'short_description', 'card_description',
            'hero_title', 'hero_subtitle', 'hero_description', 'hero_image', 'hero_badge', 'icon',
            'featured', 'sort_order', 'status', 'starting_price', 'price_label', 'delivery_time',
            'service_location', 'primary_cta_text', 'primary_cta_url', 'secondary_cta_text',
            'secondary_cta_url', 'seo_title', 'meta_description', 'focus_keyword', 'canonical_url',
            'og_title', 'og_description', 'og_image', 'robots'
        ];
        $update_data = [];
        foreach ($service_fields as $field) {
            $value = trim((string)($_POST[$field] ?? ''));
            if ($field === 'category_id' || $field === 'featured' || $field === 'sort_order' || $field === 'status') {
                $value = (int)$value;
            } elseif ($field === 'starting_price') {
                $value = $value === '' ? null : (float)$value;
            }
            $update_data[$field] = $value;
        }

        $valid_category_ids = array_map('intval', array_column($categories, 'id'));
        $errors = [];
        if (!in_array($update_data['category_id'], $valid_category_ids, true)) {
            $errors[] = 'Please select a valid service subcategory.';
        }
        if ($update_data['title'] === '') {
            $errors[] = 'Service title is required.';
        }
        if ($update_data['slug'] === '' || !preg_match('/^[a-z0-9-]+$/', $update_data['slug'])) {
            $errors[] = 'Slug must contain only lowercase letters, numbers, and hyphens.';
        }

        if (!$errors) {
            $slug_stmt = $db->prepare('SELECT id FROM services WHERE slug = :slug AND id != :id');
            $slug_stmt->execute([':slug' => $update_data['slug'], ':id' => $id]);
            if ($slug_stmt->fetch()) {
                $errors[] = 'Slug already exists. Please choose a unique slug.';
            }
        }

        if ($errors) {
            foreach ($errors as $error) setFlash('error', $error);
        } else {
            try {
                $set_parts = array_map(static fn($field) => "`$field` = :$field", $service_fields);
                $update_stmt = $db->prepare(
                    'UPDATE services SET ' . implode(', ', $set_parts) . ' WHERE id = :id'
                );
                foreach ($update_data as $field => $value) {
                    $update_stmt->bindValue(':' . $field, $value);
                }
                $update_stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $update_stmt->execute();
                setFlash('success', 'Service updated successfully.');
            } catch (PDOException $e) {
                setFlash('error', 'Could not update service.');
                error_log('[YK Services] Update failed: ' . $e->getMessage());
            }
        }
        header("Location: edit.php?id={$id}&tab=basic");
        exit;
    }

    $action = $_POST['action'] ?? '';
    $child_type = $_POST['child_type'] ?? '';
    $record_id = isset($_POST['record_id']) ? (int)$_POST['record_id'] : 0;
    $service_id = (int)$id;

    // Helper: validate that a record belongs to this service
    function validateOwnership($db, $table, $id, $service_id) {
        $stmt = $db->prepare("SELECT service_id FROM $table WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($row && $row['service_id'] == $service_id);
    }

    // Determine which table and action
    switch ($child_type) {
        case 'section':
            $table = 'service_sections';
            $fields = ['section_type', 'section_label', 'heading', 'subheading', 'content', 'image', 'video_url', 'background_image', 'sort_order', 'status'];
            $required = ['section_type']; // at least one required? we'll make some optional.
            break;
        case 'feature':
            $table = 'service_features';
            $fields = ['title', 'description', 'icon', 'sort_order', 'status'];
            $required = ['title'];
            break;
        case 'process':
            $table = 'service_process_steps';
            $fields = ['step_number', 'title', 'description', 'icon', 'image', 'sort_order', 'status'];
            $required = ['step_number', 'title'];
            break;
        case 'stat':
            $table = 'service_stats';
            $fields = ['value', 'label', 'description', 'icon', 'sort_order', 'status'];
            $required = ['value', 'label'];
            break;
        case 'technology':
            $table = 'service_technologies';
            $fields = ['name', 'icon', 'sort_order', 'status'];
            $required = ['name'];
            break;
        case 'faq':
            $table = 'service_faqs';
            $fields = ['question', 'answer', 'sort_order', 'status'];
            $required = ['question', 'answer'];
            break;
        default:
            setFlash('error', 'Invalid child type.');
            header("Location: edit.php?id={$id}&tab={$active_tab}");
            exit;
    }

    // --- DELETE ---
    if ($action === 'delete') {
        if ($record_id <= 0) {
            setFlash('error', 'Invalid record ID.');
        } else {
            // Verify ownership
            if (!validateOwnership($db, $table, $record_id, $service_id)) {
                setFlash('error', 'Record does not belong to this service.');
            } else {
                try {
                    $stmt = $db->prepare("DELETE FROM $table WHERE id = :id AND service_id = :service_id");
                    $stmt->execute([':id' => $record_id, ':service_id' => $service_id]);
                    setFlash('success', 'Record deleted successfully.');
                } catch (PDOException $e) {
                    setFlash('error', 'Database error: ' . $e->getMessage());
                }
            }
        }
        header("Location: edit.php?id={$id}&tab={$active_tab}");
        exit;
    }

    // --- ADD or UPDATE ---
    // Collect data from POST, only fields that exist in this table
    $data = [];
    $errors = [];
    foreach ($fields as $field) {
        $val = isset($_POST[$field]) ? trim($_POST[$field]) : '';
        if (in_array($field, ['sort_order', 'status', 'step_number'])) {
            // numeric or boolean
            if ($field === 'sort_order') $val = (int)$val;
            elseif ($field === 'status') $val = isset($_POST['status']) ? (int)$_POST['status'] : 1;
            elseif ($field === 'step_number') $val = (int)$val;
        }
        // For some fields, allow empty; for required, we'll check later.
        $data[$field] = $val;
    }

    // Validate required fields
    $missing = [];
    foreach ($required as $req) {
        if (empty($data[$req]) && $data[$req] !== '0' && $data[$req] !== 0) {
            $missing[] = $req;
        }
    }
    if (!empty($missing)) {
        setFlash('error', 'Missing required fields: ' . implode(', ', $missing));
        header("Location: edit.php?id={$id}&tab={$active_tab}");
        exit;
    }

    // Prepare SQL for insert or update
    try {
        if ($action === 'add') {
            $columns = implode(', ', $fields);
            $placeholders = ':' . implode(', :', $fields);
            $sql = "INSERT INTO $table (service_id, $columns) VALUES (:service_id, $placeholders)";
            $stmt = $db->prepare($sql);
            $data['service_id'] = $service_id;
            foreach ($data as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            setFlash('success', 'Record added successfully.');
        } elseif ($action === 'edit') {
            if ($record_id <= 0) {
                setFlash('error', 'Invalid record ID.');
            } else {
                // Verify ownership
                if (!validateOwnership($db, $table, $record_id, $service_id)) {
                    setFlash('error', 'Record does not belong to this service.');
                } else {
                    $set_parts = [];
                    foreach ($fields as $f) {
                        $set_parts[] = "$f = :$f";
                    }
                    $sql = "UPDATE $table SET " . implode(', ', $set_parts) . " WHERE id = :id AND service_id = :service_id";
                    $stmt = $db->prepare($sql);
                    $data['id'] = $record_id;
                    $data['service_id'] = $service_id;
                    foreach ($data as $key => $val) {
                        $stmt->bindValue(':' . $key, $val);
                    }
                    $stmt->execute();
                    setFlash('success', 'Record updated successfully.');
                }
            }
        } else {
            setFlash('error', 'Invalid action.');
        }
    } catch (PDOException $e) {
        setFlash('error', 'Database error: ' . $e->getMessage());
    }
    header("Location: edit.php?id={$id}&tab={$active_tab}");
    exit;
}

// --- Helper function to render child records list ---
function renderChildList($db, $service_id, $table, $fields, $display_fields, $title_field, $edit_form_fields, $child_type) {
    $stmt = $db->prepare("SELECT * FROM $table WHERE service_id = :service_id ORDER BY sort_order ASC, id ASC");
    $stmt->execute([':service_id' => $service_id]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($records)) {
        echo '<p class="empty-state">No records found. Add your first one below.</p>';
    } else {
        echo '<table class="child-table">';
        echo '<thead><tr>';
        foreach ($display_fields as $label) {
            echo "<th>" . htmlspecialchars($label) . "</th>";
        }
        echo '<th>Status</th><th>Sort</th><th>Actions</th>';
        echo '</tr></thead><tbody>';
        foreach ($records as $rec) {
            echo '<tr>';
            foreach ($display_fields as $field) {
                $val = isset($rec[$field]) ? htmlspecialchars($rec[$field]) : '';
                echo "<td>$val</td>";
            }
            $status = $rec['status'] ? 'Active' : 'Inactive';
            echo "<td><span class=\"badge-status " . ($rec['status'] ? 'active' : 'inactive') . "\">$status</span></td>";
            echo "<td>" . (int)$rec['sort_order'] . "</td>";
            echo '<td class="actions">';
            // Edit link: we'll use a GET parameter to show edit form for this record
            echo '<a href="?id=' . $service_id . '&tab=' . $_GET['tab'] . '&edit_id=' . $rec['id'] . '" class="btn-edit">Edit</a>';
            // Delete form (POST)
            echo '<form method="POST" style="display:inline;" onsubmit="return confirm(\'Delete this record?\')">';
            echo '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
            echo '<input type="hidden" name="action" value="delete">';
            echo '<input type="hidden" name="child_type" value="' . $child_type . '">';
            echo '<input type="hidden" name="record_id" value="' . $rec['id'] . '">';
            echo '<button type="submit" class="btn-delete">Delete</button>';
            echo '</form>';
            echo '</td></tr>';
        }
        echo '</tbody></table>';
    }

    // --- Add/Edit form ---
    // Check if we are editing a specific record
    $edit_id = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;
    $edit_data = null;
    if ($edit_id > 0) {
        $stmt = $db->prepare("SELECT * FROM $table WHERE id = :id AND service_id = :service_id");
        $stmt->execute([':id' => $edit_id, ':service_id' => $service_id]);
        $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    $action = $edit_data ? 'edit' : 'add';
    $record_id = $edit_data ? $edit_data['id'] : 0;
    ?>
    <div class="child-form">
        <h4><?= $action === 'edit' ? 'Edit Record' : 'Add New Record' ?></h4>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="<?= $action ?>">
            <input type="hidden" name="child_type" value="<?= $child_type ?>">
            <?php if ($action === 'edit'): ?>
                <input type="hidden" name="record_id" value="<?= $record_id ?>">
            <?php endif; ?>
            <div class="form-row">
                <?php foreach ($edit_form_fields as $field => $config): ?>
                    <?php
                    $type = $config['type'] ?? 'text';
                    $label = $config['label'] ?? ucfirst(str_replace('_', ' ', $field));
                    $required = isset($config['required']) && $config['required'];
                    $value = $edit_data ? ($edit_data[$field] ?? '') : '';
                    ?>
                    <div class="form-group">
                        <?php if ($type !== 'image'): ?>
                            <label for="<?= $field ?>"><?= htmlspecialchars($label) ?><?= $required ? ' *' : '' ?></label>
                        <?php endif; ?>
                        <?php if ($type === 'textarea'): ?>
                            <textarea name="<?= $field ?>" id="<?= $field ?>" rows="3"><?= htmlspecialchars($value) ?></textarea>
                        <?php elseif ($type === 'select'): ?>
                            <select name="<?= $field ?>" id="<?= $field ?>">
                                <?php foreach ($config['options'] as $opt_val => $opt_label): ?>
                                    <option value="<?= $opt_val ?>" <?= ($value == $opt_val) ? 'selected' : '' ?>><?= htmlspecialchars($opt_label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php elseif ($type === 'image'): ?>
                            <?php renderImageUploader([
                                'name' => $field,
                                'value' => $value,
                                'folder' => $config['folder'] ?? 'services',
                                'label' => $label,
                                'required' => $required,
                                'show_text' => true,
                            ]); ?>
                        <?php else: ?>
                            <input type="<?= $type ?>" name="<?= $field ?>" id="<?= $field ?>" value="<?= htmlspecialchars($value) ?>" <?= $required ? 'required' : '' ?>>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $action === 'edit' ? 'Update' : 'Add' ?></button>
                <?php if ($action === 'edit'): ?>
                    <a href="?id=<?= $service_id ?>&tab=<?= $_GET['tab'] ?>" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <?php
}

// --- Start rendering page ---
include __DIR__ . '/../../includes/admin-header.php';
include __DIR__ . '/../../includes/admin-navbar.php';
?>

<div class="service-edit-container">
    <div class="service-context">
        <p>
            <strong>Parent:</strong> <?= htmlspecialchars($category_info['parent_name'] ?? '-') ?> |
            <strong>Subcategory:</strong> <?= htmlspecialchars($category_info['subcategory_name'] ?? '-') ?> |
            <strong>Service:</strong> <?= htmlspecialchars($service['title']) ?>
        </p>
    </div>

    <!-- Tab Navigation -->
    <ul class="tab-nav">
        <li class="<?= $active_tab === 'basic' ? 'active' : '' ?>">
            <a href="?id=<?= $id ?>&tab=basic">Basic Info</a>
        </li>
        <li class="<?= $active_tab === 'sections' ? 'active' : '' ?>">
            <a href="?id=<?= $id ?>&tab=sections">Sections</a>
        </li>
        <li class="<?= $active_tab === 'features' ? 'active' : '' ?>">
            <a href="?id=<?= $id ?>&tab=features">Features</a>
        </li>
        <li class="<?= $active_tab === 'process' ? 'active' : '' ?>">
            <a href="?id=<?= $id ?>&tab=process">Process</a>
        </li>
        <li class="<?= $active_tab === 'stats' ? 'active' : '' ?>">
            <a href="?id=<?= $id ?>&tab=stats">Statistics</a>
        </li>
        <li class="<?= $active_tab === 'technologies' ? 'active' : '' ?>">
            <a href="?id=<?= $id ?>&tab=technologies">Technologies</a>
        </li>
        <li class="<?= $active_tab === 'faqs' ? 'active' : '' ?>">
            <a href="?id=<?= $id ?>&tab=faqs">FAQs</a>
        </li>
    </ul>

    <div class="tab-content">
        <?php if ($active_tab === 'basic'): ?>
            <!-- ===== BASIC INFO TAB (Step 6A) ===== -->
            <div class="service-form-container">
                <h2>Basic Information</h2>
                <?php
                // Include the existing _form.php, but we need to pass service and categories
                // We'll reuse the form from Step 6A by including _form.php
                // But _form.php expects $service and $categories variables.
                // They are already defined.
                include __DIR__ . '/_form.php';
                ?>
            </div>

        <?php elseif ($active_tab === 'sections'): ?>
            <!-- ===== SECTIONS ===== -->
            <h3>Content Sections</h3>
            <?php
            $display_fields = ['section_type', 'section_label', 'heading', 'subheading'];
            $edit_form_fields = [
                'section_type' => ['label' => 'Section Type', 'type' => 'text', 'required' => true],
                'section_label' => ['label' => 'Label', 'type' => 'text'],
                'heading' => ['label' => 'Heading', 'type' => 'text'],
                'subheading' => ['label' => 'Subheading', 'type' => 'text'],
                'content' => ['label' => 'Content', 'type' => 'textarea'],
                'image' => ['label' => 'Image', 'type' => 'image', 'folder' => 'services'],
                'video_url' => ['label' => 'Video URL', 'type' => 'url'],
                'background_image' => ['label' => 'Background Image', 'type' => 'image', 'folder' => 'services'],
                'sort_order' => ['label' => 'Sort Order', 'type' => 'number'],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => [1 => 'Active', 0 => 'Inactive']]
            ];
            renderChildList($db, $id, 'service_sections', array_keys($edit_form_fields), $display_fields, 'section_type', $edit_form_fields, 'section');
            ?>

        <?php elseif ($active_tab === 'features'): ?>
            <!-- ===== FEATURES ===== -->
            <h3>Features</h3>
            <?php
            $display_fields = ['title', 'description', 'icon'];
            $edit_form_fields = [
                'title' => ['label' => 'Title', 'type' => 'text', 'required' => true],
                'description' => ['label' => 'Description', 'type' => 'textarea'],
                'icon' => ['label' => 'Icon', 'type' => 'text'],
                'sort_order' => ['label' => 'Sort Order', 'type' => 'number'],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => [1 => 'Active', 0 => 'Inactive']]
            ];
            renderChildList($db, $id, 'service_features', array_keys($edit_form_fields), $display_fields, 'title', $edit_form_fields, 'feature');
            ?>

        <?php elseif ($active_tab === 'process'): ?>
            <!-- ===== PROCESS ===== -->
            <h3>Process Steps</h3>
            <?php
            $display_fields = ['step_number', 'title', 'description', 'icon'];
            $edit_form_fields = [
                'step_number' => ['label' => 'Step Number', 'type' => 'number', 'required' => true],
                'title' => ['label' => 'Title', 'type' => 'text', 'required' => true],
                'description' => ['label' => 'Description', 'type' => 'textarea'],
                'icon' => ['label' => 'Icon', 'type' => 'text'],
                'image' => ['label' => 'Image', 'type' => 'image', 'folder' => 'services'],
                'sort_order' => ['label' => 'Sort Order', 'type' => 'number'],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => [1 => 'Active', 0 => 'Inactive']]
            ];
            renderChildList($db, $id, 'service_process_steps', array_keys($edit_form_fields), $display_fields, 'title', $edit_form_fields, 'process');
            ?>

        <?php elseif ($active_tab === 'stats'): ?>
            <!-- ===== STATISTICS ===== -->
            <h3>Statistics</h3>
            <?php
            $display_fields = ['value', 'label', 'description', 'icon'];
            $edit_form_fields = [
                'value' => ['label' => 'Value', 'type' => 'text', 'required' => true],
                'label' => ['label' => 'Label', 'type' => 'text', 'required' => true],
                'description' => ['label' => 'Description', 'type' => 'textarea'],
                'icon' => ['label' => 'Icon', 'type' => 'text'],
                'sort_order' => ['label' => 'Sort Order', 'type' => 'number'],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => [1 => 'Active', 0 => 'Inactive']]
            ];
            renderChildList($db, $id, 'service_stats', array_keys($edit_form_fields), $display_fields, 'label', $edit_form_fields, 'stat');
            ?>

        <?php elseif ($active_tab === 'technologies'): ?>
            <!-- ===== TECHNOLOGIES ===== -->
            <h3>Technologies</h3>
            <?php
            $display_fields = ['name', 'icon'];
            $edit_form_fields = [
                'name' => ['label' => 'Name', 'type' => 'text', 'required' => true],
                'icon' => ['label' => 'Icon', 'type' => 'text'],
                'sort_order' => ['label' => 'Sort Order', 'type' => 'number'],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => [1 => 'Active', 0 => 'Inactive']]
            ];
            renderChildList($db, $id, 'service_technologies', array_keys($edit_form_fields), $display_fields, 'name', $edit_form_fields, 'technology');
            ?>

        <?php elseif ($active_tab === 'faqs'): ?>
            <!-- ===== FAQS ===== -->
            <h3>Frequently Asked Questions</h3>
            <?php
            $display_fields = ['question', 'answer'];
            $edit_form_fields = [
                'question' => ['label' => 'Question', 'type' => 'text', 'required' => true],
                'answer' => ['label' => 'Answer', 'type' => 'textarea', 'required' => true],
                'sort_order' => ['label' => 'Sort Order', 'type' => 'number'],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => [1 => 'Active', 0 => 'Inactive']]
            ];
            renderChildList($db, $id, 'service_faqs', array_keys($edit_form_fields), $display_fields, 'question', $edit_form_fields, 'faq');
            ?>
        <?php endif; ?>
    </div>
</div>

<?php
include __DIR__ . '/../../includes/admin-footer.php';
?>