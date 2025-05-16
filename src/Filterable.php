<?php

namespace Kettasoft\Filterable;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Kettasoft\Filterable\Sanitization\Sanitizer;
use Kettasoft\Filterable\Engines\Contracts\Engine;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Contracts\FilterableContext;
use Kettasoft\Filterable\Engines\Factory\EngineManager;
use Kettasoft\Filterable\Traits\InteractsWithFilterKey;
use Kettasoft\Filterable\Traits\InteractsWithMethodMentoring;
use Kettasoft\Filterable\Exceptions\RequestSourceIsNotSupportedException;

class Filterable implements FilterableContext
{
  use InteractsWithFilterKey,
    InteractsWithMethodMentoring;

  /**
   * The running filter engine.
   * @var Engine
   */
  protected Engine $engine;

  /**
   * Registered filters to operate upon.
   * @var array
   */
  protected $filters = [];

  /**
   * Ignore empty or null values option.
   * @var bool
   */
  protected $ignoreEmptyValues;

  /**
   * The Request instance.
   * @var Request
   */
  protected Request $request;

  /**
   * Request source.
   * @var string|null
   */
  protected $requestSource = 'query';

  /**
   * The Builder instance.
   * @var Builder
   */
  protected Builder $builder;

  /**
   * Registered sanitizers to operate upon.
   * @var array
   */
  protected $sanitizers = [];

  /**
   * All receved data from request.
   * @var array
   */
  protected $data = [];

  /**
   * The Sanitizer instance.
   * @var Sanitizer
   */
  public Sanitizer $sanitizer;

  /**
   * Create a new Filterable instance.
   * @param Request|null $request
   */
  public function __construct(Request|null $request = null)
  {
    $this->request = $request ?: App::make(Request::class);
    $this->sanitizer = new Sanitizer($this->sanitizers);
    $this->parseIncommingRequestData();
    $this->resolveEngine();
  }

  /**
   * Apply all filters.
   *
   * @param Builder $builder
   * @return Builder
   */
  public function apply(Builder|null $builder = null): Builder
  {
    $this->builder = $builder;
    $query = $this->engine->apply($this->builder);

    return $query;
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

  /**
   * Get sanitizer instance.
   * @return Sanitizer
   */
  public function getSanitizerInstance(): Sanitizer
  {
    return $this->sanitizer;
  }

  /**
   * Set manual data injection.
   * @param array $data
   * @param bool $override
   * @return static
   */
  public function setData(array $data, bool $override = true): static
  {
    $this->data = $override ? $data : array_merge($this->data, $data);
    return $this;
  }

  /**
   * Create new Filterable instance with custom Request.
   * @param \Illuminate\Http\Request $request
   * @return static
   */
  public static function withRequest(Request $request): static
  {
    return static::create($request);
  }

  /**
   * Set a new sanitizers classes.
   * @param array $sanitizers
   * @return Filterable
   */
  public function setSanitizers(array $sanitizers, bool $override = true): static
  {
    $this->sanitizers = $override ? $sanitizers : array_merge($this->sanitizers, $sanitizers);
    $this->sanitizer->setSanitizers($this->sanitizers);
    return $this;
  }

  /**
   * Parse incomming data from request.
   * @return void
   */
  private function parseIncommingRequestData()
  {
    $this->data = [...$this->request->all(), ...$this->request->json()->all()];
  }

  /**
   * Get current data.
   * @return array
   */
  public function getData(): mixed
  {
    return $this->data;
  }

  /**
   * Fetch all relevant filters from the filter API class.
   *
   * @return array
   */
  public function getFilterAttributes(): array
  {
    return property_exists($this, 'filters')
      && is_array($this->filters) ? $this->filters : [];
  }

  /**
   * Resolve default engine to Filterable instance.
   * @return void
   */
  private function resolveEngine()
  {
    $this->useEngin(config('filterable.default_engine'));
  }

  /**
   * Set request source.
   * @param string $source
   * @throws \Kettasoft\Filterable\Exceptions\RequestSourceIsNotSupportedException
   * @return static
   */
  public function setSource(string $source)
  {
    if (!in_array($source, ['query', 'input', 'json'])) {
      throw new RequestSourceIsNotSupportedException($source);
    }

    $this->requestSource = $source;
    return $this;
  }

  /**
   * Ignore empty or null values.
   * @return Filterable
   */
  public function ignoreEmptyValues(): static
  {
    $this->ignoreEmptyValues = true;
    return $this;
  }

  /**
   * Check if current filterable class has ignored empty values.
   * @return bool
   */
  public function hasIgnoredEmptyValues(): bool
  {
    return $this->ignoreEmptyValues === true;
  }

  /**
   * Retrieve an input item from the request.
   * @param string $key
   * @return mixed
   */
  public function get(string $key)
  {
    if (!in_array($source = $this->requestSource ?? config('filterable.request_source', 'query'), ['query', 'input', 'json'])) {
      throw new RequestSourceIsNotSupportedException($source);
    }

    return $this->request->{$source}($key);
  }
}
