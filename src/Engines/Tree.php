<?php

namespace Kettasoft\Filterable\Engines;

use Kettasoft\Filterable\Support\TreeNode;
use Kettasoft\Filterable\Support\OperatorMapper;
use Kettasoft\Filterable\Traits\FieldNormalizer;
use Kettasoft\Filterable\Engines\Foundation\Engine;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Support\RelationPathParser;
use Kettasoft\Filterable\Support\AllowedFieldChecker;
use Kettasoft\Filterable\Support\TreeBasedRelationsResolver;
use Kettasoft\Filterable\Engines\Contracts\TreeFilterableContext;
use Kettasoft\Filterable\Engines\Contracts\HasAllowedFieldChecker;
use Kettasoft\Filterable\Support\TreeBasedSignelConditionResolver;
use Kettasoft\Filterable\Engines\Contracts\HasInteractsWithOperators;

class Tree extends Engine implements HasInteractsWithOperators, HasAllowedFieldChecker
{
  use FieldNormalizer;

  /**
   * Apply filters to the query.
   * @param \Illuminate\Contracts\Database\Eloquent\Builder $builder
   * @return Builder
   */
  public function apply(Builder $builder)
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

      // Ignore empty/null values if option is enable.
      if (!$node->value && ($this->context->hasIgnoredEmptyValues() || config('filterable.engines.tree.ignore_empty_values'))) return $builder;

      [$relation, $field] = RelationPathParser::resolve($node->field);

      $field = $this->normalizeField($this->context->getFieldsMap()[$field] ?? $field);
      $operator = (new OperatorMapper($this))->map($node->operator);
      $value = $this->context->getSanitizerInstance()->handle($field, $node->value);

      if ($relation) {
        $instance = new TreeBasedRelationsResolver($this->context);
        $instance->resolve($builder, $relation, $field, $operator, $value);
      } else {
        if (! AllowedFieldChecker::check($this, $field)) {
          return;
        }

        $instance = new TreeBasedSignelConditionResolver($this->context);
        $instance->resolve($builder, $field, $operator, $value);
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
   * Get all allowed fields.
   * @return array
   */
  public function getAllowedFields(): array
  {
    return $this->context->getAllowedFields();
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
   * Get only selected operators.
   * @return array
   */
  public function allowedOperators(): array
  {
    return $this->context->getAllowedOperators();
  }

  /**
   * Default operator for use.
   * @return mixed|\Illuminate\Config\Repository
   */
  public function defaultOperator()
  {
    return config('filterable.engines.tree.default_operator', null);
  }

  /**
   * Check if engine strictable.
   * @return bool
   */
  public function isStrict(): bool
  {
    return $this->context->isStrict() ?? config('filterable.engines.tree.strict', true);
  }
}
