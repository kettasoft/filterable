# Request validation

::: danger Important Note
All filter engines in this package support **automatic request validation**, except for the [**Closure Pipeline Engine**](/engines/closure)
:::

## Overview

When using class-based filters (e.g., in the **[Dynamic Method Engine](/engines/dynamic-methods)**). you may define validation rules directly inside the filter class using `protected $rules` property. These rules follow Laravel's native validation format.

Before any filtering logic runs, the engine will validate incomming request data against the defined rules.

- If validation **passes**, filtering proceeds as expected.
- If validation fails, a <br>
  **`ValidationException`** is thrown and the process stoped.

---

## Supported Engines

| Engine                                       | Validation Support |
| -------------------------------------------- | ------------------ |
| [`Ruelset`](/engines/ruelset)                | **Yes**            |
| [`Dynamic method`](/engines/dynamic-methods) | **Yes**            |
| [`Tree based`](/engines/tree-based)          | **Yes**            |
| [`Closure pipeline`](/engines/closure)       | **No**             |
| Others (if any)                              | **Yes**            |

---

## Example (Dynamic Method Engine)

```php
class PostFilter extends Filterable
{
    /**
     * Registered filters to operate upon.
     *
     * @var array
     */
    protected $filters = [
        'status',
        'title',
    ];

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:pending,active'],
            'title' => ['required', 'string', 'max:32'],
        ];
    }

    /**
     * Filter the query by a given status.
     *
     * @param Payload $payload
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function status($payload)
    {
        return $this->builder->where('status', $payload);
    }

    /**
     * Filter the query by a given title.
     *
     * @param Payload $payload
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function title($payload)
    {
        if ($payload) {
            return $this->builder->where('title', $payload);
        }

        return $this->builder;
    }
}
```

## Error Handling

If the request does not satisfy the rules, the system will return a structured error.

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "status": ["The status field is required."],
    "title": ["The title field is required."]
  }
}
```
