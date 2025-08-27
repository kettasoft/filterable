<?php

namespace Kettasoft\Filterable\Foundation\Sorting;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Kettasoft\Filterable\Engines\Contracts\Appliable;
use Kettasoft\Filterable\Foundation\Contracts\Sortable;

/**
 * Class Sorter
 *
 * Provides functionality to manage and apply sorting rules to Eloquent queries.
 * @package Kettasoft\Filterable\Foundation\Sorting
 * @mixin \Kettasoft\Filterable\Foundation\Contracts\Sortable
 * @link https://kettasoft.github.io/filterable/sorting
 */
class Sorter implements Appliable, Sortable
{
  /**
   * List of allowed sortable fields.
   *
   * @var array<int, string>
   */
  protected array $allowed = [];

  /**
   * Default sorting definition.
   *
   * @var array{0: string, 1: string}|null
   */
  protected ?array $default = null;

  /**
   * Aliases for sorting presets.
   * Example: ['recent' => [['created_at', 'desc']]]
   *
   * @var array<string, array<int, array{0: string, 1: string}>>
   */
  protected array $aliases = [];

  /**
   * Field mapping for input to database columns.
   *
   * @var array<string, string>
   */
  protected array $map = [];

  /**
   * Configuration settings for the sorter.
   *
   * @var \Illuminate\Support\Collection
   */
  protected \Illuminate\Support\Collection $config;

  /**
   * The key used for sorting.
   * @var string
   */
  protected $sortKey;

  /**
   * Create a new Sorter instance.
   *
   * @param Request $request
   */
  public function __construct(protected Request $request, array|null $config = null)
  {
    $this->config = $config ? collect($config) : collect(config('filterable.sorting', []));
  }

  /**
   * Create a new Sorter instance.
   *
   * @param Request $request
   * @param array|null $config
   * @return static
   */
  public static function make(Request $request, array|null $config = null): self
  {
    return new self($request, $config);
  }

  /**
   * Map input fields to database columns.
   *
   * @param array<string, string> $fields
   * @return $this
   */
  public function map(array $fields): self
  {
    $this->map = $fields;
    return $this;
  }

  /**
   * Get the mapped database column for a given input field.
   *
   * @param string $field
   * @return string
   */
  public function getFieldMapping(string $field): string
  {
    return $this->map[$field] ?? $field;
  }

  /**
   * Define which fields are allowed for sorting.
   *
   * @param array<int, string> $fields
   * @return $this
   */
  public function allow(array $fields): self
  {
    $this->allowed = $fields;
    return $this;
  }

  /**
   * Allow sorting on all fields.
   * Note: Use with caution, as this may expose sensitive fields.
   * 
   * @return $this
   */
  public function allowAll(): self
  {
    $this->allowed = ['*'];
    return $this;
  }

  /**
   * Define a default sorting field and direction.
   *
   * @param string $field
   * @param string $direction 'asc' or 'desc'
   * @return $this
   */
  public function default(string $field, string $direction = 'asc'): self
  {
    $this->default = [$field, $direction];
    return $this;
  }

  /**
   * Define default sorting using an array.
   *
   * @param array{0: string, 1: string} $defaults
   * @return $this
   */
  public function defaults(array $defaults): self
  {
    if (count($defaults) === 2 && is_string($defaults[0]) && is_string($defaults[1])) {
      return $this->default($defaults[0], $defaults[1]);
    }

    throw new \InvalidArgumentException('Defaults must be an array with exactly two string elements: [field, direction].');
  }

  /**
   * Add an alias (preset) for sorting.
   *
   * Example:
   * $sortable->alias("popular", [['views', 'desc'], ['likes', 'desc']]);
   *
   * @param string $name
   * @param array<int, array{0: string, 1: string}> $sorting
   * @return $this
   */
  public function alias(string $name, array $sorting): self
  {
    $this->aliases[$name] = $sorting;
    return $this;
  }

  /**
   * Add multiple aliases (presets) for sorting.
   *
   * Example:
   * $sortable->aliases([
   *   "popular" => [['views', 'desc'], ['likes', 'desc']],
   *   "recent" => [['created_at', 'desc']]
   * ]);
   *
   * @param array<string, array<int, array{0: string, 1: string}>> $aliases
   * @return $this
   */

  public function aliases(array $aliases): self
  {
    foreach ($aliases as $name => $sorting) {
      $this->alias($name, $sorting);
    }
    return $this;
  }

  /**
   * Set the request key to look for sorting parameters.
   *
   * @param string $key
   * @return $this
   * @throws \InvalidArgumentException
   */
  public function setSortKey(string $key): self
  {
    if (empty($key)) {
      throw new \InvalidArgumentException('Sort key cannot be empty.');
    }

    $this->sortKey = $key;
    return $this;
  }

  /**
   * Apply sorting to the query.
   * 
   * @param \Illuminate\Database\Eloquent\Builder $query
   * @return Builder
   */
  public function apply(Builder $query): Builder
  {
    // Use provided input or fallback to alias/default
    $sortInput = $this->request->input($this->getSortKey(), '');

    $aliases = array_merge($this->config->get('aliases'), $this->aliases);

    $exploded = $this->parseSortInput($sortInput);

    $fields = $this->config['multi_sort']
      ? $exploded
      : [reset($exploded)];

    if (!empty($aliases)) {
      // Handle aliases
      $this->applyAliases($fields, $query);
    }

    foreach ($fields as $field) {
      [$column, $direction] = $this->parseField($field);

      if ($this->isAllowed($column) && ! isset($aliases[$column])) {
        $this->orderBy($this->getFieldMapping($column), $direction, $query);
      }
    }

    // Apply default sorting if no sort param is provided
    if ($this->default) {
      [$field, $dir] = $this->default;
      if (in_array($field, $this->allowed, true)) {
        $this->orderBy($field, $dir, $query);
      }
    }

    return $query;
  }

  /**
   * Apply sorting aliases to the query.
   * 
   * @param string $sortInput
   * @param \Illuminate\Database\Eloquent\Builder $query
   * @return void
   */
  protected function applyAliases(array $fields, Builder $query): void
  {
    $aliases = array_merge($this->config->get('aliases'), $this->aliases);

    foreach ($fields as $field) {
      if (isset($aliases[$field])) {
        $this->applyDefault($aliases[$field], $query);
      }
    }
  }

  /**
   * Apply default sorting pattern to the query.
   *
   * @param array<int, array{0: string, 1: string}> $pattern
   * @param \Illuminate\Database\Eloquent\Builder $query
   * @return void
   */
  protected function applyDefault(array $pattern, Builder $query): void
  {
    foreach ($pattern as [$aliasField, $direction]) {
      if ($this->isAllowed($aliasField)) {
        $this->orderBy($aliasField, $direction, $query);
      }
    }
  }

  /**
   * Reset the sorter state.
   *
   * @return void
   */
  public function reset(): void
  {
    $this->allowed = [];
    $this->default = null;
    $this->aliases = [];
    $this->map = [];
  }

  /**
   * Check if the field is allowed for sorting.
   *
   * @param string $field
   * @return bool
   */
  protected function isAllowed(string $field): bool
  {
    return in_array($field, array_merge($this->config['allowed'], $this->allowed), true) || $this->allowed == ['*'];
  }

  /**
   * Parse the sort input into individual fields.
   *
   * @param string $input
   * @return array<int, string>
   */
  protected function parseSortInput(string $input): array
  {
    $fields = $this->config['multi_sort']
      ? explode($this->config['delimiter'], $input)
      : [$input];

    return array_map('trim', $fields);
  }

  /**
   * Parse sorting field & direction.
   *
   * @param string $field
   * @return array{0: string, 1: string}
   */
  protected function parseField(string $field): array
  {
    $prefix = $this->config['direction_map']['prefix'] ?? '-';

    if (str_starts_with($field, $prefix)) {
      return [ltrim($field, $prefix), 'desc'];
    }

    return [$field, 'asc'];
  }

  /**
   * Apply sorting with nulls position handling.
   *
   * @param string $field
   * @param string $direction
   * @return void
   */
  protected function orderBy(string $field, string $direction, Builder $query): void
  {
    $nulls = $this->config['nulls_position'];

    if ($nulls && in_array(strtolower($nulls), ['first', 'last'])) {
      $query->orderByRaw("{$field} {$direction} NULLS " . strtoupper($nulls));
    } else {
      $query->orderBy($field, $direction);
    }
  }

  /**
   * Get the sorting configuration.
   *
   * @return array
   */
  public function getConfig(): array
  {
    return $this->config->toArray();
  }

  /**
   * Get the allowed sorting fields.
   *
   * @return array
   */
  public function getAllowed(): array
  {
    return $this->allowed;
  }

  /**
   * Get the default sorting field and direction.
   *
   * @return array|null
   */
  public function getDefault(): ?array
  {
    return $this->default;
  }

  /**
   * Get the sorting aliases.
   *
   * @return array
   */
  public function getAliases(): array
  {
    return $this->aliases;
  }

  /**
   * Get the field mapping for input to database columns.
   *
   * @return array<string, string>
   */
  public function getMap(): array
  {
    return $this->map;
  }

  /**
   * Get the sort key used in the request.
   *
   * @return string
   */
  public function getSortKey(): string
  {
    return $this->sortKey ?? $this->config->get('sort_key', 'sort');
  }
}
