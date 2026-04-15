<?php

namespace Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Scope implements \Kettasoft\Filterable\Engines\Foundation\Attributes\Contracts\MethodAttribute
{
    /**
     * Constructor for Scope attribute.
     *
     * @param string $scope The name of the Eloquent scope to apply (without the 'scope' prefix).
     */
    public function __construct(public string $scope)
    {
    }

    /**
     * Get the stage at which this attribute should be applied.
     *
     * @return int
     */
    public static function stage(): int
    {
        return \Kettasoft\Filterable\Engines\Foundation\Attributes\Enums\Stage::BEHAVIOR->value;
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
        /** @var \Illuminate\Contracts\Eloquent\Builder $query */
        $query = $context->query;

        /** @var \Kettasoft\Filterable\Support\Payload $payload */
        $payload = $context->payload;

        $scope = $this->scope;

        if (!method_exists($query->getModel(), 'scope'.ucfirst($scope))) {
            throw new \InvalidArgumentException(
                "The scope '{$scope}' does not exist on the model '".get_class($query->getModel())."'."
            );
        }

        $query->{$scope}($payload->value);

        // Set a flag in context to indicate the scope was applied,
        // allowing the engine to optionally skip the filter method execution.
        $context->set('scope_applied', true);
    }
}
