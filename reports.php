<?php
require_once __DIR__ . '/includes/header.php';
require_role('Admin','Accountant');

// KPIs
$patTotal   = total_patients();
$patAdmit   = (int)db()->query("SELECT COUNT(*) c FROM patients WHERE status='admitted'")->fetch()['c'];
$apptTotal  = (int)db()->query("SELECT COUNT(*) c FROM appointments")->fetch()['c'];
$apptDone   = (int)db()->query("SELECT COUNT(*) c FROM appointments WHERE status='completed'")->fetch()['c'];
$billTotal  = (float)db()->query("SELECT COALESCE(SUM(total_amount),0) s FROM bills")->fetch()['s'];
$billPaid   = (float)db()->query("SELECT COALESCE(SUM(paid_amount),0) s FROM bills")->fetch()['s'];
$billOut    = $billTotal - $billPaid;
$docCount   = (int)db()->query("SELECT COUNT(*) c FROM doctors WHERE status='active'")->fetch()['c'];
$medLow     = low_stock_count();
$labPend    = pending_lab_count();

// Appointments by status
$apptByStatus = db()->query("SELECT status, COUNT(*) c FROM appointments GROUP BY status")->fetchAll();

// Top doctors by appointments
$topDoctors = db()->query("SELECT d.first_name, d.last_name, d.specialization, COUNT(a.id) c
                           FROM doctors d LEFT JOIN appointments a ON d.id = a.doctor_id
                           GROUP BY d.id ORDER BY c DESC LIMIT 5")->fetchAll();

// Revenue by department (via doctor)
$revByDept = db()->query("SELECT dep.name, COALESCE(SUM(b.total_amount),0) AS total
                          FROM bills b
                          JOIN appointments a ON b.appointment_id = a.id
                          JOIN doctors d ON a.doctor_id = d.id
                          JOIN departments dep ON d.department_id = dep.id
                          GROUP BY dep.id ORDER BY total DESC")->fetchAll();

// Last 7 days revenue
$daily = db()->query("SELECT DATE(payment_date) d, COALESCE(SUM(amount),0) s
                      FROM payments WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                      GROUP BY DATE(payment_date) ORDER BY d")->fetchAll();

// Low stock medicines
$lowStockMeds = db()->query("SELECT medicine_code, name, stock_qty, reorder_level FROM medicines WHERE stock_qty <= reorder_level ORDER BY stock_qty ASC")->fetchAll();

// Recent audit
?>
<div class="page-head"><h1>Reports &amp; Analytics</h1></div>

<div class="grid grid-4" style="margin-bottom:16px;">
    <div class="kpi"><div class="kpi-label">Patients</div><div class="kpi-value"><?= $patTotal ?></div><div class="kpi-foot"><?= $patAdmit ?> admitted</div></div>
    <div class="kpi"><div class="kpi-label">Appointments</div><div class="kpi-value"><?= $apptTotal ?></div><div class="kpi-foot"><?= $apptDone ?> completed</div></div>
    <div class="kpi"><div class="kpi-label">Revenue</div><div class="kpi-value"><?= money($billTotal) ?></div><div class="kpi-foot"><?= money($billOut) ?> outstanding</div></div>
    <div class="kpi"><div class="kpi-label">Doctors</div><div class="kpi-value"><?= $docCount ?></div><div class="kpi-foot"><?= $medLow ?> meds low / <?= $labPend ?> lab pending</div></div>
</div>

<div class="grid grid-2">
    <div class="card">
        <div class="card-head"><h2>Appointments by Status</h2></div>
        <div class="table-wrap" style="border:none;">
            <table>
                <thead><tr><th>Status</th><th style="text-align:right;">Count</th><th style="text-align:right;">Share</th></tr></thead>
                <tbody>
                <?php foreach ($apptByStatus as $r): ?>
                    <tr>
                        <td><span class="badge badge-soft"><?= e($r['status']) ?></span></td>
                        <td style="text-align:right;"><?= (int)$r['c'] ?></td>
                        <td style="text-align:right;"><?= $apptTotal > 0 ? round($r['c']/$apptTotal*100, 1) : 0 ?>%</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><h2>Top Doctors (by appointments)</h2></div>
        <div class="table-wrap" style="border:none;">
            <table>
                <thead><tr><th>Doctor</th><th>Specialization</th><th style="text-align:right;">Appointments</th></tr></thead>
                <tbody>
                <?php foreach ($topDoctors as $d): ?>
                    <tr>
                        <td>Dr. <?= e($d['first_name'].' '.$d['last_name']) ?></td>
                        <td><?= e($d['specialization']) ?></td>
                        <td style="text-align:right;"><?= (int)$d['c'] ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($topDoctors)): ?><tr><td colspan="3" class="empty">No data.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="grid grid-2" style="margin-top:16px;">
    <div class="card">
        <div class="card-head"><h2>Revenue Last 7 Days</h2></div>
        <div class="table-wrap" style="border:none;">
            <table>
                <thead><tr><th>Date</th><th style="text-align:right;">Collected</th></tr></thead>
                <tbody>
                <?php foreach ($daily as $d): ?>
                    <tr><td><?= fmt_date($d['d']) ?></td><td style="text-align:right;"><?= money($d['s']) ?></td></tr>
                <?php endforeach; ?>
                <?php if (empty($daily)): ?><tr><td colspan="2" class="empty">No payments in last 7 days.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><h2>Low Stock Medicines</h2></div>
        <div class="table-wrap" style="border:none;">
            <table>
                <thead><tr><th>Code</th><th>Name</th><th style="text-align:right;">Stock</th><th style="text-align:right;">Reorder At</th></tr></thead>
                <tbody>
                <?php foreach ($lowStockMeds as $m): ?>
                    <tr>
                        <td><?= e($m['medicine_code']) ?></td>
                        <td><?= e($m['name']) ?></td>
                        <td style="text-align:right;"><span class="badge badge-danger"><?= (int)$m['stock_qty'] ?></span></td>
                        <td style="text-align:right;"><?= (int)$m['reorder_level'] ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($lowStockMeds)): ?><tr><td colspan="4" class="empty">All medicines above reorder level.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card" style="margin-top:16px;">
    <div class="card-head"><h2>Revenue by Department</h2></div>
    <div class="table-wrap" style="border:none;">
        <table>
            <thead><tr><th>Department</th><th style="text-align:right;">Total Billed</th></tr></thead>
            <tbody>
            <?php foreach ($revByDept as $r): ?>
                <tr><td><?= e($r['name']) ?></td><td style="text-align:right;"><?= money($r['total']) ?></td></tr>
            <?php endforeach; ?>
            <?php if (empty($revByDept)): ?><tr><td colspan="2" class="empty">No department-linked revenue yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
