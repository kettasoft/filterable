---
sidebarDepth: 2
---

# Invokable Engine

The **Invokable Engine** is the default and most commonly used engine in Filterable. It dynamically maps incoming request parameters to corresponding methods in your filter class, enabling clean, scalable filtering logic without large `switch` or `if-else` blocks.

---

## Purpose

Automatically execute specific methods in a filter class based on incoming request keys. Each key in the request is matched with a method of the same name (or mapped name) registered in the `$filters` property, and the method is invoked with a rich `Payload` object.

---

## How It Works

```text
[ Request ]
    │
    ▼
[ Extract Filter Keys ] ─── from $filters property
    │
    ▼
[ For Each Key ]
    ├── Parse operator & value (Dissector)
    ├── Create Payload (field, operator, value, rawValue)
    ├── Run Attribute Pipeline (CONTROL → TRANSFORM → VALIDATE → BEHAVIOR)
    ├── Call filter method with Payload
    └── Commit clause to query
    │
    ▼
[ Modified Query Builder ]
```

### Step by Step

1. The request is parsed and filter keys are extracted from the `$filters` property.
2. For each key, the engine parses the value through a **Dissector** to extract the operator and value.
3. A `Payload` object is created containing `field`, `operator`, `value`, and `rawValue`.
4. The **Attribute Pipeline** runs all PHP attributes (annotations) on the method, sorted by stage.
5. If the pipeline succeeds, the filter method is invoked with the `Payload`.
6. The resulting clause is committed to the query builder.

---

## Basic Example

### Incoming Request

```http
GET /api/posts?status=pending&title=PHP
```

### Filter Class

```php
<?php

namespace App\Http\Filters;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Support\Payload;

class PostFilter extends Filterable
{
    protected $filters = [
        'title',
        'status',
    ];

    protected function title(Payload $payload)
    {
        return $this->builder->where('title', 'like', $payload->asLike());
    }

    protected function status(Payload $payload)
    {
        return $this->builder->where('status', $payload->value);
    }
}
```

### Usage

```php
$posts = Post::filter(PostFilter::class)->paginate();
```

---

## The Payload Object

Every filter method receives a `Payload` instance, giving you full access to the parsed request data:

| Property   | Type     | Description                                    |
| ---------- | -------- | ---------------------------------------------- |
| `field`    | `string` | The column/filter name                         |
| `operator` | `string` | The parsed operator (e.g., `eq`, `like`, `gt`) |
| `value`    | `mixed`  | The sanitized filter value                     |
| `rawValue` | `mixed`  | The original raw input before sanitization     |

```php
protected function price(Payload $payload)
{
    return $this->builder->where('price', $payload->operator, $payload->value);
}
```

See the full [Payload API Reference](/api/payload) for all available methods.

---

## Method Mapping with `$mentors`

By default, the engine matches request keys directly to method names (converted to camelCase). You can customize this mapping with the `$mentors` property:

```php
class PostFilter extends Filterable
{
    protected $filters = ['joined', 'status'];

    protected $mentors = [
        'joined' => 'filterByJoinDate',
        'status' => 'filterByStatus',
    ];

    protected function filterByJoinDate(Payload $payload)
    {
        return $this->builder->whereDate('joined_at', '>', $payload->value);
    }

    protected function filterByStatus(Payload $payload)
    {
        return $this->builder->where('status', $payload->value);
    }
}
```

### Automatic Fallback

If `$mentors` is empty or not defined, the engine automatically matches request keys to method names:

```
'status'      → calls status()
'created_at'  → calls createdAt()
```

---

## Attribute Pipeline

The Invokable Engine supports **PHP 8 Attributes** (annotations) on filter methods. These attributes are processed through an **Attribute Pipeline** before the filter method executes.

Attributes are sorted and executed by **stage**:

| Order | Stage         | Purpose                          | Example Attributes                                                                  |
| ----- | ------------- | -------------------------------- | ----------------------------------------------------------------------------------- |
| 1     | **CONTROL**   | Decide whether to run the filter | `#[Authorize]`, `#[SkipIf]`                                                         |
| 2     | **TRANSFORM** | Modify the payload value         | `#[Trim]`, `#[Sanitize]`, `#[Cast]`, `#[MapValue]`, `#[DefaultValue]`, `#[Explode]` |
| 3     | **VALIDATE**  | Assert correctness of the value  | `#[Required]`, `#[In]`, `#[Between]`, `#[Regex]`                                    |
| 4     | **BEHAVIOR**  | Affect query behavior            | `#[Scope]`                                                                          |

### Example with Attributes

```php
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Trim;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Sanitize;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Required;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\In;

class PostFilter extends Filterable
{
    protected $filters = ['status', 'title'];

    #[Trim]
    #[Sanitize('lowercase')]
    #[Required]
    #[In('active', 'pending', 'archived')]
    protected function status(Payload $payload)
    {
        return $this->builder->where('status', $payload->value);
    }

    #[Trim]
    #[Sanitize('strip_tags')]
    protected function title(Payload $payload)
    {
        return $this->builder->where('title', 'like', $payload->asLike());
    }
}
```

In this example, when a `status` filter is received:

1. **Trim** removes whitespace from the value.
2. **Sanitize** converts it to lowercase.
3. **Required** ensures the value is not empty (throws exception if it is).
4. **In** validates the value is one of the allowed options (skips if not).
5. The filter method executes with the cleaned, validated payload.

👉 See [Annotations Reference](./annotations/) for full documentation of all available attributes.

---

## Default Operator

The default operator can be configured per engine:

```php
// config/filterable.php
'engines' => [
    'invokable' => [
        'default_operator' => 'eq',
    ],
],
```

---

## Key Features

| Feature                           | Description                                                  |
| --------------------------------- | ------------------------------------------------------------ |
| **Convention over Configuration** | Method names match request keys automatically                |
| **Safe Execution**                | Only registered filter keys in `$filters` are processed      |
| **Attribute Pipeline**            | PHP 8 attributes for validation, transformation, and control |
| **Custom Method Mapping**         | `$mentors` property for Flexible key-to-method mapping       |
| **Rich Payload Object**           | Full access to field, operator, value, and raw value         |
| **Extensible**                    | Add or override filter methods easily                        |

---

## Lifecycle

```text
1. Controller receives request
2. Post::filter(PostFilter::class) is called
3. Engine extracts keys from $filters
4. For each key present in the request:
   a. Dissector parses the operator and value
   b. Payload is created
   c. Attribute Pipeline runs (CONTROL → TRANSFORM → VALIDATE → BEHAVIOR)
   d. If pipeline passes, filter method is called with Payload
   e. Clause is committed to query
5. Modified Eloquent query is returned
```

---

## Best Practices

- **Always register filters** in the `$filters` property — unregistered methods won't execute.
- **Use attributes** to keep your filter methods focused on query logic, not validation.
- **Combine multiple attributes** — they execute in stage order, so `#[Trim]` always runs before `#[Required]`.
- **Type-hint `Payload`** in your filter methods for full IDE support.
- **Use `$mentors`** to decouple public API parameter names from internal method names.
- **Validate input** using `#[Required]`, `#[In]`, `#[Between]`, or `#[Regex]` attributes.
