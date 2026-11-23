<?php
require_once __DIR__ . '/includes/auth.php';
require_role('Admin');
$id = (int)($_GET['id'] ?? 0);
if ($id > 0 && $id !== current_user_id()) {
    try { db()->prepare("DELETE FROM users WHERE id=?")->execute([$id]); set_flash('success','User deleted.'); }
    catch (Exception $e) { set_flash('error','Cannot delete user with related records.'); }
} else {
    set_flash('error','Cannot delete yourself.');
}
header('Location: users.php'); exit;
