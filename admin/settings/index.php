<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$page_title = 'Settings';
$current_page = 'settings';

include __DIR__ . '/../../includes/admin-header.php';
include __DIR__ . '/../../includes/admin-navbar.php';
?>
<div class="module-placeholder">
	<h2>Settings Management</h2>
	<p>This module will be implemented in a later step.</p>
	<p><em>Coming soon: Manage website configuration and settings.</em></p>
</div>
<?php
include __DIR__ . '/../../includes/admin-footer.php';
?>
