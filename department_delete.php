<?php
require_once __DIR__ . '/includes/auth.php';
require_role('Admin');
$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    try { db()->prepare("DELETE FROM departments WHERE id=?")->execute([$id]); set_flash('success','Department deleted.'); }
    catch (Exception $e) { set_flash('error','Cannot delete department with related records.'); }
}
header('Location: departments.php'); exit;
