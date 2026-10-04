<?php
/**
 * Create a new service.
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$page_title = 'Add Service';
$current_page = 'services';

require_once __DIR__ . '/../../includes/functions.php';

// Fetch child categories only (parent_id IS NOT NULL and is_fixed = 0)
$stmt = $db->query("
    SELECT c.id, c.name, p.name as parent_name
    FROM service_categories c
    LEFT JOIN service_categories p ON c.parent_id = p.id
    WHERE c.parent_id IS NOT NULL AND c.is_fixed = 0 AND c.status = 1
    ORDER BY p.sort_order, c.sort_order, c.name
");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($categories)) {
    setFlash('error', 'No subcategories available. Please create a subcategory first.');
    header('Location: categories.php');
    exit;
}

$errors = [];
$form_data = [
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
        // Collect and sanitize
        $form_data['category_id'] = (int)($_POST['category_id'] ?? 0);
        $form_data['title'] = trim($_POST['title'] ?? '');
        $form_data['slug'] = trim($_POST['slug'] ?? '');
        $form_data['short_description'] = trim($_POST['short_description'] ?? '');
        $form_data['card_description'] = trim($_POST['card_description'] ?? '');
        $form_data['hero_title'] = trim($_POST['hero_title'] ?? '');
        $form_data['hero_subtitle'] = trim($_POST['hero_subtitle'] ?? '');
        $form_data['hero_description'] = trim($_POST['hero_description'] ?? '');
        $form_data['hero_image'] = trim($_POST['hero_image'] ?? '');
        $form_data['hero_badge'] = trim($_POST['hero_badge'] ?? '');
        $form_data['icon'] = trim($_POST['icon'] ?? '');
        $form_data['featured'] = isset($_POST['featured']) ? (int)$_POST['featured'] : 0;
        $form_data['sort_order'] = (int)($_POST['sort_order'] ?? 0);
        $form_data['status'] = isset($_POST['status']) ? (int)$_POST['status'] : 1;
        $form_data['starting_price'] = !empty($_POST['starting_price']) ? (float)$_POST['starting_price'] : null;
        $form_data['price_label'] = trim($_POST['price_label'] ?? '');
        $form_data['delivery_time'] = trim($_POST['delivery_time'] ?? '');
        $form_data['service_location'] = trim($_POST['service_location'] ?? '');
        $form_data['primary_cta_text'] = trim($_POST['primary_cta_text'] ?? '');
        $form_data['primary_cta_url'] = trim($_POST['primary_cta_url'] ?? '');
        $form_data['secondary_cta_text'] = trim($_POST['secondary_cta_text'] ?? '');
        $form_data['secondary_cta_url'] = trim($_POST['secondary_cta_url'] ?? '');
        $form_data['seo_title'] = trim($_POST['seo_title'] ?? '');
        $form_data['meta_description'] = trim($_POST['meta_description'] ?? '');
        $form_data['focus_keyword'] = trim($_POST['focus_keyword'] ?? '');
        $form_data['canonical_url'] = trim($_POST['canonical_url'] ?? '');
        $form_data['og_title'] = trim($_POST['og_title'] ?? '');
        $form_data['og_description'] = trim($_POST['og_description'] ?? '');
        $form_data['og_image'] = trim($_POST['og_image'] ?? '');
        $form_data['robots'] = trim($_POST['robots'] ?? 'index, follow');

        // --- Validation ---
        // 1. Category must be a valid child category
        $valid_cat_ids = array_column($categories, 'id');
        if (!in_array($form_data['category_id'], $valid_cat_ids)) {
            $errors[] = 'Please select a valid subcategory.';
        }

        // 2. Title required
        if (empty($form_data['title'])) {
            $errors[] = 'Service title is required.';
        }

        // 3. Slug: auto-generate if empty
        if (empty($form_data['slug'])) {
            $base = $form_data['title'];
            $form_data['slug'] = strtolower(trim(preg_replace('/[^a-zA-Z0-9-]+/', '-', $base), '-'));
        }
        // Validate slug format
        if (!preg_match('/^[a-z0-9-]+$/', $form_data['slug'])) {
            $errors[] = 'Slug must contain only lowercase letters, numbers, and hyphens.';
        }

        // 4. Slug uniqueness
        if (empty($errors)) {
            $stmt = $db->prepare("SELECT id FROM services WHERE slug = :slug");
            $stmt->execute([':slug' => $form_data['slug']]);
            if ($stmt->fetch()) {
                $errors[] = 'Slug already exists. Please choose a unique slug.';
            }
        }

        // If no errors, insert
        if (empty($errors)) {
            try {
                $stmt = $db->prepare("INSERT INTO services (
                    category_id, title, slug, short_description, card_description,
                    hero_title, hero_subtitle, hero_description, hero_image, hero_badge, icon,
                    featured, sort_order, status,
                    starting_price, price_label, delivery_time, service_location,
                    primary_cta_text, primary_cta_url, secondary_cta_text, secondary_cta_url,
                    seo_title, meta_description, focus_keyword, canonical_url,
                    og_title, og_description, og_image, robots
                ) VALUES (
                    :category_id, :title, :slug, :short_description, :card_description,
                    :hero_title, :hero_subtitle, :hero_description, :hero_image, :hero_badge, :icon,
                    :featured, :sort_order, :status,
                    :starting_price, :price_label, :delivery_time, :service_location,
                    :primary_cta_text, :primary_cta_url, :secondary_cta_text, :secondary_cta_url,
                    :seo_title, :meta_description, :focus_keyword, :canonical_url,
                    :og_title, :og_description, :og_image, :robots
                )");
                $stmt->execute([
                    ':category_id' => $form_data['category_id'],
                    ':title' => $form_data['title'],
                    ':slug' => $form_data['slug'],
                    ':short_description' => $form_data['short_description'],
                    ':card_description' => $form_data['card_description'],
                    ':hero_title' => $form_data['hero_title'],
                    ':hero_subtitle' => $form_data['hero_subtitle'],
                    ':hero_description' => $form_data['hero_description'],
                    ':hero_image' => $form_data['hero_image'],
                    ':hero_badge' => $form_data['hero_badge'],
                    ':icon' => $form_data['icon'],
                    ':featured' => $form_data['featured'],
                    ':sort_order' => $form_data['sort_order'],
                    ':status' => $form_data['status'],
                    ':starting_price' => $form_data['starting_price'],
                    ':price_label' => $form_data['price_label'],
                    ':delivery_time' => $form_data['delivery_time'],
                    ':service_location' => $form_data['service_location'],
                    ':primary_cta_text' => $form_data['primary_cta_text'],
                    ':primary_cta_url' => $form_data['primary_cta_url'],
                    ':secondary_cta_text' => $form_data['secondary_cta_text'],
                    ':secondary_cta_url' => $form_data['secondary_cta_url'],
                    ':seo_title' => $form_data['seo_title'],
                    ':meta_description' => $form_data['meta_description'],
                    ':focus_keyword' => $form_data['focus_keyword'],
                    ':canonical_url' => $form_data['canonical_url'],
                    ':og_title' => $form_data['og_title'],
                    ':og_description' => $form_data['og_description'],
                    ':og_image' => $form_data['og_image'],
                    ':robots' => $form_data['robots']
                ]);
                setFlash('success', 'Service created successfully.');
                header('Location: index.php');
                exit;
            } catch (PDOException $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Include layout
include __DIR__ . '/../../includes/admin-header.php';
include __DIR__ . '/../../includes/admin-navbar.php';
?>

<div class="service-form-container">
    <h2>Add Service</h2>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php
    // Include the form partial
    $service = $form_data; // pass for defaults
    include __DIR__ . '/_form.php';
    ?>
</div>

<?php
include __DIR__ . '/../../includes/admin-footer.php';
?>