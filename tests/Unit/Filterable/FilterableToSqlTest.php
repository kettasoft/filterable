<?php

namespace Kettasoft\Filterable\Tests\Unit\Filterable;

use Illuminate\Http\Request;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Engines\Ruleset;
use Kettasoft\Filterable\Tests\Http\Filters\PostFilter;
use Kettasoft\Filterable\Tests\Models\Post;

class FilterableToSqlTest extends TestCase
{
  /**
   * It can generate sql string.
   * @test
   */
  public function it_can_generate_sql_string()
  {
    $request = Request::create('/posts?status=pending');
    $filterable = filterable($request)->setAllowedFields(['status'])
      ->useEngin(Ruleset::class);

    $this->assertTrue(is_string($filterable->toSql(Post::query())));
  }

  public function test_it_can_create_without_params()
  {
    $this->assertInstanceOf(Filterable::class, filterable());
  }

  public function test_it_can_create_filterable_instance_with_context()
  {
    $filterable = filterable(context: PostFilter::class);

    $this->assertInstanceOf(PostFilter::class, $filterable);
    $this->assertEquals(get_class($filterable), PostFilter::class);
  }
}
