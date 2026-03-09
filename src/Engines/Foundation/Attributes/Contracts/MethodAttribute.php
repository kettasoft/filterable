<?php

namespace Kettasoft\Filterable\Engines\Foundation\Attributes\Contracts;

use Kettasoft\Filterable\Engines\Foundation\Attributes\AttributeContext;

/**
 * Base class for method attributes.
 */
interface MethodAttribute
{
  /**
   * Get the stage at which this attribute should be applied.
   * @return int The stage value.
   */
  public static function stage(): int;

  /**
   * Handle attribute behavior.
   * @param AttributeContext $context The context of the attribute application.
   */
  public function handle(AttributeContext $context): void;
}
