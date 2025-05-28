<?php

namespace Kettasoft\Filterable\Exceptions;

class MissingBuilderException extends \RuntimeException
{
  /**
   * MissingBuilderException constructor
   */
  public function __construct()
  {
    parent::__construct("Filterable requires a valid Query Builder instance before applying filters");
  }
}
