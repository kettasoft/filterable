<?php

namespace Kettasoft\Filterable\Sanitization;

use Illuminate\Support\Traits\ForwardsCalls;
use Kettasoft\Filterable\Sanitization\HandlerFactory;

class Sanitizer
{
  use ForwardsCalls;

  /**
   * Registered sanitizers to operate upon.
   * @var array
   */
  protected array $sanitizers = [];

  /**
   * Create new Sanitizer instance.
   * @param array $sanitizers
   */
  public function __construct(array $sanitizers)
  {
    $this->sanitizers = $sanitizers;
  }

  /**
   * Handle sanitizers.
   * @param string $field
   * @param mixed $value
   */
  public function handle(string $field, mixed $value)
  {
    if (empty($field) || !array_key_exists($field, $this->sanitizers)) {
      return $value;
    }

    foreach ($this->sanitizers as $key => $resolver) {
      if ($key === $field) {
        $value = HandlerFactory::handle($value, $resolver);
      }
    }

    return $value;
  }

  /**
   * Get registered sanitizers.
   * @return array
   */
  public function getSanitizers(): array
  {
    return $this->sanitizers;
  }

  /**
   * Set sanitizer classes
   * @param array $sanitizers
   * @param bool $override Override current sanitizers when (true)
   * @return static
   */
  public function setSanitizers(array $sanitizers, bool $override = true): static
  {
    $this->sanitizers = $override ? $sanitizers : array_merge($this->sanitizers, $sanitizers);
    return $this;
  }
}
