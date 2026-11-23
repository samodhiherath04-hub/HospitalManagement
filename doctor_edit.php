<?php
require_once __DIR__ . '/includes/header.php';
$id = (int)($_GET['id'] ?? 0);
$doctor = null;
if ($id > 0) {
    $stmt = db()->prepare("SELECT * FROM doctors WHERE id = ?");
    $stmt->execute([$id]);
    $doctor = $stmt->fetch();
    if (!$doctor) { set_flash('error','Doctor not found.'); header('Location: doctors.php'); exit; }
}
$deps = db()->query("SELECT id, name FROM departments ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = trim($_POST['first_name'] ?? '');
    $last  = trim($_POST['last_name'] ?? '');
    $spec  = trim($_POST['specialization'] ?? '');
    $dep   = (int)($_POST['department_id'] ?? 0) ?: null;
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $qual  = trim($_POST['qualification'] ?? '');
    $exp   = (int)($_POST['experience_years'] ?? 0);
    $fee   = (float)($_POST['consultation_fee'] ?? 0);
    $stat  = $_POST['status'] ?? 'active';

    if ($first === '' || $last === '') {
        set_flash('error', 'First and last name are required.');
    } else {
        if ($id > 0) {
            db()->prepare("UPDATE doctors SET first_name=?, last_name=?, specialization=?, department_id=?, phone=?, email=?, qualification=?, experience_years=?, consultation_fee=?, status=? WHERE id=?")
                ->execute([$first,$last,$spec,$dep,$phone,$email,$qual,$exp,$fee,$stat,$id]);
            log_action('Updated doctor','doctors',$id);
            set_flash('success','Doctor updated.');
        } else {
            $code = next_doctor_code();
            db()->prepare("INSERT INTO doctors (doctor_code, first_name, last_name, specialization, department_id, phone, email, qualification, experience_years, consultation_fee, status)
                           VALUES (?,?,?,?,?,?,?,?,?,?,?)")
                ->execute([$code,$first,$last,$spec,$dep,$phone,$email,$qual,$exp,$fee,$stat]);
            $id = (int)db()->lastInsertId();
            log_action('Added doctor','doctors',$id);
            set_flash('success',"Doctor added with code $code.");
        }
        header('Location: doctors.php'); exit;
    }
}
$d = $doctor ?: [];
?>
<div class="page-head">
    <h1><?= $id > 0 ? 'Edit Doctor' : 'New Doctor' ?></h1>
    <a href="doctors.php" class="btn btn-ghost"><?= icon_svg('arrow-left') ?> Back</a>
</div>
<form method="post" class="card">
    <div class="form-grid">
        <div class="form-group"><label>First Name *</label><input name="first_name" value="<?= e($d['first_name'] ?? '') ?>" required></div>
        <div class="form-group"><label>Last Name *</label><input name="last_name" value="<?= e($d['last_name'] ?? '') ?>" required></div>
        <div class="form-group"><label>Specialization</label><input name="specialization" value="<?= e($d['specialization'] ?? '') ?>"></div>
        <div class="form-group">
            <label>Department</label>
            <select name="department_id">
                <option value="">--</option>
                <?php foreach ($deps as $dep): ?>
                    <option value="<?= (int)$dep['id'] ?>" <?= ($d['department_id'] ?? '')==$dep['id']?'selected':'' ?>><?= e($dep['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Phone</label><input name="phone" value="<?= e($d['phone'] ?? '') ?>"></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" value="<?= e($d['email'] ?? '') ?>"></div>
        <div class="form-group"><label>Qualification</label><input name="qualification" value="<?= e($d['qualification'] ?? '') ?>"></div>
        <div class="form-group"><label>Experience (years)</label><input type="number" name="experience_years" value="<?= e($d['experience_years'] ?? '0') ?>"></div>
        <div class="form-group"><label>Consultation Fee</label><input type="number" step="0.01" name="consultation_fee" value="<?= e($d['consultation_fee'] ?? '0') ?>"></div>
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option <?= ($d['status'] ?? 'active')==='active'?'selected':'' ?>>active</option>
                <option <?= ($d['status'] ?? '')==='inactive'?'selected':'' ?>>inactive</option>
            </select>
        </div>
    </div>
    <div class="btn-row">
        <button class="btn"><?= $id > 0 ? 'Update Doctor' : 'Add Doctor' ?></button>
        <a href="doctors.php" class="btn btn-ghost">Cancel</a>
    </div>
</form>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
