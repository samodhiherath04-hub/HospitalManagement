<?php
require_once __DIR__ . '/includes/header.php';
require_role('Admin','Doctor','Nurse');

$q = trim($_GET['q'] ?? '');
$sql = "SELECT m.*, p.first_name pfn, p.last_name pln, p.patient_code,
               d.first_name dfn, d.last_name dln
        FROM medical_records m
        JOIN patients p ON m.patient_id = p.id
        JOIN doctors  d ON m.doctor_id  = d.id";
$params = [];
if ($q !== '') {
    $sql .= " WHERE p.first_name LIKE ? OR p.last_name LIKE ? OR p.patient_code LIKE ? OR m.diagnosis LIKE ?";
    $like = "%$q%"; $params = [$like,$like,$like,$like];
}
$sql .= " ORDER BY m.visit_date DESC, m.id DESC LIMIT 100";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$records = $stmt->fetchAll();
?>
<div class="page-head">
    <h1>Medical Records</h1>
    <a href="medical_record_edit.php" class="btn"><?= icon_svg('plus') ?> New Record</a>
</div>
<div class="card">
    <div class="toolbar">
        <form method="get" class="search-box">
            <?= icon_svg('search') ?>
            <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search patient or diagnosis...">
        </form>
        <span style="font-size:12px;color:var(--gray-5);margin-left:auto;"><?= count($records) ?> records</span>
    </div>
    <div class="table-wrap">
        <table id="searchTable">
            <thead><tr><th>Date</th><th>Patient</th><th>Doctor</th><th>Diagnosis</th><th>Prescription</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($records as $m): ?>
                <tr>
                    <td><?= fmt_date($m['visit_date']) ?></td>
                    <td><?= e($m['pfn'].' '.$m['pln']) ?><br><small style="color:var(--gray-5)"><?= e($m['patient_code']) ?></small></td>
                    <td>Dr. <?= e($m['dfn'].' '.$m['dln']) ?></td>
                    <td><?= e(mb_strimwidth($m['diagnosis'], 0, 60, '...')) ?></td>
                    <td><?= e(mb_strimwidth($m['prescription'], 0, 60, '...')) ?></td>
                    <td>
                        <a href="medical_record_edit.php?id=<?= (int)$m['id'] ?>" class="btn btn-ghost btn-sm"><?= icon_svg('edit') ?></a>
                        <a href="medical_record_delete.php?id=<?= (int)$m['id'] ?>" class="btn btn-ghost btn-sm" data-confirm="Delete record?"><?= icon_svg('trash') ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($records)): ?><tr><td colspan="6" class="empty">No medical records.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
