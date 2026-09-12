<?php

namespace Kettasoft\Filterable\Tests\Unit\Foundation;

use InvalidArgumentException;
use Kettasoft\Filterable\Exceptions\InvalidPageSizeException;
use Kettasoft\Filterable\Foundation\Pagination\PaginationPolicy;
use PHPUnit\Framework\TestCase;

class PaginationPolicyTest extends TestCase
{
  public function test_it_resolves_explicit_request_and_default_values_in_priority_order(): void
  {
    $policy = $this->policy();

    $this->assertSame(25, $policy->resolve(25, '40'));
    $this->assertSame(40, $policy->resolve(null, '40'));
    $this->assertSame(15, $policy->resolve());
  }

  public function test_it_clamps_values_that_exceed_the_maximum(): void
  {
    $policy = $this->policy();

    $this->assertSame(100, $policy->resolve(500));
    $this->assertSame(100, $policy->resolve(null, '500'));
  }

  public function test_the_maximum_also_caps_a_larger_configured_default(): void
  {
    $policy = $this->policy(['default' => 200]);

    $this->assertSame(100, $policy->resolve());
  }

  public function test_it_can_reject_values_that_exceed_the_maximum(): void
  {
    $policy = $this->policy(['overflow' => PaginationPolicy::OVERFLOW_REJECT]);

    $this->expectException(InvalidPageSizeException::class);
    $this->expectExceptionMessage('exceeds the configured maximum of [100]');

    $policy->resolve(null, '101');
  }

  /**
   * @dataProvider invalidPageSizes
   */
  public function test_it_rejects_malformed_and_non_positive_page_sizes(mixed $value): void
  {
    $this->expectException(InvalidPageSizeException::class);

    $this->policy()->resolve($value);
  }

  public static function invalidPageSizes(): array
  {
    return [
      'zero' => [0],
      'negative' => [-1],
      'decimal' => [1.5],
      'numeric decimal string' => ['1.5'],
      'non numeric string' => ['many'],
      'array' => [[10]],
    ];
  }

  /**
   * @dataProvider invalidConfigurations
   */
  public function test_it_validates_policy_configuration(array $config): void
  {
    $this->expectException(InvalidArgumentException::class);

    PaginationPolicy::fromArray($config);
  }

  public static function invalidConfigurations(): array
  {
    return [
      'empty parameter' => [[
        'parameter' => '', 'default' => 15, 'max' => 100, 'overflow' => 'clamp',
      ]],
      'invalid default' => [[
        'parameter' => 'per_page', 'default' => 0, 'max' => 100, 'overflow' => 'clamp',
      ]],
      'invalid maximum' => [[
        'parameter' => 'per_page', 'default' => 15, 'max' => 0, 'overflow' => 'clamp',
      ]],
      'invalid overflow' => [[
        'parameter' => 'per_page', 'default' => 15, 'max' => 100, 'overflow' => 'ignore',
      ]],
    ];
  }

  private function policy(array $overrides = []): PaginationPolicy
  {
    return PaginationPolicy::fromArray(array_replace([
      'parameter' => 'per_page',
      'default' => 15,
      'max' => 100,
      'overflow' => PaginationPolicy::OVERFLOW_CLAMP,
    ], $overrides));
  }
}
