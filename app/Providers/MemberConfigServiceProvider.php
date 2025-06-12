<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class MemberConfigServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MemberConfigService::class, function ($app) {
            return new MemberConfigService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
