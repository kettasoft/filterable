---
sidebarDepth: 1
---

# #[Authorize]

**Stage:** `CONTROL` (1)

Requires authorization before the filter method executes. If authorization fails, the filter is skipped entirely.

---

## Parameters

| Parameter    | Type     | Required | Description                                                         |
| ------------ | -------- | -------- | ------------------------------------------------------------------- |
| `$authorize` | `string` | ✅       | Fully qualified class name implementing the `Authorizable` contract |

---

## Usage

```php
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Authorize;

#[Authorize(AdminOnly::class)]
protected function secretField(Payload $payload)
{
    return $this->builder->where('secret_field', $payload->value);
}
```

---

## Authorizable Contract

The class passed to `#[Authorize]` must implement `Kettasoft\Filterable\Contracts\Authorizable`:

```php
<?php

namespace App\Filters\Authorizations;

use Kettasoft\Filterable\Contracts\Authorizable;

class AdminOnly implements Authorizable
{
    public function authorize(): bool
    {
        return auth()->user()?->is_admin ?? false;
    }
}
```

---

## Behavior

| Scenario                               | Result                                            |
| -------------------------------------- | ------------------------------------------------- |
| `authorize()` returns `true`           | Filter method executes normally                   |
| `authorize()` returns `false`          | Filter is **skipped** (SkipExecution is thrown)    |
| Class doesn't implement `Authorizable` | `InvalidArgumentException` is thrown              |

---

## Example: Role-Based Filter Access

```php
class RoleFilter implements Authorizable
{
    public function authorize(): bool
    {
        return auth()->user()?->hasRole('manager');
    }
}

// In your filter class:
#[Authorize(RoleFilter::class)]
protected function salary(Payload $payload)
{
    return $this->builder->where('salary', '>=', $payload->value);
}
```
