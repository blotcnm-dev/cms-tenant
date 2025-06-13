<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));


// .env 파일 존재 확인
if (!file_exists(base_path('.env'))) {
    return redirect()->route('explain_setup');

    // Determine if the application is in maintenance mode...
    if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
        require $maintenance;
    }

    // Register the Composer autoloader...
    require __DIR__.'/../vendor/autoload.php';

    // Bootstrap Laravel and handle the request...
    (require_once __DIR__.'/../bootstrap/app.php')
        ->handleRequest(Request::capture());
}
