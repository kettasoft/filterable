<?php

namespace Kettasoft\Filterable\Providers;

use Illuminate\Support\ServiceProvider;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Foundation\Profiler\Contracts\ProfilerStorageContract;

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
      __DIR__ . '/../../stubs/' => base_path('stubs')
    ], 'stubs');

    $this->registerCommands();
  }

  /**
   * Register any application services.
   *
   * @return void
   */
  public function register()
  {
    $this->app->bind(ProfilerStorageContract::class, function ($app) {
      return match (config('filterable.profiler.store', 'database')) {
        'database' => new \Kettasoft\Filterable\Foundation\Profiler\Storage\DatabaseProfilerStorage(),
        'log' => new \Kettasoft\Filterable\Foundation\Profiler\Storage\FileProfilerStorage(),
        default => throw new \InvalidArgumentException('Invalid profiler storage type specified.'),
      };
    });

    $this->app->singleton('filterable', function ($app) {
      return (new Filterable($app['request']));
    });

    $this->mergeConfigFrom(
      __DIR__ . '/../../config/filterable.php',
      'filterable'
    );
  }

  /**
   * Register the generator command.
   *
   * @return void
   */
  protected function registerCommands()
  {
    $this->commands([
      \Kettasoft\Filterable\Commands\MakeFilterCommand::class,
    ]);
  }
}
