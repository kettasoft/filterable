<?php

namespace Kettasoft\Filterable\Tests\Unit\Filterable;

use Kettasoft\Filterable\Filterable;
use Illuminate\Database\Eloquent\Model;
use Kettasoft\Filterable\Tests\TestCase;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Kettasoft\Filterable\Providers\AutoRegisterFilterableServiceProvider;

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

  public function test_filter_scope_can_return_the_raw_eloquent_builder(): void
  {
    $result = $this->model()->filter(Filterable::create()->shouldReturnQueryBuilder());

    $this->assertInstanceOf(EloquentBuilder::class, $result);
  }

  protected function model()
  {
    $model = new class extends Model {};

    return $model;
  }
}
