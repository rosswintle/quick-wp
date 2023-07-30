<?php

namespace App\Providers;

use App\Services\Settings;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

class SettingsProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Initialise the settings object
        app(Settings::class)->init();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            Settings::class,
            function (Application $app) {
                return new Settings();
            }
        );
    }
}
