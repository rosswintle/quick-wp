<?php

namespace App\Providers;

use App\Services\WpCoreVersion;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

class WpCoreVersionProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        app(WpCoreVersion::class)->init();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            WpCoreVersion::class,
            function (Application $app) {
                return new WpCoreVersion();
            }
        );
    }
}
