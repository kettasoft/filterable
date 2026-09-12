<?php

namespace Kettasoft\Filterable\Foundation\Pagination;

use Kettasoft\Filterable\Exceptions\InvalidPageSizeException;

/**
 * Resolves requested page sizes against a validated pagination policy.
 */
final class PaginationPolicy
{
  /**
   * Clamp oversized values to the configured maximum.
   * @var string
   */
  public const OVERFLOW_CLAMP = 'clamp';

  /**
   * Reject oversized values with an exception.
   * @var string
   */
  public const OVERFLOW_REJECT = 'reject';

  /**
   * Create a pagination policy.
   *
   * @param string $parameter Query-string parameter used for the requested page size.
   * @param int $defaultPerPage Page size used when no explicit or request value is provided.
   * @param int $maxPerPage Largest page size accepted by the policy.
   * @param string $overflow Whether oversized values should be clamped or rejected.
   */
  public function __construct(
    protected string $parameter,
    protected int $defaultPerPage,
    protected int $maxPerPage,
    protected string $overflow
  ) {
    $this->validate();
  }

  /**
   * Build a policy from normalized configuration values.
   *
   * @param array{parameter: string, default: int, max: int, overflow: string} $config
   */
  public static function fromArray(array $config): self
  {
    return new self(
      $config['parameter'],
      $config['default'],
      $config['max'],
      $config['overflow']
    );
  }

  /**
   * Resolve an explicit or request-provided page size.
   *
   * Explicit method arguments take precedence over request input. Both remain
   * subject to the configured maximum.
   *
   * @param mixed $explicit Explicit value passed to a pagination method.
   * @param mixed $fromRequest Value read from the configured query parameter.
   */
  public function resolve(mixed $explicit = null, mixed $fromRequest = null): int
  {
    $requested = $explicit ?? $fromRequest ?? $this->defaultPerPage;
    $requested = $this->normalize($requested);

    if ($requested <= $this->maxPerPage) {
      return $requested;
    }

    if ($this->overflow === self::OVERFLOW_REJECT) {
      throw InvalidPageSizeException::exceedsMaximum($requested, $this->maxPerPage);
    }

    return $this->maxPerPage;
  }

  /**
   * Get the query-string parameter used for page-size input.
   */
  public function parameter(): string
  {
    return $this->parameter;
  }

  /**
   * Export the policy as serializable configuration.
   *
   * @return array{parameter: string, default: int, max: int, overflow: string}
   */
  public function toArray(): array
  {
    return [
      'parameter' => $this->parameter,
      'default' => $this->defaultPerPage,
      'max' => $this->maxPerPage,
      'overflow' => $this->overflow,
    ];
  }

  /**
   * Validate configuration before the policy reaches query execution.
   */
  private function validate(): void
  {
    if (trim($this->parameter) === '') {
      throw new \InvalidArgumentException('Pagination parameter must be a non-empty string.');
    }

    if ($this->defaultPerPage < 1) {
      throw new \InvalidArgumentException('Pagination default must be a positive integer.');
    }

    if ($this->maxPerPage < 1) {
      throw new \InvalidArgumentException('Pagination maximum must be a positive integer.');
    }

    if (! in_array($this->overflow, [self::OVERFLOW_CLAMP, self::OVERFLOW_REJECT], true)) {
      throw new \InvalidArgumentException('Pagination overflow must be either [clamp] or [reject].');
    }

    $this->defaultPerPage = min($this->defaultPerPage, $this->maxPerPage);
  }

  /**
   * Normalize a page-size value into a positive integer.
   *
   * @param mixed $value The value supplied by application code or request input.
   */
  private function normalize(mixed $value): int
  {
    if (is_int($value) && $value > 0) {
      return $value;
    }

    if (is_string($value) && preg_match('/^[1-9][0-9]*$/', $value) === 1) {
      return (int) $value;
    }

    throw InvalidPageSizeException::invalid($value);
  }
}
