## ⚙️ Ruleset Engine

The **Ruleset Engine** is a straightforward filtering strategy that interprets filters as flat rule arrays. It's especially suitable for simple request formats, where each filter targets a specific field using one or more operators.

This engine is ideal for APIs and frontends that send clean key-value pairs, use operator-based nesting, or submit relational fields as nested arrays.

---

### ✅ When to Use

-   When handling **simple query structures** like:
    ```
    GET /posts?filter[status]=pending&filter[name][like]=kettasoft
    ```
-   When you prefer clear mapping of field-operator-value.
-   When you want to use **default operators** for common fields without specifying one explicitly.

---

### 🧩 How It Works

The engine accepts a request array structured as:

#### 🔹 Format 1: Default operator (e.g. `eq`)

```http
/posts?filter[status]=pending
```

This will be interpreted as:

```php
['status' => ['eq' => 'pending']]
```

The default operator (`eq`) is configurable through the engine's options or `Filterable` settings.

#### 🔹 Format 2: Custom operator

```http
/posts?filter[name][like]=kettasoft
```

This will be interpreted as:

```php
['name' => ['like' => 'kettasoft']]
```

#### 🔹 Format 3: Relational field

Relational fields may use either nested request keys or dot notation:

```http
/posts?filter[tags][name]=featured
/posts?filter[tags.name]=featured
```

Authorize the relation before applying the request:

```php
Filterable::for(Post::class, $request)
    ->using('ruleset')
    ->allowRelations(['tags' => ['name']])
    ->get();
```

Nested input is converted to `tags.name` internally. Operator and list arrays remain intact, so requests such as `filter[tags][name][like]=%php%` and `filter[tags][id][in][]=1` work as expected.

Use `['tags']` to allow every field on a relation, `['tags' => ['*']]` for an explicit field wildcard, or `['tags.post' => ['status']]` for a deep relation.

---

### 🛠 Operator Resolution

If an operator is not explicitly provided in the request, the **default operator** will be used.  
This default can be set via the engine configuration.

```php
'default_operator' => '='
```

---

### 🧱 Supported Operators

| Operator | SQL Equivalent | Example                                                     |
| -------- | -------------- | ----------------------------------------------------------- |
| eq       | =              | `filter[status]=published`                                  |
| neq      | !=             | `filter[status][neq]=draft`                                 |
| gt       | >              | `filter[views][gt]=100`                                     |
| gte      | >=             | `filter[created_at][gte]=2024-01-01`                        |
| lt       | <              | `filter[views][lt]=100`                                     |
| lte      | <=             | `filter[views][lte]=50`                                     |
| like     | LIKE           | `filter[title][like]=%laravel%`                             |
| in       | IN             | `filter[id][in][]=1&filter[id][in][]=2`                     |
| between  | BETWEEN        | `filter[price][between][]=100&filter[price][between][]=200` |
| nbetween | NOT BETWEEN    | `filter[price][nbetween][]=100&filter[price][nbetween][]=200` |
| null     | IS NULL        | `filter[deleted_at][null]`                                  |
| notnull  | IS NOT NULL    | `filter[published_at][notnull]`                             |

> Operators are customizable through [operator strategies](/features/operators).

---

### 🧪 Example Filter Class

```php
use Kettasoft\Filterable\Filterable;

class PostFilter extends Filterable
{
    protected $allowedFields = ['status', 'title', 'published_at'];

    protected $allowedOperators = ['eq', 'like', 'gte']; // Allowed operators
}
```

---

### 🔐 Security & Strict Mode

You can enforce strict filtering by enabling **strict mode**, which validates:

-   That each filter field is allowed.
-   That each operator is supported.
-   That no unexpected or malicious keys are applied.

If any validation fails, an exception will be thrown instead of silently ignoring the input.

---

### 🌿 Best Practices

-   Always define `allowed fields` and `allowed operators` in your filter class.
-   Prefer field-specific relation definitions such as `['tags' => ['name']]` when the client does not need access to every related field.
-   Use request validation or sanitizers to clean filter input before applying to query.
-   Avoid exposing sensitive fields via filters unless explicitly allowed.
