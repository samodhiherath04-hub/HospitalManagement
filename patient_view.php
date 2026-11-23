<?php
require_once __DIR__ . '/includes/header.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { set_flash('error','Patient not found.'); header('Location: patients.php'); exit; }

// history
$appts = db()->prepare("SELECT a.*, d.first_name dfn, d.last_name dln FROM appointments a JOIN doctors d ON a.doctor_id=d.id WHERE a.patient_id=? ORDER BY a.appointment_date DESC LIMIT 10");
$appts->execute([$id]);
$appts = $appts->fetchAll();

$emr = db()->prepare("SELECT m.*, d.first_name dfn, d.last_name dln FROM medical_records m JOIN doctors d ON m.doctor_id=d.id WHERE m.patient_id=? ORDER BY m.visit_date DESC LIMIT 10");
$emr->execute([$id]);
$emr = $emr->fetchAll();

$bills = db()->prepare("SELECT * FROM bills WHERE patient_id=? ORDER BY bill_date DESC LIMIT 10");
$bills->execute([$id]);
$bills = $bills->fetchAll();
?>
<div class="page-head">
    <h1>Patient <?= e($p['patient_code']) ?></h1>
    <a href="patient_edit.php?id=<?= $id ?>" class="btn"><?= icon_svg('edit') ?> Edit</a>
</div>

<div class="card" style="margin-bottom:16px;">
    <div class="card-head"><h2>Personal Information</h2></div>
    <dl class="detail-list">
        <dt>Name</dt><dd><?= e($p['first_name'].' '.$p['last_name']) ?></dd>
        <dt>Gender</dt><dd><?= e($p['gender']) ?></dd>
        <dt>Date of Birth</dt><dd><?= fmt_date($p['dob']) ?></dd>
        <dt>Blood Group</dt><dd><?= e($p['blood_group'] ?: '-') ?></dd>
        <dt>Phone</dt><dd><?= e($p['phone']) ?></dd>
        <dt>Email</dt><dd><?= e($p['email'] ?: '-') ?></dd>
        <dt>Address</dt><dd><?= e($p['address'] ?: '-') ?></dd>
        <dt>Emergency</dt><dd><?= e($p['emergency_contact'].' ('.$p['emergency_phone'].')') ?></dd>
        <dt>Registered</dt><dd><?= fmt_date($p['registration_date']) ?></dd>
        <dt>Status</dt><dd><span class="badge <?= $p['status']==='admitted'?'badge-solid':'badge-soft' ?>"><?= e($p['status']) ?></span></dd>
    </dl>
</div>

<div class="grid grid-2">
    <div class="card">
        <div class="card-head"><h2>Appointments</h2></div>
        <div class="table-wrap" style="border:none;">
            <table>
                <thead><tr><th>Date</th><th>Doctor</th><th>Type</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($appts as $a): ?>
                    <tr>
                        <td><?= fmt_date($a['appointment_date']) ?></td>
                        <td>Dr. <?= e($a['dfn'].' '.$a['dln']) ?></td>
                        <td><?= e($a['type']) ?></td>
                        <td><span class="badge badge-soft"><?= e($a['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($appts)): ?><tr><td colspan="4" class="empty">No appointments.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card">
        <div class="card-head"><h2>Medical Records</h2></div>
        <div class="table-wrap" style="border:none;">
            <table>
                <thead><tr><th>Date</th><th>Doctor</th><th>Diagnosis</th></tr></thead>
                <tbody>
                <?php foreach ($emr as $m): ?>
                    <tr>
                        <td><?= fmt_date($m['visit_date']) ?></td>
                        <td>Dr. <?= e($m['dfn'].' '.$m['dln']) ?></td>
                        <td><?= e($m['diagnosis']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($emr)): ?><tr><td colspan="3" class="empty">No records.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card" style="margin-top:16px;">
    <div class="card-head"><h2>Billing History</h2></div>
    <div class="table-wrap" style="border:none;">
        <table>
            <thead><tr><th>Bill No</th><th>Date</th><th>Total</th><th>Paid</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($bills as $b): ?>
                <tr>
                    <td><?= e($b['bill_no']) ?></td>
                    <td><?= fmt_date($b['bill_date']) ?></td>
                    <td><?= money($b['total_amount']) ?></td>
                    <td><?= money($b['paid_amount']) ?></td>
                    <td><span class="badge <?= $b['status']==='paid'?'badge-solid':'badge-soft' ?>"><?= e($b['status']) ?></span></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($bills)): ?><tr><td colspan="5" class="empty">No bills.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
