<?php
require_once __DIR__ . '/includes/header.php';
require_role('Admin','Accountant','Receptionist');

$bill_id = (int)($_GET['bill_id'] ?? 0);
$stmt = db()->prepare("SELECT b.*, p.first_name pfn, p.last_name pln, p.patient_code
                       FROM bills b JOIN patients p ON b.patient_id = p.id WHERE b.id = ?");
$stmt->execute([$bill_id]);
$b = $stmt->fetch();
if (!$b) { set_flash('error','Bill not found.'); header('Location: billing.php'); exit; }
$outstanding = $b['total_amount'] - $b['paid_amount'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float)$_POST['amount'];
    $method = $_POST['method'] ?? 'cash';
    $ref    = trim($_POST['reference_no'] ?? '');
    if ($amount <= 0) {
        set_flash('error','Amount must be positive.');
    } elseif ($amount > $outstanding + 0.01) {
        set_flash('error','Amount exceeds outstanding balance of ' . money($outstanding));
    } else {
        db()->prepare("INSERT INTO payments (bill_id, payment_date, amount, method, reference_no, received_by)
                       VALUES (?, NOW(), ?, ?, ?, ?)")
            ->execute([$bill_id, $amount, $method, $ref, current_user_id()]);
        $newPaid = $b['paid_amount'] + $amount;
        $status = $newPaid >= $b['total_amount'] - 0.01 ? 'paid' : 'partial';
        db()->prepare("UPDATE bills SET paid_amount = ?, status = ? WHERE id = ?")
            ->execute([$newPaid, $status, $bill_id]);
        log_action('Recorded payment','billing',$bill_id);
        set_flash('success','Payment of ' . money($amount) . ' recorded.');
        header('Location: bill_view.php?id=' . $bill_id); exit;
    }
}
?>
<div class="page-head"><h1>Record Payment</h1></div>
<div class="card" style="max-width:560px;margin:0 auto;">
    <dl class="detail-list" style="margin-bottom:20px;">
        <dt>Bill No</dt><dd><?= e($b['bill_no']) ?></dd>
        <dt>Patient</dt><dd><?= e($b['pfn'].' '.$b['pln'].' ('.$b['patient_code'].')') ?></dd>
        <dt>Total</dt><dd><?= money($b['total_amount']) ?></dd>
        <dt>Paid</dt><dd><?= money($b['paid_amount']) ?></dd>
        <dt>Outstanding</dt><dd><strong><?= money($outstanding) ?></strong></dd>
    </dl>
    <form method="post">
        <div class="form-grid">
            <div class="form-group"><label>Amount *</label><input type="number" step="0.01" name="amount" value="<?= e($outstanding) ?>" required></div>
            <div class="form-group">
                <label>Method</label>
                <select name="method">
                    <?php foreach (['cash','card','insurance','bank-transfer','upi'] as $m): ?>
                        <option><?= $m ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group full"><label>Reference No</label><input name="reference_no" placeholder="Optional"></div>
        </div>
        <div class="btn-row">
            <button class="btn">Record Payment</button>
            <a href="bill_view.php?id=<?= $bill_id ?>" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
