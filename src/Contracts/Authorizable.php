<?php

namespace Kettasoft\Filterable\Contracts;

interface Authorizable
{
  /**
   * Authorization check before running filter operation.
   * @return bool
   */
  public function authorize(): bool;
}
