<?php

namespace Kettasoft\Filterable\Engines\Factory;

use Kettasoft\Filterable\Engines\Tree;
use Kettasoft\Filterable\Engines\Ruleset;
use Kettasoft\Filterable\Engines\Expression;
use Kettasoft\Filterable\Engines\Invokable;
use Kettasoft\Filterable\Engines\Foundation\Engine;

class EngineManager
{
  /**
   * Available engines.
   * @var array
   */
  protected static $engines = [
    'tree' => Tree::class,
    'ruleset' => Ruleset::class,
    'expression' => Expression::class,
    'invokable' => Invokable::class
  ];

  /**
   * Generate a new engine instance.
   * @param \Kettasoft\Filterable\Engines\Foundation\Engine|string $engine
   * @throws \InvalidArgumentException
   * @return Engine|object
   */
  public static function generate(Engine|string $engine, ...$args)
  {
    if ($engine instanceof Engine) {
      return $engine;
    }

    if (is_a($engine, Engine::class, true)) {
      return new $engine(...$args);
    }

    $engine = self::$engines[$engine] ?? throw new \InvalidArgumentException("Unknown engine [$engine]");

    return new $engine(...$args);
  }

  /**
   * Extend the engine manager with a custom engine.
   * 
   * @param string $name The name to register the engine under.
   * @param string $engineClass The engine class name.
   * @throws \InvalidArgumentException
   * @return void
   */
  public static function extend(string $name, string $engineClass)
  {
    if (!is_a($engineClass, Engine::class, true)) {
      throw new \InvalidArgumentException("Engine class must implement " . Engine::class);
    }

    self::$engines[$name] = $engineClass;
  }
}
