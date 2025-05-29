<?php

namespace Kettasoft\Filterable\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use Kettasoft\Filterable\Contracts\FilterableContext;
use Kettasoft\Filterable\Support\FilterRegisterator;

class AutoRegisterFilterableServiceProvider extends ServiceProvider
{
  /**
   * Bootstrap any application services.
   *
   * @return void
   */
  public function boot()
  {
    Builder::macro('filter', function (FilterableContext $filter) {
      /** @var Builder */
      $builder = $this;

      return (new FilterRegisterator($builder, $filter))->register();
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
