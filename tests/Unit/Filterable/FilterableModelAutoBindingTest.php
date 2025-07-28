<?php

namespace Kettasoft\Filterable\Tests\Unit\Filterable;

use Illuminate\Database\Eloquent\Model;
use Kettasoft\Filterable\Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Kettasoft\Filterable\Traits\HasFilterable;
use Kettasoft\Filterable\Tests\Http\Filters\PostFilter;
use Kettasoft\Filterable\Exceptions\FilterClassNotResolvedException;

class FilterableModelAutoBindingTest extends TestCase
{
  public function test_it_applies_filter_automatically_from_model_property()
  {
    $model = new class extends Model {
      use HasFilterable;
      protected $filterable = PostFilter::class;
    };

    $this->assertInstanceOf(Builder::class, $model->filter());
  }

  public function test_it_throws_exception_if_no_filter_class_and_no_model_property()
  {
    $model = new class extends Model {
      use HasFilterable;
    };

    $this->expectException(FilterClassNotResolvedException::class);

    $model->filter();
  }
}
