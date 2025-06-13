<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        App\Providers\MemberConfigServiceProvider::class,
        App\Providers\AppServiceProvider::class,
        App\Providers\PromotionServiceProvider::class,
        //App\Providers\JwtServiceProvider::class
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
         $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
            'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
            //'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'session' => \Illuminate\Session\Middleware\StartSession::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
            'sua' => \App\Http\Middleware\SanctumUserAuthentication::class,
            'admin' => \App\Http\Middleware\Admin::class,
            'admin.view.menus' => \App\Http\Middleware\AdminViewMiddleware::class,
            'admin.menus' => \App\Http\Middleware\AdminMenus::class,
            'cors' => \App\Http\Middleware\Cors::class,
            'encrypt-cookies' => \App\Http\Middleware\EncryptCookies::class,
            'add-queue-cookies' => \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            'share-errors' => \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            'csrf' => \App\Http\Middleware\VerifyCsrfToken::class,
            'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
            'admin-access' => \App\Http\Middleware\CheckAdminAccess::class,
            'sanctum' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'client-session' => \App\Http\Middleware\ClientSession::class,
            'response-structure' => \App\Http\Middleware\ResponseStructure::class,
            'setup.check' => \App\Http\Middleware\SetupCheckMiddleware::class,
        ]);

        $middleware->web([
            'encrypt-cookies',
            'add-queue-cookies',
            'session',
            'share-errors',
            'csrf',
            'bindings',
            //'admin-access',
        ]);

        // API 미들웨어 그룹 (별칭 사용)
        $middleware->api([
            'sanctum',
            //'throttle:api',
            'session',
            'bindings',
            'client-session',
            'response-structure',
        ]);

//        // 레이트 리미팅 설정
//        $middleware->throttle('api')->using(function () {
//            return Illuminate\Cache\RateLimiting\Limit::perMinute(500);
//        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();



