<?php

namespace Zenapply\Viddler\Providers;

class ServiceProvider extends ServiceProvider
{
    public function register() {
        $this->mergeConfigFrom(__DIR__ . '/../../config/viddler.php', 'viddler');

        $this->app->singleton('viddler', function() {
            return new Viddler;
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/viddler.php' => base_path('config/viddler.php'),
        ]);   
    }
}