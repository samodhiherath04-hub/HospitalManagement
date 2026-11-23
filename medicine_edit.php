<?php
require_once __DIR__ . '/includes/header.php';
require_role('Admin','Pharmacist');
$id = (int)($_GET['id'] ?? 0);
$med = null;
if ($id > 0) {
    $stmt = db()->prepare("SELECT * FROM medicines WHERE id = ?");
    $stmt->execute([$id]);
    $med = $stmt->fetch();
    if (!$med) { set_flash('error','Medicine not found.'); header('Location: pharmacy.php'); exit; }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $cat  = trim($_POST['category'] ?? '');
    $manu = trim($_POST['manufacturer'] ?? '');
    $batch= trim($_POST['batch_no'] ?? '');
    $exp  = $_POST['expiry_date'] ?? null;
    $stock= (int)$_POST['stock_qty'];
    $price= (float)$_POST['unit_price'];
    $reord= (int)($_POST['reorder_level'] ?? 50);
    $desc = trim($_POST['description'] ?? '');
    if ($name === '' || $price < 0) {
        set_flash('error','Name and valid price required.');
    } else {
        if ($id > 0) {
            db()->prepare("UPDATE medicines SET name=?, category=?, manufacturer=?, batch_no=?, expiry_date=?, stock_qty=?, unit_price=?, reorder_level=?, description=? WHERE id=?")
                ->execute([$name,$cat,$manu,$batch,$exp,$stock,$price,$reord,$desc,$id]);
            log_action('Updated medicine','pharmacy',$id);
            set_flash('success','Medicine updated.');
        } else {
            $code = 'MED-' . str_pad((int)db()->query("SELECT MAX(id) FROM medicines")->fetchColumn() + 1, 3, '0', STR_PAD_LEFT);
            db()->prepare("INSERT INTO medicines (medicine_code, name, category, manufacturer, batch_no, expiry_date, stock_qty, unit_price, reorder_level, description)
                           VALUES (?,?,?,?,?,?,?,?,?,?)")->execute([$code,$name,$cat,$manu,$batch,$exp,$stock,$price,$reord,$desc]);
            $id = (int)db()->lastInsertId();
            log_action('Added medicine','pharmacy',$id);
            set_flash('success',"Medicine $code added.");
        }
        header('Location: pharmacy.php'); exit;
    }
}
$m = $med ?: [];
?>
<div class="page-head"><h1><?= $id > 0 ? 'Edit Medicine' : 'Add Medicine' ?></h1></div>
<form method="post" class="card">
    <div class="form-grid">
        <div class="form-group full"><label>Name *</label><input name="name" value="<?= e($m['name'] ?? '') ?>" required></div>
        <div class="form-group"><label>Category</label><input name="category" value="<?= e($m['category'] ?? '') ?>"></div>
        <div class="form-group"><label>Manufacturer</label><input name="manufacturer" value="<?= e($m['manufacturer'] ?? '') ?>"></div>
        <div class="form-group"><label>Batch No</label><input name="batch_no" value="<?= e($m['batch_no'] ?? '') ?>"></div>
        <div class="form-group"><label>Expiry Date</label><input type="date" name="expiry_date" value="<?= e($m['expiry_date'] ?? '') ?>"></div>
        <div class="form-group"><label>Stock Qty</label><input type="number" name="stock_qty" value="<?= e($m['stock_qty'] ?? '0') ?>"></div>
        <div class="form-group"><label>Unit Price *</label><input type="number" step="0.01" name="unit_price" value="<?= e($m['unit_price'] ?? '0') ?>" required></div>
        <div class="form-group"><label>Reorder Level</label><input type="number" name="reorder_level" value="<?= e($m['reorder_level'] ?? '50') ?>"></div>
        <div class="form-group full"><label>Description</label><textarea name="description"><?= e($m['description'] ?? '') ?></textarea></div>
    </div>
    <div class="btn-row"><button class="btn">Save</button><a href="pharmacy.php" class="btn btn-ghost">Cancel</a></div>
</form>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
