<?php

namespace Kettasoft\Filterable\Engines\Contracts;

use Kettasoft\Filterable\Engines\Exceptions\SkipExecution;
use Kettasoft\Filterable\Support\Payload;

interface Skippable
{
    /**
     * Skip the current execution with a message and optional payload.
     * @param string $message
     * @param Payload|null $payload
     * @throws SkipExecution
     * @return never
     */
    public function skip(string $message, ?Payload $payload = null): never;
}
