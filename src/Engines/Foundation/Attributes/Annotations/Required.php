<?php

namespace Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations;

use Attribute;
use Kettasoft\Filterable\Exceptions\StrictnessException;

#[Attribute(Attribute::TARGET_METHOD)]
class Required implements \Kettasoft\Filterable\Engines\Foundation\Attributes\Contracts\MethodAttribute
{
  /**
   * The error message template. %s will be replaced with the parameter name.
   * @var string
   */
  public string $message = "The parameter '%s' is required.";

  /**
   * Get the stage at which this attribute should be applied.
   *
   * @return int
   */
  public static function stage(): int
  {
    return \Kettasoft\Filterable\Engines\Foundation\Attributes\Enums\Stage::VALIDATE->value;
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

    if ($payload && ($payload->isEmpty() || $payload->isNull())) {
      throw new StrictnessException(sprintf($this->message, $payload->field));
    }
  }
}
