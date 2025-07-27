<?php

namespace Kettasoft\Filterable\Engines;

use Illuminate\Database\Eloquent\Builder;
use Kettasoft\Filterable\Traits\FieldNormalizer;
use Kettasoft\Filterable\Engines\Foundation\Clause;
use Kettasoft\Filterable\Engines\Foundation\Engine;
use Kettasoft\Filterable\Engines\Foundation\ClauseApplier;
use Kettasoft\Filterable\Engines\Foundation\Appliers\Applier;
use Kettasoft\Filterable\Exceptions\NotAllowedFieldException;

class Ruleset extends Engine
{
  use FieldNormalizer;

  /**
   * Apply filters to the query.
   * @param \Illuminate\Database\Eloquent\Builder $builder
   * @return Builder
   */
  public function execute(Builder $builder): Builder
  {
    $data = $this->context->getData();

    foreach ($data as $field => $dissector) {

      $clause = Clause::make($this->getResources(), $field, $dissector)->strict($this->isStrict());

      if (! $clause->isAllowedField()) {

        if ($this->isStrict()) {
          throw new NotAllowedFieldException($field);
        }

        continue;
      }

      Applier::apply(new ClauseApplier($clause), $builder);
    }

    return $builder;
  }

  /**
   * Check if normalize field option is enable in engine.
   * @return bool
   */
  protected function hasNormalizeFieldCondition(): bool
  {
    return config('filterable.engines.ruleset.normalize_keys', false);
  }

  /**
   * Get engine default operator.
   * @return string
   */
  public function defaultOperator(): string
  {
    return config('filterable.engines.ruleset.default_operator', 'eq');
  }

  /**
   * Get allowed fields to filtering.
   * @return array
   */
  protected function getAllowedFieldsFromConfig(): array
  {
    return config('filterable.engines.ruleset.allowed_fields', []);
  }

  public function getOperatorsFromConfig(): array
  {
    return config('filterable.engines.ruleset.allowed_operators', []);
  }

  public function isStrictFromConfig(): bool
  {
    return config('filterable.engines.ruleset.strict', true);
  }
}
