<?php

namespace Zenapply\Viddler\Providers;

use Illuminate\Support\ServiceProvider as Provider;
use Zenapply\Viddler\Service;

class Viddler extends Provider
{
    public function register() {
        $this->mergeConfigFrom(__DIR__ . '/../../config/viddler.php', 'viddler');

        $this->app->singleton('viddler', function() {
            return new Service;
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/viddler.php' => base_path('config/viddler.php'),
        ]);   
    }
}