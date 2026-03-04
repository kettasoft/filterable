<?php

namespace Kettasoft\Filterable\Tests\Feature\Engines\Attributes;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Scope;

class ScopeAttributeTest extends TestCase
{
  public function test_scope_attribute_applies_eloquent_scope()
  {
    request()->merge([
      'status' => 'active',
    ]);

    $class = new class extends Filterable {
      protected $filters = ['status'];

      #[Scope('active')]
      public function status(Payload $payload)
      {
        // The scope is already applied by the attribute.
        // This method can add additional logic if needed.
      }
    };

    $sql = Post::filter($class)->toRawSql();

    $this->assertStringContainsString('"status"', $sql);
    $this->assertStringContainsString('active', $sql);
  }

  public function test_scope_attribute_applies_popular_scope_with_value()
  {
    request()->merge([
      'views' => '500',
    ]);

    $class = new class extends Filterable {
      protected $filters = ['views'];

      #[Scope('popular')]
      public function views(Payload $payload)
      {
        // Scope is applied by the attribute with the payload value.
      }
    };

    $sql = Post::filter($class)->toRawSql();

    $this->assertStringContainsString('"views" >=', $sql);
    $this->assertStringContainsString('500', $sql);
  }

  public function test_scope_attribute_skips_filter_for_non_existent_scope()
  {
    request()->merge([
      'status' => 'active',
    ]);

    $class = new class extends Filterable {
      protected $filters = ['status'];

      #[Scope('nonExistentScope')]
      public function status(Payload $payload)
      {
        $this->builder->where('status', '=', $payload->value);
      }
    };

    $sql = Post::filter($class)->toRawSql();

    // The scope does not exist, so the filter should be skipped entirely
    // because the InvalidArgumentException is caught by the engine's attempt handler.
    $this->assertStringNotContainsString('"status" =', $sql);
  }

  public function test_scope_attribute_works_with_other_attributes()
  {
    request()->merge([
      'status' => '  active  ',
    ]);

    $class = new class extends Filterable {
      protected $filters = ['status'];

      #[\Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Trim]
      #[Scope('active')]
      public function status(Payload $payload)
      {
        // Trim runs first (TRANSFORM stage), then Scope (BEHAVIOR stage).
      }
    };

    $sql = Post::filter($class)->toRawSql();

    $this->assertStringContainsString('"status"', $sql);
    $this->assertStringContainsString('active', $sql);
  }
}
