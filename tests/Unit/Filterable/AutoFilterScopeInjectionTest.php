<?php

namespace Kettasoft\Filterable\Tests\Unit\Filterable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Providers\AutoRegisterFilterableServiceProvider;
use Kettasoft\Filterable\Tests\TestCase;

class AutoFilterScopeInjectionTest extends TestCase
{
  protected function getPackageProviders($app)
  {
    return [AutoRegisterFilterableServiceProvider::class, ...parent::getPackageProviders($app)];
  }

  /**
   * It test filter scope is available without trait.
   * @test
   */
  public function ittest_filter_scope_is_available_without_trait()
  {
    $this->assertInstanceOf(Builder::class, $this->model()->filter(Filterable::create()));
  }

  protected function model()
  {
    $model = new class extends Model {};

    return $model;
  }
}
