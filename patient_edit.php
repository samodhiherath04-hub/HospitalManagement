<?php
require_once __DIR__ . '/includes/header.php';

$id      = (int)($_GET['id'] ?? 0);
$patient = null;
if ($id > 0) {
    $stmt = db()->prepare("SELECT * FROM patients WHERE id = ?");
    $stmt->execute([$id]);
    $patient = $stmt->fetch();
    if (!$patient) {
        set_flash('error', 'Patient not found.');
        header('Location: patients.php'); exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first   = trim($_POST['first_name'] ?? '');
    $last    = trim($_POST['last_name'] ?? '');
    $gender  = $_POST['gender'] ?? 'Male';
    $dob     = $_POST['dob'] ?: null;
    $blood   = trim($_POST['blood_group'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $ec      = trim($_POST['emergency_contact'] ?? '');
    $ep      = trim($_POST['emergency_phone'] ?? '');
    $status  = $_POST['status'] ?? 'active';

    if ($first === '' || $last === '' || $phone === '') {
        set_flash('error', 'First name, last name and phone are required.');
    } else {
        if ($id > 0) {
            $stmt = db()->prepare("UPDATE patients
                SET first_name=?, last_name=?, gender=?, dob=?, blood_group=?, phone=?, email=?,
                    address=?, emergency_contact=?, emergency_phone=?, status=?
                WHERE id=?");
            $stmt->execute([$first,$last,$gender,$dob,$blood,$phone,$email,$address,$ec,$ep,$status,$id]);
            log_action('Updated patient', 'patients', $id);
            set_flash('success', 'Patient updated successfully.');
        } else {
            $code = next_patient_code();
            $reg  = date('Y-m-d');
            $stmt = db()->prepare("INSERT INTO patients
                (patient_code, first_name, last_name, gender, dob, blood_group, phone, email, address, emergency_contact, emergency_phone, registration_date, status)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$code,$first,$last,$gender,$dob,$blood,$phone,$email,$address,$ec,$ep,$reg,$status]);
            $id = (int)db()->lastInsertId();
            log_action('Registered patient', 'patients', $id);
            set_flash('success', "Patient registered with code $code.");
        }
        header('Location: patients.php'); exit;
    }
}

$p = $patient ?: [];
?>
<div class="page-head">
    <h1><?= $id > 0 ? 'Edit Patient' : 'New Patient' ?></h1>
    <a href="patients.php" class="btn btn-ghost"><?= icon_svg('arrow-left') ?> Back</a>
</div>

<form method="post" class="card">
    <div class="form-grid">
        <div class="form-group">
            <label>First Name *</label>
            <input type="text" name="first_name" value="<?= e($p['first_name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Last Name *</label>
            <input type="text" name="last_name" value="<?= e($p['last_name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Gender *</label>
            <select name="gender">
                <?php foreach (['Male','Female','Other'] as $g): ?>
                    <option <?= ($p['gender'] ?? '')===$g?'selected':'' ?>><?= $g ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Date of Birth</label>
            <input type="date" name="dob" value="<?= e($p['dob'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Blood Group</label>
            <select name="blood_group">
                <option value="">--</option>
                <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $b): ?>
                    <option <?= ($p['blood_group'] ?? '')===$b?'selected':'' ?>><?= $b ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Phone *</label>
            <input type="text" name="phone" value="<?= e($p['phone'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= e($p['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <?php foreach (['active','inactive','admitted'] as $s): ?>
                    <option <?= ($p['status'] ?? 'active')===$s?'selected':'' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group full">
            <label>Address</label>
            <textarea name="address"><?= e($p['address'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label>Emergency Contact</label>
            <input type="text" name="emergency_contact" value="<?= e($p['emergency_contact'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Emergency Phone</label>
            <input type="text" name="emergency_phone" value="<?= e($p['emergency_phone'] ?? '') ?>">
        </div>
    </div>
    <div class="btn-row">
        <button type="submit" class="btn"><?= $id > 0 ? 'Update Patient' : 'Register Patient' ?></button>
        <a href="patients.php" class="btn btn-ghost">Cancel</a>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
