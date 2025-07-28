<?php

namespace Kettasoft\Filterable\Tests\Unit\Engines;

use Kettasoft\Filterable\Engines\Expression;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Engines\Ruleset;
use Kettasoft\Filterable\Engines\Invokeable;
use Kettasoft\Filterable\Engines\Factory\EngineManager;
use Kettasoft\Filterable\Engines\Tree;

class EngineManagerTest extends TestCase
{
  /**
   * It can create ruleset engine from engine manager.
   * @test
   */
  public function it_can_create_ruleset_engine_from_engine_manager()
  {
    $engine = EngineManager::generate('ruleset', new Filterable());

    $this->assertInstanceOf(Ruleset::class, $engine);
  }
  /**
   * It can create ruleset engine from engine manager.
   * @test
   */
  public function it_can_create_tree_engine_from_engine_manager()
  {
    $engine = EngineManager::generate('tree', new Filterable());

    $this->assertInstanceOf(Tree::class, $engine);
  }
  /**
   * It can create expression engine from engine manager.
   * @test
   */
  public function it_can_create_expression_engine_from_engine_manager()
  {
    $engine = EngineManager::generate('expression', new Filterable());

    $this->assertInstanceOf(Expression::class, $engine);
  }
  /**
   * It can create ruleset engine from engine manager.
   * @test
   */
  public function it_can_create_invokeablke_engine_from_engine_manager()
  {
    $engine = EngineManager::generate('invokable', new Filterable());

    $this->assertInstanceOf(Invokeable::class, $engine);
  }
}
