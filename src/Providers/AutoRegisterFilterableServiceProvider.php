<?php

namespace Kettasoft\Filterable\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use Kettasoft\Filterable\Support\FilterResolver;
use Kettasoft\Filterable\Contracts\FilterableContext;

class AutoRegisterFilterableServiceProvider extends ServiceProvider
{
  /**
   * Bootstrap any application services.
   *
   * @return void
   */
  public function boot()
  {
    Builder::macro('filter', function (FilterableContext|string|null $filter = null) {
      /** @var Builder */
      $builder = $this;

      return (new FilterResolver($builder, $filter))->resolve();
    });
  }

  /**
   * Register any application services.
   *
   * @return void
   */
  public function register()
  {
    //
  }
}
