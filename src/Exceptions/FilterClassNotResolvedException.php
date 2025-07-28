<?php

namespace Kettasoft\Filterable\Exceptions;

class FilterClassNotResolvedException extends \InvalidArgumentException
{
  public function __construct(string $model)
  {
    parent::__construct("Could not resolve a filter class for model [{$model}]. Please either define a \$filterable property to the model or pass the filter class manually to the filter() method.");
  }
}
