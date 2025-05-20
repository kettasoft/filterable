# Header-Driven Filter Mode

## Overview

Dynamically select filter engines per request using HTTP headers. This enables:

- Different filtering strategies for various clients (mobile/web)
- A/B testing of filter engines
- Gradual migration between engines

```php
// Example: Force using Tree engine
curl -H "X-Filter-Mode: tree" https://api.example.com/products
```

## Configuration

### Enable Feature

```php
'header_driven_mode' => [
  'enabled' => true, // Enable/disable entire feature
  'header_name' => 'X-Filter-Mode', // Customize header name
  'fallback_strategy' => 'default' // 'default' or 'error'
]
```

### Engine Whitelisting

```php
'allowed_engines' => ['dynamic', 'tree'], // Empty array allows all
```

### Name Aliasing

```php
'engine_map' => [
  'simple' => 'ruleset',
  'advanced' => 'dynamic'
],
// Use: -H "X-Filter-Mode: simple" => ruleset
```

---

## Usage Guide

### Basic Implementation

1. Add header to request:

```php
GET /api/users X-Filter-Mode: tree
```

1. Server will:
   - Validate header value
   - Map aliases if configured
   - Apply specified engine

---

### Error Handling

When `fallback_strategy = 'error'`:

```json
{
  "error": "Invalid filter engine",
  "allowed": ["dynamic", "tree"],
  "aliases": { "simple": "ruleset" }
}
```

### Per-Query Selection

```php
Filterable::make(Product::query)->withHeaderDrivenMode();
```

#### Method Reference

withHeaderDrivenMode(array $config)

| Option            | Type   | Default      | Description                                                     |
| ----------------- | ------ | ------------ | --------------------------------------------------------------- |
| header_name       | string | config value | The HTTP header name that will be checked for engine selection. |
| allowed_engines   | array  | config value | List of engine names that can be specified in the header.       |
| engine_map        | array  | config value | Maps header values to actual engine names.                      |
| fallback_strategy | string | config value | Determines behavior when an invalid engine is specified         |

## Best Practices

1. **Security**: Always whitelist engines in production

```php
'allowed_engines' => ['dynamic', 'tree']
```

## Important Notes

### Closure Engine Limitation

::: warning Warning
Header-driven mode **cannot** be used with [**Closure Pipeline Engine**](engines/closure) because:

- Closures cannot be serialized in headers
- Runtime pipeline modifications require code-level changes
  :::

#### Security Considerations

- Always whitelist allowed engines in production
- Consider rate limiting header modifications
- Disable in stateless environments where headers can't be trusted
