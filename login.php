<?php
require_once __DIR__ . '/includes/auth.php';
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } elseif (attempt_login($username, $password)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login &middot; HMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { display: grid; place-items: center; min-height: 100vh; background: var(--gray-0); }
        .login-card { width: 360px; background: var(--white); border: 1px solid var(--border); padding: 40px; border-radius: 4px; }
        .login-brand { display: flex; align-items: center; gap: 10px; margin-bottom: 28px; justify-content: center; }
        .login-brand .mark { width: 32px; height: 32px; background: var(--black); color: var(--white); display: grid; place-items: center; font-weight: 700; border-radius: 2px; }
        .login-brand .text { font-weight: 700; letter-spacing: 4px; font-size: 18px; }
        .login-subtitle { text-align: center; font-size: 11px; text-transform: uppercase; letter-spacing: 2px; color: var(--gray-5); margin-bottom: 24px; }
        .login-form .form-group { margin-bottom: 18px; }
        .login-form label { display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; color: var(--gray-7); margin-bottom: 6px; }
        .login-form input { width: 100%; padding: 11px 12px; border: 1px solid var(--border); border-radius: 4px; }
        .login-form input:focus { outline: none; border-color: var(--black); }
        .login-error { border: 1px solid var(--black); border-left-width: 3px; background: var(--white); padding: 10px 14px; font-size: 13px; margin-bottom: 18px; border-radius: 2px; }
        .login-btn { width: 100%; padding: 12px; background: var(--black); color: var(--white); border: none; text-transform: uppercase; letter-spacing: 2px; font-size: 12px; font-weight: 600; border-radius: 4px; cursor: pointer; }
        .login-btn:hover { opacity: .85; }
        .demo-box { margin-top: 24px; padding: 14px; background: var(--gray-0); border: 1px solid var(--border); border-radius: 4px; font-size: 11px; color: var(--gray-7); }
        .demo-box strong { display: block; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; font-size: 10px; }
        .demo-box code { background: var(--white); padding: 1px 6px; border: 1px solid var(--border); border-radius: 2px; display: inline-block; margin: 2px; font-size: 11px; }
        .demo-list { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 4px; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-brand">
            <span class="mark">H</span>
            <span class="text">HMS</span>
        </div>
        <p class="login-subtitle">Hospital Management System</p>

        <?php if ($error): ?>
            <div class="login-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" class="login-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" autofocus required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="login-btn">Sign In</button>
        </form>

        <div class="demo-box">
            <strong>Demo Accounts</strong>
            Password for all: <code>admin123</code>
            <div class="demo-list">
                <code>admin</code><code>dr.smith</code><code>nurse.jane</code><code>reception</code><code>lab.tech</code><code>pharmacy</code><code>accounts</code>
            </div>
        </div>
    </div>
</body>
</html>
