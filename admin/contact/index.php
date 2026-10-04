<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
$page_title = 'Contact Messages';
$current_page = 'contact';
require_once __DIR__ . '/../../includes/functions.php';

$status_filter = $_GET['status'] ?? '';

$where = []; $params = [];
if (in_array($status_filter, ['new','read','contacted','converted','archived'], true)) {
    $where[] = "status = :status";
    $params[':status'] = $status_filter;
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $db->prepare("SELECT * FROM contact_messages $where_sql ORDER BY created_at DESC LIMIT 200");
$stmt->execute($params);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

$new_count = $db->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'")->fetchColumn();

include __DIR__ . '/../../includes/admin-header.php';
include __DIR__ . '/../../includes/admin-navbar.php';
?>
<div class="services-index">
    <div class="index-header">
        <h2>Contact Messages <?php if ($new_count > 0): ?><span class="badge-status active" style="margin-left:8px;"><?= (int)$new_count ?> new</span><?php endif; ?></h2>
    </div>

    <form method="GET" class="filter-form">
        <div class="filter-row">
            <div class="filter-group">
                <select name="status">
                    <option value="">All Status</option>
                    <option value="new"       <?= $status_filter === 'new'       ? 'selected' : '' ?>>New</option>
                    <option value="read"      <?= $status_filter === 'read'      ? 'selected' : '' ?>>Read</option>
                    <option value="contacted" <?= $status_filter === 'contacted' ? 'selected' : '' ?>>Contacted</option>
                    <option value="converted" <?= $status_filter === 'converted' ? 'selected' : '' ?>>Converted</option>
                    <option value="archived"  <?= $status_filter === 'archived'  ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
            <div class="filter-actions">
                <button class="btn btn-secondary">Filter</button>
                <a href="<?= BASE_URL ?>/admin/contact/index.php" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>

    <?php if (empty($messages)): ?>
        <div class="empty-state"><p>No messages found.</p></div>
    <?php else: ?>
        <table class="services-table">
            <thead><tr><th>Date</th><th>Name</th><th>Email</th><th>Phone</th><th>Company</th><th>Services</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($messages as $m): ?>
                <tr>
                    <td><?= date('M j, Y', strtotime($m['created_at'])) ?></td>
                    <td><strong><?= htmlspecialchars($m['name']) ?></strong></td>
                    <td><a href="mailto:<?= htmlspecialchars($m['email']) ?>"><?= htmlspecialchars($m['email']) ?></a></td>
                    <td><?= htmlspecialchars($m['phone'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($m['company'] ?? '—') ?></td>
                    <td><small><?= htmlspecialchars(mb_strimwidth($m['subject'] ?? '—', 0, 50, '…')) ?></small></td>
                    <td><span class="badge-status <?= $m['status'] === 'new' ? 'active' : 'inactive' ?>"><?= ucfirst($m['status']) ?></span></td>
                    <td class="actions">
                        <a href="<?= BASE_URL ?>/admin/contact/view.php?id=<?= (int)$m['id'] ?>" class="btn-edit">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../../includes/admin-footer.php'; ?>