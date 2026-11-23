<?php
require_once __DIR__ . '/includes/header.php';
$q = trim($_GET['q'] ?? '');
$filter = $_GET['status'] ?? '';
$sql = "SELECT a.*, p.first_name pfn, p.last_name pln, p.patient_code,
               d.first_name dfn, d.last_name dln
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        JOIN doctors  d ON a.doctor_id  = d.id";
$params = [];
$where = [];
if ($q !== '') {
    $where[] = "(p.patient_code LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ? OR d.first_name LIKE ? OR d.last_name LIKE ?)";
    $like = "%$q%"; $params = array_merge($params, [$like,$like,$like,$like,$like]);
}
if ($filter !== '') { $where[] = "a.status = ?"; $params[] = $filter; }
if ($where) { $sql .= " WHERE " . implode(' AND ', $where); }
$sql .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC LIMIT 100";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$appts = $stmt->fetchAll();
?>
<div class="page-head">
    <h1>Appointments</h1>
    <a href="appointment_edit.php" class="btn"><?= icon_svg('plus') ?> Book Appointment</a>
</div>
<div class="card">
    <div class="toolbar">
        <form method="get" class="search-box">
            <?= icon_svg('search') ?>
            <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search patient or doctor...">
        </form>
        <form method="get">
            <select name="status" onchange="this.form.submit()" style="padding:9px 12px;border:1px solid var(--border);border-radius:4px;">
                <option value="">All statuses</option>
                <?php foreach (['scheduled','completed','cancelled','no-show'] as $s): ?>
                    <option value="<?= $s ?>" <?= $filter===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <span style="font-size:12px;color:var(--gray-5);margin-left:auto;"><?= count($appts) ?> records</span>
    </div>
    <div class="table-wrap">
        <table id="searchTable">
            <thead><tr><th>Date</th><th>Time</th><th>Patient</th><th>Doctor</th><th>Type</th><th>Reason</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($appts as $a): ?>
                <tr>
                    <td><?= fmt_date($a['appointment_date']) ?></td>
                    <td><?= date('H:i', strtotime($a['appointment_time'])) ?></td>
                    <td><?= e($a['pfn'].' '.$a['pln']) ?><br><small style="color:var(--gray-5)"><?= e($a['patient_code']) ?></small></td>
                    <td>Dr. <?= e($a['dfn'].' '.$a['dln']) ?></td>
                    <td><?= e($a['type']) ?></td>
                    <td><?= e($a['reason']) ?></td>
                    <td>
                        <?php
                            $cls = ['scheduled'=>'badge-outline','completed'=>'badge-solid','cancelled'=>'badge-soft','no-show'=>'badge-danger'];
                        ?>
                        <span class="badge <?= $cls[$a['status']] ?? 'badge-soft' ?>"><?= e($a['status']) ?></span>
                    </td>
                    <td>
                        <a href="appointment_edit.php?id=<?= (int)$a['id'] ?>" class="btn btn-ghost btn-sm"><?= icon_svg('edit') ?></a>
                        <a href="appointment_action.php?id=<?= (int)$a['id'] ?>&action=complete" class="btn btn-ghost btn-sm" data-confirm="Mark completed?">Done</a>
                        <a href="appointment_action.php?id=<?= (int)$a['id'] ?>&action=cancel" class="btn btn-ghost btn-sm" data-confirm="Cancel appointment?">Cancel</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($appts)): ?><tr><td colspan="8" class="empty">No appointments found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
