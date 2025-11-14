<?php

namespace Kettasoft\Filterable\Engines;

use Kettasoft\Filterable\Support\Payload;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Engines\Foundation\Engine;
use Kettasoft\Filterable\Support\ConditionNormalizer;
use Kettasoft\Filterable\Support\ValidateTableColumns;
use Kettasoft\Filterable\Engines\Foundation\ClauseApplier;
use Kettasoft\Filterable\Engines\Foundation\ClauseFactory;
use Kettasoft\Filterable\Engines\Foundation\Appliers\Applier;
use Kettasoft\Filterable\Engines\Foundation\Parsers\Dissector;

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

      $dissector = Dissector::parse($condition, $this->defaultOperator());

      $clause = (new ClauseFactory($this))->make(
        new Payload($field, $dissector->operator, $this->sanitizeValue($field, $dissector->value), $dissector->value)
      );

      if (! $clause->validated) {
        continue; // skip disallowed field
      }

      $this->commit($field, $clause);

      return Applier::apply(new ClauseApplier($clause), $builder);
    }

    return $builder;
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
