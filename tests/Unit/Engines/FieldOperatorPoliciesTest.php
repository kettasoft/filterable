<?php

namespace Kettasoft\Filterable\Tests\Unit\Engines;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Kettasoft\Filterable\Engines\Exceptions\OperatorNotAllowedForFieldException;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Tests\Models\Tag;
use Kettasoft\Filterable\Tests\TestCase;
use Symfony\Component\HttpFoundation\InputBag;

class FieldOperatorPoliciesTest extends TestCase
{
  public function setUp(): void
  {
    parent::setUp();

    Post::factory()->create(['status' => 'active', 'views' => 10]);
    Post::factory()->create(['status' => 'pending', 'views' => 20]);
    Post::factory()->create(['status' => 'stopped', 'views' => 30]);
  }

  public function test_ruleset_accepts_an_operator_allowed_for_the_field(): void
  {
    $request = Request::create('/posts', 'GET', ['status' => 'eq:active']);

    $results = Filterable::for(Post::class, $request)
      ->using('ruleset')
      ->setAllowedFields(['status'])
      ->operatorPolicies(['status' => ['eq']])
      ->get();

    $this->assertCount(1, $results);
    $this->assertSame('active', $results->first()->status);
  }

  public function test_strict_mode_rejects_an_operator_not_allowed_for_the_field(): void
  {
    $request = Request::create('/posts', 'GET', ['status' => 'like:active']);

    $this->expectException(OperatorNotAllowedForFieldException::class);
    $this->expectExceptionMessage('Operator [like] is not allowed for field [status]');

    Filterable::for(Post::class, $request)
      ->using('ruleset')
      ->strict()
      ->setAllowedFields(['status'])
      ->allowOperatorsFor('status', ['eq'])
      ->get();
  }

  public function test_permissive_mode_skips_a_field_policy_violation_without_falling_back(): void
  {
    $request = Request::create('/posts', 'GET', ['status' => 'like:act']);

    $filterable = Filterable::for(Post::class, $request)
      ->using('ruleset')
      ->permissive()
      ->setAllowedFields(['status'])
      ->allowOperatorsFor('status', ['eq']);

    $results = $filterable->get();

    $this->assertCount(3, $results);
    $this->assertTrue($filterable->hasSkipped('status'));
    $this->assertNull($filterable->applied('status'));
    $this->assertSame(
      'Operator [like] is not allowed for field [status]',
      $filterable->skipped('status')[0]['reason']
    );
  }

  public function test_fields_without_a_policy_keep_the_global_operator_behavior(): void
  {
    $request = Request::create('/posts', 'GET', ['views' => 'gte:20']);

    $results = Filterable::for(Post::class, $request)
      ->using('ruleset')
      ->setAllowedFields(['status', 'views'])
      ->allowOperatorsFor('status', ['eq'])
      ->get();

    $this->assertSame([20, 30], $results->pluck('views')->sort()->values()->all());
  }

  public function test_a_field_policy_cannot_enable_a_globally_disabled_operator(): void
  {
    $request = Request::create('/posts', 'GET', ['status' => 'like:act']);

    $this->expectException(OperatorNotAllowedForFieldException::class);

    Filterable::for(Post::class, $request)
      ->using('ruleset')
      ->strict()
      ->setAllowedFields(['status'])
      ->allowedOperators(['eq'])
      ->allowOperatorsFor('status', ['like'])
      ->get();
  }

  public function test_policies_accept_resolved_operator_names(): void
  {
    $request = Request::create('/posts', 'GET', ['status' => 'eq:active']);

    $results = Filterable::for(Post::class, $request)
      ->using('ruleset')
      ->setAllowedFields(['status'])
      ->allowOperatorsFor('status', ['='])
      ->get();

    $this->assertCount(1, $results);
  }

  public function test_an_exact_policy_overrides_the_wildcard_policy(): void
  {
    $request = Request::create('/posts', 'GET', ['status' => 'like:active']);

    $results = Filterable::for(Post::class, $request)
      ->using('ruleset')
      ->setAllowedFields(['status'])
      ->allowOperatorsFor('*', ['eq'])
      ->allowOperatorsFor('status', ['like'])
      ->get();

    $this->assertCount(1, $results);
  }

  public function test_the_wildcard_policy_restricts_fields_without_an_exact_policy(): void
  {
    $request = Request::create('/posts', 'GET', ['views' => 'gte:20']);

    $this->expectException(OperatorNotAllowedForFieldException::class);

    Filterable::for(Post::class, $request)
      ->using('ruleset')
      ->strict()
      ->setAllowedFields(['views'])
      ->allowOperatorsFor('*', ['eq'])
      ->get();
  }

  public function test_policies_use_the_public_field_name_before_mapping(): void
  {
    $request = Request::create('/posts', 'GET', ['state' => 'eq:active']);

    $results = Filterable::for(Post::class, $request)
      ->using('ruleset')
      ->setAllowedFields(['state'])
      ->setFieldsMap(['state' => 'status'])
      ->allowOperatorsFor('state', ['eq'])
      ->get();

    $this->assertCount(1, $results);
  }

  public function test_expression_enforces_field_operator_policies(): void
  {
    $request = Request::create('/posts', 'GET', [
      'filter' => ['views' => ['gte' => 20]],
    ]);

    $this->expectException(OperatorNotAllowedForFieldException::class);

    Filterable::for(Post::class, $request)
      ->using('expression')
      ->strict()
      ->setAllowedFields(['views'])
      ->allowOperatorsFor('views', ['eq'])
      ->get();
  }

  public function test_expression_keeps_valid_conditions_when_a_policy_violation_is_skipped(): void
  {
    $request = Request::create('/posts', 'GET', [
      'filter' => [
        'status' => ['eq' => 'active'],
        'views' => ['lt' => 30],
      ],
    ]);

    $filterable = Filterable::for(Post::class, $request)
      ->using('expression')
      ->permissive()
      ->setAllowedFields(['status', 'views'])
      ->allowOperatorsFor('status', ['eq'])
      ->allowOperatorsFor('views', ['gte']);

    $results = $filterable->get();

    $this->assertCount(1, $results);
    $this->assertSame('active', $results->first()->status);
    $this->assertTrue($filterable->hasSkipped('views'));
    $this->assertNotNull($filterable->applied('status'));
  }

  public function test_tree_enforces_field_operator_policies(): void
  {
    $request = Request::create('/posts');
    $request->setJson(new InputBag([
      'filter' => [
        'and' => [
          ['field' => 'views', 'operator' => 'gte', 'value' => 20],
        ],
      ],
    ]));

    $this->expectException(OperatorNotAllowedForFieldException::class);

    Filterable::for(Post::class, $request)
      ->using('tree')
      ->strict()
      ->setAllowedFields(['views'])
      ->allowOperatorsFor('views', ['eq'])
      ->get();
  }

  public function test_tree_omits_a_policy_violation_and_preserves_valid_siblings(): void
  {
    $request = Request::create('/posts');
    $request->setJson(new InputBag([
      'filter' => [
        'and' => [
          ['field' => 'status', 'operator' => 'eq', 'value' => 'active'],
          ['field' => 'views', 'operator' => 'lt', 'value' => 30],
        ],
      ],
    ]));

    $filterable = Filterable::for(Post::class, $request)
      ->using('tree')
      ->permissive()
      ->setAllowedFields(['status', 'views'])
      ->allowOperatorsFor('status', ['eq'])
      ->allowOperatorsFor('views', ['gte']);

    $results = $filterable->get();

    $this->assertCount(1, $results);
    $this->assertSame('active', $results->first()->status);
    $this->assertTrue($filterable->hasSkipped('views'));
  }

  public function test_relational_fields_can_have_an_exact_policy(): void
  {
    $post = Post::query()->where('status', 'active')->firstOrFail();
    Tag::factory()->create(['post_id' => $post->id, 'name' => 'featured']);
    $request = Request::create('/posts', 'GET', [
      'filter' => ['tags.name' => ['eq' => 'featured']],
    ]);

    $results = Filterable::for(Post::class, $request)
      ->using('expression')
      ->setAllowedFields([])
      ->allowRelations(['tags' => ['name']])
      ->allowOperatorsFor('tags.name', ['eq'])
      ->get();

    $this->assertCount(1, $results);
    $this->assertSame($post->id, $results->first()->id);
  }

  public function test_invokable_filters_can_declare_policies_on_the_class(): void
  {
    $request = Request::create('/posts', 'GET', ['status' => 'like:act']);
    $filterable = new class($request) extends Filterable {
      protected $filters = ['status'];

      protected $fieldOperatorPolicies = [
        'status' => ['eq'],
      ];

      protected function status(Payload $payload): Builder
      {
        return $this->getBuilder()->where($payload->field, $payload->operator, $payload->value);
      }
    };

    $results = $filterable
      ->permissive()
      ->setModel(Post::class)
      ->setBuilder(Post::query())
      ->get();

    $this->assertCount(3, $results);
    $this->assertTrue($filterable->hasSkipped('status'));
  }

  public function test_runtime_policy_api_validates_field_and_operator_names(): void
  {
    $filterable = Filterable::create();

    $this->expectException(\InvalidArgumentException::class);

    $filterable->allowOperatorsFor('', ['eq']);
  }

  public function test_runtime_policy_api_rejects_invalid_operator_names(): void
  {
    $filterable = Filterable::create();

    $this->expectException(\InvalidArgumentException::class);

    $filterable->allowOperatorsFor('status', ['eq', '']);
  }

  public function test_runtime_policy_api_supports_multiple_fields(): void
  {
    $filterable = Filterable::create()
      ->allowOperatorsFor(['status', 'state'], ['eq', '=']);

    $expected = [
      'status' => ['eq', '='],
      'state' => ['eq', '='],
    ];

    $this->assertSame($expected, $filterable->getFieldOperatorPolicies());
  }

  public function test_bulk_policy_api_configures_many_fields_in_one_call(): void
  {
    $policies = [
      '*' => ['eq'],
      'status' => ['eq', 'in'],
      'title' => ['eq', 'like'],
      'views' => ['eq', 'gte', 'lte'],
    ];

    $filterable = Filterable::create()->operatorPolicies($policies);

    $this->assertSame($policies, $filterable->getFieldOperatorPolicies());
  }

  public function test_bulk_policy_api_replaces_existing_policies_by_default(): void
  {
    $filterable = Filterable::create()
      ->allowOperatorsFor('status', ['eq'])
      ->operatorPolicies(['views' => ['gte']]);

    $this->assertSame(
      ['views' => ['gte']],
      $filterable->getFieldOperatorPolicies()
    );
  }

  public function test_bulk_policy_api_can_merge_with_existing_policies(): void
  {
    $filterable = Filterable::create()
      ->operatorPolicies(['status' => ['eq']])
      ->operatorPolicies(['views' => ['gte']], false);

    $this->assertSame([
      'status' => ['eq'],
      'views' => ['gte'],
    ], $filterable->getFieldOperatorPolicies());
  }

  public function test_bulk_policy_api_rejects_a_non_array_operator_list(): void
  {
    $filterable = Filterable::create();

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Operator policy for field [status] must be an array.');

    /** @phpstan-ignore-next-line */
    $filterable->operatorPolicies(['status' => 'eq']);
  }
}
