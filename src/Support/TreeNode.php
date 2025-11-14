<?php

namespace Kettasoft\Filterable\Support;

use Kettasoft\Filterable\Engines\Exceptions\InvalidDataFormatException;

/**
 * TreeNode represents a filter node in a logical tree.
 * Can be either a group (AND/OR) or a condition node.
 */
class TreeNode
{
  /**
   * Logical operator(AND/OR)
   * @var string
   */
  public $logical;

  /**
   * Children of nodes
   * @var array
   */
  public array $children = [];

  /**
   * Field name.
   * @var string|null
   */
  public string|null $field = null;

  /**
   * Operator
   * @var string|null
   */
  public string|null $operator = null;
  public mixed $value = null;

  public static function parse($input): TreeNode
  {
    $node = new self();


    if (isset($input['and']) || isset($input['or'])) {
      $node->logical = isset($input['and']) ? 'and' : 'or';

      $group = $input[$node->logical];

      foreach ($group as $child) {
        $node->children[] = self::parse($child);
      }
    } else {
      try {
        $node->field = $input['field'];
        $node->operator = $input['operator'];
        $node->value = $input['value'];
      } catch (\Throwable $th) {
        throw new InvalidDataFormatException;
      }
    }

    return $node;
  }

  public function isGroup(): bool
  {
    return $this->field === null;
  }
}
