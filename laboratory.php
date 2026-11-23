<?php
require_once __DIR__ . '/includes/header.php';
require_role('Admin','Doctor','Lab Staff','Nurse');

$tab = $_GET['tab'] ?? 'requests';
?>
<div class="page-head">
    <h1>Laboratory</h1>
    <a href="lab_edit.php" class="btn"><?= icon_svg('plus') ?> New Test Request</a>
</div>

<div class="toolbar" style="gap:0;">
    <a href="laboratory.php?tab=requests" class="btn btn-sm <?= $tab==='requests'?'':'btn-ghost' ?>">Test Requests</a>
    <a href="laboratory.php?tab=catalog" class="btn btn-sm <?= $tab==='catalog'?'':'btn-ghost' ?>">Test Catalog</a>
</div>

<?php if ($tab === 'requests'): ?>
    <?php
    $rows = db()->query("SELECT lr.*, p.first_name pfn, p.last_name pln, p.patient_code,
                                d.first_name dfn, d.last_name dln, lt.test_name, lt.test_code, lt.cost
                         FROM lab_requests lr
                         JOIN patients  p  ON lr.patient_id = p.id
                         JOIN doctors   d  ON lr.doctor_id  = d.id
                         JOIN lab_tests lt ON lr.test_id    = lt.id
                         ORDER BY lr.request_date DESC, lr.id DESC LIMIT 100")->fetchAll();
    ?>
    <div class="card" style="margin-top:12px;">
        <div class="card-head"><h2>Test Requests</h2></div>
        <div class="table-wrap">
            <table id="searchTable">
                <thead><tr><th>Req #</th><th>Patient</th><th>Doctor</th><th>Test</th><th>Requested</th><th>Status</th><th>Result</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td>#<?= (int)$r['id'] ?></td>
                        <td><?= e($r['pfn'].' '.$r['pln']) ?><br><small style="color:var(--gray-5)"><?= e($r['patient_code']) ?></small></td>
                        <td>Dr. <?= e($r['dfn'].' '.$r['dln']) ?></td>
                        <td><?= e($r['test_name']) ?><br><small style="color:var(--gray-5)"><?= e($r['test_code']) ?> &middot; <?= money($r['cost']) ?></small></td>
                        <td><?= fmt_date($r['request_date']) ?></td>
                        <td>
                            <?php
                              $cls = ['pending'=>'badge-soft','sample-collected'=>'badge-outline','in-progress'=>'badge-outline','completed'=>'badge-solid','cancelled'=>'badge-danger'];
                            ?>
                            <span class="badge <?= $cls[$r['status']] ?? 'badge-soft' ?>"><?= e($r['status']) ?></span>
                        </td>
                        <td><?= e($r['result'] ? mb_strimwidth($r['result'],0,40,'...') : '-') ?></td>
                        <td>
                            <?php if (in_array(current_role(), ['Admin','Lab Staff'], true) && $r['status'] !== 'completed'): ?>
                            <a href="lab_result.php?id=<?= (int)$r['id'] ?>" class="btn btn-ghost btn-sm">Enter Result</a>
                            <?php endif; ?>
                            <?php if ($r['status'] === 'completed'): ?>
                            <a href="lab_result.php?id=<?= (int)$r['id'] ?>" class="btn btn-ghost btn-sm">View</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($rows)): ?><tr><td colspan="8" class="empty">No lab requests.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <?php
    $tests = db()->query("SELECT * FROM lab_tests ORDER BY id")->fetchAll();
    ?>
    <div class="card" style="margin-top:12px;">
        <div class="card-head">
            <h2>Test Catalog</h2>
            <a href="lab_test_edit.php" class="btn btn-ghost btn-sm"><?= icon_svg('plus') ?> Add Test</a>
        </div>
        <div class="table-wrap">
            <table id="searchTable">
                <thead><tr><th>Code</th><th>Test Name</th><th>Category</th><th>Sample</th><th>Cost</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($tests as $t): ?>
                    <tr>
                        <td><?= e($t['test_code']) ?></td>
                        <td><?= e($t['test_name']) ?></td>
                        <td><?= e($t['category']) ?></td>
                        <td><?= e($t['sample_type']) ?></td>
                        <td><?= money($t['cost']) ?></td>
                        <td>
                            <a href="lab_test_edit.php?id=<?= (int)$t['id'] ?>" class="btn btn-ghost btn-sm"><?= icon_svg('edit') ?></a>
                            <a href="lab_test_delete.php?id=<?= (int)$t['id'] ?>" class="btn btn-ghost btn-sm" data-confirm="Delete test?"><?= icon_svg('trash') ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($tests)): ?><tr><td colspan="6" class="empty">No tests in catalog.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
