<?php

namespace Kettasoft\Filterable\Traits;

use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

trait InteractsWithValidation
{
  /**
   * Validate incomming request before filtring.
   * @throws \Illuminate\Validation\ValidationException
   * @return void
   */
  public function validate(): void
  {
    if (empty($this->rules())) {
      return;
    }

    $validator = validator(Arr::only($this->data, array_keys($this->rules())), $this->rules());

    if ($validator->fails()) {
      throw new ValidationException($validator);
    }
  }

  /**
   * Get the validation rules that apply to the filter request.
   * @return array
   */
  public function rules(): array
  {
    return [];
  }
}
