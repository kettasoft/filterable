<?php

namespace Kettasoft\Filterable\Engines;

use Kettasoft\Filterable\Traits\FieldNormalizer;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Engines\Foundation\Clause;
use Kettasoft\Filterable\Engines\Foundation\Engine;
use Kettasoft\Filterable\Support\ConditionNormalizer;
use Kettasoft\Filterable\Support\ValidateTableColumns;
use Kettasoft\Filterable\Engines\Foundation\ClauseApplier;
use Kettasoft\Filterable\Engines\Foundation\Enums\Operators;
use Kettasoft\Filterable\Engines\Foundation\Appliers\Applier;
use Kettasoft\Filterable\Exceptions\InvalidOperatorException;
use Kettasoft\Filterable\Exceptions\NotAllowedFieldException;

class Expression extends Engine
{
  /**
   * Engine name.
   * @var string
   */
  protected $name = 'expression';

  /**
   * Apply filters to the query.
   * @param \Illuminate\Contracts\Database\Eloquent\Builder $builder
   * @return Builder
   */
  public function execute(Builder $builder)
  {
    $filters = $this->context->getData();

    foreach ($filters as $field => $condition) {

      // Normalize the condition to [ operator => value ].
      $condition = ConditionNormalizer::normalize($condition, $this->defaultOperator());

      $clause = Clause::make($this->getResources(), $field, $condition)->strict($this->isStrict());

      if (! $clause->isAllowedField() && ! $clause->isRelational()) {
        if ($this->isStrict()) {
          throw new NotAllowedFieldException($field);
        }

        continue; // skip disallowed field
      }

      if ($clause->isEmptyValue() && config('filterable.engines.expression.egnore_empty_values', false)) {
        continue;
      }

      return Applier::apply(new ClauseApplier($clause), $builder);
    }

    return $builder;
  }

  public function getAllowedFieldsFromConfig(): array
  {
    return config('filterable.engines.expression.allowed_fields', []);
  }

  /**
   * Check if the given column is registered in schema. 
   * @param mixed $column
   * @return bool
   */
  protected function validateTableColumns($builder, $column)
  {
    if (config('filterable.engines.expression.validate_columns', false) && ! str_contains($column, '.')) {
      return ValidateTableColumns::validate($builder, $column);
    }

    return true;
  }

  public function getOperatorsFromConfig(): array
  {
    return config('filterable.engines.expression.allowed_operators', []);
  }

  public function isStrictFromConfig(): bool
  {
    return config('filterable.engines.expression.strict', true);
  }

  /**
   * Get engine default operator.
   * @return string
   */
  public function defaultOperator(): string
  {
    return config('filterable.engines.expression.default_operator', 'eq');
  }

  /**
   * Get engine name.
   * @return string
   */
  public function getEngineName(): string
  {
    return $this->name;
  }
}
