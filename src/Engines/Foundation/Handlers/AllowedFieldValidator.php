<?php

namespace Kettasoft\Filterable\Engines\Foundation\Handlers;

use Kettasoft\Filterable\Engines\Foundation\Engine;
use Kettasoft\Filterable\Engines\Exceptions\NotAllowedFieldException;

/**
 * Validate if field is allowed to apply filtering.
 */
class AllowedFieldValidator
{
  /**
   * Check if field is allowed to apply filtering.
   * @param \Kettasoft\Filterable\Engines\Contracts\HasAllowedFieldChecker $engine
   * @param mixed $field
   * @throws \Kettasoft\Filterable\Exceptions\NotAllowedFieldException
   * @return bool
   */
  final public static function validate(Engine $engine, $field)
  {
    if (! in_array($field, $engine->getAllowedFields())) {
      return $engine->isStrict() ? self::throw($field) : false;
    }

    return true;
  }

  /**
   * Throw field is not allowed.
   * @param mixed $field
   * @throws \Kettasoft\Filterable\Exceptions\NotAllowedFieldException
   * @return never
   */
  protected static function throw($field)
  {
    throw new NotAllowedFieldException($field);
  }
}
