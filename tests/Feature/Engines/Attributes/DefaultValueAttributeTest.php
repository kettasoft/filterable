<?php

namespace Kettasoft\Filterable\Tests\Feature\Engines\Attributes;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\DefaultValue;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Tests\Models\Post;

class DefaultValueAttributeTest extends TestCase
{
  public function test_default_value_attribute_applies_default_value_when_none_provided()
  {
    request()->merge([
      'status' => '',
    ]);
    $class = new class extends Filterable {
      protected $filters = ['status'];

      #[DefaultValue('defaultValue')]
      public function status(Payload $payload)
      {
        $this->builder->where('name', '=', $payload);
      }
    };

    $sql = 'select * from "posts" where "name" = \'defaultValue\'';

    $this->assertStringContainsString($sql, Post::filter($class)->toRawSql());
  }

  public function test_default_value_attribute_does_not_override_provided_value()
  {
    request()->merge([
      'status' => 'kettasoft',
    ]);
    $class = new class extends Filterable {
      protected $filters = ['status'];

      #[DefaultValue('defaultValue')]
      public function status(Payload $payload)
      {
        $this->builder->where('name', '=', $payload);
      }
    };

    $sql = 'select * from "posts" where "name" = \'kettasoft\'';

    $this->assertStringContainsString($sql, Post::filter($class)->toRawSql());
  }
}
