<?php

namespace Kettasoft\Filterable\Tests\Unit\Sanitization;

use Kettasoft\Filterable\Sanitization\Contracts\HasSanitize;

class TrimSanitizer implements HasSanitize
{
  public function sanitize($value): mixed
  {
    return trim($value);
  }
}
