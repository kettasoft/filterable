<?php

namespace Kettasoft\Filterable\Sanitization\Contracts;

interface HasSanitize
{
  public function sanitize($value): mixed;
}
