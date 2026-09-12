<?php

namespace Kettasoft\Filterable\Foundation\Pagination;

use Closure;

/**
 * Adapts Laravel pagination arguments to an effective pagination policy.
 */
final class PaginationArguments
{
  /**
   * Determine whether the builder method is governed by pagination policy.
   */
  public static function supports(string $method): bool
  {
    return in_array($method, ['paginate', 'simplePaginate', 'cursorPaginate'], true);
  }

  /**
   * Resolve the per-page argument while preserving Laravel's call signature.
   *
   * @param string $method Forwarded builder method.
   * @param array $arguments Arguments supplied to the builder method.
   * @param PaginationPolicy $policy Effective pagination policy.
   * @param mixed $requestedPerPage Value read from the configured query parameter.
   * @return array Arguments with a policy-compliant per-page value.
   */
  public static function resolve(
    string $method,
    array $arguments,
    PaginationPolicy $policy,
    mixed $requestedPerPage = null
  ): array {
    if (! self::supports($method)) {
      return $arguments;
    }

    $usesNamedArguments = array_key_exists('perPage', $arguments)
      || ($arguments !== [] && is_string(array_key_first($arguments)));
    $explicit = $arguments['perPage'] ?? $arguments[0] ?? null;

    if ($method === 'paginate' && $explicit instanceof Closure) {
      $callback = $explicit;
      $resolved = static function (mixed ...$parameters) use (
        $callback,
        $policy,
        $requestedPerPage
      ): int {
        return $policy->resolve($callback(...$parameters), $requestedPerPage);
      };
    } else {
      $resolved = $policy->resolve($explicit, $requestedPerPage);
    }

    if ($usesNamedArguments) {
      return ['perPage' => $resolved] + $arguments;
    }

    $arguments[0] = $resolved;
    ksort($arguments);

    return array_values($arguments);
  }
}
