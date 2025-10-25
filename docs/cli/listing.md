# Listing All Filters

### **Purpose**

The `filterable:list` command scans your project for all **Filterable classes** (typically inside `app/Http/Filters`) and displays a concise overview of their configuration — including model association, allowed fields, allowed operators, and engine type.

It’s a convenient way to audit or debug all filters in your application at once.

---

### **Usage**

```bash
php artisan filterable:list
```

---

### **Example Output**

```
+-------------+-------------------+--------------------------+--------------------+-------------------+
| Filter      | Model             | Fields                   | Operators          | Engine            |
+-------------+-------------------+--------------------------+--------------------+-------------------+
| PostFilter  | App\Models\Post   | title, content, status   | =, !=, like, in    | Invokable         |
| UserFilter  | App\Models\User   | name, email              | =, !=, like        | Ruleset           |
| OrderFilter | App\Models\Order  | total, date, status      | =, >, <, between   | Tree              |
+-------------+-------------------+--------------------------+--------------------+-------------------+
```

---

### **Features**

-   Scans and lists all **Filterable** classes automatically.
-   Displays each filter’s **model**, **fields**, **operators**, and **engine**.
-   Helps quickly identify inconsistencies between filters.
-   Color-friendly tabular display that works well in any terminal.

---

### **When to Use**

-   To get an overview of all filters available in the application.
-   Before publishing or deploying, to verify that all filters are properly configured.
-   During debugging, to check for missing or incorrect field/operator setups.

---

### **Behavior**

-   Looks for classes inside the default `app/Http/Filters` directory (you can extend this logic in `CommandHelpers` if needed).
-   Only lists classes that extend `Kettasoft\Filterable\Filterable`.
-   If no filters are found, it displays:

    ```
    No filterable classes found.
    ```

---

### **Example Workflow**

```bash
php artisan filterable:make-filter PostFilter --filters=title,status
php artisan filterable:list
```

Then, confirm that your new filter appears in the list.

---

### **Notes**

-   Works great in combination with:

    -   `filterable:inspect` → for detailed inspection of a single filter.
    -   `filterable:setup` → to ensure configuration and directories exist before listing.
