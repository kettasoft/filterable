<?php

namespace Kettasoft\Filterable\Foundation\Bags;

/**
 * Class OperatorBag
 *
 * This class extends the Bag class to specifically handle operator-related data.
 * It is used to manage and access filterable operators in a structured way.
 */
class OperatorBag extends Bag
{
  /**
   * Default operator for the bag.
   *
   * @var string
   */
  public string $default;

  /**
   * OperatorBag constructor.
   *
   * Initializes the bag with the provided operators and sets a default operator.
   *
   * @param array $operators
   * @param string $default
   */
  public function setDefault(string $operator)
  {
    $this->default = $operator;
  }
}
