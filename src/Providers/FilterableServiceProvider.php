<?php

namespace Kettasoft\Filterable\Providers;

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
