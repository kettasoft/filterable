<?php

namespace Kettasoft\EloquentFilter\Providers;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Kettasoft\LaravelEloquentFilter\Commands\GenerateEloquentFilter;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/eloquentfilter.php' => config_path('eloquentfilter.php'),
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands(GenerateEloquentFilter::class);
    }
}