<?php
require_once __DIR__ . '/includes/header.php';
require_role('Admin','Accountant','Receptionist');

$status = $_GET['status'] ?? '';
$q = trim($_GET['q'] ?? '');
$sql = "SELECT b.*, p.first_name pfn, p.last_name pln, p.patient_code
        FROM bills b JOIN patients p ON b.patient_id = p.id";
$where = []; $params = [];
if ($status) { $where[] = "b.status = ?"; $params[] = $status; }
if ($q) {
    $where[] = "(b.bill_no LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ? OR p.patient_code LIKE ?)";
    $like = "%$q%"; $params = array_merge($params, [$like,$like,$like,$like]);
}
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY b.bill_date DESC, b.id DESC LIMIT 100";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$bills = $stmt->fetchAll();

$totals = db()->query("SELECT COALESCE(SUM(total_amount),0) AS t, COALESCE(SUM(paid_amount),0) AS p, COALESCE(SUM(total_amount-paid_amount),0) AS o FROM bills")->fetch();
?>
<div class="page-head">
    <h1>Billing</h1>
    <a href="bill_edit.php" class="btn"><?= icon_svg('plus') ?> Generate Bill</a>
</div>

<div class="grid grid-3" style="margin-bottom:16px;">
    <div class="kpi"><div class="kpi-label">Total Billed</div><div class="kpi-value"><?= money($totals['t']) ?></div></div>
    <div class="kpi"><div class="kpi-label">Total Collected</div><div class="kpi-value"><?= money($totals['p']) ?></div></div>
    <div class="kpi"><div class="kpi-label">Outstanding</div><div class="kpi-value"><?= money($totals['o']) ?></div></div>
</div>

<div class="card">
    <div class="toolbar">
        <form method="get" class="search-box">
            <?= icon_svg('search') ?>
            <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search bill no or patient...">
        </form>
        <form method="get">
            <select name="status" onchange="this.form.submit()" style="padding:9px 12px;border:1px solid var(--border);border-radius:4px;">
                <option value="">All</option>
                <?php foreach (['unpaid','partial','paid'] as $s): ?>
                    <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <span style="font-size:12px;color:var(--gray-5);margin-left:auto;"><?= count($bills) ?> bills</span>
    </div>
    <div class="table-wrap">
        <table id="searchTable">
            <thead><tr><th>Bill No</th><th>Date</th><th>Patient</th><th>Consultation</th><th>Lab</th><th>Pharmacy</th><th>Other</th><th>Total</th><th>Paid</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($bills as $b): ?>
                <tr>
                    <td><?= e($b['bill_no']) ?></td>
                    <td><?= fmt_date($b['bill_date']) ?></td>
                    <td><?= e($b['pfn'].' '.$b['pln']) ?><br><small style="color:var(--gray-5)"><?= e($b['patient_code']) ?></small></td>
                    <td><?= money($b['consultation_charge']) ?></td>
                    <td><?= money($b['lab_charge']) ?></td>
                    <td><?= money($b['pharmacy_charge']) ?></td>
                    <td><?= money($b['admission_charge'] + $b['other_charge']) ?></td>
                    <td><strong><?= money($b['total_amount']) ?></strong></td>
                    <td><?= money($b['paid_amount']) ?></td>
                    <td>
                        <?php $cls=['paid'=>'badge-solid','partial'=>'badge-outline','unpaid'=>'badge-danger']; ?>
                        <span class="badge <?= $cls[$b['status']] ?>"><?= e($b['status']) ?></span>
                    </td>
                    <td>
                        <a href="bill_edit.php?id=<?= (int)$b['id'] ?>" class="btn btn-ghost btn-sm"><?= icon_svg('edit') ?></a>
                        <a href="bill_view.php?id=<?= (int)$b['id'] ?>" class="btn btn-ghost btn-sm" title="View/Print"><?= icon_svg('print') ?></a>
                        <?php if ($b['status'] !== 'paid'): ?>
                        <a href="payment.php?bill_id=<?= (int)$b['id'] ?>" class="btn btn-ghost btn-sm">Payment</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($bills)): ?><tr><td colspan="11" class="empty">No bills.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
