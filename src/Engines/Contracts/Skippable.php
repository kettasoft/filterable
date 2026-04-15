<?php

namespace Kettasoft\Filterable\Engines\Contracts;

use Kettasoft\Filterable\Engines\Exceptions\SkipExecution;
use Kettasoft\Filterable\Support\Payload;

interface Skippable
{
    /**
     * Skip the current execution with a message and optional payload.
     * 
     * @param string $message The reason for skipping
     * @param Payload|null $payload The payload being skipped
     * @throws SkipExecution
     * @return never
     */
    public function skip(string $message, mixed $payload = null): never;
}
