<?php

namespace Kettasoft\Filterable\Tests\Unit\Filterable;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\Http\Filters\PostFilter;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Tests\TestCase;

class FilterableThroughTest extends TestCase
{
  public function test_it_can_apply_filter_callbacks_with_through()
  {
    /**
     * @var Filterable
     */
    $filter = Filterable::create()->setBuilder(Post::query());

    $results = $filter->through([
      fn($builder) => $builder->where('id', 1)
    ]);

    $this->assertNotEmpty($results->apply()->getBindings());
  }

  public function test_it_throws_exception_when_through_callback_is_invalid()
  {
    $this->expectException(\InvalidArgumentException::class);

    /**
     * @var Filterable
     */
    $filter = Filterable::create()->setBuilder(Post::query());

    $filter->through([
      'invalid args'
    ]);
  }
}
