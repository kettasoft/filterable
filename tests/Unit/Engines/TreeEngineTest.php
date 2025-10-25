<?php

namespace Kettasoft\Filterable\Tests\Unit\Engines;

use PHPUnit\Framework\Test;
use Illuminate\Http\Request;
use Kettasoft\Filterable\Filterable;
use Illuminate\Support\Facades\Config;
use Kettasoft\Filterable\Engines\Tree;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Tests\Models\Tag;
use Kettasoft\Filterable\Tests\Models\Post;
use Symfony\Component\HttpFoundation\InputBag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Kettasoft\Filterable\Exceptions\InvalidOperatorException;
use Kettasoft\Filterable\Exceptions\NotAllowedFieldException;
use Kettasoft\Filterable\Exceptions\InvalidDataFormatException;

class TreeEngineTest extends TestCase
{
  use RefreshDatabase;

  protected $request;

  public function setUp(): void
  {
    parent::setUp();

    $total = 15;

    Post::factory($total)->create([
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
      'post_id' => 1,
      'name' => 'stopped'
    ]);

    config()->set('filterable.default_engine', 'tree');

    $this->request = Request::capture()->setJson(new InputBag([
      "filter" => [
        "and" => [
          [
            "field" => "status",
            "operator" => "eq",
            "value" => "stopped"
          ],
          ['or' => []]
        ]
      ]
    ]));
  }

  /**
   * @test
   */
  public function it_use_tree__engine_with_simple_filtering()
  {
    $filter = Filterable::create($this->request)
      ->setAllowedFields(['*'])
      ->apply(Post::query());

    $this->assertEquals(15, $filter->count());
  }

  /**
   * @test
   */
  public function it_use_tree_engine_with_force_set_data()
  {
    $filter = Filterable::create()
      ->setData($this->request->json()->all())
      ->setAllowedFields(['*'])
      ->apply(Post::query());

    $this->assertEquals(15, $filter->count());
  }

  /**
   * @test
   */
  public function it_use_tree_based_engin_with_field_mapping()
  {
    $filter = Filterable::create($this->request)
      ->setFieldsMap(['filter_by_status' => 'status'])
      ->setAllowedFields(['*'])
      ->apply(Post::query());

    $this->assertEquals(15, $filter->count());
  }

  /**
   * It filter with tree based engin and enable strict mode option.
   * @test
   */
  public function it_make_filter_with_tree_engine_and_enable_strict_mode_globally()
  {
    Config::set('filterable.engines.tree.options.strict', true);

    $this->assertThrows(function () {
      Filterable::create($this->request)
        ->setAllowedFields([])
        ->apply(Post::query());
    }, NotAllowedFieldException::class);

    // Try after define allowed fields.
    $filter = Filterable::create($this->request)
      ->setAllowedFields(['status'])
      ->apply(Post::query());

    $this->assertEquals(15, $filter->count());
  }

  /**
   * It filter with tree based engin and enable strict mode option.
   * @test
   */
  public function it_make_filter_with_tree_engine_and_enable_strict_mode_locally()
  {

    Config::set('filterable.engines.tree.strict', false);

    $this->assertThrows(function () {
      Filterable::create($this->request)
        ->strict()
        ->setAllowedFields([])
        ->apply(Post::query());
    }, NotAllowedFieldException::class);

    // Try after define allowed fields.
    $filter = Filterable::create($this->request)
      ->strict()
      ->setAllowedFields(['status'])
      ->apply(Post::query());

    $this->assertEquals(15, $filter->count());
  }

  /**
   * It filter with tree based engin and enable strict mode option.
   * @test
   */
  public function it_can_filter_with_allowed_operators_only()
  {
    $data = [
      "filter" => [
        "and" => [
          ["field" => "status", "operator" => "eq", "value" => "pending"],
          ['or' => []]
        ]
      ]
    ];

    // Try after define allowed fields.
    $filter = Filterable::create()->strict()
      ->allowdOperators(['eq'])
      ->setAllowedFields(['status'])
      ->setData($data)
      ->apply(Post::query());

    $this->assertEquals(15, $filter->count());
  }

  /**
   * It filter with tree based engin and enable strict mode option.
   * @test
   */
  public function it_cant_filtering_with_not_allowed_operator()
  {
    $data = [
      "filter" => [
        "and" => [
          ["field" => "status", "operator" => "like", "value" => "pending"],
          ['or' => []]
        ]
      ]
    ];

    $this->assertThrows(function () use ($data) {
      Filterable::create()->strict()
        ->allowdOperators(['eq'])
        ->setAllowedFields(['*'])
        ->useEngine(Tree::class)
        ->setData($data)
        ->apply(Post::query());
    }, InvalidOperatorException::class);

    // Try after define allowed operator.
    $filter = Filterable::create()
      ->strict()
      ->setAllowedFields(['status'])
      ->allowdOperators(['like'])
      ->setData($data)
      ->apply(Post::query());

    $this->assertEquals(15, $filter->count());
  }

  /**
   * It filter with tree based engin and enable strict mode option.
   * @test
   */
  public function it_can_use_default_operator_when_receved_operator_is_not_allowed_with_permissive_option()
  {
    $data = [
      "filter" => [
        "and" => [
          ["field" => "status", "operator" => "like", "value" => "pending"],
          ['or' => []]
        ]
      ]
    ];

    $filter = Filterable::create()
      ->permissive()
      ->setAllowedFields(['*'])
      ->allowdOperators(['in'])
      ->setData($data)
      ->apply(Post::query());

    $this->assertEquals(15, $filter->count());
  }

  /**
   * It filter with tree based engin and enable strict mode option.
   * @test
   */
  public function it_can_use_default_operator_when_receved_operator_is_null_with_permissive_option()
  {
    $data = [
      "filter" => [
        "and" => [
          ["field" => "status", "operator" => null, "value" => "pending"],
          ['or' => []]
        ]
      ]
    ];

    $filter = Filterable::create()
      ->permissive()
      ->setAllowedFields(['*'])
      ->setData($data)
      ->apply(Post::query());

    $this->assertEquals(15, $filter->count());
  }

  /**
   * It filter with tree based engin and enable strict mode option.
   * @test
   */
  public function it_throw_error_when_data_is_incorrectly()
  {
    $data = [
      "filter" => [
        "and" => [
          ["incorrectly" => "status", "value" => "pending"],
          ['or' => []]
        ]
      ]
    ];

    $this->assertThrows(function () use ($data) {
      Filterable::create()
        ->permissive()
        ->setAllowedFields(['*'])
        ->setData($data)
        ->apply(Post::query());
    }, InvalidDataFormatException::class);
  }

  /**
   * It filter with tree based engin and enable strict mode option.
   * @test
   */
  #[Test]
  public function it_filter_with_tree_based_engin_relations_and_allowed_specific_relation_path()
  {
    $data = [
      "filter" => [
        "and" => [
          // ["field" => "status", "operator" => "eq", "value" => "stopped"],
          ["field" => "tags.name", "operator" => "eq", "value" => "stopped"],
          ['or' => []]
        ]
      ]
    ];

    // Config::set('filterable.engines.tree.strict', true);

    // Try after define allowed fields.
    $filter = Filterable::create()
      ->setRelations(['tags' => ['name']])
      ->setAllowedFields(['status'])
      ->useEngine(Tree::class)
      ->setData($data)
      ->apply(Post::query());

    $this->assertEquals(1, $filter->count());
  }

  /**
   * It filter with tree based engin and enable strict mode option.
   * @test
   */
  public function it_can_filter_with_or_and_logical_operator()
  {
    $data = [
      "filter" => [
        "and" => [
          ["field" => "status", "operator" => "eq", "value" => "stopped"],
          ['or' => [
            ["field" => "status", "operator" => "eq", "value" => "active"],
            ["field" => "status", "operator" => "eq", "value" => "pending"],
          ]]
        ]
      ]
    ];

    $filter = Filterable::create()
      ->setData($data, true)
      ->setAllowedFields(['*'])
      ->apply(Post::query());

    $this->assertEquals(45, $filter->count());
  }

  public function test_it_sanitize_value_before_applying_to_query()
  {
    $data = [
      "filter" => [
        "and" => [
          ["field" => "status", "operator" => "eq", "value" => "STOPPED"],
          ['or' => []]
        ]
      ]
    ];

    $filter = Filterable::create()
      ->setData($data, true)
      ->setAllowedFields(['status'])
      ->useEngine(Tree::class)
      ->setSanitizers([
        'status' => fn($value) => strtolower($value)
      ])
      ->apply(Post::query());

    $this->assertEquals(15, $filter->count());
  }
}
