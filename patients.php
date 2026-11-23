<?php
require_once __DIR__ . '/includes/header.php';

$q = trim($_GET['q'] ?? '');
$sql = "SELECT * FROM patients";
$params = [];
if ($q !== '') {
    $sql .= " WHERE patient_code LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR phone LIKE ?";
    $like = "%$q%";
    $params = [$like, $like, $like, $like];
}
$sql .= " ORDER BY id DESC LIMIT 100";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$patients = $stmt->fetchAll();
?>

<div class="page-head">
    <h1>Patients</h1>
    <a href="patient_edit.php" class="btn"><?= icon_svg('plus') ?> New Patient</a>
</div>

<div class="card">
    <div class="toolbar">
        <form method="get" class="search-box" style="flex:1; max-width:380px;">
            <?= icon_svg('search') ?>
            <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search by code, name, phone...">
        </form>
        <span style="font-size:12px;color:var(--gray-5);"><?= count($patients) ?> records</span>
    </div>

    <div class="table-wrap">
        <table id="searchTable">
            <thead>
                <tr>
                    <th>Code</th><th>Name</th><th>Gender</th><th>DOB</th>
                    <th>Blood</th><th>Phone</th><th>Registered</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($patients as $p): ?>
                <tr>
                    <td><?= e($p['patient_code']) ?></td>
                    <td><?= e($p['first_name'].' '.$p['last_name']) ?></td>
                    <td><?= e($p['gender']) ?></td>
                    <td><?= fmt_date($p['dob']) ?></td>
                    <td><?= e($p['blood_group'] ?: '-') ?></td>
                    <td><?= e($p['phone']) ?></td>
                    <td><?= fmt_date($p['registration_date']) ?></td>
                    <td>
                        <?php if ($p['status']==='admitted'): ?>
                            <span class="badge badge-solid"><?= e($p['status']) ?></span>
                        <?php else: ?>
                            <span class="badge badge-soft"><?= e($p['status']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="patient_edit.php?id=<?= (int)$p['id'] ?>" class="btn btn-ghost btn-sm" title="Edit"><?= icon_svg('edit') ?></a>
                        <a href="patient_view.php?id=<?= (int)$p['id'] ?>" class="btn btn-ghost btn-sm" title="View"><?= icon_svg('eye') ?></a>
                        <a href="patient_delete.php?id=<?= (int)$p['id'] ?>" class="btn btn-ghost btn-sm" data-confirm="Delete patient <?= e($p['patient_code']) ?>?" title="Delete"><?= icon_svg('trash') ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($patients)): ?>
                <tr><td colspan="9" class="empty">No patients found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
