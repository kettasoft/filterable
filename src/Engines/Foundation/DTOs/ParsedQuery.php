<?php

namespace Kettasoft\Filterable\Engines\Foundation\DTOs;

use Kettasoft\Filterable\Engines\Foundation\DTOs\FilterEntry;

/**
 * The final normalized output of the parser pipeline.
 * Contains all parsed filter entries ready for the engine.
 */
final class ParsedQuery
{
    /** @var FilterEntry[] */
    private array $entries = [];

    public function add(FilterEntry $entry): void
    {
        $this->entries[] = $entry;
    }

    /** @return FilterEntry[] */
    public function all(): array
    {
        return $this->entries;
    }

    public function basic(): array
    {
        return array_values(array_filter(
            $this->entries,
            fn(FilterEntry $e) => ! $e->isRelation()
        ));
    }

    public function relations(): array
    {
        return array_values(array_filter(
            $this->entries,
            fn(FilterEntry $e) => $e->isRelation()
        ));
    }

    public function isEmpty(): bool
    {
        return empty($this->entries);
    }

    public function toArray(): array
    {
        return array_map(fn(FilterEntry $e) => $e->toArray(), $this->entries);
    }
}
