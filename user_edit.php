<?php
require_once __DIR__ . '/includes/header.php';
require_role('Admin');
$id = (int)($_GET['id'] ?? 0);
$user = null;
if ($id > 0) {
    $stmt = db()->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if (!$user) { set_flash('error','User not found.'); header('Location: users.php'); exit; }
}
$roles = db()->query("SELECT id, name FROM roles ORDER BY id")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $fullname = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $rid      = (int)$_POST['role_id'];
    $status   = $_POST['status'] ?? 'active';
    $password = $_POST['password'] ?? '';

    if ($username === '' || $fullname === '' || !$rid) {
        set_flash('error','Username, full name and role are required.');
    } else {
        // Check unique username
        $chk = db()->prepare("SELECT id FROM users WHERE username = ? AND id <> ?");
        $chk->execute([$username, $id]);
        if ($chk->fetch()) {
            set_flash('error','Username already taken.');
        } else {
            if ($id > 0) {
                if ($password !== '') {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    db()->prepare("UPDATE users SET username=?, full_name=?, email=?, phone=?, role_id=?, status=?, password=? WHERE id=?")
                        ->execute([$username,$fullname,$email,$phone,$rid,$status,$hash,$id]);
                } else {
                    db()->prepare("UPDATE users SET username=?, full_name=?, email=?, phone=?, role_id=?, status=? WHERE id=?")
                        ->execute([$username,$fullname,$email,$phone,$rid,$status,$id]);
                }
                log_action('Updated user','users',$id);
                set_flash('success','User updated.');
            } else {
                if ($password === '') { set_flash('error','Password required for new user.'); }
                else {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    db()->prepare("INSERT INTO users (username, password, full_name, email, phone, role_id, status)
                                   VALUES (?,?,?,?,?,?,?)")->execute([$username,$hash,$fullname,$email,$phone,$rid,$status]);
                    $id = (int)db()->lastInsertId();
                    log_action('Created user','users',$id);
                    set_flash('success','User created.');
                }
            }
            if (!isset($_SESSION['flash']) || !isset($_SESSION['flash']['error'])) {
                header('Location: users.php'); exit;
            }
        }
    }
}
$u = $user ?: [];
?>
<div class="page-head"><h1><?= $id > 0 ? 'Edit User' : 'New User' ?></h1></div>
<form method="post" class="card">
    <div class="form-grid">
        <div class="form-group"><label>Username *</label><input name="username" value="<?= e($u['username'] ?? '') ?>" required></div>
        <div class="form-group"><label>Full Name *</label><input name="full_name" value="<?= e($u['full_name'] ?? '') ?>" required></div>
        <div class="form-group">
            <label>Role *</label>
            <select name="role_id" required>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= (int)$r['id'] ?>" <?= ($u['role_id'] ?? '')==$r['id']?'selected':'' ?>><?= e($r['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option <?= ($u['status'] ?? 'active')==='active'?'selected':'' ?>>active</option>
                <option <?= ($u['status'] ?? '')==='inactive'?'selected':'' ?>>inactive</option>
            </select>
        </div>
        <div class="form-group"><label>Email</label><input type="email" name="email" value="<?= e($u['email'] ?? '') ?>"></div>
        <div class="form-group"><label>Phone</label><input name="phone" value="<?= e($u['phone'] ?? '') ?>"></div>
        <div class="form-group full">
            <label>Password <?= $id > 0 ? '(leave blank to keep current)' : '*' ?></label>
            <input type="password" name="password" <?= $id > 0 ? '' : 'required' ?> placeholder="min 6 characters">
        </div>
    </div>
    <div class="btn-row"><button class="btn">Save</button><a href="users.php" class="btn btn-ghost">Cancel</a></div>
</form>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
