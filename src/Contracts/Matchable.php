<?php

namespace Kettasoft\Filterable\Contracts;

interface Matchable
{
    /**
     * Determine if the object matches the given condition or value.
     *
     * @param mixed $value
     * @return bool
     */
    public function is($value): bool;
}
