<p align="center"><img src="https://github.com/kettasoft/filterable/blob/docs/images/logo.png" width="180" alt="Filterable Logo" /></p>
<h1 align="center">Filterable</h1>
<p align="center">A powerful and flexible Laravel package for advanced, clean, and scalable filtering of Eloquent models using multiple customizable engines.</p><p align="center">
<a href="https://packagist.org/packages/kettasoft/filterable"><img src="https://github.com/kettasoft/filterable/actions/workflows/php.yml/badge.svg?style=flat-square" alt="Tests"></a>
<a href="https://packagist.org/packages/kettasoft/filterable"><img src="http://poser.pugx.org/kettasoft/filterable/v?style=flat-square" alt="Latest Version on Packagist"></a>
<a href="https://packagist.org/packages/kettasoft/filterable"><img src="https://img.shields.io/packagist/dt/kettasoft/filterable?style=flat-square" alt="Total Downloads"></a>
<a href="https://github.com/kettasoft/filterable/blob/master/LICENSE"><img src="https://poser.pugx.org/kettasoft/filterable/license?style=flat-square" alt="License"></a></p>

## Why Filterable?

Most filtering packages give you one approach and expect you to fit your problem around it. Filterable works the other way — you pick the **engine** that matches how your frontend sends data, and the package handles the rest.

It ships with four production-ready engines, a full caching system, per-filter authorization, validation, sanitization, sorting, a CLI, and an event system — all while keeping your controllers clean and your filter logic organized and testable.

---

## Installation

```bash
composer require kettasoft/filterable
```

```bash
php artisan vendor:publish --provider="Kettasoft\Filterable\Providers\FilterableServiceProvider" --tag="config"
```

Add the following line to the providers array in config/app.php or bootstrap/providers.php:

```php
'providers' => [
    ...

    Kettasoft\Filterable\Providers\FilterableServiceProvider::class,
];
```

---

## Quick Start

**1. Create a filter class**

```bash
php artisan filterable:make-filter PostFilter --filters=title,status
```

**2. Define your filters**

```php
namespace App\Http\Filters;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Support\Payload;

class PostFilter extends Filterable
{
    protected $filters = ['status', 'title'];

    protected function title(Payload $payload)
    {
        return $this->builder->where('title', 'like', $payload->asLike('both'));
    }

    protected function status(Payload $payload)
    {
        return $this->builder->where('status', $payload->value);
    }
}
```

**3. Apply in your controller**

```php
$posts = Post::filter(PostFilter::class)->paginate();
```

**4. Or bind the filter directly to the model**

```php
class Post extends Model
{
    use HasFilterable;

    protected $filterable = PostFilter::class;
}

// Now just:
$posts = Post::filter()->paginate();
```

---

## Choosing an Engine

Each engine is designed for a different filtering style. Pick the one that fits your use case — or mix and match across different models.

| Engine         | Best For                                           | Example Request                                       |
| -------------- | -------------------------------------------------- | ----------------------------------------------------- |
| **Invokable**  | Custom logic per field, method-per-filter pattern  | `?status=active&title=laravel`                        |
| **Ruleset**    | Clean key/operator/value API queries               | `?filter[title][like]=laravel&filter[views][gte]=100` |
| **Expression** | Ruleset-style + filtering through nested relations | `?filter[author.profile.name][like]=ahmed`            |
| **Tree**       | Complex AND/OR nested logic sent as JSON           | `{ "and": [{ "field": "status", ... }] }`             |

### Invokable Engine

Map request keys to methods automatically. Add PHP 8 annotations for per-method sanitization, casting, validation, and authorization with zero boilerplate.

```php
class PostFilter extends Filterable
{
    protected $filters = ['status', 'created_at'];

    #[Cast('integer')]
    #[DefaultValue(1)]
    protected function status(Payload $payload) { ... }

    #[SkipIf('auth()->guest()')]
    #[Between(min: '2020-01-01', max: 'now')]
    protected function created_at(Payload $payload) { ... }
}
```

Available annotations: `#[Authorize]` `#[SkipIf]` `#[Cast]` `#[Sanitize]` `#[Trim]` `#[DefaultValue]` `#[MapValue]` `#[Explode]` `#[Required]` `#[In]` `#[Between]` `#[Regex]` `#[Scope]`

### Ruleset Engine

Flat field-operator-value format, ideal for REST APIs where the frontend controls which operator to use.

```
GET /posts?filter[status]=published
GET /posts?filter[title][like]=%laravel%
GET /posts?filter[views][gte]=100
GET /posts?filter[id][in][]=1&filter[id][in][]=2
```

Supported operators: `eq` `neq` `gt` `gte` `lt` `lte` `like` `nlike` `in` `between`

### Expression Engine

Everything Ruleset does, plus filtering through deep Eloquent relationships using dot notation.

```
GET /posts?filter[author.profile.name][like]=ahmed
```

```php
Filterable::create()
    ->useEngine('expression')
    ->allowedFields(['status', 'title'])
    ->allowRelations(['author.profile' => ['name']])
    ->paginate();
```

### Tree Engine

Send a nested AND/OR JSON tree — the engine recursively translates it into Eloquent `where` / `orWhere` groups.

```json
{
    "filter": {
        "and": [
            { "field": "status", "operator": "eq", "value": "active" },
            {
                "or": [
                    { "field": "age", "operator": "gt", "value": 25 },
                    { "field": "city", "operator": "eq", "value": "Cairo" }
                ]
            }
        ]
    }
}
```

Supports depth limiting, strict operator whitelisting, and normalized field keys.

---

## Features

### Caching

A complete caching system built into the filter pipeline — not bolted on after the fact.

```php
// Cache for 1 hour
Post::filter()->cache(3600)->get();

// User-scoped cache (each user gets their own)
Post::filter()->cache(1800)->scopeByUser()->get();

// Tenant-isolated cache
Product::filter()->cache(3600)->scopeByTenant(tenant()->id)->get();

// Conditional cache
Model::filter()->cacheWhen(!auth()->user()->isAdmin(), 3600)->get();

// Tagged cache with easy invalidation
Post::filter()->cache(3600)->cacheTags(['posts', 'content'])->get();
Post::flushCacheByTagsStatic(['posts']);

// Reusable profiles defined in config
Report::filter()->cacheProfile('heavy_reports')->get();
```

Auto-invalidation: configure models and tags in `config/filterable.php` and caches are cleared automatically on create/update/delete.

### Authorization

Protect entire filter classes based on roles or permissions.

```php
class AdminFilter extends Filterable
{
    public function authorize(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }
}
```

Per-method authorization is also available via the `#[Authorize]` annotation in the Invokable engine.

### Validation & Sanitization

Validation rules and sanitizers are defined directly on the filter class —
input is cleaned and validated before any filtering logic runs.

**Validation** uses Laravel's native rules format via a `$rules` property:

```php
class PostFilter extends Filterable
{
    protected $rules = [
        'status' => ['required', 'string', 'in:active,pending,archived'],
        'title'  => ['sometimes', 'string', 'max:32'],
    ];
}
```

If validation fails, a `ValidationException` is thrown automatically —
no extra handling needed in your controller.

**Sanitization** runs _before_ validation, via dedicated sanitizer classes:

```php
class PostFilter extends Filterable
{
    protected $sanitizers = [
        TrimSanitizer::class,        // global — applies to all fields
        'title' => [
            StripTagsSanitizer::class,
            CapitalizeSanitizer::class,
        ],
    ];
}
```

A sanitizer is a simple class implementing the `Sanitizable` interface:

```php
class TrimSanitizer implements Sanitizable
{
    public function sanitize(mixed $value): mixed
    {
        return is_string($value) ? trim($value) : $value;
    }
}
```

The execution order is always: **sanitize → validate → filter**.

### Sorting

Built-in sorting support with allowed-field whitelisting.

```php
class PostFilter extends Filterable
{
    protected $sortable = ['created_at', 'views', 'title'];
}

// GET /posts?sort=-created_at (descending)
// GET /posts?sort=views       (ascending)
```

### Event System

Hook into the filter lifecycle to add logging, metrics, or custom behavior.

```php
// Fired before filters are applied
Event::listen(FilterApplying::class, fn($e) => Log::info('Filtering '.$e->model));

// Fired after filters are applied
Event::listen(FilterApplied::class, fn($e) => $metrics->record($e));
```

### Profile Management & Profiler

Save and reuse filter configurations, and inspect exactly what queries each filter generates.

### Lifecycle Hooks

`initially()` and `finally()` hooks let you modify the query builder before or after filtering runs.

---

## CLI

```bash
# Generate a new filter class with interactive setup
php artisan filterable:setup PostFilter

# Discover and auto-register all filter classes in your app
php artisan filterable:discover

# List all registered filters
php artisan filterable:list

# Test a filter class with a sample data string (key=value pairs)
php artisan filterable:test {filter} --model=User --data="status=active,age=30"

# Inspect a filter class (engines, fields, rules, etc.)
php artisan filterable:inspect PostFilter
```

---

## Requirements

- PHP 8.1+
- Laravel 10.x or higher
- Redis or Memcached recommended for tagged caching

---

## 📚 Documentation

For full documentation, installation, and usage examples, visit: **[kettasoft.github.io/filterable](https://kettasoft.github.io/filterable)**

- [Introduction](https://kettasoft.github.io/filterable/introduction.html)
- [Engines Overview](https://kettasoft.github.io/filterable/engines/invokable/)
- [Caching System](https://kettasoft.github.io/filterable/caching/overview.html)
- [Authorization](https://kettasoft.github.io/filterable/authorization.html)
- [CLI Reference](https://kettasoft.github.io/filterable/cli/setup.html)
- [API Reference](https://kettasoft.github.io/filterable/api/filterable.html)

---

## Contributing

Found a bug or want to add an engine? PRs are welcome — please open an issue first to discuss.

## License

MIT © 2024-present [Kettasoft](https://github.com/kettasoft)
