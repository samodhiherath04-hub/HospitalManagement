<?php
require_once __DIR__ . '/includes/header.php';
require_role('Admin');
$id = (int)($_GET['id'] ?? 0);
$dep = null;
if ($id > 0) {
    $stmt = db()->prepare("SELECT * FROM departments WHERE id = ?");
    $stmt->execute([$id]);
    $dep = $stmt->fetch();
    if (!$dep) { set_flash('error','Department not found.'); header('Location: departments.php'); exit; }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $loc  = trim($_POST['location'] ?? '');
    if ($name === '') {
        set_flash('error','Name required.');
    } else {
        if ($id > 0) {
            db()->prepare("UPDATE departments SET name=?, description=?, location=? WHERE id=?")->execute([$name,$desc,$loc,$id]);
            set_flash('success','Department updated.');
        } else {
            db()->prepare("INSERT INTO departments (name, description, location) VALUES (?,?,?)")->execute([$name,$desc,$loc]);
            set_flash('success','Department added.');
        }
        header('Location: departments.php'); exit;
    }
}
$d = $dep ?: [];
?>
<div class="page-head"><h1><?= $id > 0 ? 'Edit Department' : 'New Department' ?></h1></div>
<form method="post" class="card">
    <div class="form-grid">
        <div class="form-group full"><label>Name *</label><input name="name" value="<?= e($d['name'] ?? '') ?>" required></div>
        <div class="form-group full"><label>Description</label><textarea name="description"><?= e($d['description'] ?? '') ?></textarea></div>
        <div class="form-group full"><label>Location</label><input name="location" value="<?= e($d['location'] ?? '') ?>"></div>
    </div>
    <div class="btn-row"><button class="btn">Save</button><a href="departments.php" class="btn btn-ghost">Cancel</a></div>
</form>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
