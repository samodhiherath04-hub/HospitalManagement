<?php
require_once __DIR__ . '/includes/header.php';
require_role('Admin','Doctor','Nurse');

$id = (int)($_GET['id'] ?? 0);
$rec = null;
if ($id > 0) {
    $stmt = db()->prepare("SELECT * FROM medical_records WHERE id = ?");
    $stmt->execute([$id]);
    $rec = $stmt->fetch();
    if (!$rec) { set_flash('error','Record not found.'); header('Location: medical_records.php'); exit; }
}
$patients = db()->query("SELECT id, patient_code, first_name, last_name FROM patients ORDER BY first_name")->fetchAll();
$doctors  = db()->query("SELECT id, first_name, last_name FROM doctors WHERE status='active' ORDER BY first_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = (int)$_POST['patient_id'];
    $did = (int)$_POST['doctor_id'];
    $date = $_POST['visit_date'] ?? date('Y-m-d');
    $diag = trim($_POST['diagnosis'] ?? '');
    $pres = trim($_POST['prescription'] ?? '');
    $treat = trim($_POST['treatment'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    if (!$pid || !$did) {
        set_flash('error','Patient and doctor are required.');
    } else {
        if ($id > 0) {
            db()->prepare("UPDATE medical_records SET patient_id=?, doctor_id=?, visit_date=?, diagnosis=?, prescription=?, treatment=?, notes=? WHERE id=?")
                ->execute([$pid,$did,$date,$diag,$pres,$treat,$notes,$id]);
            log_action('Updated medical record','emr',$id);
            set_flash('success','Record updated.');
        } else {
            db()->prepare("INSERT INTO medical_records (patient_id, doctor_id, visit_date, diagnosis, prescription, treatment, notes)
                           VALUES (?,?,?,?,?,?,?)")->execute([$pid,$did,$date,$diag,$pres,$treat,$notes]);
            $id = (int)db()->lastInsertId();
            log_action('Created medical record','emr',$id);
            set_flash('success','Record created.');
        }
        header('Location: medical_records.php'); exit;
    }
}
$m = $rec ?: [];
?>
<div class="page-head">
    <h1><?= $id > 0 ? 'Edit Record' : 'New Medical Record' ?></h1>
    <a href="medical_records.php" class="btn btn-ghost"><?= icon_svg('arrow-left') ?> Back</a>
</div>
<form method="post" class="card">
    <div class="form-grid">
        <div class="form-group">
            <label>Patient *</label>
            <select name="patient_id" required>
                <option value="">Select...</option>
                <?php foreach ($patients as $p): ?>
                    <option value="<?= (int)$p['id'] ?>" <?= ($m['patient_id'] ?? '')==$p['id']?'selected':'' ?>><?= e($p['patient_code'].' - '.$p['first_name'].' '.$p['last_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Doctor *</label>
            <select name="doctor_id" required>
                <option value="">Select...</option>
                <?php foreach ($doctors as $d): ?>
                    <option value="<?= (int)$d['id'] ?>" <?= ($m['doctor_id'] ?? '')==$d['id']?'selected':'' ?>>Dr. <?= e($d['first_name'].' '.$d['last_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Visit Date</label><input type="date" name="visit_date" value="<?= e($m['visit_date'] ?? date('Y-m-d')) ?>"></div>
        <div class="form-group"></div>
        <div class="form-group full"><label>Diagnosis</label><textarea name="diagnosis"><?= e($m['diagnosis'] ?? '') ?></textarea></div>
        <div class="form-group full"><label>Prescription</label><textarea name="prescription"><?= e($m['prescription'] ?? '') ?></textarea></div>
        <div class="form-group full"><label>Treatment</label><textarea name="treatment"><?= e($m['treatment'] ?? '') ?></textarea></div>
        <div class="form-group full"><label>Notes</label><textarea name="notes"><?= e($m['notes'] ?? '') ?></textarea></div>
    </div>
    <div class="btn-row">
        <button class="btn"><?= $id > 0 ? 'Update Record' : 'Create Record' ?></button>
        <a href="medical_records.php" class="btn btn-ghost">Cancel</a>
    </div>
</form>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
