<?php
require_once __DIR__ . '/includes/header.php';
require_role('Admin');

$rows = db()->query("SELECT al.*, u.username, u.full_name
                     FROM audit_logs al LEFT JOIN users u ON al.user_id = u.id
                     ORDER BY al.timestamp DESC LIMIT 200")->fetchAll();
?>
<div class="page-head"><h1>Audit Logs</h1></div>
<div class="card">
    <div class="table-wrap">
        <table id="searchTable">
            <thead><tr><th>Timestamp</th><th>User</th><th>Action</th><th>Module</th><th>Record ID</th><th>IP</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $l): ?>
                <tr>
                    <td><?= fmt_datetime($l['timestamp']) ?></td>
                    <td><?= e($l['full_name'] ?? 'system') ?><br><small style="color:var(--gray-5)"><?= e($l['username'] ?? '-') ?></small></td>
                    <td><?= e($l['action']) ?></td>
                    <td><span class="badge badge-soft"><?= e($l['module']) ?></span></td>
                    <td><?= $l['record_id'] ? '#' . (int)$l['record_id'] : '-' ?></td>
                    <td><?= e($l['ip_address']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?><tr><td colspan="6" class="empty">No audit entries.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
