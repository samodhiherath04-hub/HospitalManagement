<?php
require_once __DIR__ . '/includes/header.php';
require_role('Admin','Accountant','Receptionist');

$id = (int)($_GET['id'] ?? 0);
$bill = null;
if ($id > 0) {
    $stmt = db()->prepare("SELECT * FROM bills WHERE id = ?");
    $stmt->execute([$id]);
    $bill = $stmt->fetch();
    if (!$bill) { set_flash('error','Bill not found.'); header('Location: billing.php'); exit; }
}
$patients = db()->query("SELECT id, patient_code, first_name, last_name FROM patients ORDER BY first_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = (int)$_POST['patient_id'];
    $consult = (float)($_POST['consultation_charge'] ?? 0);
    $lab     = (float)($_POST['lab_charge'] ?? 0);
    $pharm   = (float)($_POST['pharmacy_charge'] ?? 0);
    $admit   = (float)($_POST['admission_charge'] ?? 0);
    $other   = (float)($_POST['other_charge'] ?? 0);
    $tax     = (float)($_POST['tax'] ?? 0);
    $total   = $consult + $lab + $pharm + $admit + $other + $tax;

    if (!$pid) {
        set_flash('error','Patient is required.');
    } else {
        if ($id > 0) {
            db()->prepare("UPDATE bills SET patient_id=?, consultation_charge=?, lab_charge=?, pharmacy_charge=?, admission_charge=?, other_charge=?, tax=?, total_amount=? WHERE id=?")
                ->execute([$pid,$consult,$lab,$pharm,$admit,$other,$tax,$total,$id]);
            log_action('Updated bill','billing',$id);
            set_flash('success','Bill updated.');
        } else {
            $bno = next_bill_no();
            db()->prepare("INSERT INTO bills (bill_no, patient_id, bill_date, consultation_charge, lab_charge, pharmacy_charge, admission_charge, other_charge, tax, total_amount, paid_amount, status, created_by)
                           VALUES (?,?,?,?,?,?,?,?,?,?,0,'unpaid',?)")
                ->execute([$bno,$pid,date('Y-m-d'),$consult,$lab,$pharm,$admit,$other,$tax,$total,current_user_id()]);
            $id = (int)db()->lastInsertId();
            log_action('Generated bill','billing',$id);
            set_flash('success',"Bill $bno generated.");
        }
        header('Location: bill_view.php?id=' . $id); exit;
    }
}
$b = $bill ?: [];
?>
<div class="page-head">
    <h1><?= $id > 0 ? 'Edit Bill' : 'Generate Bill' ?></h1>
    <a href="billing.php" class="btn btn-ghost"><?= icon_svg('arrow-left') ?> Back</a>
</div>
<form method="post" class="card">
    <div class="form-grid">
        <div class="form-group full">
            <label>Patient *</label>
            <select name="patient_id" required>
                <option value="">Select patient...</option>
                <?php foreach ($patients as $p): ?>
                    <option value="<?= (int)$p['id'] ?>" <?= ($b['patient_id'] ?? '')==$p['id']?'selected':'' ?>><?= e($p['patient_code'].' - '.$p['first_name'].' '.$p['last_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Consultation Charge</label><input type="number" step="0.01" name="consultation_charge" value="<?= e($b['consultation_charge'] ?? '0') ?>"></div>
        <div class="form-group"><label>Lab Charges</label><input type="number" step="0.01" name="lab_charge" value="<?= e($b['lab_charge'] ?? '0') ?>"></div>
        <div class="form-group"><label>Pharmacy Charges</label><input type="number" step="0.01" name="pharmacy_charge" value="<?= e($b['pharmacy_charge'] ?? '0') ?>"></div>
        <div class="form-group"><label>Admission Charges</label><input type="number" step="0.01" name="admission_charge" value="<?= e($b['admission_charge'] ?? '0') ?>"></div>
        <div class="form-group"><label>Other Charges</label><input type="number" step="0.01" name="other_charge" value="<?= e($b['other_charge'] ?? '0') ?>"></div>
        <div class="form-group"><label>Tax</label><input type="number" step="0.01" name="tax" value="<?= e($b['tax'] ?? '0') ?>"></div>
    </div>
    <div class="btn-row">
        <button class="btn"><?= $id > 0 ? 'Update Bill' : 'Generate Bill' ?></button>
        <a href="billing.php" class="btn btn-ghost">Cancel</a>
    </div>
</form>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
