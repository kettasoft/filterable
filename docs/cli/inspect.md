---
title: Inspect Filter
description: Inspect any Filterable class from the terminal using filterable:inspect. View its model, fields, operators, engine, validation rules, and provisioned data.
tags:
    - cli
    - inspect
    - artisan
    - debugging
---

## **Purpose**

The `filterable:inspect` command allows you to **analyze and inspect** the configuration of a specific Filterable class.
It displays a summary of the filter’s settings — including its model, allowed fields and operators, data provisioning, validation rules, and more.

This is particularly useful for debugging or understanding how a filter is configured internally.

---

### **Usage**

```bash
php artisan filterable:inspect {filter}
```

---

### **Arguments**

| Argument | Description                                             | Example                                     |
| -------- | ------------------------------------------------------- | ------------------------------------------- |
| `filter` | The Filterable class name or alias you want to inspect. | `php artisan filterable:inspect PostFilter` |

---

### **Example**

```bash
php artisan filterable:inspect PostFilter
```

**Output Example:**

```
🔍 Inspecting: App\Http\Filters\PostFilter

┌────────────────────────────┬──────────────────────────────────────────────┐
│ Property                   │ Value                                        │
├────────────────────────────┼──────────────────────────────────────────────┤
│ Model                      │ Post                                         │
│ Allowed Fields             │ title, content, status                       │
│ Allowed Operators          │ =, !=, like, in                              │
│ Provided Data              │ user, request                                │
│ Ignored Empty Value        │ Yes                                          │
│ Strict Mode                │ No                                           │
│ Engine                     │ Invokable                                    │
│ Has Sanitizers             │ Yes                                          │
│ Request Source             │ query                                        │
│ Request Key                │ filters                                      │
│ Validation Roles           │ title, status                                │
└────────────────────────────┴──────────────────────────────────────────────┘
```

---

### **Features**

- Displays **all configuration details** of a Filterable class in a clear table.
- Supports both **full class names** (e.g. `App\Http\Filters\PostFilter`) and **simple names** (e.g. `PostFilter`).
- Highlights important properties (like strict mode, sanitizers, etc.) using color for quick recognition.
- Useful for **debugging misconfigurations** or **verifying filter behavior**.

---

### **Common Use Cases**

- Checking which fields and operators are currently allowed for a given filter.
- Debugging unexpected filter behavior due to configuration mismatches.
- Verifying data provisioning or engine setup.
- Inspecting filters created by other developers on your team.

---

### **Notes**

- The command requires that the given class **extends `Kettasoft\Filterable\Filterable`**.
- If the class cannot be found or is not a subclass of `Filterable`, the command will show an error message.
- Works seamlessly with filters stored under `app/Http/Filters` (by default).

---

### **Sample Error**

```
Filter class [UnknownFilter] not found.
```
