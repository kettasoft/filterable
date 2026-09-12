<?php

namespace Kettasoft\Filterable\Tests\Feature;

use Illuminate\Http\Request;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Exceptions\InvalidPageSizeException;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Tests\TestCase;

class PaginationPolicyTest extends TestCase
{
  public function setUp(): void
  {
    parent::setUp();

    Post::factory(12)->create();
  }

  public function test_paginate_uses_the_request_page_size(): void
  {
    $paginator = $this->filterable(['per_page' => '4'])->paginate();

    $this->assertSame(4, $paginator->perPage());
    $this->assertCount(4, $paginator->items());
  }

  public function test_all_pagination_methods_enforce_the_configured_maximum(): void
  {
    config()->set('filterable.pagination.max', 5);

    $lengthAware = $this->filterable(['per_page' => 20])->paginate();
    $simple = $this->filterable(['per_page' => 20])->simplePaginate();
    $cursor = $this->filterable(['per_page' => 20])->orderBy('id')->cursorPaginate();

    $this->assertSame(5, $lengthAware->perPage());
    $this->assertSame(5, $simple->perPage());
    $this->assertSame(5, $cursor->perPage());
  }

  public function test_an_explicit_page_size_wins_over_request_input_but_not_the_maximum(): void
  {
    config()->set('filterable.pagination.max', 10);

    $requested = $this->filterable(['per_page' => 8])->paginate(6);
    $clamped = $this->filterable(['per_page' => 8])->paginate(50);

    $this->assertSame(6, $requested->perPage());
    $this->assertSame(10, $clamped->perPage());
  }

  public function test_runtime_settings_override_global_configuration(): void
  {
    config()->set('filterable.pagination.max', 5);

    $paginator = $this->filterable(['limit' => 30])
      ->paginationPolicy(
        defaultPerPage: 10,
        maxPerPage: 25,
        parameter: 'limit'
      )
      ->paginate();

    $this->assertSame(25, $paginator->perPage());
  }

  public function test_filter_class_settings_override_global_configuration(): void
  {
    config()->set('filterable.pagination.max', 5);

    $request = Request::create('/posts', 'GET', ['per_page' => 15]);
    $filterable = new class($request) extends Filterable {
      protected $pagination = [
        'default' => 10,
        'max' => 20,
        'overflow' => 'clamp',
      ];
    };

    $paginator = $filterable->setBuilder(Post::query())->paginate();

    $this->assertSame(15, $paginator->perPage());
  }

  public function test_legacy_paginate_limit_remains_the_default_for_published_old_configuration(): void
  {
    config()->set('filterable.paginate_limit', 7);

    $paginator = $this->filterable()->paginate();

    $this->assertSame(7, $paginator->perPage());
  }

  public function test_reject_mode_throws_when_the_maximum_is_exceeded(): void
  {
    $this->expectException(InvalidPageSizeException::class);

    $this->filterable(['per_page' => 101])
      ->paginationPolicy(maxPerPage: 100, overflow: 'reject')
      ->paginate();
  }

  public function test_named_arguments_are_preserved(): void
  {
    $paginator = $this->filterable(['per_page' => 3])->paginate(
      columns: ['id', 'title'],
      pageName: 'posts_page'
    );

    $this->assertSame(3, $paginator->perPage());
    $this->assertSame('posts_page', $paginator->getPageName());
    $this->assertFalse($paginator->items()[0]->offsetExists('status'));
  }

  public function test_paginate_closure_is_still_supported_and_subject_to_the_maximum(): void
  {
    $paginator = $this->filterable()->paginate(fn(int $total): int => $total);

    $this->assertSame(12, $paginator->perPage());

    $clamped = $this->filterable()
      ->paginationPolicy(maxPerPage: 5)
      ->paginate(fn(int $total): int => $total);

    $this->assertSame(5, $clamped->perPage());
  }

  public function test_direct_model_pagination_is_not_affected_by_filterable_policy(): void
  {
    config()->set('filterable.pagination.max', 5);

    $paginator = Post::query()->paginate(8);

    $this->assertSame(8, $paginator->perPage());
  }

  public function test_direct_pagination_call_honors_policy_when_raw_builder_mode_is_enabled(): void
  {
    $paginator = $this->filterable(['per_page' => 20])
      ->paginationPolicy(maxPerPage: 5)
      ->shouldReturnQueryBuilder()
      ->paginate();

    $this->assertSame(5, $paginator->perPage());
  }

  public function test_an_explicit_raw_builder_return_is_a_deliberate_policy_escape_hatch(): void
  {
    $builder = $this->filterable(['per_page' => 20])
      ->paginationPolicy(maxPerPage: 5)
      ->shouldReturnQueryBuilder()
      ->apply();

    $paginator = $builder->paginate(8);

    $this->assertSame(8, $paginator->perPage());
  }

  private function filterable(array $query = []): Filterable
  {
    return Filterable::for(
      Post::class,
      Request::create('/posts', 'GET', $query)
    );
  }
}
