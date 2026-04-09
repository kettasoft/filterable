<?php

namespace Kettasoft\Filterable\Engines\Foundation\Hooks;

/**
 * Immutable value object representing the hooks configuration
 * for the Invokable engine. Built via fromConfig() from
 * config('filterable.engines.invokable.hooks').
 */
final class HookConfig
{
  /**
   * Constructor.
   * @param bool $enabled
   * @param bool $fieldHooks
   * @param bool $skipHooks
   * @param bool $emptyHooks
   * @param array $prefix
   * @param string $naming
   * @param bool $haltOnFalse
   */
  public function __construct(
    /** Master switch — when false, no hooks will fire. */
    public readonly bool $enabled,

    /** Fire before{Field} / after{Field} per specific field. */
    public readonly bool $fieldHooks,

    /** Fire onSkip{Field} when a filter key has no corresponding method. */
    public readonly bool $skipHooks,

    /** Fire onEmpty{Field} when a filter value is null or empty string. */
    public readonly bool $emptyHooks,

    /**
     * Prefix map: ['before' => 'before', 'after' => 'after',
     *               'skip'   => 'onSkip',  'empty' => 'onEmpty']
     * @var array<string, string>
     */
    public readonly array $prefix,

    /**
     * Naming convention for field portion of method names.
     * Supported: 'camel' | 'studly' | 'snake'
     */
    public readonly string $naming,

    /** When a before-hook returns (bool) false, skip the filter method. */
    public readonly bool $haltOnFalse,
  ) {}

    // -----------------------------------------------------------------------
    //  Factory
    // -----------------------------------------------------------------------

  /**
   * Build from the hooks sub-array inside config('filterable.engines.invokable.hooks').
   *
   * @param array<string, mixed> $config
   */
  public static function fromConfig(array $config): self
  {
    return new self(
      enabled: (bool) ($config['enabled'] ?? true),
      fieldHooks: (bool) ($config['field_hooks'] ?? true),
      skipHooks: (bool) ($config['skip_hooks'] ?? true),
      emptyHooks: (bool) ($config['empty_hooks'] ?? true),
      prefix: array_merge(
        ['before' => 'before', 'after' => 'after', 'skip' => 'onSkip', 'empty' => 'onEmpty'],
        (array) ($config['prefix'] ?? [])
      ),
      naming: $config['naming'] ?? 'camel',
      haltOnFalse: (bool) ($config['halt_on_false'] ?? true),
    );
  }

  /**
   * Load directly from Laravel config.
   */
  public static function fromLaravelConfig(): self
  {
    return self::fromConfig(
      config('filterable.engines.invokable.hooks', [])
    );
  }
}
