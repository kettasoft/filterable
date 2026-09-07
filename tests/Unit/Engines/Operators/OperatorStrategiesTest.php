<?php

namespace Kettasoft\Filterable\Tests\Unit\Engines\Operators;

use Illuminate\Http\Request;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\InputBag;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Tests\Models\Tag;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Engines\Exceptions\InvalidOperatorValueException;
use Kettasoft\Filterable\Engines\Foundation\Operators\OperatorResolver;
use Kettasoft\Filterable\Engines\Foundation\Operators\InOperator;
use Kettasoft\Filterable\Engines\Foundation\Operators\BetweenOperator;
use Kettasoft\Filterable\Engines\Foundation\Operators\NullOperator;
use Kettasoft\Filterable\Engines\Foundation\Operators\ComparisonOperator;
use Kettasoft\Filterable\Engines\Foundation\Operators\Contracts\Operator;
use Kettasoft\Filterable\Exceptions\InvalidOperatorDefinitionException;

class OperatorStrategiesTest extends TestCase
{
  public function test_ruleset_applies_in_with_an_array_value(): void
  {
    $this->seedPosts();

    $posts = $this->ruleset([
      'views' => ['in' => [10, 30]],
    ])->get();

    $this->assertSame([10, 30], $posts->pluck('views')->sort()->values()->all());
  }

  public function test_ruleset_applies_not_in_with_a_comma_separated_value(): void
  {
    $this->seedPosts();

    $posts = $this->ruleset([
      'views' => ['nin' => '10,30'],
    ])->get();

    $this->assertSame([20], $posts->pluck('views')->all());
  }

  public function test_expression_applies_between(): void
  {
    $this->seedPosts();
    $request = Request::create('/posts', 'GET', [
      'filter' => ['views' => ['between' => [10, 20]]],
    ]);

    $posts = Filterable::for(Post::class, $request)
      ->using('expression')
      ->setAllowedFields(['views'])
      ->get();

    $this->assertSame([10, 20], $posts->pluck('views')->sort()->values()->all());
  }

  public function test_not_between_excludes_the_requested_range(): void
  {
    $this->seedPosts();

    $posts = $this->ruleset([
      'views' => ['nbetween' => [15, 25]],
    ])->get();

    $this->assertSame([10, 30], $posts->pluck('views')->sort()->values()->all());
  }

  public function test_allowed_operators_accept_resolved_sql_symbols(): void
  {
    $this->seedPosts();
    $request = Request::create('/posts', 'GET', [
      'views' => ['>=' => 20],
    ]);

    $posts = Filterable::for(Post::class, $request)
      ->using('ruleset')
      ->setAllowedFields(['views'])
      ->allowedOperators(['>='])
      ->get();

    $this->assertSame([20, 30], $posts->pluck('views')->sort()->values()->all());
  }

  public function test_empty_set_operators_use_eloquent_set_semantics(): void
  {
    $this->seedPosts();

    $included = $this->ruleset(['views' => ['in' => []]])->get();
    $excluded = $this->ruleset(['views' => ['nin' => []]])->get();

    $this->assertCount(0, $included);
    $this->assertCount(3, $excluded);
  }

  public function test_tree_engine_uses_the_same_operator_pipeline(): void
  {
    $this->seedPosts();
    $request = Request::create('/posts');
    $request->setJson(new InputBag([
      'filter' => [
        'and' => [[
          'field' => 'views',
          'operator' => 'between',
          'value' => [20, 30],
        ]],
      ],
    ]));

    $posts = Filterable::for(Post::class, $request)
      ->using('tree')
      ->setAllowedFields(['views'])
      ->get();

    $this->assertSame([20, 30], $posts->pluck('views')->sort()->values()->all());
  }

  public function test_null_operator_does_not_require_a_value(): void
  {
    $this->seedPosts();
    config()->set('filterable.engines.ruleset.ignore_empty_values', true);

    $posts = $this->ruleset([
      'description' => ['null' => null],
    ], ['description'])->get();

    $this->assertCount(2, $posts);
  }

  public function test_not_null_operator_ignores_its_value(): void
  {
    $this->seedPosts();

    $posts = $this->ruleset([
      'description' => ['notnull' => 'ignored'],
    ], ['description'])->get();

    $this->assertCount(1, $posts);
    $this->assertSame(20, $posts->first()->views);
  }

  public function test_special_operators_work_on_relational_fields(): void
  {
    [$first, $second] = $this->seedPosts();
    Tag::factory()->create(['post_id' => $first->id, 'name' => 'featured']);
    Tag::factory()->create(['post_id' => $second->id, 'name' => 'archived']);

    $request = Request::create('/posts', 'GET', [
      'filter' => [
        'tags' => ['name' => ['in' => ['featured', 'recommended']]],
      ],
    ]);

    $posts = Filterable::for(Post::class, $request)
      ->using('expression')
      ->allowRelations(['tags' => ['name']])
      ->get();

    $this->assertCount(1, $posts);
    $this->assertSame($first->id, $posts->first()->id);
  }

  public function test_structured_expression_condition_is_preserved(): void
  {
    $this->seedPosts();
    $request = Request::create('/posts', 'GET', [
      'filter' => [
        'views' => ['operator' => 'gte', 'value' => 20],
      ],
    ]);

    $posts = Filterable::for(Post::class, $request)
      ->using('expression')
      ->setAllowedFields(['views'])
      ->get();

    $this->assertSame([20, 30], $posts->pluck('views')->sort()->values()->all());
  }

  public function test_between_rejects_an_invalid_value_with_payload_context(): void
  {
    $this->seedPosts();

    try {
      $this->ruleset(['views' => ['between' => [10]]])->strict()->get();
      $this->fail('Expected an invalid operator value exception.');
    } catch (InvalidOperatorValueException $exception) {
      $this->assertSame('views', $exception->getPayload()?->field);
      $this->assertSame([10], $exception->getPayload()?->value);
    }
  }

  public function test_invalid_between_value_is_tracked_when_permissive(): void
  {
    $this->seedPosts();
    $filterable = $this->ruleset(['views' => ['between' => [10]]])->permissive();

    $this->assertCount(3, $filterable->get());
    $this->assertTrue($filterable->hasSkipped('views'));
  }

  public function test_a_custom_operator_strategy_can_override_a_resolved_operator(): void
  {
    $this->seedPosts();
    config()->set('filterable.engines.ruleset.allowed_operators.contains', 'contains');
    config()->set('filterable.operator_strategies.contains', ContainsOperator::class);

    $posts = $this->ruleset([
      'title' => ['contains' => 'cond'],
    ], ['title'])->get();

    $this->assertCount(1, $posts);
    $this->assertSame('Second', $posts->first()->title);
  }

  public function test_resolver_maps_special_and_comparison_operators(): void
  {
    $resolver = new OperatorResolver();

    $this->assertInstanceOf(InOperator::class, $resolver->resolve('NOT_IN'));
    $this->assertInstanceOf(BetweenOperator::class, $resolver->resolve('between'));
    $this->assertInstanceOf(NullOperator::class, $resolver->resolve('IS NULL'));
    $this->assertInstanceOf(ComparisonOperator::class, $resolver->resolve('>='));
  }

  public function test_invalid_custom_operator_definitions_fail_explicitly(): void
  {
    $this->expectException(InvalidOperatorDefinitionException::class);

    (new OperatorResolver(['contains' => \stdClass::class]))->resolve('contains');
  }

  private function ruleset(array $filters, array $fields = ['views']): Filterable
  {
    $request = Request::create('/posts', 'GET', $filters);

    return Filterable::for(Post::class, $request)
      ->using('ruleset')
      ->setAllowedFields($fields);
  }

  private function seedPosts(): array
  {
    return [
      Post::factory()->create([
        'title' => 'First',
        'status' => 'active',
        'views' => 10,
        'description' => null,
      ]),
      Post::factory()->create([
        'title' => 'Second',
        'status' => 'pending',
        'views' => 20,
        'description' => 'Contains text',
      ]),
      Post::factory()->create([
        'title' => 'Third',
        'status' => 'stopped',
        'views' => 30,
        'description' => null,
      ]),
    ];
  }
}

class ContainsOperator implements Operator
{
  public function __construct(private ContainsPattern $pattern) {}

  public function apply(Builder $builder, Payload $payload): Builder
  {
    return $builder->where($payload->field, 'like', $this->pattern->wrap($payload->value));
  }
}

class ContainsPattern
{
  public function wrap(mixed $value): string
  {
    return "%{$value}%";
  }
}
