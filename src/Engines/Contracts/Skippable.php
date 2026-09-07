<?php

namespace Kettasoft\Filterable\Engines\Contracts;

use Kettasoft\Filterable\Engines\Exceptions\SkipExecution;
use Kettasoft\Filterable\Support\Payload;

interface Skippable
{
    /**
     * Skip the current payload execution.
     *
     * @param Payload $payload The payload being skipped
     * @param string|null $message The reason for skipping
     * @throws SkipExecution
     * @return never
     */
    public function skip(Payload $payload, ?string $message = null): never;
}
