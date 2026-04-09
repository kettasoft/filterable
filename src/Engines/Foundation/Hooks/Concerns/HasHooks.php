<?php

namespace Kettasoft\Filterable\Engines\Foundation\Hooks\Concerns;

use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Engines\Foundation\Hooks\HookConfig;
use Kettasoft\Filterable\Engines\Foundation\Hooks\HookRunner;

/**
 * Provides hook runner integration for the Invokable engine.
 *
 * Usage: add `use HasHooks;` inside the Invokable class.
 *
 * Covers per-field hooks only:
 *   before{Field} / after{Field} / onSkip{Field} / onEmpty{Field}
 *
 * Global before/after filtering is handled by initially() / finally()
 * on the Filterable base class and does NOT go through this trait.
 */
trait HasHooks
{
    private ?HookRunner $hookRunner = null;

    // -----------------------------------------------------------------------
    //  Hook runner access
    // -----------------------------------------------------------------------

    /**
     * Return (or lazily create) the HookRunner instance.
     */
    protected function hooks(): HookRunner
    {
        if ($this->hookRunner === null) {
            $this->hookRunner = new HookRunner(
                HookConfig::fromLaravelConfig(),
                $this->context,
            );
        }

        return $this->hookRunner;
    }

    /**
     * Whether the hooks system is globally enabled.
     */
    protected function hooksEnabled(): bool
    {
        return (bool) config('filterable.engines.invokable.hooks.enabled', true);
    }

    // -----------------------------------------------------------------------
    //  Convenience wrappers called from Invokable::execute()
    // -----------------------------------------------------------------------

    /**
     * Fire before{Field}(Payload) — field-level before hook.
     * Returns false if execution should be halted for this field.
     */
    protected function runBefore(string $field, Payload $payload): bool
    {
        if (! $this->hooksEnabled()) {
            return true;
        }

        return $this->hooks()->fireBefore($field, $payload);
    }

    /**
     * Fire after{Field}(Payload) — field-level after hook.
     */
    protected function runAfter(string $field, Payload $payload): void
    {
        if (! $this->hooksEnabled()) {
            return;
        }

        $this->hooks()->fireAfter($field, $payload);
    }

    /**
     * Fire onSkip{Field}(Payload) — when method is not defined.
     */
    protected function runSkip(string $field, Payload $payload): void
    {
        if (! $this->hooksEnabled()) {
            return;
        }

        $this->hooks()->fireSkip($field, $payload);
    }

    /**
     * Fire onEmpty{Field}(Payload) — when value is null or empty string.
     */
    protected function runEmptyHook(string $field, Payload $payload): void
    {
        if (! $this->hooksEnabled()) {
            return;
        }

        $this->hooks()->fireEmpty($field, $payload);
    }
}
