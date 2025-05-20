---
title: Filter Configuration Documentation
---

# Filterable Configuration Overview

---

This configuration file controls how filters are registered, resolved, and applied throughout your Laravel application.

## 1. Namespace Configuration

```php
'filter_namespace' => 'App\\Http\\Filters\\',
```

- **Type**: `string`
- **Default**: `App\Http\Filters\`
- **Description**: Base namespace for model-specific filter classes. Used when auto-registering filters.

## 2. Auto Registration

```php
'auto_register_filters' => false,
```

- **Type**: `boolean`
- **Default**: `false`
- **Description**: Enables automatic filter resolution using naming convention:
  ModelName → ModelNameFilter (e.g. Book model → BookFilter class).

## 3. Request Injection

```php
'auto_inject_request' => true,
```

- **Type**: `boolean`
- **Default**: `true`
- **Description**: Automatically injects the current Request object into filters using Laravel’s service container.

## 4. Default Engine

```php
'default_engine' => 'dynamic',
```

- **Type**: `string`
- **Default**: `dynamic`
- **Options**: `dynamic`, `tree`, `ruleset`, `closure_pipeline`, `expression`
- **Description**: Specifies default query builder engine for filter processing.

## 5. Filter Key

```php
'filter_key' => 'filter',
```

- **Type**: string
- **Default**: filter
- **Description**: Request parameter key containing filter data (e.g. `?filter[status]=active`).

## 6. Filter Engines

### 6.1 Dynamic Method Engine

```php
'dynamic' => [
    'options' => [
        'normalize_keys' => true
    ]
]
```

- **Normalize Keys**: Convert field names to lowercase

### 6.2 Tree Engine

```php
'tree' => [
    'options' => [
        'allowed_operators' => [
            'eq' => '=', 'gt' => '>', 'like' => 'LIKE'
        ],
        'depth_limit' => 5
    ]
]
```

- **Allowed Operators**: Whitelist of permitted SQL operators
- **Depth Limit**: Maximum nesting levels for conditions

### 6.3 Ruleset Engine

```php
'ruleset' => [
    'options' => [
        'strict_mode' => false,
        'allowed_fields' => ['id','name']
    ]
]
```

- **Strict Mode**: Stop processing on first error
- **Allowed Fields**: Whitelist of filterable columns

### 6.4 Closure Pipeline Engine

```php
'closure_pipeline' => [
    'options' => [
        'middlewares' => [
            function($query, $next) { /* preprocessing */ }
        ]
    ]
]
```

- **Middlewares**: Array of closure-based preprocessing functions

### 6.5 SQL Expression Engine

```php
'expression' => [
    'options' => [
        'validate_columns' => true,
        'quote_values' => true
    ]
]
```

- **Validate Columns**: Verify column existence in DB schema
- **Quote Values**: Automatic value escaping for SQL safety

## 7. Filters Mapping

```php
'mapping' => [],
```

- **Type**: array
- **Default**: []
- **Description**: Manual model-to-filter class mapping (e.g. 'App\Models\User' => 'App\Filters\AdminFilter').

## 9. Request Source

```php
'request_source' => 'query',
```

- **Type**: string
- **Options**: query, input, json
- **Description**: Determines where to extract filters from:
  - query: URL parameters (?filter=)
  - input: Request body
  - json: JSON payload

## 10. Empty Values Handling

```php
'allow_empty_values' => false,
```

- **Type**: boolean
- **Default**: false
- **Description**: When disabled, filters with empty/null values are ignored.

## 11. Query Logging

```php
'log_queries' => false,
```

- **Type**: boolean
- **Default**: false
- **Description**: Log all filter activities to Laravel's default log channel.

## 12. Filter Generation

```php
'save_filters_at' => 'Http/Filters',
'generator' => [
  'stub' => 'vendor/.../filter.stub'
]
```

- **Save Location**: Directory for generated filters
- **Custom Stub**: Override default filter template

## 13. Pagination Limit

```php
'paginate_limit' => null
```

- **Type**: `integer|null`
- **Default**: `null`
- **Description**: Default items per page for `simplePaginate()` or `paginate()` method.

## 14. Header Driven Filter Mode

### 14.1

- **Enable**: When true, the package will check for the filter mode header and attempt to use the specified engine if valid.
