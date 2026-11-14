<?php
/*
 * Configuration. XAMPP defaults: MySQL user "root" with an empty password.
 * Change these if your MySQL setup is different.
 */
define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3306);
define('DB_NAME', 'iot_temp_monitor');
define('DB_USER', 'root');
define('DB_PASS', '');

define('APP_NAME', 'Cold Storage Monitor');
// Used for the dashboard link inside alert emails.
define('APP_URL', 'http://localhost/iot_temp_monitor');
define('APP_TIMEZONE', 'Asia/Kolkata');

// Sign users out after this many minutes without any activity.
// (An open dashboard polls the server, so it stays signed in.)
define('SESSION_IDLE_MINUTES', 120);

// Failed sign-ins allowed per username within 15 minutes before a lockout.
define('LOGIN_MAX_FAILURES', 5);

// Show technical error details. Set to false outside a lab setup.
define('APP_DEBUG', true);
