<?php

namespace Kettasoft\Filterable\Tests\Unit\Filterable;

use BadMethodCallException;
use Illuminate\Http\Request;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Foundation\Invoker;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Tests\Models\Post;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\InputBag;

class AutoApplyFiltersTest extends TestCase
{
  public function setUp(): void
  {
    parent::setUp();

    Post::factory(2)->create([
      'title' => 'Active post',
      'status' => 'active',
      'views' => 10,
    ]);
    Post::factory(2)->create([
      'title' => 'Pending post',
      'status' => 'pending',
      'views' => 20,
    ]);
    Post::factory(2)->create([
      'title' => 'Stopped post',
      'status' => 'stopped',
      'views' => 30,
    ]);
  }

  public function test_get_auto_applies_filters()
  {
    $posts = $this->rulesetFor('active')->get();

    $this->assertCount(2, $posts);
    $this->assertTrue($posts->every(fn(Post $post) => $post->status === 'active'));
  }

  public function test_get_accepts_a_column_list()
  {
    $posts = $this->rulesetFor('active')->get(['id', 'status']);

    $this->assertCount(2, $posts);
    $this->assertFalse($posts->first()->offsetExists('title'));
  }

  public function test_get_from_request_reads_from_the_configured_source()
  {
    $filterable = $this->rulesetFor('active');

    $this->assertSame('active', $filterable->getFromRequest('status'));
  }

  public function test_dynamic_builder_calls_apply_before_forwarding()
  {
    $filterable = $this->rulesetFor('active');

    $result = $filterable
      ->where('views', '>=', 10)
      ->orderBy('id');

    $this->assertInstanceOf(Invoker::class, $result);
    $this->assertNotSame([], $filterable->applied());
    $this->assertCount(2, $result->get());
  }

  public function test_retrieval_and_aggregate_terminals_auto_apply()
  {
    $this->assertSame('active', $this->rulesetFor('active')->first()->status);
    $this->assertSame(2, $this->rulesetFor('active')->count());
    $this->assertSame(20, (int) $this->rulesetFor('active')->sum('views'));
    $this->assertSame(10, (int) $this->rulesetFor('active')->avg('views'));
    $this->assertSame(10, (int) $this->rulesetFor('active')->min('views'));
    $this->assertSame(10, (int) $this->rulesetFor('active')->max('views'));
  }

  public function test_extended_retrieval_terminals_respect_filters()
  {
    $active = Post::where('status', 'active')->firstOrFail();
    $pending = Post::where('status', 'pending')->firstOrFail();

    $this->assertNull(
      $this->rulesetFor('active')->firstWhere('id', $pending->id)
    );
    $this->assertSame(
      [$active->id],
      $this->rulesetFor('active')->findMany([$active->id, $pending->id])->modelKeys()
    );
    $this->assertSame(
      'active',
      $this->rulesetFor('active')->valueOrFail('status')
    );
  }

  public function test_boolean_and_scalar_terminals_auto_apply()
  {
    $this->assertTrue($this->rulesetFor('active')->exists());
    $this->assertTrue($this->rulesetFor('missing')->doesntExist());
    $this->assertSame('active', $this->rulesetFor('active')->value('status'));
    $this->assertSame(['active', 'active'], $this->rulesetFor('active')->pluck('status')->all());
  }

  public function test_pagination_auto_applies_filters()
  {
    $paginator = $this->rulesetFor('active')->paginate(1);
    $simplePaginator = $this->rulesetFor('active')->simplePaginate(5);

    $this->assertSame(2, $paginator->total());
    $this->assertCount(2, $simplePaginator->items());
  }

  public function test_streaming_terminals_auto_apply()
  {
    $seen = [];

    $this->rulesetFor('active')->chunk(1, function ($posts) use (&$seen) {
      array_push($seen, ...$posts->pluck('status')->all());
    });

    $this->assertSame(['active', 'active'], $seen);
    $this->assertSame(2, $this->rulesetFor('active')->lazy()->count());
  }

  public function test_update_and_delete_are_scoped_by_auto_applied_filters()
  {
    $updated = $this->rulesetFor('active')->update(['title' => 'Updated']);

    $this->assertSame(2, $updated);
    $this->assertSame(2, Post::where('title', 'Updated')->count());

    $deleted = $this->rulesetFor('pending')->delete();

    $this->assertSame(2, $deleted);
    $this->assertSame(4, Post::count());
  }

  public function test_expression_engine_auto_applies_on_terminal_calls()
  {
    $request = Request::create('/posts', 'GET', [
      'filter' => ['views' => ['eq' => 20]],
    ]);

    $count = Filterable::for(Post::class, $request)
      ->using('expression')
      ->setAllowedFields(['views'])
      ->count();

    $this->assertSame(2, $count);
  }

  public function test_tree_engine_auto_applies_on_terminal_calls()
  {
    $request = Request::create('/posts', 'POST');
    $request->setJson(new InputBag([
      'filter' => [
        'and' => [[
          'field' => 'status',
          'operator' => 'eq',
          'value' => 'stopped',
        ]],
      ],
    ]));

    $posts = Filterable::for(Post::class, $request)
      ->using('tree')
      ->setAllowedFields(['status'])
      ->get();

    $this->assertCount(2, $posts);
    $this->assertTrue($posts->every(fn(Post $post) => $post->status === 'stopped'));
  }

  public function test_invokable_engine_can_execute_a_protected_filter_method()
  {
    $request = Request::create('/posts', 'GET', ['status' => 'pending']);
    $filterClass = new class extends Filterable {
      protected $filters = ['status'];

      protected function status(Payload $payload): Builder
      {
        $builder = clone $this->getBuilder();

        return $builder->where($payload->field, $payload->value);
      }
    };

    $posts = $filterClass::for(Post::class, $request)
      ->using('invokable')
      ->get();

    $this->assertCount(2, $posts);
    $this->assertTrue($posts->every(fn(Post $post) => $post->status === 'pending'));
  }

  public function test_using_switches_the_runtime_engine_fluently()
  {
    $filterable = Filterable::for(Post::class);

    $result = $filterable->using('ruleset');

    $this->assertSame($filterable, $result);
    $this->assertSame('ruleset', $filterable->getEngine()->getEngineName());
  }

  public function test_unknown_builder_methods_fail_without_recursion()
  {
    $this->expectException(BadMethodCallException::class);

    $this->rulesetFor('active')->methodThatDoesNotExist();
  }

  private function rulesetFor(string $status): Filterable
  {
    $request = Request::create('/posts', 'GET', ['status' => $status]);

    return Filterable::for(Post::class, $request)
      ->using('ruleset')
      ->setAllowedFields(['status']);
  }
}
