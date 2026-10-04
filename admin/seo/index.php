<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$page_title = 'SEO';
$current_page = 'seo';

include __DIR__ . '/../../includes/admin-header.php';
include __DIR__ . '/../../includes/admin-navbar.php';
?>
<div class="module-placeholder">
	<h2>SEO Management</h2>
	<p>This module will be implemented in a later step.</p>
	<p><em>Coming soon: Manage page titles, descriptions, and SEO settings.</em></p>
</div>
<?php
include __DIR__ . '/../../includes/admin-footer.php';
?>
