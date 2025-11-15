<?php

namespace Kettasoft\Filterable\Engines\Exceptions;

use Kettasoft\Filterable\Exceptions\StrictnessException;

class InvalidDataFormatException extends StrictnessException
{
  public function __construct()
  {
    parent::__construct("The provided data is either incommpatible or incorrectly formatted.");
  }
}
