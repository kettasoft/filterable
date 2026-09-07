# ⚙️ Expression Engine

The **Expression Engine** is a flexible and expressive filtering engine designed to handle both flat and deeply nested filters, including relationships and their attributes.

It is ideal when you want the power of RuleSet-style syntax but also need to filter through relationships and nested relations easily.

---

## 📦 Example Request

```http
GET /posts?filter[status]=pending&filter[author.profile.name][like]=kettasoft
GET /posts?filter[author][profile][name][like]=kettasoft
```

This will:

- Filter posts where `status` is `pending`
- AND where the related author's profile `name` contains `kettasoft`

---

## 🛠️ How It Works

- Filters are parsed from the request's `filter` key.
- Each filter can be a:
    - Simple key-value pair (e.g., `filter[status]=active`)
    - Operator-based pair (e.g., `filter[name][like]=kettasoft`)
    - Nested relation filter using dot notation or nested request keys (e.g., `filter[author][profile][name]=ahmed`)

- The engine determines the filter structure and applies the corresponding query constraints.

---

## 🔧 Default Operator

If a filter doesn't specify an operator, the **default operator** will be used.  
This default is configurable in the engine settings.

```php
'default_operator' => '='
```

---

## ✅ Supported Features

- ✅ Flat and nested filters
- ✅ Dot notation and nested request arrays for relationships
- ✅ Customizable default operator
- ✅ Whitelisting of allowed fields & relations
- ✅ Works well with eager loading and relationship validation
- ✅ Prevents filtering on undefined fields (optional strict mode)

---

## ✅ Allowed Fields & Relations

To avoid unauthorized or unintended access, you can configure the engine to only accept specific fields or relations:

```php
Filterable::create()->useEngine('expression')
  ->allowedFields(['status'])
  ->allowRelations([
    'author.profile' => ['name'] // specific fields in this relation
  ])->paginate()
```

Relation authorization supports several forms:

```php
->allowRelations(['author'])                       // Every field below author
->allowRelations(['author' => ['name', 'email']]) // Selected fields
->allowRelations(['author' => ['*']])             // Explicit field wildcard
->allowRelations(['author.profile' => ['name']])  // Deep relation
```

Only configured relation paths are flattened. Ordinary array values, operator expressions, and structured conditions remain unchanged.

In **strict mode**, unsupported fields will be rejected with a validation error.

---

## 📌 Use Case

```php
Post::filter($filters, Expression::class)->get();
```

---

## 🧠 Internal Logic (Simplified)

- Normalize configured nested relation fields to dot notation.
- Detect relationships via dot notation.
- Resolve the relation path and apply `whereHas` queries for related models.
- Build appropriate SQL queries via the Eloquent builder.
- Use the defined or default operator.
