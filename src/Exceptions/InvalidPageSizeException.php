<?php

namespace Kettasoft\Filterable\Exceptions;

use InvalidArgumentException;

/**
 * Thrown when a requested pagination size cannot be accepted by the policy.
 */
class InvalidPageSizeException extends InvalidArgumentException
{
  /**
   * Create an exception for a malformed or non-positive page size.
   *
   * @param mixed $value The rejected page-size value.
   */
  public static function invalid(mixed $value): self
  {
    return new self(sprintf(
      'Pagination page size must be a positive integer; [%s] was given.',
      is_scalar($value) || $value === null ? var_export($value, true) : get_debug_type($value)
    ));
  }

  /**
   * Create an exception for a page size that exceeds the configured maximum.
   *
   * @param int $requested The requested page size.
   * @param int $maximum The largest accepted page size.
   */
  public static function exceedsMaximum(int $requested, int $maximum): self
  {
    return new self(sprintf(
      'Pagination page size [%d] exceeds the configured maximum of [%d].',
      $requested,
      $maximum
    ));
  }
}
