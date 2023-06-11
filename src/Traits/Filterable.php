<?php

namespace Kettasoft\EloquentFilter\Traits;

use Kettasoft\EloquentFilter\Filter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait Filterable
 * @package Kettasoft\EloquentFilter\Traits
 * @method static Builder filter(array $data, ?array $filters = null)
 * @method static LengthAwarePaginator paginateFilter(?int $perPage = null, array $columns = ['*'], string $pageName = 'page', ?int $page = null)
 * @method static Paginator simplePaginateFilter(?int $perPage = null, array $columns = ['*'], string $pageName = 'page', ?int $page = null)
 */
trait Filterable
{
    /**
     * @var Filter
     */
    protected $filterManager;

    /**
     * @param  Builder  $builder
     * @param  array  $data
     * @param  array|null  $filters
     * @return Builder
     */
    public function scopeFilter(Builder $builder, array $data, ?array $filters = null): Builder
    {
        $this->filterManager = new Filter($builder, $data, $filters ?: $this->filters);

        return $this->filterManager->filter();
    }

    /**
     * @param  Builder  $builder
     * @param  int|null  $perPage
     * @param  array|string[]  $columns
     * @param  string  $pageName
     * @param  int|null  $page
     * @return LengthAwarePaginator
     */
    public function scopePaginateFilter(Builder $builder, ?int $perPage = null, array $columns = ['*'], string $pageName = 'page', ?int $page = null): LengthAwarePaginator
    {
        $paginator = $builder->paginate($perPage, $columns, $pageName, $page);
        $paginator->appends($this->filterManager->getData());

        return $paginator;
    }

    /**
     * @param  Builder  $builder
     * @param  int|null  $perPage
     * @param  array|string[]  $columns
     * @param  string  $pageName
     * @param  int|null  $page
     * @return Paginator
     */
    public function scopeSimplePaginateFilter(Builder $builder, ?int $perPage = null, array $columns = ['*'], string $pageName = 'page', ?int $page = null): Paginator
    {
        $paginator = $builder->simplePaginate($perPage, $columns, $pageName, $page);
        $paginator->appends($this->filterManager->getData());

        return $paginator;
    }
}