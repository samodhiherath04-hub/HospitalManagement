<?php
require_once __DIR__ . '/includes/header.php';
require_role('Admin','Accountant','Receptionist');

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT b.*, p.first_name pfn, p.last_name pln, p.patient_code, p.address, p.phone
                       FROM bills b JOIN patients p ON b.patient_id = p.id WHERE b.id = ?");
$stmt->execute([$id]);
$b = $stmt->fetch();
if (!$b) { set_flash('error','Bill not found.'); header('Location: billing.php'); exit; }

$payments = db()->prepare("SELECT * FROM payments WHERE bill_id = ? ORDER BY payment_date");
$payments->execute([$id]);
$payments = $payments->fetchAll();
$outstanding = $b['total_amount'] - $b['paid_amount'];
?>
<div class="page-head no-print">
    <h1>Invoice <?= e($b['bill_no']) ?></h1>
    <button id="printBtn" class="btn btn-ghost"><?= icon_svg('print') ?> Print</button>
</div>

<div class="card" style="max-width:800px;margin:0 auto;">
    <div style="display:flex;justify-content:space-between;border-bottom:2px solid var(--black);padding-bottom:16px;margin-bottom:16px;">
        <div>
            <div style="font-size:20px;font-weight:700;letter-spacing:4px;">HMS</div>
            <div style="font-size:11px;color:var(--gray-5);text-transform:uppercase;letter-spacing:1.5px;">Hospital Management System</div>
            <div style="font-size:12px;color:var(--gray-5);margin-top:6px;">123 Healthcare Avenue<br>Springfield, 00100<br>Tel: 555-0000</div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:12px;text-transform:uppercase;letter-spacing:2px;font-weight:600;">Invoice</div>
            <div style="font-size:18px;font-weight:700;"><?= e($b['bill_no']) ?></div>
            <div style="font-size:12px;color:var(--gray-5);margin-top:6px;">Date: <?= fmt_date($b['bill_date']) ?></div>
        </div>
    </div>

    <div style="display:flex;justify-content:space-between;margin-bottom:16px;font-size:13px;">
        <div>
            <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--gray-5);margin-bottom:4px;">Billed To</div>
            <div style="font-weight:600;"><?= e($b['pfn'].' '.$b['pln']) ?></div>
            <div><?= e($b['patient_code']) ?></div>
            <div><?= e($b['phone']) ?></div>
            <div><?= e($b['address']) ?></div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--gray-5);margin-bottom:4px;">Status</div>
            <div><span class="badge <?= $b['status']==='paid'?'badge-solid':'badge-danger' ?>"><?= e($b['status']) ?></span></div>
        </div>
    </div>

    <table>
        <thead><tr><th>Description</th><th style="text-align:right;">Amount</th></tr></thead>
        <tbody>
            <tr><td>Consultation</td><td style="text-align:right;"><?= money($b['consultation_charge']) ?></td></tr>
            <tr><td>Laboratory</td><td style="text-align:right;"><?= money($b['lab_charge']) ?></td></tr>
            <tr><td>Pharmacy</td><td style="text-align:right;"><?= money($b['pharmacy_charge']) ?></td></tr>
            <tr><td>Admission</td><td style="text-align:right;"><?= money($b['admission_charge']) ?></td></tr>
            <tr><td>Other</td><td style="text-align:right;"><?= money($b['other_charge']) ?></td></tr>
            <tr><td>Tax</td><td style="text-align:right;"><?= money($b['tax']) ?></td></tr>
            <tr style="border-top:2px solid var(--black);">
                <td style="font-weight:700;text-transform:uppercase;letter-spacing:1px;">Total</td>
                <td style="text-align:right;font-weight:700;font-size:16px;"><?= money($b['total_amount']) ?></td>
            </tr>
            <tr><td>Paid</td><td style="text-align:right;"><?= money($b['paid_amount']) ?></td></tr>
            <?php if ($outstanding > 0): ?>
            <tr><td style="font-weight:600;">Balance Due</td><td style="text-align:right;font-weight:700;"><?= money($outstanding) ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if (!empty($payments)): ?>
    <div style="margin-top:20px;">
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:1.5px;font-weight:600;margin-bottom:8px;">Payment History</div>
        <table>
            <thead><tr><th>Date</th><th>Method</th><th>Reference</th><th style="text-align:right;">Amount</th></tr></thead>
            <tbody>
            <?php foreach ($payments as $p): ?>
                <tr>
                    <td><?= fmt_datetime($p['payment_date']) ?></td>
                    <td><?= e($p['method']) ?></td>
                    <td><?= e($p['reference_no']) ?></td>
                    <td style="text-align:right;"><?= money($p['amount']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if ($outstanding > 0): ?>
    <div class="btn-row no-print">
        <a href="payment.php?bill_id=<?= $id ?>" class="btn">Record Payment</a>
        <a href="billing.php" class="btn btn-ghost">Back to Bills</a>
    </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
