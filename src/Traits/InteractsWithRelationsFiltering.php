<?php

namespace Kettasoft\Filterable\Traits;

trait InteractsWithRelationsFiltering
{
  /**
   * List of allowed direct relations for filtering.
   * @var array string[]
   */
  protected $relations = [];

  /**
   * Set the allowed direct relations for filtering.
   * @param array $relations
   * @param mixed $override
   */
  public function allowRelations(array $relations, bool $override = false): static
  {
    $this->relations = $override ? $relations : array_merge($this->relations, $relations);
    return $this;
  }

  /**
   * Set the allowed relations for filtering.
   * @param array $relations
   * @param bool $override
   */
  public function setRelations(array $relations, bool $override = false): static
  {
    return $this->allowRelations($relations, $override);
  }

  /**
   * Check if a given relation is allowed for filtering.
   * @param string $relation
   * @return bool
   */
  public function isRelationAllowed(string $relation, $field): bool
  {
    if (in_array($relation, $this->relations, true)) {
      return isset($this->relations[$relation]) ? in_array($field, $this->relations[$relation]) : false;
    }

    return false;
  }

  /**
   * Get defined relations.
   * @return array
   */
  public function getRelations(): array
  {
    return $this->relations;
  }

  /**
   * Create Filterable instance with define relations attributes.
   * @param array $relations
   */
  public static function withRelations(array $relations): static
  {
    return static::create()->setRelations($relations);
  }
}
