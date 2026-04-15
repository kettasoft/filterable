<?php

namespace Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Authorize implements \Kettasoft\Filterable\Engines\Foundation\Attributes\Contracts\MethodAttribute
{
    /**
     * Constructor for Authorize attribute.
     *
     * @param class-string<\Kettasoft\Filterable\Contracts\Authorizable> $authorize The class name of the authorization logic.
     */
    public function __construct(public string $authorize)
    {
    }

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
     *
     * @return void
     */
    public function handle(\Kettasoft\Filterable\Engines\Foundation\Attributes\AttributeContext $context): void
    {
        if (!is_a($this->authorize, \Kettasoft\Filterable\Contracts\Authorizable::class, true)) {
            throw new \InvalidArgumentException("The class '{$this->authorize}' must implement the Authorizable contract.");
        }

        $authorize = new $this->authorize();

        if (!$authorize->authorize()) {
            throw new \Kettasoft\Filterable\Engines\Exceptions\SkipExecution("Authorization failed for class '{$this->authorize}'.");
        }
    }
}
