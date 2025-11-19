<?php

namespace Kettasoft\Filterable\Engines\Contracts;

use Kettasoft\Filterable\Engines\Exceptions\SkipExecution;

interface Skippable
{
    /**
     * Skip the current execution with a message and optional clause.
     * @param string $message
     * @param mixed $clause
     * @throws SkipExecution
     * @return never
     */
    public function skip(string $message, mixed $clause = null): never;
}
