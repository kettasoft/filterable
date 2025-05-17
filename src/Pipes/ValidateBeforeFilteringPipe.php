<?php

namespace Kettasoft\Filterable\Pipes;

use Kettasoft\Filterable\Contracts\Validatable;

class ValidateBeforeFilteringPipe
{
  /**
   * Handle incomming pipe.
   * @param \Kettasoft\Filterable\Contracts\Validatable $context
   * @param mixed $next
   */
  public function handle(Validatable $context, $next)
  {
    $context->validate();

    return $next($context);
  }
}
