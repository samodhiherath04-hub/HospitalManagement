<?php
require_once __DIR__ . '/includes/header.php';
require_role('Admin');

$rows = db()->query("SELECT u.*, r.name role_name FROM users u JOIN roles r ON u.role_id = r.id ORDER BY u.id")->fetchAll();
?>
<div class="page-head">
    <h1>Users</h1>
    <a href="user_edit.php" class="btn"><?= icon_svg('plus') ?> New User</a>
</div>
<div class="card">
    <div class="table-wrap">
        <table id="searchTable">
            <thead><tr><th>Username</th><th>Name</th><th>Role</th><th>Email</th><th>Phone</th><th>Last Login</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $u): ?>
                <tr>
                    <td><strong><?= e($u['username']) ?></strong></td>
                    <td><?= e($u['full_name']) ?></td>
                    <td><span class="badge badge-solid"><?= e($u['role_name']) ?></span></td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= e($u['phone']) ?></td>
                    <td><?= fmt_datetime($u['last_login']) ?></td>
                    <td><span class="badge <?= $u['status']==='active'?'badge-solid':'badge-soft' ?>"><?= e($u['status']) ?></span></td>
                    <td>
                        <a href="user_edit.php?id=<?= (int)$u['id'] ?>" class="btn btn-ghost btn-sm"><?= icon_svg('edit') ?></a>
                        <a href="user_delete.php?id=<?= (int)$u['id'] ?>" class="btn btn-ghost btn-sm" data-confirm="Delete user?"><?= icon_svg('trash') ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
