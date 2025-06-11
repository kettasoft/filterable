<?php

namespace Kettasoft\Filterable\Engines;

use Kettasoft\Filterable\Traits\FieldNormalizer;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Engines\Foundation\Engine;
use Kettasoft\Filterable\Exceptions\InvalidOperatorException;
use Kettasoft\Filterable\Exceptions\NotAllowedFieldException;

class Ruleset extends Engine
{
  use FieldNormalizer;

  /**
   * Apply filters to the query.
   * @param \Illuminate\Contracts\Database\Eloquent\Builder $builder
   * @return Builder
   */
  public function apply(Builder $builder): Builder
  {
    $data = $this->context->getData();

    foreach ($data as $field => $rawValue) {

      $field = $this->normalizeField($field);

      if (! in_array($field, $this->getAllowedFields())) {
        if ($this->isStrict()) {
          throw new NotAllowedFieldException($field);
        }

        continue;
      }

      [$operator, $value] = $this->parseOperatorAndValue($rawValue); // Check for operator

      $builder->where(
        $field,
        $operator,
        $this->context->getSanitizerInstance()->handle($field, $value)
      );
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
   * Get allowed fields to filtering.
   * @return array
   */
  private function getAllowedFields(): array
  {
    return array_merge(config('filterable.engines.ruleset.allowed_fields', []), $this->context->getAllowedFields());
  }

  /**
   * Parse operator and value from input.
   * @param string|array $raw
   * @return array{string|mixed}
   */
  private function parseOperatorAndValue(string|array $raw): array
  {
    if (is_array($raw)) {
      return $raw;
    }

    if (str_contains($raw, ':')) {
      [$operator, $value] = explode(':', $raw, 2);
    } else {
      [$operator, $value] = [$this->defaultOperator(), $raw];
    }

    return [$this->mapToValidOperator($operator), $value];
  }

  /**
   * Get engine default operator.
   * @return string
   */
  public function defaultOperator(): string
  {
    return config('filterable.engines.ruleset.default_operator', 'eq');
  }

  public function getOperatorsFromConfig(): array
  {
    return config('filterable.engines.ruleset.allowed_operators', []);
  }

  public function isStrictFromConfig(): bool
  {
    return config('filterable.engines.ruleset.strict', true);
  }

  /**
   * Operator mapping.
   * @param string $operator
   * @throws \Kettasoft\Filterable\Exceptions\InvalidOperatorException
   * @return string
   */
  private function mapToValidOperator(string $operator): string
  {
    $allowed = $this->context->getAllowedOperators();
    $operators = config('filterable.engines.ruleset.allowed_operators', []);

    if ($allowed === []) {
      return $operators[$operator] ?? $operators[$this->defaultOperator()];
    }

    if (!array_key_exists($operator, array_intersect_key($operators, array_flip($allowed))) && $this->isStrict()) {
      throw new InvalidOperatorException($operator);
    }

    return $operators[$this->defaultOperator()];
  }
}
