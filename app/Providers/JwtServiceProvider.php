<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\JwtService;

class JwtServiceProvider extends ServiceProvider
{
    public function register()
    {

        $this->app->singleton(JwtService::class, function ($app) {
            return new JwtService();
        });

    }

public function boot()
{
//
}
}
