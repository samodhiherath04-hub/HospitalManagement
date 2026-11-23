<?php
require_once __DIR__ . '/includes/auth.php';
require_role('Admin','Doctor');
$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    try {
        db()->prepare("DELETE FROM medical_records WHERE id=?")->execute([$id]);
        log_action('Deleted medical record','emr',$id);
        set_flash('success','Record deleted.');
    } catch (Exception $e) {
        set_flash('error','Cannot delete record with prescriptions.');
    }
}
header('Location: medical_records.php'); exit;
