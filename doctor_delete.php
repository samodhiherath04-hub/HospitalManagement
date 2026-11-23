<?php
require_once __DIR__ . '/includes/auth.php';
require_role('Admin');
$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    try {
        db()->prepare("DELETE FROM doctors WHERE id=?")->execute([$id]);
        log_action('Deleted doctor','doctors',$id);
        set_flash('success','Doctor deleted.');
    } catch (Exception $e) {
        set_flash('error','Cannot delete doctor with related records.');
    }
}
header('Location: doctors.php'); exit;
