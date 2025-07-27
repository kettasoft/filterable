<?php

namespace Kettasoft\Filterable\Engines\Foundation\Mappers;

use Kettasoft\Filterable\Engines\Contracts\Mappable;
use Kettasoft\Filterable\Engines\Contracts\HasFieldMap;

class FieldMapper implements Mappable
{
  /**
   * FieldMapper constructor.
   * @param \Kettasoft\Filterable\Engines\Contracts\HasFieldMap $context
   */
  public function __construct(protected HasFieldMap $context) {}

  /**
   * Create new FieldMapper instance.
   * @param \Kettasoft\Filterable\Engines\Contracts\HasFieldMap $engine
   * @return FieldMapper
   */
  public static function init(HasFieldMap $engine)
  {
    return new self($engine);
  }

  /**
   * Mapping field to real database column name.
   * @param string $field
   */
  public function map(string|null $field = null): string
  {
    return $this->context->getFieldsMap()[$field] ?? $field;
  }
}
