<?php
require_once __DIR__ . '/includes/auth.php';
require_role('Admin');
$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    // Soft delete: set status to inactive, or hard delete if no FK rows depend on it
    try {
        $stmt = db()->prepare("DELETE FROM patients WHERE id = ?");
        $stmt->execute([$id]);
        log_action('Deleted patient', 'patients', $id);
        set_flash('success', 'Patient deleted.');
    } catch (Exception $e) {
        set_flash('error', 'Cannot delete patient with related records. Set status to inactive instead.');
    }
}
header('Location: patients.php'); exit;
