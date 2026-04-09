# Setting Up Filterable

### **Purpose**

The `filterable:setup` command initializes the **Filterable** package in your application.
It publishes the package’s configuration file and ensures that the required directory structure (for filters) exists.
This command is typically the **first step** after installing the package.

---

### **Usage**

```bash
php artisan filterable:setup
```

---

### **Options**

| Option    | Description                                                     | Example                                |
| --------- | --------------------------------------------------------------- | -------------------------------------- |
| `--force` | Overwrite the existing configuration file if it already exists. | `php artisan filterable:setup --force` |

---

### **What It Does**

When executed, this command will:

1. **Publish Configuration File**
   It runs:

    ```bash
    php artisan vendor:publish --tag=filterable-config
    ```

    and places the configuration file in:

    ```
    config/filterable.php
    ```

2. **Ensure Directory Structure Exists**
   It creates:

    ```
    app/Http/Filters
    ```

    if it doesn’t already exist, ensuring you have a proper place to store your custom filters.

3. **Provide Next Steps**
   After setup, it suggests how to create your first filter:

    ```bash
    php artisan filterable:make-filter PostFilter --filters=author,title
    ```

   If you need to generate a filter in a custom location, you can override the
   default target at generation time:

    ```bash
    php artisan filterable:make-filter PostFilter \
      --namespace="Modules\\Blog\\App\\Filters" \
      --path="Modules/Blog/app/Filters"
    ```

---

### **Example Output**

```
🚀 Setting up Filterable package...
✅ Configuration file published: config/filterable.php
📁 Created directory: app/Http/Filters

🎉 Setup complete! You can now create your first filter with:
php artisan filterable:make-filter PostFilter --filters=test
```

---

### **Notes**

-   Use the `--force` flag if you want to **re-publish** the configuration file and overwrite existing settings.
-   The command automatically detects whether the `app/Http/Filters` directory already exists.
-   `filterable:make-filter` uses the defaults from `config/filterable.php`, but you can override them per run with `--namespace` and `--path`.

---

### **When to Use**

-   Right after installing the `kettasoft/filterable` package.
-   When upgrading to a version that changes configuration defaults.
-   When resetting your configuration to a clean state.
