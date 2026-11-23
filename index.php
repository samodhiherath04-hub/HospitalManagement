<?php
/** Entry point: redirect to login or dashboard */
require_once __DIR__ . '/includes/auth.php';
if (is_logged_in()) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;
