<?php

namespace Kettasoft\Filterable\Engines;

use Kettasoft\Filterable\Traits\FieldNormalizer;
use Kettasoft\Filterable\Engines\Contracts\Engine;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Contracts\FilterableContext;
use Kettasoft\Filterable\Support\ConditionNormalizer;
use Kettasoft\Filterable\Support\ValidateTableColumns;
use Kettasoft\Filterable\Exceptions\InvalidOperatorException;
use Kettasoft\Filterable\Exceptions\NotAllowedFieldException;

class Expression implements Engine
{
  use FieldNormalizer;

  /**
   * Create Engine instance.
   * @param \Kettasoft\Filterable\Contracts\FilterableContext $context
   */
  public function __construct(protected FilterableContext $context) {}

  /**
   * Apply filters to the query.
   * @param \Illuminate\Contracts\Database\Eloquent\Builder $builder
   * @return Builder
   */
  public function apply(Builder $builder)
  {
    $filters = $this->context->getData();

    foreach ($filters as $field => $condition) {

      $field = $this->normalizeField($field);

      if (! is_array($condition)) {
        $condition = ConditionNormalizer::normalize($condition, 'eq');
      }

      foreach ($condition as $opKey => $value) {

        if (config('filterable.engines.expression.egnore_empty_values', false)) {
          continue;
        }

        $operator = $this->getAllowedOperators($opKey);

        $value = $this->context->getSanitizerInstance()->handle($field, $value);

        if (str_contains($field, '.')) {
          $this->applyRelationFilter($builder, $field, $operator, $value);
        } else {
          $this->applyColumnFilter($builder, $field, $operator, $value);
        }
      }
    }

    return $builder;
  }

  /**
   * Get allowed fields to filtering.
   * @return array
   */
  private function getAllowedFields(): array
  {
    return array_merge(config('filterable.engines.expression.allowed_fields', []), $this->context->getAllowedFields());
  }

  /**
   * Apply filter to a top‑level column.
   *
   * @param Builder $query
   * @param string $field
   * @param string $operator
   * @param mixed $value
   * @return void
   */
  protected function applyColumnFilter(Builder $query, string $field, string $operator, mixed $value): void
  {
    if (! in_array($field, $this->getAllowedFields())) {
      if ($this->isStrict()) {
        throw new NotAllowedFieldException($field);
      }

      return; // skip disallowed column
    }

    $field = $this->pipeToColumnName($field);

    if (! $this->validateTableColumns($query, $field)) {
      return;
    }

    if (in_array($operator, ['in', 'not in']) && is_array($value)) {
      $method = $operator === 'in' ? 'whereIn' : 'whereNotIn';
      $query->{$method}($field, $value);
    } else {
      $query->where($field, $operator, $value);
    }
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
   * Apply filter to a (possibly nested) relation.
   *
   * @param Builder  $query
   * @param string $path      dot‑notation path, e.g. "author.profile.name"
   * @param string $operator
   * @param mixed $value
   * @return void
   */
  protected function applyRelationFilter(Builder $query, string $path, string $operator, mixed $value): void
  {
    $segments = explode('.', $path);
    $field = array_pop($segments);
    $relationPath = implode('.', $segments);

    if ($this->context->isRelationAllowed($relationPath, $field)) {
      return;
    }

    // build nested whereHas
    $this->buildNested($query, $segments, $field, $operator, $value);
  }

  /**
   * Recursively build nested whereHas calls.
   *
   * @param Builder  $query
   * @param string[] $relations  e.g. ['author','profile']
   * @param string $field
   * @param string $operator
   * @param mixed $value
   * @return void
   */
  protected function buildNested(Builder $query, array $relations, string $field, string $operator, mixed $value): void
  {
    $rel = array_shift($relations);

    $query->whereHas($rel, function (Builder $q) use ($relations, $field, $operator, $value) {
      if (empty($relations)) {
        // innermost: apply the actual where
        $q->where($field, $operator, $value);
      } else {
        // deeper nesting
        $this->buildNested($q, $relations, $field, $operator, $value);
      }
    });
  }

  /**
   * Pipe to the correct table column name.
   * @param string $column
   * @return string
   */
  protected function pipeToColumnName(string $column): string
  {
    return $this->context->getFieldsMap()[$column] ?? $column;
  }

  /**
   * Get allowed operators.
   * @return array
   */
  protected function getAllowedOperators(string $operator): string
  {
    $allowed = $this->context->getAllowedOperators();

    $operators = config('filterable.engines.expression.allowed_operators', []);

    if ($allowed === []) {
      return $operators[$operator] ?? $operators[$this->defaultOperator()];
    }

    if (!array_key_exists($operator, array_intersect_key($operators, array_flip($allowed))) && $this->isStrict()) {
      throw new InvalidOperatorException($operator);
    }

    return $operators[$this->defaultOperator()];
  }

  public function isStrict()
  {
    if (is_bool($this->context->strict)) {
      return $this->context->strict;
    }

    return config('filterable.engines.expression.strict', true);
  }

  /**
   * Get engine default operator.
   * @return string
   */
  private function defaultOperator(): string
  {
    return config('filterable.engines.expression.default_operator', 'eq');
  }

  /**
   * Check if normalize field option is enable in engine.
   * @return bool
   */
  protected function hasNormalizeFieldCondition(): bool
  {
    return config('filterable.engines.expression.normalize_keys', false);
  }
}
