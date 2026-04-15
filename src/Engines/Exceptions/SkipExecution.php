<?php

namespace Kettasoft\Filterable\Engines\Exceptions;

use Exception;
use Kettasoft\Filterable\Support\Payload;

class SkipExecution extends Exception
{
    /**
     * SkipExecution constructor.
     * @param string $message
     * @param Payload|null $payload
     */
    public function __construct(string $message, protected ?Payload $payload = null)
    {
        parent::__construct($message);
    }

    /**
     * Get the associated Payload that was skipped.
     * @return Payload|null
     */
    public function getPayload(): ?Payload
    {
        return $this->payload;
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
