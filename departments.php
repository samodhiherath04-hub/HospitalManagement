<?php
require_once __DIR__ . '/includes/header.php';
require_role('Admin');
$deps = db()->query("SELECT d.*, (SELECT COUNT(*) FROM doctors WHERE department_id=d.id) AS doc_count,
                            (SELECT COUNT(*) FROM employees WHERE department_id=d.id) AS emp_count
                     FROM departments d ORDER BY d.name")->fetchAll();
?>
<div class="page-head">
    <h1>Departments</h1>
    <a href="department_edit.php" class="btn"><?= icon_svg('plus') ?> Add Department</a>
</div>
<div class="card">
    <div class="table-wrap">
        <table id="searchTable">
            <thead><tr><th>Name</th><th>Description</th><th>Location</th><th>Doctors</th><th>Staff</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($deps as $d): ?>
                <tr>
                    <td><strong><?= e($d['name']) ?></strong></td>
                    <td><?= e($d['description']) ?></td>
                    <td><?= e($d['location']) ?></td>
                    <td><?= (int)$d['doc_count'] ?></td>
                    <td><?= (int)$d['emp_count'] ?></td>
                    <td>
                        <a href="department_edit.php?id=<?= (int)$d['id'] ?>" class="btn btn-ghost btn-sm"><?= icon_svg('edit') ?></a>
                        <a href="department_delete.php?id=<?= (int)$d['id'] ?>" class="btn btn-ghost btn-sm" data-confirm="Delete department?"><?= icon_svg('trash') ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
