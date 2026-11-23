<?php
require_once __DIR__ . '/includes/header.php';
require_role('Admin','Lab Staff');
$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT lr.*, p.first_name pfn, p.last_name pln, p.patient_code, lt.test_name
                       FROM lab_requests lr
                       JOIN patients p ON lr.patient_id = p.id
                       JOIN lab_tests lt ON lr.test_id = lt.id
                       WHERE lr.id = ?");
$stmt->execute([$id]);
$r = $stmt->fetch();
if (!$r) { set_flash('error','Request not found.'); header('Location: laboratory.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? 'pending';
    $result = trim($_POST['result'] ?? '');
    $rdate  = $_POST['result_date'] ?: null;
    db()->prepare("UPDATE lab_requests SET status=?, result=?, result_date=? WHERE id=?")
        ->execute([$status, $result, $rdate, $id]);
    log_action('Updated lab result','laboratory',$id);
    set_flash('success','Lab result saved.');
    header('Location: laboratory.php'); exit;
}
?>
<div class="page-head">
    <h1>Lab Result &middot; #<?= $id ?></h1>
    <a href="laboratory.php" class="btn btn-ghost"><?= icon_svg('arrow-left') ?> Back</a>
</div>
<div class="card" style="margin-bottom:16px;">
    <dl class="detail-list">
        <dt>Patient</dt><dd><?= e($r['pfn'].' '.$r['pln'].' ('.$r['patient_code'].')') ?></dd>
        <dt>Test</dt><dd><?= e($r['test_name']) ?></dd>
        <dt>Requested</dt><dd><?= fmt_date($r['request_date']) ?></dd>
    </dl>
</div>
<form method="post" class="card">
    <div class="form-grid">
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <?php foreach (['pending','sample-collected','in-progress','completed','cancelled'] as $s): ?>
                    <option <?= $r['status']===$s?'selected':'' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Result Date</label><input type="date" name="result_date" value="<?= e($r['result_date'] ?? date('Y-m-d')) ?>"></div>
        <div class="form-group full"><label>Result / Findings</label><textarea name="result" rows="6"><?= e($r['result'] ?? '') ?></textarea></div>
    </div>
    <div class="btn-row">
        <button class="btn">Save Result</button>
        <a href="laboratory.php" class="btn btn-ghost">Cancel</a>
    </div>
</form>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
