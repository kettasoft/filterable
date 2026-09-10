<?php

namespace Kettasoft\Filterable\Exceptions;

use LogicException;
use Kettasoft\Filterable\Operations\Contracts\Operation;

class UnsupportedOperationException extends LogicException
{
    /**
     * Create an exception for an operation a driver cannot translate.
     *
     * @param string $driver Driver that rejected the operation.
     * @param Operation $operation Unsupported backend-independent instruction.
     */
    public function __construct(string $driver, Operation $operation)
    {
        $operationClass = $operation::class;

        parent::__construct(
            "Filterable driver [{$driver}] does not support operation [{$operationClass}]."
        );
    }
}
