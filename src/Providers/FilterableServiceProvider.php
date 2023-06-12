<?php

namespace Kettasoft\Filterable\Providers;

use Kettasoft\Filterable\Commands\GenerateEloquentFilter;
use Illuminate\Support\ServiceProvider;

class FilterableServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/filterable.php' => config_path('filterable.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../stubs/filter.php' => base_path('stubs/filterable/filter.php')
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