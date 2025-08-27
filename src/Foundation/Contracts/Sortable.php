<?php

namespace Kettasoft\Filterable\Foundation\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface Sortable
{
  /**
   * Apply sorting to the query.
   * 
   * @param \Illuminate\Database\Eloquent\Builder $query
   * @return Builder
   */
  public function apply(Builder $query): Builder;

  /**
   * Define which fields are allowed for sorting.
   *
   * @param array<int, string> $fields
   * @return $this
   */
  public function allow(array $fields): self;

  /**
   * Map input fields to database columns.
   *
   * @param array<string, string> $fields
   * @return $this
   */
  public function map(array $fields): self;

  /**
   * Define a default sorting field and direction.
   *
   * @param string $field
   * @param string $direction 'asc' or 'desc'
   * @return $this
   */
  public function default(string $field, string $direction = 'asc'): self;

  /**
   * Add an alias (preset) for sorting.
   *
   * Example:
   * $sortable->alias("popular", [['views', 'desc'], ['likes', 'desc']]);
   *
   * @param string $name
   * @param array<int, array{0: string, 1: string}> $sorting
   * @return $this
   */
  public function alias(string $name, array $sorting): self;

  /**
   * Get the sorting configuration.
   *
   * @return array
   */
  public function getConfig(): array;

  /**
   * Get the allowed sorting fields.
   *
   * @return array
   */
  public function getAllowed(): array;

  /**
   * Get the default sorting field and direction.
   *
   * @return array|null
   */
  public function getDefault(): ?array;

  /**
   * Get the sorting aliases.
   *
   * @return array
   */
  public function getAliases(): array;

  /**
   * Set the request key to look for sorting parameters.
   *
   * @param string $key
   * @return $this
   * @throws \InvalidArgumentException
   */
  public function setSortKey(string $key): self;

  /**
   * Get the sort key used in the request.
   *
   * @return string
   */
  public function getSortKey(): string;

  /**
   * Set the delimiter for multi-field sorting.
   * 
   * @param string $delimiter
   * @throws \InvalidArgumentException
   * @return self
   */
  public function setDelimiter(string $delimiter): self;

  /**
   * Get the delimiter used for multi-field sorting.
   *
   * @return string
   */
  public function getDelimiter(): string;
}
