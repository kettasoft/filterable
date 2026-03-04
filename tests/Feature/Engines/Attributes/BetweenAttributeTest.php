<?php

namespace Kettasoft\Filterable\Tests\Feature\Engines\Attributes;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Between;

class BetweenAttributeTest extends TestCase
{
  public function test_between_attribute_allows_value_within_range()
  {
    request()->merge([
      'views' => '50',
    ]);

    $class = new class extends Filterable {
      protected $filters = ['views'];

      #[Between(min: 1, max: 100)]
      public function views(Payload $payload)
      {
        $this->builder->where('views', '=', $payload->value);
      }
    };

    $sql = Post::filter($class)->toRawSql();

    $this->assertStringContainsString('"views"', $sql);
    $this->assertStringContainsString('50', $sql);
  }

  public function test_between_attribute_allows_value_at_minimum_boundary()
  {
    request()->merge([
      'views' => '1',
    ]);

    $class = new class extends Filterable {
      protected $filters = ['views'];

      #[Between(min: 1, max: 100)]
      public function views(Payload $payload)
      {
        $this->builder->where('views', '=', $payload->value);
      }
    };

    $sql = Post::filter($class)->toRawSql();

    $this->assertStringContainsString('"views"', $sql);
  }

  public function test_between_attribute_allows_value_at_maximum_boundary()
  {
    request()->merge([
      'views' => '100',
    ]);

    $class = new class extends Filterable {
      protected $filters = ['views'];

      #[Between(min: 1, max: 100)]
      public function views(Payload $payload)
      {
        $this->builder->where('views', '=', $payload->value);
      }
    };

    $sql = Post::filter($class)->toRawSql();

    $this->assertStringContainsString('"views"', $sql);
  }

  public function test_between_attribute_skips_filter_when_value_below_range()
  {
    request()->merge([
      'views' => '0',
    ]);

    $class = new class extends Filterable {
      protected $filters = ['views'];

      #[Between(min: 1, max: 100)]
      public function views(Payload $payload)
      {
        $this->builder->where('views', '=', $payload->value);
      }
    };

    $sql = Post::filter($class)->toRawSql();

    // Filter should be skipped, no where clause for views
    $this->assertStringNotContainsString('"views" =', $sql);
  }

  public function test_between_attribute_skips_filter_when_value_above_range()
  {
    request()->merge([
      'views' => '200',
    ]);

    $class = new class extends Filterable {
      protected $filters = ['views'];

      #[Between(min: 1, max: 100)]
      public function views(Payload $payload)
      {
        $this->builder->where('views', '=', $payload->value);
      }
    };

    $sql = Post::filter($class)->toRawSql();

    // Filter should be skipped
    $this->assertStringNotContainsString('"views" =', $sql);
  }

  public function test_between_attribute_skips_filter_for_non_numeric_value()
  {
    request()->merge([
      'views' => 'abc',
    ]);

    $class = new class extends Filterable {
      protected $filters = ['views'];

      #[Between(min: 1, max: 100)]
      public function views(Payload $payload)
      {
        $this->builder->where('views', '=', $payload->value);
      }
    };

    $sql = Post::filter($class)->toRawSql();

    // Filter should be skipped
    $this->assertStringNotContainsString('"views" =', $sql);
  }

  public function test_between_attribute_works_with_float_values()
  {
    request()->merge([
      'views' => '3.5',
    ]);

    $class = new class extends Filterable {
      protected $filters = ['views'];

      #[Between(min: 1.0, max: 5.0)]
      public function views(Payload $payload)
      {
        $this->builder->where('views', '=', $payload->value);
      }
    };

    $sql = Post::filter($class)->toRawSql();

    $this->assertStringContainsString('"views"', $sql);
    $this->assertStringContainsString('3.5', $sql);
  }
}
