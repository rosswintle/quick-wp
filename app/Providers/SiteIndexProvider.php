<?php

namespace App\Providers;

use App\Services\SiteIndex;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

class SiteIndexProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
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
                return new SiteIndex();
            }
        );
    }
}
