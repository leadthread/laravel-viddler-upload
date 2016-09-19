<?php

namespace Zenapply\Viddler\Upload\Providers;

use Illuminate\Support\ServiceProvider as Provider;
use Zenapply\Viddler\Upload\Service;

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