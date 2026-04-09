<?php

namespace Kettasoft\Filterable\Engines\Foundation\Hooks\Contracts;

use Kettasoft\Filterable\Support\Payload;

/**
 * Contract for the hook runner that dispatches per-field lifecycle hooks
 * around filter method invocations in the Invokable engine.
 *
 * Note: global before/after filtering is handled by initially() / finally()
 * on the Filterable base class and is NOT part of this interface.
 */
interface HookRunnerInterface
{
    /**
     * Fire the field-level before hook: before{Field}(Payload).
     * Returns false if the hook halted execution (haltOnFalse = true).
     */
    public function fireBefore(string $field, Payload $payload): bool;

    /**
     * Fire the field-level after hook: after{Field}(Payload).
     */
    public function fireAfter(string $field, Payload $payload): void;

    /**
     * Fire the skip hook when the filter method is not defined: onSkip{Field}(Payload).
     */
    public function fireSkip(string $field, Payload $payload): void;

    /**
     * Fire the empty hook when the filter value is null/empty: onEmpty{Field}(Payload).
     */
    public function fireEmpty(string $field, Payload $payload): void;
}
