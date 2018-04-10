<?php

/**
 * Laravel start time
 */
define('LARAVEL_START', microtime(true));

/**
 * Whether the script has been executed by Codeception
 */
define('CODECEPT_TEST_RUNNER', true);

require __DIR__ . '/../../vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require_once __DIR__ . '/../../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();