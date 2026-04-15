<?php

namespace Kettasoft\Filterable\Engines\Foundation\Contracts;

use Closure;

interface Outcome
{
    /**
     * Register a callback to be executed when the outcome is resolved.
     *
     * @param \Closure $closure The callback to execute, which receives the outcome's value as an argument.
     *
     * @return self
     */
    public function then(Closure $closure): self;

    /**
     * Register a callback to be executed when the outcome is rejected.
     *
     * @param \Closure $closure The callback to execute, which receives the reason for rejection as an argument.
     *
     * @return self
     */
    public function catch(Closure $closure): self;

    /**
     * Register a callback to be executed when the outcome is settled (either resolved or rejected).
     *
     * @param \Closure $closure The callback to execute, which receives no arguments.
     *
     * @return self
     */
    public function finally(Closure $closure): self;

    /**
     * Mark the outcome as failed with the given error.
     *
     * @param \Throwable $error The error that caused the outcome to be rejected.
     *
     * @return self
     */
    public function fail(\Throwable $error): self;

    /**
     * Check if the outcome is resolved.
     *
     * @return bool True if the outcome is resolved, false otherwise.
     */
    public function isResolved(): bool;

    /**
     * Check if the outcome is rejected.
     *
     * @return bool True if the outcome is rejected, false otherwise.
     */
    public function isRejected(): bool;
}
