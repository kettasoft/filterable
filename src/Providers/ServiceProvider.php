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
            __DIR__ . '/../../config/eloquent-filter.php' => config_path('eloquent-filter.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../stubs/filter.php' => base_path('stubs/laravel-eloquent-filter/filter.php')
        ], 'stub');
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