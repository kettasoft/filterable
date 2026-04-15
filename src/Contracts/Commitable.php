<?php

namespace Kettasoft\Filterable\Contracts;

use Kettasoft\Filterable\Support\Payload;

/**
 * Contract for commitable filters.
 * 
 * @package Kettasoft\Filterable\Contracts
 */
interface Commitable
{
    /**
     * Commit applied payload.
     * @param string $key
     * @param Payload $payload
     * @return bool
     */
    public function commit(string $key, Payload $payload): bool;
}
