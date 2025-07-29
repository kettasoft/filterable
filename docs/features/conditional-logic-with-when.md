# Conditional Logic with **`when`**

## Overview

The `when()` method allows you to conditionally modify the instance based on a boolean expression.
Instead of writing verbose conditionals, `when()` helps you write expressive, chainable, and concise logic to update your filter configuration.
Unlike immutability patterns, this method modifies the current instance directly and returns $this, making it perfect for method chaining.

### ✨ Usage

```php
$filter = Filterable::create()
    ->when($isAdmin, function (Filterable $filter) {
    $filter->setAllowedFields(['email', 'role']);
});
```

In this example, the setAllowedFields() call is only executed if $isAdmin is true.

### 🔁 Nesting

```php
Filterable::create()->when(true, function ($filter) {
    $filter->setAllowedFields(['name']);

    $filter->when(true, function ($filter) {
        $filter->setAllowedFields(['email', 'phone']);

        $filter->when(true, fn($f) => $f->setAllowedFields(['address']));
    });
});
```

### 🔬 Behavior

If $condition is true → callback is invoked with the current instance.

If $condition is false → nothing happens.

In both cases, the original instance is returned.

### 💡 Benefits

-   ✨ Cleaner Code:
    Eliminates the need for verbose if conditions. Just chain your logic fluently using when().
-   🧠 Improved Readability:
    The code reads naturally, e.g.,
    “When the condition is true, apply this logic.”
-   🧪 Easier Testing:
    Testing conditional filter logic becomes straightforward and expressive.
-   🔁 Supports Nesting:
    Allows deeply nested conditional logic while keeping the syntax clean and expressive.
-   🔗 Chainable Design:
    when() returns the same instance, enabling seamless method chaining without breaking flow.
