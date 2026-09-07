<?php

namespace Kettasoft\Filterable\Tests\Unit\Support;

use PHPUnit\Framework\TestCase;
use Kettasoft\Filterable\Support\RelationFieldParser;

class RelationFieldParserTest extends TestCase
{
  public function test_it_flattens_only_registered_relation_paths()
  {
    $result = RelationFieldParser::parse([
      'tags' => ['name' => 'featured'],
      'metadata' => ['locale' => 'en'],
    ], ['tags']);

    $this->assertSame([
      'tags.name' => 'featured',
      'metadata' => ['locale' => 'en'],
    ], $result);
  }

  public function test_it_flattens_deep_associative_relation_definitions()
  {
    $result = RelationFieldParser::parse([
      'tags' => [
        'post' => [
          'status' => 'active',
        ],
      ],
    ], ['tags.post' => ['status']]);

    $this->assertSame(['tags.post.status' => 'active'], $result);
  }

  public function test_it_preserves_structured_ruleset_conditions()
  {
    $condition = ['operator' => 'eq', 'value' => 'active'];

    $result = RelationFieldParser::parse([
      'status' => $condition,
      'tags' => ['name' => $condition],
    ], ['tags'], ['eq']);

    $this->assertSame([
      'status' => $condition,
      'tags.name' => $condition,
    ], $result);
  }

  public function test_it_preserves_expression_conditions_and_list_values()
  {
    $result = RelationFieldParser::parse([
      'tags' => [
        'name' => ['like' => '%php%'],
        'status' => ['active', 'pending'],
      ],
    ], ['tags'], ['eq', 'like', 'in']);

    $this->assertSame([
      'tags.name' => ['like' => '%php%'],
      'tags.status' => ['active', 'pending'],
    ], $result);
  }

  public function test_it_preserves_empty_arrays()
  {
    $result = RelationFieldParser::parse([
      'tags' => ['name' => []],
    ], ['tags']);

    $this->assertSame(['tags.name' => []], $result);
  }

  public function test_it_supports_mixed_list_and_associative_relation_definitions()
  {
    $result = RelationFieldParser::parse([
      'comments' => ['body' => 'approved'],
      'tags' => ['name' => 'featured'],
    ], [
      'comments',
      'tags' => ['name'],
    ]);

    $this->assertSame([
      'comments.body' => 'approved',
      'tags.name' => 'featured',
    ], $result);
  }
}
