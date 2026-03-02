<?php
declare(strict_types=1);

define('APP_NAME', 'Ops Dashboard Starter');
define('APP_BASE_PATH', '/demos/ops-dashboard');

// DB (use env vars if set; otherwise default)
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'ops_dashboard');
define('DB_USER', getenv('DB_USER') ?: 'ops_user');
define('DB_PASS', getenv('DB_PASS') ?: 'q*Y39p21w');

date_default_timezone_set('America/Los_Angeles');