<?php
require_once __DIR__ . '/includes/header.php';
require_role('Admin','Lab Staff');
$id = (int)($_GET['id'] ?? 0);
$t = null;
if ($id > 0) {
    $stmt = db()->prepare("SELECT * FROM lab_tests WHERE id = ?");
    $stmt->execute([$id]);
    $t = $stmt->fetch();
    if (!$t) { set_flash('error','Test not found.'); header('Location: laboratory.php?tab=catalog'); exit; }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['test_name'] ?? '');
    $cat  = trim($_POST['category'] ?? '');
    $sample = trim($_POST['sample_type'] ?? '');
    $cost = (float)$_POST['cost'];
    $desc = trim($_POST['description'] ?? '');
    if ($name === '' || $cost < 0) {
        set_flash('error','Name and valid cost are required.');
    } else {
        if ($id > 0) {
            db()->prepare("UPDATE lab_tests SET test_name=?, category=?, sample_type=?, cost=?, description=? WHERE id=?")
                ->execute([$name,$cat,$sample,$cost,$desc,$id]);
            set_flash('success','Test updated.');
        } else {
            $code = 'LT-' . str_pad((int)db()->query("SELECT MAX(id) FROM lab_tests")->fetchColumn() + 1, 2, '0', STR_PAD_LEFT);
            db()->prepare("INSERT INTO lab_tests (test_code, test_name, category, sample_type, cost, description) VALUES (?,?,?,?,?,?)")
                ->execute([$code,$name,$cat,$sample,$cost,$desc]);
            set_flash('success',"Test $code added.");
        }
        header('Location: laboratory.php?tab=catalog'); exit;
    }
}
$x = $t ?: [];
?>
<div class="page-head"><h1><?= $id > 0 ? 'Edit Test' : 'New Test' ?></h1></div>
<form method="post" class="card">
    <div class="form-grid">
        <div class="form-group full"><label>Test Name *</label><input name="test_name" value="<?= e($x['test_name'] ?? '') ?>" required></div>
        <div class="form-group"><label>Category</label><input name="category" value="<?= e($x['category'] ?? '') ?>"></div>
        <div class="form-group"><label>Sample Type</label><input name="sample_type" value="<?= e($x['sample_type'] ?? '') ?>"></div>
        <div class="form-group"><label>Cost *</label><input type="number" step="0.01" name="cost" value="<?= e($x['cost'] ?? '0') ?>" required></div>
        <div class="form-group"></div>
        <div class="form-group full"><label>Description</label><textarea name="description"><?= e($x['description'] ?? '') ?></textarea></div>
    </div>
    <div class="btn-row"><button class="btn">Save</button><a href="laboratory.php?tab=catalog" class="btn btn-ghost">Cancel</a></div>
</form>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
