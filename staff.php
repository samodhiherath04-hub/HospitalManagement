<?php
require_once __DIR__ . '/includes/header.php';
require_role('Admin');

$tab = $_GET['tab'] ?? 'list';
?>
<div class="page-head">
    <h1>Staff Management</h1>
    <a href="staff_edit.php" class="btn"><?= icon_svg('plus') ?> New Employee</a>
</div>
<div class="toolbar" style="gap:0;">
    <a href="staff.php?tab=list" class="btn btn-sm <?= $tab==='list'?'':'btn-ghost' ?>">Employees</a>
    <a href="staff.php?tab=attendance" class="btn btn-sm <?= $tab==='attendance'?'':'btn-ghost' ?>">Attendance</a>
    <a href="staff.php?tab=leaves" class="btn btn-sm <?= $tab==='leaves'?'':'btn-ghost' ?>">Leave Requests</a>
</div>

<?php if ($tab === 'list'): ?>
    <?php $rows = db()->query("SELECT e.*, r.name role_name, dep.name dep_name
                               FROM employees e
                               LEFT JOIN roles r ON e.role_id = r.id
                               LEFT JOIN departments dep ON e.department_id = dep.id
                               ORDER BY e.id DESC")->fetchAll(); ?>
    <div class="card" style="margin-top:12px;">
        <div class="table-wrap">
            <table id="searchTable">
                <thead><tr><th>Code</th><th>Name</th><th>Role</th><th>Department</th><th>Phone</th><th>Salary</th><th>Joined</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($rows as $e): ?>
                    <tr>
                        <td><?= e($e['emp_code']) ?></td>
                        <td><?= e($e['first_name'].' '.$e['last_name']) ?></td>
                        <td><?= e($e['role_name'] ?: '-') ?></td>
                        <td><?= e($e['dep_name'] ?: '-') ?></td>
                        <td><?= e($e['phone']) ?></td>
                        <td><?= money($e['salary']) ?></td>
                        <td><?= fmt_date($e['join_date']) ?></td>
                        <td><span class="badge <?= $e['status']==='active'?'badge-solid':'badge-soft' ?>"><?= e($e['status']) ?></span></td>
                        <td>
                            <a href="staff_edit.php?id=<?= (int)$e['id'] ?>" class="btn btn-ghost btn-sm"><?= icon_svg('edit') ?></a>
                            <a href="staff_delete.php?id=<?= (int)$e['id'] ?>" class="btn btn-ghost btn-sm" data-confirm="Delete employee?"><?= icon_svg('trash') ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($rows)): ?><tr><td colspan="9" class="empty">No employees.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($tab === 'attendance'): ?>
    <?php $rows = db()->query("SELECT a.*, e.emp_code, e.first_name, e.last_name
                               FROM attendance a JOIN employees e ON a.employee_id = e.id
                               ORDER BY a.date DESC, e.first_name LIMIT 100")->fetchAll(); ?>
    <div class="card" style="margin-top:12px;">
        <div class="card-head"><h2>Attendance Records</h2></div>
        <div class="table-wrap">
            <table id="searchTable">
                <thead><tr><th>Code</th><th>Employee</th><th>Date</th><th>Check In</th><th>Check Out</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($rows as $a): ?>
                    <tr>
                        <td><?= e($a['emp_code']) ?></td>
                        <td><?= e($a['first_name'].' '.$a['last_name']) ?></td>
                        <td><?= fmt_date($a['date']) ?></td>
                        <td><?= $a['check_in'] ? date('H:i', strtotime($a['check_in'])) : '-' ?></td>
                        <td><?= $a['check_out'] ? date('H:i', strtotime($a['check_out'])) : '-' ?></td>
                        <td><span class="badge badge-soft"><?= e($a['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($rows)): ?><tr><td colspan="6" class="empty">No attendance records.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($tab === 'leaves'): ?>
    <?php $rows = db()->query("SELECT l.*, e.emp_code, e.first_name, e.last_name
                               FROM leaves l JOIN employees e ON l.employee_id = e.id
                               ORDER BY l.applied_on DESC LIMIT 100")->fetchAll(); ?>
    <div class="card" style="margin-top:12px;">
        <div class="card-head"><h2>Leave Requests</h2></div>
        <div class="table-wrap">
            <table id="searchTable">
                <thead><tr><th>Code</th><th>Employee</th><th>Type</th><th>Start</th><th>End</th><th>Reason</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($rows as $l): ?>
                    <tr>
                        <td><?= e($l['emp_code']) ?></td>
                        <td><?= e($l['first_name'].' '.$l['last_name']) ?></td>
                        <td><?= e($l['leave_type']) ?></td>
                        <td><?= fmt_date($l['start_date']) ?></td>
                        <td><?= fmt_date($l['end_date']) ?></td>
                        <td><?= e(mb_strimwidth($l['reason'],0,40,'...')) ?></td>
                        <td><span class="badge <?= $l['status']==='approved'?'badge-solid':($l['status']==='pending'?'badge-outline':'badge-soft') ?>"><?= e($l['status']) ?></span></td>
                        <td>
                            <?php if ($l['status']==='pending'): ?>
                                <a href="leave_action.php?id=<?= (int)$l['id'] ?>&action=approve" class="btn btn-ghost btn-sm" data-confirm="Approve?">Approve</a>
                                <a href="leave_action.php?id=<?= (int)$l['id'] ?>&action=reject" class="btn btn-ghost btn-sm" data-confirm="Reject?">Reject</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($rows)): ?><tr><td colspan="8" class="empty">No leave requests.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
