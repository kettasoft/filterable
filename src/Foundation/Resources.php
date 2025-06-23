<?php

namespace Kettasoft\Filterable\Foundation;

use Kettasoft\Filterable\Foundation\Bags\Bag;
use Kettasoft\Filterable\Foundation\Bags\FieldBag;
use Kettasoft\Filterable\Foundation\Bags\FieldMapBag;
use Kettasoft\Filterable\Foundation\Bags\OperatorBag;
use Kettasoft\Filterable\Foundation\Bags\RelationBag;
use Kettasoft\Filterable\Foundation\Bags\SanitizerBag;

/**
 * Class Resources
 *
 * This class holds various bags for fields, relations, operators, sanitizers, and field maps.
 * It is used to manage and access filterable resources in a structured way.
 */
class Resources
{
  /**
   * Field bag instance.
   * @var FieldBag
   */
  public Bag $fields;

  /**
   * Relation bag instance.
   * @var RelationBag
   */
  public Bag $relations;

  /**
   * Operator bag instance.
   * @var OperatorBag
   */
  public Bag $operators;

  /**
   * Sanitizers bag instance.
   * @var SanitizerBag
   */
  public Bag $sanitizers;

  /**
   * FieldMapBag bag instance.
   * @var FieldMapBag
   */
  public Bag $fieldMap;

  /**
   * Resources constructor.
   *
   * Initializes the bags with the provided settings.
   *
   * @param FilterableSettings $settings
   */
  public function __construct(FilterableSettings $settings)
  {
    $this->fields = new FieldBag($settings->fields);
    $this->relations = new RelationBag($settings->relations);
    $this->operators = new OperatorBag($settings->operators);
    $this->sanitizers = new SanitizerBag($settings->sanitizers);
    $this->fieldMap = new SanitizerBag($settings->fieldMaps);
  }

  /**
   * Initialize the Resources with the provided settings.
   *
   * @param FilterableSettings $settings
   * @return Resources
   */
  public function setOperators(array $operators)
  {
    $this->operators->fill($operators);
    return $this;
  }
}
