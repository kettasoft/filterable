<?php

namespace Kettasoft\Filterable\Exceptions;

use InvalidArgumentException;
use Kettasoft\Filterable\Exceptions\Contracts\DriverException;

class InvalidDriverDefinitionException extends InvalidArgumentException implements DriverException
{
    /**
     * Create an exception for a driver definition that violates the contract.
     *
     * @param string $driver Alias or class name requested by the caller.
     * @param mixed $definition Invalid configured or resolved definition.
     */
    public function __construct(string $driver, mixed $definition = null)
    {
        $description = is_string($definition)
            ? $definition
            : (is_object($definition) ? $definition::class : get_debug_type($definition));

        parent::__construct("Filterable driver [{$driver}] has an invalid definition [{$description}].");
    }
}
