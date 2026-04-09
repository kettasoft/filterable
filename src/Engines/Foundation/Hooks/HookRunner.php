<?php

namespace Kettasoft\Filterable\Engines\Foundation\Hooks;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Engines\Foundation\Hooks\Contracts\HookRunnerInterface;

/**
 * Central hook dispatcher for the Invokable engine.
 *
 * Fires per-field hooks around each filter method invocation:
 *
 *   onEmpty{Field}(payload)     ← only when value is null/empty
 *   before{Field}(payload)      ← returns false → skip method + after
 *   → applyFilterMethod() / onSkip{Field}
 *   after{Field}(payload)
 *
 * Global before/after filtering is handled by initially() / finally()
 * on the Filterable base class and is NOT part of this runner.
 */
final class HookRunner implements HookRunnerInterface
{
  /**
   * Hook resolver instance.
   * @var HookResolver
   */
  private readonly HookResolver $resolver;

  public function __construct(
    private readonly HookConfig $config,
    private readonly Filterable $context,
  ) {
    $this->resolver = new HookResolver($config);
  }

    // -----------------------------------------------------------------------
    //  Field-level hooks
    // -----------------------------------------------------------------------

  /**
   * {@inheritDoc}
   */
  public function fireBefore(string $field, Payload $payload): bool
  {
    if (! $this->resolver->isTypeEnabled('before')) {
      return true;
    }

    $method = $this->resolver->resolve('before', $field);

    return $this->dispatch($method, [$payload]);
  }

  /**
   * {@inheritDoc}
   */
  public function fireAfter(string $field, Payload $payload): void
  {
    if (! $this->resolver->isTypeEnabled('after')) {
      return;
    }

    $method = $this->resolver->resolve('after', $field);

    $this->dispatchVoid($method, [$payload]);
  }

  /**
   * {@inheritDoc}
   */
  public function fireSkip(string $field, Payload $payload): void
  {
    if (! $this->resolver->isTypeEnabled('skip')) {
      return;
    }

    $method = $this->resolver->resolve('skip', $field);

    $this->dispatchVoid($method, [$payload]);
  }

  /**
   * {@inheritDoc}
   */
  public function fireEmpty(string $field, Payload $payload): void
  {
    if (! $this->resolver->isTypeEnabled('empty')) {
      return;
    }

    $method = $this->resolver->resolve('empty', $field);

    $this->dispatchVoid($method, [$payload]);
  }

    // -----------------------------------------------------------------------
    //  Internals
    // -----------------------------------------------------------------------

  /**
   * Dispatch a hook method that may return a boolean halting signal.
   * Returns true (continue) unless the method exists, returns exactly false,
   * AND haltOnFalse is enabled in the config.
   * @param string $method
   * @param array $args
   * @return bool
   */
  private function dispatch(string $method, array $args): bool
  {
    if (! method_exists($this->context, $method)) {
      return true;
    }

    $result = $this->context->{$method}(...$args);

    if ($this->config->haltOnFalse && $result === false) {
      return false;
    }

    return true;
  }

  /**
   * Dispatch a void hook method (return value is ignored).
   * @param string $method
   * @param array $args
   * @return void
   */
  private function dispatchVoid(string $method, array $args): void
  {
    if (! method_exists($this->context, $method)) {
      return;
    }

    $this->context->{$method}(...$args);
  }
}
