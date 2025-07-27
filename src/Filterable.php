<?php

namespace Kettasoft\Filterable;

use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Database\Eloquent\Builder;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Foundation\Resources;
use Kettasoft\Filterable\Contracts\Validatable;
use Kettasoft\Filterable\Contracts\Authorizable;
use Kettasoft\Filterable\Sanitization\Sanitizer;
use Kettasoft\Filterable\Engines\Foundation\Engine;
use Kettasoft\Filterable\Contracts\FilterableContext;
use Kettasoft\Filterable\Engines\Factory\EngineManager;
use Kettasoft\Filterable\Foundation\FilterableSettings;
use Kettasoft\Filterable\Traits\InteractsWithFilterKey;
use Kettasoft\Filterable\Traits\InteractsWithValidation;
use Kettasoft\Filterable\Exceptions\MissingBuilderException;
use Kettasoft\Filterable\Traits\InteractsWithMethodMentoring;
use Kettasoft\Filterable\Engines\Foundation\Executors\Executer;
use Kettasoft\Filterable\Traits\InteractsWithRelationsFiltering;
use Kettasoft\Filterable\Traits\InteractsWithFilterAuthorization;
use Kettasoft\Filterable\HttpIntegration\HeaderDrivenEngineSelector;
use Kettasoft\Filterable\Exceptions\RequestSourceIsNotSupportedException;

class Filterable implements FilterableContext, Authorizable, Validatable
{
  use InteractsWithFilterKey,
    InteractsWithMethodMentoring,
    InteractsWithFilterAuthorization,
    InteractsWithValidation,
    InteractsWithRelationsFiltering,
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
   * Specify which fields are allowed to be filtered.
   * @var array
   */
  protected $allowdFields = [];

  /**
   * List of supported SQL operators you want to allow when parsing the expressions.
   * @var array
   */
  protected $allowdOperators = [];

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
    $this->resources = new Resources($this->settings());
    $this->resolveEngine();
    $this->parseIncommingRequestData();
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
      $this->allowdFields,
      $this->relations,
      $this->allowdOperators,
      $this->sanitizers,
      $this->fieldsMap
    );
  }

  /**
   * Apply all filters.
   *
   * @param Builder $builder
   * @return Builder
   */
  public function apply(Builder|null $builder = null): Builder
  {
    App::make(Pipeline::class)->send($this)->through([
      \Kettasoft\Filterable\Pipes\FilterAuthorizationPipe::class,
      \Kettasoft\Filterable\Pipes\ValidateBeforeFilteringPipe::class
    ])->thenReturn();

    $builder = $this->initQueryBuilderInstance($builder);

    $this->builder = $builder;

    return Executer::execute($this->engine, $builder);
  }

  /**
   * Alias name for @apply method.
   * @param \Illuminate\Database\Eloquent\Builder|null $builder
   * @return Builder
   */
  public function filter(Builder|null $builder = null): Builder
  {
    return $this->apply($builder);
  }

  /**
   * Initialize query builder instance.
   * @param \Illuminate\Database\Eloquent\Builder|null $builder
   * @throws \Kettasoft\Filterable\Exceptions\MissingBuilderException
   */
  private function initQueryBuilderInstance(Builder|null $builder = null)
  {
    if ($builder) return $builder;

    if ($this->model instanceof Model) {
      return $this->model->query();
    }

    if (is_string($this->model) && class_exists($this->model) && is_subclass_of($this->model, Model::class)) {
      return $this->model::query();
    }

    throw new MissingBuilderException;
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

    if (is_string($this->model) && class_exists($this->model) && is_subclass_of($this->model, Model::class)) {
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
   * Use filter engine.
   * @param \Kettasoft\Filterable\Engines\Foundation\Engine|string $engine
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
    return $this->filterKey === null ? $this->data : $this->data[$this->filterKey] ?? $this->data;
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
    $this->useEngin((new HeaderDrivenEngineSelector($this->request))->resolve());
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
    return $this->useEngin((new HeaderDrivenEngineSelector($this->request, array_merge(
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
    return $this->allowdFields;
  }

  /**
   * List of supported SQL operators you want to allow when parsing the expressions.
   * @return array
   */
  public function getAllowedOperators(): array
  {
    return $this->allowdOperators;
  }

  /**
   * Set allowed operators and override global operators.
   * @param array $operators
   * @return static
   */
  public function allowdOperators(array $operators): static
  {
    $this->allowdOperators = $operators;
    return $this;
  }

  /**
   * Define allowed fields to filtering.
   * @param array $fields
   * @return Filterable
   */
  public function setAllowedFields(array $fields, bool $override = false): static
  {
    $this->allowdFields = $override ? $fields : array_merge($this->allowdFields, $fields);
    $this->resources->fields->fill($this->allowdFields);
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
   * @return Builder
   */
  public function getBuilder(): Builder
  {
    return $this->builder;
  }

  /**
   * Set a new builder.
   * @param Builder $builder
   * @return static
   */
  public function setBuilder(Builder $builder): static
  {
    $this->builder = $builder;
    return $this;
  }

  /**
   * Auto-detect filterable fields from model fillable attributes.
   * @param bool $override To override current fields
   * @return static
   */
  public function autoSetAllowedFieldsFromModel(bool $override = false): static
  {
    $fillable = $this->builder->getModel()->getFillable();
    $this->allowdFields = $override ? $fillable : array_merge($this->allowdFields, $fillable);

    return $this;
  }

  /**
   * Get the SQL representation of the filtered query.
   * @param \Illuminate\Database\Eloquent\Builder|null $builder
   * @param mixed $withBindings
   * @return string
   */
  public function toSql(Builder|null $builder = null, $withBindings = false): string
  {
    $builder = $this->apply($builder ?? $this->builder);

    return $withBindings ? $builder->toRawSql() : $builder->toSql();
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

  /**
   * Dynamically retrieve attributes from the request.
   * @param mixed $property
   * @return mixed
   */
  public function __get($property): mixed
  {
    if (property_exists($this, $property)) {
      return $this->{$property};
    }

    return $this->get($property);
  }
}
