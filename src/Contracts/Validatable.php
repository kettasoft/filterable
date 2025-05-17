<?php

namespace Kettasoft\Filterable\Contracts;

interface Validatable
{
  public function validate();
  public function rules(): array;
}
