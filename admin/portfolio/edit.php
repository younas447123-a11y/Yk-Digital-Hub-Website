<?php
/**
 * Edit Portfolio Project — Basic Info (Step 10A) + Detailed Content (Step 10B).
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$page_title = 'Edit Portfolio Project';
$current_page = 'portfolio';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/_children.php';

// ---- Load project ----
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { setFlash('error', 'Invalid project ID.'); header('Location: index.php'); exit; }

$stmt = $db->prepare("SELECT * FROM portfolio_projects WHERE id = :id");
$stmt->execute([':id' => $id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$project) { setFlash('error', 'Project not found.'); header('Location: index.php'); exit; }

// ---- Detect parent category ----
$stmt = $db->prepare("
    SELECT c.id AS child_id, c.name AS child_name, c.slug AS child_slug,
           p.id AS parent_id, p.name AS parent_name, p.slug AS parent_slug
    FROM portfolio_categories c
    LEFT JOIN portfolio_categories p ON c.parent_id = p.id
    WHERE c.id = :cid
");
$stmt->execute([':cid' => $project['category_id']]);
$cat = $stmt->fetch(PDO::FETCH_ASSOC);
$parent_slug = $cat['parent_slug'] ?? '';
$is_marketing = ($parent_slug === 'digital-marketing');
$is_webdev    = ($parent_slug === 'web-design-development');

// ---- Tabs ----
$allowed_tabs = ['basic', 'sections', 'features', 'images', 'results'];
if ($is_webdev)    $allowed_tabs = array_merge($allowed_tabs, ['challenges', 'solutions']);
if ($is_marketing) $allowed_tabs = array_merge($allowed_tabs, ['marketing', 'mkt-services', 'mkt-channels', 'mkt-results']);

$active_tab = $_GET['tab'] ?? 'basic';
if (!in_array($active_tab, $allowed_tabs, true)) $active_tab = 'basic';

// ---- Child category list for Basic tab ----
$stmt = $db->query("
    SELECT c.id, c.name, p.name AS parent_name
    FROM portfolio_categories c
    LEFT JOIN portfolio_categories p ON c.parent_id = p.id
    WHERE c.parent_id IS NOT NULL AND c.is_fixed = 0
    ORDER BY p.order_num, c.order_num, c.name
");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
$valid_cat_ids = array_column($categories, 'id');

// =====================================================================
//  POST HANDLERS
// =====================================================================

// ---- Child manager POST (add/edit/delete on any child table) ----
$child_config = yk_child_config($active_tab, $is_webdev, $is_marketing);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $child_config && isset($_POST['action'])) {
    yk_handle_child_post($db, $id, $child_config, $active_tab);
}

// ---- Basic Info POST ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'basic') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
    } else {
        $fields = ['category_id','title','slug','client_name','project_type','industry','location','website_url','short_description','card_description','description','hero_title','hero_subtitle','hero_description','hero_image','hero_video_url','project_year','project_duration','team_size','platform','technologies','project_scope','client_challenge','project_goal','project_summary','cta_title','cta_description','cta_button_text','cta_button_url','featured','sort_order','status','seo_title','meta_description','focus_keyword','canonical_url','og_title','og_description','og_image','robots'];
        $errors = [];
        foreach ($fields as $f) $project[$f] = trim($_POST[$f] ?? '');
        $project['category_id'] = (int)$project['category_id'];
        $project['featured']    = (int)$project['featured'];
        $project['status']      = (int)$project['status'];
        $project['sort_order']  = (int)$project['sort_order'];

        if (!in_array($project['category_id'], $valid_cat_ids, true)) $errors[] = 'Please select a valid subcategory.';
        if ($project['title'] === '') $errors[] = 'Title is required.';
        if ($project['slug'] === '') $project['slug'] = strtolower(trim(preg_replace('/[^a-zA-Z0-9-]+/', '-', $project['title']), '-'));
        if (!preg_match('/^[a-z0-9-]+$/', $project['slug'])) $errors[] = 'Slug must contain only lowercase letters, numbers, and hyphens.';
        if (empty($errors)) {
            $s = $db->prepare("SELECT id FROM portfolio_projects WHERE slug = :slug AND id != :id");
            $s->execute([':slug' => $project['slug'], ':id' => $id]);
            if ($s->fetch()) $errors[] = 'Slug already in use.';
        }

        if (empty($errors)) {
            try {
                $set = [];
                foreach ($fields as $f) $set[] = "`$f` = :$f";
                $stmt = $db->prepare("UPDATE portfolio_projects SET " . implode(', ', $set) . " WHERE id = :id");
                foreach ($fields as $f) {
                    $v = $project[$f];
                    if ($v === '') $v = null;
                    $stmt->bindValue(':' . $f, $v);
                }
                $stmt->bindValue(':id', $id);
                $stmt->execute();
                setFlash('success', 'Project basic info updated.');
            } catch (PDOException $e) {
                $errors[] = 'Database error.';
            }
        }
        foreach ($errors as $e) setFlash('error', $e);
    }
    header("Location: edit.php?id=$id&tab=basic");
    exit;
}

// ---- Marketing Overview POST ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'marketing') {
    if (!$is_marketing) {
        setFlash('error', 'Marketing content is only available for Digital Marketing projects.');
        header("Location: edit.php?id=$id&tab=basic");
        exit;
    }
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        header("Location: edit.php?id=$id&tab=marketing");
        exit;
    }
    $mfields = ['marketing_objective','target_audience','target_location','campaign_duration','campaign_budget','starting_position','strategy_summary','execution_summary','results_summary','roi','traffic_growth','leads_growth','conversion_growth','revenue_growth','status'];
    $data = [];
    foreach ($mfields as $f) $data[$f] = trim($_POST[$f] ?? '');
    $data['status'] = (int)$data['status'];
    $data['campaign_budget'] = ($data['campaign_budget'] === '') ? null : (float)$data['campaign_budget'];

    $exists = $db->prepare("SELECT id FROM marketing_case_studies WHERE project_id = :pid");
    $exists->execute([':pid' => $id]);
    $exists = $exists->fetchColumn();

    try {
        if ($exists) {
            $set = [];
            foreach ($mfields as $f) $set[] = "`$f` = :$f";
            $stmt = $db->prepare("UPDATE marketing_case_studies SET " . implode(', ', $set) . " WHERE project_id = :pid");
            foreach ($data as $k => $v) $stmt->bindValue(':' . $k, $v);
            $stmt->bindValue(':pid', $id);
            $stmt->execute();
            setFlash('success', 'Marketing overview updated.');
        } else {
            $cols = 'project_id, ' . implode(', ', $mfields);
            $ph   = ':project_id, :' . implode(', :', $mfields);
            $stmt = $db->prepare("INSERT INTO marketing_case_studies ($cols) VALUES ($ph)");
            $stmt->bindValue(':project_id', $id);
            foreach ($data as $k => $v) $stmt->bindValue(':' . $k, $v);
            $stmt->execute();
            setFlash('success', 'Marketing overview created.');
        }
    } catch (PDOException $e) {
        setFlash('error', 'Database error: ' . $e->getMessage());
    }
    header("Location: edit.php?id=$id&tab=marketing");
    exit;
}

// =====================================================================
//  RENDER
// =====================================================================
include __DIR__ . '/../../includes/admin-header.php';
include __DIR__ . '/../../includes/admin-navbar.php';
?>

<div class="service-edit-container">
    <div class="service-context">
        <strong>Project:</strong> <?= htmlspecialchars($project['title']) ?> |
        <strong>Subcategory:</strong> <?= htmlspecialchars($cat['child_name'] ?? '-') ?> |
        <strong>Parent:</strong> <?= htmlspecialchars($cat['parent_name'] ?? '-') ?>
        <?php if ($is_marketing): ?>
            <span class="badge badge-admin">Digital Marketing Case Study</span>
        <?php elseif ($is_webdev): ?>
            <span class="badge badge-editor">Web Development Case Study</span>
        <?php endif; ?>
    </div>

    <ul class="tab-nav">
        <li class="<?= $active_tab === 'basic' ? 'active' : '' ?>"><a href="?id=<?= $id ?>&tab=basic">Basic Info</a></li>
        <li class="<?= $active_tab === 'sections' ? 'active' : '' ?>"><a href="?id=<?= $id ?>&tab=sections">Sections</a></li>
        <?php if ($is_webdev): ?>
            <li class="<?= $active_tab === 'challenges' ? 'active' : '' ?>"><a href="?id=<?= $id ?>&tab=challenges">Challenges</a></li>
            <li class="<?= $active_tab === 'solutions' ? 'active' : '' ?>"><a href="?id=<?= $id ?>&tab=solutions">Solutions</a></li>
        <?php endif; ?>
        <li class="<?= $active_tab === 'features' ? 'active' : '' ?>"><a href="?id=<?= $id ?>&tab=features">Features</a></li>
        <li class="<?= $active_tab === 'images' ? 'active' : '' ?>"><a href="?id=<?= $id ?>&tab=images">Images</a></li>
        <li class="<?= $active_tab === 'results' ? 'active' : '' ?>"><a href="?id=<?= $id ?>&tab=results">Results</a></li>
        <?php if ($is_marketing): ?>
            <li class="<?= $active_tab === 'marketing' ? 'active' : '' ?>"><a href="?id=<?= $id ?>&tab=marketing">Marketing Overview</a></li>
            <li class="<?= $active_tab === 'mkt-services' ? 'active' : '' ?>"><a href="?id=<?= $id ?>&tab=mkt-services">Marketing Services</a></li>
            <li class="<?= $active_tab === 'mkt-channels' ? 'active' : '' ?>"><a href="?id=<?= $id ?>&tab=mkt-channels">Channels</a></li>
            <li class="<?= $active_tab === 'mkt-results' ? 'active' : '' ?>"><a href="?id=<?= $id ?>&tab=mkt-results">Mkt. Results</a></li>
        <?php endif; ?>
    </ul>

    <div class="tab-content">
        <?php if ($active_tab === 'basic'): ?>
            <?php
            $action_url = BASE_URL . '/admin/portfolio/edit.php?id=' . $id;
            include __DIR__ . '/_form.php';
            ?>

        <?php elseif ($active_tab === 'marketing' && $is_marketing): ?>
            <?php
            $stmt = $db->prepare("SELECT * FROM marketing_case_studies WHERE project_id = :pid");
            $stmt->execute([':pid' => $id]);
            $mcs = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            ?>
            <form method="POST" class="service-form">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="form" value="marketing">
                <fieldset class="form-section">
                    <legend>Marketing Case Study Overview</legend>
                    <div class="form-row">
                        <div class="form-group"><label>Marketing Objective</label><textarea name="marketing_objective" rows="3"><?= htmlspecialchars($mcs['marketing_objective'] ?? '') ?></textarea></div>
                        <div class="form-group"><label>Target Audience</label><textarea name="target_audience" rows="3"><?= htmlspecialchars($mcs['target_audience'] ?? '') ?></textarea></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Target Location</label><input type="text" name="target_location" value="<?= htmlspecialchars($mcs['target_location'] ?? '') ?>"></div>
                        <div class="form-group"><label>Campaign Duration</label><input type="text" name="campaign_duration" value="<?= htmlspecialchars($mcs['campaign_duration'] ?? '') ?>"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Campaign Budget</label><input type="number" step="0.01" name="campaign_budget" value="<?= htmlspecialchars($mcs['campaign_budget'] ?? '') ?>"></div>
                        <div class="form-group"><label>Starting Position</label><input type="text" name="starting_position" value="<?= htmlspecialchars($mcs['starting_position'] ?? '') ?>"></div>
                    </div>
                    <div class="form-group"><label>Strategy Summary</label><textarea name="strategy_summary" rows="3"><?= htmlspecialchars($mcs['strategy_summary'] ?? '') ?></textarea></div>
                    <div class="form-group"><label>Execution Summary</label><textarea name="execution_summary" rows="3"><?= htmlspecialchars($mcs['execution_summary'] ?? '') ?></textarea></div>
                    <div class="form-group"><label>Results Summary</label><textarea name="results_summary" rows="3"><?= htmlspecialchars($mcs['results_summary'] ?? '') ?></textarea></div>
                    <div class="form-row">
                        <div class="form-group"><label>ROI</label><input type="text" name="roi" value="<?= htmlspecialchars($mcs['roi'] ?? '') ?>"></div>
                        <div class="form-group"><label>Traffic Growth</label><input type="text" name="traffic_growth" value="<?= htmlspecialchars($mcs['traffic_growth'] ?? '') ?>"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Leads Growth</label><input type="text" name="leads_growth" value="<?= htmlspecialchars($mcs['leads_growth'] ?? '') ?>"></div>
                        <div class="form-group"><label>Conversion Growth</label><input type="text" name="conversion_growth" value="<?= htmlspecialchars($mcs['conversion_growth'] ?? '') ?>"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Revenue Growth</label><input type="text" name="revenue_growth" value="<?= htmlspecialchars($mcs['revenue_growth'] ?? '') ?>"></div>
                        <div class="form-group"><label>Status</label>
                            <select name="status">
                                <option value="1" <?= (($mcs['status'] ?? 1) == 1) ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= (($mcs['status'] ?? 1) == 0) ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                </fieldset>
                <div class="form-actions"><button class="btn btn-primary">Save Marketing Overview</button></div>
            </form>

        <?php elseif ($child_config): ?>
            <?php yk_render_child_manager($db, $id, $child_config, $active_tab); ?>

        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/admin-footer.php'; ?>