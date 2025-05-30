# Tree Engine

This engine allows for advanced query filtering using a logical tree structure with AND/OR grouping. It is ideal for complex, nested conditions that simulate SQL-like logical grouping.

## Overview

The engine processes a nested JSON structure where each node is either:

- A logical group (and or or)
- A filter condition

This structure is then translated into an Eloquent query builder statement in Laravel.

## Example JSON Filter Request

```php
{
  "filter": {
    "and": [
      { "field": "status", "operator": "eq", "value": "active" },
      {
        "or": [
          { "field": "age", "operator": "gt", "value": 25 },
          { "field": "city", "operator": "eq", "value": "Cairo" }
        ]
      }
    ]
  }
  //...
}
```

## Config Options

| Key                 | Type     | Description                                                  |
| ------------------- | -------- | ------------------------------------------------------------ |
| `logic_operator`    | string   | Default logic when none is provided (`and` or `or`)          |
| `allowed_operators` | array    | List of allowed operator aliases and their SQL equivalents   |
| `depth_limit`       | int/null | Maximum nesting level allowed for groups. Null for unlimited |
| `normalize_keys`    | bool     | Whether to convert field names to lowercase automatically    |

## Supported Operators

| Alias     | SQL Equivalent |
| --------- | -------------- |
| `eq`      | =              |
| `neq`     | !=             |
| `gt`      | >              |
| `lt`      | <              |
| `gte`     | >=             |
| `lte`     | <=             |
| `like`    | like           |
| `nlike`   | not like       |
| `in`      | in             |
| `nin`     | not in         |
| `null`    | is null        |
| `notnull` | is not null    |
| `between` | between        |

## Error Handling

- An exception is thrown if:
- The tree exceeds the depth_limit
- An invalid or disallowed operator is used
