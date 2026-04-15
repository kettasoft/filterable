<?php

namespace Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations;

use Attribute;
use Kettasoft\Filterable\Engines\Exceptions\SkipExecution;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class SkipIf implements \Kettasoft\Filterable\Engines\Foundation\Attributes\Contracts\MethodAttribute
{
  /**
   * Constructor for SkipIf attribute.
   * 
   * @param string $check The Payload is* check name (e.g., 'empty', 'null', 'emptyString', 'boolean').
   *                      Prefix with '!' to negate (e.g., '!numeric').
   * @param string $message Optional custom message when skipping.
   */
  public function __construct(
    public string $check,
    public string $message = '',
  ) {}

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
    /** @var \Kettasoft\Filterable\Support\Payload $payload */
    $payload = $context->payload;

    $check = $this->check;
    $negate = false;

    if (str_starts_with($check, '!')) {
      $negate = true;
      $check = substr($check, 1);
    }

    $method = 'is' . ucfirst($check);

    if (! method_exists($payload, $method)) {
      throw new \InvalidArgumentException("Check method [{$method}] does not exist on Payload.");
    }

    $result = $payload->$method();

    if ($negate) {
      $result = ! $result;
    }

    if ($result) {
      throw new SkipExecution(
        $this->message ?: "Filter skipped because payload {$this->check} check was true.",
        $payload
      );
    }
  }
}
