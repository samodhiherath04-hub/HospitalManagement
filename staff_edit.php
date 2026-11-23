<?php
require_once __DIR__ . '/includes/header.php';
require_role('Admin');
$id = (int)($_GET['id'] ?? 0);
$emp = null;
if ($id > 0) {
    $stmt = db()->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->execute([$id]);
    $emp = $stmt->fetch();
    if (!$emp) { set_flash('error','Employee not found.'); header('Location: staff.php'); exit; }
}
$roles = db()->query("SELECT id, name FROM roles ORDER BY id")->fetchAll();
$deps  = db()->query("SELECT id, name FROM departments ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = trim($_POST['first_name'] ?? '');
    $last  = trim($_POST['last_name'] ?? '');
    $rid   = (int)($_POST['role_id'] ?? 0) ?: null;
    $depId = (int)($_POST['department_id'] ?? 0) ?: null;
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $addr  = trim($_POST['address'] ?? '');
    $sal   = (float)($_POST['salary'] ?? 0);
    $join  = $_POST['join_date'] ?? date('Y-m-d');
    $stat  = $_POST['status'] ?? 'active';
    if ($first === '' || $last === '') {
        set_flash('error','First and last name required.');
    } else {
        if ($id > 0) {
            db()->prepare("UPDATE employees SET first_name=?, last_name=?, role_id=?, department_id=?, phone=?, email=?, address=?, salary=?, join_date=?, status=? WHERE id=?")
                ->execute([$first,$last,$rid,$depId,$phone,$email,$addr,$sal,$join,$stat,$id]);
            set_flash('success','Employee updated.');
        } else {
            $code = next_employee_code();
            db()->prepare("INSERT INTO employees (emp_code, first_name, last_name, role_id, department_id, phone, email, address, salary, join_date, status)
                           VALUES (?,?,?,?,?,?,?,?,?,?,?)")->execute([$code,$first,$last,$rid,$depId,$phone,$email,$addr,$sal,$join,$stat]);
            set_flash('success',"Employee $code added.");
        }
        header('Location: staff.php'); exit;
    }
}
$e = $emp ?: [];
?>
<div class="page-head"><h1><?= $id > 0 ? 'Edit Employee' : 'New Employee' ?></h1></div>
<form method="post" class="card">
    <div class="form-grid">
        <div class="form-group"><label>First Name *</label><input name="first_name" value="<?= e($e['first_name'] ?? '') ?>" required></div>
        <div class="form-group"><label>Last Name *</label><input name="last_name" value="<?= e($e['last_name'] ?? '') ?>" required></div>
        <div class="form-group">
            <label>Role</label>
            <select name="role_id">
                <option value="">--</option>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= (int)$r['id'] ?>" <?= ($e['role_id'] ?? '')==$r['id']?'selected':'' ?>><?= e($r['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Department</label>
            <select name="department_id">
                <option value="">--</option>
                <?php foreach ($deps as $d): ?>
                    <option value="<?= (int)$d['id'] ?>" <?= ($e['department_id'] ?? '')==$d['id']?'selected':'' ?>><?= e($d['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Phone</label><input name="phone" value="<?= e($e['phone'] ?? '') ?>"></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" value="<?= e($e['email'] ?? '') ?>"></div>
        <div class="form-group"><label>Salary</label><input type="number" step="0.01" name="salary" value="<?= e($e['salary'] ?? '0') ?>"></div>
        <div class="form-group"><label>Join Date</label><input type="date" name="join_date" value="<?= e($e['join_date'] ?? date('Y-m-d')) ?>"></div>
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <?php foreach (['active','inactive','on-leave'] as $s): ?>
                    <option <?= ($e['status'] ?? 'active')===$s?'selected':'' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group full"><label>Address</label><textarea name="address"><?= e($e['address'] ?? '') ?></textarea></div>
    </div>
    <div class="btn-row"><button class="btn">Save</button><a href="staff.php" class="btn btn-ghost">Cancel</a></div>
</form>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
