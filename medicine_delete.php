<?php
require_once __DIR__ . '/includes/auth.php';
require_role('Admin','Pharmacist');
$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    try { db()->prepare("DELETE FROM medicines WHERE id=?")->execute([$id]); set_flash('success','Medicine deleted.'); }
    catch (Exception $e) { set_flash('error','Cannot delete medicine in use.'); }
}
header('Location: pharmacy.php'); exit;
