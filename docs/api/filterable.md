# Filterable

The Filterable class is the core entry point for building filterable Eloquent queries. It wires together request parsing, engines, sanitization, authorization, validation, relation-aware filtering, and optional sorting in a single expressive API.

---

### Overview

Filterable wraps an Eloquent Builder and applies filters coming from the HTTP request (query, input, or JSON body) or from manually injected data. It supports multiple engines (expression, tree, ruleset, etc.), header-driven engine selection, sanitization, validation, authorization hooks, relation filtering, and custom sorting.

```php
use Kettasoft\Filterable\Filterable;
use App\Models\User;

$invoker = Filterable::create()
    ->setModel(User::class)
    ->setAllowedFields(['name', 'email', 'created_at'])
    ->apply();

// Execute terminal operations through Invoker (paginate/get/first/etc.)
$users = $invoker->paginate();
```

---

### Key Features

-   Engine-agnostic filtering (auto-selected via header or manual)
-   Request-source aware (query, input, json)
-   Built-in authorization and validation pipes
-   Sanitization pipeline (configurable and disable-able)
-   Field whitelisting and operator allow-listing
-   Relation-aware filtering
-   Sorting definition per Filterable class
-   Supports returning Builder directly when needed

---

### Properties

| Property             | Type                                              | Description                                     |
| -------------------- | ------------------------------------------------- | ----------------------------------------------- |
| `$engine`            | `\Kettasoft\Filterable\Engines\Foundation\Engine` | The active filtering engine.                    |
| `$resources`         | `\Kettasoft\Filterable\Foundation\Resources`      | Shared resources/settings bag.                  |
| `$filters`           | `array`                                           | Declared filter attributes in a filter class.   |
| `$ignoreEmptyValues` | `bool`                                            | If true, skips empty/null values.               |
| `$request`           | `\Illuminate\Http\Request`                        | Current HTTP request.                           |
| `$requestSource`     | `string \| null`                                  | Source for inputs: `query`, `input`, or `json`. |
| `$builder`           | `\Illuminate\Database\Eloquent\Builder`           | The working Eloquent builder.                   |
| `$sanitizers`        | `array`                                           | Registered sanitizers.                          |
| `$data`              | `array`                                           | Merged request data (query + json).             |
| `$allowedFields`     | `array`                                           | Whitelisted fields for filtering.               |
| `$allowedOperators`  | `array`                                           | Allowed SQL operators for expression parsing.   |
| `$strict`            | `bool \| null`                                    | Explicit strict or permissive mode.             |
| `$fieldsMap`         | `array`                                           | Field-name mapping (input -> column).           |
| `$model`             | `\Illuminate\Database\Eloquent\Model \| string`   | The target model or class-string.               |
| `$sanitizer`         | `\Kettasoft\Filterable\Sanitization\Sanitizer`    | Active sanitizer instance.                      |
| `static $aliases`    | `\Illuminate\Support\Collection`                  | Registered aliases.                             |
| `static $sorters`    | `array<string, callable<Sorter>>`                 | Per-filterable sorting definitions.             |

---

### Static Constructors

#### `static create(Request|null $request = null): static`

Create a new Filterable instance using the provided request or the current container request.

```php
$filterable = Filterable::create();
```

#### `static withRequest(Request $request): static`

Create a new instance bound to a specific request.

```php
$filterable = Filterable::withRequest($customRequest);
```

---

### Core Methods

#### `apply(Builder|null $builder = null): Invoker|Builder`

Apply all filters, validation, and authorization. Returns an Invoker by default, or a Builder when `shouldReturnQueryBuilder()` is used or the instance implements `ShouldReturnQueryBuilder`.

```php
$invoker = Filterable::create()->setModel(User::class)->apply();
$users = $invoker->get();
```

#### `filter(Builder|null $builder = null): Invoker|Builder`

Alias of `apply()`.

#### `shouldReturnQueryBuilder(): static`

Force `apply()` to return the Eloquent Builder instead of the Invoker wrapper.

```php
$query = Filterable::create()->setModel(User::class)->shouldReturnQueryBuilder()->apply();
```

---

### Sorting

Static, per-class sorting definitions are supported via a `Sorter` builder.

#### `static addSorting(string|array $filterable, callable|string|Invokable $callback, Request|null $request = null): void`

Register sorting for a given filterable class (or many). The callback receives `Sorter $sorter, Request $request` and must return a `Sortable` implementation.

```php
use Kettasoft\Filterable\Foundation\Sorting\Sorter;

Filterable::addSorting(UserFilter::class, function (Sorter $sorter) {
    return $sorter
        ->allow(['name', 'email', 'created_at'])
        ->default('created_at', 'desc');
});
```

#### `sorting(callable|string|Invokable $sorting): static`

Define sorting for the current instance (equivalent to calling `addSorting(static::class, ...)`).

#### `static getSorting(string $filterClass): ?Sortable`

Retrieve the registered `Sortable` for the given Filterable class.

---

### Model & Builder

#### `setModel(Model|string $model): static`

Set the model (instance or class-string). Required if no builder is provided to `apply()`.

#### `getModel(): Model|string`

Return the configured model or class-string.

#### `getModelInstance(): Model|object|null`

Return a new model instance (if a class-string was set) or the provided instance.

#### `getBuilder(): Builder`

Get the working Eloquent builder (set internally after `apply()` initialization).

#### `setBuilder(Builder $builder): static`

Set a custom builder to operate on.

---

### Request & Data

#### `getRequest(): Request`

Get the current HTTP request.

#### `setData(array $data, bool $override = true): static`

Inject manual data for filtering. When `$override` is false, merges with existing data.

#### `getData(): mixed`

Return current working data. If a `filterKey` is set (via traits), returns that subset when available.

#### `setSource(string $source): static`

Set the request source: `query`, `input`, or `json`. Throws when unsupported.

#### `get(string $key): mixed`

Retrieve an input value from the configured source.

---

### Engines

#### `useEngine(Engine|string $engine): static`

Override the engine for this instance. Accepts an engine instance or a supported engine key.

#### `getEngine(): Engine`

Return the current engine instance.

#### `withHeaderDrivenMode(mixed $config = []): static`

Enable header-driven engine selection for this instance. Merges provided options over `filterable.header_driven_mode` config.

---

### Sanitization

#### `setSanitizers(array $sanitizers, bool $override = true): static`

Set or merge active sanitizers for this instance.

#### `withoutSanitizers(): static`

Disable sanitization for this instance.

#### `getSanitizerInstance(): Sanitizer`

Access the sanitizer instance.

---

### Field & Operator Control

#### `setAllowedFields(array $fields, bool $override = false): static`

Define allowed fields for filtering (optionally merged).

#### `getAllowedFields(): array`

Return the allowed fields.

#### `allowedOperators(array $operators): static`

Override globally allowed operators for this instance.

#### `getAllowedOperators(): array`

Return allowed operators.

#### `setFieldsMap($fields, bool $override = true): static`

Map input field names to database columns.

#### `getFieldsMap(): array`

Return the current field mapping.

#### `autoSetAllowedFieldsFromModel(bool $override = false): static`

Populate allowed fields from the model's fillable attributes.

---

### Modes & Options

#### `ignoreEmptyValues(): static`

Skip empty or null values when building filters.

#### `hasIgnoredEmptyValues(): bool`

Check whether empty values are currently ignored.

#### `strict(): static`

Enable strict mode for this instance.

#### `permissive(): static`

Disable strict mode for this instance.

#### `isStrict(): mixed`

Return the strict flag (`true`/`false`) or `null` when not explicitly set.

---

### Flow Control

#### `when(bool $condition, callable $callback): static`

Apply a callback to the instance conditionally.

```php
Filterable::create()
  ->when($isAdmin, fn ($f) => $f->setAllowedFields(['*']))
  ->apply();
```

#### `unless(bool $condition, callable $callback): static`

Apply a callback to the instance when the condition is false.

```php
Filterable::create()
  ->unless($isAdmin, fn ($f) => $f->setAllowedFields(['name', 'email']))
  ->apply();
```

#### `through(array $pipes): static`

Allow the query to pass through custom callables, each receiving `(Builder $builder, Filterable $filterable)`.

```php
Filterable::create()
  ->setModel(User::class)
  ->apply()
  ->through([
    function ($builder, $filterable) {
      return $builder->where('active', true);
    },
  ]);
```

#### `tap(callable $callback): static`

Invoke a callback with the current instance for side effects.

```php
Filterable::tap(function (Filterable $filterable) {
    $filterable->setAllowedFields(['name']);
    logger()->info('Current allowed fields: ', $filterable->getAllowedFields());
});
```

---

### SQL Export

#### `toSql(Builder|null $builder = null, mixed $withBindings = false): string`

Return the SQL of the filtered query. When `$withBindings` is true, returns the SQL with bindings interpolated (using Eloquent's `toRawSql()` if available).

```php
$sql = Filterable::create()
  ->setModel(User::class)
  ->shouldReturnQueryBuilder()
  ->toSql();
```

---

### Aliases

#### `static aliases(array $aliases): Collection`

Set and return class aliases as a collection.

---

### Magic Access

#### `__get($property): mixed`

Proxy missing properties to the request source via `get($property)` when not present on the instance.

---

### Exceptions

-   `MissingBuilderException` — Thrown when no builder or model is available to initialize the query builder.
-   `RequestSourceIsNotSupportedException` — Thrown when an unsupported request source is used.

---

### Example: End-to-End

```php
use Kettasoft\Filterable\Filterable;
use App\Models\Post;

$posts = Filterable::create()
    ->setModel(Post::class)
    ->setAllowedFields(['title', 'status', 'created_at'])
    ->allowedOperators(['=', '!=', 'like', 'in', 'between'])
    ->ignoreEmptyValues()
    ->strict()
    ->sorting(function ($sorter) {
        return $sorter->allow(['created_at', 'title'])->default('created_at', 'desc');
    })
    ->apply()
    ->paginate(15);
```

---

### Notes

-   The engine can be selected via header using `withHeaderDrivenMode()` or automatically via `HeaderDrivenEngineSelector`.
-   `apply()` returns an `Invoker` by default to encourage fluent terminal operations; use `shouldReturnQueryBuilder()` to work with the raw Builder.
-   Prefer `setAllowedFields()` and `allowedOperators()` to constrain user input and reduce attack surface.
