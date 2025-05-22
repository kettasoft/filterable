<?php

namespace Kettasoft\Filterable\Exceptions;

class InvalidDataFormatException extends \ErrorException
{
  public function __construct()
  {
    parent::__construct("The provided data is either incommpatible or incorrectly formatted.");
  }
}
