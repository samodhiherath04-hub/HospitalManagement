<?php
require_once __DIR__ . '/includes/header.php';
$id = (int)($_GET['id'] ?? 0);
$appt = null;
if ($id > 0) {
    $stmt = db()->prepare("SELECT * FROM appointments WHERE id = ?");
    $stmt->execute([$id]);
    $appt = $stmt->fetch();
    if (!$appt) { set_flash('error','Appointment not found.'); header('Location: appointments.php'); exit; }
}
$patients = db()->query("SELECT id, patient_code, first_name, last_name FROM patients ORDER BY first_name")->fetchAll();
$doctors  = db()->query("SELECT id, doctor_code, first_name, last_name, consultation_fee FROM doctors WHERE status='active' ORDER BY first_name")->fetchAll();
$fees = [];
foreach ($doctors as $d) { $fees[$d['id']] = $d['consultation_fee']; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = (int)$_POST['patient_id'];
    $did = (int)$_POST['doctor_id'];
    $date = $_POST['appointment_date'] ?? '';
    $time = $_POST['appointment_time'] ?? '';
    $type = $_POST['type'] ?? 'Consultation';
    $status = $_POST['status'] ?? 'scheduled';
    $reason = trim($_POST['reason'] ?? '');
    $notes  = trim($_POST['notes'] ?? '');

    if (!$pid || !$did || !$date || !$time) {
        set_flash('error','Patient, doctor, date and time are required.');
    } else {
        if ($id > 0) {
            db()->prepare("UPDATE appointments SET patient_id=?, doctor_id=?, appointment_date=?, appointment_time=?, type=?, status=?, reason=?, notes=? WHERE id=?")
                ->execute([$pid,$did,$date,$time,$type,$status,$reason,$notes,$id]);
            log_action('Updated appointment','appointments',$id);
            set_flash('success','Appointment updated.');
        } else {
            db()->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, type, status, reason, notes, created_by)
                           VALUES (?,?,?,?,?,?,?,?,?)")
                ->execute([$pid,$did,$date,$time,$type,$status,$reason,$notes,current_user_id()]);
            $id = (int)db()->lastInsertId();
            log_action('Booked appointment','appointments',$id);
            set_flash('success','Appointment booked.');
        }
        header('Location: appointments.php'); exit;
    }
}
$a = $appt ?: [];
?>
<div class="page-head">
    <h1><?= $id > 0 ? 'Edit Appointment' : 'Book Appointment' ?></h1>
    <a href="appointments.php" class="btn btn-ghost"><?= icon_svg('arrow-left') ?> Back</a>
</div>
<form method="post" class="card">
    <div class="form-grid">
        <div class="form-group">
            <label>Patient *</label>
            <select name="patient_id" required>
                <option value="">Select patient...</option>
                <?php foreach ($patients as $p): ?>
                    <option value="<?= (int)$p['id'] ?>" <?= ($a['patient_id'] ?? '')==$p['id']?'selected':'' ?>><?= e($p['patient_code'].' - '.$p['first_name'].' '.$p['last_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Doctor *</label>
            <select id="doctor_id" name="doctor_id" required data-fees='<?= json_encode($fees) ?>'>
                <option value="">Select doctor...</option>
                <?php foreach ($doctors as $d): ?>
                    <option value="<?= (int)$d['id'] ?>" <?= ($a['doctor_id'] ?? '')==$d['id']?'selected':'' ?>>Dr. <?= e($d['first_name'].' '.$d['last_name'].' ('.$d['doctor_code'].')') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Date *</label><input type="date" name="appointment_date" value="<?= e($a['appointment_date'] ?? date('Y-m-d')) ?>" required></div>
        <div class="form-group"><label>Time *</label><input type="time" name="appointment_time" value="<?= e($a['appointment_time'] ?? '09:00') ?>" required></div>
        <div class="form-group">
            <label>Type</label>
            <select name="type">
                <?php foreach (['Consultation','Follow-up','Emergency','Procedure'] as $t): ?>
                    <option <?= ($a['type'] ?? 'Consultation')===$t?'selected':'' ?>><?= $t ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <?php foreach (['scheduled','completed','cancelled','no-show'] as $s): ?>
                    <option <?= ($a['status'] ?? 'scheduled')===$s?'selected':'' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group full"><label>Reason</label><input name="reason" value="<?= e($a['reason'] ?? '') ?>"></div>
        <div class="form-group full"><label>Notes</label><textarea name="notes"><?= e($a['notes'] ?? '') ?></textarea></div>
    </div>
    <div class="btn-row">
        <button class="btn"><?= $id > 0 ? 'Update Appointment' : 'Book Appointment' ?></button>
        <a href="appointments.php" class="btn btn-ghost">Cancel</a>
    </div>
</form>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
