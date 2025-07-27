<?php

namespace Kettasoft\Filterable\Tests\Unit\Engines;

use Illuminate\Http\Request;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Tests\Models\Tag;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Engines\Expression;
use Symfony\Component\HttpFoundation\InputBag;
use Kettasoft\Filterable\Exceptions\InvalidOperatorException;
use Kettasoft\Filterable\Exceptions\NotAllowedFieldException;

class ExpressionEngineTest extends TestCase
{
  public function setUp(): void
  {
    parent::setUp();

    $total = 15;

    $posts1 = Post::factory()->create([
      'status' => 'stopped',
    ]);

    Post::factory($total)->create([
      'status' => 'active',
      'content' => null
    ]);

    Post::factory($total)->create([
      'status' => 'pending',
      'content' => null
    ]);

    Tag::factory()->create([
      'post_id' => $posts1->first()->id,
      'name' => 'stopped'
    ]);
  }

  /**
   * It applies basic ruleset filters correctly.
   * @test
   */
  public function it_applies_basic_ruleset_filters_correctly()
  {
    $request = Request::create('/posts?filter[status][eq]=pending');

    $filter = Filterable::withRequest($request)
      ->useEngin('expression')
      ->setAllowedFields(['status'])
      ->apply(Post::query());

    $this->assertEquals(15, $filter->count());
  }

  /**
   * It throw exception when enable engine strict mode with not allowed fields.
   * @test
   */
  public function it_throw_exception_when_enable_engine_strict_mode_globally_when_has_not_allowed_fields()
  {
    $request = Request::create('/posts?status=pending');

    $this->assertThrows(function () use ($request) {
      Filterable::withRequest($request)
        ->setAllowedFields([])
        ->useEngin('expression')
        ->apply(Post::query());
    }, NotAllowedFieldException::class);
  }

  /**
   * It throw exception when enable engine strict mode with not allowed fields.
   * @test
   */
  public function it_throw_exception_when_enable_engine_strict_mode_locally_when_has_not_allowed_fields()
  {
    // Disable strict mode globally
    config()->set('filterable.engines.ruleset.strict', false);

    $request = Request::create('/posts?status=pending');

    $this->assertThrows(function () use ($request) {
      Filterable::withRequest($request)
        ->strict()
        ->setAllowedFields([])
        ->useEngin(Expression::class)
        ->apply(Post::query());
    }, NotAllowedFieldException::class);
  }

  /**
   * It can permissive mode locally.
   * @test
   */
  public function it_can_use_permissive_mode_locally()
  {
    config()->set('filterable.engines.expression.strict', true);

    $request = Request::create('/posts?status=pending');

    $filterable = Filterable::withRequest($request)
      ->permissive()
      ->setAllowedFields([])
      ->useEngin(Expression::class)
      ->apply(Post::query());

    $this->assertEquals(31, $filterable->count());
  }

  /**
   * @test
   */
  public function it_use_sql_expression_engin_with_relations_test()
  {
    $request = Request::create('/posts?filter[tags.name]=stopped&filter[status][eq]=stopped');

    $filter = Filterable::withRequest($request)
      ->setAllowedFields(['status'])
      ->setRelations([
        'tags'
      ])
      ->useEngin('expression')
      ->apply(Post::query());

    $this->assertEquals(1, $filter->count());
  }

  /**
   * It applies basic ruleset filters correctly.
   * @test
   */
  public function it_cant_filtering_with_not_allowed_operators()
  {
    $request = Request::create('/posts?filter[status][like]=stopped');

    $this->assertThrows(function () use ($request) {
      Filterable::withRequest($request)
        ->setAllowedFields(['status'])
        ->allowdOperators(['eq'])
        ->useEngin(Expression::class)
        ->apply(Post::query());
    }, InvalidOperatorException::class);
  }

  /**
   * It can use default operator when invalid receved operator
   * @test
   */
  public function it_can_use_default_operator_when_invalid_receved_operator()
  {
    config()->set('filterable.engines.expression.strict', false);
    $request = Request::create('/posts?filter[status][like]=pending');

    $filterable = Filterable::withRequest($request)
      ->setAllowedFields(['status'])
      ->allowdOperators(['eq'])
      ->useEngin('expression')
      ->apply(Post::query());

    $this->assertEquals(15, $filterable->count());
  }

  /**
   * It cant use default operator when enabled strict mode.
   * @test
   */
  public function it_cant_use_default_operator_when_enabled_strict_mode()
  {
    config()->set('filterable.engines.expression.strict', false);

    $request = Request::create('/posts?filter[status][like]=pending');

    $this->assertThrows(function () use ($request) {
      Filterable::withRequest($request)
        ->setAllowedFields(['status'])
        ->allowdOperators(['eq'])
        ->useEngin(Expression::class)
        ->strict()
        ->apply(Post::query());
    }, InvalidOperatorException::class);
  }

  /**
   * It can sent json data to filtering operate.
   * @test
   */
  public function it_can_sent_json_data_to_filtering_operate()
  {
    config()->set('filterable.engines.ruleset.strict', false);

    $request = Request::create('/posts');

    $request->setJson(new InputBag([
      'status' => 'pending'
    ]));

    $filter = Filterable::withRequest($request)
      ->setAllowedFields(['status'])
      ->useEngin(Expression::class)
      ->strict()
      ->apply(Post::query());

    // dd($filter->toRawSql());

    $this->assertEquals(15, $filter->count());
  }
}
