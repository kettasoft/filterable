<?php

namespace Kettasoft\Filterable\Engines\Foundation\DTOs;

/**
 * Represents a single normalized filter after parsing.
 *
 * Every format (basic, operator-based, filter object, relation)
 * is reduced to this structure before the engine processes it.
 */
final class FilterEntry
{
    public function __construct(
        public readonly string $field,
        public readonly string $operator,
        public readonly mixed $value,
        public readonly ?string $relation = null,
    ) {}

    public function isRelation(): bool
    {
        return $this->relation !== null;
    }

    public function toArray(): array
    {
        return [
            'field'    => $this->field,
            'operator' => $this->operator,
            'value'    => $this->value,
            'relation' => $this->relation,
        ];
    }
}
