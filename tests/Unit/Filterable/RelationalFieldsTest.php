<?php

namespace Kettasoft\Filterable\Tests\Unit\Filterable;

use Illuminate\Http\Request;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Tests\Models\Tag;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Engines\Exceptions\NotAllowedFieldException;

class RelationalFieldsTest extends TestCase
{
  public function test_ruleset_filters_a_nested_relation_array()
  {
    [$active] = $this->seedPostsWithTags();
    $request = Request::create('/posts', 'GET', [
      'tags' => ['name' => 'featured'],
    ]);

    $posts = Filterable::for(Post::class, $request)
      ->using('ruleset')
      ->allowRelations(['tags'])
      ->get();

    $this->assertCount(1, $posts);
    $this->assertSame($active->id, $posts->first()->id);
  }

  public function test_expression_filters_a_nested_relation_condition()
  {
    [$active] = $this->seedPostsWithTags();
    $request = Request::create('/posts', 'GET', [
      'filter' => [
        'tags' => [
          'name' => ['eq' => 'featured'],
        ],
      ],
    ]);

    $posts = Filterable::for(Post::class, $request)
      ->using('expression')
      ->allowRelations(['tags' => ['name']])
      ->get();

    $this->assertCount(1, $posts);
    $this->assertSame($active->id, $posts->first()->id);
  }

  public function test_ruleset_filters_a_deep_nested_relation_array()
  {
    [$active] = $this->seedPostsWithTags();
    $request = Request::create('/posts', 'GET', [
      'tags' => [
        'post' => [
          'status' => 'active',
        ],
      ],
    ]);

    $posts = Filterable::for(Post::class, $request)
      ->using('ruleset')
      ->allowRelations(['tags.post' => ['status']])
      ->get();

    $this->assertCount(1, $posts);
    $this->assertSame($active->id, $posts->first()->id);
  }

  public function test_nested_parser_does_not_break_structured_ruleset_conditions()
  {
    $this->seedPostsWithTags();
    $request = Request::create('/posts', 'GET', [
      'status' => ['operator' => 'eq', 'value' => 'active'],
    ]);

    $posts = Filterable::for(Post::class, $request)
      ->using('ruleset')
      ->setAllowedFields(['status'])
      ->allowRelations(['tags'])
      ->get();

    $this->assertCount(1, $posts);
    $this->assertSame('active', $posts->first()->status);
  }

  public function test_associative_relation_fields_reject_unlisted_fields()
  {
    $this->seedPostsWithTags();
    $request = Request::create('/posts', 'GET', [
      'tags' => ['name' => 'featured'],
    ]);

    $this->expectException(NotAllowedFieldException::class);

    Filterable::for(Post::class, $request)
      ->using('ruleset')
      ->strict()
      ->allowRelations(['tags' => ['id']])
      ->get();
  }

  public function test_relation_field_wildcard_allows_any_field()
  {
    [$active] = $this->seedPostsWithTags();
    $request = Request::create('/posts', 'GET', [
      'tags' => ['name' => 'featured'],
    ]);

    $posts = Filterable::for(Post::class, $request)
      ->using('ruleset')
      ->allowRelations(['tags' => ['*']])
      ->get();

    $this->assertCount(1, $posts);
    $this->assertSame($active->id, $posts->first()->id);
  }

  public function test_field_wildcard_is_recognized_in_any_position()
  {
    $this->seedPostsWithTags();
    $request = Request::create('/posts', 'GET', ['title' => 'Active post']);

    $posts = Filterable::for(Post::class, $request)
      ->using('ruleset')
      ->setAllowedFields(['status', '*'])
      ->get();

    $this->assertCount(1, $posts);
  }

  public function test_mixed_relation_definitions_are_authorized_independently()
  {
    $filterable = Filterable::for(Post::class)->allowRelations([
      'comments',
      'tags' => ['name'],
    ]);

    $this->assertTrue($filterable->hasRelationPath('comments.body'));
    $this->assertTrue($filterable->hasRelationPath('tags.name'));
    $this->assertFalse($filterable->hasRelationPath('tags.id'));
  }

  private function seedPostsWithTags(): array
  {
    $active = Post::factory()->create([
      'title' => 'Active post',
      'status' => 'active',
    ]);
    $pending = Post::factory()->create([
      'title' => 'Pending post',
      'status' => 'pending',
    ]);

    Tag::factory()->create(['post_id' => $active->id, 'name' => 'featured']);
    Tag::factory()->create(['post_id' => $pending->id, 'name' => 'archived']);

    return [$active, $pending];
  }
}
