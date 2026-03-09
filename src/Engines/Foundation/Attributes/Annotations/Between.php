<?php

namespace Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations;

use Attribute;
use Kettasoft\Filterable\Engines\Exceptions\SkipExecution;

#[Attribute(Attribute::TARGET_METHOD)]
class Between implements \Kettasoft\Filterable\Engines\Foundation\Attributes\Contracts\MethodAttribute
{
  /**
   * Constructor for Between attribute.
   * 
   * @param float|int $min The minimum allowed value.
   * @param float|int $max The maximum allowed value.
   */
  public function __construct(
    public float|int $min,
    public float|int $max,
  ) {}

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
   */
  public function handle(\Kettasoft\Filterable\Engines\Foundation\Attributes\AttributeContext $context): void
  {
    /** @var \Kettasoft\Filterable\Support\Payload $payload */
    $payload = $context->payload;

    if (! is_numeric($payload->value)) {
      throw new SkipExecution(
        "The value '{$payload->value}' is not numeric. Expected a value between {$this->min} and {$this->max}."
      );
    }

    $value = (float) $payload->value;

    if ($value < $this->min || $value > $this->max) {
      throw new SkipExecution(
        "The value '{$value}' is not between {$this->min} and {$this->max}."
      );
    }
  }
}
