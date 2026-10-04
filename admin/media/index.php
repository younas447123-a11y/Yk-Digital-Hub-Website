<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$page_title = 'Media';
$current_page = 'media';

include __DIR__ . '/../../includes/admin-header.php';
include __DIR__ . '/../../includes/admin-navbar.php';
?>
<div class="module-placeholder">
	<h2>Media Management</h2>
	<p>This module will be implemented in a later step.</p>
	<p><em>Coming soon: Upload, manage, and delete media files.</em></p>
</div>
<?php
include __DIR__ . '/../../includes/admin-footer.php';
?>
