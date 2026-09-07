<?php

namespace Kettasoft\Filterable\Tests\Unit\Filterable;

use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Foundation\Invoker;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Tests\Models\Post;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Facades\Filterable as FilterableFacade;
use Symfony\Component\HttpFoundation\InputBag;
use Kettasoft\Filterable\Engines\Exceptions\InvalidDataFormatException;

class FilterableForMethodTest extends TestCase
{
  public function test_it_creates_an_initialized_instance_for_a_model_class()
  {
    $filterable = Filterable::for(Post::class);

    $this->assertSame(Post::class, $filterable->getModel());
    $this->assertInstanceOf(Builder::class, $filterable->getBuilder());
    $this->assertInstanceOf(Post::class, $filterable->getBuilder()->getModel());
  }

  public function test_it_preserves_a_model_instance()
  {
    $model = new Post;
    $filterable = Filterable::for($model);

    $this->assertSame($model, $filterable->getModel());
    $this->assertSame($model, $filterable->getBuilder()->getModel());
  }

  public function test_it_preserves_a_builder_and_derives_its_model()
  {
    $builder = Post::query()->where('status', 'published');
    $filterable = Filterable::for($builder);

    $this->assertSame($builder, $filterable->getBuilder());
    $this->assertSame($builder->getModel(), $filterable->getModel());
    $this->assertStringContainsString('where "status" = ?', $filterable->getBuilder()->toSql());
  }

  public function test_it_accepts_a_custom_request()
  {
    $request = Request::create('/posts', 'GET', ['status' => 'published']);

    $filterable = Filterable::for(Post::class, $request);

    $this->assertSame($request, $filterable->getRequest());
    $this->assertSame('published', $filterable->getData()['status']);
  }

  public function test_it_uses_late_static_binding()
  {
    $filterClass = new class extends Filterable {};

    $filterable = $filterClass::for(Post::class);

    $this->assertInstanceOf($filterClass::class, $filterable);
  }

  public function test_it_rejects_a_non_model_class_string()
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('must extend ' . \Illuminate\Database\Eloquent\Model::class);

    Filterable::for(\stdClass::class);
  }

  public function test_it_is_available_through_the_facade()
  {
    $filterable = FilterableFacade::for(Post::class);

    $this->assertInstanceOf(Filterable::class, $filterable);
    $this->assertInstanceOf(Post::class, $filterable->getBuilder()->getModel());
  }

  public function test_builder_methods_are_forwarded_through_an_invoker()
  {
    $filterable = Filterable::for(Post::class);

    $result = $filterable->where('status', 'published');

    $this->assertInstanceOf(Invoker::class, $result);
    $this->assertStringContainsString('where "status" = ?', $filterable->getBuilder()->toSql());
  }

  public function test_invoker_builder_methods_remain_fluent()
  {
    $invoker = Filterable::for(Post::class)->apply();

    $result = $invoker->where('status', 'published');

    $this->assertSame($invoker, $result);
    $this->assertStringContainsString('where "status" = ?', $invoker->getBuilder()->toSql());
  }

  public function test_it_applies_invokable_engine_filters_for_a_model_class()
  {
    $this->seedPosts();
    $request = Request::create('/posts', 'GET', ['status' => 'active']);
    $filterClass = new class extends Filterable {
      protected $filters = ['status'];

      protected function status(Payload $payload): Builder
      {
        return $this->getBuilder()->where($payload->field, $payload->value);
      }
    };

    $results = $filterClass::for(Post::class, $request)
      ->useEngine('invokable')
      ->apply()
      ->get();

    $this->assertCount(2, $results);
    $this->assertSame(['active'], $results->pluck('status')->unique()->values()->all());
  }

  public function test_it_applies_ruleset_engine_filters_for_a_model_instance()
  {
    $this->seedPosts();
    $request = Request::create('/posts', 'GET', ['status' => 'pending']);

    $results = Filterable::for(new Post, $request)
      ->useEngine('ruleset')
      ->setAllowedFields(['status'])
      ->apply()
      ->get();

    $this->assertCount(1, $results);
    $this->assertSame('pending', $results->first()->status);
  }

  public function test_it_applies_expression_engine_without_losing_builder_constraints()
  {
    $this->seedPosts();
    $builder = Post::query()->where('status', 'active');
    $request = Request::create('/posts', 'GET', [
      'filter' => ['views' => ['eq' => 250]],
    ]);

    $filterable = Filterable::for($builder, $request)
      ->useEngine('expression')
      ->setAllowedFields(['views']);
    $results = $filterable->apply()->get();

    $this->assertSame($builder, $filterable->getBuilder());
    $this->assertCount(1, $results);
    $this->assertSame('Second active post', $results->first()->title);
  }

  public function test_it_applies_tree_engine_without_losing_builder_constraints()
  {
    $this->seedPosts();
    $builder = Post::query()->where('views', '>=', 100);
    $request = Request::create('/posts', 'POST');
    $request->setJson(new InputBag([
      'filter' => [
        'and' => [[
          'field' => 'status',
          'operator' => 'eq',
          'value' => 'active',
        ]],
      ],
    ]));

    $results = Filterable::for($builder, $request)
      ->useEngine('tree')
      ->setAllowedFields(['status'])
      ->apply()
      ->get();

    $this->assertCount(2, $results);
    $this->assertSame(['active'], $results->pluck('status')->unique()->values()->all());
  }

  #[DataProvider('engineProvider')]
  public function test_non_tree_engines_accept_an_empty_request(string $engine)
  {
    $this->seedPosts();
    $request = Request::create('/posts');

    $count = Filterable::for(Post::class, $request)
      ->useEngine($engine)
      ->setAllowedFields(['*'])
      ->apply()
      ->count();

    $this->assertSame(4, $count);
  }

  public static function engineProvider(): array
  {
    return [
      'invokable' => ['invokable'],
      'ruleset' => ['ruleset'],
      'expression' => ['expression'],
    ];
  }

  public function test_tree_engine_reports_an_empty_request_as_invalid()
  {
    $this->expectException(InvalidDataFormatException::class);

    Filterable::for(Post::class, Request::create('/posts'))
      ->useEngine('tree')
      ->setAllowedFields(['*'])
      ->apply();
  }

  public function test_created_instances_do_not_share_builder_constraints()
  {
    $active = Filterable::for(Post::class)->where('status', 'active');
    $pending = Filterable::for(Post::class)->where('status', 'pending');

    $this->assertNotSame($active->getBuilder(), $pending->getBuilder());
    $this->assertSame(['active'], $active->getBuilder()->getBindings());
    $this->assertSame(['pending'], $pending->getBuilder()->getBindings());
  }

  public function test_it_rejects_an_unknown_model_class_before_booting()
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Model [App\\Models\\MissingPost] must extend');

    Filterable::for('App\\Models\\MissingPost');
  }

  private function seedPosts(): void
  {
    Post::factory()->create([
      'title' => 'First active post',
      'status' => 'active',
      'views' => 100,
    ]);
    Post::factory()->create([
      'title' => 'Second active post',
      'status' => 'active',
      'views' => 250,
    ]);
    Post::factory()->create([
      'title' => 'Pending post',
      'status' => 'pending',
      'views' => 250,
    ]);
    Post::factory()->create([
      'title' => 'Stopped post',
      'status' => 'stopped',
      'views' => 50,
    ]);
  }
}
