<?php

namespace Kettasoft\Filterable\Sanitization\Contracts;

interface SanitizeHandler
{
  /**
   * SanitizeHandler constructor.
   * @param \Kettasoft\Filterable\Sanitization\Contracts\Sanitizable|\Closure|string|array $sanitizer
   */
  public function __construct(Sanitizable|\Closure|string|array $sanitizer);

  /**
   * Handle incomming sanitizer.
   * @param mixed $value
   * @return mixed
   */
  public function handle(mixed $value): mixed;
}
