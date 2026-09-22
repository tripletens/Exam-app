<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Setup writable storage directories in /tmp for serverless execution
$storagePath = '/tmp/storage';
$requiredDirs = [
    $storagePath . '/framework/views',
    $storagePath . '/framework/cache/data',
    $storagePath . '/framework/sessions',
    $storagePath . '/logs',
    '/tmp/bootstrap/cache',
];

foreach ($requiredDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Redirect view compilation and internal caches to writable paths
putenv("VIEW_COMPILED_PATH={$storagePath}/framework/views");
putenv("APP_CONFIG_CACHE=/tmp/bootstrap/cache/config.php");
putenv("APP_EVENTS_CACHE=/tmp/bootstrap/cache/events.php");
putenv("APP_PACKAGES_CACHE=/tmp/bootstrap/cache/packages.php");
putenv("APP_ROUTES_CACHE=/tmp/bootstrap/cache/routes.php");
putenv("APP_SERVICES_CACHE=/tmp/bootstrap/cache/services.php");

// Autoload dependencies
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap the Laravel application
/** @var Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Override storage path so logs, views, and temporary files route to /tmp
$app->useStoragePath($storagePath);

// Process the incoming request
$app->handleRequest(Request::capture());
