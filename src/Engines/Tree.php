<?php

namespace Kettasoft\Filterable\Engines;

use Illuminate\Database\Eloquent\Builder;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Support\TreeNode;
use Kettasoft\Filterable\Traits\FieldNormalizer;
use Kettasoft\Filterable\Engines\Foundation\Engine;
use Kettasoft\Filterable\Engines\Foundation\ClauseApplier;
use Kettasoft\Filterable\Engines\Foundation\ClauseFactory;
use Kettasoft\Filterable\Engines\Foundation\Appliers\Applier;

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
        new Payload($node->field, $node->operator ?? $this->defaultOperator(), $this->sanitizeValue($node->field, $node->value), $node->value)
      );

      if (! $clause->validated) {
        return $builder; // skip disallowed field
      }

      if ($clause->isRelational()) {
        $clause->relation($this->getResources()->relations)->resolve($builder, $clause);
      } else {
        Applier::apply(new ClauseApplier($clause), $builder);
      }

      $this->commit($node->field, $clause);
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
   * Default operator for use.
   * @return mixed|\Illuminate\Config\Repository
   */
  public function defaultOperator()
  {
    return config('filterable.engines.tree.default_operator', null);
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
