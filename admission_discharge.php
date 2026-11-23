<?php
require_once __DIR__ . '/includes/auth.php';
require_role('Admin','Doctor');
$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = db()->prepare("SELECT * FROM admissions WHERE id = ?");
    $stmt->execute([$id]);
    $a = $stmt->fetch();
    if ($a) {
        db()->prepare("UPDATE admissions SET discharge_date = NOW(), status = 'discharged' WHERE id = ?")->execute([$id]);
        db()->prepare("UPDATE patients SET status = 'active' WHERE id = ?")->execute([$a['patient_id']]);
        log_action('Discharged patient','admissions',$id);
        set_flash('success','Patient discharged.');
    }
}
header('Location: admissions.php'); exit;
