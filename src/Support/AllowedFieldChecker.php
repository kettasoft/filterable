<?php

namespace Kettasoft\Filterable\Support;

use Kettasoft\Filterable\Engines\Contracts\HasAllowedFieldChecker;
use Kettasoft\Filterable\Exceptions\NotAllowedFieldException;

class AllowedFieldChecker
{
  /**
   * Check if a field is allowed for filtering.
   * @param \Kettasoft\Filterable\Engines\Contracts\HasAllowedFieldChecker $context
   * @param mixed $field
   * @throws \Kettasoft\Filterable\Exceptions\NotAllowedFieldException
   * @return bool
   */
  public static function check(HasAllowedFieldChecker $context, $field): bool
  {
    $allowedFields = $context->getAllowedFields();

    if (isset($allowedFields[0]) && $allowedFields[0] === '*') {
      return true;
    }

    if (in_array($field, $allowedFields)) {
      return true;
    }

    if ($context->isStrict()) {
      throw new NotAllowedFieldException($field);
    }

    return false;
  }
}
