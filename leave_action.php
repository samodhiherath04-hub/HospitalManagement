<?php
require_once __DIR__ . '/includes/auth.php';
require_role('Admin');
$id = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';
if ($id > 0 && in_array($action, ['approve','reject'], true)) {
    $new = $action === 'approve' ? 'approved' : 'rejected';
    db()->prepare("UPDATE leaves SET status = ? WHERE id = ?")->execute([$new, $id]);
    log_action(ucfirst($action).' leave','staff',$id);
    set_flash('success','Leave ' . $new . '.');
}
header('Location: staff.php?tab=leaves'); exit;
