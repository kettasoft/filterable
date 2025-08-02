<?php

namespace Kettasoft\Filterable\Tests\Unit\Filterable;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Tests\Models\Post;
use Illuminate\Contracts\Database\Eloquent\Builder;

class ModelToBuilderResolutionTest extends TestCase
{
  /**
   * It can inject query builder from model string.
   * @test
   */
  public function it_can_inject_query_builder_from_model_string()
  {
    $filter = Filterable::create()->setModel(Post::class);

    $this->assertInstanceOf(Builder::class, $filter->apply());
  }

  /**
   * Summary of it_can_filtering_with_inject_model_as_instance
   * @test
   */
  public function it_can_inject_query_builder_from_model_instance()
  {
    $filter = Filterable::create()->setModel(new Post);

    $this->assertInstanceOf(Builder::class, $filter->apply());
  }

  /**
   * Summary of it_can_filtering_with_inject_model_as_by_class
   * @test
   */
  public function it_can_inject_query_builder_from_model_using_custom_class()
  {
    $filter = new class extends Filterable {
      protected $model = Post::class;
    };

    $this->assertInstanceOf(Builder::class, $filter->apply());
  }
}
