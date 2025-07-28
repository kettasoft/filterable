<?php

namespace Kettasoft\Filterable\Engines\Factory;

use Kettasoft\Filterable\Engines\Tree;
use Kettasoft\Filterable\Engines\Ruleset;
use Kettasoft\Filterable\Engines\Expression;
use Kettasoft\Filterable\Engines\Invokeable;
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
    'invokable' => Invokeable::class
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

    if (class_exists($engine) && is_subclass_of($engine, Engine::class)) {
      return new $engine(...$args);
    }

    $engine = self::$engines[$engine] ?? throw new \InvalidArgumentException("Unknown engine [$engine]");

    return new $engine(...$args);
  }
}
