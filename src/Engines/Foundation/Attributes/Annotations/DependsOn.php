<?php

namespace Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class DependsOn implements \Kettasoft\Filterable\Engines\Foundation\Attributes\Contracts\MethodAttribute
{
  /**
   * Constructor for DependsOn attribute.
   * @param string $dependsOn The name of the filter method that this filter depends on.
   */
  public function __construct(public string|array $dependsOn) {}

  /**
   * Get the stage at which this attribute should be applied.
   *
   * @return int
   */
  public static function stage(): int
  {
    return \Kettasoft\Filterable\Engines\Foundation\Attributes\Enums\Stage::CONTROL->value;
  }

  /**
   * Handle the attribute logic.
   *
   * @param \Kettasoft\Filterable\Engines\Foundation\Attributes\AttributeContext $context
   * @return void
   */
  public function handle(\Kettasoft\Filterable\Engines\Foundation\Attributes\AttributeContext $context): void
  {
    $payload = $context->payload;
  }
}
