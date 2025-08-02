<?php

namespace Kettasoft\Filterable\Tests\Unit\Filterable;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Tests\Models\Post;
use Illuminate\Contracts\Database\Query\Builder;
use Kettasoft\Filterable\Tests\Http\Filters\PostFilter;

class FilterAliasesTest extends TestCase
{
  public function setUp(): void
  {
    parent::setUp();

    config()->set('filterable.aliases', collect([
      'posts' => PostFilter::class
    ]));
  }
  public function test_it_can_use_alias_filter_name()
  {
    $this->assertInstanceOf(Builder::class, Post::filter('posts'));
  }
}
