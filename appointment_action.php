<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$id = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';
if ($id > 0 && in_array($action, ['complete','cancel','no-show'], true)) {
    $map = ['complete'=>'completed','cancel'=>'cancelled','no-show'=>'no-show'];
    db()->prepare("UPDATE appointments SET status = ? WHERE id = ?")->execute([$map[$action], $id]);
    log_action(ucfirst($action).' appointment', 'appointments', $id);
    set_flash('success', 'Appointment marked as ' . $map[$action] . '.');
}
header('Location: appointments.php'); exit;
