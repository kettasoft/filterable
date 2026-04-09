<?php

namespace Kettasoft\Filterable\Engines\Foundation\Hooks;

use Illuminate\Support\Str;

/**
 * Resolves a (hookType, fieldName) pair into the concrete PHP method name
 * that should be looked for on the Filterable instance, according to the
 * HookConfig's prefix map and naming convention.
 *
 * Examples (default config):
 *   before + title       => beforeTitle
 *   after  + created_at  => afterCreatedAt
 *   skip   + user_id     => onSkipUserId
 *   empty  + status      => onEmptyStatus
 *
 * Note: global before/after filtering is handled by initially() / finally()
 * on the Filterable base class and does NOT go through this resolver.
 */
final class HookResolver
{
  /**
   * Constructor.
   * @param HookConfig $config
   */
  public function __construct(private readonly HookConfig $config) {}

    // -----------------------------------------------------------------------
    //  Public API
    // -----------------------------------------------------------------------

  /**
   * Resolve a hook method name.
   *
   * @param 'before'|'after'|'skip'|'empty' $type
   * @param string $field
   * @return string
   */
  public function resolve(string $type, string $field): string
  {
    $prefix = $this->config->prefix[$type] ?? $type;
    $fieldPart = $this->transformField($field);

    return $prefix . $fieldPart;
  }

  /**
   * Whether a specific hook type is enabled in the config.
   *
   * @param 'before'|'after'|'skip'|'empty' $type
   * @return bool
   */
  public function isTypeEnabled(string $type): bool
  {
    if (! $this->config->enabled) {
      return false;
    }

    return match ($type) {
      'before', 'after' => $this->config->fieldHooks,
      'skip'            => $this->config->skipHooks,
      'empty'           => $this->config->emptyHooks,
      default           => false,
    };
  }

    // -----------------------------------------------------------------------
    //  Internals
    // -----------------------------------------------------------------------

  /**
   * Transform the field name to the appropriate format.
   * @param string $field
   * @return string
   */
  private function transformField(string $field): string
  {
    return match ($this->config->naming) {
      'studly' => Str::studly($field),
      'snake'  => Str::snake($field),
      default  => Str::studly($field), // camel: prefix is lower + studly suffix
    };
  }
}
