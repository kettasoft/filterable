---
sidebarDepth: 2
---

# Dynamic Method Engine

The **Dynamic Method Engine** provides a powerful way to dynamically map incomming reuqest parameters to corresponding methods in a filter class. This mechanism enables clean, scalable filtering logic and behavior injection without requiring large **switch** or **if-else** blocks.

---

## Purpose

To automatically execute soecific methods in a filter class based on the incomming request keys, Each key in the request is matched with a method of the same name in the filter class and registered in **`$filters`** property, and the method is executed with the provided value.

---

## How It Works

1. The request is parsed and filtered keys are extracted.
2. A filter class contains defined methods matching possible request keys.
3. The engine loops over each request key.
4. If a method exists in the filter class matching the key and registered in **`$filters`** property, It is invoked with the value.
5. The filter class returns the modified query or resource.

---

## Example Use case

#### Incomming Request

```http
GET /api/posts?status=pending&title=PHP
```

#### Filter Class

```php
<?php

namespace App\Http\Filters;

use Kettasoft\Filterable\Abstracts\Filterable;
use Kettasoft\Filterable\Support\Payload;

class PostFilter extends Filterable
{
    protected $filters = [
        'title',
        'status',
    ];

    /**
    * Filter the query by title.
    *
    * @param Payload $payload
    * @return \Illuminate\Database\Eloquent\Builder
    */
    protected function title(Payload $payload)
    {
        return $this->builder->where('title', 'like', "%$payload%");
    }

    /**
    * Filter the query by status.
    *
    * @param Payload $payload
    * @return \Illuminate\Database\Eloquent\Builder
    */
    protected function status(Payload $payload)
    {
        if (in_array($payload->value, ['active', 'pending', 'stopped'])) {
            return $this->builder->where('status', $payload);
        }

        return $this->builder;
    }
}
```

## Usage

```php
$posts = Post::filter(PostFilter::class)->paginate();
```

---

## Supporting Arbitrary Operators

You can access not only the raw value but also the parsed operator (e.g. =, like, >, etc.) by type‑hinting the special Payload DTO in your filter methods. The engine will build an **`Payload`** instance for you containing:

- `field` – the column name
- `operator` – the parsed operator (from your ruleset or SQL‐expression config)
- `value` – the sanitized filter value
- `beforeSanitize` – the original raw input

## Mapping Request Keys

By default, the engine attempts to match the reuqest keys directly to metch names in the filter class. However, for mode flexibility and clarity, you can define a custom map that links request keys to specific method names.

This allows you to:

- Use more user-friendly request parameters.
- Decouple internal method names from public `API` keys..
- Refactor method names without affecting the frontend or query layer.

---

### Example with Custom Map

```php
<?php

namespace App\Http\Filters;

use Kettasoft\Filterable\Abstracts\Filterable;

class PostFilter extends Filterable
{
    protected $filters = [
        'joined',
        'status',
    ];

    protected $mentors = [
      'joined' => 'filterByJoined',
      'status' => 'filterByStatus'
    ]

    /**
     * Filter the query by joined.
     *
     * @param Payload $payload
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function filterByJoined(Payload $payload)
    {
        return $this->builder->whereDate('joined', '>', $payload);
    }

    /**
     * Filter the query by status.
     *
     * @param string|int Payload $payload
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function filterByStatus(Payload $payload)
    {
        return $this->builder->where('status', $payload);
    }
}
```

#### Request

```http
GET /api/users?status=1&joined=2023-01-01,2023-12-31
```

#### Explanation

- status is mapped to filterByStatus()
- joined is mapped to filterByJoined()

---

#### Automatic Fallback

If the **`$mentors`** array is empty (or not defined), the engine will automatically try to match request keys to methods by name.

```php
// If no map defined:
'status' => calls method status()
'joined' => calls method joined()
```

This ensures that the filter system remains dynamic and flexible whether or not a custom map is used.

---

### Key Features

- **Convention over configuration:** Method names match request keys.
- **Safe execution:** Only existing methods and registered filter keys in **`$filters`** are called.
- **Flexable extension:** Add or override logic in the filter class easily.
- **Clean query builder:** Keeps container logic slim and readable.

---

### Lifecycle

1. Controller recevies request.
2. $request->only([...]) extracts relevent filters.
3. Filter class loops over keys.
4. For each key:

   - if a method named **`$key`** exists and registered in **`$filters`** property, is is executed with the value.

5. Modified Eloquent query is returned.

---

### Example Flow Diagram

```text
[ Request ]
    |
    v
[ Extract Filters ] ---> [ ['status' => 'pending', 'title' => 'PHP'] ]
    |
    v
[ PostFilter ]
    - Checks: method_exists('status') && in_array($this->filters, 'status')
        - Calls: status('pending')  --> $query->where('status', 'pending')
    - Checks: method_exists('title') && in_array($this->filters, 'title')
        - Calls: title('PHP')      --> $query->where('is_title', 'PHP')
    |
    v
[ Modified Query ]
    |
    v
[ Controller returns results ]
```

### **Advanced Usage**

#### Support for Multiple Arguments

You can modify the methods to accept multiple values:

```php
public function created_between($dates)
{
    $this->query->whereBetween('created_at', explode(',', $dates));
}
```

### **Best Practices**

- Always validate input before applying filters.
- Use camelCase or snake_case consistently.
- Consider using a base filter class to share logic.
- Document available filters using PHPDoc or auto-generating from method names.
