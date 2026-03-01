<?php

namespace Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations;

use Attribute;
use Kettasoft\Filterable\Exceptions\StrictnessException;

#[Attribute(Attribute::TARGET_METHOD)]
class Cast implements \Kettasoft\Filterable\Engines\Foundation\Attributes\Contracts\MethodAttribute
{
  /**
   * Constructor for Cast attribute.
   * @param string $type The type to which the parameter should be cast.
   */
  public function __construct(public string $type) {}

  /**
   * Get the stage at which this attribute should be applied.
   *
   * @return int
   */
  public static function stage(): int
  {
    return \Kettasoft\Filterable\Engines\Foundation\Attributes\Enums\Stage::TRANSFORM->value;
  }

  /**
   * Handle the attribute logic.
   *
   * @param \Kettasoft\Filterable\Engines\Foundation\Attributes\AttributeContext $context
   * @return void
   * @throws StrictnessException if the parameter is missing or empty.
   */
  public function handle(\Kettasoft\Filterable\Engines\Foundation\Attributes\AttributeContext $context): void
  {
    /** @var \Kettasoft\Filterable\Support\Payload $payload */
    $payload = $context->payload;

    try {
      $payload->cast($this->type);
    } catch (\Exception $e) {
      throw new StrictnessException($e->getMessage());
    }
  }
}
