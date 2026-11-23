<?php
require_once __DIR__ . '/includes/header.php';
require_role('Admin','Doctor','Nurse','Receptionist');
$patients = db()->query("SELECT id, patient_code, first_name, last_name FROM patients ORDER BY first_name")->fetchAll();
$doctors  = db()->query("SELECT id, first_name, last_name FROM doctors WHERE status='active' ORDER BY first_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = (int)$_POST['patient_id'];
    $did = (int)$_POST['doctor_id'];
    $room = trim($_POST['room_no'] ?? '');
    $bed  = trim($_POST['bed_no'] ?? '');
    $reason = trim($_POST['reason'] ?? '');
    $rate = (float)($_POST['daily_rate'] ?? 100);
    if (!$pid || !$did) {
        set_flash('error','Patient and doctor required.');
    } else {
        db()->prepare("INSERT INTO admissions (patient_id, doctor_id, admission_date, room_no, bed_no, reason, daily_rate, status)
                       VALUES (?, ?, NOW(), ?, ?, ?, ?, 'admitted')")->execute([$pid,$did,$room,$bed,$reason,$rate]);
        $aid = (int)db()->lastInsertId();
        db()->prepare("UPDATE patients SET status='admitted' WHERE id=?")->execute([$pid]);
        log_action('Admitted patient','admissions',$aid);
        set_flash('success','Patient admitted.');
        header('Location: admissions.php'); exit;
    }
}
?>
<div class="page-head"><h1>New Admission</h1></div>
<form method="post" class="card">
    <div class="form-grid">
        <div class="form-group">
            <label>Patient *</label>
            <select name="patient_id" required>
                <option value="">Select...</option>
                <?php foreach ($patients as $p): ?>
                    <option value="<?= (int)$p['id'] ?>"><?= e($p['patient_code'].' - '.$p['first_name'].' '.$p['last_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Doctor *</label>
            <select name="doctor_id" required>
                <option value="">Select...</option>
                <?php foreach ($doctors as $d): ?>
                    <option value="<?= (int)$d['id'] ?>">Dr. <?= e($d['first_name'].' '.$d['last_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Room No</label><input name="room_no" placeholder="e.g. 201"></div>
        <div class="form-group"><label>Bed No</label><input name="bed_no" placeholder="e.g. A"></div>
        <div class="form-group"><label>Daily Rate</label><input type="number" step="0.01" name="daily_rate" value="100"></div>
        <div class="form-group full"><label>Reason</label><textarea name="reason"></textarea></div>
    </div>
    <div class="btn-row"><button class="btn">Admit Patient</button><a href="admissions.php" class="btn btn-ghost">Cancel</a></div>
</form>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
