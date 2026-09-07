# Operator Strategies

Ruleset, Expression, and Tree filters share the same operator pipeline. An operator is first validated against the selected engine's `allowed_operators` map, resolved to its database representation, and then applied by an operator strategy.

## Built-in operators

| Alias | Resolved operator | Behavior |
| --- | --- | --- |
| `eq`, `neq` | `=`, `!=` | Comparison |
| `gt`, `gte`, `lt`, `lte` | `>`, `>=`, `<`, `<=` | Ordered comparison |
| `like`, `nlike` | `like`, `not like` | Pattern comparison |
| `in`, `nin` | `in`, `not in` | Accepts an array or comma-separated value |
| `between`, `nbetween` | `between`, `not between` | Requires exactly two values |
| `null`, `notnull` | `is null`, `is not null` | Does not require a value |

`allowedOperators()` accepts either aliases or resolved values:

```php
$filterable->allowedOperators(['gte', 'in']);
$filterable->allowedOperators(['>=', 'in']);
```

## Custom strategies

A custom strategy implements the `Operator` contract:

```php
use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Engines\Foundation\Operators\Contracts\Operator;

final class ContainsOperator implements Operator
{
    public function apply(Builder $builder, Payload $payload): Builder
    {
        return $builder->where(
            $payload->field,
            'like',
            "%{$payload->value}%"
        );
    }
}
```

Register its public alias and resolved name in the engine, then map that resolved name to the strategy:

```php
// config/filterable.php
'operator_strategies' => [
    'contains' => App\Filtering\Operators\ContainsOperator::class,
],

'engines' => [
    'ruleset' => [
        'allowed_operators' => [
            // ...
            'contains' => 'contains',
        ],
    ],
],
```

Strategies are resolved through Laravel's container, so constructor dependencies can be injected. A configured class must implement `Operator`; invalid definitions fail explicitly instead of silently falling back to equality.

The same strategy is used for direct and relational fields.
