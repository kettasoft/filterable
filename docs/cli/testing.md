---
title: Test Filter
description: Test and debug any Filterable class from the CLI using filterable:test. Simulate filter inputs, apply them to a model, and preview the generated SQL query.
tags:
    - cli
    - testing
    - artisan
    - debugging
    - sql
---

## **Purpose**

The `filterable:test` command allows you to test and debug filter classes directly from the command line.
It applies the specified filter on a given model and displays the resulting SQL query, helping you verify that your filters behave as expected.

---

### **Usage**

```bash
php artisan filterable:test {filter} --model=User --data="status=active,age=30"
```

---

### **Arguments**

| Name     | Description                                                                                           | Required |
| -------- | ----------------------------------------------------------------------------------------------------- | -------- |
| `filter` | The name of the filter class (e.g., `UserFilter`). It must exist in your configured filter namespace. | ✅ Yes   |

---

### **Options**

| Option    | Description                                                                                                   | Example                         |
| --------- | ------------------------------------------------------------------------------------------------------------- | ------------------------------- |
| `--model` | The Eloquent model class to apply the filter on. Defaults to `App\Models\{ModelName}` if not fully qualified. | `--model=User`                  |
| `--data`  | A comma-separated list of filter key-value pairs to simulate a filter input.                                  | `--data="status=active,age=30"` |

---

### **Example**

```bash
php artisan filterable:test UserFilter --model=User --data="status=active,age=30"
```

**Output:**

```
🔍 Testing filter: App\Http\Filters\UserFilter
🧩 Model: App\Models\User

Applied filters:
  • status = active
  • age = 30

✅ Query:
select * from "users" where "status" = 'active' and "age" = 30;
```

---

### **How It Works**

1. The command resolves your filter class from the configured namespace (`filterable.filter_namespace`).
2. It instantiates the model and builds an Eloquent query.
3. The filter is applied to that query using its `apply()` method.
4. The resulting SQL query is displayed in the console.
