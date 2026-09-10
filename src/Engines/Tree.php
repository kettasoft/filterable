<?php

namespace Kettasoft\Filterable\Engines;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Support\TreeNode;
use Kettasoft\Filterable\Operations\Comparison;
use Kettasoft\Filterable\Operations\Contracts\Operation;
use Kettasoft\Filterable\Operations\Group;
use Kettasoft\Filterable\Traits\FieldNormalizer;
use Kettasoft\Filterable\Engines\Foundation\Engine;
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
    $payloads = [];
    $operation = $this->compileNode(
      TreeNode::parse($this->context->getData()),
      $payloads
    );

    $builder = $this->dispatchOperation($operation, $builder);

    foreach ($payloads as [$key, $payload]) {
      $this->commit($key, $payload);
    }

    return $builder;
  }

  /**
   * Compile a parsed Tree node into a backend-independent Operation tree.
   *
   * Invalid children handled in permissive mode are omitted from their group.
   * Valid payloads are collected and committed only after the Driver applies
   * the complete tree successfully.
   *
   * @param TreeNode $node Parsed condition or logical group.
   * @param array<int, array{0: string, 1: Payload}> $payloads Pending payloads.
   * @return Operation Compiled comparison or logical group.
   */
  private function compileNode(TreeNode $node, array &$payloads): Operation
  {
    if ($node->isGroup()) {
      $operations = [];

      foreach ($node->children as $child) {
        $operation = null;

        $this->attempt(function () use ($child, &$operation, &$payloads): bool {
          $operation = $this->compileNode($child, $payloads);

          return true;
        });

        if ($operation instanceof Operation) {
          $operations[] = $operation;
        }
      }

      return new Group($node->logical, $operations);
    }

    $payload = (new PayloadFactory($this))->make(
      new Payload(
        $node->field,
        $node->operator ?? $this->defaultOperator(),
        $this->sanitizeValue($node->field, $node->value),
        $node->value
      )
    );

    $payloads[] = [$node->field, $payload];

    return new Comparison($payload->field, $payload->operator, $payload->value);
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
