<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$storagePath = '/tmp/laravel-storage';
foreach (['app', 'framework/cache/data', 'framework/sessions', 'framework/testing', 'framework/views', 'logs'] as $directory) {
    $path = $storagePath.'/'.$directory;
    if (! is_dir($path)) {
        mkdir($path, 0775, true);
    }
}

putenv('LARAVEL_STORAGE_PATH='.$storagePath);
$_ENV['LARAVEL_STORAGE_PATH'] = $storagePath;
$_SERVER['LARAVEL_STORAGE_PATH'] = $storagePath;
$_ENV['VIEW_COMPILED_PATH'] = $storagePath.'/framework/views';
$_SERVER['VIEW_COMPILED_PATH'] = $_ENV['VIEW_COMPILED_PATH'];

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->handleRequest(Request::capture());
