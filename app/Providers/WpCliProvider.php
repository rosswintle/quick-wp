<?php

namespace App\Providers;

use App\Services\WpCli;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

class WpCliProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        app(WpCli::class)->init();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            WpCli::class,
            function (Application $app) {
                return new WpCli();
            }
        );
    }
}
