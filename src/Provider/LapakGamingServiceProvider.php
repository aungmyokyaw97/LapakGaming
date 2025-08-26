<?php

namespace Amk\LapakGaming\Provider;

use Illuminate\Support\ServiceProvider;
use Amk\LapakGaming\LapakGaming;

class LapakGamingServiceProvider extends ServiceProvider 
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/lapakgaming.php' => config_path('lapakgaming.php')
            ], 'lapakgaming-config');
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(LapakGaming::class, function ($app) {
            return new LapakGaming();
        });
    }
}
