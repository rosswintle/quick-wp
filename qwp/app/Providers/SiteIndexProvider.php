<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SiteIndex;

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
        $this->app->singleton(SiteIndex::class, function ($app) {
            return new SiteIndex();
        });
    }
}
