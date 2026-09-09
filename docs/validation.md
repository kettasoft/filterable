# Request Validation

Filterable can validate incoming data before an engine applies filters. Define standard Laravel validation rules in the filter class:

```php
use Kettasoft\Filterable\Filterable;

class PostFilter extends Filterable
{
    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', 'in:pending,active'],
            'title' => ['sometimes', 'string', 'max:100'],
        ];
    }
}
```

Validation is part of the shared Filterable pipeline and therefore applies to all built-in engines:

- [Invokable](/engines/invokable/)
- [Ruleset](/engines/rule-set)
- [Expression](/engines/expression)
- [Tree](/engines/tree)

## Validation order

Class-level validation runs before payload sanitization and query application:

```text
authorize → validate request → sanitize payload → filter
```

Validation rules therefore inspect the incoming request value. Sanitizers may transform that value afterward, immediately before the engine applies its condition.

## Handling failures

When validation fails, Laravel throws an `Illuminate\Validation\ValidationException`. API responses use Laravel's normal validation error format:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "status": ["The selected status is invalid."]
  }
}
```

Use `sometimes` for optional filters. Use `required` only when the endpoint must receive that filter on every request.

See [Sanitization](/sanitization) for preparing payload values before query application and [Authorization](/authorization) for controlling access to a filter.
