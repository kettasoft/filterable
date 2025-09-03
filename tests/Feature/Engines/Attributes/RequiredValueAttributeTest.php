<?php

namespace Kettasoft\Filterable\Tests\Feature\Engines\Attributes;

use Kettasoft\Filterable\Tests\TestCase;

class RequiredValueAttributeTest extends TestCase
{
  public function test_required_value_attribute_throws_exception_when_value_missing()
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage("The parameter 'status' is required.");

    request()->merge([
      'status' => '',
    ]);

    $class = new class extends \Kettasoft\Filterable\Filterable {
      protected $filters = ['status'];

      #[\Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Required]
      public function status(\Kettasoft\Filterable\Support\Payload $payload)
      {
        $this->builder->where('name', '=', $payload);
      }
    };

    \Kettasoft\Filterable\Tests\Models\Post::filter($class)->toRawSql();
  }

  public function test_required_value_attribute_allows_processing_when_value_provided()
  {
    request()->merge([
      'status' => 'kettasoft',
    ]);
    $class = new class extends \Kettasoft\Filterable\Filterable {
      protected $filters = ['status'];

      #[\Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Required]
      public function status(\Kettasoft\Filterable\Support\Payload $payload)
      {
        $this->builder->where('name', '=', $payload);
      }
    };

    $sql = 'select * from "posts" where "name" = \'kettasoft\'';

    $this->assertStringContainsString($sql, \Kettasoft\Filterable\Tests\Models\Post::filter($class)->toRawSql());
  }

  public function test_required_value_attribute_throws_exception_when_value_missing_with_custom_message()
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage("The 'status' parameter is mandatory.");

    request()->merge([
      'status' => '',
    ]);

    $class = new class extends \Kettasoft\Filterable\Filterable {
      protected $filters = ['status'];

      #[\Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Required('The \'%s\' parameter is mandatory.')]
      public function status(\Kettasoft\Filterable\Support\Payload $payload)
      {
        $this->builder->where('name', '=', $payload);
      }
    };

    \Kettasoft\Filterable\Tests\Models\Post::filter($class);
  }
}
