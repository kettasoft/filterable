<?php

namespace Kettasoft\Filterable\Tests\Feature\Engines\Attributes;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\SkipIf;

class SkipIfAttributeTest extends TestCase
{
  public function test_skip_if_attribute_skips_when_value_is_empty()
  {
    request()->merge([
      'status' => '',
    ]);

    $class = new class extends Filterable {
      protected $filters = ['status'];

      #[SkipIf('empty')]
      public function status(Payload $payload)
      {
        $this->builder->where('status', '=', $payload->value);
      }
    };

    $sql = Post::filter($class)->toRawSql();

    $this->assertStringNotContainsString('"status" =', $sql);
  }

  public function test_skip_if_attribute_does_not_skip_when_value_is_not_empty()
  {
    request()->merge([
      'status' => 'active',
    ]);

    $class = new class extends Filterable {
      protected $filters = ['status'];

      #[SkipIf('empty')]
      public function status(Payload $payload)
      {
        $this->builder->where('status', '=', $payload->value);
      }
    };

    $sql = Post::filter($class)->toRawSql();

    $this->assertStringContainsString("\"status\" = 'active'", $sql);
  }

  public function test_skip_if_attribute_skips_when_value_is_null()
  {
    request()->merge([
      'status' => null,
    ]);

    $class = new class extends Filterable {
      protected $filters = ['status'];

      #[SkipIf('null')]
      public function status(Payload $payload)
      {
        $this->builder->where('status', '=', $payload->value);
      }
    };

    $sql = Post::filter($class)->toRawSql();

    $this->assertStringNotContainsString('"status" =', $sql);
  }

  public function test_skip_if_attribute_with_negation_skips_when_value_is_not_numeric()
  {
    request()->merge([
      'views' => 'abc',
    ]);

    $class = new class extends Filterable {
      protected $filters = ['views'];

      #[SkipIf('!numeric')]
      public function views(Payload $payload)
      {
        $this->builder->where('views', '=', $payload->value);
      }
    };

    $sql = Post::filter($class)->toRawSql();

    // Should skip because !numeric is true (value is not numeric)
    $this->assertStringNotContainsString('"views" =', $sql);
  }

  public function test_skip_if_attribute_with_negation_does_not_skip_when_value_is_numeric()
  {
    request()->merge([
      'views' => '42',
    ]);

    $class = new class extends Filterable {
      protected $filters = ['views'];

      #[SkipIf('!numeric')]
      public function views(Payload $payload)
      {
        $this->builder->where('views', '=', $payload->value);
      }
    };

    $sql = Post::filter($class)->toRawSql();

    $this->assertStringContainsString('"views"', $sql);
    $this->assertStringContainsString('42', $sql);
  }

  public function test_skip_if_attribute_skips_when_value_is_empty_string()
  {
    request()->merge([
      'title' => '   ',
    ]);

    $class = new class extends Filterable {
      protected $filters = ['title'];

      #[SkipIf('emptyString')]
      public function title(Payload $payload)
      {
        $this->builder->where('title', '=', $payload->value);
      }
    };

    $sql = Post::filter($class)->toRawSql();

    $this->assertStringNotContainsString('"title" =', $sql);
  }

  public function test_skip_if_attribute_multiple_instances_on_same_method()
  {
    request()->merge([
      'status' => '',
    ]);

    $class = new class extends Filterable {
      protected $filters = ['status'];

      #[SkipIf('empty')]
      #[SkipIf('emptyString')]
      public function status(Payload $payload)
      {
        $this->builder->where('status', '=', $payload->value);
      }
    };

    $sql = Post::filter($class)->toRawSql();

    $this->assertStringNotContainsString('"status" =', $sql);
  }

  public function test_skip_if_attribute_skips_when_value_is_boolean()
  {
    request()->merge([
      'status' => 'true',
    ]);

    $class = new class extends Filterable {
      protected $filters = ['status'];

      #[SkipIf('boolean')]
      public function status(Payload $payload)
      {
        $this->builder->where('status', '=', $payload->value);
      }
    };

    $sql = Post::filter($class)->toRawSql();

    $this->assertStringNotContainsString('"status" =', $sql);
  }
}
