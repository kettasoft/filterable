<?php

namespace Kettasoft\Filterable\Contracts;

use Kettasoft\Filterable\Engines\Foundation\Clause;

/**
 * Contract for commitable filters.
 */
interface Commitable
{
    /**
     * Commit applied clauses.
     *
     * @param string $key
     * @param Clause $clause
     *
     * @return bool
     */
    public function commit(string $key, Clause $clause): bool;
}
