---
sidebarDepth: 1
---

# Discover Command

The `filterable:discover` command automatically analyzes your Eloquent models to discover searchable columns, suggest indexes.

## Basic Usage

```bash
php artisan filterable:discover Post
```

This will scan the `Post` model and display:

-   Searchable columns (text-based fields)
-   Filterable columns (foreign keys, status fields, etc.)
-   Relationships with other models
-   Current indexing status

## Options

### `--suggest-indexes`

Analyze columns and suggest indexes to improve query performance.

```bash
php artisan filterable:discover Post --suggest-indexes
```

**Output:**

```
💡 Index Suggestions:

┌─────────┬───────────┬──────────────────────────────────────────┐
│ Column  │ Index Type│ Reason                                   │
├─────────┼───────────┼──────────────────────────────────────────┤
│ content │ FULLTEXT  │ Searchable column - will improve search  │
│ status  │ INDEX     │ Filterable column - used in WHERE        │
└─────────┴───────────┴──────────────────────────────────────────┘
```

### `--create-indexes`

Automatically create the suggested indexes on your database.

```bash
php artisan filterable:discover Post --suggest-indexes --create-indexes
```

::: warning
This will modify your database schema. Make sure to backup your database before running this in production.
:::

### `--analyze-data`

Analyze actual data in the table to provide better insights.

```bash
php artisan filterable:discover Post --analyze-data
```

**Output:**

```
📊 Data Analysis:

title:
  Distinct values: 342
  Null count: 0
  Avg length: 48.50
  Samples: Laravel Tutorial, PHP Best Practices, Advanced Vue.js

status:
  Distinct values: 3
  Null count: 0
  Samples: published, draft, archived
```

---

### `--connection`

Use a specific database connection for analysis.

```bash
php artisan filterable:discover Post --connection=tenant_db
```

## Complete Workflow

### 1. Discovery and Analysis

```bash
php artisan filterable:discover Post --analyze-data
```

### 2. Review Suggestions

```bash
php artisan filterable:discover Post --suggest-indexes
```

### 3. Create Indexes

```bash
php artisan filterable:discover Post --suggest-indexes --create-indexes
```

## Output Breakdown

### Searchable Columns

Columns that are suitable for text-based searching:

| Column Type       | Examples                                     |
| ----------------- | -------------------------------------------- |
| **Text Fields**   | `title`, `description`, `content`, `excerpt` |
| **Name Fields**   | `name`, `username`, `full_name`              |
| **Email Fields**  | `email`, `contact_email`                     |
| **String Fields** | `slug`, `code`, `reference`                  |

### Filterable Columns

Columns that are suitable for filtering:

| Column Type        | Examples                                |
| ------------------ | --------------------------------------- |
| **Foreign Keys**   | `user_id`, `category_id`, `parent_id`   |
| **Status Fields**  | `status`, `state`, `type`               |
| **Boolean Fields** | `is_active`, `is_published`, `featured` |
| **Enum Fields**    | `role`, `priority`, `visibility`        |

### Relationships

Discovered Eloquent relationships:

| Relationship | Type          | Related Model | Searchable |
| ------------ | ------------- | ------------- | ---------- |
| `user`       | BelongsTo     | User          | ✅         |
| `category`   | BelongsTo     | Category      | ✅         |
| `comments`   | HasMany       | Comment       | ❌         |
| `tags`       | BelongsToMany | Tag           | ✅         |

## Examples

### Example 1: Blog Post Model

```bash
php artisan filterable:discover Post --suggest-indexes
```

**Discovered Columns:**

-   **Searchable:** title, content, excerpt, slug
-   **Filterable:** status, category_id, user_id, is_featured
-   **Relationships:** user (BelongsTo), category (BelongsTo), comments (HasMany), tags (BelongsToMany)

### Example 2: E-commerce Product Model

```bash
php artisan filterable:discover Product --analyze-data --suggest-indexes
```

**Discovered Columns:**

-   **Searchable:** name, description, sku
-   **Filterable:** category_id, brand_id, status, in_stock
-   **Relationships:** category, brand, variants, reviews

**Analysis:**

```
name:
  Distinct values: 1,542
  Avg length: 45.30

sku:
  Distinct values: 1,542
  Null count: 0
  Pattern: Unique identifiers

status:
  Distinct values: 4
  Values: active, inactive, out_of_stock, discontinued
```

### Example 3: User Model

```bash
php artisan filterable:discover User
```

**Discovered Columns:**

-   **Searchable:** name, email, username
-   **Filterable:** role, status, email_verified_at
-   **Relationships:** posts, comments, profile

## Index Recommendations

The discover command suggests indexes based on:

### Priority 1: Foreign Keys

```sql
CREATE INDEX posts_user_id_index ON posts(user_id);
CREATE INDEX posts_category_id_index ON posts(category_id);
```

### Priority 2: Frequently Filtered Columns

```sql
CREATE INDEX posts_status_index ON posts(status);
CREATE INDEX users_role_index ON users(role);
```

### Priority 3: Searchable Text Columns

```sql
CREATE FULLTEXT INDEX posts_title_content_fulltext ON posts(title, content);
```

## Best Practices

::: tip Discovery Workflow

1. **Run discovery first** to understand your model structure
2. **Review suggestions** before creating indexes
3. **Test performance** after adding indexes
   :::

::: tip Multi-Tenant Applications
Use `--connection` to analyze tenant-specific databases:

```bash
php artisan filterable:discover Post --connection=tenant_1_db
```

```bash
php artisan filterable:discover Post --connection=tenant_1_db
```

:::

::: tip Large Tables
Use `--analyze-data` cautiously on large tables as it may take time:

```bash
# For tables with millions of rows, skip data analysis
php artisan filterable:discover Product --suggest-indexes
```

:::

## Troubleshooting

### Model Not Found

```bash
# Try with full namespace
php artisan filterable:discover "App\Models\Post"

# Check model exists
php artisan tinker
>>> class_exists('App\Models\Post')
```

### Connection Issues

```bash
# List available connections
php artisan db:show

# Test specific connection
php artisan filterable:discover Post --connection=mysql
```

### No Columns Discovered

**Possible reasons:**

-   Table doesn't exist
-   Model's `$table` property is incorrect
-   Database connection is misconfigured

**Solution:**

```bash
# Verify table exists
php artisan tinker
>>> \App\Models\Post::first()
```

### Index Creation Failed

**Common causes:**

-   Insufficient database permissions
-   Index already exists
-   Column type not supported for FULLTEXT

**Solution:**

```bash
# Check database user permissions
SHOW GRANTS FOR CURRENT_USER;

# Manually create index
CREATE INDEX posts_title_index ON posts(title);
```

## Advanced Usage

### Combining with Other Commands

```bash
# Discover, create indexes, and test
php artisan filterable:discover Post --suggest-indexes --create-indexes
php artisan filterable:test PostFilter --model=Post
```

### Batch Discovery

```bash
# Discover multiple models
for model in Post User Product Category; do
  php artisan filterable:discover $model --suggest-indexes
done
```

### CI/CD Integration

```yaml
# .github/workflows/discover-filters.yml
- name: Discover and Analyze Models
  run: |
      php artisan filterable:discover Post --suggest-indexes
      php artisan filterable:discover User --suggest-indexes
      php artisan filterable:discover Product --suggest-indexes
```

## Migration Generation

After discovering indexes, you can create a migration:

```bash
php artisan make:migration add_filterable_indexes_to_posts_table
```

```php
public function up()
{
    Schema::table('posts', function (Blueprint $table) {
        $table->index('status');
        $table->index('category_id');
        $table->fullText(['title', 'content']);
    });
}
```

## Conclusion

The `filterable:discover` command is a powerful tool to help you identify searchable and filterable columns in your Eloquent models, suggest indexes for performance optimization, and analyze your data. By integrating this command into your development workflow, you can ensure that your filters are efficient and effective.
