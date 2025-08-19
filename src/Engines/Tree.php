<?php

namespace Kettasoft\Filterable\Engines;

use Illuminate\Database\Eloquent\Builder;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Support\TreeNode;
use Kettasoft\Filterable\Traits\FieldNormalizer;
use Kettasoft\Filterable\Engines\Foundation\Clause;
use Kettasoft\Filterable\Engines\Foundation\Engine;
use Kettasoft\Filterable\Support\RelationPathParser;
use Kettasoft\Filterable\Support\AllowedFieldChecker;
use Kettasoft\Filterable\Engines\Foundation\ClauseFactory;
use Kettasoft\Filterable\Support\TreeBasedRelationsResolver;
use Kettasoft\Filterable\Engines\Contracts\TreeFilterableContext;
use Kettasoft\Filterable\Engines\Contracts\HasAllowedFieldChecker;
use Kettasoft\Filterable\Support\TreeBasedSignelConditionResolver;
use Kettasoft\Filterable\Engines\Contracts\HasInteractsWithOperators;

class Tree extends Engine
{
  use FieldNormalizer;

  /**
   * Engine name.
   * @var string
   */
  protected $name = 'tree';

  /**
   * Apply filters to the query.
   * @param \Illuminate\Database\Eloquent\Builder $builder
   * @return Builder
   */
  public function execute(Builder $builder)
  {
    $data = $this->context->getData();

    return $this->applyNode($builder, TreeNode::parse($data));
  }

  private function applyNode(Builder $builder, TreeNode $node)
  {
    if ($node->isGroup()) {
      $builder->where(function (Builder $q) use ($node) {
        foreach ($node->children as $child) {
          $method = strtolower($child->logical) === 'and' ? 'where' : 'orWhere';

          $q->{$method}(function ($sub) use ($child) {
            $this->applyNode($sub, $child);
          });
        }
      });
    } else {

      $clause = (new ClauseFactory($this))->make(
        new Payload($node->field, $node->operator ?? $this->defaultOperator(), $node->value, null)
      );

      if (! $clause->validated) {
        return $builder; // skip disallowed field
      }

      [$_, $field] = RelationPathParser::resolve($node->field);

      $field = $clause->field;
      $operator = $clause->operator;
      $value = $clause->value;

      if ($clause->isRelational()) {
        $clause->relation($this->getResources()->relations)->resolve($builder, $clause);
      } else {
        if (! AllowedFieldChecker::check($this, $field)) {
          return;
        }

        TreeBasedSignelConditionResolver::resolve($builder, $field, $operator, $value);
      }
    }

    return $builder;
  }

  /**
   * Check if normalize field option is enable in engine.
   * @return bool
   */
  protected function hasNormalizeFieldCondition(): bool
  {
    return config('filterable.engines.tree.normalize_keys', false);
  }

  /**
   * Get allowed fields to filtering.
   * @return array
   */
  protected function getAllowedFieldsFromConfig(): array
  {
    return config('filterable.engines.tree.allowed_fields', []);
  }

  /**
   * Get all operators.
   * @return array
   */
  public function operators(): array
  {
    return config('filterable.engines.tree.allowed_operators', []);
  }

  /**
   * Default operator for use.
   * @return mixed|\Illuminate\Config\Repository
   */
  public function defaultOperator()
  {
    return config('filterable.engines.tree.default_operator', null);
  }

  public function getOperatorsFromConfig(): array
  {
    return config('filterable.engines.tree.allowed_operators', []);
  }

  public function isStrictFromConfig(): bool
  {
    return config('filterable.engines.tree.strict', true);
  }

  public function isIgnoredEmptyValuesFromConfig(): bool
  {
    return config('filterable.engines.tree.ignore_empty_values', false);
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
