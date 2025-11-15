<?php

namespace Kettasoft\Filterable\Engines\Exceptions;

class InvalidDataFormatException extends SkipExecution
{
  public function __construct()
  {
    parent::__construct("The provided data is either incommpatible or incorrectly formatted.");
  }
}
