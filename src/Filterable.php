<?php

namespace Kettasoft\Filterable;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Kettasoft\Filterable\Contracts\FilterableContext;
use Kettasoft\Filterable\Engines\Contracts\Engine;
use Kettasoft\Filterable\Engines\Factory\EngineManager;

class Filterable implements FilterableContext
{
  /**
   * The running filter engine.
   * @var Engine
   */
  protected Engine $engine;

  /**
   * The Request instance.
   * @var Request
   */
  protected Request $request;

  /**
   * Create a new Filterable instance.
   * @param Request|null $request
   */
  public function __construct(Request|null $request = null)
  {
    $this->request = $request ?: App::make(Request::class);;
  }

  /**
   * Create new Filterable instance.
   * @param \Illuminate\Http\Request|null $request
   * @return static
   */
  public static function create(Request|null $request = null): static
  {
    return new static($request ?? App::make(Request::class));
  }

  /**
   * Use filter engine.
   * @param \Kettasoft\Filterable\Engines\Contracts\Engine|string $engine
   * @return Filterable
   */
  public function useEngin(Engine|string $engine): static
  {
    $this->engine = EngineManager::generate($engine, $this);

    return $this;
  }

  /**
   * Get current engine.
   * @return Engine
   */
  public function getEngin(): Engine
  {
    return $this->engine;
  }

  /**
   * Get the current request instance.
   * @return Request
   */
  public function getRequest(): Request
  {
    return $this->request;
  }
}
