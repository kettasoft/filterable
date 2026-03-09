<?php

namespace Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Sanitize implements \Kettasoft\Filterable\Engines\Foundation\Attributes\Contracts\MethodAttribute
{
  /**
   * The sanitization rules to apply.
   *
   * @var array<string>
   */
  protected array $rules;

  /**
   * Constructor for Sanitize attribute.
   * 
   * Supported rules: 'lowercase', 'uppercase', 'ucfirst', 'strip_tags', 'nl2br', 'slug', 'trim'.
   * 
   * @param string ...$rules The sanitization rules to apply in order.
   */
  public function __construct(string ...$rules)
  {
    $this->rules = $rules;
  }

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
   * @throws \InvalidArgumentException if a rule is not supported.
   */
  public function handle(\Kettasoft\Filterable\Engines\Foundation\Attributes\AttributeContext $context): void
  {
    /** @var \Kettasoft\Filterable\Support\Payload $payload */
    $payload = $context->payload;

    if (! is_string($payload->value)) {
      return;
    }

    $value = $payload->value;

    foreach ($this->rules as $rule) {
      $value = match ($rule) {
        'lowercase' => mb_strtolower($value),
        'uppercase' => mb_strtoupper($value),
        'ucfirst' => ucfirst($value),
        'strip_tags' => strip_tags($value),
        'nl2br' => nl2br($value),
        'slug' => \Illuminate\Support\Str::slug($value),
        'trim' => trim($value),
        default => throw new \InvalidArgumentException("Sanitization rule [{$rule}] is not supported."),
      };
    }

    $payload->setValue($value);
  }
}
