<?php

namespace Kettasoft\Filterable\Tests\Unit\Filterable;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Engines\Ruleset;
use Kettasoft\Filterable\Engines\Expression;
use Kettasoft\Filterable\Engines\Invokable;
use Kettasoft\Filterable\Engines\Tree;

class CanUseEngineFromFilterableTest extends TestCase
{
  /**
   * It can filterable class use ruleset engine instance
   * @test
   */
  public function it_can_filterable_class_use_engine_instance()
  {
    $filterable = Filterable::create()->useEngine('ruleset');

    $this->assertInstanceOf(Ruleset::class, $filterable->getEngine());
  }
  /**
   * It can filterable class use invokable engine instance
   * @test
   */
  public function it_can_filterable_class_use_invokable_engine_instance()
  {
    $filterable = Filterable::create()->useEngine('invokable');

    $this->assertInstanceOf(Invokable::class, $filterable->getEngine());
  }
  /**
   * It can filterable class use tree engine instance
   * @test
   */
  public function it_can_filterable_class_use_tree_engine_instance()
  {
    $filterable = Filterable::create()->useEngine('tree');

    $this->assertInstanceOf(Tree::class, $filterable->getEngine());
  }
  /**
   * It can filterable class use expression engine instance
   * @test
   */
  public function it_can_filterable_class_use_expression_engine_instance()
  {
    $filterable = Filterable::create()->useEngine('expression');

    $this->assertInstanceOf(Expression::class, $filterable->getEngine());
  }
}
