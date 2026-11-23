<?php
require_once __DIR__ . '/includes/header.php';

// KPI values
$patients   = total_patients();
$appts      = todays_appointments_count();
$revenue    = total_revenue();
$lowStock   = low_stock_count();
$pendingLab = pending_lab_count();

// Today's appointment list
$apptsToday = db()->query("
    SELECT a.id, a.appointment_time, p.first_name AS pfn, p.last_name AS pln,
           p.patient_code, d.first_name AS dfn, d.last_name AS dln,
           a.type, a.status, a.reason
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    JOIN doctors  d ON a.doctor_id  = d.id
    WHERE a.appointment_date = CURDATE()
    ORDER BY a.appointment_time
    LIMIT 10
")->fetchAll();

// Recent patients
$recentPatients = db()->query("
    SELECT patient_code, first_name, last_name, gender, phone, registration_date
    FROM patients ORDER BY id DESC LIMIT 6
")->fetchAll();

// Revenue last 7 days (for mini sparkline-like text)
$dailyRev = db()->query("
    SELECT DATE(payment_date) AS d, SUM(amount) AS s
    FROM payments
    WHERE payment_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(payment_date) ORDER BY d
")->fetchAll();

// Unpaid bills
$unpaidBills = db()->query("
    SELECT COUNT(*) AS c, COALESCE(SUM(total_amount - paid_amount),0) AS outstanding
    FROM bills WHERE status != 'paid'
")->fetch();
?>

<div class="page-head">
    <h1>Dashboard</h1>
    <div><?= date('l, d M Y') ?></div>
</div>

<!-- KPI grid -->
<div class="grid grid-4" style="margin-bottom: 20px;">
    <div class="kpi">
        <div class="kpi-label">Total Patients</div>
        <div class="kpi-value"><?= $patients ?></div>
        <div class="kpi-foot"><?= (int)db()->query("SELECT COUNT(*) AS c FROM patients WHERE status='admitted'")->fetch()['c'] ?> currently admitted</div>
    </div>
    <div class="kpi">
        <div class="kpi-label">Today's Appointments</div>
        <div class="kpi-value"><?= $appts ?></div>
        <div class="kpi-foot"><?= (int)db()->query("SELECT COUNT(*) AS c FROM appointments WHERE appointment_date=CURDATE() AND status='completed'")->fetch()['c'] ?> completed today</div>
    </div>
    <div class="kpi">
        <div class="kpi-label">Total Revenue</div>
        <div class="kpi-value"><?= money($revenue) ?></div>
        <div class="kpi-foot"><?= money($unpaidBills['outstanding']) ?> outstanding</div>
    </div>
    <div class="kpi">
        <div class="kpi-label">Pending Lab Requests</div>
        <div class="kpi-value"><?= $pendingLab ?></div>
        <div class="kpi-foot"><?= $lowStock ?> medicines low on stock</div>
    </div>
</div>

<!-- Two columns: appointments + recent patients -->
<div class="grid grid-2">
    <div class="card">
        <div class="card-head">
            <h2>Today's Appointments</h2>
            <a href="appointments.php" class="btn btn-ghost btn-sm">View all</a>
        </div>
        <?php if (empty($apptsToday)): ?>
            <div class="empty">No appointments scheduled for today.</div>
        <?php else: ?>
        <div class="table-wrap" style="border: none;">
            <table id="searchTable">
                <thead>
                    <tr><th>Time</th><th>Patient</th><th>Doctor</th><th>Type</th><th>Status</th></tr>
                </thead>
                <tbody>
                <?php foreach ($apptsToday as $a): ?>
                    <tr>
                        <td><?= date('H:i', strtotime($a['appointment_time'])) ?></td>
                        <td><?= e($a['pfn'].' '.$a['pln']) ?><br><small style="color:var(--gray-5)"><?= e($a['patient_code']) ?></small></td>
                        <td>Dr. <?= e($a['dfn'].' '.$a['dln']) ?></td>
                        <td><?= e($a['type']) ?></td>
                        <td><span class="badge <?= $a['status']==='completed'?'badge-solid':'badge-soft' ?>"><?= e($a['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-head">
            <h2>Recent Patients</h2>
            <a href="patients.php" class="btn btn-ghost btn-sm">View all</a>
        </div>
        <div class="table-wrap" style="border:none;">
            <table>
                <thead>
                    <tr><th>Code</th><th>Name</th><th>Gender</th><th>Phone</th><th>Registered</th></tr>
                </thead>
                <tbody>
                <?php foreach ($recentPatients as $p): ?>
                    <tr>
                        <td><?= e($p['patient_code']) ?></td>
                        <td><?= e($p['first_name'].' '.$p['last_name']) ?></td>
                        <td><?= e($p['gender']) ?></td>
                        <td><?= e($p['phone']) ?></td>
                        <td><?= fmt_date($p['registration_date']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Quick actions row -->
<div class="grid grid-4" style="margin-top: 20px;">
    <a href="patient_edit.php" class="card" style="text-align:center;">
        <div style="font-size: 12px; text-transform:uppercase; letter-spacing:1.5px; color:var(--gray-5);">Action</div>
        <div style="font-size: 16px; font-weight:600; margin-top: 6px;">New Patient</div>
    </a>
    <a href="appointment_edit.php" class="card" style="text-align:center;">
        <div style="font-size: 12px; text-transform:uppercase; letter-spacing:1.5px; color:var(--gray-5);">Action</div>
        <div style="font-size: 16px; font-weight:600; margin-top: 6px;">Book Appointment</div>
    </a>
    <a href="bill_edit.php" class="card" style="text-align:center;">
        <div style="font-size: 12px; text-transform:uppercase; letter-spacing:1.5px; color:var(--gray-5);">Action</div>
        <div style="font-size: 16px; font-weight:600; margin-top: 6px;">Generate Bill</div>
    </a>
    <a href="lab_edit.php" class="card" style="text-align:center;">
        <div style="font-size: 12px; text-transform:uppercase; letter-spacing:1.5px; color:var(--gray-5);">Action</div>
        <div style="font-size: 16px; font-weight:600; margin-top: 6px;">Request Lab Test</div>
    </a>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
