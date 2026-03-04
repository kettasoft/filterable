<?php

namespace Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations;

use Attribute;
use Kettasoft\Filterable\Engines\Exceptions\SkipExecution;

#[Attribute(Attribute::TARGET_METHOD)]
class Regex implements \Kettasoft\Filterable\Engines\Foundation\Attributes\Contracts\MethodAttribute
{
  /**
   * Constructor for Regex attribute.
   * 
   * @param string $pattern The regex pattern to match against.
   * @param string $message Optional custom error message.
   */
  public function __construct(
    public string $pattern,
    public string $message = '',
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

    if (! is_string($payload->value)) {
      throw new SkipExecution(
        $this->message ?: "The value is not a string and cannot be matched against pattern '{$this->pattern}'."
      );
    }

    if (! preg_match($this->pattern, $payload->value)) {
      throw new SkipExecution(
        $this->message ?: "The value '{$payload->value}' does not match the pattern '{$this->pattern}'."
      );
    }
  }
}
