<?php
require_once __DIR__ . '/includes/header.php';
require_role('Admin','Doctor','Pharmacist','Nurse');

$tab = $_GET['tab'] ?? 'inventory';
$q = trim($_GET['q'] ?? '');
?>
<div class="page-head">
    <h1>Pharmacy</h1>
    <a href="medicine_edit.php" class="btn"><?= icon_svg('plus') ?> Add Medicine</a>
</div>
<div class="toolbar" style="gap:0;">
    <a href="pharmacy.php?tab=inventory" class="btn btn-sm <?= $tab==='inventory'?'':'btn-ghost' ?>">Inventory</a>
    <a href="pharmacy.php?tab=lowstock" class="btn btn-sm <?= $tab==='lowstock'?'':'btn-ghost' ?>">Low Stock (<?= low_stock_count() ?>)</a>
    <a href="pharmacy.php?tab=expiry" class="btn btn-sm <?= $tab==='expiry'?'':'btn-ghost' ?>">Expiry Watch</a>
</div>

<?php
$where = '';
$params = [];
if ($q !== '') {
    $where = " WHERE name LIKE ? OR medicine_code LIKE ? OR category LIKE ? OR manufacturer LIKE ?";
    $like = "%$q%"; $params = [$like,$like,$like,$like];
}
if ($tab === 'lowstock') {
    $where = ($where ? $where . " AND " : " WHERE ") . "stock_qty <= reorder_level";
}
if ($tab === 'expiry') {
    $where = ($where ? $where . " AND " : " WHERE ") . "expiry_date <= DATE_ADD(CURDATE(), INTERVAL 6 MONTH)";
}
$sql = "SELECT * FROM medicines $where ORDER BY name";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$meds = $stmt->fetchAll();
?>
<div class="card" style="margin-top:12px;">
    <div class="toolbar">
        <form method="get" class="search-box">
            <?= icon_svg('search') ?>
            <input type="hidden" name="tab" value="<?= e($tab) ?>">
            <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search medicine, code, manufacturer...">
        </form>
        <span style="font-size:12px;color:var(--gray-5);margin-left:auto;"><?= count($meds) ?> records</span>
    </div>
    <div class="table-wrap">
        <table id="searchTable">
            <thead><tr><th>Code</th><th>Name</th><th>Category</th><th>Manufacturer</th><th>Batch</th>
                      <th>Expiry</th><th>Stock</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($meds as $m): ?>
                <?php
                    $low = $m['stock_qty'] <= $m['reorder_level'];
                    $expSoon = strtotime($m['expiry_date']) <= strtotime('+6 months');
                ?>
                <tr>
                    <td><?= e($m['medicine_code']) ?></td>
                    <td><?= e($m['name']) ?></td>
                    <td><?= e($m['category']) ?></td>
                    <td><?= e($m['manufacturer']) ?></td>
                    <td><?= e($m['batch_no']) ?></td>
                    <td><?= fmt_date($m['expiry_date']) ?></td>
                    <td>
                        <?= (int)$m['stock_qty'] ?>
                        <?php if ($low): ?><span class="badge badge-danger" style="margin-left:4px;">LOW</span><?php endif; ?>
                    </td>
                    <td><?= money($m['unit_price']) ?></td>
                    <td>
                        <?php if ($expSoon): ?><span class="badge badge-danger">EXPIRING</span>
                        <?php else: ?><span class="badge badge-soft">OK</span><?php endif; ?>
                    </td>
                    <td>
                        <a href="medicine_edit.php?id=<?= (int)$m['id'] ?>" class="btn btn-ghost btn-sm"><?= icon_svg('edit') ?></a>
                        <a href="medicine_delete.php?id=<?= (int)$m['id'] ?>" class="btn btn-ghost btn-sm" data-confirm="Delete medicine?"><?= icon_svg('trash') ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($meds)): ?><tr><td colspan="10" class="empty">No medicines found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
