<?php

namespace RenokiCo\Hej;

use Illuminate\Support\ServiceProvider;

class HejServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../database/migrations/2020_07_24_000000_create_socials_table.php' => database_path('migrations/2020_07_24_000000_create_socials_table.php'),
        ], 'migrations');

        $this->publishes([
            __DIR__.'/../config/hej.php' => config_path('hej.php'),
        ], 'config');

        $this->mergeConfigFrom(
            __DIR__.'/../config/hej.php', 'hej'
        );
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
