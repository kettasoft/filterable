<?php

namespace Kettasoft\Filterable\Tests\Unit\Engines;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Engines\Tree;
use Kettasoft\Filterable\Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Kettasoft\Filterable\Engines\Ruleset;
use Kettasoft\Filterable\Engines\Expression;
use Kettasoft\Filterable\Engines\Invokable;
use Kettasoft\Filterable\Engines\Foundation\Engine;
use Kettasoft\Filterable\Engines\Factory\EngineManager;

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

    $this->assertInstanceOf(Invokable::class, $engine);
  }

  public function test_it_can_create_custom_engine_from_engine_manager()
  {
    $engine = new class(new Filterable()) extends Engine {

      public function handle(Builder $builder): Builder
      {
        return $builder;
      }

      protected function isStrictFromConfig(): bool
      {
        return false;
      }

      protected function getAllowedFieldsFromConfig(): array
      {
        return [];
      }

      protected function isIgnoredEmptyValuesFromConfig(): bool
      {
        return false;
      }

      public function getEngineName(): string
      {
        return false;
      }

      public function defaultOperator()
      {
        return '=';
      }

      public function getOperatorsFromConfig(): array
      {
        return ['='];
      }
    };

    EngineManager::extend('custom', get_class($engine));

    $engine = EngineManager::generate('custom', new Filterable());

    $this->assertInstanceOf(Engine::class, $engine);
  }
}
