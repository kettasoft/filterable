<?php

namespace Kettasoft\Filterable\Drivers\Contracts;

use Kettasoft\Filterable\Operations\Contracts\Operation;

/**
 * Translates backend-independent operations for a backend query object.
 */
interface Driver
{
    /**
     * Apply an operation and return the resulting backend query object.
     */
    public function apply(Operation $operation, object $query): object;
}
