# 🧠 How It Works

Filterable operates on a pluggable **Engine-based architecture**, giving you full control over how filters are interpreted and applied.

Each **engine** encapsulates a distinct filtering strategy — allowing you to choose the one that best fits your use case.

## Engine Overview

| Engine                                       | Description                                                                                                                                |
| -------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------ |
| [`Ruleset`](/engines/ruleset)                | Applies a flat array of key-operator-value pairs. Best for simple APIs or when using query strings.                                        |
| [`Dynamic Method`](/engines/dynamic-methods) | Maps each filter key to a method on your filter class. Great for encapsulating filter logic per field.                                     |
| [`Closure Pipeline`](/engines/closure)       | Accepts an array of closures or a filter class implementing `HasFieldFilterable`. Maximum flexibility at the cost of bypassing validation. |
| [`SQL Expression`](/engines/sql-expression)  | Global callback to handle raw SQL filtering expressions dynamically.                                                                       |
| [`Tree-Based`](/engines/tree-based)          | Supports nested and grouped logical filtering (`AND` / `OR`), ideal for advanced search scenarios.                                         |

---

## Dynamic Method Engine

The **Dynamic Method Engine** maps each incoming filter key to a method within your custom filter class.

### Example Filter Class

```php
class PostFilter extends Filterable
{
    protected $filters = ['status', 'title'];

    public function status($value)
    {
        return $this->builder->where('status', $value);
    }

    public function title($value)
    {
        return $this->builder->where('title', 'like', "%$value%");
    }
}
```

### Usage in Controller

```php
public function index(PostFilter $filter)
{
    $posts = Post::filter($filter)->paginate(10);
    return view('posts.index', compact('posts'));
}
```

---

## Closure Pipeline Engine

The **Closure Pipeline Engine** allows you to define filters as closures directly in your controller or a custom class.

### Example

```php
Post::filter([
    'status' => fn($q) => $q->where('status', 'active'),
    'title' => fn($q) => $q->where('title', 'like', '%laravel%')
])->get();
```

::: danger Important
This engine **does not** go through [Sanitization](/sanitization) or [Validation](/validation). Always handle input validation manually.
:::

---

## SQL Expression Engine

Write custom SQL logic for filtering in a centralized callback.

### Usage

```php
Post::filterUsing(function ($query, $filters) {
    if (isset($filters['published'])) {
        $query->where('published_at', '!=', null);
    }
    return $query;
})->get();
```

---

## Tree-Based Engine

Ideal for complex filters with nested conditions.

```php
$filters = [
    'and' => [
        ['field' => 'status', 'operator' => 'eq', 'value' => 'active'],
        ['or' => [
            ['field' => 'title', 'operator' => 'like', 'value' => 'Laravel'],
            ['field' => 'author.name', 'operator' => 'eq', 'value' => 'John']
        ]]
    ]
];
```

```php
Post::filter($filters)->get();
```

Supports relation filtering and nested depth control via configuration.
