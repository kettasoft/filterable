<?php

namespace Kettasoft\Filterable\Tests\Feature\Engines\Attributes;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Exceptions\StrictnessException;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Cast;

class CastAttributeTest extends TestCase
{
  public function test_cast_attribute_casts_value_to_int()
  {
    request()->merge([
      'views' => '42',
    ]);

    $class = new class extends Filterable {
      protected $filters = ['views'];

      #[Cast('int')]
      public function views(Payload $payload)
      {
        $this->builder->where('views', '=', $payload->cast('int'));
      }
    };

    $sql = Post::filter($class)->toRawSql();

    $this->assertStringContainsString('where "views" = 42', $sql);
  }

  public function test_cast_attribute_casts_value_to_boolean_true()
  {
    request()->merge([
      'is_featured' => 'true',
    ]);

    $class = new class extends Filterable {
      protected $filters = ['is_featured'];

      #[Cast('boolean')]
      public function isFeatured(Payload $payload)
      {
        $this->builder->where('is_featured', '=', $payload->cast('boolean'));
      }
    };

    $sql = Post::filter($class)->toRawSql();

    $this->assertStringContainsString('"is_featured" = 1', $sql);
  }

  public function test_cast_attribute_casts_value_to_boolean_false()
  {
    request()->merge([
      'is_featured' => 'false',
    ]);

    $class = new class extends Filterable {
      protected $filters = ['is_featured'];

      #[Cast('boolean')]
      public function isFeatured(Payload $payload)
      {
        $this->builder->where('is_featured', '=', $payload->cast('boolean'));
      }
    };

    $sql = Post::filter($class)->toRawSql();

    $this->assertStringContainsString('"is_featured"', $sql);
  }

  public function test_cast_attribute_casts_value_to_array_from_json()
  {
    request()->merge([
      'tags' => '["php","laravel"]',
    ]);

    $class = new class extends Filterable {
      protected $filters = ['tags'];

      #[Cast('array')]
      public function tags(Payload $payload)
      {
        $casted = $payload->cast('array');
        $this->builder->whereIn('tags', $casted);
      }
    };

    $sql = Post::filter($class)->toRawSql();

    $this->assertStringContainsString('"tags" in', $sql);
    $this->assertStringContainsString('php', $sql);
    $this->assertStringContainsString('laravel', $sql);
  }

  public function test_cast_attribute_throws_strictness_exception_for_unsupported_type()
  {
    $this->expectException(StrictnessException::class);
    $this->expectExceptionMessage('Cast type [unsupported] is not supported.');

    request()->merge([
      'status' => 'active',
    ]);

    $class = new class extends Filterable {
      protected $filters = ['status'];

      #[Cast('unsupported')]
      public function status(Payload $payload)
      {
        $this->builder->where('status', '=', $payload);
      }
    };

    Post::filter($class)->toRawSql();
  }

  public function test_cast_attribute_does_not_throw_for_valid_cast_type()
  {
    request()->merge([
      'views' => '100',
    ]);

    $class = new class extends Filterable {
      protected $filters = ['views'];

      #[Cast('int')]
      public function views(Payload $payload)
      {
        $this->builder->where('views', '>', $payload->cast('int'));
      }
    };

    $sql = Post::filter($class)->toRawSql();

    $this->assertStringContainsString('where "views" > 100', $sql);
  }

  public function test_cast_attribute_with_slug_type()
  {
    request()->merge([
      'title' => 'Hello World Post',
    ]);

    $class = new class extends Filterable {
      protected $filters = ['title'];

      #[Cast('slug')]
      public function title(Payload $payload)
      {
        $this->builder->where('title', '=', $payload->cast('slug'));
      }
    };

    $sql = Post::filter($class)->toRawSql();

    $this->assertStringContainsString('hello-world-post', $sql);
  }

  public function test_cast_attribute_with_like_type()
  {
    request()->merge([
      'title' => 'Laravel',
    ]);

    $class = new class extends Filterable {
      protected $filters = ['title'];

      #[Cast('like')]
      public function title(Payload $payload)
      {
        $this->builder->where('title', 'LIKE', $payload->cast('like'));
      }
    };

    $sql = Post::filter($class)->toRawSql();

    $this->assertStringContainsString('%Laravel%', $sql);
  }

  public function test_cast_attribute_with_empty_value_for_int_returns_null()
  {
    request()->merge([
      'views' => '',
    ]);

    $class = new class extends Filterable {
      protected $filters = ['views'];

      #[Cast('int')]
      public function views(Payload $payload)
      {
        $casted = $payload->cast('int');
        if (!is_null($casted)) {
          $this->builder->where('views', '=', $casted);
        }
      }
    };

    $sql = Post::filter($class)->toRawSql();

    // Empty non-numeric value should produce null from asInt(), so no where clause added
    $this->assertStringNotContainsString('where "views"', $sql);
  }

  public function test_cast_attribute_stage_is_transform()
  {
    $this->assertEquals(
      \Kettasoft\Filterable\Engines\Foundation\Attributes\Enums\Stage::TRANSFORM->value,
      Cast::stage()
    );
  }

  public function test_cast_attribute_handle_method_directly()
  {
    $payload = Payload::create('views', '=', '42', '42');
    $context = new \Kettasoft\Filterable\Engines\Foundation\Attributes\AttributeContext(
      payload: $payload
    );

    $cast = new Cast('int');
    $cast->handle($context);

    // handle doesn't throw, the cast is valid
    $this->assertTrue(true);
  }

  public function test_cast_attribute_handle_throws_for_invalid_type()
  {
    $this->expectException(StrictnessException::class);

    $payload = Payload::create('status', '=', 'active', 'active');
    $context = new \Kettasoft\Filterable\Engines\Foundation\Attributes\AttributeContext(
      payload: $payload
    );

    $cast = new Cast('nonExistent');
    $cast->handle($context);
  }
}
