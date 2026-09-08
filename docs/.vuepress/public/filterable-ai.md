# Kettasoft Filterable: AI Coding Instructions

Use these instructions when creating, reviewing, or changing filtering code that uses
`kettasoft/filterable`. Project-specific instructions and the installed package version
take precedence over this file.

## Before changing code

- Inspect `composer.lock` or `composer show kettasoft/filterable` to confirm the installed version.
- Read the project's published `config/filterable.php`; engine and exception behavior can be customized.
- Reuse the project's existing filter classes, naming, request shape, and selected engine.
- Do not add a new filtering abstraction when the package already provides the required behavior.

## Supported environment and setup

- Use PHP 8.2 or newer and Laravel 10, 11, or 12.
- Install with `composer require kettasoft/filterable`.
- Prefer `php artisan filterable:setup` for package setup.
- Generate a filter with `php artisan filterable:make-filter PostFilter --filters=title,status`.
- If manual provider registration is necessary, use `bootstrap/providers.php` on Laravel 11/12 or
  `config/app.php` on Laravel 10.

## Recommended model integration

Use `InteractsWithFilterable` and bind the filter class when a model has a default filter:

```php
use App\Http\Filters\PostFilter;
use Kettasoft\Filterable\Traits\InteractsWithFilterable;

class Post extends Model
{
    use InteractsWithFilterable;

    protected $filterable = PostFilter::class;
}
```

Then build queries through the model:

```php
$posts = Post::filter()->paginate();
$posts = Post::filter(PostFilter::class)->get();
```

For an explicit model and request, use:

```php
$posts = Filterable::for(Post::class, $request)
    ->using('ruleset')
    ->setAllowedFields(['status', 'title'])
    ->paginate();
```

Terminal builder methods such as `get()`, `first()`, and `paginate()` apply the filters
automatically. Do not add repeated `apply()` calls unless the code specifically needs the
applied Eloquent builder before a terminal operation.

## Choose the engine from the request contract

Do not change engines without checking the client request shape.

| Engine       | Use it for                                 | Typical input                              |
| ------------ | ------------------------------------------ | ------------------------------------------ |
| `invokable`  | Custom method-per-field behavior           | `?status=active&title=laravel`             |
| `ruleset`    | Field/operator/value API filters           | `?filter[views][gte]=100`                  |
| `expression` | Ruleset filters including nested relations | `?filter[author.profile.name][like]=ahmed` |
| `tree`       | Nested AND/OR condition trees              | JSON under `filter`                        |

Select an engine with `->using('invokable')`, `->using('ruleset')`,
`->using('expression')`, or `->using('tree')`.

## Invokable filter methods

Declare allowed request keys in `$filters`. Each accepted key maps automatically to its
filter method. Type-hint `Payload`, not a raw value:

```php
namespace App\Http\Filters;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Support\Payload;

class PostFilter extends Filterable
{
    protected $filters = ['status', 'title'];

    protected function status(Payload $payload): Builder
    {
        return $this->builder->where('status', $payload->value);
    }

    protected function title(Payload $payload): Builder
    {
        return $this->builder->where('title', 'like', $payload->asLike('both'));
    }
}
```

A payload exposes the requested `field`, resolved `operator`, current `value`, and
original `rawValue`. Use payload helpers and attributes for transformations instead of
re-parsing request data inside each method.

The filter class discovers eligible apply methods automatically. Do not introduce an
`autoApplyMethods` list or manually dispatch request keys.

## Request access and manual data

- Read a value from the configured request source with `$this->getFromRequest('status')`.
- Use `setData([...])` for programmatic filter input.
- Do not use an `input()` accessor on `Filterable`; it is not the current API.
- Do not confuse request access with the terminal query-builder method `get()`.

## Validation, sanitization, and authorization

- Define Laravel validation rules in `rules(): array`, not in a `$rules` property.
- Treat all filter input as untrusted.
- Prefer explicit field and operator allowlists with `setAllowedFields()` and
  `allowedOperators()`.
- Preserve the package lifecycle: authorization, request validation, payload
  sanitization, then filter application.
- Use strict mode when invalid fields or operators must fail visibly; otherwise confirm
  that skipping invalid conditions is the intended API behavior.

## Relational fields

Allow every intended relation explicitly:

```php
Filterable::for(Post::class, $request)
    ->using('expression')
    ->setAllowedFields(['status'])
    ->allowRelations([
        'author.profile' => ['name'],
        'tags' => ['name'],
    ])
    ->paginate();
```

The package accepts supported relational input in nested or dot notation. Do not expose
arbitrary relation fields or use a wildcard unless the application deliberately permits it.

## Operator strategies

Ruleset, Expression, and Tree share the operator strategy pipeline. For a custom operator:

1. Implement `Kettasoft\Filterable\Engines\Foundation\Operators\Contracts\Operator`.
2. Implement `apply(Builder $builder, Payload $payload): Builder`.
3. Register the resolved name under `filterable.operator_strategies`.
4. Add its public alias and resolved name to the selected engine's `allowed_operators` map.

Do not add operator-specific conditionals to each engine; one strategy should support both
direct and relational fields.

## APIs to avoid

- Do not use `Clause`, `ClauseFactory`, or the removed Clause pipeline. Use `Payload`.
- Do not use the deprecated `HasFilterable` trait in new code. Use `InteractsWithFilterable`.
- Do not add `autoApplyMethods`; eligible invokable methods are discovered automatically.
- Do not use `Filterable::input()` or treat `Filterable::get()` as request access.

## Verification checklist

- Confirm the selected engine matches the documented request shape.
- Test empty, null, scalar, array, invalid-field, invalid-operator, and unauthorized input
  where relevant.
- Cover direct and relational fields when relations are enabled.
- Assert both query results and strict/permissive error behavior.
- Run the focused test first, then `php vendor/bin/phpunit`.
- If diagnosing a possible infinite loop, run the suspected test with a hard timeout, for
  example `timeout --signal=KILL 20s php vendor/bin/phpunit --filter TestName`.

Official documentation: https://kettasoft.github.io/filterable/
