<?php

namespace Kettasoft\Filterable\Tests\Unit\Sanitization;

use Kettasoft\Filterable\Sanitization\Defaults\TrimSanitizer;
use Kettasoft\Filterable\Sanitization\Contracts\Sanitizable;
use Kettasoft\Filterable\Sanitization\Sanitizer;
use Kettasoft\Filterable\Tests\TestCase;

class SanitizeUnitTest extends TestCase
{
  /**
   * It can sanitize value using resolver as string.
   * @test
   */
  public function it_can_sanitize_value_using_resolver_as_string()
  {
    $value = '    value';
    $resolver = TrimSanitizer::class;

    $afterSanitize = Sanitizer::apply($value, $resolver);

    $this->assertEquals(trim($value), $afterSanitize);
  }

  /**
   * It can sanitize value using resolver as function.
   * @test
   */
  public function it_can_sanitize_value_using_resolver_as_function()
  {
    $value = '    value';
    $resolver = function ($value) {
      return trim($value);
    };

    $afterSanitize = Sanitizer::apply($value, $resolver);

    $this->assertEquals(trim($value), $afterSanitize);
  }

  /**
   * It can sanitize value using resolver as instance.
   * @test
   */
  public function it_can_sanitize_value_using_resolver_as_instance()
  {
    $value = '    value';
    $resolver = new TrimSanitizer;

    $afterSanitize = Sanitizer::apply($value, $resolver);

    $this->assertEquals(trim($value), $afterSanitize);
  }

  /**
   * It can sanitize value using resolver as instance.
   * @test
   */
  public function it_can_sanitize_value_using_resolver_as_array()
  {
    $value = '    value';

    $resolvers = [
      fn($value) => strtoupper($value),
      new TrimSanitizer
    ];

    $afterSanitize = Sanitizer::apply($value, $resolvers);

    $this->assertEquals(strtoupper(trim($value)), $afterSanitize);
  }

  /**
   * It can sanitize a value using an invokable object.
   * @test
   */
  public function it_can_sanitize_value_using_an_invokable_object()
  {
    $resolver = new class {
      public function __invoke($value)
      {
        return strtoupper(trim($value));
      }
    };

    $this->assertSame('VALUE', Sanitizer::apply('  value  ', $resolver));
  }

  /**
   * It can sanitize a value using a callable array.
   * @test
   */
  public function it_can_sanitize_value_using_a_callable_array()
  {
    $resolver = new class {
      public function normalize($value)
      {
        return strtolower(trim($value));
      }
    };

    $this->assertSame(
      'value',
      Sanitizer::apply('  VALUE  ', [$resolver, 'normalize'])
    );
  }

  /**
   * Sanitizable objects take precedence over their __invoke method.
   * @test
   */
  public function it_uses_the_sanitizable_contract_for_invokable_sanitizers()
  {
    $resolver = new class implements Sanitizable {
      public function sanitize($value): mixed
      {
        return 'sanitized:' . trim($value);
      }

      public function __invoke($value)
      {
        return 'invoked:' . trim($value);
      }
    };

    $this->assertSame('sanitized:value', Sanitizer::apply(' value ', $resolver));
  }

  /**
   * It applies global sanitizers to all fields.
   * @test
   */
  public function it_applies_global_sanitizers_to_all_fields()
  {
    $sanitizer = new Sanitizer([
      TrimSanitizer::class, // Global sanitizer (numeric key)
      'email' => 'lowercase'
    ]);

    $result = $sanitizer->handle('email', '  TEST@EXAMPLE.COM  ');

    // Should trim first (global), then lowercase (field-specific)
    $this->assertEquals('test@example.com', $result);
  }

  /**
   * It applies multiple global sanitizers in order.
   * @test
   */
  public function it_applies_multiple_global_sanitizers_in_order()
  {
    $sanitizer = new Sanitizer([
      TrimSanitizer::class,               // Global #1
      fn($v) => strtoupper($v),           // Global #2
      'name' => fn($v) => str_replace(' ', '_', $v)
    ]);

    $result = $sanitizer->handle('name', '  john doe  ');

    // Should: trim -> uppercase -> replace spaces
    $this->assertEquals('JOHN_DOE', $result);
  }

  /**
   * It applies global sanitizers even when field has no specific sanitizer.
   * @test
   */
  public function it_applies_global_sanitizers_even_without_field_specific()
  {
    $sanitizer = new Sanitizer([
      TrimSanitizer::class,    // Global
      'email' => 'lowercase'
    ]);

    $result = $sanitizer->handle('username', '  admin  ');

    // Should apply trim even though 'username' has no specific sanitizer
    $this->assertEquals('admin', $result);
  }

  /**
   * It works with only global sanitizers.
   * @test
   */
  public function it_works_with_only_global_sanitizers()
  {
    $sanitizer = new Sanitizer([
      TrimSanitizer::class,
      fn($v) => strtolower($v)
    ]);

    $result = $sanitizer->handle('any_field', '  HELLO  ');

    $this->assertEquals('hello', $result);
  }

  /**
   * It returns value unchanged when no sanitizers match.
   * @test
   */
  public function it_returns_value_unchanged_when_no_sanitizers_match()
  {
    $sanitizer = new Sanitizer([
      'email' => 'lowercase'
    ]);

    $result = $sanitizer->handle('name', 'John Doe');

    $this->assertEquals('John Doe', $result);
  }
}
