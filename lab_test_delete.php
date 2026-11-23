<?php
require_once __DIR__ . '/includes/auth.php';
require_role('Admin');
$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    try { db()->prepare("DELETE FROM lab_tests WHERE id=?")->execute([$id]); set_flash('success','Test deleted.'); }
    catch (Exception $e) { set_flash('error','Cannot delete test in use.'); }
}
header('Location: laboratory.php?tab=catalog'); exit;
