<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
$page_title = 'Add Testimonial';
$current_page = 'testimonials';
require_once __DIR__ . '/../../includes/functions.php';

$services = $db->query("SELECT id, title FROM services WHERE status = 1 ORDER BY title")->fetchAll(PDO::FETCH_ASSOC);
$projects = $db->query("SELECT id, title FROM portfolio_projects WHERE status = 1 ORDER BY title")->fetchAll(PDO::FETCH_ASSOC);

$testimonial = [
    'client_name' => '', 'company_name' => '', 'position' => '', 'location' => '',
    'client_photo' => '', 'rating' => '', 'testimonial' => '',
    'service_id' => null, 'project_id' => null,
    'status' => 1, 'featured' => 0, 'sort_order' => 0,
];
$errors = [];
$action_url = BASE_URL . '/admin/testimonials/create.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
        $fields = ['client_name','company_name','position','location','client_photo','rating','testimonial','service_id','project_id','status','featured','sort_order'];
        foreach ($fields as $f) $testimonial[$f] = trim($_POST[$f] ?? '');
        $testimonial['service_id'] = $testimonial['service_id'] === '' ? null : (int)$testimonial['service_id'];
        $testimonial['project_id'] = $testimonial['project_id'] === '' ? null : (int)$testimonial['project_id'];
        $testimonial['status']     = (int)$testimonial['status'];
        $testimonial['featured']   = (int)$testimonial['featured'];
        $testimonial['sort_order'] = (int)$testimonial['sort_order'];
        $testimonial['rating']     = $testimonial['rating'] === '' ? null : (int)$testimonial['rating'];

        if ($testimonial['client_name'] === '') $errors[] = 'Client name is required.';
        if ($testimonial['testimonial'] === '') $errors[] = 'Review text is required.';
        if ($testimonial['rating'] !== null && ($testimonial['rating'] < 1 || $testimonial['rating'] > 5)) $errors[] = 'Rating must be between 1 and 5.';

        if (empty($errors)) {
            try {
                $stmt = $db->prepare("
                    INSERT INTO testimonials
                    (client_name, company_name, position, location, client_photo, rating, testimonial,
                     service_id, project_id, status, featured, sort_order)
                    VALUES
                    (:client_name, :company_name, :position, :location, :client_photo, :rating, :testimonial,
                     :service_id, :project_id, :status, :featured, :sort_order)
                ");
                $stmt->execute([
                    ':client_name' => $testimonial['client_name'],
                    ':company_name' => $testimonial['company_name'] ?: null,
                    ':position' => $testimonial['position'] ?: null,
                    ':location' => $testimonial['location'] ?: null,
                    ':client_photo' => $testimonial['client_photo'] ?: null,
                    ':rating' => $testimonial['rating'],
                    ':testimonial' => $testimonial['testimonial'],
                    ':service_id' => $testimonial['service_id'],
                    ':project_id' => $testimonial['project_id'],
                    ':status' => $testimonial['status'],
                    ':featured' => $testimonial['featured'],
                    ':sort_order' => $testimonial['sort_order'],
                ]);
                setFlash('success', 'Testimonial created successfully.');
                header('Location: index.php'); exit;
            } catch (PDOException $e) {
                $errors[] = 'Database error while saving.';
            }
        }
    }
}

include __DIR__ . '/../../includes/admin-header.php';
include __DIR__ . '/../../includes/admin-navbar.php';
?>
<div class="service-form-container">
    <h2>Add Testimonial</h2>
    <?php if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <?php include __DIR__ . '/_form.php'; ?>
</div>
<?php include __DIR__ . '/../../includes/admin-footer.php'; ?>