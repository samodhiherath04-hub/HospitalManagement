<?php
require_once __DIR__ . '/includes/header.php';
$q = trim($_GET['q'] ?? '');
$sql = "SELECT d.*, dep.name AS dep_name
        FROM doctors d LEFT JOIN departments dep ON d.department_id = dep.id";
$params = [];
if ($q !== '') {
    $sql .= " WHERE d.doctor_code LIKE ? OR d.first_name LIKE ? OR d.last_name LIKE ? OR d.specialization LIKE ?";
    $like = "%$q%";
    $params = [$like,$like,$like,$like];
}
$sql .= " ORDER BY d.id DESC";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$doctors = $stmt->fetchAll();
?>
<div class="page-head">
    <h1>Doctors</h1>
    <a href="doctor_edit.php" class="btn"><?= icon_svg('plus') ?> New Doctor</a>
</div>
<div class="card">
    <div class="toolbar">
        <form method="get" class="search-box">
            <?= icon_svg('search') ?>
            <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search by code, name, specialization...">
        </form>
        <span style="font-size:12px;color:var(--gray-5);"><?= count($doctors) ?> records</span>
    </div>
    <div class="table-wrap">
        <table id="searchTable">
            <thead>
                <tr><th>Code</th><th>Name</th><th>Specialization</th><th>Department</th>
                <th>Phone</th><th>Fee</th><th>Exp.</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($doctors as $d): ?>
                <tr>
                    <td><?= e($d['doctor_code']) ?></td>
                    <td>Dr. <?= e($d['first_name'].' '.$d['last_name']) ?></td>
                    <td><?= e($d['specialization']) ?></td>
                    <td><?= e($d['dep_name'] ?: '-') ?></td>
                    <td><?= e($d['phone']) ?></td>
                    <td><?= money($d['consultation_fee']) ?></td>
                    <td><?= (int)$d['experience_years'] ?> yrs</td>
                    <td><span class="badge <?= $d['status']==='active'?'badge-solid':'badge-soft' ?>"><?= e($d['status']) ?></span></td>
                    <td>
                        <a href="doctor_edit.php?id=<?= (int)$d['id'] ?>" class="btn btn-ghost btn-sm"><?= icon_svg('edit') ?></a>
                        <a href="doctor_delete.php?id=<?= (int)$d['id'] ?>" class="btn btn-ghost btn-sm" data-confirm="Delete doctor?"><?= icon_svg('trash') ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($doctors)): ?><tr><td colspan="9" class="empty">No doctors found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
