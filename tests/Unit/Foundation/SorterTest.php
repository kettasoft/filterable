<?php

namespace Kettasoft\Filterable\Tests\Unit\Foundation;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Foundation\Contracts\Sortable;
use Kettasoft\Filterable\Tests\Http\Filters\PostFilter;

class SorterTest extends TestCase
{
  public function test_it_registers_sorting_rules_for_a_filterable_class()
  {
    Filterable::addSorting(PostFilter::class, function (Sortable $sort) {
      return $sort->allow(['id', 'title'])
        ->default('id', 'asc')
        ->alias('recent', ['created_at' => 'desc']);
    });

    $sortable = Filterable::getSorting(PostFilter::class);

    $this->assertNotNull($sortable);
    $this->assertEquals(['id', 'title'], $sortable->getAllowed());
    $this->assertEquals(['id', 'asc'], $sortable->getDefault());
    $this->assertEquals(['recent' => ['created_at' => 'desc']], $sortable->getAliases());
  }

  public function test_it_applies_default_sorting()
  {
    Filterable::addSorting([PostFilter::class], function (Sortable $sort) {
      return $sort->allow(['id', 'title', 'created_at'])
        ->default('created_at', 'desc');
    });

    $query = Post::filter(new PostFilter());

    $this->assertStringContainsString('order by "created_at" desc', $query->toSql());
  }

  public function test_it_applies_requested_sorting()
  {
    request()->merge(['sort' => 'title,-id']);

    Filterable::addSorting(PostFilter::class, function (Sortable $sort) {
      return $sort->allow(['title', 'id']);
    });

    $query = Post::filter(new PostFilter());

    $sql = $query->toSql();

    $this->assertStringContainsString('order by "title" asc, "id" desc', $sql);
  }

  public function test_it_applies_alias_sorting_correctly()
  {
    request()->merge(['sort' => 'id,title,recent']);

    Filterable::addSorting(PostFilter::class, function (Sortable $sort) {
      return $sort->allow(['id', 'title', 'created_at'])
        ->alias('recent', [['created_at', 'desc']]);
    });

    $query = Post::filter(new PostFilter());

    $sql = $query->toSql();

    $this->assertStringContainsString('order by "created_at" desc, "id" asc, "title" asc', $sql);
  }

  public function test_it_ignores_invalid_sorting_fields()
  {
    request()->merge(['sort' => 'id,invalid_field,-another_invalid']);

    Filterable::addSorting(PostFilter::class, function (Sortable $sort) {
      return $sort->allow(['id', 'title']);
    });

    $query = Post::filter(new PostFilter());

    $sql = $query->toSql();

    $this->assertStringContainsString('order by "id" asc', $sql);
    $this->assertStringNotContainsString('invalid_field', $sql);
    $this->assertStringNotContainsString('another_invalid', $sql);
  }

  public function test_it_allows_all_fields_for_sorting()
  {
    request()->merge(['sort' => 'id,any_field,-another_field']);

    Filterable::addSorting(PostFilter::class, function (Sortable $sort) {
      return $sort->allow(['*']);
    });

    $query = Post::filter(new PostFilter());

    $sql = $query->toSql();

    $this->assertStringContainsString('order by "id" asc, "any_field" asc, "another_field" desc', $sql);
  }

  public function test_it_falls_back_to_default_when_no_sorting_provided()
  {
    Filterable::addSorting(PostFilter::class, function (Sortable $sort) {
      return $sort->allow(['id', 'title'])
        ->default('title', 'desc');
    });

    $query = Post::filter(new PostFilter());

    $sql = $query->toSql();

    $this->assertStringContainsString('order by "title" desc', $sql);
  }

  public function test_it_falls_back_to_empty_when_no_sorting_provided_and_no_default_set()
  {
    Filterable::addSorting(PostFilter::class, function (Sortable $sort) {
      return $sort->allow(['id', 'title']);
    });

    $query = Post::filter(new PostFilter());

    $sql = $query->toSql();

    $this->assertStringNotContainsString('order by', $sql);
  }

  public function test_it_falls_back_to_default_when_only_invalid_sorting_provided()
  {
    request()->merge(['sort' => 'invalid_field,-another_invalid']);

    Filterable::addSorting(PostFilter::class, function (Sortable $sort) {
      return $sort->allow(['id', 'title'])
        ->default('id', 'asc');
    });

    $query = Post::filter(new PostFilter());

    $sql = $query->toSql();

    $this->assertStringContainsString('order by "id" asc', $sql);
    $this->assertStringNotContainsString('invalid_field', $sql);
    $this->assertStringNotContainsString('another_invalid', $sql);
  }

  public function test_it_handles_no_allowed_fields()
  {
    request()->merge(['sort' => 'id,title']);

    Filterable::addSorting(PostFilter::class, function (Sortable $sort) {
      return $sort->allow([]);
    });

    $query = Post::filter(new PostFilter());

    $sql = $query->toSql();

    $this->assertStringNotContainsString('order by', $sql);
  }

  public function test_it_handles_no_sorting_registered()
  {
    request()->merge(['sort' => 'id,title']);

    $query = Post::filter(new PostFilter());

    $sql = $query->toSql();

    $this->assertStringNotContainsString('order by', $sql);
  }

  public function test_it_handles_empty_sorting_input()
  {
    request()->merge(['sort' => '']);

    Filterable::addSorting(PostFilter::class, function (Sortable $sort) {
      return $sort->allow(['id', 'title'])
        ->default('id', 'asc');
    });

    $query = Post::filter(new PostFilter());

    $sql = $query->toSql();

    $this->assertStringContainsString('order by "id" asc', $sql);
  }

  public function test_it_handles_no_sorting_input()
  {
    // No 'sort' parameter in request

    Filterable::addSorting(PostFilter::class, function (Sortable $sort) {
      return $sort->allow(['id', 'title'])
        ->default('id', 'asc');
    });

    $query = Post::filter(new PostFilter());

    $sql = $query->toSql();

    $this->assertStringContainsString('order by "id" asc', $sql);
  }

  public function test_it_can_register_local_sorting()
  {
    request()->merge(['sort' => 'custom_sort']);

    $filter = Filterable::create()->sorting(function (Sortable $sort) {
      return $sort->allow(['id', 'title'])
        ->alias('custom_sort', [['title', 'asc'], ['id', 'desc']]);
    });

    $query = Post::filter($filter);

    $sql = $query->toSql();

    $this->assertStringContainsString('order by "title" asc, "id" desc', $sql);
  }

  public function test_it_prefers_local_over_global_sorting()
  {
    request()->merge(['sort' => 'custom_sort']);

    Filterable::addSorting(PostFilter::class, function (Sortable $sort) {
      return $sort->allow(['id', 'title'])
        ->alias('custom_sort', [['title', 'desc'], ['id', 'asc']]);
    });

    $filter = PostFilter::create()->sorting(function (Sortable $sort) {
      return $sort->allow(['id', 'title'])
        ->alias('custom_sort', [['title', 'asc'], ['id', 'desc']]);
    });

    $query = Post::filter($filter);

    $sql = $query->toSql();

    // Should use local sorting (title asc, id desc)
    $this->assertStringContainsString('order by "title" asc, "id" desc', $sql);
    $this->assertStringNotContainsString('order by "title" desc, "id" asc', $sql);
  }

  public function test_it_throws_exception_for_invalid_sorting_callback()
  {
    $this->expectException(\InvalidArgumentException::class);
    Filterable::addSorting(PostFilter::class, 'non_existent_function');
  }

  public function test_it_can_sorting_with_invokable_class()
  {
    request()->merge(['sort' => 'title,-id']);

    $class = new class implements \Kettasoft\Filterable\Foundation\Contracts\Sorting\Invokable {
      public function __invoke(Sortable $sort): Sortable
      {
        return $sort->allow(['title', 'id']);
      }
    };

    Filterable::addSorting(PostFilter::class, $class);

    $query = Post::filter(new PostFilter());

    $sql = $query->toSql();

    $this->assertStringContainsString('order by "title" asc, "id" desc', $sql);
  }

  public function test_it_can_map_fields_for_sorting()
  {
    request()->merge(['sort' => 'name,-date']);

    Filterable::addSorting(PostFilter::class, function (Sortable $sort) {
      return $sort->allow(['name', 'date'])
        ->map(['name' => 'title', 'date' => 'created_at']);
    });

    $query = Post::filter(new PostFilter());

    $sql = $query->toSql();

    $this->assertStringContainsString('order by "title" asc, "created_at" desc', $sql);
  }
}
