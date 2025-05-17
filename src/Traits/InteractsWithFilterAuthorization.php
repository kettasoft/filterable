<?php

namespace Kettasoft\Filterable\Traits;

trait InteractsWithFilterAuthorization
{
  /**
   * Authorization check before running filter operation.
   * @return bool
   */
  public function authorize(): bool
  {
    return true;
  }
}
