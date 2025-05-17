<?php

namespace Kettasoft\Filterable\Pipes;

use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;
use Kettasoft\Filterable\Contracts\Authorizable;

class FilterAuthorizationPipe
{
  /**
   * Handle incomming pipe.
   * @param \Kettasoft\Filterable\Contracts\Authorizable $filter
   * @param mixed $next
   */
  public function handle(Authorizable $filter, $next)
  {
    if (!$filter->authorize()) {
      throw new UnauthorizedException("You are not authorized to make this filter", Response::HTTP_UNAUTHORIZED);
    }

    return $next($filter);
  }
}
