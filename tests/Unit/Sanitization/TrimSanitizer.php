<?php

namespace Kettasoft\Filterable\Tests\Unit\Sanitization;

use Kettasoft\Filterable\Sanitization\Contracts\Sanitizable;

class TrimSanitizer implements Sanitizable
{
  public function sanitize($value): mixed
  {
    return trim($value);
  }
}
