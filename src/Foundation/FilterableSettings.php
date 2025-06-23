<?php

namespace Kettasoft\Filterable\Foundation;

/**
 * Class FilterableSettings
 *
 * This class holds the settings for filterable fields, relations, operators, sanitizers, and field maps.
 * It is used to initialize and manage the filterable settings in a structured way.
 */
readonly class FilterableSettings
{
  /**
   * FilterableSettings constructor.
   * @param array $fields
   * @param array $relations
   * @param array $operators
   * @param array $sanitizers
   * @param array $fieldMaps
   */
  public function __construct(
    public array $fields,
    public array $relations,
    public array $operators,
    public array $sanitizers,
    public array $fieldMaps
  ) {}

  /**
   * Initialize the FilterableSettings with the provided parameters.
   *
   * @param array $fields
   * @param array $relations
   * @param array $operators
   * @param array $sanitizers
   * @param array $fieldMaps
   * @return FilterableSettings
   */
  public static function init(array $fields, array $relations, array $operators, array $sanitizers, array $fieldMaps): FilterableSettings
  {
    return new self($fields, $relations, $operators, $sanitizers, $fieldMaps);
  }
}
