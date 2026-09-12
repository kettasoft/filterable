<?php

namespace Kettasoft\Filterable\Engines\Exceptions;

use Kettasoft\Filterable\Support\Payload;

class OperatorNotAllowedForFieldException extends SkipExecution
{
  /**
   * Create an exception for an operator rejected by a field policy.
   *
   * @param string $field Public filter field restricted by the policy.
   * @param string $operator Rejected operator alias or resolved name.
   * @param Payload|null $payload Payload associated with the rejected filter.
   */
  public function __construct(string $field, string $operator, ?Payload $payload = null)
  {
    parent::__construct(
      sprintf('Operator [%s] is not allowed for field [%s]', $operator, $field),
      $payload
    );
  }
}
