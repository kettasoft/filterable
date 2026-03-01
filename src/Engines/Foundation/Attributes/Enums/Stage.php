<?php

namespace Kettasoft\Filterable\Engines\Foundation\Attributes\Enums;

/**
 * The stage at which the filter is applied.
 */
enum Stage: int
{
  /**
   * Stop / allow execution.
   */
  case CONTROL = 1;

  /**
   * Modify payload.
   */
  case TRANSFORM = 2;

  /**
   * Assert correctness.
   */
  case VALIDATE = 3;

  /**
   * Affect query behavior.
   */
  case BEHAVIOR = 4;
}
