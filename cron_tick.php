<?php
/*
 * Background ticker: keeps estimation, alert detection and email running even
 * when nobody has the dashboard open in a browser.
 *
 *   One tick:        C:\xampp\php\php.exe C:\xampp\htdocs\iot_temp_monitor\cron_tick.php
 *   Run forever:     C:\xampp\php\php.exe C:\xampp\htdocs\iot_temp_monitor\cron_tick.php --loop
 *
 * Command line only.
 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
define('ZT_API', true);
define('ZT_NO_SESSION', true);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/engine.php';

$loop = in_array('--loop', $argv, true);
do {
    try {
        engine_run(0);
        $active = (int)db()->query("SELECT COUNT(*) FROM alerts WHERE status = 'active'")->fetchColumn();
        echo date('H:i:s'), "  tick ok, active emergencies: $active\n";
    } catch (Throwable $e) {
        fwrite(STDERR, date('H:i:s') . '  tick failed: ' . $e->getMessage() . "\n");
    }
    if ($loop) {
        sleep(max(1, (int)setting('poll_interval', '2')));
    }
} while ($loop);
