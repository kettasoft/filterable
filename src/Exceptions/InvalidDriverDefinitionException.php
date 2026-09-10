<?php

namespace Kettasoft\Filterable\Exceptions;

use InvalidArgumentException;

class InvalidDriverDefinitionException extends InvalidArgumentException
{
    public function __construct(string $driver, mixed $definition = null)
    {
        $description = is_string($definition)
            ? $definition
            : (is_object($definition) ? $definition::class : get_debug_type($definition));

        parent::__construct("Filterable driver [{$driver}] has an invalid definition [{$description}].");
    }
}
