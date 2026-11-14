<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login(true);
$forced = $u['must_change_password'] === 1;
$error = null;
$done = false;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $current = (string)($_POST['current'] ?? '');
    $new = (string)($_POST['new'] ?? '');
    $confirm = (string)($_POST['confirm'] ?? '');
    $st = db()->prepare('SELECT password_hash FROM users WHERE id = ?');
    $st->execute([$u['id']]);
    $hash = (string)$st->fetchColumn();

    if (!csrf_valid($_POST['csrf'] ?? null)) {
        $error = 'The form expired. Try again.';
    } elseif (!password_verify($current, $hash)) {
        $error = 'Your current password is not correct.';
    } elseif ($new !== $confirm) {
        $error = 'The new passwords do not match.';
    } elseif ($p = password_problem($new)) {
        $error = $p;
    } elseif (password_verify($new, $hash)) {
        $error = 'Choose a password that is different from your current one.';
    } else {
        db()->prepare('UPDATE users SET password_hash = ?, must_change_password = 0 WHERE id = ?')
            ->execute([password_hash($new, PASSWORD_DEFAULT), $u['id']]);
        session_regenerate_id(true);
        if ($forced) {
            header('Location: index.php');
            exit;
        }
        $done = true;
    }
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Change password | <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<div style="max-width:440px;margin:8vh auto;padding:0 16px">
  <form method="post" class="panel" style="padding:28px">
    <h2 style="font-size:1.35rem">Choose a new password</h2>
    <p class="muted" style="margin-top:6px">
      <?= $forced ? 'For security, replace the password you signed in with before using the system.' : 'Signed in as ' . e($u['username']) . '.' ?>
    </p>
    <?php if ($error): ?><div class="notice error" role="alert" style="margin-bottom:12px"><?= e($error) ?></div><?php endif; ?>
    <?php if ($done): ?><div class="notice ok" style="margin-bottom:12px">Password changed.</div><?php endif; ?>
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <div class="field"><label for="current">Current password</label>
      <input id="current" type="password" name="current" autocomplete="current-password" required></div>
    <div class="field" style="margin-top:12px"><label for="new">New password</label>
      <input id="new" type="password" name="new" autocomplete="new-password" required minlength="8">
      <span class="hint">At least 8 characters with a letter and a number.</span></div>
    <div class="field" style="margin-top:12px"><label for="confirm">Repeat new password</label>
      <input id="confirm" type="password" name="confirm" autocomplete="new-password" required></div>
    <div class="form-actions">
      <?php if (!$forced): ?><a class="btn" href="settings.php">Back</a><?php endif; ?>
      <button class="btn btn-primary" type="submit">Change password</button>
    </div>
  </form>
  <?php if ($forced): ?>
  <form method="post" action="logout.php" style="text-align:center;margin-top:12px">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <button class="btn-link" style="color:var(--steel)">Sign out instead</button>
  </form>
  <?php endif; ?>
</div>
</body>
</html>
