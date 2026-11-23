<?php
require_once __DIR__ . '/includes/header.php';
require_role('Admin','Doctor','Lab Staff','Nurse');
$id = (int)($_GET['id'] ?? 0);
$req = null;
if ($id > 0) {
    $stmt = db()->prepare("SELECT lr.*, p.first_name pfn, p.last_name pln, p.patient_code, lt.test_name
                           FROM lab_requests lr
                           JOIN patients p ON lr.patient_id = p.id
                           JOIN lab_tests lt ON lr.test_id = lt.id
                           WHERE lr.id = ?");
    $stmt->execute([$id]);
    $req = $stmt->fetch();
    if (!$req) { set_flash('error','Request not found.'); header('Location: laboratory.php'); exit; }
}
$patients = db()->query("SELECT id, patient_code, first_name, last_name FROM patients ORDER BY first_name")->fetchAll();
$doctors  = db()->query("SELECT id, first_name, last_name FROM doctors WHERE status='active' ORDER BY first_name")->fetchAll();
$tests    = db()->query("SELECT id, test_code, test_name, cost FROM lab_tests ORDER BY test_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = (int)$_POST['patient_id'];
    $did = (int)$_POST['doctor_id'];
    $tid = (int)$_POST['test_id'];
    $date = $_POST['request_date'] ?? date('Y-m-d');
    $notes = trim($_POST['notes'] ?? '');
    if (!$pid || !$did || !$tid) {
        set_flash('error','Patient, doctor and test are required.');
    } else {
        if ($id > 0) {
            db()->prepare("UPDATE lab_requests SET patient_id=?, doctor_id=?, test_id=?, request_date=?, notes=? WHERE id=?")
                ->execute([$pid,$did,$tid,$date,$notes,$id]);
            log_action('Updated lab request','laboratory',$id);
            set_flash('success','Request updated.');
        } else {
            db()->prepare("INSERT INTO lab_requests (patient_id, doctor_id, test_id, request_date, status, notes)
                           VALUES (?,?,?,?,'pending',?)")->execute([$pid,$did,$tid,$date,$notes]);
            $id = (int)db()->lastInsertId();
            log_action('Created lab request','laboratory',$id);
            set_flash('success','Lab request created.');
        }
        header('Location: laboratory.php'); exit;
    }
}
$r = $req ?: [];
?>
<div class="page-head">
    <h1><?= $id > 0 ? 'Edit Lab Request' : 'New Lab Request' ?></h1>
    <a href="laboratory.php" class="btn btn-ghost"><?= icon_svg('arrow-left') ?> Back</a>
</div>
<?php if ($r): ?>
<div class="card" style="margin-bottom:16px;">
    <dl class="detail-list">
        <dt>Patient</dt><dd><?= e($r['pfn'].' '.$r['pln'].' ('.$r['patient_code'].')') ?></dd>
        <dt>Test</dt><dd><?= e($r['test_name']) ?></dd>
        <dt>Requested</dt><dd><?= fmt_date($r['request_date']) ?></dd>
        <dt>Status</dt><dd><span class="badge badge-solid"><?= e($r['status']) ?></span></dd>
    </dl>
</div>
<?php endif; ?>
<form method="post" class="card">
    <div class="form-grid">
        <div class="form-group">
            <label>Patient *</label>
            <select name="patient_id" required>
                <option value="">Select...</option>
                <?php foreach ($patients as $p): ?>
                    <option value="<?= (int)$p['id'] ?>" <?= ($r['patient_id'] ?? '')==$p['id']?'selected':'' ?>><?= e($p['patient_code'].' - '.$p['first_name'].' '.$p['last_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Doctor *</label>
            <select name="doctor_id" required>
                <option value="">Select...</option>
                <?php foreach ($doctors as $d): ?>
                    <option value="<?= (int)$d['id'] ?>" <?= ($r['doctor_id'] ?? '')==$d['id']?'selected':'' ?>>Dr. <?= e($d['first_name'].' '.$d['last_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Test *</label>
            <select name="test_id" required>
                <option value="">Select...</option>
                <?php foreach ($tests as $t): ?>
                    <option value="<?= (int)$t['id'] ?>" <?= ($r['test_id'] ?? '')==$t['id']?'selected':'' ?>><?= e($t['test_name'].' ('.$t['test_code'].') - '.money($t['cost'])) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Request Date</label><input type="date" name="request_date" value="<?= e($r['request_date'] ?? date('Y-m-d')) ?>"></div>
        <div class="form-group full"><label>Notes</label><textarea name="notes"><?= e($r['notes'] ?? '') ?></textarea></div>
    </div>
    <div class="btn-row">
        <button class="btn"><?= $id > 0 ? 'Update Request' : 'Create Request' ?></button>
        <a href="laboratory.php" class="btn btn-ghost">Cancel</a>
    </div>
</form>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
