<?php

namespace Kettasoft\Filterable\Foundation\Caching;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * CacheKeyGenerator - Generates deterministic cache keys
 *
 * Creates unique, deterministic cache keys for filterable operations
 * based on filter class, query parameters, data provisioning, and scopes.
 *
 * @package Kettasoft\Filterable\Foundation\Caching
 */
class CacheKeyGenerator
{
    /**
     * Cache key prefix
     *
     * @var string
     */
    protected string $prefix;

    /**
     * Cache key version for invalidation
     *
     * @var string
     */
    protected string $version;

    /**
     * Constructor
     *
     * @param string|null $prefix
     * @param string|null $version
     */
    public function __construct(?string $prefix = null, ?string $version = null)
    {
        $this->prefix = $prefix ?? config('filterable.cache.prefix', 'filterable');
        $this->version = $version ?? config('filterable.cache.version', 'v1');
    }

    /**
     * Generate a cache key for a filterable operation
     *
     * @param string $filterClass
     * @param array $filters
     * @param array $providedData
     * @param array $scopes
     * @param Builder|null $query
     * @return string
     */
    public function generate(
        string $filterClass,
        array $filters = [],
        array $providedData = [],
        array $scopes = [],
        ?Builder $query = null
    ): string {
        $parts = [
            $this->prefix,
            $this->normalizeClassName($filterClass),
            $this->hashFilters($filters),
            $this->hashProvidedData($providedData),
            $this->hashScopes($scopes),
            $this->version,
        ];

        // Add query fingerprint if available
        if ($query) {
            $parts[] = $this->generateQueryFingerprint($query);
        }

        return implode(':', array_filter($parts));
    }

    /**
     * Generate a simple cache key from components
     *
     * @param string ...$components
     * @return string
     */
    public function simple(string ...$components): string
    {
        return implode(':', array_merge([$this->prefix], $components, [$this->version]));
    }

    /**
     * Normalize class name for cache key
     *
     * @param string $className
     * @return string
     */
    protected function normalizeClassName(string $className): string
    {
        // Convert App\Filters\PostFilter to post_filter
        return Str::snake(class_basename($className));
    }

    /**
     * Hash filters array into deterministic string
     *
     * @param array $filters
     * @return string
     */
    protected function hashFilters(array $filters): string
    {
        if (empty($filters)) {
            return 'no_filters';
        }

        // Sort by keys to ensure determinism
        ksort($filters);

        // Recursively sort nested arrays
        array_walk_recursive($filters, function (&$value) {
            if (is_array($value)) {
                ksort($value);
            }
        });

        return md5(json_encode($filters));
    }

    /**
     * Hash provided data into deterministic string
     *
     * @param array $providedData
     * @return string
     */
    protected function hashProvidedData(array $providedData): string
    {
        if (empty($providedData)) {
            return '';
        }

        ksort($providedData);

        return 'data_' . md5(json_encode($providedData));
    }

    /**
     * Hash scopes into deterministic string
     *
     * @param array $scopes
     * @return string
     */
    protected function hashScopes(array $scopes): string
    {
        if (empty($scopes)) {
            return '';
        }

        ksort($scopes);

        return 'scope_' . md5(json_encode($scopes));
    }

    /**
     * Generate a fingerprint for the query builder
     *
     * @param Builder $query
     * @return string
     */
    protected function generateQueryFingerprint(Builder $query): string
    {
        // Get SQL with bindings
        $sql = $query->toSql();
        $bindings = $query->getBindings();

        // Create fingerprint
        return 'query_' . md5($sql . json_encode($bindings));
    }

    /**
     * Generate a cache key with user scope
     *
     * @param string $filterClass
     * @param int|string $userId
     * @param array $filters
     * @param array $providedData
     * @return string
     */
    public function forUser(
        string $filterClass,
        int|string $userId,
        array $filters = [],
        array $providedData = []
    ): string {
        return $this->generate(
            $filterClass,
            $filters,
            $providedData,
            ['user' => $userId]
        );
    }

    /**
     * Generate a cache key with tenant scope
     *
     * @param string $filterClass
     * @param int|string $tenantId
     * @param array $filters
     * @param array $providedData
     * @return string
     */
    public function forTenant(
        string $filterClass,
        int|string $tenantId,
        array $filters = [],
        array $providedData = []
    ): string {
        return $this->generate(
            $filterClass,
            $filters,
            $providedData,
            ['tenant' => $tenantId]
        );
    }

    /**
     * Generate a cache key with custom scopes
     *
     * @param string $filterClass
     * @param array $scopes
     * @param array $filters
     * @param array $providedData
     * @return string
     */
    public function withScopes(
        string $filterClass,
        array $scopes,
        array $filters = [],
        array $providedData = []
    ): string {
        return $this->generate(
            $filterClass,
            $filters,
            $providedData,
            $scopes
        );
    }

    /**
     * Get the cache key prefix
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Set the cache key prefix
     *
     * @param string $prefix
     * @return self
     */
    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * Get the cache key version
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Set the cache key version
     *
     * @param string $version
     * @return self
     */
    public function setVersion(string $version): self
    {
        $this->version = $version;
        return $this;
    }
}
