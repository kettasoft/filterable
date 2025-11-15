<?php

namespace Kettasoft\Filterable\Engines\Exceptions;

use Exception;
use Kettasoft\Filterable\Engines\Foundation\Clause;

class SkipExecution extends Exception
{
    /**
     * SkipExecution constructor.
     * @param string $message
     * @param mixed $clause
     */
    public function __construct(string $message, protected mixed $clause = null)
    {
        parent::__construct($message);
    }

    /**
     * Get the associated Clause.
     * @return ?Clause
     */
    public function getClause(): ?Clause
    {
        return $this->clause;
    }

    /**
     * Determine if this exception should be reported.
     * @return bool
     */
    public function shouldReport(): bool
    {
        return false;
    }
}
