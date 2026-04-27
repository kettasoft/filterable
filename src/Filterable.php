<?php

namespace Kettasoft\Filterable;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Support\Traits\Macroable;
use Kettasoft\Filterable\Contracts\Authorizable;
use Kettasoft\Filterable\Contracts\Commitable;
use Kettasoft\Filterable\Contracts\FilterableContext;
use Kettasoft\Filterable\Contracts\HasBuilder;
use Kettasoft\Filterable\Contracts\Validatable;
use Kettasoft\Filterable\Engines\Factory\EngineManager;
use Kettasoft\Filterable\Engines\Foundation\Engine;
use Kettasoft\Filterable\Engines\Foundation\Executors\Executer;
use Kettasoft\Filterable\Exceptions\Contracts\ExceptionHandlerInterface;
use Kettasoft\Filterable\Exceptions\MissingBuilderException;
use Kettasoft\Filterable\Exceptions\RequestSourceIsNotSupportedException;
use Kettasoft\Filterable\Foundation\Contracts\FilterableProfile;
use Kettasoft\Filterable\Foundation\Contracts\ShouldReturnQueryBuilder;
use Kettasoft\Filterable\Foundation\Contracts\Sortable;
use Kettasoft\Filterable\Foundation\Contracts\Sorting\Invokable;
use Kettasoft\Filterable\Foundation\Events\Contracts\EventManager;
use Kettasoft\Filterable\Foundation\Events\FilterableEventManager;
use Kettasoft\Filterable\Foundation\FilterableSettings;
use Kettasoft\Filterable\Foundation\Invoker;
use Kettasoft\Filterable\Foundation\Resources;
use Kettasoft\Filterable\Foundation\Runtime\Context;
use Kettasoft\Filterable\Foundation\Sorting\Sorter;
use Kettasoft\Filterable\Foundation\Traits\HandleFluentReturn;
use Kettasoft\Filterable\HttpIntegration\HeaderDrivenEngineSelector;
use Kettasoft\Filterable\Sanitization\Sanitizer;
use Kettasoft\Filterable\Support\Payload;

/**
 * The main Filterable class that provides the core functionality for applying filters to Eloquent queries.
 *
 * This class serves as the central point for managing filter execution, including:
 * - Handling incoming requests and parsing filter data
 * - Managing the filter engine and its execution
 * - Providing hooks for authorization, validation, and committing applied payloads
 * - Integrating with an event system to allow extensibility at various stages of the filtering process
 * - Supporting sorting and caching mechanisms
 *
 * The Filterable class is designed to be flexible and extensible, allowing developers to customize behavior through traits, events, and configuration.
 *
 * @package Kettasoft\Filterable
 * @property \Illuminate\Database\Eloquent\Builder $builder
 */
class Filterable implements FilterableContext, Authorizable, Validatable, Commitable, HasBuilder, Builder
{
  use Traits\InteractsWithFilterKey,
    Traits\InteractsWithMethodMentoring,
    Traits\InteractsWithFilterAuthorization,
    Traits\InteractsWithValidation,
    Traits\InteractsWithRelationsFiltering,
    Traits\HasFilterableEvents,
    Traits\InteractsWithProvidedData,
    Traits\HasFilterableCache,
    HandleFluentReturn,
    ForwardsCalls,
    Macroable;

  /**
   * The running filter engine.
   * @var Engine
   */
  protected Engine $engine;

  /**
   * Resources instance.
   * @var Resources
   */
  protected Resources $resources;

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
   * Runtime context for this filterable instance.
   *
   * Encapsulates all transient state that changes during filter execution:
   * - Applied filter payloads
   * - Skipped payloads
   * - Parsed request data
   * - Query builder instance
   * - Cache key generator
   *
   * @var Context
   */
  protected Context $context;

  /**
   * Registered sanitizers to operate upon.
   * @var array
   */
  protected $sanitizers = [];

  /**
   * Specify which fields are allowed to be filtered.
   * @var array
   */
  protected $allowedFields = [];

  /**
   * List of supported SQL operators you want to allow when parsing the expressions.
   * @var array
   */
  protected $allowedOperators = [];

  /**
   * Strict mode.
   * @var bool|null
   */
  protected $strict;

  /**
   * The field name mapping.
   * @var array
   */
  protected $fieldsMap = [];

  /**
   * Registered model.
   * @var Model
   */
  protected $model;

  /**
   * Aliases of filter class
   * @var array
   */
  protected static $aliases;

  /**
   * The Sanitizer instance.
   * @var Sanitizer
   */
  public Sanitizer $sanitizer;

  /**
   * Sorters for each filterable.
   * @var array<string, callable<Sorter>>
   */
  protected static array $sorters = [];

  /**
   * @var bool
   */
  protected $shouldReturnQueryBuilder = false;

  /**
   * Executors to execute before the filters are applied.
   * @var array<string>
   */
  protected $executors = [
    // Retrieving
    'get',
    'first',
    'firstOr',
    'firstOrFail',
    'firstOrCreate',
    'firstOrNew',
    'find',
    'findOr',
    'findOrFail',
    'findOrNew',
    'sole',
    'soleValue',

    // Aggregating
    'count',
    'sum',
    'avg',
    'average',
    'min',
    'max',

    // Boolean
    'exists',
    'existsOr',
    'doesntExist',
    'doesntExistOr',

    // Scalar
    'value',
    'pluck',
    'implode',

    // Paginating
    'paginate',
    'simplePaginate',
    'cursorPaginate',

    // Streaming (chunking)
    'chunk',
    'chunkById',
    'chunkByIdDesc',
    'each',
    'eachById',
    'lazy',
    'lazyById',
    'lazyByIdDesc',
    'cursor',

    // Mutating
    'insert',
    'insertOrIgnore',
    'insertGetId',
    'insertUsing',
    'insertOrIgnoreUsing',
    'update',
    'updateOrInsert',
    'upsert',
    'delete',
    'forceDelete',
    'restore',
    'truncate',
    'increment',
    'decrement',
    'incrementEach',
    'decrementEach',
  ];

  /**
   * Event manager instance.
   * @var EventManager
   */
  protected static EventManager $eventManager;

  /**
   * Create a new Filterable instance.
   *
   * @param Request|null $request
   */
  public function __construct(Request|null $request = null)
  {
    $this->boot($request);
    $this->booting();
    $this->booted();
  }

  /**
   * Initialize core dependencies and fire the initializing event.
   *
   * @return void
   */
  public function boot($request = null)
  {
    $this->request = $request ?: App::make(Request::class);
    $this->registerEventManager();
    $this->context = new Context();

    // Fire initializing event
    $this->fireEvent('filterable.initializing', ['filterable' => $this]);
  }

  /**
   * Prepare engine and internal components.
   *
   * @return void
   */
  public function booting()
  {
    $this->sanitizer = new Sanitizer($this->sanitizers);
    $this->resources = new Resources($this->settings());
    $this->resolveEngine();
    $this->parseIncomingRequestData();
  }

  /**
   * Finalize setup and fire the booted event.
   *
   * @return void
   */
  public function booted()
  {
    // Fire resolved event after initialization is complete
    $this->fireEvent('filterable.resolved', [
      'engine' => $this->engine,
      'data' => $this->data,
    ]);
  }

  /**
   * Create a new Filterable instance for a specific model.
   *
   * @param \Illuminate\Database\Eloquent\Model|\Illuminate\Contracts\Database\Eloquent\Builder|string $model
   * @param \Illuminate\Http\Request|null $request
   * @return static
   */
  public static function for(Model|Builder|string $model, Request|null $request = null): static
  {
    $instance = static::create($request);

    if ($instance->isModel($model)) {
      $instance->setModel($model);
    }

    $instance->initQueryBuilderInstance($model);

    return $instance;
  }

  /**
   * Check if the model is a valid model instance or class.
   * @param Model|string $model
   * @return bool
   */
  protected function isModel($model): bool
  {
    return $model instanceof Model || (is_string($model) && class_exists($model) && is_subclass_of($model, Model::class));
  }

  /**
   * Get request source.
   *
   * @return string
   */
  public function getRequestSource(): string
  {
    return $this->requestSource;
  }

  /**
   * Apply a filterable profile to the current instance.
   *
   * @param FilterableProfile|string $profile
   * @return static
   */
  public function useProfile(FilterableProfile|callable|string $profile): static
  {
    // Handle callable or FilterableProfile instance directly
    if (is_callable($profile)) {
      $profile($this);
      return $this;
    }

    if ($profile instanceof FilterableProfile) {
      $profile($this);
      return $this;
    }

    // Handle string references (class name or config key)
    if (is_string($profile)) {
      $profiles = config('filterable.profiles', []);
      $resolved = $profiles[$profile] ?? $profile;

      // If still not found or invalid, return as-is
      if (!class_exists($resolved)) {
        return $this;
      }

      $instance = App::make($resolved);

      if (is_callable($instance)) {
        $instance($this);
      }

      return $this;
    }

    return $this;
  }

  /**
   * Commit applied payload.
   *
   * Records a filter payload that has been successfully applied to the query.
   * This is a wrapper method that delegates to the runtime state.
   *
   * @param string $key The field name or unique identifier for the payload
   * @param Payload $payload The payload object representing the applied filter
   * @return bool Always returns true to indicate success
   */
  public function commit(string $key, Payload $payload): bool
  {
    $this->context->commitPayload($key, $payload);
    return true;
  }

  /**
   * Register a skipped payload.
   *
   * Records information about a filter that was skipped during execution.
   * This is a wrapper method that delegates to the runtime state.
   *
   * @param Payload $payload The payload that was skipped
   * @param string|null $reason Optional explanation for why it was skipped
   * @return bool Always returns true to indicate the skip was recorded
   */
  public function skip(Payload $payload, ?string $reason = null): bool
  {
    $this->context->skipPayload($payload, $reason);
    return true;
  }

  /**
   * Get all skipped payloads.
   *
   * Retrieves information about filters that were skipped, optionally filtered by field.
   * This is a wrapper method that delegates to the runtime state.
   *
   * @param string|null $field Optional field name to filter skipped payloads
   * @return array All skipped payloads or filtered by field
   */
  public function skipped(?string $field = null): array
  {
    return $this->context->getSkipped($field);
  }

  /**
   * Check if a specific field was skipped.
   *
   * Determines whether any filters for the given field were skipped.
   * This is a wrapper method that delegates to the runtime state.
   *
   * @param string $field The field name to check
   * @return bool True if the field has skipped filters, false otherwise
   */
  public function hasSkipped(string $field): bool
  {
    return $this->context->hasSkipped($field);
  }

  /**
   * Get applied payloads.
   *
   * Retrieves all applied payloads or a specific payload by key.
   * This is a wrapper method that delegates to the runtime state.
   *
   * @param string|null $key Optional field name to get a specific payload
   * @return array|Payload|null All payloads if key is null, specific payload otherwise, or null if not found
   */
  public function applied($key = null)
  {
    return $this->context->getApplied($key);
  }

  /**
   * Register the event manager instance.
   * @param array $options
   * @return void
   */
  private function registerEventManager(array $options = [])
  {
    self::$eventManager = App::make(FilterableEventManager::class, $options);
  }

  /**
   * Get Resources instance.
   * @return Resources
   */
  public function getResources(): Resources
  {
    return $this->resources;
  }

  /**
   * Get FilterableSettings instance.
   * @return FilterableSettings
   */
  public function settings(): FilterableSettings
  {
    return FilterableSettings::init(
      $this->allowedFields,
      $this->relations,
      $this->allowedOperators,
      $this->sanitizers,
      $this->fieldsMap
    );
  }

  /**
   * Apply all filters.
   *
   * @param Builder $builder
   * @return Builder|Invoker
   */
  public function apply(Builder|null $builder = null): Invoker|Builder
  {
    try {
      App::make(Pipeline::class)->send($this)->through([
        \Kettasoft\Filterable\Pipes\FilterAuthorizationPipe::class,
        \Kettasoft\Filterable\Pipes\ValidateBeforeFilteringPipe::class
      ])->thenReturn();

      $builder = $this->initQueryBuilderInstance($builder);

      $this->context->setBuilder($this->initially($builder));

      $builder = Executer::execute($this->engine, $builder);

      if (isset(self::$sorters[static::class])) {
        $builder = static::getSorting(static::class)?->apply($builder);
      }

      // Fire applied event on success
      $this->fireEvent('filterable.applied', [
        'filterable' => $this
      ]);

      if ($this instanceof ShouldReturnQueryBuilder || $this->shouldReturnQueryBuilder) {
        return $this->finally($builder);
      }

      $invoker = new Invoker($this->finally($builder));

      // Pass caching settings to invoker
      if ($this->isCachingEnabled()) {
        $invoker->enableCaching(
          $this->generateCacheKey(),
          $this->getCacheTtl(),
          $this->getCacheTags(),
          $this->cacheForever
        );
      }

      return $invoker;
    } catch (\Throwable $exception) {
      // Fire failed event on exception
      $this->fireEvent('filterable.failed', [
        'exception' => $exception,
        'filterable' => $this,
      ]);

      // Re-throw the exception after firing the event
      throw $exception;
    } finally {
      // Always fire finished event
      $this->fireEvent('filterable.finished', [
        'filterable' => $this,
      ]);
    }
  }

  /**
   * Finalize the query builder after all filters have been applied.
   *
   * @param Builder $builder
   * @return Builder
   */
  protected function finally(Builder $builder): Builder
  {
    // Custom finalization logic can be added here
    return $builder;
  }

  /**
   * Initial processing of the query builder before applying filters.
   *
   * @param Builder $builder
   * @return Builder
   */
  protected function initially(Builder $builder): Builder
  {
    // Custom initial logic can be added here
    return $builder;
  }

  /**
   * Create and return a new Filterable instance after applying the given callback.
   *
   * @param callable \Closure(static): void  $callback  A callback that receives the instance for configuration.
   * @return static
   */
  public static function tap(callable $callback, $instance = null): static
  {
    $instance = $instance ?: new static;
    $callback($instance);
    return $instance;
  }

  /**
   * Add a sorting callback for a specific filterable.
   *
   * @param string|array $filterable
   * @param callable $callback
   * @return void
   */
  public static function addSorting(string|array $filterable, callable|string|Invokable $callback, Request|null $request = null): void
  {
    if (is_string($filterable)) {
      $filterable = [$filterable];
    }

    foreach ($filterable as $filter) {
      if (is_string($callback) && class_exists($callback) && is_subclass_of($callback, Invokable::class)) {
        $callback = app($callback, ['request' => $request ?: app('request')]);
        return;
      }

      if (!is_callable($callback) && !$callback instanceof Invokable) {
        throw new \InvalidArgumentException('The sorting callback must be a callable or an instance of ' . Invokable::class);
      }

      $request = $request ?: app('request');

      self::$sorters[$filter] = $callback(new Sorter($request), $request);
    }
  }

  /**
   * Define sorting rules for the current filterable instance.
   *
   * @param callable $sorting
   * @return static
   */
  public function sorting(callable|string|Invokable $sorting): static
  {
    static::addSorting(static::class, $sorting);
    return $this;
  }

  /**
   * Get sorting rules for a Filterable class.
   *
   * @param string $filterClass
   * @return Sortable|null
   */
  public static function getSorting(string $filterClass): ?Sortable
  {
    return static::$sorters[$filterClass] ?? null;
  }

  /**
   * Should return Query Builder instance when invoke `@apply`
   * @return static
   */
  public function shouldReturnQueryBuilder()
  {
    $this->shouldReturnQueryBuilder = true;

    return $this;
  }

  /**
   * Alias name for @apply method.
   * @param \Illuminate\Contracts\Database\Eloquent\Builder|null $builder
   * @return Invoker|Builder
   */
  public function filter(Builder|null $builder = null): Invoker|Builder
  {
    return $this->apply($builder);
  }

  /**
   * Get all aliases.
   * @return array
   */
  public static function aliases(array $aliases)
  {
    self::$aliases = $aliases;

    return self::$aliases;
  }

  /**
   * Initialize query builder instance.
   * @param \Illuminate\Contracts\Database\Eloquent\Builder|null $builder
   * @throws \Kettasoft\Filterable\Exceptions\MissingBuilderException
   */
  private function initQueryBuilderInstance($builder = null)
  {
    $resolvedBuilder = match (true) {
      $builder instanceof Builder => $builder,
      $this->context->hasBuilder() => $this->context->getBuilder(),
      $this->isModel($this->model) => $this->model::query(),
      default => throw new MissingBuilderException
    };

    $this->context->setBuilder($resolvedBuilder);

    return $resolvedBuilder;
  }

  /**
   * Set model.
   * @param \Illuminate\Database\Eloquent\Model|string $model
   * @return static
   */
  public function setModel(Model|string $model): static
  {
    $this->model = $model;
    return $this;
  }

  /**
   * Get model.
   * @return Model|string
   */
  public function getModel()
  {
    return $this->model;
  }

  /**
   * Get model instance object.
   * @return Model|object|null
   */
  public function getModelInstance()
  {
    if ($this->model instanceof Model) {
      return $this->model;
    }

    if (is_a($this->model, Model::class, true)) {
      return new $this->model;
    }

    return null;
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
   * Apply a callback conditionally and return a new modified instance.
   * @param bool $condition
   * @param callable(static): void $callback
   * @return static
   * @link https://kettasoft.github.io/filterable/features/conditional-logic
   */
  public function when(bool $condition, callable $callback)
  {
    if ($condition) {
      call_user_func($callback, $this);
    }

    return $this;
  }

  /**
   * Inverse of `when` method.
   * @param bool $condition
   * @param callable(static): void $callback
   * @return static
   * @link https://kettasoft.github.io/filterable/features/conditional-logic
   */
  public function unless(bool $condition, callable $callback)
  {
    return $this->when(!$condition, $callback);
  }

  /**
   * Allow the query to pass through a custom pipeline of pipes (callables).
   *
   * @param array<callable(\Illuminate\Contracts\Database\Eloquent\Builder, static): \Illuminate\Contracts\Database\Eloquent\Builder> $pipes
   * @return static
   * @link https://kettasoft.github.io/filterable/features/through
   */
  public function through(array $pipes): static
  {
    foreach ($pipes as $pipe) {
      if (!is_callable($pipe)) {
        throw new \InvalidArgumentException('All pipes passed to `through` must be callable.');
      }

      $pipe($this->builder, $this);
    }

    return $this;
  }

  /**
   * Override the default engine for this filterable instance.
   * @param Engine|class-string<Engine> $engine
   * @return static
   */
  public function useEngine(Engine|string $engine): static
  {
    $this->engine = EngineManager::generate($engine, $this);

    return $this;
  }

  /**
   * Alias name for {@see useEngine} method.
   * @param Engine|class-string<Engine> $engine
   * @return static
   */
  public function using(Engine|string $engine): static
  {
    return $this->useEngine($engine);
  }

  /**
   * Get current engine.
   * @return Engine
   */
  public function getEngine(): Engine
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
   *
   * Manually sets filter data, optionally merging with existing data.
   * Useful for programmatically applying filters without HTTP request.
   * This is a wrapper method that delegates to the runtime state.
   *
   * @param array $data The filter data to set
   * @param bool $override If true, replaces existing data; if false, merges with existing
   * @return static Returns $this for method chaining
   */
  public function setData(array $data, bool $override = true): static
  {
    $currentData = $this->context->getData();
    $this->context->setData($override ? $data : array_merge($currentData, $data));
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
   * Disable running sanitizers on the filters.
   * @return static
   */
  public function withoutSanitizers(): static
  {
    $this->sanitizers = [];
    $this->sanitizer->setSanitizers([]);

    return $this;
  }

  /**
   * Parse incoming request data.
   * Extracts filter parameters from the HTTP request and stores them in runtime state.
   * @return void
   */
  private function parseIncomingRequestData()
  {
    $this->context->setData([...$this->request->all(), ...$this->request->json()->all()]);
  }

  /**
   * Get current filter data.
   * Returns the filter parameters extracted from the request.
   * If a filter key is set, returns data scoped to that key.
   * This is a wrapper method that delegates to the runtime state.
   * @return mixed The filter data array or scoped data
   */
  public function getData(): mixed
  {
    $data = $this->context->getData();
    return $this->filterKey === null ? $data : ($data[$this->filterKey] ?? $data);
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
    $this->useEngine((new HeaderDrivenEngineSelector($this->request))->resolve());
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
   * Enable Header-driven mode per request.
   * @param mixed $config
   * @return Filterable
   */
  public function withHeaderDrivenMode($config = []): static
  {
    return $this->useEngine((new HeaderDrivenEngineSelector($this->request, array_merge(
      config('filterable.header_driven_mode', []),
      ['enabled' => true],
      $config
    )))->resolve());
  }

  /**
   * Get allowed fields to apply filtering.
   * @return array
   */
  public function getAllowedFields(): array
  {
    return $this->allowedFields;
  }

  /**
   * List of supported SQL operators you want to allow when parsing the expressions.
   * @return array
   */
  public function getAllowedOperators(): array
  {
    return $this->allowedOperators;
  }

  /**
   * Set allowed operators and override global operators.
   * @param array $operators
   * @return static
   */
  public function allowedOperators(array $operators): static
  {
    $this->allowedOperators = $operators;
    return $this;
  }

  /**
   * Define allowed fields to filtering.
   * @param array $fields
   * @return Filterable
   */
  public function setAllowedFields(array $fields, bool $override = false): static
  {
    $this->allowedFields = $override ? $fields : array_merge($this->allowedFields, $fields);
    $this->resources->fields->fill($this->allowedFields);
    return $this;
  }

  /**
   * Enable strict mode in this instance.
   * @return Filterable
   */
  public function strict(): static
  {
    $this->strict = true;
    return $this;
  }

  /**
   * Enable strict mode in this instance.
   * @return Filterable
   */
  public function permissive(): static
  {
    $this->strict = false;
    return $this;
  }

  /**
   * Check if filter has strict mode.
   * @return mixed
   */
  public function isStrict()
  {
    if (is_bool($this->strict)) {
      return $this->strict;
    }

    return null;
  }

  /**
   * Get columns wrapper.
   * @return array
   */
  public function getFieldsMap(): array
  {
    return $this->fieldsMap;
  }

  /**
   * Set fields wrapper.
   * @param array $fields
   * @param bool $override
   * @return static
   */
  public function setFieldsMap($fields, bool $override = true): static
  {
    $this->fieldsMap = $override ? $fields : array_merge($this->fieldsMap, $fields);
    return $this;
  }

  /**
   * Get registered filter builder.
   * Returns the Eloquent query builder that filters are being applied to.
   * This is a wrapper method that delegates to the runtime state.
   * @return Builder The query builder instance
   */
  public function getBuilder(): Builder
  {
    return $this->context->getBuilder();
  }

  /**
   * Set a new builder.
   * Attaches an Eloquent query builder to this filterable instance.
   * This is a wrapper method that delegates to the runtime state.
   * @param Builder $builder The query builder to attach
   * @return static Returns $this for method chaining
   */
  public function setBuilder(Builder $builder): static
  {
    $this->context->setBuilder($builder);
    return $this;
  }

  /**
   * Auto-detect filterable fields from model fillable attributes.
   * @param bool $override To override current fields
   * @return static
   */
  public function autoSetAllowedFieldsFromModel(bool $override = false): static
  {
    $fillable = $this->context->getBuilder()->getModel()->getFillable();
    $this->allowedFields = $override ? $fillable : array_merge($this->allowedFields, $fillable);

    return $this;
  }

  /**
   * Get the SQL representation of the filtered query.
   * @param \Illuminate\Contracts\Database\Eloquent\Builder|null $builder
   * @param mixed $withBindings
   * @return string
   */
  public function toSql(Builder|null $builder = null, $withBindings = false): string
  {
    $builder = $this->apply($builder ?? $this->builder);

    return $withBindings ? $builder->toRawSql() : $builder->toSql();
  }

  /**
   * Get exception handler instance.
   *
   * @return ExceptionHandlerInterface
   */
  public function getExceptionHandler(): ExceptionHandlerInterface
  {
    $config = config('filterable.exceptions');
    $engineOverrides = config("filterable.engines.{$this->engine->getEngineName()}.exceptions", []);

    $merged = array_merge($config, $engineOverrides);

    return app($merged['handler']);
  }

  /**
   * Dynamically retrieve attributes.
   * Provides backward compatibility for accessing runtime state properties
   * (builder, data, applied, skipped) as if they were direct properties.
   * @param mixed $property The property name
   * @return mixed The property value
   */
  public function __get($property): mixed
  {
    // Backward compatibility: map state properties to state object
    if ($property === 'builder') {
      return $this->context->getBuilder();
    }

    if ($property === 'data') {
      return $this->context->getData();
    }

    if ($property === 'applied') {
      return $this->context->getApplied();
    }

    if ($property === 'skipped') {
      return $this->context->getSkipped();
    }

    if (property_exists($this, $property)) {
      return $this->{$property};
    }

    return $this->request->{$this->requestSource}($property);
  }

  /**
   * Handle dynamic method calls to the builder.
   * @param string $method
   * @param array $parameters
   * @return mixed
   */
  public function __call($method, $parameters)
  {
    if (\in_array($method, $this->executors)) {
      return $this->apply()->{$method}(...$parameters);
    }

    return $this->handleFluentReturn($method, $parameters);
  }
}
