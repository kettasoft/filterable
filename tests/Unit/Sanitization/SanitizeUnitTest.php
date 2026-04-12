<?php

namespace Kettasoft\Filterable\Tests\Unit\Sanitization;

use Kettasoft\Filterable\Sanitization\Defaults\TrimSanitizer;
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
}
