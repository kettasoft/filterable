<?php

namespace Kettasoft\Filterable\Tests\Unit\Filterable;

use Illuminate\Http\Request;
use Kettasoft\Filterable\Engines\Ruleset;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Tests\TestCase;

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
}
