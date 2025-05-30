# 🧠 How It Works

Filterable operates on a pluggable **Engine-based architecture**, giving you full control over how filters are interpreted and applied.

Each **engine** encapsulates a distinct filtering strategy — allowing you to choose the one that best fits your use case.

## Engine Overview

| Engine                             | Description                                                                                            |
| ---------------------------------- | ------------------------------------------------------------------------------------------------------ |
| [`Ruleset`](engines/rule-set)      | Applies a flat array of key-operator-value pairs. Best for simple APIs or when using query strings.    |
| [`Invokable`](engines/invokable)   | Maps each filter key to a method on your filter class. Great for encapsulating filter logic per field. |
| [`Expression`](engines/expression) | Flexible and expressive filtering engine designed to handle both flat and deeply nested filters.       |
| [`Tree`](engines/tree)             | Supports nested and grouped logical filtering (`AND` / `OR`), ideal for advanced search scenarios.     |

---

## Invokable Engine

The **Invokable Engine** maps each incoming filter key to a method within your custom filter class.

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

## Expression Engine

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

## Tree Engine

Ideal for complex filters with nested conditions.

```json
{
  "and": [
    {
      "field": "status",
      "operator": "eq",
      "value": "active"
    },
    {
      "or": [
        {
          "field": "title",
          "operator": "like",
          "value": "Laravel"
        },
        {
          "field": "author.name",
          "operator": "eq",
          "value": "John"
        }
      ]
    }
  ]
}
```

```php
Post::filter(Filterable::create()->useEngine('tree'))->get();
```

Supports relation filtering and nested depth control via configuration.
