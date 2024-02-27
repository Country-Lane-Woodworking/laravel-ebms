<?php

namespace Mile6\LaravelEBMS;

use Illuminate\Http\Client\Factory;
use Illuminate\Support\ServiceProvider;
use Mile6\LaravelEBMS\Commands\LaravelEBMSCommand;

class LaravelEBMSServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishResources();

        $this->mergeConfigFrom(__DIR__ . '/../config/ebms.php', 'ebms');
    }

    public function register()
    {
        $this->app->singleton(EBMS::class, function ($app) {
            return new EBMS($app[Factory::class]);
        });

        $this->app->bind('ebms', EBMS::class);
    }

    public function publishResources()
    {
        $this->publishes([
            __DIR__ . '/../config/ebms.php' => config_path('ebms.php')
        ]);
    }
}
