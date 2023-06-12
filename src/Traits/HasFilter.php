<?php

namespace Kettasoft\Filterable\Traits;

use Kettasoft\Filterable\Filter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait HasFilter
 * @package Kettasoft\Filterable\Traits
 */
trait HasFilter
{
    /**
     * @param  Builder  $builder
     * @param  array  $data
     * @param  array|null  $filters
     * @return Builder
     */
    public function filter(Builder $builder, array $data, ?array $filters = null): Builder
    {
        return (new Filter($builder, $data, $filters ?: $this->filters))->filter();
    }
}