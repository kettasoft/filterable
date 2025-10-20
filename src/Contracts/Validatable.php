<?php

namespace Kettasoft\Filterable\Contracts;

interface Validatable
{
  /**
   * Validate the current request.
   *
   * @return void
   */
  public function validate();

  /**
   * Get the validation rules.
   *
   * @return array
   */
  public function rules(): array;
}
