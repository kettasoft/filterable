<?php

namespace Kettasoft\Filterable\Exceptions;

use Kettasoft\Filterable\Engines\Foundation\Operators\Contracts\Operator;

class InvalidOperatorDefinitionException extends StrictnessException
{
  public function __construct(string $operator, mixed $definition)
  {
    $type = is_string($definition)
      ? $definition
      : (is_object($definition) ? $definition::class : get_debug_type($definition));

    parent::__construct(
      "Operator strategy [{$operator}] must be a class implementing ".Operator::class."; {$type} given."
    );
  }
}
