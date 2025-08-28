<?php

namespace Kettasoft\Filterable\Tests\Unit\Engines;

use Illuminate\Http\Request;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Engines\Ruleset;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Exceptions\InvalidOperatorException;
use Kettasoft\Filterable\Exceptions\NotAllowedFieldException;
use Symfony\Component\HttpFoundation\InputBag;

class RulesetEngineTest extends TestCase
{
  public function setUp(): void
  {
    parent::setUp();

    $total = 15;

    Post::factory($total)->create([
      'status' => 'stopped',
      'title' => 'PHP',
      'content' => 'PHP artical'
    ]);

    Post::factory($total)->create([
      'status' => 'active',
      'title' => 'C#',
      'content' => 'C# artical'
    ]);

    Post::factory($total)->create([
      'status' => 'pending',
      'title' => 'Java',
      'content' => 'Java artical'
    ]);

    config()->set('filterable.default_engine', 'ruleset');
  }

  /**
   * It applies basic ruleset filters correctly.
   * @test
   */
  public function it_applies_basic_ruleset_filters_correctly()
  {
    $request = Request::create('/posts?status=pending');

    $filter = Filterable::withRequest($request)->setAllowedFields(['status'])->useEngin(Ruleset::class)->apply(Post::query());

    $this->assertEquals(15, $filter->count());
  }

  /**
   * It throw exception when enable engine strict mode with not allowed fields.
   * @test
   */
  public function it_throw_exception_when_enable_engine_strict_mode_globally_when_has_not_allowed_fields()
  {
    config()->set('filterable.engines.ruleset.strict', true);

    $request = Request::create('/posts?status=pending');

    $this->assertThrows(function () use ($request) {
      Filterable::withRequest($request)
        ->setAllowedFields([])
        ->useEngin(Ruleset::class)
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
        ->useEngin(Ruleset::class)
        ->apply(Post::query());
    }, NotAllowedFieldException::class);
  }

  /**
   * It can permissive mode locally.
   * @test
   */
  public function it_can_use_permissive_mode_locally()
  {
    config()->set('filterable.engines.ruleset.strict', true);

    $request = Request::create('/posts?status=pending');

    $filterable = Filterable::withRequest($request)
      ->permissive()
      ->setAllowedFields([])
      ->useEngin(Ruleset::class)
      ->apply(Post::query());

    $this->assertEquals(45, $filterable->count());
  }

  /**
   * It applies basic ruleset filters correctly.
   * @test
   */
  public function it_cant_filtering_with_not_allowed_operators()
  {
    $request = Request::create('/posts?status=like:pending');

    $this->assertThrows(function () use ($request) {
      Filterable::withRequest($request)
        ->setAllowedFields(['status'])
        ->allowdOperators(['eq'])
        ->useEngin(Ruleset::class)
        ->apply(Post::query());
    }, InvalidOperatorException::class);
  }

  /**
   * It can use default operator when invalid receved operator
   * @test
   */
  public function it_can_use_default_operator_when_invalid_receved_operator()
  {
    config()->set('filterable.engines.ruleset.strict', false);
    $request = Request::create('/posts?status=like:pending');

    $filterable = Filterable::withRequest($request)
      ->setAllowedFields(['status'])
      ->allowdOperators(['eq'])
      ->useEngin(Ruleset::class)
      ->apply(Post::query());

    $this->assertEquals(15, $filterable->count());
  }

  /**
   * It cant use default operator when enabled strict mode.
   * @test
   */
  public function it_cant_use_default_operator_when_enabled_strict_mode()
  {
    config()->set('filterable.engines.ruleset.strict', false);
    $request = Request::create('/posts?status=like:pending');

    $this->assertThrows(function () use ($request) {
      Filterable::withRequest($request)
        ->setAllowedFields(['status'])
        ->allowdOperators(['eq'])
        ->useEngin(Ruleset::class)
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
      ->useEngin(Ruleset::class)
      ->strict()
      ->apply(Post::query());

    $this->assertEquals(15, $filter->count());
  }

  public function test_it_sanitize_value_before_applying_to_query()
  {
    $request = Request::create('/posts?status=eq:PENDING');

    $filter = Filterable::withRequest($request)
      ->setAllowedFields(['status'])
      ->useEngin(Ruleset::class)
      ->setSanitizers([
        'status' => fn($value) => strtolower($value)
      ])
      ->apply(Post::query());

    $this->assertEquals(15, $filter->count());
  }
}
