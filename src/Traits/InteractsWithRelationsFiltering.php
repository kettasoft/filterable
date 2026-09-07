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
    $this->resources->relations->fill($this->relations);
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
    $root = explode('.', $relation, 2)[0];

    foreach ($this->relations as $allowedRelation => $fields) {
      if (is_int($allowedRelation) && $fields === $root) {
        return true;
      }

      if ($allowedRelation !== $relation || !is_array($fields)) {
        continue;
      }

      return in_array('*', $fields, true) || in_array($field, $fields, true);
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
   * Check if the given path is a valid relation path.
   * 
   * @param string $path
   * @return bool
   */
  public function hasRelationPath(string $path): bool
  {
    if (str_contains($path, '.')) {

      $relations = explode('.', $path);

      $field = array_pop($relations);

      $path = implode('.', $relations);

      return $this->isRelationAllowed($path, $field);
    }

    return false;
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
