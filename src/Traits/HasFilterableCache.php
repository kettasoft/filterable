<?php

namespace Kettasoft\Filterable\Traits;

use DateTimeInterface;
use Illuminate\Support\Str;
use Kettasoft\Filterable\Foundation\Caching\CacheKeyGenerator;
use Kettasoft\Filterable\Foundation\Caching\FilterableCacheManager;

/**
 * HasFilterableCache trait
 *
 * Provides caching capabilities to filterable classes.
 * Allows filters to cache their results with TTL, tags, scopes, and profiles.
 *
 * @package Kettasoft\Filterable\Traits
 */
trait HasFilterableCache
{
    /**
     * Cache TTL for this filter
     *
     * @var DateTimeInterface|int|null
     */
    protected DateTimeInterface|int|null $cacheTtl = null;

    /**
     * Cache tags for this filter
     *
     * @var array
     */
    protected array $cacheTags = [];

    /**
     * Cache scopes for this filter
     *
     * @var array
     */
    protected array $cacheScopes = [];

    /**
     * Cache profile name
     *
     * @var string|null
     */
    protected ?string $cacheProfile = null;

    /**
     * Whether caching is enabled for this filter instance
     *
     * @var bool
     */
    protected bool $cachingEnabled = false;

    /**
     * Whether to cache forever
     *
     * @var bool
     */
    protected bool $cacheForever = false;

    /**
     * Conditional caching predicate
     *
     * @var callable|null
     */
    protected $cacheWhenCallback = null;

    /**
     * Cache key generator instance
     *
     * @var CacheKeyGenerator|null
     */
    protected ?CacheKeyGenerator $cacheKeyGenerator = null;

    /**
     * Enable caching with optional TTL
     *
     * @param DateTimeInterface|int|null $ttl Time to live in seconds or DateTimeInterface
     * @return self
     */
    public function cache(DateTimeInterface|int|null $ttl = null): self
    {
        $this->cachingEnabled = true;
        $this->cacheTtl = $ttl;
        $this->cacheForever = false;

        return $this;
    }

    /**
     * Remember the results with caching
     *
     * @param DateTimeInterface|int|null $ttl
     * @return self
     */
    public function remember(DateTimeInterface|int|null $ttl = null): self
    {
        return $this->cache($ttl);
    }

    /**
     * Cache results forever
     *
     * @return self
     */
    public function cacheForever(): self
    {
        $this->cachingEnabled = true;
        $this->cacheForever = true;
        $this->cacheTtl = null;

        return $this;
    }

    /**
     * Set cache tags
     *
     * @param array $tags
     * @return self
     */
    public function cacheTags(array $tags): self
    {
        $this->cacheTags = $tags;
        return $this;
    }

    /**
     * Scope cache by authenticated user
     *
     * @param int|string|null $userId
     * @return self
     */
    public function scopeByUser(int|string|null $userId = null): self
    {
        if ($userId === null && function_exists('auth')) {
            /** @var \Illuminate\Contracts\Auth\Guard $guard */
            $guard = auth();
            $user = $guard->user();
            $userId = $user ? $user->getAuthIdentifier() : null;
        }

        if ($userId) {
            $this->cacheScopes['user'] = $userId;
        }

        return $this;
    }

    /**
     * Scope cache by tenant
     *
     * @param int|string $tenantId
     * @return self
     */
    public function scopeByTenant(int|string $tenantId): self
    {
        $this->cacheScopes['tenant'] = $tenantId;
        return $this;
    }

    /**
     * Add a custom cache scope
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function scopeBy(string $key, mixed $value): self
    {
        $this->cacheScopes[$key] = $value;
        return $this;
    }

    /**
     * Set multiple cache scopes
     *
     * @param array $scopes
     * @return self
     */
    public function withScopes(array $scopes): self
    {
        $this->cacheScopes = array_merge($this->cacheScopes, $scopes);
        return $this;
    }

    /**
     * Use a cache profile
     *
     * @param string $profile
     * @return self
     */
    public function cacheProfile(string $profile): self
    {
        $this->cacheProfile = $profile;

        $manager = app(FilterableCacheManager::class);

        if ($manager->hasProfile($profile)) {
            $config = $manager->getProfileConfig($profile);

            if (isset($config['ttl'])) {
                $this->cacheTtl = $config['ttl'];
            }

            if (isset($config['tags'])) {
                $this->cacheTags = array_merge($this->cacheTags, $config['tags']);
            }
        }

        return $this;
    }

    /**
     * Cache only when a condition is met
     *
     * @param bool|callable $condition
     * @param DateTimeInterface|int|null $ttl
     * @return self
     */
    public function cacheWhen(bool|callable $condition, DateTimeInterface|int|null $ttl = null): self
    {
        if (is_callable($condition)) {
            $this->cacheWhenCallback = $condition;
        } elseif ($condition === true) {
            return $this->cache($ttl);
        }

        return $this;
    }

    /**
     * Cache unless a condition is met
     *
     * @param bool|callable $condition
     * @param DateTimeInterface|int|null $ttl
     * @return self
     */
    public function cacheUnless(bool|callable $condition, DateTimeInterface|int|null $ttl = null): self
    {
        if (is_callable($condition)) {
            return $this->cacheWhen(fn() => !$condition(), $ttl);
        }

        return $this->cacheWhen(!$condition, $ttl);
    }

    /**
     * Flush all cached results for this filterable
     *
     * Flushes cache using the auto-generated class tag, which will clear
     * all cache entries for this filter regardless of the terminal method used.
     *
     * @return bool
     */
    public function flushCache(): bool
    {
        // Flush by the auto-generated class tag
        $classTag = 'filterable:' . Str::slug(class_basename(static::class));

        return $this->flushCacheByTags([$classTag]);
    }

    /**
     * Flush cache by tags
     *
     * @param array|null $tags
     * @return bool
     */
    public function flushCacheByTags(?array $tags = null): bool
    {
        $tags = $tags ?? $this->cacheTags;

        if (empty($tags)) {
            return false;
        }

        $manager = app(FilterableCacheManager::class);
        return $manager->flushByTags($tags);
    }

    /**
     * Flush cache by tags (static method)
     *
     * @param array $tags
     * @return bool
     */
    public static function flushCacheByTagsStatic(array $tags): bool
    {
        $manager = app(FilterableCacheManager::class);
        return $manager->flushByTags($tags);
    }

    /**
     * Check if caching is enabled for this instance
     *
     * @return bool
     */
    public function isCachingEnabled(): bool
    {
        if (!$this->cachingEnabled) {
            return false;
        }

        // Check conditional caching
        if ($this->cacheWhenCallback && !call_user_func($this->cacheWhenCallback)) {
            return false;
        }

        // Check global caching
        return app(FilterableCacheManager::class)->isEnabled();
    }

    /**
     * Generate cache key for this filter
     *
     * @return string
     */
    protected function generateCacheKey(): string
    {
        $generator = $this->getCacheKeyGenerator();

        $filters = method_exists($this, 'getFilters') ? $this->getFilters() : [];
        $providedData = property_exists($this, 'data') ? $this->data : [];

        return $generator->generate(
            static::class,
            $filters,
            $providedData,
            $this->cacheScopes,
            property_exists($this, 'builder') ? $this->builder : null
        );
    }

    /**
     * Get cache key generator instance
     *
     * @return CacheKeyGenerator
     */
    protected function getCacheKeyGenerator(): CacheKeyGenerator
    {
        if ($this->cacheKeyGenerator === null) {
            $this->cacheKeyGenerator = new CacheKeyGenerator();
        }

        return $this->cacheKeyGenerator;
    }

    /**
     * Execute with caching if enabled
     *
     * @param callable $callback
     * @return mixed
     */
    protected function executeWithCache(callable $callback): mixed
    {
        if (!$this->isCachingEnabled()) {
            return $callback();
        }

        $manager = app(FilterableCacheManager::class)
            ->withTags($this->cacheTags)
            ->withScopes($this->cacheScopes);

        if ($this->cacheProfile) {
            $manager->withProfile($this->cacheProfile);
        }

        $key = $this->generateCacheKey();

        if ($this->cacheForever) {
            return $manager->rememberForever($key, $callback);
        }

        return $manager->remember($key, $this->cacheTtl, $callback);
    }

    /**
     * Get cache TTL
     *
     * @return DateTimeInterface|int|null
     */
    public function getCacheTtl(): DateTimeInterface|int|null
    {
        return $this->cacheTtl;
    }

    /**
     * Get cache tags
     *
     * @return array
     */
    public function getCacheTags(): array
    {
        // Always include a tag based on the filter class name for easy flushing
        $classTag = 'filterable:' . Str::slug(class_basename(static::class));

        return array_unique(array_merge([$classTag], $this->cacheTags));
    }

    /**
     * Get cache scopes
     *
     * @return array
     */
    public function getCacheScopes(): array
    {
        return $this->cacheScopes;
    }

    /**
     * Get cache profile
     *
     * @return string|null
     */
    public function getCacheProfile(): ?string
    {
        return $this->cacheProfile;
    }

    /**
     * Reset all cache settings
     *
     * @return self
     */
    protected function resetCacheSettings(): self
    {
        $this->cachingEnabled = false;
        $this->cacheTtl = null;
        $this->cacheTags = [];
        $this->cacheScopes = [];
        $this->cacheProfile = null;
        $this->cacheForever = false;
        $this->cacheWhenCallback = null;

        return $this;
    }
}
