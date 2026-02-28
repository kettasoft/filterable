<?php

namespace Kettasoft\Filterable\Engines\Foundation;

use Closure;
use Kettasoft\Filterable\Engines\Foundation\Contracts\Outcome;

class Execution implements Contracts\Outcome
{
  /**
   * The error that caused the outcome to be rejected.
   * @var \Throwable|null
   */
  private \Throwable|null $error = null;

  /**
   * Create a new Execution instance.
   * @param \Throwable|null $error The error that caused the outcome to be rejected, or null if the outcome is resolved.
   */
  public function then(Closure $closure): Outcome
  {
    if (! $this->isRejected()) {
      $closure();
    }

    return $this;
  }

  /**
   * Register a callback to be executed when the outcome is rejected.
   * @param \Closure $closure The callback to execute, which receives the reason for rejection as an argument.
   * @return self
   */
  public function catch(Closure $closure): Outcome
  {
    if ($this->isRejected()) {
      $closure($this->error);
    }

    return $this;
  }

  /**
   * Register a callback to be executed when the outcome is settled (either resolved or rejected).
   * @param \Closure $closure The callback to execute, which receives no arguments.
   * @return self
   */
  public function finally(Closure $closure): Outcome
  {
    $closure();
    return $this;
  }

  /**
   * Mark the outcome as failed with the given error.
   * @param \Throwable $error The error that caused the outcome to be rejected.
   * @return self
   */
  public function fail(\Throwable $error): Outcome
  {
    $this->error = $error;
    return $this;
  }

  /**
   * Check if the outcome is resolved.
   * @return bool True if the outcome is resolved, false otherwise.
   */
  public function isResolved(): bool
  {
    return $this->error === null;
  }

  /**
   * Check if the outcome is rejected.
   * @return bool True if the outcome is rejected, false otherwise.
   */
  public function isRejected(): bool
  {
    return !$this->isResolved();
  }
}
