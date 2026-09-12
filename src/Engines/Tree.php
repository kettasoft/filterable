<?php

namespace Kettasoft\Filterable\Engines;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Support\TreeNode;
use Kettasoft\Filterable\Traits\FieldNormalizer;
use Kettasoft\Filterable\Engines\Foundation\Engine;
use Kettasoft\Filterable\Engines\Foundation\PayloadApplier;
use Kettasoft\Filterable\Engines\Foundation\PayloadFactory;

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
   * @param \Illuminate\Contracts\Database\Eloquent\Builder $builder
   * @return Builder
   */
  public function execute(Builder $builder): Builder
  {
    $data = $this->context->getData();

    $this->applyNode($builder, TreeNode::parse($data));

    return $builder;
  }

  /**
   * Apply a tree node to the query builder.
   *
   * A group owns the boolean used to join its direct children. Rejected
   * children are omitted in permissive mode without changing that boundary.
   *
   * @param \Illuminate\Contracts\Database\Eloquent\Builder $builder
   * @param \Kettasoft\Filterable\Support\TreeNode $node
   * @return bool Whether the node added a query constraint.
   */
  private function applyNode(Builder $builder, TreeNode $node): bool
  {
    if ($node->isGroup()) {
      $appliedChildren = 0;

      $builder->where(function (Builder $query) use ($node, &$appliedChildren): void {
        foreach ($node->children as $child) {
          $method = $appliedChildren === 0 || strtolower($node->logical) === 'and'
            ? 'where'
            : 'orWhere';
          $childApplied = $this->attempt(function () use ($child, $query, $method): bool {
            $applied = false;

            $query->{$method}(function (Builder $sub) use ($child, &$applied): void {
              $applied = $this->applyNode($sub, $child);
            });

            return $applied;
          });

          if ($childApplied) {
            $appliedChildren++;
          }
        }
      });

      return $appliedChildren > 0;
    }

    $payload = (new PayloadFactory($this))->make(
      new Payload($node->field, $node->operator ?? $this->defaultOperator(), $this->sanitizeValue($node->field, $node->value), $node->value)
    );

    (new PayloadApplier($payload))->apply($builder);

    $this->commit($node->field, $payload);

    return true;
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
