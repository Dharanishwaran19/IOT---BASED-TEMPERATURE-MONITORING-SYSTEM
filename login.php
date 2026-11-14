<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

if (current_user() !== null) {
    header('Location: index.php');
    exit;
}

$error = null;
$notice = null;
// Only hint at the default credentials while the seeded admin has never signed in.
$freshInstall = (bool)db()->query("SELECT 1 FROM users WHERE username = 'admin' AND last_login IS NULL AND must_change_password = 1")->fetchColumn();
$username = '';
if (isset($_GET['expired'])) {
    $notice = 'Your session ended. Sign in again to continue.';
} elseif (isset($_GET['out'])) {
    $notice = 'You have signed out.';
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    if (!csrf_valid($_POST['csrf'] ?? null)) {
        $error = 'The form expired. Try signing in again.';
    } else {
        $error = attempt_login($username, (string)($_POST['password'] ?? ''));
        if ($error === null) {
            $next = basename((string)($_GET['next'] ?? 'index.php'));
            $allowed = ['index.php', 'room.php', 'zones.php', 'monitoring.php', 'simulation.php', 'alerts.php', 'history.php', 'settings.php'];
            header('Location: ' . (in_array($next, $allowed, true) ? $next : 'index.php'));
            exit;
        }
    }
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign in | <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<div class="auth-page">
  <section class="auth-side">
    <div class="brand" style="border:0;padding:0">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 14.8V4a2 2 0 1 0-4 0v10.8a4 4 0 1 0 4 0z"/><path d="M12 9v7"/></svg>
      <div><strong>Cold Storage</strong><span>Multi-zone monitor</span></div>
    </div>
    <div>
      <h1>Every storage zone, read from one reference sensor.</h1>
      <p>Watch estimated zone temperatures, simulate door openings and cooling failures, and get an alarm and an email the moment a zone leaves its safe range.</p>
    </div>
    <svg class="auth-plan" viewBox="-0.5 -0.5 13 9" aria-hidden="true">
      <rect x="0" y="0" width="12" height="8" fill="#2f3d46" stroke="#9fc3d6" stroke-width=".08"/>
      <g fill="#3a4b55" stroke="#6f8590" stroke-width=".04" stroke-dasharray=".2 .1">
        <rect x=".5" y=".5" width="3" height="2"/><rect x="4.5" y=".5" width="3" height="2"/>
        <rect x=".5" y="4.5" width="3" height="2.5"/><rect x="4.5" y="4.8" width="3" height="2.2"/>
        <rect x="8.5" y="5.5" width="3" height="2"/>
      </g>
      <rect x="8.5" y=".5" width="3" height="2" fill="#c21f32"/>
      <circle cx="6" cy="4" r=".14" fill="#9fc3d6"/><circle cx="6" cy="4" r=".34" fill="none" stroke="#9fc3d6" stroke-width=".04"/>
    </svg>
  </section>
  <section class="auth-form">
    <form method="post" autocomplete="on" novalidate>
      <h2>Sign in</h2>
      <p class="muted">Use the account your administrator gave you.</p>
      <?php if ($notice): ?><div class="notice ok"><?= e($notice) ?></div><?php endif; ?>
      <?php if ($error): ?><div class="notice error" role="alert"><?= e($error) ?></div><?php endif; ?>
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <div class="field" style="margin-top:14px">
        <label for="username">Username</label>
        <input id="username" name="username" value="<?= e($username) ?>" autocomplete="username" required autofocus>
      </div>
      <div class="field" style="margin-top:12px">
        <label for="password">Password</label>
        <input id="password" type="password" name="password" autocomplete="current-password" required>
      </div>
      <button class="btn btn-primary" type="submit" style="width:100%;margin-top:18px">Sign in</button>
      <?php if ($freshInstall): ?>
      <p class="muted" style="margin-top:18px;font-size:.85rem">First sign-in after installation: username <code>admin</code>, password <code>Admin@123</code>. You will be asked to choose a new password.</p>
      <?php endif; ?>
    </form>
  </section>
</div>
</body>
</html>
