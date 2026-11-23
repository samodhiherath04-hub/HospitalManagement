<?php
require_once __DIR__ . '/includes/header.php';
require_role('Admin','Doctor','Nurse','Receptionist');

$rows = db()->query("SELECT a.*, p.first_name pfn, p.last_name pln, p.patient_code,
                            d.first_name dfn, d.last_name dln
                     FROM admissions a
                     JOIN patients p ON a.patient_id = p.id
                     JOIN doctors  d ON a.doctor_id  = d.id
                     ORDER BY a.admission_date DESC")->fetchAll();
?>
<div class="page-head">
    <h1>Admissions</h1>
    <a href="admission_edit.php" class="btn"><?= icon_svg('plus') ?> New Admission</a>
</div>
<div class="card">
    <div class="table-wrap">
        <table id="searchTable">
            <thead><tr><th>Patient</th><th>Doctor</th><th>Admitted</th><th>Room</th><th>Bed</th><th>Reason</th><th>Rate/Day</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $a): ?>
                <tr>
                    <td><?= e($a['pfn'].' '.$a['pln']) ?><br><small style="color:var(--gray-5)"><?= e($a['patient_code']) ?></small></td>
                    <td>Dr. <?= e($a['dfn'].' '.$a['dln']) ?></td>
                    <td><?= fmt_datetime($a['admission_date']) ?></td>
                    <td><?= e($a['room_no']) ?></td>
                    <td><?= e($a['bed_no']) ?></td>
                    <td><?= e($a['reason']) ?></td>
                    <td><?= money($a['daily_rate']) ?></td>
                    <td><span class="badge <?= $a['status']==='admitted'?'badge-solid':'badge-soft' ?>"><?= e($a['status']) ?></span></td>
                    <td>
                        <?php if ($a['status']==='admitted'): ?>
                        <a href="admission_discharge.php?id=<?= (int)$a['id'] ?>" class="btn btn-ghost btn-sm" data-confirm="Discharge patient?">Discharge</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?><tr><td colspan="9" class="empty">No admissions.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
