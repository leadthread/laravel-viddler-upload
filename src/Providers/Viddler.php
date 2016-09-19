<?php

namespace Zenapply\Viddler\Upload\Providers;

use Illuminate\Support\ServiceProvider as Provider;
use Zenapply\Viddler\Upload\Service;

class Viddler extends Provider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/viddler.php', 'viddler');

        $this->app->singleton('viddler', function () {
            return new Service;
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../migrations/2016_03_17_000000_create_lern_tables.php' => base_path('database/migrations/2016_03_17_000000_create_lern_tables.php'),
            __DIR__ . '/../../config/viddler.php' => base_path('config/viddler.php'),
        ]);
    }
}
