<?php

namespace Kettasoft\Filterable\Exceptions;

use LogicException;
use Kettasoft\Filterable\Operations\Contracts\Operation;

class UnsupportedOperationException extends LogicException
{
    public function __construct(string $driver, Operation $operation)
    {
        $operationClass = $operation::class;

        parent::__construct(
            "Filterable driver [{$driver}] does not support operation [{$operationClass}]."
        );
    }
}
