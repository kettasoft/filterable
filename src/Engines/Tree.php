<?php

namespace Kettasoft\Filterable\Engines;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Support\TreeNode;
use Kettasoft\Filterable\Traits\FieldNormalizer;
use Kettasoft\Filterable\Engines\Foundation\Engine;
use Kettasoft\Filterable\Engines\Foundation\PayloadApplier;
use Kettasoft\Filterable\Engines\Foundation\PayloadFactory;
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
   * @param \Illuminate\Contracts\Database\Eloquent\Builder $builder
   * @return Builder
   */
  public function execute(Builder $builder): Builder
  {
    $data = $this->context->getData();

    return $this->applyNode($builder, TreeNode::parse($data));
  }

  /**
   * Apply tree node to the query builder.
   * @param \Illuminate\Contracts\Database\Eloquent\Builder $builder
   * @param \Kettasoft\Filterable\Support\TreeNode $node
   * @return Builder
   */
  private function applyNode(Builder $builder, TreeNode $node)
  {
    if ($node->isGroup()) {
      $builder->where(function (Builder $query) use ($node) {
        foreach ($node->children as $child) {
          $this->attempt(function () use ($child, $query) {
            $method = strtolower($child->logical) === 'and' ? 'where' : 'orWhere';

            $query->{$method}(function ($sub) use ($child) {
              $this->applyNode($sub, $child);
            });
          });
        }
      });
    } else {

      $payload = (new PayloadFactory($this))->make(
        new Payload($node->field, $node->operator ?? $this->defaultOperator(), $this->sanitizeValue($node->field, $node->value), $node->value)
      );

      if ($this->isRelational($payload->field)) {
        $this->applyRelational($builder, $payload);
      } else {
        Applier::apply(new PayloadApplier($payload), $builder);
      }

      $this->commit($node->field, $payload);
    }

    return $builder;
  }

  /**
   * Check if field is relational.
   * @param string $field
   * @return bool
   */
  protected function isRelational(string $field): bool
  {
    return is_string($field) && str_contains($field, '.');
  }

  /**
   * Apply a relational payload to the query.
   * @param \Illuminate\Contracts\Database\Eloquent\Builder $builder
   * @param Payload $payload
   * @return Builder
   */
  protected function applyRelational(Builder $builder, Payload $payload)
  {
    [$relation, $field] = explode('.', $payload->field, 2);

    return $builder->whereHas($relation, function ($query) use ($field, $payload) {
      $query->where($field, $payload->operator, $payload->value);
    });
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
