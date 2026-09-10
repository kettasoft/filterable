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
     *
     * Each driver is responsible for validating that the supplied query is a
     * supported target and for translating the operation into backend calls.
     *
     * @param Operation $operation Backend-independent instruction to apply.
     * @param object $query Backend query object that receives the instruction.
     * @return object The original or replacement backend query object.
     */
    public function apply(Operation $operation, object $query): object;
}
