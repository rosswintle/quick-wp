<?php

namespace App\Providers;

use App\Services\UserStorage;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class UserStorageProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            UserStorage::class,
            function (Application $app) {
                return new UserStorage;
            }
        );
    }
}
