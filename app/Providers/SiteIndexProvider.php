<?php

namespace App\Providers;

use App\Services\SiteIndex;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class SiteIndexProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        app(SiteIndex::class)->init();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            SiteIndex::class,
            function (Application $app) {
                return new SiteIndex;
            }
        );
    }
}
