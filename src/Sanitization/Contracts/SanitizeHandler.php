<?php

namespace Kettasoft\Filterable\Sanitization\Contracts;

interface SanitizeHandler
{
  /**
   * SanitizeHandler constructor.
   * @param \Kettasoft\Filterable\Sanitization\Contracts\HasSanitize|\Closure|string|array $sanitizer
   */
  public function __construct(HasSanitize|\Closure|string|array $sanitizer);

  /**
   * Handle incomming sanitizer.
   * @param mixed $value
   * @return mixed
   */
  public function handle(mixed $value): mixed;
}
