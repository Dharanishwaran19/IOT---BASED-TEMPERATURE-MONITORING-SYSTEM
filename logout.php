<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

// Sign-out is a POST with the CSRF token so other sites cannot sign users out.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && csrf_valid($_POST['csrf'] ?? null)) {
    logout_session();
    header('Location: login.php?out=1');
    exit;
}
header('Location: index.php');
